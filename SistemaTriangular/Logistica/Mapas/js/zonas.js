// =========================
// Globals (UNA sola vez)
// =========================
let selected = []; // ✅ UNA sola declaración
let map = null;
let rectangle = null;
let polygon = null;
let currentPolyPoints = null;
let infoWindow = null;
let markers = [];
// Paralelo a "markers" - cada waypoint cargado con su marker real y si ya
// fue movido a otro Recorrido en esta sesion de trabajo (drag&drop de zonas
// sobre Recorridos "en alta" - ver renderCardsAsignacion/moverZonaARecorrido
// mas abajo). Se usa para: contar waypoints por zona sin ir al servidor, y
// para recolorear en el mapa justo los markers que se movieron.
let waypointsData = [];

let zona = null;
let zonaId = null;

// Cache de la ultima respuesta de listarZonas (Poligono/Color por zona) y si
// estamos parados en la vista "Ver Todas las Zonas" - las cards de
// asignacion por drag&drop solo tienen sentido ahi, no mirando una zona
// puntual del acordeon (esa ya tiene su propio "Cambiar Recorrido").
let zonasCache = [];
let vistaTodasActiva = false;

let milat;
let milng;

// =========================
// Accordion de zonas
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
        const idZona = z.id;

        // ✅ IDs únicos aunque se repita el nombre
        const idCol = "collapse_zona_" + idZona;
        const safeHeaderId = "zona_header_" + idZona;

        const card = `
          <div class="card mb-0">
            <div class="card-header" id="${safeHeaderId}" data-zona="${nombre}" data-idzona="${idZona}">
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
              aria-labelledby="${safeHeaderId}"
              data-bs-parent="#zonas_accordion">
              <div class="card-body">
                <div><b>Latitud Norte:</b> ${z.LatitudN || "-"} </div>
                <div><b>Latitud Sur:</b> ${z.LatitudS || "-"} </div>
                <div><b>Longitud Este:</b> ${z.LongitudE || "-"} </div>
                <div><b>Longitud Oeste:</b> ${z.LongitudO || "-"} </div>

                <hr class="my-2">

                <button
                  type="button"
                  class="btn btn-danger btn-sm btnEliminarZona"
                  data-idzona="${idZona}"
                  data-nombre="${nombre}">
                  <i class="mdi mdi-delete mdi-18px ms-1"></i> Eliminar zona
                </button>
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

// =========================
// Init on ready (UNA sola vez)
// =========================
$(document).ready(function () {
  // Limpiar (si tu backend lo necesita)
  $.ajax({
    data: { Limpiar: 1 },
    type: "POST",
    url: "Mapas/php/zonas.php",
  });

  // Cargar recorridos - solo los que tienen servicios abiertos asignados
  // (no tiene sentido elegir uno vacio para reasignar nada desde ahi).
  $.ajax({
    data: { RecorridosConServicios: 1 },
    type: "POST",
    url: "Mapas/php/zonas.php",
    dataType: "html", // zonas.php manda Content-Type: application/json por defecto en otras acciones - ver dataType:"text" en datos.js para el mismo caso
    success: function (response) {
      $(".selector-recorrido1 select").html(response).fadeIn();
    },
  });

  // Cargar zonas
  cargarZonasAccordion();
});

// =========================
// Helpers para limpiar mapa
// =========================
function clearMarkers() {
  markers.forEach((m) => m.setMap(null));
  markers = [];
  waypointsData = [];
}

// Icono de pin - antes vivia adentro del callback de exito de
// cargarWaypointsZona() (no reusable); se saca a nivel de modulo para poder
// llamarlo tambien al recolorear markers movidos (moverZonaARecorrido).
function pinSymbol(color) {
  return {
    path: "M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z",
    fillColor: "#" + color,
    fillOpacity: 1,
    strokeColor: "#FFFFFF",
    strokeWeight: 1,
    scale: 1,
  };
}

// Tarjeta de info al tocar un waypoint - mismo estilo/diseño que
// construirInfoWindowServicio() de Mapas/js/hojaderuta.js, pero recortada
// (sin telefono/WhatsApp ni "Ver en tabla", que no aplican en Zonas) y
// duplicada aca en vez de compartida: hojaderuta.js declara sus propios
// globals "map"/"markers" a nivel de modulo, cargarlo junto con zonas.js en
// la misma pagina chocaria (doble declaracion de la misma variable).
function construirInfoWindowZona(datos) {
  var seguimientoHtml = datos.seguimiento
    ? '<div style="font-size:12px;color:#5f6368;margin-bottom:4px;"><b>Seguimiento:</b> ' + datos.seguimiento + "</div>"
    : "";
  var recorridoHtml = datos.recorrido
    ? '<div style="font-size:12px;color:#5f6368;"><b>Recorrido:</b> ' + datos.recorrido + "</div>"
    : "";

  return (
    '<div style="min-width:200px;max-width:260px;font-family:-apple-system,Roboto,Arial,sans-serif;padding:0 4px;margin-top:-2px;">' +
    '<div style="font-size:16px;font-weight:700;color:#202124;margin-bottom:4px;line-height:1.3;">' +
    (datos.cliente || "") +
    "</div>" +
    '<div style="font-size:13px;color:#5f6368;margin-bottom:6px;">' +
    (datos.direccion || "") +
    "</div>" +
    seguimientoHtml +
    recorridoHtml +
    "</div>"
  );
}

function clearRectangle() {
  if (rectangle) {
    google.maps.event.clearInstanceListeners(rectangle);
    rectangle.setMap(null);
    rectangle = null;
  }
}

function clearPolygon() {
  if (polygon) {
    google.maps.event.clearInstanceListeners(polygon);
    polygon.setMap(null);
    polygon = null;
  }
  currentPolyPoints = null;
}

function computeBoundsFromPoints(points) {
  const b = new google.maps.LatLngBounds();
  points.forEach((p) => b.extend(p));
  return b;
}

// Ray-casting point in polygon (no geometry library needed)
function pointInPolygon(point, vs) {
  // point: {lat,lng}, vs: [{lat,lng}, ...]
  const x = point.lng;
  const y = point.lat;
  let inside = false;

  for (let i = 0, j = vs.length - 1; i < vs.length; j = i++) {
    const xi = vs[i].lng,
      yi = vs[i].lat;
    const xj = vs[j].lng,
      yj = vs[j].lat;

    const intersect =
      yi > y !== yj > y && x < ((xj - xi) * (y - yi)) / (yj - yi) + xi;
    if (intersect) inside = !inside;
  }

  return inside;
}

function serializePolygonPath(poly) {
  const path = poly.getPath();
  const pts = [];
  for (let i = 0; i < path.getLength(); i++) {
    const p = path.getAt(i);
    pts.push({ lat: Number(p.lat()), lng: Number(p.lng()) });
  }
  return pts;
}

function computeBBox(points) {
  // returns {LatitudN, LatitudS, LongitudE, LongitudO}
  let minLat = Infinity,
    maxLat = -Infinity,
    minLng = Infinity,
    maxLng = -Infinity;

  points.forEach((p) => {
    if (p.lat < minLat) minLat = p.lat;
    if (p.lat > maxLat) maxLat = p.lat;
    if (p.lng < minLng) minLng = p.lng;
    if (p.lng > maxLng) maxLng = p.lng;
  });

  return {
    LatitudN: maxLat,
    LatitudS: minLat,
    LongitudE: maxLng,
    LongitudO: minLng,
  };
}

// =========================
// initMap (CALLBACK GOOGLE)
// Solo crea el mapa 1 vez.
// =========================
function initMap() {
  if (map) return; // ✅ no recrear

  map = new google.maps.Map(document.getElementById("map"), {
    center: { lat: -31.4448988, lng: -64.177743 },
    zoom: 10,
  });

  infoWindow = new google.maps.InfoWindow();

  // Si ya hay zona seleccionada al cargar maps
  if (zonaId) renderZona(zonaId);
}

// =========================
// Render de zona (update overlays)
// =========================
function renderZona(id) {
  zonaId = Number(id || zonaId || 0);
  if (!zonaId) return;

  // Si maps todavía no cargó, initMap() se ejecuta por callback y luego renderZona
  if (!map) return;

  // limpiar overlays anteriores (incluida la vista de "todas las zonas")
  clearRectangle();
  clearMarkers();
  clearPolygon();
  clearOverlaysTodas();
  vistaTodasActiva = false;
  $("#fila_asignacion_zonas").addClass("d-none");

  // 1) bounds + rectangle o polígono
  $.ajax({
    data: { Buscar: 1, idZona: zonaId, rec: selected },
    url: "Mapas/php/zonas.php",
    type: "POST",
    dataType: "json",
    success: function (jsonData) {
      // 🔁 Sincronizar nombre e id desde backend
      if (jsonData && jsonData.Nombre) zona = jsonData.Nombre;
      if (jsonData && jsonData.id) zonaId = Number(jsonData.id);
      if (zona) $("#zonas_map_title").html("Zonas google Maps " + zona);
      $("#cantidad").html(
        (jsonData.Total || 0) + " Servicios dentro de " + (zona || "-")
      );

      const bounds = {
        north: Number(jsonData.LatitudN),
        south: Number(jsonData.LatitudS),
        east: Number(jsonData.LongitudE),
        west: Number(jsonData.LongitudO),
      };

      if (
        ![bounds.north, bounds.south, bounds.east, bounds.west].every(
          Number.isFinite
        )
      ) {
        console.warn("Bounds inválidos desde backend", jsonData);
        return;
      }

      // ✅ Preferir polígono si existe
      let polyPoints = null;
      if (jsonData.Poligono) {
        try {
          const parsed =
            typeof jsonData.Poligono === "string"
              ? JSON.parse(jsonData.Poligono)
              : jsonData.Poligono;

          // Soportar dos formatos:
          // A) [{lat,lng}, ...]
          // B) GeoJSON Polygon: {type:'Polygon', coordinates:[[[lng,lat],...]]}
          if (Array.isArray(parsed)) {
            polyPoints = parsed
              .map((p) => ({ lat: Number(p.lat), lng: Number(p.lng) }))
              .filter((p) => Number.isFinite(p.lat) && Number.isFinite(p.lng));
          } else if (
            parsed &&
            parsed.type === "Polygon" &&
            Array.isArray(parsed.coordinates)
          ) {
            const ring = parsed.coordinates[0] || [];
            polyPoints = ring
              .map((xy) => ({ lng: Number(xy[0]), lat: Number(xy[1]) }))
              .filter((p) => Number.isFinite(p.lat) && Number.isFinite(p.lng));
          }

          if (polyPoints && polyPoints.length >= 3) {
            currentPolyPoints = polyPoints;

            polygon = new google.maps.Polygon({
              paths: polyPoints,
              editable: true,
              draggable: true,
              map,
              // Si querés usar el color guardado, backend debería enviar jsonData.Color
              strokeOpacity: 1,
              strokeWeight: 2,
              fillOpacity: 0.25,
            });

            // listeners de edición
            const path = polygon.getPath();
            path.addListener("set_at", showNewPoly);
            path.addListener("insert_at", showNewPoly);
            path.addListener("remove_at", showNewPoly);
            polygon.addListener("click", showNewPoly);

            // centrar en el polígono
            const polyBounds = computeBoundsFromPoints(polyPoints);
            map.fitBounds(polyBounds);
            cargarWaypointsZona();
            return; // 👈 no dibujar rectángulo si hay polígono
          }
        } catch (e) {
          console.warn("Poligono inválido", e, jsonData.Poligono);
        }
      }

      // 🔁 Fallback: rectángulo con bounds
      rectangle = new google.maps.Rectangle({
        bounds,
        editable: true,
        draggable: true,
        map,
      });

      rectangle.addListener("bounds_changed", showNewRect);
      rectangle.addListener("click", showNewRect);

      const gBounds = new google.maps.LatLngBounds(
        { lat: bounds.south, lng: bounds.west },
        { lat: bounds.north, lng: bounds.east }
      );
      map.fitBounds(gBounds);
      cargarWaypointsZona();
    },
  });
}

// Markers de la zona - encadenado DESPUES de que termina "Buscar" (arriba),
// no en paralelo: antes se disparaban los dos requests juntos y, segun cual
// llegaba primero al servidor, este pedido podia leer $_SESSION['rec']
// todavia vacio/desactualizado (los waypoints no aparecian) o
// currentPolyPoints todavia con la forma de la zona anterior (filtraba mal).
// Ahora manda "rec" directo, sin depender de sesion.
function cargarWaypointsZona() {
  $.ajax({
    url: "Mapas/php/datos_zonas.php",
    type: "POST",
    dataType: "json",
    data: { rec: selected },
    success: function (objeto_json) {
      clearMarkers();

      for (let i = 0; i < objeto_json.data.length; i++) {
        const latlong = (objeto_json.data[i].coordenadas || "").split(",");
        const myLatLng = {
          lat: Number(latlong[0]),
          lng: Number(latlong[1]),
        };

        if (!Number.isFinite(myLatLng.lat) || !Number.isFinite(myLatLng.lng))
          continue;

        // ✅ Si hay polígono, filtrar puntos por contención
        if (Array.isArray(currentPolyPoints) && currentPolyPoints.length >= 3) {
          if (!pointInPolygon(myLatLng, currentPolyPoints)) {
            continue;
          }
        }

        const icono = pinSymbol(objeto_json[0][i]);

        const marker = new google.maps.Marker({
          position: myLatLng,
          map,
          title: objeto_json.data[i].nombrecliente || "",
          icon: icono,
        });

        // Antes el click en un waypoint no mostraba nada - mismo diseño de
        // tarjeta que ya usa Hoja de Ruta, sin telefono (no hace falta aca).
        const datosServicio = {
          cliente: objeto_json.data[i].nombrecliente,
          direccion: objeto_json.data[i].Direccion,
          seguimiento: objeto_json.data[i].Seguimiento,
          recorrido: objeto_json.data[i].Recorrido,
        };
        marker.addListener("click", function () {
          if (!infoWindow) infoWindow = new google.maps.InfoWindow();
          infoWindow.setContent(construirInfoWindowZona(datosServicio));
          infoWindow.open(map, marker);
        });

        markers.push(marker);
        waypointsData.push({ lat: myLatLng.lat, lng: myLatLng.lng, marker, movido: false });
      }

      renderCardsAsignacion();
    },
  });
}

// =========================
// Eventos
// =========================

// Click header zona => render
$(document).on("click", "#zonas_accordion .card-header", function () {
  const z = $(this).data("zona");
  const idz = $(this).data("idzona");
  if (!idz) return;

  zona = z || zona || null;
  zonaId = Number(idz);

  if (zona) $("#zonas_map_title").html("Zonas google Maps " + zona);
  renderZona(zonaId);
});

// Cambio recorrido => recalcular zona actual
$("#select_rec_mapa").change(function () {
  selected = [];

  $(this)
    .find("option:selected")
    .each(function (i, e) {
      selected.push(e.value);
    });

  // ✅ NO initMap() (eso solo inicializa)
  if (zonaId) {
    // Viendo una zona puntual: renderZona() ya encadena la carga de waypoints.
    renderZona(zonaId);
  } else if (map) {
    // Sin zona puntual seleccionada (ej. "Ver Todas las Zonas", o recien
    // entrando a la pantalla) - antes no pasaba nada hasta abrir una zona
    // del acordeon; ahora los waypoints se cargan apenas se eligen
    // Recorridos, sin depender de estar mirando una zona especifica.
    cargarWaypointsZona();
  }
  verificarGeolocalizacion();
});

// =========================
// Aviso de geolocalizacion faltante
// =========================
// Antes los servicios sin coordenadas validas (o con basura/'0') quedaban
// silenciosamente afuera del mapa (Clientes.Latitud<>'' no los detecta) sin
// ningun aviso - un chofer podia terminar sin ese servicio en ninguna zona y
// nadie se enteraba. Se deja un badge persistente (no solo un toast que se
// pierde) + bloqueo de confirmacion antes de mover servicios.
let ultimoChequeoGeo = null;

function verificarGeolocalizacion(callback) {
  if (!Array.isArray(selected) || selected.length === 0) {
    ultimoChequeoGeo = null;
    $("#geo-warning-badge").hide();
    if (typeof callback === "function") callback(null);
    return;
  }

  $.ajax({
    url: "Mapas/php/zonas.php",
    type: "POST",
    dataType: "json",
    data: { VerificarGeolocalizacion: 1, rec: selected },
    success: function (r) {
      ultimoChequeoGeo = r;
      if (r.status === "warning") {
        $("#geo-warning-badge")
          .text("⚠ " + r.faltantes.length + " sin geolocalizar (no incluidos)")
          .show();
      } else {
        $("#geo-warning-badge").hide();
      }
      if (typeof callback === "function") callback(r);
    },
  });
}

// Click en el badge => ver el listado completo
$(document).on("click", "#geo-warning-badge", function () {
  if (!ultimoChequeoGeo || !ultimoChequeoGeo.faltantes?.length) return;
  Swal.fire({
    icon: "warning",
    title: "Servicios sin geolocalizar",
    html:
      "<div style='text-align:left;max-height:300px;overflow-y:auto'>" +
      ultimoChequeoGeo.faltantes.map((f) => `<div>• ${f}</div>`).join("") +
      "</div>",
  });
});

// Confirma con el operador si hay servicios sin geolocalizar antes de seguir
// con una accion que mueve datos (Cambiar Recorrido). Si no hay faltantes,
// sigue directo.
function confirmarSiFaltanGeo(onContinuar) {
  if (!ultimoChequeoGeo || !ultimoChequeoGeo.faltantes?.length) {
    onContinuar();
    return;
  }
  Swal.fire({
    icon: "warning",
    title: "Hay servicios sin geolocalizar",
    html:
      `<p>${ultimoChequeoGeo.faltantes.length} servicio(s) de los Recorridos seleccionados no tienen coordenadas válidas y NO se van a mover con esta acción:</p>` +
      "<div style='text-align:left;max-height:200px;overflow-y:auto'>" +
      ultimoChequeoGeo.faltantes.map((f) => `<div>• ${f}</div>`).join("") +
      "</div>",
    showCancelButton: true,
    confirmButtonText: "Continuar de todas formas",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) onContinuar();
  });
}

// =========================
// Ver todas las zonas juntas (overview de solo lectura)
// =========================
let overlaysTodas = [];

function clearOverlaysTodas() {
  overlaysTodas.forEach((o) => {
    google.maps.event.clearInstanceListeners(o);
    o.setMap(null);
  });
  overlaysTodas = [];
}

function renderTodasLasZonas() {
  clearRectangle();
  clearPolygon();
  clearMarkers();
  clearOverlaysTodas();
  zona = null;
  zonaId = null;
  vistaTodasActiva = true;

  $.ajax({
    url: "Mapas/php/zonas.php",
    type: "POST",
    dataType: "json",
    data: { listarZonas: 1 },
    success: function (zonas) {
      if (!Array.isArray(zonas) || zonas.length === 0) {
        Swal.fire({ icon: "info", title: "No hay zonas para mostrar" });
        return;
      }

      zonasCache = zonas;

      let infowindowActivo = null;
      const bounds = new google.maps.LatLngBounds();

      zonas.forEach(function (z) {
        const color = z.Color || "#4D1A50";
        let shape = null;
        let poligonoPts = null;

        if (z.Poligono) {
          try {
            const parsed = typeof z.Poligono === "string" ? JSON.parse(z.Poligono) : z.Poligono;
            if (Array.isArray(parsed) && parsed.length >= 3) {
              poligonoPts = parsed
                .map((p) => ({ lat: Number(p.lat), lng: Number(p.lng) }))
                .filter((p) => Number.isFinite(p.lat) && Number.isFinite(p.lng));
            }
          } catch (e) {
            poligonoPts = null;
          }
        }

        if (poligonoPts && poligonoPts.length >= 3) {
          shape = new google.maps.Polygon({
            paths: poligonoPts,
            strokeColor: color,
            fillColor: color,
            fillOpacity: 0.25,
            editable: false,
            map,
          });
          poligonoPts.forEach((p) => bounds.extend(p));
        } else {
          const rectBounds = {
            north: Number(z.LatitudN),
            south: Number(z.LatitudS),
            east: Number(z.LongitudE),
            west: Number(z.LongitudO),
          };
          if (![rectBounds.north, rectBounds.south, rectBounds.east, rectBounds.west].every(Number.isFinite)) return;
          shape = new google.maps.Rectangle({
            bounds: rectBounds,
            strokeColor: color,
            fillColor: color,
            fillOpacity: 0.25,
            editable: false,
            map,
          });
          bounds.extend({ lat: rectBounds.north, lng: rectBounds.east });
          bounds.extend({ lat: rectBounds.south, lng: rectBounds.west });
        }

        const iw = new google.maps.InfoWindow({ content: `<b>${z.Nombre}</b>` });
        shape.addListener("click", function (e) {
          if (infowindowActivo) infowindowActivo.close();
          iw.setPosition(e.latLng);
          iw.open(map);
          infowindowActivo = iw;
        });

        overlaysTodas.push(shape);
      });

      map.fitBounds(bounds);
      $("#zonas_map_title").html("Zonas google Maps (todas)");
      $("#cantidad").html(zonas.length + " zona(s)");
      renderCardsAsignacion();
    },
  });
}

// =========================
// Cards de asignacion por drag & drop: zonas (con conteo de waypoints, a la
// izquierda) sobre Recorridos "en alta" (destino, a la derecha) - solo
// tiene sentido en la vista "Ver Todas las Zonas" con Recorridos elegidos y
// waypoints ya cargados en el mapa.
// =========================
function renderCardsAsignacion() {
  const $fila = $("#fila_asignacion_zonas");

  if (!vistaTodasActiva || !Array.isArray(selected) || selected.length === 0 || waypointsData.length === 0) {
    $fila.addClass("d-none");
    return;
  }

  const contenedor = $("#contenedorZonasDrag");
  contenedor.empty();

  let huboAlguna = false;

  zonasCache.forEach(function (z) {
    let poligonoPts = null;
    if (z.Poligono) {
      try {
        const parsed = typeof z.Poligono === "string" ? JSON.parse(z.Poligono) : z.Poligono;
        if (Array.isArray(parsed) && parsed.length >= 3) {
          poligonoPts = parsed
            .map((p) => ({ lat: Number(p.lat), lng: Number(p.lng) }))
            .filter((p) => Number.isFinite(p.lat) && Number.isFinite(p.lng));
        }
      } catch (e) {
        poligonoPts = null;
      }
    }
    if (!poligonoPts || poligonoPts.length < 3) return; // sin poligono real no hay forma de contar

    const cantidad = waypointsData.filter(
      (w) => !w.movido && pointInPolygon({ lat: w.lat, lng: w.lng }, poligonoPts)
    ).length;
    if (cantidad === 0) return;

    huboAlguna = true;
    const color = z.Color ? "#" + String(z.Color).replace("#", "") : "#4D1A50";

    const card = $(
      '<div class="zona-drag-card border rounded p-2 mb-2" draggable="true" style="border-left:4px solid ' +
        color +
        ' !important;">' +
        '<div class="d-flex justify-content-between align-items-center">' +
        "<strong>" + z.Nombre + "</strong>" +
        '<span class="badge bg-light text-dark border">' + cantidad + (cantidad === 1 ? " waypoint" : " waypoints") + "</span>" +
        "</div>" +
        '<div class="mt-1"><span class="badge bg-light text-muted border"><i class="mdi mdi-cursor-move"></i> Arrastrar a un Recorrido</span></div>' +
        "</div>"
    );
    card.attr("data-idzona", z.id);
    contenedor.append(card);
  });

  if (!huboAlguna) {
    contenedor.html(
      '<div class="text-muted small">No hay waypoints geolocalizados dentro de ninguna zona con los Recorridos seleccionados.</div>'
    );
  }

  $fila.removeClass("d-none");
  cargarRecorridosEnAlta();
}

function cargarRecorridosEnAlta() {
  const contenedor = $("#contenedorRecorridosDrop");
  contenedor.html('<div class="col-12 text-muted small"><i class="mdi mdi-dots-circle mdi-spin"></i> Buscando Recorridos en alta...</div>');

  $.ajax({
    url: "Mapas/php/zonas.php",
    type: "POST",
    dataType: "json",
    data: { RecorridosEnAlta: 1 },
    success: function (r) {
      contenedor.empty();

      if (r.status !== "success" || !r.data || r.data.length === 0) {
        contenedor.html('<div class="col-12 text-muted small">No hay Recorridos en alta disponibles.</div>');
        return;
      }

      r.data.forEach(function (o) {
        const color = "#" + String(o.Color || "666666").replace("#", "");
        // col-12: esta card ya vive anidada dos niveles adentro de una
        // columna angosta (col-xl-8 > col-md-6) - partir en col-md-6 de
        // nuevo aca dejaba media card vacia con 1-2 Recorridos, que es lo
        // usual (son pocos los "en alta" a la vez).
        const col = $('<div class="col-12"></div>');
        const card = $(
          '<div class="recorrido-drop-card border rounded p-2" style="border-left:4px solid ' +
            color +
            ' !important;">' +
            '<div class="d-flex align-items-center gap-2">' +
            '<i class="mdi mdi-truck font-20" style="color:' + color + '"></i>' +
            '<div class="flex-grow-1">' +
            "<strong>Recorrido " + o.Recorrido + "</strong>" +
            '<div class="text-muted small">' +
            (o.NombreRecorrido || "") +
            (o.NombreChofer ? " · " + o.NombreChofer : "") +
            "</div>" +
            "</div>" +
            "</div>" +
            "</div>"
        );
        card.attr("data-recorrido", o.Recorrido);
        card.attr("data-color", color);
        col.append(card);
        contenedor.append(col);
      });

      activarDragAndDropZonas();
    },
    error: function () {
      contenedor.html('<div class="col-12 text-danger small">No se pudo cargar la lista de Recorridos en alta.</div>');
    },
  });
}

function activarDragAndDropZonas() {
  document.querySelectorAll(".zona-drag-card").forEach((card) => {
    card.addEventListener("dragstart", function (e) {
      e.dataTransfer.setData("text/plain", card.getAttribute("data-idzona"));
      e.dataTransfer.effectAllowed = "move";
    });
  });

  document.querySelectorAll(".recorrido-drop-card").forEach((card) => {
    card.addEventListener("dragover", function (e) {
      e.preventDefault();
      e.dataTransfer.dropEffect = "move";
      card.classList.add("recorrido-dragover");
    });
    card.addEventListener("dragleave", function () {
      card.classList.remove("recorrido-dragover");
    });
    card.addEventListener("drop", function (e) {
      e.preventDefault();
      card.classList.remove("recorrido-dragover");
      const idZona = e.dataTransfer.getData("text/plain");
      if (!idZona) return;
      moverZonaARecorrido(idZona, card);
    });
  });
}

// Al soltar: reasignacion REAL e inmediata (confirmado con el usuario, a
// diferencia de Planificador que arma todo en pantalla y graba recien con
// un boton "Guardar" al final) - se llama a CambiarRecorridos ahi mismo y,
// si funciona, se recolorean puntualmente los markers que se movieron
// (match por lat/lng) en vez de recargar todo el mapa.
function moverZonaARecorrido(idZona, cardDestino) {
  const recorridoDestino = cardDestino.getAttribute("data-recorrido");
  const colorDestino = (cardDestino.getAttribute("data-color") || "#666666").replace("#", "");

  cardDestino.style.opacity = "0.5";

  $.ajax({
    url: "Mapas/php/zonas.php",
    type: "POST",
    dataType: "json",
    data: {
      CambiarRecorridos: 1,
      Recnew: recorridoDestino,
      idZona: idZona,
      Recorridos: selected,
    },
    success: function (jsonData) {
      cardDestino.style.opacity = "1";

      if (jsonData.success != 1) {
        Swal.fire({ icon: "error", title: "Error", text: jsonData.error || "No se pudo mover el recorrido." });
        return;
      }

      toast("success", "Listo", "Se movieron " + jsonData.cuenta + " servicio(s) al Recorrido " + recorridoDestino + ".");

      const TOLERANCIA = 0.0001;
      (jsonData.movidos || []).forEach(function (p) {
        const w = waypointsData.find(
          (x) => !x.movido && Math.abs(x.lat - p.lat) < TOLERANCIA && Math.abs(x.lng - p.lng) < TOLERANCIA
        );
        if (w) {
          w.marker.setIcon(pinSymbol(colorDestino));
          w.movido = true;
        }
      });

      renderCardsAsignacion();
    },
    error: function () {
      cardDestino.style.opacity = "1";
      Swal.fire({ icon: "error", title: "Error del servidor", text: "No se pudo mover. Reintentá de nuevo." });
    },
  });
}

$(document).on("click", "#ver_todas_zonas", function () {
  renderTodasLasZonas();
});

// Restaurar el trigger de "Cambiar Recorrido" (dropdown de tres puntos del
// mapa) - hoy no disparaba nada, el modal #renderizar-modal quedaba sin
// forma de abrirse.
$(document).on("click", "#cambiar_recorrido", function () {
  if (!zonaId) {
    Swal.fire({ icon: "warning", title: "Elegí una zona primero" });
    return;
  }
  $.ajax({
    data: { BuscarRecorridos: 1 },
    type: "POST",
    url: "Proceso/php/pendientes.php",
    success: function (response) {
      $("#recorrido_t").html(response).fadeIn();
      $("#renderizar-modal").modal("show");
    },
  });
});

// Eliminar zona
$(document).on("click", ".btnEliminarZona", function () {
  const idZona = $(this).data("idzona");
  const nombre = $(this).data("nombre");

  if (!idZona) {
    Swal.fire({
      icon: "error",
      title: "Atención",
      text: "No se encontró el ID de la zona para eliminar",
    });
    return;
  }

  Swal.fire({
    title: "¿Eliminar zona?",
    text: `Se eliminará la zona "${nombre}". Esta acción no se puede deshacer.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: "Mapas/php/zonas.php",
      type: "POST",
      dataType: "json",
      data: { eliminarZona: 1, idZona: idZona },
      success: function (res) {
        if (res && res.success == 1) {
          cargarZonasAccordion();

          // Si borraste la zona que estabas viendo, limpio mapa
          if (zonaId === Number(idZona)) {
            zona = null;
            zonaId = null;
            clearRectangle();
            clearPolygon();
            clearMarkers();
          }
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: res.error || "No se pudo eliminar la zona.",
          });
        }
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Error de comunicación al eliminar la zona.",
        });
      },
    });
  });
});

