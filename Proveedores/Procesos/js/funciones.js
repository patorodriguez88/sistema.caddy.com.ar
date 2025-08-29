function obtenerUsuarios() {
  $.ajax({
    data: { Usuarios_asana: 1 },
    url: "Procesos/php/tablas.php",
    type: "POST",
    success: function (data) {
      var opciones = JSON.parse(data);

      opciones.forEach(function (opcion) {
        $("#asana_gid").append(
          '<option value="' +
            opcion.gid_asana +
            '">' +
            opcion.Nombre +
            "</option>"
        );
      });
    },
    error: function () {
      alert("Error al obtener las opciones.");
    },
  });
}
function marcar(e) {
  // alert(e);
  console.log("tabla", e);

  var table = $("#basic").DataTable();

  $("#" + e).on("click", "tr", function () {
    if ($(this).hasClass("selected")) {
      $(this).removeClass("selected");
    } else {
      table.$("tr.selected").removeClass("selected");
      $(this).addClass("selected");
    }
  });
}

function borrar(i) {
  $("#id_factura_borrar").html(i);

  $.ajax({
    data: { Borrar_Factura: 1, id: i },
    url: "Procesos/php/proveedores.php",
    type: "post",
    success: function (response) {
      var j = JSON.parse(response);

      $("#danger-header-modal").modal("show");
      $("#btn_eliminar_pago_ok").hide();
      $("#btn_eliminar_ok").show();

      $("#info_factura_borrar").html(
        "Estas por elimnar el Comprobante " +
          j.data.NumeroComprobante +
          " de " +
          j.data.RazonSocial +
          " por un importe de $" +
          j.data.Debe +
          "</br></br><b> Confirmás la operación ?.</b>"
      );
    },
  });
}

function borrar_pago(i) {
  $("#id_factura_borrar").html(i);

  $.ajax({
    data: { Borrar_Pago: 1, id: i },
    url: "Procesos/php/proveedores.php",
    type: "post",
    success: function (response) {
      var j = JSON.parse(response);

      $("#danger-header-modal").modal("show");
      $("#btn_eliminar_ok").hide();
      $("#btn_eliminar_pago_ok").show();

      $("#info_factura_borrar").html(
        "Estas por elimnar el Pago del comprobante " +
          j.data.NumeroComprobante +
          " de " +
          j.data.RazonSocial +
          " por un importe de $" +
          j.data.Haber +
          "</br></br><b> Confirmás la operación ?.</b>"
      );
    },
  });
}

$("#btn_eliminar_ok").click(function () {
  let i = $("#id_factura_borrar").html();

  $.ajax({
    data: { Borrar_Factura_ok: 1, id: i },
    url: "Procesos/php/proveedores.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);

      console.log("success", jsonData.success);

      if (jsonData.success == 1) {
        $.NotificationApp.send(
          "Registro Eliminado !",
          "Se han realizado cambios.",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
        var table = $("#basic").DataTable();
        table.ajax.reload();
        $("#danger-header-modal").modal("hide");
      } else {
        $.NotificationApp.send(
          "Error !",
          "No se han realizado cambios.",
          "bottom-right",
          "#FFFFFF",
          "warning"
        );
      }
    },
  });
});

$("#btn_eliminar_pago_ok").click(function () {
  let i = $("#id_factura_borrar").html();

  $.ajax({
    data: { Borrar_Pago_ok: 1, id: i },
    url: "Procesos/php/proveedores.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);

      // console.log('success',jsonData.success);

      if (jsonData.success == 1) {
        $.NotificationApp.send(
          "Registro Eliminado !",
          "Se han realizado cambios.",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
        var table = $("#basic").DataTable();
        table.ajax.reload();
        $("#danger-header-modal").modal("hide");
      } else {
        console.log("NumeroAsiento", jsonData.NumeroAsiento);

        // $.NotificationApp.send("Error !","No se han realizado cambios.","bottom-right","#FFFFFF","warning");
      }
    },
  });
});

document.getElementById("tareas_asana").addEventListener("change", function () {
  // Detecta si el switch está activado o no
  const switchActivo = document.getElementById("tareas_asana").checked;

  if (switchActivo) {
    obtenerUsuarios();
    document.getElementById("asana_gid").disabled = false; // Para habilitarlo
  } else {
    document.getElementById("asana_gid").disabled = true; // Para deshabilitarlo
  }
});

