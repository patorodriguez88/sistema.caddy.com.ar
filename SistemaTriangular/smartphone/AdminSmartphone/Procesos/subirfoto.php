<?
    $i=1; 
    $image=$_POST["fpos"];
    $dir='../../../images/Photos/';
    $punt=strrpos($image,".");
    $puntcodigo=substr($punt,($punt-3));


function imageExists($image,$dir) {
    $i=1; 
    $probeer=$image;
    while(file_exists($dir.$probeer)) {
        $punt=strrpos($image,".");
        if(substr($image,($punt-3),1)!==("[") && substr($image,($punt-1),1)!==("]")) {
            $probeer=substr($image,0,$punt)."[".$i."]".
            substr($image,($punt),strlen($image)-$punt);
        } else {
            $probeer=substr($image,0,($punt-3))."[".$i."]".
            substr($image,($punt),strlen($image)-$punt);
        }
        $i++;
    }
    return $probeer;
}
if($_FILES['file']['name']<>''){
// $ruta_indexphp = dirname(realpath(__FILE__));
if($_FILES['file']['type']=='image/jpg'){
$extension='.jpg';  
}elseif($_FILES['file']['type']=='image/jpeg'){
$extension='.jpeg';  
}elseif($_FILES['file']['type']=='image/png'){
$extension='.png';
}

$Nombrefoto=$image.$extension;
$ruta_fichero_origen = $_FILES['file']['tmp_name'];
// $ruta_nuevo_destino = $ruta_indexphp . '/SistemaTriangular/images/Photos/'.$Nombrefoto;  

$NuevoNombreFoto=imageExists($Nombrefoto,'../../../images/Photos/');
$ruta_nuevo_destino = '../../../images/Photos/'.$NuevoNombreFoto;  
// $ruta_nuevo_destino = '../../../images/Photos/'.$_FILES['file']['name'];
$extensiones = array(0=>'image/jpg',1=>'image/jpeg',2=>'image/png');
$max_tamanyo = 1024 * 1024 * 24;
if ( in_array($_FILES['file']['type'], $extensiones) ) {
  echo $ruta_nuevo_destino;
      if ( $_FILES['file']['size']< $max_tamanyo ) {
            if( move_uploaded_file ( $ruta_fichero_origen, $ruta_nuevo_destino ) ) {
            echo $ruta_nuevo_destino;
            }else{
            echo 0;  
          }
     }
} 
     
}
?>