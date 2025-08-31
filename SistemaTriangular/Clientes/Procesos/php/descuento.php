<?php
session_start();
include('../../../Conexion/Conexioni.php');
mysqli_set_charset($mysqli,"utf8"); 
header('Content-Type: application/json');

if ($_POST['Descontar'] == 1) {


    // VERIFICAR ESTE CODIGO!!!!!!



    // if (isset($_POST['Remitos']) && isset($_POST['Descuento'])) {
    //     $OrdenN = $_POST['Remitos'];
    //     $Descuento = $_POST['Descuento'];

    //     // Inicia una transacción
    //     $mysqli->begin_transaction();

    //     try {
    //         foreach ($OrdenN as $orden) {
    //             // Verifica que OrdenN no sea null ni cero y Descuento no sea null
    //             if (!empty($orden) && $orden != 0 && !empty($Descuento)) {
    //                 // Consulta preparada para actualizar TransClientes
    //                 $stmtUpdateTransClientes = $mysqli->prepare("UPDATE TransClientes SET Debe = Debe * ((100 - ?) / 100) WHERE id = ? AND TipoDeComprobante = 'Remito' AND Eliminado = '0' LIMIT 1");
    //                 $stmtUpdateTransClientes->bind_param('ii', $Descuento, $orden);
    //                 $stmtUpdateTransClientes->execute();
    //                 $stmtUpdateTransClientes->close();

    //                 // Consulta preparada para actualizar Ctasctes
    //                 $stmtUpdateCtasCtes = $mysqli->prepare("UPDATE Ctasctes SET Debe = ? WHERE idCliente = ? AND NumeroVenta = ? AND Haber = '0' LIMIT 1");
    //                 $stmtUpdateCtasCtes->bind_param('sss', $Dato['Debe'], $Dato['IngBrutosOrigen'], $Dato['NumeroComprobante']);
    //                 $stmtUpdateCtasCtes->execute();
    //                 $stmtUpdateCtasCtes->close();
    //             }
    //         }

    //         // Confirma la transacción si todas las operaciones se han realizado correctamente
    //         $mysqli->commit();
    //         echo json_encode(array('success' => 1));
    //     } catch (Exception $e) {
    //         // Revierte la transacción si hay algún error
    //         $mysqli->rollback();
    //         echo json_encode(array('error' => $e->getMessage()));
    //     }
    // } else {
    //     echo json_encode(array('error' => 'Faltan datos necesarios.'));
    // }
}



// if($_POST[Descontar]==1){
//   $OrdenN=$_POST['Remitos'];
//   $Descuento=$_POST['Descuento'];

//   for($i=0;$i<=count($OrdenN);$i++)
//   {
//   // ACTUALIZO FACTURADO A SI EN TABLA LOGISTICA  
//   $sql=$mysqli->query("UPDATE `TransClientes` SET Debe=Debe*((100-$Descuento)/100) WHERE id='$OrdenN[$i]' AND TipoDeComprobante='Remito' AND Eliminado='0' LIMIT 1");
//   $sqlbuscar=$mysqli->query("SELECT NumeroComprobante,Debe,IngBrutosOrigen FROM TransClientes WHERE id='$OrdenN[$i]' AND TipoDeComprobante='Remito' AND Eliminado='0'");
//   $Dato= $sqlbuscar->fetch_array(MYSQLI_ASSOC);
//   $Actualizar_CtasCtes="UPDATE Ctasctes SET Debe='$Dato[Debe]' WHERE idCliente='$Dato[IngBrutosOrigen]' AND NumeroVenta='$Dato[NumeroComprobante]' AND Haber='0' LIMIT 1";
//   $mysqli->query($Actualizar_CtasCtes);
//   }
//   echo json_encode(array('success'=>1));
// }

?>