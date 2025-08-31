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

	$this->SetFont('Arial','',8);
//   $this->Image('../../images/LogoCaddy.png',10,8,22);  
  $this->Image('../../images/LogoCaddyNoAlfa.png',16 ,8, 40 , 16,'png','');  
	$this->Text(20,26,'Triangular S.A.',0,'C', 0);
	$this->Text(20,31,'Cuit: 30-71534494-3',0,'C', 0);
	$this->Text(20,36,utf8_decode('Domicilio: Reconquista 4986, Córdoba'),0,'C', 0);
	$this->Text(90,36,'www.caddy.com.ar',0,'C', 0);
	
	//FECHA
	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(150,24,utf8_decode("Córdoba ").$Fecha2,0,'C', 0);
	
  $CodigoSeguimiento=$_GET['CS'];
	$con = new DB;
	$pacientes = $con->conectar();	
	$strConsulta = "SELECT * FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'";
	$datos_strConsulta = mysql_query($strConsulta);
	$fila = mysql_fetch_array($datos_strConsulta);
    //REMITO NUMERO
	$this->Ln(20);
 	$this->SetFont('Arial','',10);
	$this->Text(150,29,'Guia de Carga N: '.$Repo,0,'C', 0);
	$this->Text(150,34,'Seguimiento: '.$CodigoSeguimiento,0,'C', 0);
  //RETIRO O ENTREGA
  $this->SetFont('Arial','B',15);  

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
}

  $cliente= $_SESSION['ClienteActivo'];
  $CodigoSeguimiento=$_GET['CS'];

	$con = new DB;
	$pacientes = $con->conectar();	
	$strConsulta = "SELECT * FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado='0'";
	$datoConsulta = mysql_query($strConsulta);
	$fila = mysql_fetch_array($datoConsulta);
    $ValorDeclarado=$fila[ValorDeclarado];  
    
    //Observaciones Cliente Origen
	$sqlCliente = "SELECT Observaciones FROM Clientes WHERE id='$fila[IngBrutosOrigen]'";
	$datoCliente = mysql_query($sqlCliente);
	$Cliente = mysql_fetch_array($datoCliente);
    
    // Observaciones Cliente Destino
    $sqlClienteD = "SELECT Observaciones FROM Clientes WHERE id='$fila[idClienteDestino]'";
	$datoClienteD = mysql_query($sqlClienteD);
	$ClienteD = mysql_fetch_array($datoClienteD);

	$Usuario=$fila['Usuario'];
	$Transportista=$fila['Transportista'];
	$Recorrido=$fila[Recorrido];
	$pdf=new PDF('P','mm','Letter');
	$pdf->Open();
	$pdf->AddPage();
    $pdf->SetMargins(20,20,20);

    $pdf->SetFont('Arial','B',14);

  if($fila[Retirado]==0){
	$pdf->Text(150,10,'RETIRO',0,'C', 0);
    $pdf->SetFont('Arial','B',10);  
    $pdf->Text(150,19,$fila[LocalidadOrigen],0,'C', 0);    


//   //ACA SOLICITO LA FIRMA DEL RETIRO
//     $pdf->SetY(-90);
// 	$pdf->SetFont('Arial','B',8);
// 	$pdf->Cell(100,10,'Firma Cliente',0,0,'L');
// 	//ACLARACION CLIENTE
// 	$pdf->SetY(-90);
//     $pdf->SetX(90);

// 	$pdf->SetFont('Arial','B',8);
// 	$pdf->Cell(100,10,'Aclaracion Cliente',0,0,'L');
// 	//D.N.I.
// 	$pdf->SetY(-90);
// 	$pdf->SetX(150);
//     $pdf->SetFont('Arial','B',8);
// 	$pdf->Cell(100,10,'D.N.I. Cliente',0,0,'L');
//   // HASTA ACA SOLICITO LA FIRMA DE RETIRO








  }else{
  $pdf->Text(150,10,'ENTREGA',0,'C', 0);  
  $pdf->SetFont('Arial','B',10);  
  $pdf->Text(150,19,$fila['LocalidadDestino'],0,'C', 0);    
  }
  $pdf->SetFont('Arial','B',10);
  $pdf->Text(150,15,'RECORRIDO: '.$fila[Recorrido],0,'C', 0);   

