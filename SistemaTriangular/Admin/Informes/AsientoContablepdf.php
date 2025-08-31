<?php
session_start();
require('../../fpdf/fpdf.php');
require('../../../conexion.php');

class PDF extends FPDF
{
    var $widths;
    var $aligns;

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Row($data)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = 5 * $nb;
        $this->CheckPageBreak($h);
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 5, $data[$i], 0, $a, 'true');
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") {
            $nb--;
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
            }
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }

    function Header()
    {
        $cliente = $_SESSION['ClienteActivo'];
        $NumeroRepo = $_GET['NR'];
        $con = new DB;
        $historial = $con->conectar();    
        $Id = $_GET['NR'];
        $strConsulta = "SELECT * FROM Tesoreria WHERE NumeroAsiento='$Id' AND Eliminado=0";
        $Dato = mysql_query($strConsulta);
        while ($row = mysql_fetch_row($Dato)) {
            $Codigo = $row[0];
            $Fecha = $row[1];
            $Id = $row[11];    
            $arrayfecha = explode('-', $Fecha, 3);
            $Fecha2 = $arrayfecha[2] . "/" . $arrayfecha[1] . "/" . $arrayfecha[0];
        }

        $this->SetFont('Arial', 'B', 10);
        $this->Text(20, 14, 'Triangular S.A.', 0, 'C', 0);
        $this->SetFont('Arial', '', 10);
        $this->Text(20, 19, 'Cuit: 30-71534494-3', 0, 'C', 0);
        $this->Text(20, 24, utf8_decode('Domicilio: Av. Simón LaPlace 5442'), 0, 'C', 0);
        $this->Text(20, 29, 'www.caddy.com.ar', 0, 'C', 0);
        $this->Text(150, 14, utf8_decode('Córdoba ' . $Fecha2), 0, 'C', 0);

        $NAsiento = $_GET['NR'];
        $SumaAsiento = "SELECT SUM(Debe-Haber) AS TotalAsiento FROM Tesoreria WHERE NumeroAsiento=$NAsiento AND Eliminado=0";
        $SumaAsientoConsulta = mysql_query($SumaAsiento);
        $row = mysql_fetch_array($SumaAsientoConsulta);
        setlocale(LC_ALL, 'es_AR');
        $Total = money_format('%i', $row[TotalAsiento]);

        $this->Ln(20);
        $this->SetFont('Arial', '', 10);
        $this->Text(150, 24, 'Id: ' . $_GET['NR'], 0, 'C', 0);
        $this->SetFont('Arial', 'B', 10);
        $this->Text(150, 30, 'Saldo Control: ' . $Total, 0, 'C', 0);

        $this->Ln(20);
        $this->SetFont('Arial', 'B', 16);
        $this->Text(70, 44, 'ASIENTO CONTABLE '.$NAsiento, 0, 'C', 0);
        $this->Line(20, 38, 190, 38);
        $this->Line(20, 45, 190, 45);
        $this->Ln(0);
        
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(5, 7, '', 0, 0); // Celda vacía para el desplazamiento
        $this->SetX($this->GetX() + 5); // Desplazamiento de 20 unidades hacia la derecha
        $this->Cell(130, 7,utf8_decode('Descripción'), 1, 0, 'C');
        $this->Cell(20, 7, 'Debe', 1, 0, 'C');
        $this->Cell(20, 7, 'Haber', 1, 1, 'C');
    }

    function Footer()
    {
        $this->SetY(-45);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(100, 10, 'Firma Recibido', 0, 0, 'L');
        $this->SetX(90);
        $this->Cell(100, 10, utf8_decode('Aclaración Nombre'), 0, 0, 'L');
        $this->SetX(150);
        $this->Cell(100, 10, 'D.N.I.', 0, 0, 'L');

        $this->SetY(-35);
        $this->Cell(100, 10, utf8_decode('Asiento contable emitido por el sistema de gestión de Triangular S.A., '), 0, 0, 'L');

        $this->SetY(-15);
        $this->Cell(100, 10, 'Recibo Triangular S.A.', 0, 0, 'L');
        $this->SetX(90);
        $this->Cell(100, 10, 'www.caddy.com.ar', 0, 0, 'L');
        $this->SetX(170);
        $this->Cell(100, 10, 'Usuario:' . $_SESSION['Usuario'], 0, 0, 'L');
    }
}

