<?
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);  
date_default_timezone_set('America/Argentina/Buenos_Aires');


if($_POST[enviar]==1){
$id=$_POST[id];
$CodigoSeguimiento=$_POST[CodigoSeguimiento]; 

$sql=mysql_query("SELECT * FROM Clientes WHERE id='$id'");
$row=mysql_fetch_array($sql);
$NombreMail=$row[nombrecliente];  

  $uniqueid= uniqid('np');
  //indicamos las cabeceras del correo
$headers = "MIME-Version: 1.0\r\n";
$headers .= "From: ".$row[Mail]." \r\n";
$headers .= "Subject: Estado de tu envío Caddy\r\n";
//lo importante es indicarle que el Content-Type
//es multipart/alternative para indicarle que existirá
//un contenido alternativo
$headers .= "Content-Type: multipart/alternative;boundary=" . $uniqueid. "\r\n";
$codigo=base64_encode("codigo=$CodigoSeguimiento");
$message = "";
//SIN FORMATO 
$message .= "\r\n\r\n--" . $uniqueid. "\r\n";
$message .= "Content-type: text/plain;charset=utf-8\r\n\r\n";
$message .= "¡Hola ".$NombreMail."!"."\n";
$message .= "Gracias por confiar en Caddy.!"."\n";
$message .= "Queremos avisarte que el repartidor nos informo que tu envío $CodigoSeguimiento esta yendo para tu domicilio !!"."\n";
$message .= "Si queres podes seguir tu envio en https://www.caddy.com.ar/Seguimiento.html?codigo=$codigo"."\n\n";
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
$message .= "Queremos avisarte que el repartidor nos informo que tu envío $CodigoSeguimiento esta yendo para tu domicilio !!"."\n";
$message .= "Si queres podes seguir tu envío <a href='https://www.caddy.com.ar/seguimiento.html?$codigo'> aquí </a>"."\n\n";
$message .= "Ante cualquier inconveniente, duda o consulta sobre este envío o sobre alguno de nuestros servicios, no dudes en ponerte en contacto con nosotros.."."<br><br>";
$message .= "Una vez más, gracias por confiar a Caddy el envío de tus productos."."<br><br>";
$message .= "Saludos,"."<br>\n";
$message .= "<b>El equipo de Caddy Logística</b> "."<br><br>\n";
$message .= "<img style='width: 30%; height: 10%; margin-top: 0px' src='https://www.caddy.com.ar/images/LogoCaddy.png'/>";
$message .= "\r\n\r\n--" . $uniqueid. "--";
 
//con la función mail de PHP enviamos el mail.
  if($row[Mail]<>''){
  mail($row[Mail], utf8_decode('Tu envío de Caddy fue entregado con éxito!'), utf8_decode($message), $headers);
  echo json_encode(array('resultado' => 1));
  }else{
  echo json_encode(array('resultado' => $NombreMail));  
  }
}  
?>