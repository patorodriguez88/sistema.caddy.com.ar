<?
ob_start();
session_start();
if($_SESSION[NCliente]==''){
header("location:https://www.caddy.com.ar/iniciosesion.php");  
}
include("../ConexionBD.php");
$sql=mysql_query("SELECT * FROM Clientes WHERE id='$_SESSION[NCliente]'");
$Dato=mysql_fetch_array($sql);

if($_POST[Proceso]=='Confirmar Envio'){
  //Controlo si el cliente tiene establecidos Servicios
  $sqlBuscoClienteDestino=mysql_query("SELECT * FROM Clientes WHERE id='$_POST[idok]'");
  $DatosClienteDestino=mysql_fetch_array($sqlBuscoClienteDestino);
  $LocalidadDestino=$DatosClienteDestino[Ciudad];
  $CodigoSeguimiento="";
  $Retiro=$Dato[Retiro];
  $Debe=$_POST['final'];
  $ClienteDestino=$_POST[clientedestinook];
  $Contacto=$_POST[contacto];
  $Observaciones=$_POST[observacionesok];
  $DomicilioDestino=$_POST['end'];
  $Cantidad=$_POST[cantidad];
  $DomicilioOrigen=$_POST[start];
  $idClienteDestino=$_POST[idok];
  $Km=$_POST[kilometros];
  $Entrega='Domicilio';
  $FormaDePago=$_POST[fpok];
  $Cobranza=$_POST[importeacobrarfinal];
  $sqlMax=mysql_query("SELECT MAX(NumeroVenta)as NumeroVenta FROM PreVenta WHERE Eliminado=0 AND id='$_SESSION[NCliente]'");
  if ($row = mysql_fetch_array($sqlMax)) {
   $DatoNV = trim($row[NumeroVenta])+1;
   }
$Codigo='49';  
$Fecha=date('Y-m-d');  
$sql=mysql_query("INSERT INTO `PreVenta`(`Fecha`, `RazonSocial`, `NCliente`, `TipoDeComprobante`, `NumeroComprobante`, `Cantidad`, `Precio`, 
`Total`, `ClienteDestino`, `idClienteDestino`,  `DomicilioDestino`, `LocalidadDestino`, `CodigoSeguimiento`, `NumeroVenta`,
`DomicilioOrigen`, `LocalidadOrigen`, `Usuario`, `Cargado`, `FormaDePago`, `EntregaEn`, `Eliminado`, `Observaciones`, `Transportista`, `Recorrido`,
`ProvinciaDestino`, `ProvinciaOrigen`, `Kilometros`,`Cobranza`,`Retirado`) VALUES 
 ('{$Fecha}','{$Dato[nombrecliente]}','{$_SESSION[NCliente]}','SOLICITUD WEB','{$Codigo}','{$Cantidad}','{$Debe}','{$Debe}',
  '{$ClienteDestino}','{$idClienteDestino}','{$DomicilioDestino}','$LocalidadDestino','{$CodigoSeguimiento}','NumeroVenta','{$DomicilioOrigen}','Localidad',
  '{$_SESSION[Usuario]}','0','{$FormaDePago}','{$Entrega}','0','{$Observaciones}','Transportista','Recorrido','Provincia Destino','Provincia Origen',
  '{$Km}','{$Cobranza}','{$Retiro}')"); 
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
    <script type="text/javascript" src="../../js/jquery.js"></script>
    <script>
        function cobra() {
          var dato=document.getElementById('cobranza').checked;
          if(dato==true){
            alert('ATENCION: La cobranza en destino tiene costos en % sobre el valor cobrado.');
          document.getElementById('cobrarendestino').style.display='inline-block';    
          document.getElementById('cobrarendestino').require;
          }else{
          document.getElementById('cobrarendestino').style.display='none';
          document.getElementById('importeacobrar').value="";
          }
        }
    </script>                      

    <script>
        function selectClienteOrigen() {
         document.getElementById('startcalle').style.display='block';
         document.getElementById('startnumero').style.display='block';
         document.getElementById('startciudad').style.display='block';
          
         var dirO=document.getElementById('clienteO').value;
         if(dirO=='Nuevo'){
         window.location='https://www.caddy.com.ar/SistemaTriangular/Plataforma/MisClientes.php?Agregar=Cliente';
         }
          
          if(dirO!=''){
          var dirO= dirO.split(','); 
          document.getElementById('startcalle').value=dirO[1];
          document.getElementById('startnumero').value=dirO[2];
          document.getElementById('startciudad').value=dirO[3];
          document.getElementById('contacto').value=dirO[4];
          document.getElementById('startobservaciones').value=dirO[5];
          document.getElementById('id').value=dirO[6];
          }
         
        }  
</script> 

    <script>
        function selectCliente() {
         document.getElementById('ClienteDestino').style.display='inline-block';  
         document.getElementById('clientedestino').style.display='block';
         document.getElementById('domicilio').style.display='block';
         document.getElementById('endciudad').style.display='block';
          var dir=document.getElementById('cliente').value;
          if(dir=='Nuevo'){
         window.location='https://www.caddy.com.ar/SistemaTriangular/Plataforma/MisClientes.php?Agregar=Cliente';
         }else{
          
            var dato={
            "id": dir,  
            };
            $.ajax({
            data: dato,
            url:'procesos/buscarcliente.php',
            type:'post',
    //         beforeSend: function(){
    //         $("#buscando").html("Buscando...");
    //         },
                success: function(response)
                {

                  var jsonData = JSON.parse(response);
                    if (jsonData.success == "1")

                    {

                      document.getElementById('id').value=jsonData.id;
                    document.getElementById('clientedestino').value=jsonData.RazonSocial;
                    document.getElementById('domicilio').value=jsonData.Direccion;  
                    document.getElementById('endciudad').value=jsonData.Ciudad;
                    document.getElementById('contacto').value=jsonData.Contacto;
                    document.getElementById('endobservaciones').value=jsonData.Observaciones;
                    document.getElementById('opcionNuevos').style.display='none';
                    }
                    else
                    {
                        alert('Error de Credenciales!');
                    }
               }
              }); 
           }
        }  
  </script> 
  <script>
  function foo() {
  document.getElementById('opcionNuevos').style.display='block';
  }  
  </script> 
    
 <style>
        #map {
        height: 100%;
        width: 100%;
        float:right;
        position:absolute; 
        top:0; 
        left:0;
        z-index:-1;   
   }
  
    </style>   
  </head>
  <body>
    
