$(document).ready(function () {
  // Inicializar DataTable vacío
  let hayDias = false; // debe estar en alcance general
  let tabla = $("#tabla_recorrido").DataTable({
    columns: [
      { title: "CodigoSeguimiento" },
      { title: "Origen" },
      { title: "Destino" },
      { title: "Teléfono" },
      { title: "CobrarEnvio" },
      { title: "", orderable: false }, // Checkbox
    ],
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "Todos"],
    ],
    pageLength: 10,
    language: {
      lengthMenu: "Mostrar _MENU_ registros por página",
      zeroRecords: "No se encontraron resultados",
      info: "Mostrando página _PAGE_ de _PAGES_",
      infoEmpty: "No hay registros disponibles",
      infoFiltered: "(filtrado de _MAX_ registros totales)",
      search: "Buscar:",
      paginate: {
        next: "Siguiente",
        previous: "Anterior",
      },
    },
  });

  $.ajax({
    url: "Procesos/php/enviar_wp.php",
    type: "POST",
    dataType: "json",
    data: { accion: "listar_recorridos" },
    success: function (respuesta) {
      const data = respuesta.data;
      const grupos = {};
      data.forEach((item) => {
        if (!grupos[item.Grupo]) grupos[item.Grupo] = [];
        grupos[item.Grupo].push(item);
      });

      const $select = $("#select_recorrido");
      $select
        .empty()
        .append('<option value="">Seleccione un recorrido...</option>');

      Object.entries(grupos).forEach(([grupo, items]) => {
        const $optgroup = $("<optgroup>", { label: grupo });
        items.forEach((i) => {
          $optgroup.append(
            $("<option>", {
              value: i.Numero,
              text: i.Numero + " | " + i.Nombre + " (" + i.Total + ")",
            })
          );
        });
        $select.append($optgroup);
      });

      if ($.fn.select2) {
        $select.select2();
      }
    },
  });

  $("#select_recorrido").on("change", function () {
    let recorrido = $(this).val();
    if (!recorrido) return;

    $("#loaderOverlay").fadeIn(); // Mostrar overlay
    let ajaxDiasCompletado = false;
    let ajaxTablaCompletado = false;

    function verificarCargaCompleta() {
      if (ajaxDiasCompletado && ajaxTablaCompletado) {
        $("#loaderOverlay").fadeOut(); // Ocultar overlay
      }
    }

    $.ajax({
      url: "Procesos/php/enviar_wp.php",
      type: "POST",
      data: {
        accion: "recorrido_dias",
        recorrido_dias_recorrido: recorrido,
      },
      dataType: "json",
      success: function (fechas) {
        const dias = fechas?.data ?? fechas;
        hayDias = Array.isArray(dias) && dias.length > 0;

        $("#select_dias").empty(); // limpia opciones previas

        if (hayDias) {
          dias.forEach(function (d) {
            const texto = typeof d === "string" ? d : d.dia;
            $("#select_dias").append(
              $("<option>", {
                value: texto,
                text: texto,
              })
            );
          });
        } else {
          // 🔁 Fallback: generar próximos 7 días corridos
          const diasSemana = [
            "Domingo",
            "Lunes",
            "Martes",
            "Miércoles",
            "Jueves",
            "Viernes",
            "Sábado",
          ];
          const hoy = new Date();
          for (let i = 0; i < 7; i++) {
            const fecha = new Date();
            fecha.setDate(hoy.getDate() + i);

            const nombreDia = diasSemana[fecha.getDay()];
            const dd = String(fecha.getDate()).padStart(2, "0");
            const mm = String(fecha.getMonth() + 1).padStart(2, "0");
            const yyyy = fecha.getFullYear();

            const texto = `${nombreDia} ${dd}/${mm}/${yyyy}`;

            $("#select_dias").append(
              $("<option>", {
                value: texto,
                text: texto,
              })
            );
          }
        }
        ajaxDiasCompletado = true;
        verificarCargaCompleta();
      },
      error: function () {
        $("#select_dias")
          .empty()
          .append(
            $("<option>", {
              value: "",
              text: "Error al cargar días disponibles",
            })
          );
        ajaxDiasCompletado = true;
        verificarCargaCompleta();
      },
    });

    $.ajax({
      url: "Procesos/php/enviar_wp.php",
      type: "POST",
      dataType: "json",
      data: {
        accion: "obtener_clientes",
        recorrido: $("#select_recorrido").val(),
      },
      success: function (data) {
        let filas = data.data.map(function (fila) {
          const clienteOrigenPlano = fila[0];
          const clienteDestinoPlano = fila[1];
          const telefonoPlano = fila[2];
          const codigoSeguimientoPlano = fila[3];
          const CobrarEnvioPlano = fila[5];
          const estadoNotificado = fila[4];

          const codigoSeguimiento = `<span class="codigoSeguimiento">${codigoSeguimientoPlano}</span>`;
          const clienteOrigen = `<span class="clienteOrigen">${clienteOrigenPlano}</span>`;
          const clienteDestino = `<span class="clienteDestino">${clienteDestinoPlano}</span>`;
          const telefono = `<span class="telefono">${telefonoPlano}</span>`;
          const CobrarEnvio = `<span class="CobrarEnvio">${CobrarEnvioPlano}</span>`;

          let control;
          if (estadoNotificado && estadoNotificado !== "") {
            switch (estadoNotificado) {
              case "Confirmada":
                control = `<span class="badge bg-success text-white">${estadoNotificado}</span>`;
                break;
              case "Reprogramada":
                control = `<span class="badge bg-warning text-white">${estadoNotificado}</span>`;
                break;
              case "Cancelada":
                control = `<span class="badge bg-danger text-white">${estadoNotificado}</span>`;
                break;
              default:
                control = `<span class="badge bg-info text-white">${estadoNotificado}</span>`;
            }
          } else {
            const soloNumeros = /^\d+$/;
            const disabledAttr = soloNumeros.test(telefonoPlano)
              ? ""
              : "disabled";
            control = `<input type="checkbox" class="marcar-telefono" value="${telefonoPlano}" ${disabledAttr}>`;
            // control = `<input type="checkbox" class="marcar-telefono" value="${telefonoPlano}">`;
          }

          return [
            codigoSeguimiento,
            clienteOrigen,
            clienteDestino,
            telefono,
            CobrarEnvio,
            control,
          ];
        });

        tabla.clear().rows.add(filas).draw();
        tabla.page.len(-1).draw(); // Mostrar todos

        ajaxTablaCompletado = true;
        verificarCargaCompleta();
      },
      error: function () {
        ajaxTablaCompletado = true;
        verificarCargaCompleta();
      },
    });
  });

  $("#btn_enviar_seleccionados").on("click", function () {
    let datos = [];

    $(".marcar-telefono:checked").each(function () {
      const fila = $(this).closest("tr");

      datos.push({
        CodigoSeguimiento: fila.find(".codigoSeguimiento").text().trim(),
        Origen: fila.find(".clienteOrigen").text().trim(),
        Destino: fila.find(".clienteDestino").text().trim(),
        telefono: $(this).val(),
        CobrarEnvio: new Intl.NumberFormat("es-AR", {
          style: "currency",
          currency: "ARS",
        }).format(
          parseFloat(
            fila
              .find(".CobrarEnvio")
              .text()
              .trim()
              .replace(/\./g, "")
              .replace(",", ".")
          )
        ),
      });
    });

    if (datos.length === 0) {
      Swal.fire({
        icon: "warning",
        title: "Sin selección",
        text: "⚠️ No seleccionaste ningún teléfono para notificar.",
        confirmButtonText: "OK",
      });
      return;
    }

    // Mostrar confirmación
    Swal.fire({
      icon: "question",
      title: "Confirmar envío",
      html: `Vas a notificar a <b>${datos.length}</b> cliente(s) por WhatsApp.<br>¿Estás seguro de continuar?`,
      showCancelButton: true,
      confirmButtonText: "Sí, enviar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) {
        // Mostrar spinner y deshabilitar botón
        $("#spinner_envio").removeClass("d-none");
        $("#btn_enviar_seleccionados").prop("disabled", true);
        $("#texto_envio").text("Enviando...");

        let dia_inicio = $("#select_dias").val();
        // 🔥 Definir la acción condicional

        // const accionFinal = hayDias
        //   ? "template_twilio_interior.php"
        //   : "template_twilio.php";

        // 🔁 Podés usar esta variable en otro $.ajax o guardarla
        console.log("hayDias:", hayDias);
        // console.log("Acción final:", accionFinal);
        console.log("dia_inicio:", dia_inicio);

        $.ajax({
          // url: "template_confirmar_entrega_caddy.php",
          url: "template_twilio_interior.php",
          type: "POST",
          dataType: "json",
          data: {
            accion: "enviar_mensajes",
            dia_inicio: dia_inicio,
            mensajes: JSON.stringify(datos),
          },
          success: function (respuesta) {
            Swal.fire({
              icon: respuesta.status === "ok" ? "success" : "warning",
              title: "Resultado del envío",
              text: respuesta.mensaje || "✅ Proceso finalizado.",
              confirmButtonText: "OK",
            }).then(() => {
              if (respuesta.status === "ok") {
                // 🔄 Volver a cargar los datos del recorrido actual
                $("#select_recorrido").trigger("change");
              }
            });
          },
          error: function () {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "❌ Hubo un problema al enviar los mensajes.",
              confirmButtonText: "Reintentar",
            });
          },
          complete: function () {
            $("#spinner_envio").addClass("d-none");
            $("#btn_enviar_seleccionados").prop("disabled", false);
            $("#texto_envio").text("Enviar seleccionados");
          },
        });

        console.log("Mensajes a enviar:", datos);
      }
    });
  });

  $(document).on("change", "#marcar_todos", function () {
    const marcar = this.checked;
    $(".marcar-telefono").each(function () {
      // Solo marcar si el checkbox está habilitado (no badge)
      if (!$(this).prop("disabled")) {
        $(this).prop("checked", marcar);
      }
    });
  });

  $(document).on("change", ".marcar-telefono", function () {
    if (!this.checked) {
      $("#marcar_todos").prop("checked", false);
    }
  });

  $(document).on("click", "#btnReprogramarEntrega", function () {
    let recorrido = $("#select_recorrido").val();

    console.log("boton dos ", recorrido);

    if (recorrido) {
      $("#multiple-tree").modal("show");

      $.ajax({
        url: "Procesos/php/enviar_wp.php",
        type: "POST",
        data: {
          accion: "recorrido_dias",
          recorrido_dias_recorrido: recorrido,
        },
        dataType: "json",
        success: function (fechas) {
          const dias = fechas?.data ?? fechas;
          hayDias = Array.isArray(dias) && dias.length > 0;

          $("#select_dias").empty(); // limpia opciones previas

          if (hayDias) {
            dias.forEach(function (d) {
              const texto = typeof d === "string" ? d : d.dia;
              $("#select_dias").append(
                $("<option>", {
                  value: texto,
                  text: texto,
                })
              );
            });
          } else {
            // 🔁 Fallback: generar próximos 5 días a partir de lo seleccionado en #select_dias
            const fechaBaseStr = $("#select_dias").val(); // Ej: "Martes 04/06/2025"
            if (!fechaBaseStr) {
              $("#select_dias").append(
                $("<option>", {
                  value: "",
                  text: "⚠️ No hay fecha base definida",
                })
              );
              return;
            }

            const partes = fechaBaseStr.split(" ");
            if (partes.length < 2) {
              $("#select_dias").append(
                $("<option>", {
                  value: "",
                  text: "⚠️ Fecha base inválida",
                })
              );
              return;
            }

            const fechaTexto = partes[1]; // Ej: "04/06/2025"
            const [dd, mm, yyyy] = fechaTexto.split("/");
            const fechaBase = new Date(`${yyyy}-${mm}-${dd}`);
            fechaBase.setDate(fechaBase.getDate() + 1); // arrancar desde el siguiente día

            const diasSemana = [
              "Domingo",
              "Lunes",
              "Martes",
              "Miércoles",
              "Jueves",
              "Viernes",
              "Sábado",
            ];

            for (let i = 0; i < 5; i++) {
              const fecha = new Date(fechaBase);
              fecha.setDate(fechaBase.getDate() + i);

              const nombreDia = diasSemana[fecha.getDay()];
              const dd = String(fecha.getDate()).padStart(2, "0");
              const mm = String(fecha.getMonth() + 1).padStart(2, "0");
              const yyyy = fecha.getFullYear();
              const texto = `${nombreDia} ${dd}/${mm}/${yyyy}`;

              $("#select_dias").append(
                $("<option>", {
                  value: texto,
                  text: texto,
                })
              );
            }
          }
        },
      });
    }
  });

  // Escuchar clic en botón de selección de fecha
  $(document).on("click", ".seleccionar-fecha", function () {
    const fechaElegida = $(this).data("fecha");

    // Cerrar el modal actual
    $("#multiple-tree").modal("hide");

    // Esperar a que se cierre y abrir el siguiente con la fecha cargada
    $("#multiple-tree").on("hidden.bs.modal", function () {
      $("#multiple-four .modal-body").html(`
      <p> 📆 Elegiste el día <strong>${fechaElegida}</strong>.<br><br>
      ¿Preferís recibirlo:<br>
      1️⃣ Por la mañana (8 a 13)<br>
      2️⃣ Por la tarde (15 a 19)?</p>
    `);
      $("#multiple-four").modal("show");
    });
  });

  $("#multiple-one").on("show.bs.modal", function () {
    $("#fecha_seleccionada").html($("#select_dias").val());
  });
  $("#multiple-two").on("show.bs.modal", function () {
    $("#fecha_seleccionada_1").html($("#select_dias").val());
  });
});
