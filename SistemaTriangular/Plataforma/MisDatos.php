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
    <script src="../js/miscript.js"></script>
  </head>
    <?php

//   header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
//   header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
  
  echo "<body style='background-color:#ffffff'>"; 
  include('Menu/menu.html');
  echo "<div id='lateral'>";
  if($_GET[Agregar]=='Cliente'){
  }else{  
  echo "<form method='POST' class='menuizquierdo' style='margin:10px;float:center;height:100%;'>";
  echo "<input name='menu' type='submit' value='Editar Mis Datos' style='background:#E24F30' >";
  echo "</form>";
  }
  echo "</div>";
  echo "<div id='principal' style='width:80%;overflow-y:hidden;'>";
  
if($_POST[Editar]=='Guardar'){ 
$direccion=$_POST[calle_t].", ".$_POST[numero_t].", ".$_POST[ciudad_t];
$Observaciones=$_POST[observaciones_t];  
$Contacto=$_POST[contacto_t];
$Celular=$_POST[telefono_t];
$Mail=$_POST[mail_t];
$sql=mysql_query("UPDATE Clientes SET nombrecliente='$_POST[nc_t]',Calle='$_POST[calle_t]',Numero='$_POST[numero_t]',
Mail='$_POST[mail_t]',Celular='$_POST[telefono_t]',Ciudad='$_POST[ciudad_t]',Direccion='$direccion',Observaciones='$Observaciones',
Contacto='$Contacto' WHERE id='$_POST[id]'");
header("location:https://www.caddy.com.ar/SistemaTriangular/Plataforma/MisDatos.php");  
}
if($_POST[Editar1]=='Guardar'){
$SituacionFiscal=$_POST[sf_t];
$Cuit=$_POST[ncuit_t];  
$sql=mysql_query("UPDATE Clientes SET SituacionFiscal='$SituacionFiscal',Cuit='$Cuit' WHERE id='$_POST[id]'");
header("location:https://www.caddy.com.ar/SistemaTriangular/Plataforma/MisDatos.php");  
} 
if($_POST[menu]=='Editar Mis Datos'){
$sqlClientes=mysql_query("SELECT * FROM Clientes WHERE id='$_SESSION[NCliente]'");
$dato=mysql_fetch_array($sqlClientes);  
  ?>
    <form class='login' method='POST' action="" style='width:70%;float:center;'>
    <h2 style='background:#E24F30'>Editar Mi informacion General:</h2>
      <input type="hidden" name='id'  value="<? echo $dato[id];?>" >
      <div style='width:100%;display: inline-block;'>
      <div style='width:100%;display: inline-block;'>
      <label>Nombre:</label> 
      <input style='margin-bottom:20px;' type="text" name='nc_t' id="razonsocial"  value="<? echo $dato[nombrecliente];?>" placeholder="Razon Social" >
      </div>
      <div style='width: 45%;display: inline-block;'>
      <label>Calle:</label> 
      <input style='margin-bottom:20px;' type="text" name='calle_t' id="calle"  value="<? echo $dato[Calle];?>" placeholder='Calle' required>
      </div>
      <div style='width: 15%;display: inline-block;'>
      <label>Numero:</label> 
      <input style='margin-bottom:20px;' type="text" name='numero_t' id="numero"  value="<? echo $dato[Numero];?>" placeholder='Numero' required>
      </div>
      <div style='width: 38%;display: inline-block;'>
      <label>Ciudad:</label> 
      <select  id="startciudad" name="ciudad_t">
      
      <?
      if($dato[Ciudad]==''){
      echo "<option value='Cordoba Capital'>Cordoba Capital</option>";    
      }else{
      echo "<option value='$dato[Ciudad]'>$dato[Ciudad]</option>";    
      }
      $sqlBuscoLocalidad=mysql_query("SELECT Localidad FROM Localidades WHERE Web=1 AND Localidad<>'Cordoba Capital'");
      while($Datos=mysql_fetch_array($sqlBuscoLocalidad)){
        echo "<option value='$Datos[Localidad]'>$Datos[Localidad]</option>";  
      }
      ?>
      </select>
      
      </div>  
      <div style='width:100%;display: inline-block;'>
      <label>Contacto:</label>   
      <input style='margin-bottom:20px;' type="text" name='contacto_t' id=""  value="<? echo $dato[Contacto];?>" placeholder='Contanos el nombre de la persona con quien nos contactamos...'>  
      <label>Observaciones:</label> 
      <input style='margin-bottom:20px;' type="text" name='observaciones_t' id=""  value="<? echo $dato[Observaciones];?>" placeholder='Aclaranos algo del domicilio, Piso, Dpto, Pueta Verde... etc.'>  
      <div style='width: 40%;display: inline-block;'>
      <label>Celular:</label> 
      <input style='margin-bottom:20px;' type="text" name='telefono_t' id=""  value="<? echo $dato[Celular];?>" placeholder='Telefono'>
      </div>
      <div style='width: 58%;display: inline-block;'>
      <label>Mail:</label>   
      <input style='margin-bottom:20px;' type="text" name='mail_t' id=""  value="<? echo $dato[Mail];?>" placeholder='Mail'>
      </div>
      <input style='float:right' type='submit' id='boton' name='Editar' value='Guardar'>
      </div>      
      </div>      
      </form>

      <form class='login' method='POST' action="" style='width:70%;float:center;margin-boton:20px;'>
      <h2 style='background:#E24F30'>Editar mi Informacion Administrativa:</h2>
      <div style='width:100%;display: inline-block;'>
      <label>Cuit:</label> 
      <input type="hidden" name='id' value="<? echo $dato[id];?>" >
      <div style='width:100%;display: inline-block;'>
      <input style='margin-bottom:20px;' type="text" name='ncuit_t' id="cuit"  value="<? echo $dato[Cuit];?>" placeholder='Cuit'>
      </div>
      <div style='width: 100%;display: inline-block;'>
      <div><label>Situacion Fiscal:</label> 
      <input style='margin-bottom:20px;' type="text" name='sf_t' id="situacionfiscal"  value="<? echo $dato[SituacionFiscal];?>" placeholder='Situacion Fiscal'>
      </div>
      <input style='float:right' type='submit' name='Editar1' value='Guardar' >
      </div>
      </div>
      </form>
    

<?
  goto a;
  
}
$sqlClientes=mysql_query("SELECT * FROM Clientes WHERE id='$_SESSION[NCliente]'");
$dato=mysql_fetch_array($sqlClientes);  
    
?>
      <form class='login' method='POST' action="" style='width:70%;float:center;'>
      <h2>Informacion General:</h2>
        
      <input type="hidden" name='id'  value="<? echo $dato[id];?>" >
<!--       <div style='width:100%;display: inline-block;'> -->
      <div style='width:100%;display: inline-block;'>
      <label>Nombre:</label>  
      <input style='margin-bottom:20px;' type="text" name='nc_t' id="razonsocial"  value="<? echo $dato[nombrecliente];?>" placeholder='Razon Social' readonly>
      </div>
      <div style='width: 45%;display: inline-block;'>
      <label>Calle:</label> 
      <input style='margin-bottom:20px;' type="text" name='calle_t' id="calle"  value="<? echo $dato[Calle];?>" placeholder='Calle' readonly>
      </div>
      <div style='width: 15%;display: inline-block;'>
      <label>Numero:</label> 
      <input style='margin-bottom:20px;' type="text" name='numero_t' id="numero"  value="<? echo $dato[Numero];?>" placeholder='Numero' readonly>
      </div>
      <div style='width: 38%;display: inline-block;'>
      <label>Ciudad:</label> 
      <input style='margin-bottom:20px;' type="text" name="ciudad_t" value="<? echo $dato[Ciudad]; ?>" placeholder='Ciudad' readonly>
      </div>  
      <div style='width:100%;display: inline-block;'>
      <label>Contacto:</label> 
      <input style='margin-bottom:20px;' type="text" name='contacto_t' id=""  value="<? echo $dato[Contacto];?>" placeholder='Contanos el nombre de la persona con quien nos contactamos...' readonly>  
      </div>
      <label>Observaciones:</label> 
      <input style='margin-bottom:20px;' type="text" name='observaciones_t' id=""  value="<? echo $dato[Observaciones];?>" placeholder='Aclaranos algo del domicilio, Piso, Dpto, Pueta Verde... etc.' readonly>  
      <div style='width: 40%;display: inline-block;'>
      <label>Celular:</label> 
      <input style='margin-bottom:20px;' type="text" name='telefono_t' id=""  value="<? echo $dato[Celular];?>" placeholder='Telefono' readonly>
      </div>
      <div style='width: 58%;display: inline-block;'>
      <label>Mail:</label> 
      <input style='margin-bottom:20px;' type="text" name='mail_t' id=""  value="<? echo $dato[Mail];?>" placeholder='Mail' readonly>
      </div>      
      </form>
        <form class='login' method='POST' action="" style='width:70%;float:center;'>
        <h2>Informacion Administrativa:</h2>
        <div style='width:100%;display: inline-block;'>
        <label>Cuit:</label></div>  
        <input type="hidden" name='id' value="<? echo $dato[id];?>" >
        <div style='width:100%;display: inline-block;'>
          <?
          $Cuit=$dato[Cuit];
          $Cuitstart=substr($dato[Cuit], 0, 2 ); 
          $Cuitend=substr($dato[Cuit], -10 , 1 );
          ?>
        <input style='margin-bottom:20px;' type="text" name='nc_t' id="razonsocial"  value="<? echo $Cuit;?>" placeholder='Cuit' readonly>
        </div>
        <div style='width: 100%;display: inline-block;'>
        <div><label>Situacion Fiscal:</label></div>  
        <input style='margin-bottom:20px;' type="text" name='calle_t' id="calle"  value="<? echo $dato[SituacionFiscal];?>" placeholder='Situacion Fiscal' readonly>
        </div>
        </form>
<?    
//     echo "</div>";
a:
?>          
</body>
</html>
<?
ob_end_flush();
?>