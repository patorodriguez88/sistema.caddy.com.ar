<?php
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
    
    $miConexion = new conexion();
    
    $conexion = $miConexion->obtenerConexion();
    
    if (!$conexion) {
        echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
        exit;
    }
    
    if ($accion === 'guardar_asiento') {
        guardarAsiento($conexion);
    } elseif ($accion === 'obtener_asiento') {
        obtenerAsiento($conexion);
    } elseif ($accion === 'obtener_cuentas') {
        obtenerCuentas($conexion);
    }elseif ($accion === 'buscar_asiento') {
        buscarAsiento($conexion);
    }elseif ($accion === 'obtener_datos_libro_diario'){
        libroDiario($conexion);
    }elseif ($accion === 'consultar_asiento'){
        consultaAsiento($conexion);   
    }
}



function buscarAsiento($conexion){
    $numero = $conexion->real_escape_string($_POST['numeroAsiento']);
    $sql = "SELECT * FROM Tesoreria WHERE NumeroAsiento = '$numero' AND Eliminado = 0";
    $resultado = $conexion->query($sql);
    $asiento = [];

    if ($resultado) {
        while ($row = $resultado->fetch_assoc()) {
            $asiento[] = $row;
        }
    }

    echo json_encode($asiento);
    exit();        
}

function guardarAsiento($conexion) {
    try {
        $usuario = $_SESSION['Usuario'];
        $fecha = date("Y-m-d");
        $nasiento = $_POST['n_asiento'];
        $infoABM = "Actualizado por $usuario el " . date('d-m-Y H:i');

        // 1. Traer IDs existentes del asiento actual
        $asientoActual = [];
        $sqlExistentes = "SELECT id FROM Tesoreria WHERE NumeroAsiento = '$nasiento' AND Eliminado != 1";
        $resultado = $conexion->query($sqlExistentes);
        while ($fila = $resultado->fetch_assoc()) {
            $asientoActual[] = $fila['id'];
        }

        $idsRecibidos = $_POST['id'] ?? [];

        // 2. Marcar como Eliminado los que fueron borrados
        foreach ($asientoActual as $idExistente) {
            if (!in_array($idExistente, $idsRecibidos)) {
                $infoABM = "Eliminado por $usuario el " . date('d-m-Y H:i');
                $conexion->query("UPDATE Tesoreria SET Eliminado = 1, InfoABM = '$infoABM' WHERE id = $idExistente");
            }
        }

        // 3. Insertar o actualizar los datos recibidos
        foreach ($_POST['nombreCuenta'] as $index => $nombreCuenta) {
            $cuenta = $_POST['cuenta'][$index] ?? null;
            $debe = floatval($_POST['debe'][$index] ?? 0);
            $haber = floatval($_POST['haber'][$index] ?? 0);
            $observaciones = $_POST['observaciones'] ?? '';
            $idFila = $_POST['id'][$index] ?? null;
        
            // 🚨 Validaciones clave
            if (!$cuenta || trim($cuenta) === '') {
                continue; // Salta esta fila si no hay cuenta
            }
        
            $nombreCuenta = $conexion->real_escape_string($nombreCuenta ?? '');
            $cuenta = $conexion->real_escape_string($cuenta);
            $observaciones = $conexion->real_escape_string($observaciones);
        
            if ($idFila && trim($idFila) !== '') {
                // UPDATE
                $sql = "UPDATE Tesoreria SET 
                            Fecha = '$fecha',
                            NombreCuenta = '$nombreCuenta',
                            Cuenta = '$cuenta',
                            Debe = $debe,
                            Haber = $haber,
                            Observaciones = '$observaciones',
                            Usuario = '$usuario',
                            InfoABM = '$infoABM'
                        WHERE id = $idFila";
            } else {
                // INSERT
                $infoABM = "Insertado por $usuario el " . date('d-m-Y H:i');
                $sql = "INSERT INTO Tesoreria 
                            (Fecha, NombreCuenta, Cuenta, Debe, Haber, Usuario, Observaciones, NumeroAsiento, InfoABM, Caja, Dominio) 
                        VALUES 
                            ('$fecha', '$nombreCuenta', '$cuenta', $debe, $haber, '$usuario', '$observaciones', '$nasiento', '$infoABM', 0, 0)";
            }
        
            if (!$conexion->query($sql)) {
                echo json_encode(["mensaje" => "Error al guardar: " . $conexion->error]);
                exit();
            }
        }


        echo json_encode(["mensaje" => "Asiento contable actualizado correctamente."]);

    } catch (Exception $e) {
        echo json_encode(["mensaje" => "Excepción: " . $e->getMessage()]);
        exit();
    }
}


