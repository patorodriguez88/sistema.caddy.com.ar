<?
ob_start();
session_start();
if($_SESSION[NCliente]==''){
header("location:https://www.caddy.com.ar/iniciosesion.php");  
}
// include("../SeguridadUsuarioWeb.php");
include("../ConexionBD.php");
// require("../Funciones/generarCodigo.php");
$sql=mysql_query("SELECT * FROM Clientes WHERE id='$_SESSION[NCliente]'");
$Dato=mysql_fetch_array($sql);

if($_POST[Proceso]=='Confirmar Envio'){
  //Controlo si el cliente tiene establecidos Servicios
  $sqlBuscoClienteDestino=mysql_query("SELECT * FROM Clientes WHERE id='$_POST[idok]'");
  $DatosClienteDestino=mysql_fetch_array($sqlBuscoClienteDestino);
  $LocalidadDestino=$DatosClienteDestino[LocalidadDestino];
// $CodigoSeguimiento=generarCodigo(7);
  $CodigoSeguimiento="";

  $Debe=$_POST['final'];
  $ClienteDestino=$_POST[clientedestinook];
  $Contacto=$_POST[contacto];
  $Observaciones=$_POST[observaciones];
  $DomicilioDestino=$_POST['end'];
  $Cantidad=$_POST[cantidad];
  $DomicilioOrigen=$_POST[start];
  $idClienteDestino=$_POST[idok];
  $Km=$_POST[kilometros];
  $Entrega='Domicilio';
  $FormaDePago=$_POST[fpok];
$sqlMax=mysql_query("SELECT MAX(NumeroVenta)as NumeroVenta FROM PreVenta WHERE Eliminado=0 AND id='$_SESSION[NCliente]'");
if ($row = mysql_fetch_array($sqlMax)) {
 $DatoNV = trim($row[NumeroVenta])+1;
 }
$Codigo='49';  
$Fecha=date('Y-m-d');  
$sql=mysql_query("INSERT INTO `PreVenta`(`Fecha`, `RazonSocial`, `NCliente`, `TipoDeComprobante`, `NumeroComprobante`, `Cantidad`, `Precio`, 
`Total`, `ClienteDestino`, `idClienteDestino`,  `DomicilioDestino`, `LocalidadDestino`, `CodigoSeguimiento`, `NumeroVenta`,
`DomicilioOrigen`, `LocalidadOrigen`, `Usuario`, `Cargado`, `FormaDePago`, `EntregaEn`, `Eliminado`, `Observaciones`, `Transportista`, `Recorrido`,
`ProvinciaDestino`, `ProvinciaOrigen`, `Kilometros`) VALUES 
 ('{$Fecha}','{$Dato[nombrecliente]}','{$_SESSION[NCliente]}','SOLICITUD WEB','{$Codigo}','{$Cantidad}','{$Debe}','{$Debe}',
  '{$ClienteDestino}','{$idClienteDestino}','{$DomicilioDestino}','$LocalidadDestino','{$CodigoSeguimiento}','NumeroVenta','{$DomicilioOrigen}','Localidad',
  '{$_SESSION[Usuario]}','0','{$FormaDePago}','{$Entrega}','0','{$Observaciones}','Transportista','Recorrido','Provincia Destino','Provincia Origen',
  '{$Km}')"); 
  ?>
  <script>
  window.location('Pendientes.php');
  </script>
  <?
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html;" charset="UTF-8" />
    
   <title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddyN.css" rel="stylesheet" type="text/css" />
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script src="js/miscript.js"></script>
<!-- <link href="css/popup.css" rel="stylesheet" type="text/css" />        
<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
    -->
<!--     //FUNCION PARA GENERAR CODIGOS ALEATORIOS DE 6 DIGIGITOS -->
    <script>
        function selectClienteOrigen() {
//          document.getElementById('clienteOrigen').style.display='inline-block';  
//          document.getElementById('clienteOrigen').style.display='block';
         document.getElementById('startcalle').style.display='block';
         document.getElementById('startnumero').style.display='block';
         document.getElementById('startciudad').style.display='block';
          
         var dirO=document.getElementById('clienteO').value;
         if(dirO=='Nuevo'){
         window.location='https://www.caddy.com.ar/SistemaTriangular/Plataforma/MisClientes.php?Agregar=Cliente';
         }
          
          if(dirO!=''){
          var dirO= dirO.split(','); 
//           document.getElementById('clienteOrigen').value=dirO[0];
          document.getElementById('startcalle').value=dirO[1];
          document.getElementById('startnumero').value=dirO[2];
          document.getElementById('startciudad').value=dirO[3];
          document.getElementById('contacto').value=dirO[4];
          document.getElementById('startobservaciones').value=dirO[5];
          document.getElementById('id').value=dirO[6];
//           document.getElementById('opcionNuevos').style.display='none';
          }
         
        }  
</script> 

    <script>
        function selectCliente() {
         document.getElementById('ClienteDestino').style.display='inline-block';  
         document.getElementById('clientedestino').style.display='block';
         document.getElementById('endcalle').style.display='block';
         document.getElementById('endnumero').style.display='block';
         document.getElementById('endciudad').style.display='block';
          
          var dir=document.getElementById('cliente').value;
         if(dir=='Nuevo'){
         window.location='https://www.caddy.com.ar/SistemaTriangular/Plataforma/MisClientes.php?Agregar=Cliente';
         }
          
          if(dir!=''){
          var dir= dir.split(','); 
          document.getElementById('clientedestino').value=dir[0];
          document.getElementById('endcalle').value=dir[1];
          document.getElementById('endnumero').value=dir[2];
          document.getElementById('endciudad').value=dir[3];
          document.getElementById('contacto').value=dir[4];
          document.getElementById('endobservaciones').value=dir[5];
          document.getElementById('id').value=dir[6];
          document.getElementById('opcionNuevos').style.display='none';
          }
         
        }  
</script> 

    <script>
        function foo() {
                 document.getElementById('opcionNuevos').style.display='block';
          
        if(checkbox.checked){

//           document.getElementById('ClienteDestino').style.display='inline-block';  
//         document.getElementById('opcionClientes').style.display='none';

        }
        else {
//         document.getElementById('ClienteDestino').style.display='none';  
         }
        }  
</script> 
    
 <style>
        #map {
        height: 100%;
        width: 50%;
          float:right;
      }
  
    </style>   
  </head>
  <body>
    
