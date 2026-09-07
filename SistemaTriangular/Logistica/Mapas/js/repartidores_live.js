// Repartidores en vivo.
//
// Muestra una entrada por ORDEN de salida activa de hoy (Logistica.Estado=
// 'Cargada'). La posición sale de UbicacionRepartidor (última posición que
// manda la PWA de reparto) y se refresca sola cada 30 s, en línea con la
// frecuencia de envío de la app (ver SistemaReparto/Proceso/js/geo_tracker.js).
//
// Color del punto = color del recorrido (Recorridos.Color), el mismo que usa
// el mapa de Hoja de Ruta. Click en el punto => ficha con chofer, recorrido,
// orden, entregados y pendientes.
var mapaRepartidores = null;
var marcadoresRepartidores = {};
var infoWindowRepartidor = null;
var ultimaFirmaMarcadores = "";

// Si hace más de esto que un repartidor no manda nada, se marca "sin señal".
var MINUTOS_SIN_SENAL = 10;

// Color de marca Caddy, usado cuando el recorrido no tiene color propio
// (Recorridos.Color vacío o el negro por defecto del <input type=color>).
// Mismo criterio que Mapas/js/hojaderuta.js.
var CADDY_ORANGE = "E24F30";

function normalizarColorRecorrido(c) {
  var limpio = (c || "").replace("#", "").toLowerCase();
  if (limpio === "" || limpio === "000000" || limpio === "000") {
    return CADDY_ORANGE;
  }
  return limpio;
}

var MOTIVOS_PAUSA_TEXTO = {
  mecanico: "Mecánico / Rotura",
  descanso: "Descanso",
  transito: "Tránsito / Accidente",
  otro: "Otro",
};

function initMap() {
  mapaRepartidores = new google.maps.Map(document.getElementById("map"), {
    center: { lat: -31.4201, lng: -64.1888 }, // Córdoba Capital
    zoom: 12,
  });
  infoWindowRepartidor = new google.maps.InfoWindow();

  cargarRepartidores();
  setInterval(cargarRepartidores, 30000);
}

function minutosDesde(timestampStr) {
  if (!timestampStr) return null;
  var ts = new Date(timestampStr.replace(" ", "T"));
  return Math.floor((Date.now() - ts.getTime()) / 60000);
}

function textoHaceCuanto(mins) {
  if (mins === null) return "sin ubicación";
  if (mins < 1) return "recién";
  if (mins < 60) return "hace " + mins + " min";
  var h = Math.floor(mins / 60);
  return "hace " + h + " h " + (mins % 60) + " min";
}

function cargarRepartidores() {
  $.ajax({
    url: "php/datos_repartidores.php",
    type: "GET",
    dataType: "json",
    success: function (resp) {
      if (!resp || resp.success !== 1) return;
      pintarMapa(resp.repartidores || []);
      pintarLista(resp.repartidores || []);
    },
  });
}

function iconoRepartidor(color, sinSenal, pausado) {
  var hex = "#" + normalizarColorRecorrido(color);
  return {
    path: google.maps.SymbolPath.CIRCLE,
    scale: pausado ? 11 : 9,
    fillColor: sinSenal ? "#98a6ad" : hex,
    fillOpacity: sinSenal ? 0.5 : 1,
    strokeColor: pausado ? "#fa5c7c" : "#ffffff",
    strokeWeight: pausado ? 4 : 2,
  };
}

function fichaRepartidor(r) {
  var mins = minutosDesde(r.timestamp);
  var pausado = !!r.pausaMotivo;
  var pend = Math.max(0, (r.totalPaquetes || 0) - (r.entregados || 0));
  var motivoTxt = pausado
    ? MOTIVOS_PAUSA_TEXTO[r.pausaMotivo] || r.pausaMotivo
    : "";
  var swatch =
    '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#' +
    normalizarColorRecorrido(r.color) +
    ';margin-right:6px;vertical-align:middle;"></span>';

  return (
    '<div style="font-size:13px;line-height:1.5;min-width:210px;">' +
    '<div style="font-weight:700;font-size:14px;margin-bottom:2px;">' +
    swatch +
    (r.nombre || "Repartidor") +
    "</div>" +
    '<div style="color:#5f6368;">Recorrido ' +
    (r.recorrido || "-") +
    (r.recorridoNombre ? " &mdash; " + r.recorridoNombre : "") +
    "</div>" +
    '<div style="color:#5f6368;">Orden #' +
    (r.orden || "-") +
    "</div>" +
    '<div style="margin-top:4px;">' +
    '<span style="color:#0acf97;font-weight:700;">' +
    (r.entregados || 0) +
    " entregados</span> &middot; " +
    '<span style="color:#fa5c7c;font-weight:700;">' +
    pend +
    " pendientes</span>" +
    ' <span style="color:#98a6ad;">/ ' +
    (r.totalPaquetes || 0) +
    " total</span>" +
    "</div>" +
    (pausado
      ? '<div style="color:#fa5c7c;font-weight:700;margin-top:4px;">⏸ ' +
        motivoTxt +
        (r.pausaDetalle ? " &mdash; " + r.pausaDetalle : "") +
        "</div>"
      : "") +
    '<div style="color:#98a6ad;margin-top:4px;">Última señal: ' +
    textoHaceCuanto(mins) +
    "</div>" +
    "</div>"
  );
}