function obtenerAsiento($conexion) {
    $sql = "SELECT MAX(id) AS id FROM Tesoreria";
    $resultado = $conexion->query($sql);
    
    if ($resultado) {
        $row = $resultado->fetch_assoc();
        $id = $row['id'];

        $sql = "SELECT NumeroAsiento FROM Tesoreria WHERE id = '$id'";
        $resultado = $conexion->query($sql);
        

        if ($resultado) {
            $row = $resultado->fetch_assoc();
            $Nasiento=$row['NumeroAsiento']+1;

            echo json_encode(["NumeroAsiento" => $Nasiento]);
        } else {
            echo json_encode(["mensaje" => "No se encontró el asiento"]);
        }
    } else {
        echo json_encode(["mensaje" => "Error al obtener el asiento"]);
    }
}

function obtenerCuentas($conexion) {

    $sql = "SELECT Nivel, NombreCuenta, Cuenta FROM PlanDeCuentas WHERE Nivel >= 4 ORDER BY NombreCuenta ASC";
    
    $resultado = $conexion->query($sql);

    $cuentas = [];

    if ($resultado) {

        while ($row = $resultado->fetch_assoc()) {
            $cuentas[] = [
                "Nivel" => $row["Nivel"],
                "NombreCuenta" => $row["NombreCuenta"],
                "Cuenta" => $row["Cuenta"]
            ];
        }
        
        echo json_encode($cuentas);
        exit(); // ✅ NECESARIO
    } else {
        
        echo json_encode([]);
        exit(); // ✅ NECESARIO
    }
}


function libroDiario($conexion) {

    $Fecha = $_POST['fecha'];
    $sql = "SELECT NumeroAsiento, Fecha, Cuenta, NombreCuenta, Debe, Haber FROM Tesoreria WHERE Fecha='$Fecha' AND Eliminado=0";
    $resultado = $conexion->query($sql);
    $rows = [];

    if ($resultado) {
        while ($row = $resultado->fetch_assoc()) {
            $rows[] = $row;
        }
        echo json_encode(['data' => $rows]);
    } else {
        echo json_encode(['data' => []]);
    }
}

function consultaAsiento($conexion){

    $desde = $_POST['fecha_desde'];
    $hasta = $_POST['fecha_hasta'];
    $cuentaDesde = $_POST['cuenta_desde'];
    $cuentaHasta = $_POST['cuenta_hasta'];

    $query = "SELECT * FROM Tesoreria 
            WHERE Fecha BETWEEN ? AND ?
            AND Cuenta BETWEEN ? AND ?
            AND Eliminado = 0
            ORDER BY Fecha, NumeroAsiento";

    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ssss", $desde, $hasta, $cuentaDesde, $cuentaHasta);
    $stmt->execute();

    $result = $stmt->get_result();
    $datos = [];

    while ($row = $result->fetch_assoc()) {
    $datos[] = $row;
    }

    echo json_encode($datos);
}





// include_once "../ConexionBD.php";
// $user= $_POST['user'];
// $password= $_POST['password'];
// $Sucursal=$_SESSION['Sucursal'];
// $color='#B8C6DE';
// $font='white';
// $color2='white';
// $font2='black';
// $Usuario=$_SESSION['Usuario'];
// $Sucursal=$_SESSION['Sucursal'];


//    <script>
//     $(document).ready(function () {
//    var n1 = parseFloat(document.MyForm.total.value); 
   
//     if(n1<>0)
//     {
//         document.getElementById('Finalizar').style.background=blue;
//     }
//     else
//     {
//         document.getElementById('Finalizar').style.background=yellow;
//     }
// });
// </script>

//   <?php
// echo "<div id='contenedor'>"; 
// echo "<div id='cabecera'>"; 
// include("../Alertas/alertas.html");     
// include("../Menu/MenuGestion.php"); 
// echo "</div>";//cabecera 
// echo "<div id='cuerpo'>"; 
// echo "<div id='lateral'>"; 
// include("Menu/MenuLateralContabilidad.php"); 	
// echo "</div>"; //lateral
// echo  "<div id='principal'>";
// if($_GET[Cancelo]==Si){
  
// }  
// $color='#B8C6DE';
// $font='white';
// $color2='white';
// $font2='black';
  
// if($_GET['ModificarAsiento']=='Aceptar'){

// $_SESSION['Ventana']='ModificarAsiento';  
// $AsientoNumero=$_GET['numeroasiento_t'];  
// if($AsientoNumero==''){
//   echo "<form class='login' action='' method='GET' style='float:center; width:500px;'>";
// echo "<div><titulo>Ingrese el Numero de Asiento a Modificar</titulos></div>";
//   echo"<div><hr></hr></div>";
// echo "<div><label>Numero de Asiento:</label><input name='numeroasiento_t' size='20' type='text' style='float:right;' value='' required/></div>";
// echo "<div><input name='ModificarAsiento' class='bottom' type='submit' value='Aceptar'></label></div>";
// echo "</form>";	
// goto a;
// }  
// echo "<table class='login' style='width:97%;margin-left:15px'>";
// echo "<caption>Modificar asiento $AsientoNumero</caption>";
// echo "<th>Fecha</th>";
// echo "<th>Cuenta</th>";
// echo "<th>Nombre Cuenta</th>";
// echo "<th>Debe</th>";
// echo "<th>Haber</th>";
// echo "<th>Eliminar</th>";
// $BuscaAsientos= mysql_query("SELECT * FROM Tesoreria WHERE NumeroAsiento=$AsientoNumero AND Eliminado=0");  
// $numfilas = mysql_num_rows($BuscaAsientos);
//   while ($file = mysql_fetch_array($BuscaAsientos)){
// 	if($file[Pendiente]==1){
//   $font1='red';
//   }else{
//   $font1='black';  
//   }
//   if($numfilas%2 == 0){
// 	echo "<tr style='font-size:12px;color:$font1;background: #f2f2f2;' >";
// 	}else{
// 	echo "<tr style='font-size:12px;color:$font1;background:$color2;' >";
// 	}	
    
