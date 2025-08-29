function ver_tabla_servicios() {
  if (document.getElementById("id_origen").value == null) {
    var cliente = document.getElementById("id_origen2").value;
  } else {
    var cliente = document.getElementById("id_origen").value;
  }

  var datatable = $("#basic").DataTable({
    paging: false,
    searching: false,
    ajax: {
      url: "Procesos/php/ventas-tablas.php",
      data: { id_cliente: cliente, datos: 1 },
      type: "post",
    },
    columns: [
      { data: "Codigo" },
      { data: "Titulo" },
      { data: "Cantidad" },
      {
        data: "Precio",
        render: $.fn.dataTable.render.number(".", ",", 0, "$ "),
      },
      {
        data: "Total",
        render: $.fn.dataTable.render.number(".", ",", 0, "$ "),
      },
      { data: "Comentario" },
      {
        data: "idPedido",
        render: function (data, type, row) {
          if (row.Codigo != 187) {
            return (
              "<td>" +
              "<a class='action-icon' onclick='eliminar(" +
              row.idPedido +
              ")'><i class='uil-trash-alt'></i></a>" +
              "</td>"
            );
          } else {
            return "<td></td>";
          }
        },
      },
    ],
  });

  // var cliente=document.getElementById('id_origen').value;
  var datatable1 = $("#basic-total").DataTable({
    paging: false,
    searching: false,
    bPaginate: false,
    bLengthChange: false,
    bFilter: false,
    bInfo: false,
    bAutoWidth: false,
    ajax: {
      url: "Procesos/php/ventas-tablas.php",
      data: { id_cliente: cliente, totales: 1 },
      type: "post",
    },
    columns: [
      { data: "Cantidad" },
      {
        data: "Neto",
        render: $.fn.dataTable.render.number(".", ",", 0, "$ "),
      },
      {
        data: "Iva",
        render: $.fn.dataTable.render.number(".", ",", 0, "$ "),
      },
      {
        data: "Total",
        render: $.fn.dataTable.render.number(".", ",", 0, "$ "),
      },
    ],
  });
}

$("#crearorigen").click(function () {
  var dato = document.getElementById("crearorigen").dataset.parent;
  document.getElementById("valorcrear").value = dato;
  $("#datonuevocliente").html("Agregar Cliente de " + dato);
  $("#nombrecliente_nc").val("");
  $("#direccion_nc").val("");
  $("#email_nc").val("");
  $("#telefono_nc").val("");
  $("#celular_nc").val("");
  $("#Calle_nc").val("");
  $("#Barrio_nc").val("");
  $("#Numero_nc").val("");
  $("#ciudad_nc").val("");
  $("#cp_nc").val("");
});
$("#creardestino").click(function () {
  var dato = document.getElementById("creardestino").dataset.parent;
  $("#datonuevocliente").html("Agregar Cliente de " + dato);
  document.getElementById("valorcrear").value = dato;
  $("#nombrecliente_nc").val("");
  $("#direccion_nc").val("");
  $("#email_nc").val("");
  $("#telefono_nc").val("");
  $("#celular_nc").val("");
  $("#Calle_nc").val("");
  $("#Barrio_nc").val("");
  $("#Numero_nc").val("");
  $("#ciudad_nc").val("");
  $("#cp_nc").val("");
});

function ComprobarNombre(n) {
  $.ajax({
    data: { ComprobarNombre: 1, Nombre: n },
    url: "Procesos/php/funciones.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        document.getElementById("errorname").style.display = "none";
      } else {
        document.getElementById("errorname").style.display = "block";
        $("#errorname_label").html(
          "Ya existen " +
            jsonData.num +
            " " +
            n +
            " cargados en el sistema, verifique !"
        );
      }
    },
  });
}

