<?
session_start();
include("../ConexionBD.php");
?>
<!DOCTYPE html>
<html>
<head>
<title>Plataforma Caddy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<!--   <link href="css/style.css" rel="stylesheet" type="text/css" />
  </head>
<body>
    <input type="checkbox" id="abrir-cerrar" name="abrir-cerrar" value="">
    <label for="abrir-cerrar">&#9776; <span class="abrir">Abrir</span><span class="cerrar">Cerrar</span></label>
    <div id="sidebar" class="sidebar">
      <ul class="menu">
            <li><a href="Plataforma.php?NuevoEnvio=new">Cargar Envios</a></li>
            <li><a href="#">Importar Envios</a></li>
        </ul>
      </div> -->
<!--     <div id="contenido"> -->

      <?
      if($_GET[NuevoEnvio]=='new'){
      include('formulario.php');
      goto a;
      }else{
      include('grafico.php');
      goto a;
        
      }
      ?>

<!--   <div id="contenido"> -->

      <script src="Highcharts-7.0.3/code/highcharts.js"></script>
<script src="Highcharts-7.0.3/code/modules/exporting.js"></script>
<script src="Highcharts-7.0.3/code/modules/export-data.js"></script>
<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto;"></div>
  
		<script type="text/javascript">
Highcharts.chart('container', {
    chart: {
        type: 'line'
    },
    title: {
        text: 'Entregas Mensuales'
    },
    subtitle: {
        text: 'Datos: Caddy Yo lo llevo!'
    },
    xAxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    },
    yAxis: {
        title: {
            text: 'Entregas Totales'
        }
    },
    plotOptions: {
        line: {
            dataLabels: {
                enabled: true
            },
            enableMouseTracking: false
        }
    },
    series: [{
     name: 'Entregados',
      data: [

           <?php
    			$sql=mysql_query("SELECT MONTH(Seguimiento.Fecha)as Mes, SUM(TransClientes.Cantidad)as Total FROM (`TransClientes`,`Seguimiento`) 
          WHERE 
          TransClientes.CodigoSeguimiento=Seguimiento.CodigoSeguimiento AND 
          TransClientes.Eliminado=0 AND 
          Seguimiento.Estado='Entregado al Cliente' AND
          Seguimiento.Fecha BETWEEN '2019-01-01 00:00:00.000' AND CURRENT_DATE()
          GROUP BY MONTH(Seguimiento.Fecha) ORDER BY MONTH(Seguimiento.Fecha)");

//           $sql=mysql_query("SELECT MONTH(Fecha)as Mes, SUM(Cantidad)as Total FROM TransClientes WHERE Entregado='1' AND Eliminado='0' AND RazonSocial='DINTER S.A. CBA' AND Fecha BETWEEN '2019-01-01 00:00:00.000' AND CURRENT_DATE() GROUP BY MONTH(Fecha) ORDER BY MONTH(Fecha)");
					while($Dato=mysql_fetch_array($sql)){
					echo $Dato[Total].",";
     		  } 
           ?>
//     data: [7.0, 6.9, 9.5, 14.5, 18.4, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
      ]
    }, {
        name: 'No Entregados',
        data: [
          <?php
     			$sql=mysql_query("SELECT MONTH(Seguimiento.Fecha)as Mes, SUM(TransClientes.Cantidad)as Total FROM (`TransClientes`,`Seguimiento`) 
          WHERE 
          TransClientes.CodigoSeguimiento=Seguimiento.CodigoSeguimiento AND 
          TransClientes.Eliminado=0 AND 
          Seguimiento.Estado='No se pudo entregar' AND
          Seguimiento.Fecha BETWEEN '2019-01-01 00:00:00.000' AND CURRENT_DATE()
          GROUP BY MONTH(Seguimiento.Fecha) ORDER BY MONTH(Seguimiento.Fecha)");
//           $sql=mysql_query("SELECT MONTH(Fecha)as Mes, SUM(Cantidad)as Total FROM TransClientes WHERE Entregado='0' AND Eliminado='0' AND RazonSocial='DINTER S.A. CBA' AND Fecha BETWEEN '2019-01-01 00:00:00.000' AND CURRENT_DATE() GROUP BY MONTH(Fecha) ORDER BY MONTH(Fecha)");
					while($Dato=mysql_fetch_array($sql)){
					echo $Dato[Total].",";
     		  } 
           ?>
      ]
    }]
});
		</script>

      <?
      a:
      ?>
      </div>
</body>
</html> 
