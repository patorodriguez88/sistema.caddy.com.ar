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
$CodigoSeguimiento=$_GET[CS];
	$con = new DB;
	$pacientes = $con->conectar();	
	$strConsulta = "SELECT * FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'";
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
QRcode::png('https://www.caddy.com.ar/seguimiento.html?codigo='.$fila['CodigoSeguimiento'], $filename, $errorCorrectionLevel, $matrixPointSize, 2); 
	//HASTA ACA EL GENERADOR DE CODIGO QR	
$a=21;

//desde aca 
	$pdf=new PDF('P','mm','Letter');
	$pdf->Open();
	$pdf->AddPage();
	$pdf->SetMargins(20,20,20);

// for ($i=1; $i<=$numfilas; $i++){
// $pdf->Image($PNG_WEB_DIR.basename($filename),175 ,$a, 16 , 16,'png','');
// // $a=$a+60; 
//  	if ($i==5){
// 	$a=20;
// 	}else{	
// 	$a=$a+72; 
// 	}
// }
for ($i=1; $i<=$numfilas; $i++)
	{
		
 	$pdf->Ln(5);
	$pdf->SetWidths(array(150));
	$pdf->SetFont('Arial','B',7);
	$pdf->SetFillColor(220,220,220);
  $pdf->SetTextColor(0);
//   $pdf->Image('../../images/LogoCaddyNoAlfa.png',16 ,8, 40 , 16,'png','');  
	$pdf->Row(array('ROTULO REMITO N:  '.$fila['NumeroComprobante'].' | Codigo de Seguimiento:  '.$fila['CodigoSeguimiento'].' | BULTO: '.$i.' DE '.$numfilas.'                          CODIGO QR'));

	$pdf->SetWidths(array(30,80));
	$pdf->SetFont('Arial','B',6);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(0);
	$pdf->Row(array('',' Caddy Yo lo llevo!  |  wwww.caddy.com.ar  |  Reconquista 4986   |   Cordoba'));
	$pdf->Row(array('',' Cuit: 30-71534494-3      |      Telef Movil: +54 9 3515 69-7188'));
  $pdf->SetFont('Arial','B',6);
  $Fecha=explode('-',$fila[Fecha],3);
  $Fecha0=$Fecha[2].'/'.$Fecha[1].'/'.$Fecha[0];
  $pdf->Row(array('','Fecha: '.$Fecha0. '  |  Recorrido: '.$fila[Recorrido].'  |  Forma de Pago: '.$fila[FormaDePago]));
  $pdf->Image($PNG_WEB_DIR.basename($filename),136 ,$a, 28 , 28,'png','');
//  	$pdf->Image($PNG_WEB_DIR.basename($filename),136 ,$a, 28 , 28,'png','');
  $b=$a-1;
  $pdf->Image('../../images/caddy.jpg',20 ,$b, 29.7 , 14.5,'jpg','');
  

  if ($i==4){
	$a=26;
	}elseif($i==8){	
	$a=26;
  }elseif($i==12){	
	$a=26;
  }elseif($i==16){	
	$a=26;
  }elseif($i==20){	
	$a=26;
  }elseif($i==24){	
	$a=26;
  }elseif($i==28){	
	$a=26;
  }elseif($i==32){	
	$a=26;
  }elseif($i==36){	
	$a=26;
  }elseif($i==40){	
	$a=26;
  }elseif($i==44){	
	$a=26;
  }elseif($i==48){	
	$a=26;
  }elseif($i==52){	
	$a=26;
  }elseif($i==56){	
	$a=26;
  }elseif($i==60){	
	$a=26;
  }elseif($i==64){	
	$a=26;
  }elseif($i==68){	
	$a=26;
  }else{
	$a=$a+60; 
  }
	
	
 	$pdf->Ln(0);
	$pdf->SetWidths(array(55,55));
	$pdf->SetFont('Arial','B',10);
	$pdf->SetFillColor(0,0,0);
  $pdf->SetTextColor(255,255,255);
	$pdf->Row(array('Origen:', 'Destino:'));

	$pdf->SetWidths(array(13, 42, 13, 42));
	$pdf->SetFont('Arial','B',5);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(0);
	$pdf->Row(array('Cliente:',$fila['RazonSocial'],'Cliente:',$fila['ClienteDestino']));
	$pdf->Row(array('Cuit:',$fila['Cuit'],'Cuit:', $fila['DocumentoDestino']));
	$pdf->SetWidths(array(13, 42, 13, 82 ));
  $pdf->Row(array('Domicilio:',$fila['DomicilioOrigen'].' | '.$fila['LocalidadOrigen'],'Domicilio:', $fila['DomicilioDestino'].' | ' .$fila['LocalidadDestino']));
	$pdf->SetFont('Arial','B',5);
  $pdf->SetWidths(array(13, 42, 13, 82));
  $pdf->Row(array('Telefono:',$fila['TelefonoOrigen'],'Telefono:',$fila['TelefonoDestino']));
  $pdf->SetWidths(array(150));
  $pdf->SetFont('Arial','B',7);
  $pdf->SetWidths(array(22,128));
  $pdf->Row(array('Observaciones:',$fila['CodigoProveedor'].' | '.$fila['Observaciones']));
  $pdf->SetFont('Arial','B',5);
  $pdf->SetWidths(array(150));
  $pdf->Row(array('IMPORTANTE: Caddy solo se limita al transporte de la mercaderia y no sera responsable por los articulos entregados y/o contenido en este paquete.'));
	}

$pdf->Output();
?>