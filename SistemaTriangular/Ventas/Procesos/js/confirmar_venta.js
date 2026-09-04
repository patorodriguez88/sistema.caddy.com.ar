document
  .getElementById("VentaSimple")
  .addEventListener("submit", function (event) {
    event.preventDefault();
    console.log("Submit prevenido y datos siendo enviados por AJAX");

    const data = {
      SolicitaEnvio: 1,
      retiro_t: document.getElementById("retiro_t").value,
      recorrido_t: document.getElementById("recorrido_t").value,
      cobroacuenta_input: document.getElementById("cobroacuenta_input").value,
      valordeclarado_input: document.getElementById("valordeclarado_input")
        .value,
      km_nc: document.getElementById("km_nc").value,
      google_km: document.getElementById("google_km").value,
      google_time: document.getElementById("google_time").value,
      redespacho_nc: document.getElementById("redespacho_nc").value,
      formadepago_t: document.getElementById("formadepago_t").value,
      entregaen_t: document.getElementById("entregaen_t").value,
      horario_entrega_t: document.getElementById("horario_entrega_t").value,
      codigocliente: document.getElementById("codigocliente").value,
      observaciones: document.getElementById("observaciones").value,
      cobranzadelenvio_t: document.getElementById("cobranzadelenvio_t").value,
      codigo_seguimiento: document.getElementById("seguimiento").textContent,
      cliente_origen: document.getElementById("id_origen").value,
      cliente_destino: document.getElementById("id_destino").value,
    };

    console.log("Datos enviados:", data);

    $.ajax({
      data: data,
      url: "Procesos/php/ConfirmarVenta.php",
      type: "post",
      dataType: "json",
      beforeSend: function () {
        console.log("Enviando datos...");
      },
      success: function (jsonData) {
        console.log("Respuesta de PHP:", jsonData); // Esto ayudará a ver exactamente qué se recibe
        try {
          if (jsonData.success == 1) {
            console.log("Si, está success 1");
            $("#success-alert-modal").modal("show");
            // $('#success-alert-modal-text').html('La venta se agrego con exito con el código '+jsonData.data);
            var segundos = 3;
            var intervalo = setInterval(function () {
              $("#success-alert-modal-text").html(
                "La venta se agrego con exito con el código " +
                  jsonData.data +
                  "<br>Cerrando en " +
                  segundos +
                  " segundos...",
              );
              segundos--;
              if (segundos == 0) {
                clearInterval(intervalo);
                location.reload();
              }
            }, 1000);
          } else if (jsonData.error) {
            // ConfirmarVenta.php no siempre manda error:1 - varias validaciones
            // (falta codigo de seguimiento, error al obtener datos del cliente,
            // fallo al insertar en HojaDeRuta) mandan error como STRING con el
            // motivo. Con "== 1" esos casos no matcheaban ni acá ni en el "if"
            // de arriba: no pasaba nada, sin avisar por qué no se confirmó.
            var motivo =
              jsonData.message ||
              (typeof jsonData.error === "string" ? jsonData.error : "No se pudo confirmar la venta.");
            $("#warning-redespacho-modal").modal("show");
            $("#warning-redespacho-text").html(motivo);
          } else {
            console.warn("Respuesta inesperada de ConfirmarVenta.php:", jsonData);
          }
        } catch (e) {
          console.error("Error al procesar la respuesta:", e, "Respuesta recibida:", jsonData);
        }
      },
      error: function (xhr) {
        console.error("Error de red/parseo en ConfirmarVenta.php:", xhr.status, xhr.responseText);
        $("#warning-redespacho-modal").modal("show");
        $("#warning-redespacho-text").html(
          "No se pudo confirmar la venta (error de servidor). Probá de nuevo o avisá a sistemas.",
        );
      },
    });
  });
