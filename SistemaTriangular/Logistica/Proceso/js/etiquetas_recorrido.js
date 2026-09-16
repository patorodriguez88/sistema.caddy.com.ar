// Pantalla "Etiquetas por Recorrido" — para el operador de WePoint: elegir
// un recorrido, ver todos sus paquetes, corregir la cantidad real de bultos
// si hace falta, e imprimir (todas juntas o una por una) el Rótulo chico o
// la Etiqueta grande, por la misma impresora Zebra que ya usa CrossDocking.

(function () {
    var $recTabla = $("#er_rec_tabla tbody");
    var $paqModal = $("#er_paq_modal");
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

    // Logo real de Caddy para la ETIQUETA grande (distinto del CADDY_LOGO_ZPL
    // de arriba, que resultó ser el logo de WEPOINT, no el de Caddy - se vio
    // en una impresión real, 2026-09-15). Generado a partir de
    // SistemaTriangular/images/LogoCaddy.png (190x77px, 1 bit, umbral 200),
    // sin el ^FO adentro (a diferencia del de arriba) para poder ubicarlo a
    // mano en cada layout. Uso exclusivo de la Etiqueta grande por ahora -
    // el rótulo chico / CrossDocking / panel de Seguimiento siguen con el
    // bitmap viejo hasta que se confirme si hace falta cambiarlo también ahí.
    var CADDY_LOGO_ZPL_ETIQUETA =
        "^GFA,1848,1848,24,0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000001F8000F800000000000000000000000000000000000000007FE003FE0000000000000000000000000000000000000000FFF80FFF8000000000000000000000000000000000000001FFF80FFFC000000000000000000000000000000000000001F0FC1F07C000000000000000000000000000000000000003E03E3E03E000000000000000000000000000000000000003C01E3C01E000000000000000000000000000000000000003C01FFC01E000000000000000000000000000000000000003C01FFC01E000000000000000000000000000000000000003C07FFF01E000000000000000000000000000000000000003C1FFFFC1E000000000000000000000000000000000000003E3F80FE3E000000000000000000000000000000000000001FFE007FFC000000000000000000000000000000000000001FFC001FFC0000000000000000000003000000C0000000000FF00007F80000000000000000000007800001F0000000001FC00003FC000000000000000000000FC00003F0000000007F800000FF000000000000000000000FC00003F000000000FE0000003F800000000000000000000FC00003F000000003F80000001FE00000000000000000000FC00003F00000000FF000000007F80000000000000000000FC00003F00000001FC000000001FC0000000000000000000FC00003F00000003F8000000000FE0000000000000000000FC00003F00000007E00000000003F0000000000000000000FC00003F00000007C00000000001F00007E0000FE1C001F8FC007E3F1C00030F800000000000F8003FFC003FFBE00FFEFC03FFFF3E0007CF000000000000F8007FFE00FFFFF01FFFFC07FFFF3F000FCF0000000000007800FFFF81FFFFF07FFFFC0FFFFF3F000FCF0000000001807801FFFF83FFFFF0FFFFFC1FFFFF3F801FCF0078000007C07803FFFFC7FFFFF0FFFFFC3FFFFF3F801F8F00FE01C01FC07807FC3FE7F83FF1FF0FFC7FC3FF1FC03F8F007F0FF87FC07807F00FCFF00FF1FC03FC7F00FF1FC03F0F007FDFFEFF80780FE007CFE007F3F801FCFE007F0FE07F0F001FFFFFFE00780FC0038FC007F3F001FCFE003F0FE07E0F0007FE3FF800780FC0001FC003F3F000FCFC003F07F0FE0F0003FE1FF000780FC0001F8003F3F000FCFC003F03F0FC0F0000FFFFC000780FC0001F8003F3F000FCFC003F03F9FC0F00003FFF0000780FC0001F8003F3F000FCFC003F01F9F80F00001FFC0000780FC0001FC007F3F000FCFC003F01FFF80F000007F80000780FE0038FC007F3F001FCFE003F00FFF00F000003E00000780FE007CFE00FF3F801FC7E007F00FFF00F000001E000007807F80FCFF01FF1FC07FC7F80FF007FE00F000001E000007807FE7FC7FE7FF1FF9FFC7FE7FF007FE00F000001E000007803FFFFC3FFFFF0FFFFFC3FFFFF003FC00F000001E000007801FFFF83FFFFF07FFFFC1FFFFF003FC00F000001E000007800FFFF01FFFFF03FFFFC0FFFFF003F800F000001E0000078007FFE007FFFF01FFFFC07FFFF003F800F000001E0000078001FF8003FF9E007FE7801FF9F007F000F00001FFE0000780003C0000380C000F030003C0C007F000F00003FFF0000780000000000000000000000000000FE000F00007FFF8000780000000000000000000000000000FE000F00003FFF0000780000000000000000000040000001FC000F00001FFF00007800530002218CA700008648C18001FC000F00000E3C0000F8007799B77BDEFF1223CF5DEBCC03F8000F80000F3C0000F000269DF65E5A4E0C227A58CA1C03F00007C0000FFC0001F00027BDF3765E6E0C227B4ECA1E01F00007E00007F80003E00024B7B743D26F0C3BCF5ECBD201E00003F80007F0000FE00000000201800700198608018000000001FC0001E0003FC000000000000000000000000000000000007F000000007F0000000000000000000000000000000000003FC0000001FE0000000000000000000000000000000000000FE0000007F800000000000000000000000000000000000003F800000FE000000000000000000000000000000000000001FE00003FC0000000000000000000000000000000000000007F0000FF00000000000000000000000000000000000000001FC001FC00000000000000000000000000000000000000000FF007F8000000000000000000000000000000000000000003F81FE0000000000000000000000000000000000000000000FFFF800000000000000000000000000000000000000000007FFF000000000000000000000000000000000000000000001FFC0000000000000000000000000000000000000000000007F0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000";

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
            // FIX (a pedido: "se ve mal, poca resolución" - foto real de la
            // etiqueta grande, 2026-09-16, mismo problema esperable acá): el
            // código nunca fijaba oscuridad/velocidad de impresión, así que
            // cada trabajo salía con lo que haya quedado seteado en la
            // impresora por el último trabajo (persiste entre impresiones
            // hasta que algo lo vuelva a cambiar). ^MD suma oscuridad sobre
            // lo que esté configurado (no lo reemplaza, así que no debería
            // "quemar" la etiqueta) y ^PR2 imprime más despacio para que
            // texto/gráficos chicos no salgan con puntos faltantes. Si con
            // esto sigue viéndose mal o se pasa de oscuro, avisar para
            // subir/bajar el 10.
            "^XA^PW520^LL256^LH0,0^CI28^MD10^PR2" +
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
    // Rediseñada a partir de una foto real de la etiqueta impresa
    // (IGALFER #582423, recorrido 3, posición 5, 2026-09-15). El usuario
    // marcó 4 problemas puntuales sobre esa impresión real:
    //   1) el logo era el de WEPOINT, no el de Caddy -> CADDY_LOGO_ZPL_ETIQUETA
    //   2) la línea divisoria del cuadro de abajo bajaba entera y cortaba el
    //      Código de Proveedor -> ahora solo cubre la fila Recorrido/Posición
    //   3) la dirección de origen (IGALFER) se veía pisada/amontonada en 1
    //      renglón -> ahora 2 renglones, letra más chica para que entre
    //   4) el REF se veía chico -> letra más grande, sigue en 2 renglones
    // más un pedido posterior: agrandar el contador de bulto (1/1).
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
            // FIX (a pedido, foto real 2026-09-16: "se ve mal, poca
            // resolución... no es la impresora porque mandé una prueba y se
            // ve bien"): el código nunca fijaba oscuridad/velocidad, así que
            // el trabajo salía con lo que haya quedado configurado en la
            // impresora por el último trabajo enviado (eso persiste entre
            // impresiones). ^MD suma oscuridad sobre lo ya configurado (no
            // lo reemplaza) y ^PR2 imprime más despacio para que texto y
            // gráficos chicos no salgan con puntos faltantes/grano. Es un
            // punto de partida - si sigue viéndose mal o se pasa de oscuro,
            // avisar para subir/bajar el 10.
            "^XA^PW800^LL800^CI28^MD10^PR2" +
            // Encabezado: logo real de Caddy (FIX 1) + origen a la derecha,
            // dirección de origen ahora en 2 renglones más chicos (FIX 3)
            // para que no se amontone, y el contador de bulto agrandado.
            "^FO25,20" + CADDY_LOGO_ZPL_ETIQUETA + "^FS" +
            "^FO230,20^A0N,24,24^FB370,1,0,L,0^FD" + origen + "^FS" +
            "^FO230,46^A0N,16,16^FB370,2,0,L,0^FD" + origenDireccion + "^FS" +
            "^FO610,24^A0N,48,48^FB180,1,0,R,0^FD" + nroBulto + "/" + totalBultos + "^FS" +
            "^FO30,104^GB740,3,3^FS" +
            // Código grande, centrado
            "^FO30,116^A0N,40,40^FB740,1,0,C,0^FD" + d.CodigoSeguimiento + "^FS" +
            "^FO30,172^GB740,3,3^FS" +
            // QR a la izquierda (mag 7) y datos de destino a la derecha.
            "^FO40,180^BQN,2,7^FDQA," + d.CodigoSeguimiento + "^FS" +
            "^FO260,185^A0N,28,28^FB490,1,0,L,0^FD" + cliente + "^FS" +
            "^FO260,216^A0N,22,22^FB490,2,0,L,0^FD" + domicilio + "^FS" +
            "^FO260,270^A0N,22,22^FB490,1,0,L,0^FD" + localidad + (cp ? " (" + cp + ")" : "") + "^FS" +
            "^FO260,299^A0N,22,22^FB490,1,0,L,0^FDProv: " + provincia + "^FS" +
            "^FO30,393^GB740,3,3^FS" +
            // FIX 4: REF con letra más grande (26 en vez de 22), sigue
            // pudiendo ocupar 2 renglones si es largo.
            (observaciones ? "^FO30,405^A0N,26,26^FB740,2,0,L,0^FDREF: " + observaciones + "^FS" : "") +
            // Caja destacada abajo de todo: Recorrido y Posición lado a
            // lado arriba, Código de Proveedor ocupando todo el ancho
            // abajo. FIX 2: la línea divisoria vertical ahora solo cubre
            // la fila de Recorrido/Posición (altura 130, arrancando en la
            // misma franja que esos números) y no sigue bajando hasta
            // pisar el Código de Proveedor como antes.
            "^FO20,476^GB760,300,4^FS" +
            "^FO40,490^A0N,18,18^FDRECORRIDO^FS" +
            "^FO40,512^A0N,74,74^FB350,1,0,L,0^FD" + (d.Recorrido || "-") + "^FS" +
            "^FO400,483^GB3,130,3^FS" +
            "^FO420,490^A0N,18,18^FDPOSICION^FS" +
            "^FO420,512^A0N,74,74^FB350,1,0,L,0^FD" + posicion + "^FS" +
            "^FO30,620^GB740,3,3^FS" +
            "^FO40,632^A0N,18,18^FDCODIGO DE PROVEEDOR^FS" +
            "^FO40,654^A0N,66,66^FB720,1,0,C,0^FD" + zplLimpio(d.idProveedor || "-") + "^FS" +
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
    //
    // FIX (a pedido: "que pregunte antes de imprimir todo, para que no se
    // equivoquen y salgan 20 al vicio"): confirmación previa mostrando
    // cuántas etiquetas/rótulos va a mandar en total (suma de Cantidad de
    // todos los paquetes, no solo la cantidad de paquetes - un paquete
    // puede tener varios bultos).
    function imprimirTodoElRecorrido() {
        if (!paquetesActuales.length) {
            if (window.toast) toast("error", "Etiquetas", "No hay paquetes para imprimir en este recorrido.");
            return;
        }

        var totalEtiquetas = paquetesActuales.reduce(function (acc, p) {
            return acc + Math.max(1, parseInt(p.Cantidad, 10) || 1);
        }, 0);
        var tipoTexto = tipoEtiquetaSeleccionado() === "etiqueta" ? "etiquetas" : "rótulos";

        function ejecutar() {
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

        if (typeof Swal !== "undefined") {
            Swal.fire({
                icon: "question",
                title: "¿Imprimir todo el recorrido?",
                text: "Estás por imprimir " + totalEtiquetas + " " + tipoTexto + " (" + paquetesActuales.length + " paquetes). ¿Continuar?",
                showCancelButton: true,
                confirmButtonText: "Sí, imprimir",
                cancelButtonText: "Cancelar",
            }).then(function (r) {
                if (r.isConfirmed) ejecutar();
            });
        } else if (confirm("Estás por imprimir " + totalEtiquetas + " " + tipoTexto + " (" + paquetesActuales.length + " paquetes). ¿Continuar?")) {
            ejecutar();
        }
    }

    // ------------------------------------------------------------------
    // RECORRIDOS
    // ------------------------------------------------------------------
    // Filtro "Solo origen Dinter" (a pedido, 2026-09-16): checkbox arriba de
    // todo, se manda a las dos acciones (Recorridos y Paquetes) para que la
    // lista de recorridos Y el detalle de cada uno queden consistentes.
    function soloDinter() {
        return $("#er_solo_dinter").is(":checked") ? 1 : 0;
    }

    function cargarRecorridos() {
        $recTabla.html('<tr><td colspan="5" class="text-center text-muted py-4">Cargando…</td></tr>');
        $.ajax({
            url: "Proceso/php/etiquetas_recorrido.php",
            type: "POST",
            data: { Recorridos: 1, SoloDinter: soloDinter() },
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
        $paqTitulo.text("Paquetes del recorrido " + recorrido);
        $paqModal.modal("show");
        cargarPaquetes(recorrido);
    }

    function cargarPaquetes(recorrido, onListo) {
        $paqTabla.html('<tr><td colspan="7" class="text-center text-muted py-4">Cargando…</td></tr>');
        $.ajax({
            url: "Proceso/php/etiquetas_recorrido.php",
            type: "POST",
            data: { Paquetes: 1, Recorrido: recorrido, SoloDinter: soloDinter() },
            success: function (response) {
                var jsonData = typeof response === "string" ? JSON.parse(response) : response;
                paquetesActuales = jsonData.data || [];
                renderPaquetes(paquetesActuales);
                if (onListo) onListo();
            },
            error: function () {
                $paqTabla.html('<tr><td colspan="7" class="text-center text-danger py-4">No se pudieron cargar los paquetes.</td></tr>');
            },
        });
    }

    function renderPaquetes(rows) {
        if (!rows.length) {
            $paqTabla.html('<tr><td colspan="7" class="text-center text-muted py-4">Este recorrido no tiene paquetes pendientes.</td></tr>');
            return;
        }
        var html = "";
        rows.forEach(function (d) {
            html +=
                '<tr data-id="' + d.id + '">' +
                "<td>" + d.CodigoSeguimiento + "</td>" +
                "<td>" + (d.OrigenNombre || "-") + "</td>" +
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

    $("#er_solo_dinter").on("change", function () {
        cargarRecorridos();
        if (recorridoActual) cargarPaquetes(recorridoActual);
    });
})();
