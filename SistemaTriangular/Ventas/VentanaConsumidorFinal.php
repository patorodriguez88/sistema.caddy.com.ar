<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
if ($_SESSION['Nivel']==''){
header("location:https://www.caddy.com.ar");
}

if ($_POST['ClienteEmisor']=='Cambiar'){
unset($_SESSION['idClienteOrigen']);	
header('location:CompruebaCliente.php');
}
if ($_POST['ClienteReceptor']=='Cambiar'){
unset($_SESSION['NClienteDestino_t']);	
header('location:CompruebaCliente.php');
}


$color='#B8C6DE';
$font='white';
$color2='white';
$font2='black';

?>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/ventana.css" rel="stylesheet" type="text/css" />
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../js/jquery.js"></script>

</head>


<script>
  function realizaProceso(a){
    var result;
    var dato={
        "localidadorigen": a,  
        };
        
        $.ajax({
        async: false,  
        data: dato,
        url:'localidades.php',
        type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
        success: function (respuesta) {
          $("#resultado").html(respuesta);
        }
        });
  }
</script>

<script>
//DIRECCION
  function initMap(){
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
              if(provincia!='Córdoba'){
              alertify.error('La Provincia de origen debe ser Córdoba '+ ' no ' + provincia);          
              document.getElementById('start').value = '';
              document.getElementById('start').focus();
              break;  
              }
            }else if (component.types[0] == 'locality') {
              ciudad = component['long_name'];
              realizaProceso(ciudad);
              if(document.getElementById('resultado').innerText==0){
              alertify.error('La Localidad de origen '+ ciudad +' no se encuentra a nuestro alcance, analice redespacho');          
              document.getElementById('start').value = '';
              document.getElementById('start').focus();
              break;
              }  
              document.getElementById('startciudad').value = ciudad;
             }
            }
        }
        }); 
  }
</script>
<body>
<?php
$Fecha=date('Y-m-d');	
$Dato=$_GET['Dato'];	
echo "<div class='overlay'></div>";
echo "<div class='modal'>";
  
echo "<form class='Caddy' action='' method='get' style='float:center; width:95%;'>";
echo "<div><titulo>Completar Datos Consumidor Final</titulo></div>";
// echo "<div><input name='nasiento_t' size='10' type='hidden' value='$NAsiento'/></div>";
echo "<div><label style='font-size:14px'>Nombre Cliente:</label><input name='nombre_t' size='10' type='text' value='Consumidor Final' style='width:300'/></div>";
// echo "<div><label style='font-size:14px'>Direccion:</label><input name='domicilio_t' size='10' type='text' value='' style='width:300'/></div>";
// echo "<div><label style='font-size:14px'>Ciudad:</label><input name='LocalidadOrigen_t' size='10' type='text' value='' style='width:300'/></div>";
echo "<div><label>Direccion:</label><input type='text' class='form-control' name='start' id='start' style='width:370px;z-index:999' placeholder='Calle Numero'></div>";
echo "<input type='hidden' name='comienzociudad' id='startciudad' style='width:200px;'>";

// echo "<div><label>Ciudad:</label><select name='LocalidadOrigen_t' style='width:300px;'/>";

//       $sqlLocalidades=mysql_query("SELECT Localidad FROM Localidades WHERE Localidad<>'$row[8]' ORDER BY Localidad ASC");		
//       echo "<option value='$row[8]'>$row[8]</option>";        
//       while ($row1 = mysql_fetch_array($sqlLocalidades)){
//       if($row[Web]==0){
//       $Color='red';  
//       }else{
//       $Color='black';  
//       }  
//       echo "<option value='".$row1[Localidad]."' style='color:$color'>$row1[Localidad]</option>";
//       }
//       echo "</select></div>";
echo "<span id='resultado'></span>";
echo "<div><label style='font-size:14px'>Situacion Fiscal:</label><input name='situacionfiscal_t' size='10' type='text' value='' style='width:300'/></div>";
echo "<div><label style='font-size:14px'>Telefono:</label><input name='TelefonoEmisor_t' size='10' type='text' value='' style='width:300'/></div>";
echo "<div ><label style='font-size:14px'>Observaciones</label><input type='text' name='observaciones_t' value='' style='width:300px'></div>";
echo "<div><input name='CargarMovimiento' class='bottom' type='submit' value='Cancelar'>";
echo "<input name='Cargar$Dato' class='bottom' type='submit' value='Aceptar' ></div>";
echo "</form>";

if ($_GET['CargarMovimiento']=='Cancelar'){
header('location:CompruebaCliente.php');
}
if ($_GET['CargarEmisor']=='Aceptar'){
  		$_SESSION['NombreClienteA']=$_GET['nombre_t'];//Nombre
			$_SESSION['DomicilioEmisor_t']=$_GET['domicilio_t'];//Domicilio
			$_SESSION['SituacionFiscalEmisor_t']=$_GET['situacionfiscal_t'];//SituacionFiscal
			$_SESSION['TelefonoEmisor_t']=$_GET['TelefonoEmisor_t'];//Telefono
			$_SESSION['LocalidadOrigen_t']=$_GET['LocalidadOrigen_t'];//Telefono
  		$_SESSION['Observaciones_Cf']=$_GET['observaciones_t'];//Observaciones
  
header('location:CompruebaCliente.php');	
}elseif($_GET['CargarReceptor']=='Aceptar'){
  		$_SESSION['NombreClienteDestino_t']=$_GET['nombre_t'];//Nombre
      $_SESSION['DomicilioDestino_t']=$_GET['domicilio_t'];//Domicilio
			$_SESSION['SituacionFiscalDestino_t']=$_GET['situacionfiscal_t'];//SituacionFiscal
			$_SESSION['TelefonoDestino_t']=$_GET['TelefonoEmisor_t'];//Telefono
			$_SESSION['LocalidadDestino_t']=$_GET['LocalidadOrigen_t'];//Telefono
  		$_SESSION['Observaciones_Cf']=$_GET['observaciones_t'];//Observaciones

  header('location:CompruebaCliente.php');	
}
a:	
echo "</tr></table>";
echo "</div>";  
echo "</body>";
echo "</html>";
ob_end_flush();	
?> 
<script async defer
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&libraries=places&callback=initMap">
</script>
  