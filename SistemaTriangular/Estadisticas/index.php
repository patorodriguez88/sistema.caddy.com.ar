<?
session_start();
include_once "../ConexionBD.php";
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Highcharts Example</title>
		<style type="text/css">
		</style>
	</head>
	<body>
<script src="../Highcharts-6.0.2/code/highcharts.js"></script>
<script src="../Highcharts-6.0.2/code/modules/exporting.js"></script>

<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
<script type="text/javascript">
Highcharts.chart('container', {
    chart: {
        type: 'line'
    },
    title: {
        text: 'Monthly Average Temperature'
    },
    subtitle: {
        text: 'Source: WorldClimate.com'
    },
    xAxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    },
    yAxis: {
        title: {
            text: 'Temperature (°C)'
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

<?php 
$Desde='2020-01-01';
$Hasta='2020-04-30';
$sql=mysql_query("SELECT RazonSocial,SUM(Cantidad)as Total,SUM(Debe)as TotalImporte,SUM(Entregado)as Entregado,Fecha FROM TransClientes 
WHERE Fecha>='$Desde'AND Fecha<='$Hasta' AND Eliminado=0 AND Debe>0 AND Cantidad>1 GROUP BY (RazonSocial) ORDER BY SUM(Debe) DESC");
while($row=mysql_fetch_array($sql)){
  $Fecha=explode('-',$row['Fecha'],3);
  $Cliente=$row['RazonSocial'];
  $Mes=$Fecha[1];
?>
    name: '<? echo $Cliente;?>', 
    data: [
 <?     
    $Fecha=explode('-',$row['Fecha'],3);
    $Mes=$Fecha[1];
    $Cliente=$row['RazonSocial'];
    if($Mes==1){
    $Enero=$row[Total];
    echo $Cliente;
    echo $Enero;
    }elseif($Mes==2){
    $Febrero=$row[Total];  
    echo $Cliente;
    echo $Febrero;
    }elseif($Mes==3){
    $Marzo=$row[Total];    
    echo $Cliente;
    echo $Marzo;
    }elseif($Mes==4){
    $Abril=$row[Total];    
    echo $Cliente;
    echo $Abril;
    }
?>
      
    ]
 }, {
<?
} 
?>
      }, {
    }]
});
 
		</script>
	</body>
</html>
