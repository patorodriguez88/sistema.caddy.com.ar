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
// Sin sesión o sin rol asignado = sin permisos (antes era "ve todo el menú": así
// quedaban con el menú completo los usuarios a los que nunca se les asignó rol).
function obtenerPermisosDelUsuario(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];

    $idUsuario = intval($_SESSION['idusuario'] ?? 0);
    if ($idUsuario <= 0) {
        return $cache;
    }

    require __DIR__ . '/../../Conexion/conexion_publica.php';

    $resRol = $mysqli->query("SELECT rol_id FROM usuarios WHERE id = $idUsuario AND ACTIVO = 1");
    $rolId = $resRol && $resRol->num_rows ? intval($resRol->fetch_assoc()['rol_id'] ?? 0) : 0;

    if ($rolId <= 0) {
        return $cache;
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

// Quien tiene el permiso reservado "Gestionar Roles y Permisos" ve el menú completo siempre,
// sin necesidad de tildar los ítems uno por uno (si no, el propio SuperAdministrador se queda
// sin menú apenas se le asigna un rol con ese único permiso).
function veTodoElMenu(): bool
{
    return in_array('__gestionar_roles__', obtenerPermisosDelUsuario(), true);
}

// Usado desde topnav.html. Sin rol asignado (usuario nuevo o sin sesión) => no ve el ítem.
function tieneMenuPermiso(string $seccion, string $texto): bool
{
    return veTodoElMenu() || in_array(menuSlug($seccion, $texto), obtenerPermisosDelUsuario(), true);
}

// Para los subgrupos de una sección (ej. Admin > Contabilidad): se muestran solo si hay
// al menos un ítem habilitado adentro, si no quedaban títulos con el desplegable vacío.
function tieneAlgunoDe(string $seccion, array $textos): bool
{
    foreach ($textos as $texto) {
        if (tieneMenuPermiso($seccion, $texto)) {
            return true;
        }
    }
    return false;
}

// Gate del <li> contenedor de cada seccion del topnav (Home, Admin, Ventas,
// etc). tieneMenuPermiso() solo tapa los links de adentro uno por uno - sin
// esto, un rol sin NINGUN permiso dentro de una seccion igual veia el
// titulo de esa seccion en el menu (con el dropdown vacio al abrirlo), que
// es justo lo que reporto el rol "Operaciones" viendo "Admin" en el menu
// sin tener nada tildado en Asignacion de Roles y Permisos. Todos los
// slugs de una seccion arrancan con menuSlug($seccion, '') + '_' (ver
// menuSlug: $seccion.'__'.$texto colapsa el doble guion bajo a uno solo),
// asi que alcanza con un prefix-match sobre los permisos ya cacheados de
// obtenerPermisosDelUsuario(), sin otra consulta a la base.
function tieneAlgunPermisoSeccion(string $seccion): bool
{
    if (veTodoElMenu()) {
        return true;
    }
    $prefijo = menuSlug($seccion, '') . '_';
    foreach (obtenerPermisosDelUsuario() as $slug) {
        if (str_starts_with($slug, $prefijo)) {
            return true;
        }
    }
    return false;
}

// ---------------------------------------------------------------------------
// Acceso al sistema y a cada pantalla
// ---------------------------------------------------------------------------

// Solo el personal entra al sistema: 1 = administrador, 2 = empleado, 7 = operaciones
// (los mismos que conect.php manda al Panel de Control). Repartidores (3) y clientes
// (4 y 6) nunca: usan la app de reparto y la plataforma de clientes.
const NIVELES_SISTEMA = [1, 2, 7];

function nivelPuedeEntrarAlSistema($nivel): bool
{
    return in_array((int) $nivel, NIVELES_SISTEMA, true);
}

// URL de una pantalla normalizada para comparar: sin dominio, query, ".php" ni barra inicial.
function normalizarPaginaMenu(string $url): string
{
    $path = parse_url($url, PHP_URL_PATH) ?: '';
    $path = preg_replace('/\.php$/i', '', $path);
    return strtolower(trim($path, '/'));
}

// [pantalla normalizada => [slugs de los ítems del menú que la abren]], leído del menú real
// (una misma pantalla puede estar en más de un ítem: alcanza con tener uno).
function mapaPaginasMenu(string $topnavPath): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    $html = file_get_contents($topnavPath);
    if ($html === false) {
        return $cache;
    }
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);

    foreach ($xpath->query('//li[contains(concat(" ", normalize-space(@class), " "), " nav-item ")]') as $li) {
        $toggle = $xpath->query('./a[contains(concat(" ", normalize-space(@class), " "), " dropdown-toggle ")]', $li)->item(0);
        $seccion = $toggle ? trim(preg_replace('/\s+/', ' ', $toggle->textContent)) : '';
        if ($seccion === '') {
            continue;
        }
        $links = $xpath->query('.//div[contains(concat(" ", normalize-space(@class), " "), " dropdown-menu ")]//a[contains(concat(" ", normalize-space(@class), " "), " dropdown-item ")]', $li);
        foreach ($links as $a) {
            $texto = trim(preg_replace('/\s+/', ' ', $a->textContent));
            $href = (string) $a->getAttribute('href');
            if ($texto === '' || stripos($href, '/SistemaTriangular/') === false) {
                continue;
            }
            $cache[normalizarPaginaMenu($href)][] = menuSlug($seccion, $texto);
        }
    }
    return $cache;
}

