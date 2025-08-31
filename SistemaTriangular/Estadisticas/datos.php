<?php
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);  
$Desde='2020-01-01';
$Hasta='2020-04-30';
$sql=mysql_query("SELECT RazonSocial,SUM(Cantidad)as Total,SUM(Debe)as TotalImporte,SUM(Entregado)as Entregado,Fecha FROM TransClientes 
WHERE Fecha>='$Desde'AND Fecha<='$Hasta' AND Eliminado=0 AND Debe>0  GROUP BY (RazonSocial) ORDER BY SUM(Debe) DESC");
while($row=mysql_fetch_array($sql)){
  $Fecha=explode('-',$row['Fecha'],3);
  $Mes=$Fecha[1];
    $Cliente=$row['RazonSocial'];
    if($Mes==1){
    $Enero=$row[Total];
    echo $Cliente.'<br>';
    echo 'enero'.$Enero.'<br>';
    }elseif($Mes==2){
    $Febrero=$row[Total];  
    echo $Cliente.'<br>';
    echo 'feb'.$Febrero.'<br>';
    }elseif($Mes==3){
    $Marzo=$row[Total];    
    echo $Cliente.'<br>';
    echo 'mar'.$Marzo.'<br>';
    }elseif($Mes==4){
    $Abril=$row[Total];    
    echo $Cliente.'<br>';
    echo 'abr'.$Abril.'<br>';
    }
}
  