//ORIGEN
$pdf->Ln(-23);
$pdf->Line(20, 38, 195, 38);  //Horizontal
$pdf->Line(20, 45, 195, 45);  //Horizontal
$pdf->SetFont('Arial','',14);
$pdf->SetTextColor(255,255,255);  // Establece el color del texto (en este caso es blanco)
$pdf->SetFillColor(100, 100, 100); // establece el color del fondo de la celda (en este caso es GRIS)
$pdf->Cell(0,6,'Origen:',0,1,'C',true);
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);//color negro
$pdf->Cell(0,6,'Nombre Cliente: '.$fila['RazonSocial'],0,1);
$pdf->Cell(0,6,'Situacion Fiscal: '.$fila['SituacionFiscalOrigen'].' | C.U.I.T.: '.$fila['Cuit'],0,1);
$pdf->Cell(0,6,'Domicilio: '.$fila['DomicilioOrigen'].' - '.$fila['LocalidadOrigen'].' | Tel.: '.$fila['TelefonoOrigen'],0,1); 
$pdf->SetFont('Arial','B',8);
$pdf->Cell(0,6,'Obs. Cliente: '.$Cliente[Observaciones],0,1); 

	//DESTINATARIO
    $pdf->Ln(5);	
    //  	$pdf->Line(20, 80, 190, 80);  //Horizontal
    $pdf->SetFont('Arial','',14);
    $pdf->SetTextColor(255,255,255);  // Establece el color del texto (en este caso es blanco)
    $pdf->SetFillColor(100, 100, 100); // establece el color del fondo de la celda (en este caso es AZUL
    $pdf->Cell(0,6,'Destino:',0,1,'C',true);
    $pdf->SetTextColor(0,0,0);//color negro
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(0,6,'Nombre Cliente: '.$fila['ClienteDestino'],0,1);
    $pdf->Cell(0,6,'Situacion Fiscal: '.$fila['SituacionFiscalDestino'].' | C.U.I.T.: '.$fila['DocumentoDestino'],0,1);
    $pdf->Cell(0,6,'Domicilio: '.$fila['DomicilioDestino'].' - '.$fila['LocalidadDestino'].' | Tel.: '.$fila['TelefonoDestino'],0,1); 
    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(0,6,'Obs. Cliente: '.utf8_decode($ClienteD[Observaciones]),0,1); 

    $pdf->Ln(2);
    $pdf->SetFont('Arial','',14);
    $pdf->SetTextColor(255,255,255);  // Establece el color del texto (en este caso es blanco)
    $pdf->SetFillColor(100, 100, 100); // establece el color del fondo de la celda (en este caso es AZUL
    $pdf->Cell(0,6,'Detalles del Servicio:',0,1,'C',true);
    $pdf->SetTextColor(0,0,0);//color negro

	$pdf->SetFont('Arial','B',10);

	if($fila['FormaDePago']=='Origen'){
    if($fila[Retirado]==0){
    $MuestraPrecio=1;		
    $pdf->Cell(0,5,'Forma De Pago: '.$fila['FormaDePago'].' - COBRAR EL IMPORTE TOTAL',0,1); 
    }else{
    $pdf->Cell(0,5,'Forma De Pago: '.$fila['FormaDePago'].' - NO COBRAR',0,1); 
    $MuestraPrecio=1;	    
    }
  }elseif($fila['FormaDePago']=='Destino'){
    if($fila[Retirado]==0){
    $pdf->Cell(0,5,'Forma De Pago: '.$fila['FormaDePago'].' - NO COBRAR',0,1); 
    $MuestraPrecio=1;	
    }else{
    $MuestraPrecio=1;		
    $pdf->Cell(0,5,'Forma De Pago: '.$fila['FormaDePago'].' - COBRAR EL IMPORTE TOTAL',0,1); 
    }
  }  

  if($fila[CobrarEnvio]==1){
  $sqlcobranza=mysql_query("SELECT SUM(CobrarEnvio)as Cobranza FROM Ventas WHERE NumPedido='$CodigoSeguimiento'");
  $datocobranza=mysql_fetch_array($sqlcobranza);  
  $pdf->SetFont('Arial','B',10);
	$pdf->Cell(0,5,'Cobranza Integrada: SI | Cobrar $ '.$datocobranza['Cobranza'].' | A CUENTA Y ORDEN DEL CLIENTE',0,1); 
  }else{
  $pdf->Cell(0,5,'Cobranza Integrada: NO COBRAR ',0,1);   
  }
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(0,5,'Valor Declarado: $ '.number_format($ValorDeclarado,2,',','.'),0,1); 
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,5,'Codigo del Cliente: '.$fila['CodigoProveedor'],0,1); 
    $pdf->Cell(0,5,'Recorrido: '.$fila[Recorrido],0,1); 
    $pdf->SetFont('Arial','b',8);
    $pdf->Cell(0,5,'Obs. Venta: '.utf8_decode($fila[Observaciones]),0,1); 
