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

// Zonas "manuales" - poligonos que el operador dibuja a mano con "Dibujar
// Zona" para armar cards de asignacion al vuelo, sin guardarlas como Zona
// persistida en ZonasMapa (a diferencia de Agregar Zona/Importar KML). Cada
// una vive solo mientras dura la vista "Ver Todas las Zonas" actual.
let zonasManuales = [];
let manualZonaContador = 0;

// Redistribucion por zonas: todas las zonas visibles a la vez, cada una con un
// Recorrido destino elegido en su <select>. El mapa idZona -> Numero de
// Recorrido vive SOLO en memoria (decidido con el usuario, sin columna nueva);
// se pierde al recargar. recorridosActivos alimenta esos <select> y el color
// para recolorear los pines. overlaysTodasPorId permite resaltar una zona sin
// ocultar el resto.
let recorridosActivos = [];
let zonaDestino = {}; // { [idZona]: "NumeroRecorrido" }
let overlaysTodasPorId = {};
let zonaLegendActivaId = null;
let redistribuyendo = false;

// Edicion de una zona EN la vista de todas: se toca "Editar zona" en el popup
// del mapa, ese poligono pasa a editable/draggable (el resto sigue visible), se
// guarda solo y se sale al clickear afuera o "Terminar".
let zonaEnEdicionId = null;
let _zonaEditListeners = [];
let _zonaEditSaveTimer = null;
let _iwZona = null; // InfoWindow reusable de "click en zona"

// =========================
// Legend de zonas (todas visibles a la vez)
// =========================

// Misma paleta que usa la importacion KML en zonas.php, para que una zona sin
// Color en la base tenga igual un color estable (por posicion) y coincida el
// swatch del panel con la forma pintada en el mapa.
const PALETA_ZONAS = [
  "#1E6FD1", "#28a745", "#ffc107", "#dc3545", "#6f42c1",
  "#20c997", "#fd7e14", "#e83e8c", "#17a2b8", "#6610f2",
];

function normalizarColor(c) {
  if (!c) return null;
  c = String(c).trim();
  if (c === "") return null;
  return c[0] === "#" ? c : "#" + c;
}

function colorDeZona(z, index) {
  return normalizarColor(z && z.Color) || PALETA_ZONAS[(index || 0) % PALETA_ZONAS.length];
}

// Parseo de ZonasMapa.Poligono -> [{lat,lng}] (>=3) o null. Unifica el mismo
// bloque try/catch repetido en renderTodasLasZonas() y renderCardsAsignacion().
function poligonoDeCache(z) {
  if (!z || !z.Poligono) return null;
  try {
    const parsed = typeof z.Poligono === "string" ? JSON.parse(z.Poligono) : z.Poligono;
    if (!Array.isArray(parsed) || parsed.length < 3) return null;
    const pts = parsed
      .map((p) => ({ lat: Number(p.lat), lng: Number(p.lng) }))
      .filter((p) => Number.isFinite(p.lat) && Number.isFinite(p.lng));
    return pts.length >= 3 ? pts : null;
  } catch (e) {
    return null;
  }
}

// Waypoints (no movidos) que caen dentro de un poligono.
function contarWaypointsEnPoligono(pts) {
  if (!Array.isArray(pts) || pts.length < 3) return 0;
  return waypointsData.filter(
    (w) => !w.movido && pointInPolygon({ lat: w.lat, lng: w.lng }, pts)
  ).length;
}

// <option>s del <select> "Recorrido destino" a partir de recorridosActivos
// (los "en alta" primero, ya vienen ordenados asi del backend).
function opcionesRecorridosDestino(seleccion) {
  const sel = seleccion == null ? "" : String(seleccion);
  let html = '<option value="">— sin asignar —</option>';
  recorridosActivos.forEach(function (r) {
    const num = String(r.Numero);
    const marca = num === sel ? " selected" : "";
    let extra = "";
    if (r.Iniciado) {
      extra = " · ⚠ YA INICIÓ" + (r.NombreChofer ? " (" + r.NombreChofer + ")" : "");
    } else if (r.EnAlta) {
      extra = " · en alta" + (r.NombreChofer ? " (" + r.NombreChofer + ")" : "");
    }
    html +=
      '<option value="' + num + '"' + marca + ' data-iniciado="' + (r.Iniciado ? 1 : 0) + '">' +
      num + " - " + (r.Nombre || "s/nombre") + extra +
      "</option>";
  });
  return html;
}

// Recorrido activo por numero (helper).
function recorridoPorNumero(num) {
  return recorridosActivos.find((r) => String(r.Numero) === String(num)) || null;
}

function cargarRecorridosActivos(cb) {
  $.ajax({
    url: "Mapas/php/zonas.php",
    type: "POST",
    dataType: "json",
    data: { TodosLosRecorridosActivos: 1 },
    success: function (resp) {
      recorridosActivos = (resp && resp.data) || [];
      if (typeof cb === "function") cb();
    },
    error: function () {
      recorridosActivos = [];
      if (typeof cb === "function") cb();
    },
  });
}

