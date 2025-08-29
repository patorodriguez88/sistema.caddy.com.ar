<?php
session_start();
require('../../fpdf/fpdf.php');
require('../../../conexion.php');
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

		$this->MultiCell($w,4,$data[$i],0,$a,'true');
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
	$NumeroRep=$_GET['NR'];
  $CodigoSeguimiento=$_GET['CS'];
    
  $con = new DB;
	$historial = $con->conectar();	
	$strConsulta = "SELECT * FROM Ventas WHERE NumPedido='$CodigoSeguimiento'";
	$Dato = mysql_query($strConsulta);
	header("Content-Type: text/html; charset=iso-8859-1 ");

  while($row=mysql_fetch_array($Dato)){
	$Codigo=$row[NumPedido];
	$Fecha=$row[FechaPedido];
  $Repo=$row[NumeroRepo];
    
	$arrayfecha=explode('-',$Fecha,3);
	$Fecha2=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
	}
  $Fecha=date('d/m/Y');
	$this->SetFont('Arial','',10);
//   $this->Image('../../images/LogoCaddy.png',10,8,22);  
  $this->Image('../../images/LogoCaddyNoAlfa.png',16 ,8, 40 , 16,'png','');  
	$this->Text(20,26,'Triangular S.A.',0,'C', 0);
	$this->Text(20,31,'Cuit: 30-71534494-3',0,'C', 0);
	$this->Text(20,36,utf8_decode('Domicilio: Reconquista 4986, Córdoba'),0,'C', 0);
	$this->Text(90,26,'www.caddy.com.ar',0,'C', 0);
	
	//FECHA
    
	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(150,14,utf8_decode("Córdoba ").$Fecha,0,'C', 0);

	//REMITO NUMERO
	$this->Ln(20);
	$this->Ln(20);
 	$this->SetFont('Arial','',14);
//   $this->SetTextColor(255,255,255);  // Establece el color del texto (en este caso es blanco)
//   $this->SetFillColor(100, 100, 100); // establece el color del fondo de la celda (en este caso es GRIS)
// 	$this->Cell(0,6,'Origen:',0,1,'C',true);
  
    
	$this->Text(40,43,'CONTROL DE ENVIOS ENTREGADOS NO FACTURADOS',0,'C', 0);
//     for($i=0;$i<1;$i++)
//   {
//     $this>Text(array('FECHA','CODIGO', 'CLIENTE', 'DIRECCION','LOCALIDAD', 'CANT.', 'PRECIO','TOTAL'));
//   }
  
}
function Footer()
{
	//FIRMA CLIENTE

// 	$this->SetY(-65);
// 	$this->SetFont('Arial','B',8);
// 	$this->Cell(100,10,'Firma del Cliente',0,0,'L');
// 	//ACLARACION CLIENTE
// 	$this->SetY(-65);
// 	$this->SetX(90);
// 	$this->SetFont('Arial','B',8);
// 	$this->Cell(100,10,'Aclaracion Nombre',0,0,'L');
// 	//D.N.I.
// 	$this->SetY(-65);
// 	$this->SetX(150);
// 	$this->SetFont('Arial','B',8);
// 	$this->Cell(100,10,'D.N.I.',0,0,'L');
	
// 	$this->SetY(-25);
// 	$this->SetFont('Arial','B',8);
// 	$this->Cell(100,10,'Observaciones: Queda sujeto el transporte de la carga descripta a las disposiciones del codigo de comercio
// 	especialmente. ',0,0,'L');
// 	$this->SetY(-20);
// 	$this->Cell(100,10,'a lo que se refiere en los Arts 172 y 177 que el cargador declara aceptar.',0,0,'L');
	
	$this->SetY(-15);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Remito Caddy Yo lo llevo!.',0,0,'L');
	
	$this->SetY(-15);
	$this->SetX(90);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'www.caddy.com.ar',0,0,'L');

  $cliente= $_SESSION['ClienteActivo'];
	$NumeroRep=$_GET['NR'];
  $CodigoSeguimiento=$_GET['CS'];
  $con = new DB;
	$pacientes = $con->conectar();	
	$strConsulta = "SELECT * FROM TransClientes WHERE RazonSocial='$cliente' AND Eliminado=0 ";
	$Usuario = mysql_query($strConsulta);
	$fila = mysql_fetch_array($Usuario);
	
	$this->SetY(-15);
	$this->SetX(170);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Usuario:'.$_SESSION['Usuario'],0,0,'L');

 }
}

  $cliente= $_SESSION['ClienteActivo'];
  $_SESSION[namedoc]=$cliente;

	$cliente= $_SESSION['ClienteActivo'];
	$NumeroRep=$_GET['NR'];
  $CodigoSeguimiento=$_GET['CS'];

	$con = new DB;
	$pacientes = $con->conectar();	
  $strConsulta="SELECT * FROM Clientes WHERE nombrecliente='$cliente'";

