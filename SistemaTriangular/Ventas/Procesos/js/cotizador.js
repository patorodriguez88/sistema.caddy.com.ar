/************* helpers básicos *************/
const qs = (s) => document.querySelector(s); // <- NO pisamos jQuery
const fmt = (n) => new Intl.NumberFormat("es-AR").format(Number(n || 0));

function verificar(name, value, max) {
  const valor = parseFloat(value);
  const maximo = parseFloat(max);
  if (valor > maximo) {
    document.getElementById(name).value = 0;
    Swal.fire("Límite", `Máximo ${max} cm. (${name})`, "warning");
  }
}

/** valida si la localidad está en alcance (../php/localidades.php debe devolver JSON) */
async function realizaProceso(localidad) {
  try {
    const body = new URLSearchParams({ localidadorigen: localidad });
    const resp = await fetch("../php/localidades.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
      },
      body,
    });
    const json = await resp.json();
    const resultado = json && json.ok && Number(json.alcance) > 0 ? 1 : 0;

    // mantenemos compat con tu #resultado legacy
    let span = document.getElementById("resultado");
    if (!span) {
      span = document.createElement("span");
      span.id = "resultado";
      span.className = "d-none";
      document.body.appendChild(span);
    }
    span.innerText = resultado;
    return resultado;
  } catch {
    return 0; // fuera de alcance si falla
  }
}

/************* Autocomplete debounced + validaciones Córdoba *************/
function _googleAcDebounce(inputEl, opciones) {
  const cfg = Object.assign(
    { fields: ["address_components"], componentRestrictions: { country: "AR" },
      types: ["geocode", "establishment"], debounce: 400, minLength: 3, onSelect: null },
    opciones || {}
  );
  const svc = new google.maps.places.AutocompleteService();
  const placeSvc = new google.maps.places.PlacesService(document.createElement("div"));
  let token = new google.maps.places.AutocompleteSessionToken();
  let timer = null;
  const wrapper = inputEl.parentElement;
  if (getComputedStyle(wrapper).position === "static") wrapper.style.position = "relative";
  const ul = document.createElement("ul");
  ul.style.cssText =
    "position:absolute;z-index:99999;width:100%;top:100%;left:0;display:none;max-height:220px;" +
    "overflow-y:auto;border-radius:0 0 4px 4px;list-style:none;padding:0;margin:0;" +
    "background:#fff;border:1px solid rgba(0,0,0,.15);box-shadow:0 .25rem .5rem rgba(0,0,0,.1);";
  wrapper.appendChild(ul);
  const close = () => { ul.style.display = "none"; };
  const selectPlace = (placeId, description) => {
    inputEl.value = description; close();
    placeSvc.getDetails({ placeId, fields: cfg.fields, sessionToken: token }, (place, status) => {
      token = new google.maps.places.AutocompleteSessionToken();
      if (status === google.maps.places.PlacesServiceStatus.OK && cfg.onSelect) cfg.onSelect(place);
    });
  };
  inputEl.addEventListener("input", function () {
    clearTimeout(timer);
    const val = this.value.trim();
    if (val.length < cfg.minLength) { close(); return; }
    const snap = val;
    timer = setTimeout(() => {
      svc.getPlacePredictions(
        { input: snap, sessionToken: token, componentRestrictions: cfg.componentRestrictions, types: cfg.types },
        (predictions, status) => {
          ul.innerHTML = "";
          if (status !== google.maps.places.PlacesServiceStatus.OK || !predictions) { close(); return; }
          predictions.forEach((p) => {
            const li = document.createElement("li");
            li.style.cssText = "padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #f0f0f0;";
            li.textContent = p.description;
            li.addEventListener("mouseover", () => { li.style.background = "#f5f5f5"; });
            li.addEventListener("mouseout", () => { li.style.background = ""; });
            li.addEventListener("mousedown", (e) => { e.preventDefault(); selectPlace(p.place_id, p.description); });
            ul.appendChild(li);
          });
          ul.style.display = "block";
        }
      );
    }, cfg.debounce);
  });
  inputEl.addEventListener("blur", () => { setTimeout(close, 200); });
}