<?

include('Menu/menu.html');
if($Dato[Direccion]==''){

}
    
    ?>
   <div id="right-panel">
    <div id="lateral" style="width:50%;margin-left:0px;float:left;">
      <div id='inicio' style="display:block" >   
          
      <form class="login" method="POST" action="" style='margin-top:10px;height:100%' >
        <h2>Envio Simple Caddy:</h2>
          <div style='width:100%;display: inline-block;'>
            <div style='width:100%;display: inline-block;margin-bottom:10px'>
              <label style='color:#E24F30;margin-bottom:30px'>Origen..</label>
            </div>
            
<!--             DESDE ACA CLIENTE ORIGEN -->
            <div id="opcionClientesOrigen">
            </select>
            </div>
            
              <input type="hidden" name='clienteorigen' id="clienteorigen" value="<? echo $Dato[nombrecliente];?>" style='display:none' readonly>
              <div style='width: 45%;display: inline-block;margin-bottom:10px'> 
              <input style='margin-bottom:20px;' type="text" name='comienzo' id="startcalle"  value="<? echo $Dato[Calle];?>" placeholder='Calle...'>
              </div>
              <div style='width: 15%;display: inline-block;'>
                <input style='margin-bottom:20px;' type="text" name='comienzo' id="startnumero"  value="<? echo $Dato[Numero];?>" placeholder='Numero'>
              </div>
                <div style='width: 35%;display: inline-block;'>
                <select  id="startciudad" name="startciudad">
                <option value="Cordoba" >Cordoba</option>  
                <?  
                $sqlBuscoLocalidad=mysql_query("SELECT Localidad FROM Localidades WHERE Web='1'");
                while($Datos=mysql_fetch_array($sqlBuscoLocalidad)){
                echo "<option value='$Datos[Localidad]'>$Datos[Localidad]</option>";  
                }
                ?>
                </select>
                </div>
          </div>
            <div style='width: 75%;display:block;margin-bottom:10px'>
            <label style='color:#E24F30;margin-bottom:30px'>Destino.. </label>
            </div>
              <div style='width: 97%;display:block;'>
      
