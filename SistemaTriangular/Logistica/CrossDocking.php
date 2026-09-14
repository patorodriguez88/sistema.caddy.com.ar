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
            padding-top: 20px;
            padding-bottom: 14px;
            box-shadow: 0 10px 20px -6px rgba(0, 0, 0, .55);
        }

        .cd-input-bar {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }

        .cd-input-bar input[type=text] {
            flex: 1;
            font-size: 24px;
            padding: 14px 20px;
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
           demás es secundario al lado. */
        #cd_ultimo {
            border-radius: 18px;
            padding: 22px 30px;
            display: flex;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
            min-height: 150px;
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
            padding: 10px 30px;
            text-align: center;
            min-width: 190px;
        }

        #cd_ultimo .cd-rec-chip .cd-rec-label {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: .85;
            font-weight: 600;
        }

        #cd_ultimo .cd-rec-chip .cd-rec-numero {
            font-size: 76px;
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
            padding: 10px 26px;
            text-align: center;
            min-width: 150px;
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
            font-size: 56px;
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
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 16px;
        }

        .cd-rec-card {
            border-radius: 14px;
            background: #1a1d23;
            border-top: 10px solid #495057;
            padding: 16px 18px;
            transition: transform .15s ease;
        }

        .cd-rec-card.cd-rec-card-nuevo {
            animation: cdPulso .6s ease;
        }

        @keyframes cdPulso {
            0% { transform: scale(1.04); }
            100% { transform: scale(1); }
        }

        .cd-rec-card .cd-rec-card-num {
            font-size: 26px;
            font-weight: 800;
        }

        .cd-rec-card .cd-rec-card-nombre {
            font-size: 13px;
            opacity: .65;
            min-height: 34px;
            margin-bottom: 6px;
        }

        .cd-rec-card .cd-rec-card-cant {
            font-size: 58px;
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
            font-size: 46px;
            font-weight: 800;
            color: #3bd671;
            margin-left: 14px;
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
            margin-bottom: 16px;
            font-size: 14px;
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

        <script src="Proceso/js/crossdocking.js"></script>
        <script src="../Menu/js/funciones.js"></script>
    </div>
</body>

</html>
