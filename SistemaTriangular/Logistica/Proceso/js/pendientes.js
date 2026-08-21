// Umbral para avisar que el horario planificado (HojaDeRuta.Hora, se pisa
// cuando se ordena el recorrido - manual, "Ordenar segun Reparto", o "Ver
// Ruta" -> "Aceptar Ruta") se aleja demasiado del horario que pidio el
// cliente (TransClientes.HorarioEntregaSolicitado) - mismo criterio (60 min)
// que ya usa ordenarPorCercania() en orden_automatico.php para priorizar
// paradas urgentes al armar el orden.
var UMBRAL_DISCREPANCIA_HORARIO_MIN = 60;

function horaAMinutos(hhmm) {
  if (!hhmm) return null;
  var partes = String(hhmm).split(":");
  if (partes.length < 2) return null;
  var h = parseInt(partes[0], 10);
  var m = parseInt(partes[1], 10);
  if (isNaN(h) || isNaN(m)) return null;
  return h * 60 + m;
}

// TransClientes.HorarioEntregaSolicitado (de esta venta puntual) manda si
// esta cargado; si no, cae a Clientes.HorarioEntregaSolicitado (la
// preferencia de base del cliente, ver HorarioEntregaSolicitadoCliente que
// agrega pendientes.php). TransClientes.HorarioEntregaSolicitado solo se
// completa hoy desde el flujo normal de Ventas.php - Colecta/Flex y las
// altas desde TiendaNube/Meli/importacion masiva nunca lo cargan aunque el
// cliente sí tenga la preferencia guardada en su ficha.
function horarioSolicitadoEfectivo(row) {
  return row.HorarioEntregaSolicitado || row.HorarioEntregaSolicitadoCliente || "";
}

// Icono de aviso junto al nombre del cliente cuando el horario planificado
// para esta parada se aleja mas de UMBRAL_DISCREPANCIA_HORARIO_MIN del
// horario que el cliente pidio - sin esto, un recorrido ya ordenado podia
// dejar una entrega horas antes/despues de lo solicitado sin que quedara
// visible en ningun lado hasta que el cliente reclamara.
function avisoHorarioIcono(row) {
  if (row.Retirado != 1) return ""; // el horario solicitado es de entrega, no de retiro
  var horarioSolicitado = horarioSolicitadoEfectivo(row);
  var minSolicitado = horaAMinutos(horarioSolicitado);
  var minPlanificado = horaAMinutos(row.Hora);
  if (minSolicitado === null || minPlanificado === null) return "";

  var diferencia = minPlanificado - minSolicitado;
  if (Math.abs(diferencia) <= UMBRAL_DISCREPANCIA_HORARIO_MIN) return "";

  var texto =
    diferencia > 0
      ? "Va a llegar ~" + Math.round(diferencia / 60) + "h después de lo solicitado"
      : "Va a llegar ~" + Math.round(-diferencia / 60) + "h antes de lo solicitado";

  return (
    ' <i class="mdi mdi-18px mdi-alert text-warning" title="Horario solicitado: ' +
    horarioSolicitado +
    " · Horario planificado: " +
    row.Hora +
    " · " +
    texto +
    '"></i>'
  );
}

function geocodeResult(results, status) {
  // Verificamos el estatus
  if (status == "OK") {
    // Si hay resultados encontrados, centramos y repintamos el mapa
    // esto para eliminar cualquier pin antes puesto
    // fitBounds acercará el mapa con el zoom adecuado de acuerdo a lo buscado
    map.fitBounds(results[0].geometry.viewport);
    // Dibujamos un marcador con la ubicación del primer resultado obtenido
    var markerOptions = {
      position: results[0].geometry.location,
      animation: google.maps.Animation.BOUNCE,
      labelContent: "A",
    };
    var marker = new google.maps.Marker(markerOptions);

    marker.setMap(map);
    map.setZoom(12);
    marker.addListener("click", eliminar);
    //     }

    function eliminar() {
      marker.setMap(null);
    }
  } else {
    // En caso de no haber resultados o que haya ocurrido un error
    // lanzamos un mensaje con el error
    alert("Geocoding no tuvo éxito debido a: " + status);
  }
}

