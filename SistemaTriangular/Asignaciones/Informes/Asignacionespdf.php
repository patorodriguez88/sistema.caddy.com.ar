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
	$NumeroRepo=$_GET['NR'];
  $con = new DB;
	$historial = $con->conectar();	

  $this->SetFont('Arial','',10);
 	$this->Image('../../images/caddy.jpg' , 120 ,8, 60 , 30,'JPG', '');
	$this->Text(20,14,'Triangular S.A.',0,'C', 0);
	$this->Text(20,19,'Cuit: 30-71534494-3',0,'C', 0);
	$this->Text(20,24,'Reconquista 4986, Cordoba - Argentina',0,'C', 0);
	$this->Text(20,29,'www.caddy.com.ar',0,'C', 0);
	
	//FECHA
	$this->Ln(20);
  $Fecha=$_SESSION[fechamapa];
  $Recorrido=$_SESSION[recorridomapa];
  $Repartidor=$_SESSION[Chofer];

  //RESUMEN
if($Repartidor=='Todos'){
$sqlsumarentregas=mysql_query("SELECT id,CodigoSeguimiento FROM TransClientes WHERE FechaEntrega='$Fecha' AND Eliminado=0");
$sumarentregas=mysql_num_rows($sqlsumarentregas);
$e=0;
$ne=0;
while($row=mysql_fetch_array($sqlsumarentregas)){
$sqlSeguimiento=mysql_query("SELECT id,Fecha,Hora,Entregado FROM Seguimiento WHERE id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]')");
$resultSeguimiento=mysql_fetch_array($sqlSeguimiento);
  if($resultSeguimiento[Entregado]==1){
  $e=$e+1;  
  }else{
  $ne=$ne+1;  
  }
}
  
$sqlkm=mysql_query("SELECT SUM(KilometrosRecorridos)as Km FROM Logistica WHERE Fecha='$Fecha' AND Eliminado='0'");
$datokm=mysql_fetch_array($sqlkm);
// $sqlRecorridos=mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$_POST[recorrido_t]'");
// $DatosRecorridos=mysql_fetch_array($sqlRecorridos);
$ef=number_format(($e/$sumarentregas)*100,2,',','.');  
  
  $this->SetFont('Arial','',10);
 	$this->Image('../../images/caddy.jpg' , 120 ,8, 60 , 30,'JPG', '');
// 	$this->Text(20,14,$Recorrido,0,'C', 0);
	$this->Text(220,14,'Kilomentos: '.$datos[Km],0,'C', 0);
	$this->Text(220,19,'Total Paquetes: '.$sumarentregas,0,'C', 0);
	$this->Text(220,24,'Entregados: '.$e,0,'C', 0);
	$this->Text(220,29,'No Entregados: '.$ne,0,'C', 0);
	$this->Text(220,34,'Efectividad: '.$ef. ' %',0,'C', 0);
  
  
}elseif(($Fecha<>'')AND($Recorrido<>'')){
$sqlBuscoChofer=mysql_query("SELECT NombreChofer,KilometrosRecorridos,Patente FROM Logistica WHERE Fecha='$fecha' AND Recorrido='$recorrido'");
$NombreChofer=mysql_fetch_array($sqlBuscoChofer);
$sqlRecorridos=mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$recorrido'");
$DatosRecorridos=mysql_fetch_array($sqlRecorridos);
$sqlsumarentregas=mysql_query("SELECT id,CodigoSeguimiento FROM TransClientes WHERE FechaEntrega='$fecha' AND Recorrido='$recorrido' AND Eliminado=0");
$sumarentregas=mysql_num_rows($sqlsumarentregas);
$e=0;
$ne=0;
while($row=mysql_fetch_array($sqlsumarentregas)){
$sqlSeguimiento=mysql_query("SELECT id,Fecha,Hora,Entregado FROM Seguimiento WHERE id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]')");
$resultSeguimiento=mysql_fetch_array($sqlSeguimiento);
  if($resultSeguimiento[Entregado]==1){
  $e=$e+1;  
  }else{
  $ne=$ne+1;  
  }
}
// echo "<div style='clear: both'></div>";  
// echo "<div style='float:left;margin:0;'>";
// echo "<table class='login' style='margin-top:10px'>";
// echo "<caption style='font-size:15px'>Datos Recorrido</caption>";
// echo "<tr style='font-size:12px'><td>Recorrido:</td><td style='width:270px'>$DatosRecorridos[Nombre] ($_POST[recorrido_t])</td></tr>";
// echo "<tr style='font-size:12px'><td>Responsable:</td><td>$NombreChofer[NombreChofer]</td></tr>"; 
// echo "<tr style='font-size:12px'><td>Dominio:</td><td>$NombreChofer[Patente]</td></tr>";
// echo "<tr style='font-size:12px'><td>Km. Recorridos:</td><td>$NombreChofer[KilometrosRecorridos]</td></tr>";
// echo "<tr style='font-size:12px'><td>Total:</td><td>$sumarentregas</td></tr>";
// echo "<tr style='font-size:12px'><td>Entregados:</td><td style='color:green'>$e</td></tr>";
// echo "<tr style='font-size:12px'><td>No Entreados:</td><td style='color:red'>$ne</td></tr>";
// $ef=number_format(($e/$sumarentregas)*100,2,',','.');  
// echo "<tr style='font-size:12px'><td>Efectividad:</td><td style='color:blue'>$ef %</td></tr>";  
// echo "</table>";
// echo "</div>";  
}
  
  
  $FechaEntrega=explode('-',$Fecha,3);
  $FechaEntregab=$FechaEntrega[2]."/".$FechaEntrega[1]."/".$FechaEntrega[0];
  
  $this->SetFont('Arial','',10);
	$this->Text(180,14,utf8_decode('Córdoba ').date('d/m/Y'),0,'C', 0);
	$this->Text(180,19,'Repartidor: '.utf8_decode($Repartidor),0,'C', 0);
	$this->Text(180,24,'Recorrido: '.$Recorrido,0,'C', 0);
	$this->Text(180,29,'Reparto: '.$FechaEntregab,0,'C', 0);

	$this->Ln(40);
 	$this->SetFont('Arial','',14);
	$this->Text(115,43,'ENTREGAS POR RECORRIDO',0,'C', 0);

 	$this->Line(20, 38, 262, 38);  //Horizontal
	$this->Line(20, 45, 262, 45);  //Horizontal
	$this->SetWidths(array(15, 16,16, 40, 45, 13,18,13,30,35,12));
	
  $this->SetFont('Arial','B',8);
	$this->SetFillColor(119,136,153);
  $this->SetTextColor(255);
  $this->SetMargins(20,20,20);
  $this->Ln(-22);
  $this->Row(array('FECHA','N.COMP.','ID PROV.','CLIENTE DESTINO','DOMICILIO','CANT.','ENTREGA','HORA','ESTADO','OBSERVACIONES','RESP.'));

  
}
	
