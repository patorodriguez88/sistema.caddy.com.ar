<?php
session_start();

$sql=mysql_query("SELECT * FROM `TransClientes`,`HojaDeRuta` 
WHERE TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
AND TransClientes.Retirado='1'
AND TransClientes.Entregado='0'
AND TransClientes.Recorrido='".$_SESSION['RecorridoAsignado']."' 
AND TransClientes.Eliminado='0' 
AND TransClientes.Devuelto='0' 
AND HojaDeRuta.Eliminado='0'
AND HojaDeRuta.Estado='Abierto' ORDER BY HojaDeRuta.Posicion ASC");                      

$sql2=mysql_query("SELECT * FROM `TransClientes`,`HojaDeRuta` 
WHERE TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
AND TransClientes.Retirado='0'
AND TransClientes.Entregado='0'
AND TransClientes.Recorrido='".$_SESSION['RecorridoAsignado']."' 
AND TransClientes.Eliminado='0' 
AND TransClientes.Devuelto='0' 
AND HojaDeRuta.Eliminado='0'
AND HojaDeRuta.Estado='Abierto' ORDER BY HojaDeRuta.Posicion ASC");                      

?>
<!-- Nav -->
<nav id="nav">
<a href="https://www.caddy.com.ar/SistemaTriangular/smartphone/AdminSmartphone/Transito.php">Remito en Transito</a>
<a href="https://www.caddy.com.ar/SistemaTriangular/smartphone/AdminSmartphone/HojaDeRuta.php">Servicios <? echo "(".mysql_numrows($sql).")";?></a> 
<a href="https://www.caddy.com.ar/SistemaTriangular/smartphone/AdminSmartphone/CerrarRecorrido.php">Cerrar Briefing</a> 
<?php
$sqlC = mysql_query("SELECT * FROM Logistica WHERE idUsuarioChofer='".$_SESSION['idusuario']."' AND Estado='Cargada' AND Eliminado='0'");
$Dato=mysql_fetch_array($sqlC);
$NumeroComprobante=$Dato[Patente];
 
$extension = explode(".",$NumeroComprobante,2);
$ruta = "../../Logistica/Polizas/" . $NumeroComprobante.".pdf";
    //comprovamos si este archivo existe para no volverlo a copiar.
		//pero si quieren pueden obviar esto si no es necesario.
		//o pueden darle otro nombre para que no sobreescriba el actual.
		if (file_exists($ruta)){
    ?>
    <a href="https://www.caddy.com.ar/SistemaTriangular/Logistica/Polizas/<? echo $NumeroComprobante;?>.pdf">Ver Poliza <? echo $Dato[Patente];?></a> 
    <?
    }                       
    ?>
<!-- <a href="https://www.caddy.com.ar/SistemaTriangular/smartphone/MP/Cobrar.php">Cobrar</a>  -->
<a href="https://www.caddy.com.ar/SistemaTriangular/smartphone/Salir.php">Salir</a> 
  </nav>