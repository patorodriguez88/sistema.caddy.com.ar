<?php
ob_start();
session_start();
include("../ConexionBD.php");
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html;" charset="UTF-8" />
    <title>.::Plataforma Caddy::.</title>
    <link href="css/popup.css" rel="stylesheet" type="text/css" />        
    <link href="../css/StyleCaddyN.css" rel="stylesheet" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="js/miscript.js"></script>
      <script>
      function initMap() {
        //START
        var inputstart = document.getElementById('start');
        var autocomplete = new google.maps.places.Autocomplete(inputstart, { types: ['geocode','establishment'], componentRestrictions: {country: ['AR']} });
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
//              }else if (component.types[0] == "sublocality_level_1") {
               ciudad = component['long_name'];
               if(ciudad ===''){
                document.getElementById('ciudad').type='text';
                 alert('Ciudad');
                 break;
               }else{
                document.getElementById('ciudad').value = ciudad;
               }
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
      }
    </script>     
  <?php

  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
  
  echo "<body style='background-color:#ffffff'>"; 
  include('Menu/menu.html');
  if($_GET[Agregar]=='Cliente'){
  }else{  
  echo "<div id='lateral'>";
    if($_GET[Editar]=='si'){
      }else{
      echo "<form method='POST' class='menuizquierdo' style='margin:10px;float:center;height:100%;'>";
      echo "<input name='menu' type='submit' value='Agregar Cliente' style='background:#E24F30' >";
      echo "</form>";
    }
  echo "</div>";
  }
  echo "<div id='principal'>";
  