function BuscarDireccion() {
  var inputstart = document.getElementById("direccion_nc");
  var autocomplete = new google.maps.places.Autocomplete(inputstart, {
    types: ["geocode", "establishment"],
    componentRestrictions: { country: ["AR"] },
  });
  autocomplete.addListener("place_changed", function () {
    var place = autocomplete.getPlace();

    if (place.address_components) {
      var components = place.address_components;
      var ciudad = "";
      var provincia = "";
      for (var i = 0, component; (component = components[i]); i++) {
        if (component.types[0] == "administrative_area_level_1") {
          provincia = component["long_name"];
        }
        if (component.types[0] == "locality") {
          ciudad = component["long_name"];
          document.getElementById("ciudad_nc").value = ciudad;
        }
        if (component.types[0] == "postal_code") {
          document.getElementById("cp_nc").value = component["short_name"];
        }
        if (component.types[0] == "neighborhood") {
          if (component["long_name"] != null) {
            document.getElementById("Barrio_nc").value = component["long_name"];
          } else if (component.types[0] == "administrative_area_level_2") {
            document.getElementById("Barrio_nc").value = component["long_name"];
          }
        }
        if (component.types[0] == "street_number") {
          document.getElementById("Numero_nc").value = component["long_name"];
        }
        if (component.types[0] == "route") {
          document.getElementById("Calle_nc").value = component["long_name"];
        }
      }
    }
  });
}
function getParameterByName(name) {
  name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
  var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
  return results === null
    ? ""
    : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function calculo(a) {
  var cantidad = document.getElementById("cantidad").value;
  document.getElementById("total").value = cantidad * a;
}

function eliminar(a) {
  // var idOrigen=document.getElementById('id_origen').value;
  if (document.getElementById("id_origen").value == null) {
    var idOrigen = document.getElementById("id_origen2").value;
  } else {
    var idOrigen = document.getElementById("id_origen").value;
  }
  $.ajax({
    data: { id: a, Eliminar: 1, id_origen: idOrigen },
    url: "Procesos/php/AgregarVenta.php",
    type: "post",
    beforeSend: function () {
      //           document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        var tabletotales = $("#basic-total").DataTable();
        tabletotales.ajax.reload();

        var table = $("#basic").DataTable();
        table.ajax.reload();

        $.NotificationApp.send(
          "Listo!",
          "Eliminaste el registro correctamente ",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
      }
    },
  });
}
$("#imprimir").click(function () {
  var codigo = getParameterByName("Repo");
  window.open("../Ventas/Informes/Remitopdf2.php?CS=" + codigo, "_blank");
});
$("#terminar").click(function () {
  window.open("../Ventas/Ventas_e.php", "_self");
});

$("#cobrar").click(function () {
  var codigo = getParameterByName("Repo");
  $("#success-header-modal").modal("hide");
  //   alert(codigo);
  //   window.open("https://www.caddy.com.ar/SistemaTriangular/Ventas/Ventas.php?UltimoPaso=Cobro&Remito="+codigo, '_self');
});

$("#standard-modal").on("show.bs.modal", function () {
  $("#success-header-modal").modal("hide");
});

function distancia() {
  if (document.getElementById("id_origen").value == null) {
    var idOrigen = document.getElementById("id_origen2").value;
  } else {
    var idOrigen = document.getElementById("id_origen").value;
  }

  if (document.getElementById("id_destino").value == null) {
    var idDestino = document.getElementById("id_destino2").value;
  } else {
    var idDestino = document.getElementById("id_destino").value;
  }

  $.ajax({
    data: { origen: idOrigen, destino: idDestino },
    type: "POST",
    url: "../Google/distancematrix.php",
    success: function (response) {
      var jsonData = JSON.parse(response);
      var dis = jsonData.distancia / 1000;
      $("#distancia").html("km: " + dis.toFixed(2));
      $("#duration").html(jsonData.duration);
      $("#km_nc").val(dis.toFixed(2));
      $("#google_km").val(jsonData.distancia);
      $("#google_time").val(jsonData.duration2);
    },
  });
}

$(document).ready(function () {
  //LIMPIO ORIGEN POR SI QUEDO GUARDADO
  document.getElementById("id_origen").value = "";
  document.getElementById("valorcrear").value = "";
  var prodId = getParameterByName("UltimoPaso");
  var codigo = getParameterByName("Repo");
  if (prodId == "Si") {
    $("#success-header-modal").modal("show");
    $("#codseg").html(codigo);
  }
  ver_tabla_servicios();
  $.ajax({
    data: { Limpiar: 1 },
    url: "Procesos/php/funciones.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        $.NotificationApp.send(
          "Limpieza",
          "Limpieza de " + jsonData.Num + " registros realizada",
          "top-right",
          "#0ACF97",
          "info"
        );
      }
    },
  });

  //CARGO LOS SERVICIOS

  // Inicializar select2
  $("#servicio").select2();

  $.ajax({
    data: { Servicios: 1 },
    url: "Procesos/php/ventas-tablas.php",
    type: "POST",
    dataType: "json", // Asegúrate de especificar que esperas JSON
    success: function (data) {
      data.forEach(function (servicio) {
        $("#servicio").append(
          `<option value="${servicio.id}">${servicio.Titulo} | $ ${servicio.PrecioVenta}</option>`
        );
      });
    },
    error: function (xhr, status, error) {
      console.error("Error al cargar las cuentas: ", error);
    },
  });
});

