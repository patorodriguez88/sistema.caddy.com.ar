<?php
session_start();

include_once "../Conexion/Conexioni.php";
require_once('../Funciones/sure.php');


    //DESDE ACA SEGURO
$ValorDeclarado='130000';
    //BUSCO LOS DATOS DE SEGURO ACORDADOS CON EL CLIENTE
    $response_sure=sure_min('30120',$ValorDeclarado);
    echo $response_sure['Seguro_min'].'</br>';
    echo $response_sure['Seguro_calculado'];
    //CARGO EL SEGURO MINIMO
    if($response_sure['Seguro_min']>0){

        $Datos_productos=$mysqli->query("SELECT * FROM Productos WHERE Codigo='0000000164'");
        $row_productos = $Datos_productos->fetch_array(MYSQLI_ASSOC);
        echo $row_productos['Titulo'];

        $Fecha_sure=date('Y-m-d');
        $Titulo_sure=$row_productos['Titulo'];
        $Codigo_sure='0000000164';
        $Total_sure=($response_sure['Seguro_min']/100);
        $iva3_sure=$Total_sure-($Total_sure/1.21);
        $ImporteNeto_sure=$Total_sure-$iva3;
        $Precio_sure=$Total_sure;
        $Cantidad_sure=0;
        
        $Comentario_sure='SEGURO CLIENTE MIN($ '.$response_sure['Seguro_min'].' )';
        $Cliente_sure='Ansilta Cordoba';
        $sql="INSERT INTO Ventas(Codigo,fechaPedido,Titulo,Precio,Cantidad,Total,Cliente,NumeroRepo,
          ImporteNeto,Iva3,NumPedido,Usuario,Comentario,idCliente)
         VALUES('{$Codigo_sure}','{$Fecha_sure}','{$Titulo_sure}','{$Precio_sure}','{$Cantidad_sure}','{$Total_sure}','{$Cliente_sure}',
          '{$NumeroRepo}','{$ImporteNeto_sure}','{$iva3_sure}','{$NumeroPedido}','{$Usuario}','{$Comentario_sure}','{$ClienteOrigen}')";
      
        $mysqli->query($sql);
    
    }

    //CARGO EL SEGURO CALCULADO POR ENCIMA DEL MINIMO Y EN RELACION AL PORC.
$row='30120';
    //SOLO POR AHORA PARA ANSILTA
    // if($row=='30120'){
    if($response_sure['Seguro_calculado']>0){

    $Datos_productos=$mysqli->query("SELECT * FROM Productos WHERE Codigo='0000000164'");
    $row_productos = $Datos_productos->fetch_array(MYSQLI_ASSOC);

    $Fecha_sure=date('Y-m-d');
    $Titulo_sure=$row_productos['Titulo'];
    $Codigo_sure='0000000164';
    $Total_sure=$response_sure['Seguro_calculado'];
    $iva3_sure=$Total_sure-($Total_sure/1.21);
    $ImporteNeto_sure=$Total_sure-$iva3;
    $Precio_sure=$Total_sure;
    $Cantidad_sure=0;
    
    $Comentario_sure='SEGURO VALOR DECLARADO PERC($ '.$ValorDeclarado.' )';

    $sql_valor_declarado="INSERT INTO Ventas(Codigo,fechaPedido,Titulo,Precio,Cantidad,Total,Cliente,NumeroRepo,
     ImporteNeto,Iva3,NumPedido,Usuario,Comentario,idCliente)
     VALUES('{$Codigo_sure}','{$Fecha_sure}','{$Titulo_sure}','{$Precio_sure}','{$Cantidad_sure}','{$Total_sure}','{$Cliente_sure}',
     '{$NumeroRepo}','{$ImporteNeto_sure}','{$iva3_sure}','{$NumeroPedido}','{$Usuario}','{$Comentario_sure}','{$ClienteOrigen}')";
 
     $mysqli->query($sql_valor_declarado);

    }
