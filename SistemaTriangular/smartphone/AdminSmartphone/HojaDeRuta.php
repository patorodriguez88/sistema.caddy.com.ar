<?php
ob_start();
session_start();
include_once "../ConexionSmartphone.php";
$user= $_SESSION['NCliente'];
$password= $_POST['password'];
$color='#B8C6DE'; 
$font='white';
date_default_timezone_set('America/Argentina/Buenos_Aires');

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Caddy. Yo lo llevo!</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="../assets/css/main.css" />
    <script type="text/javascript" src="../js/ubicacion.js"></script>
    <link rel="stylesheet" href="../signature-pad/assets/jquery.signaturepad.css">
		<link rel="stylesheet" href="../css/smartphone.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <style>
      .limpiar{
     position: absolute;
    top: 250px;
    right: 30px;
    transition: all 200ms;
    font-size: 50px;
    font-weight: bold;
    text-decoration: none;
    color: #F00;
 
      }
    </style>
    </head>
    <body class="subpage" onLoad="localize()">
    <div id="page-wrapper">
			<!-- Header -->
				<div id="header-wrapper">
					<header id="header" class="container">
						<div class="row">
							<div class="12u">
								<!-- Logo -->
									<h1><a href="#" id="logo">Caddy. Yo lo llevo! </a></h1>
                  <?php
                  include('../MenuSmartphone/MenuPrincipal.php');
                  ?>
							</div>
						</div>
					</header>
				</div>
<!-- Content -->
<?
// include('mapa.html');                      
// goto a;
?>
       <div id="content-wrapper">
					<div id="content">
						<div class="container">
              <div class="row">
								<div class="12u">
									<!-- Main Content -->
										<section>

<script>
  function select(i){
    event.preventDefault();
    if (i=='razones1'){
    document.getElementById(i).style.background='white';
    document.getElementById(i).style.color='rgb(90, 107, 134)';
    document.getElementById(i).style.border='rgb(90, 107, 134)';  
    document.getElementById('razones2').style.background='rgb(90, 107, 134)';
    document.getElementById('razones2').style.color='white';
    document.getElementById('razones3').style.background='rgb(90, 107, 134)';
    document.getElementById('razones3').style.color='white';
    document.getElementById('razones').value='NADIE EN CASA';
    }else if(i=='razones2'){
    document.getElementById(i).style.background='white';
    document.getElementById(i).style.color='rgb(90, 107, 134)'; 
    document.getElementById(i).style.border='rgb(90, 107, 134)';  
    document.getElementById('razones1').style.background='rgb(90, 107, 134)';
    document.getElementById('razones1').style.color='white';
    document.getElementById('razones3').style.background='rgb(90, 107, 134)';
    document.getElementById('razones3').style.color='white';
    document.getElementById('razones').value='DIRECCION EQUIVOCADA';
    }else if(i=='razones3'){
    document.getElementById(i).style.background='white';
    document.getElementById(i).style.color='rgb(90, 107, 134)';  
    document.getElementById(i).style.border='rgb(90, 107, 134)';  
    document.getElementById('razones1').style.background='rgb(90, 107, 134)';
    document.getElementById('razones1').style.color='white';
    document.getElementById('razones2').style.background='rgb(90, 107, 134)';
    document.getElementById('razones2').style.color='white';
    document.getElementById('razones').value='OTRAS RAZONES';
      }
  }
</script>                      
<script>
function cobrofinalizado(cobro){
  var cscobro=document.getElementById('cscobro').value;
  var emailrecibo=document.getElementById('emailrecibo').value; 
  var impcliente=document.getElementById('importecobrocliente_m').value;
  var impcaddy=document.getElementById('importecobrocaddy_m').value;

  var dato={
      "cobro":cobro,
      "codigoseguimiento_t":cscobro,
      "emailrecibo":emailrecibo,
      "impcaddy":impcaddy,
      "impcliente":impcliente,
            };
      $.ajax({
      data: dato,
      url:'Procesos/cobro.php',
      type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.resultado == "1")
          {
          alert('Cobrado Ok! Ahora podés volver al cliente y marcar la entrega.');
          document.getElementById('okcobrar').style.display='none';
          document.getElementById('todos').style.display='block';  
          }
         if (jsonData.resultado == "0")
           {
          alert('No pudiste cobrar? Avisar en administracion!');
             
           }
         
        }
 });
}


