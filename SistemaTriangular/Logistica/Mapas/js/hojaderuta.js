// Violeta de marca Caddy (mismo que --caddy-purple en inicio.php) para el
// icono de los clusters de MarkerClusterer - por defecto la libreria usa un
// naranja/rojo generico que se confundia con el naranja de los pines
// (CADDY_ORANGE) y la traza de la ruta.
var CADDY_PURPLE = "4D1A50";

var clustererRenderer = {
  render: function (_ref) {
    var count = _ref.count;
    var position = _ref.position;
    return new google.maps.Marker({
      position: position,
      label: { text: String(count), color: "#fff", fontWeight: "bold" },
      icon: {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 18,
        fillColor: "#" + CADDY_PURPLE,
        fillOpacity: 1,
        strokeColor: "#FFFFFF",
        strokeWeight: 2,
      },
      zIndex: 1000 + count,
    });
  },
};

// TODO: migrate to AdvancedMarkerElement
function abrir_todos(i) {
  console.log("todos", i);

  $.ajax({
    data: { Abrir_todos: 1, Recorrido: i },
    url: "Mapas/php/abrir_todos.php",
    type: "post",
    success: function (response) {},
  });
}

function verentabla(a) {
  var table = $("#seguimiento").DataTable();
  table.search(a).draw();
}
const pato = 22;

// Color de marca Caddy, usado como default de los pines/traza cuando el
// Recorrido no tiene un color propio configurado (Recorridos.Color vacio, o
// el negro que trae por defecto el <input type=color> del circulo de color
// de la card cuando nunca se toco) - antes esos casos quedaban con el pin
// negro de Google, que no comunica nada y no matchea el resto de la UI.
var CADDY_ORANGE = "E24F30";
function normalizarColorRecorrido(c) {
  var limpio = (c || "").replace("#", "").toLowerCase();
  if (limpio === "" || limpio === "000000" || limpio === "000") {
    return CADDY_ORANGE;
  }
  return limpio;
}

// Cuando dos o mas paradas comparten coordenadas exactas (o casi), sus
// markers quedan tapados unos con otros. Un offset "siempre visible" (lo
// que se uso primero) solo se notaba haciendo zoom a nivel calle - ahora se
// agrupan en un solo pin con contador, y al hacer click se abren en abanico
// (mismo patron que usa Google Maps con puntos muy cercanos entre si; no
// hay una funcion nativa de la API para esto, se arma a mano). Compartido
// entre el mapa real de Hoja de Ruta (initMap) y el preview de "Ver Ruta"
// (Mapas/js/datos.js).
var COINCIDENT_PRECISION = 5; // ~1.1 m de precision para considerar "mismo punto"
var ABANICO_RADIO_METROS = 14; // separacion entre pines al expandir un grupo

function posicionEnAbanico(lat, lng, idx, total, radioM) {
  var angle = ((idx * 360) / total) * (Math.PI / 180);
  var metersPerDegLat = 111320;
  var metersPerDegLng = 111320 * Math.cos((lat * Math.PI) / 180);
  return {
    lat: lat + (radioM * Math.sin(angle)) / metersPerDegLat,
    lng: lng + (radioM * Math.cos(angle)) / metersPerDegLng,
  };
}

// Pin numerado tipo Google Maps (mismo path que ya usaba hojaderuta.js,
// hecho reusable - antes estaba declarado adentro del for de initMap()).
function pinSymbol(color) {
  return {
    path: "M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z",
    fillColor: "#" + (color || CADDY_ORANGE),
    fillOpacity: 1,
    strokeColor: "#FFFFFF",
    strokeWeight: 1,
    scale: 1,
    labelOrigin: new google.maps.Point(0, -29),
  };
}

function pinLabel(Posicion) {
  return {
    color: "white",
    fontWeight: "bold",
    text: String(Posicion),
  };
}

