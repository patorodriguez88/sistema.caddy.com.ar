$("#cargar_factura_btn_verificar").click(function () {
  let id = $("#codigodeaprobacion_t").val();
  let op = $("#switch1").is(":checked");

  $.ajax({
    data: { VerificarOp: 1, id: id, Operativo: op },
    url: "Procesos/php/proveedores.php",
    type: "post",
    beforeSend: function () {
      let text = "Verificando Código...";

      $("#loading-text").html(text);

      $("#loading").modal("show");
      $("#loading-bg").removeClass("bg-danger");
      $("#loading-bg").removeClass("bg-success");
      $("#loading-bg").addClass("bg-primary");

      setTimeout(function () {
        $("#loading").modal("hide");
      }, 5000);
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        if (jsonData.idOrden != 0) {
          var text =
            "Código Aprobado en la Orden de Compra Número " + jsonData.idOrden;
        } else {
          var text = "Código Aprobado ";
        }
        $(".spinner-border ml-auto").hide();
        $("#loading-bg").removeClass("bg-primary");
        $("#loading-bg").removeClass("bg-danger");
        $("#loading-bg").addClass("bg-success");
        $("#loading-icon").removeClass("mdi mdi-24px mdi-cancel");
        $("#loading-icon").addClass("mdi mdi-24px mdi-check");
        $("#loading-icon").show();
        $(".spinner-border").css("display", "none");

        $("#loading-text").html(text);

        $("#loading").modal("show");

        setTimeout(function () {
          $("#loading").modal("hide");
        }, 3000);

        $("#cargar_factura_btn_ok").prop("disabled", false);
        $("#codigodeaprobacion").css("display", "none");
        $("#switch1").prop("disabled", true);

        $("#cargar_factura_btn_verificar").css("display", "none");
        $("#notificacion").html("Transaccion Verificada");

        //tuneo el mensaje
        $(".alert.alert-danger")
          .removeClass("alert alert-danger")
          .addClass("alert alert-success");
        $("#notificacion_alert").removeClass("col-lg-8").addClass("col-lg-12");
        $("#notificacion_alert").show();
        $("#ctaasignada_t").val(1);
      } else {
        let text = "Código Incorrecto ";

        $("#loading-bg").removeClass("bg-primary");
        $("#loading-bg").addClass("bg-danger");

        $("#loading-icon").removeClass("mdi mdi-24px mdi-check");
        $("#loading-icon").addClass("mdi mdi-24px mdi-cancel");

        $("#loading-text").html(text);
        $("#loading-icon").show();
        $(".spinner-border").css("display", "none");

        $("#loading").modal("show");

        setTimeout(function () {
          $("#loading").modal("hide");
        }, 3000);
      }
    },
  });
});

//CARGAR FACTURA 1.0