function finalizado(){
  var retirado=document.getElementById('retirado_t').value;
  var cs=document.getElementById('cs').value;
  var estado=document.getElementById('entregado_t').value;
  var nombre=document.getElementById('nombrecliente_t').value;
  var observaciones=document.getElementById('observaciones').value;
  var output=document.getElementById('output').value;
  var razones=document.getElementById('razones').value;
  var dni=document.getElementById('dni').value;
  var nombre2=document.getElementById('nombre').value;
  
  var dato={
      "retirado_t":retirado,
      "codigoseguimiento_t":cs, 
      "entregado_t":estado,
      "nombre_t":nombre,
      "observaciones_t":observaciones,
      "output":output,
      "razones":razones,
      "dni":dni,
      "nombre2":nombre2,
      };
      $.ajax({
      data: dato,
      url:'Procesos/estadoenvio1.php',
      type:'post',
        beforeSend: function(){
  
        $("#procesando").show();
        },
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.resultado == "1")
          {
          var i = document.getElementById('idveo').value;
          document.getElementById('okcobrar').style.display='none';   
          document.getElementById('ok').style.display='none'; 
          document.getElementById('todos').style.display='block';
          document.getElementById(i).style.display='none';    
          }
          else if(jsonData.resultado == '2')
          {
          alert('Paquete ya Entregado');
          }
        }
 });
}
  
function cerrar(){
  document.getElementById('ok').style.display='none'; 
  document.getElementById('todos').style.display='block';
  document.getElementById('okcobrar').style.display='none'; 
  //   document.getElementById('veo').style.display='block'; 
}
</script>

<script>
  function verok(id,i,p){
    var idveo=i;
//     var cs=document.getElementById('codigoseguimiento_t').value;
    document.getElementById('okcobrar').style.display='none'; 
    document.getElementById('todos').style.display='none';  
    var dato={
        "id": id,  
        };
        $.ajax({
        data: dato,
        url:'Procesos/buscodatos.php',
        type:'post',
        beforeSend: function(){
//         $("#buscando").html("Buscando...");
        },
            success: function(response)
            {
                var jsonData = JSON.parse(response);
                if (jsonData.success == "1")
                {
//                   if(jsonData.ImporteCobro!=0 || jsonData.Importecobrocaddy!=0){
//                   alert('Envio Cobrado'+jsonData.EnvioCobrado);
//                   alert('ImporteCobro'+jsonData.ImporteCobro);
//                   alert('Importecobrocaddy'+jsonData.Importecobrocaddy);
//                   alert(jsonData.pato);
                  if((jsonData.EnvioCobrado==0)&&(jsonData.ImporteCobro!=0 || jsonData.Importecobrocaddy!=0)){
                  document.getElementById('okcobrar').style.display='block';
                  var importetotal= Number(jsonData.ImporteCobro)+Number(jsonData.Importecobrocaddy);
                  document.getElementById('cscobro').value=jsonData.Seguimiento;   
                  $("#importecobro").html('El cliente pidio que cobremos: $'+jsonData.ImporteCobro); 
                  $("#importecobrocaddy").html('Costo Caddy Envios: $'+jsonData.Importecobrocaddy);
                  $("#importecobrototal").html('Total a Cobrar: $'+importetotal); 
                  document.getElementById('importecobrocliente_m').value=jsonData.ImporteCobro;
                  document.getElementById('importecobrocaddy_m').value=jsonData.Importecobrocaddy;
                  }else{
                  document.getElementById('ok').style.display='block';  
                  $("#nombrecliente").html(jsonData.Nombre);  
                  $("#posicion").html(jsonData.Posicion); 
                  $("#domicilio").html(jsonData.Domicilio); 
                  document.getElementById('idveo').value=idveo; 
                  document.getElementById('cs').value=jsonData.Seguimiento;   

                  if(p=='botonera1'){
                    document.getElementById('botones').style.display='block'; 
                    document.getElementById('firma').style.display='none';
                    document.getElementById('botonfinal').style.background='red';
                    document.getElementById('botonfinal').style.border='red';
                    document.getElementById('botonfinal').value='MARCAR COMO CANCELADO';  
                    document.getElementById('entregado_t').value='0';
                    }else{
  //                     alert(jsonData.ImporteCobro);
  //                   if(jsonData.ImporteCobro!=0){
  //                   $("#importecobro").html('ATENCION: El cliente pidio que cobremos: $'+jsonData.ImporteCobro);   
  //                   }else{
  //                   document.getElementById('importecobro').style.display='none';                   
  //                   }  
                    document.getElementById('botones').style.display='none';                    
                    document.getElementById('firma').style.display='block'; 
                    document.getElementById('botonfinal').style.background='green';
                    document.getElementById('botonfinal').style.border='green';
                    document.getElementById('retirado_t').value=jsonData.Retirado;  
                    if(jsonData.Retirado=="1"){
                      document.getElementById('botonfinal').value='MARCAR COMO ENTREGADO';
                      }else{
                      document.getElementById('botonfinal').value='MARCAR COMO RETIRADO';  
                      }
                    document.getElementById('entregado_t').value='1';
                    }  
                  }
                }
                  else
                  {

                      alert('Invalid Credentials!');
                  }
             }
      }); 
    }
  
