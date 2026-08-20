<?php

declare(strict_types=1);

// Reescritura: el original usaba mysql_query() (eliminado en PHP7) y requería
// ../../../conexion.php (ya no existe) — no podía funcionar bajo PHP8.

require_once __DIR__ . '/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const VEH_COLS = ['Marca', 'Modelo', 'Dominio', 'Color', 'Año', 'Motor', 'Chasis'];
const VEH_WIDTHS = [30, 40, 25, 25, 18, 30, 32];
const VEH_ALIGNS = ['L', 'L', 'C', 'L', 'C', 'L', 'L'];

class VehiculosPDF extends HdrPdfBase
{
    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $anchos = $this->anchosEscalados(VEH_WIDTHS);
        $this->SetWidths($anchos);
        $this->SetAligns(VEH_ALIGNS);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (VEH_COLS as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, VEH_ALIGNS[$i] === 'C' ? 'C' : 'L', true);
        }
        $this->Ln();
        $this->SetTextColor(...$p['darkText']);
    }

    public function Header(): void
    {
        global $totalVehiculos;

        $this->drawHeaderBase(
            'VEHICULOS DE FLOTA',
            '',
            [
                ['Fecha:', date('d/m/Y')],
                ['Vehiculos activos:', (string)($totalVehiculos ?? 0)],
            ]
        );

        $this->Ln(2);
        $this->drawTableHeader();
    }
}

$vehiculos = db_fetch_all(
    $mysqli,
    "SELECT Marca, Modelo, Dominio, Color, Ano, Motor, Chasis
       FROM Vehiculos
      WHERE Activo = 'Si'
      ORDER BY Marca, Modelo"
);

$totalVehiculos = count($vehiculos);

$pdf = new VehiculosPDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = 'Vehiculos de Flota - Triangular S.A.';
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 16);
$pdf->AddPage();

$paleta = hdrPaleta();
$fill = false;

foreach ($vehiculos as $v) {
    $pdf->Row([
        (string)($v['Marca'] ?? ''),
        (string)($v['Modelo'] ?? ''),
        (string)($v['Dominio'] ?? ''),
        (string)($v['Color'] ?? ''),
        (string)($v['Ano'] ?? ''),
        (string)($v['Motor'] ?? ''),
        (string)($v['Chasis'] ?? ''),
    ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);
    $fill = !$fill;
}

$pdf->Output('I', 'VehiculosFlota.pdf');
