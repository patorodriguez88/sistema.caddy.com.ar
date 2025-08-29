<?php
// session_start();
include_once "../../Conexion/Conexioni.php";

if(isset($_POST['Empleados'])){
  $sql="SELECT NombreCompleto,sum(KilometrosRecorridos)as Km,COUNT(Logistica.id)as Salidas FROM Empleados 
  INNER JOIN Logistica ON Empleados.NombreCompleto=Logistica.NombreChofer
  WHERE Empleados.Inactivo=0 AND YEAR(Logistica.Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Logistica.Fecha)= MONTH(CURRENT_DATE()) 
  AND Logistica.Eliminado=0 AND Aliados=0 GROUP BY NombreCompleto";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  $rows1=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

if(isset($_POST['Empleados2'])){

  $sql="SELECT Logistica.NombreChofer as Chofer, COUNT(HojaDeRuta.id)as Total FROM `HojaDeRuta` 
  INNER JOIN `Logistica` ON  HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden
  INNER JOIN `Empleados` ON Empleados.NombreCompleto=Logistica.NombreChofer
  WHERE YEAR(Logistica.Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Logistica.Fecha)= MONTH(CURRENT_DATE()) 
  AND Logistica.Eliminado=0 AND Aliados=0 GROUP BY Logistica.NombreChofer";

  $Resultado=$mysqli->query($sql);
  $rows=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

//DESEMPENIO PRODUCTIVIDAD

if(isset($_POST['Desempenio_productividad'])){
    
    $sql="SELECT concat(usuarios.Nombre,' ',usuarios.Apellido)as Chofer,idABM,COUNT(TransClientes.id)AS Total FROM TransClientes 
    LEFT JOIN usuarios ON usuarios.id=idABM
    WHERE Entregado=1 AND TransClientes.Eliminado=0 AND YEAR(FechaEntrega) =YEAR(CURRENT_DATE()) and MONTH(FechaEntrega)=MONTH(CURRENT_DATE()) GROUP BY idABM";

    $Resultado=$mysqli->query($sql);
    $rows=array();
  
    while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
    }
    echo json_encode(array('data'=>$rows));
}

if(isset($_POST['Servicios'])){
  $sql="SELECT Logistica.NumerodeOrden,Logistica.NombreChofer as Chofer,Logistica.Patente as Dominio,Logistica.Recorrido, COUNT(HojaDeRuta.id)as Total,
SUM(if(HojaDeRuta.Estado='Cerrado', 1, 0)) AS Cerradas
FROM `HojaDeRuta` INNER JOIN `Logistica` ON  HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden 
WHERE Logistica.Fecha=CURRENT_DATE() AND 
Logistica.Eliminado=0 AND 
HojaDeRuta.Eliminado=0 
GROUP BY Logistica.NumerodeOrden";
  
//   $sql="SELECT Logistica.NombreChofer as Chofer,Logistica.Patente as Dominio,Logistica.Recorrido, COUNT(HojaDeRuta.id)as Total FROM `HojaDeRuta` 
//   INNER JOIN `Logistica` ON  HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden 
//   WHERE Logistica.Fecha=CURRENT_DATE() AND Logistica.Eliminado=0 AND HojaDeRuta.Eliminado=0 GROUP BY Logistica.NumerodeOrden";
  
  $Resultado=$mysqli->query($sql);
  $rows=array();

  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}


if(isset($_POST['Flota'])){
  $sql="SELECT concat_ws(' ', Marca, Modelo) as Marca,Dominio,Ano,Kilometros,Activo,Estado FROM Vehiculos WHERE VehiculoOperativo=1";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
  }

if(isset($_POST['totales'])){
  $NombreCliente=$_SESSION['NombreClienteA'];
  $sql="SELECT SUM(ImporteNeto)as Neto,SUM(Total)as Total,SUM(Iva3)as Iva FROM Ventas WHERE NumeroRepo='$NumeroRepo' AND idCliente='$idCliente' AND terminado='0' AND FechaPedido=curdate() AND Eliminado=0";
  $ResultadoTesoreria=$mysqli->query($sql);
  $rows=array();
  while($row = $ResultadoTesoreria->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
    
  }
  echo json_encode(array('data'=>$rows));
}

if(isset($_POST['Pendientes'])){
  $sql0="SELECT Seguimiento FROM HojaDeRuta WHERE NumerodeOrden='$_POST[Orden]' AND Eliminado=0 ORDER BY Posicion";
  $Resultado0=$mysqli->query($sql0);
  $datos=array();
  while($dato=$Resultado0->fetch_array(MYSQLI_ASSOC)){
  $datos[]=join($dato);
  }
  
  $exito= json_encode($datos);
 
  $exito = trim($exito,'[]');
  $sql="SELECT * FROM TransClientes WHERE CodigoSeguimiento IN ($exito) AND Eliminado=0";
  $Resultado=$mysqli->query($sql);
  $rows=array();   
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));

}



?>