<?php

include_once "../../../Conexion/Conexioni.php";
// include_once "../../../Funciones/php/cargar_asiento.php";
include_once "../../../Empleados/Procesos/php/asana_api.php";


$Usuario = $_SESSION['Usuario'];

date_default_timezone_set('America/Argentina/Cordoba');

// Función para convertir el tamaño a unidades legibles
function formatSizeUnits($bytes)
{
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } else {
        $bytes = $bytes . ' bytes';
    }

    return $bytes;
}

// BUSCAR TODA LA FLOTA

if (isset($_POST['Flota'])) {

    $sql = "SELECT concat_ws(' ', Marca, Modelo) as Marca,Dominio,Ano,Kilometros,Activo,Estado FROM Vehiculos WHERE VehiculoOperativo=1 AND Aliados=0";

    $Resultado = $mysqli->query($sql);

    $rows = array();

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}

//BUSCAR VEHICULO

if (isset($_POST['Search_vehicle'])) {

    $sql = "SELECT * FROM Vehiculos WHERE Dominio='" . $_POST['Patent'] . "'";

    $Resultado = $mysqli->query($sql);

    $rows = array();

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}
//BUSCAR ULTIMO MES

if (isset($_POST['Buscar_Mes'])) {

    $sql = "SELECT MAX(Mes)AS Mes,MAX(Anio)AS Anio FROM Impuestos WHERE Dominio='" . $_POST['Dominio'] . "' AND Impuesto='" . $_POST['Impuesto'] . "'";

    $Resultado = $mysqli->query($sql);

    $row = $Resultado->fetch_array(MYSQLI_ASSOC);

    $mes = $row['Mes'];
    $ano = $row['Anio'];
    $anio = $ano;

    if ($mes == 12) {
        $mes = 1;
    } else {
        $mes = $mes + 1;
    }

    echo json_encode(array('success' => 1, 'mes' => $mes, 'anio' => $anio));
}
//SERVICE

if (isset($_POST['Service'])) {

    $sql = "SELECT * FROM ServiceVehiculos WHERE Dominio='" . $_POST['Patent'] . "' ORDER BY FechaServiceRealizado DESC ";

    $Resultado = $mysqli->query($sql);

    $rows = array();

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}

//BREAFING

if (isset($_POST['Breafing'])) {
    $dominio = isset($_POST['Patent']) ? $mysqli->real_escape_string($_POST['Patent']) : '';

    // 1. Consulta LOGISTICA + VEHICULO
    $sql_log = "SELECT * FROM Logistica WHERE Patente = '$dominio' AND Eliminado = 0 ORDER BY id DESC LIMIT 1";

    $result_log = $mysqli->query($sql_log);
    $logistica = array();

    while ($row = $result_log->fetch_array(MYSQLI_ASSOC)) {
        $logistica[] = $row;
    }

    // 2. Consulta MANTENIMIENTO
    $sql_mant = "SELECT * FROM Mantenimiento 
                 WHERE Dominio = '$dominio' AND Eliminado = 0 
                 ORDER BY Fecha DESC";

    $result_mant = $mysqli->query($sql_mant);
    $mantenimiento = array();

    while ($row = $result_mant->fetch_array(MYSQLI_ASSOC)) {
        $mantenimiento[] = $row;
    }

    // 3. RESPUESTA
    echo json_encode([
        'logistica' => $logistica,
        'mantenimiento' => $mantenimiento
    ]);
}
// if (isset($_POST['Breafing'])) {
//     $sql = "SELECT NumerodeOrden,NombreChofer,Fecha,Recorrido,v.* FROM Logistica as l LEFT JOIN Vehiculos as v ON l.Patente=v.Dominio WHERE Patente='$dominio' AND Eliminado=0";
//     $result = $mysqli->query($sql);


//     $sql = "SELECT * FROM `Mantenimiento` WHERE Dominio='" . $_POST['Patent'] . "' AND Eliminado=0 ORDER BY Fecha DESC";

//     $Resultado = $mysqli->query($sql);

//     $rows = array();

//     while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

//         $rows[] = $row;
//     }

//     echo json_encode(array('data' => $rows));
// }

//MULTAS

