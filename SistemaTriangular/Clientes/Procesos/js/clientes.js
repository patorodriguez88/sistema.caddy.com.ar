// $("#btn_imprimir_factura_modal").on("click", function () {
//   if (!facturaActualId) {
//     toast("error", "Error", "No hay factura seleccionada.");
//     return;
//   }

//   const urlImpresion =
//     "/SistemaTriangular/Clientes/invoice.php?id=" +
//     facturaActualId +
//     "&print=1";

//   const win = window.open(urlImpresion, "_blank");

//   if (!win) {
//     toast("error", "Error", "El navegador bloqueó la ventana de impresión.");
//   }
// });
$("#btn_imprimir_factura_modal").on("click", function () {
  const iframe = document.getElementById("iframe_factura_preview");

  if (!iframe) {
    toast("error", "Error", "No se encontró el visor de factura.");
    return;
  }

  const onLoadPrint = function () {
    try {
      iframe.contentWindow.focus();
      setTimeout(function () {
        iframe.contentWindow.print();
      }, 200);
    } catch (e) {
      console.error("Error al imprimir desde el modal:", e);
      toast("error", "Error", "No se pudo imprimir la factura.");
    }
  };

  try {
    if (
      iframe.contentDocument &&
      iframe.contentDocument.readyState === "complete"
    ) {
      onLoadPrint();
    } else {
      iframe.onload = onLoadPrint;
    }
  } catch (e) {
    console.error("Error al acceder al iframe:", e);
    toast("error", "Error", "La factura no está lista para imprimir.");
  }
});
function cargarRubrosEnSelect(selectId) {
  $.ajax({
    url: "Procesos/php/clientes.php", // ajustá ruta según tu estructura
    data: { cargar_rubros: 1 },
    type: "POST",
    dataType: "json",
    success: function (rubros) {
      const select = document.getElementById(selectId);
      if (!select) return;

      // Limpiar opciones anteriores
      select.innerHTML = "";

      // Crear el optgroup
      const optgroup = document.createElement("optgroup");
      optgroup.label = "Rubro";

      rubros.forEach(function (rubro) {
        const option = document.createElement("option");
        option.value = rubro.id;
        option.textContent = rubro.Rubro;
        optgroup.appendChild(option);
      });

      select.appendChild(optgroup);
    },
    error: function (err) {
      console.error("Error al cargar rubros:", err);
    },
  });
}

// Uso: cuando esté listo el DOM o al cargar un modal
cargarRubrosEnSelect("rubro");

function cargarRelacionEnSelect(selectId) {
  $.ajax({
    url: "Procesos/php/clientes.php", // ajustá ruta según tu estructura
    data: { cargar_relacion: 1 },
    type: "POST",
    dataType: "json",
    success: function (relaciones) {
      const select = document.getElementById(selectId);
      if (!select) return;

      // Limpiar opciones anteriores
      select.innerHTML = "";

      // Crear el optgroup
      const optgroup = document.createElement("optgroup");
      optgroup.label = "Relacion";

      relaciones.forEach(function (relacion) {
        const option = document.createElement("option");
        option.value = relacion.id;
        option.textContent = relacion.Relacion;
        optgroup.appendChild(option);
      });

      select.appendChild(optgroup);
    },
    error: function (err) {
      console.error("Error al cargar Relaciones:", err);
    },
  });
}
cargarRelacionEnSelect("");
