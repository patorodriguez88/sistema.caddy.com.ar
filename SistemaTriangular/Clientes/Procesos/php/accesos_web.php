<?php
include_once "../../../Conexion/Conexioni.php";
require_once __DIR__ . '/../../../Funciones/php/notificar_acceso.php';
date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=utf-8');

// Formato de contraseña: se escribe SIEMPRE en usuarios.PASSWORD (no en
// password_hash, esa columna es exclusiva del login de staff en conect.php).
// El login de clientes (plataforma.caddy.com.ar/Procesos/php/ver.php) verifica
// PASSWORD probando bcrypt -> MD5 -> texto plano, en ese orden, así que un hash
// bcrypt nuevo ahí es compatible sin tocar nada más.

if (isset($_POST['ListarAccesosWeb'])) {
  $id = intval($_POST['id'] ?? 0);
  if ($id <= 0) {
    echo json_encode(['data' => []]);
    exit;
  }

  $stmt = $mysqli->prepare("SELECT id, Nombre, Usuario, Mail, ACTIVO, Estado, UltimoAcceso, NotificacionAccesoEnviada, NotificacionAccesoFecha FROM usuarios WHERE NdeCliente=? AND NIVEL=4 ORDER BY id");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $rows = [];
  while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
  }
  $stmt->close();

  echo json_encode(['data' => $rows]);
  exit;
}

if (isset($_POST['CrearAccesoWeb'])) {
  $idCliente = intval($_POST['id'] ?? 0);
  $contactoId = intval($_POST['contacto_id'] ?? 0);

  if ($idCliente <= 0 || $contactoId <= 0) {
    echo json_encode(['success' => 0, 'error' => 'Faltan datos.']);
    exit;
  }

  // El acceso sale siempre de un Contacto ya cargado (así el usuario/login siempre
  // tiene nombre, apellido y mail reales) — se valida que el contacto sea de este cliente.
  $stmtContacto = $mysqli->prepare("SELECT Nombre, Apellido, email FROM mail_clientes WHERE id=? AND idCliente=? AND Eliminado=0 LIMIT 1");
  $stmtContacto->bind_param("ii", $contactoId, $idCliente);
  $stmtContacto->execute();
  $contacto = $stmtContacto->get_result()->fetch_assoc();
  $stmtContacto->close();

  if (!$contacto) {
    echo json_encode(['success' => 0, 'error' => 'El contacto seleccionado no es válido para este cliente.']);
    exit;
  }

  $usuario = trim((string)$contacto['email']);
  if ($usuario === '' || !filter_var($usuario, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => 0, 'error' => 'Ese contacto no tiene un mail válido cargado.']);
    exit;
  }

  // El Usuario (login) no puede repetirse — la BD no lo restringe, hay que validarlo acá.
  // Es común que ya exista: el cliente pudo haberse auto-registrado antes en
  // plataforma.caddy.com.ar (registro.php), sin que el operador creara nada acá.
  $stmtDup = $mysqli->prepare("SELECT usuarios.id, usuarios.NdeCliente, Clientes.nombrecliente FROM usuarios LEFT JOIN Clientes ON Clientes.id = usuarios.NdeCliente WHERE LOWER(usuarios.Usuario) = LOWER(?) LIMIT 1");
  $stmtDup->bind_param("s", $usuario);
  $stmtDup->execute();
  $existente = $stmtDup->get_result()->fetch_assoc();
  $stmtDup->close();

  if ($existente) {
    if ((int)$existente['NdeCliente'] === $idCliente) {
      $error = 'Ese contacto ya tiene un acceso web creado para este cliente.';
    } elseif ($existente['nombrecliente']) {
      $error = 'Ese mail ya es el usuario web de otro cliente: ' . $existente['nombrecliente'] . '. Probablemente se auto-registró antes en plataforma.caddy.com.ar.';
    } else {
      $error = 'Ese mail ya está usado como usuario web (sin cliente asociado, probablemente de un auto-registro viejo). Avisá para revisarlo antes de crear uno nuevo.';
    }
    echo json_encode(['success' => 0, 'error' => $error]);
    exit;
  }

  $nombreContacto = trim($contacto['Nombre'] . ' ' . $contacto['Apellido']);

  $passwordTemporalPlano = bin2hex(random_bytes(5));
  $passwordHash = password_hash($passwordTemporalPlano, PASSWORD_DEFAULT);
  $fechaPassword = date('Y-m-d');

  // gid_asana es NOT NULL sin default en esta tabla (columna de otro módulo, no
  // aplica a clientes) — hay que mandarla igual o el INSERT tira excepción.
  $gidAsanaVacio = '';

  $stmt = $mysqli->prepare("INSERT INTO usuarios (Nombre, Mail, Usuario, PASSWORD, NIVEL, ACTIVO, Estado, NdeCliente, FechaPassword, gid_asana) VALUES (?, ?, ?, ?, 4, 1, 'Activo', ?, ?, ?)");
  $stmt->bind_param("ssssiss", $nombreContacto, $usuario, $usuario, $passwordHash, $idCliente, $fechaPassword, $gidAsanaVacio);

  if (!$stmt->execute()) {
    echo json_encode(['success' => 0, 'error' => 'No se pudo crear el acceso: ' . $mysqli->error]);
    $stmt->close();
    exit;
  }
  $nuevoId = $mysqli->insert_id;
  $stmt->close();

  $resultado = notificarAccesoSistema(
    $mysqli,
    $nuevoId,
    $usuario,
    $nombreContacto,
    $usuario,
    $passwordTemporalPlano,
    'Portal de Clientes Caddy',
    'https://plataforma.caddy.com.ar/pages-login.html'
  );

  echo json_encode([
    'success' => 1,
    'mail_enviado' => (isset($resultado['success']) && $resultado['success'] == 1) ? 1 : 0,
  ]);
  exit;
}

