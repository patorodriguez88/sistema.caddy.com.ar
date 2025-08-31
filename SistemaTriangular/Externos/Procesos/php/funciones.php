<?php
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');

// if($_POST['Envios_ver']==1){
//     $data_array = array();
//     // SQL para obtener los datos
//     $sql = "SELECT * FROM `Empleados` 
//     WHERE Empleados.Aliados=1";
//     // Ejeuctar el SQL
//     $query = $mysqli->query($sql);
//     // Recorrer los resultados
//     while($data = $query->fetch_object()){
        
//         $sql1 = $mysqli->query("SELECT * FROM `Vehiculos` WHERE Vehiculos.id_usuario='$data->Usuario'");
//         $data1=$sql1->fetch_object();
//         // $data_array1[]=$data1;
//         // Poner los datos en un array en el orden de los campos de la tabla
//         $data_array[] = array($data->id,
//                               $data->NombreCompleto,
//                               $data1->Marca,
//                               $data1->Modelo,
//                               $data1->Dominio,
//                               $data->Dni,
//                               $data->Telefono,
//                               $data->Fecha,
//                               $data->VencimientoLicencia,
//                               $data->Observaciones,
//                               $data->Inactivo,
//                               $data->Usuario
//                             );
//     }
//     // crear un array con el array de los datos, importante que esten dentro de : data
//     $new_array  = array("data"=>$data_array);
//     // crear el JSON apartir de los arrays
//     echo json_encode($new_array);
// }

