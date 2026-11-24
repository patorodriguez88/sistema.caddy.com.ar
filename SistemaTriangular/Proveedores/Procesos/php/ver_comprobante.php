<?php
// Abre la foto o PDF de un comprobante de proveedor (FacturasCompra/<id>.<ext>). Antes el enlace iba siempre a .pdf
// y las fotos (.jpg/.png) daban 404. 24/11/2026.
include_once "../../../Conexion/Conexioni.php";
if (!isset($_SESSION['Usuario'])) { http_response_code(403); exit('Sesión cerrada.'); }
$id = (int)($_GET['id'] ?? 0);
$dir = __DIR__ . '/../../../FacturasCompra/';
foreach (['pdf', 'PDF', 'jpg', 'jpeg', 'png', 'webp', 'gif'] as $ext) {
    if ($id > 0 && is_file($dir . $id . '.' . $ext)) {
        header('Location: ../../../FacturasCompra/' . $id . '.' . $ext);
        exit;
    }
}
http_response_code(404);
echo 'No se encontró el archivo del comprobante.';
