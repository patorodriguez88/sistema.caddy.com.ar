<?php
session_start();
include_once "../../ConexionSmartphone.php";
//Seteamos el header de "content-type" como "JSON" para que jQuery lo reconozca como tal
$id=$_POST[id];
$sqlhdr=mysql_query("SELECT * FROM HojaDeRuta WHERE id='$id'");
$datohdr=mysql_fetch_array($sqlhdr);

// $nombre=$datohdr[Cliente];
$posicion=$datohdr[Posicion];
// $domicilio=utf8_decode($datohdr[Localizacion]);
$sql=mysql_query("SELECT Retirado,RazonSocial,ClienteDestino,DomicilioOrigen,DomicilioDestino,FormaDePago,Debe,CobrarEnvio,EnvioCobrado,CobrarCaddy FROM `TransClientes` WHERE CodigoSeguimiento='$datohdr[Seguimiento]'");
$row=mysql_fetch_array($sql);

//Cobrar Envio siempre esta en cero solo lo marco cuando esta realizada la gestion por parte del repartidor ya sea OK O CNL
$CobrarEnvio=$row[CobrarEnvio];
$FormaDePago=$row[FormaDePago];
$EnvioCobrado=$row[EnvioCobrado];

if($row[Retirado]==1){
  $nombre=$row[ClienteDestino];  
  $domicilio=utf8_encode($row[DomicilioDestino]);
    if($FormaDePago=='Destino'){
        if($row[EnvioCobrado]==0){
          if($row[CobrarEnvio]==1){
          $importecobro=$datohdr[ImporteCobranza];  
          }else{
          $importecobro=0;  
          }
          if($row[CobrarCaddy]==1){
          $importecobrocaddy=$row[Debe];  
          }else{
          $importecobrocaddy=0; 
          }
        }else{
        $importecobro=0;  
        $importecobrocaddy=0; 
        }
    }else{
    $importecobro=0;  
    $importecobrocaddy=0;   
    } 
}else{
  $nombre=$row[RazonSocial];    
  $domicilio=utf8_encode($row[DomicilioOrigen]);
  if($FormaDePago=='Origen'){
        if($row[EnvioCobrado]==0){
          if($row[CobrarEnvio]==1){
          $importecobro=$datohdr[ImporteCobranza];  
          }else{
          $importecobro=0;  
          }
          if($row[CobrarCaddy]==1){
          $importecobrocaddy=$row[Debe];  
          }else{
          $importecobrocaddy=0; 
          }
        }else{
        $importecobro=0;  
        $importecobrocaddy=0; 
        }
    }else{
    $importecobro=0;  
    $importecobrocaddy=0;   
    } 
  }  

$Retirado=$row[Retirado];
$Seguimiento=$datohdr[Seguimiento];
echo json_encode(array('success'=> 1,
                       'Nombre'=> $nombre,
                       'Posicion'=>$posicion,
                       'Domicilio'=>$domicilio,
                       'Retirado'=>$Retirado,
                       'Seguimiento'=>$Seguimiento,
                       'ImporteCobro'=>$importecobro,
                       'FormaDePago'=>$FormaDePago,
                       'Importecobrocaddy'=>$importecobrocaddy,
                       'CobrarEnvio'=>$CobrarEnvio,
                       'EnvioCobrado'=>$EnvioCobrado));