$(document).ready(function () {
  // Inicializar select2
  $("#nueva_cuentaasignada").select2();

  // Llamada AJAX para obtener las cuentas
  $.ajax({
    data: { Cuentas: 1 },
    url: "Procesos/php/tablas.php",
    type: "POST",
    dataType: "json", // Asegura que los datos se interpreten como JSON
    success: function (data) {
      // Iterar sobre los datos y agregarlos al select
      data.forEach(function (cuenta) {
        $("#nueva_cuentaasignada").append(
          `<option value="${cuenta.Cuenta}">${cuenta.Cuenta} | ${cuenta.NombreCuenta}</option>`
        );
      });
    },
    error: function (xhr, status, error) {
      console.error("Error al cargar las cuentas: ", error);
    },
  });

  if ($("#tareas_asana").is(":checked")) {
    console.log("El checkbox está activo");
    obtenerUsuarios();
    document.getElementById("asana_gid").disabled = false; // Para habilitarlo
  } else {
    document.getElementById("asana_gid").disabled = true; // Para deshabilitarlo
  }

  function currencyFormat(num) {
    return "$ " + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
  }
  // function getParameterByName(name) {
  //   name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
  //   var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
  //     results = regex.exec(location.search);
  //   return results === null
  //     ? ""
  //     : decodeURIComponent(results[1].replace(/\+/g, " "));
  // }
  // var prodId = getParameterByName("id");

  $("#modificar_cuenta").click(function () {
    document.getElementById("nueva_cuentaasignada").selectedIndex = 0;
    document.getElementById("cuenta_contable_select").style.display = "block";
    document.getElementById("cuenta_contable").style.display = "none";
  });

  $("#agregar_botton").click(function () {
    $("#editor").hide(); // Oculta selector proveedor
    $("#row_contacto").hide(); // Oculta otra fila (si corresponde)
    $("#dashboard-information").hide();
    $("#agregar_botton").addClass("d-none"); // Oculta el botón con Bootstrap
    $("#billing-information").show(); // Muestra el bloque de carga
    $("#razonsocial").attr("readonly", false); // Habilita edición
  });

  $(document).on("change", 'input[type="checkbox"]', function (e) {
    if (this.id == "solicitavehiculo") {
      if (this.checked) $("#solicitavehiculo").val(1);
      else $("#solicitavehiculo").val(0);
    }

    if (this.id == "solicitacombustible") {
      if (this.checked) $("#solicitacombustible").val(1);
      else $("#solicitacombustible").val(0);
    }
    if (this.id == "tareas_asana") {
      if (this.checked) $("#tareas_asana").val(1);
      else $("#tareas_asana").val(0);
    }
  });

  //Seleccionas todos los elementos con clase test
  var divs = document.getElementsByClassName("form-control");

  //Recorres la lista de elementos seleccionados
  for (var i = 0; i < divs.length; i++) {
    //Añades un evento a cada elemento
    divs[i].addEventListener("change", function () {
      //   $.NotificationApp.send("Recuerde Guardar el formulario !","Se han realizado cambios.","bottom-right","#FFFFFF","info");
    });
  }
  //IDEM FORM PARA CUSTOM
  var divsC = document.getElementsByClassName("custom-control-input");

  //Recorres la lista de elementos seleccionados
  for (var i = 0; i < divsC.length; i++) {
    //Añades un evento a cada elemento
    divsC[i].addEventListener("change", function () {
      //   $.NotificationApp.send("Recuerde Guardar el formulario !","Se han realizado cambios.","bottom-right","#FFFFFF","info");
    });
  }

  var table = $("#basic").DataTable();
  table.destroy();

  $("#buscarproveedor").change(function () {
    // var id = document.getElementById("buscarproveedor").value;
    var id = $(this).val();

    if (id === "0") {
      $("#agregar_botton").removeClass("d-none");
    } else {
      $("#agregar_botton").addClass("d-none");
    }
    //BUSCO TIPO DE COMPROBANTES
    $.ajax({
      url: "Procesos/php/tablas.php",
      type: "POST",
      data: { TipoDeComprobante: 1 },
      success: function (response) {
        try {
          const json = JSON.parse(response);
          if (json.success === 1) {
            $("#tipodecomprobante_t").val(json.tipo);
          } else {
            Swal.fire(
              "Aviso",
              "No se encontró el tipo de comprobante.",
              "info"
            );
          }
        } catch (e) {
          console.error("Respuesta no válida:", response);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error AJAX:", status, error);
      },
    });
    //BUSCO ANTICIPOS
    // $.ajax({
    //     data: {'Anticipos_saldo':1,'idProveedor':id},
    //     url:'Procesos/php/tablas.php',
    //     type:'post',
    //     success: function(response)
    //      {
    //         var jsonData = JSON.parse(response);
    //        $('#anticipos_ctacte').html(currencyFormat(Number(jsonData.Anticipos)));
    //      }
    // })

    var table = $("#basic").DataTable();
    table.destroy();

    var dato = { Datos: 1, id: id };
    $.ajax({
      data: dato,
      url: "Procesos/php/funciones.php",
      type: "post",
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == "1") {
          document.getElementById("steps").style.display = "flex";

          $("#codigo").val(jsonData.id);
          $("#razonsocial").val(jsonData.RazonSocial);
          $("#direccion").val(jsonData.direccion);
          $("#localidad").val(jsonData.localidad);
          $("#provincia").val(jsonData.provincia);
          $("#codigopostal").val(jsonData.codigopostal);
          $("#telefono").val(jsonData.telefono);
          $("#celular").val(jsonData.celular);
          $("#contacto").val(jsonData.contacto);
          $("#iva").val(jsonData.iva);
          $("#cuit").val(jsonData.Cuit);
          $("#rubro").val(jsonData.Rubro);
          $("#condicion").val(jsonData.Condicion);
          $("#email").val(jsonData.Mail);
          $("#web").val(jsonData.Web);
          $("#observaciones").val(jsonData.Observaciones);
          $("#ingresosbrutos").val(jsonData.IngresosBrutos);
          $("#max_days_pay").val(jsonData.Pago_comprobantes);

          $("#cuenta_contable_select").css("display", "none");

          $("#cuentaasignada").val(jsonData.CuentaAsignada);

          $("#cuenta_contable").css("display", "block");

          if (jsonData.SolicitaVehiculo == 1) {
            document.getElementById("solicitavehiculo").checked = true;
          } else {
            document.getElementById("solicitavehiculo").checked = false;
          }
          if (jsonData.SolicitaCombustible == 1) {
            document.getElementById("solicitacombustible").checked = true;
          } else {
            document.getElementById("solicitacombustible").checked = false;
          }
          if (jsonData.TareasAsana == 1) {
            document.getElementById("tareas_asana").checked = true;
          } else {
            document.getElementById("tareas_asana").checked = false;
          }

          var id = document.getElementById("codigo").value;

          var datatable = $("#basic").DataTable({
            order: [[0, "desc"]],
            dom: "Bfrtip",
            // buttons: obtenerBotonesExportacion(),
            buttons: ["copy", "csv", "excel", "pdf", "print"],
            paging: true,
            searching: true,
            footerCallback: function (row, data, start, end, display) {
              total = this.api()
                .column(4) //numero de columna a sumar
                //.column(1, {page: 'current'})//para sumar solo la pagina actual
                .data()
                .reduce(function (a, b) {
                  return parseInt(a) + parseInt(b);
                }, 0);
              total1 = this.api()
                .column(5) //numero de columna a sumar
                //.column(1, {page: 'current'})//para sumar solo la pagina actual
                .data()
                .reduce(function (a, b) {
                  return parseInt(a) + parseInt(b);
                }, 0);
              var sumadebe = currencyFormat(total);
              var sumahaber = currencyFormat(total1);
              var saldo = currencyFormat(total - total1);
              var saldo1 = Number(total - total1);

              $("#saldo_ctacte").html(saldo);

              if (saldo1 == 0) {
                document.getElementById("saldo_ctacte").className = "text-info";
              } else if (saldo1 > 0) {
                document.getElementById("saldo_ctacte").className =
                  "text-danger";
              } else if (saldo1 < 0) {
                document.getElementById("saldo_ctacte").className =
                  "text-warning";
              }
              $(this.api().column(4).footer()).html(sumadebe);
              $(this.api().column(5).footer()).html(sumahaber);
            },
            ajax: {
              url: "Procesos/php/tablas.php",
              data: { CtaCte: 1, id: id },
              type: "post",
            },
            columns: [
              {
                data: "Fecha",
                render: function (data, type, row) {
                  var Fecha = row.Fecha.split("-").reverse().join(".");
                  return (
                    '<td><span style="display: none;">' +
                    row.Fecha +
                    "</span>" +
                    Fecha +
                    "</td>"
                  );
                },
              },
              {
                data: "TipoDeComprobante",
                render: function (data, type, row) {
                  if (row.Debe != 0) {
                    if (row.Saldo == 0) {
                      var status =
                        '<span class="badge badge-success">Pagada</span>';
                    } else {
                      status =
                        '<span class="badge badge-warning text-white">Pendiente</span>';
                    }
                  } else {
                    status = "";
                  }
                  return `<p class="m-0 d-inline-block align-middle font-10">
                        <a href="apps-ecommerce-products-details.html" class="text-body">${row.TipoDeComprobante}</a>
                        <br><small class="mr-2 font-10"> ${row.NumeroComprobante} </small></p></br>${status}`;
                },
              },
              {
                data: "Concepto",
                render: function (data, type, row) {
                  if (row.Debe != 0) {
                    return `<p class="m-0 d-inline-block align-middle font-8"><td style='max-width: 15px;'>${row.Concepto}</td><br><small class="mr-2 font-10"> ${row.NumeroComprobante} </small></p>`;
                    // return row.NumeroComprobante;
                  } else {
                    return `<p class="m-0 d-inline-block align-middle font-8"><td style='max-width: 15px;'>${row.Concepto}</td><br><small class="mr-2 font-10"> ${row.FormaDePago} </small></p>`;
                    // return row.FormaDePago;
                  }
                },
              },
              {
                data: "Descripcion",
                render: function (data, type, row) {
                  return `<td style='max-width: 15px;'>${row.Descripcion}</td>`;
                },
              },
              {
                data: "Debe",
                render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
              },
              {
                data: "Haber",
                render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
              },
              {
                data: "id",
                render: function (data, type, row) {
                  if (row.Haber > 0) {
                    return (
                      "<td>" +
                      "<a target='_blank' href='Informes/OrdenDePagopdf.php?Factura=" +
                      row.id +
                      "'><i class='mdi mdi-18px mdi-file-search-outline'></i></a>" +
                      "</td>"
                    );
                  } else {
                    if (row.img == 1) {
                      //file exists
                      return (
                        "<td>" +
                        "<a target='_blank' href='../FacturasCompra/" +
                        row.id +
                        ".pdf'><i class='mdi mdi-18px mdi-file-search-outline'></i></a>" +
                        "</td>"
                      );
                    } else {
                      //file not exists
                      return (
                        "<td>" +
                        "<a><i class='mdi mdi-18px mdi-file-upload-outline' onclick='subir_factura(" +
                        row.id +
                        ");'></i></a>" +
                        "</td>"
                      );
                    }
                  }
                },
              },
              {
                data: null,
                render: function (data, type, row) {
                  if (row.Debe != 0) {
                    if (row.Saldo == 0) {
                      return (
                        '<i class="ml-2 mdi mdi-cash-check text-success mdi-24px" id="' +
                        row.NumeroComprobante +
                        '" onclick="marcar(this.id)"></i>'
                      );
                    } else {
                      return (
                        '<td class="dtr-control dt-checkboxes-cell">' +
                        '<div class="form-check"><input value="' +
                        row.id +
                        '" type="checkbox" onclick="abrir_factura()" class="form-check-input dt-checkboxes">' +
                        '<label class="form-check-label">&nbsp;</label>' +
                        '<i class="ml-2 mdi-18px mdi mdi-trash-can-outline text-danger" onclick="borrar(' +
                        row.id +
                        ')"/></div></td>'
                      );
                    }
                  } else {
                    return (
                      '<td><div class="form-check"></div><i class="ml-2 mdi-18px mdi mdi-trash-can-outline text-danger" onclick="borrar_pago(' +
                      row.id +
                      ')"/></div></td>'
                    );
                  }
                },
              },
            ],
          });
          $("#telefono_contacto").html(" Telefono: " + jsonData.celular);
          $("#mail_contacto").html(" Mail: " + jsonData.Mail);
          $("#contacto_contacto").html(" Contacto: " + jsonData.contacto);
        } else {
        }
      },
    });

    $.ajax({
      data: { Tablero: 1, id: id },
      url: "Procesos/php/funciones.php",
      type: "post",
      success: function (response1) {
        var jsonData = JSON.parse(response1);
        if (jsonData.success == "1") {
          var ComprasMes = currencyFormat(Number(jsonData.ComprasMesAnt));
          var ComprasAno = currencyFormat(Number(jsonData.ComprasAno));
          var Saldo = currencyFormat(Number(jsonData.Saldo));
          var Fecha = jsonData.UltFacFecha.split("-").reverse().join(".");
          var FechaUltPago = jsonData.FechaUltPago.split("-")
            .reverse()
            .join(".");
          var Debe = currencyFormat(Number(jsonData.UltFacDebe));
          var PromedioMensual = currencyFormat(
            Number(jsonData.PromedioMensual)
          );
          var PromedioMensualAnt = jsonData.PromedioMensualAnt;
          var ComprasAnoAntT = jsonData.ComprasAnoAntT.toFixed(2);
          var PenUltFacDebe = jsonData.PenUltFacDebe;
          var UltPago = currencyFormat(Number(jsonData.UltPago));

          $("#compras_mes").html(PromedioMensual);
          $("#compras_ano").html(ComprasAno);
          $("#saldo").html(Saldo);
          $("#fecha").html(Fecha);
          $("#debe").html(Debe);
          if (PenUltFacDebe > 0) {
            document.getElementById("tipo").className = "mdi mdi-arrow-up-bold";
          }
          $("#tipo").html(PenUltFacDebe.toFixed(2));
          $("#numero").html("Desde el Ult. Comp.");
          $("#compras_mes_ant").html(PromedioMensualAnt.toFixed(2));
          $("#fecha_ult_pago").html("Últ. Pago el " + FechaUltPago);
          $("#importe_ult_pago").html(UltPago);

          //           $("#compras_mes_ant").html(ComprasMesAnt);

          if (jsonData.Saldo > 0) {
            document.getElementById("card_saldo").className =
              "card widget-flat bg-danger text-white";
          } else if (jsonData.Saldo == 0) {
            document.getElementById("card_saldo").className =
              "card widget-flat bg-success text-white";
          } else if (jsonData.Saldo < 0) {
            document.getElementById("card_saldo").className =
              "card widget-flat bg-warning text-white";
          }
        }
      },
    });
  });

  $("#agregar_botton_ok").click(function () {
    var razonsocial = document.getElementById("razonsocial").value;
    var dir = document.getElementById("direccion").value;
    var loc = document.getElementById("localidad").value;
    var prov = document.getElementById("provincia").value;
    var cp = document.getElementById("codigopostal").value;
    var tel = document.getElementById("telefono").value;
    var cel = document.getElementById("celular").value;
    var contacto = document.getElementById("contacto").value;
    var condicion = document.getElementById("condicion").value;
    var iva = document.getElementById("iva").value;
    var cuit = document.getElementById("cuit").value;
    var rubro = document.getElementById("rubro").value;
    var email = document.getElementById("email").value;
    var web = document.getElementById("web").value;
    var obs = document.getElementById("observaciones").value;
    var ib = document.getElementById("ingresosbrutos").value;
    var comb = document.getElementById("solicitacombustible").value;
    var asana = document.getElementById("tareas_asana").value;
    var vehi = document.getElementById("solicitavehiculo").value;
    var ctaas = document.getElementById("cuentaasignada").value;
    var asana_gid = document.getElementById("asana_gid").value;
    if (ctaas == "" || ctaas == "000000000") {
      var ctaas = document.getElementById("nueva_cuentaasignada").value;
    }

    if (ctaas == "000000000" || ctaas == "Seleccionar Cuenta Contable") {
      Swal.fire({
        title: "Error!",
        text: "Verifique la Cuenta Contable Asignada",
        icon: "warning",
        confirmButtonText: "Aceptar",
      });
    } else {
      var dato = {
        Agregar: 1,
        razonsocial: razonsocial,
        dire: dir,
        loc: loc,
        prov: prov,
        cp: cp,
        tel: tel,
        cel: cel,
        contacto: contacto,
        condicion: condicion,
        iva: iva,
        cuit: cuit,
        rubro: rubro,
        email: email,
        web: web,
        obs: obs,
        ib: ib,
        comb: comb,
        vehi: vehi,
        ctaas: ctaas,
        asana: asana,
        asana_gid: asana_gid,
      };

      $.ajax({
        data: dato,
        url: "Procesos/php/funciones.php",
        type: "post",
        success: function (response) {
          var jsonData = JSON.parse(response);
          if (jsonData.success == "1") {
            Swal.fire({
              title: "Listo!",
              text: "Creamos el Proveedor",
              icon: "success",
              confirmButtonText: "Aceptar",
            });
          } else if (jsonData.success == "2") {
            Swal.fire({
              title: "Error!",
              text: "El Nombre, Direccion o Cuit, ya existen en el sistema",
              icon: "error",
              confirmButtonText: "Aceptar",
            });
            Swal.fire({
              title: "Error!",
              text: "El nombre no puede ser NULL",
              icon: "error",
              confirmButtonText: "Aceptar",
            });
          }
        },
      });
    }
  });

  $("#guardar_botton").click(function () {
    var id = document.getElementById("buscarproveedor").value;
    var dir = document.getElementById("direccion").value;
    var loc = document.getElementById("localidad").value;
    var prov = document.getElementById("provincia").value;
    var cp = document.getElementById("codigopostal").value;
    var tel = document.getElementById("telefono").value;
    var cel = document.getElementById("celular").value;
    var contacto = document.getElementById("contacto").value;
    var condicion = document.getElementById("condicion").value;
    var iva = document.getElementById("iva").value;
    var cuit = document.getElementById("cuit").value;
    var rubro = document.getElementById("rubro").value;
    var email = document.getElementById("email").value;
    var web = document.getElementById("web").value;
    var obs = document.getElementById("observaciones").value;
    var ib = document.getElementById("ingresosbrutos").value;
    var comb = document.getElementById("solicitacombustible").value;
    var vehi = document.getElementById("solicitavehiculo").value;
    var asana = document.getElementById("tareas_asana").value;
    var asana_gid = document.getElementById("asana_gid").value;
    var PagoComprobante = document.getElementById("max_days_pay").value;

    if (
      document.getElementById("nueva_cuentaasignada").value !=
      "Seleccionar Cuenta Contable"
    ) {
      var ctaas = document.getElementById("nueva_cuentaasignada").value;
    } else {
      var ctaas = document.getElementById("cuentaasignada").value;
    }

    var dato = {
      Actualizar: 1,
      id: id,
      dir: dir,
      loc: loc,
      prov: prov,
      cp: cp,
      tel: tel,
      cel: cel,
      contacto: contacto,
      condicion: condicion,
      iva: iva,
      cuit: cuit,
      rubro: rubro,
      email: email,
      web: web,
      obs: obs,
      ib: ib,
      comb: comb,
      vehi: vehi,
      ctaas: ctaas,
      asana: asana,
      asana_gid: asana_gid,
      pago_comprobante: PagoComprobante,
    };

    $.ajax({
      data: dato,
      url: "Procesos/php/funciones.php",
      type: "post",
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == "1") {
          $.NotificationApp.send(
            "Listo!",
            "Datos Guardados",
            "bottom-right",
            "#FFFFFF",
            "success"
          );
        } else {
        }
      },
    });
  });
});