// Dibuja el panel-legend de la izquierda: una fila por zona con swatch de
// color, conteo de waypoints y <select> de Recorrido destino. NO oculta ni
// aisla nada del mapa (eso lo hace renderTodasLasZonas / resaltarZonaEnMapa).
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
        zonasCache = [];
        $("#zonas_total_badge").text("");
        refrescarBotonRedistribuir();
        return;
      }

      zonasCache = zonas;
      $("#zonas_total_badge").text(zonas.length + (zonas.length === 1 ? " zona" : " zonas"));

      zonas.forEach(function (z, index) {
        const nombre = z.Nombre || "Zona " + (index + 1);
        const idZona = z.id;
        const color = colorDeZona(z, index);
        const destino = zonaDestino[idZona] || "";

        const item = $(
          '<div class="zona-legend-item" data-idzona="' + idZona + '">' +
            '<div class="zona-legend-head" data-idzona="' + idZona + '" data-nombre="' +
              String(nombre).replace(/"/g, "&quot;") + '">' +
              '<span class="zona-swatch" style="background:' + color + '"></span>' +
              '<span class="zona-legend-nombre" title="' + String(nombre).replace(/"/g, "&quot;") + '">' + nombre + "</span>" +
              '<span class="zona-legend-destino" data-idzona="' + idZona + '" hidden></span>' +
              '<span class="badge bg-light text-dark border zona-legend-count" data-idzona="' + idZona + '">0</span>' +
              '<i class="mdi mdi-chevron-down"></i>' +
            "</div>" +
            '<div class="zona-legend-body" hidden>' +
              '<label class="mb-1 small text-muted d-block">Recorrido destino</label>' +
              '<select class="form-select form-select-sm zona-destino-select" data-idzona="' + idZona + '">' +
                opcionesRecorridosDestino(destino) +
              "</select>" +
              '<div class="zona-legend-bbox">N ' + (z.LatitudN || "-") + "  ·  S " + (z.LatitudS || "-") +
                "<br>E " + (z.LongitudE || "-") + "  ·  O " + (z.LongitudO || "-") + "</div>" +
              '<div class="d-flex gap-1 mt-2">' +
                '<button type="button" class="btn btn-outline-secondary btn-sm btnEditarFormaZona" data-idzona="' + idZona + '">' +
                  '<i class="mdi mdi-vector-polygon"></i> Editar forma</button>' +
                '<button type="button" class="btn btn-outline-danger btn-sm btnEliminarZona" data-idzona="' + idZona +
                  '" data-nombre="' + String(nombre).replace(/"/g, "&quot;") + '"><i class="mdi mdi-delete"></i></button>' +
              "</div>" +
            "</div>" +
          "</div>"
        );

        contenedor.append(item);
        pintarDestinoEnHead(idZona);
      });

      actualizarConteosLegend();
      refrescarBotonRedistribuir();
    },
    error: function (xhr, status, err) {
      console.error("Error al cargar zonas:", err);
      $("#zonas_accordion").html(
        '<div class="alert alert-danger mb-0">Error al cargar las zonas.</div>'
      );
    },
  });
}

// Muestra el Recorrido destino elegido al lado del nombre de la zona en la fila
// del panel (ej. "Zona 1  → Rec 1517"), sin necesidad de abrir el cuerpo.
function pintarDestinoEnHead(idZona) {
  const $chip = $('.zona-legend-destino[data-idzona="' + idZona + '"]');
  const num = zonaDestino[idZona];
  if (!num) {
    $chip.prop("hidden", true).text("");
    return;
  }
  const rec = recorridosActivos.find((r) => String(r.Numero) === String(num));
  let txt = "→ Rec " + num;
  if (rec && rec.Nombre) {
    const corto = rec.Nombre.length > 16 ? rec.Nombre.slice(0, 15) + "…" : rec.Nombre;
    txt = "→ " + num + " " + corto;
  }
  $chip.text(txt).prop("hidden", false);
}

// Refresca solo los badges de conteo del panel (sin re-armar el DOM), a partir
// de zonasCache + waypointsData. Se llama cuando cambian los waypoints cargados
// o despues de redistribuir.
function actualizarConteosLegend() {
  zonasCache.forEach(function (z) {
    const pts = poligonoDeCache(z);
    const n = pts ? contarWaypointsEnPoligono(pts) : 0;
    const $badge = $('.zona-legend-count[data-idzona="' + z.id + '"]');
    $badge.text(n);
    $badge.toggleClass("bg-light text-dark", n === 0);
    $badge.toggleClass("bg-primary text-white", n > 0);
  });
}

// Resalta una zona en el mapa SIN ocultar el resto: sube su relleno/borde y
// baja el de las demas, centra en ella, y marca su fila en el panel.
function resaltarZonaEnMapa(idZona) {
  zonaLegendActivaId = idZona;
  $(".zona-legend-item").removeClass("zona-activa");
  $('.zona-legend-item[data-idzona="' + idZona + '"]').addClass("zona-activa");

  Object.keys(overlaysTodasPorId).forEach(function (id) {
    const shape = overlaysTodasPorId[id];
    if (!shape) return;
    const activa = String(id) === String(idZona);
    shape.setOptions({
      fillOpacity: activa ? 0.5 : 0.12,
      strokeWeight: activa ? 3 : 1,
      zIndex: activa ? 10 : 1,
    });
  });

  const shape = overlaysTodasPorId[idZona];
  if (shape && map) {
    const b = new google.maps.LatLngBounds();
    if (typeof shape.getPath === "function") {
      shape.getPath().forEach((p) => b.extend(p));
    } else if (typeof shape.getBounds === "function") {
      const gb = shape.getBounds();
      if (gb) { b.extend(gb.getNorthEast()); b.extend(gb.getSouthWest()); }
    }
    if (!b.isEmpty()) map.fitBounds(b);
  }
}

// Habilita/inhabilita el boton "Redistribuir por zonas" y actualiza el texto de
// ayuda segun lo que falte.
function refrescarBotonRedistribuir() {
  const $btn = $("#btn_redistribuir_zonas");
  const $hint = $("#redistribuir_hint");
  const hayRec = Array.isArray(selected) && selected.length > 0;
  const hayWp = waypointsData.length > 0;
  const hayDestino = Object.keys(zonaDestino).length > 0;

  const ok = hayRec && hayWp && hayDestino && !redistribuyendo;
  $btn.prop("disabled", !ok);
  if (redistribuyendo) $("#btn_confirmar_traspaso").prop("disabled", true);
  else $("#btn_confirmar_traspaso").prop("disabled", false);

  if (redistribuyendo) {
    $hint.text("Aplicando el traspaso…");
  } else if (!hayRec) {
    $hint.text("Elegí uno o más Recorridos arriba.");
  } else if (!hayWp) {
    $hint.text("No hay waypoints cargados para los Recorridos elegidos.");
  } else if (!hayDestino) {
    $hint.text("Asigná un Recorrido destino a por lo menos una zona.");
  } else {
    const zc = Object.keys(zonaDestino).length;
    $hint.text(zc + (zc === 1 ? " zona" : " zonas") + " con destino. Previsualizá y después confirmá el traspaso.");
  }

  // Generador de zonas balanceadas: necesita waypoints (no movidos) cargados.
  const wpVivos = waypointsData.filter((w) => !w.movido).length;
  $("#btn_generar_zonas").prop("disabled", redistribuyendo || wpVivos === 0);
  $("#gen_zonas_hint").text(
    wpVivos === 0
      ? "Elegí Recorridos primero; parte los waypoints en N zonas de carga pareja."
      : wpVivos + " waypoints → se parten en N zonas de carga pareja (reemplaza las zonas actuales)."
  );
}

