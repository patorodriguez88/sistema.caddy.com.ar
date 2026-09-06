document.addEventListener("DOMContentLoaded", function () {
  const params = new URLSearchParams(window.location.search);

  if (params.get("Error") === "Si") {
    Swal.fire({
      icon: "error",
      title: "Error de acceso",
      text: "Tu sesión ha expirado o no tenés permisos para acceder a esta sección.",
      confirmButtonText: "Aceptar",
    }).then(() => {
      // Limpiamos la URL sin recargar la página
      params.delete("Error");
      const newUrl =
        window.location.pathname +
        (params.toString() ? "?" + params.toString() : "");
      window.history.replaceState({}, document.title, newUrl);
    });
  }
});
// La botonera queda dentro de un <div class="table-responsive"> (o una card)
// que tiene overflow:auto/hidden - eso recortaba los botones por la mitad
// (verticalmente). Se libera el overflow del wrapper cuando contiene una tabla
// de DataTables, y de paso el overflow-hidden que la extension pone en cada
// dt-button (que recortaba el icono). NO se toca el ancho de los botones.
// Se inyecta una sola vez por pagina.
(function inyectarCssBotoneraDt() {
  if (typeof document === "undefined") return;
  if (document.getElementById("dt-botonera-fix")) return;
  var st = document.createElement("style");
  st.id = "dt-botonera-fix";
  st.textContent =
    "div.dt-buttons .dt-button,div.dt-buttons .btn{overflow:visible}" +
    "div.dt-buttons .btn .mdi{vertical-align:middle;line-height:1}" +
    "div.dt-buttons{white-space:normal}" +
    ".table-responsive:has(.dataTables_wrapper),.table-responsive:has(.dt-buttons){overflow:visible}" +
    "div.dataTables_wrapper>.dt-buttons,div.dataTables_wrapper div.dataTables_length{overflow:visible}";
  (document.head || document.documentElement).appendChild(st);

  // Fallback para navegadores sin :has() - libera el .table-responsive que
  // haya quedado envolviendo una tabla de DataTables.
  function liberarWrappers() {
    var nodos = document.querySelectorAll(".table-responsive");
    for (var i = 0; i < nodos.length; i++) {
      if (nodos[i].querySelector(".dataTables_wrapper, .dt-buttons")) {
        nodos[i].style.overflow = "visible";
      }
    }
  }
  document.addEventListener("DOMContentLoaded", function () {
    liberarWrappers();
    setTimeout(liberarWrappers, 600);
    setTimeout(liberarWrappers, 1500);
  });
})();

// El boton "Registros por pagina" (pageLength) que ahora va primero en cada
// botonera trae el texto en ingles ("Show N rows"). Se traduce una sola vez a
// nivel global para todas las tablas, sin tocar cada init.
(function traducirPageLength() {
  if (typeof window === "undefined" || !window.jQuery) return;
  var $ = window.jQuery;
  if (!$.fn || !$.fn.dataTable) return;
  $.extend(true, $.fn.dataTable.defaults, {
    language: {
      buttons: {
        pageLength: {
          _: "Mostrar %d registros",
          "-1": "Mostrar todos",
        },
      },
    },
  });
})();

// Botones de exportacion de DataTables (Bfrtip): por default salen con la
// clase que trae la extension (btn-secondary, gris parejo) y sin icono.
// buildDtButtons() arma la misma config pero con estilo "soft" + icono por
// accion - se llama como buttons: buildDtButtons(["pageLength","copy","excel","pdf"])
// en vez de pasar el array de strings pelado.
var DT_BUTTON_ESTILOS = {
  pageLength: { className: "btn-soft-secondary" },
  copy: { className: "btn-soft-primary", icon: "mdi-content-copy", texto: "Copiar" },
  csv: { className: "btn-soft-info", icon: "mdi-file-delimited-outline", texto: "CSV" },
  excel: { className: "btn-soft-success", icon: "mdi-file-excel-outline", texto: "Excel" },
  pdf: { className: "btn-soft-danger", icon: "mdi-file-pdf-box", texto: "PDF" },
  print: { className: "btn-soft-dark", icon: "mdi-printer-outline", texto: "Imprimir" },
};