// Agrupa "puntos" ({lat, lng, ...cualquier dato propio}) que comparten
// (casi) la misma coordenada. Los grupos de 1 se dibujan directo
// (crearMarkerCallback(punto, posReal)); los de 2+ arrancan colapsados en
// un pin con contador (color colorCluster) que al hacer click se reemplaza
// por los markers individuales en abanico. bounds se extiende con el punto
// real de cada grupo (uno solo, no cada punto individual).
// Se probo primero con un agrupado casero (grupos por coordenada +
// expansion en abanico al click), pero eso solo resolvia el caso de
// paradas con coordenadas practicamente identicas - en un recorrido que
// abarca varias ciudades, muchas paradas de direcciones DISTINTAS quedan
// amontonadas en los mismos pocos pixeles al ver el mapa alejado, y ese
// agrupado casero no se reacomoda con el zoom (ademas de que dos clusters
// cercanos pero no idénticos podian superponerse visualmente entre si,
// confundiendo cual se estaba clickeando). Ahora se usa clustering real
// (MarkerClusterer, libreria oficial de Google cargada como script global)
// que agrupa por pixeles en pantalla y se reagrupa solo al hacer zoom -
// click en un cluster hace zoom-in, igual que en Google Maps de verdad.
//
// El offset chico (paso 1, siempre activo) sigue haciendo falta aparte para
// paradas con la MISMA coordenada exacta (ej. mismo edificio): por mas zoom
// que se haga, dos pines en el pixel exacto nunca se separan solos.
var activeClusterers = new WeakMap(); // mapa -> MarkerClusterer activo, para poder limpiarlo en el proximo render

function dibujarPuntosAgrupados(mapa, puntos, bounds, colorCluster, crearMarkerCallback) {
  var registroExactos = new Map();
  var todosLosMarkers = [];

  puntos.forEach(function (p) {
    var key = p.lat.toFixed(COINCIDENT_PRECISION) + "," + p.lng.toFixed(COINCIDENT_PRECISION);
    var count = registroExactos.get(key) || 0;
    registroExactos.set(key, count + 1);

    var pos =
      count === 0
        ? { lat: p.lat, lng: p.lng }
        : posicionEnAbanico(
            p.lat,
            p.lng,
            (count - 1) % 8,
            8,
            ABANICO_RADIO_METROS * (Math.floor((count - 1) / 8) + 1),
          );

    bounds.extend(pos);
    var marker = crearMarkerCallback(p, pos);
    if (marker) todosLosMarkers.push(marker);
  });

  if (activeClusterers.has(mapa)) {
    activeClusterers.get(mapa).clearMarkers();
  }
  if (window.markerClusterer && todosLosMarkers.length > 0) {
    var clusterer = new markerClusterer.MarkerClusterer({
      map: mapa,
      markers: todosLosMarkers,
      renderer: clustererRenderer,
    });
    activeClusterers.set(mapa, clusterer);
  }
}

