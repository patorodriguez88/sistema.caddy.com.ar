<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
// Verificar si hay un usuario en sesión
if (!isset($_SESSION['Usuario'])) {
    http_response_code(403); // Prohibido
    json_encode(array('error'=>"Error: No se ha iniciado sesión.")) ;
    exit;
}

// Obtener el nombre de usuario de la sesión
$user = $_SESSION['Usuario'];

// Verificar si se ha enviado un archivo
if (!isset($_FILES["file"])) {
    http_response_code(400); // Solicitud incorrecta
    json_encode(array("Error: No se ha enviado ningún archivo."));
    exit;
}

// Definir los tipos de archivo permitidos
$allowed_types = array("image/pjpeg", "image/jpeg", "image/png", "image/gif", "application/pdf");

// Verificar si el tipo de archivo es permitido
$file_type = $_FILES["file"]["type"];
if (!in_array($file_type, $allowed_types)) {
    http_response_code(415); // Tipo de medio no compatible
    json_encode(array("Error: Tipo de archivo no permitido. Por favor, sube una imagen (JPEG, PNG, GIF) o un PDF."));
    exit;
}

// Mover el archivo a la ubicación deseada
$N = $_POST['id_comprobante_subir'];

$tipoArchivo = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
$destination = "../../../FacturasCompra/" . $N . '.' . $tipoArchivo;

if (!move_uploaded_file($_FILES["file"]["tmp_name"], $destination)) {
    http_response_code(500); // Error interno del servidor
    json_encode(array("Error: No se pudo subir el archivo. Por favor, inténtalo de nuevo más tarde."));
    exit;

}else{

    $mysqli->query("UPDATE `TransProveedores` SET `img` = '1' WHERE `TransProveedores`.`id` = '$N' LIMIT 1;");
    
    // Éxito en la carga del archivo
    json_encode(array("¡El archivo se ha subido correctamente!"));

}

?>
