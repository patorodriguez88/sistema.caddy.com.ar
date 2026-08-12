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
    // $SQL = $mysqli->query("SELECT Empleados.*,usuarios.Usuario,usuarios.PASSWORD FROM `Empleados` INNER JOIN usuarios ON Empleados.Usuario=usuarios.id WHERE Empleados.id='" . $_POST['id'] . "'");
    $SQL = $mysqli->query("SELECT 
    Empleados.*,
    usuarios.Usuario,
    usuarios.PASSWORD,
    usuarios.gid_asana,
    usuarios.gid_hubspot
      FROM Empleados
    INNER JOIN usuarios ON Empleados.Usuario = usuarios.id
    WHERE Empleados.id = '" . $_POST['id'] . "' LIMIT 1");

    $ROWS = array();

    while ($DATOS_CLIENTES = $SQL->fetch_array(MYSQLI_ASSOC)) {

        $ROWS[] = $DATOS_CLIENTES;
    }

    echo json_encode(array('data' => $ROWS));
}



//MODIFICAR EMPLEADO

if (isset($_POST['ModificarEmpleado'])) {

    header('Content-Type: application/json; charset=utf-8');

    try {
        $idExterno = (int)($_POST['id_externo'] ?? 0);
        if ($idExterno <= 0) {
            echo json_encode(['success' => 0, 'error' => 'id_externo inválido']);
            exit;
        }
        $dateOrNull = function ($value) {
            $v = trim((string)$value);
            $v = trim($v, " \t\n\r\0\x0B'\""); // saca comillas si vinieran
            if ($v === '') return null;
            // valida formato
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return null;
            return $v;
        };

        $FechaNacimiento = $dateOrNull($_POST['nac'] ?? '');
        $FechaIngreso = $dateOrNull($_POST['ing'] ?? '');
        $FechaLicencia = $dateOrNull($_POST['licencia'] ?? '');  // ojo: vos mandás "lic" desde JS

        // ⛔ Validaciones obligatorias
        if (!$FechaNacimiento) {
            echo json_encode([
                'success' => 0,
                'field'   => 'FechaNacimiento',
                'message' => 'Debes completar la Fecha de Nacimiento.'
            ]);
            exit;
        }

        if (!$FechaIngreso) {
            echo json_encode([
                'success' => 0,
                'field'   => 'FechaIngreso',
                'message' => 'Debes completar la Fecha de Ingreso.'
            ]);
            exit;
        }

        if (!$FechaLicencia) {
            echo json_encode([
                'success' => 0,
                'field'   => 'VencimientoLicencia',
                'message' => 'Debes completar el vencimiento de la licencia.'
            ]);
            exit;
        }
        // 1) UPDATE EMPLEADOS
        $sqlEmp = "UPDATE Empleados SET
            NombreCompleto = ?,
            Domicilio = ?,
            Localidad = ?,
            Provincia = ?,
            CodigoPostal = ?,
            Telefono = ?,
            FechaNacimiento = ?,
            FechaIngreso = ?,
            Dni = ?,
            VencimientoLicencia = ?,
            Observaciones = ?,
            GrupoSanguineo = ?,
            TelefonoEmergencia = ?
        WHERE id = ?
        LIMIT 1";

        $stmt = $mysqli->prepare($sqlEmp);
        if (!$stmt) throw new RuntimeException("Prepare Empleados failed: " . $mysqli->error);

        $stmt->bind_param(
            "sssssssssssssi",
            $_POST['nombre'],
            $_POST['domicilio'],
            $_POST['city'],
            $_POST['state'],
            $_POST['codigopostal'],
            $_POST['telefono'],
            $FechaNacimiento,
            $FechaIngreso,
            $_POST['dni'],
            $FechaLicencia,
            $_POST['obs'],
            $_POST['gruposanguineo'],
            $_POST['phone_emergency'],
            $idExterno
        );

        if (!$stmt->execute()) {
            throw new RuntimeException("Execute Empleados failed: " . $stmt->error);
        }
        $stmt->close();

        // 2) Buscar el usuario asociado (Empleados.Usuario)
        $sqlUsrId = "SELECT Usuario FROM Empleados WHERE id = ? LIMIT 1";
        $stmt2 = $mysqli->prepare($sqlUsrId);
        if (!$stmt2) throw new RuntimeException("Prepare Usuario failed: " . $mysqli->error);

        $stmt2->bind_param("i", $idExterno);
        $stmt2->execute();
        $res = $stmt2->get_result();
        $row = $res->fetch_assoc();
        $stmt2->close();

        $idUsuario = (int)($row['Usuario'] ?? 0);

        // 3) UPDATE usuarios.gid_asana / gid_hubspot
        // Normalizo: si viene '' => NULL
        $asanaGid   = $_POST['asana_gid']   ?? 0;
        $hubspotGid = $_POST['hubspot_gid'] ?? 0;

        // normalizar a enteros seguros
        $asanaGid   = is_numeric($asanaGid)   ? (int)$asanaGid   : 0;
        $hubspotGid = is_numeric($hubspotGid) ? (int)$hubspotGid : 0;

        // Actualizo
        if ($idUsuario > 0) {
            $sqlUsr = "UPDATE usuarios SET gid_asana = ?, gid_hubspot = ? WHERE id = ? LIMIT 1";
            $stmt3 = $mysqli->prepare($sqlUsr);
            if (!$stmt3) throw new RuntimeException("Prepare usuarios failed: " . $mysqli->error);

            $stmt3->bind_param("ssi", $asanaGid, $hubspotGid, $idUsuario);

            if (!$stmt3->execute()) {
                throw new RuntimeException("Execute usuarios failed: " . $stmt3->error);
            }
            $stmt3->close();
        }

        echo json_encode(['success' => 1]);
    } catch (Throwable $e) {
        echo json_encode(['success' => 0, 'error' => $e->getMessage()]);
    }

    exit;
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

    // Nivel de acceso del usuario que se crea junto con el empleado:
    // 3 = Chofer/Reparto (comportamiento de siempre, cuenta de la app).
    // 1 o 2 = SuperAdministrador/Administracion (cuenta real para este sistema).
    $nivel = isset($_POST['nivel']) ? (int)$_POST['nivel'] : 3;
    if (!in_array($nivel, [1, 2, 3], true)) $nivel = 3;
    $mail = $post('mail');
    $esUsuarioSistema = in_array($nivel, [1, 2], true);
    $passwordTemporalPlano = null;

    // Jerarquía: solo un SuperAdministrador (Nivel 1) puede crear cuentas de sistema
    // (Nivel 1 o 2). Cualquier otro actor solo puede dar de alta cuentas Chofer/Reparto.
    // Se valida acá, no solo en el front, porque es lo único que de verdad lo impide.
    $actorNivel = intval($_SESSION['Nivel'] ?? 0);
    if ($esUsuarioSistema && $actorNivel !== 1) {
        http_response_code(403);
        echo json_encode([
            'success' => 0,
            'field'   => 'nivel',
            'error'   => 'Solo un SuperAdministrador puede crear usuarios de Administracion o SuperAdministrador.'
        ]);
        exit;
    }

    if ($esUsuarioSistema && $mail === '') {
        http_response_code(422);
        echo json_encode([
            'success' => 0,
            'field'   => 'mail',
            'error'   => 'El mail es obligatorio para crear un usuario del sistema (SuperAdministrador/Administracion).'
        ]);
        exit;
    }

    // Transacción para que Usuario + Empleado queden consistentes
    $mysqli->begin_transaction();

    try {
        $usuarioTmp = $nombre;

        if ($esUsuarioSistema) {
            // 1) INSERT USUARIO — cuenta real de sistema: password temporal hasheada,
            // FechaPassword ya vencida a propósito para forzar el cambio en el primer login.
            $passwordTemporalPlano = bin2hex(random_bytes(5)); // 10 caracteres
            $passwordHashInicial = password_hash($passwordTemporalPlano, PASSWORD_DEFAULT);
            $fechaPasswordVencida = '2000-01-01';

            $sqlUsuario = "INSERT INTO usuarios
            (Nombre, NIVEL, ACTIVO, Direccion, Localidad, Ciudad, Telefono, Observaciones, Mail, Usuario, password_hash, FechaPassword, Estado, gid_asana, gid_hubspot)
            VALUES (?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo', ?, ?)";

            $stmtU = $mysqli->prepare($sqlUsuario);
            if (!$stmtU) throw new Exception("Prepare usuarios failed: " . $mysqli->error);

            $stmtU->bind_param(
                "sisssssssssss",
                $nombre,
                $nivel,
                $domicilio,
                $city,
                $city,
                $telefono,
                $obs,
                $mail,
                $usuarioTmp,
                $passwordHashInicial,
                $fechaPasswordVencida,
                $gid_asana,
                $gid_hubspot
            );
        } else {
            // 1) INSERT USUARIO (Usuario provisional = nombre; luego lo actualizás)
            $sqlUsuario = "INSERT INTO usuarios
            (Nombre, PASSWORD, NIVEL, ACTIVO, Direccion, Localidad, Ciudad, Telefono, Observaciones, Usuario, FechaPassword, Estado, gid_asana, gid_hubspot)
            VALUES (?, ?, 3, 1, ?, ?, ?, ?, ?, ?, ?, 'Activo', ?, ?)";

            $stmtU = $mysqli->prepare($sqlUsuario);
            if (!$stmtU) throw new Exception("Prepare usuarios failed: " . $mysqli->error);

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
        }

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

        if ($esUsuarioSistema) {
            require_once __DIR__ . '/../../../Funciones/php/enviar_mail.php';
            $htmlMail = "
                <p>Hola " . htmlspecialchars($nombre) . ",</p>
                <p>Se creó tu usuario para el Sistema Caddy.</p>
                <p><b>Usuario:</b> " . htmlspecialchars($UsuarioFinal) . "<br>
                <b>Contraseña temporal:</b> " . htmlspecialchars($passwordTemporalPlano) . "</p>
                <p>Por seguridad, el sistema te va a pedir que la cambies apenas inicies sesión.</p>
            ";
            enviarMail($mail, $nombre, 'Tu acceso al Sistema Caddy', $htmlMail);
        }

        // OJO: $aliado y $vehiculo NO existen en tu código actual -> eso te va a generar Notices/Warnings
        echo json_encode([
            'success' => 1,
            'user_id' => $id_usuario,
            'es_usuario_sistema' => $esUsuarioSistema,
            'mail_enviado' => $esUsuarioSistema ? $mail : null
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