// =========================
// Generador de zonas balanceadas por carga del dia
// =========================

// Biseccion recursiva PROPORCIONAL: para partir en k celdas, se divide k en dos
// mitades lo mas parejas posible (kA, kB) y se reparten los puntos en esa misma
// proporcion, cortando por el lado mas largo del rectangulo en el punto medio
// entre los dos puntos que quedan a cada lado del corte. Recursivo. Da N celdas
// rectangulares que TESELAN el bbox (con margen) sin huecos ni solape, con
// cantidad de puntos ~pareja para cualquier N (no solo potencias de 2).
function biseccionEquitativa(pts, n) {
  const lats = pts.map((p) => p.lat);
  const lngs = pts.map((p) => p.lng);
  let minLat = Math.min.apply(null, lats), maxLat = Math.max.apply(null, lats);
  let minLng = Math.min.apply(null, lngs), maxLng = Math.max.apply(null, lngs);
  const padLat = (maxLat - minLat) * 0.12 || 0.02;
  const padLng = (maxLng - minLng) * 0.12 || 0.02;
  minLat -= padLat; maxLat += padLat; minLng -= padLng; maxLng += padLng;

  const raiz = { n: maxLat, s: minLat, e: maxLng, o: minLng };

  function particion(subPts, rect, k) {
    if (k <= 1 || subPts.length === 0) {
      return [{ rect: rect, count: subPts.length }];
    }
    const kA = Math.ceil(k / 2);
    const kB = k - kA;

    const cortaLng = (rect.e - rect.o) >= (rect.n - rect.s);
    const coord = (p) => (cortaLng ? p.lng : p.lat);
    const ord = subPts.slice().sort((a, b) => coord(a) - coord(b));

    let nA = Math.round((subPts.length * kA) / k);
    nA = Math.max(kA, Math.min(nA, subPts.length - kB)); // deja al menos 1 punto por celda futura
    const izq = ord.slice(0, nA);
    const der = ord.slice(nA);

    let corte;
    if (izq.length && der.length) {
      corte = (coord(izq[izq.length - 1]) + coord(der[0])) / 2;
    } else {
      corte = cortaLng ? (rect.o + rect.e) / 2 : (rect.s + rect.n) / 2;
    }

    let rectA, rectB;
    if (cortaLng) {
      rectA = { n: rect.n, s: rect.s, e: corte, o: rect.o };
      rectB = { n: rect.n, s: rect.s, e: rect.e, o: corte };
    } else {
      rectA = { n: corte, s: rect.s, e: rect.e, o: rect.o };
      rectB = { n: rect.n, s: corte, e: rect.e, o: rect.o };
    }
    return particion(izq, rectA, kA).concat(particion(der, rectB, kB));
  }

  return particion(pts.slice(), raiz, n);
}

function generarZonasBalanceadas(nRaw) {
  const n = Math.max(1, Math.min(10, parseInt(nRaw, 10) || 1));
  const pts = waypointsData.filter((w) => !w.movido).map((w) => ({ lat: w.lat, lng: w.lng }));

  if (pts.length < n) {
    Swal.fire({
      icon: "warning",
      title: "Pocos waypoints",
      text: "Hacen falta al menos " + n + " servicios cargados para generar " + n + " zonas (hay " + pts.length + ").",
    });
    return;
  }

  const celdas = biseccionEquitativa(pts, n);
  const payload = celdas.map((c, i) => {
    const r = c.rect;
    const ring = [
      { lat: r.n, lng: r.o }, { lat: r.n, lng: r.e },
      { lat: r.s, lng: r.e }, { lat: r.s, lng: r.o },
    ];
    return {
      Nombre: "Zona " + (i + 1),
      Poligono: JSON.stringify(ring),
      LatitudN: r.n, LatitudS: r.s, LongitudE: r.e, LongitudO: r.o,
      Color: PALETA_ZONAS[i % PALETA_ZONAS.length],
      _count: c.count,
    };
  });

  const resumen = payload
    .map((z) => '<div class="rd-linea"><span class="rd-swatch" style="background:' + z.Color + '"></span>' +
      z.Nombre + ": ~<b>" + z._count + "</b> serv.</div>")
    .join("");

  Swal.fire({
    title: "Generar " + n + (n === 1 ? " zona" : " zonas"),
    html: "<div style='text-align:left'>Se <b>reemplazan todas las zonas actuales</b> por " + n +
      " de carga pareja:<br><br>" + resumen + "</div>",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, generar y reemplazar",
    cancelButtonText: "Cancelar",
  }).then((res) => {
    if (!res.isConfirmed) return;
    $.ajax({
      url: "Mapas/php/zonas.php",
      type: "POST",
      dataType: "json",
      data: {
        GenerarZonasSet: 1,
        zonas: payload.map((z) => ({
          Nombre: z.Nombre, Poligono: z.Poligono,
          LatitudN: z.LatitudN, LatitudS: z.LatitudS,
          LongitudE: z.LongitudE, LongitudO: z.LongitudO, Color: z.Color,
        })),
      },
      success: function (j) {
        if (j && j.success == 1) {
          toast("success", "Zonas generadas", j.creadas + (j.creadas === 1 ? " zona nueva." : " zonas nuevas."));
          zonaDestino = {}; // los ids de zona cambiaron
          zonaLegendActivaId = null;
          invalidarPreviewTraspaso();
          cargarRecorridosActivos(function () {
            cargarZonasAccordion();
          });
          if (map) renderTodasLasZonasConWaypoints();
        } else {
          Swal.fire({ icon: "error", title: "No se pudo generar", text: (j && j.error) || "" });
        }
      },
      error: function () {
        Swal.fire({ icon: "error", title: "Error del servidor", text: "No se pudieron generar las zonas." });
      },
    });
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

  // Recorridos activos para los <select> "Recorrido destino" del panel; cuando
  // termina, se arma el legend de zonas (necesita las opciones ya cargadas).
  cargarRecorridosActivos(function () {
    cargarZonasAccordion();
  });

  // Landing = todas las zonas visibles a la vez (antes no se veia nada hasta
  // abrir una del acordeon, y abrir una ocultaba el resto). Si el mapa todavia
  // no cargo (initMap corre por callback de Google), se reintenta.
  (function pintarTodasCuandoHayaMapa() {
    if (map) {
      renderTodasLasZonas();
    } else {
      setTimeout(pintarTodasCuandoHayaMapa, 300);
    }
  })();
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
    zoom: 11,
    fullscreenControl: true, // boton nativo "pantalla completa" (esq. sup. der.)
    gestureHandling: "greedy", // scroll = zoom sin tener que apretar Ctrl
    mapTypeControl: false,
  });

  infoWindow = new google.maps.InfoWindow();

  // Click en el fondo del mapa (fuera de cualquier zona) => salir de la edicion
  // de zona en curso ("cuando salgo de la zona se deja de editar").
  map.addListener("click", function () {
    if (zonaEnEdicionId != null) terminarEdicionZonaEnMapa();
  });

  // Si ya hay zona seleccionada al cargar maps
  if (zonaId) renderZona(zonaId);
}

