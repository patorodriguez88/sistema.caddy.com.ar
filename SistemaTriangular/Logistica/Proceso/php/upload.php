<?php
    // Función para convertir el tamaño a unidades legibles
    function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } else {
            $bytes = $bytes . ' bytes';
        }
    
        return $bytes;
    }

if(!empty($_FILES)){
        
    $fileName = $_FILES['file']['name'];
    
    if($_POST['vehicle_up_domain_value']==1){//SI EL ARCHIVO ES DEL TITULO

        $targetDir = "../../Titulos/";    
        $NewName = $targetDir.$_POST['vehicle_up_domain'].'.pdf';
    
    }elseif($_POST['vehicle_up_domain_value']==2){//EL ARCHIVO ES LA IMAGEN DEL VEHICULO

        $targetDir = "../../Images/";    
        $NewName = $targetDir.$_POST['vehicle_up_domain'].'.jpg';

    }else{//EL ARCHIVO ES EL SEGURO DEL AUTO
        
        $targetDir = "../../Polizas/";    
        $NewName = $targetDir.$_POST['vehicle_up_domain_sure'].'.pdf';
    
    }

    $targetFile = $targetDir.$fileName;

    move_uploaded_file($_FILES['file']['tmp_name'],$targetFile);
    
    if(rename($targetFile, $NewName)){
        

        
            echo "Archivo subido correctamente";
        


    } else {

        echo "Ha ocurrido un error al intentar renombrar el archivo.-> ".$_POST['vehicle_up_domain'];

    }
    
    $nombreArchivo=$NewName;

    if (file_exists($nombreArchivo)) {
        $tamañoArchivo = filesize($nombreArchivo);
    
        // Convierte el tamaño a unidades más legibles, como KB, MB, etc., si es necesario
        $tamañoLegible = formatSizeUnits($tamañoArchivo);
    
        echo "El tamaño del archivo $nombreArchivo es $tamañoLegible.";

    } else {
        
        echo "El archivo $nombreArchivo no existe.";
    
    }
    
    
}
?>