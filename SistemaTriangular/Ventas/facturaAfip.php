<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
// include_once "Procesos/facturar.js";
// DESDE ACA PARA SUBIR EL ARCHIVO
$TipoDeComprobante=$_POST[tipodecomprobante_t];
if($_POST[tipodecomprobante_t]=='FACTURAS A'){
$TCA='FA';
}elseif($_POST[tipodecomprobante_t]=='NOTAS DE CREDITO A'){
$TCA='NCA';  
}elseif($_POST[tipodecomprobante_t]=='NOTAS DE DEBITO A'){
$TCA='NDA';    
}elseif($_POST[tipodecomprobante_t]=='FACTURAS B'){
$TCA='FB';    
}elseif($_POST[tipodecomprobante_t]=='NOTAS DE DEBITO B'){
$TCA='NDB';    
}

if ($_FILES["imagen"]["error"] > 0){
	echo "ha ocurrido un error";
} else {
	//ahora vamos a verificar si el tipo de archivo es un tipo de imagen permitido.
	//y que el tamano del archivo no exceda los 100kb
	$permitidos = array("image/jpg", "image/pdf", "image/gif", "image/png","application/pdf");
	$limite_kb = 1000;

  if (in_array($_FILES['imagen']['type'], $permitidos) && $_FILES['imagen']['size'] <= $limite_kb * 40960){
		//esta es la ruta donde copiaremos la imagen
		//recuerden que deben crear un directorio con este mismo nombre
		//en el mismo lugar donde se encuentra el archivo subir.php
//    $extension = end(explode(".", $_FILES['imagen']['name']));
//     $ruta = "../FacturasVenta/" . $NumeroComprobante.".".$extension;
    
    $ruta = "../FacturasVenta/".$TCA."-".$_FILES['imagen']['name'];
		//comprovamos si este archivo existe para no volverlo a copiar.
		//pero si quieren pueden obviar esto si no es necesario.
		//o pueden darle otro nombre para que no sobreescriba el actual.
		if (!file_exists($ruta)){
			//aqui movemos el archivo desde la ruta temporal a nuestra ruta
			//usamos la variable $resultado para almacenar el resultado del proceso de mover el archivo
			//almacenara true o false
			$resultado = @move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta);
			if ($resultado){
				echo "El archivo ha sido movido exitosamente";
			header("location:http://www.caddy.com.ar/SistemaTriangular/Inicio/Cpanel.php");

			} else {
				echo "Ocurrio un error al mover el archivo.";
        echo $ruta;
			}
		} else {
			echo $NumeroComprobante.".".$extension.", este archivo existe";
		}
	} else {
		echo "Archivo no permitido, es tipo de archivo prohibido o excede el tamano de $limite_kb Kilobytes";
		header("location:http://www.caddy.com.ar/SistemaTriangular/Ventas/Ventas.php");
  }
}