<?
include('Menu/menu.html');
    ?>
   <div id="right-panel">
    <div id="lateral" style="width:50%;margin-left:0px;float:left">
      <div id='inicio' style="display:block" >   
       <form class="login" method="POST" action="" style='margin-top:10px;height:90%' >
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
                  <!-- DESDE ACA SELECCIONAR CLIENTES PARA DESTINO   -->
                  <div id="opcionClientes">
                      <select id="cliente" name="cliente" onchange="selectCliente()">
                      <option value="" >Seleccione un Cliente de Destino...</option>  
                      <option value="Nuevo" style="color:#E24F30">Agregar Nuevo Cliente...</option>
                  <?  
                  $sqlBuscoLocalidad=mysql_query("SELECT * FROM Clientes WHERE Relacion='$_SESSION[NCliente]'");
                  while($Datos=mysql_fetch_array($sqlBuscoLocalidad)){
                  //       echo "<option value='$Datos[nombrecliente],$Datos[Calle],$Datos[Numero],$Datos[Ciudad],$Datos[Contacto],$Datos[Observaciones],$Datos[id],$Datos[Direccion]' >$Datos[nombrecliente]</option>";  
                  echo "<option value='$Datos[id]' >$Datos[nombrecliente]</option>";  
                  }
                  ?>
            </select>
            </div>
          </div>  
          <div id='ClienteDestino' style='width: 100%;display: inline-block ;margin-bottom:20px;'>
            <input style='margin-bottom:20px;' type="hidden" id="waypoints" value=""  placeholder='Punto Intermedio: Direccion, Ciudad' >
            <input type="hidden" name='id' id="id" value="" >
            <input type="hidden" name='clientedestino' id="clientedestino" value="" style='display:none'>
          <div style='width: 60%;display: inline-block;margin-bottom:20px;'>
            <input style='margin-bottom:20px;display:none;font-size:12px' type="text" name='domicilio' id="domicilio" value=""  placeholder='Calle' onfocus='foo()' required readonly>
          </div>
          <div style='width: 35%;display: inline-block;'>
            <input style='margin-bottom:20px;display:none;font-size:12px' type="text" name='endciudad' id="endciudad"  value="" placeholder='Ciudad' readonly>
          </div> 
            <div style='width: 100%;display:block;margin-bottom:15px;'>
          </div> 
            <div style='width: 100%;display:block;margin-bottom:15px;'>
              <input style='margin-bottom:20px;' type="text" name='contacto' id="contacto" value=""  placeholder='Nombre de quien recibe tu envio...'>
            </div>
            <div style='width: 100%;display:block;margin-bottom:15px;'>
          </div>
            <div style='width: 100%;display:block;margin-bottom:15px;'>
              <input style='margin-bottom:20px;' type="text" name='endobservaciones' id="endobservaciones" value=""  placeholder='Observaciones...'>
            </div>
            <div style='width: 100%;display: block;'>
              <input style='margin-bottom:20px;' type="number" name='cantidad' id="cantidad" value=""  placeholder='Cantidad de paquetes...'>
            </div>
            <div style='width: 50%;display: inline-block;'>
             <label>Cobrar en Destino ?  </label>
              <input  onClick="cobra()" type="checkbox" id="cobranza">
            </div>
            <div id="cobrarendestino" style='width: 40%;display:none'>
              <input name='importeacobrar' id="importeacobrar" type="number" placeholder='Importe a Cobrar...'>
            </div>
          </div>