// 	$strConsulta = "SELECT * FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'";
	$pacientes = mysql_query($strConsulta);
	$fila = mysql_fetch_array($pacientes);
	$Usuario=$fila['Usuario'];
	$Transportista=$fila['Transportista'];
	$Recorrido=$fila[Recorrido];
	$pdf=new PDF('P','mm','Letter');
	$pdf->Open();
	$pdf->AddPage();
	$pdf->SetMargins(20,20,20);

	//ORIGEN
	$pdf->Ln(-23);
 	$pdf->Line(20, 38, 190, 38);  //Horizontal
	$pdf->Line(20, 45, 190, 45);  //Horizontal
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(0,6,'Razon Social: '.$fila['nombrecliente'],0,1);
	$pdf->Cell(0,6,'C.U.I.T.: '.$fila['Cuit'],0,1);
	$pdf->Cell(0,6,'Domicilio: '.$fila['Direccion'].' - '.$fila['Localidad'].' | Tel.: '.$fila['Telefono'],0,1); 
	$pdf->Cell(0,6,'Situacion Fiscal: '.$fila['SituacionFiscal'],0,1); 
	$pdf->Ln(2);
// 	$pdf->Line(20, 111, 190, 111);  //Horizontal
	$pdf->SetFont('Arial','',12);
	$pdf->Ln(2);
// 	$pdf->Line(20, 111, 190, 111);  //Horizontal
	$pdf->SetFont('Arial','',10);
  $pdf->SetFont('Arial','',8);
	$pdf->Cell(0,5,'Observaciones: '.$fila['Observaciones'],0,1); 
  $pdf->SetWidths(array(180));
  $pdf->SetFont('Arial','',10);
	$pdf->Ln(4);

	$pdf->SetWidths(array(12,16, 35, 61, 18, 10, 12, 12));
	$pdf->SetFont('Arial','B',6);
	$pdf->SetFillColor(100,100,100);
  $pdf->SetTextColor(255);

$ordenar="SELECT * FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe>'0' AND Facturado='0' AND Fecha>='$_GET[Desde]' AND Fecha<='$_GET[Hasta]'";
// $ordenar="SELECT * FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe>'0' AND Facturado='0'";

$MuestraRemitos=mysql_query($ordenar);

		for($i=0;$i<1;$i++)
			{
				$pdf->Row(array('FECHA','CODIGO', 'CLIENTE', 'DIRECCION','LOCALIDAD', 'CANT.', 'PRECIO','TOTAL'));
			}
	$CodigoSeguimiento=$fila['CodigoSeguimiento'];
	$historial = $con->conectar();	
  $cliente= $_SESSION['ClienteActivo'];

  while($row1=mysql_fetch_array($MuestraRemitos)){
    if($row1[FormaDePago]=='Origen'){
    $fp='RazonSocial';  
    }else{
    $fp='ClienteDestino';  
    }
    if($row[id]<>''){
    $coma=',';  
    }else{
    $coma='';  
    }  
  $id=$row1[id].$coma;  

//   $BuscarCliente=mysql_query("SELECT * FROM TransClientes WHERE $fp='$cliente' AND id IN($id) AND Eliminado='0' AND Facturado='0' AND TipoDeComprobante='Remito' AND Fecha>='$_GET[Desde]' AND Fecha<='$_GET[Hasta]' ORDER BY ClienteDestino DESC");
  $BuscarCliente=mysql_query("SELECT * FROM TransClientes WHERE $fp='$cliente' AND id IN($id) AND Eliminado='0' AND Facturado='0' AND  TipoDeComprobante='Remito' ORDER BY ClienteDestino DESC");
  $cliente= $_SESSION['ClienteActivo'];

  while($fila=mysql_fetch_array($BuscarCliente)){
  $sql=mysql_query("SELECT MAX(Visitas)as Visitas FROM Seguimiento WHERE CodigoSeguimiento='$fila[CodigoSeguimiento]'");
  $datoseguimiento=mysql_fetch_array($sql);  
    
  $numfilas = mysql_num_rows($BuscarCliente);
  
	for ($i=0; $i<$numfilas; $i++)
	{
  			$pdf->SetFont('Arial','',5);
				$TotalRepo=$TotalRepo1;
				$Precio='$'.$fila['Debe'];
				$Total='$'.$datoseguimiento[id]*$fila['Debe'];	
        $Cantidad=1;    
				$Total='$'.$Cantidad*$fila['Debe'];	
        $TotalRepo=$TotalRepo0;
			  $TotalAcumulado+=$Cantidad*$fila['Debe'];
        $TotalCant+=$Cantidad;
      $Fecha0=explode('-',$fila['Fecha'],3);
      $Fecha=$Fecha0[2].".".$Fecha0[1].".".$Fecha0[0];

      if($i%2 == 1)
			{
				$pdf->SetFillColor(255,255,255);
        $pdf->SetTextColor(0);
				$pdf->Row(array($Fecha,$fila['CodigoSeguimiento'], $fila['ClienteDestino'],$fila['DomicilioDestino'],$fila['LocalidadDestino'], $Cantidad,$Precio,$Total));
			}
			else
			{
				$pdf->SetFillColor(255,255,255);
        $pdf->SetTextColor(0);
				$pdf->Row(array($Fecha,$fila['CodigoSeguimiento'], $fila['ClienteDestino'], $fila['DomicilioDestino'], $fila['LocalidadDestino'], $Cantidad,$Precio,$Total));
			}

		}
  }

  }