// =========================
// "Dibujar Zona" - la libreria google.maps.drawing (DrawingManager) esta
// deprecada (Maps JS API 3.65+, ya ni carga - "no longer available"), asi
// que se arma a mano: un Polygon vacio que va creciendo con cada click en
// el mapa, mismo patron que ya usa el resto del sistema para editar
// poligonos de zonas. clickable:false en el poligono en progreso para que
// los clicks le lleguen al mapa (map.addListener) y no se los coma el
// propio poligono.
// =========================
let dibujandoZonaManual = false;
let polygonEnProgreso = null;
let clickListenerDibujo = null;
let dblclickListenerDibujo = null;

// Mientras se dibuja, las zonas ya pintadas (overlaysTodas) no deben
// interceptar el click - por defecto son clickable:true (para su propio
// InfoWindow con el nombre), y un click "arriba" de una de esas formas le
// llega A ELLA, no al mapa, asi que clickListenerDibujo nunca se disparaba
// si el operador clickeaba sobre (o cerca de) una zona existente.
function setClickeableZonasExistentes(clickeable) {
  overlaysTodas.forEach(function (o) {
    if (o && typeof o.setOptions === "function") o.setOptions({ clickable: clickeable });
  });
  markers.forEach(function (m) {
    if (m && typeof m.setOptions === "function") m.setOptions({ clickable: clickeable });
  });
}

function iniciarDibujoManual() {
  dibujandoZonaManual = true;
  setClickeableZonasExistentes(false);

  polygonEnProgreso = new google.maps.Polygon({
    map: map,
    // [[]] (un anillo vacio), no [] (cero anillos) - con [] getPath() no
    // tenia ningun anillo "primero" que devolver y quedaba undefined, asi
    // que el primer click tiraba "Cannot read properties of undefined
    // (reading 'push')" y nunca se agregaba ningun vertice.
    paths: [[]],
    strokeColor: "#000000",
    strokeWeight: 2,
    strokeOpacity: 0.8,
    fillColor: "#000000",
    fillOpacity: 0.12,
    editable: false,
    clickable: false,
  });

  $("#dibujar_zona_manual_btn")
    .removeClass("btn-outline-dark")
    .addClass("btn-dark")
    .html('<i class="mdi mdi-vector-polygon"></i> Dibujando... (doble click para cerrar)');

  toast(
    "info",
    "Dibujando zona",
    "Hacé click en el mapa para ir marcando los vértices, y doble click para cerrar el polígono."
  );

  clickListenerDibujo = map.addListener("click", function (e) {
    polygonEnProgreso.getPath().push(e.latLng);
  });

  dblclickListenerDibujo = map.addListener("dblclick", function (e) {
    e.stop(); // sin esto el doble click tambien hace zoom-in del mapa
    finalizarDibujoManual();
  });
}

function finalizarDibujoManual() {
  if (clickListenerDibujo) google.maps.event.removeListener(clickListenerDibujo);
  if (dblclickListenerDibujo) google.maps.event.removeListener(dblclickListenerDibujo);
  dibujandoZonaManual = false;
  setClickeableZonasExistentes(true);
  $("#dibujar_zona_manual_btn")
    .removeClass("btn-dark")
    .addClass("btn-outline-dark")
    .html('<i class="mdi mdi-vector-polygon"></i> Dibujar Zona');

  if (!polygonEnProgreso) return;

  const path = polygonEnProgreso.getPath();
  if (path.getLength() < 3) {
    Swal.fire({ icon: "warning", title: "Hacen falta al menos 3 puntos para cerrar una zona" });
    polygonEnProgreso.setMap(null);
    polygonEnProgreso = null;
    return;
  }

  polygonEnProgreso.setOptions({ editable: true, clickable: true });
  agregarZonaManual(polygonEnProgreso);
  polygonEnProgreso = null;
}

function cancelarDibujoManual() {
  if (clickListenerDibujo) google.maps.event.removeListener(clickListenerDibujo);
  if (dblclickListenerDibujo) google.maps.event.removeListener(dblclickListenerDibujo);
  dibujandoZonaManual = false;
  setClickeableZonasExistentes(true);
  if (polygonEnProgreso) {
    polygonEnProgreso.setMap(null);
    polygonEnProgreso = null;
  }
  $("#dibujar_zona_manual_btn")
    .removeClass("btn-dark")
    .addClass("btn-outline-dark")
    .html('<i class="mdi mdi-vector-polygon"></i> Dibujar Zona');
}

