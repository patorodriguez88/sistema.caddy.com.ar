$(document).ready(function () {
  $("#card_tabla").hide();
  paneles();
  // renderizar_datos() ya no se llama aca: necesita saber para que Recorrido
  // refrescar Roadmap_end, y a esta altura todavia no se eligio ninguno (se
  // dispara desde veo() apenas se abre un recorrido puntual).
});

// Refresca Roadmap_end (tabla de apoyo para el mapa/orden) para UN Recorrido
// puntual. Antes truncaba y reconstruia la tabla entera para TODA la
// empresa en cada carga de pantalla, aunque despues solo se lee filtrada
// por un Recorrido a la vez - devuelve el jqXHR para poder encadenar
// (.then) la lectura del mapa despues de que termine de refrescarse.
function renderizar_datos(recorrido) {
  return $.ajax({
    data: { Renderizar: 1, Recorrido: recorrido },
    type: "POST",
    url: "Proceso/php/roadmap_end.php",
    beforeSend: function () {
      // setting a timeout
      $("#info-alert-modal").modal("show");
      $("#info-alert-modal-title").html("Renderizando Tabla Roadmap");
    },
    success: function (response) {
      $("#info-alert-modal").modal("hide");
    },
    // Antes no habia manejo de error aca: si esta llamada fallaba (network,
    // 500, timeout), el modal "Renderizando Tabla Roadmap" quedaba abierto
    // para siempre, sin ningun aviso.
    error: function (jqXHR, textStatus, errorThrown) {
      $("#info-alert-modal").modal("hide");
      console.error("Error en renderizar_datos:", textStatus, errorThrown);
      toast("error", "Error", "No se pudo actualizar el orden del recorrido. Reintentá de nuevo.");
    },
  });
}

function color(c, r) {
  $.ajax({
    data: { Color: 1, Recorrido: r, ColorSeleccionado: c },
    type: "POST",
    url: "Proceso/php/funciones_hdr.php",
    beforeSend: function () {
      // setting a timeout
      $("#info-alert-modal").modal("show");
      $("#info-alert-modal-title").html("Actualizando...");
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      $("#info-alert-modal").modal("hide");
    },
    error: function (jqXHR, textStatus, errorThrown) {
      $("#info-alert-modal").modal("hide");
      console.error("Error en color():", textStatus, errorThrown);
    },
  });
}

//FUNCION PARA MOSTRAR LOS PANELES
function paneles() {
  $.ajax({
    data: { FormaDePago: 1 },
    type: "POST",
    url: "Proceso/php/funciones_hdr.php",
    beforeSend: function () {
      // setting a timeout
      $("#info-alert-modal").modal("show");
      $("#info-alert-modal-title").html("Cargando Hojas de Ruta");
    },
    success: function (response) {
      $("#info-alert-modal").modal("hide");
      $("#hdractivas").html(response).fadeIn();
    },
    error: function (jqXHR, textStatus, errorThrown) {
      $("#info-alert-modal").modal("hide");
      console.error("Error en paneles():", textStatus, errorThrown);
      toast("error", "Error", "No se pudieron cargar las Hojas de Ruta. Recargá la página.");
    },
  });
}

//BOTONERA CAMBIAR RECORRIDO
$("#cambiar_recorrido").click(function () {
  $("#hdractivas").show();
  $("#card_mapa").hide();
  $("#card_tabla").hide();
  paneles();
});

//BOTONERA VER TODOS
$("#todos_recorrido").click(function () {
  $.ajax({
    data: { Todos: 1 },
    type: "POST",
    url: "Mapas/php/datos_hojaderuta.php",
    success: function (response) {
      var jsonData = JSON.parse(response);
      $("#header-title").html("Servicios Pendientes");
      $("#card_tabla").show();
      var c = "t";
      // initMap(c);
      ensureGoogleMapsLoaded("initMap_order")
        .then(() => {
          initMap(c);
        })
        .catch((e) => {
          console.error(e);
        });
      var datatable = $("#seguimiento").DataTable();
      datatable.ajax.reload();
      $("#ordenar_recorrido").css("display", "none");
    },
  });
});