$TotalAcumuladoF=number_format($TotalAcumulado,2,',','.');
  $pdf->SetFillColor(255,255,255);
	$pdf->SetFont('Arial','B',5);
	$pdf->Row(array($fila[''], $fila[''],'','','Total:', $TotalCant,'','$ '.$TotalAcumuladoF));

    //DESDE ACA DETALLE DE REINTENTOS

// 	$pdf->Ln(4);
// 	$pdf->SetWidths(array(16,13, 15, 15, 18, 100));
// 	$pdf->SetFont('Arial','B',8);
// 	$pdf->SetFillColor(119,136,153);
//   $pdf->SetTextColor(255);
// // goto a;

// $ordenar="SELECT * FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe>'0' AND Facturado='0'";
// $MuestraRemitos=mysql_query($ordenar);
// // if($MuestraRemitos[id]<>''){
//     for($i=0;$i<1;$i++)
//     {
//     $pdf->Row(array('CODIGO','VISITA','FECHA', 'HORA', 'USUARIO', 'OBSERVACIONES'));
//     }

//   while($row1=mysql_fetch_array($MuestraRemitos)){
//     if($row1[FormaDePago]=='Origen'){
//     $fp='RazonSocial';  
//     }else{
//     $fp='ClienteDestino';  
//     }
//     if($row[id]<>''){
//     $coma=',';  
//     }else{
//     $coma='';  
//     }  
//   $id=$row1[id].$coma;  

//   $BuscarTrans=mysql_query("SELECT * FROM TransClientes WHERE $fp='$cliente' AND id IN($id) AND Eliminado='0' AND Facturado='0' AND TipoDeComprobante='Remito' ORDER BY Fecha DESC");
//   $numfilas = mysql_num_rows($BuscarTrans);

//   while($fila=mysql_fetch_array($BuscarTrans)){
//     $sqlSeguimiento=mysql_query("SELECT CodigoSeguimiento FROM Seguimiento WHERE CodigoSeguimiento='$fila[CodigoSeguimiento]' AND Visitas>='3'");
//     $datoseguimiento=mysql_fetch_array($sqlSeguimiento);  
    
//     $sqlSeguimiento1=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$datoseguimiento[CodigoSeguimiento]' AND CodigoSeguimiento<>'' AND Observaciones<>''");
//     while($datoseguimiento1=mysql_fetch_array($sqlSeguimiento1)){  
//     $visita=$datoseguimiento1[Visitas]-1;

//     for ($i=0; $i<count($datoseguimiento1[id]); $i++)
// 	    {
//        $pdf->SetFont('Arial','',6);
// 			if($i%2 == 1)
// 			{
// 				$pdf->SetFillColor(255,255,255);
//         $pdf->SetTextColor(0);
// 				$pdf->Row(array($datoseguimiento1['CodigoSeguimiento'],$visita,$datoseguimiento1['Fecha'], $datoseguimiento1['Hora'],$datoseguimiento1['Usuario'],$datoseguimiento1['Observaciones']));
// 			}
// 			else
// 			{
// 				$pdf->SetFillColor(220,220,220);
//         $pdf->SetTextColor(0);
// 				$pdf->Row(array($datoseguimiento1['CodigoSeguimiento'],$visita,$datoseguimiento1['Fecha'], $datoseguimiento1['Hora'],$datoseguimiento1['Usuario'],$datoseguimiento1['Observaciones']));
// 			}

//     }
//       }
//     }
//   }
// }
a:

$pdf->Output($_SESSION[namedoc].'.pdf','I');
$pdf->Output();
?>