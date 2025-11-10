<?php
session_start();
include_once "../ConexionBD.php";
$user= $_POST['user'];
$password= $_POST['password'];
$color='#B8C6DE';
$font='white';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>
</script>  
<?php
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
echo "</div>"; //lateral
echo  "<div id='principal'>";

setlocale(LC_ALL,'es_AR');
echo "<table class='login' border='1' width='540' vspace='15px' style='margin-top:15px;float:center;'>";
echo "<caption>Resultados Importes Netos Sin Iva</caption>";

// $Year=date('Y');
$Year='2019';
echo "<th>Mes ($Year)</th><th>Ventas</th><th>Gastos (Op)</th><th>Resultado</th><th>Kilometros</th><th>Valor Km</th>";
echo "<tr>";

for($Mes=1;$Mes<=12;$Mes++){
$Datos=mysql_query("SELECT SUM(ImporteNeto+Exento)as Total FROM IvaVentas WHERE YEAR(Fecha)='$Year' AND MONTH(Fecha)='$Mes' AND Eliminado=0");
$row = mysql_fetch_array($Datos);
  
$sqlTesoreria=mysql_query("SELECT SUM(Tesoreria.Debe)as TotalCompras FROM Tesoreria INNER JOIN PlanDeCuentas 
ON Tesoreria.Cuenta=PlanDeCuentas.Cuenta WHERE YEAR(Tesoreria.Fecha)='$Year' AND MONTH(Tesoreria.Fecha)='$Mes' 
AND PlanDeCuentas.TipoCuenta='R-' AND Tesoreria.Eliminado=0 AND Tesoreria.NoOperativo=0 ");
$Gastos = mysql_fetch_array($sqlTesoreria);

$sqlkm=mysql_query("SELECT SUM(KilometrosRecorridos)as Totalkilometros FROM Logistica WHERE YEAR(Fecha)='$Year' AND MONTH(Fecha)='$Mes' AND Eliminado='0'");
$km=mysql_fetch_array($sqlkm);  
  
  if($Mes==1){
$NombreMes='Enero';  
$Total="$ ".number_format($row[Total],2,",",".");    
$TotalGastos="$ ".number_format($Gastos[TotalCompras],2,",",".");    
$TotalKm=$km[Totalkilometros];
$Resultado="$ ".number_format($row[Total]-$Gastos[TotalCompras],2,",",".");
$Valorkm=number_format($Gastos[TotalCompras]/$km[Totalkilometros],2,",",".");  
$_SESSION[$Mes]=$Gastos[TotalCompras]/$km[Totalkilometros];    
  }elseif($Mes==2){
$NombreMes='Febrero';    
$Total="$ ".number_format($row[Total],2,",",".");    
$TotalGastos="$ ".number_format($Gastos[TotalCompras],2,",",".");    
$TotalKm=$km[Totalkilometros];
$Resultado="$ ".number_format($row[Total]-$Gastos[TotalCompras],2,",",".");
$Valorkm=number_format($Gastos[TotalCompras]/$km[Totalkilometros],2,",",".");  
$_SESSION[$Mes]=$Gastos[TotalCompras]/$km[Totalkilometros];
  }elseif($Mes==3){ 
$NombreMes='Marzo';  
$Total="$ ".number_format($row[Total],2,",",".");    
$TotalGastos="$ ".number_format($Gastos[TotalCompras],2,",",".");    
$TotalKm=$km[Totalkilometros];
$Resultado="$ ".number_format($row[Total]-$Gastos[TotalCompras],2,",",".");
$Valorkm=number_format($Gastos[TotalCompras]/$km[Totalkilometros],2,",",".");  
$_SESSION[$Mes]=$Gastos[TotalCompras]/$km[Totalkilometros];
  }elseif($Mes==4){ 
$NombreMes='Abril';  
$Total="$ ".number_format($row[Total],2,",",".");       
$TotalGastos="$ ".number_format($Gastos[TotalCompras],2,",",".");    
$TotalKm=$km[Totalkilometros];
$Resultado="$ ".number_format($row[Total]-$Gastos[TotalCompras],2,",",".");
$Valorkm=number_format($Gastos[TotalCompras]/$km[Totalkilometros],2,",",".");  
$_SESSION[$Mes]=$Gastos[TotalCompras]/$km[Totalkilometros];
  }elseif($Mes==5){ 
$NombreMes='Mayo';  
$Total="$ ".number_format($row[Total],2,",",".");    
$TotalGastos="$ ".number_format($Gastos[TotalCompras],2,",",".");    
$TotalKm=$km[Totalkilometros];
$Resultado="$ ".number_format($row[Total]-$Gastos[TotalCompras],2,",",".");
$Valorkm=number_format($Gastos[TotalCompras]/$km[Totalkilometros],2,",",".");  
$_SESSION[$Mes]=$Gastos[TotalCompras]/$km[Totalkilometros];
  }elseif($Mes==6){ 
$NombreMes='Junio';  
$Total="$ ".number_format($row[Total],2,",",".");    
$TotalGastos="$ ".number_format($Gastos[TotalCompras],2,",",".");    
$TotalKm=$km[Totalkilometros];
$Resultado="$ ".number_format($row[Total]-$Gastos[TotalCompras],2,",",".");
$Valorkm=number_format($Gastos[TotalCompras]/$km[Totalkilometros],2,",",".");  
$_SESSION[$Mes]=$Gastos[TotalCompras]/$km[Totalkilometros];
  }elseif($Mes==7){ 
$NombreMes='Julio';  
$Total="$ ".number_format($row[Total],2,",",".");    
$TotalGastos="$ ".number_format($Gastos[TotalCompras],2,",",".");    
$TotalKm=$km[Totalkilometros];
$Resultado="$ ".number_format($row[Total]-$Gastos[TotalCompras],2,",",".");
$Valorkm=number_format($Gastos[TotalCompras]/$km[Totalkilometros],2,",",".");  
$_SESSION[$Mes]=$Gastos[TotalCompras]/$km[Totalkilometros];
  }elseif($Mes==8){ 
$NombreMes='Agosto';  
$Total="$ ".number_format($row[Total],2,",",".");    
$TotalGastos="$ ".number_format($Gastos[TotalCompras],2,",",".");    
$TotalKm=$km[Totalkilometros];
$Resultado="$ ".number_format($row[Total]-$Gastos[TotalCompras],2,",",".");
$Valorkm=number_format($Gastos[TotalCompras]/$km[Totalkilometros],2,",",".");  
$_SESSION[$Mes]=$Gastos[TotalCompras]/$km[Totalkilometros];
  }elseif($Mes==9){ 
$NombreMes='Septiembre';  
$Total="$ ".number_format($row[Total],2,",",".");    
$TotalGastos="$ ".number_format($Gastos[TotalCompras],2,",",".");    
$TotalKm=$km[Totalkilometros];
$Resultado="$ ".number_format($row[Total]-$Gastos[TotalCompras],2,",",".");
$Valorkm=number_format($Gastos[TotalCompras]/$km[Totalkilometros],2,",",".");  
$_SESSION[$Mes]=$Gastos[TotalCompras]/$km[Totalkilometros];
  }elseif($Mes==10){ 
$NombreMes='Octubre';  
$Total="$ ".number_format($row[Total],2,",",".");    
$TotalGastos="$ ".number_format($Gastos[TotalCompras],2,",",".");    
$TotalKm=$km[Totalkilometros];
$Resultado="$ ".number_format($row[Total]-$Gastos[TotalCompras],2,",",".");
$Valorkm=number_format($Gastos[TotalCompras]/$km[Totalkilometros],2,",",".");  
$_SESSION[$Mes]=$Gastos[TotalCompras]/$km[Totalkilometros];
  }elseif($Mes==11){ 
$NombreMes='Noviembre';  
$Total="$ ".number_format($row[Total],2,",",".");    
$TotalGastos="$ ".number_format($Gastos[TotalCompras],2,",",".");    
$TotalKm=$km[Totalkilometros];
$Resultado="$ ".number_format($row[Total]-$Gastos[TotalCompras],2,",",".");
$Valorkm=number_format($Gastos[TotalCompras]/$km[Totalkilometros],2,",",".");  
$_SESSION[$Mes]=$Gastos[TotalCompras]/$km[Totalkilometros];
  }elseif($Mes==12){ 
$NombreMes='Diciembre';  
$Total="$ ".number_format($row[Total],2,",",".");    
$TotalGastos="$ ".number_format($Gastos[TotalCompras],2,",",".");    
$TotalKm=$km[Totalkilometros];
$Resultado="$ ".number_format($row[Total]-$Gastos[TotalCompras],2,",",".");
$Valorkm=number_format($Gastos[TotalCompras]/$km[Totalkilometros],2,",",".");  
$_SESSION[$Mes]=$Gastos[TotalCompras]/$km[Totalkilometros];
  }
echo "<td>$NombreMes</td><td>$Total</td><td>$TotalGastos</td><td>$Resultado</td><td>$TotalKm</td><td>$ $Valorkm</td>";
echo "</tr>";
}
echo "</table>";


echo "<table class='login' border='1' width='540' vspace='15px' style='margin-top:15px;float:center;'>";
echo "<caption>Facturados por Recorrido</caption>";

// $Year=date('Y');

// echo "<th>Mes</th><th>Recorrido</th><th>Salidas</th><th>Total Km.</th><th>Precio Recorrido</th>";
// echo "<tr>";

  $sqlRecorridos=mysql_query("SELECT Recorridos.Nombre,Recorridos.Numero,Productos.PrecioVenta FROM `Productos` INNER JOIN Recorridos ON Recorridos.CodigoProductos=Productos.Codigo GROUP BY Recorridos.Numero");
  while($row=mysql_fetch_array($sqlRecorridos)){
//           echo "<caption>Recorrido $row[Numero]</caption>";
      echo "<tr colspan='7'><th colspan='7'>Recorrido $row[Nombre] ($row[Numero]) Precio Actual $ $row[PrecioVenta]</th></tr>";
      echo "<th>Mes</th><th>Recorrido</th><th>Salidas</th><th>Total Km.</th><th>Km Promedio</th><th>Sugerido</th><th>Diferencia</th>";
      echo "<tr>";

  
    for($Mes=1;$Mes<=12;$Mes++){

    $sqlkm=mysql_query("SELECT COUNT(id)as Cantidad,SUM(KilometrosRecorridos)as Totalkilometros FROM Logistica WHERE Recorrido='$row[Numero]' AND YEAR(Fecha)='$Year' AND MONTH(Fecha)='$Mes' AND Eliminado='0' ");
    $km=mysql_fetch_array($sqlkm);  
      if($Mes==1){
      $NombreMes='Enero';  
      $Total=$km[Totalkilometros];
      $Salidas=$km[Cantidad];
        if($Total<>0){
        $Calculo=number_format($Total/$Salidas,2,",",".");  
        $Sugerido=number_format($_SESSION[$Mes]*($Total/$Salidas),2,",",".");
        $Diferencia=number_format($row[PrecioVenta]-($_SESSION[$Mes]*$Calculo),2,",",".");  
        }else{
        $Calculo=0;  
        $Sugerido=0;
        $Diferencia=0;  
        }
      }elseif($Mes==2){
      $NombreMes='Febrero';  
      $Total=$km[Totalkilometros];
      $Salidas=$km[Cantidad];
        if($Total<>0){
        $Calculo=number_format($Total/$Salidas,2,",",".");  
        $Sugerido=number_format($_SESSION[$Mes]*($Total/$Salidas),2,",",".");
        $Diferencia=number_format($row[PrecioVenta]-($_SESSION[$Mes]*$Calculo),2,",",".");  
        }else{
        $Calculo=0;  
        $Sugerido=0;
        $Diferencia=0;  
        }
      }elseif($Mes==3){
      $NombreMes='Marzo';  
      $Total=$km[Totalkilometros];
      $Salidas=$km[Cantidad];
        if($Total<>0){
        $Calculo=number_format($Total/$Salidas,2,",",".");  
        $Sugerido=number_format($_SESSION[$Mes]*($Total/$Salidas),2,",",".");
        $Diferencia=number_format($row[PrecioVenta]-($_SESSION[$Mes]*$Calculo),2,",",".");  
        }else{
        $Calculo=0;  
        $Sugerido=0;
        $Diferencia=0;  
        }
      }elseif($Mes==4){
      $NombreMes='Abril';  
      $Total=$km[Totalkilometros];
      $Salidas=$km[Cantidad];
        if($Total<>0){
        $Calculo=number_format($Total/$Salidas,2,",",".");  
        $Sugerido=number_format($_SESSION[$Mes]*($Total/$Salidas),2,",",".");
        $Diferencia=number_format($row[PrecioVenta]-($_SESSION[$Mes]*$Calculo),2,",",".");  
        }else{
        $Calculo=0;  
        $Sugerido=0;
        $Diferencia=0;  
        }
      }elseif($Mes==5){
      $NombreMes='Mayo';  
      $Total=$km[Totalkilometros];
      $Salidas=$km[Cantidad];
        if($Total<>0){
        $Calculo=number_format($Total/$Salidas,2,",",".");  
        $Sugerido=number_format($_SESSION[$Mes]*($Total/$Salidas),2,",",".");
        $Diferencia=number_format($row[PrecioVenta]-($_SESSION[$Mes]*$Calculo),2,",",".");  
        }else{
        $Calculo=0;  
        $Sugerido=0;
        $Diferencia=0;  
        }
      }elseif($Mes==6){
      $NombreMes='Junio';  
      $Total=$km[Totalkilometros];
      $Salidas=$km[Cantidad];
        if($Total<>0){
        $Calculo=number_format($Total/$Salidas,2,",",".");  
        $Sugerido=number_format($_SESSION[$Mes]*($Total/$Salidas),2,",",".");
        $Diferencia=number_format($row[PrecioVenta]-($_SESSION[$Mes]*$Calculo),2,",",".");  
        }else{
        $Calculo=0;  
        $Sugerido=0;
        $Diferencia=0;  
        }
      }elseif($Mes==7){
      $NombreMes='Julio';  
      $Total=$km[Totalkilometros];
      $Salidas=$km[Cantidad];
        if($Total<>0){
        $Calculo=number_format($Total/$Salidas,2,",",".");  
        $Sugerido=number_format($_SESSION[$Mes]*($Total/$Salidas),2,",",".");
        $Diferencia=number_format($row[PrecioVenta]-($_SESSION[$Mes]*$Calculo),2,",",".");  
        }else{
        $Calculo=0;  
        $Sugerido=0;
        $Diferencia=0;  
        }
      }elseif($Mes==8){
      $NombreMes='Agosto';  
      $Total=$km[Totalkilometros];
      $Salidas=$km[Cantidad];
        if($Total<>0){
        $Calculo=number_format($Total/$Salidas,2,",",".");  
        $Sugerido=number_format($_SESSION[$Mes]*($Total/$Salidas),2,",",".");
        $Diferencia=number_format($row[PrecioVenta]-($_SESSION[$Mes]*$Calculo),2,",",".");  
        }else{
        $Calculo=0;  
        $Sugerido=0;
        $Diferencia=0;  
        }
      }elseif($Mes==9){
      $NombreMes='Septiembre';  
      $Total=$km[Totalkilometros];
      $Salidas=$km[Cantidad];
        if($Total<>0){
        $Calculo=number_format($Total/$Salidas,2,",",".");  
        $Sugerido=number_format($_SESSION[$Mes]*($Total/$Salidas),2,",",".");
        $Diferencia=number_format($row[PrecioVenta]-($_SESSION[$Mes]*$Calculo),2,",",".");  
        }else{
        $Calculo=0;  
        $Sugerido=0;
        $Diferencia=0;  
        }
      }elseif($Mes==10){
      $NombreMes='Octubre';  
      $Total=$km[Totalkilometros];
      $Salidas=$km[Cantidad];
        if($Total<>0){
        $Calculo=number_format($Total/$Salidas,2,",",".");  
        $Sugerido=number_format($_SESSION[$Mes]*($Total/$Salidas),2,",",".");
        $Diferencia=number_format($row[PrecioVenta]-($_SESSION[$Mes]*$Calculo),2,",",".");  
        }else{
        $Calculo=0;  
        $Sugerido=0;
        $Diferencia=0;  
        }
      }elseif($Mes==11){
      $NombreMes='Noviembre';  
      $Total=$km[Totalkilometros];
      $Salidas=$km[Cantidad];
        if($Total<>0){
        $Calculo=number_format($Total/$Salidas,2,",",".");  
        $Sugerido=number_format($_SESSION[$Mes]*($Total/$Salidas),2,",",".");
        $Diferencia=number_format($row[PrecioVenta]-($_SESSION[$Mes]*$Calculo),2,",",".");  
        }else{
        $Calculo=0;  
        $Sugerido=0;
        $Diferencia=0;  
        }
      }elseif($Mes==12){
      $NombreMes='Diciembre';  
      $Total=$km[Totalkilometros];
      $Salidas=$km[Cantidad];
        if($Total<>0){
        $Calculo=number_format($Total/$Salidas,2,",",".");  
        $Sugerido=number_format($_SESSION[$Mes]*($Total/$Salidas),2,",",".");
        $Diferencia=number_format($row[PrecioVenta]-($_SESSION[$Mes]*$Calculo),2,",",".");  
        
        }else{
        $Calculo=0;  
        $Sugerido=0;
        $Diferencia=0;  
        }
      }
      echo "<td>$NombreMes</td><td>$row[Numero]</td><td>$Salidas</td><td>$Total</td><td>$Calculo</td><td>$ $Sugerido</td><td>$ $Diferencia</td>";
      echo "</tr>";
     }
//     $Dif+=$Diferencia;
//     echo "<tr><td>Promedio $Dif</td></tr>";

  }

echo $totaltotal;



echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor

?>