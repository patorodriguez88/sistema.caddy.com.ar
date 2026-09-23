<?php
// Test temporal (se borra tras usarse): confirma que el fix de "Agregar
// Proveedor" (prepared statement) soporta una comilla en el domicilio sin
// romper el INSERT - el bug que perdio el alta de MEREB CAROLINA BENITA.
// Corre SOLO contra la DB de sandbox (esa es la que responde este archivo).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$razonsocial = 'TEST COMILLA ' . time();
$dire = "O'Higgins 123"; // la comilla que rompia el SQL viejo

$id = "SELECT MAX(id) AS id FROM Proveedores";
$Resultado = $mysqli->query($id);
$row = $Resultado->fetch_array(MYSQLI_ASSOC);
$id = intval($row['id']) + 1;

$stmtIns = $mysqli->prepare(
    "INSERT INTO `Proveedores`(`Codigo`,`RazonSocial`, `Domicilio`, `Localidad`, `Provincia`, `CPostal`, `Telefono`, `Celular`,
    `Contacto`, `Iva`, `Cuit`, `Rubro`, `Condicion`, `Mail`, `PaginaWeb`, `Observaciones`, `IngresosBrutos`,
    `CtaAsignada`, `SolicitaCombustible`, `SolicitaVehiculo`,`TareasAsana`,`TareasAsana_gid`)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
);
$loc = ''; $prov=''; $cp=''; $tel=''; $cel=''; $contacto=''; $iva=''; $cuit=''; $rubro=''; $condicion='';
$email=''; $web=''; $obs=''; $ib=''; $ctaas='0'; $comb=0; $vehi=0; $asana=0; $asana_gid='0';
$stmtIns->bind_param(
    'isssssssssssssssssiiis',
    $id, $razonsocial, $dire, $loc, $prov,
    $cp, $tel, $cel, $contacto, $iva,
    $cuit, $rubro, $condicion, $email, $web,
    $obs, $ib, $ctaas, $comb, $vehi,
    $asana, $asana_gid
);

$resultado = ['razonsocial' => $razonsocial, 'dire_con_comilla' => $dire];

try {
    if ($stmtIns->execute()) {
        $newId = $mysqli->insert_id;
        // Verifico que quedo grabado tal cual (comilla incluida)
        $chk = $mysqli->prepare("SELECT RazonSocial, Domicilio FROM Proveedores WHERE Codigo = ?");
        $chk->bind_param('s', $id);
        $chk->execute();
        $chk->bind_result($rs, $dom);
        $chk->fetch();
        $resultado['insert_ok'] = true;
        $resultado['grabado_razonsocial'] = $rs;
        $resultado['grabado_domicilio'] = $dom;

        // Limpio el registro de prueba
        $mysqli->query("DELETE FROM Proveedores WHERE Codigo = '{$id}' LIMIT 1");
        $resultado['limpiado'] = true;
    } else {
        $resultado['insert_ok'] = false;
        $resultado['error'] = $stmtIns->error;
    }
} catch (\Throwable $e) {
    $resultado['insert_ok'] = false;
    $resultado['excepcion'] = $e->getMessage();
}

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
