let map;
let markers = [];
let routePaths = [];
let markerCoordCounts = new Map();
let activeClusterer = null; // MarkerClusterer activo, se limpia en clearMap()
const routeColors = ["#007bff", "#28a745", "#ffc107", "#dc3545", "#6f42c1", "#20c997"];

// Cuando dos o mas paradas comparten coordenadas exactas (o casi), sus
// markers quedan tapados unos con otros. Este offset los reparte en un
// abanico chico alrededor del punto real (el primero de cada coordenada
// queda sin tocar) para que todos sean visibles y clickeables.
const COINCIDENT_PRECISION = 5; // ~1.1 m de precision para considerar "mismo punto"
const COINCIDENT_OFFSET_METERS = 6; // separacion entre pines coincidentes, por anillo

function offsetCoincidentMarker(lat, lng) {
  const key = lat.toFixed(COINCIDENT_PRECISION) + "," + lng.toFixed(COINCIDENT_PRECISION);
  const count = markerCoordCounts.get(key) || 0;
  markerCoordCounts.set(key, count + 1);
  if (count === 0) return { lat, lng };

  // Anillo de 8 posiciones (45° cada una); si hay mas de 8 coincidentes en
  // el mismo punto (ej. un deposito con varios clientes geocodeados igual),
  // pasa a un segundo anillo de radio doble, y asi sucesivamente.
  const ring = Math.floor((count - 1) / 8) + 1;
  const angle = (((count - 1) % 8) * 45 * Math.PI) / 180;
  const radiusM = COINCIDENT_OFFSET_METERS * ring;

  const metersPerDegLat = 111320;
  const metersPerDegLng = 111320 * Math.cos((lat * Math.PI) / 180);
  return {
    lat: lat + (radiusM * Math.sin(angle)) / metersPerDegLat,
    lng: lng + (radiusM * Math.cos(angle)) / metersPerDegLng,
  };
}

$(document).ready(function () {
  $.ajax({
    data: { BuscarRecorridos: 1 },
    type: "POST",
    url: "Planificador/php/planificador.php",
    success: function (response) {
      if (response.status === "success") {
        const options = response.data.map((r) => {
          return `<option value="${r.Numero}">${r.Numero} | ${r.Nombre} ${r.Estado}</option>`;
        });
        $("#select_rec_mapa").html(options.join(""));
      } else {
        console.warn("No se recibieron datos de recorridos.");
      }
    },
  });
});

function cargarClientes() {
  const recorrido = document.getElementById("select_rec_mapa").value;

  fetch("Planificador/php/planificador.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `BuscarWaypoints=1&Recorrido=${encodeURIComponent(recorrido)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        const coordenadas = data.data.map((p) => `${p.lat},${p.lng}`).join(";");
        document.getElementById("waypoints").value = coordenadas;
        window.waypointsData = data.data.map((p) => ({
          lat: p.lat,
          lng: p.lng,
          nombrecliente: p.nombrecliente,
          CodigoSeguimiento: p.CodigoSeguimiento,
          horarioSolicitado: p.horarioSolicitado,
        }));

        $("#calcular_ruta").prop("disabled", false);

        toast("success", "Coordenadas correctas", `Se cargaron ${data.data.length} coordenadas desde Clientes.`);
      } else if (data.status === "error" && data.faltantes) {
        const lista = data.faltantes.map((item) => `<li>${item}</li>`).join("");
        Swal.fire({
          icon: "warning",
          title: "Coordenadas incompletas",
          html: `${data.message}<ul style="text-align:left">${lista}</ul>`,
        });
      } else {
        Swal.fire({
          icon: "warning",
          title: "Coordenadas incompletas",
          text: data.message || "No se pudieron cargar los datos.",
        });
      }
    })
    .catch((err) => {
      console.error("Error al cargar coordenadas:", err);
      Swal.fire({
        icon: "error",
        title: "Error de conexión",
        text: "No se pudieron obtener los puntos intermedios.",
      });
    });
}

