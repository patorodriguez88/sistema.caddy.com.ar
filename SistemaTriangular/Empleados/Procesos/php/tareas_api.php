<?php
// Rec. Humanos > Tareas (Empleados/Tareas.php): proyectos y tareas propios del sistema, tipo Asana.
//
// - Todos los usuarios del sistema ven todos los proyectos; la bandeja de entrada muestra las tareas
//   donde el usuario es responsable, ordenadas por vencimiento.
// - Mail al ser asignado como responsable (por otro) y al ser @mencionado en la descripción o en un
//   comentario. La mención se escribe como @Nombre.Apellido (ver tk_usuarios: 'handle').
// - Borrado lógico (Eliminado=1): las tareas eliminadas se ven 30 días en "Eliminados" y se recuperan.
// - "Nueva" = el responsable todavía no la abrió desde su último cambio (ProyectosTareas_vistas).
//
// Tablas: ver Conexion/migraciones/2026_10_21_tareas_proyectos.sql (se crean acá si faltan).

include_once __DIR__ . '/../../../Conexion/Conexioni.php';
require_once __DIR__ . '/../../../Funciones/php/enviar_mail.php';
require_once __DIR__ . '/../../../Funciones/php/plantilla_mail.php';
date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=utf-8');

const TK_DIAS_ELIMINADOS = 30;
const TK_ADJ_MAX = 10 * 1024 * 1024; // 10 MB por archivo
const TK_ADJ_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx', 'txt'];
define('TK_ADJ_DIR', __DIR__ . '/../../Tareas/adjuntos');

