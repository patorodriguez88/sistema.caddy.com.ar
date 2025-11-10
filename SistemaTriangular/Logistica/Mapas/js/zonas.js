$(document).ready(function () {
  $.ajax({
    data: { Limpiar: 1 },
    type: "POST",
    url: "Mapas/php/zonas.php",
    success: function (response) {},
  });

  $.ajax({
    data: { BuscarRecorridos: 1 },
    type: "POST",
    url: "Proceso/php/pendientes.php",
    success: function (response) {
      $(".selector-recorrido1 select").html(response).fadeIn();
    },
  });
  // =========================
  // Cargar acordeón de zonas
  // =========================
  function cargarZonasAccordion() {
    $.ajax({
      url: "Mapas/php/zonas.php",
      type: "POST",
      dataType: "json",
      data: { listarZonas: 1 },
      success: function (zonas) {
        const contenedor = $("#zonas_accordion");
        contenedor.empty();

        if (!Array.isArray(zonas) || zonas.length === 0) {
          contenedor.html(
            '<div class="alert alert-warning mb-0">No se encontraron zonas registradas.</div>'
          );
          return;
        }

        zonas.forEach(function (z, index) {
          const nombre = z.Nombre || "Zona " + (index + 1);
          const idCol = "collapse_" + nombre.replace(/\s+/g, "_");

          const card = `
          <div class="card mb-0">
            <div class="card-header" id="${nombre}">
              <h5 class="m-0">
                <a class="custom-accordion-title d-block py-1"
                  data-bs-toggle="collapse" href="#${idCol}"
                  aria-expanded="false" aria-controls="${idCol}">
                  <i class="uil-location-point"></i> ${nombre}
                  <i class="mdi mdi-chevron-down accordion-arrow"></i>
                </a>
              </h5>
            </div>
            <div id="${idCol}" class="collapse"
              aria-labelledby="heading"
              data-bs-parent="#custom-accordion-one">
              <div class="card-body">
                <div><b>Latitud Norte:</b> ${z.LatitudN || "-"} </div>
                <div><b>Latitud Sur:</b> ${z.LatitudS || "-"} </div>
                <div><b>Longitud Este:</b> ${z.LongitudE || "-"} </div>
                <div><b>Longitud Oeste:</b> ${z.LongitudO || "-"} </div>
              </div>
            </div>
          </div>`;
          contenedor.append(card);
        });
      },
      error: function (xhr, status, err) {
        console.error("Error al cargar zonas:", err);
        $("#zonas_accordion").html(
          '<div class="alert alert-danger mb-0">Error al cargar las zonas.</div>'
        );
      },
    });
  }

  // Llamar automáticamente al cargar la página
  $(document).ready(function () {
    cargarZonasAccordion();
  });
});

let selected = [];

$("#select_rec_mapa").change(function () {
  selected = [];

  $(this)
    .find("option:selected")
    .each(function (i, e) {
      console.log(e.value);
      console.log(zona);
      selected.push(e.value);
    });
  initMap();
  // if (zona) initMap(zona);
});

// This example adds a user-editable rectangle to the map.
// When the user changes the bounds of the rectangle,
// an info window pops up displaying the new bounds.
let rectangle;
let map;
let infoWindow;
let milat;
let milng;
let zona;

function initMap(z) {
  var divMapa = document.getElementById("map");
  var xhttp;
  var resultado = [];
  var markers = [];
  var co = [];

  map = new google.maps.Map(document.getElementById("map"), {
    center: { lat: -31.4448988, lng: -64.177743 },
    zoom: 10,
  });

  // Normalizar zona y evitar llamadas sin una zona seleccionada
  zona = z || zona || null;
  if (!zona) {
    return; // no pedir bounds/servicios hasta que el usuario elija una zona
  }

  $.ajax({
    data: { Buscar: 1, zona: zona, rec: selected },
    url: "Mapas/php/zonas.php",
    type: "post",
    dataType: "json",
    success: function (jsonData) {
      $("#cantidad").html(
        (jsonData.Total || 0) + " Servicios dentro de " + (zona || "-")
      );
      const bounds = {
        north: Number(jsonData.LatitudN),
        south: Number(jsonData.LatitudS),
        east: Number(jsonData.LongitudE),
        west: Number(jsonData.LongitudO),
      };

      // Validar números finitos antes de dibujar
      if (
        ![bounds.north, bounds.south, bounds.east, bounds.west].every(
          Number.isFinite
        )
      ) {
        console.warn("Bounds inválidos desde backend", jsonData);
        return;
      }

      // Define the rectangle and set its editable property to true.
      rectangle = new google.maps.Rectangle({
        bounds: bounds,
        editable: true,
        draggable: true,
      });
      rectangle.setMap(map);

      // Add an event listener on the rectangle.
      rectangle.addListener("bounds_changed", showNewRect);
      // Define an info window on the map.
      infoWindow = new google.maps.InfoWindow();
      rectangle.addListener("click", showNewRect);
    },
  });

  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (xhttp.readyState == 4 && xhttp.status == 200) {
      resultado = xhttp.responseText;
      var objeto_json = JSON.parse(resultado);
      console.log("veo ahora", objeto_json[0]);
      console.log(objeto_json.data.length);
      $("#cantidad").html(objeto_json.data.length);
      // $("#header-title2").html(c);

      for (var i = 0; i < objeto_json.data.length; i++) {
        //ICONO DE COLORES
        function pinSymbol(color) {
          return {
            //              path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M -2,-30 a 2,2 0 1,1 4,0 2,2 0 1,1 -4,0',
            path: "M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z",
            //                   path: 'M0-48c-9.8 0-17.7 7.8-17.7 17.4 0 15.5 17.7 30.6 17.7 30.6s17.7-15.4 17.7-30.6c0-9.6-7.9-17.4-17.7-17.4z',
            fillColor: "#" + color,
            fillOpacity: 1,
            strokeColor: "#FFFFFF",
            strokeWeight: 1,
            scale: 1,
          };
        }
        var icono = pinSymbol(objeto_json[0][i]);
        var latlong = objeto_json.data[i].coordenadas.split(",");
        myLatLng = {
          lat: Number(latlong[0]),
          lng: Number(latlong[1]),
        };

        var marker = new google.maps.Marker({
          position: myLatLng,
          map: map,
          title: objeto_json.data[i].nombrecliente,
          icon: icono,
        });
        markers.push(marker);
      }
    }
  };

  var url = "Mapas/php/datos_zonas.php";
  xhttp.open("POST", url, true);
  xhttp.send();
}