// HASTA ACA SUBIR EL ARCHIVO
if ($_POST['FacturaSimple']=='Aceptar'){
	
  
	$Fecha=$_POST['fecha_t'];
	$Mes=date("n",strtotime($Fecha));
	$Ano=date("Y",strtotime($Fecha));
	
	$Buscar=mysql_query("SELECT * FROM CierreIva WHERE Libro='IvaVentas' AND Mes='$Mes' AND Ano='$Ano'");
	$numerofilas = mysql_num_rows($Buscar);	
	
	if ($numerofilas<>'0'){
	?>
		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">
		alert("ERROR: EL LIBRO IVA DEL MES <? echo "$Mes";?> DEL AÑO <? echo "$Ano"; ?> YA ESTA CERRADO")
		</script>
		<?php
	goto a;
	}	
	
	$Usuario=$_SESSION['Usuario'];
	$Fecha=$_POST['fecha_t'];
	$RazonSocial=$_POST['razonsocial_t'];
	$Cuit2=$_POST['cuit_t'];
//	$TipoDeComprobante='FACTURA';
 	$TipoDeComprobante=$_POST['tipodecomprobante_t'];
  $NumeroComprobante=$_POST['numerocomprobante_t'];
	
  if(($TipoDeComprobante=='NOTAS DE CREDITO A')
	||($TipoDeComprobante=='NOTAS DE CREDITO B')
	||($TipoDeComprobante=='NOTAS DE CREDITO B')
	||($TipoDeComprobante=='NOTAS DE CREDITO C')
	||($TipoDeComprobante=='NOTAS DE CREDITO POR OPERACIONES CON EL EXTERIOR')
	||($TipoDeComprobante=='NOTAS DE CREDITO O DOCUMENTO EQUIVALENTE QUE CUMPLA')
	||($TipoDeComprobante=='NOTAS DE CREDITO M')
	||($TipoDeComprobante=='NOTAS DE CREDITO DE COMPROBANTES CON COD. 34, 39,')
	||($TipoDeComprobante=='RECIBOS FACTURA DE CREDITO')
	||($TipoDeComprobante=='NOTA DE CREDITO   SERVICIOS PUBLICOS   NOTA DE CRE')
	||($TipoDeComprobante=='AJUSTES CONTABLES QUE INCREMENTAN EL CREDITO FISCA')
	||($TipoDeComprobante=='NOTA DE CREDITO DE ASIGNACION')){
$Valor=-1;	
	}else{
$Valor=1;
	}
  
  $ImporteNeto=$_POST['importeneto_t']*$Valor;
	$Iva1=$_POST['iva1_t']*$Valor;
	$Iva2=$_POST['iva2_t']*$Valor;
	$Iva3=$_POST['iva3_t']*$Valor;
	$Exento=$_POST['exento_t']*$Valor;
	$Total=$_POST['total_t']*$Valor;
	$Compra=$_POST['compra_t']*$Valor;
  
	$BuscarDuplicidad=mysql_query("SELECT id FROM IvaVentas WHERE RazonSocial='$RazonSocial' AND 
  NumeroComprobante='$NumeroComprobante' AND TipoDeComprobante='$TipoDeComprobante' AND Eliminado='0'");
	$numerofilas = mysql_num_rows($BuscarDuplicidad);	
	if ($numerofilas<>'0'){
	?>
  <script>
  alertify.error("ERROR: La factura ya existe", "", 0);
  </script>
  <?  
	goto a;
	}		
  //FACTURA AFIP
  ?>
<script>
var dato={'TipoDeDocumento':99,
          'Documento':0,
          'ImpTotal':121,
          'ImpTotalConc':0,
          'ImpNeto':100,
          'ImpIVA':21,
          'ImpTrib':0};
  
  $.ajax({
        data: dato,
        url:'../afip.php/procesos/CreateVoucher.php',
        type:'post',
        beforeSend: function(){
        $("#buscando").html("Buscando...");
        },
        success: function (respuesta) {
        var jsonData= JSON.parse(respuesta);  
          $("#EmisionTipo").html(jsonData.EmisionTipo);
        }
        });
  </script>
  <?
  
	
$sql="INSERT INTO IvaVentas(
Fecha,
RazonSocial,
Cuit,
TipoDeComprobante,
NumeroComprobante,
ImporteNeto,
Iva1,
Iva2,
Iva3,
Exento,
Total,
CompraMercaderia)VALUES('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$NumeroComprobante}','{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}')";
mysql_query($sql);
?>
  <script>
			alertify.success("Comprobante cargado con exito");
  </script>
<?
//---------------INGRESA LOS MOVIMIENTOS EN TRANSACCIONES--------------------
$sqlTransacciones="INSERT INTO TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Debe,Usuario)VALUES
('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Total}','{$Usuario}')";
// mysql_query($sqlTransacciones);//NO LE ENCONTRE SENTIDO A INGRESAR DATOS DE VENTA AFIP A TRANSCLIENTES 	
$Sucursal=$_SESSION['Sucursal'];
$Usuario=$_SESSION['Usuario'];	
//-------INGRESA LOS MOVIMIENTOS EN TESORERIA---------------
// if($Compra==true){
// $Cuenta1='421600';	
// $Cuenta2='211100';	
// $NombreCuenta1='FLETES Y ENCOMIENDAS';
// $NombreCuenta2='PROVEEDORES';
// $Debe=$Total-$Iva1-$Iva2-$Iva3;
// $Haber=$Total;
// }else{
$Cuenta1='112200';	
$Cuenta2='410100';	
$NombreCuenta1='DEUDORES X VENTAS';
$NombreCuenta2='VENTAS';
$Debe=$Total;
$Haber=$Total-$Iva1-$Iva2-$Iva3;
// }

	//BUSCO EL ULTIMO NUMERO DE ASIENTO DE TESORERIA
  $BuscaNumAsiento= mysql_query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
  $row = mysql_fetch_row($BuscaNumAsiento); 
  $NAsiento = trim($row[0])+1;
	
	 $sql1="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,Sucursal,Usuario,NumeroAsiento) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','{$Debe}','{$Sucursal}','{$Usuario}','{$NAsiento}')"; 
 	mysql_query($sql1);
 	$sql2="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,Sucursal,Usuario,NumeroAsiento) VALUES ('{$Fecha}','{$NombreCuenta2}','{$Cuenta2}','{$Haber}','{$Sucursal}','{$Usuario}','{$NAsiento}')"; 
 	mysql_query($sql2);

if(($Iva1+$Iva2+$Iva3)<>'0'){
$CuentaIva='213200';	
$NombreCuenta1='IVA DEBITO FISCAL';
$Importe=($Iva1+$Iva2+$Iva3);
$Cero='0';
	$sql3="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,
	 Haber,Sucursal,Usuario,NumeroAsiento) VALUES ('{$Fecha}','{$NombreCuenta1}','{$CuentaIva}','{$Cero}','{$Importe}','{$Sucursal}','{$Usuario}','{$NAsiento}')"; 
 	mysql_query($sql3);
}	
//------------INGRESA LA VENTA EN CTAS CTES------------------------------------------------------
$sqlCtasctes="INSERT INTO Ctasctes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroVenta,Debe,Usuario,NumeroFactura)
VALUES('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Total}','{$Usuario}','{$NumeroComprobante}')";
mysql_query($sqlCtasctes);	
//------------------------------------------------------------------------			
header("location:Ventas.php");
}	

