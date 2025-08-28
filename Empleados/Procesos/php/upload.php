<?php
// Verificar si se ha enviado un archivo
if(isset($_FILES['archivo'])){
    
    $archivo = $_FILES['archivo'];

    //Nombre de la carpeta
    $nombre_carpeta = $_POST['nombre_carpeta'];
 
    // Nombre del archivo
    $nombre_archivo = $archivo['name'];

    // Ruta temporal del archivo en el servidor
    $ruta_temporal = $archivo['tmp_name'];

    // Ruta donde se guardará el archivo en el servidor
    $ruta_destino = '../../Tareas/'.$nombre_carpeta.'/'.$nombre_archivo;

    $directorio = '../../Tareas/'.$nombre_carpeta.'/';

    // Verificar si la carpeta existe, si no, crearla
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true); // Crear carpeta recursivamente
    }

    // Mover el archivo de la ruta temporal a la ruta de destino
    if(move_uploaded_file($ruta_temporal, $ruta_destino)){

        echo "El archivo se ha subido correctamente.";

    } else {
        
        echo "Ha ocurrido un error al subir el archivo.";
    
    }
}



?>