<!--       DESDE ACA SELECCIONAR CLIENTES PARA DESTINO   -->
<div id="opcionClientes">
  <select id="cliente" name="cliente" onchange="selectCliente()">
    <option value="" >Seleccione un Cliente de Destino...</option>  
    <option value="Nuevo" style="color:#E24F30" >Agregar Nuevo Cliente...</option>
      <?  
      $sqlBuscoLocalidad=mysql_query("SELECT * FROM Clientes WHERE Relacion='$_SESSION[NCliente]'");
      while($Datos=mysql_fetch_array($sqlBuscoLocalidad)){
      echo "<option value='$Datos[nombrecliente],$Datos[Calle],$Datos[Numero],$Datos[Ciudad],$Datos[Contacto],$Datos[Observaciones],$Datos[id]' >$Datos[nombrecliente]</option>";  
      }

        ?>
                </select>
                </div>
              </div>  
                <div id='ClienteDestino' style='width: 100%;display: inline-block ;margin-bottom:20px;'>
                <input style='margin-bottom:20px;' type="hidden" id="waypoints" value=""  placeholder='Punto Intermedio: Direccion, Ciudad' >
                <input type="hidden" name='id' id="id" value="" >
                <input type="hidden" name='clientedestino' id="clientedestino" value="" style='display:none'>
                  <div style='width: 45%;display: inline-block;margin-bottom:20px;'>
                  <input style='margin-bottom:20px;display:none' type="text" name='endcalle' id="endcalle" value=""  placeholder='Calle' onfocus='foo()' required readonly>
                  </div>
                     <div style='width: 10%;display: inline-block;'>
                     <input style='margin-bottom:20px;display:none;' type="text" name='endnumero' id="endnumero"  value="" placeholder='Numero' readonly>
                     </div>
                      <div style='width: 35%;display: inline-block;'>
                      <input style='margin-bottom:20px;display:none;' type="text" name='endciudad' id="endciudad"  value="" placeholder='Ciudad' readonly>
                      </div>
                      <div style='width: 100%;display:block;margin-bottom:15px;'>
                      <!--                         <label style='color:#E24F30;margin-bottom:20px'>Contacto..</label> -->
                      </div> 
                      <div style='width: 100%;display:block;margin-bottom:15px;'>
                      <input style='margin-bottom:20px;' type="text" name='contacto' id="contacto" value=""  placeholder='Nombre de quien recibe tu envio...'>
                      </div>
                      <div style='width: 100%;display:block;margin-bottom:15px;'>
                      <!--                             <label style='color:#E24F30;margin-bottom:20px'>Observaciones..</label> -->
                      </div>
                      <div style='width: 100%;display:block;margin-bottom:15px;'>
                      <input style='margin-bottom:20px;' type="text" name='endobservaciones' id="endobservaciones" value=""  placeholder='Observaciones...'>
                      </div>
                      <div style='width: 100%;display: block;'>
                      <input style='margin-bottom:20px;' type="number" name='cantidad' id="cantidad" value=""  placeholder='Cantidad de paquetes...'>
                      </div>
<!--         </div> -->
<?
$sqlBuscoServiciosxCliente0=mysql_query("SELECT * FROM ClientesyServicios WHERE NdeCliente='$_SESSION[NCliente]'");
// $Servicios0=mysql_fetch_array($sqlBuscoServiciosxCliente0);

