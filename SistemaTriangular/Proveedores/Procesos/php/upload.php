<?php
// Foto o PDF del comprobante de un proveedor (fila de Proveedores o al ingresar el comprobante). Se guarda como
// FacturasCompra/<id de TransProveedores>.<ext> y marca TransProveedores.img = 1. Responde JSON con mensaje: Dropzone
// lo muestra si falla. Fix 24/11/2026: la pantalla no cargaba Dropzone (no se subía nada desde febrero), el id iba
// sin validar a la consulta y las respuestas no se imprimían.
include_once "../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

function responder(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['Usuario'])) responder(403, ['success' => 0, 'error' => 'La sesión se cerró. Volvé a ingresar.']);

$id = (int)($_POST['id_comprobante_subir'] ?? 0);
if ($id <= 0) responder(400, ['success' => 0, 'error' => 'Falta el comprobante al que corresponde la imagen.']);

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $err = $_FILES['file']['error'] ?? -1;
    $msg = in_array($err, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
        ? 'El archivo es demasiado grande (máximo ' . ini_get('upload_max_filesize') . ').'
        : 'No llegó el archivo. Probá de nuevo.';
    responder(400, ['success' => 0, 'error' => $msg]);
}

$st = $mysqli->prepare("SELECT id FROM TransProveedores WHERE id = ? LIMIT 1");
$st->bind_param('i', $id);
$st->execute();
$existe = $st->get_result()->fetch_row();
$st->close();
if (!$existe) responder(404, ['success' => 0, 'error' => 'No se encontró el comprobante.']);

// Tipo real del archivo (no el que dice el navegador)
$tipos = ['image/jpeg' => 'jpg', 'image/pjpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['file']['tmp_name']);
if (!isset($tipos[$mime])) {
    responder(415, ['success' => 0, 'error' => 'Tipo de archivo no permitido: subí una foto (JPG, PNG, WEBP) o un PDF.']);
}

$dir = __DIR__ . '/../../../FacturasCompra/';
if (!is_dir($dir)) @mkdir($dir, 0755, true);
// Si ya tenía uno (otra extensión), se reemplaza
foreach (glob($dir . $id . '.*') ?: [] as $viejo) @unlink($viejo);
$destino = $dir . $id . '.' . $tipos[$mime];

if (!move_uploaded_file($_FILES['file']['tmp_name'], $destino)) {
    responder(500, ['success' => 0, 'error' => 'No se pudo guardar el archivo en el servidor.']);
}

$st = $mysqli->prepare("UPDATE TransProveedores SET img = 1 WHERE id = ? LIMIT 1");
$st->bind_param('i', $id);
$st->execute();
$st->close();

responder(200, ['success' => 1, 'archivo' => $id . '.' . $tipos[$mime]]);
