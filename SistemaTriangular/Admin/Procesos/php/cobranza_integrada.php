<?php
// Content-Type explicito + isset() en cada rama: sin esto, un simple Notice/
// Warning de PHP (ej. "Undefined array key" de una accion que no vino en el
// POST) se imprime ANTES del json_encode y rompe el JSON.parse() del
// navegador - con display_errors apagado (Apache de produccion) no se nota,
// pero con el server de pruebas local (php -S, display_errors On) la grilla
// queda vacia sin ningun error visible en Network, solo en Console.
header('Content-Type: application/json; charset=utf-8');
// Conexioni.php ya hace su propio session_start() - llamarlo tambien aca
// generaba un Notice "session already active" en TODAS las acciones de este
// archivo, que se sumaba a la corrupcion del JSON de salida.
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

if(isset($_POST['Change_import']) && $_POST['Change_import']==1){

    $id=$_POST['id'];
    $Importe=$_POST['Importe'];
    
    $porc=($Importe*6)/100;
    
    $Neto=($porc/1.21);
    $Iva=$porc-$Neto;
    $Info='M '.$_SESSION['Usuario'].' '.date('d-m-Y H:i'); 
    $Obs=' Modificado x '.$_SESSION['Usuario'].' '.date('d/m/Y H:i').' a $ '.$Importe;
    
    $sql_number="UPDATE Ventas SET Comentario = CONCAT(Comentario, '$Obs'), CobrarEnvio='$Importe',Precio='$porc',Total='$porc',ImporteNeto='$Neto',Iva3='$Iva',InfoABM='$Info' where idPedido='$id'";
    

    if($sql_dato=$mysqli->query($sql_number)){

     //BUSCO EL TOTAL DE VENTAS SEGUN EL CODIGO DE SEGUIMIENTO
     $sql=$mysqli->query("SELECT SUM(Total)as Total,NumPedido FROM Ventas WHERE idPedido='$id'");
     $dato=$sql->fetch_array(MYSQLI_ASSOC);
        
        if($dato['NumPedido']<>''){
        
        //ACTUALIZO TRANSCLIENTES
        $mysqli->query("UPDATE TransClientes SET Debe='$dato[Total]' WHERE CodigoSeguimiento='$dato[NumPedido]' LIMIT 1");
        
        //SELECCIONO EL ID DE TRANSCLIENTES PARA ACTUALIZAR CTASCTES
        $sql=$mysqli->query("SELECT id FROM TransClientes WHERE CodigoSeguimiento='$dato[NumPedido]'");        
        $sql_result=$sql->fetch_array(MYSQLI_ASSOC);
        $sql_id=$sql_result['id'];
        
        if($sql_id<>''){
        $mysqli->query("UPDATE `Ctasctes` SET Debe='$dato[Total]' WHERE idTransClientes='$sql_id' LIMIT 1");
        }
        echo json_encode(array('success'=>1,'Num'=>$sql_id)); 
        
        }else{
        
            echo json_encode(array('success'=>0));
        
        }
    

    }else{

        echo json_encode(array('success'=>0,'porc'=>$porc,'Neto'=>$Neto,'Iva'=>$Iva));
    }

}

if(isset($_POST['Totales']) && $_POST['Totales']==1){
    $id=(int)$_POST['id'];
    // FIX: CobrarEnvio viene repetido en cada fila/servicio de una misma
    // rendicion (ver comentario en Cobranza_Integrada mas abajo) - ahora que
    // TODAS esas filas comparten surrender_number, un SUM(CobrarEnvio) llano
    // multiplicaria el monto real de cobranza por la cantidad de servicios
    // de la rendicion. Se toma UN valor por NumPedido (MAX, mismo criterio
    // que Ventas/Procesos/php/funciones.php:47) y se suman esos.
    $sql_number="SELECT surrender_time,surrender_name,idCliente,Cliente,FechaPedido,SUM(Total)as Total,
    (SELECT COALESCE(SUM(x.MaxCobrar),0) FROM (
        SELECT MAX(CobrarEnvio) AS MaxCobrar
        FROM Ventas
        WHERE surrender_number='$id' AND Eliminado=0
        GROUP BY NumPedido
    ) x) as Cobranza
    FROM Ventas INNER JOIN TransClientes ON TransClientes.CodigoSeguimiento=Ventas.NumPedido WHERE surrender_number='$id'
    AND Ventas.Eliminado=0 AND TransClientes.Eliminado=0";
    $sql_dato=$mysqli->query($sql_number);
    $ResultadoTotales=$sql_dato->fetch_array(MYSQLI_ASSOC);
    $Total=$ResultadoTotales['Cobranza']-$ResultadoTotales['Total'];

    $sql=$mysqli->query("SELECT Direccion,Telefono,Celular,Mail FROM Clientes WHERE id='$ResultadoTotales[idCliente]'");
    $DatosCliente=$sql->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('retenido'=>$ResultadoTotales['Total'],'cobrado'=>$ResultadoTotales['Cobranza'],'total'=>$Total,
    'fecha'=>$ResultadoTotales['FechaPedido'],'cliente'=>$ResultadoTotales['Cliente'],'direccion'=>$DatosCliente['Direccion'],
    'telefono'=>$DatosCliente['Celular'],'idcliente'=>$ResultadoTotales['idCliente'],'mail'=>$DatosCliente['Mail'],
    'name'=>$ResultadoTotales['surrender_name'],'time'=>$ResultadoTotales['surrender_time']));    
}

