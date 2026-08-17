<?php
// Puente entre el menú real (topnav.html) y la tabla usuarios_permisos.
// - tieneMenuPermiso(): usado desde topnav.html para decidir si se muestra un link.
// - sincronizarPermisosMenu(): lee topnav.html y actualiza usuarios_permisos para que
//   la lista de permisos disponibles en Usuarios.php siempre refleje el menú real,
//   sin mantenimiento manual.

// Muchas páginas del sistema imprimen HTML antes de incluir topnav.html, así que acá
// los headers ya están enviados. session_start() igual carga bien los datos de la
// sesión existente en ese caso (solo falla el reenvío de la cookie, que no hace falta
// porque el navegador ya la tiene) — silenciamos ese warning puntual con @, pero NO nos
// salteamos el session_start(): si lo hacíamos, $_SESSION quedaba vacío en esas páginas
// y el menú terminaba mostrando todo sin importar el rol de quien esté logueado.
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

function menuSlug(string $seccion, string $texto): string
{
    $base = $seccion . '__' . $texto;
    $acentos = ['á', 'é', 'í', 'ó', 'ú', 'à', 'è', 'ì', 'ò', 'ù', 'ñ', 'ü', 'Á', 'É', 'Í', 'Ó', 'Ú', 'À', 'È', 'Ì', 'Ò', 'Ù', 'Ñ', 'Ü'];
    $sin_acentos = ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'n', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'n', 'u'];
    $base = str_replace($acentos, $sin_acentos, $base);
    $base = strtolower($base);
    $base = preg_replace('/[^a-z0-9]+/', '_', $base);
    return trim($base, '_');
}

// Devuelve ['Seccion' => ['Texto item 1', 'Texto item 2', ...], ...] leyendo el HTML real del menú.
function extraerMenuDesdeArchivo(string $path): array
{
    $html = file_get_contents($path);
    if ($html === false) {
        return [];
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $secciones = [];

    $lis = $xpath->query('//li[contains(concat(" ", normalize-space(@class), " "), " nav-item ")]');
    foreach ($lis as $li) {
        $toggle = $xpath->query('./a[contains(concat(" ", normalize-space(@class), " "), " dropdown-toggle ")]', $li)->item(0);
        if (!$toggle) {
            continue;
        }
        $seccion = trim(preg_replace('/\s+/', ' ', $toggle->textContent));
        if ($seccion === '') {
            continue;
        }

        $items = [];
        $links = $xpath->query('.//div[contains(concat(" ", normalize-space(@class), " "), " dropdown-menu ")]//a[contains(concat(" ", normalize-space(@class), " "), " dropdown-item ")]', $li);
        foreach ($links as $a) {
            $texto = trim(preg_replace('/\s+/', ' ', $a->textContent));
            if ($texto === '') {
                continue; // links sin texto visible (bug preexistente en el menú, no aplica)
            }
            $items[$texto] = true; // dedupe si hay texto repetido en la misma sección
        }
        if (!empty($items)) {
            $secciones[$seccion] = array_keys($items);
        }
    }

    return $secciones;
}

// Actualiza usuarios_permisos para que coincida con el menú real. Se puede llamar
// en cada carga de la pestaña Permisos: es barata (un archivo chico) e idempotente.
function sincronizarPermisosMenu(mysqli $mysqli, string $topnavPath): void
{
    $menu = extraerMenuDesdeArchivo($topnavPath);

    $slugsVigentes = [];
    foreach ($menu as $seccion => $items) {
        foreach ($items as $texto) {
            $slug = menuSlug($seccion, $texto);
            $slugsVigentes[$slug] = ['nombre' => $texto, 'seccion' => $seccion];
        }
    }

    if (empty($slugsVigentes)) {
        return; // no tocar la tabla si no se pudo leer el menú (evita borrar todo por error)
    }

    // Traer lo que ya existe (excluyendo permisos de sistema, esos no vienen del menú)
    $existentes = [];
    $res = $mysqli->query("SELECT id, slug, Eliminado FROM usuarios_permisos WHERE es_sistema = 0");
    while ($row = $res->fetch_assoc()) {
        if ($row['slug'] !== null) {
            $existentes[$row['slug']] = $row;
        }
    }

    // Insertar nuevos / reactivar los que volvieron a aparecer
    foreach ($slugsVigentes as $slug => $info) {
        if (!isset($existentes[$slug])) {
            $nombre = $mysqli->real_escape_string($info['nombre']);
            $seccion = $mysqli->real_escape_string($info['seccion']);
            $slugEsc = $mysqli->real_escape_string($slug);
            $mysqli->query("INSERT INTO usuarios_permisos (nombre, slug, seccion, es_sistema, Eliminado) VALUES ('$nombre', '$slugEsc', '$seccion', 0, 0)");
        } elseif ($existentes[$slug]['Eliminado'] == 1) {
            $id = intval($existentes[$slug]['id']);
            $mysqli->query("UPDATE usuarios_permisos SET Eliminado = 0 WHERE id = $id");
        }
    }

    // Dar de baja (soft delete) los que ya no están en el menú
    foreach ($existentes as $slug => $row) {
        if (!isset($slugsVigentes[$slug]) && $row['Eliminado'] == 0) {
            $id = intval($row['id']);
            $mysqli->query("UPDATE usuarios_permisos SET Eliminado = 1 WHERE id = $id");
        }
    }
}

// Permisos del usuario logueado, leídos en vivo de la base (no de la sesión cacheada).
// Así un cambio de rol se ve al toque, sin pedirle a la persona que cierre sesión y
// vuelva a entrar. Se resuelve una sola vez por request (cache local a la función).
// null = sin rol asignado -> sin restricción (se ve todo el menú).
function obtenerPermisosDelUsuario(): ?array
{
    static $cache = null;
    static $calculado = false;
    if ($calculado) {
        return $cache;
    }
    $calculado = true;

    $idUsuario = intval($_SESSION['idusuario'] ?? 0);
    if ($idUsuario <= 0) {
        return null;
    }

    require __DIR__ . '/../../Conexion/conexion_publica.php';

    $resRol = $mysqli->query("SELECT rol_id FROM usuarios WHERE id = $idUsuario");
    $rolId = $resRol && $resRol->num_rows ? intval($resRol->fetch_assoc()['rol_id'] ?? 0) : 0;

    if ($rolId <= 0) {
        return null; // sin rol asignado -> ve todo
    }

    $res = $mysqli->query("
        SELECT p.slug
        FROM usuarios_rol_permiso rp
        INNER JOIN usuarios_permisos p ON p.id = rp.permiso_id AND p.Eliminado = 0 AND p.slug IS NOT NULL
        WHERE rp.rol_id = $rolId
    ");
    $permisos = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $permisos[] = $row['slug'];
        }
    }

    $cache = $permisos;
    return $cache;
}

