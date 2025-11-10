<?php
// include class
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


// extend class
class KodePDF extends FPDF {
    protected $fontName = 'Arial';
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



    function renderTitle($text) {
    $this->SetFont('Arial','',8);
    $this->Image('../../images/LogoCaddyNoAlfa.png',16 ,8, 40 , 16,'png','');  
    $this->Text(20,26,'Triangular S.A.',0,'C', 0);
    $this->Text(20,31,'Cuit: 30-71534494-3',0,'C', 0);
    $this->Text(20,36,utf8_decode('Domicilio: Reconquista 4986, Córdoba'),0,'C', 0);
    $this->Text(90,36,'www.caddy.com.ar',0,'C', 0);
      
    // //DESDE ACA EL GENERADOR DE CODIGO QR 
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';
    include_once "../../phpqrcode/qrlib.php";    
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
    $filename = $PNG_TEMP_DIR.'test.png';
    $matrixPointSize = 10;
    $errorCorrectionLevel = 'L';

    $filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
    QRcode::png('https://www.caddy.com.ar/seguimiento.html?codigo='.$Codigo, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 

    $this->Image($PNG_WEB_DIR.basename($filename), 95 ,10, 20 , 20,'png','');
    // //HASTA ACA EL GENERADOR DE CODIGO QR	
    $this->Ln(20);
    $this->SetFont('Arial','',18);
    $this->Text(80,44,'GUIA DE CARGA',0,'C', 0);
    
    }

    function Footer()
    {
      $this->SetY(-15);
      $this->SetFont('Arial','B',8);
      $this->Cell(100,10,'Guia de Carga Caddy Yo lo llevo!.',0,0,'L');

      $this->SetY(-15);
      $this->SetX(90);
      $this->SetFont('Arial','B',8);
      $this->Cell(100,10,'www.caddy.com.ar',0,0,'L');
      $this->SetY(-15);
      $this->SetX(170);
      $this->SetFont('Arial','B',8);
      $this->Cell(100,10,'Usuario:'.$fila['Usuario'],0,0,'L');
    }
  
    function Observaciones($text) {
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->fontName, 'B', 6);
        $this->Cell(0, 10, utf8_decode($text), 0, 1);
        $this->Ln();
    }

    function renderText($text) {
        $this->SetTextColor(51, 51, 51);
        $this->SetFont($this->fontName, '', 12);
        $this->MultiCell(0, 7, utf8_decode($text), 0, 1);
        $this->Ln();
    }
}

// create document
$pdf = new KodePDF();
$con = new DB;
$sql = $con->conectar();	
$sqlConsulta = "SELECT * FROM HojaDeRuta WHERE Recorrido='$_GET[Recorrido]' AND Estado='Abierto' AND Eliminado='0'";
$datoConsulta = mysql_query($sqlConsulta);

while($filah = mysql_fetch_array($datoConsulta)){

  
  
  
}
// output file
$pdf->Output();
?>