$("#cargar_factura_btn_ok").click(function () {
  let operativo = $("#switch1").is(":checked");
  let Fecha = $("#fecha_t").val();
  let Rs = $("#razonsocial_t").val();

  let cuit = $("#cuit_t").val();
  let idproveedor = $("#buscarproveedor").val();
  let Tc = $("#tipodecomprobante_t").val();
  let Nc = $("#numerocomprobante_t").val();
  let Ncs = $("#numerodecomprobante_codigo_t").val();
  let NumeroComprobante = Ncs + "-" + Nc;
  let importeneto = $("#importeneto_t").val();
  let iva_1 = $("#iva1_t").val();
  let iva_2 = $("#iva2_t").val();
  let iva_3 = $("#iva3_t").val();
  let iva_4 = $("#iva4_t").val();
  let exento = $("#exento_t").val();
  let total = $("#total_t").val();
  let compra = $("#compra_t").val();
  let nasiento = $("#nasiento_t").val();
  let totaliva = $("#totaliva_t").val();
  let totalsiniva = $("#totalSiniva_t").val();
  let codigodeaprobacion = $("#codigodeaprobacion_t").val();
  let perciva = $("#perciva_t").val();
  let perciibb = $("#perciibb_t").val();
  let descripcion = $("#descripcion_t").val();

  if (Nc == null) {
    swal.fire({
      title: "Error",
      text: "El Número de Comprobante no puede ser nulo",
      icon: "error",
      confirmButtonText: "Ok",
    });
    return;
  }

  if (Tc == 0) {
    $("#tipodecomprobante_t").addClass("border border-danger");
    return;
  } else if (Ncs == "") {
    $("#numerodecomprobante_codigo_t").addClass("border border-danger");
    return;
  } else if (Nc == "") {
    $("#numerocomprobante_t").addClass("border border-danger");
    return;
  } else if (importeneto == 0 || importeneto == "") {
    $("#importeneto_t").addClass("border border-danger");
    return;
  }
  if (operativo == false && codigodeaprobacion == "") {
    $("#codigodeaprobacion_t").addClass("border border-danger");
    return;
  }

  var datos = {
    CargarFactura: 1,
    Fecha: Fecha,
    Operativo: operativo,
    idproveedor: idproveedor,
    razonsocial_t: Rs,
    cuit_t: cuit,
    tipodecomprobante_t: Tc,
    numerocomprobante_t: NumeroComprobante,
    importeneto_t: importeneto,
    iva1_t: iva_1,
    iva2_t: iva_2,
    iva3_t: iva_3,
    iva4_t: iva_4,
    exento_t: exento,
    total_t: total,
    compra_t: compra,
    nasiento_t: nasiento,
    totaliva_t: totaliva,
    totalSiniva_t: totalsiniva,
    codigodeaprobacion: codigodeaprobacion,
    perciva_t: perciva,
    perciibb_t: perciibb,
    descripcion_t: descripcion,
  };

  $.ajax({
    data: datos,
    url: "Procesos/php/proveedores.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (jsonData.success == 1) {
        $("#modal_cargar_factura").modal("hide");
        $("#success-alert-modal").modal("show");
        $("#success-alert-modal-text").html(
          "Se cargo el comprobante correctamente."
        );

        var table = $("#basic").DataTable();
        table.ajax.reload();
      } else if (jsonData.error == 1) {
        //SI EL LIBRO IVA YA ESTA CERRADO
        $("#modal_cargar_factura").modal("hide");
        $("#danger-alert-modal-text").html(
          " Error ! El Libro Iva  del mes " +
            jsonData.mes +
            " del año " +
            jsonData.ano +
            " ya se encuentra Cerrado"
        );
        $("#danger-alert-modal").modal("show");
      } else if (jsonData.error == 2) {
        //SI EL COMPROBANTE YA EXISTE
        $("#modal_cargar_factura").modal("hide");
        $("#danger-alert-modal-text").html(
          " Error ! El Comprobante " +
            jsonData.TipoComprobante +
            " " +
            jsonData.NumeroComprobante +
            " ya existe"
        );
        $("#danger-alert-modal").modal("show");
      } else if (jsonData.error == 3) {
        //SI EL COMPROBANTE YA EXISTE
        $("#modal_cargar_factura").modal("hide");
        $("#danger-alert-modal-text").html(
          " Error ! Código de aprobación incorrecto"
        );
        $("#danger-alert-modal").modal("show");
      }
    },
  });
});

$("#switch1").on("change", function () {
  solicitadatos();

  let val = $(this).is(":checked");

  if (val === true) {
    solicitarcodigo();
    $("#header-modal")
      .removeClass("modal-header modal-colored-header bg-danger")
      .addClass("modal-header modal-colored-header bg-success");
    $("#codigodeaprobacion").hide();
    $("#notificacion_alert").hide();
    // $('#imp_neto_1').hide();
    // $('#imp_neto_0').show();
  } else if (val === false) {
    $("#header-modal")
      .removeClass("modal-header modal-colored-header bg-success")
      .addClass("modal-header modal-colored-header bg-danger");
    // $('#imp_neto_1').show();
    // $('#imp_neto_0').hide();
    $("#notificacion").html("Requiere Código de Aprobación");
    $("#notificacion_alert").show();
    $("#codigodeaprobacion").show();
    $("#cargar_factura_btn_verificar").show();
  }
});

var ncomprobante_codigo = document.getElementById(
  "numerodecomprobante_codigo_t"
);
var updatetext = function () {
  ncomprobante_codigo.value = ("00000" + ncomprobante_codigo.value).slice(-5);
};
ncomprobante_codigo.addEventListener("keyup", updatetext, false);

var ncomprobante = document.getElementById("numerocomprobante_t");
var updatetext = function () {
  ncomprobante.value = ("00000000" + ncomprobante.value).slice(-8);
};
ncomprobante.addEventListener("keyup", updatetext, false);