// InfoWindow compartido entre el mapa real (hojaderuta.js) y el preview de
// "Ver Ruta" (Mapas/js/datos.js) - antes el contenido era HTML crudo sin
// estilo (un <h4>/<p> sueltos, hasta un <td> fuera de cualquier tabla) y el
// preview no mostraba nada al hacer click en un pin. Diseño tipo tarjeta,
// estilo Google Maps.
function construirInfoWindowServicio(datos) {
  var cel = "";
  if (datos.telefono1 && datos.telefono2 && datos.telefono1 !== datos.telefono2) {
    cel = datos.telefono1 + " | " + datos.telefono2;
  } else {
    cel = datos.telefono1 || datos.telefono2 || "";
  }

  var whatsappHtml = "";
  if (cel) {
    var mensaje = encodeURIComponent(
      "Hola " +
        (datos.cliente || "") +
        "! nos comunicamos de Caddy Logística, tenemos un envío para entregarte, pero necesitamos corroborar tu dirección, ya que nuestro cliente nos indicó que la misma era " +
        (datos.direccion || "") +
        "... pero no logramos ubicarnos. ¿Nos podrás ayudar?",
    );
    whatsappHtml =
      '<a href="https://api.whatsapp.com/send?phone=' +
      cel +
      "&text=" +
      mensaje +
      '" target="_blank" style="display:inline-flex;align-items:center;gap:6px;background:#25D366;color:#fff;text-decoration:none;font-size:13px;font-weight:600;padding:6px 12px;border-radius:16px;margin-top:2px;">' +
      '<i class="mdi mdi-whatsapp"></i> ' +
      cel +
      "</a>";
  }

  // El titulo (arriba de todo) es siempre el nombre del cliente - la
  // etiqueta (si hay) es info secundaria y va DESPUES del nombre, no antes,
  // para no dejar un hueco raro arriba tipo "espacio para titulo vacio".
  var etiquetaHtml = datos.etiqueta
    ? '<div style="display:inline-block;background:#' +
      CADDY_ORANGE +
      ';color:#fff;font-weight:600;font-size:11px;border-radius:10px;padding:2px 8px;margin-bottom:6px;">' +
      datos.etiqueta +
      "</div>"
    : "";

  var seguimientoHtml = datos.seguimiento
    ? '<div style="font-size:12px;color:#5f6368;margin-bottom:8px;"><b>Seguimiento:</b> ' + datos.seguimiento + "</div>"
    : "";

  var verEnTablaHtml = datos.seguimiento
    ? '<a style="cursor:pointer;font-size:12px;color:#1a73e8;text-decoration:none;" onclick="verentabla(\'' +
      datos.seguimiento +
      "')\">Ver en tabla</a>"
    : "";

  // OJO: un margin-top negativo grande ac aca corta el nombre (empuja el
  // texto por arriba del borde de la burbuja de InfoWindow, que recorta lo
  // que se sale) - se dejo un valor chico a proposito, no agrandar sin
  // probar que no vuelva a cortar el titulo.
  return (
    '<div style="min-width:220px;max-width:260px;font-family:-apple-system,Roboto,Arial,sans-serif;padding:0 4px;margin-top:-2px;">' +
    '<div style="font-size:16px;font-weight:700;color:#202124;margin-bottom:4px;line-height:1.3;">' +
    (datos.cliente || "") +
    "</div>" +
    etiquetaHtml +
    '<div style="font-size:13px;color:#5f6368;margin-bottom:8px;">' +
    (datos.direccion || "") +
    "</div>" +
    seguimientoHtml +
    whatsappHtml +
    (verEnTablaHtml ? '<div style="margin-top:8px;">' + verEnTablaHtml + "</div>" : "") +
    "</div>"
  );
}

// Decodifica un polyline encodeado de Google (mismo algoritmo que usa
// Planificador/js/planificador.js) para poder dibujar la traza real de la
// ruta en el mapa, no solo los puntos sueltos.
function decodePolyline(encoded) {
  let points = [],
    index = 0,
    lat = 0,
    lng = 0;
  while (index < encoded.length) {
    let b,
      shift = 0,
      result = 0;
    do {
      b = encoded.charCodeAt(index++) - 63;
      result |= (b & 0x1f) << shift;
      shift += 5;
    } while (b >= 0x20);
    lat += result & 1 ? ~(result >> 1) : result >> 1;
    shift = result = 0;
    do {
      b = encoded.charCodeAt(index++) - 63;
      result |= (b & 0x1f) << shift;
      shift += 5;
    } while (b >= 0x20);
    lng += result & 1 ? ~(result >> 1) : result >> 1;
    points.push({ lat: lat / 1e5, lng: lng / 1e5 });
  }
  return points;
}

// Traza dibujada actualmente en el mapa de Hoja de Ruta (se limpia y se
// vuelve a dibujar cada vez que se abre un recorrido).
var hdrRoutePolyline = null;

