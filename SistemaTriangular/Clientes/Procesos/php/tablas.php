<?php
// Mostrar errores de PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once "../../../Conexion/Conexioni.php";

//TAREAS ASANA
if (isset($_POST['Asignar_tareas_asana'])) {

  $stmt = $mysqli->prepare("UPDATE Clientes SET TareasAsana_gid = ? WHERE id = ? LIMIT 1");
  $stmt->bind_param("ii", $_POST['TareasAsana_gid'], $_POST['idCliente']);

  if ($stmt->execute()) {

    echo json_encode(array('success' => 1));
  } else {

    echo json_encode(array('success' => 0));
  }
}

if (isset($_POST['TareasAsana'])) {
  $stmt = $mysqli->prepare("UPDATE Clientes SET TareasAsana = CASE WHEN TareasAsana = 1 THEN 0 ELSE 1 END WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $_POST['idCliente']);

  if ($stmt->execute()) {

    echo json_encode(array('success' => 1));
  } else {

    echo json_encode(array('success' => 0));
  }
}


//USUARIOS ASANA

// if(isset($_POST['Usuarios_asana'])){

//     $sql= $mysqli->query("SELECT gid_asana, CONCAT(Nombre, ' ', Apellido) as Nombre,Usuario FROM usuarios WHERE gid_asana <> '' AND Activo='1'");

//     $rows=array();

//     while($row=$sql->fetch_array(MYSQLI_ASSOC)){

//         $rows[]=$row;

//     }

//     echo json_encode($rows);
// }
if (isset($_POST['Usuarios_asana'])) {

  // Ejecuta la consulta
  $sql = $mysqli->query("SELECT gid_asana, CONCAT(Nombre, ' ', Apellido) as Nombre, Usuario FROM usuarios WHERE gid_asana <> '' AND Activo='1'");

  // Verifica si la consulta falló
  if (!$sql) {
    // Si falla, imprime el error de MySQL
    echo json_encode(array('error' => 1, 'message' => 'Error en la consulta SQL: ' . $mysqli->error));
    exit; // Salir del script si hay error
  }

  // Si la consulta es exitosa
  $rows = array();

  while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }

  // Devuelve los resultados en formato JSON
  echo json_encode($rows);
}

//CONTACT
if (isset($_POST['Contact'])) {

  $id = $mysqli->real_escape_string($_POST['id']);

  // Utilizar una consulta preparada
  $stmt = $mysqli->prepare("SELECT * FROM mail_clientes WHERE idCliente = ? AND Eliminado=0");
  $stmt->bind_param("s", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  // Manejar errores en la ejecución de la consulta
  if (!$result) {
    echo json_encode(array('error' => $stmt->error));
  } else {
    // Inicializar la variable $rows
    $rows = array();

    // Obtener resultados y agregarlos a $rows
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $rows[] = $row;
    }

    // Enviar resultados como JSON
    echo json_encode(array('data' => $rows));
  }

  // Cerrar la consulta preparada
  $stmt->close();
}