function calcularDistancia(lat1, lng1, lat2, lng2) {
  const R = 6371;
  const dLat = ((lat2 - lat1) * Math.PI) / 180;
  const dLng = ((lng2 - lng1) * Math.PI) / 180;
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos((lat1 * Math.PI) / 180) *
      Math.cos((lat2 * Math.PI) / 180) *
      Math.sin(dLng / 2) *
      Math.sin(dLng / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

const VELOCIDAD_PROMEDIO_KMH = 25;

function horaAMinutos(hhmm) {
  if (!hhmm) return null;
  const partes = String(hhmm).split(":");
  if (partes.length < 2) return null;
  const h = parseInt(partes[0], 10);
  const m = parseInt(partes[1], 10);
  if (isNaN(h) || isNaN(m)) return null;
  return h * 60 + m;
}

// Busca el horario solicitado de un punto por coordenada, con la misma
// tolerancia que findWaypointData() en getRoute.php - los puntos que se
// ordenan vienen del textarea (solo lat,lng, sin metadata), asi que hace
// falta volver a matchear contra waypointsData para recuperar el horario.
function buscarHorarioPorCoordenada(lat, lng, waypointsData, tolerancia = 0.0001) {
  if (!waypointsData) return null;
  for (const item of waypointsData) {
    if (Math.abs(item.lat - lat) < tolerancia && Math.abs(item.lng - lng) < tolerancia) {
      return item.horarioSolicitado || null;
    }
  }
  return null;
}

// Nearest-neighbor por cercania. Con horaSalidaMinutos/waypointsData, aplica
// el mismo criterio de urgencia que ya usa ordenarPorCercania() en
// Logistica/Mapas/php/orden_automatico.php: las paradas con horario
// solicitado que ya se cumplio (o esta por cumplirse en menos de 60 min,
// segun la hora estimada de llegada) se priorizan por sobre la mas cercana
// - no obliga a cumplirlo siempre, pero evita que queden muy desacomodadas
// en la ruta. Solo afecta el ORDEN dentro de cada vehiculo/cluster, no a
// que vehiculo se asigna cada parada (eso lo decide el K-means de
// getRoute.php, sin cambios).
function ordenarWaypointsPorCercania(
  puntos,
  origen = { lat: -31.444994776141503, lng: -64.1779408896999 },
  horaSalidaMinutos = null,
  waypointsData = null,
  tiempoPorParadaMin = 5
) {
  const ordenado = [];
  let actual = origen;
  const restante = [...puntos];
  let tiempoAcumuladoMin = 0;

  while (restante.length > 0) {
    let mejorScore = Infinity;
    let siguienteIndex = 0;
    let mejorDist = 0;

    for (let i = 0; i < restante.length; i++) {
      const dist = calcularDistancia(actual.lat, actual.lng, restante[i].lat, restante[i].lng);

      let bonus = 0;
      if (horaSalidaMinutos !== null) {
        const horarioSolicitado =
          restante[i].horarioSolicitado !== undefined
            ? restante[i].horarioSolicitado
            : buscarHorarioPorCoordenada(restante[i].lat, restante[i].lng, waypointsData);
        const minSolicitado = horaAMinutos(horarioSolicitado);
        if (minSolicitado !== null) {
          const llegadaEstimadaMin = tiempoAcumuladoMin + (dist / VELOCIDAD_PROMEDIO_KMH) * 60;
          const urgenciaMin = minSolicitado - (horaSalidaMinutos + llegadaEstimadaMin);
          if (urgenciaMin <= 60) {
            bonus = (60 - urgenciaMin) / 2;
          }
        }
      }

      const score = dist - bonus;
      if (score < mejorScore) {
        mejorScore = score;
        siguienteIndex = i;
        mejorDist = dist;
      }
    }

    const siguiente = restante.splice(siguienteIndex, 1)[0];
    ordenado.push(siguiente);
    actual = siguiente;
    tiempoAcumuladoMin += (mejorDist / VELOCIDAD_PROMEDIO_KMH) * 60 + tiempoPorParadaMin;
  }

  return ordenado;
}

window.onload = () => {
  initMap();

  document.getElementById("routeForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const tiempoPorParada = parseInt(document.getElementById("stopTimes").value.trim()) || 600;
    const startTime = document.getElementById("startTime").value;
    const horaSalidaMinutos = horaAMinutos(startTime);

    const waypoints = ordenarWaypointsPorCercania(
      parseMultipleLatLng(document.getElementById("waypoints").value),
      undefined,
      horaSalidaMinutos,
      window.waypointsData,
      tiempoPorParada / 60,
    );
    const stopTimes = waypoints.map(() => tiempoPorParada);

    const driversCount = parseInt(document.getElementById("driversCount").value);

    const maxKmInputs = document.querySelectorAll("input[name='maxKm[]']");
    let maxKm = null;
    if (maxKmInputs.length > 0) {
      maxKm = Array.from(maxKmInputs).map((input) => {
        const val = parseFloat(input.value);
        return isNaN(val) ? null : val;
      });
    }

    const maxStopsInputs = document.querySelectorAll("input[name='maxStops[]']");
    let maxStops = null;
    if (maxStopsInputs.length > 0) {
      maxStops = Array.from(maxStopsInputs).map((input) => {
        const val = parseInt(input.value);
        return isNaN(val) ? null : val;
      });
    }

    const maxMinutes = parseFloat(document.getElementById("maxMinutes").value);

    const payload = {
      waypoints,
      stopTimes,
      driversCount,
      maxKm,
      maxStops,
      maxMinutes: isNaN(maxMinutes) ? null : maxMinutes,
      startTime,
      waypointsData: window.waypointsData,
    };

    document.getElementById("summary").innerHTML = "";
    ocultarLeyenda();

    Swal.fire({
      title: "Diseñando rutas...",
      html: "Agrupando paradas por vehículo y consultando tráfico en tiempo real con Google.",
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => Swal.showLoading(),
    });

    try {
      const response = await fetch("Planificador/php/getRoute.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      Swal.close();

      let textResponse = await response.text();
      let result;
      try {
        result = JSON.parse(textResponse);
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Error en la respuesta",
          text: "La respuesta del servidor no fue un JSON válido.",
        });
        return;
      }

      const warnings = result.data?.warnings || [];
      let warningsHTML = "";

      if (warnings.length > 0) {
        warningsHTML =
          `<h5 class="text-warning">Advertencias:</h5><ul>` +
          warnings.map((w) => `<li style="color:orange">${w.replace(/\n/g, "<br>")}</li>`).join("") +
          `</ul>`;

        if (warnings.length === 1) {
          toast("warning", "Advertencia", warnings[0].replace(/\n/g, " "));
        }

        document.getElementById("summary").innerHTML = warningsHTML;
      } else {
        document.getElementById("summary").innerHTML = "";
      }

      if (
        (result.status === "success" || result.status === "partial_success") &&
        result.data.routes.length > 0
      ) {
        window.ultimaRespuestaDeRuta = result.data;

        clearMap();
        const bounds = new google.maps.LatLngBounds();
        result.data.routes.forEach((route, index) => drawRoute(route, index, bounds));
        map.fitBounds(bounds);

        // Clustering real (agrupado por pixeles en pantalla, se reagrupa
        // solo al hacer zoom - libreria oficial de Google) sobre TODOS los
        // markers de todas las rutas. El offset de offsetCoincidentMarker()
        // sigue haciendo falta aparte para paradas con la MISMA coordenada
        // exacta, que clusterizar no separa por mas zoom que se haga.
        if (window.markerClusterer && markers.length > 0) {
          activeClusterer = new markerClusterer.MarkerClusterer({ map, markers });
        }

        renderResumenEstiloDashboard(result.data.summary);
        renderLeyendaColores(result.data.summary);

        Swal.fire({
          icon: "success",
          title: "Rutas generadas",
          html: `
            Se calcularon <strong>${result.data.routes.length}</strong> rutas válidas.
            ${warnings.length > 1 ? warningsHTML : ""}
          `,
        });
      } else if (result.status === "partial_success" && warnings.length > 1) {
        Swal.fire({
          icon: "warning",
          title: "Advertencias en la planificación",
          html: warningsHTML,
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Error en la planificación",
          text: result.message || "No se pudieron generar rutas.",
        });
      }
    } catch (error) {
      Swal.close();
      console.error("Error al contactar el backend:", error);
      Swal.fire({
        icon: "error",
        title: "Fallo en la conexión",
        text: "No se pudo contactar el servidor o la respuesta fue inválida.",
      });
    }
  });
};

