<?php
session_start();
require('../../fpdf/fpdf.php');
require('../../../conexion.php');
header("Content-Type: text/html; charset=iso-8859-1 ");

class PDF extends FPDF
{
var $widths;
var $aligns;

function SetWidths($w)
{
	//Set the array of column widths
	$this->widths=$w;
}

function SetAligns($a)
{
	//Set the array of column alignments
	$this->aligns=$a;
}

function Row($data)
{
	//Calculate the height of the row
	$nb=0;
	for($i=0;$i<count($data);$i++)
		$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
	$h=5*$nb;
	//Issue a page break first if needed
	$this->CheckPageBreak($h);
	//Draw the cells of the row
	for($i=0;$i<count($data);$i++)
	{
		$w=$this->widths[$i];
		$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
		//Save the current position
		$x=$this->GetX();
		$y=$this->GetY();
		//Draw the border
		
		$this->Rect($x,$y,$w,$h);

		$this->MultiCell($w,5,$data[$i],0,$a,'true');
		//Put the position to the right of the cell
		$this->SetXY($x+$w,$y);
	}
	//Go to the next line
	$this->Ln($h);
}

function CheckPageBreak($h)
{
	//If the height h would cause an overflow, add a new page immediately
	if($this->GetY()+$h>$this->PageBreakTrigger)
		$this->AddPage($this->CurOrientation);
}

function NbLines($w,$txt)
{
	//Computes the number of lines a MultiCell of width w will take
	$cw=&$this->CurrentFont['cw'];
	if($w==0)
		$w=$this->w-$this->rMargin-$this->x;
	$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	$s=str_replace("\r",'',$txt);
	$nb=strlen($s);
	if($nb>0 and $s[$nb-1]=="\n")
		$nb--;
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$nl=1;
	while($i<$nb)
	{
		$c=$s[$i];
		if($c=="\n")
		{
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$nl++;
			continue;
		}
		if($c==' ')
			$sep=$i;
		$l+=$cw[$c];
		if($l>$wmax)
		{
			if($sep==-1)
			{
				if($i==$j)
					$i++;
			}
			else
				$i=$sep+1;
			$sep=-1;
			$j=$i;
			$l=0;
			$nl++;
		}
		else
			$i++;
	}
	return $nl;
}
	
	
	
function Header()
{
	$cliente= $_SESSION['ClienteActivo'];
	$NumeroRepo=$_GET['NR'];
  $con = new DB;
	$historial = $con->conectar();	
	$strConsulta = "SELECT * FROM Ctasctes WHERE NumeroVenta='$NumeroRepo' AND RazonSocial='$cliente'";
	$Dato = mysql_query($strConsulta);
	$row=mysql_fetch_array($Dato);
	$Codigo=$row[id];
	$Fecha=$row[Fecha];
	$arrayfecha=explode('-',$Fecha,3);
	$Fecha2=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
  
  //R DE REMITO
  $this->Line(100, 10, 115, 10);  //Horizontal
  $this->Line(100, 20, 115, 20);  //Horizontal
    
  $this->Line(100, 10, 100, 20);  //Vertical
  $this->Line(115, 10, 115, 20);  //Vertical

//   $this->Ln(20);
 	$this->SetFont('Arial','',28);
	$this->Text(104,19,'R',0,'C', 0);
 	$this->SetFont('Arial','',12);
	$this->Text(101,25,'Recibo',0,'C', 0);
  
	$this->SetFont('Arial','',8);
  $this->Image('../../images/LogoCaddyNoAlfa.png',16 ,8, 40 , 16,'png','');  
	$this->Text(20,26,'Triangular S.A.',0,'C', 0);
	$this->Text(20,31,'Cuit: 30-71534494-3',0,'C', 0);
	$this->Text(20,36,utf8_decode('Domicilio: Reconquista 4986, Córdoba'),0,'C', 0);
	$this->Text(96,36,'www.caddy.com.ar',0,'C', 0);
// 	$this->Text(20,34,'Seguimiento:'.$Codigo,0,'C', 0);
	
	
	//FECHA
// 	$fecha=date('d/m/Y');
	$this->Ln(20);
 	$this->SetFont('Arial','',8);
	$this->Text(150,26,utf8_decode('Córdoba ').$Fecha2,0,'C', 0);
	//REMITO NUMERO
	$this->Ln(20);
//  	$this->SetFont('Arial','',10);
	$this->Text(150,31,'Recibo N:'.$row[5],0,'C', 0);
	$this->Text(150,36,'id: '.$_GET['NR'],0,'C', 0);

	
	
//DESDE ACA EL GENERADOR DE CODIGO QR 
$PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
//html PNG location prefix
$PNG_WEB_DIR = 'temp/';
include "../../phpqrcode/qrlib.php";    
//ofcourse we need rights to create temp dir
if (!file_exists($PNG_TEMP_DIR))
    mkdir($PNG_TEMP_DIR);
$filename = $PNG_TEMP_DIR.'test.png';
$matrixPointSize = 10;
$errorCorrectionLevel = 'L';

$filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
QRcode::png('http://www.caddy.com.ar/Mp/Recibopdf.php?NR='.$NumeroRepo, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 

$this->Image($PNG_WEB_DIR.basename($filename), 160 ,8, 15 , 15,'png','');
//HASTA ACA EL GENERADOR DE CODIGO QR	
	
	
	
	
	// 	$this->Ln(20);
//  	$this->SetFont('Arial','',10);
// 	$this->Text(20,24,'www.triangularlogistica.com.ar',0,'C', 0);
	
// 	$this->Ln(20);
//  	$this->SetFont('Arial','',10);
// 	$this->Text(20,34,'Seguimiento:'.$Codigo,0,'C', 0);
	$this->Ln(20);
 	$this->SetFont('Arial','B',18);
	$this->Text(80,44,'RECIBO DE PAGO',0,'C', 0);

}

function Footer()
{
	//FIRMA CLIENTE
  
	$this->SetY(-65);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Firma Caddy',0,0,'L');
	//ACLARACION CLIENTE
	$this->SetY(-65);
	$this->SetX(90);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Aclaracion Nombre',0,0,'L');
	//D.N.I.
	$this->SetY(-65);
	$this->SetX(150);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'D.N.I.',0,0,'L');

	
	$this->SetY(-35);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Observaciones: Queda sujeto el transporte de la carga descripta a las disposiciones del codigo de comercio
	especialmente. ',0,0,'L');
	$this->SetY(-30);
	$this->Cell(100,10,'a lo que se refiere en los Arts 172 y 177 que el cargador declara aceptar.',0,0,'L');

	$this->SetY(-15);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Recibo Triangular S.A.',0,0,'L');
	
	$this->SetY(-15);
	$this->SetX(90);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'www.caddy.com.ar',0,0,'L');

