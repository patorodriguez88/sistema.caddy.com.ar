<?
session_start();

include('../ConexionBD.php');
        $sqlasigna=mysql_query("SELECT Clientes.id,Clientes.nombrecliente,Clientes.Direccion,Clientes.Ciudad,Clientes.Recorrido,Clientes.Relacion,Clientes.idProveedor FROM Clientes,PreVenta 
          WHERE Clientes.idProveedor=PreVenta.idClienteDestino 
          AND Clientes.Relacion='36' 
          AND PreVenta.NCliente='36' 
          AND PreVenta.Cargado=0 
          GROUP BY PreVenta.idClienteDestino");
   
   $totalclientes=mysql_num_rows($sqlasigna); 
   while($datosqlasigna=mysql_fetch_array($sqlasigna)){
      $sqlh=mysql_query("SELECT Recorrido,idCliente FROM HojaDeRuta WHERE id=(SELECT MAX(id)as id FROM HojaDeRuta WHERE idCliente='$datosqlasigna[id]')");
     $datosqlhojaderuta=mysql_fetch_array($sqlh);
   $datos[] = array('Recorrido' => $datosqlhojaderuta[Recorrido], 'idCliente' => $datosqlhojaderuta[idCliente]);
   }

$grupo = array();
$directorios = array();
foreach($datos as $valor => $valor_){

	//CONSEGUIR EL VALOR ACTUAL
	$directorio_ = ucwords(strtolower($valor_['Recorrido']));

	//VERIFICAR SI EL VALOR SE REPITE
	if(!in_array($directorio_, $directorios)){
		//SI NO EXISTE LO AGREGA AL NUEVO ARRAY
		$directorios[] = $directorio_;
	}

	//JALO EL VALOR ACTUAL
	$directorio_u = array_search($directorio_, $directorios);

	//AGREGO EL NUEVO REGISTRO AL CONTENEDOR DEL VALOR CORRESPONDIENTE
	$grupo[$directorio_u][] = $valor_;
}
$directorio_ = array();
foreach($grupo as $uno){
	foreach($uno as $dos){
		$archivo_[] = $dos['idCliente'];
	}
	$directorio_[] = array_filter(array(
						'Recorrido' => $uno[0]['Recorrido'],
						'idCliente' => array_filter($archivo_)
					)
				);
	unset($archivo_);
}
?>
 <ul class="easyui-tree" data-options="animate:true,dnd:true">
	<?php foreach($directorio_ as $archivos){ ?>
	<li data-options="state:'closed'">
		<span><?php echo $archivos['Recorrido']; ?></span>
		<?php if($archivos['idCliente']){ ?>
		<ul>
			<?php foreach($archivos['idCliente'] as $archivos_){ ?>
				<li><?php echo $archivos_; ?></li>
			<?php } ?>
		</ul>
		<?php } ?>
	</li>
	<?php } ?>
</ul>    
    
  