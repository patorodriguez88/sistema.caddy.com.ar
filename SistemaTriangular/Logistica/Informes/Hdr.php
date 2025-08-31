<?php
session_start();
require('../../fpdf/fpdf.php');
require('../../../conexion.php');
include_once "../../phpbarcode/barcode.php";    

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
    $NumeroReco=$_GET['HR'];
    $con = new DB;
    $historial = $con->conectar();	
    $strConsulta = "SELECT Recorrido,NumerodeOrden,NombreChofer FROM Logistica WHERE Recorrido='$NumeroReco' AND Eliminado='0' AND Estado in('Alta','Cargada')";
    $Dato = mysql_query($strConsulta);
    $DatoLogistica=mysql_fetch_array($Dato);  

    $sql=mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$NumeroReco'");  
    $Datosql= mysql_fetch_array($sql);
  
	$Codigo=$DatoLogistica[Recorrido];
	$_SESSION['NOrden']=$DatoLogistica['NumerodeOrden'];
	$Fecha=date('d/m/Y');
	
	$this->SetFont('Arial','B',10);
    $this->Image('../../images/LogoCaddyNoAlfa.png',110 ,8, 40 , 16,'png','');  
	$this->Text(20,14,'Caddy Yo lo llevo!',0,'C', 0);
	$this->SetFont('Arial','',10);
    $this->Text(20,19,'Cuit: 30-71534494-3',0,'C', 0);
	$this->Text(20,24,'Reconquista 4986',0,'C', 0);
	$this->Text(20,29,'www.caddy.com.ar',0,'C', 0);
	
	//FECHA
	$this->Ln(20);
 	$this->SetFont('Arial','B',15);
    $this->Text(180,9,'RECORRIDO '.$Codigo,0,'C', 0);
    $this->SetFont('Arial','',10);  
	$this->Text(180,14,'Cordoba '.$Fecha,0,'C', 0);
    $this->Text(180,19,'Nombre Chofer: '.$DatoLogistica[NombreChofer],0,1);
	$this->Text(180,24,'Orden de Salida: '.$_SESSION['NR'],0,'C', 0);
	$this->Text(180,29,'Recorrido:'.$Codigo." | ".$Datosql[Nombre],0,'C', 0);
    $this->Text(180,34,'N Hoja de Ruta: '.$_SESSION[NOrden]." | ".$Datosql[Nombre],0,'C', 0);  

    //TITULO
	$this->SetMargins(20,20,20);
 	$this->Line(20, 38, 269, 38);  //Horizontal
	$this->Line(20, 44, 269, 44);  //Horizontal

 	$this->SetFont('Arial','B',15);
	$this->Text(65,43,'HOJA DE RUTA N '.$_SESSION[NOrden]. ' | '.$Datosql[Nombre],0,'C', 0);
	if($this->PageNo() == 1){
    $this->Ln(15);
    }else{
    $this->Ln(5);
    }
    
    $this->SetWidths(array(11,16,33,35,35,35,40,45));
	$this->SetFont('Arial','B',6);
    $this->SetFillColor(39,55,70);  
    $this->SetTextColor(255);
    $this->Row(array('ORDEN','SERVICIO','REMITO', 'ORIGEN', 'DESTINO','FIRMA RECEPCION','NOMBRE Y DNI','OBSERVACIONES'));
}
	
function Footer()
{

	$this->SetY(-15);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Hoja de Ruta Caddy',0,0,'L');
	
	$this->SetY(-15);
	$this->SetX(105);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'www.caddy.com.ar',0,0,'L');

	$this->SetY(-15);
	$this->SetX(150);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Usuario:'.$_SESSION['Usuario'],0,0,'L');

    global $NumeroHojas;
    $this->SetY(-15);
    $this->SetX(220);
    $this->SetFont('Arial','B',8);
    $this->Cell(0,10,'Hoja numero '.$this->PageNo().'/{nb}',0,0,'C');
  
 }
}
    $cliente= $_SESSION['ClienteActivo'];
	$con = new DB;
	$pacientes = $con->conectar();	
	
	$pdf=new PDF('L','mm','Letter');
    $pdf->AliasNbPages();
	$pdf->Open();
	$pdf->AddPage();
	$pdf->SetMargins(20,20,20);

    $Fecha=date('d/m/Y');	
    $NumeroReco=$_GET['HR'];
	$NOrden=$_SESSION['NOrden'];
    $strConsulta = "SELECT * FROM Logistica WHERE Recorrido='$NumeroReco' AND NumerodeOrden='$NOrden' AND Eliminado='0' AND Estado in('Alta','Cargada')";
	$pacientes = mysql_query($strConsulta);
	$fila = mysql_fetch_array($pacientes);
	$Usuario=$fila[Controla];
	$Transportista=$fila[NombreChofer];
	$_SESSION['Fecha']=$fila[Fecha];
	$_SESSION['NR']=$fila[NumerodeOrden];
	$CodigoSeguimiento=$fila['CodigoSeguimiento'];
	$historial = $con->conectar();	
	$strConsulta = "SELECT * FROM Roadmap WHERE Recorrido='$NumeroReco' AND NumerodeOrden='$NOrden' AND Estado='Abierto' AND Eliminado='0' ORDER BY Posicion";
	$historial = mysql_query($strConsulta);
	$numfilas = mysql_num_rows($historial);
	$sql=mysql_query("SELECT nombrecliente FROM Clientes WHERE NdeCliente='$dato[idCliente]'");
    $sqlrespuesta=mysql_fetch_array($sql);
    

    for ($i=0; $i<$numfilas; $i++)
	{
        $fila = mysql_fetch_array($historial);
        $sql=mysql_query("SELECT RazonSocial,DomicilioOrigen,Retirado,NumeroComprobante,TelefonoOrigen,TelefonoDestino,CodigoProveedor,CodigoSeguimiento FROM TransClientes WHERE CodigoSeguimiento='$fila[Seguimiento]' AND Eliminado='0'");
          $sqlrespuesta=mysql_fetch_array($sql);
    
        $code = $sqlrespuesta[CodigoSeguimiento];
        
		barcode('codigos/'.$code.'.png', $code, 20, 'horizontal', 'code39', true);		
	    
     if($sqlrespuesta[Retirado]==0){
      $Retiro='Retirar';  
      }else{
      $Retiro='Entregar';    
      }
    $Origen=$sqlrespuesta[RazonSocial];
    $Origen.=' | Dir.: '.$sqlrespuesta[DomicilioOrigen]." ";
    $Origen.='Tel.:'.$sqlrespuesta[TelefonoOrigen];    
    $Destino=$fila[Cliente];
    $Destino.=' | Dir: '.$fila[Localizacion]." ";
    $Destino.='Tel.:'.$fila[Celular];
      
    $pdf->SetFont('Arial','B',7);
    $b=40;
        
    if(fmod($i, 5) == 0 ){        
        if($i>=5){
        $pdf->AddPage();
        }
        $a=$pdf->GetY()+2;
        $b=46;
        $c=44;
    }else{
        $a=$pdf->GetY()+4; 
        $b=35;
        $c=46;
    }
        
    $Codigo_barras=$pdf->Image('codigos/'.$code.'.png',$c, $a,$b,22,'PNG');            
    $pdf->SetFillColor(255,255,255);
    $pdf->SetTextColor(0);
    $pdf->Row(array($fila[Posicion],$sqlrespuesta[NumeroComprobante].''.$Retiro.' '.$fila[Hora],$Codigo_barra,$Origen,$Destino,$fila[Observaciones],'',''));

    }

$pdf->Output();

?>