function initialize() {
  // initMap();
  ensureGoogleMapsLoaded("initMap_order")
    .then(() => {
      initMap();
    })
    .catch((e) => {
      console.error(e);
    });
  //    BuscarDireccion();
}

var _acDirInit = false;
$("#standard-modal-dir").on("show.bs.modal", function (e) {
  if (!_acDirInit) { BuscarDireccion(); _acDirInit = true; }
});

function _googleAcDebounce(inputEl, opciones) {
  var cfg = Object.assign(
    { fields: ["address_components"], componentRestrictions: { country: "AR" },
      types: ["geocode", "establishment"], debounce: 400, minLength: 3, onSelect: null },
    opciones || {}
  );
  var svc = new google.maps.places.AutocompleteService();
  var placeSvc = new google.maps.places.PlacesService(document.createElement("div"));
  var token = new google.maps.places.AutocompleteSessionToken();
  var timer = null;
  var wrapper = inputEl.parentElement;
  if (getComputedStyle(wrapper).position === "static") wrapper.style.position = "relative";
  var ul = document.createElement("ul");
  ul.style.cssText = "position:absolute;z-index:99999;width:100%;top:100%;left:0;display:none;max-height:220px;" +
    "overflow-y:auto;border-radius:0 0 4px 4px;list-style:none;padding:0;margin:0;" +
    "background:#fff;border:1px solid rgba(0,0,0,.15);box-shadow:0 .25rem .5rem rgba(0,0,0,.1);";
  wrapper.appendChild(ul);
  function close() { ul.style.display = "none"; }
  function selectPlace(placeId, description) {
    inputEl.value = description; close();
    placeSvc.getDetails({ placeId: placeId, fields: cfg.fields, sessionToken: token }, function (place, status) {
      token = new google.maps.places.AutocompleteSessionToken();
      if (status === google.maps.places.PlacesServiceStatus.OK && cfg.onSelect) cfg.onSelect(place);
    });
  }
  inputEl.addEventListener("input", function () {
    clearTimeout(timer);
    var val = this.value.trim();
    if (val.length < cfg.minLength) { close(); return; }
    var snap = val;
    timer = setTimeout(function () {
      svc.getPlacePredictions(
        { input: snap, sessionToken: token, componentRestrictions: cfg.componentRestrictions, types: cfg.types },
        function (predictions, status) {
          ul.innerHTML = "";
          if (status !== google.maps.places.PlacesServiceStatus.OK || !predictions) { close(); return; }
          predictions.forEach(function (p) {
            var li = document.createElement("li");
            li.style.cssText = "padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #f0f0f0;";
            li.textContent = p.description;
            li.addEventListener("mouseover", function () { this.style.background = "#f5f5f5"; });
            li.addEventListener("mouseout", function () { this.style.background = ""; });
            li.addEventListener("mousedown", function (e) { e.preventDefault(); selectPlace(p.place_id, p.description); });
            ul.appendChild(li);
          });
          ul.style.display = "block";
        }
      );
    }, cfg.debounce);
  });
  inputEl.addEventListener("blur", function () { setTimeout(close, 200); });
}

function BuscarDireccion() {
  var inputstart = document.getElementById("direccion_nc");
  if (!inputstart) return;
  _googleAcDebounce(inputstart, {
    onSelect: function (place) {
      if (!place || !place.address_components) return;
      place.address_components.forEach(function (c) {
        var t = c.types[0];
        if (t === "locality") document.getElementById("ciudad_nc").value = c.long_name;
        else if (t === "postal_code") document.getElementById("cp_nc").value = c.short_name;
        else if (t === "neighborhood") document.getElementById("Barrio_nc").value = c.long_name;
        else if (t === "street_number") document.getElementById("Numero_nc").value = c.long_name;
        else if (t === "route") document.getElementById("Calle_nc").value = c.long_name;
      });
    },
  });
}

