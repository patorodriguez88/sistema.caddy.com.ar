<?php
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper: acepta DD/MM/YYYY, YYYY-MM-DD o MM/DD/YYYY y devuelve YYYY-MM-DD
function parseFechaFlexible($s)
{
    $s = trim((string)$s);
    if ($s === '') return null;
    $dt = DateTime::createFromFormat('d/m/Y', $s);
    if ($dt instanceof DateTime) return $dt->format('Y-m-d');
    $dt = DateTime::createFromFormat('Y-m-d', $s);
    if ($dt instanceof DateTime) return $dt->format('Y-m-d');
    $dt = DateTime::createFromFormat('m/d/Y', $s);
    if ($dt instanceof DateTime) return $dt->format('Y-m-d');
    return null;
}

// ✅ Verificar si se recibió un parámetro "action"
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

if ($action === 'listar') {
    // ✅ Listar cuentas bancarias
    try {
        $query = "SELECT Cuenta, NombreCuenta FROM PlanDeCuentas WHERE Cuenta IN ('111200', '111210')";
        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            echo json_encode(["data" => [], "error" => "Error en la preparación de la consulta"]);
            exit;
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $bancos = [];

        while ($row = $result->fetch_assoc()) {
            $bancos[] = $row;
        }

        $stmt->close();

        echo json_encode(["data" => $bancos], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        echo json_encode(["data" => [], "error" => "Error en la consulta: " . $e->getMessage()]);
        exit;
    }
} elseif ($action === 'consultar_conciliacion') {
    // ✅ Consultar conciliación bancaria
    $Cuenta = isset($_POST['Cuenta']) ? $_POST['Cuenta'] : '';
    $Desde = isset($_POST['desde']) ? $_POST['desde'] : '';
    $Hasta = isset($_POST['hasta']) ? $_POST['hasta'] : '';
    $Sucursal = "Córdoba";

    $Desde = parseFechaFlexible($Desde);
    $Hasta = parseFechaFlexible($Hasta);

    $query = "SELECT t.id, t.Conciliado, t.Fecha, t.NombreCuenta, t.Cuenta, t.Debe, t.Haber,
                     t.Observaciones, t.idTransProvee, t.Usuario, t.NumeroAsiento, 
                     COALESCE(Ctasctes.RazonSocial, TransProveedores.RazonSocial) AS Cliente
              FROM Tesoreria t 
              LEFT JOIN Ctasctes ON t.idCtasctes = Ctasctes.id
              LEFT JOIN TransProveedores ON t.idTransProvee = TransProveedores.id
              WHERE t.Eliminado = 0 AND t.Pendiente = 0 AND t.Sucursal = ?";

    $params = [$Sucursal];
    $types = "s";

    if (!empty($Cuenta)) {
        $query .= " AND t.Cuenta = ?";
        $params[] = $Cuenta;
        $types .= "s";
    }
    if (!empty($Desde) && !empty($Hasta)) {
        $query .= " AND t.Fecha BETWEEN ? AND ?";
        $params[] = $Desde;
        $params[] = $Hasta;
        $types .= "ss";
    }

    $query .= " ORDER BY t.Fecha ASC";
    $stmt = $mysqli->prepare($query);

    if (!$stmt) {
        echo json_encode(["error" => "Error en la preparación de la consulta"]);
        exit;
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $datos = [];
    while ($row = $result->fetch_assoc()) {
        $datos[] = $row;
    }

    $stmt->close();
    $mysqli->close();

    echo json_encode(["data" => $datos], JSON_UNESCAPED_UNICODE);
    exit;
} elseif ($action === 'grabar_conciliacion') {

    // ✅ Validar que se envíen los datos correctos
    if (!isset($_POST['ids']) || empty($_POST['ids'])) {
        echo json_encode(["success" => false, "error" => "No hay registros seleccionados"]);
        exit;
    }

    // ✅ Recibir los datos desde el POST
    $ids = is_array($_POST['ids']) ? $_POST['ids'] : [$_POST['ids']]; // Convertir a array si es un solo valor
    $ids = array_filter($ids, 'is_numeric'); // Filtrar valores no numéricos

    if (empty($ids)) {
        echo json_encode(["success" => false, "error" => "IDs inválidos"]);
        exit;
    }

    $Cuenta = isset($_POST['cuenta']) ? $_POST['cuenta'] : '';
    $Desde = isset($_POST['desde']) ? $_POST['desde'] : '';
    $Hasta = isset($_POST['hasta']) ? $_POST['hasta'] : '';
    $Sucursal = "Córdoba";
    $UsuarioConciliado = $_SESSION['Usuario'] ?? 'Sistema';

    // ✅ Formatear las fechas al formato MySQL (YYYY-MM-DD)
    $Desde = parseFechaFlexible($Desde);
    $Hasta = parseFechaFlexible($Hasta);

    // ✅ PRIMERO: Poner todos los conciliados entre las fechas seleccionadas en `0`
    if (!empty($Cuenta) && !empty($Desde) && !empty($Hasta)) {
        $query = "UPDATE Tesoreria SET Conciliado = 0 WHERE Cuenta = ? AND Fecha BETWEEN ? AND ? AND Eliminado = 0 AND Pendiente = 0 AND Sucursal = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssss", $Cuenta, $Desde, $Hasta, $Sucursal);
        $stmt->execute();
        $stmt->close();
    }

    // ✅ SEGUNDO: Marcar los conciliados seleccionados en `1`
    $FechaConciliado = date('Y-m-d'); // Obtener la fecha actual

    // Construcción de placeholders dinámicos
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $query = "UPDATE Tesoreria SET Conciliado = 1, FechaConciliado = ?, UsuarioConciliado = ? WHERE id IN ($placeholders)";

    $stmt = $mysqli->prepare($query);

    // ✅ Crear tipos dinámicos: 2 strings (fecha y usuario) + n enteros (IDs)
    $types = "ss" . str_repeat('i', count($ids));
    $params = array_merge([$FechaConciliado, $UsuarioConciliado], $ids);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $stmt->close();

        // ✅ TERCERO: Si la conciliación es de un cheque a pagar, marcarlo en la tabla `Cheques`
        $query = "UPDATE Cheques SET Pagado = 1 WHERE NumeroCheque IN (SELECT NumeroCheque FROM Tesoreria WHERE id IN ($placeholders))";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => true, "message" => "Conciliación guardada correctamente"]);
    } else {
        echo json_encode(["success" => false, "error" => "Error al actualizar conciliados"]);
    }

    $mysqli->close();
    exit;
}

