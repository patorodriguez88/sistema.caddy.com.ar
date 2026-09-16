<?php
// Mismo patrón de auth que CrossDocking.php/Wepoint.php (ver comentario ahí).
include_once "../Conexion/Conexioni.php";
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Etiquetas por Recorrido</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Impresión de etiquetas/rótulos por recorrido para el operador de WePoint" name="description" />
    <meta content="Coderthemes" name="author" />
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

    <style>
        /* Mismo lenguaje visual "KDS" oscuro que CrossDocking.php (se usa en
           el mismo depósito, por el mismo operador). */
        body {
            background: #0f1115;
        }

        .er-wrap {
            min-height: 100vh;
            padding: 0 24px 24px;
            color: #f1f3f5;
        }

        .er-sticky-header {
            position: sticky;
            top: 0;
            z-index: 60;
            background: #0f1115;
            padding-top: 12px;
            padding-bottom: 10px;
            box-shadow: 0 10px 20px -6px rgba(0, 0, 0, .55);
        }

        .er-printer-bar {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .er-printer-estado {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 20px;
            background: #1a1d23;
            border: 2px solid #495057;
            font-size: 14px;
        }

        .er-printer-estado .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #6c757d;
        }

        .er-printer-estado.buscando .dot {
            background: #ffc107;
        }

        .er-printer-estado.ok .dot {
            background: #3bd671;
        }

        .er-printer-estado.error .dot {
            background: #fa5c7c;
        }

        .er-tipo-select {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .er-tipo-select select {
            background: #1a1d23;
            color: #f1f3f5;
            border: 2px solid #495057;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 14px;
        }

        .er-titulo {
            font-size: 20px;
            font-weight: 700;
            margin: 18px 0 10px;
            color: #f1f3f5;
        }

        .er-card {
            background: #1a1d23;
            border: 2px solid #343a40;
            border-radius: 14px;
            padding: 6px 4px;
        }

        .er-card table {
            color: #f1f3f5;
            margin-bottom: 0;
        }

        .er-card table thead th {
            border-bottom: 2px solid #343a40;
            color: #adb5bd;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .er-card table tbody tr {
            border-bottom: 1px solid #2a2e35;
        }

        .er-card table tbody tr:hover {
            background: rgba(255, 255, 255, .03);
        }

        .er-rec-dot {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            vertical-align: middle;
            margin-right: 6px;
        }

        .er-cantidad-input {
            background: #0f1115 !important;
            color: #f1f3f5 !important;
            border: 2px solid #495057 !important;
        }

        .er-cantidad-input:focus {
            border-color: #3bd671 !important;
            box-shadow: 0 0 0 3px rgba(59, 214, 113, .25) !important;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>

        <div class="content-page">
            <div class="content">
                <div class="er-wrap">

                    <div class="er-sticky-header">
                        <div class="er-printer-bar">
                            <span class="er-printer-estado buscando" id="er_printer_estado">
                                <span class="dot"></span>
                                <span id="er_printer_estado_txt">Impresora: buscando…</span>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-light" id="er_printer_reintentar">Conectar / reintentar</button>

                            <div class="er-tipo-select">
                                <label for="er_tipo_etiqueta" class="mb-0">Imprimir como:</label>
                                <select id="er_tipo_etiqueta">
                                    <option value="rotulo">Rótulo (chico)</option>
                                    <option value="etiqueta" selected>Etiqueta (grande, más info)</option>
                                </select>
                            </div>

                            <!-- FIX (a pedido, 2026-09-16): filtro "Solo origen Dinter" -
                                 recorridos sin ningún paquete de origen Dinter quedan afuera
                                 de la lista, y dentro de un recorrido solo se ven (e imprimen)
                                 sus paquetes Dinter. Checkbox para poder volver a ver todo
                                 cuando haga falta (no es exclusivo de Dinter para siempre). -->
                            <div class="form-check form-check-inline" style="margin-left:4px">
                                <input class="form-check-input" type="checkbox" id="er_solo_dinter" checked>
                                <label class="form-check-label" for="er_solo_dinter">Solo origen Dinter</label>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-light ms-auto" id="er_actualizar"><i class="mdi mdi-refresh"></i> Actualizar</button>
                        </div>
                    </div>

                    <div class="er-titulo">Recorridos con paquetes pendientes</div>
                    <div class="er-card">
                        <table class="table table-borderless mb-0" id="er_rec_tabla">
                            <thead>
                                <tr>
                                    <th>Recorrido</th>
                                    <th>Nombre</th>
                                    <th class="text-center">Paquetes</th>
                                    <th class="text-center">Bultos</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Cargando…</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- FIX (a pedido: "ver paquetes que me abra un modal, no me lleves
                         abajo") - antes era una sección que aparecía debajo de la tabla
                         de recorridos con scroll automático; ahora es un modal. -->
                    <div class="modal fade" id="er_paq_modal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content" style="background:#1a1d23;color:#f1f3f5">
                                <div class="modal-header" style="border-color:#343a40">
                                    <h5 class="modal-title" id="er_paq_titulo">Paquetes del recorrido</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="er-card">
                                        <table class="table table-borderless mb-0" id="er_paq_tabla">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Origen</th>
                                                    <th>Destino</th>
                                                    <th>Localidad</th>
                                                    <th>Cantidad</th>
                                                    <th>Impreso</th>
                                                    <th class="text-end">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- REPOSICIONES DINTER (a pedido, 2026-09-16): Dinter a veces
                         avisa DESPUÉS de que ya se imprimieron las etiquetas de un
                         envío que hay que sumarle más bultos al mismo pedido, en
                         vez de generar un servicio nuevo. Acá se carga cuánto se
                         suma por paquete y se imprime YA MISMO solo lo nuevo,
                         marcado "REPO" para no confundirlo con el envío original. -->
                    <div class="modal fade" id="er_repo_modal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content" style="background:#1a1d23;color:#f1f3f5">
                                <div class="modal-header" style="border-color:#343a40">
                                    <h5 class="modal-title" id="er_repo_titulo">Reposiciones del recorrido</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="text-muted small mb-3">
                                        Cargá cuántos bultos nuevos mandó Dinter para cada paquete y tocá "Agregar e imprimir" -
                                        se suma a la cantidad real del envío y se imprimen solo las etiquetas nuevas, marcadas <b>REPO</b>.
                                    </p>
                                    <div class="er-card">
                                        <table class="table table-borderless mb-0" id="er_repo_tabla">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Destino</th>
                                                    <th class="text-center">Cantidad actual</th>
                                                    <th class="text-center">Cantidad repo</th>
                                                    <th class="text-end">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/assets/js/app.js"></script>
    <script src="../Ticket/zebra/BrowserPrint-3.0.216.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>
    <script src="Proceso/js/etiquetas_recorrido.js"></script>
    <!-- FIX (a pedido, 2026-09-16 - "el nombre de Diego desaparece, ya nos
         había pasado en CrossDocking"): esta pantalla nunca cargaba
         Menu/js/funciones.js, que es justo el script que rellena el
         nombre/avatar/sucursal del usuario logueado en el header (Menu/
         head.html los deja vacíos / con el placeholder "PR" a propósito,
         esperando que este script los complete por AJAX). Sin este script
         quedaban vacíos para siempre en esta pantalla. -->
    <script src="../Menu/js/funciones.js"></script>
</body>

</html>