function makeAutocompleteKeepCordoba(
  inputSel,
  ciudadHiddenSel,
  etiquetaProvincia = "Córdoba"
) {
  const input = qs(inputSel);
  const hiddenCity = qs(ciudadHiddenSel);

  _googleAcDebounce(input, {
    onSelect: async (place) => {
      if (!place || !place.address_components) return;
      let provincia = "", ciudad = "";
      for (const c of place.address_components) {
        if (c.types[0] === "administrative_area_level_1") provincia = c.long_name;
        if (c.types[0] === "locality") ciudad = c.long_name;
      }
      if (provincia !== etiquetaProvincia) {
        Swal.fire("Atención", `La provincia debe ser ${etiquetaProvincia}, no ${provincia}`, "warning");
        input.value = ""; input.focus(); return;
      }
      const okAlcance = await realizaProceso(ciudad);
      if (!okAlcance) {
        Swal.fire("Fuera de alcance", `La localidad ${ciudad} no está a nuestro alcance (redespacho).`, "info");
        input.value = ""; input.focus(); return;
      }
      hiddenCity.value = ciudad;
    },
  });
}

/************* Google Maps init *************/
let map, directionsService, directionsRenderer;
function initMap() {
  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 7,
    center: { lat: -31.4448988, lng: -64.177743 },
  });
  directionsService = new google.maps.DirectionsService();
  directionsRenderer = new google.maps.DirectionsRenderer({ map });

  makeAutocompleteKeepCordoba("#start", "#startciudad");
  makeAutocompleteKeepCordoba("#end", "#endciudad");
  makeAutocompleteKeepCordoba("#waypoints", "#waypointsciudad");

  qs("#btn_rutear")?.addEventListener("click", calcularRuta);
  qs("#btn_cotizar")?.addEventListener("click", cotizarConProductos);

  qs("#wp_toggle")?.addEventListener("change", (e) => {
    qs("#waypoints").classList.toggle("d-none", !e.target.checked);
    if (!e.target.checked) {
      qs("#waypoints").value = "";
      qs("#waypointsciudad").value = "";
    }
  });
}

/************* Ruta + panel *************/
let ultimoTotalKm = 0; // guardamos el km para la cotización

function calcularRuta() {
  const start = `${qs("#start").value}, ${qs("#startciudad").value}`;
  const end = `${qs("#end").value}, ${qs("#endciudad").value}`;

  if (
    !qs("#start").value ||
    !qs("#startciudad").value ||
    !qs("#end").value ||
    !qs("#endciudad").value
  ) {
    Swal.fire(
      "Faltan datos",
      "Completá origen y destino (con ciudad).",
      "info"
    );
    return;
  }

  const waypts = [];
  if (qs("#waypoints").value) {
    waypts.push({
      location: `${qs("#waypoints").value}, ${qs("#waypointsciudad").value}`,
      stopover: true,
    });
  }

  directionsService.route(
    {
      origin: start,
      destination: end,
      waypoints: waypts,
      optimizeWaypoints: true,
      travelMode: google.maps.TravelMode.DRIVING,
    },
    (response, status) => {
      if (status !== "OK") {
        Swal.fire("Error", "No se pudo calcular la ruta.", "error");
        return;
      }
      directionsRenderer.setDirections(response);
      renderResumen(response);
    }
  );
}

