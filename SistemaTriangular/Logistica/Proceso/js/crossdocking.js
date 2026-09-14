// Pantalla de CrossDocking — el operador de WePoint escanea etiquetas de
// cualquier origen (Ferniplast, Mercado Libre, Caddy propio, IGALFER) y acá
// se resuelve siempre contra el mismo dato: el envío de Caddy.
//
// El input queda enfocado todo el tiempo (estilo caja registradora / pantalla
// de cocina): el lector de códigos escribe y manda Enter solo (así vienen
// configurados de fábrica los lectores "keyboard wedge"), no hace falta
// tocar nada con el mouse.
//
// Lo más importante para el operador es EL RECORRIDO (a qué carro/zona va el
// bulto) — por eso domina el banner de "último escaneo" y por eso el grueso
// de la pantalla es una grilla de tarjetas por recorrido, coloreadas igual
// que el mapa de Repartidores en Vivo (Recorridos.Color), con la cantidad
// bien grande para leer de lejos.

(function () {
    var $input = $("#cd_input");
    var $ultimo = $("#cd_ultimo");
    var $feed = $("#cd_feed");
    var $recGrid = $("#cd_rec_grid");

    var contadores = { ok: 0, dup: 0, err: 0 };

    // recorrido (int) -> { cantidad, nombre, color, ultimaHora }
    var recorridos = {};

    // Mismo criterio que Logistica/Mapas/js/repartidores_live.js: el color
    // vive en Recorridos.Color (a veces con '#', a veces sin, a veces vacío
    // o negro "sin elegir") — se normaliza igual en toda la app.
    var CADDY_ORANGE = "E24F30";
    function normalizarColorRecorrido(c) {
        var limpio = (c || "").replace("#", "").toLowerCase();
        if (limpio === "" || limpio === "000000" || limpio === "000") {
            return CADDY_ORANGE;
        }
        return limpio;
    }

    // Texto blanco o negro según qué contraste mejor contra el color de fondo.
    function colorTexto(hex) {
        var r = parseInt(hex.substr(0, 2), 16) || 0;
        var g = parseInt(hex.substr(2, 2), 16) || 0;
        var b = parseInt(hex.substr(4, 2), 16) || 0;
        var luminancia = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return luminancia > 0.55 ? "#111" : "#fff";
    }

    // ------------------------------------------------------------------
    // Impresora Zebra (BrowserPrint). Corre un servicio local en la PC del
    // operador — si no está instalado/abierto, getDefaultDevice tira error
    // y acá lo mostramos como "No detectada" en vez de romper la pantalla.
    // El switch de impresión queda guardado por navegador (localStorage):
    // es una preferencia de ESTA estación de trabajo, no algo que tenga
    // sentido compartir entre operadores/PCs distintas. Arranca APAGADO a
    // propósito — se prende una vez que el operador confirmó que detectó
    // la impresora, así el primer uso nunca tira error por sorpresa.
    // ------------------------------------------------------------------
    var $printerEstado = $("#cd_printer_estado");
    var $printerEstadoTxt = $("#cd_printer_estado_txt");
    var $printSwitch = $("#cd_print_switch");
    var selected_device = null;

    function imprimirActivo() {
        return $printSwitch.is(":checked");
    }

    function leerPreferenciaImpresion() {
        try {
            return localStorage.getItem("cd_imprimir_activo") === "1";
        } catch (e) {
            return false;
        }
    }

    function guardarPreferenciaImpresion(activo) {
        try {
            localStorage.setItem("cd_imprimir_activo", activo ? "1" : "0");
        } catch (e) {
            // localStorage puede fallar (ventana privada, storage bloqueado):
            // no es grave, simplemente no se recuerda la preferencia.
        }
    }

    function setEstadoPrinter(estado, texto) {
        // estado: "buscando" | "ok" | "error"
        $printerEstado.removeClass("buscando ok error").addClass(estado);
        $printerEstadoTxt.text(texto);
    }

    // FIX (post-prueba real en depósito): la app de escritorio "Zebra
    // Browser Print" tiene un arranque en frío conocido — el primer
    // getDefaultDevice() del día/sesión de navegador suele fallar aunque la
    // impresora esté prendida y todo bien configurado, y una vez que
    // CUALQUIER página logra conectar una vez, las siguientes conectan
    // solas. Antes eso obligaba al operador a ir a buscar otra pantalla
    // (Pendientes > Rótulo) para "despertarla". Ahora se reintenta sola,
    // callada, un par de veces al abrir la pantalla.
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

    $("#cd_printer_reintentar").on("click", function () {
        conectarImpresora(false, 2);
    });

    $printSwitch.on("change", function () {
        guardarPreferenciaImpresion(imprimirActivo());
    });

    // Logo real (el mismo bitmap que ya usa el botón "Rótulo" del panel de
    // seguimiento — Funciones/js/seguimiento.js — y la etiqueta de colecta
    // de wepoint.ar). No es un logo inventado por mí: lo copié tal cual de
    // ahí para que salga igual de prolijo que el rótulo que ya conocen.
    var CADDY_LOGO_ZPL =
        "^FO30,15^GFA,1675,1675,25,,:::::::::::::M0CJ04J01,L07F8003FCI0FE,L0FFC007FE003FF,K01FFE00IF007FF8,K03FFE01IF007FFC,K03IF01IF80IFC,K03IF81IFC0IFE,K07IF83IFC0IFE,K07IFC1IFE0IFE,K03IFE1JF0F01E,K03IFE1JF0E01C,K03JF1JF8703C,K01JF0JF87C78,L0JF87IFC3FF,L0JFC7IFE0FE,L07IFC3IFE01V07,L07IFE3JFX0F,L03IFE1JFX0F,L01JF0JF8W07J0F,L01JF8JFCgH0F,M0JF87IFCgH0F,M0JFC7IFEI0E3C38FC3FE07F8F73F3FE,M07IFC3IFEI0E3E79FF3FF0FFCF7FFBFF,M03IFE1JFI0E3E7BFF3FF9FFEF7FFBFE,M03IFE1JFI0F7E77C73C79E1EF7C78F,M01IFE0JFI0F7E7787BC3DE1E77878F,N0IFE07IFI077FF7FFBC3DC0FF7838F,N0IFE07IFI07F7E7FFBC3DC0FF7838F,N07FFE03IFI07E7E7803C3DE1EF7838F,N07FFE03IFI03E7E7C13C79E1EF7838F,N03FFC01FFEI03E3C3FF3FF9FFEF7878FE,N01FF800FFCI03E3C1FFBFF0FFCF78787F,O0FFI07F8I01C3C0FF3FE07F0778383F,U08Q03C,gM03C,:::,::::::::::::::^FS";

    // ZPL del rótulo — mismo tamaño y layout que el botón "Rótulo" ya
    // probado (Funciones/js/seguimiento.js::_rotuloZPL): 6,5x3,2cm @203dpi
    // = 520x256 puntos, logo real, QR + texto desde X=200. La diferencia
    // con ese: acá "Bulto" es el X/Y REAL de este escaneo (bultoActual/
    // bultoTotal), no un "1/N" fijo — con multi-bulto dice cuál bulto es
    // cada etiqueta, no solo cuántos hay en total.
    function construirZplCrossdocking(data) {
        var fechaTexto = (function () {
            var h = new Date();
            return String(h.getDate()).padStart(2, "0") + "/" + String(h.getMonth() + 1).padStart(2, "0") + "/" + h.getFullYear();
        })();

        return (
            "^XA^PW520^LL256^LH0,0^CI28" +
            CADDY_LOGO_ZPL +
            "^FO215,10^A0N,20,20^FD" + zplLimpio((data.clienteDestino || "-").substring(0, 26)) + "^FS" +
            "^FO215,35^A0N,18,18^FD" + zplLimpio((data.domicilioDestino || "").substring(0, 30)) + "^FS" +
            "^FO215,57^A0N,18,18^FDId: " + data.codigoEtiqueta + "^FS" +
            "^FO215,79^A0N,18,18^FDOrigen: " + zplLimpio((data.razonSocialOrigen || "").substring(0, 26)) + "^FS" +
            "^FO215,101^A0N,18,18^FDBulto: " + data.bultoActual + "/" + data.bultoTotal + "^FS" +
            "^FO215,123^A0N,18,18^FDFecha: " + fechaTexto + "^FS" +
            "^FO215,148^A0N,30,30^FDRec: " + (data.recorrido || "-") + "^FS" +
            "^FO215,185^A0N,26,26^FDPos: " + (data.posicion || "-") + "^FS" +
            // FIX (2026-09-14): mismo ajuste que el rótulo de seguimiento.js -
            // todo el bloque corrido 15 dots a la derecha, el QR arrancaba
            // pegado al margen izquierdo físico del rótulo.
            "^FO45,74^BQN,2,7^FDQA," + data.codigoEtiqueta + "^FS" +
            "^XZ"
        );
    }

    // Rótulo de PALLET (distinto del rótulo de bulto de arriba): solo el
    // número de recorrido, lo más grande posible, para pegar en el pallet/
    // carro físico donde el operador va apilando los bultos de ese
    // recorrido — así lo identifican de lejos sin tener que leer una
    // etiqueta de bulto. Mismo tamaño físico que el resto de los rótulos
    // de esta pantalla: 6x2,5cm @203dpi = 480x200pt.
    function construirZplPallet(numeroRecorrido) {
        var texto = zplLimpio(String(numeroRecorrido || "-"));
        // Fuente escalable centrada con ^FB: baja el tamaño para números de
        // 3+ dígitos para que siga entrando ancho en el rótulo.
        var fontSize = texto.length <= 2 ? 190 : (texto.length === 3 ? 150 : 110);
        var y = Math.max(0, Math.round((200 - fontSize) / 2));
        return (
            "^XA^PW480^LL200^CI28" +
            "^FO0," + y + "^FB480,1,0,C,0^A0N," + fontSize + "," + fontSize + "^FD" + texto + "^FS" +
            "^XZ"
        );
    }

    // Limpieza básica para texto dentro de campos ^FD — nada de acentos/ñ
    // ni caracteres que rompan el ZPL (mismo criterio que getCleanedString
    // de print_automatic.js).
    function zplLimpio(s) {
        return String(s || "")
            .replace(/[áàä]/gi, "a").replace(/[éèë]/gi, "e").replace(/[íìï]/gi, "i")
            .replace(/[óòö]/gi, "o").replace(/[úùü]/gi, "u").replace(/ñ/gi, "n")
            .replace(/[\^~]/g, "");
    }

    // FIX (post-prueba real): la primera impresión del día podía fallar
    // aunque el estado ya mostrara "conectada" — el mismo arranque en frío
    // de BrowserPrint puede dejar un device "vivo" en la variable pero que
    // ya no responde. En vez de obligar al operador a ir a otra pantalla
    // para "despertarla" (que es justo lo que pasó en la prueba real),
    // ahora un fallo de impresión dispara UN reintento automático: pide un
    // device fresco con getDefaultDevice() y reintenta el mismo rótulo una
    // sola vez antes de darse por vencido.
    //
    // Envío genérico de un ZPL ya armado, con el mismo reintento automático
    // (arranque en frío de BrowserPrint) que se probó en vivo para el
    // rótulo de bulto: si falla el envío, pide un device fresco y reintenta
    // una sola vez antes de darse por vencido. Se separó de imprimirRotulo
    // para poder reusarlo también en el rótulo de pallet (botón en la
    // tarjeta de cada recorrido), sin duplicar la lógica de reintento.
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

    // forzado=true: pedido MANUAL de reimpresión (botón "Reimprimir Rótulo"
    // en un reingreso) — imprime aunque el switch de auto-impresión esté
    // apagado, porque acá el operador lo está pidiendo a propósito, no es
    // un escaneo automático.
    function imprimirRotulo(data, reintentando, forzado) {
        if (!imprimirActivo() && !forzado) return;
        enviarZPL(construirZplCrossdocking(data), reintentando);
    }

    // Rótulo de PALLET (botón chico en la tarjeta de cada recorrido): pedido
    // manual del operador, siempre imprime — a propósito ignora el switch
    // de auto-impresión igual que "Reimprimir Rótulo", porque acá siempre
    // es un pedido explícito, nunca un efecto secundario de un escaneo.
    function imprimirRotuloPallet(numeroRecorrido, onOk, onErr) {
        enviarZPL(construirZplPallet(numeroRecorrido), false, onOk, onErr);
    }

    function refocus() {
        // Con el modal de recorrido abierto, este input queda tapado por el
        // backdrop pero NO deshabilitado — sin este chequeo, cualquier click
        // adentro del modal (ej. en el panel de "Controlar recorrido") le
        // robaba el foco al input de control, cortando el lector de códigos
        // a mitad de un control.
        if ($(".modal.show").length) return;
        // pequeño delay: si se dispara justo después de un blur del navegador
        // (alt-tab, click accidental) igual termina enfocado.
        setTimeout(function () {
            $input.trigger("focus");
        }, 30);
    }

    function beep(tipo) {
        try {
            var Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            var ctx = new Ctx();
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);

            // ok: tono agudo cortito. dup: dos tonos medios. err: tono grave largo.
            var freq = tipo === "ok" ? 880 : tipo === "dup" ? 620 : 220;
            var dur = tipo === "err" ? 0.35 : 0.12;

            osc.frequency.value = freq;
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + dur);
            osc.start();
            osc.stop(ctx.currentTime + dur);

            if (tipo === "dup") {
                setTimeout(function () {
                    var osc2 = ctx.createOscillator();
                    var gain2 = ctx.createGain();
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.frequency.value = 500;
                    gain2.gain.setValueAtTime(0.15, ctx.currentTime);
                    gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.12);
                    osc2.start();
                    osc2.stop(ctx.currentTime + 0.12);
                }, 140);
            }
        } catch (e) {
            // sin audio no se frena nada, es solo un plus.
        }
    }

    function matchLabel(match) {
        return {
            ML_JSON: "Mercado Libre",
            ML_CODPROV: "Mercado Libre (Flex)",
            WEPOINT_C: "Proveedor / Caddy",
            CADDY_QR: "Caddy",
            PROV_CODPROV: "Proveedor",
            HISTORIAL: "recargado de hoy",
        }[match] || match;
    }

    function renderUltimoOk(data, esDup) {
        var colorHex = "#" + normalizarColorRecorrido(data.recorridoColor);
        var txtColor = colorTexto(normalizarColorRecorrido(data.recorridoColor));

        var bultoTotal = data.bultoTotal || data.cantidad || 1;
        var bultoActual = data.bultoActual || (esDup ? bultoTotal : 1);
        var bultoCompleto = bultoActual >= bultoTotal;

        $ultimo.removeClass("err").toggleClass("dup", !!esDup);
        $ultimo.html(
            '<div class="cd-rec-chip" style="background:' + colorHex + ';color:' + txtColor + ';">' +
                '<div class="cd-rec-label">Recorrido</div>' +
                '<div class="cd-rec-numero"></div>' +
                '<div class="cd-rec-nombre"></div>' +
            '</div>' +
            '<div class="cd-bulto-chip' + (bultoCompleto ? ' completo' : '') + '">' +
                '<div class="cd-rec-label">Bulto</div>' +
                '<div class="cd-bulto-numero"></div>' +
            '</div>' +
            '<div class="cd-info">' +
                '<div class="cd-estado">' + (esDup ? "⚠ Ya estaba ingresado" : "✔ Ingreso OK") + " · " + matchLabel(data.match) + '</div>' +
                '<div class="cd-codigo"></div>' +
                '<div class="cd-detalle">' +
                    '<div><b>Origen</b><span class="v-origen"></span></div>' +
                    '<div><b>Destino</b><span class="v-destino"></span></div>' +
                    '<div><b>Localidad</b><span class="v-localidad"></span></div>' +
                    '<div><b>Posición</b><span class="v-posicion"></span></div>' +
                    '<div><b>Orden</b><span class="v-orden"></span></div>' +
                '</div>' +
            '</div>' +
            (esDup ? '<button type="button" class="btn btn-warning btn-lg cd-reimprimir-btn">🖨️ Reimprimir Rótulo</button>' : '')
        );
        $ultimo.find(".cd-rec-numero").text(data.recorrido || "-");
        $ultimo.find(".cd-rec-nombre").text(data.recorridoNombre || "");
        $ultimo.find(".cd-bulto-numero").text(bultoActual + " / " + bultoTotal);
        $ultimo.find(".cd-codigo").text(data.codigoEtiqueta);
        $ultimo.find(".v-origen").text(data.razonSocialOrigen || "-");
        $ultimo.find(".v-destino").text(data.clienteDestino || "-");
        $ultimo.find(".v-localidad").text(data.localidadDestino || "-");
        $ultimo.find(".v-posicion").text(data.posicion || "-");
        $ultimo.find(".v-orden").text(data.numeroOrden || "-");

        // Reimpresión manual — a propósito ignora el switch de
        // auto-impresión (imprimirRotulo con forzado=true): el operador la
        // está pidiendo, no es un escaneo automático.
        $ultimo.find(".cd-reimprimir-btn").on("click", function () {
            var $b = $(this).prop("disabled", true).text("Imprimiendo…");
            imprimirRotulo(data, false, true);
            setTimeout(function () {
                $b.prop("disabled", false).text("🖨️ Reimprimir Rótulo");
            }, 1500);
        });
    }

    function renderUltimoErr(codigo) {
        $ultimo.removeClass("dup ambiguo").addClass("err");
        $ultimo.html(
            '<div class="cd-info">' +
                '<div class="cd-estado">✖ No se encontró ningún envío para este código</div>' +
                '<div class="cd-codigo"></div>' +
                '<div class="cd-detalle"><div>Revisá la etiqueta o cargá el envío en el sistema.</div></div>' +
            '</div>'
        );
        $ultimo.find(".cd-codigo").text(codigo);
    }

    // Colisión real detectada por el server: el código de proveedor coincide
    // con pendientes de más de un cliente de origen. En vez de adivinar
    // (como hacía antes, priorizando Ferniplast a ciegas), se le pregunta al
    // operador con un botón grande por cada candidato.
    function renderUltimoAmbiguo(codigo, candidatos) {
        $ultimo.removeClass("dup err").addClass("ambiguo");
        var $wrap = $(
            '<div class="cd-info">' +
                '<div class="cd-estado">⚠ Este código coincide con más de un proveedor</div>' +
                '<div class="cd-codigo"></div>' +
                '<div class="cd-detalle"><div>¿De cuál es este bulto?</div></div>' +
                '<div class="cd-ambiguo-botones"></div>' +
            '</div>'
        );
        $wrap.find(".cd-codigo").text(codigo);
        var $botones = $wrap.find(".cd-ambiguo-botones");
        candidatos.forEach(function (c) {
            var $b = $('<button type="button" class="btn btn-warning btn-lg"></button>').text(c.razonSocial);
            $b.on("click", function () {
                procesarCodigo(codigo, c.idClienteOrigen);
            });
            $botones.append($b);
        });
        $ultimo.empty().append($wrap);
    }

    function agregarAlFeed(claseCss, codigo, meta) {
        var $item = $(
            '<div class="cd-feed-item ' + claseCss + '">' +
                '<div class="cd-feed-codigo"></div>' +
                '<div class="cd-feed-meta"></div>' +
            '</div>'
        );
        $item.find(".cd-feed-codigo").text(codigo);
        $item.find(".cd-feed-meta").text(meta);
        $feed.prepend($item);

        // no dejamos crecer infinito el feed en memoria/DOM
        var $items = $feed.children();
        if ($items.length > 60) {
            $items.slice(60).remove();
        }
    }

    function actualizarContadores() {
        $("#cd_cnt_ok").text("OK: " + contadores.ok);
        $("#cd_cnt_dup").text("Reingresos: " + contadores.dup);
        $("#cd_cnt_err").text("Sin match: " + contadores.err);
    }

    // Actualiza (o crea) la tarjeta del recorrido en memoria y re-renderiza
    // toda la grilla, ordenada por "más recién tocado primero" — la pantalla
    // es en vivo, el recorrido en el que están trabajando ahora tiene que
    // quedar arriba, no perdido entre los demás.
    //
    // cantidad/esperados vienen YA CALCULADOS por el server en cada
    // respuesta (no se suman acá): así el número es autoritativo — si hay
    // dos pantallas abiertas a la vez, las dos terminan mostrando lo mismo,
    // y no hay riesgo de que un cálculo local se desincronice.
    function tocarRecorrido(numero, nombre, colorRaw, horaHHMMSS, cantidad, esperados) {
        numero = numero || 0;
        var previo = recorridos[numero];
        recorridos[numero] = {
            cantidad: cantidad,
            esperados: esperados,
            nombre: nombre || (previo ? previo.nombre : ""),
            color: colorRaw || (previo ? previo.color : ""),
            ultimaHora: horaHHMMSS || (previo ? previo.ultimaHora : ""),
        };
        renderGrid(numero);
    }

    function setRecorridoDesdeEstado(numero, nombre, colorRaw, cantidad, esperados, ultimaHora) {
        recorridos[numero || 0] = { cantidad: cantidad, esperados: esperados, nombre: nombre || "", color: colorRaw || "", ultimaHora: ultimaHora || "" };
    }

    function renderGrid(numeroRecienTocado) {
        var lista = Object.keys(recorridos).map(function (k) {
            return Object.assign({ numero: parseInt(k, 10) }, recorridos[k]);
        });
        lista.sort(function (a, b) {
            return (b.ultimaHora || "").localeCompare(a.ultimaHora || "");
        });

        $recGrid.empty();
        lista.forEach(function (r) {
            var colorHex = "#" + normalizarColorRecorrido(r.color);
            var esperados = r.esperados || r.cantidad;
            var completo = r.cantidad >= esperados && esperados > 0;
            var $card = $('<div class="cd-rec-card"></div>').css("border-top-color", colorHex);
            if (completo) $card.addClass("completo");
            if (r.numero === numeroRecienTocado) {
                $card.addClass("cd-rec-card-nuevo");
            }

            // Check automático al lado de los números — no hace falta que
            // el operador confirme nada, solo indica de un vistazo que
            // llegaron todos los bultos esperados para este recorrido.
            var check = completo ? '<span class="cd-check-completo">✔</span>' : "";

            $card.html(
                '<div class="cd-rec-card-num" style="color:' + colorHex + ';"></div>' +
                '<div class="cd-rec-card-nombre"></div>' +
                '<div class="cd-rec-card-cant"><span class="v-cant"></span><span class="cd-rec-card-de">de</span><span class="v-esp"></span>' + check + '</div>' +
                '<div class="cd-rec-card-label">Paquetes hoy</div>'
            );
            $card.find(".cd-rec-card-num").text("Recorrido " + (r.numero || "-"));
            $card.find(".cd-rec-card-nombre").text(r.nombre || "");
            $card.find(".v-cant").text(r.cantidad);
            $card.find(".v-esp").text(esperados);

            // Tocar la tarjeta abre el menú del recorrido (imprimir / ver
            // pendientes / controlar) — el botón de imprimir que antes vivía
            // suelto en la tarjeta se movió adentro de ese menú, a pedido,
            // para no ensuciar la tarjeta con más botones.
            $card.on("click", function () {
                abrirModalRecorrido(r.numero, r.nombre, esperados);
            });

            $recGrid.append($card);
        });
    }

    // Modal del recorrido: arranca en un menú de 3 acciones (imprimir / ver
    // pendientes / controlar). Nada de esto se guarda entre aperturas —
    // cada vez que se abre se pide todo fresco al server (o arranca en 0,
    // en el caso del control).
    var $modalRecorrido = $("#cd_modal_recorrido");
    var $modalRecNum = $("#cd_modal_rec_num");
    var $modalRecNombre = $("#cd_modal_rec_nombre");
    var $modalBody = $("#cd_modal_body");

    // Estado del control de recorrido en curso (null = no hay ninguno
    // activo). Vive SOLO en memoria del navegador — se pierde al cerrar el
    // modal o recargar la página, a propósito (ver docblock del endpoint
    // ControlarCrossdocking en el server).
    var controlState = null;

    // "esperados" del recorrido que tiene abierto el modal ahora mismo — se
    // guarda acá (no solo se pasa de función en función) para que "Volver"
    // desde Pendientes hacia el menú, y de ahí a Controlar, no lo pierda.
    var modalEsperadosActual = null;

    function abrirModalRecorrido(numero, nombre, esperados) {
        controlState = null;
        modalEsperadosActual = esperados;
        $modalRecorrido.removeClass("cd-control-completo");
        $modalRecNum.text(numero || "-");
        $modalRecNombre.text(nombre || "");
        renderMenuView(numero, nombre, esperados);
        $modalRecorrido.modal("show");
    }

    function renderMenuView(numero, nombre, esperados) {
        var $menu = $(
            '<div class="cd-menu-botones">' +
                '<button type="button" class="cd-menu-btn" id="cd_menu_imprimir"><span class="cd-menu-btn-icono">🖨️</span><span>Imprimir rótulo del recorrido</span></button>' +
                '<button type="button" class="cd-menu-btn" id="cd_menu_pendientes"><span class="cd-menu-btn-icono">📋</span><span>Ver pendientes</span></button>' +
                '<button type="button" class="cd-menu-btn" id="cd_menu_controlar"><span class="cd-menu-btn-icono">🎯</span><span>Controlar recorrido</span></button>' +
            '</div>'
        );

        $menu.find("#cd_menu_imprimir").on("click", function () {
            var $btn = $(this).prop("disabled", true);
            var $texto = $btn.find("span").eq(1);
            var textoOriginal = $texto.text();
            $texto.text("Imprimiendo…");
            imprimirRotuloPallet(
                numero,
                function () { $texto.text("✔ Impreso"); setTimeout(function () { $texto.text(textoOriginal); $btn.prop("disabled", false); }, 1500); },
                function () { $texto.text("✖ No se pudo imprimir"); setTimeout(function () { $texto.text(textoOriginal); $btn.prop("disabled", false); }, 2000); }
            );
        });

        $menu.find("#cd_menu_pendientes").on("click", function () {
            cargarPendientes(numero);
        });

        $menu.find("#cd_menu_controlar").on("click", function () {
            iniciarControl(numero, esperados);
        });

        $modalBody.empty().append($menu);
    }

    function botonVolver(onClick) {
        var $btn = $('<button type="button" class="cd-modal-volver">← Volver</button>');
        $btn.on("click", onClick);
        return $btn;
    }

    function cargarPendientes(numero) {
        $modalBody.empty().append(
            botonVolver(function () { renderMenuView(numero, $modalRecNombre.text(), null); }),
            $('<div class="cd-modal-vacio">Cargando…</div>')
        );

        $.ajax({
            url: "Proceso/php/crossdocking.php",
            type: "GET",
            dataType: "json",
            data: { PendientesCrossdocking: 1, recorrido: numero },
            success: function (resp) {
                if (!resp || !resp.ok) {
                    $modalBody.find(".cd-modal-vacio").text("No se pudo cargar. Reintentá.");
                    return;
                }
                renderPendientes(numero, resp.pendientes || []);
            },
            error: function () {
                $modalBody.find(".cd-modal-vacio").text("Error de conexión. Reintentá.");
            },
        });
    }

    // Un renglón por BULTO pendiente (no por envío) — mismo criterio que la
    // tarjeta, así "34 esperados - 5 escaneados" siempre da exactamente la
    // cantidad de filas de esta tabla. Un envío multi-bulto con más de un
    // bulto pendiente aparece más de una vez, una por cada _N que falta.
    function renderPendientes(numero, pendientes) {
        var $volver = botonVolver(function () { renderMenuView(numero, $modalRecNombre.text(), null); });

        if (!pendientes.length) {
            $modalBody.empty().append($volver, '<div class="cd-modal-vacio">✔ No quedan pendientes en este recorrido.</div>');
            return;
        }

        var $tabla = $(
            '<table class="table table-sm">' +
                '<thead><tr>' +
                    '<th>Código</th>' +
                    '<th>Origen</th>' +
                    '<th>Cliente</th>' +
                    '<th>Localidad</th>' +
                '</tr></thead>' +
                '<tbody></tbody>' +
            '</table>'
        );
        var $tbody = $tabla.find("tbody");

        pendientes.forEach(function (p) {
            var $fila = $(
                '<tr>' +
                    '<td class="cd-modal-codigo"></td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td></td>' +
                '</tr>'
            );
            $fila.find(".cd-modal-codigo").text(p.codigoEtiqueta || "-");
            $fila.find("td").eq(1).text(p.origen || "-");
            $fila.find("td").eq(2).text(p.clienteDestino || "-");
            $fila.find("td").eq(3).text(p.localidadDestino || "-");
            $tbody.append($fila);
        });

        $modalBody.empty().append(
            $volver,
            $('<div class="text-muted mb-2"></div>').text(pendientes.length + " bulto" + (pendientes.length === 1 ? "" : "s") + " pendiente" + (pendientes.length === 1 ? "" : "s")),
            $tabla
        );
    }

    // --------------------------------------------------------------------
    // Controlar recorrido: el operador junta físicamente todos los bultos
    // de este recorrido y los vuelve a pasar, de corrido, como control
    // final. No escanea contra "lo que falta" sino contra "todo lo que
    // debería estar acá" — cualquier bulto de OTRO recorrido que se haya
    // mezclado se detecta en rojo. No imprime nada, no marca nada en el
    // server (ver docblock de ControlarCrossdocking) — la cuenta vive acá,
    // en memoria, mientras el modal está abierto.
    // --------------------------------------------------------------------
    function iniciarControl(numero, esperados) {
        controlState = {
            recorrido: numero,
            total: esperados || 0,
            contados: {}, // bultoKey -> true
            completo: false,
        };

        var $vista = $(
            '<div class="cd-control-view">' +
                '<button type="button" class="cd-modal-volver">← Volver</button>' +
                '<div class="cd-control-header">Control recorrido ' + (numero || "-") + '</div>' +
                '<div class="cd-control-contador"><span class="cd-control-actual">0</span><span class="cd-control-de">de</span><span class="cd-control-total"></span></div>' +
                '<div class="cd-control-flash" id="cd_control_flash">' +
                    '<div class="cd-control-flash-icono">📷</div>' +
                    '<div class="cd-control-flash-msg">Escaneá los bultos de este recorrido, uno por uno…</div>' +
                '</div>' +
                '<input type="text" class="cd-control-input" id="cd_control_input" placeholder="Escaneá acá…" autocomplete="off">' +
            '</div>'
        );
        $vista.find(".cd-modal-volver").on("click", function () {
            controlState = null;
            $modalRecorrido.removeClass("cd-control-completo");
            renderMenuView(numero, $modalRecNombre.text(), esperados);
        });
        $vista.find(".cd-control-total").text(controlState.total);

        $modalBody.empty().append($vista);
        setTimeout(function () { $("#cd_control_input").trigger("focus"); }, 50);

        $modalBody.off("keydown", "#cd_control_input").on("keydown", "#cd_control_input", function (e) {
            if (e.key !== "Enter") return;
            e.preventDefault();
            var $input = $(this);
            var val = $.trim($input.val());
            $input.val("");
            if (!val || !controlState || controlState.completo) return;
            escanearControl(val);
        });
    }

    function escanearControl(codigo) {
        $.ajax({
            url: "Proceso/php/crossdocking.php",
            type: "POST",
            dataType: "json",
            data: { ControlarCrossdocking: 1, codigo: codigo, recorrido: controlState.recorrido },
            success: function (resp) {
                if (!controlState) return; // el modal ya se cerró o se volvió al menú
                if (!resp || !resp.ok) {
                    flashControl("err", "✖", codigo, "Error de conexión");
                    return;
                }
                if (!resp.pertenece) {
                    var motivo = resp.recorridoReal
                        ? "Es del recorrido " + resp.recorridoReal + ", no de este"
                        : (resp.motivo === "AMBIGUO" ? "Código ambiguo — escaneá con la pantalla normal" : "No pertenece a este recorrido");
                    flashControl("err", "✖", resp.codigoEtiqueta || codigo, motivo);
                    return;
                }

                var key = resp.idTransClientes + "|" + (resp.bultoSufijo || "");
                var yaContado = !!controlState.contados[key];
                if (!yaContado) {
                    controlState.contados[key] = true;
                    actualizarContadorControl();
                }

                flashControl("ok", "✔", resp.codigoEtiqueta || codigo, yaContado ? "Ya contado — pertenece igual" : (resp.clienteDestino || ""));

                if (!controlState.completo && Object.keys(controlState.contados).length >= controlState.total && controlState.total > 0) {
                    marcarControlCompleto();
                }
            },
            error: function () {
                if (!controlState) return;
                flashControl("err", "✖", codigo, "Error de conexión");
            },
        });
    }

    function actualizarContadorControl() {
        if (!controlState) return;
        $modalBody.find(".cd-control-actual").text(Object.keys(controlState.contados).length);
    }

    var flashControlTimeout = null;
    function flashControl(tipo, icono, codigo, msg) {
        var $flash = $("#cd_control_flash");
        if (!$flash.length) return; // se volvió al menú mientras llegaba la respuesta
        clearTimeout(flashControlTimeout);
        $flash.removeClass("ok err").addClass(tipo);
        $flash.empty().append(
            $('<div class="cd-control-flash-icono"></div>').text(icono),
            $('<div class="cd-control-flash-codigo"></div>').text(codigo || ""),
            $('<div class="cd-control-flash-msg"></div>').text(msg || "")
        );
        // Vuelve a gris después de un par de segundos, listo para el
        // próximo escaneo — salvo que ya se haya completado el control.
        flashControlTimeout = setTimeout(function () {
            if (controlState && controlState.completo) return;
            $flash.removeClass("ok err");
            $flash.empty().append(
                '<div class="cd-control-flash-icono">📷</div>',
                '<div class="cd-control-flash-msg">Escaneá el próximo bulto…</div>'
            );
        }, 2000);
    }

    function marcarControlCompleto() {
        controlState.completo = true;
        $modalRecorrido.addClass("cd-control-completo");
        clearTimeout(flashControlTimeout);
        var $flash = $("#cd_control_flash");
        var hora = new Date();
        var horaTxt = String(hora.getHours()).padStart(2, "0") + ":" + String(hora.getMinutes()).padStart(2, "0");
        var usuario = window.CD_USUARIO || "";
        $flash.removeClass("err").addClass("ok").empty().append(
            '<div class="cd-control-flash-icono">✔</div>',
            '<div class="cd-control-flash-codigo">CONTROL OK</div>',
            $('<div class="cd-control-completo-msg"></div>').text("Controlado por " + usuario + " · " + horaTxt)
        );
        $modalBody.find("#cd_control_input").prop("disabled", true);
    }

    // Al cerrar el modal (cualquier vista) se corta el control en curso —
    // nada queda pendiente en el navegador, y el input principal de la
    // pantalla recupera el foco para seguir escaneando normal.
    $modalRecorrido.on("hidden.bs.modal", function () {
        controlState = null;
        $modalRecorrido.removeClass("cd-control-completo");
        refocus();
    });

    function procesarCodigo(codigo, proveedorForzado) {
        codigo = $.trim(codigo);
        if (!codigo) return;

        var datos = { EscanearCrossdocking: 1, codigo: codigo };
        if (proveedorForzado) datos.proveedorForzado = proveedorForzado;

        $.ajax({
            url: "Proceso/php/crossdocking.php",
            type: "POST",
            dataType: "json",
            data: datos,
            success: function (resp) {
                if (resp && !resp.ok && resp.error === "AMBIGUO") {
                    // Corta el flujo automático a propósito: necesita que
                    // el operador elija, no se puede seguir escaneando
                    // "a ciegas" hasta que responda esto.
                    renderUltimoAmbiguo(resp.codigo, resp.candidatos || []);
                    beep("dup");
                    return;
                }
                if (resp && resp.ok) {
                    var esDup = !!resp.yaIngresado;
                    renderUltimoOk(resp, esDup);
                    beep(esDup ? "dup" : "ok");

                    var horaCorta = (resp.hora || "").slice(0, 5);
                    agregarAlFeed(
                        esDup ? "dup" : "ok",
                        resp.codigoEtiqueta,
                        horaCorta + " · Rec " + (resp.recorrido || "-") + " · " + (resp.clienteDestino || matchLabel(resp.match))
                    );

                    tocarRecorrido(resp.recorrido, resp.recorridoNombre, resp.recorridoColor, resp.hora, resp.recorridoEscaneadosHoy, resp.recorridoEsperados);

                    // Un reingreso no imprime — ya salió un rótulo para esa
                    // etiqueta puntual, reimprimir sería duplicar el papel.
                    if (!esDup) {
                        imprimirRotulo(resp);
                    }

                    contadores[esDup ? "dup" : "ok"]++;
                } else {
                    renderUltimoErr((resp && resp.codigo) || codigo);
                    beep("err");
                    agregarAlFeed("err", codigo, "sin match");
                    contadores.err++;
                }
                actualizarContadores();
            },
            error: function () {
                renderUltimoErr(codigo);
                beep("err");
                agregarAlFeed("err", codigo, "error de conexión");
                contadores.err++;
                actualizarContadores();
            },
        });
    }

    $input.on("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            var val = $input.val();
            $input.val("");
            procesarCodigo(val);
        }
    });

    // si el operador clickea afuera (o el foco se pierde por cualquier motivo)
    // lo recuperamos, esta pantalla vive SIEMPRE con el input activo.
    $(document).on("click", function (e) {
        if (!$(e.target).is("input, textarea, select, button, a")) {
            refocus();
        }
    });
    $(window).on("focus", refocus);

    // Nada de esto se guarda en el navegador (no hay localStorage acá): todo
    // se reconstruye pidiéndole al server el estado de hoy (TransClientes.
    // Wepoint_f/h, agrupado por Recorrido). Así un F5 no vacía la grilla, y
    // si dos operadores abren la pantalla ven el mismo estado.
    // FIX: si esta llamada fallaba por lo que sea (sesión vencida justo al
    // recargar, error de PHP, corte de red) no había NINGÚN aviso — la
    // pantalla se quedaba con los contadores en 0 como si no se hubiera
    // escaneado nada en todo el día, sin ninguna pista de qué pasó. Ahora
    // reintenta sola una vez, y si vuelve a fallar lo deja bien visible en
    // vez de fallar calladita.
    function cargarEstadoDeHoy(reintentando) {
        $.ajax({
            url: "Proceso/php/crossdocking.php",
            type: "GET",
            dataType: "json",
            data: { EstadoCrossdocking: 1 },
            success: function (resp) {
                if (!resp || !resp.ok) {
                    console.error("EstadoCrossdocking respondió sin ok:", resp);
                    avisarFalloEstadoInicial();
                    return;
                }

                (resp.porRecorrido || []).forEach(function (r) {
                    setRecorridoDesdeEstado(r.recorrido, r.nombre, r.color, r.cantidad, r.esperados, r.ultimaHora);
                });
                renderGrid(null);

                var totalHoy = (resp.porRecorrido || []).reduce(function (acc, r) { return acc + r.cantidad; }, 0);
                contadores.ok = totalHoy;
                actualizarContadores();

                (resp.items || []).forEach(function (item) {
                    var horaCorta = (item.hora || "").slice(0, 5);
                    agregarAlFeed("ok", item.codigoEtiqueta, horaCorta + " · Rec " + (item.recorrido || "-") + " · " + (item.clienteDestino || "-"));
                });

                if (resp.items && resp.items.length) {
                    var ultimo = resp.items[0];
                    var recInfo = recorridos[ultimo.recorrido || 0] || {};
                    renderUltimoOk({
                        match: "HISTORIAL",
                        codigoEtiqueta: ultimo.codigoEtiqueta,
                        razonSocialOrigen: ultimo.razonSocialOrigen,
                        clienteDestino: ultimo.clienteDestino,
                        localidadDestino: ultimo.localidadDestino,
                        cantidad: ultimo.cantidad,
                        bultoActual: ultimo.bultoActual,
                        bultoTotal: ultimo.bultoTotal,
                        recorrido: ultimo.recorrido,
                        recorridoNombre: recInfo.nombre,
                        recorridoColor: recInfo.color,
                        posicion: ultimo.posicion,
                        numeroOrden: ultimo.numeroOrden,
                    }, false);
                }
            },
            error: function (xhr) {
                console.error("Error cargando EstadoCrossdocking:", xhr.status, xhr.responseText);
                if (!reintentando) {
                    // Puede ser un hiccup de sesión justo al abrir la página
                    // (401 "X-Session-Expired") o timing — se reintenta una
                    // vez sola antes de avisar.
                    setTimeout(function () { cargarEstadoDeHoy(true); }, 1500);
                    return;
                }
                if (xhr.status === 401) {
                    avisarFalloEstadoInicial("Tu sesión venció — recargá la página para volver a entrar.");
                } else {
                    avisarFalloEstadoInicial();
                }
            },
        });
    }

    // Aviso visible cuando no se pudo traer el estado de hoy al abrir la
    // pantalla — antes esto fallaba en silencio y quedaba todo en 0 sin
    // ninguna pista de qué pasó.
    function avisarFalloEstadoInicial(mensaje) {
        if (contadores.ok > 0 || contadores.dup > 0) return; // ya se cargó algo, no pisar
        $ultimo.removeClass("dup ambiguo").addClass("err");
        $ultimo.html(
            '<div class="cd-info">' +
                '<div class="cd-estado">⚠ No se pudo cargar lo escaneado hoy</div>' +
                '<div class="cd-codigo" style="font-size:20px;"></div>' +
            '</div>'
        );
        $ultimo.find(".cd-codigo").text(mensaje || "Revisá la conexión o recargá la página. (Detalle en la consola del navegador — F12)");
    }

    $printSwitch.prop("checked", leerPreferenciaImpresion());
    conectarImpresora();

    cargarEstadoDeHoy();
    $input.trigger("focus");
})();