function modificardir(e) {
  $.ajax({
    data: { BuscarDatosClienteDestino: 1, id: e },
    url: "Proceso/php/pendientes.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);

      $("#standard-modal-dir").modal("show");
      $("#myCenterModalLabel").html(
        "Modificar Direccion a " + jsonData.data[0].ClienteDestino
      );
      $("#direccion_nc").val(jsonData.data[0].DomicilioDestino);
      $("#id_nc").val(jsonData.data[0].idClienteDestino);
      $("#cs_nc").val(jsonData.data[0].CodigoSeguimiento);
      $("#latitud_nc").val(jsonData.data[0].Latitud);
      $("#longitud_nc").val(jsonData.data[0].Longitud);
      $("#observaciones_nc").val(jsonData.data[0].Observaciones);
      // if($('#switch1').is(":checked")){
      console.log("vero", jsonData.data[0].ActivarCoordenadas);
      if (jsonData.data[0].ActivarCoordenadas == 1) {
        // $('#switch1').prop("checked");
        // $('#switch1'). siblings('input').prop("checked");
        // $('#switch1').attr('checked', 'true');
        $("#switch1").prop("checked", "true");
        $("#switch1").attr("value", "1");
      } else {
        $("#switch1").prop("checked", "");
        $("#switch1").attr("value", "0");
      }
    },
  });
}

$("#switch1").click(function () {
  if ($("#switch1").is(":checked")) {
    $("#latitud_nc").prop("disabled", false);
    $("#longitud_nc").prop("disabled", false);
    $("#google_import_nc").prop("disabled", false);

    $("#latitud_nc").css("background", "");
    $("#longitud_nc").css("background", "");
    $("#google_import_nc").css("background", "");
  } else {
    $("#latitud_nc").prop("disabled", true);
    $("#longitud_nc").prop("disabled", true);
    $("#google_import_nc").prop("disabled", true);

    $("#longitud_nc").css("background", "#D5D3D3");
    $("#latitud_nc").css("background", "#D5D3D3");
    $("#google_import_nc").css("background", "#D5D3D3");
  }
});

$("#standard-modal-dir").on("shown.bs.modal", function () {
  $("#google_import_nc").val("");
  // $('#switch1').val('off');
  // $("#switch1").prop('checked','off');
  // $('#switch1').removeAttr('checked');
  // $('#switch1').prop('checked','');
  $("#latitud_nc").prop("disabled", true);
  $("#longitud_nc").prop("disabled", true);
  $("#google_import_nc").prop("disabled", true);
  $("#longitud_nc").css("background", "#D5D3D3");
  $("#latitud_nc").css("background", "#D5D3D3");
  $("#google_import_nc").css("background", "#D5D3D3");
});

$("#google_import_nc").bind("paste", function (e) {
  //Ejecutar función
  // access the clipboard using the api
  var pastedData = e.originalEvent.clipboardData.getData("text");
  var lat = pastedData.split(",")[0];
  var lon = Number(pastedData.split(",")[1]);
  $("#latitud_nc").val(lat);
  $("#longitud_nc").val(lon);
});

function servicio_mod(i, v) {
  // console.log('Servicio id',i);

  if (v == "0") {
    var nuevo_val_servicio = "Entrega";
    var Retirado = 1;
  } else {
    var nuevo_val_servicio = "Retiro";
    var Retirado = 0;
  }

  $("#servicio_modal").modal("show");
  $("#servicio_modal-body").html(
    "Estas por modificar el servicio " + i + " a " + nuevo_val_servicio
  );
  $("#servicio_id_trans").val(i);
  $("#servicio_retirado").val(Retirado);
}