function veo(i) {
  // Refresca Roadmap_end para ESTE recorrido antes de pedir los datos del
  // mapa, que leen de esa misma tabla - si no se espera, se puede leer una
  // version vieja (o vacia, si justo se estaba truncando la version global
  // de antes) de los datos.
  renderizar_datos(i).then(function () {
    $.ajax({
      data: { Mapa: 1, Rec: i },
      type: "POST",
      url: "Mapas/php/datos_hojaderuta.php",
      success: function (response) {
        // Cierre defensivo: renderizar_datos() ya debería haber cerrado el
        // modal en su propio success, pero si por algun motivo (timing,
        // varios clicks seguidos) quedo abierto, esto lo garantiza apenas
        // llegan los datos del mapa.
        $("#info-alert-modal").modal("hide");
        var jsonData = JSON.parse(response);
        $("#recorrido").html(jsonData.Recorrido);
        $("#hdractivas").hide();
        $("#card_mapa").show();
        $("#header-title2").html(jsonData.Color);
        if (jsonData.NombreChofer == "") {
          if (jsonData.Estado == "Alta") {
            var color = "warning";
          }
          if (jsonData.Estado == "Cargada") {
            var color = "success";
          }
          if (jsonData.Estado == "Cerrada") {
            var color = "danger";
          }

          $("#header-title").html(
            "Servicios Pendientes Recorrido " +
              jsonData.Recorrido +
              ' Estado <a class="text-' +
              color +
              '"> ' +
              jsonData.Estado +
              "</a>",
          );
        } else {
          $("#header-title").html(
            "Servicios Pendientes Recorrido " +
              jsonData.Recorrido +
              ' Estado <a class="text-' +
              color +
              '"> ' +
              jsonData.Estado +
              "</a> Chofer: " +
              jsonData.NombreChofer,
          );
        }
        // Trazabilidad: quien ordeno este recorrido, cuando, y con que metodo
        // (Manual / Automatico / Gestya).
        if (jsonData.UltimoOrdenUsuario) {
          var fechaOrden = "";
          if (jsonData.UltimoOrdenFecha) {
            var partes = jsonData.UltimoOrdenFecha.split(" ");
            var dia = partes[0].split("-").reverse().slice(0, 2).join("/");
            fechaOrden = " el " + dia + " " + (partes[1] || "").substring(0, 5);
          }
          $("#orden_trazabilidad")
            .html(
              '<i class="mdi mdi-map-marker-path"></i> Ordenado por <b>' +
                jsonData.UltimoOrdenUsuario +
                "</b>" +
                fechaOrden +
                (jsonData.UltimoOrdenMetodo
                  ? ' <span class="badge bg-light text-dark">' + jsonData.UltimoOrdenMetodo + "</span>"
                  : ""),
            )
            .show();
        } else {
          $("#orden_trazabilidad").hide();
        }

        $("#card_tabla").show();
        $("#ordenar_recorrido").css("display", "block");
        // initMap(jsonData.Color);
        ensureGoogleMapsLoaded("initMap_order")
          .then(() => {
            initMap(jsonData.Color);
          })
          .catch((e) => {
            console.error(e);
          });
        var datatable = $("#seguimiento").DataTable();
        datatable.ajax.reload();
      },
      // Sin esto, un fallo aca (network, 500, JSON invalido) no mostraba
      // nada - la pantalla se quedaba en el estado anterior sin ningun
      // aviso de que algo salio mal.
      error: function (jqXHR, textStatus, errorThrown) {
        $("#info-alert-modal").modal("hide");
        console.error("Error en datos_hojaderuta.php (Mapa):", textStatus, errorThrown);
        toast("error", "Error", "No se pudo cargar el mapa del recorrido. Reintentá de nuevo.");
      },
    });
  });
}

function imprimirhdr(i) {
  alert(i);
  var myWindow = window.open("", "_self");
  myWindow.document.write("<p>I replaced the current window.</p>");
}

function eliminarrecorrido(i) {
  $("#warning-modal-body").html("Estas por eliminar el Recorrido " + i);
  $("#id_eliminar").val(i);
}