if (isset($_POST['Fines'])) {

    $sql = "SELECT * FROM Multas WHERE Patente='" . $_POST['Patent'] . "' ORDER BY Fecha ASC ";

    $Resultado = $mysqli->query($sql);

    $rows = array();

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}
//IMPUESTOS
if (isset($_POST['Tax_data'])) {

    $sql = "SELECT * FROM Impuestos WHERE id='" . $_POST['id_tax'] . "'";

    $Resultado = $mysqli->query($sql);

    $rows = array();

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}

//MODIFICAR IMPUESTOS
if (isset($_POST['Tax_modify'])) {

    if ($_POST['Id']) {

        $Referencia = $_POST['Referencia'];
        $Recargo = $_POST['Recargo'];
        $Mes = $_POST['Mes'];
        $Anio = $_POST['Anio'];
        $Vencimiento = $_POST['Vencimiento'];
        $Importe = $_POST['Importe'];
        $Descuento = $_POST['Descuento'];
        $Multa = $_POST['Multa'];
        $Importe_total_base = $Importe + $Multa + $Recargo - $Descuento;
        $Importe_total = number_format($Importe_total_base, 2); // Formatear el número con 2 decimales
        $NAsiento = $_POST['NAsiento'];
        $Impuesto = $_POST['Cuenta'];
        $Observaciones = $_POST['Observaciones'];
        $Cuenta = $_POST['Cuenta'];
        $NCuenta = $_POST['NCuenta'];
        $Nominal = $Importe - $Descuento;

        $stmt = $mysqli->prepare("UPDATE Impuestos SET Referencia=?,Recargo=?,Mes=?,Anio=?,Vencimiento=?,Importe=?, Total=?, Descuento=?,Multa=? WHERE id=? LIMIT 1");

        $stmt->bind_param("sdsssddddi", $Referencia, $Recargo, $Mes, $Anio, $Vencimiento, $Importe, $Importe_total_base, $Descuento, $Multa, $_POST['Id']);

        if ($stmt->execute()) {

            echo json_encode(array('success' => 1));

            $abm = 'Modificado por ' . $Usuario . ' el ' . date('Y-m-d H:i');

            //CARGAR ASIENTO DE DEVENGAMIENTO

            $stmt->close();

            //CARGA EN DEBE

            // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
            $sql_debe = "UPDATE `Tesoreria` SET Debe=?,Observaciones=?,Usuario=?,InfoABM=? WHERE NumeroAsiento= ? AND Cuenta=? LIMIT 1";

            // Preparar la consulta SQL
            if ($stmt_debe = $mysqli->prepare($sql_debe)) {
                // Vincular los parámetros a la consulta SQL
                $stmt_debe->bind_param("dsssss", $Nominal, $Observaciones, $Usuario, $abm, $NAsiento, $NCuenta);

                // Ejecutar la consulta SQL
                if (!$stmt_debe->execute()) {

                    echo "Falló la ejecución de la consulta: (" . $stmt->errno . ") " . $stmt->error;
                } else {

                    $idTeso = $mysqli->insert_id;
                }

                // // Cerrar la consulta preparada
                $stmt_debe->close();
            } else {

                echo "234 Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
            }

            //CARGA EN MULTA
            if ($Multa > 0) {
                //DATOS PARA MULTAS
                $NombreCuenta = 'MULTAS IMPOSITIVAS';
                $NumeroCuenta = '000423800';
                $abm = 'Creado por ' . $Usuario . ' el ' . date('Y-m-d H:i');

                // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
                $sql_multa = "UPDATE `Tesoreria` SET Debe=?,Observaciones=?,Usuario=?,InfoABM=? WHERE NumeroAsiento= ? AND Cuenta=? LIMIT 1";

                // Preparar la consulta SQL
                if ($stmt_multa = $mysqli->prepare($sql_multa)) {
                    // Vincular los parámetros a la consulta SQL
                    $stmt_multa->bind_param("dsssss", $Multa, $Observaciones, $Usuario, $abm, $NAsiento, $NumeroCuenta);

                    // Ejecutar la consulta SQL
                    if (!$stmt_multa->execute()) {
                        echo "Falló la ejecución de la consulta: (" . $stmt_multa->errno . ") " . $stmt_multa->error;
                    }

                    // Cerrar la consulta preparada
                    $stmt_multa->close();
                } else {
                    echo "261.Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
                }
            }

            //CARGA EN RECARGO

            if ($Recargo > 0) {
                //DATOS PARA RECARGO
                $NombreCuenta = 'INTERESES PERDIDOS ';
                $NumeroCuenta = '000213100';
                $abm = 'Creado por ' . $Usuario . ' el ' . date('Y-m-d H:i');

                // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
                $sql_recargo = "UPDATE `Tesoreria` SET Debe=?,Observaciones=?,Usuario=?,InfoABM=? WHERE NumeroAsiento= ? AND Cuenta=? LIMIT 1";

                // Preparar la consulta SQL
                if ($stmt_recargo = $mysqli->prepare($sql_recargo)) {
                    // Vincular los parámetros a la consulta SQL
                    $stmt_recargo->bind_param("dsssii", $Recargo, $Observaciones, $Usuario, $abm, $NAsiento, $NumeroCuenta);

                    // Ejecutar la consulta SQL
                    if (!$stmt_recargo->execute()) {
                        echo "Falló la ejecución de la consulta: (" . $stmt_recargo->errno . ") " . $stmt_recargo->error;
                    }

                    // Cerrar la consulta preparada
                    $stmt_recargo->close();
                } else {
                    echo "290.Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
                }
            }


            //CARGAR EN HABER
            //DATOS PARA DEVENGAMIENTO
            $NombreCuenta = 'IMPUESTOS Y TASAS A PAGAR	';
            $NumeroCuenta = '000420300';

            // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
            $sql_haber = "UPDATE `Tesoreria` SET Haber=?,Observaciones=?,Usuario=?,InfoABM=? WHERE NumeroAsiento= ? AND Cuenta=? LIMIT 1";

            // Preparar la consulta SQL
            if ($stmt_haber = $mysqli->prepare($sql_haber)) {
                // Vincular los parámetros a la consulta SQL
                $stmt_haber->bind_param("dsssii", $Importe_total_base, $Observaciones, $Usuario, $abm, $NAsiento, $NumeroCuenta);

                // Ejecutar la consulta SQL
                if (!$stmt_haber->execute()) {

                    echo "Falló la ejecución de la consulta: (" . $stmt->errno . ") " . $stmt->error;
                }

                // Cerrar la consulta preparada
                $stmt_haber->close();
            } else {

                echo "325.Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
            }
        } else {

            echo json_encode(array('success' => 0, 'error' => $mysqli->error, 'error stmt' => $stmt->error));
        }
    } else {

        echo json_encode(array('success' => 0, 'error' => 'No hay id'));
    }
}


