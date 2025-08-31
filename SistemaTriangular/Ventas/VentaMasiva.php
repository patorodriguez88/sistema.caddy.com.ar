<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
if($_GET[start]=='clean'){
unset($_SESSION['idClienteRelacion']);
 }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script> 
</head>
<?php
echo "<div id='contenedor'>"; 
  echo "<div id='cabecera'>"; 
  include("../Menu/MenuGestion.php"); 
  echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
// echo "<div id='lateral'>";
//   include("Menu/MenuLateral.php"); 
  
// echo "</div>"; //lateral
echo  "<div id='principal'>";

  $color='#B8C6DE';
$font='white';
$color1='white';
$font1='black';

if($_GET['Cargar']=='Aceptar'){
 if($_POST['Cargar']=='Cancelar'){
 header("location:VentaMasiva.php");  
 } 
 //desde ACA COPIO A REPOSICIONES
$Total=$_SESSION['ImpTotal'];
$Usuario=$_SESSION['Usuario'];
$color='#B8C6DE';
$font='white';
$Clientes=$_POST['cliente_t'];
$Cantidad=$_POST['cantidad_t'];
$Producto=$_POST['producto_t'];
$Titulo=$_POST['titulo_t'];  
$_SESSION['clientes_t']=$_POST['cliente_t'];
$_SESSION['cantidad_t']=$_POST['cantidad_t'];
$_SESSION['producto_t']=$_POST['producto_t'];
$_SESSION['titulo_t']=$_POST['titulo_t'];  
  
  
echo "<table class='login' border='0' width='840' vspace='5px' style='margin-top:5px;float:center;'>";
// echo "<tr align='center' style='background:$color; color:$font; font-size:6px;'>";
echo "<caption>Solicitud de Envíos</caption>";
// echo "<tr align='left' style='background:$color; color:$font; font-size:12px;'>";
echo "<th>Cliente Emisor</th><th>Cliente Receptor</th><th>Codigo</th><th>Producto</th>
      <th>Precio</th><th>Cantidad</th><th>Total</th><th>Eliminar</th>";
for($i=0;$i < count($Clientes);$i++){
echo "<tr style='color:$font2;background:$color2;font-size:12px;'>";
echo "<td>".$_SESSION['NombreClienteA']."</td>";
echo "<td>".$Clientes[$i]."</td>";
echo "<td>$Producto[$i]</td>";
  $sql=mysql_query("SELECT Titulo,PrecioVenta FROM Productos WHERE Codigo='$Producto[$i]'");
  while($row=mysql_fetch_array($sql)){
  echo "<td>$row[Titulo]</td>";
  echo "<td>$ $row[PrecioVenta]</td>";
  $Precio=$row[PrecioVenta];  
  }
echo "<td>$Cantidad[$i]</td>";
echo "<td>$ ".$Cantidad[$i]*$Precio."</td>";
echo "<td align='center'><a class='img' href='AgregarRepo.php?Eliminar=si&id=$row[0]'><img src='../../images/botones/eliminar.png' width='15' height='15' border='0' style='float:center;'></a></td>";
echo "</tr>";
}
echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='7' style='font-size:16px'><strong>Total: $Total</strong></td><td></td></tr>";
echo "</tr>";
echo "</table>";
	
$OfertasPublicadas='Productos';
$Ordenar1="SELECT * FROM Ventas WHERE Cliente='$cliente' AND FechaPedido=curdate() AND terminado=0 AND Usuario='".$_SESSION['Usuario']."';";
$datoskioscos2=mysql_query($Ordenar1);

echo "<form class='login' action='AgregarRepoVentaMasiva.php' method='get' style='width:95%;margin-top:10px'>";
echo "<div style='margin-bottom:5px'><label style='margin-left:150px'>Fecha de Salida del Recorrido:</label><input type='date' name='fecha_t' style='width:270px;'></div>";
echo "<div style='margin-bottom:5px'><label style='margin-left:150px'>Código del Cliente (Nº Factura/Remito/Orden/etc.):</label><input type='text' name='codigoproveedor_t' style='width:270px;'></div>";
echo "<div style='margin-bottom:5px'><label style='margin-left:150px'>Observaciones:</label><input type='text' name='observaciones_t' value='".$_SESSION['Observaciones_Cf']."'style='width:270px;' ></div>";

 	$Grupo="SELECT Numero,Nombre FROM Recorridos ";
	$estructura= mysql_query($Grupo);
	echo "<div style='margin-bottom:5px'><label style='margin-left:150px'>Recorrido:</label><select name='Recorrido_t' style='float:center;width:280px;' size='1' ondblclick='submit()'>";
  while ($row = mysql_fetch_array($estructura)){
	echo "<option value='".$row[Numero]."'>".$row[Numero]." ".$row[Nombre]."</option>";
		}
	echo "</select></div>";
 
echo "<div style='margin-bottom:5px'><label style='margin-left:150px'>Forma de Pago:</label><select name='formadepago_t' style='width:280px;' size='1'>";
echo "<option value='Origen'>Origen</option>";
echo "<option value='Destino'>Destino</option>";
echo "</select></div>";
echo "<div><label style='margin-left:150px'>Entrega en:</label><select name='entregaen_t' style='width:280px;' size='1'>";
echo "<option value='Retira'>Retira Sucursal</option>";
echo "<option value='Domicilio'>Domicilio</option>";
echo "</select></div>";
echo "<div><input type='submit' name='SolicitaEnvio' value='Confirmar Envio' width='20' height='15' ></div>";
echo "</form>";


 //HASTA ACA COPIO A REPOSICIONES 
		//INGRESA LA TRANSACCION EN LA TABLA TRANSCLIENTES
		$result=mysql_query("SELECT SUM(Cantidad*Precio) as Saldo FROM Ventas WHERE Cliente='$cliente' AND fechaPedido=curdate() AND terminado='0' AND Usuario='".$_SESSION['Usuario']."';");
		$rowresult = mysql_fetch_array($result);
		$Total= $rowresult[Saldo];
  
		$Compra='0';
		$Haber='0';
		$Fecha=date('Y-m-d');	
		$TipoDeComprobante='Remito';
		$cliente=$_SESSION['NombreClienteA'];
			
		$IngBrutosDestino='';	
		$CodigoSeguimiento='';
		$DomicilioOrigen=$_SESSION['DomicilioEmisor_t'];
		$SituacionFiscalOrigen=$_SESSION['SituacionFiscalEmisor_t'];
		$LocalidadOrigen=$_SESSION['LocalidadOrigen_t'];
		$IngBrutosOrigen=$_SESSION['IngBrutosOrigen_t'];
		$TelefonoOrigen=$_SESSION['TelefonoEmisor_t'];
		$FormaDePago=$_GET['formadepago_t'];
  
		$EntregaEn=$_GET['entregaen_t'];	
	
        $Usuario=$_SESSION['Usuario'];
		$CodigoProveedor=$_GET['codigoproveedor_t'];
		$Observaciones=$_GET['observaciones_t'];
		$Recorrido=$_GET['recorrido_t'];
  
		$ProvinciaDestino=$_SESSION['ProvinciaDestino_t'];
		$Sqltransportista=mysql_query("SELECT * FROM Logistica WHERE Recorrido='$Recorrido' AND Estado='Cargada' AND Eliminado='0'");
// 		$Dato=mysql_fetch_array($Sqltransportista);
		$Trasnsportista=$Dato[NombreChofer];	
		if ($Trasnsportista==''){
		$Trasnsportista='No Asignado';	
		}
			
		$Cero='0';

		$Usuario=$_SESSION['Usuario'];			

	
		//MODIFICA LAS REPOSICIONES A TERMINADO		
		$Termina0="SELECT * FROM Ventas WHERE Cliente='$cliente' AND fechaPedido=curdate() AND terminado=0 AND Usuario='".$_SESSION['Usuario']."';";
		$Termina1=mysql_query($Termina0);
			while($row = mysql_fetch_row($Termina1)){
			$Termina="UPDATE Ventas SET terminado=1 WHERE idPedido='$row[0]'";
// 			mysql_query($Termina);
			}
			$NumeroRepo= ''; 
			$Comentario='';
			unset($_SESSION['NumeroPedido']);
			unset($_SESSION['NCliente']);	
			unset($_SESSION['NClienteDestino_t']);	
			$NumeroRepo=$_SESSION['NumeroRepo'];
goto a; 
}
//   }
if ($_GET['BuscaCliente']=='Si'){
echo "<form class='login' action='' method='POST'  style='width:450px;';>";
	$Grupo="SELECT nombrecliente,Cuit FROM Clientes ORDER BY idProveedor ASC";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Cliente:</label><select name='BuscaCliente_t' style='float:center;width:260px;' size='1'>";
	while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[0]."'>".$row[0]."</option>";

	}
	echo "</select></div>";
	echo "<div><input name='BuscaCliente' class='bottom' type='submit' value='Aceptar' align='right'></div>";
	echo "</form>";

	if($_POST['BuscaCliente']=='Aceptar'){
	$Cliente=$_POST['BuscaCliente_t'];	
	$_SESSION['ClienteActivo']=$Cliente;	
	
	$Grupo="SELECT * FROM Clientes WHERE nombrecliente='$Cliente'";
	$estructura= mysql_query($Grupo);
	while ($row = mysql_fetch_row($estructura)){
	$_SESSION['ClienteActivo']=$row[2];	
	$_SESSION['CuitActivo']=$row[24];	
	}
		header("location:Ventas.php?Ventas=Mostrar&Cliente=$Cliente");
	}
	goto a;
}