// =========================
// showNewRect (igual que el tuyo, pero usando globals)
// =========================
function showNewRect() {
  if (!rectangle || typeof rectangle.getBounds !== "function") return;
  if (!zona) return;

  const b = rectangle.getBounds();
  const ne = b.getNorthEast();
  const sw = b.getSouthWest();

  const nelat = ne.lat();
  const nelng = ne.lng();
  const swlat = sw.lat();
  const swlng = sw.lng();

  if (![nelat, nelng, swlat, swlng].every(Number.isFinite)) {
    console.warn("showNewRect: bounds no finitos", {
      nelat,
      nelng,
      swlat,
      swlng,
    });
    return;
  }

  if (!infoWindow) infoWindow = new google.maps.InfoWindow();

  infoWindow.setContent(
    `<b>${zona}</b><br>` +
      `NE: ${nelat.toFixed(6)}, ${nelng.toFixed(6)}<br>` +
      `SW: ${swlat.toFixed(6)}, ${swlng.toFixed(6)}`
  );
  infoWindow.setPosition(ne);
  infoWindow.open(map);

  // Debounce
  if (window._rectSaveTimer) clearTimeout(window._rectSaveTimer);
  window._rectSaveTimer = setTimeout(function () {
    $.ajax({
      url: "Mapas/php/zonas.php",
      type: "POST",
      dataType: "json",
      data: {
        zona: zona,
        idZona: zonaId,
        Subir: 1,
        nelat: nelat,
        nelng: nelng,
        swlat: swlat,
        swlng: swlng,
        rec: Array.isArray(selected) ? selected : [],
      },
      success: function (jsonData) {
        if (jsonData && (jsonData.success == 1 || jsonData.ok === true)) {
          $("#cantidad").html(
            (jsonData.Total || 0) + " Servicios dentro de " + (zona || "-")
          );
        }
      },
    });
  }, 300);
}

