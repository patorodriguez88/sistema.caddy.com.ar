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

    function conectarImpresora() {
        if (typeof BrowserPrint === "undefined") {
            setEstadoPrinter("error", "Impresora: SDK no cargó");
            return;
        }
        setEstadoPrinter("buscando", "Impresora: buscando…");
        BrowserPrint.getDefaultDevice(
            "printer",
            function (device) {
                selected_device = device;
                setEstadoPrinter("ok", "Impresora: " + device.name);
            },
            function () {
                selected_device = null;
                setEstadoPrinter("error", "Impresora: no detectada — ¿está abierto Zebra Browser Print en esta PC?");
            }
        );
    }

    $("#cd_printer_reintentar").on("click", conectarImpresora);

    $printSwitch.on("change", function () {
        guardarPreferenciaImpresion(imprimirActivo());
    });

    // ZPL del rótulo que se pega en el bulto — mismo criterio que
    // imprimirEtiquetasColecta() de print_automatic.js: el QR SIEMPRE lleva
    // el código de Caddy (nunca el del proveedor), y acá además el
    // Recorrido va grande porque es el dato que el operador necesita leer
    // de un vistazo para saber a qué carro/zona va el bulto.
    //
    // OJO: este NO es el rótulo grande de colecta (6,5x3,2cm / 520x256pt) —
    // es el rótulo chico (6x2,5cm), que a 203dpi (2.54cm=1", único dpi de
    // la impresora) son ~480x200 puntos. A ese tamaño el logo de Caddy no
    // entra bien junto con QR + 6 líneas de texto, así que se sacó acá:
    // prioriza que el QR y los datos queden legibles antes que la marca.
    // Si igual lo querés, avisame y lo agrego más chico probando en la
    // impresora real (una foto ayuda a calibrar).
    function construirZplCrossdocking(data) {
        var fechaTexto = (function () {
            var h = new Date();
            return String(h.getDate()).padStart(2, "0") + "/" + String(h.getMonth() + 1).padStart(2, "0") + "/" + h.getFullYear();
        })();

        return (
            "^XA" +
            "^PW480" +
            "^LL200" +
            "^LH0,0" +
            "^FX Datos crossdocking." +
            "^FO170,4^A0N,14,14^FD" + zplLimpio(data.razonSocialOrigen || "CROSSDOCKING") + "^FS" +
            "^FO170,22^A0N,30,30^FDRec: " + (data.recorrido || "-") + "^FS" +
            // Pos (posición de entrega dentro del recorrido) — mismo par
            // Rec/Pos grande que ya usa el rótulo de colecta (wepoint.ar).
            "^FO170,54^A0N,26,26^FDPos: " + (data.posicion || "-") + "^FS" +
            "^FO170,82^A0N,13,13^FDId: " + data.codigoEtiqueta + "^FS" +
            "^FO170,98^A0N,13,13^FDBulto: " + data.bultoActual + "/" + data.bultoTotal + "^FS" +
            "^FO170,114^A0N,13,13^FD" + zplLimpio((data.clienteDestino || "-").substring(0, 22)) + "^FS" +
            "^FO170,130^A0N,13,13^FD" + fechaTexto + "^FS" +
            "^FO15,30^BQN,2,5^FDQA," + data.codigoEtiqueta + "^FS" +
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

    function imprimirRotulo(data) {
        if (!imprimirActivo()) return;
        if (!selected_device) {
            setEstadoPrinter("error", "Impresora: no conectada (no se imprimió)");
            return;
        }
        try {
            selected_device.send(construirZplCrossdocking(data), undefined, function (err) {
                console.error("Error imprimiendo:", err);
                setEstadoPrinter("error", "Impresora: error al imprimir");
            });
        } catch (e) {
            console.error("Excepción imprimiendo:", e);
            setEstadoPrinter("error", "Impresora: error al imprimir");
        }
    }

    function refocus() {
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
            '</div>'
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
            $recGrid.append($card);
        });
    }

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
    function cargarEstadoDeHoy() {
        $.ajax({
            url: "Proceso/php/crossdocking.php",
            type: "GET",
            dataType: "json",
            data: { EstadoCrossdocking: 1 },
            success: function (resp) {
                if (!resp || !resp.ok) return;

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
        });
    }

    $printSwitch.prop("checked", leerPreferenciaImpresion());
    conectarImpresora();

    cargarEstadoDeHoy();
    $input.trigger("focus");
})();