if(mysql_num_rows($sqlBuscoServiciosxCliente0)<>''){ 
?>
          <div>
<!--             <input type="hidden" id="servicios" value="1">   
            <input type="hidden" id="restriccionlocalidad" value="<? echo $RestriccionLocalidad;?>">    -->
            <!--       <div style='width: 25%;display: inline-block;'> -->
      <input type="hidden" id="servicios" value="1">   

            <select  id="dimensiones" name="dimensiones">
<?  
  echo "<option value=''>Seleccione una opcion</option>";   
  while($Servicios=mysql_fetch_array($sqlBuscoServiciosxCliente0)){
  $PrecioPlano=$Servicios[PrecioPlano];
  $sqllocalidad=mysql_query("SELECT Localidad FROM Localidades WHERE id='$Servicios[Localidad]'");
  $datoloca=mysql_fetch_array($sqllocalidad);  
  $RestriccionLocalidad=$datoloca[Localidad]; 
  $sqlBuscoServiciosxCliente=mysql_query("SELECT Titulo FROM Productos WHERE id='$Servicios[Servicio]'");
  $Nombre=mysql_fetch_array($sqlBuscoServiciosxCliente);
  echo "<option value='$PrecioPlano,$Nombre[Titulo],$RestriccionLocalidad'>$Nombre[Titulo]  ( $ $Servicios[PrecioPlano])</option>";  
  }
?>
      </select>
      </div>
<?
}else{
  
?>  
      <input type="hidden" id="servicios" value="0" >   
<!--       <label style='color:#E24F30'>Dimensiones..</label> -->
      <select  id="dimensiones" name="dimensiones" >
      <option value="" >Seleccione una opcion</option>  
      <option value="1">Paquete 1 hasta 27,5 cm. x 36 cm. (500g)</option>  
      <option value="2">Paquete 2 hasta 35 cm. x 13 cm. x 10 cm. (1 kg.)</option>  
      <option value="3">Paquete 3 hasta 35 cm. x 35 cm. x 10 cm. (2 kg.)</option>  
      <option value="4">Paquete 4 hasta 35 cm. x 35 cm. x 18 cm. (5 kg.)</option>  
      <option value="5">Paquete 5 hasta 35 cm. x 35 cm. x 35 cm. (10 kg.)</option>  
      <option value="5">Paquete 6 hasta 40 cm. x 40 cm. x 40 cm. (15 kg.)</option>  
      <option value="6">Paquete 7 hasta 50 cm. x 40 cm. x 40 cm. (20 kg.)</option>  
      <option value="7">Paquete 8 hasta 55 cm. x 45 cm. x 40 cm. (25 kg.)</option>  
      </select>
<?
}
        ?>
<!-- <div> -->
  
    <input class="boton" name="Subimos" value="Aceptar" id="submit" style='
   -moz-box-shadow: inset 0px 0px 0px 0px #45D6D6;
	-webkit-box-shadow: inset 0px 0px 0px 0px #45D6D6;
	box-shadow: inset 0px 0px 0px 0px #45D6D6;
	background-color: #E24F30;
	border: 0px solid #E24F30;
	display: inline-block;
	cursor: pointer;
	color: #FFFFFF;
	font-family: Open Sans Condensed,sans-serif;
	font-size: 14px;
	padding: 8px 18px;
	text-decoration: none;
	text-transform: uppercase;
  width:100px;
  float:right;
  margin-top:20px;'>
                  
    </form>
      </div>
      </div>
      </div>

      <div id="mapa">    
<!--       <form  class='login' method='POST' action="" style='margin-top:0px;margin-right:5px;height:70%;' > -->
<!--       <h2>Resultado de su cotizacion:</h2> -->
      <div id='map' ></div>
<!--       </form> -->
      </div> 
      <div id="directions-panel" style='display:none' ></div>
    
    <div id="lateral" >
        <div id="resultado" style="display:none">
          <form  class='login' method='POST' action="" style='margin-top:10px;margin-left:50px;height:30%' >
          <h2>Confirmar Envio:</h2>
          <a>TOTAL DE ESTE ENVIO: </a>
          <input type="text" id="final" value="" style="font-size:20px;margin-bottom:30px;" placeholder=''  readonly>
          <label style='color:#E24F30'>Contanos como queres pagar este envio..</label>
          <select name='fp' id='fp' style='margin-top:10px;' >
          <option value="Origen" >Quiero que me cobren cuando retiren el envio...</option>
          <option value="Destino" >Quiero que me cobren cuando entreguen el envio...</option>