function renderResumen(response) {
  const panel = qs("#directions-panel");
  panel.innerHTML = "";
  const route = response.routes[0];

  let totalKm = 0;
  let totalSeg = 0;

  route.legs.forEach((leg, i) => {
    // leg.distance.text puede traer “12,3 km”
    const km = parseFloat(leg.distance.text.replace(",", "."));
    totalKm += km;
    totalSeg += leg.duration.value;
    panel.innerHTML += `
      <b>Ruta Segmento: ${i + 1}</b><br>
      Desde ${leg.start_address} hasta ${leg.end_address}<br>
      Total Segmento: ${leg.distance.text}<br>
      Duración: ${leg.duration.text}<br><br>
    `;
  });

  const horas = Math.round(totalSeg / 60) + 1; // tu regla original
  const horas1 = Math.floor(horas / 60);
  const minutos = horas - horas1 * 60;

  ultimoTotalKm = totalKm; // ← guardamos para la cotización
  panel.innerHTML += `<hr><b> Distancia Total: ${totalKm.toFixed(
    2
  )} km.</b>  <b>Duración Total:</b> ${horas1} Horas ${minutos} minutos<br>`;
}

/************* Backend de tarifas (Productos Web) *************/
async function pedirTarifaDesdeProductos({
  km,
  anchoCm,
  largoCm,
  altoCm,
  cantidad,
  tieneWP,
  cambiaLocalidad,
  valorDeclarado,
}) {
  const m3 = (Number(anchoCm) * Number(largoCm) * Number(altoCm)) / 1_000_000; // cm^3 -> m^3

  const body = new URLSearchParams({
    km: String(km),
    m3: String(m3.toFixed(3)),
    cantidad: String(cantidad || 1),
    tiene_wp: tieneWP ? "1" : "0",
    cambia_localidad: cambiaLocalidad ? "1" : "0",
    valordeclarado: String(valorDeclarado || 0),
  });

  const resp = await fetch("Ventas/Procesos/php/cotizador.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
    },
    body,
  });

  const json = await resp.json().catch(() => null);
  return json;
}

/** Decide si cambia localidad (según A, B, C) */
function hayCambioLocalidad() {
  const A = qs("#startciudad").value?.trim();
  const B = qs("#waypointsciudad").value?.trim() || A; // si no hay WP, comparo A con C
  const C = qs("#endciudad").value?.trim();

  const tieneWP = !!qs("#waypoints").value;
  if (tieneWP) {
    // si hay WP y cualquiera difiere, hay cambio
    return !(A === B && A === C && B === C);
  }
  // sin WP: difiere origen vs destino
  return A !== C;
}

/** Botón “Cotizar” */
async function cotizarConProductos() {
  if (!ultimoTotalKm) {
    Swal.fire("Ruta", "Primero calculá la ruta (para obtener km).", "info");
    return;
  }

  const ancho = qs("#ancho").value;
  const largo = qs("#largo").value;
  const alto = qs("#alto").value;
  const cantidad = qs("#cantidad").value || 1;
  const tieneWP = !!qs("#waypoints").value;
  const cambiaLocalidad = hayCambioLocalidad();
  const valorDeclarado = qs("#valordeclarado").value || 0;

  const res = await pedirTarifaDesdeProductos({
    km: ultimoTotalKm,
    anchoCm: ancho,
    largoCm: largo,
    altoCm: alto,
    cantidad,
    tieneWP,
    cambiaLocalidad,
    valorDeclarado,
  });

  if (!res || !res.ok) {
    Swal.fire(
      "Sin tarifa",
      (res && res.error) || "No se encontró tarifa que cubra la solicitud.",
      "warning"
    );
    return;
  }

  const panel = document.getElementById("directions-panel");
  panel.innerHTML += `
    <hr>
    <b>Tarifa:</b> ${res.match.descripcion} [${res.match.codigo}] (≤ ${
    res.match.kilometros
  } km, ≤ ${res.match.m3} m³, grupo ${res.match.letra})<br>
    <b>Base:</b> $ ${fmt(res.precios.precio_base)}<br>
    <b>Por Cantidad:</b> $ ${fmt(res.precios.precio_por_cantidad)}<br>
    <b>Seguro:</b> $ ${fmt(res.precios.seguro)}<br>
    <b>Cambia Localidad:</b> $ ${fmt(res.precios.recargo_cambia_loc)}<br>
    <b>Factor Waypoint:</b> ${res.precios.factor_wp}<br>
    <b>Total:</b> <span style="font-size:18px">$ ${fmt(
      res.precios.total
    )}</span>
  `;
}
