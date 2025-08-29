<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
$Usuario = $_SESSION['Usuario'];
$Nivel = $_SESSION['Nivel'];
setlocale(LC_ALL,'es_AR');
date_default_timezone_set('America/Argentina/Buenos_Aires');

if($_POST['OrdenesDeCompra']==1){
    
    if($_POST['filtro']){

        $sql = "SELECT * FROM OrdenesDeCompra WHERE Eliminado = 0 AND Estado ='".$_POST['filtro']."' ORDER BY Fecha";
   
    }else{
    
        $sql = "SELECT * FROM OrdenesDeCompra WHERE Eliminado = 0 AND Estado ='Cargada' ORDER BY Fecha";    
    
    }

    $result = $mysqli->query($sql);
    $rows=array();

if ($result->num_rows > 0) {
    // Itera sobre cada fila de resultados
    while ($row = $result->fetch_assoc()) {
        // Accede a los datos de cada fila usando los nombres de columna
        $rows[]=$row;
    }
    
    echo json_encode(array('data' => $rows));

} else {
    
    echo json_encode(array('data' => $rows));
}



}

if($_POST['OrdenDeCompra_id']==1) {
    // Consulta para obtener el máximo id de OrdenesDeCompra
    $sql = "SELECT MAX(id) AS id FROM OrdenesDeCompra";
    $result = $mysqli->query($sql);

    // Verificar si se obtuvieron resultados
    if ($result->num_rows > 0) {
        // Obtener la fila como un array asociativo
        $row = $result->fetch_assoc();
        
        // Obtener el id máximo y sumar 1
        $id = intval($row['id']) + 1;
        
        // Devolver el id máximo como JSON
        echo json_encode(array('id'=>$id));
    } else {
        // Si no hay resultados, devolver un mensaje de error
        echo json_encode(array('error' => 'No se encontró ningún ID de Orden de Compra'));
    }

    // Cerrar la conexión
    // $mysqli->close();
}

// Consulta para obtener las cuentas del PlanDeCuentas relacionadas con OrdenesDeCompra
if($_POST['OrdenDeCompra_cuentas']==1) {

    $sql = "SELECT NombreCuenta FROM PlanDeCuentas WHERE OrdenesDeCompra = 1 ORDER BY NombreCuenta DESC";
    $result = $mysqli->query($sql);

    // Verificar si se obtuvieron resultados
    if ($result->num_rows > 0) {
        // Crear un array para almacenar las opciones de selección
        $options = array();
        
        // Iterar sobre los resultados y almacenar las opciones en el array
        while ($row = $result->fetch_assoc()) {
            $options[] = $row['NombreCuenta'];
        }
        
        // Devolver el array como JSON
        echo json_encode($options);
    } else {
        // Si no hay resultados, devolver un mensaje de error
        echo json_encode(array('error' => 'No se encontraron cuentas'));
    }

}


if($_POST['OrdenDeCompra_new'] == 1) {
    
    $Fecha = date('Y-m-d');
    $TipoDeOrden = $_POST['TipoDeOrden'];
    $Motivo = $_POST['Motivo'];
    $Precio = $_POST['Precio'];
    $FechaOrden = $_POST['FechaOrden'];
    $FechaAprobado= $_POST['FechaAprobado'];

    $Observaciones = $_POST['Observaciones'];
    $Estado = "Cargada";
    $Aprobado = 0;
    $Titulo = $_POST['Titulo'];    
    

    // Preparar la consulta
    $sql = "INSERT INTO OrdenesDeCompra (Fecha, TipoDeOrden, Titulo, Motivo, Precio, FechaOrden, Estado, Aprobado, Observaciones, UsuarioCarga,FechaAprobado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);

    // Verificar si la preparación de la consulta fue exitosa
    if ($stmt) {
        // Asociar los parámetros y ejecutar la consulta
        $stmt->bind_param("ssssississs", $Fecha, $TipoDeOrden, $Titulo, $Motivo, $Precio, $FechaOrden, $Estado, $Aprobado, $Observaciones, $Usuario,$FechaAprobado);
        $stmt->execute();

        // Obtener el ID insertado
        $id = $stmt->insert_id;

        // Verificar si la inserción fue exitosa
        if ($id) {
            echo json_encode(array('success' => 1, 'id' => $id));
        } else {
            echo json_encode(array('success' => 0, 'error' => 'Error al insertar en la base de datos' .$mysqli->error));
        }

        // Cerrar la consulta preparada
        // $stmt->close();
    } else {
        echo json_encode(array('success' => 0, 'error' => 'Error en la preparación de la consulta'));
    }
}

