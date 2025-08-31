<?
// function generarCodigo($longitud) {
 $key = '';
//  $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
 $longitud=8;
  $pattern = date('ymd').'1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
 $date=date('ymd'); 
 $max = strlen($pattern)-1;
 for($i=0;$i < $longitud;$i++)
//  $key .=date('ymd');  
 $key .= $pattern{mt_rand(0,$max)};
//  return $key;
 
// }
print $date.$key;
// print date('ymd');
?>