$("#ok_servicio_modal").click(function () {
  let i = $("#servicio_id_trans").val();
  let Retirado = $("#servicio_retirado").val();

  $.ajax({
    data: { MarcaRetirado: 1, id_trans: i, Retirado: Retirado },
    url: "Proceso/php/pendientes.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == 1) {
        // initMap() lee de Roadmap_end - hay que esperar a que termine de
        // refrescarse para este recorrido antes de recargar el mapa.
        renderizar_datos($("#recorrido").html()).then(function () {
          ensureGoogleMapsLoaded("initMap_order")
            .then(() => {
              initMap();
            })
            .catch((e) => {
              console.error(e);
            });
        });
        var datatable = $("#seguimiento").DataTable();
        datatable.ajax.reload(null, false);

        toast("success", "Registro Actualizado !", "Se ha actualizado el Servicio.");
      } else {
        toast("error", "Registro No Actualizado !", "No pudimos actualizar el Servicio.");
      }
      $("#servicio_modal").modal("hide");
    },
  });
});

$("#modificardir_ok").click(function () {
  var dir = $("#direccion_nc").val();
  var calle = $("#Calle_nc").val();
  var barrio = $("#Barrio_nc").val();
  var numero = $("#Numero_nc").val();
  var ciudad = $("#ciudad_nc").val();
  var cp = $("#cp_nc").val();
  var id = $("#id_nc").val();
  var cs = $("#cs_nc").val();
  var obs = $("#observaciones_nc").val();
  var lat = $("#latitud_nc").val();
  var lon = $("#longitud_nc").val();

  if ($("#switch1").is(":checked")) {
    var modificar_lat_lon = 1;
  } else {
    var modificar_lat_lon = 0;
  }

  var origen = "Reconquista 4986, Cordoba, Argentina";

  $.ajax({
    data: {
      ActualizarDireccion: 1,
      modificar_lat_lon_manual: modificar_lat_lon,
      Direccion: dir,
      id: id,
      calle: calle,
      barrio: barrio,
      numero: numero,
      ciudad: ciudad,
      cp: cp,
      cs: cs,
      obs: obs,
      lat: lat,
      lon: lon,
    },
    url: "Proceso/php/pendientes.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      var datatable = $("#seguimiento").DataTable();
      datatable.ajax.reload();

      $("#standard-modal-dir").modal("hide");
      var color = $("#header-title2").html();

      // initMap(color);
      ensureGoogleMapsLoaded("initMap_order")
        .then(() => {
          initMap(color);
        })
        .catch((e) => {
          console.error(e);
        });
    },
  });
});

