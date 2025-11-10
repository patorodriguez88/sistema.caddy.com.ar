<?php
// session_start();
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');


if(isset($_POST['Control'])){ 

    $Fecha=explode(' - ',$_POST['Fechas'],2);      

    $FechaInicio=explode('/',$Fecha[0],3);
    $FechaI=$FechaInicio[2].'-'.$FechaInicio[0].'-'.$FechaInicio[1];

    $FechaFinal=explode('/',$Fecha[1],3);
    $FechaF=$FechaFinal[2].'-'.$FechaFinal[0].'-'.$FechaFinal[1];
   
if($_POST['Recorrido']==''){   

    $sql="SELECT Logistica.id,Fecha,Hora,FechaRetorno,HoraRetorno,Patente,Logistica.Estado,Logistica.Recorrido,Logistica.NombreChofer,Logistica.NombreChofer2,
    Vehiculos.Marca,Vehiculos.Modelo,Recorridos.Nombre FROM Logistica 
    INNER JOIN Vehiculos on Logistica.Patente = Vehiculos.Dominio 
    INNER JOIN Recorridos on Logistica.Recorrido=Recorridos.Numero
    WHERE Logistica.Fecha>='$FechaI' AND Logistica.Fecha<='$FechaF' AND Logistica.Fecha<>'0000-00-00' 
    AND Logistica.Eliminado=0 ORDER BY Fecha";
   
    }else{      

    $sql="SELECT Logistica.id,Fecha,Hora,FechaRetorno,HoraRetorno,Patente,Logistica.Estado,Logistica.Recorrido,Logistica.NombreChofer,Logistica.NombreChofer2,
    Vehiculos.Marca,Vehiculos.Modelo,Recorridos.Nombre FROM Logistica 
    INNER JOIN Vehiculos on Logistica.Patente = Vehiculos.Dominio 
    INNER JOIN Recorridos on Logistica.Recorrido=Recorridos.Numero
    WHERE Logistica.Recorrido='$_POST[Recorrido]' AND Logistica.Fecha>='$FechaI' AND Logistica.Fecha<='$FechaF' 
    AND Logistica.Fecha<>'0000-00-00' AND Logistica.Eliminado=0 ORDER BY Fecha";      

}

  $Resultado=$mysqli->query($sql);
  $rows=array();   
  
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  
    $rows[]=$row;
  }

  echo json_encode(array('data'=>$rows));

}

if(isset($_POST['Control_Recorrido'])){

    $id=$_POST['idLogistica'];
    $sql="SELECT * FROM Logistica WHERE id='$id' AND Eliminado=0";
    $Resultado=$mysqli->query($sql);
    $row=$Resultado->fetch_array(MYSQLI_ASSOC);   
  
    $sql_rec="SELECT Kilometros,Nombre FROM Recorridos WHERE Numero='$row[Recorrido]' AND Activo=1";
    $Resultado_rec=$mysqli->query($sql_rec);
    $row_rec=$Resultado_rec->fetch_array(MYSQLI_ASSOC);   
        
    //SI LOS KM ESTAN EN CERO TRAIGO LA ESTIMA
    if($row['KilometrosRecorridos']==0){
        $KmRecorridos=$row_rec['Kilometros'];
        $Estima=1;
    }else{
        $KmRecorridos=$row['KilometrosRecorridos']; 
        $Estima=0; 
    }   
 //PROMEDIO DE PAQUETES
 $sql="SELECT COUNT(TransClientes.id) as total_paq,SUM(Debe)as tota_debe_paq FROM TransClientes WHERE Recorrido='$row[Recorrido]' AND Eliminado=0";
 $Resultado=$mysqli->query($sql);
 $row_promedio_paq=$Resultado->fetch_array(MYSQLI_ASSOC);      
 
 $Total_Recorrido=$row['TotalFacturado']+$row_promedio_paq['total_debe_paq'];    

 $sql="SELECT COUNT(id)as total_rec FROM Logistica WHERE Recorrido='$row[Recorrido]' AND Eliminado=0";
 $Resultado=$mysqli->query($sql);
 $row_promedio_rec=$Resultado->fetch_array(MYSQLI_ASSOC);      
 
 $sql="SELECT COUNT(id)as Total FROM TransClientes WHERE Eliminado=0 AND NumerodeOrden='$row[NumerodeOrden]'";
 $Resultado=$mysqli->query($sql);
 $rows=$Resultado->fetch_array(MYSQLI_ASSOC);      
 $a=(($rows['Total']-($row_promedio_paq['total_paq']/$row_promedio_rec['total_rec']))/($row_promedio_paq['total_paq']/$row_promedio_rec['total_rec']))*100;

 $promedio=number_format($a, 2, '.', ""); 

  //PROMEDIO DE KM

  $sql="SELECT SUM(KilometrosRecorridos)/COUNT(id)as Total_km FROM Logistica WHERE  Recorrido='$row[Recorrido]' AND Eliminado=0 AND Fecha BETWEEN date_sub(now(), interval 2 month) AND NOW()";
  $Resultado=$mysqli->query($sql);
  $row_promedio_km=$Resultado->fetch_array(MYSQLI_ASSOC);      
    
  $a=(($KmRecorridos-$row_promedio_km['Total_km'])/$row_promedio_km['Total_km'])*100;
 
  $promedio_km=number_format($a, 2, '.', ""); 
 
  $Total_value_paq=$Total_Recorrido/$rows['Total'];
  $Total_value_km=$Total_Recorrido/$KmRecorridos;

  echo json_encode(array('data'=>$rows['Total'],'km'=>$KmRecorridos,'estima'=>$Estima,'prom_km'=>$promedio_km,'price'=>$Total_Recorrido,
                         'Rec'=>$row['Recorrido'],'RecName'=>$row_rec['Nombre'],'No'=>$row['NumerodeOrden'],'Total_paq'=>$promedio,'total_value_paq'=>$Total_value_paq,'total_value_km'=>$Total_value_km));

}