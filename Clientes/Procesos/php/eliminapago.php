<?php
session_start();
include_once "../../../Conexion/Conexioni.php";

if(isset($_POST['Eliminar_pago_permisos'])){
    
    if($_SESSION['Nivel']==1){

        echo json_encode(array('success'=>1));

    }else{

        echo json_encode(array('success'=>401));

    }
}

if(isset($_POST['Eliminar_pago'])){

    if($_SESSION['Nivel']==1){
 
    $id=$_POST['idCtasctes'];
    $tabla=$mysqli->query("SELECT idTransClientes,idTesoreria FROM Ctasctes WHERE id='$id' AND Eliminado=0");
    $datos=$tabla->fetch_array(MYSQLI_ASSOC);
    $idTransClientes=$datos['idTransClientes'];
    $idTesoreria=$datos['idTesoreria'];

    if($idTransClientes==0){
        
        echo json_encode(array('success'=>0,'error'=>'Trans Clientes es Cero'));
    
        }else if($idTesoreria==0){
        
        echo json_encode(array('success'=>0,'error'=>'Tesoreria es Cero'));
        
        }else{
        
        //ELIMINO TRANS CLIENTES
        
        $QUERY_TABLA_TRANSCLIENTES="UPDATE TransClientes SET Eliminado=1 WHERE id='$idTransClientes' LIMIT 1 ";
        
        if($EXEC_TABLA_TRANSCLIENTES=$mysqli->query($QUERY_TABLA_TRANSCLIENTES)){

                //BUSCO NUMERO DE ASIENTO
                
                $QUERY_NUMERO_ASIENTO="SELECT NumeroAsiento FROM Tesoreria WHERE id='$idTesoreria' AND Eliminado=0";
                $EXEC_NUMERO_ASIENTO=$mysqli->query($QUERY_NUMERO_ASIENTO);
                $DATO_NUMERO_ASIENTO=$EXEC_NUMERO_ASIENTO->fetch_array(MYSQLI_ASSOC);
                
                //REVERSO TESORERIA

                $QUERY_TABLA_TESORERIA="SELECT * FROM Tesoreria WHERE NumeroAsiento='$DATO_NUMERO_ASIENTO[NumeroAsiento]' AND Eliminado=0";
                $EXEC_TABLA_TESORERIA=$mysqli->query($QUERY_TABLA_TESORERIA);
                
                $QUERY_MAX_ASIENTO=$mysqli->query("SELECT MAX(NumeroAsiento)as NumeroAsiento FROM Tesoreria WHERE Eliminado=0");
                $DATO_MAX_ASIENTO=$QUERY_MAX_ASIENTO->fetch_array(MYSQLI_ASSOC);
                $NUMERO_ASIENTO=$DATO_MAX_ASIENTO['NumeroAsiento']+1;

                $Fecha=date('Y-m-d');
                $ABM="Creado por ".$_SESSION['Usuario']." el ".$Fecha;

                while($row=$EXEC_TABLA_TESORERIA->fetch_array(MYSQLI_ASSOC)){
            
                    if($row['Debe']==0){
                    

                    $QUERY_UPDATE_TABLA_TESORERIA=$mysqli->query("INSERT INTO `Tesoreria`(`Fecha`, `NombreCuenta`, `Cuenta`, `Debe`, `Haber`, `Observaciones`, `Banco`, `FechaCheque`, `NumeroCheque`, `Sucursal`, `idTransProvee`, `Usuario`, `NumeroAsiento`, `FechaTrans`, `NumeroTrans`, `NoOperativo`, `FormaDePago`, `idCtasctes`, `InfoABM`) 
                    VALUES ('{$Fecha}','{$row['NombreCuenta']}','{$row['Cuenta']}','{$row['Haber']}','0','Asiento Reversado por pago eliminado ".$_SESSION['Usuario']."','{$row['Banco']}','{$row['FechaCheque']}','{$row['NumeroCheque']}','{$row['Sucursal']}','{$row['idTransProvee']}','{$_SESSION['Usuario']}','{$NUMERO_ASIENTO}','{$row['FechaTrans']}','{$row['NumeroTrans']}','{$row['NoOperativo']}','{$row['FormaDePago']}','{$id}','{$ABM}')");
                    
                    }else{
                    
                    $QUERY_UPDATE_TABLA_TESORERIA=$mysqli->query("INSERT INTO `Tesoreria`(`Fecha`, `NombreCuenta`, `Cuenta`, `Debe`, `Haber`, `Observaciones`, `Banco`, `FechaCheque`, `NumeroCheque`, `Sucursal`, `idTransProvee`, `Usuario`, `NumeroAsiento`, `FechaTrans`, `NumeroTrans`, `NoOperativo`, `FormaDePago`, `idCtasctes`, `InfoABM`) 
                    VALUES ('{$Fecha}','{$row['NombreCuenta']}','{$row['Cuenta']}','0','{$row['Debe']}','Asiento Reversado por pago eliminado ".$_SESSION['Usuario']."','{$row['Banco']}','{$row['FechaCheque']}','{$row['NumeroCheque']}','{$row['Sucursal']}','{$row['idTransProvee']}','{$_SESSION['Usuario']}','{$NUMERO_ASIENTO}','{$row['FechaTrans']}','{$row['NumeroTrans']}','{$row['NoOperativo']}','{$row['FormaDePago']}','{$id}','{$ABM}')");
                
                        
                    }

                }

                //ELIMINO CTAS CTES

                $QUERY_TABLA_CTASCTES="UPDATE Ctasctes SET Eliminado=1 WHERE id='$id' LIMIT 1 ";

                if($EXEC_TABLA_CTASCTES=$mysqli->query($QUERY_TABLA_CTASCTES)){

                    echo json_encode(array('success'=>1));

                }else{

                    echo json_encode(array('success'=>0,'msg'=>'Registro '.$id.' NO eliminado'));
                
                }
        }else{

        echo json_encode(array('success'=>0,'msg'=>'Registro '.$idTransClientes.' NO eliminado'));
    
    }

        
    }

}else{
    echo json_encode(array('success'=>401));
}

}

?>