$(document).ready(function () {
  var datatable = $("#seguimiento").DataTable({
    dom: "Bfrtip",
    buttons: ["pageLength"],
    paging: true,
    searching: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],
    ajax: {
      url: "Proceso/php/pendientes.php",
      data: { Pendientes: 1 },
      processing: true,
      type: "post",
    },
    columns: [
      {
        data: "Posicion",
        render: function (data, type, row) {
          if (row.HdrEstado == "Abierto") {
            var colororden = "success";
          } else {
            var colororden = "danger";
          }

          if (row.Retirado == 1) {
            return `<div class="avatar-xs"><span class="avatar-title bg-${colororden} rounded ">${row.Posicion}</span></div></div>`;
          } else {
            return (
              `<div class="btn-group mb-2">` +
              `<div class="avatar-xs"><span class="avatar-title bg-warning rounded mr-1">${row.Posicion_retiro}</span></div>` +
              `<div class="avatar-xs ml-1"><span class="avatar-title bg-${colororden} rounded ">${row.Posicion}</span></div></div>`
            );
          }
        },
      },
      {
        data: "Fecha",
        render: function (data, type, row) {
          //    console.log([0].Latitud);
          var Fecha = row.Fecha.split("-").reverse().join(".");
          // Hora estimada del paso pendiente (calculada al Ordenar segun
          // Reparto / desde el Planificador) y, si el operador lo cargo,
          // el horario de entrega solicitado por el cliente - se muestran
          // abajo de la fecha en vez de sumar columnas nuevas.
          var horaEstimada = row.Retirado == 1 ? row.Hora : row.Hora_retiro;
          var lineas = "";
          if (horaEstimada) {
            lineas +=
              '<br><small class="text-muted"><i class="mdi mdi-clock-outline"></i> ' +
              horaEstimada.substring(0, 5) +
              "</small>";
          }
          var horarioSolicitado = horarioSolicitadoEfectivo(row);
          if (horarioSolicitado) {
            lineas +=
              '<br><small class="text-info"><i class="mdi mdi-clock-alert-outline"></i> Solicitado ' +
              horarioSolicitado.substring(0, 5) +
              "</small>";
          }
          return (
            '<td><span style="display: none;">' +
            row.Fecha +
            "</span>" +
            Fecha +
            lineas +
            "</td>"
          );
        },
      },
      {
        data: "RazonSocial",
        render: function (data, type, row) {
          if (row.Retirado == 0) {
            var color = "success";
          } else {
            color = "muted";
          }

          return (
            "<td><b>" +
            row.RazonSocial +
            "</br>" +
            '<i class="mdi mdi-18px mdi-map-marker text-' +
            color +
            '"></i><a class="text-muted">' +
            row.DomicilioOrigen +
            "</td>"
          );
        },
      },
      {
        data: "DomicilioDestino",
        render: function (data, type, row) {
          // Antes solo se marcaba en rojo si faltaba la Latitud, o si
          // coincidia con este par exacto hardcodeado (fallback conocido:
          // geocoding que no encontro la direccion y cayo al centro de
          // Cordoba) - no cubria lat/lng en 0,0 ni coordenadas fuera de
          // Argentina, que tampoco se van a poder usar para armar la ruta
          // (mismo criterio de validez que usan Planificador/orden_automatico.php).
          var lat = parseFloat(row.Latitud);
          var lng = parseFloat(row.Longitud);
          var esFallbackConocido =
            row.Latitud == "-31.41972520387455" &&
            row.Longitud == "-64.18901825595384";
          var coordenadaValida =
            !esFallbackConocido &&
            !!lat &&
            !!lng &&
            lat <= -21 &&
            lat >= -55 &&
            lng <= -53 &&
            lng >= -75;

          var color1;
          var titulo1 = "";
          if (!coordenadaValida) {
            color1 = "danger";
            titulo1 = "Sin coordenadas válidas - hacé click para corregir la dirección";
          } else if (row.Retirado == 1) {
            color1 = "success";
          } else {
            color1 = "muted";
          }

          return (
            "<td><b>" +
            row.ClienteDestino +
            avisoHorarioIcono(row) +
            "</br>" +
            '<a data-id="' +
            row.id +
            '" id="' +
            row.id +
            '" onclick="modificardir(this.id);"class="action-icon" title="' +
            titulo1 +
            '">' +
            '<i class="mdi mdi-18px mdi-map-marker text-' +
            color1 +
            '"></i><a class="text-muted">' +
            row.DomicilioDestino +
            "</td>"
          );
        },
      },
      { data: "LocalidadDestino" },
      {
        data: "CodigoSeguimiento",
        render: function (data, type, row) {
          if (row.Retirado == 1) {
            var color = "success";
            var servicio = "Entrega";
          } else {
            var color = "warning";
            var servicio = "Retiro";
          }
          if (row.Retirado == 1) {
            return `<td class="table-action"><a>${row.NumeroComprobante}</a><br/><a>${row.CodigoSeguimiento}</a><br/><a><b><a value='${servicio}' href='#' class='badge badge-${color} mb-1 mt-1' style='font-size:10px' onclick='servicio_mod(${row.id},${row.Retirado})'>${servicio}</a></b></a><br/><a href='#' class='badge badge-success' style='font-size:10px'>${row.Hora}</a></td></td>`;
          } else {
            return `<td class="table-action"><a>${row.NumeroComprobante}</a><br/><a>${row.CodigoSeguimiento}</a><br/><a><b><a value='${servicio}' href='#' class='badge badge-${color} mb-1 mt-1' style='font-size:10px' onclick='servicio_mod(${row.id},${row.Retirado})'>${servicio}</a></b></a><br/><a href='#' class='badge badge-warning' style='font-size:10px'>${row.Hora_retiro}</a><br/><a href='#' class='badge badge-success mt-1' style='font-size:10px'>${row.Hora}</a></td></td>`;
          }
        },
      },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          return (
            '<td class="table-action">' +
            '<a style="cursor:pointer" data-id="' +
            row.CodigoSeguimiento +
            '" id="' +
            row.CodigoSeguimiento +
            '" onclick="modificarrecorrido(this.id);" ><b class="text-primary">' +
            row.Recorrido +
            "</b></a>" +
            "</td>"
          );
        },
      },
      {
        data: "id",
        render: function (data, type, row) {
          let myLatLng = row.Latitud + "," + row.Longitud;

          return `<td class="table-action d-print-none mt-4"><a data-id="${myLatLng}" id="${myLatLng}" onclick="ubicacion(this.id);" class="action-icon"> <i class="mdi mdi-18px mdi-map-marker text-danger"></i></a><a data-id="${row.id}" id="${row.id}" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-pencil text-warning"></i></a><a data-id="${row.id}" id="${row.id}" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-trash-can text-danger"></i></a></td>`;
          // return `<td class="table-action d-print-none mt-4"><a data-id="${row.DomicilioDestino}" id="${row.DomicilioDestino}" onclick="ubicacion(this.id);" class="action-icon"> <i class="mdi mdi-18px mdi-map-marker text-danger"></i></a><a data-id="${row.id}" id="${row.id}" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-pencil text-primary"></i></a><a data-id="${row.id}" id="${row.id}" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-delete text-danger"></i></a></td>`;
        },
      },
    ],
  });
});