// echo "<td style='padding:8px;'>$file[Fecha]</td><td>$file[Cuenta]</td><td>$file[NombreCuenta]</td><td>$file[Debe]</td><td>$file[Haber]</td>";
// echo "<td align='center'><a class='img' href='Contabilidad.php?Eliminar=si&id=$file[0]&NA=$NAsiento'><img src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:center;'></a></td></tr>";    
// $numfilas++;  
// }

  
// $SumaAsiento="SELECT SUM(Debe-Haber)AS TotalAsiento FROM Tesoreria WHERE NumeroAsiento='$AsientoNumero' AND Eliminado=0 ";
// $SumaAsientoConsulta=mysql_query($SumaAsiento);
// $row=mysql_fetch_array($SumaAsientoConsulta);
// setlocale(LC_ALL,'es_AR');
// $Total=money_format('%i',$row[TotalAsiento]);
// $Fecha=date('Y-m-d');
//   echo "<tr align='right' style='background:$color; color:$font; font-size:16px;'>";
// echo "<td colspan='6'>Total Asiento: $Total</td></tr></table>";
// echo "<form action='VentanaCargaAsientos.php' class='limpio' method='GET' style='width:97%;'>";
//   echo "<div><input type='hidden'  name='Ventana' value='ModificarAsiento' ></div>";
//   echo "<div><input type='hidden'  name='fecha_t' value='$Fecha' ></div>";
//   echo "<div><input type='hidden'  name='nasiento_t' value='$AsientoNumero' ></div>";
//   echo "<div><input type='hidden'  name='nasiento_t' value='$AsientoNumero' ></div>";
//   echo "<div><input type='submit' class='botton' name='Aceptar' value='Ingresar Asiento'></div>";

// if($row[TotalAsiento]<>0 OR $numfilas==0){
// $activar='disabled';
// $stylo='opacity:0.5;filter:aplpha(opacity=50);';  
// }else{
// $activar='';  
// $stylo='';  
// }  

//   echo "<div><input type='submit' class='botton' name='Cargar' value='Finalizar' style='$stylo' $activar></div>";
//   echo "</form>";  

// }
  
// 	if ($_POST['CerrarEjercicio']=='Aceptar'){
// 	$FechaCierre=$_POST['fecha_t'];
// 	$Ejercicio=$_POST['ano_t'];
// 	$Buscar=mysql_query("SELECT * FROM CierreEjercicio WHERE Ejercicio='$Ejercicio'");
// 	$numerofilas = mysql_num_rows($Buscar);	
	

//     if ($numerofilas<>'0'){

 	
// 		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script> -->
 // 	  <script language="JavaScript" type="text/javascript"> -->
 // 		alert("ERROR: EL EJERCICIO 
 
//  echo "$Ejercicio";
//    echo "$FechaCierre";
// )
// 		</script> -->
  		

// 	goto a;
// 	}else{
// 	 $sql="INSERT INTO `CierreEjercicio`(
// 	 Ejercicio,
// 	 Fecha,
// 	 Usuario,
// 	 Estado) VALUES ('{$Ejercicio}','{$FechaCierre}','{$Usuario}','1')"; 
//  	mysql_query($sql);

// }
		
// 	}	
// //DESDE ACA PARA CERRR EJERCICIO
// if ($_GET['CerrarEjercicio']=='Si'){
// echo "<form name='MyForm' class='login' action='' method='post' style='float:center; width:500px;'>";
// echo "<div><label style='float:center;color:red;font-size:22px;'>Cerrar Ejercicio</label></div>";
// echo "<div><label>Fecha de cierre:</label><input type='date' name='fecha_t' ></div>";
// echo "<div><label>Ejercicio:</label><select name='ano_t' style='width:150px;'>";
// $a=date('Y')-2;
// $ii=$a+2;	
// for ($i=$a;$i<=$ii;$i++){
// echo "<option value='$i'>$i</option>";
// }	
// echo "</select></div>";
// echo "<div><input name='CerrarEjercicio' class='bottom' type='submit' value='Aceptar'></label></div>";
// echo "</form>";	
// goto a;
// }
	