function renderLeyendaColores(resumen) {
  const legend = document.getElementById("mapLegend");
  if (!legend) return;

  if (!resumen || resumen.length === 0) {
    ocultarLeyenda();
    return;
  }

  legend.innerHTML = resumen
    .map(
      (r) => `
    <span class="d-inline-flex align-items-center gap-1 small text-muted">
      <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${r.color};"></span>
      Vehículo ${r.routeIndex}
    </span>`
    )
    .join("");
  legend.classList.remove("d-none");
}

function ocultarLeyenda() {
  const legend = document.getElementById("mapLegend");
  if (!legend) return;
  legend.innerHTML = "";
  legend.classList.add("d-none");
}

function initMap() {
  map = new google.maps.Map(document.getElementById("map"), {
    center: { lat: -31.4201, lng: -64.1888 },
    zoom: 12,
  });
}

function clearMap() {
  markers.forEach((marker) => marker.setMap(null));
  markers = [];
  routePaths.forEach((path) => path.setMap(null));
  routePaths = [];
  markerCoordCounts.clear();
  if (activeClusterer) {
    activeClusterer.clearMarkers();
    activeClusterer = null;
  }
}

function parseMultipleLatLng(value) {
  return value
    .split(";")
    .map((coord) => {
      const parts = coord.split(",");
      if (parts.length !== 2) return null;
      const lat = parseFloat(parts[0].trim());
      const lng = parseFloat(parts[1].trim());
      if (isNaN(lat) || isNaN(lng)) return null;
      return { lat, lng };
    })
    .filter((coord) => coord !== null);
}

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

