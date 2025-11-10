<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
if($_GET[start]=='clean'){
  if($_GET[Emisor]==''){
  unset($_SESSION[NCliente]);
  }
unset($_SESSION[NClienteDestino_t]);
 }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/stylenew.css" rel="stylesheet" type="text/css" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" />        
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
<script src="../js/jquery-1.7.1.min.js"></script>
</head>
</script>  
<style>

#botonrojo {
  background: none repeat scroll 0 0 #E24F30;
  border: 1px solid #C6C6C6;
  float: right;
  font-weight: bold;
  padding: 8px 26px;
  color:#FFFFFF;
  font-size:12px;
  width:140px;
  cursor:pointer;
  margin-bottom:10px;
}

</style>
<script>
function agregarcliente(){
  var origen=document.getElementById('origen').value;
  var nombre=document.getElementById('nombrecliente').value;
  var direccion=document.getElementById('start').value;
  var telefono= document.getElementById('telefono').value;
  var relacion= document.getElementById('relacion_t').value;
  var cp=document.getElementById('Codigo_Postal_t').value;
  var calle=document.getElementById('calle').value;
  var numero=document.getElementById('numero').value;
  var ciudad=document.getElementById('ciudad').value;
  var observaciones=document.getElementById('observaciones_t').value;

  var dato={
        "nombrecliente":nombre,
        "Direccion":direccion,
        "Telefono":telefono,
        "Relacion":relacion,
        "CodigoPostal":cp,
        "Calle":calle,
        "Numero":numero,
        "Ciudad":ciudad,
        "Observaciones":observaciones,    
        };
        
        $.ajax({
        data: dato,
        url:'../Clientes/crearcliente.php',
        type:'post',
        beforeSend: function(){
//         $("#buscando").html("Buscando...");
//   alert('enviando...');
        },
        success: function (respuesta) {
                var jsonData = JSON.parse(respuesta);
                if (jsonData.success == "1")
                {
                var NombreCliente=jsonData.NombreCliente;  
                var id=jsonData.id;  
                window.location ='https://www.caddy.com.ar/SistemaTriangular/Ventas/CompruebaCliente.php?' + origen + '=' + id + ' - ' + NombreCliente;
                }
                else
                {
                    alert('Error en el ingreso!');
                }
        }
        });
  }
</script>
<script>
function initMap() {
        
        //START
        var inputstart = document.getElementById('start');
        var autocomplete = new google.maps.places.Autocomplete(inputstart);
        autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();
        if (place.address_components) {
        var components= place.address_components;
        var ciudad='';
        var provincia='';  
          for (var i = 0, component; component = components[i]; i++) {
            console.log(component);
            if (component.types[0] == 'administrative_area_level_1') {
               provincia=component['long_name'];
             }else if (component.types[0] == 'locality') {
              ciudad = component['long_name'];
              document.getElementById('ciudad').value = ciudad;
             }else if(component.types[0] == 'postal_code'){
             document.getElementById('Codigo_Postal_t').value= component['long_name'];   
             }else if(component.types[0] == 'street_number'){
             document.getElementById('numero').value= component['long_name'];   
             }else if(component.types[0] == 'route'){
             document.getElementById('calle').value= component['long_name'];   
            
             }
            }
        }
        }); 
        }; 
</script>
<?php
include("../Menu/MenuGestion.php"); 
echo "<div id='cuerpo'>"; 
echo "<center>";
if ($_GET['BuscaCliente']=='Si'){
echo "<form class='Caddy' action='' method='POST'  style='width:450px;float:left;';>";
	$Grupo="SELECT nombrecliente,Cuit FROM Clientes ORDER BY nombrecliente ASC";
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
$_SESSION['formadepago_od']=$_POST[formadepago];
  
header("location:https://www.caddy.com.ar/SistemaTriangular/Ventas/Reposiciones.php");
}

if ($_POST['ClienteEmisor']=='Cambiar'){
 unset($_SESSION['NCliente']);	
}

if ($_POST['ClienteReceptor']=='Cambiar'){
unset($_SESSION['NClienteDestino_t']);	
}

//DATOS CLIENTE EMISOR
if($_POST[Busca]=='Aceptar'){
$Cliente_t=explode(' - ',$_POST['Cliente_t']);
$CuitClienteA=$Cliente_t[0];	
$BuscarCliente="SELECT * FROM Clientes WHERE id='$CuitClienteA';";
$BuscarClienteA=mysql_query($BuscarCliente);
$row = mysql_fetch_array($BuscarClienteA);
			$_SESSION['NCliente']=$row[id];	//CUIT
			$_SESSION['NombreClienteA']=$row[nombrecliente];//NOMBRE CLIENTE
			$_SESSION['DomicilioEmisor_t']=addslashes($row['Direccion']);//Domicilio
			$_SESSION['SituacionFiscalEmisor_t']=$row[21];//SituacionFiscal
			$_SESSION['TelefonoEmisor_t']=$row[12];//Telefono
  		$_SESSION['CelularEmisor_t']=$row[15];//Telefono
  		$_SESSION['LocalidadOrigen_t']=$row[8];//Telefono
			$_SESSION['IngBrutosOrigen_t']=$row[0];//id
			$_SESSION['ProvinciaOrigen_t']=$row[9];//ProvinciaOrigen
      $_SESSION['RetiroOrigen_t']=$row[42];//Retiro y Entrega 0 Solo Entrega 2 
}
$ClienteReceptor0=explode(' - ',$_POST['ClienteReceptor_t']);	
$ClienteReceptor=$ClienteReceptor0[0];