  $this->SetY(-15);
	$this->SetX(160);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Usuario:'.$_SESSION['Usuario'],0,0,'L');

}

}
$cliente= $_SESSION['ClienteActivo'];
$NumeroRepo=$_GET['NR'];

$con = new DB;
$historial = $con->conectar();	
$Dato="Recibo de Pago";
$strConsulta = "SELECT * FROM TransClientes WHERE NumeroComprobante='$NumeroRepo' AND TipoDeComprobante='$Dato' AND RazonSocial='$cliente' AND Eliminado=0";
$pacientes = mysql_query($strConsulta);
if($fila = mysql_fetch_array($pacientes)){

	$pdf=new PDF('P','mm','Letter');
	$pdf->Open();
	$pdf->AddPage();
	$pdf->SetMargins(20,20,20);

//   $pdf->Ln(20);
//   $pdf->SetWidths(array(100));
// 	$pdf->SetWidths(100);
// 	$pdf->SetFont('Arial','B',18);
// 	$pdf->SetFillColor(119,136,153);
//   $pdf->SetTextColor(255);
//   $pdf->Row(array('RECIBO DE PAGO'));

  $strConsulta =mysql_query("SELECT Direccion,SituacionFiscal FROM Clientes WHERE nombrecliente= '".$fila[RazonSocial]."'");
  $DatoClientes=mysql_fetch_array($strConsulta);
  $sqlFormaDePago =mysql_query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta= '".$fila[FormaDePago]."'");
  $DatoFormaDePago=mysql_fetch_array($sqlFormaDePago);

//ORIGEN
	$pdf->Ln(-23);
 	$pdf->Line(20, 38, 190, 38);  //Horizontal
	$pdf->Line(20, 45, 190, 45);  //Horizontal
	$pdf->SetFont('Arial','B',12);
	$pdf->SetFillColor(119,136,153);
  $pdf->SetTextColor(0);
  $pdf->Cell(0,6,'Datos Cliente:',0,1);
  $pdf->SetFont('Arial','',10);
  $pdf->Cell(0,6,'Nombre Cliente: '.$fila['RazonSocial'],0,1);
	$pdf->Cell(0,6,'C.U.I.T.: '.$fila['Cuit'],0,1);
	$pdf->Cell(0,6,'Domicilio: '.$DatoClientes['Direccion'],0,1); 
	$pdf->Cell(0,6,'Situacion Fiscal: '.$DatoClientes[SituacionFiscal],0,1); 
  $pdf->Ln(10);
  $pdf->SetFont('Arial','B',14);
	$pdf->SetFillColor(220,220,220); 
	$pdf->Cell(0,6,'Total Pagado: $ '.$fila[Haber],0,1); 
	
	
	$pdf->Ln(10);
	
	$pdf->SetWidths(array(150));
	$pdf->SetFont('Arial','B',10);
	$pdf->SetFillColor(220,220,220); 

	$pdf->Cell(0,6,'Concepto: Recibimos el importe de $ '.$fila['Haber'].' para imputar a comprobante N '.$fila['NumeroComprobante'].'',0,1);
	$pdf->Cell(0,7,'Forma de Pago: Numero de Cuenta ('.$fila['FormaDePago'].') Cuenta '.$DatoFormaDePago[NombreCuenta],0,1);
	
	$pdf->Output();
}else{
  header('location:i.php');
}
?>