// ✅ Si no se recibe una acción válida
echo json_encode(["error" => "Acción no permitida"]);
exit;





// $Sucursal=$_SESSION['Sucursal'];
// $Cuenta=$_POST['CuentaBancaria'];
// $Desde=$_POST[desde_t];
// $Hasta=$_POST[hasta_t];
// $Desde1=explode("-",$Desde,3);
// $Hasta1=explode("-",$Hasta,3);
// $Desde2=$Desde1[2]."/".$Desde1[1]."/".$Desde1[0];
// $Hasta2=$Hasta1[2]."/".$Hasta1[1]."/".$Hasta1[0];
// $ColSpan=5;

// if($_POST[Accion]=='Grabar Conciliacion'){

// //  PRIMERO PONGO TODOS LOS CONCILIADOS ENTRE LAS FECHAS SELECCIONADAS EN CERO

//   $sql=mysql_query("UPDATE Tesoreria SET Conciliado='0' WHERE Cuenta='$Cuenta' AND Fecha>='$Desde' AND Fecha<='$Hasta' AND Eliminado=0 AND Pendiente=0 AND Sucursal='$Sucursal'");             
//   $idConciliado=$_POST[conciliado];

// // LUEGO MARCO TOOS LOS QUE ESTEN SELECCIONADOS EN EL CHECKBOX LOS ANTERIORES Y LOS NUEVOS

//   for($i=0;$i<=count($idConciliado);$i++){
//   $Fecha=date('Y-m-d');  
//   $sql=mysql_query("UPDATE Tesoreria SET Conciliado='1',FechaConciliado='$Fecha',UsuarioConciliado='".$_SESSION[Usuario]."' WHERE id='$idConciliado[$i]'");      

// //  AHORA SI LA CONCILIACION ES DE UN CHEQUE A PAGAR LO MARCO EN LA TABLA CHEQUES.