<!--           <option value="ahora" >Quiero pagar ahora con Mercado Pago...</option> -->
            </select>
            <input value="Seguir" id="submit2" style="
   -moz-box-shadow: inset 0px 0px 0px 0px #45D6D6;
	-webkit-box-shadow: inset 0px 0px 0px 0px #45D6D6;
	box-shadow: inset 0px 0px 0px 0px #45D6D6;
	background-color: #E24F30;
	border: 0px solid #E24F30;
	display: inline-block;
	cursor: pointer;
	color: #FFFFFF;
	font-family: Open Sans Condensed,sans-serif;
	font-size: 14px;
	padding: 8px 18px;
	text-decoration: none;
	text-transform: uppercase;
  width:100px;
  float:right;
  margin-top:20px;" >
          </form>
        </div>

  <div id='checkout' style='display:none;width:50%;' >    
      <form  class='login' method='POST' action="" style='margin-top:10px;width:;float:left;margin-left:50px;'>
      <h2>Checkout envio simple caddy:</h2>
      <label style='color:#E24F30'>Desde.. </label><input style='margin-bottom:20px;' type="text" name="start" id="startok"  value="" readonly>
      <label id='labelway' style='color:#E24F30;display:none;'>Punto Intermedio..</label><input style='margin-bottom:20px;' type="hidden" id="waypointsok" value=""  placeholder='Punto Intermedio: Direccion, Ciudad' >
      <label style='color:#E24F30'>Hasta..</label><input style='margin-bottom:20px;' type="text" name='end' id="endok" value="<? echo $b;?>"  placeholder='Final: Direccion, Ciudad'>
      <input type="hidden" name='idok' id="idok" value="">
      <label style='color:#E24F30'>Cliente..</label><input style='margin-bottom:20px;' type="text" name='clientedestinook' id="clientedestinook" value=""  placeholder='Nombre de quien recibe tu envio...' readonly>
      <label style='color:#E24F30'>Contacto..</label><input style='margin-bottom:20px;' type="text" name='contacto' id="contactook" value=""  placeholder='Nombre de quien recibe tu envio...'>
      <label style='color:#E24F30'>Cantidad..</label><input style='margin-bottom:20px;' type="number" name='cantidad' id="cantidadok" value=""  placeholder='Cantidad de paquetes...' Onchange='modifico()'>
      <label style='color:#E24F30'>Dimensiones..</label><input style='margin-bottom:20px;' type="text" name='dimensiones' id="dimensionesok" value=""  placeholder='Dimensiones del paquete a enviar...' readonly>
      <label style='color:#E24F30'>Precio..</label><input style='margin-bottom:20px;' type="text" name='precio' id="preciook" value=""  placeholder='Precio del paquete a enviar...' readonly>
      <label style='color:#E24F30'>Observaciones..</label><input style='margin-bottom:20px;' type="text" name='observacionesok' id="observacionesok" value=""  placeholder='Observaciones...'>
      <label style='color:#E24F30'>Kilometros..</label><input style='margin-bottom:20px;' type="text" name='kilometros' id="kilometros" value=""  placeholder='Observaciones...' readonly>
      <label style='color:#E24F30'>Forma de Pago..</label><input style='margin-bottom:20px;' type="text" name='fpok' id="fpok" value=""  readonly>  
      <label style='color:#E24F30'>Total Cotizacion..</label><input style='margin-bottom:20px;' type="text" name='final' id="finalok" value=""  readonly>
      <input type='submit' name='Proceso' value="Confirmar Envio" style="float:left;">
      </form>
  </div>
    </div>
    
