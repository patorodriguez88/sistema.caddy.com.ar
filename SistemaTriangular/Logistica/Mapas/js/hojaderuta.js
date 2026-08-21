// TODO: migrate to AdvancedMarkerElement
function asignacion_recorrido() {
  $("#asignaciones").show();
}

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

  var etiquetaHtml = datos.etiqueta
    ? '<div style="display:inline-block;background:#' +
      CADDY_ORANGE +
      ';color:#fff;font-weight:600;font-size:12px;border-radius:12px;padding:2px 9px;margin-bottom:6px;">' +
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

  return (
    '<div style="min-width:220px;max-width:260px;font-family:-apple-system,Roboto,Arial,sans-serif;padding:2px 4px;">' +
    etiquetaHtml +
    '<div style="font-size:15px;font-weight:600;color:#202124;margin-bottom:2px;">' +
    (datos.cliente || "") +
    "</div>" +
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

      for (var i = 0; i < objeto_json.data.length; i++) {
        var a = Number(objeto_json.data.length);

        $("#marker").html("Total " + objeto_json.data.length);
        $("#marker_2").html("Errores " + Number(a - (markers.length + 1)));
        $("#marker_0").html("Entregas " + objeto_json.Total_entregas); //ENCONTRADOS EN TABLA
        $("#marker_1").html("Retiros " + objeto_json.Total_retiros);

        if (c != "") {
          if (c == "t") {
            if (objeto_json[0][i] == null) {
              var icono = null;
            } else {
              var icono = pinSymbol(objeto_json[0][i]);
            }
          } else {
            if (
              objeto_json.data[i].Retirado == 0 &&
              objeto_json.data[i].Entrega == 0
            ) {
              var valor_retirado = 0;
              var icono = pinSymbol("ffc107");
              // var Posicion=objeto_json.data[i].Posicion_retiro;
            } else {
              var valor_retirado = 1;
              // var Posicion=objeto_json.data[i].Posicion;
              var icono = pinSymbol(colorRecorrido);
              $("#marker_0").css("color", `#${colorRecorrido}`);
            }
          }
        } else {
          icono = pinSymbol(colorRecorrido);
        }

        var latlong = objeto_json.data[i].coordenadas.split(",");
        myLatLng = {
          lat: Number(latlong[0]),
          lng: Number(latlong[1]),
        };
        bounds.extend(myLatLng);

        var marker = new google.maps.Marker({
          position: myLatLng,
          map: map,
          label: pinLabel(objeto_json.data[i].Posicion),
          title: objeto_json.data[i].nombrecliente,
          icon: icono,
          pato: valor_retirado,
        });

        markers.push(marker);

        var tel1 = objeto_json.data[i].Celular;
        var tel2 = objeto_json.data[i].Telefono;

        markers[i].id = objeto_json.data[i].idHojaderuta;
        markers[i].Recorrido = objeto_json.data[i].Recorrido;

        markers[i].infoWindow = new google.maps.InfoWindow({
          content: construirInfoWindowServicio({
            cliente: objeto_json.data[i].nombrecliente,
            direccion: objeto_json.data[i].Direccion,
            seguimiento: objeto_json.data[i].Seguimiento,
            telefono1: tel1,
            telefono2: tel2,
            etiqueta: "Recorrido " + objeto_json.data[i].Recorrido,
          }),
        });

        google.maps.event.addListener(markers[i], "click", function () {
          if ($("#alert_ordenar").css("display") == "block") {
            // console.log('marker',this);
            var send_id = this.id;
            var valorpato = this;

            console.log("valor", valorpato);

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
                  // console.log('marker',markers[_i]);
                  valorpato.icon = pinSymbol("#CCCCCC");
                  valorpato.setMap(null);
                  valorpato.setMap(map);
                  valorpato.setAnimation(google.maps.Animation.DROP);
                  valorpato.label = {
                    color: "gray",
                    fontWeight: "bold",
                    text: jsonData.newPosicion,
                  };
                }
              },
            });
          } else {
            if (infowindowActivo) {
              infowindowActivo.close();
            }

            infowindowActivo = this.infoWindow;
            infowindowActivo.open(map, this);
          }
        });
      }

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
  if ($("#alert_ordenar").css("display") == "block") {
    $("#alert_ordenar").hide();
    $("#map").css("min-height", "400px");
    $("#ordenar_recorrido").html("Ordenar");
  } else {
    $("#alert_ordenar").show();
    $("#map").css("min-height", "450px");
    $("#ordenar_recorrido").html("Cerrar Orden");
  }
  var Recorrido = $("#recorrido").html();

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

  $("#warning-alert-modal-ok").click(function () {
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
        var jsonData = JSON.parse(response);

        if (jsonData.resultado == "1") {
          // veo() ya refresca Roadmap_end para este Recorrido antes de leer
          // el mapa (ver funciones_hdr.js) - llamar renderizar_datos() aca
          // aparte era trabajo duplicado.
          veo(Recorrido);

          $("#info-alert-modal").modal("hide");
          $("#next_number").html(1);

          var datatable = $("#seguimiento").DataTable();
          datatable.ajax.reload();
        }
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