// Traza "en progreso" de Ordenar Manual: se va armando tramo por tramo (por
// calles reales, via Routes API - no linea recta) a medida que el operador
// clickea cada parada. Se resetea al entrar a modo Ordenar Manual, arranca
// desde el origen fijo de la empresa (mismo punto que usa
// orden_automatico.php/Planificador).
var ORIGEN_EMPRESA = { lat: -31.444994776141503, lng: -64.1779408896999 };
var manualOrderPath = [];
var manualOrderPolyline = null;
var manualOrderUltimoPunto = null;

function redibujarManualOrderPolyline() {
  if (manualOrderPolyline) {
    manualOrderPolyline.setMap(null);
  }
  manualOrderPolyline = new google.maps.Polyline({
    path: manualOrderPath,
    geodesic: true,
    strokeColor: "#" + CADDY_PURPLE,
    strokeOpacity: 0.85,
    strokeWeight: 4,
  });
  manualOrderPolyline.setMap(map);
}

// Pide a Google el tramo real (siguiendo calles) desde el ultimo punto
// agregado hasta "destino", y lo concatena a manualOrderPath - en vez de
// recalcular TODA la ruta con cada click nuevo (mas lento y mas caro), solo
// se pide el tramo nuevo cada vez.
function agregarTramoManual(destino) {
  var origen = manualOrderUltimoPunto || ORIGEN_EMPRESA;

  $.ajax({
    data: {
      SegmentoRuta: 1,
      origenLat: origen.lat,
      origenLng: origen.lng,
      destinoLat: destino.lat,
      destinoLng: destino.lng,
    },
    url: "Mapas/php/cambiar_posicion.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.resultado == 1 && jsonData.polyline) {
        manualOrderPath = manualOrderPath.concat(decodePolyline(jsonData.polyline));
      } else {
        // Si Google no pudo calcular este tramo puntual, se dibuja igual
        // con una linea recta para ese segmento en vez de dejar la traza
        // cortada - no bloquea el resto del ordenamiento manual.
        console.error("No se pudo calcular el tramo real:", jsonData.message);
        manualOrderPath.push(destino);
      }
      redibujarManualOrderPolyline();
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.error("Error en SegmentoRuta:", textStatus, errorThrown);
      manualOrderPath.push(destino);
      redibujarManualOrderPolyline();
    },
  });

  manualOrderUltimoPunto = destino;
}