//DATOS CLIENTE RECEPTOR
if($_POST[BuscaReceptor]=='Aceptar'){
$BuscarCliente="SELECT * FROM Clientes WHERE id='$ClienteReceptor';";
$BuscarClienteA=mysql_query($BuscarCliente);
$rowB = mysql_fetch_array($BuscarClienteA);
      $_SESSION['idClienteDestino_t']=$rowB[id];	//id
			$_SESSION['NClienteDestino_t']=$rowB[Cuit];	//CUIT
			$_SESSION['NombreClienteDestino_t']=$rowB[nombrecliente];//NOMBRE CLIENTE
			$_SESSION['DomicilioDestino_t']=$rowB[Direccion];//Domicilio
			$_SESSION['SituacionFiscalDestino_t']=$rowB[21];//SituacionFiscal
			$_SESSION['TelefonoDestino_t']=$rowB[Telefono];//Telefono
      $_SESSION['CelularDestino_t']=$row[Celular];//Telefono
			$_SESSION['LocalidadDestino_t']=$rowB[8];//Telefono
			$_SESSION['ProvinciaDestino_t']=$rowB[9];//Provincia Destino
      $_SESSION['RetiroDestino_t']=$row[42];//Retiro y Entrega 0 Solo Entrega 2
}
///--------------------HASTA ACA DATOS CLIENTES-----------
//------------DESDE ACA CLIENTE EMISOR---------------------------------------------------------------------
if ($_SESSION['NCliente']==''){
echo "<form class='Caddy' action='CompruebaCliente.php' method='POST' style='width:40%;float:left'>";
	$Grupo="SELECT id,nombrecliente FROM Clientes WHERE nombrecliente <> 'Consumidor Final' ORDER BY id";
	$estructura= mysql_query($Grupo);
	echo "<h2>Cliente Emisor:</h2>";
    echo "<div style='width:80%;display:inline-block;'>";
    echo "<div><input name='Cliente_t' list='Cliente_t' value='$_GET[Emisor]' type='text' placeholder='Comience a escribir un nombre..'/></div>";
    echo "</div>";
    echo "<div style='width:20%;display: inline-block;vertical-align:top;margin-top:15px'>";
    echo "<div><a class='img' href='?origen=Emisor#nuevocliente'><img src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;vertical-align:top;'></a></div>";
    echo "</div>";
  echo "<datalist id='Cliente_t'>";
  echo "<div><select name='' list='Cliente_t'>";
    $Estructura=mysql_query("SELECT id,nombrecliente FROM Clientes");		
    while ($row = mysql_fetch_array($Estructura)){
    echo "<option value='$row[id] - $row[nombrecliente]'></option>";
    }
    echo "</select></div>";
  echo "</datalist>";
  echo "<div><input name='Busca' class='bottom' type='submit' value='Aceptar' align='right'></div>";
  echo "</form>";
}else{
echo "<form class='Caddy' action='CompruebaCliente.php' method='POST' style='width:40%;float:left'>";
echo "<h2>Cliente Emisor:</h2>";
echo "<div><label>Nombre Cliente:</label><input type='text' value='$_SESSION[NombreClienteA]' readonly></div>";
// echo "<div><label>Situacion Fiscal:</label><input type='text' value'$_SESSION[SituacionFiscalEmisor_t]'></div>";
  $dir=stripslashes($_SESSION['DomicilioEmisor_t']);
echo "<div><label>Cuit:</label><input type='text' value='$_SESSION[NCliente]' readonly></div>";
echo "<div><label>Domicilio:</label><input type='text' value='$dir'readonly></div>";
echo "<div><label>Localidad:</label><input type='text' value='$_SESSION[LocalidadOrigen_t]' readonly></div>";
echo "<div><label>Telefono:</label><input type='text' value='$_SESSION[TelefonoEmisor_t]' readonly></div>";
echo "<div><input name='ClienteEmisor' class='bottom' type='submit' value='Cambiar' align='right' style='width:150px'></div>";
echo "</form>";
}
//------------DESDE ACA CLIENTE RECEPTOR-------------------------------------------------------------------

