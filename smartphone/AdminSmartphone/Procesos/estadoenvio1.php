<?
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);  
mysqli_set_charset($mysqli,"utf8"); 
date_default_timezone_set('America/Argentina/Buenos_Aires');

$CodigoSeguimiento=$_POST['codigoseguimiento_t'];
$dni=$_POST['dni'];
$nombre2=$_POST['nombre2'];
$sqlLocalizacion=mysql_query("SELECT DomicilioDestino,LocalidadDestino,Redespacho,IngBrutosOrigen FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");  
$sqlLocalizacionR=mysql_fetch_array($sqlLocalizacion);
$Localizacion=$sqlLocalizacionR[DomicilioDestino];    
//BUSCO LOS DATOS PARA EL ENVIO DEL MAIL
$sqlmail=mysql_query("SELECT nombrecliente,Mail FROM Clientes WHERE id='$sqlLocalizacionR[IngBrutosOrigen]'");
$datomail=mysql_fetch_array($sqlmail);
$NombreMail=$datomail[nombrecliente];
$EmailMail=$datomail[Mail];
$Fecha= date("Y-m-d");	
$Hora=date("H:i"); 
$Usuario=$_SESSION['Usuario'];
$Sucursal=$_SESSION['Sucursal'];
$Localizacion=$_SESSION[Localizacion];  


if($_POST['output']<>''){
  if($_POST['retirado_t']==1){
  $firma='R';
  }else{
  $firma='E';  
  }

  require_once '../../signature-pad/signature-to-image.php';
$json = $_POST['output']; // From Signature Pad
$img = sigJsonToImage($json);
$Nombrefirma=$firma.'-'.$CodigoSeguimiento;  
// $img = sigJsonToImage(file_get_contents('../signature-pad/examples/sig-output.json'));
// Save to file
imagepng($img, '../../../images/Firmas/'.$Nombrefirma.'.png');
// Output to browser
// header('Content-Type: image/png');
// imagepng($img);
// Destroy the image in memory when complete
imagedestroy($img);
}


// COMPRUEBO SI LA ENTREGA NECESITA REDESPACHO
$sqlredespacho=mysql_query("SELECT * FROM Localidades WHERE Localidad='$sqlLocalizacionR[LocalidadDestino]'");
$datosql=mysql_fetch_array($sqlredespacho);
if($datosql[Web]==0){
$Redespacho=0;//MODIFICAR ESTO CUANDO TENGAS OK  
}else{
$Redespacho=0;   
}

// SI TIENE REDESPACHO SOLO MODIFICO LA TABLA SEGUIMENTO SIN PONER ENTREGADA AL CLIENTE PERO SI PONGO ENTREGADA EN TRANS CLIENTES
  
if($sqlLocalizacionR[Redespacho]==0){
  if($_POST['entregado_t']==1){
    if($_POST['retirado_t']==1){
      $Entregado='1';  
      $Estado='Entregado al Cliente';	
      $Retirado='0';
      
      
      
      
      }else{
      $Entregado='0';
      $Retirado='1';
      $Estado='Retirado del Cliente';	
    }    
   }elseif($_POST['entregado_t']==0){
          if($_POST['retirado_t']==1){
          $Entregado='0';  
          $Retirado='0';
          $Estado='No se pudo entregar';	
          }else{
          $Retirado='0';
          $Entregado='0';  
          $Estado='No se pudo Retirar';	  
          }
    }  
}else{
  if($_POST['entregado_t']==1){
    $Entregado='0';  
    $Estado='En Transito | Redespacho';	
    }elseif($_POST['entregado_t']==0){
    $Entregado='0';  
    $Estado='No se pudo entregar';	
  }  
}
if($_POST['nombre2']==''){
$NombreCompleto=$_POST['nombre_t'];
}else{
$NombreCompleto=$_POST['nombre2'];
$Dni=$_POST['dni'];	  
}

if($_POST[razones]<>''){
$Observaciones=$_POST[razones]." | ".$_POST['observaciones_t'];
}else{
$Observaciones=$_POST['observaciones_t'];
  
}
//BUSCO QUE NUMERO DE VISITA ES
$sqlvisita=mysql_query("SELECT MAX(Visitas)as Visita FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento'");
$visita=mysql_fetch_array($sqlvisita);
$Visita=$visita[Visita]+1;

