<?php
include_once "../../../Conexion/Conexioni.php";

if (isset($_POST['Color'])) {
  $color = explode('#', $_POST['ColorSeleccionado'], '2');
  $sql = "UPDATE Recorridos SET Color='$color[1]' WHERE Numero='$_POST[Recorrido]'";
  if ($mysqli->query($sql)) {
    echo json_encode(array('success' => 1));
  } else {
    echo json_encode(array('success' => 0));
  }
}

if (isset($_POST['FormaDePago'])) {
  $BuscarRecorridos = $mysqli->query("SELECT Recorrido FROM HojaDeRuta WHERE Estado='Abierto' AND Recorrido<>0 AND Eliminado='0' AND Devuelto='0' AND Seguimiento<>'' GROUP BY Recorrido");

  while (($fila = $BuscarRecorridos->fetch_array(MYSQLI_ASSOC)) != NULL) {

    // BUSCO TODOS LOS SERVICIOS EN TRANS CLIENTES RELACIONADOS EN HOJA DE RUTA
    $sqlhdr = $mysqli->query("SELECT COUNT(HojaDeRuta.id)as id FROM HojaDeRuta INNER JOIN TransClientes ON TransClientes.id=HojaDeRuta.idTransClientes 
WHERE HojaDeRuta.Recorrido='$fila[Recorrido]' AND HojaDeRuta.Eliminado=0 AND TransClientes.Eliminado=0 AND TransClientes.Entregado=0 AND TransClientes.Devuelto=0 AND HojaDeRuta.Devuelto=0 AND HojaDeRuta.Seguimiento<>''");
    $datohdr = $sqlhdr->fetch_array(MYSQLI_ASSOC);
    //BUSCO SOLO ABIERTOS
    $sqlhdrAbiertos = $mysqli->query("SELECT COUNT(id)as id FROM HojaDeRuta WHERE Recorrido='$fila[Recorrido]' AND Estado='Abierto' AND Eliminado=0 AND Devuelto=0 AND Seguimiento<>''");
    $datohdra = $sqlhdrAbiertos->fetch_array(MYSQLI_ASSOC);
    $difhdr = $datohdr['id'] - $datohdra['id'];

    $sqllogistica = $mysqli->query("SELECT * FROM Logistica WHERE id=(SELECT MAX(id) FROM Logistica WHERE Recorrido='$fila[Recorrido]' AND Eliminado='0')");
    $datologistica = $sqllogistica->fetch_array(MYSQLI_ASSOC);

    $sqlrecorrido = $mysqli->query("SELECT Color,Nombre FROM Recorridos WHERE Numero='$fila[Recorrido]'");
    $datorecorrido = $sqlrecorrido->fetch_array(MYSQLI_ASSOC);


    if (isset($datologistica['Estado']) && $datologistica['Estado'] == 'Cerrada') {

      $color = 'danger';
      $Nombre = '<a class="text-danger">Sin Transporte</a>';
    } else if (isset($datologistica['Estado']) && $datologistica['Estado'] == 'Cargada') {

      $color = 'success';
      $Nombre = ucwords($datologistica['NombreChofer']);
    } else if (isset($datologistica['Estado']) && $datologistica['Estado'] == 'Alta') {

      $color = 'warning';
      $Nombre = ucwords($datologistica['NombreChofer']);
    }

    echo '<div class="col-xl-3 col-lg-6">';
    echo '<div class="card widget-flat ribbon-box">';
    echo '<div class="card-body">';
    if (isset($datologistica['Estado']) && $datologistica['Estado'] == 'Cargada') {
      echo '<div class="ribbon-two ribbon-two-success"><span>En Ruta</span></div>';
    }
    echo '<div class="dropdown float-right">';
    echo '<a id="header-title2" class="header-title mb-3 ml-2"></a>';
    //      echo   '<i class="mdi mdi-18px mdi-map-marker"></i>';
    echo '<a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">';
    echo '<i class="mdi mdi-dots-vertical"></i>';
    echo '</a>';
    echo '<div class="dropdown-menu dropdown-menu-right">';

    //       echo '<a value="1" Onclick="eliminarrecorrido('.$fila[Recorrido].');" role="button" class="dropdown-item">Eliminar</a>';
    echo '<a target="t_blank" href="https://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/HojaDeRutapdf.php?HR=' . $fila['Recorrido'] . '" role="button" class="dropdown-item"> Imprimir</a>';
    echo '<a target="t_blank" onclick="abrir_todos(' . $fila['Recorrido'] . ')" role="button" class="dropdown-item"> Abrir Todos</a>';
    //         echo  '<a id="asignacion_recorrido" role="button" class="dropdown-item">Asignar</a>';
    echo  '</div>';
    echo '</div>';
    echo '<div class="float-right">';
    echo '<i class="mdi mdi-truck widget-icon bg-danger rounded-circle text-white"></i></div>';
    if (isset($datorecorrido['Color']) && isset($datologistica['Fecha'])) {
      echo '<h6 class="text-#' . $datorecorrido['Color'] . ' font-weight-normal mt-0 mr-3" style="color:#' . $datorecorrido['Color'] . '" title="Revenue">   Recorrido ' . $fila['Recorrido'] . '    #' . $datologistica['Fecha'] . '</h6>';
    } else {
      echo '<h6 class="text-muted mt-0 mr-3" title="Revenue">   Recorrido ' . $fila['Recorrido'] . '</h6>';
    }
    echo '<h6 class="text-muted mt-0 mb-1">' . $datorecorrido['Nombre'] . '</h6>';
    echo '<h5 class="mt-3 mb-2">' . $Nombre . '</h5>';
    echo '<p class="mb-0 text-muted">';
    echo '<span class="text-nowrap"><i class="mdi mdi-18px mdi-map-marker text-success"></i>' . $datohdr['id'] . ' Servicios </span>';
    if ($difhdr > 0) {
      echo '<span class="text-nowrap"><i class="mdi mdi-18px mdi-map-marker text-danger"></i>' . $difhdr . ' Cerrados ! </span>';
    }
    echo '</p>';
    echo '<p class="mb-0 text-muted">';
    echo  '<span class="badge badge-' . $color . ' mr-1">';
    if (isset($datorecorrido['Color']) && isset($datologistica['Estado'])) {
      echo '<i> Orden ' . $datologistica['Estado'] . '</i> </span> <input type="color" id="color" value="#' . $datorecorrido['Color'] . '" onblur="color(this.value,' . $fila['Recorrido'] . ')" ></p>';
    } else {
      echo '</span>';
    }
    if (isset($fila['Recorrido'])) {
      echo '<button value="' . $fila['Recorrido'] . '" Onclick="veo(this.value);" id="boton_abrir_hdr" type="button" class="btn btn-block btn-outline-success mt-2"><i class="mdi mdi-folder-marker"></i> Abrir Hoja de Ruta</button>';
    }
    //   echo '<span class="text-nowrap text-right"></span>';

    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
  }
  // Liberar resultados
  mysqli_free_result($BuscarRecorridos);
}
