document.addEventListener("DOMContentLoaded", function () {
  let desactivarValidacion = false;

  obtenerCuentas();
  obtener_asiento();
  document.getElementById("n_asiento").readOnly = true; // para solo lectura

  const hoy = new Date();
  const dia = String(hoy.getDate()).padStart(2, "0");
  const mes = String(hoy.getMonth() + 1).padStart(2, "0");
  const anio = hoy.getFullYear();

  // ✅ Formato compatible con <input type="date">
  const fechaFormateada = `${anio}-${mes}-${dia}`;
  document.getElementById("example-date").value = fechaFormateada;
  const modificarCard = document.getElementById("card_buscar_asiento");
  const nuevoCard = document.getElementById("card_nuevo_asiento");
  const cardsumasysaldos = document.getElementById(
    "card_informes_sumas_y_saldos"
  );
  const cardvisorpdf = document.getElementById("visor_pdf");
  const cardbusquedaasientos = document.getElementById(
    "card_busqueda_asientos"
  );
  //Botones Menu

  // Resalta el item del menu lateral (nav-pills) que se clickeo, mismo
  // estilo que usa Empleados/Usuarios.php - como acá las secciones no son
  // pestañas reales de Bootstrap (cada botón muestra/oculta cards a mano),
  // el estado "active" tampoco se maneja solo y hay que togglearlo manual.
  document.querySelectorAll(".caddy-nav-pills .nav-link").forEach((link) => {
    link.addEventListener("click", function () {
      document
        .querySelectorAll(".caddy-nav-pills .nav-link")
        .forEach((otro) => otro.classList.remove("active"));
      this.classList.add("active");
    });
  });

  document
    .getElementById("btn_modificar_asiento")
    .addEventListener("click", function () {
      modificarCard.style.display = "block";
      nuevoCard.style.display = "none";
      cardvisorpdf.style.display = "none";
      cardbusquedaasientos.style.display = "none";
      $("#card_informes").css("display", "none");
      $("#card_resultado_asientos").hide();
      $("#card_informes_mayor").hide();
      cargarCuentasParaBusqueda();
    });

  document
    .getElementById("btn_nuevo_asiento")
    .addEventListener("click", function () {
      $("#campos-container").empty();
      obtenerCuentas();
      obtener_asiento();
      agregarCampo();
      // El numero que trae obtener_asiento() es solo una propuesta (el
      // proximo disponible) - todavia no existe en Tesoreria, asi que no
      // hay nada que imprimir hasta que se confirme.
      $("#btnImprimirAsiento").hide();
      // calcularTotalAsiento();
      // ✅ Opcional: poner un valor inicial para evitar la alerta
      setTimeout(() => {
        const debeInput = document.querySelector("input[name='debe[]']");
        if (debeInput) debeInput.value = "0.00";
        const haberInput = document.querySelector("input[name='haber[]']");
        if (haberInput) haberInput.value = "0.00";
        calcularTotalAsiento();
      }, 50);

      $("#titulo_asiento").html("Nuevo Asiento Contable");
      nuevoCard.style.display = "block";
      modificarCard.style.display = "none";
      cardvisorpdf.style.display = "none";
      cardbusquedaasientos.style.display = "none";
      $("#card_informes").css("display", "none");
      $("#card_resultado_asientos").hide();
      $("#card_informes_mayor").hide();
    });

  document
    .getElementById("btn_consulta_asiento")
    .addEventListener("click", function () {
      modificarCard.style.display = "none";
      nuevoCard.style.display = "none";
      cardbusquedaasientos.style.display = "block";
      cardvisorpdf.style.display = "none";
      cardsumasysaldos.style.display = "none";

      $("#card_informes").css("display", "none");
      $("#card_resultado_asientos").hide();
      $("#card_informes_mayor").hide();
      cargarCuentasParaBusqueda();
    });

  // MENU INFORMES

  // LIBRO DIARIO
  document
    .getElementById("btn_libro_diario")
    .addEventListener("click", function () {
      modificarCard.style.display = "none";
      nuevoCard.style.display = "none";
      cardsumasysaldos.style.display = "none";
      cardvisorpdf.style.display = "none";
      $("#titulo_informes").html("Libro Diario");
      $("#card_informes").css("display", "block");
      cardbusquedaasientos.style.display = "none";
      $("#card_resultado_asientos").hide();
      $("#card_informes_mayor").hide();
      tablaInformes();
    });

  // SUMAS Y SALDO
  document
    .getElementById("btn_sumas_y_saldos")
    .addEventListener("click", function () {
      modificarCard.style.display = "none";
      nuevoCard.style.display = "none";
      cardsumasysaldos.style.display = "none";
      cardvisorpdf.style.display = "none";
      cardbusquedaasientos.style.display = "none";
      $("#card_resultado_asientos").hide();
      $("#card_informes_mayor").hide();
      $("#titulo_informes").html("Sumas y Saldos");
      $("#card_informes").css("display", "block");

      sumasySaldos();
    });
  // LIBROS CONTABLES
  document
    .getElementById("btn_libros_contables")
    .addEventListener("click", function () {
      modificarCard.style.display = "none";
      nuevoCard.style.display = "none";
      cardsumasysaldos.style.display = "none";
      cardvisorpdf.style.display = "none";
      cardbusquedaasientos.style.display = "none";
      $("#card_informes").css("display", "none");
      $("#card_resultado_asientos").hide();
      $("#card_informes_mayor").hide();

      mayoresContables();
    });

  $(document).on("input", "input[name='debe[]']", function () {
    let haberInput = $(this).closest(".row").find("input[name='haber[]']");
    if ($(this).val() !== "") {
      haberInput.val("0");
    }
    calcularTotalAsiento();
  });

  $(document).on("input", "input[name='haber[]']", function () {
    let debeInput = $(this).closest(".row").find("input[name='debe[]']");
    if ($(this).val() !== "") {
      debeInput.val("0");
    }
    calcularTotalAsiento();
  });

  function formatearFecha(fechaISO) {
    //FUNCIONES
    if (!fechaISO) return "";
    const [a, m, d] = fechaISO.split("-");
    return `${d}/${m}/${a}`;
  }

  function formatearMonto(valor) {
    const num = parseFloat(valor) || 0;
    return num.toLocaleString("es-AR", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  }

  function bloquearCajaSiFechaEsHoy() {
    const hoy = new Date();
    const fechaInput = new Date(document.getElementById("example-date").value);

    const mismoDia = hoy.getDate() === fechaInput.getDate();
    const mismoMes = hoy.getMonth() === fechaInput.getMonth();
    const mismoAnio = hoy.getFullYear() === fechaInput.getFullYear();

    const esHoy = mismoDia && mismoMes && mismoAnio;

    document.querySelectorAll("select[name='cuenta[]']").forEach((select) => {
      select.querySelectorAll("option").forEach((option) => {
        if (option.text.includes("CAJA")) {
          option.disabled = esHoy;
        }
      });
    });
  }

  document
    .getElementById("example-date")
    .addEventListener("change", bloquearCajaSiFechaEsHoy);

  function calcularTotalAsiento() {
    if (desactivarValidacion) return; // 🚫 No validar durante reinicio

    let totalDebe = 0;
    let totalHaber = 0;
    // "Fila valida" = tiene cuenta seleccionada Y un importe distinto de
    // cero en Debe o Haber. Antes alcanzaba con que los inputs no
    // estuvieran vacíos ("0.00" cuenta como no-vacío), por eso la fila que
    // agrega btn_nuevo_asiento con Debe=Haber=0.00 y sin cuenta ya
    // "habilitaba" el botón de confirmar sin ningún dato real cargado.
    let hayFilaValida = false;

    $("#campos-container .row").each(function () {
      const cuenta = $(this).find("select[name='cuenta[]']").val() || "";
      const debe =
        parseFloat(
          ($(this).find("input[name='debe[]']").val() || "").replace(
            ",",
            "."
          )
        ) || 0;
      const haber =
        parseFloat(
          ($(this).find("input[name='haber[]']").val() || "").replace(
            ",",
            "."
          )
        ) || 0;
      totalDebe += debe;
      totalHaber += haber;
      if (cuenta !== "" && (debe !== 0 || haber !== 0)) {
        hayFilaValida = true;
      }
    });

    // Totales de Debe y Haber por separado (no un solo campo con el signo
    // +/-, que confunde) + un indicador tipo "semáforo" de balanceado/no
    // balanceado - mismo criterio que usan los sistemas contables serios
    // (ej. SAP FB50): se ve el total de cada lado y una luz verde/roja que
    // avisa si coinciden, en vez de un campo que se pinta de rojo aunque
    // el asiento esté perfecto.
    $("#total_debe_asiento").val(formatearMonto(totalDebe));
    $("#total_haber_asiento").val(formatearMonto(totalHaber));

    const diferencia = totalDebe - totalHaber;
    // Epsilon en vez de "!== 0": comparar floats con igualdad exacta puede
    // dar falsos "no balanceado" por errores de redondeo (0.1 + 0.2 no da
    // 0.3 exacto en punto flotante).
    const estaBalanceado = Math.abs(diferencia) < 0.005;

    if (!hayFilaValida) {
      $("#estado_balance_asiento").html(
        '<small class="text-muted"><i class="mdi mdi-information-outline"></i> Cargá al menos una cuenta con un importe en Debe o Haber.</small>'
      );
      $("#btnAceptar").prop("disabled", true);
    } else if (!estaBalanceado) {
      $("#estado_balance_asiento").html(
        '<span class="badge bg-danger p-2"><i class="mdi mdi-close-circle"></i> No balanceado — diferencia ' +
          formatearMonto(Math.abs(diferencia)) +
          "</span>"
      );
      $("#btnAceptar").prop("disabled", true);
    } else {
      $("#estado_balance_asiento").html(
        '<span class="badge bg-success p-2"><i class="mdi mdi-check-circle"></i> Asiento balanceado</span>'
      );
      $("#btnAceptar").prop("disabled", false);
    }
    $("#mensaje").hide();
  }

  function obtener_asiento() {
    $.ajax({
      url: "../Admin/Procesos/php/contabilidad.php",
      method: "POST",
      data: { accion: "obtener_asiento" },
      dataType: "json",
      success: function (response) {
        let jsonData = response;

        $("#n_asiento").val(jsonData.NumeroAsiento);
        $("#nasiento").val(jsonData.NumeroAsiento);
        console.log("asiento", jsonData.NumeroAsiento);
      },
      error: function (xhr, status, error) {
        console.error("Error al obtener las cuentas:", status, error);
      },
    });
  }

  function llenarSelect(select, cuentas) {
    select.innerHTML = `<option value="">Seleccione una cuenta</option>`;
    cuentas.forEach((cuenta) => {
      // Aseguramos que el value coincida con lo que devuelve el backend
      let value = cuenta.Cuenta.padStart(9, "0"); // Ej: convierte "211400" → "000211400"
      let option = `<option value="${value}">${cuenta.NombreCuenta} (${cuenta.Cuenta})</option>`;
      select.innerHTML += option;
    });
  }
  let cuentasDisponibles = [];

  function obtenerCuentas(callback) {
    $.ajax({
      url: "../Admin/Procesos/php/contabilidad.php",
      method: "POST",
      data: { accion: "obtener_cuentas" },
      dataType: "json",
      success: function (response) {
        cuentasDisponibles = response;

        // Llenar y asignar eventos a cada select
        document
          .querySelectorAll("select[name='cuenta[]']")
          .forEach((select) => {
            llenarSelect(select, cuentasDisponibles);

            // Asignar evento change para actualizar nombreCuenta[]
            $(select)
              .off("change")
              .on("change", function () {
                const cuentaSeleccionada = $(this)
                  .find("option:selected")
                  .text(); // "Caja (1.1.1.01)"
                const nombreCuentaSolo = cuentaSeleccionada.split(" (")[0]; // "Caja"

                // Buscar input nombreCuenta[] dentro de la misma fila
                $(this)
                  .closest(".row")
                  .find("input[name='nombreCuenta[]']")
                  .val(nombreCuentaSolo);
              });
          });

        // ✅ Acá llamás a la función para bloquear "Caja" si es necesario
        bloquearCajaSiFechaEsHoy();

        if (callback) callback();
      },
      error: function (xhr, status, error) {
        console.error("Error al obtener las cuentas:", status, error);
      },
    });
  }
  window.agregarCampo = function () {
    let container = document.getElementById("campos-container");
    let div = document.createElement("div");
    div.classList.add("row", "mb-2");

    div.innerHTML = `
            <input type="hidden" name="id[]" value="">
            <input type="text" class="form-control" name="nombreCuenta[]" hidden>
            <div class="col-7">
                <select class="form-control" name="cuenta[]">
                    <option value="">Seleccione una cuenta</option>
                </select>
            </div>
            <div class="col"><input type="number" class="form-control" name="debe[]" placeholder="Debe" step="0.01"></div>
            <div class="col"><input type="number" class="form-control" name="haber[]" placeholder="Haber" step="0.01"></div>
            <div class="col">
                <i onclick="eliminarCampo(this)" class="mdi mdi-24px mdi-trash-can text-danger"></i>
                <i onclick="agregarCampo()" class="mdi mdi-24px mdi-plus text-primary"></i>
            </div>
        `;

    container.appendChild(div);

    // Llenar solo el nuevo select
    const nuevoSelect = div.querySelector("select[name='cuenta[]']");
    llenarSelect(nuevoSelect, cuentasDisponibles);

    // Asignar evento para actualizar el nombreCuenta[] automáticamente
    $(nuevoSelect)
      .off("change")
      .on("change", function () {
        const cuentaSeleccionada = $(this).find("option:selected").text();
        const nombreCuentaSolo = cuentaSeleccionada.split(" (")[0];
        $(this)
          .closest(".row")
          .find("input[name='nombreCuenta[]']")
          .val(nombreCuentaSolo);
      });
    calcularTotalAsiento();
  };

  // Numero del ultimo asiento realmente guardado en la base (para
  // Imprimir Asiento) - no se puede leer #nasiento en ese momento porque
  // confirmarAsiento() ya lo pisa con el proximo numero propuesto al
  // limpiar el formulario.
  let ultimoAsientoGuardado = null;

  // Imprime el asiento ya guardado (PDF con el diseño del sistema nuevo,
  // mismo contenido que el AsientoContablepdf.php viejo de Caddy_produccion:
  // cuenta, nombre de cuenta, debe, haber, totales y observaciones).
  window.imprimirAsiento = function () {
    if (!ultimoAsientoGuardado) return;
    window.open(
      "../Admin/Informes/AsientoContablepdf.php?NR=" +
        encodeURIComponent(ultimoAsientoGuardado),
      "_blank"
    );
  };

  // Imprime un asiento puntual desde la tabla de Consulta - no depende de
  // ultimoAsientoGuardado (eso es solo para el flujo de "recién confirmado").
  window.imprimirAsientoNumero = function (numero) {
    if (!numero) return;
    window.open(
      "../Admin/Informes/AsientoContablepdf.php?NR=" +
        encodeURIComponent(numero),
      "_blank"
    );
  };

  // Abre un asiento desde la tabla de Consulta en un modal de SOLO LECTURA
  // (no reusa el formulario de Crear/Modificar a propósito: acá el
  // objetivo es ver el asiento sin tapar la tabla de resultados de la
  // búsqueda de atrás, no editarlo).
  window.abrirAsientoDesdeConsulta = function (numero) {
    if (!numero) return;

    $.ajax({
      url: "../Admin/Procesos/php/contabilidad.php",
      method: "POST",
      data: { accion: "buscar_asiento", numeroAsiento: numero },
      dataType: "json",
      success: function (asiento) {
        if (!asiento || asiento.length === 0) {
          toast("warning", "No encontrado", "No se encontró el asiento " + numero + ".");
          return;
        }

        $("#titulo_ver_asiento").html("Asiento Contable N° " + numero);
        $("#fecha_ver_asiento").html(
          "<strong>Fecha:</strong> " + formatearFecha(asiento[0].Fecha)
        );
        document.getElementById("modalVerAsiento").dataset.numero = numero;

        const tbody = $("#tbody_ver_asiento");
        tbody.empty();
        let totalDebe = 0;
        let totalHaber = 0;
        const observaciones = [];

        asiento.forEach((item) => {
          totalDebe += parseFloat(item.Debe) || 0;
          totalHaber += parseFloat(item.Haber) || 0;
          const obs = (item.Observaciones || "").trim();
          if (obs && observaciones.indexOf(obs) === -1) {
            observaciones.push(obs);
          }
          tbody.append(`
            <tr>
              <td>${escapeHtml(item.Cuenta)}</td>
              <td>${escapeHtml(item.NombreCuenta)}</td>
              <td class="text-end">${formatearMonto(item.Debe)}</td>
              <td class="text-end">${formatearMonto(item.Haber)}</td>
            </tr>
          `);
        });

        $("#total_debe_ver_asiento").html(formatearMonto(totalDebe));
        $("#total_haber_ver_asiento").html(formatearMonto(totalHaber));
        $("#observaciones_ver_asiento").html(
          observaciones.length
            ? "<strong>Observaciones:</strong> " + escapeHtml(observaciones.join(" | "))
            : ""
        );

        $("#modalVerAsiento").modal("show");
      },
      error: function () {
        toast("error", "Error", "No se pudo abrir el asiento.");
      },
    });
  };

  window.eliminarCampo = function (element) {
    element.parentElement.parentElement.remove();
    calcularTotalAsiento();
  };

  window.confirmarAsiento = function () {
    // Validacion previa: hace falta al menos una fila con cuenta
    // seleccionada y un importe real (Debe o Haber distinto de cero).
    // Antes se podia confirmar un asiento vacio: la fila que agrega
    // btn_nuevo_asiento ya trae Debe=Haber="0.00" para "pasar" el chequeo
    // de balance (Total=0), pero eso no es un asiento real - el backend
    // terminaba sin guardar ninguna fila (sin cuenta seleccionada) e
    // igual mostraba el cartel de éxito.
    let filasValidas = 0;
    document.querySelectorAll("#campos-container .row").forEach((fila) => {
      const cuenta =
        fila.querySelector("select[name='cuenta[]']")?.value || "";
      const debe =
        parseFloat(fila.querySelector("input[name='debe[]']")?.value || "0") ||
        0;
      const haber =
        parseFloat(
          fila.querySelector("input[name='haber[]']")?.value || "0"
        ) || 0;
      if (cuenta !== "" && (debe !== 0 || haber !== 0)) {
        filasValidas++;
      }
    });

    if (filasValidas === 0) {
      $("#mensaje")
        .show()
        .html(
          `<div class="alert alert-danger">Cargá al menos una cuenta con un importe en Debe o Haber antes de confirmar.</div>`
        );
      return;
    }

    // ✅ Crear inputs ocultos de nombreCuenta
    document.querySelectorAll("select[name='cuenta[]']").forEach((select) => {
      const nombre = select.options[select.selectedIndex].text.split(" (")[0];
      const hiddenInput = document.createElement("input");
      hiddenInput.type = "hidden";
      hiddenInput.name = "nombreCuenta[]";
      hiddenInput.value = nombre;
      document.getElementById("asientoForm").appendChild(hiddenInput);
    });

    let formData = new FormData(document.getElementById("asientoForm"));
    formData.append("accion", "guardar_asiento");

    // 🛑 Desactivamos eventos de cálculo momentáneamente
    $(document).off("input", "input[name='debe[]']");
    $(document).off("input", "input[name='haber[]']");

    fetch("../Admin/Procesos/php/contabilidad.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (!data.success) {
          $("#mensaje")
            .show()
            .html(`<div class="alert alert-danger">${data.mensaje}</div>`);
          return;
        }

        // ✅ Mostramos el cartel de éxito
        $("#mensaje")
          .show()
          .html(`<div class="alert alert-success">${data.mensaje}</div>`);

        // Guardamos el numero recien confirmado para poder imprimirlo -
        // el reset de mas abajo pisa #nasiento con el proximo numero
        // propuesto, asi que hay que leerlo antes de que eso pase.
        ultimoAsientoGuardado = $("#nasiento").val();
        $("#btnImprimirAsiento").show();

        desactivarValidacion = true; // 🛑 Pausar validación temporalmente

        setTimeout(() => {
          $("#campos-container").empty();
          agregarCampo();

          $("#observaciones").val("");
          $("#total_debe_asiento").val("");
          $("#total_haber_asiento").val("");
          $("#estado_balance_asiento").html("");
          $("#btnAceptar").prop("disabled", true);
          $("#titulo_asiento").html("Nuevo Asiento Contable");

          obtener_asiento();
          activarEventosInput();

          desactivarValidacion = false; // ✅ Reanudar validación
        }, 200);

        setTimeout(() => {
          $("#mensaje").fadeOut();
        }, 3000);
      })
      .catch((error) => {
        $("#mensaje")
          .show()
          .html(
            `<div class="alert alert-danger">Error al guardar el asiento</div>`
          );
      });
  };

  // ✅ Activar nuevamente los eventos de input para recalcular
  function activarEventosInput() {
    $(document).on("input", "input[name='debe[]']", function () {
      let haberInput = $(this).closest(".row").find("input[name='haber[]']");
      if ($(this).val() !== "") haberInput.val("0");
      calcularTotalAsiento();
    });

    $(document).on("input", "input[name='haber[]']", function () {
      let debeInput = $(this).closest(".row").find("input[name='debe[]']");
      if ($(this).val() !== "") debeInput.val("0");
      calcularTotalAsiento();
    });
  }

  //BUSCAR ASIENTO
  window.buscarAsiento = function buscarAsiento() {
    const numero = document.getElementById("buscar_asiento").value;

    if (!numero) return;

    // Se resetea acá y se vuelve a mostrar solo si la búsqueda encuentra el
    // asiento - si no, no debe quedar apuntando al último asiento cargado.
    $("#btnImprimirAsiento").hide();
    ultimoAsientoGuardado = null;

    $.ajax({
      url: "../Admin/Procesos/php/contabilidad.php",
      method: "POST",
      data: { accion: "buscar_asiento", numeroAsiento: numero },
      dataType: "json",
      success: function (asiento) {
        if (!asiento || asiento.length === 0) {
          toast("warning", "No encontrado", "No se encontró el asiento " + numero + ".");
          return;
        }

        modificarCard.style.display = "none";
        nuevoCard.style.display = "block";

        // Limpiar los campos existentes
        $("#campos-container").empty();

        asiento.forEach((item) => {
          agregarCampoDesdeDatos(item);
        });

        $("#n_asiento").val(numero);
        $("#nasiento").val(numero);
        $("#titulo_asiento").html("Asiento Contable N° " + numero);

        // Si en el array viene la fecha, por ejemplo asiento[0].Fecha - esto
        // antes estaba fuera de este callback (usando una variable "asiento"
        // que todavia no existia ahi, un ReferenceError silencioso) y nunca
        // llegaba a setear la fecha.
        const fechaOriginal = asiento[0].Fecha; // por ejemplo "2025-03-26"
        if (fechaOriginal) {
          $("#example-date").val(fechaOriginal); // debe estar en formato yyyy-mm-dd
        }

        // Este asiento ya existe en la base (se acaba de traer de
        // Tesoreria), asi que se puede imprimir.
        ultimoAsientoGuardado = numero;
        $("#btnImprimirAsiento").show();

        // agregarCampoDesdeDatos() asigna el valor de cada <select> dentro
        // de un setTimeout(10ms) (para esperar a que se le carguen las
        // opciones) - sin este delay, calcularTotalAsiento() se ejecutaba
        // antes de que los selects tuvieran cuenta seleccionada y el
        // asiento quedaba marcado como "sin cuenta cargada" (borde rojo)
        // aunque estuviera perfectamente balanceado.
        setTimeout(() => {
          calcularTotalAsiento();
        }, 100);
      },
      error: function (xhr, status, error) {
        console.error("Error al buscar el asiento:", status, error);
        toast("error", "Error", "No se pudo buscar el asiento.");
      },
    });
  };

  function agregarCampoDesdeDatos(data) {
    let container = document.getElementById("campos-container");
    let div = document.createElement("div");
    div.classList.add("row", "mb-2");

    div.innerHTML = `   
        <input type="hidden" name="id[]" value="${data.id}">     
        <input type="text" class="form-control" name="nombreCuenta[]" hidden>
        <div class="col-7">
            <select class="form-control" name="cuenta[]">
                <option value="">Seleccione una cuenta</option>
            </select>
        </div>
        <div class="col">
            <input type="number" class="form-control" name="debe[]" value="${data.Debe}" step="0.01">
        </div>
        <div class="col">
            <input type="number" class="form-control" name="haber[]" value="${data.Haber}" step="0.01">
        </div>
        <div class="col">
            <i onclick="eliminarCampo(this)" class="mdi mdi-24px mdi-trash-can text-danger"></i>
            <i onclick="agregarCampo()" class="mdi mdi-24px mdi-plus text-primary"></i>

        </div>
    `;

    container.appendChild(div);

    const nuevoSelect = div.querySelector("select[name='cuenta[]']");

    // Llenar el select y luego asignar el valor
    llenarSelect(nuevoSelect, cuentasDisponibles);
    setTimeout(() => {
      nuevoSelect.value = data.Cuenta;

      // actualizar nombreCuenta[] oculto
      const nombreCuentaSolo = data.NombreCuenta;
      $(nuevoSelect)
        .closest(".row")
        .find("input[name='nombreCuenta[]']")
        .val(nombreCuentaSolo);
    }, 10); // pequeño delay para asegurar que el select esté listo

    // Evento para cambios posteriores
    $(nuevoSelect).on("change", function () {
      const cuentaSeleccionada = $(this).find("option:selected").text();
      const nombreCuentaSolo = cuentaSeleccionada.split(" (")[0];
      $(this)
        .closest(".row")
        .find("input[name='nombreCuenta[]']")
        .val(nombreCuentaSolo);
    });

    calcularTotalAsiento();
  }

  document.getElementById("btnBuscar").addEventListener("click", buscarAsiento);

  // $(document).ready(function() {
  function tablaInformes() {
    $("#card_informes_sumas_y_saldos").css("display", "none");
    $("#card_informes").css("display", "block");

    document.getElementById("date-informes").value = fechaFormateada;
    document.getElementById("date-informes-hasta-diario").value =
      fechaFormateada;
    // Antes esto era un solo día exacto (aunque la etiqueta decía "Desde") -
    // ahora es un rango real, igual que Sumas y Saldos y Consulta de Asientos.
    $("#date-informes, #date-informes-hasta-diario")
      .off("change")
      .on("change", function () {
        $("#tabla-informes").DataTable().ajax.reload();
      });

    $("#tabla-informes").DataTable({
      destroy: true,
      lengthChange: false,
      dom: "Bfrtip", // 👈 muestra los botones arriba de la tabla
      paging: false,
      buttons: [
        {
          text: '<i class="mdi mdi-file-pdf-box"></i> Ver / Imprimir',
          className: "btn btn-danger btn-sm me-2",
          action: function () {
            const desde = $("#date-informes").val();
            const hasta = $("#date-informes-hasta-diario").val();
            window.open(
              "../Admin/Informes/LibroDiariopdf.php?Desde=" +
                encodeURIComponent(desde) +
                "&Hasta=" +
                encodeURIComponent(hasta),
              "_blank"
            );
          },
        },
        {
          extend: "excelHtml5",
          text: '<i class="mdi mdi-file-excel"></i> Exportar a Excel',
          className: "btn btn-success btn-sm",
        },
      ],
      ajax: {
        url: "../Admin/Procesos/php/contabilidad.php",
        method: "POST",
        data: function (d) {
          d.accion = "obtener_datos_libro_diario";
          d.fecha_desde = $("#date-informes").val();
          d.fecha_hasta = $("#date-informes-hasta-diario").val();
        },
        dataSrc: "data",
      },
      columns: [
        { data: "NumeroAsiento" },
        {
          data: "Fecha",
          render: function (data) {
            const fecha = new Date(data);
            const dia = String(fecha.getDate()).padStart(2, "0");
            const mes = String(fecha.getMonth() + 1).padStart(2, "0");
            const anio = fecha.getFullYear();
            return `${dia}/${mes}/${anio}`;
          },
        },
        { data: "Cuenta" },
        { data: "NombreCuenta" },
        { data: "Debe" },
        { data: "Haber" },
      ],
      footerCallback: function (row, data, start, end, display) {
        let totalDebe = 0;
        let totalHaber = 0;

        data.forEach(function (row) {
          totalDebe += parseFloat(row.Debe) || 0;
          totalHaber += parseFloat(row.Haber) || 0;
        });

        $("#total-debe").html(totalDebe.toFixed(2));
        $("#total-haber").html(totalHaber.toFixed(2));
      },
      createdRow: function (row, data, dataIndex) {
        const api = this.api();
        const asiento = data.NumeroAsiento;

        // Agrupar todas las filas del mismo asiento
        const grupo = api
          .rows()
          .data()
          .toArray()
          .filter((r) => r.NumeroAsiento === asiento);

        let totalDebe = 0;
        let totalHaber = 0;

        grupo.forEach((item) => {
          totalDebe += parseFloat(item.Debe) || 0;
          totalHaber += parseFloat(item.Haber) || 0;
        });

        if (totalDebe.toFixed(2) !== totalHaber.toFixed(2)) {
          // Si no está balanceado, agregamos la clase a la fila
          $(row).addClass("table-primary");
        }
      },
    });
  }

  function sumasySaldos() {
    $("#card_informes_sumas_y_saldos").css("display", "block");
    $("#card_informes").css("display", "none");
    $("#card_informes_mayor").css("display", "none");
    document.getElementById("visor_pdf").style.display = "none";

    document.getElementById("date-informes-desde").value = fechaFormateada;
    document.getElementById("date-informes-hasta").value = fechaFormateada;

    // .off()+.on() en vez de addEventListener: sumasySaldos() se vuelve a
    // llamar cada vez que se hace click en "Sumas y Saldos" del menú - sin
    // esto se apilaba un listener nuevo por cada visita a la pantalla.
    $("#date-informes-desde, #date-informes-hasta, #chk_no_operativo")
      .off("change")
      .on("change", function () {
        $("#tabla-sumas-y-saldos").DataTable().ajax.reload();
      });

    if ($.fn.DataTable.isDataTable("#tabla-sumas-y-saldos")) {
      $("#tabla-sumas-y-saldos").DataTable().destroy();
      $("#tabla-sumas-y-saldos tbody").empty();
    }

    $("#tabla-sumas-y-saldos").DataTable({
      lengthChange: false,
      dom: "Bfrtip",
      paging: false,
      buttons: [
        {
          text: '<i class="mdi mdi-file-pdf-box"></i> Ver / Imprimir',
          className: "btn btn-danger btn-sm me-2",
          action: function () {
            var desde = document.getElementById("date-informes-desde").value;
            var hasta = document.getElementById("date-informes-hasta").value;
            var noOperativo = document.getElementById("chk_no_operativo")
              .checked
              ? 1
              : 0;
            $("#titulo_informes").html("Sumas y Saldos");
            document.getElementById("visor_pdf").style.display = "block";
            // Antes esto apuntaba hardcodeado a www.caddy.com.ar
            // (producción) incluso probando en localhost - con ruta
            // relativa apunta siempre al mismo servidor donde corre la
            // pantalla, sea cual sea.
            document.getElementById("iframe_pdf").src =
              "../Admin/Informes/SumasySaldospdf.php?Desde=" +
              encodeURIComponent(desde) +
              "&Hasta=" +
              encodeURIComponent(hasta) +
              "&NoOperativo=" +
              noOperativo;
          },
        },
        {
          extend: "excelHtml5",
          text: '<i class="mdi mdi-file-excel"></i> Exportar a Excel',
          className: "btn btn-success btn-sm",
        },
      ],
      ajax: {
        url: "../Admin/Procesos/php/contabilidad.php",
        method: "POST",
        data: function (d) {
          d.accion = "obtener_datos_sumas_y_saldos";
          d.fecha_desde = $("#date-informes-desde").val();
          d.fecha_hasta = $("#date-informes-hasta").val();
          d.no_operativo = $("#chk_no_operativo").is(":checked") ? 1 : 0;
        },
        dataSrc: "data",
      },
      columns: [
        { data: "TipoCuenta" },
        { data: "Cuenta" },
        { data: "NombreCuenta" },
        { data: "SumaDebe" },
        { data: "SumaHaber" },
        {
          data: "SaldoDeudor",
          render: function (data) {
            return parseFloat(data) > 0 ? data : "";
          },
        },
        {
          data: "SaldoAcreedor",
          render: function (data) {
            return parseFloat(data) > 0 ? data : "";
          },
        },
      ],
      footerCallback: function (row, data) {
        let totalDebe = 0;
        let totalHaber = 0;
        let totalSaldoDeudor = 0;
        let totalSaldoAcreedor = 0;
        data.forEach(function (row) {
          totalDebe += parseFloat(row.SumaDebe) || 0;
          totalHaber += parseFloat(row.SumaHaber) || 0;
          totalSaldoDeudor += parseFloat(row.SaldoDeudor) || 0;
          totalSaldoAcreedor += parseFloat(row.SaldoAcreedor) || 0;
        });
        $("#total-debe-syc").html(totalDebe.toFixed(2));
        $("#total-haber-syc").html(totalHaber.toFixed(2));
        $("#total-saldo-deudor-syc").html(totalSaldoDeudor.toFixed(2));
        $("#total-saldo-acreedor-syc").html(totalSaldoAcreedor.toFixed(2));
      },
    });
  }

  var dtMayor = null;

  function mayoresContables() {
    $("#titulo_mayor").html("Libro Mayor");
    $("#card_informes_mayor").css("display", "block");
    $("#card_informes").css("display", "none");
    $("#card_informes_sumas_y_saldos").css("display", "none");
    document.getElementById("visor_pdf").style.display = "none";

    document.getElementById("date-mayor-desde").value = fechaFormateada;
    document.getElementById("date-mayor-hasta").value = fechaFormateada;
    document.getElementById("chk_mayor_todas_cuentas").checked = false;

    // obtenerCuentas() es async y no soporta callback (el parametro existe
    // pero la función nunca lo invoca) - llenar el select ahí mismo justo
    // después dejaba la primera apertura de esta pantalla sin opciones,
    // porque el AJAX todavía no había terminado. Se carga acá directo.
    if (cuentasDisponibles.length) {
      llenarSelect(document.getElementById("cuenta_mayor"), cuentasDisponibles);
    } else {
      $.ajax({
        url: "../Admin/Procesos/php/contabilidad.php",
        method: "POST",
        data: { accion: "obtener_cuentas" },
        dataType: "json",
        success: function (response) {
          cuentasDisponibles = response;
          llenarSelect(document.getElementById("cuenta_mayor"), cuentasDisponibles);
        },
        error: function () {
          toast("error", "Error", "No se pudieron cargar las cuentas.");
        },
      });
    }

    // "Todas las cuentas" desactiva el select para que quede claro que ese
    // filtro no se está usando mientras el checkbox esté tildado.
    $("#chk_mayor_todas_cuentas")
      .off("change")
      .on("change", function () {
        $("#cuenta_mayor").prop("disabled", this.checked);
        $("#tabla-mayor").DataTable().ajax.reload();
      });

    $("#cuenta_mayor, #date-mayor-desde, #date-mayor-hasta")
      .off("change")
      .on("change", function () {
        $("#tabla-mayor").DataTable().ajax.reload();
      });

    if ($.fn.DataTable.isDataTable("#tabla-mayor")) {
      $("#tabla-mayor").DataTable().destroy();
      $("#tabla-mayor tbody").empty();
    }

    dtMayor = $("#tabla-mayor").DataTable({
      lengthChange: false,
      dom: "Bfrtip",
      paging: false,
      buttons: [
        {
          text: '<i class="mdi mdi-file-pdf-box"></i> Ver / Imprimir',
          className: "btn btn-danger btn-sm me-2",
          action: function () {
            var todas = $("#chk_mayor_todas_cuentas").is(":checked");
            var cuenta = document.getElementById("cuenta_mayor").value;
            var desde = document.getElementById("date-mayor-desde").value;
            var hasta = document.getElementById("date-mayor-hasta").value;
            if (!todas && !cuenta) {
              toast(
                "warning",
                "Falta la cuenta",
                'Elegí una cuenta para ver su Mayor, o tildá "Todas las cuentas".'
              );
              return;
            }
            $("#titulo_informes").html(
              todas ? "Libro Mayor - Todas las cuentas" : "Libro Mayor"
            );
            document.getElementById("visor_pdf").style.display = "block";
            document.getElementById("iframe_pdf").src =
              "../Admin/Informes/LibroMayorpdf.php?" +
              (todas ? "" : "Cuenta=" + encodeURIComponent(cuenta) + "&") +
              "Desde=" +
              encodeURIComponent(desde) +
              "&Hasta=" +
              encodeURIComponent(hasta);
          },
        },
        {
          extend: "excelHtml5",
          text: '<i class="mdi mdi-file-excel"></i> Exportar a Excel',
          className: "btn btn-success btn-sm",
        },
      ],
      ajax: {
        url: "../Admin/Procesos/php/contabilidad.php",
        method: "POST",
        data: function (d) {
          d.accion = "obtener_datos_libro_mayor";
          d.fecha_desde = $("#date-mayor-desde").val();
          d.fecha_hasta = $("#date-mayor-hasta").val();
          d.cuenta = $("#chk_mayor_todas_cuentas").is(":checked") ? "" : $("#cuenta_mayor").val();
        },
        dataSrc: "data",
      },
      columns: [
        { data: "Cuenta" },
        { data: "NombreCuenta" },
        {
          data: "Fecha",
          render: function (data) {
            const fecha = new Date(data);
            const dia = String(fecha.getDate()).padStart(2, "0");
            const mes = String(fecha.getMonth() + 1).padStart(2, "0");
            const anio = fecha.getFullYear();
            return `${dia}/${mes}/${anio}`;
          },
        },
        { data: "NumeroAsiento" },
        { data: "Observaciones" },
        {
          data: "Debe",
          render: function (data) {
            return data === null ? "" : data;
          },
        },
        {
          data: "Haber",
          render: function (data) {
            return data === null ? "" : data;
          },
        },
        { data: "SaldoCorrido" },
      ],
      footerCallback: function (row, data) {
        let totalDebe = 0;
        let totalHaber = 0;
        data.forEach(function (row) {
          totalDebe += parseFloat(row.Debe) || 0;
          totalHaber += parseFloat(row.Haber) || 0;
        });
        $("#total-debe-mayor").html(totalDebe.toFixed(2));
        $("#total-haber-mayor").html(totalHaber.toFixed(2));
      },
    });
  }

  $("#form_busqueda_asientos").on("submit", function (e) {
    e.preventDefault();

    const fechaDesde = $("#fecha_desde").val();
    const fechaHasta = $("#fecha_hasta").val();

    $("#titulo_resultado_asientos").html(
      "Asiento desde " +
        formatearFecha(fechaDesde) +
        " hasta " +
        formatearFecha(fechaHasta)
    );

    $.ajax({
      url: "../Admin/Procesos/php/contabilidad.php",
      method: "POST",
      data: $(this).serialize() + "&accion=consultar_asiento",
      dataType: "json",
      success: function (response) {
        // Hay que destruir la instancia anterior de DataTable antes de
        // tocar el tbody a mano - si no, DataTable sigue "pensando" que
        // tiene las filas viejas y la paginación queda rota.
        if ($.fn.DataTable.isDataTable("#tabla_resultado_asientos")) {
          $("#tabla_resultado_asientos").DataTable().destroy();
        }

        const tbody = $("#tabla_resultado_asientos tbody");
        tbody.empty();

        if (response.length > 0) {
          response.forEach((asiento) => {
            tbody.append(`
              <tr>
                <td>${formatearFecha(asiento.Fecha)}</td>
                <td>${asiento.NumeroAsiento}</td>
                <td class="text-end">${formatearMonto(asiento.Debe)}</td>
                <td class="text-end">${formatearMonto(asiento.Haber)}</td>
                <td>${escapeHtml(asiento.Observaciones)}</td>
                <td class="text-center">
                  <i class="mdi mdi-eye-outline caddy-accion-icon me-2" title="Abrir asiento" onclick="abrirAsientoDesdeConsulta('${asiento.NumeroAsiento}')"></i>
                  <i class="mdi mdi-printer-outline caddy-accion-icon" title="Imprimir asiento" onclick="imprimirAsientoNumero('${asiento.NumeroAsiento}')"></i>
                </td>
              </tr>
            `);
          });

          // 50 asientos por hoja en vez de todos juntos en una sola tabla
          // larga - solo se inicializa con datos reales (6 celdas por
          // fila): la fila de "Sin resultados" de abajo tiene una sola
          // celda con colspan, y si DataTable la llega a encontrar tira
          // "Requested unknown parameter '1' for row 0, column 1" al
          // intentar leer celdas que no existen.
          $("#tabla_resultado_asientos").DataTable({
            destroy: true,
            pageLength: 50,
            lengthChange: false,
            searching: false,
            info: true,
            order: [],
            columnDefs: [{ orderable: false, targets: -1 }],
            language: {
              paginate: { previous: "Anterior", next: "Siguiente" },
              info: "Mostrando _START_ a _END_ de _TOTAL_ asientos",
              infoEmpty: "Sin asientos para mostrar",
              zeroRecords: "Sin resultados",
            },
          });
        } else {
          tbody.append(
            '<tr><td colspan="6" class="text-center">Sin resultados</td></tr>'
          );
        }
        // La tarjeta que envuelve #resultado_asientos tenia
        // style="display:none" fijo y nada la volvia a mostrar - el botón
        // Buscar en la práctica no hacía nada visible aunque la búsqueda
        // funcionara bien.
        $("#card_resultado_asientos").show();
        $("#resultado_asientos").show();
      },
      error: function () {
        $("#mensaje")
          .show()
          .html(
            `<div class="alert alert-danger">Error al consultar los asientos.</div>`
          );
      },
    });
  });

  function cargarCuentasParaBusqueda() {
    $.ajax({
      url: "../Admin/Procesos/php/contabilidad.php",
      method: "POST",
      data: { accion: "obtener_cuentas" },
      dataType: "json",
      success: function (cuentas) {
        llenarSelect(document.getElementById("cuenta_desde"), cuentas);
        llenarSelect(document.getElementById("cuenta_hasta"), cuentas);
      },
      error: function (xhr, status, error) {
        console.error("Error al obtener las cuentas:", status, error);
      },
    });
  }
});