function Footer()
{

	$this->SetY(-15);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Triangular S.A.',0,0,'L');
	
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
	$con = new DB;
	$pacientes = $con->conectar();	
	$Usuario=$fila['Usuario'];

	$pdf=new PDF('L','mm','Letter');
	$pdf->Open();
	$pdf->AddPage();
	$CodigoSeguimiento=$fila['CodigoSeguimiento'];
	$historial = $con->conectar();
 //IDENTIFICO LAS VARIABLES DE SESSION
  $Fecha=$_SESSION[fechamapa];
  $Recorrido=$_SESSION[recorridomapa];
  $Repartidor=$_SESSION[Chofer];

//DESDE ACA CUADRO RESUMEN



  if($Repartidor=='Todos'){
  $strConsulta="SELECT * FROM TransClientes WHERE FechaEntrega='$Fecha' AND Eliminado=0 ORDER BY idClienteDestino";
  }else{
  $strConsulta="SELECT * FROM TransClientes WHERE FechaEntrega='$Fecha' AND Recorrido='$Recorrido' AND Eliminado=0 ORDER BY idClienteDestino";
  }
    // 	$strConsulta = "SELECT Cuenta,NombreCuenta,SUM(Debe)AS Debe,SUM(Haber)AS Haber FROM Tesoreria WHERE Eliminado=0 GROUP BY Cuenta";
	$historial = mysql_query($strConsulta);
	$numfilas = mysql_num_rows($historial);
	//Calcula el total de la repo
  setlocale(LC_ALL,'es_AR');
	for ($i=0; $i<$numfilas; $i++)
	{
	  $fila = mysql_fetch_array($historial);
    $sqlSeguimiento=mysql_query("SELECT id,Fecha,Hora,Observaciones,Estado,Entregado,Usuario FROM Seguimiento WHERE id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$fila[CodigoSeguimiento]')");
    
    $sqlbusconcliente=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$fila[ClienteDestino]'");
    $NCliente=mysql_fetch_array($sqlbusconcliente);

    $resultSeguimiento=mysql_fetch_array($sqlSeguimiento);
    $Fecha=explode('-',$fila[FechaEntrega],3);
    $Fechab=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
    if($resultSeguimiento[Entregado]==1){
    $Entregado='Si';
    }else{
    $Entregado="No";  
    }

    $pdf->SetFont('Arial','',6);
      if($i%2 == 1)
			{
				$pdf->SetFillColor(255,255,255);
    		$pdf->SetTextColor(0);
				$pdf->Row(array($Fechab, $fila['NumeroComprobante'],$NCliente[idProveedor],$fila['ClienteDestino'],$fila['DomicilioDestino'],$fila['Cantidad'],$Entregado,$resultSeguimiento[Hora],$resultSeguimiento[Estado],$resultSeguimiento[Observaciones],$resultSeguimiento[Usuario]));
			}
			else
			{
				$pdf->SetFillColor(220,220,220);
    		$pdf->SetTextColor(0);
				$pdf->Row(array($Fechab, $fila['NumeroComprobante'],$NCliente[idProveedor],$fila['ClienteDestino'],$fila['DomicilioDestino'],$fila['Cantidad'],$Entregado,$resultSeguimiento[Hora],$resultSeguimiento[Estado],$resultSeguimiento[Observaciones],$resultSeguimiento[Usuario]));
			}
		}
// 	$pdf->Row(array($fila[''], $fila[''], $fila[''], 'Total:', $TotalRepo));
$pdf->Output();
?>