<?php
ob_start();
session_start();
require("phpsqlajax_dbinfo.php");
function geolocalizar($direccion){
    // urlencode codifica datos de texto modificando simbolos como acentos
    $direccion = urlencode($direccion);
    // envio la consulta a Google map api
    $url= "https://maps.google.com/maps/api/geocode/json?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&address={$direccion}";
//     $url = "http://maps.google.com/maps/api/geocode/json?address={$direccion}";
    // recibo la respuesta en formato Json
    $datosjson = file_get_contents($url);
//     print "datos:".$datosjson;
    // decodificamos los datos Json
    $datosmapa = json_decode($datosjson, true);
    // si recibimos estado o status igual a OK, es porque se encontro la direccion
    
           
    if($datosmapa['status']==='OK'){
        // asignamos los datos
        $latitud = $datosmapa['results'][0]['geometry']['location']['lat'];
        $longitud = $datosmapa['results'][0]['geometry']['location']['lng'];
        $localizacion = $datosmapa['results'][0]['formatted_address'];
           // Guardamos los datos en una matriz
            $datosmapa = array();           
            array_push(
                $datosmapa,
                $latitud,
                $longitud,
                $localizacion
                );
            return $datosmapa;
      }
} 

function parseToXML($htmlStr)
{
$xmlStr=str_replace('<','&lt;',$htmlStr);
$xmlStr=str_replace('>','&gt;',$xmlStr);
$xmlStr=str_replace('"','&quot;',$xmlStr);
$xmlStr=str_replace("'",'&#39;',$xmlStr);
$xmlStr=str_replace("&",'&amp;',$xmlStr);
return $xmlStr;
}

// Opens a connection to a MySQL server
$connection=mysql_connect ('localhost', $username, $password);
if (!$connection) {
  die('Not connected : ' . mysql_error());
}

// // Set the active MySQL database
$db_selected = mysql_select_db($database, $connection);
if (!$db_selected) {
  die ('Can\'t use db : ' . mysql_error());
}
// Select all the rows in the markers table

  $rec=$_SESSION[recorridos];
  for($i=0;$i<count($rec);$i++){
  $Recorrido.=$rec[$i];  
  $dato=(count($rec)-1);
  if($i==$dato){
  }else{
  $Recorrido.=",";  
  }
}  

     
          $query="SELECT Clientes.id,Clientes.nombrecliente,Clientes.Direccion,Clientes.Ciudad,Clientes.Recorrido,Clientes.Relacion,Clientes.idProveedor FROM Clientes,PreVenta 
          WHERE Clientes.idProveedor=PreVenta.idClienteDestino 
          AND Clientes.Relacion='36' 
          AND PreVenta.NCliente='36' 
          AND PreVenta.Cargado=0 
          GROUP BY PreVenta.idClienteDestino";
     
        
          $result = mysql_query($query);
          if (!$result) {
            die('Invalid query: ' . mysql_error());
          }
          header("Content-type: text/xml");
          
           
          // Start XML file, echo parent node
          echo '<markers>';
          $p=0;
          // Iterate through the rows, printing XML nodes for each
          while ($row = @mysql_fetch_assoc($result)){
            $sqlhojaderuta=mysql_query("SELECT Recorrido FROM HojaDeRuta WHERE id=(SELECT MAX(id)as id FROM HojaDeRuta WHERE idCliente='$row[id]')");
            
          while($datosqlhojaderuta=mysql_fetch_array($sqlhojaderuta)){
          $p++;  
          $ciudad=$row['Ciudad'];
          $direccion=$row['Direccion'].",".$ciudad;
          $localizar=$direccion.",".$ciudad;
          $datosmapa = geolocalizar($localizar);
          $latitud = $datosmapa[0];
          $longitud = $datosmapa[1];
          $localizacion = $datosmapa[2];

            // Add to XML document node
            echo '<marker ';
            echo 'ncliente="' . parseToXML($row['idProveedor']) . '" ';
            echo 'name="' . parseToXML($row['nombrecliente']) . '" ';
            echo 'address="' . parseToXML($direccion) . '" ';
            echo 'lat="' .  parseToXML($latitud) . '" ';
            echo 'lng="' . parseToXML($longitud) . '" ';
            echo 'type="' . parseToXML($datosqlhojaderuta['Recorrido']) . '" ';
            echo 'ciudad="' . parseToXML($ciudad) . '" ';
            echo 'p="' . parseToXML($p) . '" ';
            echo '/>';
            }
     }  
echo '</markers>';

//       }  

// End XML file
// echo '</markers>';

ob_end_flush();
?>