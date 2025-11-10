<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
$Usuario=$_SESSION[Usuario];
setlocale(LC_ALL,'es_AR');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
<script src="../js/notification.js" type="text/javascript"></script>
</head>
  <script>
function sumar(){
var n1 = parseFloat(document.Presupuestos.cantidad_p.value); 
var n2 = parseFloat(document.Presupuestos.precio_p.value); 
var n3 = parseFloat(document.Presupuestos.iva_p.value);
var subtotal=(n1*n2);
var total=((n1*n2)*(n3/100))+(n1*n2);   
document.Presupuestos.subtotal_p.value=subtotal.toFixed(2); 
document.Presupuestos.total_p.value=total.toFixed(2);
}
</script> 
  
<?php
include("../Alertas/alertas.html");
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
include("Menu/MenuLateralOrdenDeCompra.php"); 	
echo "</div>"; //lateral
//        	        echo "<div id='otrolado'>"; 
//     include("Menu/MenuLateralProcedimientos.php"); 	
//   echo "</div>"; //lateral
  
   echo  "<div id='principal' style='margin-left:10%;width:80%;' >";
$sqlTabla=mysql_query("SELECT * FROM Presupuestos WHERE idOrden='".$_GET[OC]."'");  
$CantPresupuestos=mysql_num_rows($sqlTabla);
  ?>
<script>
function presupuestos(){
  var vent = document.Ordenes.exito.value;
//   var vent2 = new String("Aprobar Orden");
  var vent2= document.Ordenes.Ventana.value;
  var n1= document.Ordenes.presupuestos_t.value;
  var n2= "<?php echo $CantPresupuestos;?>";
  var n3= n1-n2;
alert(vent2);
    if (vent == 1){
      if (n1 != n2){ 
      alertify.error("Faltan " + n2 + " de " + n1 + " Presupuestos");
      return false; //si haces un return false no se enviara el formulario, caso contrario si haces un return true si se enviara
    }
  }
}  
  </script>
<?  
setlocale(LC_ALL,'es_AR');
$color='#B8C6DE';
$font='white';
$color2='white';
$font2='black';
$Pregunta=base64_encode(Cargar);
$Respuesta=base64_encode(Si);
$Pregunta1=base64_encode(Ver);
  $Respuesta1=base64_encode(Cargada);
  $Respuesta1_1=base64_encode(Aceptada);
  $Respuesta1_2=base64_encode(Rechazada);
  $Respuesta1_3=base64_encode(Autorizada);
  $Respuesta1_4=base64_encode(Cerrada);
$Pregunta2=base64_encode(Ver);
$Respuesta2=base64_encode(Presupuestos);
$Pregunta3=base64_encode(OrdenDeCompra);

if($_GET[Aviso]=='Cnl'){
  ?>
  <script>
		alertify.error("Operacion Cancelada");
  </script>
  <?
}elseif($_GET[Aviso]=='Ok'){
  ?>
  <script>
		alertify.success("Orden Cargada con exito!");
  </script>
  <?
}elseif($_GET[Presupuesto]=='Ok'){  
  ?>
  <script>
		alertify.success("Presupuesto cargado con exito!");
  </script>
  <?
 unset($_POST[Presupuesto]);   
  }

