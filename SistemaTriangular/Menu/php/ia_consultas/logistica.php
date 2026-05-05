<?php

function consultarLogistica($mysqli, $ctx)
{
    $q = $ctx['q'];

    $clienteDetectado = detectarClienteConsulta($mysqli, $q);

    if ($clienteDetectado) {
        return false; // deja que lo maneje ventas
    }

    $inicioMes = date('Y-m-01');
    $finMes = date('Y-m-t');

    if (strpos($q, 'rendicion') !== false || strpos($q, 'rendición') !== false) {
        $total = contar($mysqli, "
            SELECT COUNT(*) AS total
            FROM Logistica
            WHERE Eliminado = 0
              AND Rendicion = 0
              AND IFNULL(Costo_rendicion, 0) > 0
        ");

        salir([
            'success' => 1,
            'respuesta' => "Hay <strong>$total</strong> rendiciones controladas pendientes de facturar.",
            'detalle' => "Criterio: Logistica.Rendicion = 0 y Costo_rendicion > 0."
        ]);
    }
    if (contieneAlguna($q, ['facturado', 'facturacion', 'facturación']) && contieneAlguna($q, ['mes', 'mensual'])) {
        if (!isset($_SESSION['Nivel']) || (int)$_SESSION['Nivel'] !== 1) {
            salir([
                'success' => 0,
                'msg' => 'No tenés permisos para consultar facturación.'
            ]);
        }

        $total = sumar($mysqli, "
            SELECT SUM(IFNULL(Debe, 0)) AS total
            FROM TransClientes
            WHERE Eliminado = 0
              AND Facturado = 1
              AND Fecha >= '$inicioMes'
              AND Fecha <= '$finMes'
        ");

        salir([
            'success' => 1,
            'respuesta' => "En el mes se facturaron aproximadamente <strong>" . dinero($total) . "</strong>.",
            'detalle' => "Criterio: TransClientes.Facturado = 1 y Fecha entre $inicioMes y $finMes."
        ]);
    }

    return false;
}
