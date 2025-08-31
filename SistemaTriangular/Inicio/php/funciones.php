<?php
// session_start();
include_once "../../Conexion/Conexioni.php";
//DATOS CHOFERES
if(isset($_POST['Choferes'])){
  $Dominio= $_POST['Dominio'];
  $sql="SELECT * FROM Logistica WHERE Patente='$Dominio' AND Eliminado=0 AND Estado='Cargada'";  
  $Resultado=$mysqli->query($sql);
  $row = $Resultado->fetch_array(MYSQLI_ASSOC);
  echo json_encode(array('success'=>1,'NombreChofer'=>$row['NombreChofer']));
}

//IMPRIMIR TODOS LOS REMITOS
if(isset($_POST['RemitosRec'])){
  $rec=$_POST[rec];
  $sql="SELECT * FROM TransClientes WHERE Eliminado=0 AND Entregado=0 AND Recorrido='$_POST[rec]' AND CodigoSeguimiento<>''";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
	header('Location:SistemaTriangular/Ventas/Informes/RemitopdfAut.php?CS="'.$fila[CodigoSeguimiento].'"');
  }
  $contar=$Resultado->num_rows;
  echo json_encode(array('data'=>$rows,$contar));
}

//PAQUETES PENDIENTES DE ENTREGA
if(isset($_POST['Entregas'])){
  $sql=$mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND FechaEntrega=CURDATE() AND Debe>0");	
  $row=$sql->fetch_array(MYSQLI_ASSOC);
  $sqlMes=$mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe>0 
  AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= MONTH(CURRENT_DATE())");	
  $rowMes=$sqlMes->fetch_array(MYSQLI_ASSOC);
  $sqlMesant=$mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe>0 
  AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= (MONTH(CURRENT_DATE())-1)");	
  $rowMesant=$sqlMesant->fetch_array(MYSQLI_ASSOC);
  if(isset($rowMesant['id'])){
    $Porcentaje=0;
  }else{
    $Porcentaje=number_format((($rowMesant['id']-$rowMes['id'])/$rowMesant['id'])*100,2,'.',',');  
  }
  
  if($rowMesant['id']>$rowMes['id']){
  $tendencia='2';  
  }else if($rowMesant['id']==$rowMes['id']){
  $tendencia='0';    
  }else if($rowMesant['id']<$rowMes['id']){
  $tendencia='1';      
  }
  
//   //RECORRIDOS
  $sqlR=$mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND FechaEntrega=CURDATE() AND Debe=0");	
  $rowR=$sqlR->fetch_array(MYSQLI_ASSOC);
  $sqlMesR=$mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe=0 
  AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= MONTH(CURRENT_DATE())");	
  $rowMesR=$sqlMesR->fetch_array(MYSQLI_ASSOC);
  $sqlMesantR=$mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe=0 
  AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= (MONTH(CURRENT_DATE())-1)");	
  $rowMesantR=$sqlMesantR->fetch_array(MYSQLI_ASSOC);
  
  if(isset($rowMesant['id'])){
    $PorcentajeR=0;
  }else{
    $PorcentajeR=number_format((($rowMesant['id']-$rowMes['id'])/$rowMesant['id'])*100,2,'.',',');
  }  
  
  if($rowMesant['id']>$rowMes['id']){
  $tendenciaR='2';  
  }else if($rowMesant['id']==$rowMes['id']){
  $tendenciaR='0';    
  }else if($rowMesant['id']<$rowMes['id']){
  $tendenciaR='1';      
  }


  echo json_encode(array('success'=> 1,'Total'=>$row['id'],'TotalMes'=>$rowMes['id'],'TotalMesant'=>$rowMesant['id'],'Porcentaje'=>$Porcentaje,'Tendencia'=>$tendencia,
                        'Totalr'=>$rowR['id'],'TotalMesr'=>$rowMesR['id'],'TotalMesantr'=>$rowMesantR['id'],'Porcentajer'=>$PorcentajeR,'Tendenciar'=>$tendenciaR));

}

if(isset($_POST['Clientes'])){
  $sql=$mysqli->query("SELECT count(distinct RazonSocial)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND FechaEntrega=CURDATE() AND Debe=0");	
  $row=$sql->fetch_array(MYSQLI_ASSOC);
  $sqlMes=$mysqli->query("select count(distinct RazonSocial)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe>'0' 
  AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= MONTH(CURRENT_DATE())");	
  $rowMes=$sqlMes->fetch_array(MYSQLI_ASSOC);
  $sqlMesant=$mysqli->query("select count(distinct RazonSocial)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe>0 
  AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= (MONTH(CURRENT_DATE())-1)");	
  $rowMesant=$sqlMesant->fetch_array(MYSQLI_ASSOC);
  
  if(isset($rowMesant['id'])){
    $Porcentaje=number_format((($rowMesant['id']-$rowMes['id'])/$rowMesant['id'])*100,2,'.',',');  
  }else{
    $Porcentaje=0;
  }
  
  if($rowMesant['id']>$rowMes['id']){
  $tendencia='2';  
  }else if($rowMesant['id']==$rowMes['id']){
  $tendencia='0';    
  }else if($rowMesant['id']<$rowMes['id']){
  $tendencia='1';      
  }

  if($row['id'] <>0){
  echo json_encode(array('success'=> 1,'Total'=>$row['id'],'TotalMes'=>$rowMes['id'],'TotalMesant'=>$rowMesant['id'],'Porcentaje'=>$Porcentaje,'Tendencia'=>$tendencia));
  }
}

//KILOMETROS
if(isset($_POST['Kilometros'])){
  $sql=$mysqli->query("SELECT SUM(KilometrosRecorridos)as id FROM Logistica WHERE Eliminado='0' AND Fecha=CURDATE()");	
  $row=$sql->fetch_array(MYSQLI_ASSOC);
  $sqlMes=$mysqli->query("SELECT SUM(KilometrosRecorridos)as id FROM Logistica WHERE Eliminado='0' 
  AND YEAR(Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Fecha)= MONTH(CURRENT_DATE())");	
  $rowMes=$sqlMes->fetch_array(MYSQLI_ASSOC);
  $sqlMesant=$mysqli->query("SELECT SUM(KilometrosRecorridos)as id FROM Logistica WHERE Eliminado='0' 
  AND YEAR(Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Fecha)= (MONTH(CURRENT_DATE())-1)");	
  $rowMesant=$sqlMesant->fetch_array(MYSQLI_ASSOC);
  
  if(isset($rowMesant['id'])){
    $Porcentaje=number_format((($rowMesant['id']-$rowMes['id'])/$rowMesant['id'])*100,2,'.',',');  
  }else{
    $Porcentaje=0;
  }
  
  if($rowMesant['id']>$rowMes['id']){
  $tendencia='2';  
  }else if($rowMesant['id']==$rowMes['id']){
  $tendencia='0';    
  }else if($rowMesant['id']<$rowMes['id']){
  $tendencia='1';      
  }
  if($row[id] <>0){
  echo json_encode(array('success'=> 1,'Total'=>$row['id'],'TotalMes'=>$rowMes['id'],'TotalMesant'=>$rowMesant['id'],'Porcentaje'=>$Porcentaje,'Tendencia'=>$tendencia));
  }
}

?>