//AFORO
    $pdf->SetWidths(array(175));
    $pdf->SetFont('Arial','B',7);
    $pdf->SetFillColor(255,255,255);
    $pdf->SetTextColor(0);
    $pdf->Cell(0,6,'AFORO DE LA GUIA DE CARGA:',0,1,'C',true);
    $pdf->SetWidths(array(18, 50, 48, 14, 15,15,15));
    $pdf->SetFont('Arial','B',7);
    $pdf->SetFillColor(100,100,100);

    $pdf->SetTextColor(255);
		for($i=0;$i<1;$i++)
			{
				$pdf->Row(array('CODIGO', 'SERVICIO', 'OBSERV.', 'CANT.', 'IMP.NETO','I.V.A.','TOTAL'));
			}
	$CodigoSeguimiento=$fila['CodigoSeguimiento'];
	$historial = $con->conectar();	
	$strConsulta = "SELECT Codigo,Titulo,Comentario,Precio,Cantidad FROM Ventas WHERE NumPedido='$CodigoSeguimiento' AND Eliminado=0";
	$historial = mysql_query($strConsulta);
	$numfilas = mysql_num_rows($historial);
	//Calcula el total de la repo
    setlocale(LC_ALL,'es_AR');
	$Muestra=mysql_query("SELECT SUM(ImporteNeto)as Neto,SUM(Total) as Total,SUM(Iva3)as Iva FROM Ventas WHERE NumPedido='$CodigoSeguimiento'");
	$row=mysql_fetch_array($Muestra);

	$Muestra1=mysql_query("SELECT SUM(Cantidad) as TotalCantidad FROM Ventas WHERE NumPedido='$CodigoSeguimiento'");
	$row1=mysql_fetch_array($Muestra1);
 	$TotalCant=$row1[TotalCantidad];
 	$TotalRepo0= '$'.$row[Total];
	$TotalRepo1='';	
	for ($i=0; $i<$numfilas; $i++)
	    {

        $filaVentas = mysql_fetch_array($historial);
        $pdf->SetFont('Arial','',6);
        $Precio='$'.$filaVentas['Precio'];
        $Total='$'.$filaVentas['Cantidad']*$filaVentas['Precio'];	
        if($filaVentas[Cantidad]==0){
            $Cantidad=1;
        }else{
            $Cantidad=$filaVentas[Cantidad];
        }
        //SEGURO SOBRE EL VALOR DECLARADO
//         $ImporteNeto=(($row[Total])/1.21);
        $ImporteNeto=(($Cantidad*$filaVentas[Precio])/1.21);
        $ImporteNeto_label=number_format($ImporteNeto,2,',','.');

    //     if($ValorDeclarado>5000){
    //     $TotalNeto=$ImporteNeto+(($ValorDeclarado-5000)*0.007);
    //     $Seguro=$ValorDeclarado*0.007;
    //     $Seguro_label=number_format($Seguro,2,',','.');    
    //     $Neto=number_format($TotalNeto,2,',','.');
    //     $Iva=number_format(($TotalNeto*21)/100,2,',','.');
    //     $Iva_precio=$filaVentas[Precio]-$ImporteNeto;
    //     $Iva_precio_label=number_format($Iva_precio,2,',','.'); 
    //     $Iva_seguro=($Seguro*21)/100;
    //     $Iva_seguro_label=number_format($Iva_seguro,2,',','.');
    //     $Seguro_total=$Seguro+$Iva_seguro;
    //     $Seguro_total_label=number_format($Seguro_total,2,',','.');  
    //     $Iva_total=$Iva_precio+$Iva_seguro;
    //     $Iva_total_label=number_format($Iva_total,2,',','.');  
    //     $TotalFinal=number_format((($TotalNeto+($TotalNeto*21)/100)),2,',','.'); 
    //     $Neto_total_label=number_format($row[Neto],2,',','.');
    //    }else{
        $TotalNeto=$ImporteNeto;
        $Neto=number_format($TotalNeto,2,',','.');
        $Iva_total=number_format(($TotalNeto*21)/100,2,',','.');
        $Iva_precio=$filaVentas[Precio]-$ImporteNeto;
        $Iva_precio_label=number_format($Iva_precio,2,',','.');  
        $Iva_total=$Iva_precio;
        $Iva_total_label=number_format($row[Iva],2,',','.');  
        $Neto_total_label=number_format($row[Neto],2,',','.');  
        $TotalFinal=number_format($row[Total],2,',','.');  
    //    } 

        $Seguro=$ValorDeclarado*0.007;

      if($i%2 == 1)
			{
        $pdf->SetFont('Arial','B',5);
        $pdf->SetFillColor(255,255,255);  
        $pdf->SetTextColor(0);
        $pdf->Row(array($filaVentas['Codigo'], $filaVentas['Titulo'], $filaVentas['Comentario'], $Cantidad,'$ '.$ImporteNeto_label,'$ '.$Iva_precio_label,$Precio));
        }else{
        $pdf->SetFont('Arial','B',5);
        $pdf->SetFillColor(255,255,255);  
        $pdf->SetTextColor(0);
        $pdf->Row(array($filaVentas['Codigo'], $filaVentas['Titulo'], $filaVentas['Comentario'], $Cantidad,'$ '.$ImporteNeto_label,'$ '.$Iva_precio_label,$Precio));
        }
  }