if ($_SESSION['NClienteDestino_t']==''){
echo "<form class='Caddy'  action='CompruebaCliente.php' method='POST' style='width:40%;margin-left:15px;float:left'>";
	$Grupo="SELECT id,nombrecliente FROM Clientes WHERE nombrecliente <> 'Consumidor Final' ORDER BY id";
	$estructura= mysql_query($Grupo);
	echo "<h2>Cliente Receptor:</h2>";
  echo "<div style='width:80%;display:inline-block;'>";
  echo "<div><input name='ClienteReceptor_t' list='ClienteReceptor_t'  value='$_GET[Receptor]' type='text' placeholder='Comience a escribir un nombre..'/></div>";
  echo "</div>";
  echo "<div style='width:20%;display: inline-block;vertical-align:top;margin-top:15px'>";
  echo "<div><a class='img' href='?origen=Receptor#nuevocliente'><img src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;vertical-align:top;'></a></div>";
  echo "</div>";
  echo "<datalist id='ClienteReceptor_t'>";
  echo "<div><select name='' list='ClienteReceptor_t'>";
    $Estructura=mysql_query("SELECT id,nombrecliente FROM Clientes");		
    while ($row = mysql_fetch_array($Estructura)){
    echo "<option value='$row[id] - $row[nombrecliente]'></option>";
    }
    echo "</select></div>";
  echo "</datalist>";
	echo "<div><input name='BuscaReceptor' class='bottom' type='submit' value='Aceptar' align='right'></div>";
	echo "</form>";
}else{
echo "<form class='Caddy' action='CompruebaCliente.php' method='POST' style='width:40%;margin-left:15px;float:left'>";
echo "<h2>Cliente Receptor:</h2>";
echo "<div><label>Nombre Cliente:</label><input type='text' value='$_SESSION[NombreClienteDestino_t]' readonly></div>";
// echo "<div><label>Situacion Fiscal:</label><input type='text' value='$_SESSION[SituacionFiscalDestino_t]'></div>";
echo "<div><label>Cuit:</label><input type='text' value='$_SESSION[NClienteDestino_t]' readonly></div>";
echo "<div><label>Domicilio:</label><input type='text' value='$_SESSION[DomicilioDestino_t]' readonly></div>";
echo "<div><label>Localidad:</label><input type='text' value='$_SESSION[LocalidadDestino_t]' readonly></div>";
echo "<div><label>Telefono:</label><input type='text' value='$_SESSION[TelefonoDestino_t]' readonly></div>";
echo "<div><input name='ClienteReceptor' class='bottom' type='submit' value='Cambiar' align='right' style='width:150px'></div>";
echo "</form>";
}
if (($_SESSION['NCliente']!='')and($_SESSION['NClienteDestino_t']!='')){
echo "<form class='Caddy' action='' method='POST' style='width:86%;float:left'>";
echo "<div><label>Forma De Pago:</label><select name='formadepago'>";
echo "<option value='Origen'>Origen</option>";
echo "<option value='Destino'>Destino</option>";
echo "</select></div>";
echo "<div><input name='Pasar' class='bottom' type='submit' value='Enviar' align='right'></div>";
echo "</form>";
}
echo "</div>";  //contenedor
echo "</div>";  //contenedor

echo "<div id='nuevocliente' class='overlay'>";
echo "<div id='popupBody' style='margin-top:15%'>";
echo "<a id='cerrar' href='#'>&times;</a>";
echo "<div class='popupContent' style='width:600px;margin-top:10px'>";
echo "<form class='Caddy' style='height:30%'>";
// echo "<h2>AGREGAR CLIENTE</h2>";
echo "<div><input type='text' value='' id='nombrecliente' placeholder='Nombre Cliente' onblur='comrpueba()'></div>";
echo "<div><input type='text' value='' class='form-control' name='direccion' id='start' placeholder='Direccion: Calle Numero'></div>";
echo "<div><input type='text' value='' id='observaciones_t' placeholder='Alguna Observacion ?'></div>";
echo "<div><input type='text' id='telefono' name='telefono' value='' placeholder='Telefono' required></div>";
echo "<input type='hidden' name='calle' value=''  id='calle'>";
echo "<input type='hidden' name='numero' value='' id='numero'>";
echo "<input type='hidden' name='ciudad' value='' id='ciudad'>";
echo "<input type='hidden' name='cp' value='' id='Codigo_Postal_t'>";
echo "<input type='hidden' name='origen'  value='$_GET[origen]' id='origen'>";
echo "<div><input type='text' name='relacion' value='' placeholder='Relacion' list='relacion' id='relacion_t'>";
echo "<datalist id='relacion'>";
$sql=mysql_query("SELECT id,nombrecliente FROM Clientes");
echo "<select name=''>";
while($row=mysql_fetch_array($sql)){
echo "<option value='$row[id]'>$row[nombrecliente]></option>";
}
echo "</select>";
echo"</datalists>";
echo "</div>";
echo "<div><input class='botonrojo' value='Aceptar' Onclick='agregarcliente()' style='cursor:pointer;background: none repeat scroll 0 0 #E24F30;
  border: 1px solid #C6C6C6;
  float: right;
  font-weight: bold;
  padding: 8px 26px;
  color:#FFFFFF;
  font-size:12px;
  width:140px;
  cursor:pointer;
  margin-bottom:10px;'></div>";

// echo "<div><input name='ClienteReceptor' class='bottom' type='submit' value='Agregar' align='right' style='width:150px;'></div>";
echo "<div style='height:10px'></div>";
echo "</form>";
echo "</div>";
echo "</div>";
echo "</div>";      

a:
?>
<script async defer
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&libraries=places&callback=initMap">
</script>
<?
ob_end_flush();	
?>
