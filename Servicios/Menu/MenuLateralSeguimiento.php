<?php
ob_start();
session_start();

if($_GET['idCliente_c']==''){
	
}else{
$idCA=$_GET['idCliente_c'];	
$sql=mysql_query("SELECT NdeCliente,nombrecliente,Cuit FROM Clientes WHERE NdeCliente='$idCA'");
$DatoCA=mysql_fetch_array($sql);
$_SESSION['ClienteActivo']=$DatoCA[nombrecliente];	
$_SESSION['idClienteActivo']=$DatoCA[NdeCliente];
$_SESSION['CuitActivo']=$DatoCA[Cuit];		
}
$Empresa=$_SESSION['ClienteActivo'];
$Cuit=$_SESSION['CuitActivo'];	
$id=$_SESSION['idClienteActivo'];	
$dato=$_SESSION['NdeCliente'];


echo "<div id='cssmenu'>";
echo "<ul>";
echo "<li><a href='Seguimiento.php?CargarSeguimiento=Si'><span>Cargar Seguimiento</span></a></li>";

echo "<li class='has-sub'><a href='Seguimiento.php?Seguimiento=Agregar&idCliente=$id'><span>Agregar Avisos</span></a>";
echo "            <ul>";
echo "              <li><a href='Seguimiento.php?Seguimiento=Agregar&idCliente=$id'><span>Avisos de Envios</span></a></li>";
echo "               <li><a href='Seguimiento.php?SeguimientoService=Agregar&idCliente=$id'><span>Avisos de Service</span></a></li>";
echo "            </ul>";
echo "         </li>";
echo "<li><a href='Seguimiento.php?Seguimiento=Si&idCliente=$id'><span>Ver Avisos</span></a></li>";
echo "<li><a href='Recorridos.php'><span>Remitos x Recorrido</span></a></li>";
if (($_POST['recorrido_t']<>'')||($_POST['fecha_t']<>'')){
echo "<li><a target='t_blank' href='Informes/Recorridospdf.php'><span>Imprimir Informe</span></a></li>";
echo "<li><a target='t_blank' href='GenerarExcel.php'><span>Descargar Informe</span></a></li>";
}
// echo "<li><a href='Ventas.php?Facturas=Mostrar&Cliente=$Empresa'><span>Ver Facturas</span></a></li>";
// echo "   <li class='active has-sub'><a href=''><span>Ver Remitos</span></a>";
// echo "      <ul>";
// echo "         <li class='has-sub'><a href='#'><span>Remitos de Envio</span></a>";
// echo "            <ul>";
// echo "              <li><a href='Ventas.php?Ventas=MostrarxNumeroEnvio&Cliente=$Empresa'><span>X Numero de Remito</span></a></li>";
// echo "               <li><a href='Ventas.php?Ventas=MostrarEnvio&Cliente=$Empresa&Remitos=Pendientes'><span>Remitos a Facturar</span></a></li>";
// echo "               <li class='last'><a href='Ventas.php?Ventas=MostrarEnvio&Cliente=$Empresa'><span>Ver Todos</span></a></li>";
// echo "            </ul>";
// echo "         </li>";
// echo "         <li class='has-sub'><a href='Ventas.php?Ventas=MostrarRecepcion&Cliente=$Empresa'><span>Remitos de Recepcion</span></a>";
// // echo "            <ul>";
// // echo "               <li><a href='#'><span>Sub Product</span></a></li>";
// // echo "               <li class='last'><a href='#'><span>Sub Product</span></a></li>";
// // echo "            </ul>";
// echo "         </li>";
// echo "      </ul>";
// echo "   </li>";
// echo "<li><a href='Ventas.php?Cargar=Si'><span>Cargar Factura AFIP</span></a></li>";
$RemitosPendientes=mysql_query("SELECT * FROM TransClientes WHERE RazonSocial='".$_SESSION['ClienteActivo']."' AND TipoDeComprobante='Remito' AND Eliminado='0' AND Debe>'0' AND Facturado='0'");
$TotalRemitos=mysql_num_rows($RemitosPendientes);

$RecorridosPendientes=mysql_query("SELECT * FROM `Logistica` WHERE `Cliente` = '".$_SESSION['ClienteActivo']."' AND Facturado='0' AND Eliminado='0'");   
$TotalRecorridos=mysql_num_rows($RecorridosPendientes);
// echo "   <li class='active has-sub'><a href=''><span>Facturacion</span></a>";
// echo "      <ul>";
// echo "         <li><a href='Ventas.php?Cargar=Si'><span>Factura Simple</span></a>";
// echo "         <li><a href='Ventas.php?Ventas=MostrarEnvio&Cliente=$Empresa&Remitos=Pendientes'><span>Factura x Remitos ($TotalRemitos)</span></a>";
// echo "         <li><a href='Ventas.php?Ventas=Recorridos&Cliente=$Empresa'><span>Factura x Recorridos ($TotalRecorridos)</span></a>";
// echo "      </ul>";
// echo "    </li>";

// echo "<li><a href='Ventas.php?Cobranza=Si'><span>Cargar Cobranza</span></a></li>";
// echo "<li><a href='Ventas.php?Ventas=Recorridos&Cliente=$Empresa'><span>Facturar Recorridos</span></a></li>";
// echo "<li><a href='../Clientes/Clientes.php?id=Modificar&IdCliente=$id'><span>Modificar Datos</span></a></li>";
// echo "<li><a href='Ventas.php?CtaCte=Aceptar'><span>Cuenta Corriente</span></a></li>";
// echo "   <li class='last'><a href='../Clientes/Clientes.php?Seguimiento=Si&Cliente=$Empresa'><span>Seguimiento de Envios</span></a></li>";
echo "</ul>";
echo "</div>";
$fecha=$_POST[fecha_t];
$recorrido=$_POST[recorrido_t];

