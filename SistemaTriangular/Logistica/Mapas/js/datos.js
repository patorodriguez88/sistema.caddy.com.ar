// "Ver Ruta" (dropdown de tres puntos, recorrido abierto): antes calculaba
// con la Directions API vieja de Google y dos direcciones hardcodeadas como
// origen/destino, con un checkbox "Optimizar Ruta" opcional. Ahora usa el
// mismo motor que Planificador (Routes API real, ver orden_automatico.php),
// siempre calcula el mejor orden, lo dibuja como preview en el mapa SIN
// grabar nada, y recien graba si el operador aprieta "Aceptar Ruta".

$("#routes").click(function () {
  pedirFechaHoraYVerRuta();
});

$("#points").click(function () {
  var Recorrido = $("#recorrido").html();
  veo(Recorrido);
  $("#routes").css("display", "block");
  $("#points").css("display", "none");
  $("#route_header").css("display", "none");
  $("#optimizar_ok").css("display", "none");
});

// Antes de calcular, se le pregunta al operador a que fecha/hora sale el
// recorrido (por defecto ahora mismo) - no siempre es hoy mismo/ahora, y
// depender en silencio de Logistica.Hora podia quedar desactualizado o en
// el pasado (ver fix de "Timestamp must be set to a future time").
function pedirFechaHoraYVerRuta() {
  var ahora = new Date();
  var fechaDefault =
    ahora.getFullYear() +
    "-" +
    String(ahora.getMonth() + 1).padStart(2, "0") +
    "-" +
    String(ahora.getDate()).padStart(2, "0");
  var horaDefault = String(ahora.getHours()).padStart(2, "0") + ":" + String(ahora.getMinutes()).padStart(2, "0");

  Swal.fire({
    title: "¿A qué fecha y hora sale el recorrido?",
    html:
      '<input type="date" id="swal-fecha-salida" class="swal2-input" value="' +
      fechaDefault +
      '">' +
      '<input type="time" id="swal-hora-salida" class="swal2-input" value="' +
      horaDefault +
      '">',
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: "Calcular ruta",
    cancelButtonText: "Cancelar",
    preConfirm: function () {
      var fecha = document.getElementById("swal-fecha-salida").value;
      var hora = document.getElementById("swal-hora-salida").value;
      if (!fecha || !hora) {
        Swal.showValidationMessage("Completá fecha y hora de salida.");
        return false;
      }
      return { fecha: fecha, hora: hora };
    },
  }).then(function (result) {
    if (!result.isConfirmed) return;
    verRutaOptimizada(result.value.fecha, result.value.hora);
  });
}

function verRutaOptimizada(fechaSalida, horaSalida) {
  var Recorrido = $("#recorrido").html();

  $.ajax({
    data: { Orden_Automatic: 1, Recorrido: Recorrido, FechaSalida: fechaSalida, HoraSalida: horaSalida },
    type: "POST",
    url: "Mapas/php/orden_automatico.php",
    // orden_automatico.php manda Content-Type: application/json - sin esto,
    // jQuery auto-detecta y parsea la respuesta solo, y el JSON.parse(response)
    // de mas abajo (pensado para un string) rompe con "Unexpected token o"
    // porque response ya llega como objeto.
    dataType: "text",
    beforeSend: function () {
      $("#info-alert-modal-title").html("Calculando la mejor ruta...");
      $("#info-alert-modal").modal("show");
    },
    success: function (response) {
      $("#info-alert-modal").modal("hide");

      var jsonData;
      try {
        jsonData = JSON.parse(response);
      } catch (e) {
        toast("error", "Error", "Respuesta inválida del servidor.");
        return;
      }

      if (jsonData.resultado != 1) {
        $("#alert_route").css("display", "block");
        $("#alert_route_header").html("No se pudo calcular la ruta");
        $("#alert_route_text").html(jsonData.message || "");
        return;
      }

      dibujarPreviewRuta(jsonData);

      $("#routes").css("display", "none");
      $("#points").css("display", "block");
      $("#route_header").css("display", "inline");
      $("#optimizar_ok").css("display", "inline-block");
    },
    error: function (jqXHR, textStatus, errorThrown) {
      $("#info-alert-modal").modal("hide");
      console.error("Error en verRutaOptimizada:", textStatus, errorThrown);
      toast("error", "Error del servidor", "No se pudo calcular la ruta. Reintentá de nuevo.");
    },
  });
}