// =========================
// showNewPoly (guardar polígono editable)
// =========================
function showNewPoly() {
  if (!polygon || typeof polygon.getPath !== "function") return;
  if (!zona) return;

  const pts = serializePolygonPath(polygon).filter(
    (p) => Number.isFinite(p.lat) && Number.isFinite(p.lng)
  );

  if (pts.length < 3) return;

  currentPolyPoints = pts;

  // Mostrar info rápida
  const bb = computeBBox(pts);
  if (!infoWindow) infoWindow = new google.maps.InfoWindow();

  const contentString =
    `<b>${zona}</b><br>` +
    `Puntos: ${pts.length}<br>` +
    `N: ${bb.LatitudN.toFixed(6)} | S: ${bb.LatitudS.toFixed(6)}<br>` +
    `E: ${bb.LongitudE.toFixed(6)} | O: ${bb.LongitudO.toFixed(6)}`;

  // Posicionar en el primer punto (o centro aproximado)
  infoWindow.setContent(contentString);
  infoWindow.setPosition(pts[0]);
  infoWindow.open(map);

  // Debounce de guardado
  if (window._polySaveTimer) clearTimeout(window._polySaveTimer);
  window._polySaveTimer = setTimeout(function () {
    $.ajax({
      url: "Mapas/php/zonas.php",
      type: "POST",
      dataType: "json",
      data: {
        zona: zona,
        idZona: zonaId,
        SubirPoligono: 1,
        Poligono: JSON.stringify(pts),
        // Mantengo también la caja para búsquedas rápidas
        LatitudN: bb.LatitudN,
        LatitudS: bb.LatitudS,
        LongitudE: bb.LongitudE,
        LongitudO: bb.LongitudO,
        rec: Array.isArray(selected) ? selected : [],
      },
      success: function (jsonData) {
        // Si backend devuelve Total, lo mostramos
        if (jsonData && (jsonData.success == 1 || jsonData.ok === true)) {
          if (jsonData.Total !== undefined) {
            $("#cantidad").html(
              (jsonData.Total || 0) + " Servicios dentro de " + (zona || "-")
            );
          }
        } else {
          console.warn("SubirPoligono sin éxito", jsonData);
        }
      },
      error: function (xhr) {
        console.error("Error al subir polígono", xhr && xhr.responseText);
      },
    });
  }, 350);
}

