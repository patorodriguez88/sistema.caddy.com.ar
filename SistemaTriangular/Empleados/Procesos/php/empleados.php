<?php
include_once "../../../Conexion/Conexioni.php";
include_once('asana_api.php');

date_default_timezone_set('America/Argentina/Cordoba');
if (isset($_POST['Empleados'])) {

    $SQL = $mysqli->query("SELECT * FROM `Empleados` WHERE Empleados.Aliados=0 AND Empleados.Inactivo=0");

    $ROWS = array();

    while ($DATOS_CLIENTES = $SQL->fetch_array(MYSQLI_ASSOC)) {

        //ACTUALIZO LOS CLIENTES
        // if ($DATOS_CLIENTES['VencimientoLicencia'] < date('Y-m-d')) {

        //     $mysqli->query("UPDATE Empleados SET Inactivo=1 WHERE id='$DATOS_CLIENTES[id]' LIMIT 1");
        // } else {

        //     $mysqli->query("UPDATE Empleados SET Inactivo=0 WHERE id='$DATOS_CLIENTES[id]' LIMIT 1");
        // }

        $ROWS[] = $DATOS_CLIENTES;
    }

    echo json_encode(array('data' => $ROWS));
}
//VER EMPLEADO
if (isset($_POST['VerEmpleado'])) {

    // $SQL=$mysqli->query("SELECT * FROM `Empleados` WHERE id='".$_POST['id']."'");
    $SQL = $mysqli->query("SELECT Empleados.*,usuarios.Usuario,usuarios.PASSWORD FROM `Empleados` INNER JOIN usuarios ON Empleados.Usuario=usuarios.id WHERE Empleados.id='" . $_POST['id'] . "'");
    $ROWS = array();

    while ($DATOS_CLIENTES = $SQL->fetch_array(MYSQLI_ASSOC)) {

        $ROWS[] = $DATOS_CLIENTES;
    }

    echo json_encode(array('data' => $ROWS));
}

//MODIFICAR EMPLEADO
if (isset($_POST['ModificarEmpleado'])) {

    $VencimientoLic = explode("/", $_POST['licencia'], 3);
    $FechaVencimientoLicencia = $VencimientoLic[2] . '-' . $VencimientoLic[0] . '-' . $VencimientoLic[1];

    $Nacimiento = explode("/", $_POST['nac'], 3);
    $FechaNacimiento = $Nacimiento[2] . '-' . $Nacimiento[0] . '-' . $Nacimiento[1];

    $Ingreso = explode("/", $_POST['ing'], 3);
    $FechaIngreso = $Ingreso[2] . '-' . $Ingreso[0] . '-' . $Ingreso[1];

    $SQL = "UPDATE `Empleados` SET `NombreCompleto`='" . $_POST['nombre'] . "',`Domicilio`='" . $_POST['domicilio'] . "',`Localidad`='" . $_POST['city'] . "',
    `Provincia`='" . $_POST['state'] . "',`CodigoPostal`='" . $_POST['codigopostal'] . "',`Telefono`='" . $_POST['telefono'] . "',`FechaNacimiento`='" . $FechaNacimiento . "',
    `FechaIngreso`='" . $FechaIngreso . "',`Dni`='" . $_POST['dni'] . "',`VencimientoLicencia`='" . $FechaVencimientoLicencia . "',
    `Observaciones`='" . $_POST['obs'] . "',`GrupoSanguineo`='" . $_POST['gruposanguineo'] . "',`TelefonoEmergencia`='" . $_POST['phone_emergency'] . "'
     WHERE id='" . $_POST['id_externo'] . "' LIMIT 1";

    if ($mysqli->query($SQL)) {

        echo json_encode(array('success' => 1, 'Fecha' => $FechaVencimientoLicencia));
    } else {

        echo json_encode(array('success' => 0));
    }
}

