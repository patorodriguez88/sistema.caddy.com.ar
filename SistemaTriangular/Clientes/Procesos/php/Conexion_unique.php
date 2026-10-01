<?php
session_start();


if ($_GET['token'] <> null) {

    //     // Ejemplo de uso
    $dato_a_verificar = $_GET['token'];
    $dato_a_verificar_1 = $_GET['id'];

    if (verificarDato($dato_a_verificar, $dato_a_verificar_1)) {

        require_once __DIR__ . '/../../../Conexion/report_db_config.php';
        $host = REPORT_DB_HOST;
        $user = REPORT_DB_USER;
        $pass = REPORT_DB_PASS;

        $db = REPORT_DB_NAME;
        $mysqli = new mysqli($host, $user, $pass, $db);
        mysqli_set_charset($mysqli, "utf8");

        $_SESSION['token_unique'] = $_GET['token'];
    } else {

        unset($_SESSION['token_unique']);

        header("location:/SistemaTriangular/Mail/plantilla/invoice_failed.html");
    }
}



function verificarDato($dato, $dato1)
{
    // Configuración de la conexión a la base de datos
    require_once __DIR__ . '/../../../Conexion/report_db_config.php';
    $servername = REPORT_DB_HOST;
    $username = REPORT_DB_USER;
    $password = REPORT_DB_PASS;
    $dbname = REPORT_DB_NAME;

    // Crear conexión
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Escapar el dato para evitar inyección de SQL (reemplácelo con la función correspondiente según la extensión de la base de datos que esté utilizando)
    $dato = $conn->real_escape_string($dato);
    $dato1 = $conn->real_escape_string($dato1);

    // Consulta SQL para verificar el dato
    $sql = "SELECT idCliente FROM Notifications WHERE Token = '$dato' AND CodigoSeguimiento='$dato1' AND DATEDIFF(NOW(), Fecha) <= 5";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {

        // Obtener el resultado de la consulta
        $row = $result->fetch_assoc();
        $usuarioId = $row["idCliente"];
        $_SESSION['idusuario'] = $usuarioId;

        $sql_user = "SELECT Nombre,NdeCliente,NIVEL,usuario FROM usuarios WHERE NdeCliente='$usuarioId' AND Activo=1";
        $result_user = $conn->query($sql_user);
        $row_user = $result_user->fetch_assoc();

        $_SESSION['NIVEL'] = $row_user['NIVEL'];
        $_SESSION['NCliente'] = $row_user['NdeCliente'];
        $_SESSION['Usuario'] = $row_user['usuario'];

        // Cerrar la conexión
        $conn->close();

        if ($row_user['usuario']) {
            // Devolver el UsuarioId si el dato es correcto (existente en la base de datos)
            return $usuarioId;
        } else {

            return null;
        }
    } else {
        // Cerrar la conexión en caso de que no haya resultados
        $conn->close();

        // Devolver un valor indicando que el dato no es correcto (puedes ajustar según tus necesidades)
        return null;
    }
}
