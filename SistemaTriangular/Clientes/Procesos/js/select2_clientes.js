function initSelect2Clientes(selector, placeholder, dropdownParent) {
  $(selector).select2({
    placeholder: placeholder || undefined,
    allowClear: !!placeholder,
    dropdownParent: dropdownParent || undefined,
    ajax: {
      url: "Procesos/php/select2_clientes.php",
      type: "get",
      dataType: "json",
      delay: 250,
      data: function (params) {
        return {
          searchTerm: params.term /* search term */,
        };
      },
      processResults: function (response) {
        return {
          results: response,
        };
      },
      cache: true,
    },
  });
}

$(document).ready(function () {
  initSelect2Clientes("#buscarcliente");

  // #agregar_relacionado vive en la pestaña Relaciones, que arranca oculta
  // (display:none). Select2 calcula mal el ancho si lo inicializás mientras el
  // contenedor está oculto, y el buscador queda roto (no trae resultados) hasta
  // que se reinicializa con el tab ya visible — por eso se inicializa recién al
  // mostrar esa pestaña por primera vez, no acá en el load de la página.
  var agregarRelacionadoInicializado = false;
  $("#botonrelacion").on("shown.bs.tab", function () {
    if (agregarRelacionadoInicializado) return;
    agregarRelacionadoInicializado = true;
    initSelect2Clientes(
      "#agregar_relacionado",
      "Buscar cliente para relacionar...",
    );
  });

  // #relacion_nc vive en el modal "Crear Nuevo Cliente", oculto hasta que se
  // abre. Mismo problema de ancho que #agregar_relacionado si se inicializa
  // antes de que el modal esté visible, así que se inicializa recién al
  // mostrarse por primera vez.
  var relacionNcInicializado = false;
  $("#nuevocliente-modal-lg").on("shown.bs.modal", function () {
    if (relacionNcInicializado) return;
    relacionNcInicializado = true;
    initSelect2Clientes(
      "#relacion_nc",
      "Buscar cliente para relacionar (opcional)...",
      $("#nuevocliente-modal-lg"),
    );
  });
});