//  DESDE ACA FACTURACION X RECORRIDO

if ($_POST['FacturacionxRecorrido']=='Aceptar'){
  $Codigo='0000000028';
  $Titulo=$_POST['titulo_t'];
  $Fecha=$_POST['fecha_t'];
	$RazonSocial=$_POST['razonsocial_t'];
	$Cuit2=$_POST['cuit_t'];
	$TipoDeComprobante=$_POST['tipodecomprobante_t'];
  $NumeroComprobante=$_POST['numerocomprobante_t'];
  
	if(($TipoDeComprobante=='NOTAS DE CREDITO A')
	||($TipoDeComprobante=='NOTAS DE CREDITO B')
	||($TipoDeComprobante=='NOTAS DE CREDITO B')
	||($TipoDeComprobante=='NOTAS DE CREDITO C')
	||($TipoDeComprobante=='NOTAS DE CREDITO POR OPERACIONES CON EL EXTERIOR')
	||($TipoDeComprobante=='NOTAS DE CREDITO O DOCUMENTO EQUIVALENTE QUE CUMPLA')
	||($TipoDeComprobante=='NOTAS DE CREDITO M')
	||($TipoDeComprobante=='NOTAS DE CREDITO DE COMPROBANTES CON COD. 34, 39,')
	||($TipoDeComprobante=='RECIBOS FACTURA DE CREDITO')
	||($TipoDeComprobante=='NOTA DE CREDITO   SERVICIOS PUBLICOS   NOTA DE CRE')
	||($TipoDeComprobante=='AJUSTES CONTABLES QUE INCREMENTAN EL CREDITO FISCA')
	||($TipoDeComprobante=='NOTA DE CREDITO DE ASIGNACION')){
$Valor=-1;	
	}else{
$Valor=1;
	}

	$ImporteNeto=$_POST['importeneto_t']*$Valor;
	
  $Iva1=$_POST['iva1_t']*$Valor;
	$Iva2=$_POST['iva2_t']*$Valor;
	$Iva3=$_POST['iva3_t']*$Valor;
  
	$Exento=$_POST['exento_t']*$Valor;
	$Total=$_POST['total_t']*$Valor;
	$Compra=$_POST['compra_t'];
	$Cantidad=$_POST['cantidad_t'];
	$Usuario=$_SESSION['NombreUsuario'];
	$Terminado='1';
	$Observaciones=$_POST['observaciones_t'];
  $Precio=$ImporteNeto/$Cantidad;
  
//------------INGRESA LA VENTA EN VENTAS----------------------
$sql="INSERT INTO Ventas(Codigo,fechaPedido,Titulo,Edicion,Precio,Cantidad,Total,Cliente,NumeroRepo,
ImporteNeto,Iva1,Iva2,Iva3,Usuario,terminado,Comentario)
VALUES('{$Codigo}','{$Fecha}','{$Titulo}','{$edicion}','{$Precio}','{$Cantidad}','{$Total}','{$RazonSocial}',
'{$NumeroComprobante}','{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Usuario}','{$Terminado}','{$Observaciones}')";
mysql_query($sql);
//------------INGRESA LA VENTA EN CTAS CTES----------------------
$sqlCtasctes="INSERT INTO Ctasctes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroVenta,Debe,Usuario,NumeroFactura)
VALUES('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$Titulo}','{$NumeroComprobante}','{$Total}','{$Usuario}','{$NumeroComprobante}')";
mysql_query($sqlCtasctes);	
//------------------------------------------------------------------------			
// DESDE ACA INGRESA LOS MOVIMIENTOS EN TESORERIA
$Cuenta1='112200';	
$Cuenta2='410100';	
$NombreCuenta1='DEUDORES POR VENTAS';
$NombreCuenta2='VENTAS';
$Haber=$Total-$Iva1-$Iva2-$Iva3;
$Debe=$Total;
	//BUSCO EL ULTIMO NUMERO DE ASIENTO DE TESORERIA
						$BuscaNumAsiento= mysql_query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
						$row = mysql_fetch_row($BuscaNumAsiento); 
						$NAsiento = trim($row[0])+1;
  
 $Observaciones="Facturacion x Recorrido ".$TipoDeComprobante." ".$NumeroComprobante; 

	 $sql1="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,NumeroAsiento,Observaciones) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','{$Debe}','{$NAsiento}','{$Observaciones}')"; 
 	mysql_query($sql1);// SE ELMINITA PORQUE NO DEBERIA CARGAR UNA VENTA EN TESORERIA HASTA QUE SE FACTURE
 	$sql2="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,NumeroAsiento,Observaciones) VALUES ('{$Fecha}','{$NombreCuenta2}','{$Cuenta2}','{$Haber}','{$NAsiento}','{$Observaciones}')"; 
 	mysql_query($sql2);// SE ELMINITA PORQUE NO DEBERIA CARGAR UNA VENTA EN TESORERIA HASTA QUE SE FACTURE

