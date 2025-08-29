<?php
ob_start();
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
	$Id=$_GET['NO'];
	$strConsulta = "SELECT * FROM Logistica WHERE NumerodeOrden='$Id'";
	$Dato = mysql_query($strConsulta);
  
	while($row=mysql_fetch_row($Dato)){
  $sqlRecorrido = mysql_query("SELECT * FROM Recorridos WHERE Numero='$row[9]'");
	$sqlR = mysql_fetch_array($sqlRecorrido);
  $Estado=$row[25];  
  $NumeroOrden=$row[1];
	$Fecha=$row[2];
	$Hora=$row[3];	
	$Controla=$row[4];
	$Dominio=$row[5];
	$_SESSION['Dominio']=$row[5];	
	$Kilometros=$row[6];	
	$Chofer=$row[7];
	$Chofer2=$row[8];	
	$Recorrido=$row[9];	
	$_SESSION['Recorrido']=$row[9];	
	$FechaVencSeguro=$row[13];	
	$arrayFechaSeguro=explode('-',$FechaVencSeguro,3);
	$FechaSeguro=$arrayFechaSeguro[2]."/".$arrayFechaSeguro[1]."/".$arrayFechaSeguro[0];
	$Observaciones=$row[24];
	$NivelCombustible=$row[29];	
	$FechaVencLicencia=$row[10];	
	$arrayFechaVencLicencia=explode('-',$FechaVencLicencia,3);
	$FechaVencLicencia2=$arrayFechaVencLicencia[2]."/".$arrayFechaVencLicencia[1]."/".$arrayFechaVencLicencia[0];
	
	$arrayfecha=explode('-',$Fecha,3);
	$Fecha2=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
	}

	$this->SetFont('Arial','',10);
	$this->Text(20,14,'Caddy',0,'C', 0);
	$this->Text(20,19,'Cuit: 30-71534494-3',0,'C', 0);
	$this->Text(20,24,'Domicilio: Reconquista 4986 ',0,'C', 0);
	$this->Text(20,29,'www.caddy.com.ar',0,'C', 0);
	 	$this->SetFont('Arial','B',15);
  $this->Text(90,22,''.$Estado,0,'C', 0);
	
	
	//FECHA
// 	$fecha=date('d/m/Y');
	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(150,14,'Orden Num: '.$NumeroOrden,0,'C', 0);

	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(150,19,'Fecha:'.$Fecha2,0,'C', 0);

	//REMITO NUMERO
	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(150,24,'Hora: '.$Hora,0,'C', 0);

	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(150,29,'Controla: '.$Controla,0,'C', 0);

