<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../vendor/autoload.php';
include_once "../../../Conexion/Conexioni.php";

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['file']) && isset($_POST['mappedData'])) {
    $file = $_FILES['file']['tmp_name'];
    $mappedData = json_decode($_POST['mappedData'], true);
    $relacion_nc = $_POST['relacion_nc'] ?? '';
    $Precio=$_POST['tarifa'];

    if (!isset($relacion_nc)) {

        echo json_encode(['success' => false, 'error' => 'Error al cargar el Cliente Origen']);
        
        exit;
    }else{
        $sql_origen="SELECT * FROM Clientes WHERE id='".$relacion_nc."' AND Eliminado=0";
        $result_origen=$mysqli->query($sql_origen);
        $ClienteOrigen=$result_origen->fetch_array(MYSQLI_ASSOC);
        print_r($ClienteOrigen);
        $DireccionOrigen = "'" . $mysqli->real_escape_string($ClienteOrigen['Direccion']) . "'";
    }

    if(!isset($Precio)){
        echo json_encode(['success' => false, 'error' => 'Error al cargar la tarifa']);
        
        exit;
    }

    if (!is_uploaded_file($file)) {
        echo json_encode(['success' => false, 'error' => 'Error al cargar el archivo']);
        exit;
    }

    $spreadsheet = IOFactory::load($file);
    $rows = $spreadsheet->getActiveSheet()->toArray();
    $headers = $rows[0];
    unset($rows[0]);

    foreach ($mappedData as $mapping) {
        if (!in_array($mapping['excelTitle'], $headers)) {
            echo json_encode(['success' => false, 'error' => 'El campo "' . $mapping['excelTitle'] . '" no se encuentra en el archivo Excel']);
            exit;
        }
    }

        

    foreach ($rows as $row) {
        $columns = [];
        $values = [];

    
        // Recorrer el mapeo de datos
        foreach ($mappedData as $mapping) {
            // Obtener el valor desde el Excel
            $excelValue = $row[array_search($mapping['excelTitle'], $headers)];

                // if (!in_array($mapping['dbField'], $existingFields)) {
                    $columns[] = $mapping['dbField'];
                    $values[] = "'" . $mysqli->real_escape_string($excelValue) . "'";
                    $existingFields[] = $mapping['dbField'];
                // }

            // Verificar si el campo es 'idProveedor' para buscar el cliente en la base de datos
            if ($mapping['dbField'] == 'idProveedor') {
                // print_r($excelValue);
                $query = "SELECT id, nombrecliente FROM Clientes WHERE Relacion='$relacion_nc' AND idProveedor='$excelValue'";
                $result = $mysqli->query($query);
                $ClienteDestino = $result->fetch_array(MYSQLI_ASSOC);
                // Verificamos si el cliente fue encontrado en la base de datos
                if ($ClienteDestino) {
                    // Valor a eliminar
                    $target = 'ClienteDestino';
                    

                    

                    // Buscar el índice del valor en $columns
                    $index = array_search($target, $columns);

                    if ($index !== false) {
                        // Eliminar el elemento de $columns
                        unset($columns[$index]);
                        // Eliminar el elemento correspondiente de $values
                        unset($values[$index]);

                        // Reindexar ambos arrays
                        $columns = array_values($columns);
                        $values = array_values($values);
                    }
                    
                        //Agregamos Cliente Destino al array con el valor encontrado en la bd.  
                        $columns[] = 'RazonSocial';
                        $values[] = "'" . $ClienteOrigen['nombrecliente'] . "'";                      
                        $columns[] = 'ClienteDestino';
                        $values[] = "'" . $ClienteDestino['nombrecliente'] . "'";  // Usamos el nombrecliente de la base de datos
                        $columns[] = 'idClienteDestino';
                        $values[] = "'" . $ClienteDestino['id'] . "'";
                        $columns[] = 'Cobranza';
                        $values[] = 0;
                        $columns[]='FormaDePago';
                        $values[] = "'Origen'";
                        $columns[]='EntregaEn';
                        $values[]="'Domicilio'";
                        $columns[]='NCliente';
                        $values[]=$ClienteOrigen['id'];
                        $columns[]='DomicilioOrigen';
                        $values[]=$DireccionOrigen;
                        if(!in_array('Cantidad',$columns)){
                            $columns[]='Cantidad';  
                            $values[]='1';
                        }
                        
                        if(!in_array('Total',$columns)){
                            $columns[]='Total';  
                            $values[]='0';
                        }
                    }
            }
        }
    print_r($columns);
    print_r($values);
        // Agregar el campo 'Cobranza' solo si no está ya en los campos existentes
        // if (!in_array('Cobranza', $existingFields)) {
            // $columns[] = 'Cobranza';
            // $values[] = 0;
        // }
    
        // Generar y ejecutar la consulta INSERT
        $query = "INSERT INTO PreVenta (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
        if (!$mysqli->query($query)) {
            echo json_encode(['success' => false, 'error' => 'Error al insertar los datos: ' . $mysqli->error]);
            exit;
        }
    }
    


    // foreach ($rows as $row) {
    //     $columns = [];
    //     $values = [];
    //     foreach ($mappedData as $mapping) {
    //         $excelValue = $row[array_search($mapping['excelTitle'], $headers)];
    //         $columns[] = $mapping['dbField'];
    //         $values[] = "'" . $mysqli->real_escape_string($excelValue) . "'";

    //         if ($mapping['dbField'] == 'idProveedor') {
    //             $query = "SELECT id,nombrecliente FROM Clientes WHERE Relacion='$relacion_nc' AND idProveedor='$excelValue'";
    //             $result = $mysqli->query($query);
    //             $cliente = $result->fetch_array(MYSQLI_ASSOC);

    //             if ($cliente) {
    //                 $columns[] = 'NCliente';
    //                 $values[] = "'" . $cliente['id'] . "'";
    //                 $columns[]= 'ClienteDestino';
    //                 $values[] = "'" . $cliente['nombrecliente'] . "'";
    //             }
    //         }
    //     }
    //     $columns[] = 'Cobranza';
    //     $values[] = 0;

    //     $query = "INSERT INTO PreVenta (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
    //     if (!$mysqli->query($query)) {
    //         echo json_encode(['success' => false, 'error' => 'Error al insertar los datos: ' . $mysqli->error]);
    //         exit;
    //     }
    // }

    // echo json_encode(['success' => true, 'message' => 'Datos insertados correctamente. relacion '.$cliente['nombrecliente']]);
} else {
    echo json_encode(['success' => false, 'error' => 'Datos no recibidos']);
}
?>