if ($_POST['Pasar']=='Enviar'){
	if ($_SESSION['NCliente']==''){
	}
	if ($_SESSION['NClienteReceptor']==''){
	}
header("location:https://www.caddy.com.ar/SistemaTriangular/Ventas/Reposiciones.php");
}

if ($_POST['ClienteEmisor']=='Cambiar'){
unset($_SESSION['idClienteRelacion']);	
}

if ($_POST['ClienteReceptor']=='Cambiar'){
unset($_SESSION['NClienteDestino_t']);	
}

// $_SESSION['NCliente']=$_POST['Cliente_t'];
if($_POST[Busca]=='Aceptar'){
$idCliente0=explode(' - ',$_POST['Cliente_t'],2);
$idCliente=$idCliente0[0];  
$ClienteReceptor=$_POST['ClienteReceptor_t'];	

//DATOS CLIENTE EMISOR
$BuscarCliente=mysql_query("SELECT * FROM Clientes WHERE id='$idCliente'");
$row=mysql_fetch_row($BuscarCliente);
  
    $_SESSION['idClienteRelacion']=$row[0];	//id
    $_SESSION['NCliente']=$row[24];	//CUIT
    $_SESSION['NombreClienteA']=$row[2];//NOMBRE CLIENTE
    $_SESSION['DomicilioEmisor_t']=$row[17];//Domicilio
    $_SESSION['SituacionFiscalEmisor_t']=$row[21];//SituacionFiscal
    $_SESSION['TelefonoEmisor_t']=$row[12];//Telefono
    $_SESSION['LocalidadOrigen_t']=$row[8];//Telefono
    $_SESSION['IngBrutosOrigen_t']=$row[0];//Telefono
    $_SESSION['ProvinciaOrigen_t']=$row[9];//ProvinciaOrigen
    $_SESSION['RetiroOrigen_t']=$row[42];//Retiro
}
///--------------------HASTA ACA DATOS CLIENTES-----------
//------------DESDE ACA CLIENTE EMISOR---------------------------------------------------------------------
if ($_SESSION['idClienteRelacion']==''){
echo "<form class='Caddy' action='VentaMasiva.php' method='POST'  style='width:80%;float:center;'>";
	$Grupo="SELECT id,nombrecliente FROM Clientes WHERE nombrecliente <> 'Consumidor Final' ORDER BY NdeCliente";
	$estructura= mysql_query($Grupo);
  echo "<div><titulo>Seleccione un Cliente</titulo></div>";
  echo"<div><hr></hr></div>";  
  echo "<div><input name='Cliente_t' list='Cliente_t' type='text' style='width:350px' placeholder='Comience a escribir un nombre..'/></div>";
  echo "<datalist id='Cliente_t'>";
  echo "<div><select name='' list='Cliente_t'>";
  while ($row = mysql_fetch_array($estructura)){
	echo "<option value='$row[id] - $row[nombrecliente]'></option>";
		}
	echo "</select></div>";
  echo "</datalist>";
	echo "<div><input name='Busca' class='bottom' type='submit' value='Aceptar' align='right'></div>";
	echo "</form>";
}else{
echo "<form class='Caddy' action='' method='POST'  style='width:80%;float:center;'>";
echo "<div><titulo>Cliente Emisor:</titulo></div>";
echo "<div><hr></hr></div>";  
echo "<div style='margin-bottom:5px;'><label>Nombre Cliente:</label><label style='float:right'>".$_SESSION['NombreClienteA']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Situacion Fiscal:</label><label style='float:right'>".$_SESSION['SituacionFiscalEmisor_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Cuit:</label><label style='float:right'>".$_SESSION['NCliente']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Domicilio:</label><label style='float:right'>".$_SESSION['DomicilioEmisor_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Localidad:</label><label style='float:right'>".$_SESSION['LocalidadOrigen_t']."</label></div>";
echo "<div><label>Telefono:</label><label style='float:right'>".$_SESSION['TelefonoEmisor_t']."</label></div>";

echo "<div><input name='ClienteEmisor' class='bottom' type='submit' value='Cambiar' align='right' style='width:150px'></div>";
echo "</form>";
  
}


