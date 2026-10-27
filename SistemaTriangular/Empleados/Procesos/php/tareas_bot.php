<?php
// Tareas cargadas por Claude Code (pedido de Patricio, 2026-10-27): el hosting no deja dar permisos
// de MySQL por tabla, así que en vez de un usuario de base con escritura hay este endpoint acotado.
//
// - Sin sesión: se autentica con el header X-Tareas-Token. Acá solo está el sha256 de la clave; la
//   clave vive en la Mac de Patricio (~/.caddy_tareas_token). Para revocar: borrar este archivo o
//   cambiar el hash.
// - Acciones, siempre sobre el proyecto Sistema: 'crear', 'comentar' y 'completar'. No lee ni toca otra cosa.
// - Por defecto creador, responsable y autor son Patricio (sin mails). Si se pasa 'creador', 'responsable'
//   o 'autor' (usuarios.id), los mails salen igual que desde la pantalla: al responsable si se la asignó
//   otro y a los @mencionados (funciones de tareas_funciones.php).

const TB_TOKEN_SHA256 = '6cad6acc98fb65c12629ff67d1b60434f28c154827a971170777f017cc77b883';
const TB_USUARIO = 7;            // Patricio Rodriguez (usuarios.id)
const TB_PROYECTO = 'Sistema';   // Proyectos.Nombre

header('Content-Type: application/json; charset=utf-8');

