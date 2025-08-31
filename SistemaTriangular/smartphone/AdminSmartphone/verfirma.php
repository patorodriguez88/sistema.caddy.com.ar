<?
require_once '../signature-pad/signature-to-image.php';
$json = $_POST['output']; // From Signature Pad
$img = sigJsonToImage($json);

// $img = sigJsonToImage(file_get_contents('../signature-pad/examples/sig-output.json'));

// Save to file
imagepng($img, '../../images/Firmas/signature.png');

// Output to browser
header('Content-Type: image/png');
imagepng($img);

// Destroy the image in memory when complete
imagedestroy($img);
?>  