if(isset($_POST['VerFechas']) && $_POST['VerFechas']==1){
  // Claves de array sin comillas ($_POST[Recorrido], $_SESSION[RecorridoMapa])
  // - en PHP 8 eso ya no es un Warning, es un Error fatal (constante
  // indefinida): esta rama fallaba SIEMPRE, en cualquier entorno, cada vez
  // que se cambiaba el rango de fechas en esta pantalla.
  $_SESSION['RecorridoMapa'] = $_POST['Recorrido'] ?? null;
  $Fecha=explode(' - ',$_POST['Fechas'],2);

  $FechaInicio=explode('/',$Fecha[0],3);
  $FechaI=$FechaInicio[2].'-'.$FechaInicio[0].'-'.$FechaInicio[1];
  
  $FechaFinal=explode('/',$Fecha[1],3);
  $FechaF=$FechaFinal[2].'-'.$FechaFinal[0].'-'.$FechaFinal[1];

  echo json_encode(array('Inicio'=>$FechaI,'Final'=>$FechaF));
}

  if(isset($_POST['Pendientes']) && $_POST['Pendientes']==1){
  // Se agrega el join con TransClientes para poder mostrar en la grilla el
  // destinatario, el codigo de proveedor interno del cliente, el recorrido
  // y el estado del paquete (Entregado/Devuelto) - antes solo se veia el
  // nombre del cliente (empresa), sin forma de identificar el envio puntual.
  //
  // FIX (recuperado de Caddy_produccion, a pedido): esta pantalla no tenia
  // filtro por Recorrido ni "Solo Pendientes de Rendicion" - siempre traia
  // TODO lo del rango de fechas, sin forma de acotar la busqueda. Mismo
  // criterio que ya usaba Caddy_produccion: Recorrido opcional (numerico),
  // SoloPendientes=1 filtra por surrender_number=0 (el default de esa
  // columna - ver Ventas.surrender_number).
  $recorrido = $_POST['Recorrido'] ?? '';
  $soloPendientes = ($_POST['SoloPendientes'] ?? '0') == '1';

  $sql="SELECT v.*, tc.ClienteDestino, tc.CodigoProveedor, tc.Entregado, tc.Devuelto, tc.Recorrido
  FROM `Ventas` AS v
  INNER JOIN TransClientes AS tc ON v.NumPedido = tc.CodigoSeguimiento
  WHERE v.FechaPedido>='$_POST[Inicio]' AND v.FechaPedido<='$_POST[Final]' AND v.Eliminado=0 AND v.CobrarEnvio<>0 AND tc.Eliminado=0";

  if (is_numeric($recorrido)) {
      $sql .= " AND tc.Recorrido = '" . $mysqli->real_escape_string($recorrido) . "'";
  }
  if ($soloPendientes) {
      $sql .= " AND v.surrender_number = 0";
  }

  $Resultado=$mysqli->query($sql);
  $rows=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  // $FechaI/$FechaF eran de la rama VerFechas - copiados aca por error, no
  // se calculan en esta rama y siempre daban "Undefined variable".
  echo json_encode(array('data'=>$rows));
}

