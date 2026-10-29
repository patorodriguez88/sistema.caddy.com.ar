<?php
// Funciones compartidas de Tareas: las usan tareas_api.php (la pantalla, con sesión) y tareas_bot.php
// (cargas de Claude Code, sin sesión). Usuarios y @menciones, quién ve qué, mails y lectura de una tarea.
// Espera $mysqli abierto y enviar_mail.php / plantilla_mail.php cargados.

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
// ---------- Quién ve qué ----------
function tk_es_admin(): bool
{
    return (int) ($_SESSION['Nivel'] ?? 0) === 1;
}
// Condición SQL: proyectos (alias $p) que $yo puede ver
function tk_sql_proyecto_visible(int $yo, string $p = 'p'): string
{
    if (tk_es_admin()) return '1 = 1';
    return "($p.Todos = 1 OR EXISTS (SELECT 1 FROM ProyectosMiembros pm WHERE pm.idProyecto = $p.id AND pm.idUsuario = $yo))";
}
// Condición SQL: tareas (alias t, con su proyecto en p) que $yo puede ver: las de sus proyectos, más las
// que tiene asignadas, creó o donde lo mencionaron aunque no participe del proyecto.
function tk_sql_tarea_visible(int $yo): string
{
    return '(' . tk_sql_proyecto_visible($yo) . " OR t.idResponsable = $yo OR t.idCreador = $yo
            OR EXISTS (SELECT 1 FROM ProyectosTareas_menciones mm WHERE mm.idTarea = t.id AND mm.idUsuario = $yo))";
}
function tk_proyecto_visible(mysqli $db, int $idProyecto, int $yo): bool
{
    $r = $db->query("SELECT 1 FROM Proyectos p WHERE p.id = " . $idProyecto . " AND p.Eliminado = 0 AND " . tk_sql_proyecto_visible($yo));
    return $r && $r->num_rows > 0;
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

// $yo > 0: solo si $yo la puede ver (si no, null igual que si no existiera).
function tk_tarea(mysqli $db, int $id, bool $incluirEliminadas = false, int $yo = 0): ?array
{
    $st = $db->prepare("SELECT t.*, p.Nombre AS Proyecto, p.Color AS ProyectoColor FROM ProyectosTareas t
                        LEFT JOIN Proyectos p ON p.id = t.idProyecto WHERE t.id = ?" . ($incluirEliminadas ? '' : ' AND t.Eliminado = 0')
                        . ($yo ? ' AND ' . tk_sql_tarea_visible($yo) : ''));
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}
// ---------- Tareas que se repiten (como en Asana) ----------
// RepetirTipo: dia | habil (lunes a viernes) | semana | mes | anio. RepetirCada: cada N (dia, semana,
// mes, anio). RepetirDias: días de la semana para "semana", ISO 1=lunes..7=domingo ("1,3,5").
const TK_REPETIR_TIPOS = ['dia', 'habil', 'semana', 'mes', 'anio'];

// Valores del formulario → [tipo|null, cada, dias|null] válidos para guardar.
function tk_repetir_normalizar($tipo, $cada, $dias): array
{
    $tipo = in_array($tipo, TK_REPETIR_TIPOS, true) ? $tipo : null;
    if ($tipo === null) return [null, 1, null];
    $cada = max(1, min(99, (int) $cada));
    $lista = array_values(array_unique(array_filter(array_map('intval', explode(',', (string) $dias)), fn($d) => $d >= 1 && $d <= 7)));
    sort($lista);
    return [$tipo, $tipo === 'habil' ? 1 : $cada, $tipo === 'semana' && $lista ? implode(',', $lista) : null];
}

// Próximo vencimiento a partir del anterior ($base, o hoy si no tenía). Si queda en el pasado (se
// completó tarde) se sigue avanzando hasta hoy o después, para no crear la siguiente ya atrasada.
function tk_proxima_fecha(?string $base, string $tipo, int $cada, ?string $dias, ?string $hoy = null): string
{
    $hoy = new DateTimeImmutable($hoy ?? date('Y-m-d'));
    $fecha = $base ? new DateTimeImmutable($base) : $hoy;
    $diaMes = (int) $fecha->format('j'); // "cada mes" respeta el día original aunque un mes sea más corto
    $semanaBase = (new DateTimeImmutable($fecha->format('o-\WW-1')));
    $diasSemana = $dias ? array_map('intval', explode(',', $dias)) : [];
    for ($i = 0; $i < 1000; $i++) {
        switch ($tipo) {
            case 'dia':
                $fecha = $fecha->modify("+$cada day");
                break;
            case 'habil':
                do { $fecha = $fecha->modify('+1 day'); } while ((int) $fecha->format('N') >= 6);
                break;
            case 'semana':
                if (!$diasSemana) {
                    $fecha = $fecha->modify("+$cada week");
                    break;
                }
                // El próximo día marcado que caiga en una semana "de turno" (cada N semanas desde la base).
                do {
                    $fecha = $fecha->modify('+1 day');
                    $semanas = intdiv((int) $semanaBase->diff(new DateTimeImmutable($fecha->format('o-\WW-1')))->days, 7);
                } while (!in_array((int) $fecha->format('N'), $diasSemana, true) || $semanas % $cada !== 0);
                break;
            case 'mes':
                $primero = $fecha->modify('first day of this month')->modify("+$cada month");
                $fecha = $primero->setDate((int) $primero->format('Y'), (int) $primero->format('n'), min($diaMes, (int) $primero->format('t')));
                break;
            case 'anio':
                $primero = $fecha->modify('first day of this month')->modify("+$cada year");
                $fecha = $primero->setDate((int) $primero->format('Y'), (int) $primero->format('n'), min($diaMes, (int) $primero->format('t')));
                break;
        }
        if ($fecha >= $hoy) break;
    }
    return $fecha->format('Y-m-d');
}

// Al completar una tarea que se repite: crea la siguiente (una sola vez por tarea, aunque la
// desmarquen y la vuelvan a completar). Devuelve [id, vence] de la nueva, o null.
function tk_crear_repeticion(mysqli $db, array $t, int $yo): ?array
{
    if (empty($t['RepetirTipo']) || !empty($t['RepeticionId'])) return null;
    $vence = tk_proxima_fecha($t['FechaVencimiento'] ?: null, $t['RepetirTipo'], (int) $t['RepetirCada'], $t['RepetirDias'] ?: null);
    $st = $db->prepare("INSERT INTO ProyectosTareas (Titulo, Descripcion, idProyecto, idResponsable, idCreador, FechaVencimiento,
                            RepetirTipo, RepetirCada, RepetirDias, FechaAlta, Actualizada)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $idProyecto = (int) $t['idProyecto'];
    $idResponsable = (int) $t['idResponsable'];
    $idCreador = (int) $t['idCreador'];
    $cada = (int) $t['RepetirCada'];
    $st->bind_param('ssiiissis', $t['Titulo'], $t['Descripcion'], $idProyecto, $idResponsable, $idCreador, $vence, $t['RepetirTipo'], $cada, $t['RepetirDias']);
    $st->execute();
    $nueva = (int) $db->insert_id;
    $db->query("UPDATE ProyectosTareas SET RepeticionId = $nueva WHERE id = " . (int) $t['id']);
    tk_tocar($db, $nueva, $yo);
    return ['id' => $nueva, 'vence' => $vence];
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
