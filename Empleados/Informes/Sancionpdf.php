<?php
session_start();
require('../../fpdf/fpdf.php');
require('../../../conexion.php');
//--------------------DESDE ACA PARA LA FUNCION DE ALINEAR TEXTO----------------
function textIntoCols($strOriginal,$noCols,$pdf) 
{ 
    $iAlturaRow = 6; //Altura entre renglones 
    $iMaxCharRow = 180; //Número máximo de caracteres por renglón 
    $iSizeMultiCell = $iMaxCharRow / $noCols; //Tamaño ancho para la columna 
    $iTotalCharMax = 9957; //Número máximo de caracteres por página 
    $iCharPerCol = $iTotalCharMax / $noCols; //Caracteres por Columna 
    $iCharPerCol = $iCharPerCol - 290; //Ajustamos el tamaño aproximado real del número de caracteres por columna 
    $iLenghtStrOriginal = strlen($strOriginal); //Tamaño de la cadena original 
    $iPosStr = 0; // Variable de la posición para la extracción de la cadena. 
    // get current X and Y 
    $start_x = $pdf->GetX(); //Posición Actual eje X 
    $start_y = $pdf->GetY(); //Posición Actual eje Y 
    $cont = 0; 
    while($iLenghtStrOriginal > $iPosStr) // Mientras la posición sea menor al tamaño total de la cadena entonces imprime 
    { 
        $strCur = substr($strOriginal,$iPosStr,$iCharPerCol);//Obtener la cadena actual a pintar 
        if($cont != 0) //Evaluamos que no sea la primera columna 
        { 
            // seteamos a X y Y, siendo el nuevo valor para X 
            // el largo de la multicelda por el número de la columna actual, 
            // más 10 que sumamos de separación entre multiceldas 
            $pdf->SetXY(($iSizeMultiCell*$cont)+10,$start_y); //Calculamos donde iniciará la siguiente columna 
        } 
        $pdf->MultiCell($iSizeMultiCell,$iAlturaRow,$strCur); //Pintamos la multicelda actual 
        $iPosStr = $iPosStr + $iCharPerCol; //Posicion actual de inicio para extracción de la cadena 
        $cont++; //Para el control de las columnas 
    }     
    return $pdf; 
} 
//----------hasta aca texto en columnas

class PDF extends FPDF
{
// Cabecera de página
function Header()
{
  header("Content-Type: text/html; charset=iso-8859-1 ");
    // Logo
	$ConsultaEmpresa=mysql_query("SELECT * FROM DatosEmpresa");
  $this->Image('../../images/caddy.jpg',8,6,53);
  while($row=mysql_fetch_array($ConsultaEmpresa)){
  	$this->SetFont('Arial','',9);
		$this->Text(10,29,$row[RazonSocial],0,'C', 0);
		$this->Text(10,34,'Cuit: '.$row[Cuit],0,'C', 0);
		$this->Text(10,39,$row[Direccion],0,'C', 0);
		$this->Text(10,44,$_SESSION['DEWeb'],0,'C', 0);
    
  }

	// Arial bold 15
    $this->SetFont('Arial','B',15);
    // Movernos a la derecha
    $this->Cell(80);
    // Título
    $this->Cell(30,50,'Sancion Disciplinaria',0,0,'C');
    // Salto de línea
    $this->Ln(20);

}

// Pie de página
function Footer()
{
    // Posición: a 1,5 cm del final
    $this->SetY(250);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Número de página
    $this->Cell(0,-15,'X _______________________________',0,2,'I');  
    $this->Cell(0,30,'Firma '.$_SESSION['NombreEmpleado'],0,2,'I');  

    $this->SetY(250);
    $this->SetX(120);
    $this->Cell(0,-15,'X _______________________________',0,2,'I');  
    $this->Cell(0,30,'Firma Responsable '.$_SESSION['Empresa'],0,2,'I');  
    $this->SetX(0);
    $this->SetY(280);

	$this->Cell(180,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}
// Creación del objeto de la clase heredada
	$con = new DB;
	$sqlSanciones = $con->conectar();	
	$sqlConsulta=mysql_query("SELECT * FROM Sanciones WHERE id='".$_GET[id]."'");
	while($row = mysql_fetch_array($sqlConsulta)){
			$motivo=$row[Motivo];
      $NombreEmpleado=$row[Empleado];
			$fecha=$row[Fecha];
			$SancionAplicada=$row[Sancion];	
			}
$nota='      Lo invitamos a reflexionar sobre lo ocurrido y le prevenimos que en caso de incurrir en cualquier nueva inconducta nos veremos obligados a sancionarlo con mayor severidad. Queda Ud. debidamente notificado y prevenido.';
// 	utf8_decode(
//  $str = "\nCat".html_entity_decode("&aacute;").$nota."\n";
$str = iconv('UTF8', 'windows-1252', $nota);
// $Motivo=utf8_decode($motivo);
$Motivo=html_entity_decode($motivo);
// $str1 = iconv('utf8_decode', 'windows-1252', utf8_decode($nota1));
// $str2 = iconv('utf8_decode', 'windows-1252', utf8_decode($nota2));

$FechaS= explode("-",$fecha);
$FechaSancion=$FechaS[2]."/".$FechaS[1]."/".$FechaS[0];

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->Ln(20);
$pdf->SetFont('Arial','',10);
$pdf->Text(150,14,utf8_decode('Córdoba ').$FechaSancion,0,'C', 0);
$pdf->SetFont('Arial','B',13);
$pdf->Cell(120,14,'Sr.:'.$NombreEmpleado,0,2,'I');
$pdf->SetFont('Times','B',12);
$pdf->Cell(120,14,'Sancion Aplicada: '.$SancionAplicada,0,2,'I');
$pdf->SetFont('Times','',12);
$pdf->Cell(120,6,'       Comunicamos a Ud. que esta Empresa ha decidido aplicarle una SANCION DISCIPLINARIA ',0,2,'I');
$pdf->Cell(120,6,'consistente en un SEVERO APERCIBIMIENTO con constancia escrita en su legajo y con fundamento,',0,2,'I');
$pdf->Cell(120,6,' en la grave inconducta verificada de su parte. ',0,2,'I');
$pdf->SetFont('Times','I',12);
textIntoCols($Motivo,1,$pdf);
$pdf->SetFont('Times','',12);
textIntoCols($str,1,$pdf); 
$pdf->Output();
?>