function agregarZonaManual(polygon) {
  const path = polygon.getPath();
  const puntos = [];
  for (let i = 0; i < path.getLength(); i++) {
    const p = path.getAt(i);
    puntos.push({ lat: p.lat(), lng: p.lng() });
  }

  if (puntos.length < 3) {
    polygon.setMap(null);
    return;
  }

  manualZonaContador++;
  zonasManuales.push({
    id: "manual-" + manualZonaContador,
    nombre: "Zona Manual " + manualZonaContador,
    color: "#000000",
    poligono: puntos,
    shape: polygon,
  });

  // Se agrega al mismo array que "Ver Todas las Zonas" ya limpia en cada
  // recarga, asi las zonas dibujadas a mano se borran solas junto con el
  // resto de los overlays sin necesidad de un cleanup aparte.
  overlaysTodas.push(polygon);

  // Recalcular vertices si el operador edita el poligono despues de dibujarlo
  // (editable:true) - sin esto la card quedaba con el conteo de cuando se
  // termino de dibujar, no del ajuste final.
  const path2 = polygon.getPath();
  ["set_at", "insert_at", "remove_at"].forEach(function (evento) {
    path2.addListener(evento, function () {
      const nuevosPuntos = [];
      for (let i = 0; i < path2.getLength(); i++) {
        const p = path2.getAt(i);
        nuevosPuntos.push({ lat: p.lat(), lng: p.lng() });
      }
      const zm = zonasManuales.find((z) => z.shape === polygon);
      if (zm) zm.poligono = nuevosPuntos;
      renderCardsAsignacion();
    });
  });

  toast("success", "Zona dibujada", "Se agregó como card abajo, lista para arrastrar a un Recorrido.");
  renderCardsAsignacion();
}

$(document).on("click", "#dibujar_zona_manual_btn", function () {
  if (!map) return;

  // Toggle: si ya estaba dibujando, el mismo boton cancela el modo.
  if (dibujandoZonaManual) {
    cancelarDibujoManual();
    return;
  }

  if (!Array.isArray(selected) || selected.length === 0) {
    Swal.fire({ icon: "warning", title: "Elegí primero uno o más Recorridos" });
    return;
  }

  if (!vistaTodasActiva) {
    // mostrarFormas:false - trae los datos de las zonas (necesarios para
    // que sus cards de conteo/drag&drop sigan funcionando junto a la que se
    // va a dibujar) pero sin pintar las 9-10 formas: el operador quiere un
    // lienzo limpio para dibujar la suya, no que le tapen el mapa.
    // callback encadenado: recien arranca el modo dibujo cuando termina el
    // fetch, para no pisarse con el usuario clickeando antes de tiempo.
    renderTodasLasZonas(iniciarDibujoManual, false);
    return;
  }

  iniciarDibujoManual();
});

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
  if (dibujandoZonaManual) cancelarDibujoManual();
  zonasManuales = [];
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
      actualizarConteosLegend();
      refrescarBotonRedistribuir();
    },
  });
}

// =========================
// Eventos
// =========================

// Click en una fila del panel de zonas => resaltar esa zona en el mapa SIN
// ocultar las demas, y abrir/cerrar su cuerpo (select destino + acciones).
$(document).on("click", "#zonas_accordion .zona-legend-head", function () {
  const idz = Number($(this).data("idzona"));
  if (!idz) return;

  const $item = $(this).closest(".zona-legend-item");
  const $body = $item.find(".zona-legend-body");
  $body.prop("hidden", !$body.prop("hidden"));

  // Si venimos de "Ver Todas las Zonas" / landing, las formas ya estan en el
  // mapa; solo se resalta. Si alguien entro a "Editar forma" antes, se
  // reconstruye la vista de todas primero.
  if (vistaTodasActiva && overlaysTodasPorId[idz]) {
    resaltarZonaEnMapa(idz);
  } else {
    renderTodasLasZonas(function () {
      if (Array.isArray(selected) && selected.length > 0) cargarWaypointsZona();
      resaltarZonaEnMapa(idz);
    });
  }
});

// "Editar forma" del panel => mismo modo de edicion en la vista de todas
// (editable/draggable en su lugar, el resto de las zonas sigue visible).
$(document).on("click", ".btnEditarFormaZona", function (e) {
  e.stopPropagation();
  const idz = Number($(this).data("idzona"));
  if (!idz) return;

  if (vistaTodasActiva && overlaysTodasPorId[idz]) {
    iniciarEdicionZonaEnMapa(idz);
  } else {
    renderTodasLasZonas(function () {
      if (Array.isArray(selected) && selected.length > 0) cargarWaypointsZona();
      iniciarEdicionZonaEnMapa(idz);
    });
  }
});