$("#cobroacuenta_button").click(function () {
  // ESTE DATO SE PUEDE PARAMETRIZAR DESDE CLIENTES POR DEFECTO 4%
  // var idOrigen=document.getElementById('id_origen').value;
  if (document.getElementById("id_origen").value == null) {
    var idOrigen = document.getElementById("id_origen2").value;
  } else {
    var idOrigen = document.getElementById("id_origen").value;
  }
  var dato = "178"; // ID DEL CODIGO DEL COBRO A CUENTA
  var cantidad = 1;
  var importe = document.getElementById("cobroacuenta_input").value;
  var comentario = "COBRANZA INTEGRADA ($ " + importe + ")";
  var nventa = document.getElementById("nventa").value;
  var seguimiento = document.getElementById("seguimiento").value;

  $.ajax({
    data: {
      id: dato,
      cantidad: cantidad,
      Comentario: comentario,
      importe: importe,
      cargarcobroacuenta: 1,
      idOrigen: idOrigen,
      nventa: nventa,
      seguimiento: seguimiento,
    },
    url: "Procesos/php/AgregarVenta.php",
    type: "post",
    beforeSend: function () {
      //           document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        var table = $("#basic").DataTable();
        table.ajax.reload();
        var tabletotales = $("#basic-total").DataTable();
        tabletotales.ajax.reload();
      } else if (jsonData.success == "2") {
        $.NotificationApp.send(
          "Error",
          "No seleccionaste ningún cliente origen ",
          "bottom-right",
          "#FFFFFF",
          "error"
        );
      } else {
        $.NotificationApp.send(
          "Error",
          "No seleccionaste ningún servicio ",
          "bottom-right",
          "#FFFFFF",
          "error"
        );
      }
    },
  });
});

$("#valordeclarado_button").click(function () {
  valor_declarado();
});

function valor_declarado() {
  // ESTE DATO SE PUEDE PARAMETRIZAR DESDE CLIENTES POR DEFECTO 4%
  if (document.getElementById("id_origen").value == null) {
    var idOrigen = document.getElementById("id_origen2").value;
  } else {
    var idOrigen = document.getElementById("id_origen").value;
  }

  var dato = "177"; // ID DEL CODIGO DE VALOR DECLARADO
  var cantidad = 1;
  var importe = document.getElementById("valordeclarado_input").value;
  var comentario = "SEGURO SEGUN VALOR DECLARADO ($ " + importe + ")";
  var nventa = document.getElementById("nventa").value;
  var seguimiento = document.getElementById("seguimiento").value;

  $.ajax({
    data: {
      id: dato,
      cantidad: cantidad,
      Comentario: comentario,
      importe: importe,
      valordeclarado: 1,
      idOrigen: idOrigen,
      nventa: nventa,
      seguimiento: seguimiento,
    },
    url: "Procesos/php/AgregarVenta.php",
    type: "post",
    beforeSend: function () {
      //           document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        var table = $("#basic").DataTable();
        table.ajax.reload();

        var tabletotales = $("#basic-total").DataTable();
        tabletotales.ajax.reload();
      } else if (jsonData.success == "2") {
        $.NotificationApp.send(
          "Error",
          "No seleccionaste ningún cliente origen ",
          "bottom-right",
          "#FFFFFF",
          "error"
        );
      } else if (jsonData.success == "3") {
        $.NotificationApp.send(
          "Error",
          "No aplica seguro extra porque Valor Declarado esta establecido en $ 10.000",
          "bottom-right",
          "#FFFFFF",
          "error"
        );
      } else {
        $.NotificationApp.send(
          "Error",
          "No seleccionaste ningún servicio ",
          "bottom-right",
          "#FFFFFF",
          "error"
        );
      }
    },
  });
}

function todoslosrec() {
  var r = $("#retiro_t").val(); //SI ES 0 ES RETIRO Y ENTREGA SI ES 1 ES SOLO ENTREGA
  if (r == 0) {
    // COMO ES RETIRO SELECCIONO EL ID DEL ORIGEN
    if (document.getElementById("id_origen").value == null) {
      var id = document.getElementById("id_origen2").value;
    } else {
      var id = document.getElementById("id_origen").value;
    }
  } else if (r == 1) {
    if (document.getElementById("id_destino").value == null) {
      var id = document.getElementById("id_destino2").value;
    } else {
      var id = document.getElementById("id_destino").value;
    }
  }
  robot(id);
}