$("#entregado").change(function (e) {
  if (this.checked) {
    $("#entregado").val(1);
  } else {
    $("#entregado").val(0);
  }
});

function ubicacion(i) {
  // Obtenemos la dirección y la asignamos a una variable
  var address = i;
  // Creamos el Objeto Geocoder
  var geocoder = new google.maps.Geocoder();
  // Hacemos la petición indicando la dirección e invocamos la función
  // geocodeResult enviando todo el resultado obtenido
  geocoder.geocode({ address: address }, geocodeResult);

  //     var latitudReal = -27.798521169850478;
  //     var longitudReal = -63.683109002298416;
  //     var markerPosicionReal = new google.maps.Marker({
  //         position: {
  //           lat: latitudReal,
  //           lng: longitudReal
  //         },
  //         title: "Mi actual ubicación"
  //     });
  //     markerPosicionReal.setMap(map);
  //     // Si quieres centrar el mapa en el nuevo marker:
  //     map.setCenter(markerPosicionReal.getPosition());
}

function modificarrecorrido(i) {
  $("#cs_modificar_REC").val(i);
  $.ajax({
    data: { BuscarRecorridos: 1, cs: i },
    type: "POST",
    url: "Proceso/php/pendientes.php",
    success: function (response) {
      $(".selector-recorrido select").html(response).fadeIn();
    },
  });

  $("#myCenterModalLabel_rec").html("Modificar Recorrido a Código " + i);
  $("#standard-modal-rec").modal("show");
}

