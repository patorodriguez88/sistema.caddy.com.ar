<?php
// Admin > Cheques: listado de cheques de terceros (recibidos de clientes) y
// propios (emitidos a proveedores). Reemplaza a la pantalla vieja, que usaba
// mysql_* (no existe en PHP 8) y daba error al entrar.
include_once "../Conexion/Conexioni.php";
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Cheques</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema Caddy" name="author" />

    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">

    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="wrapper">
        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid mt-3">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
                                        <li class="breadcrumb-item active">Cheques</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Cheques</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <ul class="nav nav-tabs nav-bordered mb-3">
                                        <li class="nav-item">
                                            <a href="#" class="nav-link active" data-terceros="1">De terceros</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#" class="nav-link" data-terceros="0">Propios</a>
                                        </li>
                                    </ul>

                                    <div class="row align-items-end mb-3">
                                        <div class="col-auto">
                                            <label class="form-label">Estado</label>
                                            <select id="ch-estado" class="form-select"></select>
                                        </div>
                                        <div class="col-auto ms-auto" id="ch-totales"></div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="tabla-cheques" class="table table-striped dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Fecha de cobro</th>
                                                    <th>Banco</th>
                                                    <th>N° de cheque</th>
                                                    <th id="ch-col-proveedor">Recibido de / Entregado a</th>
                                                    <th>Importe</th>
                                                    <th>Estado</th>
                                                    <th>Asiento</th>
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
            <div id="menuhyper_footer"></div>
        </div>
    </div>

    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/assets/js/app.js"></script>
    <?php include '../Menu/php/script_datatables.php'; ?>
    <script src="../Menu/js/funciones.js"></script>
    <script src="Procesos/js/cheques.js?v=20261002a"></script>
</body>

</html>
