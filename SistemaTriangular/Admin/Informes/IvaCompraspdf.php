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
	$con = new DB;
	$pacientes = $con->conectar();	

	$Fecha=$_GET['Desde'];
	$Mes=date("n",strtotime($Fecha));
	$Ano=date("Y",strtotime($Fecha));
	
	$Buscar=mysql_query("SELECT * FROM CierreIva WHERE Libro='IvaCompras' AND Eliminado=0 AND Mes='$Mes' AND Ano='$Ano'");
	$numerofilas = mysql_num_rows($Buscar);	
	
	if ($numerofilas<>'0'){
	$row=mysql_fetch_array($Buscar);
	$Folio=$row[Folio];	
	}else{
	$Folio="LIBRO IVA AUN NO CERRADO";
	}
	
	$Desde=$_GET['Desde'];
	$Hasta=$_GET['Hasta'];
	$this->SetMargins(20,20,20);
	$this->SetFont('Arial','',10);
	$this->Text(20,14,'Libro Iva Compras Triangular S.A.',0,'C', 0);
	$this->Ln(6);
	
//TITULO
	$this->Ln(1);
	$this->SetFont('Arial','',14);
// 	$this->Cell(10);
	$this->Cell(0,6,'LIBRO IVA COMPRAS',0,1);

	$arrayfecha=explode('-',$Desde,3);
  $MDesde=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];

	$arrayfecha1=explode('-',$Hasta,3);
	$MHasta=$arrayfecha1[2]."/".$arrayfecha1[1]."/".$arrayfecha1[0];

	$this->SetWidths(array(40, 40, 64, 48, 48));
	$this->SetFont('Arial','B',8);
	$this->SetFillColor(246,246,246);
  $this->SetTextColor(0);
	$this->Row(array('Cuit: 30-71534494-3', 'IIBB:281861638', 'Folio N:'.$Folio,'Desde: '.$MDesde,'Hasta: '.$MHasta));
	$this->Ln(5);	
	$this->SetWidths(array(13, 50, 18, 25, 19, 17, 14, 14, 14, 14,14,13,17));
	$this->SetFont('Arial','B',7);
	$this->SetFillColor(119,136,153);
  $this->SetTextColor(255);
	$this->Row(array('FECHA', 'RAZON SOCIAL', 'CUIT', 'TIPO COMP.', 'NUMERO','IMP.NETO','IVA 10.5%','IVA 21%','IVA 27%','EXENTO','PERC.IVA','PEC.IIBB','TOTAL'));

}

function Footer()
{
	$con = new DB;
	$pacientes = $con->conectar();	
global $NumeroHojas;
	$this->SetY(-15);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Libro Iva Compras Triangular S.A.',0,0,'L');
	$this->Cell(0,10,'Hoja numero '.$this->PageNo(). ' de un total de {nb} hojas',0,0,'C');
// 	$this->Cell(0,10,'Total {nb}',0,0,'C');

}
// function Close()
// {
// // 	$con = new DB;
// // 	$pacientes = $con->conectar();	
// global $NumeroHojas;
// 	$this->SetY(-15);
// 	$this->SetFont('Arial','B',8);
// 	$this->Cell(100,10,'Libro Iva Compras Triangular S.A.',0,0,'L');
// 	$this->Cell(0,10,'Hoja numero '.$this->PageNo(). ' de un total de {nb} hojas',0,0,'C');
// // 	$this->Cell(0,10,'Total {nb}',0,0,'C');

// }

}

