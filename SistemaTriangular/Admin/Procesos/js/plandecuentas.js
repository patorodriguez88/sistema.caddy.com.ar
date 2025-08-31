$(document).ready(function () {
    
    $('#edit-nombre').on('input', function () {
        this.value = this.value.toUpperCase();
    });

    $('#edit-tipo').on('change', function () {
        const tipo = $(this).val();
        if (tipo === 'R+' || tipo === 'R-') {
            $('.presupuesto-campos').show();
        } else {
            $('.presupuesto-campos').hide();
            $('#edit-presupuestoMensual').val('');
            $('#edit-presupuestoAnual').val('');
        }
    });

    $('#plandecuentas').DataTable({
        destroy: true,
        lengthChange: false,
        dom: 'Bfrtip',       // 👈 muestra los botones arriba de la tabla
        paging: false, 
        ordering:false,
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="mdi mdi-file-excel"></i> Exportar a Excel',
                className: 'btn btn-success btn-sm mr-1'
            },
            {
                extend: 'print',
                text: '<i class="mdi mdi-printer"></i> Imprimir',
                titleAttr: 'Imprimir reporte',
                title: 'Libro Diario - Triangular S.A.',
                className: 'btn btn-info btn-sm'
                
            }
        ],
        ajax: {
            url: '../Admin/Procesos/php/plandecuentas.php',
            method: 'POST',
            data: function (d) {
                d.accion = 'obtener_datos';
                d.fecha = $('#date-informes').val(); // asegurate de tener un input con ese id
            },
            dataSrc: 'data'
        },
        columns: [
            { data: 'Nivel' },
            { data: 'Cuenta'},
            { 
                data: 'NombreCuenta',
            createdCell: function (td, cellData, rowData, row, col) {
                // Calcula el padding según el nivel (por ejemplo 20px por nivel)
                let padding = 40 * (parseInt(rowData.Nivel) - 1);
                $(td).css('padding-left', padding + 'px');
                if (rowData.Nivel == 1) {
                    $(td).css('font-weight', 'bold');
                }
            }
            },
            { data: 'TipoCuenta' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <i class="mdi mdi-pencil editar-cuenta" 
                           style="color: #ffc107; cursor: pointer;" 
                           title="Editar" 
                           data-id="${data}"></i>
                    `;
                }
            }
        ]        
    });

    $('#plandecuentas tbody').on('click', '.editar-cuenta', function () {
        let row = $('#plandecuentas').DataTable().row($(this).closest('tr')).data();
    
        // Llenamos el modal
        $('#edit-id').val(row.id);
        $('#edit-cuenta').val(row.Cuenta);
        $('#edit-nombre').val(row.NombreCuenta);
        $('#edit-tipo').val(row.TipoCuenta);
        $('#edit-nivel').val(row.Nivel);
        $('#edit-presupuestoMensual').val(row.PresupuestoMensual);
        $('#edit-presupuestoAnual').val(row.PresupuestoAnual);
        $('#edit-formaPago').prop('checked', row.FormaPago == 1);
        $('#edit-ordenCompra').prop('checked', row.OrdenesDeCompra == 1);
        $('#edit-anticipoEmpleado').prop('checked', row.Anticipos == 1);
        $('#edit-sinCodigo').prop('checked', row.Autorizacion == 1);
        $.ajax({
            url: '../Admin/Procesos/php/plandecuentas.php',
            method: 'POST',
            data: {
                accion: 'tiene_forma_pago',
                Cuenta: row.Cuenta
            },
            success: function (response) {
                const res = JSON.parse(response);
                $('#edit-formaPago').prop('checked', res.existe == 1);
            },
            error: function () {
                console.error('No se pudo verificar si tiene forma de pago');
            }
        });
        // Mostramos el modal
        const modalElement = document.getElementById('modalEditarCuenta');
        window.modalEditarCuenta = new bootstrap.Modal(modalElement);
        window.modalEditarCuenta.show();
    });


    $('#formEditarCuenta').on('submit', function (e) {
        e.preventDefault();
       
        let accion = $('#edit-id').val() ? 'editar_cuenta' : 'agregar_cuenta';

        $.ajax({
            url: '../Admin/Procesos/php/plandecuentas.php',
            method: 'POST',
            data: {
                accion: accion,
                id: $('#edit-id').val(),
                Cuenta: $('#edit-cuenta').val(),
                NombreCuenta: $('#edit-nombre').val(),
                TipoCuenta: $('#edit-tipo').val(),
                Nivel: $('#edit-nivel').val(),
                FormaPago: $('#edit-formaPago').is(':checked') ? 1 : 0,
                OrdenCompra: $('#edit-ordenCompra').is(':checked') ? 1 : 0,
                AnticipoEmpleado: $('#edit-anticipoEmpleado').is(':checked') ? 1 : 0,
                SinCodigo: $('#edit-sinCodigo').is(':checked') ? 1 : 0,
                CuentaBancaria: $('#edit-cuentaBancaria').is(':checked') ? 1 : 0,
                PresupuestoMensual: $('#edit-presupuestoMensual').val(),
                PresupuestoAnual: $('#edit-presupuestoAnual').val()
                
            },
            success: function (response) {
                let res = typeof response === "string" ? JSON.parse(response) : response;
            
                if (res.success) {
                    if (window.modalEditarCuenta) window.modalEditarCuenta.hide();
                    $('#plandecuentas').DataTable().ajax.reload(null, false);
                    Swal.fire('¡Éxito!', 'La cuenta fue guardada correctamente.', 'success');
                } else {
                    Swal.fire('Error', res.error || 'No se pudo guardar la cuenta.', 'error');
                }
            }
        });
    });

    $('#btnNuevaCuenta').on('click', function () {
        // Limpiar formulario
        $('#formEditarCuenta')[0].reset();
        $('#edit-id').val(''); // Sin ID
        $('#edit-formaPago').prop('checked', false);
        $('#edit-ordenCompra').prop('checked', false);
        $('#edit-anticipoEmpleado').prop('checked', false);
        $('#edit-sinCodigo').prop('checked', false);
        $('#edit-cuentaBancaria').prop('checked', false);
    
        // Cambiar título
        $('#editarCuentaLabel').text('Agregar nueva cuenta');
    
        // Mostrar el modal
        const modalElement = document.getElementById('modalEditarCuenta');
        window.modalEditarCuenta = new bootstrap.Modal(modalElement);
        window.modalEditarCuenta.show();
    });

    

});