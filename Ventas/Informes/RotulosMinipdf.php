<?php
session_start();
require('../../fpdf/fpdf.php');
require('../../conexion.php');
// require('Rotation.php');
class PDF_Rotate extends FPDF
{
var $angle=0;

function Rotate($angle,$x=-1,$y=-1)
{
    if($x==-1)
        $x=$this->x;
    if($y==-1)
        $y=$this->y;
    if($this->angle!=0)
        $this->_out('Q');
    $this->angle=$angle;
    if($angle!=0)
    {
        $angle*=M_PI/180;
        $c=cos($angle);
        $s=sin($angle);
        $cx=$x*$this->k;
        $cy=($this->h-$y)*$this->k;
        $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
    }
}

function _endpage()
{
    if($this->angle!=0)
    {
        $this->angle=0;
        $this->_out('Q');
    }
    parent::_endpage();
}
}


class PDF extends PDF_Rotate
{
function RotatedText($x,$y,$txt,$angle)
{
    //Text rotated around its origin
    $this->Rotate($angle,$x,$y);
    $this->Text($x,$y,$txt);
    $this->Rotate(0);
}

function RotatedImage($file,$x,$y,$w,$h,$angle)
{
    //Image rotated around its upper-left corner
    $this->Rotate($angle,$x,$y);
    $this->Image($file,$x,$y,$w,$h);
    $this->Rotate(0);
}
// }

// class PDF extends FPDF
// {
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

}
$cliente= $_SESSION['ClienteActivo'];
$NumeroRepo=$_GET['NR'];

	$con = new DB;
	$pacientes = $con->conectar();	
	$strConsulta = "SELECT * FROM TransClientes WHERE NumeroComprobante='$NumeroRepo'";
	$pacientes = mysql_query($strConsulta);
	$fila = mysql_fetch_array($pacientes);

$bulto='1';
$bultototal='5';
$numfilas=$fila['Cantidad'];

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
QRcode::png('http://www.triangularlogistica.com.ar/Seguimiento.php?codigo_t='.$fila['CodigoSeguimiento'], $filename, $errorCorrectionLevel, $matrixPointSize, 2); 
	//HASTA ACA EL GENERADOR DE CODIGO QR	

$a=15;
//desde aca 
	$pdf=new PDF('P','mm','Letter');
	$pdf->Open();
	$pdf->AddPage();
	$pdf->SetMargins(70,20,10);

 for ($i=1; $i<=$numfilas; $i++){
// 	$pdf->Image($PNG_WEB_DIR.basename($filename),135 ,$a, 16 , 16,'png','');
	$pdf->RotatedImage($filename,135,$a,16,16,90);

	 // $a=$a+60; 
  if ($i==5){
 	$a=25;
 	}else{	
 	$a=$a+32; 
 	}
 }
for ($i=1; $i<=$numfilas; $i++)
	{
		
 	$pdf->Ln(5);
	$pdf->SetWidths(array(60));
	$pdf->SetFont('Arial','B',8);
// 	$pdf->cell(100,10,"",1,1,'C');
	$pdf->SetFillColor(253,254,254);
  $pdf->SetTextColor(0);
	$pdf->RotatedImage($filename,85,60,40,16,45);
	$pdf->RotatedText(100,60,'Hello!',45);
	$pdf->Row(array('ROTULO REMITO N:  '.$fila['NumeroComprobante'].'   
	Codigo de Seguimiento:  '.$fila['CodigoSeguimiento'].'  
	Bulto: '.$i.' DE '.$numfilas.' 
  Origen: ' .$fila['RazonSocial'].'
	Destino:' .$fila['ClienteDestino']));

// 	$pdf->SetWidths(array(20, 45, 20, 55));
// 	$pdf->SetFont('Arial','B',9);
// 	$pdf->SetFillColor(253,253,253);
// 	$pdf->SetTextColor(0);
// 	$pdf->Row(array('Transporte:','Triangular S.A.','Web:','wwww.triangularlogistica.com.ar'));
// 	$pdf->Row(array('Domicilio:','Justiniano Posse 1236','Localidad:','Cordoba'));
// 	$pdf->Row(array('Cuit:','30-71534494-3','Telef Movil:','03516151944'));
//  	$pdf->Image($PNG_WEB_DIR.basename($filename),105 ,$a, 15 , 15,'png','');
 	
// 	if ($i==4){
// 	$a=25;
// 	}elseif($i==8){	
// 	$a=25;
// 	}elseif($i==12){	
// 	$a=25;
// 	}else{
// 	$a=$a+52; 
// 	}
	
	
 	$pdf->Ln(0);
// 	$pdf->SetWidths(array(90,90));
// 	$pdf->SetFont('Arial','B',16);
// 	$pdf->SetFillColor(220,220,220);
//   $pdf->SetTextColor(0);
// 	$pdf->Row(array('Origen:', 'Destino:'));

// 	$pdf->SetWidths(array(30, 60, 30, 60));
// 	$pdf->SetFont('Arial','B',10);
// 	$pdf->SetFillColor(253,253,253);
// 	$pdf->SetTextColor(0);
// 	$pdf->Row(array('Razon Social:',$fila['RazonSocial'],'Razon Social:',$fila['ClienteDestino']));
// 	$pdf->Row(array('Cuit:',$fila['Cuit'],'Cuit:', $fila['DocumentoDestino']));
// 	$pdf->Row(array('Domicilio:',$fila['DomicilioOrigen'],'Domicilio:', $fila['DomicilioDestino']));
// 	$pdf->Row(array('Sit. Fiscal:',$fila['SituacionFiscalOrigen'], 'Sit. Fiscal:',$fila['SituacionFiscalDestino']));
// 	$pdf->Row(array('Localidad:',$fila['LocalidadOrigen'],'Localidad:',$fila['LocalidadDestino'] ));
// 	$pdf->Row(array('Telefono:',$fila['Telefono'],'Telefono:',$fila['TelefonoDestino'] ));
	}

$pdf->Output();
?>