if(($Iva1+$Iva2+$Iva3)>'0'){
$Cuenta1='213200';	
$NombreCuenta1='IVA DEBITO FISCAL';
$Importe=($Iva1+$Iva2+$Iva3);
	$sql3="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,
	 Haber,NumeroAsiento,Observaciones) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','0','{$Importe}','{$NAsiento}','{$Observaciones}')"; 
 	mysql_query($sql3);// SE ELMINITA PORQUE NO DEBERIA CARGAR UNA VENTA EN TESORERIA HASTA QUE SE FACTURE
}	
  
  
//HASTA ACA INGRESA LOS MOVIMIENTOS EN TESORERIA  
$OrdenN=$_SESSION['NumOrden'];  
// $OrdenN=$_GET['NumOrden']; 

for($i=0;$i<count($OrdenN);$i++)
      {
// ACTUALIZO FACTURADO A SI EN TABLA LOGISTICA  
$sqlRecorrido=mysql_query("SELECT Recorrido FROM Logistica WHERE NumerodeOrden='$OrdenN[$i]' AND Eliminado='0'");
$Recorrido=mysql_fetch_array($sqlRecorrido);  
$sqlCodigo=mysql_query("SELECT CodigoProductos FROM Recorridos WHERE Numero='$Recorrido[Recorrido]'")or die(mysql_error());
if(mysql_num_rows($sqlCodigo)<>0){
$Codigo=mysql_result($sqlCodigo,0);
}
$sqlprecio=mysql_query("SELECT PrecioVenta FROM Productos WHERE Codigo='$Codigo' ")or die(mysql_error());
if(mysql_num_rows($sqlprecio)<>0){
$DatoPrecio=mysql_result($sqlprecio,0);
}
  
  
  
$sql="UPDATE Logistica SET Facturado='1',ComprobanteF='$TipoDeComprobante',NumeroF='$NumeroComprobante',TotalFacturado='$DatoPrecio' WHERE NumerodeOrden='$OrdenN[$i]'";
mysql_query($sql);
}