<?
$sqlBuscoServiciosxCliente0=mysql_query("SELECT * FROM ClientesyServicios WHERE NdeCliente='$_SESSION[NCliente]'");
if(mysql_num_rows($sqlBuscoServiciosxCliente0)<>''){ 
?>
<div>
<? 
echo $RestriccionLocalidad;
?>
<input type="hidden" id="servicios" value="1">   
<select  id="dimensiones" name="dimensiones" style='font-size:14px'>
<?  
  echo "<option value=''>Seleccione una opcion</option>";   
  while($Servicios=mysql_fetch_array($sqlBuscoServiciosxCliente0)){
  $PrecioPlano=$Servicios[PrecioPlano];
  $MaxKm=$Servicios[MaxKm];  
  $sqllocalidad=mysql_query("SELECT Localidad FROM Localidades WHERE id='$Servicios[Localidad]'");
  $datoloca=mysql_fetch_array($sqllocalidad);  
  $RestriccionLocalidad=$datoloca[Localidad]; 
  $sqlBuscoServiciosxCliente=mysql_query("SELECT Titulo,Descripcion FROM Productos WHERE id='$Servicios[Servicio]'");
  $Nombre=mysql_fetch_array($sqlBuscoServiciosxCliente);
  echo "<option  value='$Servicios[PrecioPlano],$Nombre[Titulo],$RestriccionLocalidad,$Servicios[MaxKm],$Servicios[PrecioPlano]'>$Nombre[Titulo] $Nombre[Descripcion] ( Max $Servicios[MaxKm] km.) ( $ $Servicios[PrecioPlano])</option>";  
  }

?>
</select>
</div>
<?
}else{
  
      ?>  
      <input type="hidden" id="servicios" value="0" >   
      <select  id="dimensiones" name="dimensiones" >
      <option value="" >Seleccione una opcion</option>  
      <option value="1">Paquete 1 hasta 27,5 cm. x 36 cm. (500g)</option>  
      <option value="2">Paquete 2 hasta 35 cm. x 13 cm. x 10 cm. (1 kg.)</option>  
      <option value="3">Paquete 3 hasta 35 cm. x 35 cm. x 10 cm. (2 kg.)</option>  
      <option value="4">Paquete 4 hasta 35 cm. x 35 cm. x 18 cm. (5 kg.)</option>  
      <option value="5">Paquete 5 hasta 35 cm. x 35 cm. x 35 cm. (10 kg.)</option>  
      <option value="6">Paquete 6 hasta 40 cm. x 40 cm. x 40 cm. (15 kg.)</option>  
      <option value="7">Paquete 7 hasta 50 cm. x 40 cm. x 40 cm. (20 kg.)</option>  
      <option value="8">Paquete 8 hasta 55 cm. x 45 cm. x 40 cm. (25 kg.)</option>  
      </select>
      <?
      }
      ?>
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
<!--       <div id="mapa">     -->
      <div id='map' ></div>
