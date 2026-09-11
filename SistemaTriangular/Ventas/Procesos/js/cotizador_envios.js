// Cotizador de Envios (Ventas > Cotizador de Envios).
// Mapa y ruteo con Google Maps (Directions + Places Autocomplete).
// Backend: Ventas/Procesos/php/cotizador_envios.php

(function () {
  "use strict";

  var API = "Procesos/php/cotizador_envios.php";
  var CBA = { lat: -31.4201, lng: -64.1888 };

  var map, dirService, dirRenderer, geocoder;
  var puntos = {}; // puntos[id] = { lat, lng, texto, localidad }
  var pins = {};   // pins[id] = google.maps.Marker (preview antes de trazar)
  var ultimoCalculo = null;
  var montoMinimoSeguro = 0; // Variables.MontoMinimoSeguro: cobertura sin cargo incluida en la tarifa
  var cotizacionId = 0;
  var wpSeq = 0;

  function money(n) {
    return "$ " + Number(n || 0).toLocaleString("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function el(id) { return document.getElementById(id); }
  function toast2(t, tit, m) { if (typeof toast === "function") toast(t, tit, m); else if (t === "error") alert(tit + ": " + m); }

  function localidadDeComponents(comps) {
    if (!comps) return "";
    var loc = "", adm2 = "";
    comps.forEach(function (c) {
      if (c.types.indexOf("locality") >= 0) loc = c.long_name;
      if (c.types.indexOf("administrative_area_level_2") >= 0) adm2 = c.long_name;
    });
    return loc || adm2 || "";
  }

  // "Por km" solo aplica si el envio sale de Cordoba capital (toca otra
  // localidad en origen/destino/paradas). Espejo del cotFueraDeCordoba() del backend.
  function normCordoba(s) {
    return (s || "").toString().toLowerCase()
      .replace(/[áàä]/g, "a").replace(/[éèë]/g, "e").replace(/[íìï]/g, "i")
      .replace(/[óòö]/g, "o").replace(/[úùü]/g, "u").replace(/ñ/g, "n");
  }
  function saleDeCordoba() {
    var porLocalidad = stops.some(function (s) {
      var loc = normCordoba(sd[s.sid] && sd[s.sid].localidad);
      return loc && loc.indexOf("cordoba") === -1;
    });
    if (porLocalidad) return true;
    // Respaldo: si Google no taggeo bien la localidad, un recorrido largo
    // (mismo criterio que el backend) casi seguro sale de Cordoba capital.
    var info = el("cot_ruta_info");
    var km = parseFloat((info && info.dataset.km) || "0");
    return km > 28;
  }
  function actualizarDisponibilidadKm() {
    var fuera = saleDeCordoba();
    var radioKm = el("cot_modo_km");
    var hint = el("cot_modo_cordoba_hint");
    if (!radioKm || !hint) return;
    radioKm.disabled = !fuera;
    if (fuera) {
      hint.className = "small mb-2 text-success";
      hint.textContent = "El envío sale de Córdoba: se puede cotizar \"Por km\".";
    } else {
      hint.className = "small mb-2 text-muted";
      hint.textContent = "El cálculo \"Por km\" solo aplica si el envío sale de Córdoba capital (toca otra localidad).";
    }
    if (!fuera && radioKm.checked) {
      el("cot_modo_auto").checked = true;
      el("cot_veh_wrap").classList.add("d-none");
      toast2("info", "Cotizador", "El envío no sale de Córdoba: se cotiza \"Por servicio\".");
    }
  }

  // ------------------------------------------------------------ Google Maps init
  window.initMap = function () {
    map = new google.maps.Map(el("cot_map"), {
      center: CBA,
      zoom: 11,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: true,
    });
    dirService = new google.maps.DirectionsService();
    dirRenderer = new google.maps.DirectionsRenderer({
      map: map,
      draggable: true,
      polylineOptions: { strokeColor: "#e24f30", strokeWeight: 5, strokeOpacity: 0.9 },
    });
    dirRenderer.addListener("directions_changed", function () {
      leerRutaDeRenderer(dirRenderer.getDirections());
    });
    geocoder = new google.maps.Geocoder();

    initStops();
    initFullscreen();
  };

  // --- Mapa a pantalla completa (estilo Zonas) ---
  var fsInit = false;
  function initFullscreen() {
    var row = el("cot_layout");
    if (!row || fsInit) return;
    fsInit = true;

    function mapResize() { if (window.google && map) google.maps.event.trigger(map, "resize"); }

    function ajustarTop() {
      var top = 0;
      document.querySelectorAll(".navbar-custom, .topnav").forEach(function (e) {
        var r = e.getBoundingClientRect();
        if (r.height && r.top < 200) top = Math.max(top, r.bottom);
      });
      row.style.top = (top > 0 ? Math.round(top) : 120) + "px";
      mapResize();
    }
    ajustarTop();
    setTimeout(ajustarTop, 300);
    setTimeout(ajustarTop, 1200);
    window.addEventListener("resize", ajustarTop);

    function setPanel(off) {
      row.classList.toggle("cot-panel-off", off);
      setTimeout(mapResize, 230);
    }
    var bHide = el("cot_panel_hide"), bShow = el("cot_panel_toggle");
    if (bHide) bHide.addEventListener("click", function () { setPanel(true); });
    if (bShow) bShow.addEventListener("click", function () { setPanel(false); });
  }

  // ============================================================ Recorrido (lista de paradas estilo My Maps)
  var stops = [];   // [{ sid }]  orden actual
  var sd = {};      // sd[sid] = { texto, lat, lng, localidad, ok, row, input, ac, pin }
  var sidSeq = 0;

  function nuevoSid() { sidSeq++; return "s" + sidSeq; }

  function stopLabel(i) {
    if (i === 0) return "A";
    if (i === stops.length - 1) return "B";
    return String(i);
  }

  function crearStop(sid, valor) {
    var row = document.createElement("div");
    row.className = "cot-stop d-flex align-items-center gap-1 mb-1";
    row.dataset.sid = sid;
    row.innerHTML =
      '<span class="cot-stop-badge badge bg-secondary"></span>' +
      '<input class="form-control form-control-sm cot-stop-input" placeholder="Dirección" autocomplete="off">' +
      '<button class="btn btn-light btn-sm p-0 px-1 cot-stop-up" title="Subir"><i class="mdi mdi-chevron-up"></i></button>' +
      '<button class="btn btn-light btn-sm p-0 px-1 cot-stop-down" title="Bajar"><i class="mdi mdi-chevron-down"></i></button>' +
      '<button class="btn btn-light btn-sm p-0 px-1 cot-stop-del" title="Quitar"><i class="mdi mdi-close"></i></button>';
    var input = row.querySelector(".cot-stop-input");
    if (valor) input.value = valor;
    sd[sid] = { texto: valor || "", lat: null, lng: null, localidad: "", ok: false, row: row, input: input, pin: null };

    if (window.google && google.maps.places) {
      var ac = new google.maps.places.Autocomplete(input, {
        fields: ["geometry", "formatted_address", "address_components"],
        componentRestrictions: { country: "ar" },
      });
      ac.addListener("place_changed", function () {
        var pl = ac.getPlace();
        if (!pl || !pl.geometry) return;
        var ll = pl.geometry.location;
        setStop(sid, ll.lat(), ll.lng(), pl.formatted_address || input.value, pl.address_components);
      });
      sd[sid].ac = ac;
    }
    input.addEventListener("blur", function () { setTimeout(function () { confirmarStop(sid); }, 250); });
    input.addEventListener("input", function () {
      sd[sid].ok = false;
      input.classList.remove("is-valid", "is-invalid");
    });
    row.querySelector(".cot-stop-up").addEventListener("click", function (e) { e.preventDefault(); moverStop(sid, -1); });
    row.querySelector(".cot-stop-down").addEventListener("click", function (e) { e.preventDefault(); moverStop(sid, 1); });
    row.querySelector(".cot-stop-del").addEventListener("click", function (e) { e.preventDefault(); quitarStop(sid); });
    return row;
  }

  function renderStops() {
    var cont = el("cot_stops");
    stops.forEach(function (s) { cont.appendChild(sd[s.sid].row); });
    stops.forEach(function (s, i) {
      var d = sd[s.sid];
      var b = d.row.querySelector(".cot-stop-badge");
      b.textContent = stopLabel(i);
      b.className = "cot-stop-badge badge " + (i === 0 ? "bg-success" : (i === stops.length - 1 ? "bg-danger" : "bg-secondary"));
      d.row.querySelector(".cot-stop-del").style.visibility = stops.length <= 2 ? "hidden" : "visible";
      d.row.querySelector(".cot-stop-up").style.visibility = i === 0 ? "hidden" : "visible";
      d.row.querySelector(".cot-stop-down").style.visibility = i === stops.length - 1 ? "hidden" : "visible";
      d.input.placeholder = i === 0 ? "Origen" : (i === stops.length - 1 ? "Destino" : "Parada intermedia");
    });
    actualizarPins();
  }

  function moverStop(sid, dir) {
    var i = stops.findIndex(function (s) { return s.sid === sid; });
    var j = i + dir;
    if (j < 0 || j >= stops.length) return;
    var t = stops[i]; stops[i] = stops[j]; stops[j] = t;
    renderStops();
  }

  function quitarStop(sid) {
    if (stops.length <= 2) return;
    stops = stops.filter(function (s) { return s.sid !== sid; });
    if (sd[sid].pin) sd[sid].pin.setMap(null);
    sd[sid].row.remove();
    delete sd[sid];
    renderStops();
  }

  function agregarParada() {
    var sid = nuevoSid();
    el("cot_stops").appendChild(crearStop(sid, ""));
    stops.splice(Math.max(1, stops.length - 1), 0, { sid: sid });
    renderStops();
    sd[sid].input.focus();
  }

  function initStops() {
    stops = []; sd = {};
    var a = nuevoSid(), b = nuevoSid();
    el("cot_stops").innerHTML = "";
    el("cot_stops").appendChild(crearStop(a, ""));
    el("cot_stops").appendChild(crearStop(b, ""));
    stops = [{ sid: a }, { sid: b }];
    renderStops();
    actualizarDisponibilidadKm();
  }

  // reconstruye la lista al cargar una cotización guardada
  // origenLoc/destinoLoc: localidad ya conocida (viene de la cotizacion guardada)
  // para no perderla al reconstruir la lista (antes quedaba "? -> ?" en el
  // listado porque se volvia a armar el pin con comps=null, sin localidad).
  function reconstruirStops(origenTxt, wps, destinoTxt, origenLoc, destinoLoc) {
    Object.keys(sd).forEach(function (k) { if (sd[k].pin) sd[k].pin.setMap(null); });
    stops = []; sd = {};
    el("cot_stops").innerHTML = "";
    var defs = [{ t: origenTxt, loc: origenLoc || "" }]
      .concat((wps || []).map(function (w) { return { t: w.texto, lat: w.lat, lng: w.lng, loc: w.localidad || "" }; }))
      .concat([{ t: destinoTxt, loc: destinoLoc || "" }]);
    defs.forEach(function (dfn) {
      var sid = nuevoSid();
      el("cot_stops").appendChild(crearStop(sid, dfn.t || ""));
      stops.push({ sid: sid });
      if (dfn.lat && dfn.lng) setStop(sid, Number(dfn.lat), Number(dfn.lng), dfn.t || "", null, dfn.loc);
      else if (dfn.loc) { sd[sid].localidad = dfn.loc; }
    });
    renderStops();
  }

  function setStop(sid, lat, lng, formatted, comps, localidadOverride) {
    var d = sd[sid];
    if (!d) return;
    d.lat = lat; d.lng = lng; d.texto = formatted;
    d.localidad = localidadOverride != null && localidadOverride !== "" ? localidadOverride : localidadDeComponents(comps);
    d.ok = true;
    if (formatted) d.input.value = formatted;
    d.input.classList.remove("is-invalid");
    d.input.classList.add("is-valid");
    if (!d.pin) {
      d.pin = new google.maps.Marker({ position: { lat: lat, lng: lng }, map: map, draggable: true });
      d.pin.addListener("dragend", function () {
        var ll = d.pin.getPosition();
        geocoder.geocode({ location: { lat: ll.lat(), lng: ll.lng() } }, function (res, st) {
          if (st === "OK" && res[0]) setStop(sid, ll.lat(), ll.lng(), res[0].formatted_address, res[0].address_components);
          else { d.lat = ll.lat(); d.lng = ll.lng(); }
        });
      });
    } else {
      d.pin.setPosition({ lat: lat, lng: lng });
      d.pin.setMap(map);
    }
    actualizarPins();
  }

  function actualizarPins() {
    var lls = [];
    stops.forEach(function (s, i) {
      var d = sd[s.sid];
      if (d && d.pin) { d.pin.setLabel(stopLabel(i)); lls.push(d.pin.getPosition()); }
    });
    if (lls.length === 1) { map.panTo(lls[0]); map.setZoom(14); }
    else if (lls.length > 1) {
      var b = new google.maps.LatLngBounds();
      lls.forEach(function (p) { b.extend(p); });
      map.fitBounds(b);
    }
  }

  function ocultarPins() {
    Object.keys(sd).forEach(function (k) { if (sd[k].pin) sd[k].pin.setMap(null); });
  }

  function confirmarStop(sid, cb) {
    cb = cb || function () {};
    var d = sd[sid];
    if (!d || !d.input.value.trim()) { cb(false); return; }
    if (d.ok && d.texto === d.input.value.trim() && d.pin) { cb(true); return; }
    geocoder.geocode({ address: d.input.value.trim(), componentRestrictions: { country: "AR" } }, function (res, status) {
      if (status === "OK" && res[0]) {
        var ll = res[0].geometry.location;
        setStop(sid, ll.lat(), ll.lng(), res[0].formatted_address, res[0].address_components);
        cb(true);
      } else {
        d.ok = false;
        d.input.classList.remove("is-valid");
        d.input.classList.add("is-invalid");
        cb(false);
      }
    });
  }

  function leerRutaDeRenderer(result) {
    if (!result || !result.routes || !result.routes.length) return;
    var route = result.routes[0];
    var m = 0, s = 0;
    route.legs.forEach(function (leg) {
      m += leg.distance ? leg.distance.value : 0;
      s += leg.duration ? leg.duration.value : 0;
    });
    var km = m / 1000, min = Math.round(s / 60);
    el("cot_ruta_info").textContent = km.toFixed(1) + " km · " + min + " min de manejo";
    el("cot_ruta_info").dataset.km = km.toFixed(2);
    el("cot_ruta_info").dataset.min = String(min);
    actualizarDisponibilidadKm();
  }

  function calcularRuta() {
    var i = 0;
    function next() {
      if (i >= stops.length) return trazar();
      var d = sd[stops[i].sid];
      if (!d.input.value.trim()) { toast2("info", "Ruta", "Completá todas las direcciones."); return; }
      confirmarStop(stops[i].sid, function (ok) {
        if (!ok) { toast2("error", "Ruta", "No se encontró: " + d.input.value); return; }
        i++;
        next();
      });
    }
    function trazar() {
      var pts = stops.map(function (s) { return sd[s.sid]; });
      var optimizar = el("cot_optimizar") && el("cot_optimizar").checked;
      el("cot_ruta_info").textContent = "Calculando...";
      dirService.route(
        {
          origin: { lat: pts[0].lat, lng: pts[0].lng },
          destination: { lat: pts[pts.length - 1].lat, lng: pts[pts.length - 1].lng },
          waypoints: pts.slice(1, -1).map(function (w) { return { location: { lat: w.lat, lng: w.lng }, stopover: true }; }),
          optimizeWaypoints: optimizar,
          travelMode: google.maps.TravelMode.DRIVING,
        },
        function (res, status) {
          if (status !== "OK") { el("cot_ruta_info").textContent = ""; toast2("error", "Ruta", "No se pudo calcular la ruta."); return; }
          var ord = res.routes[0].waypoint_order;
          if (optimizar && ord && ord.length) {
            var medios = stops.slice(1, -1);
            stops = [stops[0]].concat(ord.map(function (k) { return medios[k]; }), [stops[stops.length - 1]]);
            renderStops();
          }
          ocultarPins();
          dirRenderer.setDirections(res);
          leerRutaDeRenderer(res);
        }
      );
    }
    next();
  }

  // ------------------------------------------------------------ Paquetes (modal + lista)
  var paquetes = []; // { descripcion, cantidad, peso, ancho, largo, alto }

  function renderPaquetes() {
    var cont = el("cot_paquetes_lista");
    if (!paquetes.length) {
      cont.innerHTML = '<div class="text-muted small">Sin paquetes. Tocá "Agregar paquete".</div>';
      return;
    }
    cont.innerHTML = paquetes
      .map(function (p, i) {
        var vol = ((p.ancho * p.largo * p.alto) / 1000000).toFixed(3);
        return (
          '<div class="d-flex align-items-center justify-content-between border rounded px-2 py-1 mb-1" style="font-size:12px">' +
          '<div class="cot-paq-edit" data-i="' + i + '" style="cursor:pointer;line-height:1.25">' +
          '<b>' + (p.cantidad > 1 ? p.cantidad + "× " : "") + (p.descripcion || "Paquete") + "</b><br>" +
          '<span class="text-muted">' + p.ancho + "×" + p.largo + "×" + p.alto + " cm · " + p.peso + " kg · " + vol + " m³</span>" +
          "</div>" +
          '<button class="btn btn-sm btn-light cot-paq-del" data-i="' + i + '"><i class="mdi mdi-close"></i></button>' +
          "</div>"
        );
      })
      .join("");
  }

  function abrirModalPaquete(i) {
    var editar = typeof i === "number" && i >= 0;
    el("cot_pq_idx").value = editar ? String(i) : "-1";
    el("cot_modal_paquete_titulo").textContent = editar ? "Editar paquete" : "Agregar paquete";
    el("cot_pq_guardar").textContent = editar ? "Guardar" : "Agregar";
    var p = editar ? paquetes[i] : { descripcion: "", cantidad: 1, peso: 1, ancho: 10, largo: 10, alto: 10 };
    el("cot_pq_desc").value = p.descripcion;
    el("cot_pq_cant").value = p.cantidad;
    el("cot_pq_peso").value = p.peso;
    el("cot_pq_ancho").value = p.ancho;
    el("cot_pq_largo").value = p.largo;
    el("cot_pq_alto").value = p.alto;
    el("cot_pq_error").textContent = "";
    calcularVolModal();
    var m = window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(el("cot_modal_paquete")) : null;
    if (m) m.show(); else $("#cot_modal_paquete").modal("show");
  }

  function calcularVolModal() {
    var v = (parseFloat(el("cot_pq_ancho").value) || 0) * (parseFloat(el("cot_pq_largo").value) || 0) * (parseFloat(el("cot_pq_alto").value) || 0);
    el("cot_pq_vol").textContent = "Volumen: " + (v / 1000000).toFixed(3) + " m³";
  }

  function guardarModalPaquete() {
    var p = {
      descripcion: el("cot_pq_desc").value.trim(),
      cantidad: parseInt(el("cot_pq_cant").value, 10) || 1,
      peso: parseFloat(el("cot_pq_peso").value) || 0,
      ancho: parseFloat(el("cot_pq_ancho").value) || 0,
      largo: parseFloat(el("cot_pq_largo").value) || 0,
      alto: parseFloat(el("cot_pq_alto").value) || 0,
    };
    if (p.cantidad < 1 || p.ancho <= 0 || p.largo <= 0 || p.alto <= 0) {
      el("cot_pq_error").textContent = "Completá cantidad y dimensiones (mayores a 0).";
      return;
    }
    var idx = parseInt(el("cot_pq_idx").value, 10);
    if (idx >= 0) paquetes[idx] = p;
    else paquetes.push(p);
    renderPaquetes();
    var m = window.bootstrap ? window.bootstrap.Modal.getInstance(el("cot_modal_paquete")) : null;
    if (m) m.hide(); else $("#cot_modal_paquete").modal("hide");
  }

  function leerPaquetes() {
    return paquetes;
  }

  // ------------------------------------------------------------ Calcular
  function modo() {
    var r = document.querySelector('input[name="cot_modo"]:checked');
    return r ? r.value : "servicio";
  }

  function payloadBase() {
    var info = el("cot_ruta_info");
    return {
      modo: modo(),
      km: parseFloat(info.dataset.km || "0"),
      tiempo_manejo_min: parseInt(info.dataset.min || "0", 10),
      demoras_min: parseInt(el("cot_demoras_min").value, 10) || 0,
      paquetes: JSON.stringify(leerPaquetes()),
      valor_declarado: parseFloat(el("cot_valordeclarado").value) || 0,
      lleva_seguro: el("cot_lleva_seguro").checked ? 1 : 0,
      seguro_pct: parseFloat(el("cot_seguro_pct").value) || 0,
      lleva_cobranza: el("cot_lleva_cobranza").checked ? 1 : 0,
      cobranza_base: parseFloat(el("cot_cobranza_base").value) || 0,
      cobranza_pct: parseFloat(el("cot_cobranza_pct").value) || 0,
      viatico: parseFloat(el("cot_viatico").value) || 0,
      demoras_monto: parseFloat(el("cot_demoras_monto").value) || 0,
      descuento_tipo: el("cot_desc_tipo").value,
      descuento_valor: parseFloat(el("cot_desc_valor").value) || 0,
      id_vehiculo: parseInt(el("cot_vehiculo").value || "0", 10),
      localidades: JSON.stringify(stops.map(function (s) { return (sd[s.sid] && sd[s.sid].localidad) || ""; })),
    };
  }

  function calcular() {
    var info = el("cot_ruta_info");
    if (!info.dataset.km || parseFloat(info.dataset.km) <= 0) {
      toast2("info", "Cotizador", "Primero calculá la ruta.");
      return;
    }
    if (!paquetes.length) {
      toast2("error", "Cotizador", "Agregá al menos un paquete: no se puede cotizar sin saber qué se envía.");
      irPaso(1);
      return;
    }
    var body = new URLSearchParams(Object.assign({ action: "calcular" }, payloadBase()));
    fetch(API, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" }, body: body })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.ok) { toast2("error", "Cotizador", (res && res.error) || "No se pudo calcular."); return; }
        // cotizacionId NO se toca aca: si estabas editando una cotizacion ya
        // guardada, recalcular no debe "desguardarla" — el proximo Guardar
        // tiene que actualizar esa misma fila, no crear una nueva.
        ultimoCalculo = res;
        renderResultado(res);
      })
      .catch(function () { toast2("error", "Cotizador", "Error de red."); });
  }

  function fila(lbl, val, cls, detalle) {
    return '<tr' + (cls ? ' class="' + cls + '"' : "") + '><td>' + lbl +
      (detalle ? '<div class="cot-fila-sub">' + detalle + '</div>' : "") +
      '</td><td class="text-end">' + val + "</td></tr>";
  }

  function renderResultado(res) {
    var d = res.desglose;
    // el backend es la autoridad final sobre si el envio sale de Cordoba
    // (incluye el respaldo por km); resincroniza el cartel/radio si hacia falta.
    if (typeof res.fuera_cordoba === "boolean") {
      var radioKm = el("cot_modo_km"), hint = el("cot_modo_cordoba_hint");
      if (radioKm && hint) {
        radioKm.disabled = !res.fuera_cordoba;
        if (res.fuera_cordoba) {
          hint.className = "small mb-2 text-success";
          hint.textContent = "El envío sale de Córdoba: se puede cotizar \"Por km\".";
        } else {
          hint.className = "small mb-2 text-muted";
          hint.textContent = "El cálculo \"Por km\" solo aplica si el envío sale de Córdoba capital (toca otra localidad).";
        }
      }
    }
    el("cot_r_titulo").textContent = el("cot_titulo").value.trim() || "Cotización";
    el("cot_r_titulo").title = el("cot_r_titulo").textContent;
    var metaBits = [res.km + " km", res.tiempo.total_txt];
    if (res.carga) {
      metaBits.push((res.carga.kg || 0).toLocaleString("es-AR") + " kg", (res.carga.m3 || 0).toLocaleString("es-AR") + " m³");
    }
    el("cot_r_meta").innerHTML = metaBits.map(function (b) { return "<span>" + b + "</span>"; }).join("");

    var mr = res.modo_resuelto || res.modo;
    // Qué tarifa se usó en "por servicio": nombres distintos de los bultos.
    var tarifasUsadas = [];
    (res.bultos || []).forEach(function (b) {
      if (b.tarifa_nombre && b.tarifa_precio != null && tarifasUsadas.indexOf(b.tarifa_nombre) < 0) {
        tarifasUsadas.push(b.tarifa_nombre);
      }
    });
    var transDetalle = (mr === "km" ? "Por km · " + res.vehiculo_nombre : "Por servicio" +
      (tarifasUsadas.length === 1 ? " · " + tarifasUsadas[0]
        : tarifasUsadas.length > 1 ? " · " + tarifasUsadas.length + " tarifas" : "")) +
      (res.modo === "auto" ? " · automático" : "");

    // comparativa (solo en modo automatico)
    if (res.modo === "auto" && res.comparativa && res.comparativa.length) {
      el("cot_avisos").innerHTML =
        '<div class="cot-comparativa border rounded p-2 mb-2">' +
        '<div class="text-muted mb-1">Comparativa (más económico apto):</div>' +
        res.comparativa
          .map(function (c) {
            if (c.apto === false) {
              return '<div class="text-muted" style="opacity:.7">&nbsp;&nbsp;&nbsp;' + c.label +
                ' — <span class="text-danger">' + (c.motivo || "no apto") + "</span></div>";
            }
            return '<div class="' + (c.elegida ? "elegida text-success" : "") + '">' +
              (c.elegida ? "✔ " : "&nbsp;&nbsp;&nbsp;") + c.label + " — " + money(c.transporte) + "</div>";
          })
          .join("") +
        "</div>";
    } else {
      el("cot_avisos").innerHTML = "";
    }

    // anexo demoras (no entra en el total)
    if (res.anexo_demora) {
      el("cot_r_anexo").textContent = "Anexo: " + res.anexo_demora;
      el("cot_r_anexo").classList.remove("d-none");
    } else {
      el("cot_r_anexo").classList.add("d-none");
    }

    var rows = "";
    rows += fila("Transporte", money(d.precio_transporte), null, transDetalle);
    if (d.seguro > 0) {
      rows += fila("Seguro", money(d.seguro), null, d.seguro_pct + "% sobre excedente de " + money(d.seguro_incluido || 0));
    }
    if (d.cobranza > 0) {
      rows += fila("Cobranza integrada", money(d.cobranza), null, d.cobranza_pct + "% de " + money(d.cobranza_base));
    }
    if (d.viatico > 0) rows += fila("Viático", money(d.viatico));
    rows += fila("Subtotal", money(d.subtotal), "fw-bold");
    if (d.descuento_monto > 0) {
      rows += fila("Descuento", "- " + money(d.descuento_monto), null, d.descuento_tipo === "pct" ? d.descuento_valor + "% de descuento" : "importe fijo");
    }
    rows += fila("Neto", money(d.neto));
    rows += fila("IVA 21%", money(d.iva));
    rows += fila("TOTAL (IVA incluido)", money(d.total), "cot-total-row");
    el("cot_desglose_body").innerHTML = rows;

    var bd = "";
    (res.bultos || []).forEach(function (b, i) {
      bd +=
        "<div>#" + (i + 1) + " " + (b.descripcion || "Bulto") +
        " · " + (b.ancho + "×" + b.largo + "×" + b.alto) + " cm · " + b.peso + " kg" +
        (b.tarifa_precio != null
          ? " · " + b.tarifa_nombre + " " + money(b.tarifa_precio) +
            " × " + (b.factor === 1 ? "100%" : b.factor === 0 ? "0%" : "50%") +
            " = <b>" + money(b.precio_final) + "</b>"
          : "") +
        "</div>";
    });
    el("cot_bultos_detalle").innerHTML = bd || "<div class='text-muted'>—</div>";

    var av = (res.avisos || []).map(function (a) { return '<div class="alert alert-warning py-1 px-2 small mb-1">' + a + "</div>"; }).join("");
    el("cot_avisos").innerHTML += av;

    el("cot_result").classList.remove("d-none");
    // PDF/Mail siguen habilitados si ya habia una cotizacion guardada abierta
    // (recalcular no la "desguarda"); si es nueva, quedan bloqueados hasta Guardar.
    el("cot_pdf").disabled = !cotizacionId;
    el("cot_mail").disabled = !cotizacionId;
    el("cot_guardar_estado").textContent = "";
  }

  // ------------------------------------------------------------ Guardar
  function rutaPayload() {
    var pts = stops.map(function (s) { return sd[s.sid]; });
    var o = pts[0] || {}, dd = pts[pts.length - 1] || {};
    return {
      origenTexto: o.texto || (o.input ? o.input.value.trim() : ""),
      origenLocalidad: o.localidad || "",
      origenLat: o.lat || null,
      origenLng: o.lng || null,
      destinoTexto: dd.texto || (dd.input ? dd.input.value.trim() : ""),
      destinoLocalidad: dd.localidad || "",
      destinoLat: dd.lat || null,
      destinoLng: dd.lng || null,
      waypoints: pts.slice(1, -1).map(function (w) {
        return { texto: w.texto || w.input.value.trim(), localidad: w.localidad || "", lat: w.lat || null, lng: w.lng || null };
      }),
    };
  }

  function guardar(onDone) {
    onDone = onDone || function () {};
    if (!ultimoCalculo) { onDone(false); return; }
    var payload = {
      idCliente: parseInt(el("cot_idcliente").value || "0", 10),
      razonSocial: el("cot_cliente").value.trim() || "Consumidor Final",
      titulo: el("cot_titulo").value.trim(),
      modo: ultimoCalculo.modo_resuelto || ultimoCalculo.modo,
      idVehiculo: ultimoCalculo.elegida_id_vehiculo || parseInt(el("cot_vehiculo").value || "0", 10),
      vehiculoNombre: ultimoCalculo.vehiculo_nombre || "",
      km: ultimoCalculo.km,
      valorDeclarado: parseFloat(el("cot_valordeclarado").value) || 0,
      llevaSeguro: el("cot_lleva_seguro").checked,
      llevaCobranza: el("cot_lleva_cobranza").checked,
      observaciones: el("cot_obs").value.trim(),
      ruta: rutaPayload(),
      tiempo: ultimoCalculo.tiempo,
      bultos: ultimoCalculo.bultos,
      paquetesInput: paquetes,
      desglose: ultimoCalculo.desglose,
      id: cotizacionId || 0, // si ya esta guardada, actualiza esa misma fila
    };
    var body = new URLSearchParams({ action: "guardar", id: String(cotizacionId || 0), payload: JSON.stringify(payload) });
    el("cot_guardar_estado").textContent = "Guardando...";
    fetch(API, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" }, body: body })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.ok) {
          el("cot_guardar_estado").innerHTML = '<span class="text-danger">' + ((res && res.error) || "No se pudo guardar") + "</span>";
          onDone(false);
          return;
        }
        cotizacionId = res.id;
        el("cot_guardar_estado").innerHTML = '<span class="text-success">Cotización #' + res.id + (res.actualizada ? " actualizada." : " guardada.") + "</span>";
        el("cot_pdf").disabled = false;
        el("cot_mail").disabled = false;
        onDone(true);
      })
      .catch(function () { el("cot_guardar_estado").innerHTML = '<span class="text-danger">Error de red</span>'; onDone(false); });
  }

  // ------------------------------------------------------------ Mail (chips)
  var chips = [];
  function renderChips() {
    el("cot_mail_chips").innerHTML = chips
      .map(function (m) {
        return '<span class="badge bg-danger d-inline-flex align-items-center gap-1">' + m +
          '<a href="#" class="text-white cot-chip-x" data-m="' + m + '" style="text-decoration:none">&times;</a></span>';
      })
      .join("");
  }
  function addChip(raw) {
    (raw || "").split(/[,;\s]+/).map(function (s) { return s.trim(); }).filter(Boolean).forEach(function (m) {
      if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(m) && chips.indexOf(m) < 0) chips.push(m);
    });
    renderChips();
  }

  function enviarMail() {
    var inp = el("cot_mail_input");
    if (inp.value.trim()) { addChip(inp.value); inp.value = ""; }
    if (!chips.length) { el("cot_mail_estado").innerHTML = '<span class="text-danger">Agregá al menos un mail.</span>'; return; }
    var btn = el("cot_mail_enviar");
    btn.disabled = true;
    el("cot_mail_estado").innerHTML = '<span class="text-muted">Enviando...</span>';
    var body = new URLSearchParams({ action: "enviar", id: String(cotizacionId), mails: chips.join(",") });
    fetch("Informes/enviar_cotizacion_mail.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" }, body: body })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        btn.disabled = false;
        el("cot_mail_estado").innerHTML = res && res.ok
          ? '<span class="text-success">' + res.msg + "</span>"
          : '<span class="text-danger">' + ((res && res.error) || "No se pudo enviar") + "</span>";
      })
      .catch(function () { btn.disabled = false; el("cot_mail_estado").innerHTML = '<span class="text-danger">Error de red</span>'; });
  }

  // ------------------------------------------------------------ Cotizaciones guardadas
  function fmtFechaHora(s) {
    if (!s) return "";
    var p = String(s).replace("T", " ").split(/[- :]/);
    return p[2] + "/" + p[1] + "/" + p[0] + " " + (p[3] || "00") + ":" + (p[4] || "00");
  }

  function abrirLista() {
    var body = el("cot_lista_body");
    body.innerHTML = '<tr><td colspan="7" class="text-muted">Cargando…</td></tr>';
    el("cot_lista_estado").textContent = "";
    var m = window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(el("cot_modal_lista")) : null;
    if (m) m.show(); else $("#cot_modal_lista").modal("show");

    fetch(API, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" }, body: "action=listar" })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.ok) { body.innerHTML = '<tr><td colspan="7" class="text-danger">No se pudo cargar.</td></tr>'; return; }
        if (!res.data.length) { body.innerHTML = '<tr><td colspan="7" class="text-muted">Sin cotizaciones guardadas.</td></tr>'; return; }
        body.innerHTML = res.data
          .map(function (c) {
            return (
              '<tr style="cursor:pointer" class="cot-lista-row" data-id="' + c.id + '">' +
              "<td>" + c.id + "</td>" +
              "<td>" + fmtFechaHora(c.Fecha) + "</td>" +
              "<td>" + (c.Usuario || "-") + "</td>" +
              "<td>" + (c.Titulo || '<span class="text-muted">—</span>') + "</td>" +
              "<td>" + (c.RazonSocial || "-") + "</td>" +
              "<td>" + (c.OrigenLocalidad || "?") + " → " + (c.DestinoLocalidad || "?") + "</td>" +
              '<td class="text-end">' + money(c.Total) + "</td></tr>"
            );
          })
          .join("");
      })
      .catch(function () { body.innerHTML = '<tr><td colspan="7" class="text-danger">Error de red.</td></tr>'; });
  }

  function setVal(id, v) { var e = el(id); if (e) e.value = v == null ? "" : v; }

  function cargarCotizacion(id) {
    el("cot_lista_estado").textContent = "Cargando cotización #" + id + "…";
    fetch(API, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" }, body: "action=obtener&id=" + id })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.ok) { el("cot_lista_estado").innerHTML = '<span class="text-danger">' + ((res && res.error) || "No se pudo cargar") + "</span>"; return; }
        var c = res.cotizacion;

        setVal("cot_cliente", c.RazonSocial);
        setVal("cot_idcliente", c.idCliente || "");
        setVal("cot_titulo", c.Titulo || "");
        setVal("cot_obs", c.Observaciones || "");

        // modo + vehiculo
        var esKm = c.Modo === "km";
        el("cot_modo_km").checked = esKm;
        el("cot_modo_serv").checked = !esKm;
        el("cot_veh_wrap").classList.toggle("d-none", !esKm);
        if (esKm && c.idValorxKilometro) setVal("cot_vehiculo", c.idValorxKilometro);

        // adicionales
        el("cot_lleva_seguro").checked = Number(c.LlevaSeguro) === 1;
        el("cot_seguro_inputs").classList.toggle("d-none", Number(c.LlevaSeguro) !== 1);
        setVal("cot_valordeclarado", c.ValorDeclarado);
        setVal("cot_seguro_pct", c.SeguroPct);
        el("cot_lleva_cobranza").checked = Number(c.LlevaCobranza) === 1;
        el("cot_cobranza_inputs").classList.toggle("d-none", Number(c.LlevaCobranza) !== 1);
        setVal("cot_cobranza_base", c.CobranzaBase);
        setVal("cot_cobranza_pct", c.CobranzaPct);
        setVal("cot_viatico", c.Viatico);
        setVal("cot_demoras_monto", c.DemorasMonto);
        setVal("cot_demoras_min", c.DemorasMin);
        setVal("cot_desc_tipo", c.DescuentoTipo || "monto");
        setVal("cot_desc_valor", c.DescuentoValor);

        // paquetes
        var pj = {};
        try { pj = JSON.parse(c.PaquetesJSON || "{}"); } catch (e) {}
        paquetes = Array.isArray(pj) ? pj : (pj.input || []);
        renderPaquetes();

        // ruta: reconstruir la lista de paradas
        var wps = [];
        try { wps = JSON.parse(c.WaypointsJSON || "[]"); } catch (e) {}
        reconstruirStops(c.OrigenTexto, wps, c.DestinoTexto, c.OrigenLocalidad, c.DestinoLocalidad);
        if (c.OrigenLat && c.OrigenLng) setStop(stops[0].sid, Number(c.OrigenLat), Number(c.OrigenLng), c.OrigenTexto, null, c.OrigenLocalidad);
        if (c.DestinoLat && c.DestinoLng) setStop(stops[stops.length - 1].sid, Number(c.DestinoLat), Number(c.DestinoLng), c.DestinoTexto, null, c.DestinoLocalidad);
        el("cot_ruta_info").dataset.km = String(c.KmTotales || 0);
        el("cot_ruta_info").dataset.min = String(c.TiempoManejoMin || 0);
        el("cot_ruta_info").textContent = Number(c.KmTotales || 0).toFixed(1) + " km";

        // se mantiene el id: el proximo "Guardar" actualiza esta misma cotizacion.
        cotizacionId = Number(c.id) || 0;
        ultimoCalculo = null; // se recalcula abajo; evita que un guardar dispare antes de tiempo
        var m = window.bootstrap ? window.bootstrap.Modal.getInstance(el("cot_modal_lista")) : null;
        if (m) m.hide(); else $("#cot_modal_lista").modal("hide");
        irPaso(0);
        setTimeout(function () { calcularRuta(); }, 200);
        setTimeout(function () { calcular(); }, 1400);
        toast2("success", "Cotización", "Cargada. Revisá y recalculá si hace falta.");
      })
      .catch(function () { el("cot_lista_estado").innerHTML = '<span class="text-danger">Error de red.</span>'; });
  }

  // ------------------------------------------------------------ Wizard (pasos)
  var paso = 0;
  var PASOS = 3;
  function irPaso(n) {
    paso = Math.max(0, Math.min(PASOS - 1, n));
    document.querySelectorAll(".cot-paso").forEach(function (d) {
      d.classList.toggle("d-none", parseInt(d.getAttribute("data-paso"), 10) !== paso);
    });
    document.querySelectorAll(".cot-steps .nav-link").forEach(function (a) {
      a.classList.toggle("active", parseInt(a.getAttribute("data-paso"), 10) === paso);
    });
    el("cot_prev").classList.toggle("d-none", paso === 0);
    var ultimo = paso === PASOS - 1;
    el("cot_next").classList.toggle("d-none", ultimo);
    el("cot_calcular").classList.toggle("d-none", !ultimo);
  }

  // ------------------------------------------------------------ Pantalla inicial + Cotización Flex
  function mostrarSplash(v) {
    var s = el("cot_splash");
    if (s) s.classList.toggle("d-none", !v);
    if (!v && window.google && map) setTimeout(function () { google.maps.event.trigger(map, "resize"); }, 60);
  }

  function flexParams() {
    var p = new URLSearchParams();
    p.set("cliente", el("flex_cliente").value.trim());
    p.set("titulo", el("flex_titulo").value.trim());
    p.set("bonif", el("flex_bonif_colectas").checked ? "1" : "0");
    p.set("detalle", el("flex_bonif_detalle").value.trim());
    return p;
  }

  var flexChips = [];
  function renderFlexChips() {
    el("flex_mail_chips").innerHTML = flexChips
      .map(function (m) {
        return '<span class="badge bg-danger d-inline-flex align-items-center gap-1">' + m +
          '<a href="#" class="text-white flex-chip-x" data-m="' + m + '" style="text-decoration:none">&times;</a></span>';
      })
      .join("");
  }
  function addFlexChip(raw) {
    (raw || "").split(/[,;\s]+/).map(function (s) { return s.trim(); }).filter(Boolean).forEach(function (m) {
      if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(m) && flexChips.indexOf(m) < 0) flexChips.push(m);
    });
    renderFlexChips();
  }
  function enviarFlexMail() {
    var inp = el("flex_mail_input");
    if (inp.value.trim()) { addFlexChip(inp.value); inp.value = ""; }
    if (!flexChips.length) { el("flex_mail_estado").innerHTML = '<span class="text-danger">Agregá al menos un mail.</span>'; return; }
    var btn = el("flex_mail_enviar");
    btn.disabled = true;
    el("flex_mail_estado").innerHTML = '<span class="text-muted">Enviando...</span>';
    var body = flexParams();
    body.set("action", "enviar");
    body.set("mails", flexChips.join(","));
    fetch("Informes/enviar_propuesta_flex_mail.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" }, body: body })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        btn.disabled = false;
        el("flex_mail_estado").innerHTML = res && res.ok
          ? '<span class="text-success">' + res.msg + "</span>"
          : '<span class="text-danger">' + ((res && res.error) || "No se pudo enviar") + "</span>";
      })
      .catch(function () { btn.disabled = false; el("flex_mail_estado").innerHTML = '<span class="text-danger">Error de red</span>'; });
  }

  function wireSplashYFlex() {
    el("cot_go_full").addEventListener("click", function () { mostrarSplash(false); irPaso(0); });
    el("cot_go_flex").addEventListener("click", function () {
      var m = window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(el("cot_modal_flex")) : null;
      if (m) m.show(); else $("#cot_modal_flex").modal("show");
    });
    var bInicio = el("cot_inicio");
    if (bInicio) bInicio.addEventListener("click", function () { mostrarSplash(true); });

    el("flex_bonif_colectas").addEventListener("change", function () {
      el("flex_bonif_wrap").classList.toggle("d-none", !this.checked);
    });
    el("flex_pdf").addEventListener("click", function () {
      if (!el("flex_cliente").value.trim()) { el("flex_estado").innerHTML = '<span class="text-danger">Ingresá el nombre del cliente.</span>'; return; }
      el("flex_estado").textContent = "";
      window.open("Informes/PropuestaFlexPdf.php?" + flexParams().toString(), "_blank");
    });
    el("flex_mail").addEventListener("click", function () {
      if (!el("flex_cliente").value.trim()) { el("flex_estado").innerHTML = '<span class="text-danger">Ingresá el nombre del cliente.</span>'; return; }
      el("flex_estado").textContent = "";
      flexChips = []; renderFlexChips(); el("flex_mail_input").value = ""; el("flex_mail_estado").textContent = "";
      var m = window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(el("cot_modal_flex_mail")) : null;
      if (m) m.show(); else $("#cot_modal_flex_mail").modal("show");
    });
    el("flex_mail_input").addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === "," || e.key === ";") { e.preventDefault(); addFlexChip(this.value); this.value = ""; }
      else if (e.key === "Backspace" && this.value === "" && flexChips.length) { flexChips.pop(); renderFlexChips(); }
    });
    el("flex_mail_box").addEventListener("click", function () { el("flex_mail_input").focus(); });
    el("flex_mail_chips").addEventListener("click", function (e) {
      var a = e.target.closest(".flex-chip-x"); if (!a) return; e.preventDefault();
      flexChips = flexChips.filter(function (x) { return x !== a.getAttribute("data-m"); }); renderFlexChips();
    });
    el("flex_mail_enviar").addEventListener("click", enviarFlexMail);
  }

  // ------------------------------------------------------------ Salir sin guardar
  // El cartel nativo del navegador (beforeunload) es inevitable para cerrar la
  // pestaña, refrescar o tipear otra URL — los navegadores no dejan poner un
  // texto ni botones propios ahi, es una restriccion de seguridad. Lo que SI
  // podemos controlar es la navegacion dentro del sitio (un link del menu):
  // ahi mostramos un modal propio en vez de dejar que se pierda la cotizacion.
  function hayCambiosSinGuardar() {
    return !!(ultimoCalculo && !cotizacionId);
  }

  var salirPendienteHref = null;
  function mostrarModalSalir(href) {
    salirPendienteHref = href;
    var m = window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(el("cot_modal_salir")) : null;
    if (m) m.show(); else $("#cot_modal_salir").modal("show");
  }
  function ocultarModalSalir() {
    var m = window.bootstrap ? window.bootstrap.Modal.getInstance(el("cot_modal_salir")) : null;
    if (m) m.hide(); else $("#cot_modal_salir").modal("hide");
  }

  document.addEventListener("click", function (e) {
    if (!hayCambiosSinGuardar()) return;
    var a = e.target.closest("a[href]");
    if (!a || e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || a.target === "_blank") return;
    var href = a.getAttribute("href") || "";
    if (href === "" || href.charAt(0) === "#" || /^(javascript|mailto|tel):/i.test(href)) return;
    e.preventDefault();
    e.stopPropagation();
    mostrarModalSalir(a.href);
  }, true);

  // ------------------------------------------------------------ Init
  window.addEventListener("beforeunload", function (e) {
    if (hayCambiosSinGuardar()) {
      e.preventDefault();
      e.returnValue = "";
    }
  });

  document.addEventListener("DOMContentLoaded", function () {
    renderPaquetes();
    irPaso(0);
    wireSplashYFlex();
    el("cot_prev").addEventListener("click", function (e) { e.preventDefault(); irPaso(paso - 1); });
    el("cot_next").addEventListener("click", function (e) {
      e.preventDefault();
      if (paso === 1 && !paquetes.length) {
        toast2("error", "Cotizador", "Agregá al menos un paquete para continuar.");
        return;
      }
      irPaso(paso + 1);
    });
    document.querySelectorAll(".cot-steps .nav-link").forEach(function (a) {
      a.addEventListener("click", function (e) { e.preventDefault(); irPaso(parseInt(a.getAttribute("data-paso"), 10)); });
    });

    fetch(API, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" }, body: "action=opciones" })
      .then(function (r) { return r.json(); })
      .then(function (o) {
        if (o && o.ok) {
          // los vehiculos sin Valor x Km cargado (0) no se pueden usar en "Por km":
          // se muestran deshabilitados para no dejar elegir algo que va a dar $0.
          el("cot_vehiculo").innerHTML = o.vehiculos
            .map(function (v) {
              var sinTarifa = !(v.valorKm > 0);
              return '<option value="' + v.id + '"' + (sinTarifa ? " disabled" : "") + ">" +
                v.nombre + (sinTarifa ? " (sin tarifa cargada)" : " ($ " + v.valorKm + "/km)") + "</option>";
            })
            .join("");
          var primerApto = o.vehiculos.find(function (v) { return v.valorKm > 0; });
          if (primerApto) el("cot_vehiculo").value = String(primerApto.id);
          montoMinimoSeguro = Number(o.monto_minimo_seguro || 0);
          if (el("cot_seguro_hint")) {
            el("cot_seguro_hint").textContent = montoMinimoSeguro > 0
              ? "Incluye cobertura sin cargo hasta " + money(montoMinimoSeguro) + "; el % se cobra sobre el excedente."
              : "";
          }
        }
      });

    el("cot_add_wp").addEventListener("click", function (e) { e.preventDefault(); agregarParada(); });
    el("cot_calcular_ruta").addEventListener("click", function (e) { e.preventDefault(); calcularRuta(); });
    el("cot_add_paquete").addEventListener("click", function (e) { e.preventDefault(); abrirModalPaquete(); });
    el("cot_pq_guardar").addEventListener("click", guardarModalPaquete);
    ["cot_pq_ancho", "cot_pq_largo", "cot_pq_alto"].forEach(function (id) {
      el(id).addEventListener("input", calcularVolModal);
    });
    el("cot_paquetes_lista").addEventListener("click", function (e) {
      var del = e.target.closest(".cot-paq-del");
      if (del) { paquetes.splice(parseInt(del.getAttribute("data-i"), 10), 1); renderPaquetes(); return; }
      var ed = e.target.closest(".cot-paq-edit");
      if (ed) abrirModalPaquete(parseInt(ed.getAttribute("data-i"), 10));
    });
    el("cot_calcular").addEventListener("click", function (e) { e.preventDefault(); calcular(); });
    el("cot_guardar").addEventListener("click", function (e) { e.preventDefault(); guardar(); });

    el("cot_salir_cancelar").addEventListener("click", function () { ocultarModalSalir(); });
    el("cot_salir_descartar").addEventListener("click", function () {
      var href = salirPendienteHref;
      ultimoCalculo = null; // para que ni el modal ni el beforeunload vuelvan a preguntar
      ocultarModalSalir();
      if (href) window.location.href = href;
    });
    el("cot_salir_guardar").addEventListener("click", function () {
      var href = salirPendienteHref;
      var btn = el("cot_salir_guardar");
      btn.disabled = true;
      guardar(function (ok) {
        btn.disabled = false;
        if (!ok) return; // se queda en el modal; el estado del error ya se ve atras
        ocultarModalSalir();
        if (href) window.location.href = href;
      });
    });

    document.querySelectorAll('input[name="cot_modo"]').forEach(function (r) {
      r.addEventListener("change", function () {
        el("cot_veh_wrap").classList.toggle("d-none", modo() !== "km");
      });
    });

    el("cot_lleva_seguro").addEventListener("change", function () {
      el("cot_seguro_inputs").classList.toggle("d-none", !this.checked);
    });
    el("cot_lleva_cobranza").addEventListener("change", function () {
      el("cot_cobranza_inputs").classList.toggle("d-none", !this.checked);
    });

    el("cot_ver_lista").addEventListener("click", abrirLista);
    el("cot_lista_body").addEventListener("click", function (e) {
      var tr = e.target.closest(".cot-lista-row");
      if (tr) cargarCotizacion(tr.getAttribute("data-id"));
    });

    el("cot_pdf").addEventListener("click", function () {
      if (cotizacionId) window.open("Informes/CotizacionEnvioPdf.php?id=" + cotizacionId, "_blank");
    });
    el("cot_mail").addEventListener("click", function () {
      if (!cotizacionId) return;
      chips = []; renderChips(); el("cot_mail_input").value = ""; el("cot_mail_estado").textContent = "";
      var m = window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(el("cot_modal_mail")) : null;
      if (m) m.show(); else $("#cot_modal_mail").modal("show");
    });
    el("cot_mail_input").addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === "," || e.key === ";") { e.preventDefault(); addChip(this.value); this.value = ""; }
      else if (e.key === "Backspace" && this.value === "" && chips.length) { chips.pop(); renderChips(); }
    });
    el("cot_mail_box").addEventListener("click", function () { el("cot_mail_input").focus(); });
    el("cot_mail_chips").addEventListener("click", function (e) {
      var a = e.target.closest(".cot-chip-x"); if (!a) return; e.preventDefault();
      chips = chips.filter(function (x) { return x !== a.getAttribute("data-m"); }); renderChips();
    });
    el("cot_mail_enviar").addEventListener("click", enviarMail);
  });
})();