//   if($ValorDeclarado>5000){
//   $pdf->Row(array('SEGURO 0,07 %', 'SEGUN VALOR DECLARADO DE $ '.number_format($ValorDeclarado,2,',','.'),'','1', '$ '.$Seguro_label,'$ '.$Iva_seguro_label,'$ '.$Seguro_total_label));
//   $pdf->SetTextColor(0,0,0);//color negro
//   }
    $pdf->SetFont('Arial','B',6);
    $pdf->Row(array($filaVentas[''], $filaVentas[''],'TOTAL:', $TotalCant,'$ '.$Neto_total_label,'$ '.$Iva_total_label,'$ '.$TotalFinal));
    $pdf->SetTextColor(0,0,0);//color negro
  
	$pdf->SetY(-70);
	$pdf->SetFont('Arial','B',8);
  if($fila[Retirado]==0){
	$pdf->Cell(100,10,'Firma de Caddy',0,0,'L');
  }else{
  $pdf->Cell(100,10,'Firma del Cliente',0,0,'L');  
  }
    //ACLARACION CLIENTE
	$pdf->SetY(-70);
	$pdf->SetX(90);
  $pdf->SetFont('Arial','B',8);
// 	$pdf->Cell(100,10,'Aclaracion Nombre',0,0,'L');

	$pdf->Cell(100,10,'Aclaracion Nombre',0,0,'L');
	//D.N.I.
	$pdf->SetY(-70);
	$pdf->SetX(160);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(100,10,'D.N.I.',0,0,'L');
	//OBSERVACIONES
    $pdf->SetY(-54);
    $pdf->SetFont('Arial','',6);
    $pdf->Cell(100,6,utf8_decode('La presente Guia de Carga (Carga de Porte) es el único titulo legal del contrato de transporte y su prueba entre todas las partes involucradas y por el que ellas reconocen y aceptan las normas y'),0,0,'L');
    $pdf->SetY(-51);
    $pdf->Cell(100,6,utf8_decode(' condiciones generales pre-establecidas con el solo hecho y al momento de entregarse la carga en la empresa. Este contrato de transporte esta sujeto a lo estipulado en el Código Civil y'),0,0,'L');
    $pdf->SetY(-48);  
    $pdf->Cell(100,6,utf8_decode('Comercial de la República Argentina en su Cap.VII Secc. 1era. art. 1280 al art. 1287. Y Secc. 3era  art. 1296 a 1318. Ley 24653/96. Dto. Reg. 1035/02 y por el Reglamento de la Empresa'),0,0,'L');
    $pdf->SetY(-45);  
    $pdf->Cell(100,6,utf8_decode('y cuanto mas acuerdo establecido entre las partes. El Remitente declarará el valor de la mercaderia en sus remitos al momento del despacho, de lo contrario el transportista no estará'),0,0,'L');
    $pdf->SetY(-42);  
    $pdf->Cell(100,6,utf8_decode('obligado a indemnización alguna ante casos de pérdida o robo de la mercaderia transportada. La mercaderia con embalaje insuficiente o deficiente queda excluida del riesgo de roturas salvo accidente.'),0,0,'L');
    $pdf->SetY(-39);  
    $pdf->Cell(100,6,utf8_decode('Los bultos cerrados excluyen de responsabilidad al transportista sobre la existencia, peligrosidad cantidad y calidad de los efectos enviados.'),0,0,'L');
    $pdf->SetY(-36);  
    $pdf->Cell(100,6,utf8_decode('Queda terminantemente prohibido remitir o contaminante sin la previa autorizacion del transportista, adecuadamente acondicinada e identificada por el remitente.'),0,0,'L');
    $pdf->SetY(-33);  
    $pdf->Cell(100,6,utf8_decode('SEGURO: En el supuesto de corresponder seguro por el porteador, el mismo ampara sobre la base del monto declarado por el cargador, limitado por los deducibles del ley'),0,0,'L');
    $pdf->SetY(-30);  
    $pdf->Cell(100,6,utf8_decode('la Superintendencia de Seguro y Póliza particular.'),0,0,'L');

  if($fila['Retirado']==0){
//DESDE ACA SI VA REMITO DE RETIRO Y ENTREGA
    $pdf->AddPage();
    $pdf->SetMargins(20,20,20);
    $pdf->SetFont('Arial','B',14);  
    $pdf->Text(150,10,'ENTREGA',0,'C', 0);  
  //ORIGEN
    $pdf->Ln(-33);
 	$pdf->Line(20, 38, 195, 38);  //Horizontal
	$pdf->Line(20, 45, 195, 45);  //Horizontal
	$pdf->SetFont('Arial','',14);
    $pdf->SetTextColor(255,255,255);  // Establece el color del texto (en este caso es blanco)
    $pdf->SetFillColor(100, 100, 100); // establece el color del fondo de la celda (en este caso es AZUL
	$pdf->Cell(0,6,'Origen:',0,1,'C',true);
	$pdf->SetFont('Arial','',8);
    $pdf->SetTextColor(0,0,0);//color negro
	$pdf->Cell(0,6,'Nombre Cliente: '.$fila['RazonSocial'],0,1);
	$pdf->Cell(0,6,'Situacion Fiscal: '.$fila['SituacionFiscalOrigen'].' | C.U.I.T.: '.$fila['Cuit'],0,1);
	$pdf->Cell(0,6,'Domicilio: '.$fila['DomicilioOrigen'].' - '.$fila['LocalidadOrigen'].' | Tel.: '.$fila['TelefonoOrigen'],0,1); 
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(0,6,'Obs. Cliente: '.$Cliente[Observaciones],0,1); 

	//DESTINATARIO
    $pdf->Ln(5);	
    $pdf->SetFont('Arial','',14);
    $pdf->SetTextColor(255,255,255);  // Establece el color del texto (en este caso es blanco)
    $pdf->SetFillColor(100, 100, 100); // establece el color del fondo de la celda (en este caso es AZUL
    $pdf->Cell(0,6,'Destino:',0,1,'C',true);
    $pdf->SetTextColor(0,0,0);//color negro
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(0,6,'Nombre Cliente: '.$fila['ClienteDestino'],0,1);
    $pdf->Cell(0,6,'Situacion Fiscal: '.$fila['SituacionFiscalDestino'].' | C.U.I.T.: '.$fila['DocumentoDestino'],0,1);
    $pdf->Cell(0,6,'Domicilio: '.$fila['DomicilioDestino'].' - '.$fila['LocalidadDestino'].' | Tel.: '.$fila['TelefonoDestino'],0,1); 
    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(0,6,'Obs. Cliente: '.utf8_decode($ClienteD[Observaciones]),0,1); 

	$pdf->Ln(2);

    $pdf->SetFont('Arial','',14);
    $pdf->SetTextColor(255,255,255);  // Establece el color del texto (en este caso es blanco)
    $pdf->SetFillColor(100, 100, 100); // establece el color del fondo de la celda (en este caso es AZUL
    $pdf->Cell(0,6,'Detalles del Servicio:',0,1,'C',true);
    $pdf->SetTextColor(0,0,0);//color negro

	$pdf->SetFont('Arial','',10);
    
	if($fila['FormaDePago']=='Origen'){
	$pdf->Cell(0,5,'Forma De Pago: '.$fila['FormaDePago'].' - NO COBRAR',0,1); 
	$MuestraPrecioEntrega=1;	
	}else{
	$MuestraPrecioEntrega=1;		
	$pdf->Cell(0,5,'Forma De Pago: '.$fila['FormaDePago'].' - COBRAR EL IMPORTE TOTAL',0,1); 
	}
    
  if($fila[CobrarEnvio]==1){
  $sqlcobranza=mysql_query("SELECT SUM(CobrarEnvio)as Cobranza FROM Ventas WHERE NumPedido='$CodigoSeguimiento'");
  $datocobranza=mysql_fetch_array($sqlcobranza);  
  $pdf->SetFont('Arial','b',10);
	$pdf->Cell(0,5,'Cobranza Integrada: SI | Cobrar $ '.$datocobranza['Cobranza'].' | A CUENTA Y ORDEN DEL CLIENTE',0,1); 
  }else{
  $pdf->Cell(0,5,'Cobranza Integrada: NO COBRAR ',0,1);   
  }
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(0,5,'Valor Declarado: $ '.number_format($ValorDeclarado,2,',','.'),0,1); 
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,5,'Codigo del Cliente: '.$fila['CodigoProveedor'],0,1); 
    // 	$pdf->Cell(0,5,'Transportista: '.$fila['Transportista'],0,1); 
    $pdf->Cell(0,5,'Recorrido: '.$fila[Recorrido],0,1); 
    $pdf->SetFont('Arial','b',8);
    $pdf->Cell(0,5,'Obs. Venta: '.utf8_decode($fila[Observaciones]),0,1); 