<!--       </div>  -->
      <div id="directions-panel" style='display:none' ></div>
        <div id="lateral" >
          <div id="resultado" style="display:none">
            <form  class='login' method='POST' action="" style='margin-top:10px;margin-left:50px;height:30%' >
              <h2>Confirmar Envio:</h2>
              <a>TOTAL DE ESTE ENVIO: </a>
              <input type="text" id="final" value="" style="font-size:20px;margin-bottom:30px;" placeholder=''  readonly>
              <label style='color:#E24F30'><span  id="respuesta" ></span>Contanos como queres pagar este envio..</label>
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
      <form  class='login' method='POST' action="" style='margin-top:10px;float:left;margin-left:50px;'>
      <h2>Checkout envío simple Caddy:</h2>
      <label style='color:#E24F30'>Desde.. </label><input style='margin-bottom:20px;' type="text" name="start" id="startok"  value="" readonly>
      <label id='labelway' style='color:#E24F30;display:none;'>Punto Intermedio..</label><input style='margin-bottom:20px;' type="hidden" id="waypointsok" value=""  placeholder='Punto Intermedio: Direccion, Ciudad' >
        <div style='width: 60%;display: inline-block;margin-bottom:0px;'>
        <label style='color:#E24F30'>Hasta..</label><input style='margin-bottom:20px;' type="text" name='end' id="endok" value="<? echo $b;?>"  placeholder='Final: Direccion'>
        </div>
        <div style='width: 35%;display: inline-block;margin-bottom:10px;'>
        <label style='color:#E24F30'>Ciudad..</label><input style='margin-bottom:20px;' type="text" name='ciudadok' id="ciudadok" value=""  placeholder='Ciudad'>
        </div>
        
        <input type="hidden" name='idok' id="idok" value="">
      <input type="hidden" name='maxkmok' id="maxkmok" value="">
      <label style='color:#E24F30'>Cliente..</label><input style='margin-bottom:20px;' type="text" name='clientedestinook' id="clientedestinook" value=""  placeholder='Nombre de quien recibe tu envio...' readonly>
      <label style='color:#E24F30'>Contacto..</label><input style='margin-bottom:20px;' type="text" name='contacto' id="contactook" value=""  placeholder='Nombre de quien recibe tu envio...'>
      <label style='color:#E24F30'>Observaciones..</label><input style='margin-bottom:20px;' type="text" name='observacionesok' id="observacionesok" value=""  placeholder='Observaciones...'>
      <div style='width: 30%;display: inline-block;'>
      <label style='color:#E24F30'>Forma de Pago..</label><input style='margin-bottom:20px;' type="text" name='fpok' id="fpok" value=""  readonly>  
      </div>
      <div style='width: 30%;display: inline-block;'>
      <label style='color:#E24F30'>Dimensiones..</label><input style='margin-bottom:20px;' type="text" name='dimensiones' id="dimensionesok" value=""  placeholder='Dimensiones del paquete a enviar...' readonly>
      </div>
      <div style='width: 30%;display: inline-block;'>
      <label style='color:#E24F30'>Kilometros..</label><input style='margin-bottom:20px;' type="text" name='kilometros' id="kilometros" value=""  placeholder='Observaciones...' readonly>
      </div>  
      <div style='width: 30%;display: inline-block;'>
      <label style='color:#E24F30'>Cantidad..</label><input style='margin-bottom:20px;' type="number" name='cantidad' id="cantidadok" value=""  placeholder='Cantidad de paquetes...' Onchange='modifico()'>
      </div>
      <div style='width: 30%;display: inline-block;'>
      <label style='color:#E24F30'>Precio..</label><input style='margin-bottom:20px;' type="text" name='precio' id="preciook" value=""  placeholder='Precio del paquete a enviar...' readonly>
      </div>
      <div id="importeacobrarfinal_label" style='width: 30%;display: inline-block'>
      <label style='color:#E24F30'>Importe a Cobrar..</label><input style='margin-bottom:20px;' type="text" name='importeacobrarfinal' id="importeacobrarfinal" value=""  readonly>
      </div>
      <div style='width: 100%;display: block;'>
      <label style='color:#E24F30'>Total Cotizacion..</label><label id="ast" style='margin-left:150px;color:#E24F30;display:none'> * </label><input style='margin-bottom:20px;' type="text" name='final' id="finalok" value=""  readonly>
      </div>
       <label id="min-label" style="color:#E24F30;font-size:10px;display:none">* incluye 4% de costo por cobranza.</label> 
        <input type='submit' name='Proceso' value="Confirmar Envio" style="float:right;">
      </form>
  </div>
    </div>