function cobroacuenta(a) {
  if ($('[name="my-checkbox"]').is(":checked")) {
    $("#cobroacuenta_input").prop("disabled", false);
    $("#cobroacuenta_button").prop("disabled", false);
  } else {
    $("#cobroacuenta_button").prop("disabled", true);
    $("#cobroacuenta_input").prop("disabled", true);
    $("#cobroacuenta_input").val("");
  }
}

function valordeclarado(a) {
  if ($('[name="my-checkbox2"]').is(":checked")) {
    $("#valordeclarado_input").prop("disabled", false);
    $("#valordeclarado_button").prop("disabled", false);
  } else {
    $("#valordeclarado_button").prop("disabled", true);
    $("#valordeclarado_input").prop("disabled", true);
    $("#valordeclarado_input").val("");
  }
}

function subir() {
  console.log("veo la tabla servicios");
  var dato = document.getElementById("codigo").value;
  if (dato == 0) {
    $.NotificationApp.send(
      "Error",
      "No seleccionaste ningún servicio ",
      "bottom-right",
      "#FFFFFF",
      "error"
    );
    document.getElementById("servicio").style.background = "red";
  } else {
    if (document.getElementById("id_origen").value == null) {
      var idOrigen = document.getElementById("id_origen2").value;
    } else {
      var idOrigen = document.getElementById("id_origen").value;
    }
    // var idOrigen=document.getElementById('id_origen').value;
    var cantidad = document.getElementById("cantidad").value;
    var comentario = document.getElementById("comentario").value;
    var importe = document.getElementById("precioventa").value;
    var nventa = document.getElementById("nventa").value;
    var seguimiento = document.getElementById("seguimiento").value;
    if (!idOrigen) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Debes seleccionar un origen",
        background: "#FFFFFF",
      });
      return;
    }
    $.ajax({
      data: {
        subir: 1,
        id: dato,
        cantidad: cantidad,
        Comentario: comentario,
        importe: importe,
        idOrigen: idOrigen,
        nventa: nventa,
        seguimiento: seguimiento,
      },
      url: "Procesos/php/AgregarVenta.php",
      type: "post",
      beforeSend: function () {
        //           document.getElementById("spinner").style.display="block";
      },
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == "1") {
          // var table = $("#basic").DataTable();
          // table.ajax.reload();
          // Para la tabla principal
          if ($.fn.DataTable.isDataTable("#basic")) {
            $("#basic").DataTable().ajax.reload();
          }
          if ($.fn.DataTable.isDataTable("#basic-total")) {
            $("#basic-total").DataTable().ajax.reload();
          }
          // var tabletotales = $("#basic-total").DataTable();
          // tabletotales.ajax.reload();
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "No seleccionaste ningún servicio",
            position: "bottom-right",
            background: "#FFFFFF",
          });
        }
      },
    });
  }
}

function oculto_origen(id) {
  // console.log('ver',id);
  //   var id=document.getElementById('id_origen').value;
  var dato = { id_origen: id };
  $.ajax({
    data: dato,
    url: "Procesos/php/funciones.php",
    type: "post",
    beforeSend: function () {
      document.getElementById("spinner").style.display = "block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        $("#origen_ok").css("display", "inline");
        if (jsonData.Direccion1 != "") {
          $("#origen_ok_icon").css("display", "inline");
        }

        document.getElementById("origen_ok").innerHTML =
          "Direccion: " + jsonData.Direccion;
        $("#spinner").css("display", "none");
        $("#retiro_t").val(jsonData.Retiro);

        //SELECT RECORRIDO
        robot(id);

        var table = $("#basic").DataTable();
        table.ajax.reload();

        var tabletotales = $("#basic-total").DataTable();
        tabletotales.ajax.reload();

        $("#nventa").html(jsonData.NumeroRepo);
        $("#seguimiento").html(jsonData.NumeroPedido);

        distancia();
      }
    },
  });
}