// Cambio del <select> "Recorrido destino" de una zona (solo en memoria).
$(document).on("change", ".zona-destino-select", function () {
  const idz = Number($(this).data("idzona"));
  const val = String($(this).val() || "");
  if (val === "") {
    delete zonaDestino[idz];
  } else {
    zonaDestino[idz] = val;
  }
  pintarDestinoEnHead(idz);
  invalidarPreviewTraspaso();
  refrescarBotonRedistribuir();
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
  invalidarPreviewTraspaso();
  refrescarBotonRedistribuir();
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
  // Corta cualquier edicion en curso: las formas se van a recrear.
  _zonaEditListeners.forEach((l) => google.maps.event.removeListener(l));
  _zonaEditListeners = [];
  if (_zonaEditSaveTimer) { clearTimeout(_zonaEditSaveTimer); _zonaEditSaveTimer = null; }
  zonaEnEdicionId = null;
  if (_iwZona) { _iwZona.close(); }

  overlaysTodas.forEach((o) => {
    google.maps.event.clearInstanceListeners(o);
    o.setMap(null);
  });
  overlaysTodas = [];
  overlaysTodasPorId = {};
  zonaLegendActivaId = null;
}

// ==========================================================================
// Editar una zona SIN salir de la vista de todas.
// ==========================================================================

// Popup al clickear una zona en el mapa: nombre + boton "Editar zona"
// (o "Terminar edicion" si ya se esta editando esa).
function abrirPopupZona(idZona, latLng) {
  // Pasar a otra zona corta la edicion de la anterior.
  if (zonaEnEdicionId != null && String(zonaEnEdicionId) !== String(idZona)) {
    terminarEdicionZonaEnMapa();
  }

  const zc = zonasCache.find((z) => String(z.id) === String(idZona));
  const nombre = zc && zc.Nombre ? zc.Nombre : "Zona";
  const editando = String(zonaEnEdicionId) === String(idZona);

  if (!_iwZona) _iwZona = new google.maps.InfoWindow();
  _iwZona.setContent(
    '<div style="min-width:150px;font-size:13px;line-height:1.4">' +
      "<b>" + nombre + "</b><br>" +
      (editando
        ? '<span style="color:#0a7d33">Movés vértices y arrastrás la forma. Se guarda solo.</span><br>' +
          '<button class="btn btn-sm btn-outline-secondary mt-1" onclick="terminarEdicionZonaEnMapa()">Terminar edición</button>'
        : '<button class="btn btn-sm btn-primary mt-1" onclick="iniciarEdicionZonaEnMapa(' + Number(idZona) + ')">' +
          '<i class="mdi mdi-pencil"></i> Editar zona</button>') +
    "</div>"
  );
  if (latLng) _iwZona.setPosition(latLng);
  _iwZona.open(map);
  resaltarZonaEnMapa(idZona);
}

function iniciarEdicionZonaEnMapa(idZona) {
  const shape = overlaysTodasPorId[idZona];
  if (!shape) return;

  if (zonaEnEdicionId != null && String(zonaEnEdicionId) !== String(idZona)) {
    terminarEdicionZonaEnMapa();
  }

  const zc = zonasCache.find((z) => String(z.id) === String(idZona));
  const nombre = zc && zc.Nombre ? zc.Nombre : "";
  zonaEnEdicionId = idZona;

  shape.setOptions({ editable: true, draggable: true, fillOpacity: 0.45, strokeWeight: 3, zIndex: 30 });

  const guardar = function () { guardarFormaZonaEnMapa(idZona, nombre, shape); };

  if (typeof shape.getPath === "function") {
    const path = shape.getPath();
    _zonaEditListeners.push(path.addListener("set_at", guardar));
    _zonaEditListeners.push(path.addListener("insert_at", guardar));
    _zonaEditListeners.push(path.addListener("remove_at", guardar));
    _zonaEditListeners.push(shape.addListener("dragend", guardar));
  } else if (typeof shape.getBounds === "function") {
    _zonaEditListeners.push(shape.addListener("bounds_changed", guardar));
    _zonaEditListeners.push(shape.addListener("dragend", guardar));
  }

  abrirPopupZona(idZona); // reabre el popup ya en modo "editando"
}

// Global para el onclick del popup y para el boton del panel.
function terminarEdicionZonaEnMapa() {
  if (_zonaEditSaveTimer) { clearTimeout(_zonaEditSaveTimer); _zonaEditSaveTimer = null; }
  _zonaEditListeners.forEach((l) => google.maps.event.removeListener(l));
  _zonaEditListeners = [];

  const shape = zonaEnEdicionId != null ? overlaysTodasPorId[zonaEnEdicionId] : null;
  if (shape) shape.setOptions({ editable: false, draggable: false });

  zonaEnEdicionId = null;
  if (_iwZona) _iwZona.close();
}

// Guardado con debounce: SubirPoligono para poligonos, Subir para rectangulos.
// Misma logica que showNewPoly/showNewRect pero apuntada a una zona puntual de
// la vista de todas (sin tocar los globals polygon/zona/zonaId).
function guardarFormaZonaEnMapa(idZona, nombre, shape) {
  if (_zonaEditSaveTimer) clearTimeout(_zonaEditSaveTimer);
  _zonaEditSaveTimer = setTimeout(function () {
    let data;
    let nuevoPoli = null;

    if (typeof shape.getPath === "function") {
      const pts = serializePolygonPath(shape).filter(
        (p) => Number.isFinite(p.lat) && Number.isFinite(p.lng)
      );
      if (pts.length < 3) return;
      const bb = computeBBox(pts);
      nuevoPoli = JSON.stringify(pts);
      data = {
        SubirPoligono: 1, idZona: idZona, zona: nombre, Poligono: nuevoPoli,
        LatitudN: bb.LatitudN, LatitudS: bb.LatitudS,
        LongitudE: bb.LongitudE, LongitudO: bb.LongitudO,
        rec: Array.isArray(selected) ? selected : [],
      };
    } else if (typeof shape.getBounds === "function") {
      const b = shape.getBounds();
      if (!b) return;
      const ne = b.getNorthEast(), sw = b.getSouthWest();
      data = {
        Subir: 1, idZona: idZona, zona: nombre,
        nelat: ne.lat(), swlat: sw.lat(), nelng: ne.lng(), swlng: sw.lng(),
        rec: Array.isArray(selected) ? selected : [],
      };
    } else {
      return;
    }

    $.ajax({
      url: "Mapas/php/zonas.php", type: "POST", dataType: "json", data: data,
      success: function () {
        // Actualiza la cache local para que los conteos y el redistribuir usen
        // la forma nueva sin recargar todo.
        const zc = zonasCache.find((z) => String(z.id) === String(idZona));
        if (zc) {
          if (nuevoPoli) zc.Poligono = nuevoPoli;
          if (data.LatitudN !== undefined) {
            zc.LatitudN = data.LatitudN; zc.LatitudS = data.LatitudS;
            zc.LongitudE = data.LongitudE; zc.LongitudO = data.LongitudO;
          }
        }
        actualizarConteosLegend();
        invalidarPreviewTraspaso(); // la forma cambio: la previa quedo vieja
      },
      error: function (x) {
        console.error("Guardar forma de zona", x && x.responseText);
      },
    });
  }, 400);
}

// callback (opcional): se ejecuta recien cuando terminan de dibujarse las
// zonas - sin esto, "Dibujar Zona" disparaba esto en paralelo (fetch async)
// y arrancaba el modo dibujo al toque, antes de que las 9 zonas terminaran
// de renderizarse; el usuario terminaba dibujando "por encima" de zonas que
// recien estaban apareciendo.
// mostrarFormas (default true): "Dibujar Zona" necesita los DATOS de las
// zonas existentes (zonasCache, para que sus cards de conteo/drag&drop
// tambien sigan funcionando) pero no quiere ver las 9-10 formas pintadas
// tapando el lienzo limpio donde va a dibujar la suya - con false se trae
// todo igual pero no se crea ningun Polygon/Rectangle en el mapa ni se
// mueve el zoom para encuadrarlas.
function renderTodasLasZonas(callback, mostrarFormas) {
  if (mostrarFormas === undefined) mostrarFormas = true;
  clearRectangle();
  clearPolygon();
  clearMarkers();
  clearOverlaysTodas();
  if (dibujandoZonaManual) cancelarDibujoManual();
  zonasManuales = []; // los poligonos ya se limpiaron del mapa arriba (overlaysTodas)
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
        if (typeof callback === "function") callback();
        return;
      }

      zonasCache = zonas;

      if (mostrarFormas) {
        const bounds = new google.maps.LatLngBounds();

        zonas.forEach(function (z, index) {
          const color = colorDeZona(z, index);
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

          shape.addListener("click", function (e) {
            abrirPopupZona(z.id, e.latLng);
          });

          shape._zonaId = z.id;
          shape._baseFill = 0.25;
          overlaysTodas.push(shape);
          overlaysTodasPorId[z.id] = shape;
        });

        map.fitBounds(bounds);
        $("#zonas_map_title").html("Zonas google Maps (todas)");
        $("#cantidad").html(zonas.length + " zona(s)");
      }

      renderCardsAsignacion();
      actualizarConteosLegend();
      refrescarBotonRedistribuir();
      if (typeof callback === "function") callback();
    },
  });
}

