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
function _googleAcDebounce(inputEl, opciones) {
    var cfg = Object.assign(
        { fields: ['address_components'], componentRestrictions: { country: 'AR' },
          types: ['geocode','establishment'], debounce: 400, minLength: 3, onSelect: null },
        opciones || {}
    );
    var svc = new google.maps.places.AutocompleteService();
    var placeSvc = new google.maps.places.PlacesService(document.createElement('div'));
    var token = new google.maps.places.AutocompleteSessionToken();
    var timer = null;
    var wrapper = inputEl.parentElement;
    if (getComputedStyle(wrapper).position === 'static') wrapper.style.position = 'relative';
    var ul = document.createElement('ul');
    ul.style.cssText = 'position:absolute;z-index:99999;width:100%;top:100%;left:0;display:none;max-height:220px;' +
        'overflow-y:auto;border-radius:0 0 4px 4px;list-style:none;padding:0;margin:0;' +
        'background:#fff;border:1px solid rgba(0,0,0,.15);box-shadow:0 .25rem .5rem rgba(0,0,0,.1);';
    wrapper.appendChild(ul);
    function close() { ul.style.display = 'none'; }
    function selectPlace(placeId, description) {
        inputEl.value = description; close();
        placeSvc.getDetails({ placeId: placeId, fields: cfg.fields, sessionToken: token }, function(place, status) {
            token = new google.maps.places.AutocompleteSessionToken();
            if (status === google.maps.places.PlacesServiceStatus.OK && cfg.onSelect) cfg.onSelect(place);
        });
    }
    inputEl.addEventListener('input', function() {
        clearTimeout(timer);
        var val = this.value.trim();
        if (val.length < cfg.minLength) { close(); return; }
        var snap = val;
        timer = setTimeout(function() {
            svc.getPlacePredictions(
                { input: snap, sessionToken: token, componentRestrictions: cfg.componentRestrictions, types: cfg.types },
                function(predictions, status) {
                    ul.innerHTML = '';
                    if (status !== google.maps.places.PlacesServiceStatus.OK || !predictions) { close(); return; }
                    predictions.forEach(function(p) {
                        var li = document.createElement('li');
                        li.style.cssText = 'padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #f0f0f0;';
                        li.textContent = p.description;
                        li.addEventListener('mouseover', function() { this.style.background = '#f5f5f5'; });
                        li.addEventListener('mouseout', function() { this.style.background = ''; });
                        li.addEventListener('mousedown', function(e) { e.preventDefault(); selectPlace(p.place_id, p.description); });
                        ul.appendChild(li);
                    });
                    ul.style.display = 'block';
                }
            );
        }, cfg.debounce);
    });
    inputEl.addEventListener('blur', function() { setTimeout(close, 200); });
}
function initMap(){
    var inputstart = document.getElementById('start');
    if (!inputstart) return;
    _googleAcDebounce(inputstart, {
        onSelect: function(place) {
            if (!place || !place.address_components) return;
            var provincia = '', ciudad = '';
            place.address_components.forEach(function(c) {
                var t = c.types[0];
                if (t === 'administrative_area_level_1') {
                    provincia = c.long_name;
                    if (provincia !== 'Córdoba') {
                        alertify.error('La Provincia de origen debe ser Córdoba, no ' + provincia);
                        inputstart.value = ''; inputstart.focus(); return;
                    }
                } else if (t === 'locality') {
                    ciudad = c.long_name;
                    realizaProceso(ciudad);
                    if (document.getElementById('resultado').innerText == 0) {
                        alertify.error('La Localidad ' + ciudad + ' no se encuentra a nuestro alcance, analice redespacho');
                        inputstart.value = ''; inputstart.focus(); return;
                    }
                    document.getElementById('startciudad').value = ciudad;
                }
            });
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
  