//SEGURO
function sure(id) {
  let cs = $("#seguimiento").html();
  $.ajax({
    data: { Seguro: 1, id_cliente: id, cs: cs },
    url: "Procesos/php/sure.php",
    type: "post",
    beforeSend: function () {},
    success: function (response) {
      var jsonData = JSON.parse(response);

      $("#valordeclarado_input").val(jsonData.Sure);
      var table = $("#basic").DataTable();
      table.ajax.reload();

      var tabletotales = $("#basic-total").DataTable();
      tabletotales.ajax.reload();

      // SI EL VALOR DECLARADO ES SUPERIOR A 10.000 (ESTO SACARLO PARA QUE EJECUTE TODOS LOS SEGUROS)
      // if(jsonData.Sure>10000){
      valor_declarado();
      // }
    },
  });
}
function robot(id) {
  if ($('[name="my-checkbox-recorrido"]').is(":checked")) {
    var todos = 1;
  } else {
    var todos = 0;
  }

  // console.log('robot',todos);

  $.ajax({
    data: { RobotRecorrido: 1, id: id, Todos: todos },
    type: "POST",
    url: "../Funciones/php/tablas.php",
    success: function (response) {
      $(".selector-recorrido select").html(response).fadeIn();
    },
  });
}

function oculto_destino(id) {
  var dato = { id_destino: id };
  $.ajax({
    data: dato,
    url: "Procesos/php/funciones.php",
    type: "post",
    //         beforeSend: function(){
    //         $("#buscando").html("Buscando...");
    //         },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        document.getElementById("destino_ok").style.display = "block";
        document.getElementById("destino_ok").innerHTML =
          "Direccion: " + jsonData.Direccion;
      }
    },
  });

  robot(id);
  distancia();
}

$("#retiro_t").change(function (e) {
  var r = e.target.value; //SI ES 0 ES RETIRO Y ENTREGA SI ES 1 ES SOLO ENTREGA
  if (r == 0) {
    // COMO ES RETIRO SELECCIONO EL ID DEL ORIGEN
    var id = document.getElementById("id_origen").value;
  } else if (r == 1) {
    var id = document.getElementById("id_destino").value;
  }
  robot(id);
});

function oculto_tercero(id) {
  alert(id);
  var dato = { id_tercero: id };
  $.ajax({
    data: dato,
    url: "Procesos/php/funciones.php",
    type: "post",
    //         beforeSend: function(){
    //         $("#buscando").html("Buscando...");
    //         },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        //            document.getElementById('tercero_ok').style.display="block";
        document.getElementById("tercero_ok").innerHTML =
          "Direccion: " + jsonData.Direccion;
      }
    },
  });
}

function formadepago(selec) {
  if (selec == "Origen") {
    sure(document.getElementById("id_origen").value);

    document.getElementById("pagaorigen_icon").style.display = "inline";
    document.getElementById("pagaorigen_label").style.color = "#08AB7C";
    document.getElementById("pagadestino_label").style.color = "gray";
    document.getElementById("pagadestino_icon").style.display = "none";
    document.getElementById("pagatercero_icon").style.display = "none";
    document.getElementById("row_fp_tercero").style.display = "none";
    $.NotificationApp.send(
      "Seleccion",
      "Seleccionaste que paga " + selec,
      "top-right",
      "#0ACF97",
      "info"
    );
  } else if (selec == "Destino") {
    sure(document.getElementById("id_destino").value);
    document.getElementById("pagaorigen_label").style.color = "gray";
    document.getElementById("pagaorigen_icon").style.display = "none";
    document.getElementById("pagadestino_icon").style.display = "inline";
    document.getElementById("pagadestino_label").style.color = "#08AB7C";
    document.getElementById("pagatercero_icon").style.display = "none";
    document.getElementById("row_fp_tercero").style.display = "none";
    $.NotificationApp.send(
      "Seleccion",
      "Seleccionaste que paga " + selec,
      "top-right",
      "#0ACF97",
      "info"
    );
  } else if (selec == "Tercero") {
    document.getElementById("row_fp_tercero").style.display = "inline";
    document.getElementById("pagaorigen_label").style.color = "gray";
    document.getElementById("pagaorigen_icon").style.display = "none";
    document.getElementById("pagadestino_label").style.color = "gray";
    document.getElementById("pagadestino_icon").style.display = "none";
    document.getElementById("pagatercero_icon").style.display = "inline";
    document.getElementById("pagatercero_label").style.color = "#08AB7C";
    $.NotificationApp.send(
      "Seleccion",
      "Seleccionaste que paga Otro Cliente",
      "top-right",
      "#0ACF97",
      "info"
    );
  } else if (selec == "") {
    document.getElementById("row_fp_tercero").style.display = "none";
    document.getElementById("pagaorigen_label").style.color = "gray";
    document.getElementById("pagaorigen_icon").style.display = "none";
    document.getElementById("pagadestino_icon").style.display = "none";
    document.getElementById("pagadestino_label").style.color = "gray";
  }
}