</script>

<script>
function mostrar(){
  var codigoseguimiento=document.getElementById('remito').value;
  alert(codigoseguimiento);
  if(document.getElementById(codigoseguimiento).style.display=='block'){
  document.getElementById(codigoseguimiento).style.display='none';
  }else{
  document.getElementById(codigoseguimiento).style.display='block';
  }
}
</script>
<script> 
contenido_textarea = ""; 
num_caracteres_permitidos = 200;

function valida_longitud(){ 
   num_caracteres = document.forms[0].observaciones_t.value.length;

   if (num_caracteres > num_caracteres_permitidos){ 
      document.forms[0].observaciones_t.value = contenido_textarea; 
   }else{ 
      contenido_textarea = document.forms[0].observaciones_t.value;	
   } 

   if (num_caracteres >= num_caracteres_permitidos){ 
      document.forms[0].caracteres.style.color="#ff0000"; 
   }else{ 
      document.forms[0].caracteres.style.color="#000000"; 
   } 

   cuenta() 
} 
function cuenta(){ 
   document.forms[0].caracteres.value=document.forms[0].observaciones_t.value.length 
} 
</script> 
  <script>
    function verboton(){
    document.getElementById('veo').style.display='block';
    document.getElementById('listado').style.display='none';  
    }
</script>
  <script>
  (function(window) {
    var $canvas,
        onResize = function(event) {
          $canvas.attr({
            height: window.innerHeight,
            width: window.innerWidth
          });
        };

    $(document).ready(function() {
      $canvas = $('canvas');
      window.addEventListener('orientationchange', onResize, false);
      window.addEventListener('resize', onResize, false);
      onResize();

      $('form').signaturePad({
        drawOnly: true,
        defaultAction: 'drawIt',
        validateFields: false,
        lineWidth: 0,
        output: null,
        sigNav: null,
        name: null,
        typed: null,
        clear: 'input[type=reset]',
        typeIt: null,
        drawIt: null,
        typeItDesc: null,
        drawItDesc: null
      });
    });
  }(this));
  </script>
<img id="procesando" src="../images/loading.gif" alt="" style="display:none;position:absolute;z-index:999999;left:50%;top:50%;width:10%;height:20%">

<?
                      
//AVISAR QUE VOY
// if($_POST['Remito']=='Avisar que voy!'){
//   $CodigoSeguimiento=$_POST['codigoseguimiento_t'];
//   // DESDE ACA PARA AVISARLE MANUALMENTE AL CLIENTE QUE VOY PARA ALLA
//   $sql=mysql_query("SELECT Celular FROM HojaDeRuta WHERE Seguimiento='$CodigoSeguimiento' AND Eliminado=0 AND Avisado=0 ");
//   $Celular=mysql_fetch_array($sql);
//   $smsusuario = "SMSDEMO45010"; //usuario de SMS MASIVOS
//   $smsclave = "logistica"; //clave de SMS MASIVOS
//   $smsnumero = $Celular[Celular]; //coloca en esta variable el numero (pueden ser varios separados por coma)
//   $smstexto  = "Caddy le informa que su pedido de $_POST[razonsocial_t] esta en camino."; //texto del mensaje (hasta 160 caracteres)
//   //   ACTIVAR PARA ENVIAR LOS SMS 
//   $smsrespuesta = file_get_contents("http://servicio.smsmasivos.com.ar/enviar_sms.asp?API=1&TOS=". urlencode($smsnumero) ."&TEXTO=". urlencode($smstexto) ."&USUARIO=". urlencode($smsusuario) ."&CLAVE=". urlencode($smsclave) );
//   if($smsrespuesta){
//   $sqlAviso=mysql_query("UPDATE HojaDeRuta SET Avisado=1 WHERE Seguimiento='$CodigoSeguimiento' AND Eliminado=0");  
//   }
// }
  