<!--         <div id="pagar" style="display:none">
          <form  class='login' method='POST' action="" style='margin-top:10px;margin-left:50px;height:30%' >
          <h2>Confirmar Envio:</h2>
          <a>TOTAL DE ESTE ENVIO: </a>
          <input type="text" id="final" value="" style="font-size:20px;margin-bottom:30px;" placeholder=''  readonly>
          <script
            src="https://www.mercadopago.com.ar/integrations/v1/web-tokenize-checkout.js"
            data-button-label="Pagar con Mercado Pago"
            data-public-key="APP_USR-17b98749-da51-49c7-94e7-1fee7528771d"
             data-transaction-amount= "<? 
// echo $_POST['final'];?>"
            data-summary-product-label="Servicio de Envio">
            </script>
          </form>
        </div>
 -->
  <script>
  function modifico(){
  var cant=document.getElementById('cantidadok').value;
//   alert('hola mundo');
  var totl=document.getElementById('preciook').value;
  document.getElementById('finalok').value= cant*totl;  
  }
  </script>
      <script>
      function initMap() {
        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 7,
          center: {lat: -31.4448988, lng: -64.177743}
        });
        
        navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };

            infoWindow.setPosition(pos);
            infoWindow.setContent('Location found.');
            infoWindow.open(map);
            map.setCenter(pos);
          }, function() {
            handleLocationError(true, infoWindow, map.getCenter());
          });
        
        
        directionsDisplay.setMap(map);
        
        document.getElementById('submit2').addEventListener('click', function() {
        document.getElementById('startok').value=document.getElementById('startcalle').value+ '   '  + document.getElementById('startnumero').value;
        document.getElementById('endok').value=document.getElementById('endcalle').value+ '   '  + document.getElementById('endnumero').value;
        document.getElementById('contactook').value=document.getElementById('contacto').value;
        document.getElementById('clientedestinook').value=document.getElementById('clientedestino').value;
        document.getElementById('observacionesok').value=document.getElementById('endobservaciones').value;
        document.getElementById('idok').value=document.getElementById('id').value;

        var serv=document.getElementById('servicios').value;
        var dim=document.getElementById('dimensiones').value;
        var endciudad=document.getElementById('endciudad').value;
          
        if(serv==1){
        var dim= dim.split(','); 
        var restriccion=dim[2];

          if(restriccion==0){
           //SI NO TIENE RESTRICCION DE LOCALIDAD 
          document.getElementById('dimensionesok').value=dim[1];             
          }else{
            if(restriccion!=endciudad){       
            alert("Error en la seleccion de la tarifa para la ciudad de Destino, la tarifa seleccionada esta establecida para la Ciudad de "+restriccion+ ". Por favor seleccione otra tarifa o contactese con hola@caddy.com.ar para mas informacion. Gracias.");  
            document.location.href = "Calculador.php";
            }
          }
        }else{        

            if(dim==0){
            document.getElementById('dimensionesok').value='nada';  
            }else if(dim==1){
            document.getElementById('dimensionesok').value='Paquete 1 hasta 27,5 cm. x 36 cm. (500g)';  
            }else if(dim==2){
            document.getElementById('dimensionesok').value='Paquete 2 hasta 35 cm. x 13 cm. x 10 cm. (1 kg.)';
            }else if(dim==3){
            document.getElementById('dimensionesok').value='Paquete 3 hasta 35 cm. x 35 cm. x 10 cm. (2 kg.)';
            }else if(dim==4){
            document.getElementById('dimensionesok').value='Paquete 4 hasta 35 cm. x 35 cm. x 18 cm. (5 kg.)';
            }else if(dim==5){
            document.getElementById('dimensionesok').value='Paquete 5 hasta 35 cm. x 35 cm. x 35 cm. (10 kg.)';
            }else if(dim==6){
            document.getElementById('dimensionesok').value='Paquete 6 hasta 40 cm. x 40 cm. x 40 cm. (15 kg.)';
            }else if(dim==7){
            document.getElementById('dimensionesok').value='Paquete 7 hasta 50 cm. x 40 cm. x 40 cm. (20 kg.)';
            }else if(dim==8){
            document.getElementById('dimensionesok').value='Paquete 8 hasta 55 cm. x 45 cm. x 40 cm. (25 kg.)';
            }
        }  
        
          if(document.getElementById('cantidad').value == ""){
        document.getElementById('cantidadok').value=1;
        }else{
        document.getElementById('cantidadok').value=document.getElementById('cantidad').value;
        var cantidad = document.getElementById('cantidad').value;
        }
        document.getElementById('preciook').value=document.getElementById('final').value;  
        document.getElementById('finalok').value=document.getElementById('final').value;
        
        var fp = document.getElementById('fp').value;
        document.getElementById('fpok').value=fp;  

        document.getElementById('resultado').style.display='none';
        document.getElementById('checkout').style.display='block';
        });
        
        document.getElementById('submit').addEventListener('click', function() {
        if(document.getElementById('startcalle').value == ""){
        alert('Seleccione donde buscaremos el paquete...');
        initMap.finish();  
        }
        if(document.getElementById('startnumero').value == ""){
        alert('Seleccione la numeracion de la calle desde donde buscaremos el paquete...');
        initMap.finish();  
        }
        if(document.getElementById('endcalle').value == ""){
        alert('Seleccione el destino del paquete...');
        initMap.finish();  
        }
        if(document.getElementById('dimensiones').value == ""){
        alert('Seleccione las dimensiones del Envio...');
        initMap.finish();  
        }
        if(document.getElementById('endciudad').value == ""){
        alert('Seleccione la ciudad de destino...');
        initMap.finish();  
        }

          
        calculateAndDisplayRoute(directionsService, directionsDisplay);
        
        document.getElementById('resultado').style.display='block';
        document.getElementById('inicio').style.display='none';

          document.location.href = "Calculador.php#resultado";

        });
        
      }

      function calculateAndDisplayRoute(directionsService, directionsDisplay) {
        var waypts = [];
        var checkboxArray = document.getElementById('waypoints');
        var dato1 = document.getElementById('waypoints').value;
//         for (var i = 0; i < checkboxArray.length; i++) {
          if (dato1 != '') {
            waypts.push({
              location: document.getElementById('waypoints').value,
              stopover: true
            });
  
          }
        
        directionsService.route({
          origin: document.getElementById('startcalle').value + document.getElementById('startnumero').value + ',' +
          document.getElementById('startciudad').value,
          destination: document.getElementById('endcalle').value + document.getElementById('endnumero').value + ',' +
          document.getElementById('endciudad').value,
          waypoints: waypts,
          optimizeWaypoints: true,
          provideRouteAlternatives: true,
          travelMode: 'DRIVING'
        }, function(response, status) {
          
          if (status === 'OK') {
            directionsDisplay.setDirections(response);
            var route = response.routes[0];
            var summaryPanel = document.getElementById('directions-panel');
            summaryPanel.innerHTML = '';
            // For each route, display summary information.
            var totalDistance = 0;
            var totalDuration = 0;
            for (var i = 0; i < route.legs.length; i++) {
              var routeSegment = i + 1;
              totalDistance += parseFloat(route.legs[i].distance.text);
              totalDuration += route.legs[i].duration.value;
        
              summaryPanel.innerHTML += '<b style="background:#DFIJEJ;font-weight: bold;">Envio: ' + routeSegment +
                  '</b><br>Desde ';
              summaryPanel.innerHTML += route.legs[i].start_address + ' hasta ';
//               summaryPanel.innerHTML += route.legs[i].end_address + '<br>Total Segmento: ';
//               summaryPanel.innerHTML += route.legs[i].distance.text + '<br>Duracion: ';
//               summaryPanel.innerHTML += route.legs[i].duration.text + '<br><br>';

              var dato = route.legs[i].duration.value;
            
            }
            
            var summaryPanel = document.getElementById('directions-panel');
            
            var horas=Math.round((totalDuration /60))+1;
            var horas1=Math.floor(horas/60);
            var minutos=((horas-(horas1*60)));
            var dimensiones = document.getElementById('dimensiones').value;
            if(document.getElementById('cantidad').value == ""){
            var cantidad=1;
            }else{
            var cantidad = document.getElementById('cantidad').value;
            }
            var dimen=1;
            var serv=document.getElementById('servicios').value;  
            
            if(serv==1){
            var dimensiones= dimensiones.split(','); 
            var dimen = dimensiones[0];
            var titulodimensiones=dimensiones[1];  
            var txtdimensiones=dimensiones[1];  
            }else{        
            
                  if(dimensiones == 1){
                  var dimen = 150;
                  var titulodimensiones="Envio 1";  
                  var txtdimensiones="Envio 1 hasta 27,5 cm. x 36 cm. (500 g.)";  
                  }else if(dimensiones== 2){
                  var dimen= 180;  
                  var titulodimensiones="Envio 2";  
                  var txtdimensiones="Envio 2 hasta 35 cm. x 13 cm. x 10 cm. (1 kg.)";  
                  }else if(dimensiones== 3){
                  var dimen= 220;  
                  var titulodimensiones="Envio 3";  
                  var txtdimensiones="Envio 3 hasta 35 cm. x 35 cm. x 10 cm. (2 kg.)";  
                  }else if(dimensiones== 4){
                  var dimen= 250;  
                  var titulodimensiones="Envio 4";  
                  var txtdimensiones="Envio 4 hasta 35 cm. x 35 cm. x 18 cm. (5 kg.)";  
                  }else if(dimensiones== 5){
                  var dimen= 300;  
                  var titulodimensiones="Envio 5";  
                  var txtdimensiones="Envio 5 hasta 35 cm. x 35 cm. x 35 cm. (10 kg.)";  
                  }else if(dimensiones== 6){
                  var dimen= 350;  
                  var titulodimensiones="Envio 6";  
                  var txtdimensiones="Envio 6 hasta 40 cm. x 40 cm. x 40 cm. (15 kg.)";  
                  }else if(dimensiones== 7){
                  var dimen= 400;  
                  var titulodimensiones="Envio 7";  
                  var txtdimensiones="Envio 7 hasta 50 cm. x 40 cm. x 40 cm. (20 kg.)";  
                  }else if(dimensiones== 8){
                  var dimen= 500;  
                  var titulodimensiones="Envio 8";  
                  var txtdimensiones="Envio 8 hasta 55 cm. x 45 cm. x 40 cm. (25 kg.)";  
                  }
            }
            var res = totalDistance;
            document.getElementById('kilometros').value=res;
            
            <? $_SESSION[MaxKm]=50;?>//max de seguridad
            
            var servicio=document.getElementById('servicios').value;
            var maxKmCliente= <? echo $_SESSION['MaxKm'];?>

            if(res < maxKmCliente){
            var costo= dimen * cantidad;
            }else{
            var costokm= (res-10) * <? echo $_SESSION['PrecioKmCliente'];?>  
            var costo= costokm + dimen * cantidad; 
            }
            
            var iva= costo * 1.21;
            var costoconiva=costo +(costo * 1.21);
            summaryPanel.innerHTML += '<b> Distancia Total: ' + res + ' km.</b><br><b> Duracion Total: ';
            summaryPanel.innerHTML += horas1 +' Horas ' + minutos + ' minutos </b><br>';
            summaryPanel.innerHTML += '<br></br><b> Dimensiones:  ' + titulodimensiones + ' </b>';
            summaryPanel.innerHTML += '<br></br><b> ' + txtdimensiones + ' </b>';
            
            
            var costoconDecimal = costo.toFixed(2);
            var ivaconDecimal = iva.toFixed(2);
            var costoconivaconDecimal = costoconiva.toFixed(2);

            document.getElementById('final').value=costoconDecimal;
            
          } else {
            window.alert('Verifique las direcciones cargadas ' + status);
            document.getElementById('resultado').style.display='none';
            document.getElementById('inicio').style.display='block';

          }
        });
      }
    </script>
    <script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap">
    </script>
  <?
  a:
 ob_end_flush();
    ?>
  </body>
</html>