function subir_factura(id) {
  $("#standard-modal").modal("show");
  document.getElementsByName("id_comprobante_subir")[0].value = id;
  $("#id_comprobante_subir_html").html(id);
}

function abrir_factura() {
  var oTable = $("#basic").dataTable();

  var allPages = oTable.fnGetNodes();

  //Creamos un array que almacenará los valores de los input "checked"
  var checked = [];

  //Recorremos todos los input checkbox que se encuentren "checked"
  $("input.form-check-input:checked", allPages).each(function () {
    //Mediante la función push agregamos al arreglo los values de los checkbox
    if ($(this).attr("value") != null) {
      checked.push($(this).attr("value"));
    }
    // Utilizamos console.log para ver comprobar que en realidad contiene algo el arreglo
    if (checked.length > 1) {
      $(this).prop("checked", false);
      $.NotificationApp.send(
        "Error !",
        "Seleccione de a un comprobante por vez para Imputar Pagos.",
        "bottom-right",
        "#FFFFFF",
        "waring"
      );
    } else {
      $("#btn_pago_facturas").css("display", "block");
    }
  });
}

function verificarArchivoEnServidor(nombreArchivo) {
  fetch(nombreArchivo)
    .then((response) => {
      if (response.ok) {
        return 1;
      } else {
        return 0;
      }
    })
    .catch((error) => {
      console.error("Error al verificar el archivo:", error);
    });
}

// Llamar a la función con el nombre del archivo que deseas verificar
// verificarArchivoEnServidor('ruta/del/archivo.txt');

$("#standard-modal_btn_ok").click(function () {
  var datatable = $("#basic").DataTable();
  datatable.ajax.reload(null, false);
});
