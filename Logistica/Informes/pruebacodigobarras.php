<?php
session_start();
require('../../fpdf/fpdf.php');
require('../../Conexion/Conexioni.php');
include_once "../../phpbarcode/barcode.php";    
	
	$sql = "SELECT CodigoProveedor FROM TransClientes WHERE CodigoSeguimiento='3NBXCCHSV'";
	$resultado = $mysqli->query($sql);
	
	$pdf = new FPDF();
	$pdf->AddPage();
	$pdf->SetAutoPageBreak(true, 20);
	$y = $pdf->GetY();
	
	while ($row = $resultado->fetch_assoc()){
		
		$code = $row['CodigoProveedor'];
		
		barcode('codigos/'.$code.'.png', $code, 20, 'horizontal', 'code128', true);
		
		$pdf->Image('codigos/'.$code.'.png',10,$y,50,0,'PNG');
		
		$y = $y+15;
	}
	$pdf->Output();	
	
?>