$sql="INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,NombreCompleto,Dni,Destino,Visitas,Retirado)
 VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$CodigoSeguimiento}','{$Observaciones}','{$Entregado}','{$Estado}','{$NombreCompleto}'
 ,'{$Dni}','{$Localizacion}','{$Visita}','{$Retirado}')";
$verifico=mysql_query("SELECT id FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Entregado=1");
//ACTUALIZO TRANSCLIENTES
$sqlT="UPDATE TransClientes SET Entregado='$Entregado',Retirado='$Retirado' WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0";
mysql_query($sqlT);
//ACTUALIZO HOJA DE RUTA SIEMPRE A CERRADO PARA QUE NO FIGURE MAS EN EL SISTEMA DE SMARTPHONE
$sqlhdr="UPDATE HojaDeRuta SET Estado='Cerrado' WHERE Seguimiento='$CodigoSeguimiento' AND Eliminado=0";
mysql_query($sqlhdr);	

if(mysql_num_rows($verifico) == '0'){
  if(mysql_query($sql)){
//ENVIO MAIL AL CLIENTE
//creamos un identificador único
//para indicar que las partes son idénticas
$uniqueid= uniqid('np');
 
//indicamos las cabeceras del correo
$headers = "MIME-Version: 1.0\r\n";
$headers .= "From: prodriguez@caddy.com.ar \r\n";
$headers .= "Subject: Estado de tu envío Caddy\r\n";
//lo importante es indicarle que el Content-Type
//es multipart/alternative para indicarle que existirá
//un contenido alternativo
$headers .= "Content-Type: multipart/alternative;boundary=" . $uniqueid. "\r\n";
 
$message = "";
//SIN FORMATO 
$message .= "\r\n\r\n--" . $uniqueid. "\r\n";
$message .= "Content-type: text/plain;charset=utf-8\r\n\r\n";
$message .= "¡Hola ".$NombreMail."!"."\n";
$message .= "Gracias por confiar en Caddy.!"."\n";
$message .= "Queremos avisarte que el repartidor nos informo que tu envío $CodigoSeguimiento fue entregado con éxito a las $Hora hs. y lo recibió $_POST[nombre2] !"."\n";
$message .= "Ante cualquier inconveniente, duda o consulta sobre este envío o sobre alguno de nuestros servicios, no dudes en ponerte en contacto con nosotros.."."\n\n";
$message .= "Una vez más, gracias por confiar a Caddy el envío de tus productos."."\n\n";
$message .= "Saludos,"."\n";
$message .= "El equipo de Caddy Logística "."\n";
$message .= "Caddy, yo lo llevo!.";
//CON FORMATO 
$message .= "\r\n\r\n--" . $uniqueid. "\r\n";
$message .= "Content-type: text/html;charset=utf-8\r\n\r\n";
$message .= "<b>¡Hola </b> ".$NombreMail."!"."\n";
$message .= "Gracias por confiar en <b>Caddy</b>."."<br>\n";
$message .= "Queremos avisarte que el repartidor nos informo que tu envio fue <b>entregado</b> con éxito a las $Hora hs. y lo recibió $_POST[nombre2] !"."<br>\n";
$message .= "Ante cualquier inconveniente, duda o consulta sobre este envío o sobre alguno de nuestros servicios, no dudes en ponerte en contacto con nosotros.."."<br><br>";
$message .= "Una vez más, gracias por confiar a Caddy el envío de tus productos."."<br><br>";
$message .= "Saludos,"."<br>\n";
$message .= "<b>El equipo de Caddy Logística</b> "."<br><br>\n";
// $message .= "Cofundador de Caddy."."<br><br>";
$message .= "<img style='width: 20%; height: 10%; margin-top: 0px' src='https://www.caddy.com.ar/images/LogoCaddy.png'/>";
$message .= "\r\n\r\n--" . $uniqueid. "--";
 
//con la función mail de PHP enviamos el mail.
if($EmailMail<>''){
mail($EmailMail, utf8_decode('Tu envío de Caddy fue entregado con éxito!'), utf8_decode($message), $headers);
}

  echo json_encode(array('resultado' => 1));
  }
}else{
  echo json_encode(array('resultado' => 2));
}  
