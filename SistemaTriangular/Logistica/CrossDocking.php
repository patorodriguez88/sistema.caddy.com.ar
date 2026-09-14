<?php
// Sin esto la pantalla se armaba igual (HTML/CSS/JS) para cualquiera que
// entrara a la URL sin sesión activa — no se filtraba ningún dato (todo
// llega después por AJAX, y esas llamadas sí están gateadas por la
// sesión que valida Conexioni.php), pero quedaba "viva" sin login en vez
// de mandar directo al login como el resto del sistema. Wepoint.php y
// Zonas.php (mismo patrón, arrancan directo en <!DOCTYPE>) tienen el
// mismo agujero — quedan afuera de este fix, avisar si también hay que
// tocarlas.
include_once "../Conexion/Conexioni.php";
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | CrossDocking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Pantalla de escaneo para el operador de WePoint (crossdocking)" name="description" />
    <meta content="Coderthemes" name="author" />
    <!-- Caddy favicon -->
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <!-- Theme Config Js -->
    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <!-- Vendor css -->
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <!-- App css -->
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <!-- Icons css -->
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

    <style>
        /* Pantalla estilo "kitchen display" (KDS) — se mira de lejos, en el
           depósito, no desde un escritorio. Todo grande y con mucho contraste.
           Lo MÁS importante para el operador es el RECORRIDO (a qué carro/zona
           va el bulto), así que es el elemento más grande de toda la pantalla. */
        body {
            background: #0f1115;
        }

        .cd-wrap {
            min-height: 100vh;
            padding: 0 24px 24px;
            color: #f1f3f5;
        }

        /* Header pegajoso: barra de escaneo + banner del último escaneo. Usa
           position:sticky (no fixed) a propósito — así ocupa su lugar real en
           el flujo y el grid de recorridos de abajo NUNCA queda tapado por
           detrás; solo se "pega" arriba cuando scrolleás. */
        .cd-sticky-header {
            position: sticky;
            top: 0;
            z-index: 60;
            background: #0f1115;
            padding-top: 12px;
            padding-bottom: 10px;
            box-shadow: 0 10px 20px -6px rgba(0, 0, 0, .55);
        }

        .cd-input-bar {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 10px;
        }

        .cd-input-bar input[type=text] {
            flex: 1;
            font-size: 22px;
            padding: 10px 18px;
            border-radius: 10px;
            border: 3px solid #495057;
            background: #1a1d23;
            color: #f1f3f5;
        }

        .cd-input-bar input[type=text]:focus {
            outline: none;
            border-color: #3bd671;
            box-shadow: 0 0 0 4px rgba(59, 214, 113, .25);
        }

        .cd-counters {
            display: flex;
            gap: 10px;
            font-size: 16px;
            white-space: nowrap;
        }

        .cd-counters .badge {
            font-size: 15px;
            padding: 9px 13px;
        }

        /* Banner del último escaneo: el RECORRIDO es lo grande-grande, todo lo
           demás es secundario al lado.
           FIX (2026-09-14, a pedido): este banner + la barra de arriba se
           comían tanta altura que en el monitor real del depósito la
           grilla de recorridos quedaba cortada por el borde de la
           pantalla (no había forma de verlos todos sin scrollear, y en un
           monitor sin mouse/touch a mano nadie scrollea). Se achica el
           banner (menos padding, min-height más bajo) para dejarle más
           lugar a la grilla, que es lo que el operador necesita ver
           completo de un vistazo. */
        #cd_ultimo {
            border-radius: 18px;
            padding: 14px 26px;
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
            min-height: 100px;
            background: #1a1d23;
            border: 4px solid #343a40;
        }

        #cd_ultimo.err {
            background: #3a1414;
            border-color: #ff5c5c;
        }

        #cd_ultimo.ambiguo {
            background: #3a2d0d;
            border-color: #ffc93b;
        }

        /* margin-left:auto lo empuja al espacio libre de la derecha del
           banner (flex), sin desarmar el resto del layout. */
        #cd_ultimo .cd-reimprimir-btn {
            margin-left: auto;
            align-self: center;
            flex: 0 0 auto;
            font-size: 18px;
            padding: 14px 22px;
            white-space: nowrap;
        }

        .cd-ambiguo-botones {
            display: flex;
            gap: 12px;
            margin-top: 14px;
            flex-wrap: wrap;
        }

        .cd-ambiguo-botones button {
            font-size: 20px;
            padding: 14px 26px;
        }

        #cd_ultimo .cd-rec-chip {
            flex: 0 0 auto;
            border-radius: 14px;
            padding: 8px 26px;
            text-align: center;
            min-width: 160px;
        }

        #cd_ultimo .cd-rec-chip .cd-rec-label {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: .85;
            font-weight: 600;
        }

        #cd_ultimo .cd-rec-chip .cd-rec-numero {
            font-size: 54px;
            font-weight: 800;
            line-height: 1;
        }

        #cd_ultimo .cd-rec-chip .cd-rec-nombre {
            font-size: 14px;
            opacity: .85;
            margin-top: 2px;
        }

        /* "Bulto X de Y" — segundo dato prioritario después del recorrido:
           cuántos de los bultos de ESTE envío puntual ya se leyeron. Gris
           mientras falta alguno, verde cuando el envío quedó completo. */
        #cd_ultimo .cd-bulto-chip {
            flex: 0 0 auto;
            border-radius: 14px;
            padding: 8px 22px;
            text-align: center;
            min-width: 130px;
            background: #2a2e35;
            border: 2px solid #495057;
        }

        #cd_ultimo .cd-bulto-chip.completo {
            background: #123322;
            border-color: #3bd671;
        }

        #cd_ultimo .cd-bulto-chip .cd-rec-label {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: .75;
            font-weight: 600;
        }

        #cd_ultimo .cd-bulto-chip .cd-bulto-numero {
            font-size: 40px;
            font-weight: 800;
            line-height: 1;
        }

        #cd_ultimo .cd-info {
            flex: 1 1 320px;
        }

        #cd_ultimo .cd-estado {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 6px;
            color: #3bd671;
        }

        #cd_ultimo.dup .cd-estado { color: #ffc93b; }
        #cd_ultimo.err .cd-estado { color: #ff5c5c; }
        #cd_ultimo.ambiguo .cd-estado { color: #ffc93b; }

        #cd_ultimo .cd-codigo {
            font-size: 34px;
            font-weight: 800;
            line-height: 1.05;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }

        #cd_ultimo .cd-detalle {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 30px;
            margin-top: 10px;
            font-size: 17px;
        }

        #cd_ultimo .cd-detalle b {
            font-size: 13px;
            display: block;
            font-weight: 400;
            opacity: .65;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .cd-titulo {
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: .6;
            margin: 18px 0 10px;
        }

        /* Tarjetas por recorrido — el corazón de la pantalla: de un vistazo,
           cuántos paquetes hay ya en cada recorrido, coloreado igual que el
           mapa de Repartidores en Vivo. */
        .cd-rec-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
            gap: 12px;
        }

        /* Toda la tarjeta es clickeable (abre el detalle de pendientes) -
           cursor + un hover sutil para que se note, sin competir con el
           hover propio del botón de rótulo. */
        .cd-rec-card {
            position: relative;
            border-radius: 14px;
            background: #1a1d23;
            border-top: 8px solid #495057;
            padding: 12px 14px;
            transition: transform .15s ease, box-shadow .15s ease;
            cursor: pointer;
        }

        .cd-rec-card:hover {
            box-shadow: 0 0 0 2px rgba(255, 255, 255, .12);
        }


        .cd-rec-card.cd-rec-card-nuevo {
            animation: cdPulso .6s ease;
        }

        @keyframes cdPulso {
            0% { transform: scale(1.04); }
            100% { transform: scale(1); }
        }

        .cd-rec-card .cd-rec-card-num {
            font-size: 21px;
            font-weight: 800;
        }

        .cd-rec-card .cd-rec-card-nombre {
            font-size: 12px;
            opacity: .65;
            min-height: 16px;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cd-rec-card .cd-rec-card-cant {
            font-size: 42px;
            font-weight: 800;
            line-height: 1;
            display: flex;
            align-items: baseline;
        }

        /* "de" tiene que leerse como separador, no como un tercer número —
           bien chico, gris, con aire de los dos lados. */
        .cd-rec-card .cd-rec-card-de {
            font-size: 15px;
            font-weight: 400;
            opacity: .5;
            margin: 0 8px;
        }

        .cd-rec-card .v-esp {
            opacity: .55;
        }

        .cd-rec-card .cd-rec-card-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: .6;
        }

        /* Recorrido completo (llegaron todos los bultos esperados): se
           pinta de verde y aparece un check grande al lado de los números
           — no hace falta que el operador confirme nada a propósito. */
        .cd-rec-card.completo {
            background: #0d3321;
            box-shadow: inset 0 0 0 2px #3bd67155;
        }

        .cd-rec-card .cd-check-completo {
            font-size: 32px;
            font-weight: 800;
            color: #3bd671;
            margin-left: 10px;
            line-height: 1;
        }

        /* Feed chico de escaneos individuales — queda como detalle secundario
           para trazabilidad, ya no es el foco de la pantalla. */
        .cd-feed {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 10px;
        }

        .cd-feed-item {
            background: #1a1d23;
            border-left: 5px solid #495057;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
        }

        .cd-feed-item.ok { border-left-color: #3bd671; }
        .cd-feed-item.dup { border-left-color: #ffc93b; }
        .cd-feed-item.err { border-left-color: #ff5c5c; }

        .cd-feed-item .cd-feed-codigo {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 15px;
        }

        .cd-feed-item .cd-feed-meta {
            opacity: .7;
            font-size: 12px;
        }

        /* Estado de la impresora Zebra + switch de impresión automática. */
        .cd-printer-bar {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .cd-printer-estado {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 20px;
            background: #1a1d23;
            border: 2px solid #495057;
        }

        .cd-printer-estado .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #98a6ad;
            flex: 0 0 auto;
        }

        .cd-printer-estado.ok { border-color: #3bd671; }
        .cd-printer-estado.ok .dot { background: #3bd671; }
        .cd-printer-estado.buscando .dot { background: #ffc93b; }
        .cd-printer-estado.error { border-color: #ff5c5c; }
        .cd-printer-estado.error .dot { background: #ff5c5c; }

        .cd-printer-bar button {
            font-size: 13px;
            padding: 4px 10px;
        }

        /* Toggle real (Bootstrap .form-switch) en vez de un checkbox chico
           feo de leer/tocar en una pantalla que se opera de lejos/rápido. */
        .cd-print-switch {
            display: flex;
            align-items: center;
            margin: 0;
            padding-left: 2.75em;
        }

        .cd-print-switch .form-check-input {
            width: 2.75em;
            height: 1.5em;
            margin-left: -2.75em;
            cursor: pointer;
        }

        .cd-print-switch .form-check-label {
            margin-left: 10px;
            cursor: pointer;
            color: #f1f3f5;
        }

        /* Modal de recorrido (menú + pendientes + control) — mismo tema
           oscuro que el resto de la pantalla (el modal de Bootstrap por
           default sale blanco, y acá todo el resto es un KDS oscuro). */
        #cd_modal_recorrido .modal-content {
            background: #1a1d23;
            color: #f1f3f5;
            border: 1px solid #343a40;
            transition: background-color .25s ease;
        }

        #cd_modal_recorrido .modal-header {
            border-bottom-color: #343a40;
        }

        #cd_modal_recorrido .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        #cd_modal_recorrido table {
            color: #f1f3f5;
            margin-bottom: 0;
        }

        #cd_modal_recorrido table thead th {
            border-color: #343a40;
            opacity: .7;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 600;
        }

        #cd_modal_recorrido table td {
            border-color: #262a31;
            vertical-align: middle;
        }

        #cd_modal_recorrido .cd-modal-codigo {
            font-family: 'Courier New', monospace;
            font-weight: 700;
        }

        /* Bootstrap pinta .text-muted gris pensado para fondo blanco - acá
           contra el negro del modal quedaba casi invisible. */
        #cd_modal_recorrido .text-muted {
            color: #c8cdd3 !important;
        }

        #cd_modal_recorrido .cd-modal-vacio {
            text-align: center;
            opacity: .7;
            padding: 30px 0;
        }

        /* Menú de 3 opciones, una al lado de la otra — grandes, para tocar
           sin apuntar fino. En pantallas angostas se apilan solas (flex-wrap). */
        .cd-menu-botones {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 12px;
        }

        .cd-menu-btn {
            display: flex;
            flex: 1 1 160px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 22px 16px;
            border-radius: 12px;
            border: 2px solid #495057;
            background: #23262d;
            color: #f1f3f5;
            font-size: 16px;
            font-weight: 700;
            text-align: center;
        }

        .cd-menu-btn:hover {
            background: #2b2f38;
            border-color: #6c757d;
        }

        .cd-menu-btn .cd-menu-btn-icono {
            font-size: 32px;
            flex: 0 0 auto;
        }

        .cd-menu-btn:disabled {
            opacity: .6;
        }

        /* "Volver": tiene que notarse a simple vista contra el fondo bien
           oscuro del modal — nada de outline sutil que se pierda. */
        .cd-modal-volver {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 9px;
            border: 2px solid #6c757d;
            background: #2b2f38;
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .cd-modal-volver:hover {
            background: #3a3f4a;
            border-color: #98a6ad;
            color: #ffffff !important;
        }

        /* Control de recorrido: el operador está lejos de la pantalla
           escaneando, no cerca del mouse - el feedback tiene que ser
           GIGANTE y de color (nada de sonido: no hay parlante ahí, y nada
           de texto chico: no se lee de lejos). */
        .cd-control-view {
            text-align: center;
        }

        .cd-control-header {
            font-size: 15px;
            opacity: .7;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .cd-control-contador {
            font-size: 64px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 6px;
        }

        .cd-control-contador .cd-control-de {
            font-size: 28px;
            opacity: .5;
            font-weight: 400;
            margin: 0 6px;
        }

        .cd-control-input {
            width: 100%;
            font-size: 20px;
            padding: 12px 16px;
            border-radius: 10px;
            border: 3px solid #495057;
            background: #0f1115;
            color: #f1f3f5;
            text-align: center;
            margin: 14px 0;
        }

        .cd-control-input:focus {
            outline: none;
            border-color: #3bd671;
        }

        /* Panel de flash — ocupa el lugar del último resultado, cambia de
           color entero (no solo un texto) unos segundos así se nota desde
           lejos. Gris = esperando el próximo escaneo. */
        .cd-control-flash {
            border-radius: 14px;
            padding: 26px 20px;
            min-height: 110px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #2a2e35;
            border: 3px solid #495057;
            transition: background-color .15s ease, border-color .15s ease;
        }

        .cd-control-flash .cd-control-flash-icono {
            font-size: 40px;
            line-height: 1;
        }

        .cd-control-flash .cd-control-flash-codigo {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 20px;
        }

        .cd-control-flash .cd-control-flash-msg {
            font-size: 14px;
            opacity: .85;
        }

        .cd-control-flash.ok {
            background: #123322;
            border-color: #3bd671;
        }

        .cd-control-flash.err {
            background: #3a1414;
            border-color: #ff5c5c;
        }

        /* Cuando llega al total: la tarjeta ENTERA del modal se pone verde,
           no solo el panel de flash - máxima visibilidad desde lejos. */
        #cd_modal_recorrido.cd-control-completo .modal-content {
            background: #123322;
            border-color: #3bd671;
        }

        .cd-control-completo-msg {
            font-size: 15px;
            opacity: .85;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>

        <div class="content-page">
            <div class="content">
                <div class="cd-wrap">

                    <div class="cd-sticky-header">
                        <div class="cd-printer-bar">
                            <span class="cd-printer-estado buscando" id="cd_printer_estado">
                                <span class="dot"></span>
                                <span id="cd_printer_estado_txt">Impresora: buscando…</span>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-light" id="cd_printer_reintentar">Conectar / reintentar</button>
                            <div class="form-check form-switch cd-print-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="cd_print_switch">
                                <label class="form-check-label" for="cd_print_switch">Imprimir rótulo al escanear</label>
                            </div>
                        </div>
                        <div class="cd-input-bar">
                            <input type="text" id="cd_input" placeholder="Escaneá una etiqueta (Ferniplast / Mercado Libre / Caddy / IGALFER)..." autocomplete="off">
                            <div class="cd-counters">
                                <span class="badge bg-success" id="cd_cnt_ok">OK: 0</span>
                                <span class="badge bg-warning text-dark" id="cd_cnt_dup">Reingresos: 0</span>
                                <span class="badge bg-danger" id="cd_cnt_err">Sin match: 0</span>
                            </div>
                        </div>

                        <div id="cd_ultimo">
                            <div class="cd-estado">Esperando escaneo…</div>
                            <div class="cd-codigo">—</div>
                        </div>
                    </div>

                    <div class="cd-titulo">Recorridos — paquetes ingresados hoy</div>
                    <div class="cd-rec-grid" id="cd_rec_grid"></div>

                    <div class="cd-titulo">Últimos escaneados (detalle)</div>
                    <div class="cd-feed" id="cd_feed"></div>

                    <!-- Modal de recorrido: se abre al tocar una tarjeta - arranca en un
                         menú de 3 acciones (imprimir / ver pendientes / controlar), y
                         cambia el contenido del body según cuál se elija. -->
                    <div class="modal fade" id="cd_modal_recorrido" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Recorrido <span id="cd_modal_rec_num"></span> <span id="cd_modal_rec_nombre" class="text-muted"></span></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="cd_modal_body"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- content -->
                <div id="menuhyper_footer"></div>
            </div>
        </div>
        <!-- END wrapper -->

        <!-- Vendor js -->
        <script src="../hyper/dist/assets/js/vendor.min.js"></script>
        <!-- App js -->
        <script src="../hyper/dist/assets/js/app.js"></script>

        <!-- SDK de Zebra BrowserPrint (mismo vendorizado que ya usa Ticket/zebra/rotulo.html
             en este mismo repo) — corre un servicio local en la PC del operador; si no está
             instalado/corriendo, getDefaultDevice tira error y el estado queda en rojo. -->
        <script src="../Ticket/zebra/BrowserPrint-3.0.216.min.js"></script>

        <script>
            // Usado solo para el mensaje "Controlado por <usuario> · <hora>"
            // al terminar un control de recorrido — no se manda al server,
            // es puramente informativo en pantalla.
            window.CD_USUARIO = <?php echo json_encode($_SESSION['Usuario'] ?? ''); ?>;
        </script>
        <script src="Proceso/js/crossdocking.js"></script>
        <script src="../Menu/js/funciones.js"></script>
    </div>
</body>

</html>