// renderTodasLasZonas() hace clearMarkers() y NO recarga los waypoints. Cuando
// se repinta el mapa despues de generar/importar/borrar zonas hay que volver a
// traer los pines de los Recorridos elegidos, si hay.
function renderTodasLasZonasConWaypoints() {
  renderTodasLasZonas(function () {
    if (Array.isArray(selected) && selected.length > 0) {
      cargarWaypointsZona();
    }
  });
}

// El bloque de cards drag&drop "zonas -> Recorridos en alta" debajo del mapa se
// saco (2026-09-08): la redistribucion ahora se hace con el <select> de destino
// por zona + el boton "Redistribuir por zonas". renderCardsAsignacion() queda
// como no-op para no tocar sus ~5 puntos de llamada.
function renderCardsAsignacion() {}

$(document).on("click", "#ver_todas_zonas", function () {
  renderTodasLasZonasConWaypoints();
});

// =========================
// Redistribuir por zonas: cada zona con destino manda sus waypoints al
// Recorrido elegido, todo de una. Reusa la accion CambiarRecorridos (ya
// resuelve contencion en poligono real + cambiarRecorrido() con webhook).
// =========================
// Paso 1: previsualizar (no toca nada). Paso 2: "Confirmar traspaso".
$(document).on("click", "#btn_redistribuir_zonas", function () {
  previsualizarRedistribucion();
});

$(document).on("click", "#btn_confirmar_traspaso", function () {
  confirmarTraspaso();
});

$(document).on("click", "#btn_generar_zonas", function () {
  generarZonasBalanceadas($("#gen_zonas_n").val());
});

// El plan calculado en la previsualizacion, a la espera de "Confirmar traspaso".
let _planTraspaso = null;

// Invalida la previa (cambio de recorridos, de destinos, o se genero/edito una
// zona): esconde el boton de confirmar y limpia el resumen.
function invalidarPreviewTraspaso() {
  _planTraspaso = null;
  $("#btn_confirmar_traspaso").prop("hidden", true);
  $("#redistribuir_resumen").empty();
}

function previsualizarRedistribucion() {
  redistribuirPorZonas();
}

