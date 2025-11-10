<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Incluir el autoload de Composer y la conexión a la base de datos
require '../../vendor/autoload.php';
include_once "../../../Conexion/Conexioni.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

try {
    // Validar que el archivo fue subido correctamente
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("No se ha subido un archivo o hubo un error en la carga.");
    }

    $file = $_FILES['file']['tmp_name'];

    // Cargar el archivo Excel
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();

    // Obtener los títulos de las primeras celdas
    $titles = [];
    $highestColumn = $sheet->getHighestColumn();
    $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

    // Suponemos que los títulos están en la primera fila
    for ($col = 1; $col <= $highestColumnIndex; $col++) {
        // $titles[] = $sheet->getCellByColumnAndRow($col, 1)->getValue();
        $titles[] = $sheet->getCell(chr(64 + $col) . '1')->getValue();
    }

    // Validar conexión a la base de datos
    if ($mysqli->connect_error) {
        throw new Exception("Error de conexión a la base de datos: " . $mysqli->connect_error);
    }

    // Obtener los campos de la tabla PreVenta
    $stmt = $mysqli->query("DESCRIBE PreVenta");
    if (!$stmt) {
        throw new Exception("Error al obtener campos de la tabla PreVenta: " . $mysqli->error);
    }
    
    $fields = [];
    while ($row = $stmt->fetch_assoc()) {
        if($row['Field']!='RazonSocial' && $row['Field'] !== 'id' && $row['Field'] !== 'NCliente'){
        $fields[] = $row['Field'];
        }
    }

    // Validar que los datos no estén vacíos
    if (empty($titles) || empty($fields)) {
        throw new Exception("No se encontraron títulos en el archivo Excel o campos en la base de datos.");
    }

    // Responder con los datos
    echo json_encode([
        'success' => true,
        'titles' => $titles,
        'dbFields' => $fields
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}




?>