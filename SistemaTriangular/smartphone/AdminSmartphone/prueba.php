<?
if($_POST[Remito]=='prueba'){
?>
<body onLoad="localize()">
<script type="text/javascript" src="../js/ubicacion.js"></script>
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script type="text/javascript" src="../Tracker/miscript.js"></script>

<!-- <p>Latitud: <span id="lti"></span></p> -->
<p>Latitud: <button data-user="10">Dame los datos de la persona con ID = 1</button></p> 
<p>Longitud: <span id="lgi"></span></p>
<p>Presici&oacute;n: <span id="psc"></span></p>
  

<?
}
echo "<form class='feature-image' action='' method='post'>";
echo "<input type='text' id='lti' value='' name='latitud'>";

echo "<input class='button-big' align='center' type='submit' name='Remito' value='prueba' style='font-size:18px;width:95%;height:50px;margin-bottom:5px;background:$ColorBoton;'>";
echo "</form>";
?>