$cliente= $_SESSION['NCliente'];
$NumeroRepo=$_GET['NR'];
$Desde=$_GET['Desde'];
$Hasta=$_GET['Hasta'];

	$con = new DB;
	$Conexion = $con->conectar();	

	$strConsulta = "SELECT * from Clientes where nombrecliente = '$cliente'";
	$Conexion = mysql_query($strConsulta);
	$fila = mysql_fetch_array($Conexion);
	$pdf=new PDF('L','mm','Letter');
	$pdf->Open();
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetMargins(20,20,20);

	$Fecha=$_GET['Desde'];
	$Mes=date("n",strtotime($Fecha));
	$Ano=date("Y",strtotime($Fecha));

	$con = new DB;
	$historial = $con->conectar();	
	$strConsulta = "SELECT * FROM IvaCompras WHERE Fecha>='$Desde' AND Fecha<='$Hasta' AND Eliminado=0 ORDER BY Fecha ASC";
	

	$sql="UPDATE CierreIva SET Hojas='{nb}' WHERE Libro='IvaCompras' AND Mes='$Mes' AND Ano='$Ano'"; 
 	mysql_query($sql);
	
	$historial = mysql_query($strConsulta);
	$numfilas = mysql_num_rows($historial);
	//Calcula el total de la repo
  setlocale(LC_ALL,'es_AR');
	$Muestra=mysql_query("SELECT SUM(Total) as Total, SUM(ImporteNeto) as ImpNeto,SUM(Iva4) as Iva4,SUM(Iva2) as Iva2,
SUM(Iva3) as Iva3, SUM(Exento) as Exento, SUM(PercepcionIva) as PercecpcionIva, SUM(PercepcionIIBB) as PercepcionIIBB FROM
IvaCompras WHERE Eliminado=0 AND Fecha>='$Desde' AND Fecha<='$Hasta'");
	$row=mysql_fetch_array($Muestra);
	//$TotalRepo= money_format('%i',$row[Total]);
	$TotalRepo= '$ '.number_format($row[Total],2,",",".");
  $TotalImpNeto='$ '.number_format($row[ImpNeto],2,",",".");
	$TotalIva4='$ '.number_format($row[Iva4],2,",",".");
	$TotalIva2='$ '.number_format($row[Iva2],2,",",".");
	$TotalIva3='$ '.number_format($row[Iva3],2,",",".");
	$TotalExento='$ '.number_format($row[Exento],2,",",".");
	$TotalPercIva='$ '.number_format($row[PercepcionIva],2,",",".");
	$TotalPercIIBB='$ '.number_format($row[PercepcionIIBB],2,",",".");

for ($i=0; $i<$numfilas; $i++)
		{
			$fila = mysql_fetch_array($historial);
			$Total=number_format($fila[Total],2,",",".");
			$pdf->SetFont('Arial','',5);
			$ImporteNeto='$ '.number_format($fila[ImporteNeto],2,",",".");
            $Exento='$ '.number_format($fila[Exento],2,",",".");
            $Iva2='$ '.number_format($fila[Iva2],2,",",".");
            $Iva3='$ '.number_format($fila[Iva3],2,",",".");
            $Iva4='$ '.number_format($fila[Iva4],2,",",".");
            $PercIva='$ '.number_format($fila[PercepcionIva],2,",",".");
            $PercIIBB='$'.number_format($fila[PercepcionIIBB],2,",",".");
            $Fecha0=explode("-",$fila[Fecha],3);
			$Fecha=$Fecha0[2]."/".$Fecha0[1]."/".$Fecha0[0];
      if($i%2 == 1)
			{
				$pdf->SetFillColor(255,255,255);
    			$pdf->SetTextColor(0);
				$pdf->Row(array($Fecha, $fila['RazonSocial'], $fila['Cuit'], $fila['TipoDeComprobante'],$fila['NumeroComprobante'],$ImporteNeto,$Iva2,$Iva3,$Iva4,$Exento,$PercIva,$PercIIBB,'$ '.$Total));
			}
			else
			{
				$pdf->SetFillColor(220,220,220);
    			$pdf->SetTextColor(0);
				$pdf->Row(array($Fecha, $fila['RazonSocial'], $fila['Cuit'], $fila['TipoDeComprobante'],$fila['NumeroComprobante'],$ImporteNeto,$Iva2,$Iva3,$Iva4,$Exento,$PercIva,$PercIIBB,'$ '.$Total));
			}
		}

$pdf->SetFillColor(119,136,153);
$pdf->SetTextColor(255);
$pdf->Row(array($fila[''], $fila[''], $fila[''],$fila[''],'Totales: ',$TotalImpNeto,$TotalIva2,$TotalIva3,$TotalIva4,$TotalExento,$TotalPercIva,$TotalPercIIBB,$TotalRepo));
$pdf->Output();
?>