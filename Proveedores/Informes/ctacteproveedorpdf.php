<?php
session_start();
require('../../fpdf/fpdf.php');
class DB{
	var $conect;
	var $BaseDatos;
	var $Servidor;
	var $Usuario;
	var $Clave;
	function DB(){
    $this->BaseDatos = "dinter6_triangular";
		$this->Servidor = "localhost";
		$this->Usuario = "dinter6_prodrig";
		$this->Clave = "pato@4986";
		}
	 function conectar() {
		if(!($con=@mysql_connect($this->Servidor,$this->Usuario,$this->Clave))){
			echo"<h1> [:(] Error al conectar a la base de datos</h1>";	
			exit();
		}
		if (!@mysql_select_db($this->BaseDatos,$con)){
			echo "<h1> [:(] Error al seleccionar la base de datos</h1>";  
			exit();
		}
		$this->conect=$con;
		return true;	
	}
}
// require('../../../conexion.php');
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
	$h=4*$nb;
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
	
	//FECHA
	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(130,14,utf8_decode("Córdoba ").date('d.m.Y'),0,'C', 0);
	$idProveedor=$_GET[id];
	$con = new DB;
	$conexion = $con->conectar();	
	$strConsulta = "SELECT * FROM TransProveedores WHERE idProveedor='$idProveedor'";
	$Usuario = mysql_query($strConsulta);
	$fila = mysql_fetch_array($Usuario);
  $sqltotales = "SELECT SUM(Debe)as TotalDebe,SUM(Haber)as TotalHaber FROM TransProveedores WHERE Eliminado=0 AND idProveedor='$idProveedor'";
	$resultado = mysql_query($sqltotales);
	$row = mysql_fetch_array($resultado);
  $SaldoActual=number_format($row[TotalDebe]-$row[TotalHaber],2,',','.');

    //REMITO NUMERO
	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(130,19,'Proveedor: '.$fila[RazonSocial],0,'C', 0);
	
	$this->Text(130,24,'Saldo Actual: $ '.$SaldoActual,0,'C', 0);

	$this->Ln(20);
 	$this->SetFont('Arial','',18);
	$this->Text(80,44,'Resumen de Cuentas ',0,'B', 0);

  }
	
function Footer()
{
	$this->SetY(-15);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Caddy Yo lo llevo!.',0,0,'L');
	
	$this->SetY(-15);
	$this->SetX(90);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'www.caddy.com.ar',0,0,'L');
	$this->SetY(-15);
	$this->SetX(170);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Usuario:'.$fila['Usuario'],0,0,'L');
 }
}
	$con = new DB;
	$conexion = $con->conectar();	

	$pdf=new PDF('P','mm','Letter');
	$pdf->Open();
	$pdf->AddPage();
  $pdf->SetMargins(20,10,10);

  $pdf->SetFont('Arial','B',14);
	$pdf->Text(130,7,'RESUMEN DE CUENTAS',0,'C', 0);
	$pdf->SetFont('Arial','B',10);

//AFORO
	$pdf->SetFillColor(255,255,255);
  $pdf->SetTextColor(0);
	$pdf->Cell(0,6,'',0,1,'C',true);
  $pdf->SetWidths(array(20, 75, 25, 25,25));
	$pdf->SetFont('Arial','B',7);
	$pdf->SetFillColor(100,100,100);

  $pdf->SetTextColor(255);
		for($i=0;$i<1;$i++)
			{
				$pdf->Row(array('FECHA','COMPROBANTE','DEBE', 'HABER','SALDO'));
			}
	$historial = $con->conectar();	
  $idProveedor=$_GET[id];
	$strConsulta = "SELECT Fecha,Concepto,TipoDeComprobante,NumeroComprobante,Debe,Haber,(Debe-Haber)as Saldo FROM TransProveedores WHERE Eliminado=0 AND idProveedor='$idProveedor'";
	$historial = mysql_query($strConsulta);
	$numfilas = mysql_num_rows($historial);
	//Calcula el total de la repo
  setlocale(LC_ALL,'es_AR');
	$i=0;
  $row=mysql_fetch_array($Muestra);
  $Debe=number_format($row[Debe],2,',','.');
  $Haber=number_format($row[Haber],2,',','.');
  $Saldo=number_format($row[Saldo],2,',','.');  
  $acumulado=0;
for ($i=0; $i<$numfilas; $i++)
	    {
  
        $filaVentas = mysql_fetch_array($historial);
        $acumulado=$acumulado+$filaVentas[Debe]-$filaVentas[Haber];
        if($filaVentas[Haber]>0){
        $Tipo=$filaVentas[Concepto].' Ref.: '.$filaVentas[TipoDeComprobante];
        }else{
        $Tipo=$filaVentas[TipoDeComprobante];  
        }
        $ImporteNeto_label=number_format($filaVentas[ImporteNeto],2,',','.');
        $Iva_precio_label=number_format($filaVentas[Iva3],2,',','.'); 
        $Total=number_format($filaVentas[Total],2,',','.');
      if($i%2 == 1)
			{
        $pdf->SetFont('Arial','B',5);
        $pdf->SetTextColor(0,0,0);//color negro
        $pdf->SetFillColor(255,255,255);  
				$pdf->Row(array($filaVentas['Fecha'], $Tipo.' '.$filaVentas['NumeroComprobante'],'$ '.number_format($filaVentas['Debe'],2,',','.'),'$ '.number_format($filaVentas['Haber'],2,',','.'),'$ '.number_format($acumulado,2,',','.')));
			}
			else
			{
        $pdf->SetFont('Arial','B',5);
        $pdf->SetFillColor(255,255,255);  
        $pdf->SetTextColor(0);
				$pdf->Row(array($filaVentas['Fecha'], $Tipo.' '.$filaVentas['NumeroComprobante'],'$ '.number_format($filaVentas['Debe'],2,',','.'),'$ '.number_format($filaVentas['Haber'],2,',','.'),'$ '.number_format($acumulado,2,',','.')));
			}
//   $acumulado=$acumulado+($filaVentas[Debe]-$filaVentas[Haber]);
  }   
	$sqltotales = "SELECT SUM(Debe)as TotalDebe,SUM(Haber)as TotalHaber FROM TransProveedores WHERE Eliminado=0 AND idProveedor='$idProveedor'";
	$resultado = mysql_query($sqltotales);
	$row = mysql_fetch_array($resultado);
  $TotalDebe=number_format($row[TotalDebe],2,',','.');
  $TotalHaber=number_format($row[TotalHaber],2,',','.');
  $SaldoActual=number_format($row[TotalDebe]-$row[TotalHaber],2,',','.');

  $pdf->SetFont('Arial','B',6);
	$pdf->Row(array($filaVentas[''],'TOTALES: ','$ '.$TotalDebe,'$ '.$TotalHaber,'$ '.$SaldoActual));

  $pdf->Output();  

?>