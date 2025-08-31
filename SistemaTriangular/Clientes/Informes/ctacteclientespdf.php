<?php
require('../../fpdf/fpdf.php');
include_once('../../Conexion/Conexioni.php');
header("Content-Type: text/html; charset=iso-8859-1 ");

class PDF extends FPDF
{
    var $widths;
    var $aligns;

    function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns = $a;
    }

    function Row($data)
    {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 4 * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 5, $data[$i], 0, $a, 'true');
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw = & $this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
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
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    function Header()
    {
        header("Content-Type: text/html; charset=iso-8859-1 ");
        $this->SetFont('Arial', '', 8);
        $this->Image('../../images/LogoCaddyNoAlfa.png', 16, 8, 40, 16, 'png', '');
        $this->Text(20, 26, 'Triangular S.A.', 0, 'C', 0);
        $this->Text(20, 31, 'Cuit: 30-71534494-3', 0, 'C', 0);
        $domicilio="Domicilio: Reconquista 4986, Córdoba";
        $domicilio_decodificado = mb_convert_encoding($domicilio, 'ISO-8859-1', 'UTF-8');
        $this->Text(20, 36,$domicilio_decodificado, 0, 'C', 0);
        $this->Text(90, 36, 'www.caddy.com.ar', 0, 'C', 0);

        //FECHA
        $this->Ln(20);
        $this->SetFont('Arial', '', 10);
        $ciudad="Córdoba";
        $ciudad_decodificado = mb_convert_encoding($ciudad, 'ISO-8859-1', 'UTF-8').date('d.m.Y');
        $this->Text(130, 14, $ciudad_decodificado, 0, 'C', 0);
        $idCliente = $_GET['id'];
        
        // Crear una instancia de la clase conexion
        $miConexion = new conexion();
        // Obtener la conexión a la base de datos desde la instancia
        $conexion = $miConexion->obtenerConexion();

        $strConsulta = "SELECT * FROM Ctasctes WHERE idCliente='$idCliente'";
        $Usuario = $conexion->query($strConsulta);
        $fila = $Usuario->fetch_assoc();
        $sqltotales = "SELECT SUM(Debe) as TotalDebe, SUM(Haber) as TotalHaber FROM Ctasctes WHERE Eliminado=0 AND idCliente='$idCliente'";
        $resultado = $conexion->query($sqltotales);
        $row = $resultado->fetch_assoc();
        $SaldoActual = number_format($row['TotalDebe'] - $row['TotalHaber'], 2, ',', '.');

        //REMITO NUMERO
        $this->Ln(20);
        $this->SetFont('Arial', '', 10);
        $this->Text(130, 19, 'Cliente: ' . $fila['RazonSocial'], 0, 'C', 0);
        $this->Text(130, 24, 'Saldo Actual: $ ' . $SaldoActual, 0, 'C', 0);

        $this->Ln(20);
        $this->SetFont('Arial', '', 18);
        $this->Text(80, 44, 'Resumen de Cuentas ', 0, 'B', 0);

        $this->SetWidths(array(20, 75, 25, 25, 25));
        $this->SetFont('Arial', 'B', 7);
        $this->SetFillColor(100, 100, 100);
        $this->SetTextColor(255);
        $this->Row(array('FECHA', 'COMPROBANTE', 'DEBE', 'HABER', 'SALDO'));
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(100, 10, 'Caddy Yo lo llevo!.', 0, 0, 'L');

        $this->SetY(-15);
        $this->SetX(90);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(100, 10, 'www.caddy.com.ar', 0, 0, 'L');
        $this->SetY(-15);
        $this->SetX(170);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(100, 10, 'Usuario:' . $_SESSION['Usuario'], 0, 0, 'L');
    }
}

// Crear una instancia de la clase conexion
$miConexion = new conexion();
// Obtener la conexión a la base de datos desde la instancia
$conexion = $miConexion->obtenerConexion();

$pdf = new PDF('P', 'mm', 'Letter');
$pdf->Open();
$pdf->AddPage();
$pdf->SetMargins(20, 10, 10);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Text(130, 7, 'RESUMEN DE CUENTAS', 0, 'C', 0);
$pdf->SetFont('Arial', 'B', 10);

$idCliente = $_GET['id'];
$strConsulta = "SELECT Fecha, TipoDeComprobante, NumeroVenta, Debe, Haber, (Debe - Haber) as Saldo FROM Ctasctes WHERE Eliminado=0 AND idCliente='$idCliente'";
$historial = $conexion->query($strConsulta);
$numfilas = $historial->num_rows;
setlocale(LC_ALL, 'es_AR');
$i = 0;
$row = $historial->fetch_assoc();
$Debe = number_format($row['Debe'], 2, ',', '.');
$Haber = number_format($row['Haber'], 2, ',', '.');
$Saldo = number_format($row['Saldo'], 2, ',', '.');
$acumulado=0;

for ($i=0; $i<$numfilas; $i++)
	    {
  
        $filaVentas = $historial->fetch_assoc();
        $acumulado=$acumulado+$filaVentas['Debe']-$filaVentas['Haber'];
        if($filaVentas['Haber']>0){
        $Tipo=' Ref.: '.$filaVentas['TipoDeComprobante'];
        }else{
        $Tipo=$filaVentas['TipoDeComprobante'];  
        }
        $ImporteNeto_label=number_format($filaVentas['ImporteNeto'],2,',','.');
        $Iva_precio_label=number_format($filaVentas['Iva3'],2,',','.'); 
        $Total=number_format($filaVentas['Total'],2,',','.');
  
		if($i%2 == 1)
			{
        $pdf->SetFont('Arial','B',5);
        $pdf->SetTextColor(0,0,0);//color negro
        $pdf->SetFillColor(255,255,255);  
				$pdf->Row(array($filaVentas['Fecha'], $Tipo.' '.$filaVentas['NumeroVenta'],'$ '.number_format($filaVentas['Debe'],2,',','.'),'$ '.number_format($filaVentas['Haber'],2,',','.'),'$ '.number_format($acumulado,2,',','.')));
			}
			else
			{
        $pdf->SetFont('Arial','B',5);
        $pdf->SetFillColor(255,255,255);  
        $pdf->SetTextColor(0);
				$pdf->Row(array($filaVentas['Fecha'], $Tipo.' '.$filaVentas['NumeroVenta'],'$ '.number_format($filaVentas['Debe'],2,',','.'),'$ '.number_format($filaVentas['Haber'],2,',','.'),'$ '.number_format($acumulado,2,',','.')));
			}
//   $acumulado=$acumulado+($filaVentas[Debe]-$filaVentas[Haber]);
  }   

$sqltotales = "SELECT SUM(Debe)as TotalDebe,SUM(Haber)as TotalHaber FROM Ctasctes WHERE Eliminado=0 AND idCliente='$idCliente'";
$resultado = $conexion->query($sqltotales);
$row = $resultado->fetch_assoc();
$TotalDebe=number_format($row['TotalDebe'],2,',','.');
$TotalHaber=number_format($row['TotalHaber'],2,',','.');
$SaldoActual=number_format($row['TotalDebe']-$row['TotalHaber'],2,',','.');

$pdf->SetFont('Arial','B',6);
$pdf->Row(array($filaVentas[''],'TOTALES: ','$ '.$TotalDebe,'$ '.$TotalHaber,'$ '.$SaldoActual));

$pdf->Output();  

?>