if(isset($_POST['Cobranza_Integrada']) && $_POST['Cobranza_Integrada']==1){
    
$nombre=$_POST['nombre'];
$dni=$_POST['dni'];
$obs=$_POST['obs'];
$fecha=$_POST['fecha'];
$hora=$_POST['hora'];
$time=$fecha.' '.$hora;
$name=$nombre.' Dni.: '.$dni;
$box=$_POST['id'];
$rows=array();

$sql_number="SELECT MAX(surrender_number)as Numero FROM Ventas WHERE Eliminado=0";
$sql_dato=$mysqli->query($sql_number);
$Resultado_number=$sql_dato->fetch_array(MYSQLI_ASSOC);
$Numero=$Resultado_number['Numero']+1;

      // FIX (Asana: "la cobranza integrada toma un solo servicio de la
      // rendicion"): una rendicion con varios servicios queda repartida en
      // varias FILAS de Ventas, una por servicio, todas con el mismo
      // NumPedido (y el mismo CobrarEnvio repetido - ver el mismo criterio
      // documentado en Ventas/Procesos/php/funciones.php:43-47). En la
      // grilla el operador tilda UNA sola fila por rendicion (tildar mas de
      // una duplicaria el monto en el total del modal, ya que CobrarEnvio
      // viene repetido por fila). Antes esto marcaba surrender_number SOLO
      // en la fila puntual tildada -> la liquidacion terminaba con un solo
      // servicio de toda la rendicion. Ahora, por cada fila tildada, se
      // marcan TODAS las filas de Ventas que comparten su mismo NumPedido.
      for($i=0;$i<count($box);$i++){
        $idPedidoEsc = (int)$box[$i];
        $sqlNumPedido = $mysqli->query("SELECT NumPedido FROM Ventas WHERE idPedido='$idPedidoEsc' AND Eliminado=0 LIMIT 1");
        $filaNumPedido = $sqlNumPedido ? $sqlNumPedido->fetch_assoc() : null;
        $numPedido = $filaNumPedido['NumPedido'] ?? '';

        if ($numPedido !== '') {
            $numPedidoEsc = $mysqli->real_escape_string($numPedido);
            $mysqli->query("UPDATE Ventas SET surrender_name='$name',surrender_time='$time',surrender_observations='$obs',surrender_number='$Numero' WHERE NumPedido='$numPedidoEsc' AND Eliminado='0'");
        } else {
            // Fallback defensivo: si no se encontro el NumPedido (dato
            // corrupto/fila ya eliminada), al menos se marca la fila
            // puntual tildada, como hacia antes.
            $mysqli->query("UPDATE Ventas SET surrender_name='$name',surrender_time='$time',surrender_observations='$obs',surrender_number='$Numero' WHERE idPedido='$idPedidoEsc' AND Eliminado='0'");
        }
        $rows[]=$box[$i];
      }
      echo json_encode(array('data'=>$rows,'surrender_number'=>$Numero));
}

if(isset($_POST['Actualiza']) && $_POST['Actualiza']==1){
// $Entregado=$_POST[entregado];  
// $Observaciones='Carga Manual: '.$_POST[Observaciones];
// if($_POST[Fecha]==''){
// $Fecha= date("Y-m-d");	  
// }else{
// $Fecha= date("Y-m-d", strtotime($_POST[Fecha]));  
// }
// if($_POST[Hora]==''){
// $Hora=date("H:i");   
// }else{
// $Hora=date('H:i',strtotime($_POST[Hora]));  
// }  
  
// $sql=$mysqli->query("SELECT CodigoSeguimiento,id,idClienteDestino,ClienteDestino FROM TransClientes WHERE id='$_POST[id]'");
// $sqldato=$sql->fetch_array(MYSQLI_ASSOC);  
// $sql=$mysqli->query("UPDATE `TransClientes` SET Retirado='1',Entregado='$Entregado' WHERE id='$_POST[id]'");    
  
// $sqlseguimiento=$mysqli->query("INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`, `Observaciones`, `Entregado`, `Estado`,
//                               `idCliente`, `Retirado`,`idTransClientes`,`Destino`)VALUES('{$Fecha}','{$Hora}','{$_SESSION[Usuario]}',
//                               '{$_SESSION[Sucursal]}','{$sqldato[CodigoSeguimiento]}','{$Observaciones}','{$Entregado}','Entregado al Cliente',
//                               '{$sqldato[idClienteDestino]}','1','{$sqldato[id]}','{$sqldato[ClienteDestino]}')");
  
// $sql=$mysqli->query("UPDATE `HojaDeRuta` SET Estado='Cerrado' WHERE Seguimiento='$sqldato[CodigoSeguimiento]'");
// $sql=$mysqli->query("UPDATE `TransClientes` SET Estado='Entregado al Cliente' WHERE CodigoSeguimiento='$sqldato[CodigoSeguimiento]'");
  
// echo json_encode(array('success'=>1));
}

