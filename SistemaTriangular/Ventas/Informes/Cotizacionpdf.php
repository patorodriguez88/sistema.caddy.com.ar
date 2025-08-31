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
	$NumeroRep=$_GET['NR'];
  $CodigoSeguimiento=$_GET['CS'];
    
  $con = new DB;
	$historial = $con->conectar();	
	$strConsulta = "SELECT * FROM Cotizaciones WHERE id='$CodigoSeguimiento'";
	$Dato = mysql_query($strConsulta);
// 	header("Content-Type: text/html; charset=iso-8859-1 ");

  $row=mysql_fetch_array($Dato);
// 	$Codigo=$row[NumPedido];
	$Fecha=$row[Fecha];
//   $Repo=$row[NumeroRepo];  
	$arrayfecha=explode('-',$Fecha,3);
	$Fecha2=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
// 	}

	$this->SetFont('Arial','',10);
//   $this->Image('../../images/LogoCaddy.png',10,8,22);  
  $this->Image('../../images/LogoCaddyNoAlfa.png',16 ,8, 40 , 16,'png','');  
	$this->Text(20,26,'Triangular S.A.',0,'C', 0);
	$this->Text(20,31,'Cuit: 30-71534494-3',0,'C', 0);
	$this->Text(20,36,utf8_decode('Domicilio: Reconquista 4986, Córdoba'),0,'C', 0);
	$this->Text(90,36,'www.caddy.com.ar',0,'C', 0);
	
	//FECHA
	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(150,14,utf8_decode("Córdoba ").$Fecha2,0,'C', 0);

	//REMITO NUMERO
	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(150,24,'Vendedor: '.$_SESSION[Usuario],0,'C', 0);
	$this->Text(150,29,'Presupuesto N: '.$CodigoSeguimiento,0,'C', 0);
	
// // //DESDE ACA EL GENERADOR DE CODIGO QR 
// $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
// //html PNG location prefix
// $PNG_WEB_DIR = 'temp/';
// include_once "../../phpqrcode/qrlib.php";    
// //ofcourse we need rights to create temp dir
// if (!file_exists($PNG_TEMP_DIR))
//     mkdir($PNG_TEMP_DIR);
// $filename = $PNG_TEMP_DIR.'test.png';
// $matrixPointSize = 10;
// $errorCorrectionLevel = 'L';

// $filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
// QRcode::png('http://www.caddy.com.ar/Seguimiento.php?codigo_t='.$Codigo, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 

// $this->Image($PNG_WEB_DIR.basename($filename), 95 ,10, 20 , 20,'png','');
// // //HASTA ACA EL GENERADOR DE CODIGO QR	
	
	$this->Ln(20);
 	$this->SetFont('Arial','',18);
	$this->Text(70,44,'PRESUPUESTO DE ENVIO',0,'C', 0);

}
	
function Footer()
{
	//FIRMA CLIENTE
  
// 	$this->SetY(-65);
// 	$this->SetFont('Arial','B',8);
// 	$this->Cell(100,10,'Firma del Cliente',0,0,'L');
// 	//ACLARACION CLIENTE
// 	$this->SetY(-65);
// 	$this->SetX(90);
// 	$this->SetFont('Arial','B',8);
// 	$this->Cell(100,10,'Aclaracion Nombre',0,0,'L');
// 	//D.N.I.
// 	$this->SetY(-65);
// 	$this->SetX(150);
// 	$this->SetFont('Arial','B',8);
// 	$this->Cell(100,10,'D.N.I.',0,0,'L');
	
// 	$this->SetY(-25);
// 	$this->SetFont('Arial','B',8);
// 	$this->Cell(100,10,'Observaciones: Queda sujeto el transporte de la carga descripta a las disposiciones del codigo de comercio
// 	especialmente. ',0,0,'L');
// 	$this->SetY(-20);
// 	$this->Cell(100,10,'a lo que se refiere en los Arts 172 y 177 que el cargador declara aceptar.',0,0,'L');
	
	$this->SetY(-15);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Remito Caddy Yo lo llevo!.',0,0,'L');
	
	$this->SetY(-15);
	$this->SetX(90);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'www.caddy.com.ar',0,0,'L');

	$CodigoSeguimiento=$_GET['CS'];
	$con = new DB;
	$pacientes = $con->conectar();	
	$strConsulta = "SELECT * FROM Cotizaciones WHERE id='$CodigoSeguimiento'";
	$Usuario = mysql_query($strConsulta);
	$fila = mysql_fetch_array($Usuario);
	
	$this->SetY(-15);
	$this->SetX(170);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Usuario:'.$fila['Usuario'],0,0,'L');

 }
}

