<?php
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

//INCREMENTAR VALORES DE SERVICIOS
if (isset($_POST['Incrementar_valores'])) {

    if ($_POST['Incremento'] <> 0 || $_POST['Incremento'] <> '') {

        $Fecha = date('Y-m-d');
        $Incremento = $_POST['Incremento'] / 100;

        if ($mysqli->query("INSERT INTO Productos_incremento SET Fecha='$Fecha',Incremento='$_POST[Incremento]',Observaciones='$_POST[Observaciones]',Usuario='$_SESSION[Usuario]'")) {

            $mysqli->query("UPDATE Productos SET PrecioVenta_ant=PrecioVenta,PrecioVenta=PrecioVenta+(PrecioVenta * $Incremento)");

            echo json_encode(array('success' => 1));
        } else {

            echo json_encode(array('success' => 0));
        }
    } else {

        echo json_encode(array('success' => 2));
    }
}

if (isset($_POST['Servicios'])) {

    $sql = "SELECT * FROM Productos ";

    $Resultado = $mysqli->query($sql);
    $rows = array();

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}

//VER HISTORIAL DE INCREMENTOS

if (isset($_POST['Incrementos'])) {

    $sql = "SELECT * FROM Productos_incremento ORDER BY Fecha DESC LIMIT 10";

    $Resultado = $mysqli->query($sql);
    $rows = array();

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}


//VER ENVIOS FIJOS DEL RECORRIDO
// if($_POST['VerFijos']==1){

//     $_SESSION['Recorrido']=$_POST['id'];

//     $sql="SELECT
//     tablaB.id,
//     tablaA1.nombrecliente as nombre1,
//     tablaA2.nombrecliente as nombre2
// FROM EntregasFijas tablaB 
// INNER JOIN Clientes as tablaA1 on tablaA1.id = tablaB.idClienteOrigen
// INNER JOIN Clientes as tablaA2 on tablaA2.id = tablaB.idClienteDestino
// WHERE tablaB.Recorrido='$_POST[id]'";

//     $Resultado=$mysqli->query($sql);
//     $rows=array();   

//     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){

//     $rows[]=$row;

//     }

//     echo json_encode(array('data'=>$rows));
// }

//ELIMINAR FIJOS

// if($_POST['EliminarFijo']==1){

// $sql="DELETE FROM EntregasFijas WHERE id='$_POST[id]'";

// if($mysqli->query($sql)){

//     echo json_encode(array('success'=>1));   

// }else{

//     echo json_encode(array('success'=>0));   

// }
// }

if (isset($_POST['ActivarServicios'])) {

    if ($_POST['Inactivo'] == 0) {
        $Inactivo = 1;
    } else {
        $Inactivo = 0;
    }

    $sql = "UPDATE Productos SET Inactivo='$Inactivo' WHERE id='$_POST[id]'";

    if ($mysqli->query($sql)) {
        echo json_encode(array('success' => 1));
    } else {
        echo json_encode(array('success' => 0));
    }
}

//BUSCO EL PROXIMO NUMERO DE RECORRIDO

if (isset($_POST['Prod_num'])) {

    $sql = $mysqli->query("SELECT MAX(Codigo)as Num FROM Productos");
    $row = $sql->fetch_array(MYSQLI_ASSOC);
    $nuevo = $row['Num'] + 1;
    echo json_encode(array('next_num_prod' => $nuevo));
}

//AGREGAR RECORRIDOS

if (isset($_POST['AgregarServicios'])) {
    $name = $_POST['name'] ?? '';
    $number = $_POST['number'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $km = $_POST['km'] ?? 0;
    $precio = $_POST['precio'] ?? 0;
    $alicuota = $_POST['alicuota'] ?? 0;
    $costo = $_POST['costo'] ?? 0;
    $fecha = date('Y-m-d');

    $stmt = $mysqli->prepare("INSERT INTO Productos (Codigo, Titulo, Descripcion, PrecioCosto, PrecioVenta, Fecha, Iva, Kilometros) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("sssddssd", $number, $name, $descripcion, $costo, $precio, $fecha, $alicuota, $km);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => 1]);
        } else {
            echo json_encode(['success' => 0, 'error' => 'No se insertó ningún registro.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => 0, 'error' => $mysqli->error]);
    }
    exit();
}
//MODIFICAR SERVICIOS

if (isset($_POST['ModificarServicios'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $number = $_POST['number'];
    $km = $_POST['km'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $alicuota = $_POST['alicuota'];
    $preciocosto = $_POST['preciocosto'];

    $sql = "UPDATE `Productos` SET `Titulo`='$name', `Descripcion`='$descripcion',`PrecioVenta`='$precio', `Kilometros`='$km',
    `Iva`='$alicuota',`PrecioCosto`='$preciocosto' WHERE id='$id'";

    if ($mysqli->query($sql)) {
        echo json_encode(array('success' => 1));
    } else {
        echo json_encode(array('success' => 0));
    }
}


if (isset($_POST['Serv_datos'])) {
    $Serv = $_POST['Serv'];
    $sql = $mysqli->query("SELECT * FROM Productos WHERE id='$Serv'");

    $row = $sql->fetch_array(MYSQLI_ASSOC);
    $rows = array();
    $rows[] = $row;

    echo json_encode(array('data' => $rows));
}
