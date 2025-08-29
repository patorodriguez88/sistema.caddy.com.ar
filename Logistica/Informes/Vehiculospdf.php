<?php
session_start();
require('../../fpdf/fpdf.php');
include("../../../conexion.php");
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
  $con = new DB;
	$historial = $con->conectar();	

	$this->SetFont('Arial','',10);
	$this->Text(20,14,'Triangular S.A.',0,'C', 0);
	$this->Text(20,19,'Cuit: 30-71534494-3',0,'C', 0);
	$this->Text(20,24,'9 de Julio 1680 Piso 1 Dpto 8',0,'C', 0);
// 	$this->Text(20,29,'www.triangularlogistica.com.ar',0,'C', 0);
	
	//FECHA
	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(150,14,'Cordoba '.date('d/m/Y'),0,'C', 0);

	$this->Ln(20);
 	$this->SetFont('Arial','',12);
	$this->Text(75,44,'VEHICULOS TRIANGULAR S.A.',0,'C', 0);

}
	
function Footer()
{
	
	$this->SetY(-15);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Vehiculos',0,0,'L');
	
	$this->SetY(-15);
	$this->SetX(90);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Triangular S.A.',0,0,'L');

	$this->SetY(-15);
	$this->SetX(170);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Usuario:'.$_SESSION['Usuario'],0,0,'L');

 }
}
$cliente= $_SESSION['ClienteActivo'];
$NumeroRepo=$_GET['NR'];

	$con = new DB;
	$pacientes = $con->conectar();	
	$strConsulta="SELECT * FROM Vehiculos WHERE Activo='Si'";
	$pacientes = mysql_query($strConsulta);
	$fila = mysql_fetch_array($pacientes);

	$pdf=new PDF('P','mm','Letter');
	$pdf->Open();
	$pdf->AddPage();
	$pdf->SetMargins(20,20,20);
	$pdf->Ln(2);

if (mysql_num_rows($pacientes)!='0'){
	$pdf->Ln(5);
 	$pdf->SetFont('Arial','B',14);
	$pdf->SetFillColor(255,0,0);
  $pdf->SetTextColor(255);
	$pdf->SetWidths(array(181));
	$pdf->Row(array('Vehiculos de Flota'));

	$pdf->SetWidths(array(17, 50, 18, 18, 15, 28, 35));
	$pdf->SetFont('Arial','B',8);
	$pdf->SetFillColor(119,136,153);
  $pdf->SetTextColor(255);

		for($i=0;$i<1;$i++)
			{
				$pdf->Row(array('MARCA', 'MODELO', 'DOMINIO', 'COLOR', utf8_decode('AÑO'),'MOTOR','CHASIS'));
			}
	$CodigoSeguimiento=$fila['CodigoSeguimiento'];
	$historial = $con->conectar();	
	$strConsulta = "SELECT * FROM Vehiculos WHERE Activo='Si'";

	$historial = mysql_query($strConsulta);
	$numfilas = mysql_num_rows($historial);
  setlocale(LC_ALL,'es_AR');
	$TotalRepo1='';	
	for ($i=0; $i<$numfilas; $i++)
	{
			$fila = mysql_fetch_array($historial);
			$pdf->SetFont('Arial','',8);
				
			if($i%2 == 1)
			{
				$pdf->SetFillColor(255,255,255);
    			$pdf->SetTextColor(0);
				$pdf->Row(array($fila['Marca'], $fila['Modelo'], $fila['Dominio'], $fila['Color'],$fila['Ano'],$fila['Motor'],$fila['Chasis']));
			}
			else
			{
				$pdf->SetFillColor(220,220,220);
    			$pdf->SetTextColor(0);
				$pdf->Row(array($fila['Marca'], $fila['Modelo'], $fila['Dominio'], $fila['Color'],$fila['Ano'],$fila['Motor'],$fila['Chasis']));
			}
		}
	}	

//-------------------------HASTA ACA EL CUADRO DE ADELANTOS----------------------------------
$pdf->Output();

?>