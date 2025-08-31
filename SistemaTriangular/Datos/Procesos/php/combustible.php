<?
session_start();
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

if($_POST['EstadisticasCombustible_0']==1){

    $sql="SELECT SUM(Total)AS Total FROM `Ypf` WHERE MONTH(Fecha) = 4 AND YEAR(Fecha) = YEAR(CURRENT_DATE)";
    $Resultado=$mysqli->query($sql);
    
     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
     
     $Total[]=intval($row['Total']);

     }

     echo json_encode(array('total_expend'=>$Total));
}

if($_POST['EstadisticasCombustible']==1){

    $sql="SELECT Producto,SUM(Total)AS Total FROM `Ypf` WHERE MONTH(Fecha) = 4 AND YEAR(Fecha) = YEAR(CURRENT_DATE) GROUP BY Producto";
    $Resultado=$mysqli->query($sql);
    
     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
     $Producto[]=$row['Producto'];
     $Total[]=intval($row['Total']);
     }

     echo json_encode(array('Producto'=>$Producto,'Total'=>$Total));
    
}
if($_POST['EstadisticasCombustible_1']==1){

    $sql="SELECT IF(Empleados.NombreCompleto IS NULL,Conductor,Empleados.NombreCompleto)as Conductor,SUM(Total)as Total FROM `Ypf` LEFT JOIN Empleados ON Ypf.Conductor=Empleados.Dni WHERE MONTH(Fecha) = 4 AND YEAR(Fecha) = YEAR(CURRENT_DATE) GROUP BY Conductor";
    $Resultado=$mysqli->query($sql);
    
     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
     $Conductor[]=$row['Conductor'];
     $Total[]=intval($row['Total']);
     }

     echo json_encode(array('Conductor'=>$Conductor,'Total'=>$Total));
    
}
    
if($_POST['EstadisticasCombustible_2']==1){

    $sql="SELECT MONTHNAME(Fecha)as Mes, SUM(Total) AS Total FROM Ypf WHERE YEAR(fecha)=YEAR(CURRENT_DATE) GROUP BY MONTH(Fecha)";

    $Resultado=$mysqli->query($sql);
    
     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
     $Mes[]=$row['Mes'];
     $Total[]=intval($row['Total']);
     }

     echo json_encode(array('Mes'=>$Mes,'Total'=>$Total));
    
}
//X VEHICULO
if($_POST['EstadisticasCombustible_3']==1){

    $sql="SELECT Patente, SUM(Total) AS Total FROM Ypf WHERE MONTH(Fecha)=4 AND YEAR(Fecha)=YEAR(CURRENT_DATE) GROUP BY Patente";

    $Resultado=$mysqli->query($sql);
    
     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
     $Vehiculo[]=$row['Patente'];
     $Total[]=intval($row['Total']);
     }

     echo json_encode(array('Vehiculo'=>$Vehiculo,'Total'=>$Total));
    
}
// AA056 XV
if($_POST['EstadisticasCombustible_4']==1){
    
    $sql="SELECT Patente, Producto, SUM(Total)AS Total FROM Ypf WHERE MONTH(Fecha)=4 AND YEAR(Fecha)=YEAR(CURRENT_DATE) AND Patente='AA056 XV' GROUP by Patente,Producto";

    $Resultado=$mysqli->query($sql);
    
     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
     $Vehiculo[]=$row['Patente'];
     $Producto[]=$row['Producto'];
     $Total[]=intval($row['Total']);
     }

     echo json_encode(array('Vehiculo'=>$Vehiculo,'Producto'=>$Producto,'Totales'=>$Total));
}

// OQR318
if($_POST['EstadisticasCombustible_4']==2){
    
    $sql="SELECT Patente, Producto, SUM(Total)AS Total FROM Ypf WHERE MONTH(Fecha)=4 AND YEAR(Fecha)=YEAR(CURRENT_DATE) AND Patente='OQR318' GROUP by Patente,Producto";

    $Resultado=$mysqli->query($sql);
    
     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
     $Vehiculo[]=$row['Patente'];
     $Producto[]=$row['Producto'];
     $Total[]=intval($row['Total']);
     }

     echo json_encode(array('Vehiculo'=>$Vehiculo,'Producto'=>$Producto,'Totales'=>$Total));
}

//AD917 CR
if($_POST['EstadisticasCombustible_4']==3){
    
    $sql="SELECT Patente, Producto, SUM(Total)AS Total FROM Ypf WHERE MONTH(Fecha)=4 AND YEAR(Fecha)=YEAR(CURRENT_DATE) AND Patente='AD917 CR' GROUP by Patente,Producto";

    $Resultado=$mysqli->query($sql);
    
     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
     $Vehiculo[]=$row['Patente'];
     $Producto[]=$row['Producto'];
     $Total[]=intval($row['Total']);
     }

     echo json_encode(array('Vehiculo'=>$Vehiculo,'Producto'=>$Producto,'Totales'=>$Total));
}

?>    