// if($_GET['Accion']=='Eliminar Asiento'){
// $Asiento=$_GET['Asiento'];
// $sql="UPDATE `Tesoreria` SET Eliminado=1 WHERE NumeroAsiento='$Asiento'";
// mysql_query($sql);	
// }
	
	
// 	if($_GET['Anticipo']=='Si'){
// 	echo "<form class='login' action='' method='GET' style='float:center; width:600px;'>";
// 			echo "<div><titulo>Cargar Anticipo a Empleados</titulo></div>";
//       echo "<div><hr></hr></div>";
//       $sqlorigen= mysql_query("SELECT Cuenta,NombreCuenta FROM PlanDeCuentas WHERE Anticipos=1");
//       $Cuentaorigen=mysql_result($GrupoC,0,'NombreCuenta');
//     	echo "<div><label>Cuenta Origen:</label><select placeholder='Seleccionar Cuenta Origen' name='nombrecuenta2_t' style='float:center;width:390px;' size='1' required>";
// 			echo "<option value=''>Seleccione una opcion</option>";
//       while ($row = mysql_fetch_array($sqlorigen)){
// 			echo "<option value='".$row[NumeroCuenta]."'>".$row[NombreCuenta]."</option>";
// 			}
// 			echo "</select></div>";
// 			$Grupo="SELECT NombreCompleto,CuentaAnticipos FROM Empleados WHERE Inactivo='0' ORDER BY NombreCompleto ASC";
// 			$estructura= mysql_query($Grupo);
// 			echo "<div><label>Nombre Empleado:</label><select placeholder='Seleccionar' name='nombrecuenta1_t' style='float:center;width:390px;' size='1' required>";
// 			echo "<option value=''>Seleccione una opcion</option>";
//       while ($row = mysql_fetch_row($estructura)){
// 			echo "<option value='".$row[1]."'>".$row[0]."</option>";
// 			}
// 			echo "</select></div>";
// 			echo "<div><label>Importe:</label><input type='text' name='importe_t'></div>";
// 			echo "<div><label>Observaciones:</label><input type='text' name='observaciones_t'></div>";
// 			echo "<div><input type='submit' name='Accion' value='Aceptar'></div>";
// 	echo "</form>";	
// }
// if($_GET['Accion']=='Aceptar'){
// 	$NumeroCuenta1=$_GET['nombrecuenta1_t'];
// 	$NumeroCuenta2=$_GET['nombrecuenta2_t'];
  
// 	$GrupoB= mysql_query("SELECT * FROM PlanDeCuentas WHERE Cuenta='$NumeroCuenta1'");
// 	$Cuenta1=mysql_result($GrupoB,0,'NombreCuenta');
// 	$GrupoC= mysql_query("SELECT * FROM PlanDeCuentas WHERE Cuenta='$NumeroCuenta2'");
// 	$Cuenta2=mysql_result($GrupoC,0,'NombreCuenta');
// 	$Fecha=date('Y-m-d');	
// 	$Debe1=$_GET['importe_t'];
// 	$Haber1='0';
// 	$Debe2='0';
// 	$Haber2=$_GET['importe_t'];
// 	$Observaciones=$_GET['observaciones_t'];

//   $BuscaNumAsiento= mysql_query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
// 	$row = mysql_fetch_row($BuscaNumAsiento); 
// 	$NAsiento = trim($row[0])+1;
// // 		print $NAsiento;
	
// 	 $sql1="INSERT INTO `Tesoreria`(Fecha,NombreCuenta, Cuenta, Debe, Haber,Observaciones,Usuario,Sucursal,NumeroAsiento)
// 	 VALUES ('{$Fecha}','{$Cuenta1}','{$NumeroCuenta1}','{$Debe1}','{$Haber1}','{$Observaciones}','{$Usuario}','{$Sucursal}','{$NAsiento}')"; 
  
//   if (($Debe1=='0')AND($Haber1=='0')){
// 	goto a;	
// 	}else{ 
// 	mysql_query($sql1);
// 	}

// 	$sql2="INSERT INTO `Tesoreria`(Fecha,NombreCuenta, Cuenta, Debe, Haber,Observaciones,Usuario,Sucursal,NumeroAsiento)
// 	 VALUES ('{$Fecha}','{$Cuenta2}','{$NumeroCuenta2}','{$Debe2}','{$Haber2}','{$Observaciones}','{$Usuario}','{$Sucursal}','{$NAsiento}')"; 
//  if (($Debe2=='0')AND($Haber2=='0')){
// 	goto a;	
// 	}else{ 
// 	mysql_query($sql2);
// 	}
	
// }

// if ($_GET['Eliminar']=='si'){
// $id=$_GET['id'];
// $sql="UPDATE Tesoreria SET Eliminado=1 WHERE id='$id'";
// mysql_query($sql);
// $NAsiento=$_GET['NA'];
// header('location:Contabilidad.php?IngresaAsientos=Si&NA='.$NAsiento);
// }	
// if ($_GET['BuscarAsiento']=='Aceptar'){
// $Asiento=$_GET['numeroasiento_t'];
  
// $ordenar="SELECT * FROM Tesoreria WHERE NumeroAsiento='$Asiento' AND Eliminado=0";	
// $MuestraTrans=mysql_query($ordenar);
// $numfilas = mysql_num_rows($MuestraTrans);
// if(($numfilas)==''){
 		