function sumar() {
  var n1 = parseFloat(document.CargarFactura.importeneto_t.value);
  var n2 = parseFloat(document.CargarFactura.iva1_t.value);
  var n3 = parseFloat(document.CargarFactura.iva2_t.value);
  var n4 = parseFloat(document.CargarFactura.iva3_t.value);
  var n8 = parseFloat(document.CargarFactura.iva4_t.value);

  var n5 = parseFloat(document.CargarFactura.exento_t.value);
  var n6 = parseFloat(document.CargarFactura.perciva_t.value);
  var n7 = parseFloat(document.CargarFactura.perciibb_t.value);
  document.CargarFactura.total_t.value = n1 + n2 + n3 + n4 + n5 + n6 + n7 + n8;

  document.CargarFactura.totaliva_t.value = n2 + n3 + n4 + n8;
  document.CargarFactura.totalSiniva_t.value = n1 + n5;
}

function comprobar() {
  let op = $("#switch1").is(":checked");

  if (op === false) {
    var autorizado = document.getElementById("ctaasignada_t").value;

    var valor = parseFloat(document.CargarFactura.total_t.value);
    var tipo = document.CargarFactura.tipodecomprobante_t.value;

    if (autorizado == 0) {
      if (valor > "1000") {
        if (
          tipo == "NOTAS DE CREDITO A" ||
          tipo == "NOTAS DE DEBITO A" ||
          tipo == "NOTAS DE CREDITO B" ||
          tipo == "NOTAS DE DEBITO B" ||
          tipo == "NOTAS DE CREDITO C" ||
          tipo == "NOTAS DE DEBITO C" ||
          tipo == "NOTAS DE CREDITO POR OPERACIONES CON EL EXTERIOR" ||
          tipo == "NOTAS DE CREDITO O DOCUMENTO EQUIVALENTE QUE CUMPLA" ||
          tipo == "NOTAS DE CREDITO M" ||
          tipo == "NOTAS DE CREDITO DE COMPROBANTES CON COD. 34, 39," ||
          tipo == "RECIBOS FACTURA DE CREDITO" ||
          tipo == "NOTA DE CREDITO   SERVICIOS PUBLICOS   NOTA DE CRE" ||
          tipo == "AJUSTES CONTABLES QUE INCREMENTAN EL CREDITO FISCA" ||
          tipo == "NOTA DE CREDITO DE ASIGNACION"
        ) {
          // document.getElementById('codigodeaprobacion').style.display = 'none';
          $("#notificacion_alert").hide();
          $("#codigodeaprobacion").css("display", "none");
        } else {
          //EFECTIVO 000111100

          $("#notificacion_alert").show();
          $("#notificacion").html("Requiere Código de Aprobación");
          $("#codigodeaprobacion").show();
        }
      }
    }
  }
}

$(document).ready(function () {
  $.ajax({
    data: { cargar_proveedores: 1 },
    url: "Procesos/php/proveedores.php",
    method: "POST",
    dataType: "json",
    success: function (data) {
      if (data.success) {
        var proveedores = data.proveedores;
        var $select = $("#buscarproveedor");
        $select.empty();
        $select.append("<option>Seleccionar Proveedor</option>");
        $select.append('<optgroup label="Proveedores">');
        $.each(proveedores, function (index, proveedor) {
          $select.append(
            '<option value="' +
              proveedor.id +
              '">' +
              proveedor.RazonSocial +
              "</option>"
          );
        });
        $select.append("</optgroup>");
        $select.select2(); // Iniciar select2
      } else {
        alert("Error al cargar proveedores");
      }
    },
    error: function () {
      alert("Error en la petición AJAX");
    },
  });

  let id = $("#buscarproveedor").val();

  if (id == "Seleccionar Proveedor") {
    $("#botonera").hide();
  } else {
    $("#botonera").css("display", "inline");
  }
});

$("#buscarproveedor").change(function () {
  let id = $("#buscarproveedor").val();

  if (id == "Seleccionar Proveedor") {
    $("#botonera").hide();
  } else {
    $("#botonera").css("display", "inline");
  }
});