<script>
  function clientesyservicios(km){
    var dato={
        "km": km,  
        };
        $.ajax({
        async: false,  
        data: dato,
        url:'clientesyservicios.php',
        type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
            success: function(response)
            {
                var jsonData = JSON.parse(response);
                if (jsonData.success == "1")
                {
                document.getElementById('final').value=jsonData.PrecioPlano;  
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
  function modifico(){
  var cant=document.getElementById('cantidadok').value;
  var totl=document.getElementById('preciook').value;
  var cob=Number(document.getElementById('importeacobrarfinal').value);  
  document.getElementById('finalok').value= cant*totl+cob;  
  }
  </script>
      <script>
      function initMap() {
        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 7,
//           center: {lat: -31.4448988, lng: -64.177743}
          center: {lat: -31.280655, lng: -66.491903}
          
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
        document.getElementById('startok').value=document.getElementById('startcalle').value+ ' '  + document.getElementById('startnumero').value;
        document.getElementById('endok').value=document.getElementById('domicilio').value;
        document.getElementById('ciudadok').value=document.getElementById('endciudad').value;  
        document.getElementById('contactook').value=document.getElementById('contacto').value;
        document.getElementById('clientedestinook').value=document.getElementById('clientedestino').value;
        document.getElementById('observacionesok').value=document.getElementById('endobservaciones').value;
        document.getElementById('idok').value=document.getElementById('id').value;
          
        if(document.getElementById('importeacobrar').value>1){
           document.getElementById('importeacobrarfinal').style.display='inline-block';
           document.getElementById('importeacobrarfinal').value=document.getElementById('importeacobrar').value;
           document.getElementById('min-label').style.display='block';
           document.getElementById('ast').style.display='block';
        }else{
          document.getElementById('importeacobrarfinal').style.display='none';
          document.getElementById('importeacobrarfinal_label').style.display='none';
          
        }

        var serv=document.getElementById('servicios').value;
        var dim=document.getElementById('dimensiones').value;
        var endciudad=document.getElementById('endciudad').value;
          
        if(serv==1){
        var dim= dim.split(','); 
        var restriccion=dim[2];

          if(restriccion==0){
           //SI NO TIENE RESTRICCION DE LOCALIDAD 
          document.getElementById('dimensionesok').value=dim[1]; 
          document.getElementById('maxkmok').value=dim[3];
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

          if(document.getElementById('importeacobrar').value>1){
          document.getElementById('finalok').value=Number(document.getElementById('final').value)+Number(((document.getElementById('importeacobrar').value*4)/100));
          }else{
          document.getElementById('finalok').value=document.getElementById('final').value;
          }
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
        if(document.getElementById('domicilio').value == ""){
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
          destination: document.getElementById('domicilio').value,
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
            var res = totalDistance;

            if(res<25){
            var categoriatarifa=1;  
            }else if (res>25 && res<50){
            var categoriatarifa=2;  
            }else{
            var categoriatarifa=3;    
            }
            
            if(serv==1){ // si el cliente tiene servicios cargados
            var valoresnuevos= '';
            clientesyservicios(res);
            var dimen=document.getElementById('final').value;
            var titulodimensiones='';  
            var txtdimensiones='';  
            }else{        
            
                if(dimensiones == 1){
                  if(categoriatarifa==1){
                  var dimen = 150;
                  var titulodimensiones="Envio 1";  
                  var txtdimensiones="Envio 1 hasta 27,5 cm. x 36 cm. (2 kg.)";  
                  }else if(categoriatarifa==2){
                  var dimen = 200;
                  var titulodimensiones="Envio 1";  
                  var txtdimensiones="Envio 1 hasta 27,5 cm. x 36 cm. (2 kg.)";  
                  }else if(categoriatarifa==3){
                  var dimen = 250;
                  var titulodimensiones="Envio 1";  
                  var txtdimensiones="Envio 1 hasta 27,5 cm. x 36 cm. (2 kg.)";  
                  }

                }else if(dimensiones== 2){
                  if(categoriatarifa==1){
                  var dimen= 180;  
                  var titulodimensiones="Envio 2";  
                  var txtdimensiones="Envio 2 hasta 35 cm. x 13 cm. x 12 cm. (4 kg.)";  
                  }else if(categoriatarifa==2){
                  var dimen= 240;  
                  var titulodimensiones="Envio 2";  
                  var txtdimensiones="Envio 2 hasta 35 cm. x 13 cm. x 12 cm. (4 kg.)";  
                  }else if(categoriatarifa==3){
                  var dimen= 300;  
                  var titulodimensiones="Envio 2";  
                  var txtdimensiones="Envio 2 hasta 35 cm. x 13 cm. x 12 cm. (4 kg.)";  
                  }
                 }else if(dimensiones== 3){
                  if(categoriatarifa==1){
                  var dimen= 220;  
                  var titulodimensiones="Envio 3";  
                  var txtdimensiones="Envio 3 hasta 35 cm. x 35 cm. x 15 cm. (10 kg.)";  
                  }else if(categoriatarifa==2){
                  var dimen= 295;  
                  var titulodimensiones="Envio 3";  
                  var txtdimensiones="Envio 3 hasta 35 cm. x 35 cm. x 15 cm. (10 kg.)";  
                  }else if(categoriatarifa==3){
                  var dimen= 365;  
                  var titulodimensiones="Envio 3";  
                  var txtdimensiones="Envio 3 hasta 35 cm. x 35 cm. x 15 cm. (10 kg.)";  
                  }                    
                 }else if(dimensiones== 4){
                  if(categoriatarifa==1){
                  var dimen= 250;  
                  var titulodimensiones="Envio 4";  
                  var txtdimensiones="Envio 4 hasta 35 cm. x 35 cm. x 18 cm. (15 kg.)";  
                  }else if(categoriatarifa==2){
                  var dimen= 335;  
                  var titulodimensiones="Envio 4";  
                  var txtdimensiones="Envio 4 hasta 35 cm. x 35 cm. x 18 cm. (15 kg.)";  
                  }else if(categoriatarifa== 3){
                  var dimen= 420;  
                  var titulodimensiones="Envio 4";  
                  var txtdimensiones="Envio 4 hasta 35 cm. x 35 cm. x 18 cm. (15 kg.)";  
                  }
                }else if(dimensiones== 5){
                  if(categoriatarifa==1){
                  var dimen= 300;  
                  var titulodimensiones="Envio 5";  
                  var txtdimensiones="Envio 5 hasta 35 cm. x 35 cm. x 35 cm. (20 kg.)";  
                  }else if(categoriatarifa==2){
                  var dimen= 400;  
                  var titulodimensiones="Envio 5";  
                  var txtdimensiones="Envio 5 hasta 35 cm. x 35 cm. x 35 cm. (20 kg.)";  
                  }else if(categoriatarifa== 3){
                  var dimen= 500;  
                  var titulodimensiones="Envio 5";  
                  var txtdimensiones="Envio 5 hasta 35 cm. x 35 cm. x 35 cm. (20 kg.)";  
                  }                  
                }else if(dimensiones== 6){
                  if(categoriatarifa==1){
                  var dimen= 350;  
                  var titulodimensiones="Envio 6";  
                  var txtdimensiones="Envio 6 hasta 40 cm. x 40 cm. x 40 cm. (25 kg.)"; 
                  }else if(categoriatarifa==2){
                  var dimen= 465;  
                  var titulodimensiones="Envio 6";  
                  var txtdimensiones="Envio 6 hasta 40 cm. x 40 cm. x 40 cm. (25 kg.)"; 
                  }else if(categoriatarifa== 3){
                  var dimen= 585;  
                  var titulodimensiones="Envio 6";  
                  var txtdimensiones="Envio 6 hasta 40 cm. x 40 cm. x 40 cm. (25 kg.)"; 
                  }
                 }else if(dimensiones== 7){
                  if(categoriatarifa==1){
                  var dimen= 400;  
                  var titulodimensiones="Envio 7";  
                  var txtdimensiones="Envio 7 hasta 50 cm. x 40 cm. x 40 cm. (25 kg.)";  
                  }else if(categoriatarifa==2){
                  var dimen= 535;  
                  var titulodimensiones="Envio 7";  
                  var txtdimensiones="Envio 7 hasta 50 cm. x 40 cm. x 40 cm. (25 kg.)";  
                  }else if(categoriatarifa== 3){
                  var dimen= 665;  
                  var titulodimensiones="Envio 7";  
                  var txtdimensiones="Envio 7 hasta 50 cm. x 40 cm. x 40 cm. (25 kg.)";  
                  }
                }else if(dimensiones== 8){
                  if(categoriatarifa==1){
                  var dimen= 500;  
                  var titulodimensiones="Envio 8";  
                  var txtdimensiones="Envio 8 hasta 55 cm. x 45 cm. x 40 cm. (25 kg.)";  
                  }else if(categoriatarifa==2){
                  var dimen= 665;  
                  var titulodimensiones="Envio 8";  
                  var txtdimensiones="Envio 8 hasta 55 cm. x 45 cm. x 40 cm. (25 kg.)";  
                  }else if(categoriatarifa== 3){
                  var dimen= 850;  
                  var titulodimensiones="Envio 8";  
                  var txtdimensiones="Envio 8 hasta 55 cm. x 45 cm. x 40 cm. (25 kg.)";  
                  }                                          
                }
          }
            document.getElementById('kilometros').value=res;
            <? 
            $_SESSION[MaxKm]=50;?>//max de seguridad
            var servicio=document.getElementById('servicios').value;
            var costo= Number(dimen) * Number(cantidad);            
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