if($_POST[recorrido_t]=='Todos'){
$sqlsumarentregas=mysql_query("SELECT id,CodigoSeguimiento FROM TransClientes WHERE FechaEntrega='$fecha' AND Eliminado=0");
$sumarentregas=mysql_num_rows($sqlsumarentregas);
$e=0;
$ne=0;
while($row=mysql_fetch_array($sqlsumarentregas)){
$sqlSeguimiento=mysql_query("SELECT id,Fecha,Hora,Entregado FROM Seguimiento WHERE id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]')");
$resultSeguimiento=mysql_fetch_array($sqlSeguimiento);
  if($resultSeguimiento[Entregado]==1){
  $e=$e+1;  
  }else{
  $ne=$ne+1;  
  }
}
  
$sqlkm=mysql_query("SELECT SUM(KilometrosRecorridos)as Km FROM Logistica WHERE Fecha='$fecha' AND Eliminado='0'");
$datokm=mysql_fetch_array($sqlkm);
// $sqlRecorridos=mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$_POST[recorrido_t]'");
// $DatosRecorridos=mysql_fetch_array($sqlRecorridos);
echo "<div style='clear: both;'></div>";  
echo "<div style='float:left;margin:0;'>";
echo "<table class='login' style='margin-top:10px;'>";
echo "<caption style='font-size:18px'>Datos Recorrido</caption>";
echo "<tr style='font-size:12px'><td>Recorrido:</td><td style='width:270px;'>$DatosRecorridos[Nombre] ($_POST[recorrido_t])</td></tr>";
// echo "<tr style='font-size:12px'><td>Responsable:</td><td>$NombreChofer[NombreChofer]</td></tr>"; 
// echo "<tr style='font-size:12px'><td>Dominio:</td><td>$NombreChofer[Patente]</td></tr>";
echo "<tr style='font-size:12px'><td>Km. Recorridos:</td><td>$datokm[Km]</td></tr>";
echo "<tr style='font-size:12px'><td>Cant.Entregas:</td><td>$sumarentregas</td></tr>";
echo "<tr style='font-size:12px'><td>Entregados:</td><td style='color:green'>$e</td></tr>";
echo "<tr style='font-size:12px'><td>No Entreados:</td><td style='color:red'>$ne</td></tr>";
$ef=number_format(($e/$sumarentregas)*100,2,',','.');  
echo "<tr style='font-size:12px'><td>Efectividad:</td><td style='color:blue'>$ef %</td></tr>";  
echo "</table>";
echo "</div>";  
  
}elseif(($fecha<>'')AND($recorrido<>'')){
$sqlBuscoChofer=mysql_query("SELECT NombreChofer,KilometrosRecorridos,Patente FROM Logistica WHERE Fecha='$fecha' AND Recorrido='$recorrido' AND Eliminado=0");
$NombreChofer=mysql_fetch_array($sqlBuscoChofer);
$sqlRecorridos=mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$recorrido'");
$DatosRecorridos=mysql_fetch_array($sqlRecorridos);
$sqlsumarentregas=mysql_query("SELECT id,CodigoSeguimiento FROM TransClientes WHERE FechaEntrega='$fecha' AND Recorrido='$recorrido' AND Eliminado=0");
$sumarentregas=mysql_num_rows($sqlsumarentregas);
$e=0;
$ne=0;
while($row=mysql_fetch_array($sqlsumarentregas)){
$sqlSeguimiento=mysql_query("SELECT id,Fecha,Hora,Entregado FROM Seguimiento WHERE id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]')");
$resultSeguimiento=mysql_fetch_array($sqlSeguimiento);
  if($resultSeguimiento[Entregado]==1){
  $e=$e+1;  
  }else{
  $ne=$ne+1;  
  }
}
echo "<div style='clear: both'></div>";  
echo "<div style='float:left;margin:0;'>";
echo "<table class='login' style='margin-top:10px'>";
echo "<caption style='font-size:15px'>Datos Recorrido</caption>";
echo "<tr style='font-size:12px'><td>Recorrido:</td><td style='width:270px'>$DatosRecorridos[Nombre] ($_POST[recorrido_t])</td></tr>";
echo "<tr style='font-size:12px'><td>Responsable:</td><td>$NombreChofer[NombreChofer]</td></tr>"; 
echo "<tr style='font-size:12px'><td>Dominio:</td><td>$NombreChofer[Patente]</td></tr>";
echo "<tr style='font-size:12px'><td>Km. Recorridos:</td><td>$NombreChofer[KilometrosRecorridos]</td></tr>";
echo "<tr style='font-size:12px'><td>Total:</td><td>$sumarentregas</td></tr>";
echo "<tr style='font-size:12px'><td>Entregados:</td><td style='color:green'>$e</td></tr>";
echo "<tr style='font-size:12px'><td>No Entreados:</td><td style='color:red'>$ne</td></tr>";
$ef=number_format(($e/$sumarentregas)*100,2,',','.');  
echo "<tr style='font-size:12px'><td>Efectividad:</td><td style='color:blue'>$ef %</td></tr>";  
echo "</table>";
echo "</div>";  
}


ob_end_flush();  