//verificar si el fletero debe cobrar el envio
//   $sqlcobrar=mysql_query("SELECT Debe,CobrarEnvio,EnvioCobrado FROM TransClientes WHERE CodigoSeguimiento='$_POST[codigoseguimiento_t]' AND Eliminado=0");
//   $datosqlcobrar=mysql_fetch_array($sqlcobrar);
//   if($datosqlcobrar[CobrarEnvio]==1 && $datosqlcobrar[EnvioCobrado]==0){//VERIFICAR ESTO
  //desde aca cobramos
   // SDK de Mercado Pago
//  require_once '../../../../vendor/autoload.php';
// require __DIR__ .  '/vendor/autoload.php';
// $precio=$datosqlcobrar[Debe];
                      
// Agrega credenciales
// MercadoPago\SDK::setAccessToken('APP_USR-3440890679214265-032602-c68e5386033b7dbadb4ea0c5f87643f6-50894474');
// Crea un objeto de preferencia
// $preference = new MercadoPago\Preference();

// Crea un ítem en la preferencia
// $item = new MercadoPago\Item();
// $item->title = 'Servicio de envio Caddy';
// $item->quantity = 1;
// $item->unit_price = $precio;
// $preference = new MercadoPago\Preference();
// $preference->items = array($item);
// $preference = new MercadoPago\Preference();
// $preference->back_urls = array(
//     "success" => "https://www.caddy.com.ar/SistemaTriangular/smartphone/MP/success",
//     "failure" => "https://www.caddy.com.ar/SistemaTriangular/smartphone/MP/failure",
//     "pending" => "https://www.caddy.com.ar/SistemaTriangular/smartphone/MP/pending"
// );
// $preference->auto_return = "approved";
// $preference->save();
  ?>
<!-- <html data-elements-color="#8e44ad">
  <body>
  <form class='feature-image' action='' method='post'>
  <h2> Seguimiento de envio id: 
  <? 
//     echo $_POST['codigoseguimiento_t'];
  ?></h2>
  <h2>Importe del Servicio: $ 
  <? 
//     echo $precio;
  ?></h2>
  <h2>Este Remito debe ser cobrado, proceda con la cobranza...</h2>
    
    </form>
    <a  style="
  background-color: #5DADE2 ;
  color: #FFFFFF;
  border: 0px solid #111;
  border-radius: 2;
  text-decoration:none;
  padding:15px;"
 href="https://www.caddy.com.ar/SistemaTriangular/smartphone/MP/Cobrar2.php?cd=
  <? 
//       echo $_POST['codigoseguimiento_t'];
  ?>">Cobrar con Mercado Pago</a>
    <a  style="
  background-color: #5DADE2 ;
  color: #FFFFFF;
  border: 0px solid #111;
  border-radius: 2;
  text-decoration:none;
  padding:15px;"
 href="https://www.caddy.com.ar/SistemaTriangular/smartphone/AdminSmartphone/HojaDeRuta.php?Cobrar=null">No puedo Cobrar</a>

  </body>
</html> -->
<?
//   goto a;  
//   }
//DESDE ACA EL OKCOBRAR
echo "<div id='okcobrar' style='display:none'>";  
echo "<div style='height:24%;background:white;'></div>";                      
echo "<a id='cerrar' onclick='cerrar()' href='#'>&times;</a>";
echo "<form action='' class='login' style='width:auto;height:100%;min-height:360px;margin-top:2px;margin-bottom:10px;' method='post'>";
echo "<h2 style='padding:0'><span>EL ENVIO REQUIERE GESTION DE COBRO</span>";
// echo "<h2 style='font-weight: bold;'><span id='posicion'></span>) | <span style='text-transform: uppercase' id='nombreclientecobrar'></span></h2>";    
echo "<h1 style='padding:0'><span id='domiciliocobrar'></span>";
echo "<h1><span id='importecobro'></span></h1>";
echo "<h1><span id='importecobrocaddy'></span></h1>";

echo "<input type='hidden' id='importecobrocliente_m'>";
echo "<input type='hidden' id='importecobrocaddy_m'>";
                      