if (isset($_POST['OrdenDeCompra_delete']) && $_POST['OrdenDeCompra_delete'] == 1) {
    // Verificar que se recibió el ID a eliminar
    if (isset($_POST['delete_oc'])) {
        // Obtener el ID y limpiarlo para evitar inyecciones
        $id = $_POST['delete_oc'];
        $id = $mysqli->real_escape_string($id);

        // Preparar la consulta SQL con un parámetro
        $sql = $mysqli->prepare("UPDATE OrdenesDeCompra SET Eliminado = 1 WHERE id = ? LIMIT 1");

        // Vincular el parámetro y ejecutar la consulta
        $sql->bind_param("i", $id);
        $result = $sql->execute();

        // Verificar si la consulta fue exitosa
        if ($result) {
            echo json_encode(array('success' => 1));
        } else {
            echo json_encode(array('success' => 0, 'error' => $mysqli->error));
        }

        // Cerrar la consulta preparada
        $sql->close();
    } else {
        echo json_encode(array('success' => 0, 'error' => 'No se proporcionó un ID válido'));
    }
}

if($_POST['OrdenDeCompra_pres'] == 1) {

    $id = $_POST['id'];
    $id = $mysqli->real_escape_string($id);
    
    // Preparar la consulta SQL con parámetros
    $sql = $mysqli->prepare("SELECT * FROM OrdenesDeCompra WHERE id = ?");
    
    // Verificar si la preparación de la consulta fue exitosa
    if($sql) {
        // Vincular el parámetro y ejecutar la consulta
        $sql->bind_param("i", $id);
        $sql->execute();
        
        // Obtener el resultado de la consulta
        $result = $sql->get_result();
        
        // Verificar si hay filas devueltas
        if($result->num_rows > 0) {
            $rows = array();
            
            // Iterar sobre las filas del resultado
            while($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            
            // Codificar los resultados como JSON y devolverlos
            echo json_encode(array('data' => $rows));
        } else {
            // No se encontraron resultados
            echo json_encode(array('error' => 'No se encontraron resultados'));
        }
        
        // Cerrar la consulta
        $sql->close();
    } else {
        // Error al preparar la consulta
        echo json_encode(array('error' => 'Error al preparar la consulta'));
    }
}

if($_POST['Presupuestos'] == 1) {

    $id = $_POST['id'];
    $id = $mysqli->real_escape_string($id);
    $Eliminado=0;
    
    // Preparar la consulta SQL con parámetros
    $sql = $mysqli->prepare("SELECT * FROM Presupuestos WHERE idOrden = ? AND Eliminado = ?");
    
    // Verificar si la preparación de la consulta fue exitosa
    if($sql) {
        // Vincular el parámetro y ejecutar la consulta
        $sql->bind_param("ii", $id,$Eliminado);
        $sql->execute();
        
        // Obtener el resultado de la consulta
        $result = $sql->get_result();
        
        // Verificar si hay filas devueltas
        if($result->num_rows > 0) {
            $rows = array();
            
            // Iterar sobre las filas del resultado
            while($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            
            // Codificar los resultados como JSON y devolverlos
            echo json_encode(array('data' => $rows));
        } else {
            // No se encontraron resultados
            echo json_encode(array('data' => 0));
        }
        
        // Cerrar la consulta
        $sql->close();
    } else {
        // Error al preparar la consulta
        echo json_encode(array('data' => 0));
    }
}
//EDITAR PRESUPUESTO
if($_POST['Presupuestos_edit'] == 1) {

    $id = $_POST['id'];
    $id = $mysqli->real_escape_string($id);
    $Eliminado=0;
    
    //Controlo que el usuario sea quien cargo el presupuesto o tenga nivel 1
    
    // Preparar la consulta SQL con parámetros
    $sql_control = $mysqli->prepare("SELECT Usuario FROM Presupuestos WHERE id = ? AND Eliminado = ? ");
    
    // Verificar si la preparación de la consulta fue exitosa
    if($sql_control) {
        // Vincular el parámetro y ejecutar la consulta
        $sql_control->bind_param("ii", $id,$Eliminado);
        $sql_control->execute();
        
        // Obtener el resultado de la consulta
        $result_control = $sql_control->get_result();
        $row_control = $result_control->fetch_assoc();
        
    }

if($Nivel==1 || $row_control['Usuario']==$Usuario){

    // Preparar la consulta SQL con parámetros
    $sql = $mysqli->prepare("SELECT * FROM Presupuestos WHERE id = ? AND Eliminado = ? ");
    
    // Verificar si la preparación de la consulta fue exitosa
    if($sql) {
        // Vincular el parámetro y ejecutar la consulta
        $sql->bind_param("ii", $id,$Eliminado);
        $sql->execute();
        
        // Obtener el resultado de la consulta
        $result = $sql->get_result();
        
        // Verificar si hay filas devueltas
        if($result->num_rows > 0) {
            $rows = array();
            
            // Iterar sobre las filas del resultado
            while($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }            
            
            // Codificar los resultados como JSON y devolverlos
            echo json_encode(array('data' => $rows));
        } else {
            // No se encontraron resultados
            echo json_encode(array('data' => 0));
        }
        
        // Cerrar la consulta
        $sql->close();
    } else {
        // Error al preparar la consulta
        echo json_encode(array('data' => 0));
    }

}else{

    echo json_encode(array('error'=>'Sin permisos'));

}
}


// Consulta para obtener las cuentas del PlanDeCuentas relacionadas con OrdenesDeCompra
if($_POST['Proveedores_cuentas']==1) {

    $sql = "SELECT RazonSocial FROM Proveedores ORDER BY RazonSocial asc";
    $result = $mysqli->query($sql);

    // Verificar si se obtuvieron resultados
    if ($result->num_rows > 0) {
        // Crear un array para almacenar las opciones de selección
        $options = array();
        
        // Iterar sobre los resultados y almacenar las opciones en el array
        while ($row = $result->fetch_assoc()) {
            $options[] = $row['RazonSocial'];
        }
        
        // Devolver el array como JSON
        echo json_encode($options);
    } else {
        // Si no hay resultados, devolver un mensaje de error
        echo json_encode(array('error' => 'No se encontraron cuentas'));
    }

}

if(isset($_POST['Presupuestos_new']) && $_POST['Presupuestos_new'] == 1) {
    // Escapar y limpiar los datos recibidos del formulario
    
    $Fecha = $_POST['Fecha'];
    $Fecha_formateada = date("Y-m-d", strtotime($Fecha));

    $IdOrden = $mysqli->real_escape_string($_POST['IdOrden']);
    $Proveedor = $mysqli->real_escape_string($_POST['Proveedor']);
    $Descripcion = $mysqli->real_escape_string($_POST['Descripcion']);
    $FormaDePago = $mysqli->real_escape_string($_POST['FormaDePago']);
    $Cantidad = $mysqli->real_escape_string($_POST['Cantidad']);
    $Total = $mysqli->real_escape_string($_POST['Total']);
    $Observaciones = $mysqli->real_escape_string($_POST['Observaciones']);
    
    $estado = ''; // Valor inicial de estado
    $aprobado=0;
    $eliminado=0;
    // Calcular el IVA y el precio
    $iva = ($Total * 21) / 100;
    $precio = $Total - $iva;

    // Preparar la consulta
    $sql = "INSERT INTO `Presupuestos`(`Fecha`, `idOrden`, `Proveedor`, `Descripcion`, `Cantidad`, `Observaciones`, `Precio`, `Iva`, `Total`, `Usuario`, `Eliminado`, `FormaDePago`, `Estado`, `Aprobado`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);

    // Verificar si la preparación de la consulta fue exitosa
    if ($stmt) {
        // Asociar los parámetros y ejecutar la consulta
        $stmt->bind_param("sissisdddsissi", $Fecha_formateada, $IdOrden, $Proveedor, $Descripcion, $Cantidad, $Observaciones, $precio, $iva, $Total, $Usuario, $eliminado, $FormaDePago, $estado, $aprobado);
        $result = $stmt->execute();
    
        // Verificar si la ejecución de la consulta fue exitosa
        if ($result) {
            // Obtener el ID insertado
            $id = $stmt->insert_id;
    
            // Verificar si la inserción fue exitosa
            if ($id) {
                echo json_encode(array('success' => 1, 'id' => $id));
            } else {
                echo json_encode(array('success' => 0, 'error' => 'Error al obtener el ID insertado'));
            }
        } else {
            echo json_encode(array('success' => 0, 'error' => 'Error al ejecutar la consulta' . $mysqli->error));
        }

    } else {
        echo json_encode(array('success' => 0, 'error' => 'Error en la preparación de la consulta: ' . $mysqli->error));
    }
    
}

if($_POST['AprobarPresupuesto'] == 1) {

    $id = $_POST['id'];
    
    $idOrden = $_POST['idOrden'];
    $Eliminado = 0;

    if($Nivel == 1) {
        // Preparar la consulta SQL para actualizar el presupuesto actual
        $sql = $mysqli->prepare("UPDATE Presupuestos SET Aprobado = 1 WHERE id = ? AND Eliminado = ? LIMIT 1");

        // Verificar si la preparación de la consulta fue exitosa
        if($sql) {
            // Vincular los parámetros y ejecutar la consulta
            $sql->bind_param("ii", $id, $Eliminado);
            
            if($sql->execute()) {
                // Consulta ejecutada con éxito, ahora actualiza otros presupuestos relacionados
                $sql = $mysqli->prepare("UPDATE Presupuestos SET Aprobado = 0 WHERE idOrden = ? AND Eliminado = ? AND id<> ?");
                if($sql) {
                    // Vincular los parámetros y ejecutar la consulta
                    $sql->bind_param("iii", $idOrden, $Eliminado,$id);
                    
                    if($sql->execute()) {
                        // Consulta ejecutada con éxito
                        echo json_encode(array('success' => 1));
                    } else {
                        // Error en la ejecución de la consulta secundaria
                        echo json_encode(array('success' => 0, 'error' => 'Error al actualizar otros presupuestos'));
                    }
                } else {
                    // Error en la preparación de la consulta secundaria
                    echo json_encode(array('success' => 0, 'error' => 'Error en la preparación de la consulta'));
                }
            } else {
                // Error en la ejecución de la consulta principal
                echo json_encode(array('success' => 0, 'error' => 'Error al aprobar el presupuesto'));
            }
        } else {
            // Error en la preparación de la consulta principal
            echo json_encode(array('success' => 0, 'error' => 'Error en la preparación de la consulta'));
        }
    } else {
        // Nivel de usuario no autorizado
        echo json_encode(array('success' => 0, 'error' => 'Nivel de usuario no autorizado'));
    }
}

if($_POST['ordendecompra_aprobar'] == 1) {
    // Obtener el ID de la orden de compra de manera segura
    $id = filter_var($_POST['idOrden'], FILTER_SANITIZE_NUMBER_INT);
    
    // Obtener la fecha y hora actual
    $Fecha = date('Y-m-d'); // Formato de fecha compatible con MySQL
    $Hora = date("H:i:s"); // Formato de hora compatible con MySQL
    $FechaAprobado=$_POST['FechaAprobado'];
    // Generar código de aprobación
    $CodigoAprobacion = generarCodigo(6);  

    // Actualizar orden de compra de manera segura para evitar inyecciones SQL
    $subirdatos = $mysqli->prepare("UPDATE OrdenesDeCompra SET UsuarioAprobado='$Usuario',Aprobado = 1, Estado = 'Aprobada',FechaAprobado= ?, CodigoAprobacion = ? WHERE id = ?");
    $subirdatos->bind_param("ssi", $FechaAprobado,$CodigoAprobacion, $id);
    $subirdatos->execute();
    
    // Verificar si las consultas se ejecutaron correctamente
    if($subirdatos) {
        echo json_encode(array('success' => 1));
    } else {
        echo json_encode(array('success' => 0));
    }
}

function generarCodigo($longitud) {

    $key = '';
    $pattern = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $max = strlen($pattern) - 1;

    for($i = 0; $i < $longitud; $i++) {
        $key .= $pattern[mt_rand(0, $max)];
    }

    return $key;
}

if($_POST['Aceptar_orden_ok'] == 1) {

    // Obtener los valores de los campos del formulario
    $id = $_POST['idOrden'];
    $NPresup = $_POST['NPresup'];
    
    // Escapar los valores para evitar inyecciones SQL
    $id = $mysqli->real_escape_string($id);
    $NPresup = $mysqli->real_escape_string($NPresup);
    
    // Definir los valores de Aprobado y Eliminado
    $Aprobado = 1;
    $Eliminado = 0;
    
    // Preparar la consulta SQL con parámetros
    $sql = $mysqli->prepare("UPDATE OrdenesDeCompra SET Estado = 'Aceptada', Presupuestos = ?, UsuarioAprobado = ? WHERE id = ? AND Eliminado = ? LIMIT 1");
    
    // Verificar si la preparación de la consulta fue exitosa
    if($sql) {
        // Vincular los parámetros y ejecutar la consulta
        $sql->bind_param("isii", $NPresup, $Usuario, $id, $Eliminado);
        
        if($sql->execute()) {            

            // La consulta se ejecutó con éxito
            echo json_encode(array('success' => 1));
        } else {
            // Error al ejecutar la consulta
            echo json_encode(array('success' => 0, 'error' => 'Error al ejecutar la consulta'));
        }
    } else {
        // Error en la preparación de la consulta
        echo json_encode(array('success' => 0, 'error' => 'Error en la preparación de la consulta: ' . $mysqli->error));
    }
}



if($_POST['Nivel']==1){

    echo json_encode(array('Nivel'=>$Nivel));
}

if($_POST['Total_pendientes']==1){
    
// Consulta para obtener el total de las órdenes de compra con estado 'Cargadas'
$sql_cargadas = $mysqli->prepare("SELECT SUM(Precio) AS Total_Cargadas,COUNT(id) AS Total_Cargadas_cant FROM OrdenesDeCompra WHERE Estado = 'Cargada' AND Eliminado = ?");
$sql_cargadas->bind_param("i", $eliminado);
$eliminado = 0; // Valor del parámetro 'Eliminado'
$sql_cargadas->execute();
$resultado_cargadas = $sql_cargadas->get_result();
$total_cargadas = $resultado_cargadas->fetch_assoc();


// Consulta para obtener el total de las órdenes de compra con estado 'Aceptadas'
$sql_aceptadas = $mysqli->prepare("SELECT SUM(Precio) AS Total_Aceptadas,COUNT(id)AS Total_Aceptadas_cant FROM OrdenesDeCompra WHERE Estado = 'Aceptada' AND Eliminado = ?");
$sql_aceptadas->bind_param("i", $eliminado);
$sql_aceptadas->execute();
$resultado_aceptadas = $sql_aceptadas->get_result();
$total_aceptadas = $resultado_aceptadas->fetch_assoc();

// Consulta para obtener el total de las órdenes de compra con estado 'Aprobadas'
$sql_aprobadas = $mysqli->prepare("SELECT SUM(Precio) AS Total_Aprobadas FROM OrdenesDeCompra WHERE Estado = 'Aprobada' AND Eliminado = ?");
$sql_aprobadas->bind_param("i", $eliminado);
$sql_aprobadas->execute();
$resultado_aprobadas = $sql_aprobadas->get_result();
$total_aprobadas = $resultado_aprobadas->fetch_assoc();

// Convertir los resultados a JSON
$resultados_json = json_encode(array(
    'Total_Cargadas' => $total_cargadas['Total_Cargadas'],
    'Total_Aceptadas' => $total_aceptadas['Total_Aceptadas'],
    'Total_Aprobadas' => $total_aprobadas['Total_Aprobadas'],
    'Total_Cargadas_cant' => $total_cargadas['Total_Cargadas_cant'],
    'Total_Aceptadas_cant' => $total_aceptadas['Total_Aceptadas_cant'],
));

// Devolver los resultados JSON
echo $resultados_json;


}

if($_POST['Observaciones'] == 1) {
    // Verificar si se recibieron los datos esperados
    if(isset($_POST['id'], $_POST['obs'])) {
        // Obtener y limpiar los datos recibidos
        $id = intval($_POST['id']); // Convertir a entero para evitar inyecciones SQL
        $Fecha=date('d/m/Y H:i');
        $obs = $mysqli->real_escape_string(' | '.$Fecha.' '.$Usuario.' '.$_POST['obs']); // Evitar inyecciones SQL
        

        // Preparar la consulta SQL con un parámetro
        $sql = $mysqli->prepare("UPDATE OrdenesDeCompra SET Observaciones = CONCAT(Observaciones, ?) WHERE id = ? LIMIT 1");
        
        // Verificar si la preparación de la consulta fue exitosa
        if($sql) {
            // Vincular los parámetros y ejecutar la consulta
            $sql->bind_param("si", $obs, $id);
            $result = $sql->execute();
            
            // Verificar si la consulta se ejecutó con éxito
            if($result) {
            
                // Consulta para obtener las observaciones actualizadas
                $sql_obs = $mysqli->prepare("SELECT Observaciones FROM OrdenesDeCompra WHERE id = ?");
                $sql_obs->bind_param("i", $id);
                $sql_obs->execute();      
                $result_obs = $sql_obs->get_result();
                $row = $result_obs->fetch_assoc();

                echo json_encode(array('success' => 1,'Observaciones'=>$row['Observaciones']));
            
            } else {
                echo json_encode(array('success' => 0, 'error' => 'Error al ejecutar la consulta'));
            }
            
        } else {
            echo json_encode(array('success' => 0, 'error' => 'Error en la preparación de la consulta'));
        }
    } else {
        echo json_encode(array('success' => 0, 'error' => 'Datos incompletos recibidos'));
    }
}

if($_POST['Presupuestos_edit_ok']==1){
    $id=$_POST['id'];
    $descripcion=$_POST['Descripcion'];
    $formadepago=$_POST['Formadepago'];
    $cantidad=$_POST['Cantidad'];
    $total=$_POST['Total'];
    $observaciones=$_POST['Observaciones'];

    // Preparar la consulta SQL con un parámetro
    $sql = $mysqli->prepare("UPDATE Presupuestos SET Descripcion = ? , FormaDePago = ? , Cantidad = ?, Total= ?, Observaciones =? WHERE id = ? LIMIT 1");
    
    if($sql) {
        // Vincular los parámetros y ejecutar la consulta
        $sql->bind_param("ssiisi", $descripcion,$formadepago,$cantidad,$total,$observaciones,$id);
        $result = $sql->execute();
        
        // Verificar si la consulta se ejecutó con éxito
        if($result) {
            echo json_encode(array('success' => 1));
            
        } else {
            echo json_encode(array('success' => 0, 'error' => 'Error al ejecutar la consulta'));
        }
    } else {
        echo json_encode(array('success' => 0, 'error' => 'Error en la preparación de la consulta'));
    }


}

?>