//AFORO
    $pdf->SetWidths(array(175));
    $pdf->SetFont('Arial','B',7);
    $pdf->SetFillColor(255,255,255);
    $pdf->SetTextColor(0);
    $pdf->Cell(0,6,'AFORO DE LA GUIA DE CARGA:'.$CodigoSeguimiento,0,1,'C',true);
    $pdf->SetWidths(array(18, 50, 48, 14, 15,15,15));
    $pdf->SetFont('Arial','B',7);
    $pdf->SetFillColor(100,100,100);

    $pdf->SetTextColor(255);
		for($i=0;$i<1;$i++)
			{
				$pdf->Row(array('CODIGO', 'SERVICIO', 'OBSERV.', 'CANT.', 'IMP.NETO','I.V.A.','TOTAL'));
			}

    $historial = $con->conectar();	
    $strConsulta = "SELECT Codigo,Titulo,Comentario,Precio,Cantidad FROM Ventas WHERE NumPedido='$CodigoSeguimiento' AND Eliminado=0";
    $historial = mysql_query($strConsulta);
    $numfilas = mysql_num_rows($historial);
   	//Calcula el total de la repo
    setlocale(LC_ALL,'es_AR');
    $Muestra=mysql_query("SELECT SUM(ImporteNeto)as Neto,SUM(Total) as Total,SUM(Iva3)as Iva FROM Ventas WHERE NumPedido='$CodigoSeguimiento'");
    $row=mysql_fetch_array($Muestra);

    $Muestra1=mysql_query("SELECT SUM(Cantidad) as TotalCantidad FROM Ventas WHERE NumPedido='$CodigoSeguimiento'");
    $row1=mysql_fetch_array($Muestra1);
    $TotalCant=$row1[TotalCantidad];
    $TotalRepo0= '$'.$row[Total];
    $TotalRepo1='';	
   
	for ($i=0; $i<$numfilas; $i++)
	{
        $filaVentas = mysql_fetch_array($historial);
        $Precio='$'.$filaVentas['Precio'];
        $Total='$'.$filaVentas['Cantidad']*$filaVentas['Precio'];	
        //SEGURO SOBRE EL VALOR DECLARADO
//         $ImporteNeto=(($row[Total])/1.21);
        if($filaVentas[Cantidad]==0){
         $Cantidad=1;   
        }else{
          $Cantidad=$filaVentas[Cantidad];
        }
        $ImporteNeto=(($Cantidad*$filaVentas[Precio])/1.21);
        $ImporteNeto_label=number_format($ImporteNeto,2,',','.');

    //     if($ValorDeclarado>5000){

    //     $TotalNeto=$ImporteNeto+(($ValorDeclarado-5000)*0.007);
    //     $Seguro=$ValorDeclarado*0.007;
    //     $Seguro_label=number_format($Seguro,2,',','.');    
    //     $Neto=number_format($TotalNeto,2,',','.');
    //     $Iva=number_format(($TotalNeto*21)/100,2,',','.');
    //     $Iva_precio=$filaVentas[Precio]-$ImporteNeto;
    //     $Iva_precio_label=number_format($Iva_precio,2,',','.'); 
    //     $Iva_seguro=($Seguro*21)/100;
    //     $Iva_seguro_label=number_format($Iva_seguro,2,',','.');
    //     $Seguro_total=$Seguro+$Iva_seguro;
    //     $Seguro_total_label=number_format($Seguro_total,2,',','.');  
    //     $Iva_total=$Iva_precio+$Iva_seguro;
    //     $Iva_total_label=number_format($Iva_total,2,',','.');  
    //     $TotalFinal=number_format((($TotalNeto+($TotalNeto*21)/100)),2,',','.'); 
    //    }else{
        $TotalNeto=$ImporteNeto;
        $Neto=number_format($TotalNeto,2,',','.');
        $Iva_total=number_format(($TotalNeto*21)/100,2,',','.');
        $Iva_precio=$filaVentas[Precio]-$ImporteNeto;
        $Iva_precio_label=number_format($Iva_precio,2,',','.');  
        $Iva_total=$Iva_precio;
        $Iva_total_label=number_format($row[Iva],2,',','.');  
        $Neto_total_label=number_format($row[Neto],2,',','.');  
        $TotalFinal=number_format($row[Total],2,',','.');  
    //    } 


			$pdf->SetFont('Arial','B',5);
				if ($MuestraPrecioEntrega==0){
				$Precio='';
				$Total=	'';
				$TotalRepo=$TotalRepo1;
				}elseif($MuestraPrecioEntrega==1){
				$Precio='$'.$filaVentas['Precio'];
				$Total='$'.$filaVentas['Cantidad']*$filaVentas['Precio'];	
				$TotalRepo='$ '.number_format($TotalRepo0+$ValorDeclarado*0.007,2,',','.');
				}
			if($i%2 == 1)
			{
            $pdf->SetFont('Arial','B',5);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0);
            $pdf->Row(array($filaVentas['Codigo'], $filaVentas['Titulo'], $filaVentas['Comentario'], $Cantidad,'$ '.$ImporteNeto_label,'$ '.$Iva_precio_label,$Precio));

            }else{

            $pdf->SetFont('Arial','B',5);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0);
            $pdf->Row(array($filaVentas['Codigo'], $filaVentas['Titulo'], $filaVentas['Comentario'], $Cantidad,'$ '.$ImporteNeto_label,'$ '.$Iva_precio_label,$Precio));
            }
  }
