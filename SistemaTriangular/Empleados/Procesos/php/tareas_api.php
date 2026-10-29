<?php
// Rec. Humanos > Tareas (Empleados/Tareas.php): proyectos y tareas propios del sistema, tipo Asana.
//
// - Cada proyecto tiene participantes (ProyectosMiembros) y solo ellos lo ven; los admins (nivel 1) ven
//   todos y los marcados "Visible para todos" (Todos=1) son de todos. No hay proyecto por defecto. Alguien de afuera asignado o mencionado
//   ve solo esa tarea. La bandeja muestra las tareas donde el usuario es responsable, por vencimiento.
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
    // Columnas sumadas después de crear la tabla (donde ya existía sin ellas)
    $r = $mysqli->query("SHOW COLUMNS FROM ProyectosTareas_adjuntos LIKE 'Purgado'");
    if (!$r->num_rows) {
        $mysqli->query("ALTER TABLE ProyectosTareas_adjuntos ADD COLUMN EliminadoFecha DATETIME NULL AFTER Eliminado,
                        ADD COLUMN Purgado TINYINT(1) NOT NULL DEFAULT 0 AFTER EliminadoFecha");
    }
    // Participantes (sumado después): donde ya había proyectos, quedan como participantes su creador y
    // quienes crearon o tienen tareas en él.
    $r = $mysqli->query("SHOW COLUMNS FROM Proyectos LIKE 'Todos'");
    if (!$r->num_rows) {
        $mysqli->query("ALTER TABLE Proyectos ADD COLUMN Todos TINYINT(1) NOT NULL DEFAULT 0 AFTER Color");
        $mysqli->query("CREATE TABLE IF NOT EXISTS ProyectosMiembros (
            idProyecto INT NOT NULL, idUsuario INT NOT NULL, idAgrego INT NULL,
            Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (idProyecto, idUsuario), KEY idx_usuario (idUsuario)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $mysqli->query("INSERT IGNORE INTO ProyectosMiembros (idProyecto, idUsuario)
                        SELECT id, idCreador FROM Proyectos WHERE idCreador > 0
                        UNION SELECT idProyecto, idResponsable FROM ProyectosTareas
                        UNION SELECT idProyecto, idCreador FROM ProyectosTareas");
    }
    // Tareas que se repiten (29/10): al completarla se crea la siguiente (tk_crear_repeticion).
    $r = $mysqli->query("SHOW COLUMNS FROM ProyectosTareas LIKE 'RepetirTipo'");
    if (!$r->num_rows) {
        $mysqli->query("ALTER TABLE ProyectosTareas
            ADD COLUMN RepetirTipo VARCHAR(10) NULL COMMENT 'dia | habil | semana | mes | anio',
            ADD COLUMN RepetirCada TINYINT NOT NULL DEFAULT 1,
            ADD COLUMN RepetirDias VARCHAR(20) NULL COMMENT 'semana: 1=lunes..7=domingo',
            ADD COLUMN RepeticionId INT NULL COMMENT 'la tarea que se creó al completar esta'");
    }
} catch (Throwable $e) {
    tk_responder(['success' => 0, 'error' => 'No se pudieron crear las tablas de tareas: ' . $e->getMessage()], 500);
}

require_once __DIR__ . '/tareas_funciones.php';

function tk_adjuntos(mysqli $db, int $idTarea): array
{
    $st = $db->prepare("SELECT id, idUsuario, NombreOriginal, Mime, Tamano, Fecha FROM ProyectosTareas_adjuntos
                        WHERE idTarea = ? AND Eliminado = 0 AND Purgado = 0 ORDER BY Fecha, id");
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
    if (!is_dir(TK_ADJ_DIR)) @mkdir(TK_ADJ_DIR, 0755, true);
    if (!is_dir(TK_ADJ_DIR) || !is_writable(TK_ADJ_DIR)) {
        error_log('tareas_api: sin permiso de escritura en ' . TK_ADJ_DIR);
        tk_responder(['success' => 0, 'error' => 'No se pudo guardar: la carpeta de archivos (Empleados/Tareas/adjuntos) no tiene permiso de escritura en el servidor.'], 500);
    }
    if (!is_file(TK_ADJ_DIR . '/.htaccess')) {
        file_put_contents(TK_ADJ_DIR . '/.htaccess', "Require all denied\n<IfModule !mod_authz_core.c>\n  Deny from all\n</IfModule>\n");
    }
    $dir = TK_ADJ_DIR . '/' . $idTarea;
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    if (!is_dir($dir)) tk_responder(['success' => 0, 'error' => 'No se pudo crear la carpeta de archivos de la tarea en el servidor.'], 500);
    return $dir;
}

// Para no llenar el disco: los archivos quitados, o de tareas eliminadas, se borran del disco a los
// TK_DIAS_ELIMINADOS días (la fila queda con Purgado=1). No hay cron: corre como mucho una vez por día,
// cuando alguien abre Tareas.
function tk_limpiar_adjuntos(mysqli $db): void
{
    if (!is_dir(TK_ADJ_DIR)) return;
    $marca = TK_ADJ_DIR . '/.ultima_limpieza';
    if (is_file($marca) && filemtime($marca) > time() - 86400) return;
    @touch($marca);
    $dias = TK_DIAS_ELIMINADOS;
    $res = $db->query("SELECT a.id, a.idTarea, a.Archivo FROM ProyectosTareas_adjuntos a
                       JOIN ProyectosTareas t ON t.id = a.idTarea
                       WHERE a.Purgado = 0
                         AND ((a.Eliminado = 1 AND a.EliminadoFecha < DATE_SUB(NOW(), INTERVAL $dias DAY))
                           OR (t.Eliminado = 1 AND t.EliminadoFecha < DATE_SUB(NOW(), INTERVAL $dias DAY)))
                       LIMIT 500");
    while ($a = $res->fetch_assoc()) {
        $ruta = TK_ADJ_DIR . '/' . (int) $a['idTarea'] . '/' . basename($a['Archivo']);
        if (is_file($ruta)) @unlink($ruta);
        $db->query("UPDATE ProyectosTareas_adjuntos SET Purgado = 1 WHERE id = " . (int) $a['id']);
    }
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
        'repetir'      => !empty($t['RepetirTipo']) ? ['tipo' => $t['RepetirTipo'], 'cada' => (int) $t['RepetirCada'], 'dias' => (string) $t['RepetirDias']] : null,
    ];
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$db = $mysqli;

try {
    switch ($accion) {

        case 'init':
            tk_limpiar_adjuntos($db);
            $proyectos = [];
            $miembros = [];
            foreach ($db->query("SELECT idProyecto, idUsuario FROM ProyectosMiembros")->fetch_all(MYSQLI_ASSOC) as $m) {
                $miembros[(int) $m['idProyecto']][] = (int) $m['idUsuario'];
            }
            $res = $db->query("SELECT p.id, p.Nombre, p.Color, p.Todos, p.idCreador,
                                      SUM(t.id IS NOT NULL AND t.Eliminado = 0 AND t.Completada = 0) AS Pendientes
                               FROM Proyectos p LEFT JOIN ProyectosTareas t ON t.idProyecto = p.id
                               WHERE p.Eliminado = 0 AND " . tk_sql_proyecto_visible($yo) . "
                               GROUP BY p.id ORDER BY p.Todos DESC, p.Nombre");
            while ($p = $res->fetch_assoc()) {
                $ids = $miembros[(int) $p['id']] ?? [];
                $proyectos[] = ['id' => (int) $p['id'], 'nombre' => $p['Nombre'], 'color' => $p['Color'], 'pendientes' => (int) $p['Pendientes'],
                                'todos' => (int) $p['Todos'] === 1, 'miembros' => $ids, 'participo' => in_array($yo, $ids, true),
                                'creador' => (int) $p['idCreador']];
            }
            $st = $db->prepare("SELECT COUNT(*) FROM ProyectosTareas WHERE idResponsable = ? AND Eliminado = 0 AND Completada = 0");
            $st->bind_param('i', $yo);
            $st->execute();
            $bandeja = (int) $st->get_result()->fetch_row()[0];
            $eliminadas = (int) $db->query("SELECT COUNT(*) FROM ProyectosTareas t LEFT JOIN Proyectos p ON p.id = t.idProyecto
                                            WHERE t.Eliminado = 1 AND t.EliminadoFecha >= DATE_SUB(NOW(), INTERVAL " . TK_DIAS_ELIMINADOS . " DAY)
                                            AND " . tk_sql_tarea_visible($yo))->fetch_row()[0];
            $usuarios = array_values(array_map(fn($u) => array_diff_key($u, ['mail' => 1]), tk_usuarios($db)));
            tk_responder(['success' => 1, 'yo' => $yo, 'esAdmin' => tk_es_admin(), 'usuarios' => $usuarios, 'proyectos' => $proyectos,
                          'contadores' => ['bandeja' => $bandeja, 'eliminadas' => $eliminadas], 'diasEliminados' => TK_DIAS_ELIMINADOS]);

        case 'contador':
            // Para el botón "Tareas" del Panel de Control: mis pendientes, y cuántas vencen hoy o están atrasadas.
            $st = $db->prepare("SELECT COUNT(*), SUM(FechaVencimiento < CURDATE()), SUM(FechaVencimiento = CURDATE())
                                FROM ProyectosTareas WHERE idResponsable = ? AND Eliminado = 0 AND Completada = 0");
            $st->bind_param('i', $yo);
            $st->execute();
            [$pend, $atr, $hoy] = $st->get_result()->fetch_row();
            tk_responder(['success' => 1, 'pendientes' => (int) $pend, 'atrasadas' => (int) $atr, 'hoy' => (int) $hoy]);

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
                                             AND " . tk_sql_tarea_visible($yo) . " ORDER BY t.EliminadoFecha DESC");
                $st->bind_param('i', $yo);
            } elseif ($vista === 'proyecto') {
                $idP = (int) ($_POST['idProyecto'] ?? 0);
                if (!tk_proyecto_visible($db, $idP, $yo)) tk_responder(['success' => 0, 'error' => 'No participás de ese proyecto.'], 403);
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
            $t = tk_tarea($db, (int) ($_POST['id'] ?? 0), true, $yo);
            if (!$t) tk_responder(['success' => 0, 'error' => 'La tarea no existe o no tenés acceso.'], 404);
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
            $anterior = $id ? tk_tarea($db, $id, false, $yo) : null;
            if ($id && !$anterior) tk_responder(['success' => 0, 'error' => 'La tarea no existe, fue eliminada o no tenés acceso.']);
            // Se puede crear o mover a un proyecto del que participo; una tarea que veo sin participar
            // (me la asignaron) puede quedarse en su proyecto.
            if (!tk_proyecto_visible($db, $idProyecto, $yo) && !($anterior && (int) $anterior['idProyecto'] === $idProyecto)) {
                tk_responder(['success' => 0, 'error' => 'Elegí un proyecto del que participes.']);
            }
            $vence = preg_match('/^\d{4}-\d{2}-\d{2}$/', $vence) ? $vence : null;
            $titulo = mb_substr($titulo, 0, 200);
            [$repTipo, $repCada, $repDias] = tk_repetir_normalizar($_POST['repetirTipo'] ?? '', $_POST['repetirCada'] ?? 1, $_POST['repetirDias'] ?? '');

            if ($id) {
                $st = $db->prepare("UPDATE ProyectosTareas SET Titulo=?, Descripcion=?, idProyecto=?, idResponsable=?, FechaVencimiento=?,
                                    RepetirTipo=?, RepetirCada=?, RepetirDias=? WHERE id=?");
                $st->bind_param('ssiissisi', $titulo, $descripcion, $idProyecto, $responsable, $vence, $repTipo, $repCada, $repDias, $id);
                $st->execute();
            } else {
                $st = $db->prepare("INSERT INTO ProyectosTareas (Titulo, Descripcion, idProyecto, idResponsable, idCreador, FechaVencimiento,
                                    RepetirTipo, RepetirCada, RepetirDias)
                                    VALUES (?,?,?,?,?,?,?,?,?)");
                $st->bind_param('ssiiissis', $titulo, $descripcion, $idProyecto, $responsable, $yo, $vence, $repTipo, $repCada, $repDias);
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
            $t = tk_tarea($db, $id, false, $yo);
            if (!$t) tk_responder(['success' => 0, 'error' => 'La tarea no existe, fue eliminada o no tenés acceso.']);
            $st = $db->prepare("UPDATE ProyectosTareas SET Completada=?, CompletadaFecha=IF(?=1, NOW(), NULL), CompletadaPor=IF(?=1, ?, NULL) WHERE id=?");
            $st->bind_param('iiiii', $valor, $valor, $valor, $yo, $id);
            $st->execute();
            tk_tocar($db, $id, $yo);
            tk_responder(['success' => 1, 'proxima' => $valor ? tk_crear_repeticion($db, $t, $yo) : null]);

        case 'comentar':
            $id = (int) ($_POST['id'] ?? 0);
            $texto = trim((string) ($_POST['texto'] ?? ''));
            $t = tk_tarea($db, $id, false, $yo);
            if (!$t) tk_responder(['success' => 0, 'error' => 'La tarea no existe, fue eliminada o no tenés acceso.']);
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
            $t = tk_tarea($db, $id, true, $yo);
            if (!$t) tk_responder(['success' => 0, 'error' => 'La tarea no existe o no tenés acceso.']);
            if ($accion === 'eliminar') {
                $st = $db->prepare("UPDATE ProyectosTareas SET Eliminado=1, EliminadoFecha=NOW(), EliminadoPor=? WHERE id=?");
                $st->bind_param('ii', $yo, $id);
            } else {
                // Si su proyecto ya no existe, vuelve al primer proyecto del que participo.
                $p = $db->query("SELECT Eliminado FROM Proyectos WHERE id=" . (int) $t['idProyecto'])->fetch_assoc();
                $idProyecto = (int) $t['idProyecto'];
                if (!$p || (int) $p['Eliminado'] === 1) {
                    $idProyecto = (int) ($db->query("SELECT p.id FROM Proyectos p JOIN ProyectosMiembros m ON m.idProyecto = p.id AND m.idUsuario = " . $yo . "
                                                     WHERE p.Eliminado = 0 ORDER BY p.Nombre LIMIT 1")->fetch_row()[0] ?? 0);
                    if (!$idProyecto) tk_responder(['success' => 0, 'error' => 'Su proyecto fue eliminado y no participás de ningún otro: creá un proyecto y volvé a recuperarla.']);
                }
                $st = $db->prepare("UPDATE ProyectosTareas SET Eliminado=0, EliminadoFecha=NULL, EliminadoPor=NULL, idProyecto=? WHERE id=?");
                $st->bind_param('ii', $idProyecto, $id);
            }
            $st->execute();
            tk_tocar($db, $id, $yo);
            tk_responder(['success' => 1]);

        case 'subir':
            $id = (int) ($_POST['id'] ?? 0);
            if (!tk_tarea($db, $id, false, $yo)) tk_responder(['success' => 0, 'error' => 'La tarea no existe, fue eliminada o no tenés acceso.']);
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
                                JOIN ProyectosTareas t ON t.id = a.idTarea LEFT JOIN Proyectos p ON p.id = t.idProyecto
                                WHERE a.id = ? AND a.Eliminado = 0 AND a.Purgado = 0 AND " . tk_sql_tarea_visible($yo));
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
            $idT = (int) ($db->query("SELECT idTarea FROM ProyectosTareas_adjuntos WHERE id = " . $idA)->fetch_row()[0] ?? 0);
            if (!tk_tarea($db, $idT, false, $yo)) tk_responder(['success' => 0, 'error' => 'No tenés acceso a ese archivo.'], 403);
            $st = $db->prepare("UPDATE ProyectosTareas_adjuntos SET Eliminado = 1, EliminadoFecha = NOW() WHERE id = ?");
            $st->bind_param('i', $idA);
            $st->execute();
            tk_responder(['success' => 1, 'adjuntos' => tk_adjuntos($db, $idT)]);

        case 'guardar_proyecto':
            $id = (int) ($_POST['id'] ?? 0);
            $nombre = mb_substr(trim((string) ($_POST['nombre'] ?? '')), 0, 120);
            $color = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($_POST['color'] ?? '')) ? $_POST['color'] : '#727cf5';
            $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
            $todos = !empty($_POST['todos']) ? 1 : 0;
            // Participantes: los elegidos (usuarios del sistema), y yo siempre (si no soy admin, si me
            // saco a mí mismo dejaría de verlo).
            $miembros = array_values(array_intersect(array_map('intval', (array) ($_POST['miembros'] ?? [])), array_keys(tk_usuarios($db))));
            if (!tk_es_admin() || !$id) $miembros[] = $yo;
            $miembros = array_values(array_unique($miembros));
            if ($nombre === '') tk_responder(['success' => 0, 'error' => 'Poné un nombre al proyecto.']);
            if ($id) {
                if (!tk_proyecto_visible($db, $id, $yo)) tk_responder(['success' => 0, 'error' => 'No participás de ese proyecto.'], 403);
                $st = $db->prepare("UPDATE Proyectos SET Nombre=?, Color=?, Descripcion=?, Todos=? WHERE id=? AND Eliminado=0");
                $st->bind_param('sssii', $nombre, $color, $descripcion, $todos, $id);
                $st->execute();
            } else {
                $st = $db->prepare("INSERT INTO Proyectos (Nombre, Color, Descripcion, Todos, idCreador) VALUES (?,?,?,?,?)");
                $st->bind_param('sssii', $nombre, $color, $descripcion, $todos, $yo);
                $st->execute();
                $id = $db->insert_id;
            }
            $db->query("DELETE FROM ProyectosMiembros WHERE idProyecto = " . $id
                       . ($miembros ? " AND idUsuario NOT IN (" . implode(',', $miembros) . ")" : ''));
            $st = $db->prepare("INSERT IGNORE INTO ProyectosMiembros (idProyecto, idUsuario, idAgrego) VALUES (?,?,?)");
            foreach ($miembros as $m) {
                $st->bind_param('iii', $id, $m, $yo);
                $st->execute();
            }
            tk_responder(['success' => 1, 'id' => $id]);

        case 'eliminar_proyecto':
            // Solo vacío: las tareas se mueven o se eliminan primero (así nada se pierde sin querer).
            $id = (int) ($_POST['id'] ?? 0);
            if (!tk_proyecto_visible($db, $id, $yo)) tk_responder(['success' => 0, 'error' => 'No participás de ese proyecto.'], 403);
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