function calcularcantidad(total) {
  var nuevototal = document.getElementById("precioventa").value * total;
  document.getElementById("total").value = nuevototal;
}

function cargar(id) {
  var dato = { id_servicio: id };
  $.ajax({
    data: dato,
    url: "Procesos/php/funciones.php",
    type: "post",
    //         beforeSend: function(){
    //         $("#buscando").html("Buscando...");
    //         },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        document.getElementById("precioventa").value = jsonData.PrecioVenta;
        document.getElementById("codigo").value = jsonData.Codigo;
        var cantidad = document.getElementById("cantidad").value;
        document.getElementById("total").value =
          jsonData.PrecioVenta * cantidad;
        if (jsonData.Codigo != "") {
          $("#subir").prop("disabled", false);
        }
      }
    },
  });
}

$("#AgregarCliente").click(function () {
  var nombre = document.getElementById("nombrecliente_nc").value;
  var email = document.getElementById("email_nc").value;
  var direccion = document.getElementById("direccion_nc").value;
  var telefono = document.getElementById("telefono_nc").value;
  var celular = document.getElementById("celular_nc").value;
  var relacion = document.getElementById("relacion_nc").value;
  var cp = document.getElementById("cp_nc").value;
  var ciudad = document.getElementById("ciudad_nc").value;
  var observaciones = document.getElementById("observaciones_nc").value;
  var calle = document.getElementById("Calle_nc").value;
  var numero = document.getElementById("Numero_nc").value;
  var barrio = document.getElementById("Barrio_nc").value;

  var dato = {
    nombrecliente: nombre,
    Direccion: direccion,
    Telefono: telefono,
    Celular: celular,
    Mail: email,
    Relacion: relacion,
    CodigoPostal: cp,
    Ciudad: ciudad,
    Calle: calle,
    Numero: numero,
    Barrio: barrio,
    Observaciones: observaciones,
  };

  $.ajax({
    data: dato,
    url: "Procesos/php/crearcliente.php",
    type: "post",
    beforeSend: function () {
      // $("#buscando").html("Buscando...");
      // alert('enviando...');
    },
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      if (jsonData.success == "1") {
        var NombreCliente = jsonData.NombreCliente;
        var id = jsonData.id;
        var necesitosaber = document.getElementById("valorcrear").value;
        if (necesitosaber == "Origen") {
          $("#bs-example-modal-lg").modal("hide");
          document.getElementById("nuevoclienteorigen").style.display = "none";
          document.getElementById("nuevoclienteorigen2").style.display =
            "block";
          $("#id_origen2_label").val(NombreCliente);
          $("#id_origen2_label").prop("disabled", true);
          $("#id_origen2").val(id);
          document.getElementById("origen2_ok").style.display = "block";
          document.getElementById("origen2_ok").innerHTML =
            "Direccion: " + jsonData.Direccion;
          oculto_origen(id);
          if (document.getElementById("retiro_t").value == "0") {
            robot(id);
          }
        } else {
          $("#bs-example-modal-lg").modal("hide");
          document.getElementById("nuevoclientedestino").style.display = "none";
          document.getElementById("nuevoclientedestino2").style.display =
            "block";
          $("#id_destino2_label").val(NombreCliente);
          $("#id_destino2_label").prop("disabled", true);
          $("id_destino2").val(id);
          document.getElementById("destino2_ok").style.display = "block";
          document.getElementById("destino2_ok").innerHTML =
            "Direccion: " + jsonData.Direccion;
          oculto_destino(id);
          if (document.getElementById("retiro_t").value == "0") {
            robot(id);
          }
        }
      } else {
        alert("Error en el ingreso!");
      }
    },
  });
});

$("#confirmarenvio").click(function () {
  // $.ajax({
  //     data: {'SolicitaEnvio':'confirmarenvio'},
  //     url:'Procesos/php/ConfirmarVenta.php',
  //     type:'post',
  //     beforeSend: function(){
  //   // $("#buscando").html("Buscando...");
  //   // alert('enviando...');
  //     },
  //     success: function (respuesta){
  //             var jsonData = JSON.parse(respuesta);
  //             if (jsonData.success == "1")
  //             {
  //             }
  //         }
  //     });
});