// =========================
// Agregar zona (tu lógica)
// =========================
$("#agregarzonas").click(function () {
  var nombrezona = $("#nombrezona").val();
  $.ajax({
    data: { AgregarZona: 1, nombrezona: nombrezona },
    url: "Mapas/php/zonas.php",
    type: "POST",
    success: function () {
      $("#zona-modal").modal("hide");
      toast("success", "Exito !", "Se agrego la Zona.!");
      cargarZonasAccordion();
    },
  });
});

// =========================
// Renderizar OK (tu lógica, pero al final renderZona)
// =========================
$("#renderizar_ok").click(function () {
  var recnew = $("#recorrido_t").val();

  confirmarSiFaltanGeo(function () {
    $.ajax({
      data: {
        CambiarRecorridos: 1,
        Recnew: recnew,
        idZona: zonaId,
        Recorridos: selected,
      },
      type: "POST",
      url: "Mapas/php/zonas.php",
      dataType: "json",
      beforeSend: function () {
        $("#renderizar-modal").modal("hide");
        mostrarModalCarga("#info-alert-modal", "Estamos moviendo los registros !");
      },
      success: function (jsonData) {
        ocultarModalCarga("#info-alert-modal");
        if (jsonData.success == 1) {
          toast("success", "Exito !", "Se movieron " + jsonData.cuenta + " registros.!");
          renderZona(zonaId);
        } else {
          Swal.fire({ icon: "error", title: "Error", text: jsonData.error || "No se pudo cambiar el recorrido." });
        }
      },
      error: function () {
        ocultarModalCarga("#info-alert-modal");
        Swal.fire({ icon: "error", title: "Error del servidor", text: "No se pudo cambiar el recorrido. Reintentá de nuevo." });
      },
    });
  });
});