// 		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script> -->
// 		<script language="JavaScript" type="text/javascript"> -->
// 			alert("EL NUMERO DE ASIENTO CONTABLE NO EXISTE O ESTA ELIMINADO") -->
// 		</script> -->
 

// goto a;	
// }
// echo "<table class='login'>";
// echo "<caption>ASIENTO CONTABLE</caption>";
// // echo "<caption>$cliente</caption>";
// echo "<caption>Asiento contable número: $Asiento</caption>";		
// echo "<th>Asiento</th>";
// echo "<th>Fecha</th>";
// echo "<th>N Cuenta</th>";
// echo "<th>Cuenta</th>";
// echo "<th>Razon Social</th>";  
// echo "<th>Observaciones</th>";
// echo "<th>Debe</th>";
// echo "<th>Haber</th>";

// $numfilas =0;
// 	while($fila = mysql_fetch_array($MuestraTrans)){
//     if($fila[Pendiente]==1){
//     $font1='red'; 
//     }else{
//     $font1='black'; 
//     }

//     if($numfilas%2 == 0){
// 	echo "<tr style='color:$font1;background: #f2f2f2;' >";
// 	}else{
// 	echo "<tr style='color:$font1;background:$color2;' >";
// 	}	

// echo "<td>".$fila['NumeroAsiento']."</td>";

// $BuscarRazonSocial=mysql_query("SELECT RazonSocial FROM TransProveedores WHERE id =".$fila['idTransProvee']."");	
// $RazonSocial= mysql_fetch_array($BuscarRazonSocial);

// $fecha=$fila['Fecha'];
// $arrayfecha=explode('-',$fecha,3);
//     echo "<td>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
//     echo "<td>".$fila['Cuenta']."</td>";
//     echo "<td>".$fila['NombreCuenta']."</td>";
//     echo "<td>".$RazonSocial[RazonSocial]."</td>";  
//     echo "<td>".$fila['Observaciones']."</td>";
//     echo "<td> $ ".$fila['Debe']."</td>";
//     echo "<td> $ ".$fila['Haber']."</td></tr>";
// // 	echo "<td>".$fila['Cantidad']."</td></tr>";
//  	$numfilas++; 
// 	}
// $Asiento=$_GET['numeroasiento_t'];
// $ordenarTotal=mysql_query("SELECT SUM(Debe)as Debe,SUM(Haber)as Haber FROM Tesoreria WHERE NumeroAsiento='$Asiento' AND Eliminado='0'");	
//     $fila = mysql_fetch_array($ordenarTotal);
    
// 		$Total= money_format('%i',$fila[Debe]-$fila[Haber]);
//     echo "<tr align='right' style='font-size:14px;color:$font2;background:F2F2F2;' >";
//     echo "<td colspan='8'>Total Asiento: $Total</td></tr>";
//     echo "<tr>";	

//     echo "</tr></table>";

//     echo "<form action='Informes/AsientoContablepdf.php' method='GET' class='limpio' >";
//     echo "<div><input type='hidden' name='NR' value='$Asiento'></div>";
//     echo "<div><input type='submit' name='Aceptar' value='Imprimir' style=''></div>";
// //   echo "<div><input type='submit' name='Aceptar' value='Modificar' style=''></div>";
  
//   echo "</form>";
  
//   goto a;	
// }

// if ($_GET['LibroDiario']=='Si'){
// $cliente=$_GET['Cliente'];
// $FechaSeleccionada=$_GET['fecha'];	
// if ($FechaSeleccionada==""){	
// $FechaSeleccionada=date('Y-m-d');
// }

// $ordenar="SELECT * FROM Tesoreria WHERE Fecha='$FechaSeleccionada' AND Eliminado=0";	
	
// $MuestraTrans=mysql_query($ordenar);
// $numfilas = mysql_num_rows($MuestraTrans);
// echo "<table class='login'>";
// echo "<caption>LIBRO DIARIO</caption>";
// $Extender='15';		
// echo "<form action='' class='limpio'>";
// echo "<tr><td colspan='5'>Fecha:<input name='fecha' value='$FechaSeleccionada' type='date'>";
// echo "<input name='Aceptar' value='Aceptar' class='button' type='submit'>";
// echo "<input name='LibroDiario' value='Si' type='Hidden'></td></tr>";
// echo "</form>";	
// $_GET['fecha'];		
// echo "<th style='width:10px'>NAsiento</th>";
// echo "<th>Fecha</th>";
// echo "<th>Cuenta</th>";
// echo "<th>Nombre Cuenta</th>";
// echo "<th>Debe</th>";
// echo "<th>Haber</th>";
  
  
// $numfilas =0;
// 	while($fila = mysql_fetch_array($MuestraTrans)){
// 	if($numfilas%2 == 0){
// 	echo "<tr align='left' style='font-size:12px;color:$font1;background: #f2f2f2;' >";
// 	}else{
// 	echo "<tr align='left' style='font-size:12px;color:$font1;background:$color2;' >";
// 	}	

