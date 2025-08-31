<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once "../../../Conexion/Conexioni.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($_POST['accion']) {
        case 'agregar_multa':
            agregarMulta();
            break;
        case 'cargar_municipios':
            cargarMunicipios();
            break;
        case 'cargar_vehiculos':
            cargarVehiculos();
            break;
        case 'cargar_empleados':
            cargarEmpleados();
            break;
        case 'listar_multas':
            listarMultas();
            break;
        case 'resumen_multas':
            resumenMultas();
            break;
        case 'obtener_multa':
            obtener_Multa();
            break;
        case 'eliminar_multa':
            eliminar_Multa();
            break;
        case 'editar_multa':
            editar_Multa();
            break;
    }
}
function resumenMultas()
{
    $conn = (new conexion())->obtenerConexion();

    // 1. Totales simples
    $sqlTotales = "SELECT
    SUM(CASE WHEN Estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
    SUM(CASE WHEN Estado = 'Pagada' THEN 1 ELSE 0 END) AS pagadas,
    SUM(Importe) AS totalImporte,
    SUM(CASE WHEN Estado = 'Pendiente' THEN Importe ELSE 0 END) AS importePendientes,
    SUM(CASE WHEN Estado = 'Pagada' THEN Importe ELSE 0 END) AS importePagadas
    FROM Multas";

    $resTotales = $conn->query($sqlTotales)->fetch_assoc();

    // 2. Agrupado por Empleado
    $sqlPorEmpleado = "SELECT Empleado, COUNT(*) AS cantidad FROM Multas GROUP BY Empleado";
    $resEmp = $conn->query($sqlPorEmpleado);
    $porRepartidor = [];
    while ($row = $resEmp->fetch_assoc()) {
        $porRepartidor[$row['Empleado']] = (int)$row['cantidad'];
    }

    // 3. Agrupado por Municipio
    $sqlPorMuni = "SELECT Municipio, COUNT(*) AS cantidad FROM Multas GROUP BY Municipio";
    $resMuni = $conn->query($sqlPorMuni);
    $porMunicipio = [];
    while ($row = $resMuni->fetch_assoc()) {
        $porMunicipio[$row['Municipio']] = (int)$row['cantidad'];
    }

    $totalCantidad = (int)$resTotales['pendientes'] + (int)$resTotales['pagadas'];

    // 4. Devolver respuesta JSON
    echo json_encode([
        "pendientes" => (int)$resTotales['pendientes'],
        "pagadas" => (int)$resTotales['pagadas'],
        "totalCantidad" => $totalCantidad,
        "totalImporte" => (float)$resTotales['totalImporte'],
        "importePendientes" => (float)$resTotales['importePendientes'],
        "importePagadas" => (float)$resTotales['importePagadas'],
        "porRepartidor" => $porRepartidor,
        "porMunicipio" => $porMunicipio
    ]);

    exit;
}
function listarMultas()
{
    $conn = (new conexion())->obtenerConexion();
    $sql = "SELECT id, Fecha, Municipio, Patente, Empleado, Importe, Estado FROM Multas WHERE Eliminado=0 ORDER BY Fecha DESC";
    $result = $conn->query($sql);

    $multas = [];
    while ($row = $result->fetch_assoc()) {
        $multas[] = $row;
    }

    echo json_encode(['data' => $multas]);
    exit;
}
function agregarMulta()
{
    $conn = (new conexion())->obtenerConexion();

    $fecha = $_POST['fecha'];
    $municipio = $_POST['municipio'];
    $patente = $_POST['patente'];
    $fechainfraccion = $_POST['fechainfraccion'];
    $vencimiento = $_POST['vencimiento'];
    $importe = $_POST['importe'];
    $numero = $_POST['numero'];
    $motivo = $_POST['motivo'];
    $estado = $_POST['estado'];
    $NumeroAsiento = 0;

    $empleadoData = explode(',', $_POST['empleado']);
    $idEmpleado = $empleadoData[0] ?? null;
    $nombreEmpleado = $empleadoData[1] ?? '';

    $stmt = $conn->prepare("INSERT INTO Multas (FechaInfraccion, Fecha, Municipio, Patente, Vencimiento, Importe, Numero, Motivo, Estado, Empleado, idEmpleado,NumeroAsiento)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssdssssii", $fechainfraccion, $fecha, $municipio, $patente, $vencimiento, $importe, $numero, $motivo, $estado, $nombreEmpleado, $idEmpleado, $NumeroAsiento);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
}
function cargarMunicipios()
{
    $conn = (new conexion())->obtenerConexion();
    $sql = "SELECT Localidad, Provincia FROM Localidades ORDER BY Localidad ASC";
    $result = $conn->query($sql);

    $opciones = [];
    while ($row = $result->fetch_assoc()) {
        $opciones[] = $row['Localidad'] . ' ' . $row['Provincia'];
    }

    echo json_encode($opciones);
}
function cargarVehiculos()
{
    $conn = (new conexion())->obtenerConexion();
    $sql = "SELECT Dominio, Marca, Modelo FROM Vehiculos WHERE Activo = 'Si' AND VehiculoOperativo=1 AND Aliados=0";
    $result = $conn->query($sql);

    $vehiculos = [];
    while ($row = $result->fetch_assoc()) {
        $vehiculos[] = [
            'value' => $row['Dominio'],
            'label' => "{$row['Marca']} {$row['Modelo']} ({$row['Dominio']})"
        ];
    }

    echo json_encode($vehiculos);
}
function cargarEmpleados()
{
    $conn = (new conexion())->obtenerConexion();
    $sql = "SELECT id, NombreCompleto FROM Empleados WHERE Puesto='Transportista' AND Inactivo='0' AND Aliados=0";
    $result = $conn->query($sql);

    $empleados = [];
    while ($row = $result->fetch_assoc()) {
        $empleados[] = [
            'value' => $row['id'] . ',' . $row['NombreCompleto'],
            'label' => $row['NombreCompleto']
        ];
    }

    echo json_encode($empleados);
}
// 🔎 Obtener una multa

function obtener_Multa()
{
    $mysqli = (new conexion())->obtenerConexion();
    $id = intval($_POST['id']);
    $sql = "SELECT * FROM multas WHERE id = $id LIMIT 1";
    $res = $mysqli->query($sql);
    echo json_encode($res->fetch_assoc());
    exit;
}

// ❌ Eliminar (lógicamente) una multa
function eliminar_Multa()
{
    $mysqli = (new conexion())->obtenerConexion();
    $id = intval($_POST['id']);
    $sql = "UPDATE multas SET Eliminado = 1 WHERE id = $id";
    $success = $mysqli->query($sql);
    echo json_encode(['success' => $success]);
    exit;
}


function editar_Multa()
{
    $conn = (new conexion())->obtenerConexion();

    $id = intval($_POST['id_multa']);
    $fecha = $_POST['fecha'];
    $fechainfraccion = $_POST['fechainfraccion'];
    $vencimiento = $_POST['vencimiento'];
    $municipio = $_POST['municipio'];
    $patente = $_POST['patente'];
    $importe = floatval($_POST['importe']);
    $numero = $_POST['numero'];
    $motivo = $_POST['motivo'];
    $estado = $_POST['estado'];

    $empleadoData = explode(',', $_POST['empleado']);
    $idEmpleado = $empleadoData[0] ?? null;
    $nombreEmpleado = $empleadoData[1] ?? '';

    $sql = "UPDATE multas SET 
                Fecha = ?,
                FechaInfraccion = ?,
                Vencimiento = ?,
                Municipio = ?,
                Patente = ?,
                Importe = ?,
                Numero = ?,
                Motivo = ?,
                Estado = ?,
                Empleado = ?,
                idEmpleado = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssdssssii",
        $fecha,
        $fechainfraccion,
        $vencimiento,
        $municipio,
        $patente,
        $importe,
        $numero,
        $motivo,
        $estado,
        $nombreEmpleado,
        $idEmpleado,
        $id
    );

    $success = $stmt->execute();
    echo json_encode(['success' => $success]);
    exit;
}
