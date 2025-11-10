<?php
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
    
    $miConexion = new conexion();
    
    $conexion = $miConexion->obtenerConexion();
    
    if (!$conexion) {
        echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
        exit;
    }
    
    if ($accion === 'obtener_datos') {
        obtenerDatos($conexion);
    }else if($accion === 'editar_cuenta'){
        editarDatos($conexion);
    }else if ($accion === 'tiene_forma_pago') {
        tieneFormaDePago($conexion);
    }else if ($accion === 'agregar_cuenta') {
        agregarCuenta($conexion);
    }
}

function agregarCuenta($conexion) {
    try {
        $cuenta = $_POST['Cuenta'];
        $nombre = $_POST['NombreCuenta'];
        $tipo = $_POST['TipoCuenta'];
        $nivel = $_POST['Nivel'];
        $formapago = $_POST['FormaPago'];
        $ordencompra = $_POST['OrdenCompra'];
        $anticipoempleado = $_POST['AnticipoEmpleado'];
        $sincodigo = $_POST['SinCodigo'];
        $ejerciciocontable = '2016';
        $sucursal= $_SESSION['Sucursal'];
        $muestragastos=0;
        $cuentabancaria=$_POST['CuentaBancaria'];
        $presupuestoanual = $_POST['PresupuestoAnual'] ?? 0;
        $presupuestomensual= $_POST['PresupuestoMensual'] ?? 0;
        $usuario = $_SESSION['Usuario'];
        $FechaPresupuesto = date('Y-m-d');
        $admitecobranzas=0;

        // Insertar en PlanDeCuentas
        $query = "INSERT INTO PlanDeCuentas 
                (Cuenta, NombreCuenta, TipoCuenta, Nivel, OrdenesDeCompra, Anticipos, Autorizacion, EjercicioContable, Sucursal, MuestraGastos,
                CuentaBancaria, PresupuestoMensual, PresupuestoAnual, Usuario, FechaPresupuesto) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($query);
        if (!$stmt) {
            throw new Exception("Error en prepare: " . $conexion->error);
        }

        $stmt->bind_param("sssiiiiisiiiiss", $cuenta, $nombre, $tipo, $nivel, $ordencompra, $anticipoempleado, $sincodigo,
            $ejerciciocontable, $sucursal, $muestragastos, $cuentabancaria, $presupuestomensual, $presupuestoanual,
            $usuario, $FechaPresupuesto);

        if (!$stmt->execute()) {
            throw new Exception("Error al insertar cuenta: " . $stmt->error);
        }

        // Si corresponde, insertar en FormaDePago
        if ($formapago == 1) {
            $sqlInsert = "INSERT INTO FormaDePago (FormaDePago, CuentaContable, AdmiteCobranzas) VALUES (?, ?, ?)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->bind_param("ssi", $nombre, $cuenta, $admitecobranzas);
            $stmtInsert->execute();
        }

        echo json_encode(['success' => true]);
        exit;

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}


function tieneFormaDePago($conexion) {
    $cuenta = $_POST['Cuenta'];

    $stmt = $conexion->prepare("SELECT * FROM FormaDePago WHERE CuentaContable = ?");
    $stmt->bind_param("s", $cuenta);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $existe = $resultado->num_rows > 0 ? 1 : 0;

    echo json_encode(['existe' => $existe]);
    exit;
}
function obtenerDatos($conexion){
    
    $sql = "SELECT * FROM PlanDeCuentas ORDER BY Cuenta ASC";
    $resultado = $conexion->query($sql);
    $rows = [];

    if ($resultado) {
        while ($row = $resultado->fetch_assoc()) {
            $rows[] = $row;
        }
    }

    echo json_encode(array('data'=>$rows));


}
function editarDatos($conexion) {
    $id = $_POST['id'];

    if (!is_numeric($id) || $id <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID inválido']);
        exit;
    }

    $cuenta = $_POST['Cuenta'];
    $nombre = $_POST['NombreCuenta'];
    $tipo = $_POST['TipoCuenta'];
    $nivel = $_POST['Nivel'];
    $cuentabancaria = $_POST['CuentaBancaria'];

    // $ordencompra = $_POST['OrdenCompra'];
    $anticipoempleado = $_POST['AnticipoEmpleado'];
    $sincodigo = $_POST['SinCodigo'];

    $formapago = $_POST['FormaPago']; // 1 o 0

    // 1. Actualizar PlanDeCuentas (sin FormaDePago)
    $query = "UPDATE PlanDeCuentas 
    SET Cuenta = ?, NombreCuenta = ?, TipoCuenta = ?, Nivel = ?, 
        Anticipos = ?, Autorizacion = ?, CuentaBancaria = ?
    WHERE id = ? LIMIT 1";

    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conexion->error]);
    exit;
    }

    $stmt->bind_param("sssiiisi", $cuenta, $nombre, $tipo, $nivel, $anticipoempleado, $sincodigo, $cuentabancaria, $id);    
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
        exit;
    }

    // 2. Gestionar FormaDePago
    if ($formapago == 1) {
        $sqlVer = "SELECT * FROM FormaDePago WHERE CuentaContable = ?";
        $stmtVer = $conexion->prepare($sqlVer);
        $stmtVer->bind_param("s", $cuenta);
        $stmtVer->execute();
        $resultado = $stmtVer->get_result();
        $admitecobranzas=1;//ver esto!

        if ($resultado->num_rows == 0) {
            $sqlInsert = "INSERT INTO FormaDePago (FormaDePago, CuentaContable,AdmiteCobranzas) VALUES (?, ?, ?)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->bind_param("ssi", $nombre, $cuenta,$admitecobranzas);
            $stmtInsert->execute();
        }

    } else {
        $sqlDelete = "DELETE FROM FormaDePago WHERE CuentaContable = ?";
        $stmtDelete = $conexion->prepare($sqlDelete);
        $stmtDelete->bind_param("s", $cuenta);
        $stmtDelete->execute();
    }

    echo json_encode(['success' => true]);
    exit;
}
?>