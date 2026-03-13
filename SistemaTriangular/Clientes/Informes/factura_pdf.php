<?php
require_once __DIR__ . '/../../fpdf/fpdf.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';
require_once __DIR__ . '/../../phpqrcode/qrlib.php';

function pdf_text($texto)
{
    return mb_convert_encoding((string)$texto, 'ISO-8859-1', 'UTF-8');
}

function generarFacturaPDF($idCtasctes, $rutaSalida)
{
    global $mysqli;

    $idCtasctes = intval($idCtasctes);

    $sql = $mysqli->query("
        SELECT 
            CT.id,
            CT.Fecha,
            CT.TipoDeComprobante,
            CT.NumeroFactura,
            CT.RazonSocial,
            CT.Cuit,
            CT.Debe,
            CT.Haber,
            CT.idCliente,
            CT.idFacturado,
            C.Direccion,
            C.Ciudad,
            C.Provincia,
            C.CodigoPostal,
            C.Telefono,
            C.Celular,
            C.Mail
        FROM Ctasctes CT
        LEFT JOIN Clientes C ON C.id = CT.idCliente
        WHERE CT.id = '{$idCtasctes}'
        LIMIT 1
    ");

    if (!$sql) {
        throw new Exception('Error SQL: ' . $mysqli->error);
    }

    $row = $sql->fetch_assoc();

    if (!$row) {
        throw new Exception('No se encontró la factura');
    }

    $detalle = [];
    $total = 0;
    $esRecorrido = false;

    // Detalle normal de remitos
    $sqlDetalle = $mysqli->query("
        SELECT 
            Fecha,
            TipoDeComprobante,
            NumeroComprobante,
            ClienteDestino,
            CodigoSeguimiento,
            CodigoProveedor,
            Debe
        FROM TransClientes
        WHERE id IN (
            SELECT idTransClientes
            FROM Ctasctes
            WHERE Eliminado = 0
            AND idFacturado = '" . intval($row['id']) . "'
        )
        AND Eliminado = 0
        AND Debe > 0
    ");

    if (!$sqlDetalle) {
        throw new Exception('Error SQL detalle remitos: ' . $mysqli->error);
    }

    while ($r = $sqlDetalle->fetch_assoc()) {
        $detalle[] = $r;
        $total += (float)$r['Debe'];
    }

    // Si no hay remitos, probar recorridos
    if (empty($detalle)) {
        $sqlRecorridos = $mysqli->query("
            SELECT 
                Fecha,
                TipoDeComprobante,
                NumeroVenta,
                Observaciones,
                Debe
            FROM Ctasctes
            WHERE Eliminado = 0
            AND idFacturado = '" . intval($row['id']) . "'
            AND Debe > 0
        ");

        if (!$sqlRecorridos) {
            throw new Exception('Error SQL detalle recorridos: ' . $mysqli->error);
        }

        while ($r = $sqlRecorridos->fetch_assoc()) {
            $detalle[] = $r;
            $total += (float)$r['Debe'];
            $esRecorrido = true;
        }
    }

    if ($total <= 0) {
        $total = (float)$row['Debe'];
    }

    // =========================
    // DATOS AFIP / QR
    // =========================
    $afip = null;
    $rutaQR = '';

    $stmtAfip = $mysqli->prepare("SELECT IvaVentas.*
        FROM IvaVentas
        INNER JOIN Ctasctes ON Ctasctes.idIvaVentas = IvaVentas.id
        WHERE Ctasctes.id = ?
        LIMIT 1");

    if ($stmtAfip) {
        $stmtAfip->bind_param("i", $idCtasctes);
        $stmtAfip->execute();
        $resAfip = $stmtAfip->get_result();
        $afip = $resAfip->fetch_assoc();
        $stmtAfip->close();
    }

    if (!empty($afip['id'])) {
        $TipoComp = 0;

        $stmtTipo = $mysqli->prepare("
            SELECT Codigo
            FROM AfipTipoDeComprobante
            WHERE Descripcion = ?
            LIMIT 1
        ");

        if ($stmtTipo) {
            $stmtTipo->bind_param("s", $afip['TipoDeComprobante']);
            $stmtTipo->execute();
            $resTipo = $stmtTipo->get_result();
            $rowTipo = $resTipo->fetch_assoc();
            if ($rowTipo && isset($rowTipo['Codigo'])) {
                $TipoComp = (int)$rowTipo['Codigo'];
            }
            $stmtTipo->close();
        }

        $Ncomp = explode('-', (string)$afip['NumeroComprobante']);
        $PtoVta = isset($Ncomp[0]) ? (int)$Ncomp[0] : 0;
        $Numero = isset($Ncomp[1]) ? (int)$Ncomp[1] : 0;
        $Documento = preg_replace("/[^0-9]/", "", (string)$afip['Cuit']);

        $datos_cmp_base_64 = json_encode([
            "ver" => 1,
            "fecha" => $afip['Fecha'],
            "cuit" => 30715344943,
            "ptoVta" => $PtoVta,
            "tipoCmp" => $TipoComp,
            "nroCmp" => $Numero,
            "importe" => (float)$afip['Total'],
            "moneda" => "PES",
            "ctz" => 1,
            "tipoDocRec" => 80,
            "nroDocRec" => (int)$Documento,
            "tipoCodAut" => "E",
            "codAut" => (int)$afip['CAE']
        ]);

        $to_qr = 'https://www.afip.gob.ar/fe/qr/?p=' . base64_encode($datos_cmp_base_64);

        $dirTmp = __DIR__ . '/../../archivos_tmp/';
        if (!is_dir($dirTmp)) {
            @mkdir($dirTmp, 0777, true);
        }

        if (!is_dir($dirTmp) || !is_writable($dirTmp)) {
            throw new Exception('No se pudo crear/escribir la carpeta temporal para QR: ' . $dirTmp);
        }

        $rutaQR = $dirTmp . 'qr_afip_' . $idCtasctes . '_' . time() . '.png';
        QRcode::png($to_qr, $rutaQR, 'L', 4, 2);
    }

    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    $logo = __DIR__ . '/../../images/LogoCaddy.png';
    if (file_exists($logo)) {
        $pdf->Image($logo, 10, 10, 40);
    }

    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 10, pdf_text($row['TipoDeComprobante']), 0, 1, 'R');

    $pdf->Ln(8);

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(100, 6, 'Caddy Logistica', 0, 0, 'L');
    $pdf->Cell(90, 6, 'N: ' . $row['NumeroFactura'], 0, 1, 'R');

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 6, 'Fecha: ' . date('d/m/Y', strtotime($row['Fecha'])), 0, 1, 'L');

    $pdf->Ln(4);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, pdf_text('Cliente'), 0, 1);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, pdf_text($row['RazonSocial']), 0, 1);
    $pdf->Cell(0, 6, 'CUIT: ' . $row['Cuit'], 0, 1);
    $pdf->Cell(0, 6, pdf_text('Dirección: ' . $row['Direccion']), 0, 1);
    $pdf->Cell(0, 6, pdf_text('Localidad: ' . $row['Ciudad'] . ' - ' . $row['Provincia']), 0, 1);
    $pdf->Cell(0, 6, 'Telefono: ' . $row['Celular'], 0, 1);
    $pdf->Cell(0, 6, 'Mail: ' . $row['Mail'], 0, 1);

    $pdf->Ln(8);

    $pdf->SetFont('Arial', 'B', 10);

    if ($esRecorrido) {
        $pdf->Cell(25, 8, 'Fecha', 1, 0, 'C');
        $pdf->Cell(45, 8, 'Tipo', 1, 0, 'C');
        $pdf->Cell(35, 8, 'Comprobante', 1, 0, 'C');
        $pdf->Cell(45, 8, 'Observaciones', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Importe', 1, 1, 'C');
    } else {
        $pdf->Cell(25, 8, 'Fecha', 1, 0, 'C');
        $pdf->Cell(35, 8, 'Seguimiento', 1, 0, 'C');
        $pdf->Cell(55, 8, 'Cliente Destino', 1, 0, 'C');
        $pdf->Cell(35, 8, 'Cod. Cliente', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Importe', 1, 1, 'C');
    }

    $pdf->SetFont('Arial', '', 9);

    if (!empty($detalle)) {
        foreach ($detalle as $item) {
            $fecha = '';
            if (!empty($item['Fecha']) && $item['Fecha'] != '0000-00-00') {
                $fecha = date('d/m/Y', strtotime($item['Fecha']));
            }

            if ($esRecorrido) {
                $pdf->Cell(25, 7, $fecha, 1, 0, 'C');
                $pdf->Cell(45, 7, pdf_text(substr($item['TipoDeComprobante'], 0, 24)), 1, 0, 'L');
                $pdf->Cell(35, 7, pdf_text($item['NumeroVenta']), 1, 0, 'L');
                $pdf->Cell(45, 7, pdf_text(substr($item['Observaciones'], 0, 28)), 1, 0, 'L');
                $pdf->Cell(40, 7, '$ ' . number_format((float)$item['Debe'], 2, ',', '.'), 1, 1, 'R');
            } else {
                $pdf->Cell(25, 7, $fecha, 1, 0, 'C');
                $pdf->Cell(35, 7, pdf_text($item['CodigoSeguimiento']), 1, 0, 'L');
                $pdf->Cell(55, 7, pdf_text(substr($item['ClienteDestino'], 0, 28)), 1, 0, 'L');
                $pdf->Cell(35, 7, pdf_text($item['CodigoProveedor']), 1, 0, 'L');
                $pdf->Cell(40, 7, '$ ' . number_format((float)$item['Debe'], 2, ',', '.'), 1, 1, 'R');
            }
        }
    } else {
        $pdf->Cell(150, 7, pdf_text('Comprobante'), 1, 0, 'L');
        $pdf->Cell(40, 7, '$ ' . number_format((float)$total, 2, ',', '.'), 1, 1, 'R');
    }

    $pdf->Ln(8);

    $neto = $total / 1.21;
    $iva  = $total - $neto;

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(140, 7, 'Total Neto', 0, 0, 'R');
    $pdf->Cell(50, 7, '$ ' . number_format($neto, 2, ',', '.'), 0, 1, 'R');

    $pdf->Cell(140, 7, 'IVA 21%', 0, 0, 'R');
    $pdf->Cell(50, 7, '$ ' . number_format($iva, 2, ',', '.'), 0, 1, 'R');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(140, 9, 'Total Comprobante', 0, 0, 'R');
    $pdf->Cell(50, 9, '$ ' . number_format($total, 2, ',', '.'), 0, 1, 'R');

    // =========================
    // BLOQUE AFIP
    // =========================
    if (!empty($afip['id']) && $rutaQR !== '' && file_exists($rutaQR)) {
        $pdf->Ln(10);

        $yBase = $pdf->GetY();

        // QR
        $pdf->Image($rutaQR, 10, $yBase, 28);

        // Logo AFIP si existe
        $logoAfip = __DIR__ . '/../../afip.php/images/qr.png';
        if (file_exists($logoAfip)) {
            $pdf->Image($logoAfip, 45, $yBase, 20);
        }

        // Texto autorización
        $pdf->SetXY(68, $yBase);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(90, 6, pdf_text('Comprobante Autorizado'), 0, 1, 'L');

        $pdf->SetX(68);
        $pdf->SetFont('Arial', '', 8);
        $pdf->MultiCell(
            120,
            4,
            pdf_text('Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación.'),
            0,
            'L'
        );

        // Datos CAE
        $pdf->SetXY(150, $yBase);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 6, 'CAE:', 0, 1, 'L');

        $pdf->SetX(150);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(50, 5, pdf_text($afip['CAE']), 0, 1, 'L');

        $pdf->SetX(150);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 6, pdf_text('Vto. CAE:'), 0, 1, 'L');

        $pdf->SetX(150);
        $pdf->SetFont('Arial', '', 9);
        $vtoCae = !empty($afip['FechaVencimientoCAE']) ? date('d/m/Y', strtotime($afip['FechaVencimientoCAE'])) : '';
        $pdf->Cell(50, 5, pdf_text($vtoCae), 0, 1, 'L');

        $pdf->Ln(20);
    }

    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 6, 'Generado el ' . date('d/m/Y H:i:s'), 0, 1, 'L');

    $pdf->Output('F', $rutaSalida);

    if ($rutaQR !== '' && file_exists($rutaQR)) {
        @unlink($rutaQR);
    }

    return [
        'success' => 1,
        'numero' => $row['NumeroFactura'],
        'ruta' => $rutaSalida
    ];
}