if (isset($_POST['Recorridos'])) {

  $id = $_POST['id'];
  $sql = $mysqli->query("SELECT Logistica.Fecha,Logistica.id,Logistica.NumerodeOrden,Logistica.Fecha,Logistica.Hora,Logistica.Patente,Logistica.NombreChofer,Logistica.Recorrido,
    Productos.PrecioVenta,Logistica.KilometrosRecorridos,Clientes.nombrecliente FROM Logistica 
    INNER JOIN Recorridos ON Logistica.Recorrido=Recorridos.Numero 
    INNER JOIN Productos ON Recorridos.CodigoProductos=Productos.Codigo
    LEFT JOIN Clientes ON Logistica.Cliente=Clientes.id
    WHERE Logistica.Fecha>='2021-09-01' AND Logistica.Eliminado=0 AND Logistica.Facturado=0 
    AND Logistica.id NOT IN (SELECT idLogistica FROM `Ctasctes`)");

  while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

if (isset($_POST['Saldos'])) {

  $sql = "SELECT idCliente,RazonSocial,SUM(Debe)AS Debe,SUM(Haber)AS Haber,SUM(Debe-Haber)as Saldo FROM Ctasctes WHERE Eliminado=0 AND idFacturado=0 GROUP BY RazonSocial HAVING SUM(Debe-Haber)<>0 ";
  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

if (isset($_POST['Facturacion'])) {

  $id    = $mysqli->real_escape_string($_POST['id']);
  $desde = isset($_POST['desde']) ? $_POST['desde'] : '';
  $hasta = isset($_POST['hasta']) ? $_POST['hasta'] : '';

  if ($desde === '' && $hasta === '') {
    // SIN filtro de fechas: visitas calculadas sobre todo Seguimiento (Eliminado=0)
    $sql = "SELECT
    t.CodigoProveedor,
    t.ClienteDestino,
    t.DomicilioDestino,
    t.CodigoSeguimiento,
    t.Fecha,
    t.TipoDeComprobante,
    t.NumeroComprobante,
    t.Observaciones,
    t.Debe,
    t.Haber,
    t.ComprobanteF,
    t.NumeroF,
    t.id,
    IF(t.FormaDePago='Origen', t.idClienteOrigen, t.idClienteDestino) AS idCliente,
    t.FormaDePago,
    t.Entregado,
    t.Devuelto,
    t.Control_facturacion,
    COALESCE(t.Visitas, 0) AS CantidadVisitas,
    c.CodigoPostal AS CodigoPostal,
    COALESCE(vv.CantServicios, 0) AS CantServicios,
    CASE
      WHEN COALESCE(t.Visitas,0) > 1 AND COALESCE(vv.CantServicios,0) = 1 THEN 1
      ELSE 0
    END AS AlertaActualizar
    FROM TransClientes t
    LEFT JOIN Clientes c ON c.id = t.idClienteDestino
    LEFT JOIN (
      SELECT
        NumPedido,
        SUM(CASE
              WHEN CobrarEnvio = 0
                   AND Eliminado = 0
                   AND not_invoice = 0
              THEN Cantidad
              ELSE 0
            END) AS CantServicios
      FROM Ventas
      GROUP BY NumPedido
    ) vv ON vv.NumPedido = t.CodigoSeguimiento
    WHERE
      t.Debe > 0
      AND t.Facturado = 0
      AND t.Eliminado = 0
      AND (t.idClienteOrigen = '$id' OR t.idClienteDestino = '$id')";
  } else {

    // base mínima y rango opcional
    $whereFechaTC = "";
    if ($desde !== '' && $hasta !== '') {
      $desde_sql = $mysqli->real_escape_string($desde);
      $hasta_sql = $mysqli->real_escape_string($hasta);
      $whereFechaTC .= " AND t.Fecha >= '$desde_sql' AND t.Fecha <= '$hasta_sql' ";
    }

    // ACTUALIZA t.Visitas con tope 2, usando Estados válidos
    $sqlUpdVisitas = "UPDATE TransClientes t
    JOIN (
    SELECT
      s.CodigoSeguimiento,
      LEAST(COUNT(*), 2) AS VisitasCalc
    FROM Seguimiento s
    JOIN TransClientes t2
      ON t2.CodigoSeguimiento = s.CodigoSeguimiento
      AND t2.Eliminado = 0
      AND t2.Facturado = 0
      " . str_replace('t.', 't2.', $whereFechaTC) . "
      AND (t2.idClienteOrigen = '$id' OR t2.idClienteDestino = '$id')
      WHERE
      s.Eliminado = 0
      AND TRIM(s.Estado) IN ('Entregado al Cliente','No se pudo entregar')
      GROUP BY s.CodigoSeguimiento
      ) x ON x.CodigoSeguimiento = t.CodigoSeguimiento SET t.Visitas = x.VisitasCalc
      WHERE
      t.Eliminado = 0
      AND t.Facturado = 0
      $whereFechaTC
      AND (t.idClienteOrigen = '$id' OR t.idClienteDestino = '$id')
    ";
    $mysqli->query($sqlUpdVisitas);

    $sql = "SELECT
    t.CodigoProveedor,
    t.ClienteDestino,
    t.DomicilioDestino,
    t.CodigoSeguimiento,
    t.Fecha,
    t.TipoDeComprobante,
    t.NumeroComprobante,
    t.Observaciones,
    t.Debe,
    t.Haber,
    t.ComprobanteF,
    t.NumeroF,
    t.id,
    IF(t.FormaDePago='Origen', t.idClienteOrigen, t.idClienteDestino) AS idCliente,
    t.FormaDePago,
    t.Entregado,
    t.Flex,
    t.Devuelto,
    t.Control_facturacion,
    COALESCE(t.Visitas, 0) AS CantidadVisitas,
    c.CodigoPostal AS CodigoPostal,
    COALESCE(vv.CantServicios, 0) AS CantServicios,
    CASE
      WHEN COALESCE(t.Visitas,0) > 1 AND COALESCE(vv.CantServicios,0) = 1 THEN 1
      ELSE 0
    END AS AlertaActualizar
    FROM TransClientes t
    LEFT JOIN Clientes c ON c.id = t.idClienteDestino
    LEFT JOIN (
      SELECT
        NumPedido,
        SUM(CASE
              WHEN CobrarEnvio = 0
                   AND Eliminado = 0
                   AND not_invoice = 0
              THEN Cantidad
              ELSE 0
            END) AS CantServicios
      FROM Ventas
      GROUP BY NumPedido
    ) vv ON vv.NumPedido = t.CodigoSeguimiento
    WHERE
      t.Debe > 0
      $whereFechaTC
      AND t.Facturado = 0
      AND t.Eliminado = 0
      AND (t.idClienteOrigen = '$id' OR t.idClienteDestino = '$id')";
  }

  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    if ($row['idCliente'] == $id) {
      $rows[] = $row; // cada fila incluye 'CantidadVisitas' (0, 1 o 2) y 'AlertaActualizar'
    }
  }
  echo json_encode(array('data' => $rows));
}


//TABLA FACTURACION X RECORRIDO
if (isset($_POST['FacturacionRecorridos'])) {

  $sql = "SELECT Ctasctes.id, Ctasctes.Fecha, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`, `Haber`, `NumeroFactura`, Ctasctes.Observaciones,
Ctasctes.Facturado,Recorridos.Nombre,Ctasctes.idLogistica,Logistica.Hora,Logistica.HoraRetorno FROM `Ctasctes` 
INNER JOIN Recorridos ON SUBSTR(TipoDeComprobante, 10, 12)=Recorridos.Numero AND FacturacionxRecorrido=1
LEFT JOIN Logistica ON Logistica.id=Ctasctes.idLogistica
WHERE `FacturacionxRecorrido`=1 AND Ctasctes.Eliminado=0 AND idCliente='$_POST[id]'";


  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

//TABLA GUIAS RECIBIDAS
if (isset($_POST['Recibidas'])) {
  $sql = "SELECT RazonSocial,DomicilioOrigen,CodigoSeguimiento,Fecha,TipoDeComprobante,NumeroComprobante,Observaciones,Debe,Haber,ComprobanteF,NumeroF,id,
  FormaDePago,Entregado,Facturado FROM TransClientes WHERE Eliminado=0 AND idClienteDestino='$_POST[id]' AND Haber=0";
  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

//TABLA GUIAS ENVIADAS
if (isset($_POST['Enviadas'])) {
  $sql = "SELECT ClienteDestino,DomicilioDestino,CodigoSeguimiento,Fecha,TipoDeComprobante,NumeroComprobante,Observaciones,Debe,Haber,ComprobanteF,NumeroF,id,
  FormaDePago,Entregado,Facturado FROM TransClientes WHERE Eliminado=0 AND idClienteOrigen='$_POST[id]' AND Haber=0";
  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}


if (isset($_POST['FacturacionProforma'])) {
  $sql = "SELECT Fecha,TipoDeComprobante,NumeroComprobante,Observaciones,Debe,Haber,ComprobanteF,NumeroF,id,ClienteDestino,CodigoSeguimiento,CodigoProveedor 
  FROM TransClientes WHERE Debe>0 AND Facturado=0 AND Eliminado=0 AND idClienteOrigen='$_POST[id]' OR idClienteDestino='$_POST[id]'";
  $Resultado = $mysqli->query($sql);
  $rows = array();
  $box = $_POST['Remitos'];
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    for ($i = 0; $i < count($box); $i++) {
      if ($row['id'] == $box[$i]) {
        $rows[] = $row;
      }
    }
  }
  echo json_encode(array('data' => $rows));
}

if (isset($_POST['FacturacionProformaDetalle'])) {

  $box = $_POST['Remitos'];
  $rowsr[] = $box;
  $exito = json_encode($rowsr);
  $exito = trim($exito, '[]');

  $sql = "SELECT TransClientes.ClienteDestino,idPedido,NumPedido,FechaPedido,Codigo,Titulo,Precio,Ventas.Cantidad,Comentario,Cliente,NumeroRepo,ImporteNeto,Iva3,Total,Ventas.CobrarEnvio from Ventas   
            INNER JOIN TransClientes ON TransClientes.CodigoSeguimiento=Ventas.NumPedido
            WHERE Ventas.Eliminado=0 AND TransClientes.id IN($exito)";

  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }

  echo json_encode(array('data' => $rows));
}



if (isset($_POST['FacturacionProformaRecorridos'])) {
  $sql = "SELECT `id`, `Fecha`, `TipoDeComprobante`, `NumeroVenta`, `Debe`,`Observaciones` FROM `Ctasctes` WHERE `FacturacionxRecorrido`=1 AND idCliente='$_POST[id]'";

  $Resultado = $mysqli->query($sql);
  $rows = array();
  $box = $_POST['Remitos'];
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    for ($i = 0; $i < count($box); $i++) {
      if ($row['id'] == $box[$i]) {
        $rows[] = $row;
      }
    }
  }
  echo json_encode(array('data' => $rows));
}

if (isset($_POST['CtaCte'])) {

  $idCliente = intval($_POST['id']);

  $sql = "
    SELECT 
        C.*,

        COALESCE((
            SELECT SUM(I.Importe)
            FROM Ctasctes_Imputaciones I
            WHERE I.idMovimientoOrigen = C.id
            AND I.Eliminado = 0
        ),0) AS AplicadoDebe,

        COALESCE((
            SELECT SUM(I.Importe)
            FROM Ctasctes_Imputaciones I
            WHERE I.idMovimientoDestino = C.id
            AND I.Eliminado = 0
        ),0) AS AplicadoHaber

    FROM Ctasctes C

    WHERE C.Eliminado='0'
    AND C.idCliente='$idCliente'
    AND C.Facturado='1'
    AND C.idFacturado='0'

    ORDER BY C.Fecha DESC
    ";

  $Resultado = $mysqli->query($sql);
  $rows = array();

  while ($row = $Resultado->fetch_assoc()) {

    $debe = floatval($row['Debe']);
    $haber = floatval($row['Haber']);

    if ($debe > 0) {

      $aplicado = floatval($row['AplicadoDebe']);
      $saldo = $debe - $aplicado;
    } else {

      $aplicado = floatval($row['AplicadoHaber']);
      $saldo = $haber - $aplicado;
    }

    $row['Aplicado'] = round($aplicado, 2);
    $row['SaldoMovimiento'] = round($saldo, 2);

    if ($saldo <= 0.01) {
      $row['EstadoAplicacion'] = "CANCELADO";
    } elseif ($aplicado > 0) {
      $row['EstadoAplicacion'] = "PARCIAL";
    } else {
      $row['EstadoAplicacion'] = "PENDIENTE";
    }

    $rows[] = $row;
  }

  echo json_encode(array("data" => $rows));
}


//CTAS CTES UNIFICADAS
if (isset($_POST['CtaCteUnificadas'])) {

  //BUSCO LOS CLIENTES RELACIONADOS
  $sql = "SELECT id FROM Clientes WHERE Relacion='$_POST[id]' and AdminEnvios='1'";
  $Resultado = $mysqli->query($sql);
  $rows = array();

  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row['id'];
  }
  $exito = json_encode($rows);
  $exito = trim($exito, '[]');

  $sql = "SELECT * FROM Ctasctes WHERE Eliminado='0' AND idCliente IN($exito) AND Facturado='1' AND idFacturado='0' ORDER BY Fecha DESC";
  $Resultado = $mysqli->query($sql);
  $rows = array();

  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

if (isset($_POST['Tarifas'])) {
  $sql = "SELECT ClientesyServicios.id,Titulo,MaxKm,PrecioPlano FROM ClientesyServicios INNER JOIN Productos ON ClientesyServicios.Servicio=Productos.id WHERE ClientesyServicios.NdeCliente='$_POST[id]'";
  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}


if (isset($_POST['Relaciones'])) {
  $sql = "SELECT id,AdminEnvios,idProveedor,nombrecliente,Celular,Direccion FROM Clientes WHERE Relacion='$_POST[id]'";
  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

//FACTURACION
if (isset($_POST['AdminEnvios'])) {

  // Escapar el valor de id para evitar SQL Injection
  $id_escapado = $mysqli->real_escape_string($_POST['id']);

  // Consulta SQL
  $sql = "SELECT id, nombrecliente FROM Clientes WHERE Relacion = '$id_escapado' AND AdminEnvios = '1'";
  $Resultado = $mysqli->query($sql);

  if ($Resultado) {
    $cuantos = $Resultado->num_rows;
    $inicio = 1;
    $id = ''; // Inicializar la variable

    // Recorrer los resultados
    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
      $id .= $row['nombrecliente'];  // Concatenar nombrecliente

      if ($inicio < $cuantos) {
        $id .= ' | ';  // Agregar separador si no es el último
      }
      $inicio++;
    }

    // Verificar si 'nombrecliente' existe en el array $_POST
    $nombrecliente = isset($_POST['nombrecliente']) ? $_POST['nombrecliente'] : '';

    // Concatenar con el valor de $id
    $idfinal = $nombrecliente . ' | ' . $id;

    // Devolver la respuesta en JSON
    echo json_encode(array('success' => 1, 'data' => $id, 'total' => $cuantos));
  } else {
    // Si hay un error en la consulta
    echo json_encode(array('success' => 0, 'message' => 'Error en la consulta SQL: ' . $mysqli->error));
  }
}

if (isset($_POST['TotalRemitos'])) {
  //REMITOS DE ENVIO Y RECEPCION PENDIENTES DESDE EL INICIO DE LOS TIEMPOS
  $cuantos = 0;
  $idCliente = $_POST['id'];
  $sqlbuscopendientes0 = $mysqli->query("Select SUM(Debe)as total FROM Ctasctes WHERE Eliminado='0' AND idCliente='$_POST[id]' AND Debe>0 AND Facturado='0'"); //ID 2990 ES EL ID DESDE EL QUE COMENZAMOS A CARGAR LOS REMITOS EN LA CTA CTE DEL CLIENTE 
  $buscopendientes = $sqlbuscopendientes0->fetch_array(MYSQLI_ASSOC);

  $sql = $mysqli->query("SELECT SUM(Debe-Haber)as Total FROM Ctasctes WHERE idCliente='$_POST[id]' AND Eliminado=0 and idFacturado=0");
  $row = $sql->fetch_array(MYSQLI_ASSOC);

  echo json_encode(array('success' => 1, 'totalenviados' => $buscopendientes['total'], 'saldo_total' => $row['Total']));
}


if (isset($_POST['Estadisticas'])) {

  $sql = $mysqli->query("SELECT MONTHNAME(v.Fecha) AS Mes,SUM(v.Debe) AS Total FROM TransClientes v WHERE YEAR(v.fecha) = YEAR(CURRENT_DATE()) 
AND IF(FormaDePago='Origen',v.idClienteOrigen,v.idClienteDestino)='$_POST[idCliente]' AND Eliminado='0' GROUP BY Mes ORDER BY YEAR(v.Fecha),MONTH(v.Fecha)");

  $Mes = array();
  $Total = array();

  while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
    $Mes[] = $row['Mes'];
    $Total[] = $row['Total'];
  }

  echo json_encode(array('x' => $Mes, 'y' => $Total));
}

if (isset($_POST['EstadisticasEnvios'])) {

  $sql = $mysqli->query("SELECT MONTHNAME(v.FechaEntrega) AS Mes,COUNT(v.id) AS Total FROM TransClientes v 
  WHERE YEAR(v.FechaEntrega) = YEAR(CURRENT_DATE()-1) AND v.Eliminado='0' AND idClienteOrigen='$_POST[idCliente]' AND v.Haber=0 GROUP BY Mes ORDER BY YEAR(v.FechaEntrega-1),MONTH(v.FechaEntrega)");
  $Mes = array();
  $Total = array();

  while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
    $Mes[] = $row['Mes'];
    $Total[] = $row['Total'];
  }

  echo json_encode(array('x' => $Mes, 'y' => $Total));
}

if (isset($_POST['EstadisticasEnvios'])) {
  if ($_POST['EstadisticasEnvios'] == 2) {
    $result = $mysqli->query("SELECT CONCAT(' semana ', FLOOR(((DAY(v.Fecha) - 1) / 7) + 1)) `name`, COUNT(IF(Flex=0,1,0)) AS Total,SUM(Flex)as Flex FROM TransClientes v 
      WHERE YEAR(v.Fecha) = YEAR(CURRENT_DATE()-1) AND MONTH(v.Fecha)=MONTH(CURRENT_DATE) AND v.Eliminado='0' AND idClienteOrigen='$_POST[idCliente]' AND v.Haber=0 
      GROUP BY 1 order by `name`");


    $series = array();

    if ($result->num_rows > 0) {
      // Loop through each row and construct the series array
      while ($row = $result->fetch_assoc()) {
        $name[] = str_replace('"', '', $row["name"]);
        $data[] = intval($row["Total"]);
        $flex[] = intval($row["Flex"]);
      }
    }

    // Convert the array to JSON format for easier use in JavaScript
    $series_json = json_encode(array('name' => $name, 'data' => $data, 'flex' => $flex, JSON_UNESCAPED_UNICODE));

    echo $series_json;
  }
}

// COMPROBANTES FISCALES (FACTURAS A/B) DE UN CLIENTE, PARA ELEGIR CONTRA CUAL EMITIR UNA NC/ND
if (isset($_POST['FacturasCliente'])) {
  $idCliente = intval($_POST['id']);

  $rows = array();
  if ($idCliente > 0) {
    $stmt = $mysqli->prepare(
      "SELECT id, Fecha, TipoDeComprobante, NumeroFactura, Debe
       FROM Ctasctes
       WHERE idCliente = ? AND Eliminado = 0
         AND TipoDeComprobante IN ('FACTURAS A','FACTURAS B')
       ORDER BY Fecha DESC, id DESC"
    );
    $stmt->bind_param("i", $idCliente);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $rows[] = $row;
    }
    $stmt->close();
  }

  echo json_encode(array('data' => $rows));
}

// SERVICIOS (REMITOS) TODAVIA VINCULADOS A UNA FACTURA, PARA EL PICKER DE
// "SERVICIOS ESPECIFICOS" AL EMITIR UNA NOTA DE CREDITO. Si ya se liberó
// alguno con una NC previa, no aparece más acá (idFacturado ya quedó NULL).
if (isset($_POST['ServiciosFactura'])) {
  $idFacturado = intval($_POST['idFacturado']);

  $rows = array();
  $esRecorrido = false;

  if ($idFacturado > 0) {
    $stmtTipo = $mysqli->prepare("SELECT FacturacionxRecorrido FROM Ctasctes WHERE id = ? AND Eliminado = 0 LIMIT 1");
    $stmtTipo->bind_param("i", $idFacturado);
    $stmtTipo->execute();
    $filaTipo = $stmtTipo->get_result()->fetch_assoc();
    $stmtTipo->close();
    $esRecorrido = $filaTipo && intval($filaTipo['FacturacionxRecorrido']) === 1;

    if (!$esRecorrido) {
      $stmt = $mysqli->prepare(
        "SELECT TC.id, TC.Fecha, TC.CodigoSeguimiento, TC.ClienteDestino, CT.Debe
         FROM Ctasctes CT
         JOIN TransClientes TC ON TC.id = CT.idTransClientes
         WHERE CT.Eliminado = 0 AND CT.idFacturado = ? AND CT.Haber = 0 AND CT.Debe > 0
         ORDER BY TC.Fecha, TC.id"
      );
      $stmt->bind_param("i", $idFacturado);
      $stmt->execute();
      $result = $stmt->get_result();
      while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
      }
      $stmt->close();
    }
  }

  echo json_encode(array('data' => $rows, 'esRecorrido' => $esRecorrido));
}