function initMap(c) {
  var divMapa = document.getElementById("map");
  var xhttp;
  var resultado = [];
  var markers = [];
  var co = [];

  var markerss = [];

  var infowindowActivo = false;
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (xhttp.readyState == 4 && xhttp.status == 200) {
      resultado = xhttp.responseText;
      var objeto_json = JSON.parse(resultado);

      $("#header-title2").html(c);

      var colorRecorrido = normalizarColorRecorrido(c);
      var bounds = new google.maps.LatLngBounds();

      $("#marker").html("Total " + objeto_json.data.length);
      $("#marker_2").html("Errores " + 0); // ver nota en agrupado de abajo
      $("#marker_0").html("Entregas " + objeto_json.Total_entregas); //ENCONTRADOS EN TABLA
      $("#marker_1").html("Retiros " + objeto_json.Total_retiros);

      // Crea el marker real de UN punto puntual en la posicion indicada -
      // toda la logica de icono/color/InfoWindow/click (antes inline en el
      // for principal), parametrizada para poder crearse de una (puntos
      // sueltos) o diferida (al expandir un grupo de puntos coincidentes,
      // ver mas abajo).
      function crearMarkerPunto(i, pos) {
        if (c != "") {
          if (c == "t") {
            var icono = objeto_json[0][i] == null ? null : pinSymbol(objeto_json[0][i]);
          } else {
            if (objeto_json.data[i].Retirado == 0 && objeto_json.data[i].Entrega == 0) {
              var valor_retirado = 0;
              var icono = pinSymbol("ffc107");
            } else {
              var valor_retirado = 1;
              var icono = pinSymbol(colorRecorrido);
              $("#marker_0").css("color", `#${colorRecorrido}`);
            }
          }
        } else {
          var icono = pinSymbol(colorRecorrido);
        }

        var marker = new google.maps.Marker({
          position: pos,
          map: map,
          label: pinLabel(objeto_json.data[i].Posicion),
          title: objeto_json.data[i].nombrecliente,
          icon: icono,
          pato: valor_retirado,
        });

        markers.push(marker);

        var tel1 = objeto_json.data[i].Celular;
        var tel2 = objeto_json.data[i].Telefono;

        marker.id = objeto_json.data[i].idHojaderuta;
        marker.Recorrido = objeto_json.data[i].Recorrido;

        marker.infoWindow = new google.maps.InfoWindow({
          content: construirInfoWindowServicio({
            cliente: objeto_json.data[i].nombrecliente,
            direccion: objeto_json.data[i].Direccion,
            seguimiento: objeto_json.data[i].Seguimiento,
            telefono1: tel1,
            telefono2: tel2,
            // Sin etiqueta "Recorrido X" aca - no aporta nada viendo un solo
            // recorrido a la vez (si en algun momento se quiere distinguir
            // en la vista "Ver Todos", ahi si tendria sentido agregarla).
          }),
        });

        google.maps.event.addListener(marker, "click", function () {
          if ($("#alert_ordenar").css("display") == "block") {
            var send_id = this.id;
            var valorpato = this;

            let Posicion = $("#next_number").html();
            $.ajax({
              data: {
                NewOrder: 1,
                Recorrido: this.Recorrido,
                idhdr: send_id,
                valor_retirado: this.pato,
                Posicion: Posicion,
              },
              url: "Mapas/php/cambiar_posicion.php",
              type: "post",
              success: function (response) {
                var jsonData = JSON.parse(response);

                if (jsonData.resultado == "1") {
                  $("#next_number").html(jsonData.new_p);
                  // pinSymbol() ya le agrega el "#" al color - pasarle
                  // "#CCCCCC" (con #) daba "##CCCCCC", un color CSS
                  // invalido que el navegador termina pintando negro.
                  valorpato.icon = pinSymbol("CCCCCC");
                  valorpato.setMap(null);
                  valorpato.setMap(map);
                  valorpato.setAnimation(google.maps.Animation.DROP);
                  valorpato.label = {
                    color: "white",
                    fontWeight: "bold",
                    text: jsonData.newPosicion,
                  };

                  // Va armando la ruta en pantalla a medida que se clickea
                  // cada parada, en el orden en que se van eligiendo - pide
                  // el tramo real (siguiendo calles) desde el ultimo punto
                  // hasta este, no una linea recta.
                  var destino = valorpato.getPosition();
                  agregarTramoManual({ lat: destino.lat(), lng: destino.lng() });
                }
              },
            });
          } else {
            if (infowindowActivo) {
              infowindowActivo.close();
            }

            infowindowActivo = marker.infoWindow;
            infowindowActivo.open(map, marker);
          }
        });

        return marker;
      }

      // "Errores" del bloque de arriba se simplifica a 0 fijo: con el flujo
      // normal (todos los puntos terminan con marker, agrupados o no)
      // siempre daba 0 de todas formas.
      var puntosParaAgrupar = [];
      for (var gi = 0; gi < objeto_json.data.length; gi++) {
        var ll = objeto_json.data[gi].coordenadas.split(",");
        puntosParaAgrupar.push({ i: gi, lat: Number(ll[0]), lng: Number(ll[1]) });
      }

      dibujarPuntosAgrupados(map, puntosParaAgrupar, bounds, colorRecorrido, function (punto, pos) {
        return crearMarkerPunto(punto.i, pos);
      });

      // Traza real de la ruta (si el recorrido tiene un Polyline guardado -
      // desde el Planificador, o desde "Ordenar segun Reparto") - antes el
      // mapa solo mostraba los puntos sueltos, sin la traza real entre ellos.
      if (hdrRoutePolyline) {
        hdrRoutePolyline.setMap(null);
        hdrRoutePolyline = null;
      }
      if (objeto_json.Polyline) {
        var path = decodePolyline(objeto_json.Polyline);
        if (path.length > 0) {
          hdrRoutePolyline = new google.maps.Polyline({
            path: path,
            geodesic: true,
            strokeColor: "#E24F30",
            strokeOpacity: 1.0,
            strokeWeight: 4,
          });
          hdrRoutePolyline.setMap(map);
        }
      }

      // El mapa arrancaba siempre centrado en el mismo punto fijo con zoom
      // 10, sin importar donde estuvieran las paradas reales - ahora encuadra
      // automaticamente todos los waypoints del recorrido (igual que ya hace
      // el preview de "Ver Ruta" y el Planificador).
      if (objeto_json.data.length > 0) {
        map.fitBounds(bounds);
      }
    }
  };
  var myLatLng = {
    lat: -31.4448988,
    lng: -64.177743,
  };
  var url = "Mapas/php/datos_hojaderuta.php";
  xhttp.open("POST", url, true);
  xhttp.send();

  map = new google.maps.Map(document.getElementById("map"), {
    center: new google.maps.LatLng(-31.4448988, -64.177743),
    zoom: 10,
  });
}

