<?
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangularcopia",$conexion);  
$CodigoSeguimiento='PATOBIEN';
require_once '../../signature-pad/signature-to-image.php';
$json = $_POST['output']; // From Signature Pad
$img = sigJsonToImage($json);
// $img = sigJsonToImage(file_get_contents('../signature-pad/examples/sig-output.json'));
// Save to file
imagepng($img, '../../../images/Firmas/'.$CodigoSeguimiento.'.png');
// Output to browser
// header('Content-Type: image/png');
// imagepng($img);
// Destroy the image in memory when complete
imagedestroy($img);
// echo json_encode(array('resultado' => 1));

// echo json_encode(array('resultado' => 0));


