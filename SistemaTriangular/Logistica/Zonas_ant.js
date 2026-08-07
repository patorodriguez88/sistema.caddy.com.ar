// =========================
// Globals (UNA sola vez)
// =========================
let selected = []; // ✅ UNA sola declaración
let map = null;
let rectangle = null;
let infoWindow = null;
let markers = [];
let zona = null;

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
    <div class="card-header" id="${safeHeaderId}" data-zona="${nombre}">
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

// Cargar recorridos
$.ajax({
data: { BuscarRecorridos: 1 },
type: "POST",
url: "Proceso/php/pendientes.php",
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
}

function clearRectangle() {
if (rectangle) {
google.maps.event.clearInstanceListeners(rectangle);
rectangle.setMap(null);
rectangle = null;
}
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
if (zona) renderZona(zona);
}

// =========================
// Render de zona (update overlays)
// =========================
function renderZona(z) {
zona = z || zona || null;
if (!zona) return;

// Si maps todavía no cargó, initMap() se ejecuta por callback y luego renderZona
if (!map) return;

// limpiar overlays anteriores
clearRectangle();
clearMarkers();

// 1) bounds + rectangle
$.ajax({
data: { Buscar: 1, zona: zona, rec: selected },
url: "Mapas/php/zonas.php",
type: "POST",
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

if (
![bounds.north, bounds.south, bounds.east, bounds.west].every(
Number.isFinite
)
) {
console.warn("Bounds inválidos desde backend", jsonData);
return;
}

rectangle = new google.maps.Rectangle({
bounds,
editable: true,
draggable: true,
map,
});

rectangle.addListener("bounds_changed", showNewRect);
rectangle.addListener("click", showNewRect);

// centrar en la zona
const gBounds = new google.maps.LatLngBounds(
{ lat: bounds.south, lng: bounds.west },
{ lat: bounds.north, lng: bounds.east }
);
map.fitBounds(gBounds);
},
});

// 2) markers (tu endpoint actual)
const xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function () {
if (xhttp.readyState === 4 && xhttp.status === 200) {
const objeto_json = JSON.parse(xhttp.responseText);

for (let i = 0; i < objeto_json.data.length; i++) {
    const latlong=(objeto_json.data[i].coordenadas || "" ).split(",");
    const myLatLng={
    lat: Number(latlong[0]),
    lng: Number(latlong[1]),
    };

    if (!Number.isFinite(myLatLng.lat) || !Number.isFinite(myLatLng.lng))
    continue;

    // Mantengo tu icono de colores
    function pinSymbol(color) {
    return {
    path: "M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z" ,
    fillColor: "#" + color,
    fillOpacity: 1,
    strokeColor: "#FFFFFF" ,
    strokeWeight: 1,
    scale: 1,
    };
    }

    const icono=pinSymbol(objeto_json[0][i]);

    const marker=new google.maps.Marker({
    position: myLatLng,
    map,
    title: objeto_json.data[i].nombrecliente || "" ,
    icon: icono,
    });

    markers.push(marker);
    }
    }
    };

    xhttp.open("POST", "Mapas/php/datos_zonas.php" , true);
    xhttp.send();
    }

    //=========================// Eventos
    //=========================// Click header zona=> render
    $(document).on("click", "#zonas_accordion .card-header", function () {
    const z = $(this).data("zona");
    if (!z) return;

    zona = z;
    $(".header-title").html("Zonas google Maps " + zona);
    renderZona(zona);
    });

    // Cambio recorrido => recalcular zona actual
    $("#select_rec_mapa").change(function () {
    selected = [];

    $(this)
    .find("option:selected")
    .each(function (i, e) {
    selected.push(e.value);
    });

    // ✅ NO initMap() (eso solo inicializa), renderiza la zona
    renderZona(zona);
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
    if (zona === nombre) {
    zona = null;
    clearRectangle();
    clearMarkers();
    }
    } else {
    alert(res.error || "No se pudo eliminar la zona.");
    }
    },
    error: function () {
    alert("Error de comunicación al eliminar la zona.");
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
    $("#info-alert-modal").modal("hide");
    toast("success", "Exito !", "Se movieron " + jsonData.cuenta + " registros.!");
    renderZona(zona);
    }
    },
    });
    });