//   $sqlBuscoCheque=mysql_query("SELECT NumeroCheque FROM Tesoreria WHERE NumeroCheque<>'' AND id='$idConciliado[$i]'");
//   $sqlBuscoChequeR=mysql_fetch_array($sqlBuscoCheque);
//   $sqlEjecutar=mysql_query("UPDATE Cheques SET Pagado='1' WHERE NumeroCheque='$sqlBuscoChequeR[NumeroCheque]'");  
//   }		
// }
//   if ($_POST['CuentaBancaria']==''){
// 			echo "<form class='login' action='' method='post' style='float:center; width:500px;'>";
// 			echo "<div><titulo>Seleccione Cuenta Bancaria y Fechas</titulo></div>";
//       echo "<div><hr></hr></div>";
// 			$Seleccion=mysql_query("SELECT Cuenta, NombreCuenta FROM PlanDeCuentas WHERE Cuenta in('111200','111210')");
// 			echo "<div><label>Razon Social:</label><select name='CuentaBancaria' style='float:center;width:390px;' size='1'>";
// 			echo "<option>Seleccione Cuenta Bancaria</option>";
// // 			$Dato = mysql_result($Seleccion,0);

//       while ($row = mysql_fetch_array($Seleccion)){
//       echo "<option value='".$row[Cuenta]."'>".$row['NombreCuenta']."</option>";
//       }
// 			echo "</select></div>";
//       echo "<div><label>Desde:</label><input type='date' name='desde_t' ></div>";
//       echo "<div><label>Hasta:</label><input type='date' name='hasta_t' ></div>";
//       echo "<div><input type='submit' name='BuscarCuenta' value='Aceptar' ></div>";

//       echo "</form>";
//       goto a;  
//     }  
// echo "<table class='login' border='0'>";
// echo "<caption>Cuenta Bancaria: $Dato $Cuenta Desde: $Desde2 Hasta: $Hasta2</caption>";
// echo "<th>Fecha</th>";
// echo "<th>Cuenta</th>";
// echo "<th>Razon Social</th>";
// echo "<th>N.Asiento</th>";  
// echo "<th>Observaciones</th>";
// echo "<th>Debe</th>";
// echo "<th>Haber</th>";
// echo "<th>Conciliado</th>";  

// $ordenar="SELECT t.id,t.Conciliado,t.Fecha,t.NombreCuenta,t.Cuenta,t.Debe,t.Haber,
// t.Observaciones,t.idTransProvee,t.Usuario,t.NumeroAsiento, 
// if(Ctasctes.RazonSocial is NULL,TransProveedores.RazonSocial,Ctasctes.RazonSocial)AS Cliente FROM Tesoreria t 
// LEFT JOIN Ctasctes ON t.idCtasctes=Ctasctes.id
// LEFT JOIN TransProveedores ON t.idTransProvee=TransProveedores.id
// WHERE t.Cuenta='$Cuenta' AND t.Fecha>='$Desde' AND t.Fecha<='$Hasta' AND t.Eliminado=0 AND t.Pendiente=0 AND t.Sucursal='$Sucursal' ORDER BY t.Fecha ASC";
// //  $ordenar="SELECT * FROM Tesoreria WHERE Cuenta='$Cuenta' AND Fecha>='$Desde' AND Fecha<='$Hasta' AND Eliminado=0 AND Pendiente=0 AND Sucursal='$Sucursal' ORDER BY Fecha ASC";	
//  $MuestraStock=mysql_query($ordenar);

// 	while($row=mysql_fetch_array($MuestraStock)){
//     if($numfilas%2 == 0){
//     echo "<tr style='background: #f2f2f2;font-size:11px' >";
//     }else{
//     echo "<tr style='background:$color2;font-size:11px' >";
//     }	

//     $fecha=$row['Fecha'];
//     $arrayfecha=explode('-',$fecha,3);
//     $Fecha2=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
//     $Debe=number_format($row['Debe'],2,',','.');
//     $Haber=number_format($row['Haber'],2,',','.');