echo "<h2 style='font-weight:bold;color:red'><span id='importecobrototal'></span></h2>";
echo "<div><input type='tel' id='cobrado' name='importecobrado' placeholder='$ Colocar aqui Importe Cobrado si difiere del total' style='width:100%;margin-top:0px' ></div>";                      
// echo "<input type='text' name='caracteres' size='4' style='border:none;rigth:0px' readonly></h1>";
echo "<input type='hidden' name='cscobro' id='cscobro' value=''>";                      
echo "<textarea rows='2' style='width:100%' name='observaciones_t' id='observaciones' value='' onKeyDown='valida_longitud()' onKeyUp='valida_longitud()' placeholder='Escribe comentarios aquí...'></textarea>";
echo "<div><input type='email' id='emailrecibo' name='nombre' placeholder='Email para el recibo de pago' style='width:100%;margin-booton:0px' ></div>";
// echo "<div><input type='text' id='dni' name='dni' placeholder='D.N.I del Receptor' style='width:100%;margin-top:0px' ></div>";                      
// echo "<h1 style='font-weight:bold;color:red'><span id='importecobro'></span></h1>";
// echo "<input type='hidden' id='nombrecliente_t' name='nombrecliente' value=''>";	
// echo "<input type='hidden' id='lti' name='latitud' value=''>";	
// echo "<input type='hidden' id='lgi' name='longitud' value=''>";	
// echo "<input type='hidden' name='cs' id='cs' value=''>";
// echo "<input type='hidden' name='entregado_t' id='entregado_t' value=''>";
// echo "<div id='botones' style='display:none'>";
// echo "<button id='razones1' value='NADIE EN CASA' class='button-select' onClick='select(this.id)'>NADIE EN CASA</button>";
// echo "<button id='razones2' value='DIRECCION EQUIVOCADA' class='button-select' onClick='select(this.id)'>DIRECCION EQUIVOCADA</button>";
// echo "<button id='razones3' value='OTRAS RAZONES' class='button-select' onClick='select(this.id)'>OTRAS RAZONES</button>";
// echo "<input type='hidden' id='razones'>";
// echo "</div>";
// echo "<textarea rows='1' style='width:100%' name='observaciones_t' id='observaciones' value='' onKeyDown='valida_longitud()' onKeyUp='valida_longitud()' placeholder='Escribe comentarios aquí...'></textarea>";
// echo "<div id='firma' class='sigPad' style='outline: 2px dashed #aaa;height:198px;width:100%'>";
//   echo "<div>";
//     echo "<div class='typed'></div>";
//         echo "<canvas id='firmapad' class='pad' width='100%' height='198px' ></canvas>";
//         echo "<input type='hidden' name='output' id='output' class='output'>";   
// //         echo "<fieldset>";
// //           echo "<input type='reset' value='Limpiar' />";
// //         echo "</fieldset>";
//   echo "</div>";
// echo "</div>";
// echo "<input type='hidden' id='idveo'>";                      
echo "<input id='botonfinalcobro' class='button-big' type='button' style='width:100%;' value='COBRADO' onClick='cobrofinalizado(this.value)'>";
echo "<input id='botonfinalcobro1' class='button-big' type='button' style='width:100%' value='NO PUDE COBRAR' onClick='cobrofinalizado(this.value)'>";
echo "</form>";	
echo "</div>";//ENTREGADO     
                      