// AGREGAR EMPLEADO
if (isset($_POST['Agregar_empleado'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    $FechaHoy = date('Y-m-d');

    // Helpers
    $post = function (string $k, $default = '') {
        return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $default;
    };

    $dateOrNull = function ($value) {
        $v = trim((string)$value);
        return ($v === '') ? null : $v; // Debe venir YYYY-mm-dd desde el input type="date"
    };

    // Datos
    $nombre          = $post('nombre');
    $dni             = $post('dni');
    $domicilio       = $post('domicilio');
    $city            = $post('city');
    $state           = $post('state');
    $telefono        = $post('telefono');
    $obs             = $post('obs');
    $codigopostal    = $post('codigopostal');
    $gruposanguineo  = $post('gruposanguineo');
    $phone_emergency = $post('phone_emergency');

    // Fechas: valor "YYYY-mm-dd" o null
    $dateOrNull = function ($value) {
        $v = trim((string)$value);
        $v = trim($v, " \t\n\r\0\x0B'\""); // saca comillas si vinieran
        if ($v === '') return null;
        // valida formato
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return null;
        return $v;
    };

    $nac = $dateOrNull($_POST['nac'] ?? '');
    $ing = $dateOrNull($_POST['ing'] ?? '');
    $lic = $dateOrNull($_POST['lic'] ?? '');  // ojo: vos mandás "lic" desde JS

    // Datos extras
    $gid_asana   = $post('asana_gid');
    $gid_hubspot = $post('hubspot_gid');

    $alergico  = isset($_POST['alergico']) ? (int)$_POST['alergico'] : 0;
    $driver_id = $_POST['driver_id'] ?? 0;

    // Transacción para que Usuario + Empleado queden consistentes
    $mysqli->begin_transaction();

    try {
        // 1) INSERT USUARIO (Usuario provisional = nombre; luego lo actualizás)
        $sqlUsuario = "INSERT INTO usuarios
        (Nombre, PASSWORD, NIVEL, ACTIVO, Direccion, Localidad, Ciudad, Telefono, Observaciones, Usuario, FechaPassword, Estado, gid_asana, gid_hubspot)
        VALUES (?, ?, 3, 1, ?, ?, ?, ?, ?, ?, ?, 'Activo', ?, ?)";

        $stmtU = $mysqli->prepare($sqlUsuario);
        if (!$stmtU) throw new Exception("Prepare usuarios failed: " . $mysqli->error);

        $usuarioTmp = $nombre;

        $stmtU->bind_param(
            "sssssssssss",   // <-- 11 tipos
            $nombre,         // 1
            $dni,            // 2
            $domicilio,      // 3
            $city,           // 4
            $state,          // 5
            $telefono,       // 6
            $obs,            // 7
            $usuarioTmp,     // 8
            $FechaHoy,       // 9
            $gid_asana,      // 10
            $gid_hubspot     // 11
        );

        if (!$stmtU->execute()) throw new Exception("Execute usuarios failed: " . $stmtU->error);

        $id_usuario = $mysqli->insert_id;

        // 2) INSERT EMPLEADO
        $sqlEmp = "INSERT INTO Empleados
            (NombreCompleto, Domicilio, Localidad, Provincia, CodigoPostal, Telefono, FechaNacimiento, FechaIngreso, Dni, VencimientoLicencia, Puesto, Observaciones, CuentaAnticipos, GrupoSanguineo, TelefonoEmergencia, Inactivo, Aliados, Usuario, Alergico, driver_id)
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Transportista', ?, '112500', ?, ?, '0', '1', ?, ?, ?)";

        $stmtE = $mysqli->prepare($sqlEmp);
        if (!$stmtE) throw new Exception("Prepare empleados failed: " . $mysqli->error);

        // s = string, i = int; para fechas uso string o null (MySQLi lo manda bien si el campo acepta NULL)
        $stmtE->bind_param(
            "sssssssssssssiis",
            $nombre,
            $domicilio,
            $city,
            $state,
            $codigopostal,
            $telefono,
            $nac,
            $ing,
            $dni,
            $lic,
            $obs,
            $gruposanguineo,
            $phone_emergency,
            $id_usuario,
            $alergico,
            $driver_id
        );

        if (!$stmtE->execute()) throw new Exception("Execute empleados failed: " . $stmtE->error);

        // 3) UPDATE username final
        $UsuarioFinal = strtok($nombre, " ") . "_" . $id_usuario;

        $stmtUp = $mysqli->prepare("UPDATE usuarios SET Usuario=? WHERE id=? LIMIT 1");
        if (!$stmtUp) throw new Exception("Prepare update usuarios failed: " . $mysqli->error);

        $stmtUp->bind_param("si", $UsuarioFinal, $id_usuario);
        if (!$stmtUp->execute()) throw new Exception("Execute update usuarios failed: " . $stmtUp->error);

        $mysqli->commit();

        // OJO: $aliado y $vehiculo NO existen en tu código actual -> eso te va a generar Notices/Warnings
        echo json_encode([
            'success' => 1,
            'user_id' => $id_usuario
        ]);
    } catch (Throwable $e) {
        $mysqli->rollback();
        http_response_code(500);
        echo json_encode([
            'success' => 0,
            'error' => $e->getMessage()
        ]);
    }
}