function drawRoute(route, index, bounds) {
  if (!route.legs || route.legs.length === 0) return;

  const polylineData = route.polyline?.encodedPolyline;
  if (!polylineData) return;

  const path = decodePolyline(polylineData);
  if (path.length === 0) return;

  const polyline = new google.maps.Polyline({
    path,
    geodesic: true,
    strokeColor: routeColors[index % routeColors.length],
    strokeOpacity: 1.0,
    strokeWeight: 4,
  });
  polyline.setMap(map);
  routePaths.push(polyline);

  route.legs.forEach((leg, i) => {
    const latLng = leg.endLocation?.latLng;
    if (!latLng || typeof latLng.latitude !== "number" || typeof latLng.longitude !== "number") return;

    const lat = latLng.latitude;
    const lng = latLng.longitude;

    const cliente = route?.waypointsData?.[i]?.nombrecliente || "Cliente";
    const seguimiento = route?.waypointsData?.[i]?.CodigoSeguimiento || "Sin código";

    const info = new google.maps.InfoWindow({
      content: `<em>N° Orden: ${i + 1}</em><br><strong>${cliente}</strong><br>Código: ${seguimiento}<br>`,
    });

    // Si esta parada comparte coordenadas con otra ya dibujada (misma o
    // distinta ruta), se separa visualmente en un abanico - la polyline
    // sigue la geometria real, solo se offsetea el pin.
    const pos = offsetCoincidentMarker(lat, lng);

    const marker = new google.maps.Marker({
      position: pos,
      label: `${i + 1}`,
      map,
      icon: {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 12,
        fillColor: routeColors[index % routeColors.length],
        fillOpacity: 1,
        strokeColor: "#000",
        strokeWeight: 1,
      },
    });

    marker.addListener("mouseover", () => info.open(map, marker));
    marker.addListener("mouseout", () => info.close());

    markers.push(marker);
    bounds.extend(marker.getPosition());
  });
}