// echo "<td>".$fila['NumeroAsiento']."</td>";
// $Total= money_format('%i',$fila['Debe']);
// $fecha=$fila['Fecha'];
// $arrayfecha=explode('-',$fecha,3);
// 	echo "<td style='padding:8px;color=$font1'>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
// 	echo "<td>".$fila['Cuenta']."</td>";
// 	echo "<td>".$fila['NombreCuenta']."</td>";
// 	echo "<td>$ ".$fila['Debe']."</td>";
// 	echo "<td>$ ".$fila['Haber']."</td>";
//   echo "</form>";
//  	$numfilas++; 
// 	}
// $SumaTotales=mysql_query("SELECT SUM(Debe)as TotalDebe, SUM(Haber)as TotalHaber FROM Tesoreria WHERE Fecha='$FechaSeleccionada' AND Eliminado=0");
// $file=mysql_fetch_array($SumaTotales);
// $TotalDebe=$file[TotalDebe];	
// $TotalHaber=$file[TotalHaber];	

// echo "<tr align='left' style='margin-top:5px;background:$color; color:$font; font-size:16px;'>";
// echo "<th colspan='4'></th>";
// echo "<th>$ $TotalDebe</th>";
// echo "<th>$ $TotalHaber</th></tr>";
// echo "</tr></table>";
// goto a;	
// }

// if ($_GET['VerMayor']=='Aceptar'){
// 	//-----------------------------------DESDE ACA LISTADO DE MAYORES POR CUENTA--------------	
// // include("Menu/MenuImprimeExporta.php"); 	
// $DesdeCuenta=$_GET['desdecuenta_t'];
// $HastaCuenta=$_GET['hastacuenta_t'];
// $Desde00=$_GET['desde_t'];
// $Desde0=explode('-',$Desde00,3);
// $Desde=$Desde0[2]."/".$Desde0[1]."/".$Desde0[0];  
// $Hasta00=$_GET['hasta_t'];
// $Hasta0=explode("-",$Hasta00,3);
//   $Hasta=$Hasta0[2]."/".$Hasta0[1]."/".$Hasta0[0];
// $ColSpan=5;
// echo "<table class='login' >";

// if ($Cuenta=="Cheques de Terceros "){	
// echo "<caption>Mayor x Cuenta Cuenta desde cuenta: '$DesdeCuenta' hasta cuenta: '$HastaCuenta'</caption>";
// echo "<caption>Desde: $Desde Hasta: $Hasta</caption>";
// echo "<th>Fecha</th>";
// echo "<th>Cuenta</th>";
// echo "<th>Nombre Cuenta</th>";
// echo "<th>Banco</th>";
// echo "<th>Numero</th>";
// echo "<th>Fecha Cobro</th>";
// echo "<th>Debe</th>";
// echo "<th>Haber</th>";
//  $ordenar="SELECT * FROM Tesoreria WHERE Cuenta>='$DesdeCuenta' AND Cuenta<='$HastaCuenta' AND Fecha>='$Desde00' AND Fecha<='$Hasta00' AND Eliminado=0 ORDER BY Fecha ASC";	
//  $MuestraStock=mysql_query($ordenar);

// 	while($row=mysql_fetch_row($MuestraStock)){
// 	$fecha=$row[1];
// 	$arrayfecha=explode('-',$fecha,3);
// 	$Fecha2=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
// 	echo "<tr style='font-size:12px;'><td>$Fecha2</td>";
// 	echo "<td>$row[3]</td>";
// 	echo "<td>$row[2]</td>";
// 	echo "<td>$row[7]</td>";
// 	echo "<td>$row[9]</td>";
// 	echo "<td>$row[8]</td>";
// 	echo "<td>$row[4]</td>";
// 	echo "<td>$row[5]</td>";
// 	}
// $Saldo=mysql_query("SELECT SUM(Debe-Haber)as Total FROM Tesoreria WHERE Cuenta>='$DesdeCuenta' AND Cuenta<='$HastaCuenta' AND Fecha>='$Desde00' AND Fecha<='$Hasta00' AND Eliminado=0");	
// $row=mysql_fetch_array($Saldo);
// $SaldoTotal=number_format($row[Total],2);	
	
// 		if ($SaldoTotal>='0'){
// 			$colorsaldo=white;
// 		}else{
// 			$colorsaldo=red;
// 		}

// echo "</tr>";
// echo "<th>Saldo: $ $SaldoTotal</th>";
// echo "</table>";
	
// }else{

// echo "<caption>Mayor x Cuenta Cuenta desde cuenta: '$DesdeCuenta' hasta cuenta: '$HastaCuenta'</caption>";
// echo "<caption>Desde: $Desde Hasta: $Hasta</caption>";
// echo "<th>Fecha</th>";
// echo "<th>Asiento</th>";
// echo "<th>Cuenta</th>";
// echo "<th>Nombre Cuenta</th>";
// echo "<th>Observaciones</th>";
// echo "<th>Descripcion</th>";  
// echo "<th>Debe</th>";
// echo "<th>Haber</th>";
// echo "<th>Ver</th>";