$cliente = $_SESSION['ClienteActivo'];
$NumeroRepo = $_GET['NR'];

$con = new DB;
$historial = $con->conectar();    
$Id = $_GET['NR'];
$strConsulta = "SELECT * FROM Tesoreria WHERE NumeroAsiento='$Id' AND Eliminado=0 ORDER BY id";
$pacientes = mysql_query($strConsulta);
$NumerodeRegistros = mysql_num_rows($pacientes);

$pdf = new PDF('P', 'mm', 'Letter');
$pdf->Open();
$pdf->AddPage();
$pdf->SetMargins(20, 20, 20);
$pdf->Ln(0);

$observaciones=array();
$observacionesTexto = '';

while ($fila = mysql_fetch_array($pacientes)) {
    $SQL_TESORERIA = "SELECT * FROM AnticiposProveedores WHERE id='" . $fila['idAnticiposProveedores'] . "' AND Eliminado=0";
    $DATO_ANTICIPO = mysql_query($SQL_TESORERIA);
    $DATO = mysql_fetch_array($DATO_ANTICIPO);
    
    $SQL_FORMADEPAGO = "SELECT * FROM FormaDePago WHERE CuentaContable='".$fila['FormaDePago']."'";
    $DATO_SQL_FORMADEPAGO = mysql_query($SQL_FORMADEPAGO);
    $DATO_FORMADEPAGO = mysql_fetch_array($DATO_SQL_FORMADEPAGO);
    
    if($DATO_FORMADEPAGO['FormaDePago']){
        $FormaDePago= ' [F.P.]: '.$DATO_FORMADEPAGO['FormaDePago'];    
    }else{
        $FormaDePago= '';
    }    

    if (!in_array($fila['Observaciones'], $observaciones)) {
        $observaciones[] = $fila['Observaciones'];
        $observacionesTexto .= $fila['Observaciones'] . "\n"; // Agregar observaciones al texto
    }

    if($DATO['id']){
        $Descripcion = utf8_decode($fila['Cuenta'].' '.$fila['NombreCuenta'] .$FormaDePago.' [R.S.]: ' . $DATO['RazonSocial']);
    }else{
        $Descripcion = utf8_decode($fila['Cuenta'].' '.$fila['NombreCuenta'] .$FormaDePago);        
    }

    $Debe = number_format($fila['Debe'], 2, ',', '.');
    $Haber = number_format($fila['Haber'], 2, ',', '.');
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(130, 7, $Descripcion, 1,0);
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(20, 7, $Debe, 1, 0, 'R');
    $pdf->Cell(20, 7, $Haber, 1, 1, 'R');
}

$strConsulta = "SELECT SUM(Debe) AS TotalDebe, SUM(Haber) AS TotalHaber FROM Tesoreria WHERE NumeroAsiento='$Id' AND Eliminado=0";
$pacientes = mysql_query($strConsulta);
$fila = mysql_fetch_array($pacientes);
$TotalDebe = number_format($fila['TotalDebe'], 2, ',', '.');
$TotalHaber = number_format($fila['TotalHaber'], 2, ',', '.');

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(130, 7, 'Total:', 1);
$pdf->Cell(20, 7, $TotalDebe, 1, 0, 'R');
$pdf->Cell(20, 7, $TotalHaber, 1, 1, 'R');

$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(170, 7, utf8_decode('Observaciones: ' . $observacionesTexto), 1); // Imprimir las observaciones con MultiCell

$pdf->Output();
?>
