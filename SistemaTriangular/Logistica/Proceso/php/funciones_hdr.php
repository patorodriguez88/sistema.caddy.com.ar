<?php
include_once "../../../Conexion/Conexioni.php";

// Etiqueta legible para Recorridos.UltimoOrdenMetodo, mostrada en las cards
// de Hoja de Ruta - 'Gestya' es el nombre interno del sistema legacy de
// Ordenar segun Reparto, se muestra como "Reparto" para el operador.
function etiquetaMetodoOrden($metodo)
{
  switch ($metodo) {
    case 'Automatico':
      return 'Automático';
    case 'Manual':
      return 'Manual';
    case 'Gestya':
      return 'Reparto';
    case 'Planificador':
      return 'Planificador';
    default:
      return null;
  }
}

if (isset($_POST['Color'])) {
  $color = explode('#', $_POST['ColorSeleccionado'], 2);
  $sql = "UPDATE Recorridos SET Color='$color[1]' WHERE Numero='$_POST[Recorrido]'";
  if ($mysqli->query($sql)) {
    echo json_encode(array('success' => 1));
  } else {
    echo json_encode(array('success' => 0));
  }
}

if (isset($_POST['FormaDePago'])) {
  $BuscarRecorridos = $mysqli->query("SELECT Recorrido FROM HojaDeRuta WHERE Estado='Abierto' AND Recorrido<>0 AND Eliminado='0' AND Devuelto='0' AND Seguimiento<>'' GROUP BY Recorrido");

  // Se junta primero toda la info de cada card (en vez de imprimir en el
  // loop) para poder ordenarlas antes de mostrarlas: los recorridos "En
  // Ruta" (Logistica.Estado='Cargada') van primero, y el resto despues,
  // ordenado por Logistica.NumerodeOrden.
  $cards = [];

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

    $sqlrecorrido = $mysqli->query("SELECT Color,Nombre,UltimoOrdenMetodo,UltimoOrdenKm,UltimoOrdenMinutos FROM Recorridos WHERE Numero='$fila[Recorrido]'");
    $datorecorrido = $sqlrecorrido->fetch_array(MYSQLI_ASSOC);


    // $color/$Nombre solo se definian dentro de este if/elseif - si el
    // Recorrido no tenia ninguna orden de Logistica en estado Cerrada/
    // Cargada/Alta (por ejemplo, paradas abiertas sobre un Recorrido sin
    // orden asignada todavia), quedaban indefinidas y tiraban warnings mas
    // abajo. Se agrega un valor por defecto para ese caso.
    $enRuta = isset($datologistica['Estado']) && $datologistica['Estado'] == 'Cargada';

    if (isset($datologistica['Estado']) && $datologistica['Estado'] == 'Cerrada') {

      $color = 'danger';
      $Nombre = '<a class="text-danger">Sin Transporte</a>';
    } else if ($enRuta) {

      $color = 'success';
      $Nombre = ucwords($datologistica['NombreChofer']);
    } else if (isset($datologistica['Estado']) && $datologistica['Estado'] == 'Alta') {

      $color = 'warning';
      $Nombre = ucwords($datologistica['NombreChofer']);
    } else {

      $color = 'secondary';
      $Nombre = '<a class="text-muted">Sin Orden Asignada</a>';
    }

    $cards[] = [
      'enRuta' => $enRuta,
      // Sin orden asignada (NumerodeOrden 0/null) queda al final del grupo.
      'numerodeOrden' => !empty($datologistica['NumerodeOrden']) ? (int)$datologistica['NumerodeOrden'] : PHP_INT_MAX,
      'fila' => $fila,
      'datohdr' => $datohdr,
      'difhdr' => $difhdr,
      'datologistica' => $datologistica,
      'datorecorrido' => $datorecorrido,
      'color' => $color,
      'Nombre' => $Nombre,
    ];
  }
  // Liberar resultados
  mysqli_free_result($BuscarRecorridos);

  usort($cards, function ($a, $b) {
    if ($a['enRuta'] !== $b['enRuta']) {
      return $a['enRuta'] ? -1 : 1;
    }
    return $a['numerodeOrden'] <=> $b['numerodeOrden'];
  });

  $huboEnRuta = false;
  $mostroSeparador = false;

  foreach ($cards as $c) {
    $fila = $c['fila'];
    $datohdr = $c['datohdr'];
    $difhdr = $c['difhdr'];
    $datologistica = $c['datologistica'];
    $datorecorrido = $c['datorecorrido'];
    $color = $c['color'];
    $Nombre = $c['Nombre'];

    if ($c['enRuta']) {
      $huboEnRuta = true;
    } elseif ($huboEnRuta && !$mostroSeparador) {
      // Separador visual entre los recorridos "En Ruta" y el resto.
      echo '<div class="col-12"><hr class="my-2"></div>';
      $mostroSeparador = true;
    }

    echo '<div class="col-xl-3 col-lg-6">';
    echo '<div class="card widget-flat ribbon-box">';
    echo '<div class="card-body">';
    if (isset($datologistica['Estado']) && $datologistica['Estado'] == 'Cargada') {
      echo '<div class="ribbon-two ribbon-two-success"><span>En Ruta</span></div>';
    }
    echo '<div class="dropdown float-end">';
    echo '<a id="header-title2" class="header-title mb-3 ml-2"></a>';
    echo '<a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">';
    // echo '<i class="mdi mdi-dots-vertical"></i>';
    echo '<i class="ri-more-2-fill"></i>';
    echo '</a>';
    echo '<div class="dropdown-menu dropdown-menu-end">';


    echo '<a target="t_blank" href="Informes/HojaDeRutapdf.php?HR=' . $fila['Recorrido'] . '" role="button" class="dropdown-item"> Imprimir</a>';
    echo '<a target="t_blank" onclick="abrir_todos(' . $fila['Recorrido'] . ')" role="button" class="dropdown-item"> Abrir Todos</a>';
    //         echo  '<a id="asignacion_recorrido" role="button" class="dropdown-item">Asignar</a>';
    echo  '</div>';
    echo '</div>';
    echo '<div class="float-end">';
    echo '<i class="mdi mdi-truck widget-icon bg-danger rounded-circle text-white"></i></div>';
    // Punto de color del Recorrido: circulo chico al lado del nombre, en la
    // misma linea del titulo (antes quedaba como bloque aparte debajo). Se usa
    // onchange (se dispara al cerrar la paleta nativa con un color elegido) en
    // vez de onblur (poco confiable para inputs type=color en varios navegadores).
    $colorDot = isset($datorecorrido['Color'])
      ? ' <input type="color" class="color-dot" value="#' . ltrim($datorecorrido['Color'], '#') . '" onchange="color(this.value,' . $fila['Recorrido'] . ')" title="Cambiar color del recorrido">'
      : '';
    if (isset($datorecorrido['Color']) && isset($datologistica['Fecha'])) {
      $colorHex = ltrim($datorecorrido['Color'], '#');
      echo '<h6 class="font-weight-normal mt-0 mr-3" style="color:#' . $colorHex . '" title="Revenue">   Recorrido ' . $fila['Recorrido'] . '    #' . $datologistica['Fecha'] . $colorDot . '</h6>';
    } else {
      echo '<h6 class="text-muted mt-0 mr-3" title="Revenue">   Recorrido ' . $fila['Recorrido'] . $colorDot . '</h6>';
    }
    echo '<h6 class="text-muted mt-0 mb-1">' . ($datorecorrido['Nombre'] ?? '') . '</h6>';
    echo '<h5 class="mt-3 mb-2">' . $Nombre . '</h5>';
    echo '<p class="mb-0 text-muted">';
    echo '<span class="text-nowrap"><i class="mdi mdi-18px mdi-map-marker text-success"></i>' . $datohdr['id'] . ' Servicios </span>';
    if ($difhdr > 0) {
      echo '<span class="text-nowrap"><i class="mdi mdi-18px mdi-map-marker text-danger"></i>' . $difhdr . ' Cerrados ! </span>';
    }
    echo '</p>';

    // Horario de salida (Logistica.Hora de la Orden vigente) y, si ya se
    // calculo/confirmo un orden para este Recorrido, el metodo usado
    // (Automatico/Manual/Reparto/Planificador) + km y tiempo totales
    // estimados de esa ruta.
    if (!empty($datologistica['Hora']) && $datologistica['Hora'] !== '00:00:00') {
      echo '<p class="mb-0 text-muted small"><i class="mdi mdi-18px mdi-clock-outline"></i> Salida ' . substr($datologistica['Hora'], 0, 5) . '</p>';
    }
    $metodoLabel = etiquetaMetodoOrden($datorecorrido['UltimoOrdenMetodo'] ?? null);
    if ($metodoLabel !== null) {
      echo '<p class="mb-0 text-muted small"><i class="mdi mdi-18px mdi-routes"></i> Orden ' . $metodoLabel . '</p>';
    }
    if (!empty($datorecorrido['UltimoOrdenKm']) || !empty($datorecorrido['UltimoOrdenMinutos'])) {
      echo '<p class="mb-0 text-muted small">';
      if (!empty($datorecorrido['UltimoOrdenKm'])) {
        echo '<span class="text-nowrap mr-2"><i class="mdi mdi-18px mdi-map-marker-distance"></i> ' . number_format((float)$datorecorrido['UltimoOrdenKm'], 1) . ' km</span>';
      }
      if (!empty($datorecorrido['UltimoOrdenMinutos'])) {
        $min = (int)$datorecorrido['UltimoOrdenMinutos'];
        $tiempoTexto = $min >= 60 ? floor($min / 60) . 'h ' . ($min % 60) . 'm' : $min . ' min';
        echo '<span class="text-nowrap"><i class="mdi mdi-18px mdi-timer-outline"></i> ' . $tiempoTexto . '</span>';
      }
      echo '</p>';
    }

    echo '<p class="mb-0 text-muted">';
    echo  '<span class="badge badge-' . $color . ' mr-1">';
    if (isset($datorecorrido['Color']) && isset($datologistica['Estado'])) {
      echo '<i> Orden ' . $datologistica['Estado'] . '</i> </span></p>';
    } else {
      echo '</span>';
    }
    if (isset($fila['Recorrido'])) {
      echo '<button value="' . $fila['Recorrido'] . '" Onclick="veo(this.value);" id="boton_abrir_hdr" type="button" class="btn w-100 btn-outline-success mt-2"><i class="mdi mdi-folder-marker"></i> Abrir Hoja de Ruta</button>';
    }
    //   echo '<span class="text-nowrap text-right"></span>';

    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
  }
}