//DESDE ACA EL OK
echo "<div id='ok'>";  
echo "<div style='height:14%;background:white;'></div>";                      
echo "<a id='cerrar' onclick='cerrar()' href='#'>&times;</a>";
echo "<form action='' class='login' style='width:auto;height:100%;min-height:360px;margin-top:2px;margin-bottom:10px;' method='post'>";
echo "<h2 style='font-weight: bold;'><span id='posicion'></span>) | <span style='text-transform: uppercase' id='nombrecliente'></span></h2>";    
echo "<h1 style='padding:0;font-size:12px'><span id='domicilio'></span>";
echo "<input type='text' name='caracteres' size='4' style='border:none;rigth:0px' readonly></h1>";
echo "<div><input type='text' id='nombre' name='nombre' placeholder='Nombre del Receptor' style='width:100%;margin-booton:0px' required></div>";
echo "<div><input type='text' id='dni' name='dni' placeholder='D.N.I del Receptor' style='width:100%;margin-top:0px' ></div>";                      
echo "<h1 style='font-weight:bold;color:red'><span id='importecobro'></span></h1>";
echo "<input type='hidden' id='nombrecliente_t' name='nombrecliente' value=''>";	
echo "<input type='hidden' id='lti' name='latitud' value=''>";	
echo "<input type='hidden' id='lgi' name='longitud' value=''>";	
echo "<input type='hidden' name='cs' id='cs' value=''>";
echo "<input type='hidden' name='entregado_t' id='entregado_t' value=''>";
echo "<div id='botones' style='display:none'>";
echo "<button id='razones1' value='NADIE EN CASA' class='button-select' onClick='select(this.id)'>NADIE EN CASA</button>";
echo "<button id='razones2' value='DIRECCION EQUIVOCADA' class='button-select' onClick='select(this.id)'>DIRECCION EQUIVOCADA</button>";
echo "<button id='razones3' value='OTRAS RAZONES' class='button-select' onClick='select(this.id)'>OTRAS RAZONES</button>";
echo "<input type='hidden' id='razones'>";
echo "</div>";
// echo "<div>";                      
echo "<textarea rows='1' style='width:100%' name='observaciones_t' id='observaciones' value='' onKeyDown='valida_longitud()' onKeyUp='valida_longitud()' placeholder='Escribe comentarios aquí...'></textarea>";
// echo "<input style='width:10%' type='file' accept=image/*' capture='camera' name='imagen'>";
// echo "</div>";
echo "<div id='firma' class='sigPad' style='outline: 2px dashed #aaa;height:198px;width:100%'>";
  echo "<div>";
    echo "<div class='typed'></div>";
        echo "<canvas id='firmapad' class='pad' width='100%' height='198px' ></canvas>";
        echo "<input type='hidden' name='output' id='output' class='output'>";   
//         echo "<fieldset>";
//           echo "<input type='reset' value='Limpiar' />";
//         echo "</fieldset>";
  echo "</div>";
echo "</div>";
echo "<input type='hidden' id='idveo'>";                      
echo "<input id='botonfinal' class='button-big' type='button' style='width:100%' value='MARCAR COMO FINALIZADO' onClick='finalizado()'>";
echo "</form>";	
echo "</div>";//ENTREGADO                      

$sqlC = mysql_query("SELECT * FROM Logistica WHERE idUsuarioChofer='".$_SESSION['idusuario']."' AND Estado='Cargada' AND Eliminado='0'");
$Dato=mysql_fetch_array($sqlC);
$_SESSION['RecorridoAsignado']=$Dato[Recorrido];