function solicitarcodigo() {
  //VERIFICAR SI EL LA CUENTA DEL PROVEEDOR SOLICITA CODIGO DE AUTORIZACION
  let cta = $("#cuentaasignada").val();

  $.ajax({
    data: { SolicitaCodigo: 1, Ctaasignada: cta },
    url: "Procesos/php/proveedores.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        console.log("Autorizar", jsonData.Autorizado);

        if (jsonData.Autorizado == 0) {
          $("#notificacion_alert").show();
          $("#notificacion").html(
            "La Cuenta Contable Requiere Código de Autorización"
          );
          $("#codigodeaprobacion").show();
          $("#cargar_factura_btn_verificar").show();
          //tuneo el mensaje
          $(".alert.alert-success")
            .removeClass("alert alert-success")
            .addClass("alert alert-danger");
          $("#notificacion_alert")
            .removeClass("col-lg-12")
            .addClass("col-lg-8");
          $("#notificacion_alert").show();
          $("#ctaasignada_t").val(0);
        } else {
          $("#notificacion_alert").hide();
          $("#codigodeaprobacion").hide();
          $("#cargar_factura_btn_verificar").hide();
          $("#cargar_factura_btn_ok").prop("disabled", false);
        }
      }
    },
  });
}

function solicitadatos() {
  let idproveedor = $("#buscarproveedor").val();

  $.ajax({
    data: { DatosProveedor: 1, idproveedor: idproveedor },
    url: "Procesos/php/proveedores.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        console.log("Vehiculo", jsonData.SolicitaVehiculo);

        if (jsonData.SolicitaVehiculo == 1) {
          $("#solicita_vehiculo").css("display", "block");
          // $('#notificacion_alert').show();
          // $('#notificacion').html("La Cuenta Contable Requiere Código de Autorización");
          // $('#codigodeaprobacion').show();
          // $('#cargar_factura_btn_verificar').show();
        } else {
          $("#solicita_vehiculo").css("display", "none");
          // $('#notificacion_alert').hide();
          // $('#codigodeaprobacion').hide();
          // $('#cargar_factura_btn_verificar').hide();
          // $('#cargar_factura_btn_ok').prop('disabled',false);
        }

        if (jsonData.SolicitaCombustible == 1) {
          $("#solicita_combustible").css("display", "block");
        } else {
          $("#solicita_combustible").css("display", "none");
        }
      }
    },
  });
}

$("#modal_cargar_factura").on("hidden.bs.modal", function (e) {
  $("#CargarFactura").trigger("reset");

  $("#switch1").prop("disabled", false);
  $("#switch1").prop("checked", true);

  $("#header-modal").removeClass("bg-danger").addClass("bg-success");
});

$("#modal_cargar_factura").on("show.bs.modal", function (e) {
  let op = $("#switch1").is(":checked");

  $("#fecha_t").val(new Date().toISOString().split("T")[0]);

  solicitadatos();

  if (op === true) {
    //controlo si la cuenta contable requiere autorizacion
    solicitarcodigo();
  }

  let id = $("#buscarproveedor").val();

  $.ajax({
    data: { Proveedores: 1, id: id },
    url: "Procesos/php/tablas.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        $.ajax({
          data: { Asiento: 1 },
          url: "Procesos/php/tablas.php",
          type: "post",
          success: function (response) {
            var jsonData_asiento = JSON.parse(response);
            if (jsonData_asiento.success == "1") {
              $("#nasiento_t").val(jsonData_asiento.Asiento);

              let SolicitaCombustible = jsonData.data[0].SolicitaCombustible;
              let SolicitaVehiculo = jsonData.data[0].SolicitaVehiculo;
              let TareasAsana = jsonData.data[0].TareasAsana;
              let PagoComprobantes = jsonData.data[0].Pago_comprobantes;

              $("#razonsocial_t").val(jsonData.data[0].RazonSocial);
              $("#cuit_t").val(jsonData.data[0].Cuit);

              $("#max_days_pay").val(PagoComprobantes);

              if (SolicitaCombustible == 1) {
                $("#solicita_combustible").css("display", "block");
                $("#solicita_combustible_l").css("display", "block");
              } else {
                $("#solicita_combustible").css("display", "none");
                $("#solicita_combustible_l").css("display", "none");
              }

              if (SolicitaVehiculo == 1) {
                $("#solicita_vehiculo").css("display", "block");
              } else {
                $("#solicita_vehiculo").css("display", "none");
              }
              if (TareasAsana == 1) {
                $("#alert_asana").show();
              } else {
                $("#alert_asana").hide();
              }
            }
          },
        });
      }
    },
  });
});