// ¿La pantalla que se está abriendo está permitida para el rol? Las pantallas que no están
// en el menú (detalles, informes, procesos) no se controlan acá. El Panel de Control es la
// pantalla de llegada después del login: siempre se puede abrir.
function paginaActualPermitida(): bool
{
    $actual = normalizarPaginaMenu((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($actual === 'sistematriangular/inicio/cpanel' || veTodoElMenu()) {
        return true;
    }
    $slugs = mapaPaginasMenu(__DIR__ . '/../topnav.html')[$actual] ?? null;
    if ($slugs === null) {
        return true;
    }
    return (bool) array_intersect($slugs, obtenerPermisosDelUsuario());
}

// Al principio del menú: una sesión que no es de personal (repartidor, cliente o sin login)
// no puede ver ninguna pantalla del sistema. Los headers ya se enviaron (el menú se incluye
// con la página empezada), así que se redirige desde el navegador.
function controlarAccesoAlSistema(): void
{
    if (nivelPuedeEntrarAlSistema($_SESSION['Nivel'] ?? 0) && intval($_SESSION['idusuario'] ?? 0) > 0) {
        return;
    }
    $_SESSION = [];
    @session_destroy();
    echo '<script>window.location.replace("/SistemaTriangular/inicio.php");</script></div></body></html>';
    exit;
}

// Al final del menú: si el rol no tiene la pantalla, en lugar del contenido se muestra el aviso.
function controlarPaginaPermitida(): void
{
    if (paginaActualPermitida()) {
        return;
    }
    $sinRol = empty(obtenerPermisosDelUsuario());
    $mensaje = $sinRol
        ? 'Tu usuario todavía no tiene un rol asignado. Pedile a un administrador que te lo asigne.'
        : 'No tenés permiso para entrar a esta pantalla. Si la necesitás, pedile a un administrador que la agregue a tu rol.';
    echo '<div class="content-page"><div class="content"><div class="container-fluid" style="padding-top:90px">'
        . '<div class="alert alert-warning" role="alert" style="max-width:640px;margin:40px auto;font-size:15px">'
        . '<i class="mdi mdi-lock-outline"></i> ' . htmlspecialchars($mensaje)
        . '<div class="mt-2"><a href="/SistemaTriangular/Inicio/Cpanel.php">Volver al Panel de Control</a></div>'
        . '</div></div></div></div></div>'
        . '<script src="/SistemaTriangular/hyper/dist/assets/js/vendor.min.js"></script>'
        . '<script src="/SistemaTriangular/hyper/dist/assets/js/app.js"></script></body></html>';
    exit;
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