function redistribuirPorZonas() {
  if (redistribuyendo) return;

  if (!Array.isArray(selected) || selected.length === 0) {
    Swal.fire({ icon: "warning", title: "Elegí uno o más Recorridos primero" });
    return;
  }
  if (Object.keys(zonaDestino).length === 0) {
    Swal.fire({ icon: "warning", title: "Asigná un Recorrido destino a alguna zona" });
    return;
  }
  if (waypointsData.length === 0) {
    Swal.fire({ icon: "warning", title: "No hay waypoints cargados para los Recorridos elegidos" });
    return;
  }

  // Poligonos parseados una sola vez.
  const polys = zonasCache
    .map((z, i) => ({ z: z, pts: poligonoDeCache(z), color: colorDeZona(z, i) }))
    .filter((o) => o.pts);

  // Preview: a que zona cae cada waypoint no movido, recoloreando el pin.
  const porZona = {};
  let sinZona = 0;

  waypointsData.forEach(function (w) {
    if (w.movido) return;
    const hit = polys.find((o) => pointInPolygon({ lat: w.lat, lng: w.lng }, o.pts));
    if (!hit) { sinZona++; return; }
    const id = hit.z.id;
    if (!zonaDestino[id]) return; // cae en zona sin destino: no se toca
    if (!porZona[id]) {
      const rec = recorridoPorNumero(zonaDestino[id]);
      porZona[id] = {
        nombre: hit.z.Nombre || "Zona",
        color: hit.color,
        destinoNum: String(zonaDestino[id]),
        destinoNombre: rec ? rec.Nombre : "",
        destinoColor: rec ? normalizarColor(rec.Color) || "#666666" : "#666666",
        destinoIniciado: !!(rec && rec.Iniciado),
        cant: 0,
      };
    }
    porZona[id].cant++;
    w.marker.setIcon(pinSymbol(String(hit.color).replace("#", "")));
  });

  const ids = Object.keys(porZona).filter((id) => porZona[id].cant > 0);
  if (ids.length === 0) {
    Swal.fire({
      icon: "info",
      title: "Nada para mover",
      text: "Ningún waypoint cae en una zona con Recorrido destino asignado.",
    });
    cargarWaypointsZona(); // restaura colores originales de los pines
    invalidarPreviewTraspaso();
    return;
  }

  const total = ids.reduce((acc, id) => acc + porZona[id].cant, 0);
  const iniciados = ids
    .filter((id) => porZona[id].destinoIniciado)
    .map((id) => "Rec " + porZona[id].destinoNum);

  const resumenHtml =
    '<div style="text-align:left">' +
    ids
      .map(function (id) {
        const p = porZona[id];
        return (
          '<div class="rd-linea"><span class="rd-swatch" style="background:' + p.color + '"></span>' +
          "<b>" + p.nombre + "</b> → Rec " + p.destinoNum +
          (p.destinoNombre ? " (" + p.destinoNombre + ")" : "") +
          (p.destinoIniciado ? ' <span style="color:#d9822b">⚠ ya inició</span>' : "") +
          ": <b>" + p.cant + "</b></div>"
        );
      })
      .join("") +
    (sinZona
      ? '<div class="rd-linea text-muted">Fuera de toda zona (no se mueven): <b>' + sinZona + "</b></div>"
      : "") +
    (iniciados.length
      ? '<div class="rd-linea" style="color:#d9822b;font-weight:600;margin-top:.3rem">' +
        "⚠ " + iniciados.join(", ") + " ya arrancaron el recorrido." +
        "</div>"
      : "") +
    '<div class="text-muted mt-1" style="font-size:.7rem">Previsualización — todavía no se movió nada.</div>' +
    "</div>";

  $("#redistribuir_resumen").html(resumenHtml);

  _planTraspaso = {
    tareas: ids.map((id) => ({ idZona: id, dest: porZona[id] })),
    total: total,
    iniciados: iniciados,
  };
  $("#btn_confirmar_traspaso")
    .prop("hidden", false)
    .html('<i class="mdi mdi-check-bold"></i> Confirmar traspaso de ' + total + " servicio(s)");
}

function confirmarTraspaso() {
  if (redistribuyendo || !_planTraspaso) return;
  const plan = _planTraspaso;

  confirmarSiFaltanGeo(function () {
    const seguir = function () {
      ejecutarRedistribucion(plan.tareas);
    };
    if (plan.iniciados.length) {
      Swal.fire({
        title: "Ojo: recorrido(s) ya iniciado(s)",
        html:
          "<p>" + plan.iniciados.join(", ") +
          " ya arrancaron. Los paquetes que les mandes pueden no aparecerle al chofer hasta que refresque la app.</p>" +
          "<p>¿Traspasar igual los " + plan.total + " servicio(s)?</p>",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Traspasar igual",
        cancelButtonText: "Cancelar",
      }).then(function (r) {
        if (r.isConfirmed) seguir();
      });
    } else {
      seguir();
    }
  });
}

function ejecutarRedistribucion(tareas) {
  redistribuyendo = true;
  refrescarBotonRedistribuir();

  let totalMovidos = 0;
  const movidosTodos = [];

  (function siguiente(i) {
    if (i >= tareas.length) {
      redistribuyendo = false;

      const TOL = 0.0001;
      movidosTodos.forEach(function (m) {
        const w = waypointsData.find(
          (x) => !x.movido && Math.abs(x.lat - m.lat) < TOL && Math.abs(x.lng - m.lng) < TOL
        );
        if (w) {
          w.marker.setIcon(pinSymbol(String(m.color).replace("#", "")));
          w.movido = true;
        }
      });

      toast("success", "Traspaso listo", "Se movieron " + totalMovidos + " servicio(s).");
      _planTraspaso = null;
      $("#btn_confirmar_traspaso").prop("hidden", true);
      $("#redistribuir_resumen").html(
        '<div class="text-success small">✓ Traspaso confirmado: ' + totalMovidos + " servicio(s) movidos.</div>"
      );
      actualizarConteosLegend();
      refrescarBotonRedistribuir();
      return;
    }

    const t = tareas[i];
    const destino = String(t.dest.destinoNum);
    // Excluir el propio destino de la lista para no re-tocar lo que ya estaba ahi.
    const recorridosParaEsta = selected.filter((r) => String(r) !== destino);

    if (recorridosParaEsta.length === 0) {
      siguiente(i + 1);
      return;
    }

    $.ajax({
      url: "Mapas/php/zonas.php",
      type: "POST",
      dataType: "json",
      data: {
        CambiarRecorridos: 1,
        Recnew: destino,
        Recorridos: recorridosParaEsta,
        idZona: t.idZona,
      },
      success: function (j) {
        if (j && j.success == 1) {
          totalMovidos += Number(j.cuenta || 0);
          (j.movidos || []).forEach(function (p) {
            movidosTodos.push({ lat: p.lat, lng: p.lng, color: t.dest.destinoColor });
          });
        } else {
          toast("error", "Zona " + t.dest.nombre, (j && j.error) || "No se pudo mover.");
        }
        siguiente(i + 1);
      },
      error: function () {
        toast("error", "Zona " + t.dest.nombre, "Error de servidor.");
        siguiente(i + 1);
      },
    });
  })(0);
}

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
          delete zonaDestino[idZona];
          cargarZonasAccordion();

          // Si borraste la zona que estabas viendo, limpio mapa
          if (zonaId === Number(idZona)) {
            zona = null;
            zonaId = null;
            clearRectangle();
            clearPolygon();
            clearMarkers();
          }
          // Refrescar la capa de "todas las zonas" para que la borrada
          // desaparezca del mapa sin recargar la pagina.
          if (map) renderTodasLasZonasConWaypoints();
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
        if (map) renderTodasLasZonasConWaypoints();
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