// Usado desde topnav.html. Sin rol asignado (usuario nuevo o sin sesión) => se ve todo el menú.
// Quien tiene el permiso reservado "Gestionar Roles y Permisos" ve el menú completo siempre,
// sin necesidad de tildar los ítems uno por uno (si no, el propio SuperAdministrador se queda
// sin menú apenas se le asigna un rol con ese único permiso).
function tieneMenuPermiso(string $seccion, string $texto): bool
{
    $permisos = obtenerPermisosDelUsuario();
    if ($permisos === null) {
        return true;
    }
    if (in_array('__gestionar_roles__', $permisos, true)) {
        return true;
    }
    return in_array(menuSlug($seccion, $texto), $permisos, true);
}

// Doble candado para crear/editar/borrar roles, permisos y qué contiene cada rol:
// Nivel 1 (SuperAdministrador) + tener asignado el permiso reservado "Gestionar Roles y Permisos".
// Se valida siempre contra la base (no contra la sesión cacheada) porque es una acción sensible.
function usuarioPuedeGestionarRoles(mysqli $mysqli): bool
{
    if (intval($_SESSION['Nivel'] ?? 0) !== 1) {
        return false;
    }
    $uid = intval($_SESSION['idusuario'] ?? 0);
    if ($uid <= 0) {
        return false;
    }
    $res = $mysqli->query("
        SELECT rp.id
        FROM usuarios u
        INNER JOIN usuarios_rol_permiso rp ON rp.rol_id = u.rol_id
        INNER JOIN usuarios_permisos p ON p.id = rp.permiso_id AND p.slug = '__gestionar_roles__' AND p.Eliminado = 0
        WHERE u.id = $uid
        LIMIT 1
    ");
    return $res && $res->num_rows > 0;
}