if($_POST[Editar]=='Aceptar'){ 
$direccion=$_POST[direccion];
$Observaciones=$_POST[observaciones_t];  
$Contacto=$_POST[contacto_t];  
$sql=mysql_query("UPDATE Clientes SET nombrecliente='$_POST[nc_t]',Calle='$_POST[calle_t]',Numero='$_POST[numero_t]',
Mail='$_POST[mail_t]',Celular='$_POST[telefono_t]',Ciudad='$_POST[ciudad_t]',Direccion='$direccion',Observaciones='$Observaciones',
Contacto='$Contacto' WHERE id='$_POST[id]' AND Relacion='$_SESSION[NCliente]'");
header("location:https://www.caddy.com.ar/SistemaTriangular/Plataforma/MisClientes.php");  
} 
  
if($_POST[ingresar]=='Aceptar'){
$MaxCliente=mysql_query("SELECT MAX(idProveedor)as Userid FROM Clientes WHERE Relacion='".$_SESSION[NCliente]."'");  
$row=mysql_fetch_array($MaxCliente);
$NdeCliente = trim($row[Userid])+1;
$Direccion=$_POST[direccion];
$BuscoCliente=mysql_query("SELECT id FROM Clientes WHERE nombrecliente='$_POST[nc_t]' AND Relacion='".$_SESSION[NCliente]."'");
  if(mysql_num_rows($BuscoCliente)==0){
  $Cuit=$_SESSION[NCliente].''.$NdeCliente;  
  $sql=mysql_query("INSERT INTO Clientes(NdeCliente,nombrecliente,DocumentoNacional,Mail,Ciudad,Provincia,Pais,
  CodigoPostal,Telefono,Celular,Direccion,SituacionFiscal,TipoDocumento,idProveedor,Relacion,Calle,Numero,Observaciones,Cuit) 
  VALUES ('{$Cuit}','{$_POST[nc_t]}','{$_POST[dn_t]}','{$_POST[mail_t]}','{$_POST[ciudad_t]}','Cordoba','Argentina','5000',
  '{$_POST[telefono_t]}','{$_POST[telefono_t]}','{$Direccion}','Consumidor Final','Cuit','{$NdeCliente}',
  '{$_SESSION[NCliente]}','{$_POST[calle_t]}','{$_POST[numero_t]}','{$_POST[observaciones_t]}','{$Cuit}')");
  if($_POST[vuelvo]=='si'){
  header("location:https://www.caddy.com.ar/SistemaTriangular/Plataforma/Calculador.php");  
  }
    
  }else{
  ?> <script>alert('El cliente ya existe');</script><?  
  }

}
if($_GET[Editar]=='si'){
$sqlClientes=mysql_query("SELECT * FROM Clientes WHERE id='$_GET[id]' AND Relacion='$_SESSION[NCliente]'");
$dato=mysql_fetch_array($sqlClientes);  
  ?>
    <form class='login' method='POST' action="" style='width:70%;float:center;'>
    <h2>Editar Cliente:</h2>
      <input type="hidden" name='id'  value="<? echo $dato[id];?>" >
      <div style='width:100%;display: inline-block;'>
      <div style='width:100%;display: inline-block;'>
      <input style='margin-bottom:20px;' type="text" name='nc_t' id="razonsocial"  value="<? echo $dato[nombrecliente];?>" placeholder='Nombre y Apellido' required>
      </div>
      <div style='width:49%;display: inline-block;'>
      <input type="text" class="form-control" name='direccion' id="start" value="<? echo $dato[Direccion];?>" placeholder='Direccion: Calle Numero'>
      </div>
      <input  type="hidden" name='calle_t' id="calle"  value="<? echo $dato[Calle];?>" placeholder='Calle' >
      <div style='width:24%;display: inline-block;'>
      <input  type="text" name='numero_t' id="numero"  value="<? echo $dato[Numero];?>" placeholder='Numero'>
      </div>
      <div style='width:24%;display: inline-block;'>
      <input type='text'  value='<? echo $dato[Ciudad];?>' id="ciudad" name="ciudad_t" placeholder="Ciudad" required>
      </div>
      <div style='width:100%;display: inline-block;'>
      <input style='margin-bottom:20px;' type="text" name='contacto_t' id=""  value="<? echo $dato[Contacto];?>" placeholder='Contacto: Aclaranos el nombre de quien va a recibir tu pedido... .'>  
      </div>
      <input style='margin-bottom:20px;' type="text" name='observaciones_t' id=""  value="<? echo $dato[Observaciones];?>" placeholder='Observaciones: Aclaranos algo del domicilio, Piso, Dpto, Puerta Verde... etc.'>  
      <div style='width: 40%;display: inline-block;'>
      <input style='margin-bottom:20px;' type="text" name='telefono_t' id=""  value="<? echo $dato[Celular];?>" placeholder='Telefono'>
      </div>
      <div style='width: 58%;display: inline-block;'>
      <input style='margin-bottom:20px;' type="text" name='mail_t' id=""  value="<? echo $dato[Mail];?>" placeholder='Mail'>
      </div>
      <input style='float:right' type='submit' name='Editar' value='Aceptar' >
      </div>      
      </form>
  
<?
  goto a;
  
}
  if(($_POST[menu]=='Agregar Cliente')OR($_GET[Agregar]=='Cliente')){
  ?>
    <form class='login' method='POST' action="" style='width:70%;float:center;'>
    <h2>AGREGAR Nuevo Cliente:</h2>
      <div style='width:100%;display: inline-block;'>
      <div style='width:100%;display: inline-block;'>
      <input style='margin-bottom:20px;' type="text" name='nc_t' id="razonsocial"  value="" placeholder='Nommbre y Apellido' required>
      </div>
      <div style='width:100%;display: inline-block;'>
      <div style="width:100%"><input type="text" class="form-control" name='direccion' id="start" placeholder='Direccion: Calle Numero'></div>
      <input type="hidden" name='ciudad_t' id="ciudad" value="">
      <input type="hidden" name='calle_t' id="calle"  value="" placeholder='Calle' >
      <input type="hidden" name='numero_t' id="numero"  value="" placeholder='Numero'>
      </div>
      <input type="hidden" name='Codigo_Postal_t' id="Codigo_Postal_t" placeholder='Ciudad' value="">
      <?
      if($_GET[Agregar]=='Cliente'){
      echo "<input type='hidden' name='vuelvo' value='si'>";  
      }
      ?>
      </div>  
      <div style='width:100%;display: inline-block;'>
      <input style='margin-bottom:20px;' type="text" name='observaciones_t' id=""  value="" placeholder='Observaciones: Aclaranos algo del domicilio, Piso, Dpto, Puerta Verde... etc.'>  
      <div style='width: 40%;display: inline-block;'>
      <input style='margin-bottom:20px;' type="text" name='telefono_t' id=""  value="" placeholder='Telefono'>
      </div>
      <div style='width: 58%;display: inline-block;'>
      <input style='margin-bottom:20px;' type="text" name='mail_t' id=""  value="" placeholder='Mail'>
      </div>

      </div>
       
      <input style='float:right' type='submit' name='ingresar' value='Aceptar' >
            <div style='width:100%;display: inline-block;'>
            </div>
      </div>      
      </form>
      <?
goto a;

}  
  
  //  DESDE ACA MUESTRO LA TABLA     
    $dato=$_POST[service];

  $sqlClientes=mysql_query("SELECT * FROM Clientes WHERE Relacion='$_SESSION[NCliente]'");
      echo "<div style='height:90%;overflow:auto;width:98%'>";

//   echo "<div style='max-height:200px;'>";
    echo "<table class='login'>";
    echo "<caption style='background-color: #4D1A50;'>MIS CLIENTES</caption>";
    echo "<th style='background-color:#E24F30;'>Numero</th>"; 
    echo "<th style='background-color:#E24F30;'>Nombre</th>"; 
    echo "<th style='background-color:#E24F30;'>Direccion</th>"; 
//     echo "<th style='background-color:#E24F30;'>Numero</th>"; 
    echo "<th style='background-color:#E24F30;'>Ciudad</th>";
    echo "<th style='background-color:#E24F30;'>Telefono</th>";
    echo "<th style='background-color:#E24F30;'>Email</th>";
    echo "<th style='background-color:#E24F30;'>Editar</th>";

  $Numero=1;
    while($row=mysql_fetch_array($sqlClientes)){
    echo "<tr>";
      
    echo "<td style='padding:5px'>$Numero</td>";
    echo "<td style='padding:5px'>$row[nombrecliente]</td>";
    echo "<td style='padding:5px'>$row[Direccion]</td>";
//     echo "<td style='padding:5px'>$row[Numero]</td>";
    echo "<td style='padding:5px'>$row[Ciudad]</td>";  
    echo "<td style='padding:5px'>$row[Telefono]</td>";  
    echo "<td style='padding:5px'>$row[Mail]</td>";     
    echo "<td align='center'><a href='https://www.caddy.com.ar/SistemaTriangular/Plataforma/MisClientes.php?Editar=si&id=".$row[id]."'><input type='image' src='images/Editar.png' width='30' height='30' border='0' style='vertical-align:middle;float:center;'></td>";
    echo "</tr>";  
    $Numero++;
    }
    echo "</table>";
    echo "</div>";
    echo "</div>";
a:
        ?>          
</body>
</html>
<script async defer
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&libraries=places&callback=initMap">
</script>

  <?
ob_end_flush();
?>