if($_POST['Envios']==1){

    if($_POST['Filtro']=='inactivo'){
      $SQL=$mysqli->query("SELECT Empleados.id,Empleados.NombreCompleto,Empleados.Dni,Empleados.Telefono,Empleados.Puesto,Empleados.FechaIngreso,Empleados.VencimientoLicencia,Empleados.Inactivo,Empleados.Observaciones, Vehiculos.Marca,Vehiculos.Modelo,Vehiculos.Dominio,Empleados.Usuario FROM `Empleados` 
      INNER JOIN Vehiculos ON Empleados.Usuario =Vehiculos.id_usuario WHERE Empleados.Aliados=1 AND Empleados.Inactivo=1 AND Vehiculos.id_usuario<>0");
        
    }else if($_POST['Filtro']=='activo'){
      $SQL=$mysqli->query("SELECT Empleados.id,Empleados.NombreCompleto,Empleados.Dni,Empleados.Telefono,Empleados.Puesto,Empleados.FechaIngreso,Empleados.VencimientoLicencia,Empleados.Inactivo,Empleados.Observaciones, Vehiculos.Marca,Vehiculos.Modelo,Vehiculos.Dominio,Empleados.Usuario FROM `Empleados` 
      INNER JOIN Vehiculos ON Empleados.Usuario =Vehiculos.id_usuario WHERE Empleados.Aliados=1 AND Empleados.Inactivo=0 AND Vehiculos.id_usuario<>0");

    }else{

      $SQL=$mysqli->query("SELECT Empleados.id,Empleados.NombreCompleto,Empleados.Dni,Empleados.Telefono,Empleados.Puesto,Empleados.FechaIngreso,Empleados.VencimientoLicencia,Empleados.Inactivo,Empleados.Observaciones, Vehiculos.Marca,Vehiculos.Modelo,Vehiculos.Dominio,Empleados.Usuario FROM `Empleados` 
      INNER JOIN Vehiculos ON Empleados.Usuario =Vehiculos.id_usuario WHERE Empleados.Aliados=1 AND Empleados.Inactivo=0 AND Vehiculos.id_usuario<>0");
    
    }

    $ROWS=array();
    
    while($DATOS_CLIENTES = $SQL->fetch_array(MYSQLI_ASSOC)){

        //ACTUALIZO LOS CLIENTES
        if($DATOS_CLIENTES['VencimientoLicencia']<date('Y-m-d')){
        
            $mysqli->query("UPDATE Empleados SET Inactivo=1 WHERE id='$DATOS_CLIENTES[id]' LIMIT 1");
        
        }else{
            
            $mysqli->query("UPDATE Empleados SET Inactivo=0 WHERE id='$DATOS_CLIENTES[id]' LIMIT 1");
        
        }
        
        $ROWS[]=$DATOS_CLIENTES;
    
    }
    
    echo json_encode(array('data'=>$ROWS));
}
//VER EXTERNO
if($_POST['VerExterno']==1){
    
    // $SQL=$mysqli->query("SELECT * FROM `Empleados` WHERE id='".$_POST['id']."'");
    $SQL=$mysqli->query("SELECT Empleados.*,usuarios.Usuario,usuarios.PASSWORD FROM `Empleados` INNER JOIN usuarios ON Empleados.Usuario=usuarios.id WHERE Empleados.id='".$_POST['id']."'");
    $ROWS=array();
    
    while($DATOS_CLIENTES = $SQL->fetch_array(MYSQLI_ASSOC)){

        $ROWS[]=$DATOS_CLIENTES;
    
    }
    
    echo json_encode(array('data'=>$ROWS));
}

//MODIFICAR EXTERNO
if($_POST['ModificarExterno']==1){
    $VencimientoLic=explode("/",$_POST['licencia'],3);
    $FechaVencimientoLicencia=$VencimientoLic[2].'-'.$VencimientoLic[0].'-'.$VencimientoLic[1];

    $Nacimiento=explode("/",$_POST['nac'],3);
    $FechaNacimiento=$Nacimiento[2].'-'.$Nacimiento[0].'-'.$Nacimiento[1];

    $Ingreso=explode("/",$_POST['ing'],3);
    $FechaIngreso=$Ingreso[2].'-'.$Ingreso[0].'-'.$Ingreso[1];

    $SQL="UPDATE `Empleados` SET `NombreCompleto`='".$_POST['nombre']."',`Domicilio`='".$_POST['domicilio']."',`Localidad`='".$_POST['city']."',
    `Provincia`='".$_POST['state']."',`CodigoPostal`='".$_POST['codigopostal']."',`Telefono`='".$_POST['telefono']."',`FechaNacimiento`='".$FechaNacimiento."',
    `FechaIngreso`='".$FechaIngreso."',`Dni`='".$_POST['dni']."',`VencimientoLicencia`='".$FechaVencimientoLicencia."',
    `Observaciones`='".$_POST['obs']."',`GrupoSanguineo`='".$_POST['gruposanguineo']."',`TelefonoEmergencia`='".$_POST['phone_emergency']."'
     WHERE id='".$_POST['id_externo']."' LIMIT 1";
     
    if($mysqli->query($SQL)){

        echo json_encode(array('success'=>1,'Fecha'=>$FechaVencimientoLicencia));         
     
    }else{

        echo json_encode(array('success'=>0));

    }

}

//AGREGAR EXTERNO
if($_POST['Agregar_externo']==1){

    $FechaHoy=date('Y-m-d');            
     $Usuario=$_POST['nombre'];

    //SQL USUARIO
    $SQL_USUARIO="INSERT INTO `usuarios`(`Nombre`, `PASSWORD`, `NIVEL`, `ACTIVO`, `Direccion`, `Localidad`, `Ciudad`,`Telefono`, `Observaciones`, `Usuario`, `FechaPassword`,`Estado`) VALUES 
    ('{$_POST['nombre']}','{$_POST['dni']}','3','1','{$_POST['domicilio']}','{$_POST['city']}','{$_POST['state']}','{$_POST['telefono']}','{$_POST['obs']}','{$Usuario}','{$FechaHoy}','Activo')";
    $mysqli->query($SQL_USUARIO);
    $id_usuario=$mysqli->insert_id;
        
    //SQL EMPLEADO
    $SQL="INSERT INTO `Empleados`(`NombreCompleto`, `Domicilio`, `Localidad`, `Provincia`, `CodigoPostal`, `Telefono`, `FechaNacimiento`, `FechaIngreso`, `Dni`, `VencimientoLicencia`, `Puesto`, `Observaciones`, `CuentaAnticipos`, `GrupoSanguineo`, `TelefonoEmergencia`,`Inactivo`, `Aliados`,`Usuario`) 
    VALUES ('{$_POST['nombre']}','{$_POST['domicilio']}','{$_POST['city']}','{$_POST['state']}','{$_POST['codigopostal']}','{$_POST['telefono']}','{$_POST['nac']}','{$_POST['ing']}','{$_POST['dni']}','{$_POST['lic']}','Transportista','{$_POST['obs']}','112500','{$_POST['gruposanguineo']}','{$_POST['phone_emergency']}','0','1','{$id_usuario}')";
    

    //SI EXISTENN LAS VARIABLES MARCA MODELO Y DOMINIO CARGO EL VEHICULO
    if(($_POST['marca'])&&($_POST['modelo'])&&($_POST['dominio'])){
    
    $SQL_VEHICULO="INSERT INTO `Vehiculos`(`Marca`, `Modelo`, `Dominio`, `FechaVencSeguro`, `Kilometros`, `Color`, `Seguro`, `NumeroPoliza`, `Motor`, `Chasis`,`Ano`, `Observaciones`, `Activo`,`ObleaITV`, `FechaVencITV`, `Estado`, `CapacidadTotalCarga`, `PesoTotalCarga`, `VehiculoOperativo`, `Aliados`,`id_usuario`) 
    VALUES ('{$_POST['marca']}','{$_POST['modelo']}','{$_POST['dominio']}','{$_POST['seguro_vencimiento']}','{$_POST['km']}','{$_POST['color']}','{$_POST['seguro']}','{$_POST['poliza']}','{$_POST['motor']}','{$_POST['chasis']}','{$_POST['ano']}','{$_POST['vehiculo_obs']}','Si','{$_POST['itv_oblea']}','{$_POST['itv_vencimiento']}','Disponible','{$_POST['volumen']}','{$_POST['peso']}','1','1','{$id_usuario}')";
    
    }else{
    
    $vehiculo=0;
    
    }

    if($mysqli->query($SQL)){
        
        $Usuario=strtok($_POST['nombre']," ")."_".$id_usuario;
        
        $mysqli->query("UPDATE usuarios SET Usuario='$Usuario' WHERE id='$id_usuario' AND PASSWORD='".$_POST['dni']."' LIMIT 1");   
        
        //SI SE CARGA EL EXTERNO CARGO EL VECHICULO
        if($mysqli->query($SQL_VEHICULO)){
            $vehiculo=1;    
            }else{
            $vehiculo=0;
            }        

        $aliado=1;
        
        }else{
        
        $aliado=0;
    }

    echo json_encode(array('success'=>$aliado,'vehiculo'=>$vehiculo,'user_id'=>$id_usuario));

}
if($_POST['Desempeno']==1){
    
    $SQL=$mysqli->query("SELECT Recorridos.Nombre,Logistica.Fecha,Logistica.Recorrido,Logistica.NumerodeOrden,COUNT(TransClientes.id)as Total FROM `Logistica` 
    INNER JOIN TransClientes ON TransClientes.NumerodeOrden=Logistica.NumerodeOrden  
    INNER JOIN Recorridos ON Recorridos.Numero=Logistica.Recorrido
    WHERE Logistica.Fecha>='".$_POST['Desde']."' AND Logistica.Fecha<='".$_POST['Hasta']."' AND Logistica.Eliminado=0 and Logistica.idUsuarioChofer='".$_POST['id']."' AND TransClientes.idABM='".$_POST['id']."' AND TransClientes.Eliminado=0
    GROUP BY NumerodeOrden");
    $ROWS=array();
    
    while($DATOS_CLIENTES = $SQL->fetch_array(MYSQLI_ASSOC)){

        //ACTUALIZO LOS CLIENTES
        if($DATOS_CLIENTES['VencimientoLicencia']<date('Y-m-d')){
        
            $mysqli->query("UPDATE Empleados SET Inactivo=1 WHERE id='$DATOS_CLIENTES[id]' LIMIT 1");
        
        }else{
            
            $mysqli->query("UPDATE Empleados SET Inactivo=0 WHERE id='$DATOS_CLIENTES[id]' LIMIT 1");
        
        }
        
        $ROWS[]=$DATOS_CLIENTES;
    
    }
    
    echo json_encode(array('data'=>$ROWS));
}

if($_POST['Reporte']==1){
    
    $SQL=$mysqli->query("SELECT idPago,ClienteDestino,TransClientes.Fecha,DomicilioDestino,LocalidadDestino,CodigoSeguimiento,Estado,idABM,infoABM 
    FROM `TransClientes`  
    where TransClientes.NumerodeOrden='".$_POST['NOrden']."' AND idABM='".$_POST['id']."' AND TransClientes.Eliminado=0 ");

    $ROWS=array();
    
    while($DATOS_CLIENTES = $SQL->fetch_array(MYSQLI_ASSOC)){

        //ACTUALIZO LOS CLIENTES
        if($DATOS_CLIENTES['VencimientoLicencia']<date('Y-m-d')){
        
            $mysqli->query("UPDATE Empleados SET Inactivo=1 WHERE id='$DATOS_CLIENTES[id]' LIMIT 1");
        
        }else{
            
            $mysqli->query("UPDATE Empleados SET Inactivo=0 WHERE id='$DATOS_CLIENTES[id]' LIMIT 1");
        
        }
        
        $ROWS[]=$DATOS_CLIENTES;
    
    }
    
    echo json_encode(array('data'=>$ROWS));
}

