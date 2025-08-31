$(document).ready(function () {
  $("#seguimiento").DataTable({
    ajax: {
      url: "Procesos/php/dashboard.php",
      type: "POST",
      dataSrc: "data",
    },
    columns: [
      { data: "Fecha" },
      { data: "POR_ARRIBAR" },
      { data: "PROC_PREPARACION" },
      { data: "PROC_DESPACHO" },
      { data: "PROCESO_DISTRIBUCION" },
      { data: "PROC_ADM_DEVOLUCION" },
      { data: "Total" },
    ],
    paging: false,
    ordering: false,
    searching: false,
  });
});