// 	if($DesdeCuenta<$HastaCuenta){
// 	$ordenar="SELECT * FROM Tesoreria WHERE Cuenta>='$DesdeCuenta' AND Cuenta<='$HastaCuenta' AND Fecha>='$Desde00' AND Fecha<='$Hasta00' AND Eliminado=0 ORDER BY Fecha ASC";	
// 	}else{
// 	$ordenar="SELECT * FROM Tesoreria WHERE Cuenta>='$HastaCuenta' AND Cuenta<='$DesdeCuenta' AND Fecha>='$Desde00' AND Fecha<='$Hasta00' AND Eliminado=0 ORDER BY Fecha ASC";	
// 	}
 
// 	$MuestraStock=mysql_query($ordenar);

// 	while($row=mysql_fetch_row($MuestraStock)){
// 	if($row[18]==1){
//   $font2=red;  
//   }else{
//   $font2=black;    
//   }
//   $fecha=$row[1];
// 	$arrayfecha=explode('-',$fecha,3);
// 	$Fecha2=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
// 	echo "<tr style='font-size:12px;background:$color2; color:$font2'><td>$Fecha2</td>";
// 	echo "<td>$row[14]</td>";
// 	echo "<td>$row[3]</td>";
// 	echo "<td>$row[2]</td>";
// 	echo "<td>$row[6]</td>";
//   $sqlDescripcion=mysql_query("SELECT Descripcion FROM TransProveedores WHERE id='$row[11]'");
//   $sqlDescripcionresult=mysql_fetch_array($sqlDescripcion);
// 	echo "<td>$sqlDescripcionresult[Descripcion]</td>";
    
// 	echo "<td>$row[4]</td>";
// 	echo "<td>$row[5]</td>";
// 	echo "<td align='center'><a target='_blank' href='Informes/AsientoContablepdf.php?NR=".$row[14]."'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
// 	}

// $Saldo=mysql_query("SELECT SUM(Debe)as Debe, SUM(Haber)as Haber FROM Tesoreria WHERE Cuenta>='$DesdeCuenta' AND Cuenta<='$HastaCuenta' AND Fecha>='$Desde00' AND Fecha<='$Hasta00' AND Eliminado=0");	
// $row=mysql_fetch_array($Saldo);
// $TotalDebe=number_format($row[Debe],2);
// $TotalHaber=number_format($row[Haber],2);
// $Total=$row[Debe]-$row[Haber];  
// $SaldoTotal=number_format($Total,2);	

// 		if ($SaldoTotal>='0'){
// 			$colorsaldo=white;
// 		}else{
// 			$colorsaldo=red;
// 		}
// echo "</table>";
// // echo "<div id='pie' style='float:left'>";
// echo "<table class='login' >";
// echo "<th>Totales:</th><th>Debe</th><th>Haber</th><th>Saldo</th>";
// echo "<tr><td></td>
// <td>$ $TotalDebe</td>
// <td>$ $TotalHaber</td>
// <td>$ $SaldoTotal</td></tr>";
// echo "</table>";
// // echo "</div>";  
// }
// goto a;	
// }
// //---------------------------------HASTA ACA LISTADO DE MAYORES X CUENTA--------------------	
// if ($_GET['Mayores']=='Si'){
// echo "<form class='login' action='' method='get' style='width:500px'>";
// echo "<div><titulo>Movimientos por Fecha por Cuenta</titulos></div>";
//   echo"<div><hr></hr></div>";
// 	$Grupo="SELECT Cuenta,NombreCuenta,Nivel FROM PlanDeCuentas WHERE Nivel=4 ORDER BY Cuenta ASC";
// 	$estructura= mysql_query($Grupo);
// 	echo "<div><label>Desde Cuenta</label><select name='desdecuenta_t' style='width:310px;' size='0'>";
// 	while ($row = mysql_fetch_row($estructura)){
// 	echo "<option value='".$row[0]."'>".$row[1]."</option>";
// 	}
// 	echo "</select></div>";
// 	echo "<div><label>Hasta Cuenta</label><select name='hastacuenta_t' style='width:310px;' size='0'>";
// 	$estructura1= mysql_query($Grupo);
// 	while ($row = mysql_fetch_row($estructura1)){
// 	echo "<option value='".$row[0]."'>".$row[1]."</option>";
// 	}
// 	echo "</select></div>";

// echo "<div><label>Desde</label><input name='desde_t' size='16' type='date' value='' style='float:right' required/></div>";
// echo "<div><label>Hasta</label><input name='hasta_t' size='16' type='date' value='' style='float:right' required/></div>";
// echo "<div><input name='VerMayor' class='bottom' type='submit' value='Aceptar' ></div>";
// echo "</form>";
// goto a;	
// }	
// //----------------------------------------HASTA ACA MAYORES X CUENTA-------------------------------
// if ($_GET['IngresaAsientos']=='Si'){
// $_SESSION[Ventana]='';  
// 	$BuscaNumAsiento= mysql_query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria WHERE Eliminado='0'");
// 	if ($row = mysql_fetch_row($BuscaNumAsiento)) {
// 								if ($_GET['NA']==''){
// 								$NAsiento = trim($row[0])+1;
// 								}else{
// 								$NAsiento = $_GET['NA'];
// 								}
// 								}	
	