$sql="INSERT INTO IvaVentas(
Fecha,
RazonSocial,
Cuit,
TipoDeComprobante,
NumeroComprobante,
ImporteNeto,
Iva1,
Iva2,
Iva3,
Exento,
Total,
CompraMercaderia)VALUES('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$NumeroComprobante}','{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}')";
mysql_query($sql);
  
header("location:Ventas.php");
} 
// HASTA ACA ACTUALIZA LOS DATOS EN LOGISTICA
// DESDE ACA PARA FACTURACION DIRECTA
if ($_POST['FacturacionDirecta']=='Aceptar'){
  $Codigo='0000000000';
  $Titulo="VENTA DIRECTA";
  $Fecha=$_POST['fecha_t'];
	$RazonSocial=$_POST['razonsocial_t'];
	$Cuit2=$_POST['cuit_t'];
	$TipoDeComprobante='Servicios de Logistica';
  $NumeroComprobante=$_POST['numerocomprobante_t'];
	
  	if(($TipoDeComprobante=='NOTAS DE CREDITO A')
	||($TipoDeComprobante=='NOTAS DE CREDITO B')
	||($TipoDeComprobante=='NOTAS DE CREDITO B')
	||($TipoDeComprobante=='NOTAS DE CREDITO C')
	||($TipoDeComprobante=='NOTAS DE CREDITO POR OPERACIONES CON EL EXTERIOR')
	||($TipoDeComprobante=='NOTAS DE CREDITO O DOCUMENTO EQUIVALENTE QUE CUMPLA')
	||($TipoDeComprobante=='NOTAS DE CREDITO M')
	||($TipoDeComprobante=='NOTAS DE CREDITO DE COMPROBANTES CON COD. 34, 39,')
	||($TipoDeComprobante=='RECIBOS FACTURA DE CREDITO')
	||($TipoDeComprobante=='NOTA DE CREDITO   SERVICIOS PUBLICOS   NOTA DE CRE')
	||($TipoDeComprobante=='AJUSTES CONTABLES QUE INCREMENTAN EL CREDITO FISCA')
	||($TipoDeComprobante=='NOTA DE CREDITO DE ASIGNACION')){
$Valor=-1;	
	}else{
$Valor=1;
	}
  
  $ImporteNeto=$_POST['importeneto_t']*$Valor;
	$Iva1=$_POST['iva1_t']*$Valor;
	$Iva2=$_POST['iva2_t']*$Valor;
	$Iva3=$_POST['iva3_t']*$Valor;
	$Exento=$_POST['exento_t']*$Valor;
	$Total=$_POST['total_t']*$Valor;
	$Compra=$_POST['compra_t'];
	$Cantidad=$_POST['cantidad_t'];
	$Usuario=$_SESSION['NombreUsuario'];
	$Terminado='1';
	$Observaciones=$_POST['observaciones_t'];

//------------INGRESA LA VENTA EN CTAS CTES----------------------
$sqlCtasctes="INSERT INTO Ctasctes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroVenta,Debe,Usuario)
VALUES('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Total}','{$Usuario}')";
mysql_query($sqlCtasctes);	
//------------------------------------------------------------------------			

//------------INGRESA LA VENTA EN VENTAS----------------------
$sql="INSERT INTO Ventas(Codigo,fechaPedido,Titulo,Edicion,Precio,Cantidad,Total,Cliente,NumeroRepo,
ImporteNeto,Iva1,Iva2,Iva3,Usuario,terminado,Comentario)
VALUES('{$Codigo}','{$Fecha}','{$Titulo}','{$edicion}','{$ImporteNeto}','{$Cantidad}','{$Total}','{$RazonSocial}',
'{$NumeroComprobante}','{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Usuario}','{$Terminado}','{$Observaciones}')";
mysql_query($sql);

  
  