function tb_responder(array $data, int $http = 200): void
{
    http_response_code($http);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') tb_responder(['success' => 0, 'error' => 'Método no permitido.'], 405);
$token = (string) ($_SERVER['HTTP_X_TAREAS_TOKEN'] ?? '');
if ($token === '' || !hash_equals(TB_TOKEN_SHA256, hash('sha256', $token))) {
    tb_responder(['success' => 0, 'error' => 'No autorizado.'], 403);
}

require __DIR__ . '/../../../Conexion/conexion_publica.php';
require_once __DIR__ . '/../../../Funciones/php/enviar_mail.php';
require_once __DIR__ . '/../../../Funciones/php/plantilla_mail.php';
require_once __DIR__ . '/tareas_funciones.php';
date_default_timezone_set('America/Argentina/Cordoba');
$mysqli->set_charset('utf8mb4');
$mysqli->query("SET time_zone = '-03:00'"); // el servidor está en hora de EE.UU.
$db = $mysqli;

// Usuario pasado por POST (o Patricio): tiene que ser de los que usan Tareas (tk_usuarios).
function tb_usuario(mysqli $db, string $campo): int
{
    $id = (int) ($_POST[$campo] ?? TB_USUARIO);
    if (!isset(tk_usuarios($db)[$id])) tb_responder(['success' => 0, 'error' => "Usuario inválido en '$campo'."]);
    return $id;
}

$st = $mysqli->prepare("SELECT id FROM Proyectos WHERE Nombre = ? AND Eliminado = 0 ORDER BY id LIMIT 1");
$nombreProyecto = TB_PROYECTO;
$st->bind_param('s', $nombreProyecto);
$st->execute();
$idProyecto = (int) ($st->get_result()->fetch_assoc()['id'] ?? 0);
if (!$idProyecto) tb_responder(['success' => 0, 'error' => 'No existe el proyecto ' . TB_PROYECTO . '.'], 500);

$usuario = TB_USUARIO;
switch ($_POST['accion'] ?? '') {
    case 'crear':
        $titulo = mb_substr(trim((string) ($_POST['titulo'] ?? '')), 0, 200);
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $vence = trim((string) ($_POST['vence'] ?? ''));
        $vence = preg_match('/^\d{4}-\d{2}-\d{2}$/', $vence) ? $vence : date('Y-m-d');
        $completada = !empty($_POST['completada']) ? 1 : 0;
        $creador = tb_usuario($db, 'creador');
        $responsable = tb_usuario($db, 'responsable');
        if ($titulo === '') tb_responder(['success' => 0, 'error' => 'Falta el título.']);

        // Mismo título en el proyecto = la misma tarea (evita duplicar si se reintenta).
        $st = $mysqli->prepare("SELECT id FROM ProyectosTareas WHERE idProyecto = ? AND Titulo = ? AND Eliminado = 0 LIMIT 1");
        $st->bind_param('is', $idProyecto, $titulo);
        $st->execute();
        if ($fila = $st->get_result()->fetch_assoc()) {
            tb_responder(['success' => 1, 'id' => (int) $fila['id'], 'existente' => 1]);
        }

        $st = $mysqli->prepare("INSERT INTO ProyectosTareas (Titulo, Descripcion, idProyecto, idResponsable, idCreador, FechaVencimiento,
                                    Completada, CompletadaFecha, CompletadaPor, FechaAlta, Actualizada)
                                VALUES (?, ?, ?, ?, ?, ?, ?, IF(? = 1, NOW(), NULL), IF(? = 1, ?, NULL), NOW(), NOW())");
        $st->bind_param('ssiiisiiii', $titulo, $descripcion, $idProyecto, $responsable, $creador, $vence,
                        $completada, $completada, $completada, $usuario);
        $st->execute();
        $id = $mysqli->insert_id;
        tk_tocar($db, $id, $creador);
        $t = tk_tarea($db, $id);

        // Mismos avisos que guardar_tarea en tareas_api.php
        $avisados = [];
        if ($responsable !== $creador) {
            tk_mail($db, $responsable, 'Nueva tarea para vos: ' . $titulo, 'Tenés una tarea nueva',
                    tk_nombre($db, $creador) . ' te asignó esta tarea', tk_bloque_tarea($db, $t,
                    $descripcion !== '' ? '<div style="background:#f6f7fb;border-radius:8px;padding:12px 14px;font-size:14px;color:#333">' . tk_html_texto($db, $descripcion) . '</div>' : ''));
            $avisados[] = $responsable;
        }
        $avisados = array_merge($avisados, tk_menciones($db, $t, $descripcion, null, $creador));
        tb_responder(['success' => 1, 'id' => $id, 'avisados' => array_values(array_unique($avisados))]);

    case 'comentar':
        $id = (int) ($_POST['id'] ?? 0);
        $texto = trim((string) ($_POST['texto'] ?? ''));
        $autor = tb_usuario($db, 'autor');
        $t = tk_tarea($db, $id);
        if (!$t || (int) $t['idProyecto'] !== $idProyecto) tb_responder(['success' => 0, 'error' => 'La tarea no existe o no es del proyecto ' . TB_PROYECTO . '.']);
        if ($texto === '') tb_responder(['success' => 0, 'error' => 'Falta el texto.']);
        $st = $mysqli->prepare("INSERT INTO ProyectosTareas_comentarios (idTarea, idUsuario, Comentario) VALUES (?,?,?)");
        $st->bind_param('iis', $id, $autor, $texto);
        $st->execute();
        $idComentario = $mysqli->insert_id;
        tk_tocar($db, $id, $autor);
        tb_responder(['success' => 1, 'idComentario' => $idComentario, 'avisados' => tk_menciones($db, $t, $texto, $idComentario, $autor)]);

    case 'completar':
        $id = (int) ($_POST['id'] ?? 0);
        $st = $mysqli->prepare("UPDATE ProyectosTareas SET Completada = 1, CompletadaFecha = NOW(), CompletadaPor = ?, Actualizada = NOW()
                                WHERE id = ? AND idProyecto = ? AND Eliminado = 0 AND Completada = 0");
        $st->bind_param('iii', $usuario, $id, $idProyecto);
        $st->execute();
        tb_responder(['success' => 1, 'cambiada' => $st->affected_rows]);

    default:
        tb_responder(['success' => 0, 'error' => 'Acción desconocida.'], 400);
}
