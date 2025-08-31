<?php
// Verificar si se ha enviado un archivo
if(isset($_FILES['archivo'])){
    $archivo = $_FILES['archivo'];

    // Nombre del archivo
    $nombre_archivo = $archivo['name'];
    $nombre_archivo = $_POST['nombreArchivo'];    

    // Ruta temporal del archivo en el servidor
    $ruta_temporal = $archivo['tmp_name'];

    // Ruta donde se guardará el archivo en el servidor
    $ruta_destino = '../Presupuestos/' . $nombre_archivo.'.pdf';

    // Mover el archivo de la ruta temporal a la ruta de destino
    if(move_uploaded_file($ruta_temporal, $ruta_destino)){

        echo "El archivo se ha subido correctamente.";
    
    } else {
    
        echo "Ha ocurrido un error al subir el archivo.";
    
    }
}

if($_POST['borrar_archivo']==1){

$nombre_archivo_a_borrar = $_POST['nombre_archivo']; // Reemplaza con la ruta completa de tu archivo

// Verificar si el archivo existe antes de intentar borrarlo
if (file_exists('../Presupuestos/'.$nombre_archivo_a_borrar)) {
    // Intentar borrar el archivo
    if (unlink('../Presupuestos/'.$nombre_archivo_a_borrar)) {

        echo json_encode(array('success'=>1));
    
    } else {
        
        echo json_encode(array('success'=>0,'error'=>"No se pudo borrar el archivo."));        
    
    }

} else {

    echo json_encode(array('success'=> 0,"error"=>"El archivo no existe."));
}
}
?>