// //---------------INGRESA LOS MOVIMIENTOS EN TRANSACCIONES--------------------
// $sqlTransacciones="INSERT INTO TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Debe)VALUES
// ('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Total}')";
// mysql_query($sqlTransacciones);	
	
//-------INGRESA LOS MOVIMIENTOS EN TESORERIA---------------
// SE ELiMinA PORQUE NO DEBERIA CARGAR UNA VENTA EN TESORERIA HASTA QUE SE FACTURE
  if($Compra==true){
$Cuenta1='421600';	
$Cuenta2='211100';	
$NombreCuenta1='FLETES Y ENCOMIENDAS';
$NombreCuenta2='PROVEEDORES';
$Debe=$Total-$Iva1-$Iva2-$Iva3;
$Haber=$Total;
}else{
$Cuenta1='112200';	
$Cuenta2='410100';	
$NombreCuenta1='DEUDORES POR VENTAS';
$NombreCuenta2='VENTAS';
$Haber=$Total-$Iva1-$Iva2-$Iva3;
$Debe=$Total;
}
	//BUSCO EL ULTIMO NUMERO DE ASIENTO DE TESORERIA
						$BuscaNumAsiento= mysql_query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
						$row = mysql_fetch_row($BuscaNumAsiento); 
						$NAsiento = trim($row[0])+1;

 $Observaciones="Facturacion Directa".$TipoDeComprobante." ".$NumeroComprobante; 
  
	 $sql1="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,NumeroAsiento,Observaciones) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','{$Debe}','{$NAsiento}','{$Observaciones}')"; 
//  	mysql_query($sql1);// SE ELMINITA PORQUE NO DEBERIA CARGAR UNA VENTA EN TESORERIA HASTA QUE SE FACTURE
 	$sql2="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,NumeroAsiento,Observaciones) VALUES ('{$Fecha}','{$NombreCuenta2}','{$Cuenta2}','{$Haber}','{$NAsiento}','{$Observaciones}')"; 
//  	mysql_query($sql2);// SE ELMINITA PORQUE NO DEBERIA CARGAR UNA VENTA EN TESORERIA HASTA QUE SE FACTURE

if(($Iva1+$Iva2+$Iva3)>'0'){
$Cuenta1='213200';	
$NombreCuenta1='IVA DEBITO FISCAL';
$Importe=($Iva1+$Iva2+$Iva3);
	$sql3="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,
	 Haber,NumeroAsiento,Observaciones) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','0','{$Importe}','{$NAsiento}','{$Observaciones}')"; 
//  	mysql_query($sql3);// SE ELMINITA PORQUE NO DEBERIA CARGAR UNA VENTA EN TESORERIA HASTA QUE SE FACTURE

}
    
