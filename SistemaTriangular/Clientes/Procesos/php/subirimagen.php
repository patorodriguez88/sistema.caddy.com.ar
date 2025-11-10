<?php

//NO FUNCIONA.



print "imagen type".$_FILES["imagen"]["type"];
print "imagen size".$_FILES["imagen"]["size"];

if ($_FILES["imagen"]["error"] > 0){
	echo "ha ocurrido un error";
} else {
	//ahora vamos a verificar si el tipo de archivo es un tipo de imagen permitido.
	//y que el tamano del archivo no exceda los 100kb
	$permitidos = array("image/jpg", "image/pdf", "image/gif", "image/png","application/pdf");
	$limite_kb = 1000;

  if (in_array($_FILES['imagen']['type'], $permitidos) && $_FILES['imagen']['size'] <= $limite_kb * 40960){
		//esta es la ruta donde copiaremos la imagen
		//recuerden que deben crear un directorio con este mismo nombre
		//en el mismo lugar donde se encuentra el archivo subir.php
//    $extension = end(explode(".", $_FILES['imagen']['name']));
//     $ruta = "../FacturasVenta/" . $NumeroComprobante.".".$extension;
    
    $ruta = "../FacturasVenta/".$TCA."-".$_FILES['imagen']['name'];
		//comprovamos si este archivo existe para no volverlo a copiar.
		//pero si quieren pueden obviar esto si no es necesario.
		//o pueden darle otro nombre para que no sobreescriba el actual.
		if (!file_exists($ruta)){
			//aqui movemos el archivo desde la ruta temporal a nuestra ruta
			//usamos la variable $resultado para almacenar el resultado del proceso de mover el archivo
			//almacenara true o false
			$resultado = @move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta);
			if ($resultado){
				echo "El archivo ha sido movido exitosamente";
			header("location:http://www.caddy.com.ar/SistemaTriangular/Inicio/Cpanel.php");

			} else {
				echo "Ocurrio un error al mover el archivo.";
        echo $ruta;
			}
		} else {
			echo $NumeroComprobante.".".$extension.", este archivo existe";
		}
	} else {
		echo "Archivo no permitido, es tipo de archivo prohibido o excede el tamano de $limite_kb Kilobytes";
		header("location:http://www.caddy.com.ar/SistemaTriangular/Ventas/Ventas.php");
  }
}



?>