document.getElementById("driversCount").addEventListener("change", function () {
  const cantidad = parseInt(this.value);
  const container = document.getElementById("maxKmContainer");
  container.innerHTML = "";

  for (let i = 1; i <= cantidad; i++) {
    const card = document.createElement("div");
    card.className = "border rounded px-2 py-2";
    card.style.minWidth = "170px";

    card.innerHTML = `
      <div class="d-flex align-items-center gap-1 mb-2 text-muted">
        <i class="mdi mdi-truck-outline"></i>
        <strong class="text-body">Vehículo ${i}</strong>
      </div>
      <div class="input-group input-group-sm mb-1">
        <span class="input-group-text" title="Máximo de kilómetros">km</span>
        <input type="number" min="0" step="0.1" class="form-control" name="maxKm[]" placeholder="Sin límite">
      </div>
      <div class="input-group input-group-sm">
        <span class="input-group-text" title="Máximo de paradas">paradas</span>
        <input type="number" min="0" step="1" class="form-control" name="maxStops[]" placeholder="Sin límite">
      </div>
    `;

    container.appendChild(card);
  }
});

document.getElementById("driversCount").dispatchEvent(new Event("change"));

function renderResumenEstiloDashboard(resumen) {
  const contenedor = document.getElementById("contenedorRutasResumen");
  const tarjeta = document.getElementById("rutasResumen");
  if (!contenedor) return;

  contenedor.innerHTML = "";
  window.asignaciones = {}; // NumerodeOrden -> { routeIndex, nombre, zona }
  actualizarBotonGuardar();

  if (tarjeta) tarjeta.classList.toggle("d-none", !resumen || resumen.length === 0);
  if (!resumen || resumen.length === 0) return;

  resumen.forEach((ruta) => {
    const color = ruta.color;
    const index = ruta.routeIndex - 1;

    const html = `
  <div class="ruta-drag-card border rounded p-2 mb-2" id="ruta-drag-${index}" data-index="${index}" draggable="true" style="cursor: grab; border-left: 4px solid ${color} !important;">
    <div class="d-flex justify-content-between align-items-center">
      <strong>Ruta ${ruta.routeIndex}</strong>
      <span class="text-muted small">${ruta.distance_km} km</span>
    </div>
    <div class="text-muted" style="font-size: .8rem;">
      ${ruta.waypoints_count} puntos • ${ruta.estimated_time} hs
    </div>
    <div class="mt-1">
      <span class="badge bg-light text-muted border ruta-drag-hint"><i class="mdi mdi-cursor-move"></i> Arrastrar a un chofer</span>
    </div>
  </div>
`;
    contenedor.innerHTML += html;
  });

  cargarChoferesDisponibles();
}

