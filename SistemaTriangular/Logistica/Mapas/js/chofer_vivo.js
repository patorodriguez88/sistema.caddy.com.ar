// Marcador del repartidor EN VIVO para los mapas de HojaDeRuta2.
//
// Fuente: Logistica/Mapas/php/ubicacion_orden.php -> UbicacionRepartidor (la
// misma tabla que "Repartidores en Vivo", la manda la PWA de reparto ~cada 30s).
//
// Uso:
//   choferVivoMontar(map, { recorrido: "1464" });  // mapa "Servicios Pendientes Recorrido N" (initMap)
//   choferVivoMontar(map, { id: 17290 });           // mapa de la orden abierta (initMap_order)
//   choferVivoLimpiar();                            // al cerrar / cambiar de recorrido
//
// Solo se usa si la pagina prende window.HDR2_LIVE_CHOFER.

var _choferVivoInterval = null;
var _choferVivoMarker = null;
var _choferVivoInfo = null;

function choferVivoLimpiar() {
  if (_choferVivoInterval) {
    clearInterval(_choferVivoInterval);
    _choferVivoInterval = null;
  }
  if (_choferVivoMarker) {
    _choferVivoMarker.setMap(null);
    _choferVivoMarker = null;
  }
  if (_choferVivoInfo) {
    _choferVivoInfo.close();
    _choferVivoInfo = null;
  }
}

function _choferVivoHace(mins) {
  if (mins === null || mins === undefined) return "sin senal";
  if (mins < 1) return "recien";
  if (mins < 60) return "hace " + mins + " min";
  var h = Math.floor(mins / 60);
  return "hace " + h + " h " + (mins % 60) + " min";
}

// minutos desde la ultima senal, en el cliente (igual que repartidores_live.js)
// para no depender del timezone del server.
function _choferVivoMinutos(r) {
  if (r && r.timestamp) {
    var t = new Date(String(r.timestamp).replace(" ", "T")).getTime();
    if (!isNaN(t)) return Math.floor((Date.now() - t) / 60000);
  }
  return r && r.minutos !== null && r.minutos !== undefined ? r.minutos : null;
}

function _choferVivoTick(map, params) {
  $.ajax({
    url: "Mapas/php/ubicacion_orden.php",
    data: params,
    type: "post",
    dataType: "json",
    success: function (r) {
      if (!r || r.success !== 1 || r.tienePos !== 1) return;
      if (!map) return;

      var pos = { lat: Number(r.lat), lng: Number(r.lng) };
      var mins = _choferVivoMinutos(r);
      var viejo = mins !== null && mins >= 10;
      var icon = {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 10,
        fillColor: viejo ? "#98a6ad" : "#E24F30",
        fillOpacity: 1,
        strokeColor: "#ffffff",
        strokeWeight: 3,
      };
      var titulo =
        (r.nombre || r.usuario || "Repartidor") + " - " + _choferVivoHace(mins);

      if (!_choferVivoMarker) {
        _choferVivoMarker = new google.maps.Marker({
          position: pos,
          map: map,
          icon: icon,
          zIndex: 99999,
          title: titulo,
        });
        _choferVivoInfo = new google.maps.InfoWindow();
        _choferVivoMarker.addListener("click", function () {
          _choferVivoInfo.setContent(
            '<div style="font-size:12px;line-height:1.4">' +
              "<b>" +
              (r.nombre || r.usuario || "Repartidor") +
              "</b><br>" +
              "Recorrido " +
              (r.recorrido || "-") +
              " &middot; Orden " +
              (r.orden || "-") +
              "<br>" +
              "Estado: " +
              (r.estado || "-") +
              "<br>" +
              "Ultima senal: " +
              _choferVivoHace(mins) +
              (r.precision ? " (&plusmn;" + r.precision + " m)" : "") +
              "</div>",
          );
          _choferVivoInfo.open(map, _choferVivoMarker);
        });
      } else {
        _choferVivoMarker.setMap(map);
        _choferVivoMarker.setPosition(pos);
        _choferVivoMarker.setIcon(icon);
        _choferVivoMarker.setTitle(titulo);
      }
    },
  });
}

function choferVivoMontar(map, params) {
  choferVivoLimpiar();
  if (!window.HDR2_LIVE_CHOFER || !map || !params) return;
  if (!params.id && !params.recorrido) return;
  _choferVivoTick(map, params);
  _choferVivoInterval = setInterval(function () {
    _choferVivoTick(map, params);
  }, 30000);
}