$sql=mysql_query("SELECT * FROM `TransClientes`,`HojaDeRuta` 
WHERE TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
AND TransClientes.Entregado='0'
AND TransClientes.Recorrido='".$_SESSION['RecorridoAsignado']."' 
AND TransClientes.Eliminado='0' 
AND HojaDeRuta.Eliminado='0' 
AND TransClientes.Devuelto='0'
AND HojaDeRuta.Estado='Abierto' ORDER BY HojaDeRuta.Posicion ASC");                      

// echo "<form id='listado' class='' action='' method='post'>";
    $sqlRecorridos=mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$_SESSION[RecorridoAsignado]'");
    $datosql=mysql_fetch_array($sqlRecorridos);                      
    if(mysql_num_rows($sqlC)==0){
    echo "<form id='listado' class='' action='' method='post'>";  
    echo "<h2>No hay Recorridos disponibles</h2>";
    echo "</form>";
    goto a;  
    }
    //CANTIDADES
    $sqlCantidad=mysql_query("SELECT COUNT(id)as Cantidad FROM HojaDeRuta WHERE Recorrido='$_SESSION[RecorridoAsignado]' AND Eliminado=0 AND Estado='Abierto' AND NumerodeOrden='$Dato[NumerodeOrden]' AND Devuelto='0'");
    $CantidadPendientes=mysql_fetch_array($sqlCantidad);
    $sqlCantidadTotal=mysql_query("SELECT COUNT(id)as Cantidad FROM HojaDeRuta WHERE Recorrido='$_SESSION[RecorridoAsignado]' AND Eliminado=0 AND NumerodeOrden='$Dato[NumerodeOrden]' AND Devuelto='0'");
    $TotalCantidad=mysql_fetch_array($sqlCantidadTotal);
    $Pendientes=$TotalCantidad[Cantidad]-$CantidadPendientes[Cantidad];
    
    echo "<div id='recorrido'><h2>Recorrido: ".$_SESSION['RecorridoAsignado']."</h2></div>";//RECORRIDO                     
    echo "<div id='circulo' style='right:100px;background:#82E0AA'><h2>$CantidadPendientes[Cantidad]</h2></div>";//ENTREGADOS
    echo "<div id='circulo' style='right:55px;background:#F1948A'><h2>$Pendientes</h2></div>";//PENDIENTES
    echo "<div id='circulo' style='background:gray'><h2>$TotalCantidad[Cantidad]</h2></div>";
                      
    if(mysql_numrows($sql)==0){
    $sqlproximo = mysql_query("SELECT * FROM Logistica WHERE idUsuarioChofer='".$_SESSION['idusuario']."' AND Estado='Cargada' AND Eliminado='0' AND id<>'$Dato[id]'");
    $datoproximo=mysql_fetch_array($sqlproximo);
      if(mysql_numrows($sqlproximo)<>0){
      echo "<h3>Proximo Recorrido Asignado: Recorrido $datoproximo[Recorrido]</h3>";  
      goto a;
      }
    }              
echo "<div id='todos'>"; 
$i=0;
                      
while($row=mysql_fetch_array($sql)){
$_SESSION[Localizacion]=$row[DomicilioDestino];
  //ACA REEMPLAZAR ingBrutosOrigen por el ID DEL CLIENTE EMISOR
// $sqlBuscoidProveedor=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$row[ClienteDestino]' AND Relacion='$row[IngBrutosOrigen]'");
// $idProveedor=mysql_fetch_array($sqlBuscoidProveedor);  

$sqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]' ORDER BY id DESC");
$Seguimiento=mysql_fetch_array($sqlSeguimiento); 

if($row[Retirado]==0){
  //ACA REEMPLAZAR ingBrutosOrigen por el ID DEL CLIENTE EMISOR
$sqlBuscoidProveedor=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$row[RazonSocial]' AND Relacion='$row[IngBrutosOrigen]'");
$idProveedor=mysql_fetch_array($sqlBuscoidProveedor);  
$Retirado=0;  
$Servicio='Retiro';
$Direccion=$row[DomicilioOrigen];
$NombreCliente=$row[RazonSocial];  
  if(strlen($row[TelefonoOrigen])>='10'){
    if(substr($row[TelefonoOrigen], 0, 2)<>'54'){
    $Contacto='54'.$row[TelefonoOrigen];
    }else{
    $Contacto=$row[TelefonoOrigen];  
    } 
  $veocel=1;
  }else{
  $veocel=0;  
  }  
}else{
  //ACA REEMPLAZAR ingBrutosOrigen por el ID DEL CLIENTE EMISOR
$sqlBuscoidProveedor=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$row[ClienteDestino]' AND Relacion='$row[IngBrutosOrigen]'");
$idProveedor=mysql_fetch_array($sqlBuscoidProveedor);  
$Retirado=1;  
$Servicio='Entrega';    
if($row[PisoDeptoDestino]<>''){  
$Direccion=$row[DomicilioDestino].'|'.$row[PisoDeptoDestino];
}else{
$Direccion=$row[DomicilioDestino];  
}  
$NombreCliente=$row[ClienteDestino];    
  if(strlen($row[TelefonoDestino])>='10'){
    if(substr($row[TelefonoDestino], 0, 2)<>'54'){
    $Contacto='54'.$row[TelefonoDestino];
    }else{
    $Contacto=$row[TelefonoDestino];  
    }  
  $veocel=1;
  }else{
  $veocel=0;
  }
 }
    echo "<div id='$i'>";
    echo "<form  class='login' action='' method='post' style='width:auto;height:auto;min-height:450px;margin-top:20px;margin-bottom:10px;'>";  
    if($idProveedor[idProveedor]<>''){
    $idProveedortxt= "(".$idProveedor[idProveedor].")"; 
    }
    echo "<h2 style='font-weight: bold;text-transform: uppercase;'><i style='font-size:16px'>($row[Posicion]) | $idProveedortxt</i> $NombreCliente</h2>";
//     echo "<h1>$row[DomicilioDestino]</h1>";
    echo "<h1>$Direccion</h1>";
    echo "<h1>Remito: $row[NumeroComprobante] | $row[CodigoSeguimiento]</h1>";	
    if($veocel==1){
    echo "<h1>Celular: $Contacto <a style='float:right;margin-right:14%;' href='https://api.whatsapp.com/send?phone=$Contacto&text=Hola!,%20Mi%20nombre%20es%20$_SESSION[NombreUsuario]%20soy%20de%20Caddy%20Logística%20!%20Estoy%20en%20camino%20para%20entregarte%20tu%20pedido...'>
    <img id='1' src='../images/wp.png' width='30' height='30'/>$Celular[Celular]</a></h1>";	
    }else{
    echo "<h1>Celular: Sin Datos</h1>";  
    }
    echo "<h1 style='font-weight: bolder;'>$Servicio de: $row[Cantidad] paquetes</h1>";
      
    //-----START ASGINACIONES-----
    $FechaAsignacion=date('Y-m-d');
    $sqlasignaciones=mysql_query("SELECT * FROM Asignaciones WHERE idProveedor='$idProveedor[idProveedor]' and Relacion='$row[IngBrutosOrigen]' AND Fecha='$FechaAsignacion'");
    if(mysql_num_rows($sqlasignaciones)<>0){
      echo "<table class='rwd_auto'style='margin-bottom:0px'>";
      echo "<th>Nombre</th>";
      echo "<th>Edicion</th>";
      echo "<th>Cantidad</th>";
      while($datosasignaciones=mysql_fetch_array($sqlasignaciones)){
           $sqlasigproductos=mysql_query("SELECT * FROM AsignacionesProductos WHERE CodigoProducto='$datosasignaciones[CodigoProducto]' AND Relacion='$row[IngBrutosOrigen]'");
           $datosasigproducto=mysql_fetch_array($sqlasigproductos);
      echo "<tr>";
      echo "<td style='text-align:left'>$datosasigproducto[Nombre]</td>";
      echo "<td>$datosasignaciones[Edicion]</td>";
      echo "<td style='font-weight: bold;'>$datosasignaciones[Cantidad]</td>";
      echo "<tr>";
      }
      echo "</table>";
    //-----END ASIGNACIONES------
      }
echo "<h1>Observaciones:</h1><textarea style='width:100%;height:50px;border:0px;background:none' type='text' readonly>$row[Observaciones]</textarea>";
echo "<input type='hidden' name='codigoseguimiento_t' id='codigoseguimiento_t' value='$row[CodigoSeguimiento]'>";	
echo "<input type='hidden' name='razonsocial_t' id='razonsocial_t' value='$row[NumeroComprobante]'>";
echo "<input type='hidden' name='retirado_t' id='retirado_t' value='$Retirado'>";
  
$a=$row[id];
echo "<a style='position:relative;bottom:0px;right:10px;float:left;margin-left:15%;' href='#'><img id='botonera1' src='../images/wrong.png' width='60' height='60' Onclick='verok($a,$i,this.id)' /></a>";
echo "<a style='position:relative;bottom:7px;float:left;margin-left:3%;' href='http://maps.google.com/?q=$Direccion', '_system', 'location=yes');' target='_blank'><img src='../images/goto.png' width='70' height='70' /></a>";
echo "<a style='float:left;margin-left:6%;' href='#'><img id='botonera2' src='../images/ok.png' width='60' height='60' Onclick='verok($a,$i,this.id)' /></a>";
echo "<input type='hidden' name='id' value='$row[id]'>";
echo "<input type='hidden' name='Posicion' value='$row[Posicion]'>";  
echo "</form>";
echo "</div>"; 
  $i++;  
}
echo "</div>";  
                      
                      
                      
a:

ob_end_flush();		
		?>
</section>											
<!-- <div class="row">
<div class="6u 12u(mobile)"> -->

								</div>
							</div>
						</div>
					</div>
				</div>
		<!-- Scripts -->
			<script src="../assets/js/jquery.min.js"></script>
			<script src="../assets/js/skel.min.js"></script>
			<script src="../assets/js/skel-viewport.min.js"></script>
			<script src="../assets/js/util.js"></script>
			<script src="../assets/js/main.js"></script>
<!--       <script src="../signature-pad/assets/json2.min.js"></script> -->

<!--       Firma -->
  <script src="../signature-pad/jquery.signaturepad.js"></script>
  <script>
    $(document).ready(function() {
      $('.sigPad').signaturePad({drawOnly:true});
    });
  
</script>
     <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.6.1.min.js"></script>
<!--      <script src="../signature-pad/jquery.signaturepad.js"></script>
 -->
	</body>
</html>
