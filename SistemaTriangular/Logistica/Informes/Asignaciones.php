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

  $Relacion=$_GET[Relacion];
  $FechaAsignacion=$_GET[Fecha];
  $CodigoProducto=$_GET[CodigoProducto];
//   $historial = $con->conectar();	
  
  $sqltotal=mysql_query("SELECT SUM(Cantidad)as Total FROM Asignaciones WHERE Relacion='$Relacion' AND Fecha='$FechaAsignacion' AND CodigoProducto='$CodigoProducto'");
  $rowtotal=mysql_fetch_array($sqltotal);
  $_SESSION[datorowtotal]=$rowtotal[Total];  
//BUSCO EL PRODUCTO
  $sqlAsignaciones_productos=mysql_query("SELECT * FROM AsignacionesProductos WHERE Relacion='$Relacion' AND CodigoProducto='$CodigoProducto'");
  $fila_asignaciones_productos = mysql_fetch_array($sqlAsignaciones_productos);
  $_SESSION[NombreProducto]=$fila_asignaciones_productos[Nombre];
  $_SESSION[CodigoProducto]=$fila_asignaciones_productos[CodigoProducto];

	$this->SetFont('Arial','',10);
    $this->Image('../../images/LogoCaddyNoAlfa.png',110 ,8, 40 , 16,'png','');  
	$this->Text(20,14,'Caddy Yo lo llevo!',0,'C', 0);
	$this->Text(20,19,'Cuit: 30-71534494-3',0,'C', 0);
	$this->Text(20,24,'Reconquista 4986',0,'C', 0);
	$this->Text(20,29,'www.caddy.com.ar',0,'C', 0);
	$Fecha=date('d/m/Y');
	//FECHA
	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(190,14,'Cordoba '.$Fecha,0,'C', 0);
    $this->Text(190,19,'Cliente: '.$_GET[Relacion],0,1);
	$this->Text(190,24,'Fecha Asignacion Salida:'.$_GET[Fecha],0,'C', 0);
	$this->Text(190,29,'Total Ingreso: '.$cantidad.' '.$rowtotal[Total],0,'C', 0);
    $this->Text(190,34,'Producto: ('.$_SESSION[CodigoProducto].') '.$_SESSION[NombreProducto],0,'C', 0);

  //TITULO
	$this->SetMargins(20,20,20);
 	$this->Line(20, 38, 258, 38);  //Horizontal
	$this->Line(20, 44, 258, 45);  //Horizontal

 	$this->SetFont('Arial','',16);
    $FechaBD=explode("-",$_GET[Fecha],3);
    $FechaBD0=$FechaBD[2].'/'.$FechaBD[1].'/'.$FechaBD[0];
	$this->Text(85,43,'Asignacion de '.$_SESSION[NombreProducto].' Fecha '.$FechaBD0,0,'C', 0);
  if($this->PageNo() == 1){
  $this->Ln(15);
  }else{
  $this->Ln(5);
  }
  
	$this->SetWidths(array(15,15, 70 ,101, 17, 18, 18));
	$this->SetFont('Arial','B',6);
	$this->SetFillColor(119,136,153);
  $this->SetTextColor(255);
  $this->Row(array('POSICION','ID.PROV.','NOMBRE CLIENTE DESTINO', 'DIRECCION DESTINO', 'NOMBRE','EDICION', 'CANTIDAD'));

}
	
function Footer()
{

	$this->SetY(-15);
	$this->SetFont('Arial','B',8);
	$this->Cell(100,10,'Planillas de Asignacioens Caddy',0,0,'L');
	
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

  $Relacion=$_GET[Relacion];
  $FechaAsignacion=$_GET[Fecha];

  $con = new DB;
  $pdf=new PDF('L','mm','Letter');
  $pdf->Open();
  $pdf->AddPage();
  $cantidad0=0;
  $cantidad=0;
  $Relacion=$_GET[Relacion];
  $FechaAsignacion=$_GET[Fecha];
  $CodigoProducto=$_GET[CodigoProducto];
  $historial = $con->conectar();	

  $sqlrecorrido=mysql_query("SELECT Recorrido FROM TransClientes WHERE FechaEntrega='$FechaAsignacion' AND Eliminado=0  GROUP BY Recorrido ORDER BY Recorrido");
  
  while($rowrecorrido = mysql_fetch_array($sqlrecorrido)){
  $cantidad0=0;
  $recorridoactual=$rowrecorrido[Recorrido];
    $strConsulta=mysql_query("SELECT idClienteDestino,a.id,Posicion FROM TransClientes a INNER JOIN HojaDeRuta b ON a.id=b.idTransClientes 
    WHERE a.FechaEntrega='$FechaAsignacion' AND a.Recorrido='$rowrecorrido[Recorrido]' AND a.Eliminado=0 ORDER BY Posicion");
    
    while($historial = mysql_fetch_array($strConsulta)){
      $sqlclientes0=mysql_query("SELECT idProveedor FROM Clientes WHERE id='$historial[idClienteDestino]'");      
      $rowclientes0 = mysql_fetch_array($sqlclientes0);
      
      //OBTENGO LA POSICION
    //   $sqlclientes1=mysql_query("SELECT Posicion FROM HojaDeRuta WHERE HojaDeRuta.idTransClientes='$historial[id]'");
    //   $rowclientes1 = mysql_fetch_array($sqlclientes1);

      $sqlAsignaciones=mysql_query("SELECT * FROM Asignaciones WHERE Relacion='$Relacion' AND Fecha='$FechaAsignacion' AND idProveedor='$rowclientes0[idProveedor]' AND CodigoProducto='$CodigoProducto'");
      $fila = mysql_fetch_array($sqlAsignaciones);
      $nproveedor=sprintf("%04d",$fila['idProveedor']);  
      setlocale(LC_ALL,'es_AR');
      $TotalRepo1='';	
      $pdf->SetFont('Arial','',6);
			if($fila[Cantidad] <> 0)
			{
            //SACO EL ID DE CLIENTE SEGUN LA RELACION
            $sqlclientes=mysql_query("SELECT id,nombrecliente,Direccion FROM Clientes WHERE idProveedor='$fila[idProveedor]'");
            $rowclientes = mysql_fetch_array($sqlclientes);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0);
            
            $pdf->Row(array($historial['Posicion'],$nproveedor,$rowclientes[nombrecliente], $rowclientes[Direccion], $_SESSION[NombreProducto],$fila['Edicion'], $fila['Cantidad']));
            $cantidad0=$cantidad0+$fila[Cantidad];
            $cantidad=$cantidad+$fila[Cantidad];
            }
        
        }	
            $pdf->SetFont('Arial','B',7);
            $pdf->Row(array('','','','','','TOTAL '.$rowrecorrido[Recorrido].':',$cantidad0));
            $pdf->AddPage();
        }    
        // if($rowrecorrido[Recorrido]<>''){
        // $pdf->AddPage();
        // }
    
     	$pdf->SetFont('Arial','',16);
	    $pdf->Text(105,100,'TOTAL RECIBIDO: '.$_SESSION[datorowtotal],0,'C', 0);
	    $pdf->Text(105,120,'TOTAL ASIGNADO: '.$cantidad,0,'C', 0);
      $SOBRANTE=$_SESSION[datorowtotal]-$cantidad;
      $pdf->Text(105,140,'VERIFICAR SOBRANTE DE: '.$SOBRANTE.' EJEMPLARES',0,'C', 0);
      
    

$pdf->Output();
?>