<?
session_start();
include("../ConexionBD.php");
$sqlNombreCliente=mysql_query("SELECT nombrecliente FROM Clientes WHERE id='$_SESSION[NCliente]'");
$RazonSocial=mysql_fetch_array($sqlNombreCliente);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Inicio Sesion</title>
		<meta charset='utf-8' />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../css/StyleCaddyN.css" rel="stylesheet" type="text/css" />
    <script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
	  </head>
    <body style='background-color:#ffffff'>
  
<? include('Menu/menu.html');
echo "<form action='' method='POST' class='login' style='height:450px;width:700px' >";
echo "<h2>ESTADISTICAS DE ENVIOS </h2>";
        
    			$sql0=mysql_query("SELECT MONTH(Seguimiento.Fecha)as Mes, SUM(TransClientes.Cantidad)as Total FROM (`TransClientes`,`Seguimiento`) 
          WHERE 
          TransClientes.CodigoSeguimiento=Seguimiento.CodigoSeguimiento AND 
          TransClientes.Eliminado=0 AND 
          Seguimiento.Estado='Entregado al Cliente' AND
          Seguimiento.Fecha BETWEEN '2019-01-01 00:00:00.000' AND CURRENT_DATE()
          AND TransClientes.RazonSocial='$RazonSocial[nombrecliente]'
          GROUP BY MONTH(Seguimiento.Fecha) ORDER BY MONTH(Seguimiento.Fecha)");
//           $contador = 1;
//           $cantidad_row = mysql_num_rows($sql0);
//           while($Dato0=mysql_fetch_array($sql0)){
//           if($contador == $cantidad_row){
//            echo "'".$Dato0[Mes]."'";    
//           }else{
//           echo "'".$Dato0[Mes]."',";  
//             $contador++;
//             }
//           }
      
// goto a;
      ?>
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
      
        categories: [
        <?
          $sql=mysql_query("SELECT MONTH(Seguimiento.Fecha)as Mes, SUM(TransClientes.Cantidad)as Total FROM (`TransClientes`,`Seguimiento`) 
          WHERE 
          TransClientes.CodigoSeguimiento=Seguimiento.CodigoSeguimiento AND 
          TransClientes.Eliminado=0 AND 
          Seguimiento.Estado='Entregado al Cliente' AND
          Seguimiento.Fecha BETWEEN '2019-01-01 00:00:00.000' AND CURRENT_DATE()
          AND TransClientes.RazonSocial='$RazonSocial[nombrecliente]'
          GROUP BY MONTH(Seguimiento.Fecha) ORDER BY MONTH(Seguimiento.Fecha)");

          $contador = 1;
          $cantidad_row = mysql_num_rows($sql);
          while($Dato=mysql_fetch_array($sql)){
          if($contador == $cantidad_row){
           echo "'".$Dato[Mes]."'";    
          }else{
          echo "'".$Dato[Mes]."',";  
            $contador++;
            }
          }
  ?>
      
//           '1', '2', '3','Nov', 'Dec']
          
        ]
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
// data: [7.0, 6.9, 9.5, 14.5, 18.4, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
           <?php
          $contadorn = 1;
          $cantidad_rown = mysql_num_rows($sql0);

          while($Dato0n=mysql_fetch_array($sql0)){
          if($contadorn == $cantidad_rown){
            echo $Dato0n[Total];    
            }else{
            echo $Dato0n[Total].",";  
             $contadorn++;
            }
          }

        ?>
//     data: [7.0, 6.9, 9.5, 14.5, 18.4, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
      ]
    }, {
        name: 'No Entregados',
        data: [
          <?php
          $sql1=mysql_query("SELECT MONTH(Seguimiento.Fecha)as Mes, SUM(TransClientes.Cantidad)as Total FROM (`TransClientes`,`Seguimiento`) 
          WHERE 
          TransClientes.CodigoSeguimiento=Seguimiento.CodigoSeguimiento AND 
          TransClientes.Eliminado=0 AND 
          Seguimiento.Estado='No se pudo entregar' AND
          Seguimiento.Fecha BETWEEN '2019-01-01 00:00:00.000' AND CURRENT_DATE()
          AND TransClientes.RazonSocial='$RazonSocial[nombrecliente]'
          GROUP BY MONTH(Seguimiento.Fecha) ORDER BY MONTH(Seguimiento.Fecha)");
            while($Dato=mysql_fetch_array($sql1)){
            echo $Dato[Total].",";
            } 
           ?>
      ]
    }]
});
		</script>

    
    
    </div>
	</div>
<? 
a:
echo "</form>";

?>


</body>
</html>
