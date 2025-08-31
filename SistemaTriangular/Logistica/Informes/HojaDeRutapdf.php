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
    $cliente= $_SESSION['ClienteActivo'];
    $NumeroReco=$_GET['HR'];
    $con = new DB;
    $historial = $con->conectar();	
    $strConsulta = "SELECT Recorrido,NumerodeOrden,NombreChofer FROM Logistica WHERE Recorrido='$NumeroReco' AND Estado = 'Cargada' AND Eliminado='0'";
    $Dato = mysql_query($strConsulta);
    $DatoLogistica=mysql_fetch_array($Dato);  

    $sql=mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$NumeroReco'");  
    $Datosql= mysql_fetch_array($sql);
  
	$Codigo=$DatoLogistica[Recorrido];
	$_SESSION['NOrden']=$DatoLogistica[NumerodeOrden];
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
 	$this->SetFont('Arial','',10);
	$this->Text(170,14,'Cordoba '.$Fecha,0,'C', 0);
    $this->Text(170,19,'Nombre Chofer: '.$DatoLogistica[NombreChofer],0,1);
	$this->Text(170,24,'Orden de Salida: '.$_SESSION['NR'],0,'C', 0);
	$this->Text(170,29,'Recorrido:'.$Codigo." | ".$Datosql[Nombre],0,'C', 0);
    $this->Text(170,34,'N Hoja de Ruta: '.$DatoLogistica[NumerodeOrden]." | ".$Datosql[Nombre],0,'C', 0);  

    // //TITULO
	$this->SetMargins(20,20,20);
 	$this->Line(20, 38, 266, 38);  //Horizontal
	$this->Line(20, 44, 266, 45);  //Horizontal

 	$this->SetFont('Arial','B',15);
	$this->Text(65,43,'HOJA DE RUTA N '.$DatoLogistica[NumerodeOrden]. ' | '.$Datosql[Nombre],0,'C', 0);
	$this->Ln(20);
    $this->SetWidths(array(5,20,33,35,35,35,40,45));
	$this->SetFont('Arial','B',6);
    $this->SetFillColor(39,55,70);  
    $this->SetTextColor(255);
    $this->Row(array('N','SERVICIO','REMITO', 'ORIGEN', 'DESTINO','OBSERVACIONES','FIRMA RECEPCION','NOMBRE Y DNI'));
    
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
    $strConsulta = "SELECT * FROM Logistica WHERE Recorrido='$NumeroReco' AND NumerodeOrden='$NOrden' AND Eliminado='0' ";
	$pacientes = mysql_query($strConsulta);
	$fila = mysql_fetch_array($pacientes);
	$Usuario=$fila['Controla'];
	$Transportista=$fila['NombreChofer'];
	$_SESSION['Fecha']=$fila['Fecha'];
	$_SESSION['NR']=$fila['NumerodeOrden'];
	$CodigoSeguimiento=$fila['CodigoSeguimiento'];
	$historial = $con->conectar();	
	
    $strConsulta = "SELECT HojaDeRuta.Cliente,HojaDeRuta.Seguimiento,HojaDeRuta.Localizacion,HojaDeRuta.Celular,HojaDeRuta.Hora,HojaDeRuta.Observaciones 
    FROM HojaDeRuta 
    INNER JOIN TransClientes ON HojaDeRuta.Seguimiento=TransClientes.CodigoSeguimiento 
    WHERE HojaDeRuta.Recorrido='$NumeroReco' AND HojaDeRuta.Estado='Abierto' AND HojaDeRuta.Eliminado='0' AND HojaDeRuta.Devuelto='0' AND Seguimiento<>'' ORDER BY if(TransClientes.Retirado=1,HojaDeRuta.Posicion,HojaDeRuta.Posicion_retiro)";
	
    $historial = mysql_query($strConsulta);

	$numfilas = mysql_num_rows($historial);
	
    $sql=mysql_query("SELECT nombrecliente FROM Clientes WHERE NdeCliente='$dato[idCliente]'");
    $sqlrespuesta=mysql_fetch_array($sql);

	for ($i=1; $i<=$numfilas; $i++)
	{
	
      $fila = mysql_fetch_array($historial);

	  $sql=mysql_query("SELECT CodigoSeguimiento,RazonSocial,DomicilioOrigen,Retirado,NumeroComprobante,TelefonoOrigen,TelefonoDestino,CodigoProveedor FROM TransClientes WHERE CodigoSeguimiento='$fila[Seguimiento]' AND Eliminado='0'");
      $sqlrespuesta=mysql_fetch_array($sql);
	    if($sqlrespuesta['Retirado']==0){
      $Retiro='Retirar';  
      }else{
      $Retiro='Entregar';    
      }
    $Origen=$sqlrespuesta['RazonSocial'];
    $Origen.=' | Dir.: '.$sqlrespuesta['DomicilioOrigen']." ";
    $Origen.='Tel.:'.$sqlrespuesta['TelefonoOrigen'];    
    $Destino=$fila['Cliente'];
    $Destino.=' | Dir: '.$fila['Localizacion']." ";
    $Destino.='Tel.:'.$fila['Celular'];
      
    $pdf->SetFont('Arial','B',6);
    					
    if($i%2 == 1){			
        $pdf->SetFillColor(255,255,255);
        $pdf->SetTextColor(0);
        $pdf->Row(array($i,$sqlrespuesta['NumeroComprobante'].' '.$Retiro.' '.$fila['Hora'],'N Venta '.$sqlrespuesta['NumeroComprobante'].'      Id.Prov.:  '.$sqlrespuesta['CodigoProveedor'].'   Codigo Seguimiento: '.$sqlrespuesta['CodigoSeguimiento'],$Origen,$Destino,$fila['Observaciones'],'',''));
    }else{
        $pdf->SetFillColor(255,255,255);
        $pdf->SetTextColor(0);
        $pdf->Row(array($i,$sqlrespuesta['NumeroComprobante'].' '.$Retiro.' '.$fila['Hora'],'N Venta '.$sqlrespuesta['NumeroComprobante'].'      Id.Prov.:  '.$sqlrespuesta['CodigoProveedor'].'   Codigo Seguimiento: '.$sqlrespuesta['CodigoSeguimiento'],$Origen,$Destino,$fila['Observaciones'],'',''));
    }
    }

$pdf->Output();

?>