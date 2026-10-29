<?php
// Tareas cargadas por Claude Code (pedido de Patricio, 2026-10-27): el hosting no deja dar permisos
// de MySQL por tabla, así que en vez de un usuario de base con escritura hay este endpoint acotado.
//
// - Sin sesión: se autentica con el header X-Tareas-Token. Acá solo está el sha256 de la clave; la
//   clave vive en la Mac de Patricio (~/.caddy_tareas_token). Para revocar: borrar este archivo o
//   cambiar el hash.
// - Acciones, siempre sobre el proyecto Sistema: 'crear', 'comentar', 'adjuntar' y 'completar'. No lee ni toca otra cosa.
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

    case 'adjuntar':
        // Un archivo (campo 'archivo') a una tarea, igual que la pantalla (tareas_api.php: subir).
        $id = (int) ($_POST['id'] ?? 0);
        $autor = tb_usuario($db, 'autor');
        $t = tk_tarea($db, $id);
        if (!$t || (int) $t['idProyecto'] !== $idProyecto) tb_responder(['success' => 0, 'error' => 'La tarea no existe o no es del proyecto ' . TB_PROYECTO . '.']);
        $f = $_FILES['archivo'] ?? null;
        if (!$f || $f['error'] !== UPLOAD_ERR_OK) tb_responder(['success' => 0, 'error' => 'No llegó el archivo.']);
        $nombre = mb_substr(trim(str_replace(["\0", '/', '\\'], '', (string) $f['name'])) ?: 'archivo', 0, 255);
        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        if ($f['size'] > 10 * 1024 * 1024 || !in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xlsx', 'docx', 'csv', 'txt'], true)) {
            tb_responder(['success' => 0, 'error' => 'Tipo de archivo no permitido o más de 10 MB.']);
        }
        $raiz = __DIR__ . '/../../Tareas/adjuntos'; // misma carpeta que la pantalla, bloqueada por .htaccess
        if (!is_dir($raiz)) @mkdir($raiz, 0755, true);
        if (!is_file($raiz . '/.htaccess')) {
            file_put_contents($raiz . '/.htaccess', "Require all denied\n<IfModule !mod_authz_core.c>\n  Deny from all\n</IfModule>\n");
        }
        $dir = $raiz . '/' . $id;
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $archivo = bin2hex(random_bytes(12)) . '.' . $ext;
        if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $archivo)) tb_responder(['success' => 0, 'error' => 'No se pudo guardar el archivo.']);
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($dir . '/' . $archivo) ?: 'application/octet-stream';
        $tam = (int) $f['size'];
        $st = $mysqli->prepare("INSERT INTO ProyectosTareas_adjuntos (idTarea, idUsuario, NombreOriginal, Archivo, Mime, Tamano) VALUES (?,?,?,?,?,?)");
        $st->bind_param('iisssi', $id, $autor, $nombre, $archivo, $mime, $tam);
        $st->execute();
        tk_tocar($db, $id, $autor);
        tb_responder(['success' => 1, 'idAdjunto' => $mysqli->insert_id]);

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