$sql="INSERT INTO IvaVentas(
Fecha,
RazonSocial,
Cuit,
TipoDeComprobante,
NumeroComprobante,
ImporteNeto,
Iva1,
Iva2,
Iva3,
Exento,
Total,
CompraMercaderia)VALUES('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$NumeroComprobante}','{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}')";
mysql_query($sql);
    
header("location:Ventas.php");
}	
  //DESDE ACA FACTURACION AFIP POR REMITO
  
if($_POST['FacturacionxRemito']=='Aceptar'){
// echo $_POST['FacturacionxRemito'];
  $Codigo='0000000028';
  $Titulo=$_POST['titulo_t'];
  $Fecha=$_POST['fecha_t'];
	$RazonSocial=$_POST['razonsocial_t'];
	$Cuit2=$_POST['cuit_t'];
	$TipoDeComprobante=$_POST['tipodecomprobante_t'];
	$NumeroComprobante=$_POST['numerocomprobante_t'];
  
    	if(($TipoDeComprobante=='NOTAS DE CREDITO A')
	||($TipoDeComprobante=='NOTAS DE CREDITO B')
	||($TipoDeComprobante=='NOTAS DE CREDITO B')
	||($TipoDeComprobante=='NOTAS DE CREDITO C')
	||($TipoDeComprobante=='NOTAS DE CREDITO POR OPERACIONES CON EL EXTERIOR')
	||($TipoDeComprobante=='NOTAS DE CREDITO O DOCUMENTO EQUIVALENTE QUE CUMPLA')
	||($TipoDeComprobante=='NOTAS DE CREDITO M')
	||($TipoDeComprobante=='NOTAS DE CREDITO DE COMPROBANTES CON COD. 34, 39,')
	||($TipoDeComprobante=='RECIBOS FACTURA DE CREDITO')
	||($TipoDeComprobante=='NOTA DE CREDITO   SERVICIOS PUBLICOS   NOTA DE CRE')
	||($TipoDeComprobante=='AJUSTES CONTABLES QUE INCREMENTAN EL CREDITO FISCA')
	||($TipoDeComprobante=='NOTA DE CREDITO DE ASIGNACION')){
$Valor=-1;	
	}else{
$Valor=1;
	}
  
	$ImporteNeto=$_POST['importeneto_t']*$Valor;
	$Iva3=$_POST['iva_t']*$Valor;
	$Exento=$_POST['exento_t']*$Valor;
	$Total=$_POST['total_t']*$Valor;
	$Compra=$_POST['compra_t'];
	$Cantidad=$_POST['cantidad_t'];
	$Usuario=$_SESSION['NombreUsuario'];
	$Terminado='1';
	$Observaciones=$_POST['observaciones_t'];
  $Precio=$ImporteNeto/$Cantidad;
//------------INGRESA LA VENTA EN VENTAS----------------------
// $sql="INSERT INTO Ventas(Codigo,fechaPedido,Titulo,Edicion,Precio,Cantidad,Total,Cliente,NumeroRepo,
// ImporteNeto,Iva1,Iva2,Iva3,Usuario,terminado,Comentario)
// VALUES('{$Codigo}','{$Fecha}','{$Titulo}','{$edicion}','{$Precio}','{$Cantidad}','{$Total}','{$RazonSocial}',
// '{$NumeroComprobante}','{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Usuario}','{$Terminado}','{$Observaciones}')";
// mysql_query($sql);
//------------INGRESA LA VENTA EN CTAS CTES----------------------
// // $sql=mysql_query("SELECT NumeroComprobante FROM TransClientes WHERE id='$")  
// $sqlCtasctes="INSERT INTO Ctasctes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroVenta,Debe,Usuario,NumeroFactura)
// VALUES('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Total}','{$Usuario}','{$NumeroComprobante}')";
// // mysql_query($sqlCtasctes);// NO VA MAS LA FACTURA EN LA CTA CTE DEL CLIENTE	
// $sqlCtasctes="UPDATE Ctasctes SET TipoDeComprobante='$TipoDeComprobante',NumeroFactura='$NumeroComprobante' WHERER id=";
// mysql_query($sqlCtasctes);// ACTUALIZO EL NUMERO DE FACTURA Y REEMPLAZO LA PALABRA REMITO X EL TIPO DE COMPROBANTE

  //------------------------------------------------------------------------			
// DESDE ACA INGRESA LOS MOVIMIENTOS EN TESORERIA
$Cuenta1='112200';	
$Cuenta2='410100';	
$NombreCuenta1='DEUDORES POR VENTAS';
$NombreCuenta2='VENTAS';
$Haber=$Total-$Iva1-$Iva2-$Iva3;
$Debe=$Total;

	//BUSCO EL ULTIMO NUMERO DE ASIENTO DE TESORERIA
  $BuscaNumAsiento= mysql_query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
  $row = mysql_fetch_row($BuscaNumAsiento); 
  $NAsiento = trim($row[0])+1;
  $Observaciones="Facturacion x Remito ".$TipoDeComprobante." ".$NumeroComprobante; 

   $sql1="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,NumeroAsiento,Observaciones) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','{$Debe}','{$NAsiento}','{$Observaciones}')"; 
 	mysql_query($sql1);
 	$sql2="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,NumeroAsiento,Observaciones) VALUES ('{$Fecha}','{$NombreCuenta2}','{$Cuenta2}','{$Haber}','{$NAsiento}','{$Observaciones}')"; 
 	mysql_query($sql2);
  
if(($Iva1+$Iva2+$Iva3)>'0'){
$Cuenta1='213200';	
$NombreCuenta1='IVA DEBITO FISCAL';
$Importe=($Iva1+$Iva2+$Iva3);
	$sql3="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,
	 Haber,NumeroAsiento,Observaciones) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','0','{$Importe}','{$NAsiento}','{$Observaciones}')"; 
 	mysql_query($sql3);// SE ELMINA PORQUE NO DEBERIA CARGAR UNA VENTA EN TESORERIA HASTA QUE SE FACTURE
}	
  
