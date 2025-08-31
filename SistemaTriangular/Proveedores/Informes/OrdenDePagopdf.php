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
	
	
	
function Header()
{
  header("Content-Type: text/html; charset=iso-8859-1 ");
	$this->SetFont('Arial','',8);
  $this->Image('../../images/LogoCaddyNoAlfa.png',16 ,8, 40 , 16,'png','');  
	$this->Text(20,26,'Triangular S.A.',0,'C', 0);
	$this->Text(20,31,'Cuit: 30-71534494-3',0,'C', 0);
	$this->Text(20,36,utf8_decode('Domicilio: Reconquista 4986, Córdoba'),0,'C', 0);
	$this->Text(90,36,'www.caddy.com.ar',0,'C', 0);
  
	$cliente= $_SESSION['ClienteActivo'];
// 	$NumeroRepo=$_GET['NR'];
  $con = new DB;
	$historial = $con->conectar();	
	$Id=$_GET['Factura'];
	$strConsulta = "SELECT * FROM TransProveedores WHERE id='$Id' AND Eliminado=0";
	$Dato = mysql_query($strConsulta);
	while($row=mysql_fetch_row($Dato)){
	$Codigo=$row[0];
	$Fecha=$row[1];
	$Id=$row[11];	
	$arrayfecha=explode('-',$Fecha,3);
	$Fecha2=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
	}

	$this->SetFont('Arial','',10);
	$this->Text(150,14,'Cordoba '.$Fecha2,0,'C', 0);
	$this->Ln(2);
  $this->SetFont('Arial','',10);
	$this->Text(150,24,'Recibo N: '.$_GET['Factura'],0,'C', 0);

	$this->Ln(27);
  $this->SetX(20);
  $this->SetFont('Arial','',14);
  $this->SetTextColor(255,255,255);  // Establece el color del texto (en este caso es blanco)
  $this->SetFillColor(100, 100, 100); // establece el color del fondo de la celda (en este caso es GRIS)
	$this->Cell(170,10,'ORDEN DE PAGO',0,1,'C',true);
	$this->Ln(30);

}

function Footer()
{
	//FIRMA CLIENTE
  
	$this->SetY(-65);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Firma Proveedor S.A.',0,0,'L');
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
	$this->Cell(100,10,'Observaciones: La firma de esta ORDEN DE PAGO declara la aceptacion y la efectiva recepcion del importe asignado, ',0,0,'L');
	$this->SetY(-30);
	$this->Cell(100,10,'y tambien las condiciones de pago consignadas en este documento.',0,0,'L');

	$this->SetY(-15);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Recibo Triangular S.A.',0,0,'L');
	
	$this->SetY(-15);
	$this->SetX(90);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'www.caddy.com.ar',0,0,'L');

	$this->SetY(-15);
	$this->SetX(170);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Usuario:'.$_SESSION['Usuario'],0,0,'L');

	
}

}
$cliente= $_SESSION['ClienteActivo'];
// $NumeroRepo=$_GET['NR'];

	$con = new DB;
	$historial = $con->conectar();	
	$Dato="Recibo de Pago";
	$Id=$_GET['Factura'];
	$strConsulta = "SELECT * FROM TransProveedores WHERE id='$Id' ";
	$pacientes = mysql_query($strConsulta);
	$fila = mysql_fetch_array($pacientes);

	$pdf=new PDF('P','mm','Letter');
	$pdf->Open();
	$pdf->AddPage();
	$pdf->SetMargins(20,20,20);
	$pdf->Ln(-23);
 	$pdf->Line(20, 109, 190, 109);  //Horizontal forma de pago

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,6,'Proveedor:',0,1);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,6,'Nombre Cliente: '.$fila['RazonSocial'],0,1);
    $pdf->Cell(0,6,'C.U.I.T.: '.$fila['Cuit'],0,1);

    $pdf->Ln(10);

    $pdf->SetWidths(array(150));
    $pdf->SetFont('Arial','B',12);
    $pdf->Line(20, 81, 190, 81);  //Horizontal Datos CONCEPTO
    $pdf->Cell(0,6,'Concepto:',0,1);
    $pdf->SetFont('Arial','',10);
    $pdf->SetFillColor(220,220,220);  
    $pdf->Cell(0,6,'Se genera la siguiente Orden De Pago por el importe de $ '.number_format($fila['Haber'],2,",",".").' en concepto de ',0,1);
    $pdf->Cell(0,6,'Pago de Comprobante ' .$fila['TipoDeComprobante'].' Numero '.$fila['NumeroComprobante'].'',0,1);

	$Id=$_GET[Factura];
	$strConsulta = "SELECT * FROM Tesoreria WHERE idTransProvee='$fila[id]' AND Eliminado=0 AND Haber<>'0'";
	$resultado = mysql_query($strConsulta);
	$file = mysql_fetch_array($resultado);
		
	$FormaDePago=$file['NombreCuenta'];

	$pdf->Ln(10);
//  	$pdf->Line(20, 38, 190, 38);  //Horizontal TITULO
// 	$pdf->Line(20, 45, 190, 45);  //Horizontal TITULO
 	
//   $pdf->Line(20, 38, 190, 38);  //Horizontal
//  	$pdf->Line(20, 53, 190, 53);  //Horizontal Datos Proveedor

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,6,'Forma de pago:',0,1);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,6,'Cuenta de Origen: '.$file['NombreCuenta'],0,1);
    $pdf->Cell(0,6,'Numero de Cuenta de Origen: '.$file[Cuenta],0,1);

if($file[NumeroCheque]<>''){
    $pdf->Cell(0,6,'Banco Emisor: '.$file[Banco],0,1);  
    $pdf->Cell(0,6,'Numero de Cheque: '.$file[NumeroCheque],0,1);
    $Fecha0=explode('-',$file[FechaCheque],3);
    $FechaCheque=$Fecha0[2]."/".$Fecha0[1]."/".$Fecha0[0];
	$pdf->Cell(0,6,'Fecha de Cheque: '.$FechaCheque,0,1);             
}
$pdf->Cell(0,6,'TOTAL PAGADO: $ '.number_format($file['Haber'],2,",","."),0,1);
$pdf->Output();

?>