// 	$this->Ln(20);
//  	$this->SetFont('Arial','B',14);
// 	$this->Text(75,40,'CONTROL DE VEHICULOS',0,'C', 0);

	$this->Ln(20);
 	$this->SetFont('Arial','B',9);
	$this->Text(40,47,'VEHICULO',0,'C', 0);
	$this->Text(140,47,'CHOFER',0,'C', 0);

	$this->SetFont('Arial','B',10);
	$this->Text(20,55,'Patente: '.$Dominio,0,'C', 0);
	$this->SetFont('Arial','',10);
	$this->Text(20,60,'Kilometros: '. $Kilometros,0,'C', 0);
	$this->Text(20,65,'Nivel de Combustible: '. $NivelCombustible,0,'C', 0);
	
	$this->Text(80,55,'Nombre: '.$Chofer,0,'C', 0);
	$this->Text(142,55,'Acomp.: '.$Chofer2,0,'C', 0);
	
	$this->Text(80,60,'Recorrido: '.$Recorrido." | ".$sqlR[Nombre],0,'C', 0);
	$this->Text(80,65,'Fecha de Venc.Registro: '.$FechaVencLicencia2,0,'C', 0);

 	$this->SetFont('Arial','B',12);
	$this->Text(90,73,'ADMINISTRACION',0,'C', 0);

	$this->SetFont('Arial','',10);
	$this->Text(20,78,'Tarjeta Verde/Azul:',0,'C', 0);
	$this->Text(80,78,'SI      NO',0,'C', 0);
	$this->Text(100,78,'Obs.:__________________________________________',0,'C', 0);

	$this->Text(20,84,'Comprobante de Seguro:',0,'C', 0);
	$this->Text(80,84,'SI      NO',0,'C', 0);
	$this->Text(100,84,'Obs.:__________________________________________',0,'C', 0);

	$this->Text(20,90,'Fecha de Vencimiento Seguro:',0,'C', 0);
	$this->Text(80,90,$FechaSeguro,0,'C', 0);
	$this->Text(100,90,'Obs.:__________________________________________',0,'C', 0);

	$this->SetFont('Arial','B',12);
	$this->Text(90,98,'ESTADO DEL VEHICULO',0,'C', 0);

	$this->SetFont('Arial','',10);
	$this->Text(20,104,'Cubiertas Ok:',0,'C', 0);
	$this->Text(80,104,'SI      NO',0,'C', 0);
	$this->Text(100,104,'Obs.:__________________________________________',0,'C', 0);

	$this->SetFont('Arial','',10);
	$this->Text(20,110,'Auxilio ok:',0,'C', 0);
	$this->Text(80,110,'SI      NO',0,'C', 0);
	$this->Text(100,110,'Obs.:__________________________________________',0,'C', 0);
  $this->SetFont('Arial','',10);
	$this->Text(20,116,'Chapas patentes en condiciones:',0,'C', 0);
	$this->Text(80,116,'SI      NO',0,'C', 0);
	$this->Text(100,116,'Obs.:__________________________________________',0,'C', 0);
  $this->SetFont('Arial','',10);
	$this->Text(20,122,'Luces Posicion:',0,'C', 0);
	$this->Text(80,122,'SI      NO',0,'C', 0);
	$this->Text(100,122,'Obs.:__________________________________________',0,'C', 0);
  $this->SetFont('Arial','',10);
	$this->Text(20,128,'Luces Bajas:',0,'C', 0);
	$this->Text(80,128,'SI      NO',0,'C', 0);
	$this->Text(100,128,'Obs.:__________________________________________',0,'C', 0);
  $this->SetFont('Arial','',10);
	$this->Text(20,134,'Luces Altas:',0,'C', 0);
	$this->Text(80,134,'SI      NO',0,'C', 0);
	$this->Text(100,134,'Obs.:__________________________________________',0,'C', 0);
  $this->SetFont('Arial','',10);
	$this->Text(20,140,'Luces de Freno:',0,'C', 0);
	$this->Text(80,140,'SI      NO',0,'C', 0);
	$this->Text(100,140,'Obs.:__________________________________________',0,'C', 0);
  $this->SetFont('Arial','',10);
	$this->Text(20,146,'GNC Funcionando:',0,'C', 0);
	$this->Text(80,146,'SI      NO',0,'C', 0);
	$this->Text(100,146,'Obs.:__________________________________________',0,'C', 0);
  $this->SetFont('Arial','',10);
	$this->Text(20,152,'Tarjeta de Combustible:',0,'C', 0);
	$this->Text(80,152,'SI      NO',0,'C', 0);
	$this->Text(100,152,'Obs.:__________________________________________',0,'C', 0);

	$this->SetFont('Arial','B',12);
	$this->Text(70,160,'OBSERVACIONES DE CHAPA Y PINTURA',0,'C', 0);

	$this->SetFont('Arial','B',9);
	$this->Text(20,168,'MARQUE LAS OBSERVACIONES',0,'C', 0);
  $this->Image('../../images/auto.png',30,170,23);

	$this->Text(95,168,'ESPECIFIQUE LAS OBSERVACIONES DE CHAPA Y PINTURA',0,'C', 0);

 	$this->SetY(170);
	$this->SetX(90);
	$this->SetFont('Arial','',10);
	$this->SetFillColor(255,255,255);
  $this->SetTextColor(0);
	$this->SetWidths(array(100));
// 	$this->SetHeights(array(100));
	$this->Row(array($Observaciones));
	
	$this->SetFont('Arial','B',12);
	$this->Text(70,230,'RETORNO DEL VEHICULO',0,'C', 0);

	$this->SetFont('Arial','',10);
	$this->Text(20,235,'Hora de Retorno:',0,'C', 0);
	$this->Text(60,235,'___:___',0,'C', 0);

	$this->SetFont('Arial','',10);
	$this->Text(85,235,'Km. Retorno:',0,'C', 0);

	$this->SetFont('Arial','',10);
	$this->Text(135,235,'Combustible Retorno:',0,'C', 0);

	
	$this->SetFont('Arial','B',12);
	$this->Text(80,242,'OBSERVACIONES',0,'C', 0);
	$this->SetFont('Arial','B',10);
// 	$this->Text(20,247,'Costo estimado para anticipo:'.$Resultado,0,'C', 0);
	
}

function Footer()
{
// 	//FIRMA CLIENTE
 	$this->SetY(-65);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,100,'Firma Chofer',0,0,'L');