/** Show the new coordinates for the rectangle in an info window. */
function showNewRect() {
  // Guardas básicas
  if (!rectangle || typeof rectangle.getBounds !== "function") return;
  if (!zona) return; // no hay zona seleccionada

  const b = rectangle.getBounds();
  const ne = b.getNorthEast();
  const sw = b.getSouthWest();

  const nelat = ne.lat();
  const nelng = ne.lng();
  const swlat = sw.lat();
  const swlng = sw.lng();

  // Validar números finitos para evitar InvalidValueError
  if (![nelat, nelng, swlat, swlng].every(Number.isFinite)) {
    console.warn("showNewRect: bounds no finitos", {
      nelat,
      nelng,
      swlat,
      swlng,
    });
    return;
  }

  // Asegurar infoWindow
  if (!infoWindow) infoWindow = new google.maps.InfoWindow();
  const contentString =
    `<b>${zona}</b><br>` +
    `NE: ${nelat.toFixed(6)}, ${nelng.toFixed(6)}<br>` +
    `SW: ${swlat.toFixed(6)}, ${swlng.toFixed(6)}`;

  infoWindow.setContent(contentString);
  infoWindow.setPosition(ne);
  infoWindow.open(map);

  // Debounce para no saturar el backend con bounds_changed continuos
  if (window._rectSaveTimer) clearTimeout(window._rectSaveTimer);
  window._rectSaveTimer = setTimeout(function () {
    $.ajax({
      url: "Mapas/php/zonas.php",
      type: "POST",
      dataType: "json",
      data: {
        zona: zona,
        Subir: 1,
        nelat: nelat,
        nelng: nelng,
        swlat: swlat,
        swlng: swlng,
        rec: Array.isArray(selected) ? selected : [],
      },
      success: function (jsonData) {
        console.log("Subir response", jsonData);
        if (jsonData && (jsonData.success == 1 || jsonData.ok === true)) {
          $("#cantidad").html(
            (jsonData.Total || 0) + " Servicios dentro de " + (zona || "-")
          );
        } else {
          console.warn("Subir sin éxito", jsonData);
        }
      },
      error: function (xhr) {
        console.error("Error al subir bounds", xhr && xhr.responseText);
      },
    });
  }, 300);
}

$("#agregarzonas").click(function () {
  var nombrezona = $("#nombrezona").val();
  $.ajax({
    data: { AgregarZona: 1, nombrezona: nombrezona },
    url: "Mapas/php/zonas.php",
    type: "post",
    success: function (response) {
      $("#zona-modal").modal("hide");
      $.NotificationApp.send(
        "Exito !",
        "Se agrego la Zona.!",
        "bottom-right",
        "#FFFFFF",
        "success"
      );
    },
  });
});

$(".card-header").click(function () {
  var id = $(this).attr("id");
  zona = id; // primero fijo la zona
  $(".header-title").html("Zonas google Maps " + zona);
  initMap(zona); // luego inicializo el mapa con la zona definida
});

$("#cambiar_recorrido").click(function () {
  $("#renderizar-modal").modal("show");

  $.ajax({
    data: { BuscarRecorridos: 1 },
    type: "POST",
    url: "Proceso/php/pendientes.php",
    success: function (response) {
      $(".selector-recorrido select").html(response).fadeIn();
    },
  });
});

$("#renderizar_ok").click(function () {
  var recnew = $("#recorrido_t").val();
  // alert(selected+' '+zona+' '+recnew);
  $.ajax({
    data: {
      CambiarRecorridos: 1,
      Recnew: recnew,
      Zona: zona,
      Recorridos: selected,
    },
    type: "POST",
    url: "Mapas/php/zonas.php",
    beforeSend: function () {
      $("#renderizar-modal").modal("hide");
      $("#info-alert-modal").modal("show");
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == 1) {
        console.log("veamos", jsonData.exito);
        $("#info-alert-modal").modal("hide");
        $.NotificationApp.send(
          "Exito !",
          "Se movieron " + jsonData.cuenta + " registros.!",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
        initMap();
      }
    },
  });
});