// Arma UN boton estilizado - se usa cuando el resto de las paginas necesitan
// tocar solo el color/icono de un boton particular sin perder una
// configuracion de export custom que ya tenia (title, filename, orientation,
// customize, exportOptions, etc.) - fusiona lo propio arriba de lo generado
// asi lo especifico de esa pagina nunca se pierde.
function dtButtonConfig(claveOExtra, conMargen) {
  var clave = typeof claveOExtra === "string" ? claveOExtra : claveOExtra.extend;
  var extra = typeof claveOExtra === "string" ? {} : claveOExtra;
  var estilo = DT_BUTTON_ESTILOS[clave] || {};

  var clases = "btn btn-sm " + (estilo.className || "btn-soft-secondary");
  if (conMargen) clases += " me-1";

  var boton = { extend: clave, className: clases };
  if (estilo.icon) {
    boton.text = '<i class="mdi ' + estilo.icon + ' me-1"></i>' + estilo.texto;
  }
  // extra primero: lo propio de esa pagina (title, filename, orientation,
  // customize, exportOptions...) se conserva, pero className/text/extend
  // siempre los pisa el estilo unificado.
  return Object.assign({}, extra, boton);
}

function buildDtButtons(claves) {
  var lista = claves.map(function (clave, idx) {
    return dtButtonConfig(clave, idx < claves.length - 1);
  });

  return {
    dom: { button: { className: "" } },
    buttons: lista,
  };
}

//FUNCION TOAST

// toast("error", "Error", "No se pudo procesar el pago");
// toast("warning", "Atención", "El importe es incorrecto");
// toast("success", "Perfecto", "Pago registrado correctamente");

function toast(tipo, titulo, mensaje) {
  Swal.fire({
    toast: true,
    position: "bottom-end",
    icon: tipo,
    title: titulo,
    text: mensaje,
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
  });
}
// FUNCTION ALERTAS
// alerta("error", "Error!", jsonData.data.message);
// alerta("success", "Guardado", "El registro se guardó correctamente");
// alerta("warning", "Atención!", "El importe no puede ser 0 ni nulo");
// alerta(
//   "info",
//   "Información",
//   "El pago se registró pero no se pudo enviar el email de notificación",
// );
// alerta(
//   "question",
//   "¿Estás seguro?",
//   "¿Deseas eliminar este registro? Esta acción no se puede deshacer.",
// );
function alerta(tipo, titulo, mensaje) {
  Swal.fire({
    icon: tipo,
    title: titulo,
    html: mensaje,
    confirmButtonText: "Aceptar",
    confirmButtonColor: "#10c469",
  });
}

// FUNCIONES PARA EL MODAL GENERICO DE "CARGANDO..." (#info-alert-modal,
// reusado en HojaDeRuta2/Zonas/etc con su propio -title dentro de cada
// pagina) - Bootstrap 5 ignora modal("hide") si todavia esta a mitad de la
// transicion de modal("show") (chequea un flag interno _isTransitioning).
// En pedidos que resuelven muy rapido (ej. localhost, sin latencia real de
// red) el success/error de un $.ajax puede llegar y llamar hide() antes de
// que termine de animarse el show(), y el modal queda trabado para siempre
// con el spinner girando - nunca pasaba con pedidos mas lentos (ej. Routes
// API de Google) porque para cuando llegaba la respuesta el show() ya habia
// terminado hace rato. Se corrige esperando el evento shown.bs.modal antes
// de ocultar si el modal todavia no termino de mostrarse.
//
// mostrarModalCarga("#info-alert-modal", "Cargando...");
// ocultarModalCarga("#info-alert-modal");
function mostrarModalCarga(selector, titulo) {
  if (titulo) {
    $(selector + "-title").html(titulo);
  }
  $(selector).modal("show");
}

function ocultarModalCarga(selector) {
  const $el = $(selector);
  const el = $el.get(0);
  if (!el) return;
  if (el.classList.contains("show")) {
    $el.modal("hide");
  } else {
    $el.one("shown.bs.modal", function () {
      $el.modal("hide");
    });
  }
}

// FIX: Bootstrap 5 no maneja bien un modal abierto arriba de otro (ej. el par
// "Modificar Guia" / "Modificar Venta" en Clientes.php) - cada modal nuevo y
// su backdrop se agregan con el mismo z-index base, asi que el backdrop del
// modal de ADENTRO puede terminar quedando ARRIBA del contenido del modal de
// AFUERA sin tapar visualmente nada raro, pero el click cae en ese backdrop
// en vez del boton (ej. "Guardar Cambios" no hacia nada). Se sube el z-index
// de cada modal nuevo y su backdrop segun cuantos modales ya estan abiertos,
// para que el ultimo en abrirse siempre quede arriba de todo.
$(document).on("show.bs.modal", ".modal", function () {
  var zIndex = 1055 + 20 * $(".modal:visible").length;
  $(this).css("z-index", zIndex);
  setTimeout(function () {
    $(".modal-backdrop")
      .not(".modal-stack")
      .css("z-index", zIndex - 5)
      .addClass("modal-stack");
  }, 0);
});
