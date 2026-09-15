// Pantalla "Etiquetas por Recorrido" — para el operador de WePoint: elegir
// un recorrido, ver todos sus paquetes, corregir la cantidad real de bultos
// si hace falta, e imprimir (todas juntas o una por una) el Rótulo chico o
// la Etiqueta grande, por la misma impresora Zebra que ya usa CrossDocking.

(function () {
    var $recTabla = $("#er_rec_tabla tbody");
    var $paqSeccion = $("#er_paq_seccion");
    var $paqTabla = $("#er_paq_tabla tbody");
    var $paqTitulo = $("#er_paq_titulo");
    var $tipoEtiqueta = $("#er_tipo_etiqueta");

    var recorridoActual = null;
    var paquetesActuales = [];

    // ------------------------------------------------------------------
    // Impresora Zebra (BrowserPrint) — mismo mecanismo que CrossDocking
    // (Logistica/Proceso/js/crossdocking.js): corre un servicio local en la
    // PC del operador. Copiado tal cual de ahí (conectar/enviar/reintentos)
    // para que se comporte exactamente igual, ya probado en depósito.
    // ------------------------------------------------------------------
    var $printerEstado = $("#er_printer_estado");
    var $printerEstadoTxt = $("#er_printer_estado_txt");
    var selected_device = null;

    function setEstadoPrinter(estado, texto) {
        $printerEstado.removeClass("buscando ok error").addClass(estado);
        $printerEstadoTxt.text(texto);
    }

    function conectarImpresora(silencioso, intentosRestantes) {
        if (typeof intentosRestantes !== "number") intentosRestantes = 2;
        if (typeof BrowserPrint === "undefined") {
            setEstadoPrinter("error", "Impresora: SDK no cargó");
            return;
        }
        if (!silencioso) setEstadoPrinter("buscando", "Impresora: buscando…");
        BrowserPrint.getDefaultDevice(
            "printer",
            function (device) {
                selected_device = device;
                setEstadoPrinter("ok", "Impresora: " + device.name);
            },
            function () {
                selected_device = null;
                if (intentosRestantes > 0) {
                    setTimeout(function () {
                        conectarImpresora(true, intentosRestantes - 1);
                    }, 1500);
                    return;
                }
                setEstadoPrinter("error", "Impresora: no detectada — ¿está abierto Zebra Browser Print en esta PC?");
            }
        );
    }

    $("#er_printer_reintentar").on("click", function () {
        conectarImpresora(false, 2);
    });

    function enviarZPL(zpl, reintentando, onOk, onErr) {
        function enviarConDispositivo(device) {
            try {
                device.send(zpl, function () {
                    setEstadoPrinter("ok", "Impresora: " + device.name);
                    if (onOk) onOk();
                }, function (err) {
                    console.error("Error imprimiendo:", err);
                    if (!reintentando) {
                        setEstadoPrinter("buscando", "Impresora: reintentando…");
                        setTimeout(function () {
                            selected_device = null;
                            BrowserPrint.getDefaultDevice(
                                "printer",
                                function (dev) {
                                    selected_device = dev;
                                    enviarZPL(zpl, true, onOk, onErr);
                                },
                                function () {
                                    setEstadoPrinter("error", "Impresora: no se pudo imprimir (reintenté y no conectó)");
                                    if (onErr) onErr();
                                }
                            );
                        }, 800);
                        return;
                    }
                    setEstadoPrinter("error", "Impresora: no se pudo imprimir");
                    if (onErr) onErr();
                });
            } catch (e) {
                console.error("Excepción imprimiendo:", e);
                setEstadoPrinter("error", "Impresora: error al imprimir");
                if (onErr) onErr();
            }
        }

        if (!selected_device) {
            if (!reintentando && typeof BrowserPrint !== "undefined") {
                BrowserPrint.getDefaultDevice(
                    "printer",
                    function (dev) {
                        selected_device = dev;
                        enviarZPL(zpl, true, onOk, onErr);
                    },
                    function () {
                        setEstadoPrinter("error", "Impresora: no conectada (no se imprimió)");
                        if (onErr) onErr();
                    }
                );
                return;
            }
            setEstadoPrinter("error", "Impresora: no conectada (no se imprimió)");
            if (onErr) onErr();
            return;
        }
        enviarConDispositivo(selected_device);
    }

    function zplLimpio(s) {
        return String(s || "")
            .replace(/[áàä]/gi, "a").replace(/[éèë]/gi, "e").replace(/[íìï]/gi, "i")
            .replace(/[óòö]/gi, "o").replace(/[úùü]/gi, "u").replace(/ñ/gi, "n")
            .replace(/[\^~]/g, "");
    }

    // Logo real — copiado tal cual de crossdocking.js / Funciones/js/seguimiento.js
    // (mismo bitmap en toda la app, no es un logo aparte para esta pantalla).
    var CADDY_LOGO_ZPL =
        "^FO30,15^GFA,1675,1675,25,,:::::::::::::M0CJ04J01,L07F8003FCI0FE,L0FFC007FE003FF,K01FFE00IF007FF8,K03FFE01IF007FFC,K03IF01IF80IFC,K03IF81IFC0IFE,K07IF83IFC0IFE,K07IFC1IFE0IFE,K03IFE1JF0F01E,K03IFE1JF0E01C,K03JF1JF8703C,K01JF0JF87C78,L0JF87IFC3FF,L0JFC7IFE0FE,L07IFC3IFE01V07,L07IFE3JFX0F,L03IFE1JFX0F,L01JF0JF8W07J0F,L01JF8JFCgH0F,M0JF87IFCgH0F,M0JFC7IFEI0E3C38FC3FE07F8F73F3FE,M07IFC3IFEI0E3E79FF3FF0FFCF7FFBFF,M03IFE1JFI0E3E7BFF3FF9FFEF7FFBFE,M03IFE1JFI0F7E77C73C79E1EF7C78F,M01IFE0JFI0F7E7787BC3DE1E77878F,N0IFE07IFI077FF7FFBC3DC0FF7838F,N0IFE07IFI07F7E7FFBC3DC0FF7838F,N07FFE03IFI07E7E7803C3DE1EF7838F,N07FFE03IFI03E7E7C13C79E1EF7838F,N03FFC01FFEI03E3C3FF3FF9FFEF7878FE,N01FF800FFCI03E3C1FFBFF0FFCF78787F,O0FFI07F8I01C3C0FF3FE07F0778383F,U08Q03C,gM03C,:::,::::::::::::::^FS";

    function fechaTexto() {
        var h = new Date();
        return String(h.getDate()).padStart(2, "0") + "/" + String(h.getMonth() + 1).padStart(2, "0") + "/" + h.getFullYear();
    }

    // ------------------------------------------------------------------
    // RÓTULO (chico) — 6,5x3,2cm @203dpi = 520x256pt. Mismo tamaño/layout
    // que ya usan CrossDocking y el panel de Seguimiento.
    // ------------------------------------------------------------------
    function construirZplRotulo(d, bultoActual, bultoTotal) {
        return (
            "^XA^PW520^LL256^LH0,0^CI28" +
            CADDY_LOGO_ZPL +
            "^FO215,10^A0N,20,20^FD" + zplLimpio((d.ClienteDestino || "-").substring(0, 26)) + "^FS" +
            "^FO215,35^A0N,18,18^FD" + zplLimpio((d.DomicilioDestino || "").substring(0, 30)) + "^FS" +
            "^FO215,57^A0N,18,18^FDId: " + d.CodigoSeguimiento + "^FS" +
            "^FO215,79^A0N,18,18^FDOrigen: " + zplLimpio((d.OrigenNombre || "").substring(0, 26)) + "^FS" +
            "^FO215,101^A0N,18,18^FDBulto: " + bultoActual + "/" + bultoTotal + "^FS" +
            "^FO215,123^A0N,18,18^FDFecha: " + fechaTexto() + "^FS" +
            "^FO215,148^A0N,30,30^FDRec: " + (d.Recorrido || "-") + "^FS" +
            "^FO45,74^BQN,2,7^FDQA," + d.CodigoSeguimiento + "^FS" +
            "^XZ"
        );
    }

    // ------------------------------------------------------------------
    // ETIQUETA (grande) — 10x10cm @203dpi = 800x800pt (etiqueta CUADRADA,
    // no 10x15 como el PDF de Rotulospdf.php - el papel físico disponible
    // es 10x10). Mismos datos que Rotulospdf.php pero acomodados en un
    // layout más compacto: logo+origen arriba, bulto X/Y al lado, código
    // grande, QR + datos de destino a su lado (en vez de uno debajo del
    // otro, para no gastar alto), observaciones si entran, pie chico.
    // OJO: nunca salió de una impresora física todavía - es muy probable
    // que haga falta un ajuste de posiciones/tamaños después de la
    // primera prueba real (mismo criterio que ya pasó con el rótulo chico
    // de CrossDocking).
    // ------------------------------------------------------------------
    function construirZplEtiqueta(d, nroBulto, totalBultos) {
        var origen = zplLimpio((d.OrigenNombre || "") + (d.idProveedor ? "  #" + d.idProveedor : ""));
        var origenDireccion = zplLimpio(d.OrigenDireccion || "");
        var cliente = zplLimpio(d.ClienteDestino || "");
        var domicilio = zplLimpio(d.DomicilioDestino || "");
        var localidad = zplLimpio(d.LocalidadDestino || "");
        var provincia = zplLimpio(d.ProvinciaDestino || "");
        var cp = zplLimpio(d.cpdestino || "");
        var observaciones = zplLimpio(d.Observaciones || "");
        // Posición en el recorrido: Entrega usa Posicion, Retiro usa
        // Posicion_retiro (colas independientes, mismo criterio que en
        // Proceso/js/pendientes.js).
        var posicion = (d.Retirado == 1 ? d.Posicion : d.Posicion_retiro) || "-";

        return (
            "^XA^PW800^LL800^CI28" +
            // Margen arriba (a pedido: "separá más de arriba, dejá
            // margen") - todo el bloque de encabezado arranca más abajo
            // que antes (35 en vez de 15).
            // Logo (mismo bitmap de siempre) + origen a su derecha + bulto
            // X/Y arriba a la derecha, todo en la misma franja superior.
            CADDY_LOGO_ZPL +
            "^FO230,35^A0N,30,30^FB400,1,0,L,0^FD" + origen + "^FS" +
            // FIX (a pedido: "la letra chiquita no se ve nada"): se agranda
            // todo lo que había quedado chico (dirección de origen,
            // domicilio/localidad/provincia, observaciones y el pie),
            // recalculando los espacios para que no se pisen.
            "^FO230,72^A0N,23,23^FB400,1,0,L,0^FD" + origenDireccion + "^FS" +
            "^FO640,38^A0N,38,38^FD" + nroBulto + "/" + totalBultos + "^FS" +
            "^FO30,140^GB740,3,3^FS" +
            // Código grande, centrado
            "^FO30,155^A0N,42,42^FB740,1,0,C,0^FD" + d.CodigoSeguimiento + "^FS" +
            "^FO30,210^GB740,3,3^FS" +
            // QR a la izquierda, datos de destino a la derecha (uno al
            // lado del otro para no gastar alto de más). Posición del
            // recorrido junto al Recorrido (a pedido: "agregá la posición,
            // no se ve en ningún lado").
            "^FO40,225^BQN,2,6^FDQA," + d.CodigoSeguimiento + "^FS" +
            "^FO230,225^A0N,32,32^FB520,1,0,L,0^FD" + cliente + "^FS" +
            "^FO230,264^A0N,25,25^FB520,2,0,L,0^FD" + domicilio + "^FS" +
            "^FO230,330^A0N,25,25^FB520,1,0,L,0^FD" + localidad + (cp ? " (" + cp + ")" : "") + "^FS" +
            "^FO230,362^A0N,25,25^FB520,1,0,L,0^FDProv: " + provincia + "^FS" +
            "^FO230,394^A0N,28,28^FDRec: " + (d.Recorrido || "-") + "   Pos: " + posicion + "^FS" +
            "^FO30,440^GB740,3,3^FS" +
            (observaciones ? "^FO30,455^A0N,25,25^FB740,3,0,L,0^FDREF: " + observaciones + "^FS" : "") +
            // Pie, abajo del todo
            "^FO30,775^A0N,20,20^FB740,1,0,R,0^FDUsuario: " + zplLimpio(d.Usuario || "") + " | Fecha: " + fechaTexto() + "^FS" +
            "^XZ"
        );
    }

    function tipoEtiquetaSeleccionado() {
        return $tipoEtiqueta.val(); // "rotulo" | "etiqueta"
    }

    function construirZpl(d, nroBulto, totalBultos) {
        return tipoEtiquetaSeleccionado() === "etiqueta"
            ? construirZplEtiqueta(d, nroBulto, totalBultos)
            : construirZplRotulo(d, nroBulto, totalBultos);
    }

    // Imprime TODOS los bultos de un paquete (expande por Cantidad, igual
    // que ya hace Funciones/js/seguimiento.js con el rótulo individual).
    function imprimirPaquete(d, onFin) {
        var total = Math.max(1, parseInt(d.Cantidad, 10) || 1);
        var pendientes = total;
        for (var i = 1; i <= total; i++) {
            enviarZPL(construirZpl(d, i, total), false, function () {
                pendientes--;
                if (pendientes === 0 && onFin) onFin(true);
            }, function () {
                pendientes--;
                if (pendientes === 0 && onFin) onFin(false);
            });
        }
    }

    // Imprime TODOS los paquetes del recorrido actual, uno atrás del otro
    // (no todos a la vez: son potencialmente muchos bultos, y mandarlos
    // todos juntos satura la cola de la impresora).
    function imprimirTodoElRecorrido() {
        if (!paquetesActuales.length) {
            if (window.toast) toast("error", "Etiquetas", "No hay paquetes para imprimir en este recorrido.");
            return;
        }
        var idx = 0;
        function siguiente() {
            if (idx >= paquetesActuales.length) {
                if (window.toast) toast("success", "Listo", "Se mandaron a imprimir los " + paquetesActuales.length + " paquetes del recorrido.");
                return;
            }
            var d = paquetesActuales[idx];
            idx++;
            imprimirPaquete(d, function () {
                siguiente();
            });
        }
        siguiente();
    }

    // ------------------------------------------------------------------
    // RECORRIDOS
    // ------------------------------------------------------------------
    function cargarRecorridos() {
        $recTabla.html('<tr><td colspan="5" class="text-center text-muted py-4">Cargando…</td></tr>');
        $.ajax({
            url: "Proceso/php/etiquetas_recorrido.php",
            type: "POST",
            data: { Recorridos: 1 },
            success: function (response) {
                var jsonData = typeof response === "string" ? JSON.parse(response) : response;
                renderRecorridos(jsonData.data || []);
            },
            error: function () {
                $recTabla.html('<tr><td colspan="5" class="text-center text-danger py-4">No se pudieron cargar los recorridos.</td></tr>');
            },
        });
    }

    function renderRecorridos(rows) {
        if (!rows.length) {
            $recTabla.html('<tr><td colspan="5" class="text-center text-muted py-4">No hay recorridos con paquetes pendientes.</td></tr>');
            return;
        }
        var html = "";
        rows.forEach(function (r) {
            var color = (r.Color || "").replace("#", "") || "E24F30";
            html +=
                '<tr class="er-rec-row" data-recorrido="' + r.Recorrido + '" style="cursor:pointer">' +
                '<td><span class="er-rec-dot" style="background:#' + color + '"></span> <b>' + r.Recorrido + "</b></td>" +
                "<td>" + (r.Nombre || "-") + "</td>" +
                '<td class="text-center">' + r.Paquetes + "</td>" +
                '<td class="text-center">' + r.Bultos + "</td>" +
                '<td class="text-end"><button type="button" class="btn btn-sm btn-success er-btn-abrir">Ver paquetes</button> ' +
                '<button type="button" class="btn btn-sm btn-outline-light er-btn-imprimir-todo">Imprimir todas</button></td>' +
                "</tr>";
        });
        $recTabla.html(html);
    }

    $recTabla.on("click", ".er-rec-row .er-btn-abrir, .er-rec-row", function (e) {
        // El botón "Imprimir todas" también está dentro de la fila - no abrir
        // el detalle si lo que tocaron fue ese botón.
        if ($(e.target).hasClass("er-btn-imprimir-todo")) return;
        var recorrido = $(this).closest(".er-rec-row").data("recorrido");
        abrirRecorrido(recorrido);
    });

    $recTabla.on("click", ".er-btn-imprimir-todo", function (e) {
        e.stopPropagation();
        var recorrido = $(this).closest(".er-rec-row").data("recorrido");
        cargarPaquetes(recorrido, function () {
            imprimirTodoElRecorrido();
        });
    });

    // ------------------------------------------------------------------
    // PAQUETES de un recorrido
    // ------------------------------------------------------------------
    function abrirRecorrido(recorrido) {
        recorridoActual = recorrido;
        $paqSeccion.show();
        $paqTitulo.text("Paquetes del recorrido " + recorrido);
        cargarPaquetes(recorrido);
        $("html, body").animate({ scrollTop: $paqSeccion.offset().top - 20 }, 300);
    }

    function cargarPaquetes(recorrido, onListo) {
        $paqTabla.html('<tr><td colspan="6" class="text-center text-muted py-4">Cargando…</td></tr>');
        $.ajax({
            url: "Proceso/php/etiquetas_recorrido.php",
            type: "POST",
            data: { Paquetes: 1, Recorrido: recorrido },
            success: function (response) {
                var jsonData = typeof response === "string" ? JSON.parse(response) : response;
                paquetesActuales = jsonData.data || [];
                renderPaquetes(paquetesActuales);
                if (onListo) onListo();
            },
            error: function () {
                $paqTabla.html('<tr><td colspan="6" class="text-center text-danger py-4">No se pudieron cargar los paquetes.</td></tr>');
            },
        });
    }

    function renderPaquetes(rows) {
        if (!rows.length) {
            $paqTabla.html('<tr><td colspan="6" class="text-center text-muted py-4">Este recorrido no tiene paquetes pendientes.</td></tr>');
            return;
        }
        var html = "";
        rows.forEach(function (d) {
            html +=
                '<tr data-id="' + d.id + '">' +
                "<td>" + d.CodigoSeguimiento + "</td>" +
                "<td>" + (d.ClienteDestino || "-") + "</td>" +
                "<td>" + (d.DomicilioDestino || "-") + "</td>" +
                "<td>" + (d.LocalidadDestino || "-") + "</td>" +
                '<td><input type="number" min="1" class="form-control form-control-sm er-cantidad-input" style="width:80px" value="' + d.Cantidad + '"></td>' +
                '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-light er-btn-imprimir-individual">Imprimir</button></td>' +
                "</tr>";
        });
        $paqTabla.html(html);
    }

    // Cantidad: se guarda al perder foco (blur) o con Enter, no en cada
    // tecla — evita mandar un UPDATE por cada dígito tipeado.
    function guardarCantidad($input) {
        var $fila = $input.closest("tr");
        var id = $fila.data("id");
        var cantidad = parseInt($input.val(), 10);

        if (!cantidad || cantidad < 1) {
            if (window.toast) toast("error", "Cantidad inválida", "Tiene que ser un número mayor a 0.");
            var item = paquetesActuales.find(function (p) { return p.id == id; });
            $input.val(item ? item.Cantidad : 1);
            return;
        }

        $.ajax({
            url: "Proceso/php/etiquetas_recorrido.php",
            type: "POST",
            data: { ModificarCantidad: 1, id: id, Cantidad: cantidad },
            success: function (response) {
                var jsonData = typeof response === "string" ? JSON.parse(response) : response;
                if (jsonData.success == 1) {
                    var item = paquetesActuales.find(function (p) { return p.id == id; });
                    if (item) item.Cantidad = cantidad;
                    if (window.toast) toast("success", "Listo", "Cantidad actualizada.");
                } else {
                    if (window.toast) toast("error", "Error", jsonData.error || "No se pudo actualizar la cantidad.");
                }
            },
            error: function () {
                if (window.toast) toast("error", "Error del servidor", "No se pudo actualizar la cantidad. Reintentá de nuevo.");
            },
        });
    }

    $paqTabla.on("blur", ".er-cantidad-input", function () {
        guardarCantidad($(this));
    });
    $paqTabla.on("keydown", ".er-cantidad-input", function (e) {
        if (e.key === "Enter") {
            $(this).blur();
        }
    });

    $paqTabla.on("click", ".er-btn-imprimir-individual", function () {
        var id = $(this).closest("tr").data("id");
        var d = paquetesActuales.find(function (p) { return p.id == id; });
        if (!d) return;
        imprimirPaquete(d, function (ok) {
            if (window.toast) toast(ok ? "success" : "error", ok ? "Listo" : "Error", ok ? "Etiqueta enviada a imprimir." : "No se pudo imprimir.");
        });
    });

    // ------------------------------------------------------------------
    // INIT
    // ------------------------------------------------------------------
    $(document).ready(function () {
        conectarImpresora(false, 2);
        cargarRecorridos();
    });

    $("#er_actualizar").on("click", function () {
        cargarRecorridos();
        if (recorridoActual) cargarPaquetes(recorridoActual);
    });
})();