if (isset($_POST['ResetearAccesoWeb'])) {
  $usuarioId = intval($_POST['usuario_id'] ?? 0);
  if ($usuarioId <= 0) {
    echo json_encode(['success' => 0, 'error' => 'Usuario inválido.']);
    exit;
  }

  $stmtBuscar = $mysqli->prepare("SELECT id, Nombre, Mail, Usuario FROM usuarios WHERE id=? AND NIVEL=4 LIMIT 1");
  $stmtBuscar->bind_param("i", $usuarioId);
  $stmtBuscar->execute();
  $usuario = $stmtBuscar->get_result()->fetch_assoc();
  $stmtBuscar->close();

  if (!$usuario) {
    echo json_encode(['success' => 0, 'error' => 'Usuario no encontrado.']);
    exit;
  }

  $mail = trim((string)($usuario['Mail'] ?: $usuario['Usuario']));
  if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => 0, 'error' => 'El acceso no tiene un mail válido cargado.']);
    exit;
  }

  $passwordTemporalPlano = bin2hex(random_bytes(5));
  $passwordHash = password_hash($passwordTemporalPlano, PASSWORD_DEFAULT);
  $fechaPassword = date('Y-m-d');

  $stmt = $mysqli->prepare("UPDATE usuarios SET PASSWORD=?, FechaPassword=? WHERE id=? AND NIVEL=4 LIMIT 1");
  $stmt->bind_param("ssi", $passwordHash, $fechaPassword, $usuarioId);
  $stmt->execute();
  $stmt->close();

  $resultado = notificarAccesoSistema(
    $mysqli,
    $usuarioId,
    $mail,
    $usuario['Nombre'],
    $usuario['Usuario'],
    $passwordTemporalPlano,
    'Portal de Clientes Caddy',
    'https://plataforma.caddy.com.ar/pages-login.html'
  );

  $enviado = (isset($resultado['success']) && $resultado['success'] == 1);
  echo json_encode([
    'success' => $enviado,
    'error' => $enviado ? null : ($resultado['msg'] ?? 'No se pudo enviar el mail.'),
  ]);
  exit;
}

if (isset($_POST['ToggleAccesoWeb'])) {
  $usuarioId = intval($_POST['usuario_id'] ?? 0);
  $activo = intval($_POST['activo'] ?? 0) ? 1 : 0;

  if ($usuarioId <= 0) {
    echo json_encode(['success' => 0, 'error' => 'Usuario inválido.']);
    exit;
  }

  if ($activo === 1) {
    $stmt = $mysqli->prepare("UPDATE usuarios SET ACTIVO=1, Estado='Activo' WHERE id=? AND NIVEL=4 LIMIT 1");
    $stmt->bind_param("i", $usuarioId);
  } else {
    $stmt = $mysqli->prepare("UPDATE usuarios SET ACTIVO=0 WHERE id=? AND NIVEL=4 LIMIT 1");
    $stmt->bind_param("i", $usuarioId);
  }

  if ($stmt->execute()) {
    echo json_encode(['success' => 1]);
  } else {
    echo json_encode(['success' => 0, 'error' => 'No se pudo actualizar: ' . $mysqli->error]);
  }
  $stmt->close();
  exit;
}

if (isset($_POST['EliminarAccesoWeb'])) {
  $usuarioId = intval($_POST['usuario_id'] ?? 0);

  if ($usuarioId <= 0) {
    echo json_encode(['success' => 0, 'error' => 'Usuario inválido.']);
    exit;
  }

  $stmt = $mysqli->prepare("DELETE FROM usuarios WHERE id=? AND NIVEL=4 LIMIT 1");
  $stmt->bind_param("i", $usuarioId);

  if ($stmt->execute()) {
    echo json_encode(['success' => 1]);
  } else {
    echo json_encode(['success' => 0, 'error' => 'No se pudo eliminar: ' . $mysqli->error]);
  }
  $stmt->close();
  exit;
}