// 	//ACLARACION CLIENTE
	$this->SetY(-65);
	$this->SetX(150);
	$this->SetFont('Arial','B',8);
	$this->Cell(300,100,'Firma Administracion',0,0,'L');

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
$NumeroOrden=$_GET['NO'];

	$con = new DB;
	$historial = $con->conectar();	
	$Dato="Recibo de Pago";
	$strConsulta = "SELECT * FROM Logistica WHERE NumerodeOrden='$NumeroOrden' ";
	$pacientes = mysql_query($strConsulta);
	$fila = mysql_fetch_array($pacientes);

	$pdf=new PDF('P','mm','Letter');
	$pdf->Open();
	$pdf->AddPage();
	$pdf->SetMargins(20,20,20);
	
	$pdf->Ln(-23);
 	$pdf->Line(20, 35, 190, 35);  //Horizontal CONTROL DE VEHICULOS
	$pdf->Line(20, 42, 190, 42);  //Horizontal CONTROL DE VEHICULOS
	$pdf->Line(20, 49, 190, 49);  //Horizontal VEHICULO CHOFER
  $pdf->Line(20, 69, 190, 69);  //Horizontal ADMINISTRACION
	$pdf->Line(20, 74, 190, 74);  //Horizontal ADMINISTRACION
  $pdf->Line(20, 94, 190, 94);  //Horizontal ESTADO DEL VEHICULO
	$pdf->Line(20, 99, 190, 99);  //Horizontal ESTADO DEL VEHICULO
	$pdf->Line(20, 156, 190, 156);  //Horizontal OBSERVACIONES DE CHAPA Y PINTURA
	$pdf->Line(20, 161, 190, 161);  //Horizontal OBSERVACIONES DE CHAPA Y PINTURA
	$pdf->Line(20, 226, 190, 226);  //Horizontal RETORNO DEL VEHICULO
	$pdf->Line(20, 231, 190, 231);  //Horizontal RETORNO DEL VEHICULO
	$pdf->Line(20, 238, 190, 238);  //Horizontal OBSERVACIONES 
	$pdf->Line(20, 243, 190, 243);  //Horizontal OBSERVACIONES
	$pdf->Line(20, 262, 50, 262);  //Horizontal OBSERVACIONES
	$pdf->Line(140, 262, 180, 262);  //Horizontal OBSERVACIONES

	$pdf->Line(90, 170, 90, 220);  //VERTICAL CUADRO
	$pdf->Line(90, 170, 190, 170);  //Horizontal CUADRO
	$pdf->Line(190, 170, 190, 220);  //VERTICAL CUADRO
	$pdf->Line(90, 220, 190, 220);  //Horizontal CUADRO
	
	$strConsulta =mysql_query("SELECT Nombre,Valor FROM Variables");
  while($fila=mysql_fetch_array($strConsulta)){
    if($fila[Nombre]=='CostoPeajes'){
    $CADP = $fila[Valor];// Costo Actual de Peajes
    }
    if($fila[Nombre]=='PrecioNaftaSuper'){
	  $PNS = $fila[Valor];      
    }
  }

	$Recorrido=$_SESSION['Recorrido'];	
	$ConsultaRecorrido ="SELECT Peajes FROM Recorridos WHERE Numero='$Recorrido'";
	$ConsultaRecorrido1=mysql_query($ConsultaRecorrido);
	$dato = mysql_fetch_array($ConsultaRecorrido1); // Cantidad de Peajes segun Recorrido
	$CPR=$dato[Peajes];
	$Resultado=$CADP*$CPR; // Resultado1 de Peajes
//---------------VEHICULOS----------------------------------------------------------------------------
  $Dominio=$_SESSION['Dominio'];	
	$ConsultaVehiculo ="SELECT NivelCombustible,CapacidadTanque,Marca,Modelo FROM Vehiculos WHERE Dominio='$Dominio'";
	$ConsultaVehiculo1=mysql_query($ConsultaVehiculo);
	$fila=mysql_fetch_array($ConsultaVehiculo1);	
	$CT = $fila[CapacidadTanque]; // Cantidad de Peajes segun Recorrido 
	$NC = explode("/",$fila[NivelCombustible],2);
	$Marca=$fila[Marca];
	$Modelo=$fila[Modelo];

	$NC1=$NC[0]; // Nivel de combustible
	$CT1=$CT/8;
	$CF=8-$NC1; //Cuanto le falta al tanque para completarse
	$Resultado1=($CF*$CT1)*$PNS;// Lo que le falta multiplicado el tanque total dividido las 8 medidas * el precio de nafta super
	$Total=$Resultado+$Resultado1;

	$pdf->SetFont('Arial','B',12);
	$pdf->Text(35,40,'CONTROL DE VEHICULO: '.$Marca.' '.$Modelo.' ('.$Dominio.')', 0);

	$pdf->SetFont('Arial','B',10);
	$pdf->Text(20,247,'Costo estimado para anticipo:$ '.$Total.' ('.$CPR.' Peajes: $ '.$Resultado.')+(Combustible: $ '.$Resultado1.')',0,'C', 0);
		
$pdf->Output();
ob_end_flush();
?>