function pintarMapa(repartidores) {
  var vistos = {};
  var bounds = new google.maps.LatLngBounds();
  var conPos = 0;
  var firma = [];

  repartidores.forEach(function (r) {
    if (r.lat == null || r.lng == null) return; // sin posición: solo panel
    vistos[r.usuario] = true;
    conPos++;
    firma.push(r.usuario);

    var sinSenal =
      r.timestamp == null || minutosDesde(r.timestamp) > MINUTOS_SIN_SENAL;
    var pausado = !!r.pausaMotivo;
    var pos = { lat: r.lat, lng: r.lng };
    bounds.extend(pos);

    var icono = iconoRepartidor(r.color, sinSenal, pausado);

    if (marcadoresRepartidores[r.usuario]) {
      marcadoresRepartidores[r.usuario].setPosition(pos);
      marcadoresRepartidores[r.usuario].setIcon(icono);
    } else {
      marcadoresRepartidores[r.usuario] = new google.maps.Marker({
        position: pos,
        map: mapaRepartidores,
        icon: icono,
      });
    }
    var marker = marcadoresRepartidores[r.usuario];
    marker.setTitle(
      (r.nombre || "") +
        (r.recorrido ? " - Recorrido " + r.recorrido : "") +
        " - " +
        (r.entregados || 0) +
        "/" +
        (r.totalPaquetes || 0),
    );
    marker.__datos = r;
    google.maps.event.clearListeners(marker, "click");
    marker.addListener("click", function () {
      infoWindowRepartidor.setContent(fichaRepartidor(marker.__datos));
      infoWindowRepartidor.open(mapaRepartidores, marker);
    });
  });

  // Saca del mapa a los que ya no vienen en la respuesta.
  Object.keys(marcadoresRepartidores).forEach(function (usuario) {
    if (!vistos[usuario]) {
      marcadoresRepartidores[usuario].setMap(null);
      delete marcadoresRepartidores[usuario];
    }
  });

  // Encuadra el mapa solo cuando cambia el conjunto de repartidores en pantalla,
  // para no pelear con el zoom/pan manual del operador en cada refresco.
  var firmaActual = firma.sort().join("|");
  if (conPos > 0 && firmaActual !== ultimaFirmaMarcadores) {
    ultimaFirmaMarcadores = firmaActual;
    mapaRepartidores.fitBounds(bounds);
    if (conPos === 1) mapaRepartidores.setZoom(14);
  }
}

function itemLista(r) {
  var mins = minutosDesde(r.timestamp);
  var sinSenal = mins === null || mins > MINUTOS_SIN_SENAL;
  var pausado = !!r.pausaMotivo;
  var motivoTxt = pausado
    ? MOTIVOS_PAUSA_TEXTO[r.pausaMotivo] || r.pausaMotivo
    : "";
  var pend = Math.max(0, (r.totalPaquetes || 0) - (r.entregados || 0));
  var colorHex = "#" + normalizarColorRecorrido(r.color);

  var badgeClase = "bg-success";
  var badgeTxt = textoHaceCuanto(mins);
  if (r.timestamp == null) {
    badgeClase = "bg-secondary";
    badgeTxt = "Sin ubicación";
  } else if (pausado) {
    badgeClase = "bg-danger";
    badgeTxt = "Pausado";
  } else if (sinSenal) {
    badgeClase = "bg-secondary";
    badgeTxt = "Sin señal (" + textoHaceCuanto(mins).replace("hace ", "") + ")";
  }

  return (
    '<div class="d-flex align-items-center justify-content-between py-2 border-bottom" ' +
    'style="border-left:4px solid ' +
    colorHex +
    ';padding-left:8px;">' +
    "<div>" +
    '<div class="fw-semibold">' +
    (r.nombre || "Repartidor") +
    "</div>" +
    '<div class="text-muted" style="font-size:12px;">Recorrido ' +
    (r.recorrido || "-") +
    (r.recorridoNombre ? " &middot; " + r.recorridoNombre : "") +
    "</div>" +
    '<div class="text-muted" style="font-size:12px;">Orden #' +
    (r.orden || "-") +
    "</div>" +
    '<div style="font-size:12px;">' +
    '<span class="text-success fw-semibold">' +
    (r.entregados || 0) +
    " entregados</span>" +
    (pend > 0
      ? ' &middot; <span class="text-danger fw-semibold">' +
        pend +
        " pendientes</span>"
      : "") +
    "</div>" +
    (pausado
      ? '<div class="text-danger fw-semibold" style="font-size:12px;">⏸ ' +
        motivoTxt +
        (r.pausaDetalle ? " - " + r.pausaDetalle : "") +
        "</div>"
      : "") +
    "</div>" +
    '<span class="badge ' +
    badgeClase +
    '">' +
    badgeTxt +
    "</span>" +
    "</div>"
  );
}

function pintarLista(repartidores) {
  var $lista = $("#lista_repartidores");

  if (repartidores.length === 0) {
    $lista.html(
      '<div class="text-center text-muted py-4">Ninguna orden cargada hoy.</div>',
    );
    return;
  }

  var html = "";
  repartidores.forEach(function (r) {
    html += itemLista(r);
  });
  $lista.html(html);
}
