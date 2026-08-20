<?php

// Diagnostico temporal: "No se pudo actualizar el orden del recorrido" en
// sandbox al abrir una Hoja de Ruta - sin esto no hay forma de ver el error
// real. Sacar despues de identificar la causa.
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode(['fatal' => $err]);
    }
});

require_once('../../../Conexion/Conexioni.php');

if (isset($_POST['Renderizar'])) {
    $Recorrido = trim((string)($_POST['Recorrido'] ?? ''));

    // Antes esto hacia TRUNCATE TABLE Roadmap_end (borraba TODA la tabla, para
    // TODOS los recorridos de toda la empresa) y reconstruia el listado
    // completo de pendientes de toda la empresa (miles de filas, un INSERT
    // por fila) en cada carga de esta pantalla o cada "Restablecer Orden" -
    // aunque Roadmap_end solo se lee despues filtrada por UN Recorrido a la
    // vez (routes.php, datos_hojaderuta.php). Ademas, al ser una tabla
    // compartida sin scope, dos personas viendo recorridos distintos al
    // mismo tiempo podian pisarse: el TRUNCATE de una dejaba la tabla vacia
    // mientras la otra la estaba leyendo. Se escala todo a un Recorrido
    // puntual (llega por POST) para que sea rapido y no colisione entre
    // usuarios.
    if ($Recorrido === '') {
        exit;
    }

    $stmtDelete = $mysqli->prepare('DELETE FROM Roadmap_end WHERE Recorrido = ?');
    $stmtDelete->bind_param('s', $Recorrido);
    $stmtDelete->execute();
    $stmtDelete->close();

    $stmtPendientes = $mysqli->prepare(
        'SELECT HojaDeRuta.Posicion, HojaDeRuta.Posicion_retiro, TransClientes.Recorrido AS Recorrido,
                TransClientes.idClienteOrigen AS idOrigen, TransClientes.idClienteDestino AS idDestino,
                TransClientes.CodigoSeguimiento, TransClientes.id AS idTransClientes,
                HojaDeRuta.id AS idHojaDeRuta, TransClientes.Retirado
           FROM HojaDeRuta
          INNER JOIN TransClientes ON HojaDeRuta.idTransClientes = TransClientes.id
          WHERE TransClientes.Eliminado = 0 AND Entregado = 0 AND Haber = 0
            AND TransClientes.Devuelto = 0 AND TransClientes.Recorrido = ?'
    );
    $stmtPendientes->bind_param('s', $Recorrido);
    $stmtPendientes->execute();
    $pendientes = $stmtPendientes->get_result();

    $stmtInsert = $mysqli->prepare(
        'INSERT INTO Roadmap_end (idDestino, CodigoSeguimiento, Retirado, Recorrido, idHojaderuta, idTransClientes, Entrega, Posicion)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    while ($row = $pendientes->fetch_assoc()) {
        if ($row['Retirado'] == 1) {
            // CARGO UNA VISITA PARA ENTREGA
            $entrega = 1;
            $stmtInsert->bind_param(
                'isiiiiii',
                $row['idDestino'],
                $row['CodigoSeguimiento'],
                $row['Retirado'],
                $row['Recorrido'],
                $row['idHojaDeRuta'],
                $row['idTransClientes'],
                $entrega,
                $row['Posicion']
            );
            $stmtInsert->execute();
        } else {
            // CARGO UNA VISITA PARA RETIRO Y UNA PARA ENTREGA
            $entregaRetiro = 0;
            $stmtInsert->bind_param(
                'isiiiiii',
                $row['idOrigen'],
                $row['CodigoSeguimiento'],
                $row['Retirado'],
                $row['Recorrido'],
                $row['idHojaDeRuta'],
                $row['idTransClientes'],
                $entregaRetiro,
                $row['Posicion_retiro']
            );
            $stmtInsert->execute();

            $entrega = 1;
            $stmtInsert->bind_param(
                'isiiiiii',
                $row['idDestino'],
                $row['CodigoSeguimiento'],
                $row['Retirado'],
                $row['Recorrido'],
                $row['idHojaDeRuta'],
                $row['idTransClientes'],
                $entrega,
                $row['Posicion']
            );
            $stmtInsert->execute();
        }
    }

    $stmtInsert->close();
}
