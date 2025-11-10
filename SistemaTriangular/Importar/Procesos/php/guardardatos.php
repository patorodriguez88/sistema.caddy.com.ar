<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la conexión a la base de datos
require '../../vendor/autoload.php';
include_once "../../../Conexion/Conexioni.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


if (isset($_FILES['file']) && isset($_POST['mappedData'])) {
    
    // $file = $_FILES['file']; // El archivo Excel
    $file = $_FILES['file']['tmp_name'];

    $mappedData = json_decode($_POST['mappedData'], true); // El mapeo de campos
    $relacion_nc=$_POST['relacion_nc'];
    
    // Verificar que el archivo sea un archivo válido
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Error al cargar el archivo']);
        exit;
    }
    
    // Cargar el archivo Excel usando PhpSpreadsheet
    // try {
    //     $spreadsheet = IOFactory::load($file['tmp_name']);
    // } catch (Exception $e) {
    //     echo json_encode(['success' => false, 'error' => 'Error al leer el archivo Excel: ' . $e->getMessage()]);
    //     exit;
    // }
    // Cargar el archivo Excel
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();

    // Obtener la primera hoja del archivo
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(); // Convertir las filas a un array

    // Iterar sobre cada fila de datos del archivo Excel
    foreach ($rows as $rowIndex => $row) {
        // Inicializar arrays para las columnas y valores
        $columns = [];
        $values = [];

        // Recorrer el mapeo para asignar valores a las columnas
        foreach ($mappedData as $mapping) {
            // Asegurarse de que la columna en el mapeo esté presente en los datos
            $excelValue = isset($row[array_search($mapping['excelTitle'], $row)]) ? $row[array_search($mapping['excelTitle'], $row)] : null;

            // Si el valor está presente, lo añadimos a la lista de columnas y valores
            $columns[] = $mapping['dbField'];
            $values[] = "'" . $mysqli->real_escape_string($excelValue) . "'"; // Sanitizar el valor
            
            if ($mapping['dbField'] == 'idProveedor') {
                $query = "SELECT id FROM Clientes WHERE id='" . $relacion_nc . "' AND idProveedor='" . $excelValue . "'";
                $result = $mysqli->query($query);
                $cliente = $result->fetch_array(MYSQLI_ASSOC);
            
                if ($cliente) { // Verificar si se encontró un resultado
                    $columns[] = 'NCliente';
                    $values[] = "'" . $cliente['id'] . "'";
                }
            }
        }
        $columns[] = 'Cobranza';  // Añadir el campo "Cobrar" a los nombres
        $values[] = 0;          // Añadir el valor predeterminado "0"

        // Generar la consulta SQL para insertar los datos
        $query = "INSERT INTO PreVenta (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ")";

        // Ejecutar la consulta
        if (!$mysqli->query($query)) {
            // Si ocurre un error al insertar, devolver el error
            echo json_encode([
                'success' => false,
                'error' => 'Error al insertar los datos: ' . $mysqli->error
            ]);
            exit;
        }
    }

    // Respuesta exitosa después de insertar todos los datos
    echo json_encode(['success' => true, 'message' => 'Datos insertados correctamente.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Datos no recibidos']);
}
?>