function tk_responder(array $data, int $http = 200): void
{
    http_response_code($http);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function tk_migrar(mysqli $db): void
{
    $sql = file_get_contents(__DIR__ . '/../../../Conexion/migraciones/2026_10_21_tareas_proyectos.sql');
    foreach (array_filter(array_map('trim', explode(';', preg_replace('/^--.*$/m', '', $sql)))) as $q) {
        $db->query($q);
    }
}

$yo = (int) ($_SESSION['idusuario'] ?? 0);
if (!$yo) tk_responder(['success' => 0, 'error' => 'Sesión vencida: volvé a ingresar.'], 401);

try {
    $r = $mysqli->query("SHOW TABLES LIKE 'ProyectosTareas_adjuntos'"); // la última tabla agregada
    if (!$r || !$r->num_rows) tk_migrar($mysqli);
} catch (Throwable $e) {
    tk_responder(['success' => 0, 'error' => 'No se pudieron crear las tablas de tareas: ' . $e->getMessage()], 500);
}

// ---------- Usuarios del sistema (los que pueden ser responsables / mencionados) ----------
function tk_usuarios(mysqli $db): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = [];
    $res = $db->query("SELECT id, Usuario, Nombre, Apellido, Mail FROM usuarios
                       WHERE ACTIVO = 1 AND NIVEL IN (1, 2, 7) ORDER BY Nombre, Apellido");
    while ($u = $res->fetch_assoc()) {
        $nombre = trim(($u['Nombre'] ?? '') . ' ' . ($u['Apellido'] ?? '')) ?: $u['Usuario'];
        // Apodo para las @menciones: Nombre.Apellido (el login no sirve: hay con ñ o con un mail adentro).
        $handle = trim(preg_replace('/[^\p{L}\p{N}]+/u', '.', $nombre), '.') ?: 'usuario' . $u['id'];
        $cache[(int) $u['id']] = [
            'id'       => (int) $u['id'],
            'handle'   => $handle,
            'usuario'  => (string) $u['Usuario'],
            'nombre'   => $nombre,
            'iniciales' => mb_strtoupper(mb_substr($u['Nombre'] ?? '', 0, 1) . mb_substr($u['Apellido'] ?? '', 0, 1)) ?: mb_strtoupper(mb_substr($u['Usuario'], 0, 2)),
            'mail'     => trim((string) $u['Mail']),
        ];
    }
    // Apodos repetidos (dos personas con el mismo nombre): se les suma el id.
    $vistos = array_count_values(array_map(fn($u) => mb_strtolower($u['handle']), $cache));
    foreach ($cache as $id => $u) {
        if ($vistos[mb_strtolower($u['handle'])] > 1) $cache[$id]['handle'] .= $id;
    }
    return $cache;
}
// @Apodo en un texto → ids de usuario (sin repetir). Un punto final de oración no es parte del apodo.
function tk_handles(mysqli $db, string $texto): array
{
    $porHandle = [];
    foreach (tk_usuarios($db) as $u) $porHandle[mb_strtolower($u['handle'])] = $u['id'];
    preg_match_all('/@([\p{L}\p{N}._-]+)/u', $texto, $m);
    $ids = [];
    foreach ($m[1] as $h) {
        $id = $porHandle[mb_strtolower(rtrim($h, '.'))] ?? null;
        if ($id) $ids[$id] = $id;
    }
    return array_values($ids);
}
function tk_nombre(mysqli $db, int $id): string
{
    return tk_usuarios($db)[$id]['nombre'] ?? 'Usuario #' . $id;
}

// ---------- Mails ----------
function tk_link_tarea(int $idTarea): string
{
    $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $esquema . '://' . ($_SERVER['HTTP_HOST'] ?? 'sistema.caddy.com.ar') . '/SistemaTriangular/Empleados/Tareas.php?tarea=' . $idTarea;
}
function tk_mail(mysqli $db, int $idUsuario, string $asunto, string $titulo, string $subtitulo, string $cuerpo): bool
{
    $u = tk_usuarios($db)[$idUsuario] ?? null;
    if (!$u || !filter_var($u['mail'], FILTER_VALIDATE_EMAIL)) return false;
    try {
        $r = enviarMail($u['mail'], $u['nombre'], $asunto, plantillaMailCaddy($titulo, $subtitulo, $cuerpo));
        return !empty($r['success']);
    } catch (Throwable $e) {
        error_log('tareas_api mail: ' . $e->getMessage());
        return false;
    }
}
function tk_html_texto(mysqli $db, string $texto): string
{
    $html = nl2br(htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'));
    $porHandle = [];
    foreach (tk_usuarios($db) as $u) $porHandle[mb_strtolower($u['handle'])] = $u['nombre'];
    return preg_replace_callback('/@([\p{L}\p{N}._-]+)/u', function ($m) use ($porHandle) {
        $h = rtrim($m[1], '.');
        $n = $porHandle[mb_strtolower($h)] ?? null;
        return $n ? '<b style="color:#E24F30">@' . htmlspecialchars($n, ENT_QUOTES, 'UTF-8') . '</b>' . substr($m[1], strlen($h)) : $m[0];
    }, $html);
}
function tk_bloque_tarea(mysqli $db, array $t, string $extraHtml = ''): string
{
    $vence = $t['FechaVencimiento'] ? date('d/m/Y', strtotime($t['FechaVencimiento'])) : 'Sin fecha';
    return '<p style="margin:0 0 6px"><b>' . htmlspecialchars($t['Titulo'], ENT_QUOTES, 'UTF-8') . '</b></p>'
        . '<p style="margin:0 0 14px;color:#666;font-size:14px">Proyecto: ' . htmlspecialchars($t['Proyecto'] ?? '', ENT_QUOTES, 'UTF-8')
        . ' · Responsable: ' . htmlspecialchars(tk_nombre($db, (int) $t['idResponsable']), ENT_QUOTES, 'UTF-8') . ' · Vence: ' . $vence . '</p>'
        . $extraHtml
        . '<p style="margin:18px 0 0"><a href="' . tk_link_tarea((int) $t['id']) . '" style="display:inline-block;background:#E24F30;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:bold">Abrir la tarea</a></p>';
}

// ---------- Menciones: @usuario en un texto → registro + mail (una vez por usuario y origen) ----------
function tk_menciones(mysqli $db, array $tarea, string $texto, ?int $idComentario, int $autor): array
{
    $nuevos = [];
    foreach (tk_handles($db, $texto) as $id) {
        if ($id === $autor) continue;
        // En la descripción, solo las menciones nuevas (al editarla no se vuelve a avisar a los de antes)
        if ($idComentario === null) {
            $st = $db->prepare("SELECT 1 FROM ProyectosTareas_menciones WHERE idTarea=? AND idComentario IS NULL AND idUsuario=? LIMIT 1");
            $st->bind_param('ii', $tarea['id'], $id);
            $st->execute();
            if ($st->get_result()->num_rows) continue;
        }
        $st = $db->prepare("INSERT INTO ProyectosTareas_menciones (idTarea, idComentario, idUsuario, idAutor) VALUES (?,?,?,?)");
        $st->bind_param('iiii', $tarea['id'], $idComentario, $id, $autor);
        $st->execute();
        $idMencion = $db->insert_id;
        $donde = $idComentario === null ? 'en la descripción de una tarea' : 'en un comentario';
        $ok = tk_mail(
            $db, $id,
            tk_nombre($db, $autor) . ' te mencionó: ' . $tarea['Titulo'],
            'Te mencionaron en una tarea',
            tk_nombre($db, $autor) . ' te mencionó ' . $donde,
            tk_bloque_tarea($db, $tarea, '<div style="background:#f6f7fb;border-radius:8px;padding:12px 14px;font-size:14px;color:#333">' . tk_html_texto($db, $texto) . '</div>')
        );
        if ($ok) $db->query("UPDATE ProyectosTareas_menciones SET MailEnviado=1 WHERE id=" . (int) $idMencion);
        $nuevos[] = $id;
    }
    return $nuevos;
}

function tk_tarea(mysqli $db, int $id, bool $incluirEliminadas = false): ?array
{
    $st = $db->prepare("SELECT t.*, p.Nombre AS Proyecto, p.Color AS ProyectoColor FROM ProyectosTareas t
                        LEFT JOIN Proyectos p ON p.id = t.idProyecto WHERE t.id = ?" . ($incluirEliminadas ? '' : ' AND t.Eliminado = 0'));
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}
function tk_marcar_vista(mysqli $db, int $idTarea, int $usuario): void
{
    $st = $db->prepare("INSERT INTO ProyectosTareas_vistas (idTarea, idUsuario, UltimaVista) VALUES (?, ?, NOW())
                        ON DUPLICATE KEY UPDATE UltimaVista = NOW()");
    $st->bind_param('ii', $idTarea, $usuario);
    $st->execute();
    $st = $db->prepare("UPDATE ProyectosTareas_menciones SET Vista = 1 WHERE idTarea = ? AND idUsuario = ? AND Vista = 0");
    $st->bind_param('ii', $idTarea, $usuario);
    $st->execute();
}
function tk_tocar(mysqli $db, int $idTarea, int $usuario): void
{
    $db->query("UPDATE ProyectosTareas SET Actualizada = NOW() WHERE id = " . $idTarea);
    tk_marcar_vista($db, $idTarea, $usuario); // lo que hago yo no me aparece como novedad
}
function tk_adjuntos(mysqli $db, int $idTarea): array
{
    $st = $db->prepare("SELECT id, idUsuario, NombreOriginal, Mime, Tamano, Fecha FROM ProyectosTareas_adjuntos
                        WHERE idTarea = ? AND Eliminado = 0 ORDER BY Fecha, id");
    $st->bind_param('i', $idTarea);
    $st->execute();
    $lista = [];
    foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $a) {
        $lista[] = ['id' => (int) $a['id'], 'usuario' => (int) $a['idUsuario'], 'nombre' => $a['NombreOriginal'],
                    'mime' => $a['Mime'], 'tamano' => (int) $a['Tamano'], 'fecha' => $a['Fecha'],
                    'imagen' => strpos($a['Mime'], 'image/') === 0 && $a['Mime'] !== 'image/heic'];
    }
    return $lista;
}
// La carpeta de adjuntos no se sirve por URL: solo por accion=descargar (con sesión).
function tk_carpeta_adjuntos(int $idTarea): string
{
    if (!is_dir(TK_ADJ_DIR)) mkdir(TK_ADJ_DIR, 0755, true);
    if (!is_file(TK_ADJ_DIR . '/.htaccess')) {
        file_put_contents(TK_ADJ_DIR . '/.htaccess', "Require all denied\n<IfModule !mod_authz_core.c>\n  Deny from all\n</IfModule>\n");
    }
    $dir = TK_ADJ_DIR . '/' . $idTarea;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    return $dir;
}

function tk_fila(array $t, int $yo): array
{
    $hoy = date('Y-m-d');
    return [
        'id'           => (int) $t['id'],
        'titulo'       => $t['Titulo'],
        'proyecto'     => ['id' => (int) $t['idProyecto'], 'nombre' => $t['Proyecto'] ?? '', 'color' => $t['ProyectoColor'] ?? '#727cf5'],
        'responsable'  => (int) $t['idResponsable'],
        'creador'      => (int) $t['idCreador'],
        'vence'        => $t['FechaVencimiento'],
        'atrasada'     => !$t['Completada'] && $t['FechaVencimiento'] && $t['FechaVencimiento'] < $hoy,
        'completada'   => (int) $t['Completada'] === 1,
        'comentarios'  => (int) ($t['Comentarios'] ?? 0),
        'nueva'        => !$t['Completada'] && (int) $t['idResponsable'] === $yo && (empty($t['UltimaVista']) || $t['UltimaVista'] < $t['Actualizada']),
        'eliminadaEl'  => $t['EliminadoFecha'],
        'eliminadaPor' => $t['EliminadoPor'] !== null ? (int) $t['EliminadoPor'] : null,
        'diasRestantes' => $t['EliminadoFecha'] ? max(0, TK_DIAS_ELIMINADOS - (int) floor((time() - strtotime($t['EliminadoFecha'])) / 86400)) : null,
    ];
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$db = $mysqli;

try {
    switch ($accion) {

        case 'init':
            $proyectos = [];
            $res = $db->query("SELECT p.id, p.Nombre, p.Color,
                                      SUM(t.id IS NOT NULL AND t.Eliminado = 0 AND t.Completada = 0) AS Pendientes
                               FROM Proyectos p LEFT JOIN ProyectosTareas t ON t.idProyecto = p.id
                               WHERE p.Eliminado = 0 GROUP BY p.id ORDER BY p.Nombre");
            while ($p = $res->fetch_assoc()) {
                $proyectos[] = ['id' => (int) $p['id'], 'nombre' => $p['Nombre'], 'color' => $p['Color'], 'pendientes' => (int) $p['Pendientes']];
            }
            $st = $db->prepare("SELECT COUNT(*) FROM ProyectosTareas WHERE idResponsable = ? AND Eliminado = 0 AND Completada = 0");
            $st->bind_param('i', $yo);
            $st->execute();
            $bandeja = (int) $st->get_result()->fetch_row()[0];
            $eliminadas = (int) $db->query("SELECT COUNT(*) FROM ProyectosTareas WHERE Eliminado = 1
                                            AND EliminadoFecha >= DATE_SUB(NOW(), INTERVAL " . TK_DIAS_ELIMINADOS . " DAY)")->fetch_row()[0];
            $usuarios = array_values(array_map(fn($u) => array_diff_key($u, ['mail' => 1]), tk_usuarios($db)));
            tk_responder(['success' => 1, 'yo' => $yo, 'usuarios' => $usuarios, 'proyectos' => $proyectos,
                          'contadores' => ['bandeja' => $bandeja, 'eliminadas' => $eliminadas], 'diasEliminados' => TK_DIAS_ELIMINADOS]);

        case 'listar':
            $vista = $_POST['vista'] ?? 'bandeja';
            $completadas = !empty($_POST['completadas']) ? 1 : 0;
            $base = "SELECT t.*, p.Nombre AS Proyecto, p.Color AS ProyectoColor, v.UltimaVista,
                            (SELECT COUNT(*) FROM ProyectosTareas_comentarios c WHERE c.idTarea = t.id AND c.Eliminado = 0) AS Comentarios
                     FROM ProyectosTareas t
                     LEFT JOIN Proyectos p ON p.id = t.idProyecto
                     LEFT JOIN ProyectosTareas_vistas v ON v.idTarea = t.id AND v.idUsuario = ?";
            if ($vista === 'eliminados') {
                $st = $db->prepare($base . " WHERE t.Eliminado = 1 AND t.EliminadoFecha >= DATE_SUB(NOW(), INTERVAL " . TK_DIAS_ELIMINADOS . " DAY)
                                             ORDER BY t.EliminadoFecha DESC");
                $st->bind_param('i', $yo);
            } elseif ($vista === 'proyecto') {
                $idP = (int) ($_POST['idProyecto'] ?? 0);
                $st = $db->prepare($base . " WHERE t.Eliminado = 0 AND t.idProyecto = ? AND t.Completada = ?
                                             ORDER BY t.FechaVencimiento IS NULL, t.FechaVencimiento, t.id");
                $st->bind_param('iii', $yo, $idP, $completadas);
            } else {
                $st = $db->prepare($base . " WHERE t.Eliminado = 0 AND t.idResponsable = ? AND t.Completada = ?
                                             ORDER BY t.FechaVencimiento IS NULL, t.FechaVencimiento, t.id");
                $st->bind_param('iii', $yo, $yo, $completadas);
            }
            $st->execute();
            $res = $st->get_result();
            $filas = [];
            while ($t = $res->fetch_assoc()) $filas[] = tk_fila($t, $yo);
            tk_responder(['success' => 1, 'tareas' => $filas]);

        case 'tarea':
            $t = tk_tarea($db, (int) ($_POST['id'] ?? 0), true);
            if (!$t) tk_responder(['success' => 0, 'error' => 'La tarea no existe.'], 404);
            tk_marcar_vista($db, (int) $t['id'], $yo);
            $st = $db->prepare("SELECT id, idUsuario, Comentario, Fecha FROM ProyectosTareas_comentarios
                                WHERE idTarea = ? AND Eliminado = 0 ORDER BY Fecha, id");
            $st->bind_param('i', $t['id']);
            $st->execute();
            $comentarios = [];
            foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $c) {
                $comentarios[] = ['id' => (int) $c['id'], 'usuario' => (int) $c['idUsuario'], 'texto' => $c['Comentario'], 'fecha' => $c['Fecha']];
            }
            $fila = tk_fila($t, $yo);
            tk_responder(['success' => 1, 'adjuntos' => tk_adjuntos($db, (int) $t['id']), 'tarea' => $fila + [
                'descripcion'   => (string) $t['Descripcion'],
                'creada'        => $t['FechaAlta'],
                'completadaEl'  => $t['CompletadaFecha'],
                'completadaPor' => $t['CompletadaPor'] !== null ? (int) $t['CompletadaPor'] : null,
                'eliminada'     => (int) $t['Eliminado'] === 1,
            ], 'comentarios' => $comentarios]);

        case 'guardar_tarea':
            $id          = (int) ($_POST['id'] ?? 0);
            $titulo      = trim((string) ($_POST['titulo'] ?? ''));
            $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
            $idProyecto  = (int) ($_POST['idProyecto'] ?? 0);
            $responsable = (int) ($_POST['idResponsable'] ?? 0);
            $vence       = trim((string) ($_POST['vence'] ?? ''));
            if ($titulo === '') tk_responder(['success' => 0, 'error' => 'Poné un título a la tarea.']);
            if (!isset(tk_usuarios($db)[$responsable])) tk_responder(['success' => 0, 'error' => 'Elegí un responsable.']);
            $st = $db->prepare("SELECT 1 FROM Proyectos WHERE id = ? AND Eliminado = 0");
            $st->bind_param('i', $idProyecto);
            $st->execute();
            if (!$st->get_result()->num_rows) tk_responder(['success' => 0, 'error' => 'Elegí un proyecto.']);
            $vence = preg_match('/^\d{4}-\d{2}-\d{2}$/', $vence) ? $vence : null;
            $titulo = mb_substr($titulo, 0, 200);

            $anterior = $id ? tk_tarea($db, $id) : null;
            if ($id && !$anterior) tk_responder(['success' => 0, 'error' => 'La tarea no existe o fue eliminada.']);
            if ($id) {
                $st = $db->prepare("UPDATE ProyectosTareas SET Titulo=?, Descripcion=?, idProyecto=?, idResponsable=?, FechaVencimiento=? WHERE id=?");
                $st->bind_param('ssiisi', $titulo, $descripcion, $idProyecto, $responsable, $vence, $id);
                $st->execute();
            } else {
                $st = $db->prepare("INSERT INTO ProyectosTareas (Titulo, Descripcion, idProyecto, idResponsable, idCreador, FechaVencimiento)
                                    VALUES (?,?,?,?,?,?)");
                $st->bind_param('ssiiis', $titulo, $descripcion, $idProyecto, $responsable, $yo, $vence);
                $st->execute();
                $id = $db->insert_id;
            }
            tk_tocar($db, $id, $yo);
            $t = tk_tarea($db, $id);

            // Mail al responsable si se la asignó otro (al crearla o al cambiar de responsable)
            $avisados = [];
            if ($responsable !== $yo && (!$anterior || (int) $anterior['idResponsable'] !== $responsable)) {
                tk_mail($db, $responsable, 'Nueva tarea para vos: ' . $titulo, 'Tenés una tarea nueva',
                        tk_nombre($db, $yo) . ' te asignó esta tarea', tk_bloque_tarea($db, $t,
                        $descripcion !== '' ? '<div style="background:#f6f7fb;border-radius:8px;padding:12px 14px;font-size:14px;color:#333">' . tk_html_texto($db, $descripcion) . '</div>' : ''));
                $avisados[] = $responsable;
            }
            $avisados = array_merge($avisados, tk_menciones($db, $t, $descripcion, null, $yo));
            tk_responder(['success' => 1, 'id' => $id, 'avisados' => array_values(array_unique($avisados))]);

        case 'completar':
            $id = (int) ($_POST['id'] ?? 0);
            $valor = !empty($_POST['completada']) ? 1 : 0;
            if (!tk_tarea($db, $id)) tk_responder(['success' => 0, 'error' => 'La tarea no existe o fue eliminada.']);
            $st = $db->prepare("UPDATE ProyectosTareas SET Completada=?, CompletadaFecha=IF(?=1, NOW(), NULL), CompletadaPor=IF(?=1, ?, NULL) WHERE id=?");
            $st->bind_param('iiiii', $valor, $valor, $valor, $yo, $id);
            $st->execute();
            tk_tocar($db, $id, $yo);
            tk_responder(['success' => 1]);

        case 'comentar':
            $id = (int) ($_POST['id'] ?? 0);
            $texto = trim((string) ($_POST['texto'] ?? ''));
            $t = tk_tarea($db, $id);
            if (!$t) tk_responder(['success' => 0, 'error' => 'La tarea no existe o fue eliminada.']);
            if ($texto === '') tk_responder(['success' => 0, 'error' => 'Escribí un comentario.']);
            $st = $db->prepare("INSERT INTO ProyectosTareas_comentarios (idTarea, idUsuario, Comentario) VALUES (?,?,?)");
            $st->bind_param('iis', $id, $yo, $texto);
            $st->execute();
            $idComentario = $db->insert_id;
            tk_tocar($db, $id, $yo);
            $avisados = tk_menciones($db, $t, $texto, $idComentario, $yo);
            tk_responder(['success' => 1, 'avisados' => $avisados]);

        case 'eliminar':
        case 'recuperar':
            $id = (int) ($_POST['id'] ?? 0);
            $t = tk_tarea($db, $id, true);
            if (!$t) tk_responder(['success' => 0, 'error' => 'La tarea no existe.']);
            if ($accion === 'eliminar') {
                $st = $db->prepare("UPDATE ProyectosTareas SET Eliminado=1, EliminadoFecha=NOW(), EliminadoPor=? WHERE id=?");
                $st->bind_param('ii', $yo, $id);
            } else {
                // Si su proyecto ya no existe, vuelve a "General" (o al primer proyecto vivo).
                $p = $db->query("SELECT Eliminado FROM Proyectos WHERE id=" . (int) $t['idProyecto'])->fetch_assoc();
                $idProyecto = (int) $t['idProyecto'];
                if (!$p || (int) $p['Eliminado'] === 1) {
                    $idProyecto = (int) ($db->query("SELECT id FROM Proyectos WHERE Eliminado=0 ORDER BY Nombre='General' DESC, id LIMIT 1")->fetch_row()[0] ?? 0);
                }
                $st = $db->prepare("UPDATE ProyectosTareas SET Eliminado=0, EliminadoFecha=NULL, EliminadoPor=NULL, idProyecto=? WHERE id=?");
                $st->bind_param('ii', $idProyecto, $id);
            }
            $st->execute();
            tk_tocar($db, $id, $yo);
            tk_responder(['success' => 1]);

        case 'subir':
            $id = (int) ($_POST['id'] ?? 0);
            if (!tk_tarea($db, $id)) tk_responder(['success' => 0, 'error' => 'La tarea no existe o fue eliminada.']);
            $files = $_FILES['archivos'] ?? null;
            if (!$files || !is_array($files['name'])) tk_responder(['success' => 0, 'error' => 'No llegó ningún archivo.']);
            $dir = tk_carpeta_adjuntos($id);
            $subidos = [];
            $errores = [];
            foreach ($files['name'] as $i => $nombre) {
                $nombre = trim(str_replace(["\0", '/', '\\'], '', (string) $nombre)) ?: 'archivo';
                $err = $files['error'][$i];
                $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
                if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE || $files['size'][$i] > TK_ADJ_MAX) {
                    $errores[] = "$nombre: supera los " . (TK_ADJ_MAX / 1048576) . ' MB';
                    continue;
                }
                if ($err !== UPLOAD_ERR_OK) { $errores[] = "$nombre: no se pudo subir (error $err)"; continue; }
                if (!in_array($ext, TK_ADJ_EXT, true)) { $errores[] = "$nombre: tipo de archivo no permitido"; continue; }
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($files['tmp_name'][$i]) ?: 'application/octet-stream';
                $archivo = bin2hex(random_bytes(12)) . '.' . $ext;
                if (!move_uploaded_file($files['tmp_name'][$i], $dir . '/' . $archivo)) {
                    $errores[] = "$nombre: no se pudo guardar en el servidor";
                    continue;
                }
                $nombre = mb_substr($nombre, 0, 255);
                $tam = (int) $files['size'][$i];
                $st = $db->prepare("INSERT INTO ProyectosTareas_adjuntos (idTarea, idUsuario, NombreOriginal, Archivo, Mime, Tamano) VALUES (?,?,?,?,?,?)");
                $st->bind_param('iisssi', $id, $yo, $nombre, $archivo, $mime, $tam);
                $st->execute();
                $subidos[] = $nombre;
            }
            if ($subidos) tk_tocar($db, $id, $yo); // el responsable lo ve como novedad
            tk_responder(['success' => $subidos ? 1 : 0, 'subidos' => $subidos, 'errores' => $errores,
                          'error' => $subidos ? null : ($errores ? implode(' · ', $errores) : 'No se subió nada.'),
                          'adjuntos' => tk_adjuntos($db, $id)]);

        case 'descargar':
            $st = $db->prepare("SELECT a.*, t.Eliminado AS TareaEliminada FROM ProyectosTareas_adjuntos a
                                JOIN ProyectosTareas t ON t.id = a.idTarea WHERE a.id = ? AND a.Eliminado = 0");
            $idA = (int) ($_GET['id'] ?? 0);
            $st->bind_param('i', $idA);
            $st->execute();
            $a = $st->get_result()->fetch_assoc();
            $ruta = $a ? TK_ADJ_DIR . '/' . (int) $a['idTarea'] . '/' . basename($a['Archivo']) : '';
            if (!$a || !is_file($ruta)) {
                header('Content-Type: text/plain; charset=utf-8');
                http_response_code(404);
                exit('El archivo no existe.');
            }
            $inline = !empty($_GET['ver']) && (strpos($a['Mime'], 'image/') === 0 || $a['Mime'] === 'application/pdf');
            header('Content-Type: ' . $a['Mime']);
            header('Content-Length: ' . filesize($ruta));
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: private, max-age=3600');
            header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . "; filename*=UTF-8''" . rawurlencode($a['NombreOriginal']));
            readfile($ruta);
            exit;

        case 'eliminar_adjunto':
            $idA = (int) ($_POST['id'] ?? 0);
            $st = $db->prepare("UPDATE ProyectosTareas_adjuntos SET Eliminado = 1 WHERE id = ?");
            $st->bind_param('i', $idA);
            $st->execute();
            $idT = (int) ($db->query("SELECT idTarea FROM ProyectosTareas_adjuntos WHERE id = " . $idA)->fetch_row()[0] ?? 0);
            tk_responder(['success' => 1, 'adjuntos' => $idT ? tk_adjuntos($db, $idT) : []]);

        case 'guardar_proyecto':
            $id = (int) ($_POST['id'] ?? 0);
            $nombre = mb_substr(trim((string) ($_POST['nombre'] ?? '')), 0, 120);
            $color = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($_POST['color'] ?? '')) ? $_POST['color'] : '#727cf5';
            $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
            if ($nombre === '') tk_responder(['success' => 0, 'error' => 'Poné un nombre al proyecto.']);
            if ($id) {
                $st = $db->prepare("UPDATE Proyectos SET Nombre=?, Color=?, Descripcion=? WHERE id=? AND Eliminado=0");
                $st->bind_param('sssi', $nombre, $color, $descripcion, $id);
            } else {
                $st = $db->prepare("INSERT INTO Proyectos (Nombre, Color, Descripcion, idCreador) VALUES (?,?,?,?)");
                $st->bind_param('sssi', $nombre, $color, $descripcion, $yo);
            }
            $st->execute();
            tk_responder(['success' => 1, 'id' => $id ?: $db->insert_id]);

        case 'eliminar_proyecto':
            // Solo vacío: las tareas se mueven o se eliminan primero (así nada se pierde sin querer).
            $id = (int) ($_POST['id'] ?? 0);
            $pend = (int) $db->query("SELECT COUNT(*) FROM ProyectosTareas WHERE idProyecto=" . $id . " AND Eliminado=0")->fetch_row()[0];
            if ($pend) tk_responder(['success' => 0, 'error' => "El proyecto tiene $pend tarea(s). Movelas a otro proyecto o eliminalas primero."]);
            $st = $db->prepare("UPDATE Proyectos SET Eliminado=1, EliminadoFecha=NOW(), EliminadoPor=? WHERE id=?");
            $st->bind_param('ii', $yo, $id);
            $st->execute();
            tk_responder(['success' => 1]);

        default:
            tk_responder(['success' => 0, 'error' => 'Acción desconocida.'], 400);
    }
} catch (Throwable $e) {
    error_log('tareas_api ' . $accion . ': ' . $e->getMessage());
    tk_responder(['success' => 0, 'error' => 'Error del servidor: ' . $e->getMessage()], 500);
}