function dibujarPreviewRuta(jsonData) {
  if (!jsonData.paradas || jsonData.paradas.length === 0) return;

  // Reasigna la variable global "map" (definida en Mapas/js/hojaderuta.js,
  // mismo patron que ya usa initMap() en cada veo() - se descarta el mapa
  // viejo entero en vez de limpiar markers uno por uno).
  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 12,
    center: { lat: jsonData.paradas[0].lat, lng: jsonData.paradas[0].lng },
  });

  if (jsonData.polyline) {
    var path = decodePolyline(jsonData.polyline);
    if (path.length > 0) {
      new google.maps.Polyline({
        path: path,
        geodesic: true,
        strokeColor: "#E24F30",
        strokeOpacity: 1.0,
        strokeWeight: 4,
      }).setMap(map);
    }
  }

  var bounds = new google.maps.LatLngBounds();
  var infowindowActivo = null;

  // Mismo clustering real (agrupado por pixeles, se reagrupa con el zoom)
  // que el mapa real de Hoja de Ruta - ver nota completa en
  // dibujarPuntosAgrupados(), Mapas/js/hojaderuta.js.
  dibujarPuntosAgrupados(map, jsonData.paradas, bounds, CADDY_ORANGE, function (p, pos) {
    var marker = new google.maps.Marker({
      position: pos,
      label: pinLabel(p.posicion),
      icon: pinSymbol(CADDY_ORANGE),
      map: map,
    });

    // Antes el preview no mostraba nada al hacer click en un pin (a
    // diferencia del mapa real, que ya tenia InfoWindow) - mismo diseño
    // compartido (construirInfoWindowServicio, en hojaderuta.js).
    var infoWindow = new google.maps.InfoWindow({
      content: construirInfoWindowServicio({
        cliente: p.nombrecliente,
        direccion: p.Direccion,
        seguimiento: p.Seguimiento,
        telefono1: p.Celular,
        telefono2: p.Telefono,
        etiqueta: "Parada " + p.posicion + " · Hora estimada " + (p.horaEstimada || "").substring(0, 5),
      }),
    });
    marker.addListener("click", function () {
      if (infowindowActivo) infowindowActivo.close();
      infowindowActivo = infoWindow;
      infoWindow.open(map, marker);
    });

    return marker;
  });

  map.fitBounds(bounds);

  $("#route_km").html("<b>Distancia Total: " + jsonData.kmTotal + " km.</b>");
  $("#route_time").html("<b>Duración Total: " + jsonData.duracionTotalMin + " minutos</b>");
}

$("#optimizar_ok").click(function () {
  var Recorrido = $("#recorrido").html();

  $.ajax({
    data: { Orden_Automatic_Confirmar: 1, Recorrido: Recorrido },
    type: "POST",
    url: "Mapas/php/orden_automatico.php",
    dataType: "text", // ver nota de mas arriba en verRutaOptimizada()
    beforeSend: function () {
      $("#info-alert-modal-title").html("Guardando el nuevo orden...");
      $("#info-alert-modal").modal("show");
    },
    success: function (response) {
      $("#info-alert-modal").modal("hide");

      var jsonData;
      try {
        jsonData = JSON.parse(response);
      } catch (e) {
        toast("error", "Error", "Respuesta inválida del servidor.");
        return;
      }

      if (jsonData.resultado == 1) {
        toast("success", "Listo", "La ruta quedó ordenada.");
        $("#optimizar_ok").css("display", "none");
        $("#route_header").css("display", "none");
        $("#points").css("display", "none");
        $("#routes").css("display", "block");
        veo(Recorrido);
      } else {
        toast("error", "Error", jsonData.message || "No se pudo guardar el orden.");
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      $("#info-alert-modal").modal("hide");
      console.error("Error en Orden_Automatic_Confirmar:", textStatus, errorThrown);
      toast("error", "Error del servidor", "No se pudo guardar. Reintentá de nuevo.");
    },
  });
});
