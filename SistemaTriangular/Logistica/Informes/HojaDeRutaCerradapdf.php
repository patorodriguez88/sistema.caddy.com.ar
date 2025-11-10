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
	$NumeroOrden=$_GET['ON'];
  $con = new DB;
	$historial = $con->conectar();
    
  //LOGISTICA
  $sqllogistica = mysql_query("SELECT * FROM Logistica WHERE NumerodeOrden='$NumeroOrden' AND Eliminado='0'");
	$fila = mysql_fetch_array($sqllogistica);
	$Usuario=$fila[Controla];
	$Transportista=$fila[NombreChofer];
  //RECORRIDOS  
  $sql=mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$fila[Recorrido]'");  
  $Datosql= mysql_fetch_array($sql);
  //TOTAL DE SERVICIOS
  $strConsulta = mysql_query("SELECT COUNT(id)as Total FROM HojaDeRuta WHERE NumerodeOrden='$NumeroOrden'");
  $row=mysql_fetch_array($strConsulta);
//   $TotalServicios=mysql_num_rows($Dato);  

  $sqlseguimiento=mysql_query("SELECT Entregado FROM Seguimiento WHERE CodigoSeguimiento='$row[Seguimiento]'");
  $datosqlseguimiento=mysql_fetch_array($sqlseguimiento);  
  if($datosqlseguimiento[Entregado]=='1'){
  $Estado='Si';  
  }else{
  $Estado='No';    
  }  
	$Codigo=$row[Recorrido];
	$_SESSION['NOrden']=$row[1];
	$Fecha=date('d/m/Y');
	
	$this->SetFont('Arial','',10);
  $this->Image('../../images/LogoCaddyNoAlfa.png',110 ,8, 40 , 16,'png','');  
	$this->Text(20,14,'Caddy Yo lo llevo!',0,'C', 0);
	$this->Text(20,19,'Cuit: 30-71534494-3',0,'C', 0);
	$this->Text(20,24,'Reconquista 4986',0,'C', 0);
	$this->Text(20,29,'www.caddy.com.ar',0,'C', 0);
	
	//FECHA
	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(185,14,'Cordoba '.$Fecha,0,'C', 0);
  $this->Text(185,19,'Nombre Chofer: '.$Transportista,0,1);
	$this->Text(185,24,'Orden de Salida: '.$_GET['ON'],0,'C', 0);
	$this->Text(185,29,'Recorrido:'.$Codigo." | ".$Datosql[Nombre],0,'C', 0);
  $this->Text(185,34,'Km. Recorridos: '.$fila[KilometrosRecorridos].' Km.',0,'C', 0);  
  $this->Text(185,39,'Servicios: '.$row[Total],0,'C', 0);  

  //TITULO
	$this->SetMargins(20,20,20);
 	$this->Line(20, 42, 252, 42);  //Horizontal
	$this->Line(20, 48, 252, 48);  //Horizontal

 	$this->SetFont('Arial','',15);
	$this->Text(65,47,'HOJA DE RUTA RECORRIDO '.$Codigo." | ".$Datosql[Nombre],0,'C', 0);
	$this->Ln(20);
  $this->SetWidths(array(12,12,12,12,65,70,16,34));
	$this->SetFont('Arial','B',6);
	$this->SetFillColor(119,136,153);
  $this->SetTextColor(255);

    		for($i=0;$i<1;$i++)
			{
				$this->Row(array('ORDEN','RETIRO','HORA', 'REMITO', 'ORIGEN', 'DESTINO','ENTREGADO','OBSERVACIONES'));
			}
    
}
	
function Footer()
{

	$this->SetY(-15);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Hoja de ruta Caddy',0,0,'L');
	
	$this->SetY(-15);
	$this->SetX(115);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'www.caddy.com.ar',0,0,'L');

	$this->SetY(-15);
	$this->SetX(220);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Usuario:'.$_SESSION['Usuario'],0,0,'L');

 }
}
  $cliente= $_SESSION['ClienteActivo'];
	$con = new DB;
	$pacientes = $con->conectar();	
	
	$pdf=new PDF('L','mm','Letter');
	$pdf->Open();
	$pdf->AddPage();
	$pdf->SetMargins(20,20,20);
// 	$Fecha=$_SESSION['FechaHojaDeRuta'];
  $Fecha=date('d/m/Y');	
  $NumeroOrden=$_GET['ON'];
	
//   $strConsulta = "SELECT * FROM Logistica WHERE NumerodeOrden='$NumeroOrden' AND Eliminado='0' ";
// 	$pacientes = mysql_query($strConsulta);
// 	$fila = mysql_fetch_array($pacientes);
// 	$Usuario=$fila[Controla];
// 	$Transportista=$fila[NombreChofer];
	$_SESSION['Fecha']=$fila[Fecha];
	$_SESSION['NR']=$fila[NumerodeOrden];
	$CodigoSeguimiento=$fila['CodigoSeguimiento'];
	$historial = $con->conectar();	
	$strConsulta = "SELECT * FROM HojaDeRuta WHERE NumerodeOrden='$NumeroOrden' AND Estado='Cerrado' AND Eliminado='0' ORDER BY Posicion";
	$historial = mysql_query($strConsulta);
	$numfilas = mysql_num_rows($historial);
	$sql=mysql_query("SELECT nombrecliente FROM Clientes WHERE NdeCliente='$dato[idCliente]'");
  $sqlrespuesta=mysql_fetch_array($sql);
  $N=1;

	for ($i=0; $i<$numfilas; $i++)
	{
			$fila = mysql_fetch_array($historial);
			$sql=mysql_query("SELECT RazonSocial,DomicilioOrigen,Retirado,NumeroComprobante,TelefonoOrigen,TelefonoDestino FROM TransClientes WHERE CodigoSeguimiento='$fila[Seguimiento]' AND Eliminado='0'");
      $sqlrespuesta=mysql_fetch_array($sql);
	    if($sqlrespuesta[Retirado]==0){
      $Retiro='Retirar';  
      }else{
      $Retiro='Entregar';    
      }
    $Origen='Dir.: '.$sqlrespuesta[DomicilioOrigen]." ";
    $Origen.='('.$sqlrespuesta[RazonSocial].") ";
    $Origen.='Tel.:'.$sqlrespuesta[TelefonoOrigen];    
    $Destino='Dir: '.$fila[Localizacion]." ";
    $Destino.='('.$fila[Cliente].") ";
    $Destino.='Tel.:'.$fila[Celular];

    $pdf->SetFont('Arial','',5);
					
			if($i%2 == 1)
			{
				$pdf->SetFillColor(255,255,255);
    		$pdf->SetTextColor(0);
        $pdf->Row(array($N,$Retiro,$fila[Hora], $sqlrespuesta[NumeroComprobante],$Origen,$Destino,$Estado,$fila[Observaciones]));
      }
			else
			{
				$pdf->SetFillColor(220,220,220);
   			$pdf->SetTextColor(0);
        $pdf->Row(array($N,$Retiro,$fila[Hora], $sqlrespuesta[NumeroComprobante],$Origen,$Destino,$Estado,$fila[Observaciones]));

//         $pdf->Row(array($i,$Retiro,$fila[Hora], $sqlrespuesta[RazonSocial], $fila[Cliente], $fila[NumerodeOrden], $fila[Localizacion],$fila[Observaciones],$fila[Celular]));
			}
	$N++;	
  }

$pdf->Output();

?>