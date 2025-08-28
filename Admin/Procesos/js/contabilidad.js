document.addEventListener("DOMContentLoaded", function () {
    let desactivarValidacion = false;

    obtenerCuentas();
    obtener_asiento();
    document.getElementById("n_asiento").readOnly = true;    // para solo lectura

    const hoy = new Date();
    const dia = String(hoy.getDate()).padStart(2, '0');
    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
    const anio = hoy.getFullYear();
    
    // ✅ Formato compatible con <input type="date">
    const fechaFormateada = `${anio}-${mes}-${dia}`;
    document.getElementById("example-date").value = fechaFormateada;
    const modificarCard = document.getElementById("card_buscar_asiento");
    const nuevoCard = document.getElementById("card_nuevo_asiento");
    const cardsumasysaldos = document.getElementById("card_informes_sumas_y_saldos");
    const cardvisorpdf = document.getElementById("visor_pdf");
    const cardbusquedaasientos = document.getElementById("card_busqueda_asientos");
    //Botones Menu
    
    document.getElementById("btn_modificar_asiento").addEventListener("click", function () {
    
        modificarCard.style.display = "block";
        nuevoCard.style.display = "none";
        cardvisorpdf.style.display = "none";        
        cardbusquedaasientos.style.display="none";
        $('#card_informes').css('display', 'none');
        cargarCuentasParaBusqueda();

    });

    document.getElementById("btn_nuevo_asiento").addEventListener("click", function () {
        $("#campos-container").empty();
        obtenerCuentas();
        obtener_asiento();
        agregarCampo();
        // calcularTotalAsiento();
        // ✅ Opcional: poner un valor inicial para evitar la alerta
        setTimeout(() => {
            const debeInput = document.querySelector("input[name='debe[]']");
            if (debeInput) debeInput.value = "0.00";
            const haberInput = document.querySelector("input[name='haber[]']");
            if (haberInput) haberInput.value = "0.00";
            calcularTotalAsiento();
        }, 50);
        nuevoCard.style.display = "block";
        modificarCard.style.display = "none";
        cardvisorpdf.style.display = "none";
        cardvisorpdf.style.display = "none";
        cardbusquedaasientos.style.display="none";


        $('#titulo_asiento').html('Nuevo Asiento Contable');
    
    });
    
    document.getElementById("btn_consulta_asiento").addEventListener("click", function () {
        
        modificarCard.style.display = "none";
        nuevoCard.style.display = "none";
        cardbusquedaasientos.style.display = "block";
        cardvisorpdf.style.display = "none";
        cardsumasysaldos.style.display = "none";
        
        $('#card_informes').css('display', 'none');
        cargarCuentasParaBusqueda();

    });
    
    // MENU INFORMES

    // LIBRO DIARIO
    document.getElementById("btn_libro_diario").addEventListener("click", function () {
    
        modificarCard.style.display = "none";
        nuevoCard.style.display = "none";
        cardsumasysaldos.style.display = "none";
        cardvisorpdf.style.display = "none";
        $('#titulo_informes').html('Libro Diario');
        $('#card_informes').css('display', 'block');
        
        tablaInformes();

    });

    // SUMAS Y SALDO
    document.getElementById("btn_sumas_y_saldos").addEventListener("click", function () {
    
        modificarCard.style.display = "none";
        nuevoCard.style.display = "none";
        cardsumasysaldos.style.display = "none";
        cardvisorpdf.style.display = "none";
        $('#titulo_informes').html('Sumas y Saldos');
        $('#card_informes').css('display', 'block');
        
        sumasySaldos();

    });
    // LIBROS CONTABLES
    document.getElementById("btn_libros_contables").addEventListener("click", function () {
    
        modificarCard.style.display = "none";
        nuevoCard.style.display = "none";                
        cardsumasysaldos.style.display = "none";
        cardvisorpdf.style.display = "none";
        $('#card_informes').css('display', 'none');        
        
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
        if (!fechaISO) return '';
        const [a, m, d] = fechaISO.split("-");
        return `${d}/${m}/${a}`;
    }

    function bloquearCajaSiFechaEsHoy() {
        const hoy = new Date();
        const fechaInput = new Date(document.getElementById("example-date").value);
    
        const mismoDia = hoy.getDate() === fechaInput.getDate();
        const mismoMes = hoy.getMonth() === fechaInput.getMonth();
        const mismoAnio = hoy.getFullYear() === fechaInput.getFullYear();
    
        const esHoy = mismoDia && mismoMes && mismoAnio;
    
        document.querySelectorAll("select[name='cuenta[]']").forEach(select => {
            select.querySelectorAll("option").forEach(option => {
                if (option.text.includes("CAJA")) {
                    option.disabled = esHoy;
                }
            });
        });
    }

    document.getElementById("example-date").addEventListener("change", bloquearCajaSiFechaEsHoy);


function calcularTotalAsiento() {
    if (desactivarValidacion) return; // 🚫 No validar durante reinicio

    let totalDebe = 0;
    let totalHaber = 0;
    let hayValores = false;

    $("input[name='debe[]']").each(function () {
        let val = $(this).val();
        if (val !== "") hayValores = true;
        totalDebe += parseFloat(val.replace(",", ".")) || 0;
    });

    $("input[name='haber[]']").each(function () {
        let val = $(this).val();
        if (val !== "") hayValores = true;
        totalHaber += parseFloat(val.replace(",", ".")) || 0;
    });

    let total = totalDebe - totalHaber;
    $("#total_asiento").val(total.toFixed(2));

    if (total !== 0 || !hayValores) {
        $("#total_asiento").addClass("is-invalid").removeClass("is-valid");
        $("#btnAceptar").prop("disabled", true);
        $("#mensaje").show().html(`<div class="alert alert-danger"> El asiento no está balanceado (Debe ≠ Haber)</div>`);
    } else {
        $("#total_asiento").addClass("is-valid").removeClass("is-invalid");
        $("#btnAceptar").prop("disabled", false);
        $("#mensaje").hide();
    }
}

    function obtener_asiento(){
        $.ajax({
            url: "../Admin/Procesos/php/contabilidad.php",
            method: "POST",
            data: { accion: "obtener_asiento" },
            dataType: "json",
            success: function (response) {
                
                let jsonData=response;

                $('#n_asiento').val(jsonData.NumeroAsiento);
                $('#nasiento').val(jsonData.NumeroAsiento);
                console.log('asiento',jsonData.NumeroAsiento);

            },
            error: function (xhr, status, error) {
                console.error("Error al obtener las cuentas:", status, error);
    } });
    }

    function llenarSelect(select, cuentas) {
        select.innerHTML = `<option value="">Seleccione una cuenta</option>`;
        cuentas.forEach(cuenta => {
            // Aseguramos que el value coincida con lo que devuelve el backend
            let value = cuenta.Cuenta.padStart(9, '0'); // Ej: convierte "211400" → "000211400"
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
                document.querySelectorAll("select[name='cuenta[]']").forEach(select => {
                    llenarSelect(select, cuentasDisponibles);
    
                    // Asignar evento change para actualizar nombreCuenta[]
                    $(select).off("change").on("change", function () {
                        const cuentaSeleccionada = $(this).find("option:selected").text(); // "Caja (1.1.1.01)"
                        const nombreCuentaSolo = cuentaSeleccionada.split(" (")[0];         // "Caja"
    
                        // Buscar input nombreCuenta[] dentro de la misma fila
                        $(this).closest(".row").find("input[name='nombreCuenta[]']").val(nombreCuentaSolo);
                    });
                });
    
                // ✅ Acá llamás a la función para bloquear "Caja" si es necesario
            bloquearCajaSiFechaEsHoy();

                if (callback) callback();
            },
            error: function (xhr, status, error) {
                console.error("Error al obtener las cuentas:", status, error);
            }
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
        $(nuevoSelect).off("change").on("change", function () {
            const cuentaSeleccionada = $(this).find("option:selected").text();
            const nombreCuentaSolo = cuentaSeleccionada.split(" (")[0];
            $(this).closest(".row").find("input[name='nombreCuenta[]']").val(nombreCuentaSolo);
        });
        calcularTotalAsiento();
    };

    window.eliminarCampo = function (element) {
        element.parentElement.parentElement.remove();
        calcularTotalAsiento();
    };


    window.confirmarAsiento = function () {
        // ✅ Crear inputs ocultos de nombreCuenta
        document.querySelectorAll("select[name='cuenta[]']").forEach(select => {
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
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // ✅ Mostramos el cartel de éxito
            $("#mensaje").show().html(`<div class="alert alert-success">${data.mensaje}</div>`);
        
            desactivarValidacion = true; // 🛑 Pausar validación temporalmente
        
            setTimeout(() => {
                $("#campos-container").empty();
                agregarCampo();
        
                $('#observaciones').val('');
                $("#total_asiento").val('').removeClass("is-invalid is-valid");
                $("#btnAceptar").prop("disabled", true);
        
                obtener_asiento();
                activarEventosInput();
        
                desactivarValidacion = false; // ✅ Reanudar validación
        
            }, 200);
        
            setTimeout(() => { $("#mensaje").fadeOut(); }, 3000);
        })
        .catch(error => {
            $("#mensaje").show().html(`<div class="alert alert-danger">Error al guardar el asiento</div>`);
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

        modificarCard.style.display = "none";
        nuevoCard.style.display = "block";

    $.ajax({
        url: "../Admin/Procesos/php/contabilidad.php",
        method: "POST",
        data: { accion: "buscar_asiento", numeroAsiento: numero },
        dataType: "json",
        success: function (asiento) {
            if (!asiento || asiento.length === 0) {
                $("#mensaje").html(`<div class="alert alert-warning">No se encontró el asiento</div>`);
                return;
            }

            // Limpiar los campos existentes
            $("#campos-container").empty();

            asiento.forEach(item => {
                agregarCampoDesdeDatos(item);
            });

            $('#n_asiento').html(numero);
            $('#nasiento').val(numero);
        },
        error: function (xhr, status, error) {
            console.error("Error al buscar el asiento:", status, error);
            $("#mensaje").html(`<div class="alert alert-danger">Error al buscar el asiento</div>`);
        }
    });

    $('#n_asiento').val(numero); // Número de asiento
    $('#nasiento').val(numero);  // hidden

    // Si en el array viene la fecha, por ejemplo asiento[0].Fecha
    const fechaOriginal = asiento[0].Fecha; // por ejemplo "2025-03-26"
    if (fechaOriginal) {
        $('#example-date').val(fechaOriginal); // debe estar en formato yyyy-mm-dd
    }

}

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
        $(nuevoSelect).closest(".row").find("input[name='nombreCuenta[]']").val(nombreCuentaSolo);
    }, 10); // pequeño delay para asegurar que el select esté listo

    // Evento para cambios posteriores
    $(nuevoSelect).on("change", function () {
        const cuentaSeleccionada = $(this).find("option:selected").text();
        const nombreCuentaSolo = cuentaSeleccionada.split(" (")[0];
        $(this).closest(".row").find("input[name='nombreCuenta[]']").val(nombreCuentaSolo);
    });

    calcularTotalAsiento();
}

document.getElementById("btnBuscar").addEventListener("click", buscarAsiento);


// $(document).ready(function() {
    function tablaInformes() {
    
    
    $('#card_informes_sumas_y_saldos').css('display','none');
    $('#card_informes').css('display','block');
    
    document.getElementById("date-informes").value = fechaFormateada;
    $('#date-informes').on('change', function() {
        $('#tabla-informes').DataTable().ajax.reload();
    });

    $('#tabla-informes').DataTable({
        destroy: true,
        lengthChange: false,
        dom: 'Bfrtip',       // 👈 muestra los botones arriba de la tabla
        paging: false, 
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="mdi mdi-file-excel"></i> Exportar a Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="mdi mdi-printer"></i> Imprimir',
                titleAttr: 'Imprimir reporte',
                title: 'Libro Diario - Triangular S.A.'
                
            }
        ],
        ajax: {
            url: '../Admin/Procesos/php/contabilidad.php',
            method: 'POST',
            data: function (d) {
                d.accion = 'obtener_datos_libro_diario';
                d.fecha = $('#date-informes').val(); // asegurate de tener un input con ese id
            },
            dataSrc: 'data'
        },
        columns: [
            { data: 'NumeroAsiento' },
            { data: 'Fecha', render: function(data) {
                const fecha = new Date(data);
                const dia = String(fecha.getDate()).padStart(2, '0');
                const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                const anio = fecha.getFullYear();
                return `${dia}/${mes}/${anio}`;
            } },
            { data: 'Cuenta' },
            { data: 'NombreCuenta' },
            { data: 'Debe' },
            { data: 'Haber' }
        ],
        footerCallback: function(row, data, start, end, display) {
            let totalDebe = 0;
            let totalHaber = 0;

            data.forEach(function(row) {
                totalDebe += parseFloat(row.Debe) || 0;
                totalHaber += parseFloat(row.Haber) || 0;
            });

            $('#total-debe').html(totalDebe.toFixed(2));
            $('#total-haber').html(totalHaber.toFixed(2));
        },
        createdRow: function (row, data, dataIndex) {
            const api = this.api();
            const asiento = data.NumeroAsiento;
        
            // Agrupar todas las filas del mismo asiento
            const grupo = api.rows().data().toArray().filter(r => r.NumeroAsiento === asiento);
        
            let totalDebe = 0;
            let totalHaber = 0;
        
            grupo.forEach(item => {
                totalDebe += parseFloat(item.Debe) || 0;
                totalHaber += parseFloat(item.Haber) || 0;
            });
        
            if (totalDebe.toFixed(2) !== totalHaber.toFixed(2)) {
                // Si no está balanceado, agregamos la clase a la fila
                $(row).addClass('table-primary');
            }
        },
    });
}

function sumasySaldos(){
    
    $('#card_informes_sumas_y_saldos').css('display','block');
    $('#card_informes').css('display','none');

    document.getElementById("date-informes-desde").value = fechaFormateada;
    document.getElementById("date-informes-hasta").value = fechaFormateada;

    document.getElementById("btn_sumas_y_saldos_buscar").addEventListener("click", function(){
        var desde = document.getElementById("date-informes-desde").value;
        var hasta = document.getElementById("date-informes-hasta").value;
        $('#titulo_informes').html('Sumas y Saldos');
        document.getElementById("visor_pdf").style.display = "block";
        document.getElementById("iframe_pdf").src = "https://www.caddy.com.ar/SistemaTriangular/Admin/Informes/SumasySaldospdf.php?Desde="+desde+"&Hasta="+hasta;

        
    });

}

function mayoresContables(){

    $('#titulo_informes').html('Mayores Contables');
    document.getElementById("visor_pdf").style.display = "block";
    document.getElementById("iframe_pdf").src = "https://www.caddy.com.ar/SistemaTriangular/Admin/Informes/MayoresContablespdf.php";

}



$("#form_busqueda_asientos").on("submit", function(e) {
    e.preventDefault();
    
    const fechaDesde = $('#fecha_desde').val();
    const fechaHasta = $('#fecha_hasta').val();
    
    $('#titulo_resultado_asientos').html(
        "Asiento desde " + formatearFecha(fechaDesde) + " hasta " + formatearFecha(fechaHasta)
    );

    $.ajax({
      url: "../Admin/Procesos/php/contabilidad.php",
      method: "POST",
      data: $(this).serialize() + '&accion=consultar_asiento',
      dataType: "json",
      success: function(response) {
        const tbody = $("#tabla_resultado_asientos tbody");
        tbody.empty();
  
        if (response.length > 0) {
          response.forEach(asiento => {
            tbody.append(`
              <tr>
                <td>${asiento.Fecha}</td>
                <td>${asiento.NumeroAsiento}</td>
                <td>${asiento.Cuenta}</td>
                <td>${asiento.NombreCuenta}</td>
                <td>${asiento.Debe}</td>
                <td>${asiento.Haber}</td>
                <td>${asiento.Observaciones}</td>
              </tr>
            `);
          });
          $("#resultado_asientos").show();
        } else {
          tbody.append('<tr><td colspan="7" class="text-center">Sin resultados</td></tr>');
          $("#resultado_asientos").show();
        }
      }
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
    }
  });
}







});