$cliente= $_SESSION['ClienteActivo'];
$CodigoSeguimiento=$_GET['CS'];

	$con = new DB;
	$pacientes = $con->conectar();	
	$strConsulta = "SELECT * FROM Cotizaciones WHERE id='$CodigoSeguimiento'";
	$pacientes = mysql_query($strConsulta);
	$fila = mysql_fetch_array($pacientes);
	$Usuario=$fila['Usuario'];
	$Transportista=$fila['Transportista'];
	$Recorrido=$fila[Recorrido];
	$pdf=new PDF('P','mm','Letter');
	$pdf->Open();
	$pdf->AddPage();
	$pdf->SetMargins(20,20,20);

	//ORIGEN
	$pdf->Ln(-23);
 	$pdf->Line(20, 38, 190, 38);  //Horizontal
	$pdf->Line(20, 45, 190, 45);  //Horizontal
	$pdf->SetFont('Arial','',14);
	$pdf->SetWidths(array(170));

	$pdf->SetFont('Arial','B',11);
	$pdf->SetFillColor(226,79,48);
  $pdf->SetTextColor(255);
	$pdf->Row(array("Origen"));
	$pdf->SetFont('Arial','',10);
  $pdf->SetFillColor(255,255,255);
  $pdf->SetTextColor(0);
  $pdf->Row(array("Nombre Cliente: " .$fila['RazonSocial']));

	$pdf->SetFont('Arial','',8);
  $pdf->Row(array("Domicilio Origen: " .$fila['DomicilioOrigen']." | ".$fila[LocalidadOrigen]));

  $pdf->SetFont('Arial','B',8);
	$pdf->SetFillColor(226,79,48);
  $pdf->SetTextColor(255);
  $pdf->Row(array("Destino"));
	$pdf->SetFont('Arial','',8);
  $pdf->SetFillColor(255,255,255);
  $pdf->SetTextColor(0);
  $pdf->Row(array("Nombre Cliente:".$fila['ClienteDestino']));
  $pdf->Row(array("Domicilio Destino:".$fila['DomicilioDestino']." | ".$fila[LocalidadDestino]));

  $pdf->SetFont('Arial','B',11);
	$pdf->SetFillColor(226,79,48);
  $pdf->SetTextColor(255);
	$pdf->Row(array("Detalle"));
  $pdf->SetFillColor(255,255,255);
  $pdf->SetTextColor(0);
  $pdf->Row(array($fila['Descripcion']));

//   $pdf->Cell(0,5,'Recorrido: '.$fila[Recorrido],0,1); 
	$pdf->Cell(0,5,'Observaciones: '.$fila['Observaciones'],0,1); 

	$pdf->SetWidths(array(18, 35, 30, 30, 35, 20));
	$pdf->SetFont('Arial','B',7);
	$pdf->SetFillColor(226,79,48);
    $pdf->SetTextColor(255);

		for($i=0;$i<1;$i++)
			{
				$pdf->Row(array('CANTIDAD', 'PRECIO', 'REDESPACHO', 'PUNTO INTERMEDIO', 'CAMBIO DE LOCALIDAD','TOTAL'));
			}
	$CodigoSeguimiento=$_GET[CS];
	$historial = $con->conectar();	
	$strConsulta = "SELECT * FROM Cotizaciones WHERE id='$CodigoSeguimiento'";
	$historial = mysql_query($strConsulta);
	$numfilas = mysql_num_rows($historial);
	//Calcula el total de la repo
  setlocale(LC_ALL,'es_AR');
	for ($i=0; $i<$numfilas; $i++)
	{
			$fila = mysql_fetch_array($historial);
	//$Total=$fila['Cantidad']*$fila['Precio'];
			$pdf->SetFont('Arial','',6);
		
				if ($MuestraPrecio==0){
				$Precio='';
				$Total=	'';
				$TotalRepo=$TotalRepo1;
				}elseif($MuestraPrecio==1){
				$Precio='$'.$fila['Precio'];
				$Total='$'.$fila['Cantidad']*$fila['Precio'];	
				$TotalRepo=$TotalRepo0;
				}
			if($i%2 == 1)
			{
				$pdf->SetFillColor(255,255,255);
    			$pdf->SetTextColor(0);
				$pdf->Row(array($fila['Cantidad'], $fila['Precio'], $fila[Redespacho],$fila[PuntoIntermedio],$fila[CambiaLocalidad],$fila['Total']));
			}
			else
			{
				$pdf->SetFillColor(220,220,220);
    			$pdf->SetTextColor(0);
				$pdf->Row(array($fila['Cantidad'], $fila['Precio'], $fila[Redespacho],$fila[PuntoIntermedio],$fila[CambiaLocalidad],$fila['Total']));
			}
      $iva=($fila['Total']*21)/100;
    	  $pdf->Row(array('', '', '','','Iva 21%:',$iva));

		}
// 	$pdf->Row(array($fila[''], $fila[''],'Total:', $TotalCant,'',$TotalRepo));
	$pdf->SetY(-45);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(100,10,'Firma del Cliente',0,0,'L');
	//ACLARACION CLIENTE
	$pdf->SetY(-45);
	$pdf->SetX(90);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(100,10,'Aclaracion Nombre',0,0,'L');
	//D.N.I.
	$pdf->SetY(-45);
	$pdf->SetX(150);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(100,10,'D.N.I.',0,0,'L');
	//OBSERVACIONES
	$pdf->SetY(-38);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(100,10,'Observaciones: Queda sujeto el transporte de la carga descripta a las disposiciones del codigo de comercio
	especialmente. ',0,0,'L');
	$pdf->SetY(-33);
	$pdf->Cell(100,10,'a lo que se refiere en los Arts 172 y 177 que el cargador declara aceptar.',0,0,'L');
  $pdf->SetY(-42);  


$pdf->Output();
?>