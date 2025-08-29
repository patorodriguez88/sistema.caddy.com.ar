<?
session_start();
include("../ConexionBD.php");
?>
<!DOCTYPE html>
    <html lang="es">
    <head>

    <meta charset="utf-8" />
        <title>Sistema Caddy | Routes</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
       <!-- App favicon -->
       <link rel="shortcut icon" href="../images/favicon/favicon.ico">
        <!-- third party css -->
        <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
        <!-- third party css end -->
        <!-- App css -->
        <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />

        

    <!-- <script type="text/javascript" src="../../js/jquery.js"></script> -->

    <meta charset="utf-8">
    <title>Mapa Hoja de Ruta Triangular</title>
    <style>
      #right-panel {
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }

      #right-panel select, #right-panel input {
        font-size: 15px;
      }

      #right-panel select {
        width: 100%;
      }

      #right-panel i {
        font-size: 12px;
      }
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #map {
        height: 100%;
        float: left;
        width: 60%;
        height: 100%;
      }
      #right-panel {
        margin: 5px;
        border-width: 2px;
        width: 30%;
        height: 400px;
        float: left;
        text-align: left;
        padding-top: 0;
      }
      #directions-panel {
        margin-top: 10px;
        background-color: #AED6F1; #FFEE77;
        padding: 10px;
        overflow: scroll;
        height: 500px;
        color:white;
      }
    </style>
  </head>
  <body>
        <!-- Begin page -->

                  <style>
                  .modal{
                      z-index: 20;   

                    }
                  .modal-backdrop{
                      z-index: 10;        
                  }
                  </style>

<div id="container">
      <div id="map"></div>
      <div id="sidebar">
        <p>Total Distance: <span id="total"></span></p>
        <div id="panel"></div>
      </div>
    </div>

    <!-- <div id="map"></div> -->
    <!-- <div id="right-panel"></div> -->
    <!-- <div id="Total"></div> -->

    <div>
    <b>Comienzo:</b>
    <select id="start">
      <option value="Reconquista 4986, Córdoba, Argentina">Caddy</option>
      <option value="Justiniano Posse 1236 Córdoba, Argentina">Wepoint</option> 
      
    </select>
    <br>
    <b>Waypoints:</b> <br>
    <i>(Ctrl+Click or Cmd+Click for multiple selection)</i> <br>
<?
  
$Recorrido=$_SESSION['Recorrido'];
// $Recorrido='53';
$query="SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto' AND Eliminado=0 AND Devuelto=0 ORDER BY Posicion ";
$result = mysql_query($query);
      
echo "<select multiple id='waypoints'>";
while ($row = @mysql_fetch_array($result)){
// $localiza=$row[Localizacion].",".$row[Ciudad];  
// echo "<input type='hidden' value='$localiza' id='waypoints[]'>";
  echo "<option value='$row[Localizacion],$row[Ciudad]'>$row[Localizacion]</option>";

}
  echo "</select>";
?>
       <br>
    <b>Final:</b>
<?
    $query="SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto' AND Eliminado='0' ORDER BY Posicion";
    $result = mysql_query($query);
    echo"<select id='end'>";
    // echo"<option value='Justiniano Posse 1236, Córdoba, Argentina'>Triangular S.A.</option>";
    while ($row = @mysql_fetch_array($result)){
    echo "<option value='$row[Localizacion].",".$row[Ciudad]'>$row[Localizacion]</option>";
    }
    echo "</select>";
?>
      <br>
      <input type="submit" id="submit">
    </div>
    <div id="directions-panel"></div>
    </div>
        </div>

  </body>

        <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>         -->
        <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/app.min.js"></script>

        <!-- third party js -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/dataTables.keyTable.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script> -->
        <!-- third party js ends -->


  <!-- <script src="Mapas/js/routes.js"></script> -->
  <script src="Mapas/js/datos.js"></script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap">
    </script>
</html>