if($_POST['selec']=='todos'){
$c='checked';
$d='';
$e='';
}elseif($_POST['selec']=='relacionados'){
$c='';
$d='checked';
$e='';  
}elseif($_POST['selec']=='relacionados'){
$c='';
$d='';
$e='checked';
}
  if($_SESSION['NombreClienteA']!=''){// ESTO ES PARA VERIFICAR QUE PRIMERO SE SELECCIONE EL CLIENTE EMISOR, PARA LA RELACION.

echo "<form class='Caddy' action='' method='POST'  style='width:80%;float:center;'>";
echo "<div><label style='font-weight: bold'>Todos</label><input style='float:right' type='radio' name='selec' value='todos' onclick='submit()' $c></div>";
echo "<div><label style='font-weight: bold'>Relacionados</label><input style='float:right' type='radio' name='selec' value='relacionados' onclick='submit()'$d></div>";
echo "<div><label style='font-weight: bold'>Por Recorrido</label><input style='float:right' type='radio' name='selec' value='recorrido' onclick='submit()'$e></div>";
echo "<div><label style='font-weight: bold'>Filtro Aplicado: ($_POST[selec])</label></div>";

  if($_POST['selec']=='recorrido'){
	$estructura=mysql_query("SELECT * FROM Recorridos");
  echo "<div><label style='font-weight: bold'>Recorrido:</label><select name='recorridoselec_t' style='float:center;width:260px;' OnChange='submit()' size='1'>";
  echo "<option value='Agregar' style='font-weight: bold;'>Seleccionar Recorrido</option>";
    
  while ($row = mysql_fetch_array($estructura)){
	echo "<option value='$row[Numero]'>".$row[Nombre]."</option>";
	}
	echo "</select></div>";
}

echo "</form>";
echo "<form class='Caddy' action='VentanaVentaMasiva.php' method='POST'  style='width:80%;float:center;'>";

   if($_POST['selec']=='todos'){
  $Grupo="SELECT idProveedor,nombrecliente as Cliente,Direccion,Ciudad FROM Clientes ORDER BY idProveedor";
  
  }elseif($_POST['selec']=='relacionados'){
//   $Grupo="SELECT ClienteDestino as Cliente FROM TransClientes WHERE RazonSocial='".$_SESSION['NombreClienteA']."' UNION 
//   SELECT nombrecliente as Cliente FROM Clientes WHERE Relacion='".$_SESSION['NombreClienteA']."'ORDER BY Cliente";
    if($_SESSION[idClienteRelacion]=='5447'){
    $Grupo="SELECT nombrecliente as Cliente, idProveedor,Direccion,Ciudad,Observaciones FROM Clientes WHERE Relacion='".$_SESSION['idClienteRelacion']."'ORDER BY Observaciones";  
    }else{
     $Grupo="SELECT nombrecliente as Cliente, idProveedor,Direccion,Ciudad,Observaciones FROM Clientes WHERE Relacion='".$_SESSION['idClienteRelacion']."'ORDER BY idProveedor";
    }
  
  }elseif($_POST['recorridoselec_t']!=''){
  $_SESSION['Recorrido']==$_POST['recorridoselec_t'];

  $Grupo="SELECT Clientes.idProveedor,Clientes.nombrecliente as Cliente,Clientes.Recorrido,Clientes.Direccion,Clientes.Ciudad 
  FROM Clientes,HojaDeRuta 
  WHERE HojaDeRuta.Recorrido='".$_POST['recorridoselec_t']."' AND Clientes.id=HojaDeRuta.idCliente 
  GROUP BY Clientes.idProveedor ORDER BY nombrecliente ";
  
   }    
if((!isset($_POST[selec]))&&(!isset($_POST[recorridoselec_t]))){
goto z;  
}

if($estructura= mysql_query($Grupo)){
    echo "<table class='login'>";
    echo "<th>N de Cliente</th>";
    echo "<th>Cliente</th>";
    echo "<th>Direccion</th>";
    echo "<th>Localidad</th>";
    echo "<th>Observaciones</th>";
    echo "<th>Seleccionar</th>";    
    
  while ($row = mysql_fetch_array($estructura)){
  if($numfilas%2 == 0){
	echo "<tr align='left' style='font-size:12px;color:$font1;background: #f2f2f2;' >";
	}else{
	echo "<tr align='left' style='font-size:12px;color:$font1;background:$color2;' >";
	}	

  echo "<td>$row[idProveedor]</td>";
  echo "<td>$row[Cliente]</td>";
  echo "<td>$row[Direccion]</td>";
  echo "<td>$row[Ciudad]</td>";
  echo "<td>$row[Observaciones]</td>";
      
  echo "<td align='center'><input type='checkbox' name='ClienteReceptor_t[]' value='$row[Cliente]'></td>";  
  echo "</tr>";
  $numfilas++;
    }
    
  echo "</table>";    
}
    echo "<div><input name='Busca' class='bottom' type='submit' value='Aceptar' align='right'></div>";
z:
  }
  echo "</form>";

a:
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor

ob_end_flush();	
?>