if(isset($_POST['EliminarRegistro']) && $_POST['EliminarRegistro']==1){
//   //ACTURALIZO HOJA DE RUTA
//   if($sql=$mysqli->query("UPDATE `HojaDeRuta` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE Seguimiento='$_POST[CodigoSeguimiento]'")){
//   $hojaderuta=1;  
//   }else{
//   $hojaderuta=0;    
//   }
//   //ACTUALIZO TRANS CLIENTES
//   if($sql=$mysqli->query("UPDATE `TransClientes` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE id='$_POST[id]'")){
//   $transclientes=1;    
//   }else{
//   $transclientes=0;  
//   }
//   //BUSCO ID TRANSCLIENTES
//   $sql=$mysqli->query("SELECT id FROM TransClientes WHERE id='$_POST[id]'");
//   $datoid=$sql->fetch_array(MYSQLI_ASSOC);
//   $sqlventas=$mysqli->query("UPDATE Ventas SET Eliminado='1' WHERE NumPedido='$_POST[CodigoSeguimiento]'");
//   $sqlCtasCtes=$mysqli->query("UPDATE Ctasctes SET Debe='$Saldo' WHERE idTransClientes='$datoid[id]'");

//   echo json_encode(array('success'=>1,'hojaderuta'=>$hojaderuta,'transclientes'=>$transclientes));
}

//SELECT RECORRIDOS
// if($_POST[BuscarRecorridos]==1){
//   $BuscarVenta=$mysqli->query("SELECT Numero,Nombre FROM Recorridos");
//   if($_POST[cs]<>''){
//     $BuscarRecorrido=$mysqli->query("SELECT Recorrido FROM TransClientes WHERE CodigoSeguimiento='$_POST[cs]'");  
//     $Recorrido=$BuscarRecorrido->fetch_array(MYSQLI_ASSOC);
//     $Rec_label='Recorrido '.$Recorrido[Recorrido];
//     $Rec=$Recorrido[Recorrido];  
//   }else{
//     $Rec=$Recorrido[Recorrido];
//     $Rec_label="Seleccionar Recorrido";  
//   }
//     echo '<option value='.$Rec.'>'.$Rec_label.'</option>';
//     while (($fila = $BuscarVenta->fetch_array(MYSQLI_ASSOC))!= NULL) {
//     echo '<option value="'.$fila["Numero"].'">'.$fila["Numero"].' | '.$fila["Nombre"].'</option>';
//   }
//   // Liberar resultados
//   mysql_free_result($BuscarVenta);
// }
//HASTA ACA SELET RECORRIDOS
//SELECT RECORRIDOS
// if($_POST[ActualizaRecorrido]==1){
//   if($_POST[cs]<>''){
//     $sql=$mysqli->query("SELECT NumerodeOrden FROM `Logistica` WHERE Recorrido='$_POST[r]' AND Eliminado=0 AND Estado ='Cargada'");
//     $NOrden=$sql->fetch_array(MYSQLI_ASSOC);

//     if(($sql->num_rows) == 0) {
//     $NO=0;  
//     }else{
//     $NO=$NOrden[NumerodeOrden];    
//     }
    
//     $ActualizarTransClientes=$mysqli->query("UPDATE TransClientes SET Recorrido='$_POST[r]',NumerodeOrden='$NO' WHERE CodigoSeguimiento='$_POST[cs]'");
//     $ActualizarHojaDeRuta=$mysqli->query("UPDATE HojaDeRuta SET Recorrido='$_POST[r]',NumerodeOrden='$NO' WHERE Seguimiento='$_POST[cs]'");
//   echo json_encode(array('success'=>1,'Recorrido'=>$_POST[r],'CodigoSeguimiento'=>$_POST[cs]));  
//   }else{
//   echo json_encode(array('success'=>0));
//   }
  
// }
//HASTA ACA SELET RECORRIDOS
if(isset($_POST['Invoice']) && $_POST['Invoice']==1){
    $Number=$_POST['Number'];
    $sql="SELECT Ventas.*,TransClientes.ClienteDestino FROM `Ventas` INNER JOIN TransClientes ON Ventas.NumPedido=TransClientes.CodigoSeguimiento 
    WHERE surrender_number='$Number' AND Ventas.Eliminado=0 AND Ventas.CobrarEnvio<>0 AND TransClientes.Eliminado=0";
    $Resultado=$mysqli->query($sql);
    $rows=array();   
    while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
    }
    echo json_encode(array('data'=>$rows));
  }



?>