function cargarChoferesDisponibles() {
  const contenedor = document.getElementById("contenedorChoferes");
  if (!contenedor) return;

  contenedor.innerHTML = `<div class="col-12 text-muted small"><i class="mdi mdi-dots-circle mdi-spin"></i> Buscando choferes disponibles...</div>`;

  $.ajax({
    url: "Planificador/php/planificador.php",
    method: "POST",
    dataType: "json",
    data: { BuscarOrdenesDisponibles: 1 },
    success: function (json) {
      contenedor.innerHTML = "";

      if (json.status !== "success" || !json.data || json.data.length === 0) {
        contenedor.innerHTML = `<div class="col-12 text-muted small">No hay Ordenes de Salida cargadas sin ruta todavía. Cargalas primero en <a href="Ordenes.php">Ordenes de Salida</a>.</div>`;
        return;
      }

      json.data.forEach((orden) => {
        const col = document.createElement("div");
        col.className = "col-md-6";
        col.innerHTML = `
          <div class="chofer-drop-card border rounded p-2" id="chofer-${orden.NumerodeOrden}"
               data-orden="${orden.NumerodeOrden}" data-recorrido="${orden.Recorrido}" data-patente="${orden.Patente}"
               style="filter: grayscale(100%); opacity: .65; transition: all .2s;">
            <div class="d-flex align-items-center gap-2">
              <i class="mdi mdi-account-circle font-22 text-muted"></i>
              <div class="flex-grow-1">
                <strong>${orden.NombreChofer || "Sin nombre"}</strong>
                <div class="text-muted small">${orden.Patente || ""}${orden.Vehiculo ? " · " + orden.Vehiculo : ""} · Orden #${orden.NumerodeOrden}</div>
              </div>
            </div>
            <div class="chofer-ruta-asignada d-none mt-2"></div>
          </div>
        `;
        contenedor.appendChild(col);
      });

      activarDragAndDrop();
    },
    error: function () {
      contenedor.innerHTML = `<div class="col-12 text-danger small">No se pudo cargar la lista de choferes.</div>`;
    },
  });
}

function activarDragAndDrop() {
  document.querySelectorAll(".ruta-drag-card").forEach((card) => {
    card.addEventListener("dragstart", function (e) {
      if (card.classList.contains("ruta-usada")) {
        e.preventDefault();
        return;
      }
      e.dataTransfer.setData("text/plain", card.getAttribute("data-index"));
      e.dataTransfer.effectAllowed = "move";
    });
  });

  document.querySelectorAll(".chofer-drop-card").forEach((card) => {
    card.addEventListener("dragover", function (e) {
      e.preventDefault();
      e.dataTransfer.dropEffect = "move";
      card.classList.add("chofer-dragover");
    });
    card.addEventListener("dragleave", function () {
      card.classList.remove("chofer-dragover");
    });
    card.addEventListener("drop", function (e) {
      e.preventDefault();
      card.classList.remove("chofer-dragover");
      const routeIndex = parseInt(e.dataTransfer.getData("text/plain"));
      if (isNaN(routeIndex)) return;
      asignarRutaAChofer(routeIndex, card);
    });
  });
}

function asignarRutaAChofer(routeIndex, choferCard) {
  const resumen = window.ultimaRespuestaDeRuta?.summary?.[routeIndex];
  if (!resumen) return;

  const numerodeOrden = choferCard.getAttribute("data-orden");

  // Si esa ruta ya estaba en otro chofer, la liberamos primero (una ruta = un chofer).
  Object.keys(window.asignaciones || {}).forEach((ordenKey) => {
    if (window.asignaciones[ordenKey].routeIndex === routeIndex && ordenKey !== numerodeOrden) {
      delete window.asignaciones[ordenKey];
      const otraTarjeta = document.getElementById(`chofer-${ordenKey}`);
      if (otraTarjeta) resetearTarjetaChofer(otraTarjeta);
    }
  });

  window.asignaciones[numerodeOrden] = {
    routeIndex,
    nombre: `Ruta ${resumen.routeIndex}`,
    zona: "",
  };

  choferCard.style.filter = "none";
  choferCard.style.opacity = "1";
  choferCard.style.borderColor = resumen.color;
  choferCard.style.background = hexConAlpha(resumen.color, 0.06);

  const detalle = choferCard.querySelector(".chofer-ruta-asignada");
  if (detalle) {
    detalle.classList.remove("d-none");
    detalle.innerHTML = `
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:${resumen.color};" class="me-1"></span><strong>Ruta ${resumen.routeIndex}</strong></span>
        <button type="button" class="btn btn-sm btn-link text-danger p-0 quitarAsignacionBtn" data-orden="${numerodeOrden}" title="Quitar"><i class="mdi mdi-close"></i></button>
      </div>
      <div class="text-muted small mb-2">${resumen.distance_km} km • ${resumen.waypoints_count} puntos • ${resumen.estimated_time} hs</div>
      <input type="text" class="form-control form-control-sm mb-1 inputNombreAsignacion" data-orden="${numerodeOrden}" placeholder="Nombre de la ruta" value="Ruta ${resumen.routeIndex}">
      <input type="text" class="form-control form-control-sm inputZonaAsignacion" data-orden="${numerodeOrden}" placeholder="Zona de reparto (opcional)">
    `;
  }

  const rutaCard = document.getElementById(`ruta-drag-${routeIndex}`);
  if (rutaCard) {
    rutaCard.classList.add("ruta-usada");
    rutaCard.style.opacity = ".4";
    rutaCard.style.cursor = "default";
    const hint = rutaCard.querySelector(".badge");
    if (hint) hint.outerHTML = `<span class="badge bg-success-subtle text-success border border-success"><i class="mdi mdi-check"></i> Asignada</span>`;
  }

  actualizarBotonGuardar();
}