// $FechaTemporal=$_SESSION['FechaTemporalAsientos'];
// $BuscaAsientos= mysql_query("SELECT * FROM Tesoreria WHERE NumeroAsiento='$NAsiento' AND Pendiente=1 AND Eliminado=0");
// $numfilas = mysql_num_rows($BuscaAsientos);
// echo "<form name='MyForm' class='login' action='VentanaCargaAsientos.php?NA=$NAsiento' method='get'/>";
// echo "<div><titulo>Ingreso de Asientos Contables</titulo></div>";
// echo "<div><hr></hr></div>";
// echo "<div><label>Fecha:</label><input name='fecha_t' size='16' type='date' style='float:left;' value='$FechaTemporal' required/></div>";
// echo "<div><label>Numero de Asiento:</label><a style='color:black'>$NAsiento</a></div>";
// if(mysql_num_rows($BuscaAsientos)<>0){  
// echo "<div><label style='float:right'><a target='_blank' href='Informes/AsientoContablepdf.php?NR=$NAsiento'>Imprimir!</a></label></div>";
// }
//   echo "<div><input name='nasiento_t' size='10' type='hidden' value='$NAsiento'/></div>";
// echo "<div><input name='Cargar' style='width:150px' type='submit' value='Agregar Asiento'/></div>";

// $SumaAsiento="SELECT SUM(Debe-Haber)AS TotalAsiento FROM Tesoreria WHERE NumeroAsiento='$NAsiento' AND Eliminado=0 AND Pendiente=1";

// $SumaAsientoConsulta=mysql_query($SumaAsiento);
// $row=mysql_fetch_array($SumaAsientoConsulta);

// if($row[TotalAsiento]<>0 OR $numfilas==0){
// $activar='disabled';
// $stylo='opacity:0.5;filter:aplpha(opacity=50);';  
// }else{
// $activar='';  
// $stylo='';  
// }  
// echo "<div><input name='Cargar' style='$stylo' type='submit' value='Finalizar' $activar/></div></form>";
  
// echo "<table class='login' style='width:97%;margin-left:15px'>";
// echo "<th>Fecha</th>";
// echo "<th>Cuenta</th>";
// echo "<th>Nombre Cuenta</th>";
// echo "<th>Debe</th>";
// echo "<th>Haber</th>";
// echo "<th>Eliminar</th>";
// $BuscaAsientos= mysql_query("SELECT * FROM Tesoreria WHERE NumeroAsiento='$NAsiento' AND Pendiente=1 AND Eliminado=0");  

// 	while ($file = mysql_fetch_array($BuscaAsientos)){
// 	$numfilas = mysql_num_rows($BuscaAsientos);
// 	if($numfilas%2 == 0){
// 	echo "<tr style='font-size:12px;color:$font1;background: #f2f2f2;' >";
// 	}else{
// 	echo "<tr style='font-size:12px;color:$font1;background:$color2;' >";
// 	}	
// echo "<td style='padding:8px'>$file[Fecha]</td><td>$file[Cuenta]</td><td>$file[NombreCuenta]</td><td>$file[Debe]</td><td>$file[Haber]</td>";
// echo "<td align='center'><a class='img' href='Contabilidad.php?Eliminar=si&id=$file[0]&NA=$NAsiento'><img src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:center;'></a></td></tr>";    
  
// }	
// $SumaAsiento="SELECT SUM(Debe-Haber)AS TotalAsiento FROM Tesoreria WHERE NumeroAsiento='$NAsiento' AND Eliminado=0 AND Pendiente=1";

// $SumaAsientoConsulta=mysql_query($SumaAsiento);
// $row=mysql_fetch_array($SumaAsientoConsulta);
// setlocale(LC_ALL,'es_AR');
// $Total=money_format('%i',$row[TotalAsiento]);
// // echo "</table>";	
// echo "<tr align='right' style='background:$color; color:$font; font-size:16px;'>";
// echo "<td colspan='6'>Total Asiento: $Total</td></tr></table>";

// }

// if ($_GET['ConsxNAsiento']=='Si'){
// echo "<form class='login' action='' method='GET' style='float:center; width:500px;'>";
// echo "<div><titulo>Movimientos por Numero de Asiento</titulos></div>";
//   echo"<div><hr></hr></div>";
// echo "<div><label>Numero de Asiento:</label><input name='numeroasiento_t' size='20' type='text' style='float:right;' value='' required/></div>";
// echo "<div><input name='BuscarAsiento' class='bottom' type='submit' value='Aceptar'></label></div>";
// echo "</form>";	
// goto a;
// }
	
// a:
// echo "</div>"; // principal
// echo "</div>"; //cuerpo
// echo "</div>";  //contenedor
  
// // ob_end_flush();	