//       if($ValorDeclarado>5000){
//   $pdf->Row(array('SEGURO 0,07 %', 'SEGUN VALOR DECLARADO DE $ '.number_format($ValorDeclarado,2,',','.'),'','1', '$ '.$Seguro_label,'$ '.$Iva_seguro_label,'$ '.$Seguro_total_label));
//   $pdf->SetTextColor(0,0,0);//color negro
//   }

//   $pdf->Row(array('SEGURO 0,07 %', 'SEGUN VALOR DECLARADO DE $ '.number_format($ValorDeclarado,2,',','.'),'','1', '$ '.$Seguro_label,'$ '.$Iva_seguro_label,'$ '.$Seguro_total_label));
//   $pdf->SetTextColor(0,0,0);//color negro
  $pdf->SetFont('Arial','B',6);
  $pdf->Row(array($filaVentas[''], $filaVentas[''],'TOTAL:', $TotalCant,'$ '.$Neto_total_label,'$ '.$Iva_total_label,'$ '.$TotalFinal));
//   $pdf->Row(array($filaVentas[''], $filaVentas[''],'TOTAL:', $TotalCant,'','$ '.$Iva_total_label,'$ '.$TotalFinal));
  $pdf->SetTextColor(0,0,0);//color negro
    $pdf->SetY(-65);
    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(100,10,'Firma del Cliente',0,0,'L');
    //ACLARACION CLIENTE
    $pdf->SetY(-65);
    $pdf->SetX(90);

    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(100,10,'Aclaracion Nombre',0,0,'L');
    //D.N.I.
    $pdf->SetY(-65);
    $pdf->SetX(150);
    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(100,10,'D.N.I.',0,0,'L');
    //OBSERVACIONES
    $pdf->SetY(-54);
    $pdf->SetFont('Arial','',6);
    $pdf->Cell(100,6,utf8_decode('La presente Guia de Carga (Carta de Porte) es el único titulo legal del contrato de transporte y su prueba entre todas las partes involucradas y por el que ellas reconocen y aceptan las normas y'),0,0,'L');
    $pdf->SetY(-51);
    $pdf->Cell(100,6,utf8_decode(' condiciones generales pre-establecidas con el solo hecho y al momento de entregarse la carga en la empresa. Este contrato de transporte esta sujeto a lo estipulado en el Código Civil y'),0,0,'L');
    $pdf->SetY(-48);  
    $pdf->Cell(100,6,utf8_decode('Comercial de la República Argentina en su Cap.VII Secc. 1era. art. 1280 al art. 1287. Y Secc. 3era  art. 1296 a 1318. Ley 24653/96. Dto. Reg. 1035/02 y por el Reglamento de la Empresa'),0,0,'L');
    $pdf->SetY(-45);  
    $pdf->Cell(100,6,utf8_decode('y cuanto mas acuerdo establecido entre las partes. El Remitente declarará el valor de la mercaderia en sus remitos al momento del despacho, de lo contrario el transportista no estará'),0,0,'L');
    $pdf->SetY(-42);  
    $pdf->Cell(100,6,utf8_decode('obligado a indemnización alguna ante casos de pérdida o robo de la mercaderia transportada. La mercaderia con embalaje insuficiente o deficiente queda excluida del riesgo de roturas salvo accidente.'),0,0,'L');
    $pdf->SetY(-39);  
    $pdf->Cell(100,6,utf8_decode('Los bultos cerrados excluyen de responsabilidad al transportista sobre la existencia, peligrosidad cantidad y calidad de los efectos enviados.'),0,0,'L');
    $pdf->SetY(-36);  
    $pdf->Cell(100,6,utf8_decode('Queda terminantemente prohibido remitir mercaderia peligrosa o contaminante sin la previa autorizacion del transportista, adecuadamente acondicinada e identificada por el remitente.'),0,0,'L');
    $pdf->SetY(-33);  
    $pdf->Cell(100,6,utf8_decode('SEGURO: En el supuesto de corresponder seguro por el porteador, el mismo ampara sobre la base del monto declarado por el cargador, limitado por los deducibles del ley'),0,0,'L');
    $pdf->SetY(-30);  
    $pdf->Cell(100,6,utf8_decode('la Superintendencia de Seguro y Póliza particular.'),0,0,'L');
    $pdf->Output();
  }else{
  $pdf->Output();  
  }  
?>