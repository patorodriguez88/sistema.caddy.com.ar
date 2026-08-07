<?php
session_start();
include('../../../Conexion/Conexioni.php');
mysqli_set_charset($mysqli, "utf8");
header('Content-Type: application/json');

if (!isset($_POST['Descontar']) || $_POST['Descontar'] != 1) {
    echo json_encode(['success' => 0, 'msg' => 'Solicitud inválida.']);
    exit;
}

$Remitos = isset($_POST['Remitos']) ? (array) $_POST['Remitos'] : [];
$Remitos = array_values(array_filter(array_map('intval', $Remitos)));

$Descuento = isset($_POST['Descuento']) ? $_POST['Descuento'] : '';
$Descuento = str_replace(['%', ','], ['', '.'], trim($Descuento));

if (empty($Remitos)) {
    echo json_encode(['success' => 0, 'msg' => 'No hay remitos seleccionados.']);
    exit;
}

if (!is_numeric($Descuento) || $Descuento <= 0 || $Descuento > 100) {
    echo json_encode(['success' => 0, 'msg' => 'El descuento debe ser un porcentaje numérico entre 0 y 100.']);
    exit;
}

$Descuento = (float) $Descuento;

$mysqli->begin_transaction();

try {
    $stmtUpdateTrans = $mysqli->prepare(
        "UPDATE TransClientes SET Debe = Debe * ((100 - ?) / 100) WHERE id = ? AND TipoDeComprobante = 'Remito' AND Eliminado = '0'"
    );
    $stmtBuscar = $mysqli->prepare(
        "SELECT Debe FROM TransClientes WHERE id = ? AND TipoDeComprobante = 'Remito' AND Eliminado = '0'"
    );
    $stmtUpdateCtasctes = $mysqli->prepare(
        "UPDATE Ctasctes SET Debe = ? WHERE idTransClientes = ? AND Haber = '0' AND Eliminado = '0'"
    );

    $actualizados = 0;

    foreach ($Remitos as $idRemito) {
        $stmtUpdateTrans->bind_param('di', $Descuento, $idRemito);
        $stmtUpdateTrans->execute();

        $stmtBuscar->bind_param('i', $idRemito);
        $stmtBuscar->execute();
        $fila = $stmtBuscar->get_result()->fetch_assoc();

        if (!$fila) {
            continue;
        }

        $nuevoDebe = $fila['Debe'];
        $stmtUpdateCtasctes->bind_param('di', $nuevoDebe, $idRemito);
        $stmtUpdateCtasctes->execute();

        $actualizados++;
    }

    $stmtUpdateTrans->close();
    $stmtBuscar->close();
    $stmtUpdateCtasctes->close();

    if ($actualizados === 0) {
        $mysqli->rollback();
        echo json_encode(['success' => 0, 'msg' => 'No se encontró ninguno de los remitos seleccionados.']);
        exit;
    }

    $mysqli->commit();
    echo json_encode(['success' => 1, 'actualizados' => $actualizados]);
} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => 0, 'msg' => 'Error al aplicar el descuento: ' . $e->getMessage()]);
}