$sql="INSERT INTO IvaVentas(
Fecha,
RazonSocial,
Cuit,
TipoDeComprobante,
NumeroComprobante,
ImporteNeto,
Iva1,
Iva2,
Iva3,
Exento,
Total,
CompraMercaderia)VALUES('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$NumeroComprobante}','{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}')";
mysql_query($sql);
  
//HASTA ACA INGRESA LOS MOVIMIENTOS EN TESORERIA  
$OrdenN=$_SESSION['idTransClientes']; 

for($i=0;$i<count($OrdenN);$i++)
      {
// ACTUALIZO FACTURADO A SI EN TABLA LOGISTICA  
$sql=mysql_query("UPDATE `TransClientes` SET Facturado=1, `ComprobanteF`='$TipoDeComprobante',`NumeroF`='$NumeroComprobante' WHERE id='$OrdenN[$i]'");

  $sql=mysql_query("SELECT NumeroComprobante FROM TransClientes WHERE id='$OrdenN[$i]'");
  $Dato=mysql_fetch_array($sql);
  $Observ=$Observ.' | '.$Dato[NumeroComprobante];
  $sqlCtasctesE="UPDATE Ctasctes SET Eliminado=1 WHERE NumeroVenta='$Dato[NumeroComprobante]' LIMIT 1";
  mysql_query($sqlCtasctesE);// ELIMINO LOS REMITOS X EL TIPO DE COMPROBANTE
}
  $sqlCtasctes="INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`, `Usuario`, `NumeroFactura`,`Observaciones`) VALUES 
  ('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Total}','{$Usuario}','{$NumeroComprobante}','{$Observ}')";
  mysql_query($sqlCtasctes);
header("location:Ventas.php");
} 

//---------------------------------HASTA ACA PARA CARGAR VENTA DIRECTA----------------------------------

a:
ob_end_flush();
?>
<script type="text/javascript" src="Procesos/facturar.js"></script>
