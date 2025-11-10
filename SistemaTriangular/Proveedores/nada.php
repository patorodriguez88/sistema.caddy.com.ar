<?
session_start();
include_once "../../Conexion/Conexioni.php";
    
    // FECHAS
    $Fecha_HOY=date('Y-m-d');
    
    //DATOS
    $CuentaDebe='ACREEDORES';
    $NCuentaDebe='211400';
    $CuentaHaber='ANTICIPO A ACREEDORES';
    $NCuentaHaber='112500';

    $RazonSocial=$_POST['RazonSocial'];
    $Cuit=$_POST['Cuit'];
    $idProveedor=$_POST['idproveedor'];
    
    // $idFacturas=array();
    $idFacturas=$_POST['idFacturas'];

    $idAnticipos=$_POST['idAnticipos'];
    // $Importe=count($idFacturas);
    echo 'Facturas'.$_POST['idFacturas'];
    for($i=0;$i < count($idFacturas);$i++){
        echo 'Facturas'.$idFacturas[$i];

    //BUSCO EL IMPORTE Y EL NUMERO DE ASIENTO
    $sql=$mysqli->query("SELECT Tesoreria.NumeroAsiento FROM Tesoreria WHERE Tesoreria.idTransProvee=".$idFacturas[$i]." AND Eliminado=0 GROUP BY Tesoreria.idTransProvee");
    $row=$sql->fetch_array(MYSQLI_ASSOC);
    $NAsiento=$row['NumeroAsiento'];

    //BUSCO EL IMPORTE Y EL NUMERO DE ASIENTO
    $sql_asiento=$mysqli->query("SELECT Debe FROM TransProveedores WHERE id=".$idFacturas[$i]." AND Debe<>0 AND Eliminado=0");
    $row=$sql_asiento->fetch_array(MYSQLI_ASSOC);
    $Importe=$row['Debe'];

    // $Importe=$idFacturas[$i];
    //INSERT ASIENTO CONTABLE
    // $sql1="INSERT INTO `Tesoreria`(
    //     Fecha,NombreCuenta,Cuenta,Debe,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idTransProvee,FormaDePago) VALUES 
    //     ('{$Fecha}','{$CuentaDebe}','{$NCuentaDebe}','{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque}',
    //     '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idTransProveedores}','{$FormaDePago}')"; 
    //      $mysqli->query($sql1);
     
    //      $sql2="INSERT INTO `Tesoreria`(
    //     Fecha,
    //     NombreCuenta,
    //     Cuenta,
    //     Haber,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idTransProvee,FormaDePago) VALUES 
    //     ('{$Fecha}','{$Cuenta1}','{$FormaDePago}','{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque}',
    //     '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idTransProveedores}','{$FormaDePago}')"; 
    //       $mysqli->query($sql2);
    // $Importe=$idFacturas[$i];
    }
    echo json_encode(array('success'=>1,'Asiento'=>$NAsiento,'Importe'=>$Importe));