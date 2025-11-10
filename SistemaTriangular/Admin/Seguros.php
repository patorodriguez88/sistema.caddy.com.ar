<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::Triangular Logistica::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/jquery.animated.innerfade.js"></script>
<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>
  <?php
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Alertas/alertas.html");     
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
echo "</div>"; //lateral
echo  "<div id='principal'>";

      echo "<form class='Caddy' action='' method='post' style='float:center; width:80%;' enctype='multipart/form-data'>";
    echo "<div><titulo>Ingresar Poliza de Seguro</titulos></div>";
    echo"<div><hr></hr></div>";
    $date=date('Y-m-d');
    echo "<div><label>Fecha:</label><input type='date' value='$date' name='fecha_t' readonly></div>";
    echo "<div><label>Patente:</label><select name='patente_t' required>";
    $sqlvehiculos=mysql_query("SELECT * FROM Vehiculos WHERE Activo = 'Si'");
    echo "<option value=''>Seleccione un Vehiculo</option>";
    while($row=mysql_fetch_array($sqlvehiculos)){
    echo "<option value='$row[Dominio]'>$row[Marca] $row[Modelo] ($row[Dominio])</option>";  
    }
    echo "</select></div>";
    echo "<div><label>Vigencia Desde:</label><input type='date' value='' name='vigenciadesde_t' required></div>";
    echo "<div><label>Vigencia Hasta:</label><input type='date' name='vigenciahasta_t' size='20' type='date'  required/></div>";
    echo "<div><label>Poliza Numero:</label><input name='numero_t' size='20' type='text' style='float:right;' value='' required/></div>";
    echo "<div><label>Empresa Aseguradora:</label><select name='empresa_t' title='Muestra Proveedores cargados con rubro Seguros' required>";
    $sqlvehiculos=mysql_query("SELECT * FROM Proveedores WHERE Rubro='Seguros'");
    echo "<option value=''>Seleccione una Opcion</option>";
    while($row=mysql_fetch_array($sqlvehiculos)){
    echo "<option value='$row[RazonSocial]'>$row[RazonSocial]</option>";  
    }
    echo "</select></div>";
    echo "<div><label style='font-size:10px;float:right;margin-top:0px;font-style: italic;color:gray'>Empresa Aseguradora: corresponde a Proveedores cargados con rubro Seguros.</label></div>";

  
    echo "<div><label>Subir Poliza:</label><input type='file' name='imagen' id='imagen' / style='float:right;width:350px'></div>";
    echo "<div><label style='font-size:10px;float:right;margin-top:0px;font-style: italic;color:gray'>Atencion: Cargar Solamente Archivo con extension .pdf </label></div>";
  
    echo "<div><input name='CargarSeguro' class='bottom' type='submit' value='Aceptar'></label></div>";
    echo "</form>";	
 
  if($_POST[CargarSeguro]=='Aceptar'){
 
  if ($_FILES["imagen"]["error"] > 0){
	echo "ha ocurrido un error";
} else {
	//ahora vamos a verificar si el tipo de archivo es un tipo de imagen permitido.
	//y que el tamano del archivo no exceda los 100kb
// 	$permitidos = array("image/jpg", "image/pdf", "image/gif", "image/png","application/pdf");
		$permitidos = array("image/pdf","application/pdf");

    $limite_kb = 10000;
$NumeroComprobante=$_POST[patente_t];
  
//   if ($_FILES['imagen']['size'] <= $limite_kb * 40960){
	if (in_array($_FILES['imagen']['type'], $permitidos) && $_FILES['imagen']['size'] <= $limite_kb * 40960){
		//esta es la ruta donde copiaremos la imagen
		//recuerden que deben crear un directorio con este mismo nombre
		//en el mismo lugar donde se encuentra el archivo subir.php
//     $ruta = "../FacturasCompra/" . $_FILES['imagen']['name'];
    $extension = end(explode(".", $_FILES['imagen']['name']));
    $ruta = "../Logistica/Polizas/" . $NumeroComprobante.".".$extension;
    //comprovamos si este archivo existe para no volverlo a copiar.
		//pero si quieren pueden obviar esto si no es necesario.
		//o pueden darle otro nombre para que no sobreescriba el actual.
		if (file_exists($ruta)){
      unlink("../Logistica/Polizas/" . $NumeroComprobante.".".$extension);
      //aqui movemos el archivo desde la ruta temporal a nuestra ruta
			//usamos la variable $resultado para almacenar el resultado del proceso de mover el archivo
			//almacenara true o false
			$resultado = @move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta);
			if ($resultado){
//         $sql=mysql_query("UPDATE Stock SET Imagen=1 WHERE id='$_POST[id2]'");
				?>
        <script>
        alertify.success("El archivo ha sido cargado exitosamente");
        </script>
<!--         echo "El archivo ha sido cargado exitosamente"; -->
        <?
        } else {
        ?>
        <script>
        alertify.error("Ocurrio un error al subir el archivo de la poliza.");
        </script>
        <?
      }
		} else {
      $resultado = @move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta);
			if ($resultado){
        ?>
        <script>
        alertify.success("El archivo ha sido cargado exitosamente");
        </script>
        <?
      } else {
        ?>
        <script>
        alertify.error("Ocurrio un error al subir el archivo de la poliza.");
        </script>
        <?
      }
    }
	} else {
            ?>
        <script>
        alertify.error("archivo no permitido, es tipo de archivo prohibido o excede el tamano de <? echo  $limite_kb;?> Kilobytes");
        </script>
        <?
  }
}
  
  //HASTA ACA PARA SUBIR EL ARCHIVO    
    $Desde=$_POST[vigenciadesde_t];
    $Hasta=$_POST[vigenciahasta_t];
    $Numero=$_POST[numero_t];
    $Empresa=$_POST[empresa_t];
    $Dominio=$_POST[patente_t];
    $sqlproveedores=mysql_query("SELECT * FROM Proveedores WHERE RazonSocial='$Empresa'");
    $datoproveedores=mysql_fetch_array($sqlproveedores);    
    $Telefono=$datoproveedores[Telefono]. " / ".$datoproveedores[Celular];
    
if($sql=mysql_query("UPDATE `Vehiculos` SET `FechaVencSeguro`='$Hasta',`Seguro`='$Empresa',`NumeroPoliza`='$Numero',
`TelefonoSeguro`='$Telefono' WHERE Dominio='$Dominio'")
    ){
   ?><script>alertify.success("Datos de la poliza cargados con exito en el vehiculo");</script><?
    }
  }

echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor

ob_end_flush();	
?>
</div>
</body>
</center>