$("#ordenar_recorrido_automatic").click(function () {
  $("#full-width-modal_order").modal("show");
  var datatable = $("#flota").DataTable();
  datatable.destroy();

  var fechas1 =
    new Date().getUTCMonth() -
    1 +
    "/" +
    new Date().getUTCDate() +
    "/" +
    new Date().getUTCFullYear();
  var fechas2 =
    new Date().getUTCMonth() +
    1 +
    "/" +
    new Date().getUTCDate() +
    "/" +
    new Date().getUTCFullYear();
  var fechas_control = fechas1 + " - " + fechas2;

  init_datatable(fechas_control);
});

$("#ordenar_recorrido").click(function () {
  var Recorrido = $("#recorrido").html();

  if ($("#alert_ordenar").css("display") == "block") {
    // CERRAR el orden manual: calcula la hora estimada de llegada a cada
    // parada segun el orden que quedo armado (Haversine + velocidad
    // promedio, sin llamar a la Routes API - mismo criterio que ya usa
    // ordenarPorCercania() en orden_automatico.php para estimar mientras
    // ordena) y las guarda en HojaDeRuta.Hora. veo() al final refresca el
    // mapa con la traza real ya calculada (reemplaza la linea recta "en
    // progreso" de manualOrderPolyline).
    $("#alert_ordenar").hide();
    $("#map").css("min-height", "400px");
    $("#ordenar_recorrido").html("Ordenar");

    $.ajax({
      data: { CalcularHorariosManual: 1, Recorrido: Recorrido },
      url: "Mapas/php/cambiar_posicion.php",
      type: "post",
      beforeSend: function () {
        $("#info-alert-modal-title").html("Calculando horarios...");
        $("#info-alert-modal").modal("show");
      },
      success: function () {
        $("#info-alert-modal").modal("hide");
        veo(Recorrido);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        $("#info-alert-modal").modal("hide");
        console.error("Error en CalcularHorariosManual:", textStatus, errorThrown);
        toast("error", "Error", "No se pudieron calcular los horarios. Reintentá de nuevo.");
      },
    });
    return;
  }

  // ABRIR el orden manual: arranca la traza "en progreso" de cero.
  $("#alert_ordenar").show();
  $("#map").css("min-height", "450px");
  $("#ordenar_recorrido").html("Cerrar Orden");

  manualOrderPath = [];
  manualOrderUltimoPunto = null;
  if (manualOrderPolyline) {
    manualOrderPolyline.setMap(null);
    manualOrderPolyline = null;
  }

  $.ajax({
    data: { ViewOrder: 1, Recorrido: Recorrido },
    url: "Mapas/php/cambiar_posicion.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (jsonData.resultado == "1") {
        var new_p = Number(jsonData.newPosicion) + 1;

        $("#next_number").html(new_p);
      }
    },
  });
});