// =========================
// Importar zonas desde KML/KMZ (ej. mapa de zonas FLEX exportado de Google My Maps)
// =========================
$("#importar_poligono_ok").click(function () {
  const archivo = $("#importar_poligono_file")[0].files[0];
  if (!archivo) {
    Swal.fire({ icon: "warning", title: "Elegí un archivo .kml o .kmz primero" });
    return;
  }

  const formData = new FormData();
  formData.append("ImportarKML", 1);
  formData.append("archivo", archivo);

  $.ajax({
    url: "Mapas/php/zonas.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    beforeSend: function () {
      $("#importar-poligono-modal").modal("hide");
      mostrarModalCarga("#info-alert-modal", "Importando zonas...");
    },
    success: function (r) {
      ocultarModalCarga("#info-alert-modal");
      if (r.status === "success") {
        toast("success", "Listo", r.message);
        if (r.omitidas && r.omitidas.length) {
          Swal.fire({
            icon: "warning",
            title: "Algunos placemarks no se importaron",
            html: "<div style='text-align:left'>" + r.omitidas.map((o) => `<div>• ${o}</div>`).join("") + "</div>",
          });
        }
        cargarZonasAccordion();
      } else {
        Swal.fire({ icon: "error", title: "No se pudo importar", text: r.message || "" });
      }
    },
    error: function () {
      ocultarModalCarga("#info-alert-modal");
      Swal.fire({ icon: "error", title: "Error del servidor", text: "No se pudo importar el archivo. Reintentá de nuevo." });
    },
  });
});
