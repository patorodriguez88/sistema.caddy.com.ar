// Repartidores en vivo - lee la última posición conocida de cada repartidor
// (tabla UbicacionRepartidor, alimentada por la PWA de reparto) y la refresca
// sola cada 30s, en línea con la frecuencia con la que la app manda su
// posición (ver SistemaReparto/Proceso/js/geo_tracker.js).
var mapaRepartidores = null;
var marcadoresRepartidores = {};

// Si hace más de esto que un repartidor no manda nada, se marca como
// "sin señal" en vez de asumir que sigue ahí parado.
var MINUTOS_SIN_SENAL = 10;

function initMap() {
  mapaRepartidores = new google.maps.Map(document.getElementById("map"), {
    center: { lat: -31.4201, lng: -64.1888 }, // Córdoba Capital
    zoom: 12,
  });

  cargarRepartidores();
  setInterval(cargarRepartidores, 30000);
}

function minutosDesde(timestampStr) {
  var ts = new Date(timestampStr.replace(" ", "T"));
  return Math.floor((Date.now() - ts.getTime()) / 60000);
}

function cargarRepartidores() {
  $.ajax({
    url: "php/datos_repartidores.php",
    type: "GET",
    dataType: "json",
    success: function (resp) {
      if (!resp || resp.success !== 1) return;
      pintarMapa(resp.repartidores);
      pintarLista(resp.repartidores);
    },
  });
}

var MOTIVOS_PAUSA_TEXTO = {
  mecanico: "Mecánico / Rotura",
  descanso: "Descanso",
  transito: "Tránsito / Accidente",
  otro: "Otro",
};

function pintarMapa(repartidores) {
  var vistos = {};

  repartidores.forEach(function (r) {
    vistos[r.usuario] = true;
    var sinSenal = minutosDesde(r.timestamp) > MINUTOS_SIN_SENAL;
    var pausado = !!r.pausaMotivo;
    var pos = { lat: r.lat, lng: r.lng };

    if (marcadoresRepartidores[r.usuario]) {
      marcadoresRepartidores[r.usuario].setPosition(pos);
      marcadoresRepartidores[r.usuario].setIcon(iconoRepartidor(sinSenal, pausado));
    } else {
      marcadoresRepartidores[r.usuario] = new google.maps.Marker({
        position: pos,
        map: mapaRepartidores,
        title: r.nombre,
        icon: iconoRepartidor(sinSenal, pausado),
      });
    }

    var motivoTxt = pausado ? MOTIVOS_PAUSA_TEXTO[r.pausaMotivo] || r.pausaMotivo : "";
    marcadoresRepartidores[r.usuario].setTitle(
      r.nombre +
        (r.recorrido ? " - Recorrido " + r.recorrido : "") +
        (pausado ? " - ⏸ PAUSADO: " + motivoTxt + (r.pausaDetalle ? " (" + r.pausaDetalle + ")" : "") : "")
    );
  });

  // Saca del mapa a los que ya no vienen en la respuesta (no debería pasar
  // casi nunca, ya que la tabla guarda 1 fila por repartidor, pero por las dudas).
  Object.keys(marcadoresRepartidores).forEach(function (usuario) {
    if (!vistos[usuario]) {
      marcadoresRepartidores[usuario].setMap(null);
      delete marcadoresRepartidores[usuario];
    }
  });
}

function iconoRepartidor(sinSenal, pausado) {
  var color = "#0acf97"; // verde: en movimiento, ok
  if (pausado) color = "#fa5c7c"; // rojo: parado por algún inconveniente
  else if (sinSenal) color = "#98a6ad"; // gris: sin señal reciente

  return {
    path: google.maps.SymbolPath.CIRCLE,
    scale: pausado ? 10 : 8,
    fillColor: color,
    fillOpacity: 1,
    strokeColor: "#ffffff",
    strokeWeight: pausado ? 3 : 2,
  };
}

function pintarLista(repartidores) {
  var $lista = $("#lista_repartidores");

  if (repartidores.length === 0) {
    $lista.html('<div class="text-center text-muted py-4">Ningún repartidor con ubicación reciente.</div>');
    return;
  }

  var html = "";
  repartidores.forEach(function (r) {
    var mins = minutosDesde(r.timestamp);
    var sinSenal = mins > MINUTOS_SIN_SENAL;
    var haceCuanto = mins < 1 ? "recién" : mins + " min";
    var pausado = !!r.pausaMotivo;
    var motivoTxt = pausado ? MOTIVOS_PAUSA_TEXTO[r.pausaMotivo] || r.pausaMotivo : "";

    html +=
      '<div class="d-flex align-items-center justify-content-between py-2 border-bottom">' +
      '<div>' +
      '<div class="fw-semibold">' + r.nombre + "</div>" +
      '<div class="text-muted" style="font-size:12px;">' +
      (r.recorrido ? "Recorrido " + r.recorrido : "Sin recorrido asignado") +
      "</div>" +
      (pausado
        ? '<div class="text-danger fw-semibold" style="font-size:12px;">⏸ ' +
          motivoTxt +
          (r.pausaDetalle ? " - " + r.pausaDetalle : "") +
          "</div>"
        : "") +
      "</div>" +
      '<span class="badge ' + (pausado ? "bg-danger" : sinSenal ? "bg-secondary" : "bg-success") + '">' +
      (pausado ? "Pausado" : sinSenal ? "Sin señal (" + haceCuanto + ")" : "Hace " + haceCuanto) +
      "</span>" +
      "</div>";
  });

  $lista.html(html);
}