function resetearTarjetaChofer(card) {
  card.style.filter = "grayscale(100%)";
  card.style.opacity = ".65";
  card.style.borderColor = "";
  card.style.background = "";
  const detalle = card.querySelector(".chofer-ruta-asignada");
  if (detalle) {
    detalle.classList.add("d-none");
    detalle.innerHTML = "";
  }
}

function hexConAlpha(hex, alpha) {
  const h = (hex || "#666666").replace("#", "");
  const r = parseInt(h.substring(0, 2), 16) || 0;
  const g = parseInt(h.substring(2, 4), 16) || 0;
  const b = parseInt(h.substring(4, 6), 16) || 0;
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function actualizarBotonGuardar() {
  const btn = document.getElementById("btn_guardar_asignaciones");
  if (!btn) return;
  const cantidad = Object.keys(window.asignaciones || {}).length;
  btn.disabled = cantidad === 0;
  btn.innerHTML =
    cantidad > 0
      ? `<i class="mdi mdi-content-save-check-outline me-1"></i>Guardar ${cantidad} asignación${cantidad > 1 ? "es" : ""}`
      : `<i class="mdi mdi-content-save-check-outline me-1"></i>Guardar asignaciones`;
}

document.addEventListener("click", function (e) {
  const quitarBtn = e.target.closest(".quitarAsignacionBtn");
  if (!quitarBtn) return;

  const numerodeOrden = quitarBtn.getAttribute("data-orden");
  const asignacion = window.asignaciones?.[numerodeOrden];
  if (!asignacion) return;

  delete window.asignaciones[numerodeOrden];

  const choferCard = document.getElementById(`chofer-${numerodeOrden}`);
  if (choferCard) resetearTarjetaChofer(choferCard);

  const rutaCard = document.getElementById(`ruta-drag-${asignacion.routeIndex}`);
  if (rutaCard) {
    rutaCard.classList.remove("ruta-usada");
    rutaCard.style.opacity = "1";
    rutaCard.style.cursor = "grab";
    const hint = rutaCard.querySelector(".badge");
    if (hint) hint.outerHTML = `<span class="badge bg-light text-muted border ruta-drag-hint"><i class="mdi mdi-cursor-move"></i> Arrastrar a un chofer</span>`;
  }

  actualizarBotonGuardar();
});

document.addEventListener("input", function (e) {
  if (e.target.classList.contains("inputNombreAsignacion")) {
    const orden = e.target.getAttribute("data-orden");
    if (window.asignaciones?.[orden]) window.asignaciones[orden].nombre = e.target.value;
  }
  if (e.target.classList.contains("inputZonaAsignacion")) {
    const orden = e.target.getAttribute("data-orden");
    if (window.asignaciones?.[orden]) window.asignaciones[orden].zona = e.target.value;
  }
});

document.getElementById("btn_guardar_asignaciones")?.addEventListener("click", async function () {
  const entradas = Object.entries(window.asignaciones || {});
  if (entradas.length === 0) return;

  const confirmacion = await Swal.fire({
    icon: "info",
    title: "¿Confirmar asignaciones?",
    html: `Se van a asignar <strong>${entradas.length}</strong> ruta${entradas.length > 1 ? "s" : ""} a su${entradas.length > 1 ? "s" : ""} chofer${entradas.length > 1 ? "es" : ""}.`,
    showCancelButton: true,
    confirmButtonText: "✅ Confirmar",
    cancelButtonText: "❌ Cancelar",
    reverseButtons: true,
  });
  if (!confirmacion.isConfirmed) return;

  Swal.fire({
    title: "Guardando asignaciones...",
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => Swal.showLoading(),
  });

  const recorrido_actual = document.getElementById("select_rec_mapa").value;
  const errores = [];

  for (const [numerodeOrden, asign] of entradas) {
    const choferCard = document.getElementById(`chofer-${numerodeOrden}`);
    const recorrido_destino = choferCard ? choferCard.getAttribute("data-recorrido") : null;
    const rutaSeleccionada = window.ultimaRespuestaDeRuta?.routes?.[asign.routeIndex];
    const resumenSeleccionado = window.ultimaRespuestaDeRuta?.summary?.[asign.routeIndex];

    if (!rutaSeleccionada || !resumenSeleccionado || !recorrido_destino) {
      errores.push(`Orden #${numerodeOrden}: datos incompletos`);
      continue;
    }

    const detalles = rutaSeleccionada.waypointsData || [];
    const datosAceptados = detalles.map((d, i) => ({
      orden: i + 1,
      cliente: d?.nombrecliente || "Cliente",
      codigo: d?.CodigoSeguimiento || "Sin código",
    }));

    try {
      const json = await $.ajax({
        url: "Planificador/php/planificador.php",
        method: "POST",
        dataType: "json",
        data: {
          AsignarRecorrido: 1,
          recorrido_actual: recorrido_actual,
          recorrido_destino: recorrido_destino,
          color: (resumenSeleccionado.color || "#000000").replace("#", ""),
          recorrido: asign.nombre || `Ruta ${resumenSeleccionado.routeIndex}`,
          kilometros: resumenSeleccionado.distance_km || 0,
          puntos: resumenSeleccionado.waypoints_count || 0,
          zona: asign.zona || "",
          polyline: rutaSeleccionada?.polyline?.encodedPolyline || "",
          datos: JSON.stringify(datosAceptados),
        },
      });

      if (json.status === "success") {
        marcarChoferComoGuardado(choferCard);
      } else {
        errores.push(`Orden #${numerodeOrden}: ${json.message || "error desconocido"}`);
      }
    } catch (err) {
      errores.push(`Orden #${numerodeOrden}: error de conexión`);
    }
  }

  Swal.close();

  if (errores.length === 0) {
    toast("success", "Asignaciones guardadas", "Todas las rutas quedaron asignadas correctamente.");
  } else {
    Swal.fire({
      icon: errores.length === entradas.length ? "error" : "warning",
      title: "Hubo problemas al guardar",
      html: `<ul style="text-align:left">${errores.map((e) => `<li>${e}</li>`).join("")}</ul>`,
    });
  }
});

function marcarChoferComoGuardado(choferCard) {
  if (!choferCard) return;
  choferCard.style.borderColor = "#10c469";
  choferCard.style.background = "rgba(16, 196, 105, .08)";
  const detalle = choferCard.querySelector(".chofer-ruta-asignada");
  if (!detalle) return;
  detalle.querySelectorAll("input").forEach((i) => (i.disabled = true));
  const quitarBtn = detalle.querySelector(".quitarAsignacionBtn");
  if (quitarBtn) quitarBtn.remove();
  detalle.insertAdjacentHTML("beforeend", `<div class="text-success small mt-1"><i class="mdi mdi-check-circle"></i> Guardado</div>`);
}