$("#modificarrecorrido_ok").click(function () {
  var cs = $("#cs_modificar_REC").val();
  var r = $("#recorrido_t").val();

  var datatable = $("#seguimiento").DataTable();
  datatable.ajax.reload();
  // initMap();
  ensureGoogleMapsLoaded("initMap_order")
    .then(() => {
      initMap();
    })
    .catch((e) => {
      console.error(e);
    });
  $("#standard-modal-rec").modal("hide");

  // Llamada a la API para actualizar el recorrido
  fetch("../Funciones/Funciones.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      ActualizaRecorrido: 1,
      cs: cs,
      r: r,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success === 1) {
        // console.log("✅ Cambio realizado correctamente");
        // console.log("Número de orden:", data.numerodeorden);
        // console.log("TC actualizado:", data.tc_updated);
        // console.log("Hoja de ruta actualizada:", data.hdr_updated);
        // console.log("Seguimiento insertado:", data.seg_inserted);
        var datatable = $("#seguimiento").DataTable();
        datatable.ajax.reload();
        // initMap();
        ensureGoogleMapsLoaded("initMap_order")
          .then(() => {
            initMap();
          })
          .catch((e) => {
            console.error(e);
          });
        $("#standard-modal-rec").modal("hide");

        // podés mostrar un Swal o refrescar tabla
        Swal.fire({
          icon: "success",
          title: "Recorrido actualizado",
          text: `Orden N° ${data.numerodeorden || "(sin hoja abierta)"}`,
          timer: 2500,
          showConfirmButton: false,
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: data.message,
        });
      }
    })
    .catch((err) => {
      console.error("Error en fetch cambio_recorrido:", err);
      Swal.fire({
        icon: "error",
        title: "Error de conexión",
        text: "No se pudo conectar con el servidor.",
      });
    });
});

function modificar(i) {
  $("#id_modificar").val(i);
  $("#standard-modal").modal("show");
  $("#myCenterModalLabel").html("Modificar id # " + i);
}

$("#modificardireccion_ok").click(function () {
  var entregado = $("#entregado").val();
  var Fecha = $("#fecha_receptor").val();
  var hora = $("#hora_receptor").val();
  var i = $("#id_modificar").val();
  var obs = $("#observaciones_receptor").val();
  $("#myCenterModalLabel").html("Modificar id # " + i);

  if (entregado == 1) {
    $.ajax({
      data: {
        Actualiza: 1,
        id: i,
        entregado: entregado,
        Fecha: Fecha,
        Hora: hora,
        Observaciones: obs,
      },
      url: "Procesos/php/pendientes.php",
      type: "post",
      success: function (response) {
        var jsonData = JSON.parse(response);
        toast("success", "Registro Actualizado !", "Se ha actualizado la tabla Clientes correctamente.");
        var datatable = $("#seguimiento").DataTable();
        datatable.ajax.reload();
        $("#standard-modal").modal("hide");
        $("#form")[0].reset();
      },
    });
  } else {
    toast("warning", "Presione Entregado !", "No se realizaron cambios.");
  }
});

function eliminar(e) {
  $.ajax({
    data: { BuscarDatos: 1, id: e },
    url: "Proceso/php/pendientes.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      $("#warning-modal-body").html(
        "Estas por eliminar el Registro " +
          e +
          " Origen " +
          jsonData.RazonSocial
      );
      $("#id_eliminar").val(e);
      $("#codigoseguimiento_eliminar").val(jsonData.CodigoSeguimiento);
      $("#warning-modal").modal("show");
    },
  });
}
$("#warning-modal-ok").click(function () {
  var id = $("#id_eliminar").val();
  var cs = $("#codigoseguimiento_eliminar").val();
  $.ajax({
    data: { EliminarRegistro: 1, id: id, CodigoSeguimiento: cs },
    url: "Proceso/php/pendientes.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      $("#warning-modal").modal("hide");
      if (jsonData.success == 1) {
        if (jsonData.hojaderuta == 1) {
          toast("success", "Registro Borrado !", "Se ha borrado el registro en Hoja de Ruta correctamente.");
          var datatable = $("#seguimiento").DataTable();
          datatable.ajax.reload();
        } else {
          toast("error", "Error !", "No se han realizado cambios en Hoja de Ruta.");
        }
        if (jsonData.transclientes == 1) {
          toast("success", "Registro Borrado !", "Se ha borrado el registro en Trans Clientes correctamente.");
          var datatable = $("#seguimiento").DataTable();
          datatable.ajax.reload();
        } else {
          toast("error", "Error !", "No se han realizado cambios en Trans Clientes.");
        }
      } else {
        toast("error", "Error !", "No se han realizado cambios.");
      }
    },
  });
});