// //   echo "<tr style='font-size:12px;background:$color1;color:$font1'>
//     echo "<td>$Fecha2</td>";
//     echo "<td style='font-size:10px;min-width:130px'>".$row['Cuenta']."</br> ".$row['NombreCuenta']."</td>";
//     echo "<td>".$row['Cliente']."</td>";
//     echo "<td>".$row['NumeroAsiento']."</td>";
//     echo "<td>".$row['Observaciones']."</td>";
//     echo "<td>$ $Debe</td>";
//     echo "<td>$ $Haber</td>";
//    if($row['Conciliado']==1){
//      $valor='checked';
//    }else{
//      $valor='';
//    } 
//     echo "<form id='limpio' action='' method='POST'>";  
//     echo "<td align='center' style='float:center'><input type='checkbox' name='conciliado[]' value='$row[id]' $valor></td>";  
//     echo "<input type='hidden' name='conci[]' value='$row[Conciliado]'>";
// 	echo "<input type='hidden' name='CuentaBancaria' value='$Cuenta'>";
// 	echo "<input type='hidden' name='desde_t' value='$Desde'>";
// 	echo "<input type='hidden' name='hasta_t' value='$Hasta'>";
//   $numfilas++;
//   }
//   $SaldoSeleccionDebe=mysql_query("SELECT SUM(Debe)as TotalDebe FROM Tesoreria WHERE Cuenta='$Cuenta' AND Fecha>=$Desde AND Fecha<='$Hasta' 
// AND Sucursal='$Sucursal' AND Eliminado=0 AND Pendiente=0 ");	
// $row=mysql_fetch_array($SaldoSeleccionDebe);
// $DebeSeleccion=$row[TotalDebe];
// $DebeSeleccion1=number_format($row[TotalDebe],2,',','.');	
// $SaldoSeleccionHaber=mysql_query("SELECT SUM(Haber)as TotalHaber FROM Tesoreria WHERE Cuenta='$Cuenta' AND Fecha>=$Desde AND Fecha<='$Hasta' 
// AND Sucursal='$Sucursal' AND Eliminado=0 AND Pendiente=0 ");	
// $row=mysql_fetch_array($SaldoSeleccionHaber);
// $HaberSeleccion=$row[TotalHaber];
// $HaberSeleccion1=number_format($row[TotalHaber],2,',','.');  
// $SaldoCuenta=number_format($DebeSeleccion-$HaberSeleccion,2,',','.');	

// $SaldoSeleccionDebeConciliado=mysql_query("SELECT SUM(Debe)as TotalDebe FROM Tesoreria WHERE Cuenta='$Cuenta' AND Fecha>='$Desde' AND Fecha<='$Hasta' 
// AND Sucursal='$Sucursal' AND Eliminado=0 AND Pendiente=0 AND Conciliado='1'");	
// $row1=mysql_fetch_array($SaldoSeleccionDebeConciliado);
// $DebeSeleccionConciliado=$row1[TotalDebe];  
// $DebeSeleccionConciliado1=number_format($row1[TotalDebe],2,',','.');	
// $SaldoSeleccionHaberConciliado=mysql_query("SELECT SUM(Haber)as TotalHaber FROM Tesoreria WHERE Cuenta='$Cuenta' AND Fecha>='$Desde' AND Fecha<='$Hasta' 
// AND Sucursal='$Sucursal' AND Eliminado=0 AND Pendiente=0 AND Conciliado='1'");	
// $row1=mysql_fetch_array($SaldoSeleccionHaberConciliado);
// $HaberSeleccionConciliado=$row1[TotalHaber];  
// $HaberSeleccionConciliado1=number_format($row1[TotalHaber],2,',','.');	
// // $SaldoCuentaConciliado=number_format(51.84+$DebeSeleccionConciliado-$HaberSeleccionConciliado,2,',','.');		
// $SaldoCuentaConciliado=number_format($DebeSeleccionConciliado-$HaberSeleccionConciliado,2,',','.');		
// 		if ($SaldoTotal>='0'){
// 			$colorsaldo=white;
// 		}else{
// 			$colorsaldo=red;
// 		}
// //SALDO TOTAL
// $DiferenciaSaldos=number_format($Numero1-$Numero2,2,',','.');

//   echo "<tfoot>";
//   echo "<th colspan='5'>Detalle:</th><th>Debe</th><th>Haber</th><th>Saldo</th>";
//   echo "<tr><td colspan='5'>Total Cuenta Contable:</td><td>$ $DebeSeleccion1</td><td>$ $HaberSeleccion1</td><td>$ $SaldoCuenta</td></tr>";
//   echo "<tr><td colspan='5'>Total Conciliado > 01 de Mayo 2018:</td><td>$ $DebeSeleccionConciliado1</td><td>$ $HaberSeleccionConciliado1</td><td>$ $SaldoCuentaConciliado</td></tr>";
//   echo "</tfoot>";  

// 	echo "</table>";

// echo "<div><input type='submit' name='Accion' value='Grabar Conciliacion' 
//     style='background: none repeat scroll 0 0 #E24F30;
//     border: 1px solid #C6C6C6;
//     float: right;
//     font-weight: bold;
//     padding: 8px 26px;
// 	  color:#FFFFFF;
//     font-size:12px;'></div>";
// echo "</form>";