//IMPUESTOS
if (isset($_POST['Tax'])) {

    $sql = "SELECT * FROM Impuestos WHERE Dominio='" . $_POST['Patent'] . "' AND Eliminado=0 ORDER BY Fecha ASC ";

    $Resultado = $mysqli->query($sql);

    $rows = array();

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}

//AGREGAR IMPUESTOS

if (isset($_POST['Tax_insert'])) {

    // Obtener los datos de la solicitud POST
    $Fecha = $_POST['Fecha'];
    $Dominio = $_POST['Dominio'];
    $Impuesto = $_POST['Cuenta'];
    $Mes = $_POST['Mes'];
    $Anio = $_POST['Anio'];
    $Vencimiento = $_POST['Vencimiento'];
    $Importe = $_POST['Importe'];
    $Pagado = $_POST['Pagado']; // Corrección: $_POST en mayúsculas
    $idTeso = $_POST['idTeso'];
    $Observaciones = $_POST['Observaciones'];
    $Cuenta = $_POST['Cuenta'];
    $NCuenta = $_POST['NCuenta'];
    $Multa = $_POST['Multa'];
    $Recargo = $_POST['Recargo'];
    $Descuento = $_POST['Descuento'];
    $Referencia = $_POST['Referencia'];

    $Importe_total = $Importe + $Multa + $Recargo - $Descuento;
    $Nominal = $Importe - $Descuento;

    //DATOS PARA DEVENGAMIENTO
    $abm = 'Creado por ' . $Usuario . ' el ' . date('Y-m-d H:i');

    //CARGAR ASIENTO DE DEVENGAMIENTO
    $sql_nasiento = $mysqli->query("SELECT MAX(NumeroAsiento)as NAsiento FROM Tesoreria WHERE Eliminado=0");
    $row_nasiento = $sql_nasiento->fetch_array(MYSQLI_ASSOC);
    $NAsiento = $row_nasiento['NAsiento'] + 1;

    //CARGA EN DEBE

    // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
    $sql = "INSERT INTO `Tesoreria`(`Fecha`, `NombreCuenta`, `Cuenta`, `Debe`, `Observaciones`, `Usuario`,`NumeroAsiento`,`Dominio`,`InfoABM`) 
    VALUES (?,?,?,?,?,?,?,?,?)";

    // Preparar la consulta SQL
    if ($stmt = $mysqli->prepare($sql)) {
        // Vincular los parámetros a la consulta SQL
        $stmt->bind_param("ssiississ", $Fecha, $Cuenta, $NCuenta, $Nominal, $Observaciones, $Usuario, $NAsiento, $Dominio, $abm);

        // Ejecutar la consulta SQL
        if (!$stmt->execute()) {
            echo "Falló la ejecución de la consulta: (" . $stmt->errno . ") " . $stmt->error;
        } else {
            $idTeso = $mysqli->insert_id;
        }

        // Cerrar la consulta preparada
        $stmt->close();
    } else {
        echo "241 Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    //CARGA EN MULTA
    if ($Multa > 0) {
        //DATOS PARA MULTAS
        $NombreCuenta = 'MULTAS IMPOSITIVAS';
        $NumeroCuenta = '423800';
        $abm = 'Creado por ' . $Usuario . ' el ' . date('Y-m-d H:i');

        // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
        $sql_multa = "INSERT INTO `Tesoreria`(`Fecha`, `NombreCuenta`, `Cuenta`, `Debe`, `Observaciones`, `Usuario`,`NumeroAsiento`,`Dominio`,`InfoABM`) 
    VALUES (?,?,?,?,?,?,?,?,?)";

        // Preparar la consulta SQL
        if ($stmt_multa = $mysqli->prepare($sql_multa)) {
            // Vincular los parámetros a la consulta SQL
            $stmt_multa->bind_param("ssiississ", $Fecha, $NombreCuenta, $NumeroCuenta, $Multa, $Observaciones, $Usuario, $NAsiento, $Dominio, $abm);

            // Ejecutar la consulta SQL
            if (!$stmt_multa->execute()) {
                echo "Falló la ejecución de la consulta: (" . $stmt_multa->errno . ") " . $stmt_multa->error;
            }

            // Cerrar la consulta preparada
            $stmt_multa->close();
        } else {
            echo "269.Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
        }
    }

    //CARGA EN RECARGO

    if ($Recargo > 0) {
        //DATOS PARA RECARGO
        $NombreCuenta = 'INTERESES PERDIDOS ';
        $NumeroCuenta = '213100';
        $abm = 'Creado por ' . $Usuario . ' el ' . date('Y-m-d H:i');

        // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
        $sql_recargo = "INSERT INTO `Tesoreria`(`Fecha`, `NombreCuenta`, `Cuenta`, `Debe`, `Observaciones`, `Usuario`,`NumeroAsiento`,`Dominio`,`InfoABM`) 
        VALUES (?,?,?,?,?,?,?,?,?)";

        // Preparar la consulta SQL
        if ($stmt_recargo = $mysqli->prepare($sql_recargo)) {
            // Vincular los parámetros a la consulta SQL
            $stmt_recargo->bind_param("ssisssiss", $Fecha, $NombreCuenta, $NumeroCuenta, $Recargo, $Observaciones, $Usuario, $NAsiento, $Dominio, $abm);

            // Ejecutar la consulta SQL
            if (!$stmt_recargo->execute()) {
                echo "Falló la ejecución de la consulta: (" . $stmt_recargo->errno . ") " . $stmt_recargo->error;
            }

            // Cerrar la consulta preparada
            $stmt_recargo->close();
        } else {
            echo "299.Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
        }
    }

    //CARGAR EN HABER
    //DATOS PARA DEVENGAMIENTO
    $NombreCuenta = 'IMPUESTOS Y TASAS A PAGAR	';
    $NumeroCuenta = '420300';

    // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
    $sql = "INSERT INTO `Tesoreria`(`Fecha`, `NombreCuenta`, `Cuenta`, `Haber`, `Observaciones`, `Usuario`,`NumeroAsiento`,`Dominio`,
    `InfoABM`) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Preparar la consulta SQL
    if ($stmt = $mysqli->prepare($sql)) {
        // Vincular los parámetros a la consulta SQL
        $stmt->bind_param("ssidssiss", $Fecha, $NombreCuenta, $NumeroCuenta, $Importe_total, $Observaciones, $Usuario, $NAsiento, $Dominio, $abm);

        // Ejecutar la consulta SQL
        if (!$stmt->execute()) {
            echo "Falló la ejecución de la consulta: (" . $stmt->errno . ") " . $stmt->error;
        }

        // Cerrar la consulta preparada
        $stmt->close();
    } else {
        echo "325.Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
    }


    //CARGAR EN IMPUESTOS
    // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
    $sql = "INSERT INTO `Impuestos`(`Fecha`, `Dominio`, `Impuesto`,`Cuenta`,`Descuento`,`Recargo`,`Multa`,`Total`, `Mes`, `Anio`, `Vencimiento`, `Importe`, `Pagado`, `NumeroAsiento`,`Referencia`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Preparar la consulta SQL
    if ($stmt = $mysqli->prepare($sql)) {
        // Vincular los parámetros a la consulta SQL
        $stmt->bind_param("ssssisssiisiiis", $Fecha, $Dominio, $Impuesto, $NCuenta, $Descuento, $Recargo, $Multa, $Importe_total, $Mes, $Anio, $Vencimiento, $Importe, $Pagado, $NAsiento, $Referencia);

        // Ejecutar la consulta SQL
        if (!$stmt->execute()) {

            echo "Falló la ejecución de la consulta: (" . $stmt->errno . ") " . $stmt->error;
        }

        // Cerrar la consulta preparada
        $stmt->close();
    } else {

        echo "349.Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    // Devolver una respuesta JSON (si es necesario)
    echo json_encode(array('success' => true)); // Cambiar esto según tus necesidades
}

//ELIMINAR TAX

if (isset($_POST['Tax_delete'])) {

    $id = $_POST['id'];

    $NombreCuenta = 'IMPUESTOS Y TASAS A PAGAR	';
    $NumeroCuenta = '000420300';
    $abm = 'Creado por ' . $Usuario . ' el ' . date('Y-m-d H:i');

    $sql = $mysqli->query("SELECT * FROM `Impuestos` WHERE id= '" . $id . "' ");
    $row = $sql->fetch_array();

    $Fecha = $row['Fecha'];
    $Dominio = $row['Dominio'];
    $NAsiento = $row['NumeroAsiento'];
    $Cuenta = $row['Impuesto'];
    $NCuenta = $row['Cuenta'];
    $NumeroAsiento = $row['NumeroAsiento'];

    $Importe = $row['Importe'];
    $Multa = $row['Multa'];
    $Recargo = $row['Recargo'];
    $Descuento = $row['Descuento'];
    $Importe_total = $Importe + $Multa + $Recargo - $Descuento;

    $Nominal = $Importe - $Descuento;

    $Observaciones = 'Reversion de Asiento por eliminacion: ';

    // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
    $sql = "INSERT INTO `Tesoreria`(`Fecha`, `NombreCuenta`, `Cuenta`, `Debe`, `Observaciones`, `Usuario`,`NumeroAsiento`,`Dominio`,`InfoABM`) 
    VALUES (?,?,?,?,?,?,?,?,?)";

    // Preparar la consulta SQL
    if ($stmt = $mysqli->prepare($sql)) {
        // Vincular los parámetros a la consulta SQL
        $stmt->bind_param("ssiississ", $Fecha, $NombreCuenta, $NumeroCuenta, $Importe_total, $Observaciones, $Usuario, $NAsiento, $Dominio, $abm);

        // Ejecutar la consulta SQL
        if (!$stmt->execute()) {
            echo "Falló la ejecución de la consulta: (" . $stmt->errno . ") " . $stmt->error;
        } else {
            $idTeso = $mysqli->insert_id;
        }

        // // Cerrar la consulta preparada
        $stmt->close();
    } else {
        echo "Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    //CARGA EN MULTA
    if ($Multa <> 0) {
        //DATOS PARA MULTAS
        $NombreCuenta = 'MULTAS IMPOSITIVAS';
        $NumeroCuenta = '423800';
        $abm = 'Creado por ' . $Usuario . ' el ' . date('Y-m-d H:i');

        // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
        $sql_multa = "INSERT INTO `Tesoreria`(`Fecha`, `NombreCuenta`, `Cuenta`, `Haber`, `Observaciones`, `Usuario`,`NumeroAsiento`,`Dominio`,`InfoABM`) 
        VALUES (?,?,?,?,?,?,?,?,?)";

        // Preparar la consulta SQL
        if ($stmt_multa = $mysqli->prepare($sql_multa)) {
            // Vincular los parámetros a la consulta SQL
            $stmt_multa->bind_param("ssiississ", $Fecha, $NombreCuenta, $NumeroCuenta, $Multa, $Observaciones, $Usuario, $NAsiento, $Dominio, $abm);

            // Ejecutar la consulta SQL
            if (!$stmt_multa->execute()) {
                echo "Falló la ejecución de la consulta: (" . $stmt_multa->errno . ") " . $stmt_multa->error;
            }

            // Cerrar la consulta preparada
            $stmt_multa->close();
        } else {
            echo "Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
        }
    }

    //CARGA EN RECARGO

    if ($Recargo <> 0) {
        //DATOS PARA RECARGO
        $NombreCuenta = 'INTERESES PERDIDOS ';
        $NumeroCuenta = '213100';
        $abm = 'Creado por ' . $Usuario . ' el ' . date('Y-m-d H:i');

        // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
        $sql_recargo = "INSERT INTO `Tesoreria`(`Fecha`, `NombreCuenta`, `Cuenta`, `Haber`, `Observaciones`, `Usuario`,`NumeroAsiento`,`Dominio`,`InfoABM`) 
        VALUES (?,?,?,?,?,?,?,?,?)";

        // Preparar la consulta SQL
        if ($stmt_recargo = $mysqli->prepare($sql_recargo)) {
            // Vincular los parámetros a la consulta SQL
            $stmt_recargo->bind_param("ssiississ", $Fecha, $NombreCuenta, $NumeroCuenta, $Recargo, $Observaciones, $Usuario, $NAsiento, $Dominio, $abm);

            // Ejecutar la consulta SQL
            if (!$stmt_recargo->execute()) {
                echo "Falló la ejecución de la consulta: (" . $stmt_recargo->errno . ") " . $stmt_recargo->error;
            }

            // Cerrar la consulta preparada
            $stmt_recargo->close();
        } else {
            echo "Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
        }
    }



    //CARGAR EN HABER EL IMPUESTO DE VEHICULOS

    // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
    $sql = "INSERT INTO `Tesoreria`(`Fecha`, `NombreCuenta`, `Cuenta`, `Haber`, `Observaciones`, `Usuario`,`NumeroAsiento`,`Dominio`,
    `InfoABM`) VALUES (?,?,?,?,?,?,?,?,?)";

    // Preparar la consulta SQL
    if ($stmt = $mysqli->prepare($sql)) {
        // Vincular los parámetros a la consulta SQL
        $stmt->bind_param("ssiississ", $Fecha, $Cuenta, $NCuenta, $Importe, $Observaciones, $Usuario, $NAsiento, $Dominio, $abm);

        // Ejecutar la consulta SQL
        if (!$stmt->execute()) {
            echo "Falló la ejecución de la consulta: (" . $stmt->errno . ") " . $stmt->error;
        } else {
            $idTeso = $mysqli->insert_id;
        }

        // Cerrar la consulta preparada
        $stmt->close();
    } else {
        echo "Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    //ELIMINAR DE IMPUESTOS
    // Preparar la consulta SQL para insertar datos en la tabla 'Impuestos'
    $sql = "UPDATE `Impuestos` SET Eliminado = 1 WHERE id= ? LIMIT 1";
    // Preparar la consulta SQL
    if ($stmt = $mysqli->prepare($sql)) {
        // Vincular los parámetros a la consulta SQL
        $stmt->bind_param("i", $id);

        // Ejecutar la consulta SQL
        if (!$stmt->execute()) {
            echo "Falló la ejecución de la consulta: (" . $stmt->errno . ") " . $stmt->error;
        }
        // Cerrar la consulta preparada
        $stmt->close();
    } else {
        echo "Falló la preparación de la consulta: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    // Devolver una respuesta JSON (si es necesario)
    echo json_encode(array('success' => true)); // Cambiar esto según tus necesidades
}


//TAMAÑO TITULO

if (isset($_POST['Qualification'])) {

    $targetDir = "../../Titulos/" . $_POST['Patent'] . ".pdf";

    $nombreArchivo = $targetDir;

    if (file_exists($nombreArchivo)) {

        $tamañoArchivo = filesize($nombreArchivo);

        // Convierte el tamaño a unidades más legibles, como KB, MB, etc., si es necesario
        $tamañoLegible = formatSizeUnits($tamañoArchivo);

        echo json_encode(array('data' => $tamañoLegible));
    } else {

        echo json_encode(array('data' => 0));
    }
}

//TAMAÑO SEGURO

if (isset($_POST['Sure'])) {

    $targetDir = "../../Polizas/" . $_POST['Patent'] . ".pdf";

    $nombreArchivo = $targetDir;

    if (file_exists($nombreArchivo)) {

        $tamañoArchivo = filesize($nombreArchivo);

        // Convierte el tamaño a unidades más legibles, como KB, MB, etc., si es necesario
        $tamañoLegible = formatSizeUnits($tamañoArchivo);

        echo json_encode(array('data' => $tamañoLegible));
    } else {

        echo json_encode(array('data' => 0));
    }
}

if ($_POST['accion'] === 'obtener_datos_seguro') {
    $dominio = $_POST['dominio'];
    $sql = "SELECT FechaVencSeguro, NumeroPoliza, TelefonoSeguro FROM Vehiculos WHERE Dominio = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $dominio);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    echo json_encode($result);
    exit;
}

if (isset($_POST['Mantenimiento'])) {

    // Obtener el dominio del vehículo del POST
    $dominio = $_POST['dominio'];

    // Consulta SQL para obtener las órdenes de la base de datos
    $sql = "SELECT NumerodeOrden,NombreChofer,Fecha,Recorrido,CONCAT_WS(' ', Observaciones, ObservacionesCierre) AS ObservacionesConcatenadas FROM Logistica WHERE Patente='$dominio' AND Eliminado=0";
    $result = $mysqli->query($sql);

    // Comprobar si la consulta tuvo éxito
    if ($result) {
        // Crear un array para almacenar las órdenes
        $ordenes = array();

        // Obtener las órdenes de los resultados y almacenarlas en el array
        while ($row = $result->fetch_assoc()) {
            $ordenes[] = $row;
        }

        // Devolver las órdenes como JSON
        echo json_encode($ordenes);
    } else {
        // Si hay un error en la consulta, devolver un mensaje de error
        echo json_encode(array('error' => 'Hubo un error al obtener las órdenes.'));
    }

    // Cerrar la conexión a la base de datos
    $mysqli->close();
}

if (isset($_POST['Agregar_Mantenimiento'])) {

    // Establecer los valores recibidos por POST
    $dominio = $_POST['dominio'];
    $fecha = $_POST['fecha'];
    $titulo = $_POST['titulo'];
    $nota = $_POST['nota'];
    $estado = $_POST['estado'];
    $prioridad = $_POST['prioridad'];
    $orden = $_POST['orden'];

    $assignee = '1207004401160014';
    $workspace = '734348733635084';

    $due_on = date('Y-m-d', strtotime($fecha));
    $clean_notes = preg_replace('/\s+/', ' ', $nota);

    $sql_projects = $mysqli->query("SELECT Dominio,gid_projets_asana FROM `Vehiculos` WHERE Dominio='$dominio'");
    $row_projects = $sql_projects->fetch_array(MYSQLI_ASSOC);
    $projects = $row_projects['gid_projets_asana'];

    $gid_asana = Create_task($projects, $titulo, $clean_notes, $due_on, $assignee, $workspace);

    if ($gid_asana) {
        // Preparar la sentencia SQL utilizando una sentencia preparada
        $stmt = $mysqli->prepare("INSERT INTO Mantenimiento (Fecha, Dominio, Titulo, Notas, Usuario, Prioridad, Estado, Orden, gid_task) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssii", $fecha, $dominio, $titulo, $nota, $Usuario, $prioridad, $estado, $orden, $gid_asana);

        // Ejecutar la sentencia
        if ($stmt->execute()) {
            // $lastid = $mysqli->insert_id;

            // $sql_projects=$mysqli->query("SELECT Dominio,gid_projets_asana FROM `Vehiculos` WHERE Dominio='$dominio'");
            // $row_projects=$sql_projects->fetch_array(MYSQLI_ASSOC);
            // $projects=$row_projects['gid_projets_asana'];

            // $assignee='1207004401160014';
            // $workspace='734348733635084';

            // $due_on = date('Y-m-d', strtotime($fecha));
            // $clean_notes = preg_replace('/\s+/', ' ', $nota);

            // $gid_asana = Create_task($projects,$titulo,$clean_notes,$due_on,$assignee,$workspace);    

            // if($gid_asana){

            // $mysqli->query("UPDATE Mantenimiento SET gid_task='$gid_asana' WHERE id='$lastid'");
            // Devolver un mensaje de éxito en formato JSON
            echo json_encode(array('success' => 1, 'gid_asana' => $gid_asana, 'id' => $lastid, 'projects' => $projects));
        } else {

            echo json_encode(array("success" => 0, "error" => 'No cargo la tarea en asana', "nota" => $clean_notes));
        }
    } else {
        // En caso de error, devolver un mensaje de error en formato JSON
        echo json_encode(array('success' => 0, 'error' => $stmt->error));
    }
}

// if(isset($_POST['Mantenimiento_asana'])){

//     $gid=$_POST['gid_task'];

//     $result=Get_a_Task($gid);

//     echo json_encode($result);

// }

if (isset($_POST['Service_new'])) {

    $dominio = $_POST['dominio'];
    $fecha = $_POST['fecha'];
    $km = $_POST['km'];
    $place = $_POST['place'];
    $costo = $_POST['costo'];
    $obs = $_POST['obs'];

    // Calcular la cantidad de servicios completos hasta el momento
    $serviciosCompletos = floor($km / 10000);

    // Calcular el próximo kilometraje de servicio
    $proximoService = ($serviciosCompletos + 1) * 10000;

    $Estado = 'Abierto';

    $stmt = $mysqli->prepare("INSERT INTO ServiceVehiculos(Dominio,kmReales,ServiceRealizado,FechaServiceRealizado,LugarService,CostoService,Observaciones,Estado) VALUES 
        (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssss", $dominio, $km, $proximoService, $fecha, $place, $costo, $obs, $Estado);

    if (!$stmt->execute()) {

        echo json_encode(array('success' => 0, 'error' => $mysqli->error));
    } else {

        $mysqli->query("UPDATE Vehiculos SET Estado='En Taller' WHERE Dominio='$dominio'");

        echo json_encode(array('success' => 1));
    }
}

if (isset($_POST['cuadro_forma_de_pago'])) {

    $Grupo = "SELECT id,FormaDePago,CuentaContable FROM FormaDePago WHERE FormaDePago<>'ANTICIPO A PROVEEDORES' ORDER BY FormaDePago ASC";
    $estructura = $mysqli->query($Grupo);
    // echo "<div class='col-lg-5'>";
    echo "<label>Forma de Pago:</label>";
    echo "<select id='formadepago_t' name='formadepago_t' onchange='mostrary(this.value)' class='form-control select2' data-toggle='select2'>";
    echo "<optgroup label='Seleccione una Opción'>";

    while ($row = $estructura->fetch_array(MYSQLI_ASSOC)) {
        //     echo "<option value='".$row[id]."'";
        // if($row[id]=='3' ) {
        //     echo "selected";
        // }
        //     echo ">".$row[FormaDePago]."</option>";
        echo "<option value='" . $row['id'] . "'";
        echo ">" . $row['FormaDePago'] . "</option>";
    }
    echo "</optgroup>";
    echo "</select>";
}


if ($_POST['action'] == 'guardar_seguro') {
    $dominio = $_POST['dominio'];
    $poliza = $_POST['NumeroPoliza'];
    $telefono = $_POST['TelefonoSeguro'];
    $fecha = $_POST['FechaVencSeguro'];

    $sql = "UPDATE Vehiculos SET 
                      NumeroPoliza = '$poliza',
                      TelefonoSeguro = '$telefono',
                      FechaVencSeguro = '$fecha'
                    WHERE Dominio = '$dominio'";

    $res = $mysqli->query($sql); // o mysqli_query si usás mysqli

    echo $res ? 'ok' : 'error';
    exit;
}