$("#restaurar_orden").click(function () {
  var Recorrido = $("#recorrido").html();
  $("#warning-alert-modal").modal("show");

  // .off() antes de .on(): cada click en "Restablecer Orden" volvia a
  // agregar OTRO listener a #warning-alert-modal-ok sin sacar el anterior -
  // si se abria este flujo mas de una vez, "Continuar" terminaba disparando
  // el AJAX 2, 3, 4 veces (una por cada listener acumulado).
  $("#warning-alert-modal-ok")
    .off("click")
    .on("click", function () {
      $.ajax({
        data: { RestartOrder: 1, Recorrido: Recorrido },
        url: "Mapas/php/cambiar_posicion.php",
        type: "post",
        beforeSend: function () {
          $("#info-alert-modal").modal("show");
          $("#info-alert-modal-title").html(
            "Restableciendo Todo el Orden al Recorrido " + Recorrido,
          );
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

          if (jsonData.resultado == "1") {
            // veo() ya refresca Roadmap_end para este Recorrido antes de leer
            // el mapa (ver funciones_hdr.js) - llamar renderizar_datos() aca
            // aparte era trabajo duplicado.
            veo(Recorrido);

            $("#next_number").html(1);

            var datatable = $("#seguimiento").DataTable();
            datatable.ajax.reload();
          } else {
            toast("error", "Error", "No se pudo restablecer el orden. Reintentá de nuevo.");
          }
        },
        // Sin esto, un fallo (network, 500, JSON invalido) dejaba el modal
        // "Restableciendo..." pegado para siempre, sin ningun aviso.
        error: function (jqXHR, textStatus, errorThrown) {
          $("#info-alert-modal").modal("hide");
          console.error("Error en RestartOrder:", textStatus, errorThrown);
          toast("error", "Error del servidor", "No se pudo restablecer el orden. Reintentá de nuevo.");
        },
      });
    });
  $("#warning-alert-modal-cancel").click(function () {});
});

$("#orden_automatico").click(function () {
  var Recorrido = $("#recorrido").html();
  $.ajax({
    data: { Orden_Automatic: 1, Recorrido: Recorrido },
    url: "Mapas/php/orden_automatico.php",
    type: "post",
    beforeSend: function () {
      $("#info-alert-modal").modal("show");

      $("#info-alert-modal-title").html(
        "Analizando Distancias del Recorrido " + Recorrido,
      );
    },

    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.resultado == "1") {
        veo(Recorrido);
        $("#info-alert-modal").modal("hide");
      }
    },
  });
});

$("#orden_anterior").click(function () {
  var Recorrido = $("#recorrido").html();
  $.ajax({
    data: { Orden_Anterior: 1, Recorrido: Recorrido },
    url: "Mapas/php/orden_anterior.php",
    type: "post",
    beforeSend: function () {
      $("#info-alert-modal").modal("show");

      $("#info-alert-modal-title").html(
        "Analizando Posiciones Anteriores del Recorrido " + Recorrido,
      );
    },

    success: function (response) {
      var jsonData = JSON.parse(response);
      console.log(jsonData);
      if (jsonData.success == "1") {
        $("#info-alert-modal").modal("hide");
        var datatable = $("#seguimiento").DataTable();
        datatable.ajax.reload();
        veo(Recorrido);
      }
    },
  });
});