if($_POST[Presupuesto]=='Aceptar'){
  $Fecha=date('Y-m-d');
  $idOrden=$_POST[id_o];
  $Proveedor=$_POST[proveedor_p];
  $Descripcion=$_POST[descripcion_p];
  $Cantidad=$_POST[cantidad_p];
  $Observaciones=$_POST[observaciones_p];
  $Precio=$_POST[precio_p];
  $Iva=$_POST[iva_p];
  $Total=$_POST[total_p];
  $FormaDePago=$_POST[formadepago_p];
  
$sql=mysql_query("INSERT INTO Presupuestos 
(Fecha,idOrden,Proveedor,Descripcion,FormaDePago,Cantidad,Observaciones,Precio,Iva,Total,Usuario)
VALUES ('{$Fecha}','{$idOrden}','{$Proveedor}','{$Descripcion}','{$FormaDePago}','{$Cantidad}','{$Observaciones}','{$Precio}','{$Iva}','{$Total}','{$Usuario}')");

  include('../Admin/subir.php');
  
  if($sql){
  $sqlUltimoPresupuesto=mysql_query("SELECT MAX(id)as id FROM Presupuestos");
  $UP=mysql_fetch_array($sqlUltimoPresupuesto);
  $asunto='Se cargo un Presupuesto';
  $id=$UP[id];
  $mensaje='agregado';
  header("location:EnviarMail.php?asunto=$asunto&id_presupuesto=$id&mensaje=$mensaje&procedimiento=PO");

  }else{
  ?>
  <script>
			alertify.error("El Presupuesto No se cargo");
  </script>
  <?
  }
  
}  

  
  if(($_GET[OC])or($_POST[OC])){
    if($_GET[OC]){
    $idOC=$_GET[OC];
    }else{
    $idOC=$_POST[OC];  
    }
    
$sql=mysql_query("SELECT * FROM OrdenesDeCompra WHERE id='$idOC'");
$sqlRespuesta=mysql_fetch_array($sql);
  
if(($sqlRespuesta[Estado]==Cargada)||  
  ($sqlRespuesta[Estado]==Aceptada)){
$Modificar='';  
$Select='false';  
}elseif(($sqlRespuesta[Estado]==Rechazada)||
       ($sqlRespuesta[Estado]==Cerrada)){
$Modificar='readonly';
$Select='true';    
}
  
echo "<form class='login' name='Ordenes' action='VOrden.php' method='post' style='width:95%'>";
echo "<div><titulo>Editar Orden de Compra</titulo></div>";
echo "<div><hr></hr></div>";
echo "<div><label>id:</label><input name='id_t' type='text' value='$sqlRespuesta[id]' style='width:300px;'/></div>";
echo "<div><label>Fecha:</label><input name='fecha_t' value='$sqlRespuesta[Fecha]' type='text' style='width:300px;'readonly/></div>";
echo "<div><label>Titulo:</label><input type='text' name='titulo_t' maxlength='100' style='width:300px;' value='$sqlRespuesta[Titulo]' placeholder='Ej.: Compra de Repuestos' $Modificar/></div>";
echo "<div><label>Tipo de Orden:</label><select name='tipodeorden_t' style='width:300px;'/>";
$Sql=mysql_query("SELECT NombreCuenta FROM PlanDeCuentas WHERE OrdenesDeCompra=1 ORDER BY NombreCuenta DESC");
echo "<option value='$sqlRespuesta[TipoDeOrden]'>$sqlRespuesta[TipoDeOrden]</option>";

while($row=mysql_fetch_array($Sql)){
  echo "<option value='$row[NombreCuenta]'>$row[NombreCuenta]</option>";
}
echo "</select></div>";
echo "<div><label>Motivo:</label><input type='text' name='motivo_t' value='$sqlRespuesta[Motivo]' maxlength='100' style='width:300px;' placeholder='Ej.: Stock minimo' $Modificar></div>";
echo "<div><label>Precio Maximo:</label><input name='precio_t' type='number' value='$sqlRespuesta[Precio]' step='.01' style='width:300px;' placeholder='Si no se conoce dejar en 0' readonly/></div>";
echo "<div><label>Presupuestos Solicitados:</label><input name='presupuestos_t' type='number' value='$sqlRespuesta[Presupuestos]' step='.01' style='width:300px;' placeholder='Si no se conoce dejar en 0'readonly/></div>";
echo "<div><label>Fecha de la Compra:</label><input name='fechadeorden_t' value='$sqlRespuesta[FechaCompra]' type='date' style='width:300px;' readonly/></div>";
echo "<div><label>Observaciones:</label><textarea name='observaciones_t' rows='5' cols='140' readonly>$sqlRespuesta[Observaciones]</textarea></div>";
echo "<div><label>Estado:</label><input name='estado_t' type='text' value='$sqlRespuesta[Estado]' style='width:300px;'readonly/></div>";
echo "<div><input class='submit' name='Ventana' type='submit' style='width:150px;margin-right:4px;' value='Grabar'></div>";  
if($_SESSION[Nivel]==1){
echo "<div><input class='submit' name='Ventana' type='submit' style='width:150px;margin-right:4px;' value='Rechazar Orden'>";  
echo "<input class='submit' name='Ventana' type='submit' style='width:150px;background:#F1C40F;margin-right:4px;' value='Observar Orden'>";

  if($sqlRespuesta[Estado]=='Aceptada'){ 
      if($CantPresupuestos<$sqlRespuesta[Presupuestos]){
      $activar='disabled';
      $stylo='opacity:0.5;filter:aplpha(opacity=50);';  
      }else{
      $activar='';  
      $stylo='';  
      }  
  echo "<input class='submit' name='Ventana' type='submit' style='width:150px;background:#27AE60;margin-right:4px;$stylo' value='Aprobar Orden' $activar></div>";
  }else{
      if($sqlRespuesta[Estado]<>'Aprobada'){
      echo "<input class='submit' name='Ventana' type='submit' style='width:150px;background:#27AE60;margin-right:4px;' value='Aceptar Orden'></div>";  
      }else{
      echo "</div>"; 
      }
    }
}
  
echo "</form>";
$sqlTabla=mysql_query("SELECT * FROM Presupuestos WHERE idOrden='".$_GET[OC]."' AND Eliminado=0");  
$CantPresupuestos=mysql_num_rows($sqlTabla);

if($CantPresupuestos=='0'){
goto a;
}    
echo "<table class='login' style='width:97%;margin-left:16px'>";
  echo "<caption>Presupuestos Relacionados</caption>";
  echo "<th>id</th>";
  echo "<th>Fecha</th>";
  echo "<th>Proveedor</th>";
  echo "<th>Descripcion</th>";
  echo "<th>Forma de Pago</th>";
  echo "<th>Cantidad</th>";
  echo "<th>Total</th>";
  echo "<th>Usuario</th>";   
  echo "<th>Adjunto</th>";   

while($sqlTablaRespuesta=mysql_fetch_array($sqlTabla)){
 if($sqlTablaRespuesta[Aprobado]==1){
  echo "<tr style='color:white;background: #27AE60;' >";
 }elseif($numfilas%2 == 0){
  echo "<tr style='color:$font1;background: #f2f2f2;' >";
  }else{
  echo "<tr style='color:$font1;background:$color2;' >";
  }	
//   echo "<tr>";
  echo "<td>$sqlTablaRespuesta[id]</td>";
  echo "<td>$sqlTablaRespuesta[Fecha]</td>";
  echo "<td>$sqlTablaRespuesta[Proveedor]</td>";
  echo "<td>$sqlTablaRespuesta[Descripcion]</td>";
  echo "<td>$sqlTablaRespuesta[FormaDePago]</td>";
  echo "<td>$sqlTablaRespuesta[Cantidad]</td>";
  echo "<td>$sqlTablaRespuesta[Total]</td>";
  echo "<td>$sqlTablaRespuesta[Usuario]</td>";
  

  $nombre="../Presupuestos/OP".$sqlRespuesta[id]."P".$sqlTablaRespuesta[id].".pdf";
  
  if(is_file($nombre)==true){
//   echo "<td>Si</td>";  
  echo "<td align='center'><a href='../Presupuestos/$nombre' target='t_blank'><input type='image' src='../images/botones/Factura.png' width='20' height='20' border='0' style='float:center;'></td>";
  }else{
    
  echo "<td></td>";  
  }
  echo "</tr>";
$numfilas++;
} 
  echo "</table>";
goto a;    
// header("location:OrdenDeCompra.php?$Pregunta1=$Respuesta1");
}  
  
  
if($_POST[OrdenDeCompra]=='Aceptar'){
$Fecha=date('Y-m-d');
$TipoDeOrden=$_POST[tipodeorden_t];
  $Motivo=$_POST[motivo_t];
  $Precio=$_POST[precio_t];
  $FechaOrden=$_POST[fechadeorden_t];
  $Observaciones=$_POST[observaciones_t];
  $Estado="Cargada";
  $Aprobado="0";
  $Titulo=$_POST[titulo_t];
$sql=mysql_query("INSERT INTO OrdenesDeCompra (Fecha,TipoDeOrden,Titulo,Motivo,Precio,FechaOrden,Estado,Aprobado,Observaciones,UsuarioCarga)
VALUES ('{$Fecha}','{$TipoDeOrden}','{$Titulo}','{$Motivo}','{$Precio}','{$FechaOrden}','{$Estado}','{$Aprobado}','{$Observaciones}','{$Usuario}')");
  if($sql){
  $asunto='Se dio el alta una Orden de Compra';
  $id=$_POST[id_t];
  $mensaje='generado';
    header("location:EnviarMail.php?asunto=$asunto&id_orden=$id&mensaje=$mensaje&procedimiento=OC");
//   header("location:OrdenDeCompra.php");
  }else{
  ?>
  <script>
			alertify.error("La Orden de Compra No se cargo");
  </script>
  <?
    

  }
}  
if($_GET[$Pregunta]==$Respuesta){
    $idoferta= mysql_query("SELECT MAX(id) AS id FROM OrdenesDeCompra");
    if ($row = mysql_fetch_row($idoferta)) {
     $id = trim($row[0])+1;
     }
 $Fecha=date('d/m/Y');
  echo "<form class='login' action='' method='post' style='width:95%'><div>";
echo "<div><titulo>Agregar Nueva Orden de Compra</titulo></div>";
echo "<div><hr></hr></div>";
echo "<div><label>id:</label><input name='id_t' type='text' value='$id' style='width:300px;'/></div>";
echo "<div><label>Fecha:</label><input name='fecha_t' value='$Fecha' type='text' style='width:300px;' readonly/></div>";
echo "<div><label>Titulo:</label><input type='text' name='titulo_t' maxlength='100' style='width:300px;' placeholder='Ej.: Compra de Repuestos' required/></div>";
echo "<div><label>Tipo de Orden:</label><select name='tipodeorden_t' style='width:300px;'required/>";
$Sql=mysql_query("SELECT NombreCuenta FROM PlanDeCuentas WHERE OrdenesDeCompra=1 ORDER BY NombreCuenta DESC");
echo "<option value=''>Seleccione una Opcion</option>";

while($row=mysql_fetch_array($Sql)){
  echo "<option value='$row[NombreCuenta]'>$row[NombreCuenta]</option>";
}
echo "</select></div>";
echo "<div><label>Motivo:</label><input type='text' name='motivo_t' maxlength='100' style='width:300px;' placeholder='Ej.: Stock minimo' required></div>";
echo "<div><label>Precio Estimado:</label><input name='precio_t' type='number' step='.01' style='width:300px;' placeholder='Si no se conoce dejar en 0'/></div>";
echo "<div><label>Fecha de la Compra:</label><input name='fechadeorden_t' type='date' style='width:300px;'/></div>";
echo "<div><label>Observaciones:</label><textarea name='observaciones_t' rows='4' cols='130'></textarea></div>";

  echo "<div><input class='submit' name='OrdenDeCompra' type='submit' value='Aceptar'></div>";
echo "</form>";
goto a;
}
b:
  
if(($_GET[$Pregunta1]==$Respuesta1)||
   ($_POST[$Pregunta1]==$Respuesta1)||
   ($_GET[$Pregunta1]==$Respuesta1_1)||
   ($_GET[$Pregunta1]==$Respuesta1_2)||
   ($_GET[$Pregunta1]==$Respuesta1_3)||
    ($_GET[$Pregunta1]==$Respuesta1_4)){
  if($_POST[desde_t]==''){  
  echo "<form class='login' action='' method='post' style='max-width:800px'>";
  echo "<div><titulo>Ver Ordenes de Compra </titulos></div>";
  echo"<div><hr></hr></div>";
  echo "<div><label>Desde</label><input name='desde_t' size='16' type='date' value='' style='float:right' required/></div>";
  echo "<div><label>Hasta</label><input name='hasta_t' size='16' type='date' value='' style='float:right' required/></div>";
  echo "<input type='hidden' name='$Pregunta1' value='$Respuesta1'>";
  echo "<div><input name='buscar' class='bottom' type='submit' style='width:110px' value='Aceptar' ></div>";
  // echo "<div><input name='Descargar' class='bottom' type='submit' style='width:110px' value='Descargar' ></div>";
  echo "</form>";
  goto a;	
  }	
  
$Desde=$_POST[desde_t];
$Hasta=$_POST[hasta_t];  
  $Desde3=explode('-',$Desde,3);
  $Desde2=$Desde3[2]."/".$Desde3[1]."/".$Desde3[0];
  $Hasta3=explode('-',$Hasta,3);
  $Hasta2=$Hasta3[2]."/".$Hasta3[1]."/".$Hasta3[0];


  if($_GET[$Pregunta1]==$Respuesta1){
          $ordenar="SELECT * FROM OrdenesDeCompra WHERE Eliminado =0 AND Fecha>='$Desde' AND Fecha<='$Hasta' AND Estado='Cargada' ORDER BY Fecha ";
  }  
  if($_GET[$Pregunta1]==$Respuesta1_1){
          $ordenar="SELECT * FROM OrdenesDeCompra WHERE Eliminado =0 AND Fecha>='$Desde' AND Fecha<='$Hasta' AND Estado='Aceptada' ORDER BY Fecha ";
  }
  if($_GET[$Pregunta1]==$Respuesta1_2){
          $ordenar="SELECT * FROM OrdenesDeCompra WHERE Eliminado =0 AND Fecha>='$Desde' AND Fecha<='$Hasta' AND Estado='Rechazada' ORDER BY Fecha ";
  }
  if($_GET[$Pregunta1]==$Respuesta1_3){
          $ordenar="SELECT * FROM OrdenesDeCompra WHERE Eliminado =0 AND Fecha>='$Desde' AND Fecha<='$Hasta' AND Estado='Autorizada' ORDER BY Fecha ";
  }
  if($_GET[$Pregunta1]==$Respuesta1_4){
          $ordenar="SELECT * FROM OrdenesDeCompra WHERE Eliminado =0 AND Fecha>='$Desde' AND Fecha<='$Hasta' AND Estado='Cerrada' ORDER BY Fecha ";
  }
  
    
          $MuestraOrdenes=mysql_query($ordenar);
 		      $numfilas = mysql_num_rows($MuestraOrdenes);
  if($numfilas==0){
  ?>
  <script>
  alertify.error("No hay datos en las Fechas solicitadas");
  </script>
  <?
goto a;
  }

    echo "<table class='login'>";
    echo "<caption>Ordenes de Compra Desde el $Desde2 Hasta el $Hasta2</caption>";
    echo "<th>id</th>";
    echo "<th>Fecha</th>";
    echo "<th>Tipo de Orden</th>";
    echo "<th>Motivo</th>";
    echo "<th>Precio Máximo</th>";
    echo "<th>Fecha Compra</th>";
    echo "<th>Observaciones</th>";
    echo "<th>Estado</th>";
    // 					echo "<th>Aprobado</th>";
    echo "<th>Editar</th>";

  
  while($row=mysql_fetch_array($MuestraOrdenes)){
			     if($numfilas%2 == 0){
            echo "<tr style='color:$font1;background: #f2f2f2;' >";
            }else{
            echo "<tr style='color:$font1;background:$color2;' >";
            }	
						$id=$row[id];

            $Fecha2=$row[Fecha];
					  $Fecha1=explode('-',$Fecha2,3);
						$Fecha=$Fecha1[2]."/".$Fecha1[1]."/".$Fecha1[0];

            $FechaOrden2=$row[FechaOrden];
					  $FechaOrden1=explode('-',$FechaOrden2,3);
						$FechaOrden=$FechaOrden1[2]."/".$FechaOrden1[1]."/".$FechaOrden1[0];
//             if($row[Aprobado]==0){
//             $Aprobado='Pendiente';  
//             }else{
//             $Aprobado='Aprobado';  
//             }
            echo "<td>$id</td>";
            echo "<td>$Fecha</td>";
						echo "<td>$row[TipoDeOrden]</td>";
      	  	echo "<td>$row[Motivo]</td>"; 
  					echo "<td align='right'>$ ".number_format($row[Precio],2,",",".")."</td>";
 						echo "<td>$FechaOrden</td>";
						echo "<td style='max-width:60px'>$row[Observaciones]</td>";
            echo "<td>$row[Estado]</td>";
// 						echo "<td>$Aprobado</td>";
            $Pregunta3=base64_encode(OrdenDeCompra);
            $Respuesta3=base64_encode($id);
						echo "<td align='center'><a target='' href='OrdenDeCompra.php?OC=$id'><input type='image' src='../images/botones/lapiz.png' width='12' height='12' border='0' style='float:center;'></td>";
			
//             $ruta = "../FacturasCompra/" . $row[id].".pdf";				
// 						if ($row[8]>0){
// 						echo "<td align='center'><a target='_blank' href='OrdenDePagopdf.php?Factura=$row[0]'><input type='image' src='../images/botones/Factura.png' width='12' height='12' border='0' style='float:center;'></td>";
// 						}elseif (file_exists($ruta)){
//             //Compruebo que exista el comprobante  
//             echo "<td align='center'><a target='_blank' href='../FacturasCompra/$row[5].pdf'><input type='image' src='../images/botones/Factura.png' width='12' height='12' border='0' style='float:center;'></td>";
//             }else{
//             echo "<td></td>";  
//             }

//  				echo "<td align='center'><a href='OrdenDePagopdf.php?Factura=Eliminar&NR=$NumComp'><input type='image' src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:center;'></td>";
           	$numfilas++; 

						}
					echo "</tr>";
// 					$Suma=mysql_query("SELECT SUM(Debe)as Suma FROM TransProveedores WHERE Cuit='$Cuit' AND Eliminado=0");
// 					$SumaDebe=mysql_result($Suma,0);
//           $SumaDebeF=number_format($SumaDebe,2);
// 					$SumaH=mysql_query("SELECT SUM(Haber)as Suma FROM TransProveedores WHERE Cuit='$Cuit' AND Eliminado=0");
// 					$SumaHaber=mysql_result($SumaH,0);
//           $SumaHaberF=number_format($SumaHaber,2);
// 					$Total=number_format($SumaDebe-$SumaHaber,2);
// 					echo "<th colspan='5'></th>";
// 					echo "<th align='right' style='font-size:16px'>$ $SumaDebeF</th>";
// 					echo "<th align='right' style='font-size:16px'>$ $SumaHaberF</th><th>Saldo: $ $Total</th>";
          echo "</tr></table>";

  // 					echo "</form>";
					goto a;

}  
if(($_GET[$Pregunta2]==$Respuesta2)||($_POST[$Pregunta2]==$Respuesta2)){

  if($_POST[numerodeorden_t]==''){  
      
  echo "<form class='login' action='' method='post' style='width:500px'>";
  echo "<div><titulo>Cargar Presupuesto </titulos></div>";
  echo"<div><hr></hr></div>";
  echo "<div><label>Numero de Orden de Compra:</label><input id='numerodeorden' name='numerodeorden_t' size='16' type='text' value='' style='float:right' Onblur='comprobar()' required/></div>";
  echo "<input type='hidden' name='$Pregunta2' value='$Respuesta2'>";
  echo "<div><input name='buscar' class='bottom' type='submit' style='width:110px' value='Aceptar' ></div>";
  // echo "<div><input name='Descargar' class='bottom' type='submit' style='width:110px' value='Descargar' ></div>";
  echo "</form>";
  goto a;	
  }else{
//   SI EL NUMERO DE ORDEN NO ES NULL COMPROBAMOS QUE EXISTA LA ORDEN  
  $sqlBuscarOC=mysql_query("SELECT * FROM OrdenesDeCompra WHERE id='$_POST[numerodeorden_t]' AND Eliminado='0'");
        if(mysql_num_rows($sqlBuscarOC)<>0){
        $Dato=mysql_fetch_array($sqlBuscarOC);
        if($Dato[Estado]=='Cargada'){
         ?>
          <script>
          alertify.error("La Orden de Compra No esta Aceptada");
          </script>
          <?
         goto a; 
        }elseif($Dato[Estado]=='Rechazada'){
         ?>
          <script>
          alertify.error("La Orden de Compra esta Rechazada");
          </script>
          <?
         goto a; 
        }elseif($Dato[Estado]=='Cerrada'){
         ?>
          <script>
          alertify.error("La Orden de Compra esta Cerrada");
          </script>
          <?
         goto a; 
        }elseif($Dato[Estado]=='Aprobada'){
         ?>
          <script>
          alertify.error("La Orden de Compra ya se encuentra Aprobada");
          </script>
          <?
         goto a; 
        }
        $Fecha=date('d/m/Y');
        $idPresupuesto= mysql_query("SELECT MAX(id) AS id FROM Presupuestos");
        if ($row = mysql_fetch_row($idPresupuesto)) {
         $id = trim($row[0])+1;
         }
          
        $idOrden=$_POST[numerodeorden_t];
        echo "<form name='Presupuestos' class='login' action='' method='post' style='width:95%' enctype='multipart/form-data'>";
        echo "<div><titulo>Datos de la Orden de Compra</titulo></div>";
        echo "<div><hr></hr></div>";
        $FechaOrden1=explode('-',$Dato[Fecha],3);
        $FechaOrden=$FechaOrden1[2]."/".$FechaOrden1[1]."/".$FechaOrden1[0];
        echo "<div><label>Fecha:</label><input name='fecha' value='$FechaOrden' type='text' style='width:300px;' readonly/></div>";
        echo "<div><label>id Orden de Compra:</label><input name='id_o' type='text' value='$idOrden' style='width:300px;'/></div>";
        echo "<div><label>Tipo de Orden:</label><input name='tipodeorden' type='text' value='$Dato[TipoDeOrden]' style='width:300px;'/></div>";
        echo "<div><label>Precio:</label><input name='precio' type='text' value='$Dato[Precio]' style='width:300px;'/></div>";
        echo "<div><label>Fecha de Compra:</label><input name='fechadecompra' type='text' value='$Dato[FechaOrden]' style='width:300px;'/></div>";
        echo "<div><hr></hr></div>";
 
        echo "<div><titulo>Agregar Nuevo Presupuesto</titulo></div>";
        echo "<div><hr></hr></div>";
        echo "<div><label>id:</label><input name='id_p' type='text' value='$id' style='width:300px;'/></div>";
        echo "<div><label>Fecha:</label><input name='fecha_p' value='$Fecha' type='text' style='width:300px;' readonly/></div>";
        echo "<div><label>Proveedor:</label><select name='proveedor_p' style='width:300px;'required/>";
        $Sql=mysql_query("SELECT RazonSocial FROM Proveedores ORDER BY RazonSocial asc");
        echo "<option value=''>Seleccione una Opcion</option>";
        while($row=mysql_fetch_array($Sql)){
        echo "<option value='$row[RazonSocial]'>$row[RazonSocial]</option>";
        }
        echo "</select></div>";
        echo "<div><label>Descripcion:</label><input type='text' name='descripcion_p' maxlength='100' style='width:300px;' required/></div>";
        echo "<div><label>Forma de Pago:</label><input type='text' name='formadepago_p' maxlength='100' style='width:300px;' required/></div>";
        echo "<div><label>Cantidad:</label><input type='number' name='cantidad_p' value='0' maxlength='100' style='width:100px;' Onblur='sumar()' required></div>";
        echo "<div><label>Precio:</label><input name='precio_p' type='number' value='0' step='.01' style='width:100px;'Onblur='sumar()' /></div>";
        echo "<div><label>Sub Total:</label><input  name='subtotal_p' value='0' tabindex='-1' type='number' step='.01' style='width:100px;' readonly/></div>";
        echo "<div><label>Iva (%):</label><input  name='iva_p' type='number' step='.01' value='0' style='width:100px;'Onblur='sumar()' placeholder='%' /></div>";
        echo "<div><label>Total:</label><input id='total_p' value='0' name='total_p' tabindex='-1' type='number' step='.01' style='width:100px;' readonly/></div>";
        
//         echo "<form class='login' action='subir.php' method='POST' style='width:50%' enctype='multipart/form-data'>";
//         echo "<div><titulo>Cargar Factura de Venta AFIP:</titulo></div>";
//         echo "<div><hr></hr></div>";
        echo "<div><input type='file' name='imagen' id='imagen' /></div>";
        echo "<input type='hidden' name='carpeta' value='Presupuestos'>";
          
        echo "<div><label>Observaciones:</label><textarea name='observaciones_p' rows='4' cols='120'></textarea></div>";
        echo "<div><input class='submit' name='Presupuesto' type='submit' value='Aceptar'></div>";
        echo "</form>";
        goto a;
        }else{
          ?>
          <script>
          alertify.error("La Orden de Compra No se encontro");
          </script>
          <?
          $_POST[numerodeorden_t]=='';
//           header('Location:OrdenDeCompra.php');
          }  
    }	
  
  
}
  
// echo "<table style='margin-top:40px'><tr>";  
// echo "<td><a href='OrdenDeCompra.php?$Pregunta1=$Respuesta1' class='boton_fondo'>Ordenes Cargadas</a></td>";
// echo "<td><a href='OrdenDeCompra.php?$Pregunta1=$Respuesta1_1' class='boton_fondo'>Ordenes Aceptadas</a></td>";
// echo "<td><a href='OrdenDeCompra.php?$Pregunta1=$Respuesta1_3' class='boton_fondo'>Ordenes Aprobadas</a></td>";
// echo "</tr></table>";
  
// goto a;  
// DESDE ACA TODAS LAS ORDENES DE COMPRA 
            echo "<table class='login' >";
            echo "<caption>Ordenes de Compra</caption>";
            echo "<th>Estado</th>";
            echo "<th>id</th>";
            echo "<th>Fecha</th>";
            echo "<th>Tipo de Orden</th>";
            echo "<th>Motivo</th>";
            echo "<th>Precio Máximo</th>";
            echo "<th>Fecha Compra</th>";
            echo "<th>Estado</th>";
            echo "<th>Codigo Aprobacion</th>";
            echo "<th>Editar</th>";

          $ordenar="SELECT * FROM OrdenesDeCompra WHERE Eliminado =0 ORDER BY Fecha ";
    
          $MuestraOrdenes=mysql_query($ordenar);
 		      $numfilas = mysql_num_rows($MuestraOrdenes);
  
          while($row=mysql_fetch_array($MuestraOrdenes)){
			     if($numfilas%2 == 0){
            echo "<tr style='color:$font1;background: #f2f2f2;' >";
            }else{
            echo "<tr style='color:$font1;background:$color2;' >";
            }	
            $id=$row[id];

            $Fecha2=$row[Fecha];
            $Fecha1=explode('-',$Fecha2,3);
            $Fecha=$Fecha1[2]."/".$Fecha1[1]."/".$Fecha1[0];

            $FechaOrden2=$row[FechaOrden];
            $FechaOrden1=explode('-',$FechaOrden2,3);
            $FechaOrden=$FechaOrden1[2]."/".$FechaOrden1[1]."/".$FechaOrden1[0];
            if($row[Observada]==1){
            $Observada="<input type='image' src='../images/botones/circuloyellow.png' width='20' height='20' border='0' style='float:left;margin-left:2px;'>";      
            }else{
            $Observada='';  
            }
            if($row[Estado]=='Aprobada'){
            echo "<td align='center'><input type='image' src='../images/botones/circulogreen.png' width='20' height='20' border='0' style='float:left;'>$Observada</td>";      
            }elseif($row[Estado]=='Rechazada'){
            echo "<td align='center'><input type='image' src='../images/botones/circulored.png' width='20' height='20' border='0' style='float:left;'>$Observada</td>";
            }elseif($row[Estado]=='Cargada'){
            echo "<td align='center'><input type='image' src='../images/botones/circulowhite.png' width='20' height='20' border='0' style='float:left;'>$Observada</td>";
            }elseif($row[Estado]=='Aceptada'){
            echo "<td align='center'><input type='image' src='../images/botones/circuloorange.png' width='20' height='20' border='0' style='float:left;'>$Observada</td>";
            }
            
            echo "<td>$id</td>";
            echo "<td>$Fecha</td>";
            echo "<td>$row[TipoDeOrden]</td>";
            echo "<td>$row[Motivo]</td>"; 
            echo "<td align='right'>$ ".number_format($row[Precio],2,",",".")."</td>";
            echo "<td>$FechaOrden</td>";
            echo "<td>$row[Estado]</td>";
            $Pregunta3=base64_encode(OrdenDeCompra);
            $Respuesta3=base64_encode($id);
            echo "<td>$row[CodigoAprobacion]</td>";			
            echo "<td align='center'><a target='' href='OrdenDeCompra.php?OC=$id'><input type='image' src='../images/botones/lapiz.png' width='12' height='12' border='0' style='float:center;'></td>";
           	$numfilas++; 

						}
					echo "</tr>";
          echo "</tr></table>";
					goto a;
  
  
a:
  ?>