<?php
include_once "../Conexion/Conexioni.php";
require_once "../Menu/php/permisos_menu.php";
$puedeGestionarRoles = usuarioPuedeGestionarRoles($mysqli);
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Usuarios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <!-- Plugin css -->
    <link href="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css">

    <!-- Datatables css -->
    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <!-- For checkbox Select-->
    <link href="../hyper/dist/assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <!-- For Buttons -->
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <!-- Fixe header-->
    <link href="../hyper/dist/assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css">

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
        .caddy-rol-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .55rem;
            border-radius: 6px;
            background: rgba(226, 79, 48, .1);
            color: #E24F30;
            font-weight: 600;
            font-size: .72rem;
            white-space: nowrap;
        }

        .caddy-rol-badge.sin-rol {
            background: rgba(152, 166, 173, .15);
            color: #6c757d;
        }

        .caddy-nav-card .card-body {
            padding: .6rem;
        }

        #v-pills-tab .nav-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            text-align: left;
            border-radius: 8px;
            padding: .6rem .7rem;
            margin-bottom: 2px;
            color: #6c757d;
            font-weight: 500;
            font-size: .875rem;
            border-left: 3px solid transparent;
            transition: background-color .15s ease, color .15s ease;
        }

        #v-pills-tab .nav-link:last-child {
            margin-bottom: 0;
        }

        #v-pills-tab .caddy-nav-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 30px;
            height: 30px;
            border-radius: 7px;
            background: rgba(152, 166, 173, .14);
            color: #98a6ad;
            font-size: 1rem;
            transition: background-color .15s ease, color .15s ease;
        }

        #v-pills-tab .nav-link:hover {
            background-color: rgba(226, 79, 48, .06);
            color: #E24F30;
        }

        #v-pills-tab .nav-link:hover .caddy-nav-icon {
            background: rgba(226, 79, 48, .14);
            color: #E24F30;
        }

        #v-pills-tab .nav-link.active {
            background-color: rgba(226, 79, 48, .1);
            color: #E24F30;
            border-left-color: #E24F30;
            font-weight: 700;
        }

        #v-pills-tab .nav-link.active .caddy-nav-icon {
            background: #E24F30;
            color: #fff;
        }

        .caddy-nav-lock {
            display: flex;
            gap: .6rem;
            align-items: flex-start;
            background: rgba(152, 166, 173, .1);
            border-radius: 8px;
            padding: .75rem;
            margin-top: .75rem;
            font-size: .8rem;
            color: #6c757d;
        }

        .caddy-nav-lock i {
            font-size: 1.05rem;
            color: #98a6ad;
            flex-shrink: 0;
        }

        .btn-caddy {
            background-color: #E24F30;
            border-color: #E24F30;
            color: #fff;
        }

        .btn-caddy:hover,
        .btn-caddy:focus {
            background-color: #c9432a;
            border-color: #c9432a;
            color: #fff;
        }

        .caddy-permiso-check {
            border: 1px solid var(--ct-border-color, #e3eaef);
            border-radius: 6px;
            padding: .5rem .75rem .5rem 2.25rem;
        }

        .caddy-permiso-seccion {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #98a6ad;
            margin: 1rem 0 .4rem;
        }

        /* Botón "Reenviar acceso" — cápsula sutil, se vuelve el color principal
           (call to action) cuando la persona todavía no confirmó el primer ingreso. */
        .caddy-btn-reenviar {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .22rem .6rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 600;
            line-height: 1.2;
            border: 1px solid rgba(226, 79, 48, .3);
            background: rgba(226, 79, 48, .06);
            color: #E24F30;
            white-space: nowrap;
            transition: background-color .15s ease, color .15s ease, border-color .15s ease,
                box-shadow .15s ease, transform .1s ease;
        }

        .caddy-btn-reenviar i {
            font-size: .82rem;
            line-height: 1;
        }

        .caddy-btn-reenviar:hover:not(:disabled) {
            background: #E24F30;
            border-color: #E24F30;
            color: #fff;
            box-shadow: 0 4px 10px rgba(226, 79, 48, .25);
        }

        .caddy-btn-reenviar:active:not(:disabled) {
            transform: scale(.96);
        }

        .caddy-btn-reenviar:disabled {
            opacity: .65;
            cursor: not-allowed;
        }

        .caddy-btn-reenviar.is-pendiente {
            background: #E24F30;
            border-color: #E24F30;
            color: #fff;
            box-shadow: 0 2px 6px rgba(226, 79, 48, .3);
        }

        .caddy-btn-reenviar.is-pendiente:hover:not(:disabled) {
            background: #c9432a;
            border-color: #c9432a;
            box-shadow: 0 4px 10px rgba(226, 79, 48, .35);
        }

        .caddy-btn-reenviar i.caddy-spin {
            animation: caddy-spin .7s linear infinite;
        }

        @keyframes caddy-spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .caddy-tabla-vacia {
            text-align: center;
            color: #98a6ad;
            font-size: .85rem;
            padding: 1.5rem .5rem;
        }

        .caddy-tabla-vacia i {
            display: block;
            font-size: 1.4rem;
            margin-bottom: .35rem;
            color: #ced4da;
        }
    </style>
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="text-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Rec. Humanos</a></li>
                                        <li class="breadcrumb-item active">Usuarios y Permisos</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Usuarios, Roles y Permisos</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-3 mb-3 mb-sm-0">
                            <div class="card caddy-nav-card mb-0">
                                <div class="card-body">
                                    <div class="nav nav-pills flex-column" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                        <button class="nav-link active" id="v-pills-usuarios-tab" data-bs-toggle="pill" data-bs-target="#v-pills-usuarios" type="button" role="tab">
                                            <span class="caddy-nav-icon"><i class="uil-users-alt"></i></span> Usuarios
                                        </button>
                                        <?php if ($puedeGestionarRoles): ?>
                                        <button class="nav-link" id="v-pills-roles-tab" data-bs-toggle="pill" data-bs-target="#v-pills-roles" type="button" role="tab">
                                            <span class="caddy-nav-icon"><i class="uil-shield-check"></i></span> Roles
                                        </button>
                                        <button class="nav-link" id="v-pills-permisos-tab" data-bs-toggle="pill" data-bs-target="#v-pills-permisos" type="button" role="tab">
                                            <span class="caddy-nav-icon"><i class="uil-key-skeleton"></i></span> Permisos
                                        </button>
                                        <button class="nav-link" id="v-pills-asignacion-tab" data-bs-toggle="pill" data-bs-target="#v-pills-asignacion" type="button" role="tab">
                                            <span class="caddy-nav-icon"><i class="uil-link-alt"></i></span> Asignación de Permisos
                                        </button>
                                        <?php endif; ?>
                                        <button class="nav-link" id="v-pills-permisos-asignados-tab" data-bs-toggle="pill" data-bs-target="#v-pills-permisos-asignados" type="button" role="tab">
                                            <span class="caddy-nav-icon"><i class="uil-eye"></i></span> Ver Permisos Asignados
                                        </button>
                                    </div>
                                    <?php if (!$puedeGestionarRoles): ?>
                                    <div class="caddy-nav-lock">
                                        <i class="uil-lock-alt"></i>
                                        <span>Crear o editar roles y permisos requiere el rol SuperAdministrador.</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-9">
                            <div class="tab-content" id="v-pills-tabContent">

                                <!-- Usuarios -->
                                <div class="tab-pane fade show active" id="v-pills-usuarios" role="tabpanel">
                                    <?php if ($puedeGestionarRoles): ?>
                                    <div class="card mb-3">
                                        <div class="card-header d-flex align-items-center justify-content-between py-2">
                                            <strong>Asignar rol a un usuario</strong>
                                        </div>
                                        <div class="card-body">
                                            <form id="formAsignar" class="row g-2 align-items-end">
                                                <div class="col-md-5">
                                                    <label class="form-label" for="usuario_select">Usuario</label>
                                                    <select id="usuario_select" class="form-control"></select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label" for="rol_select">Rol</label>
                                                    <select id="rol_select" class="form-control"></select>
                                                </div>
                                                <div class="col-md-3">
                                                    <button type="submit" class="btn btn-caddy btn-sm w-100">Asignar Rol</button>
                                                </div>
                                            </form>
                                            <div class="mt-3" id="rolActualUsuario"></div>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="caddy-nav-lock mb-3">
                                        <i class="uil-lock-alt"></i>
                                        <span>Asignar o quitar el rol de un usuario requiere el rol SuperAdministrador.</span>
                                    </div>
                                    <?php endif; ?>

                                    <div class="card">
                                        <div class="card-header py-2"><strong>Usuarios (Nivel 1, 2 y 7)</strong></div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-centered mb-0" id="tablaUsuarios">
                                                    <thead>
                                                        <tr>
                                                            <th>Nombre</th>
                                                            <th>Nivel</th>
                                                            <th>Rol</th>
                                                            <th>Notificación</th>
                                                            <th>Acceso</th>
                                                            <th title="Permiso independiente del Nivel para borrar pagos (Ctasctes)">Eliminar Pagos</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($puedeGestionarRoles): ?>
                                <!-- Roles -->
                                <div class="tab-pane fade" id="v-pills-roles" role="tabpanel">
                                    <div class="card mb-3">
                                        <div class="card-header py-2"><strong>Crear / Editar Rol</strong></div>
                                        <div class="card-body">
                                            <form id="formRol" class="row g-2 align-items-end">
                                                <input type="hidden" id="rol_id" />
                                                <div class="col-md-8">
                                                    <input type="text" id="rol_nombre" class="form-control" placeholder="Nombre del rol" required />
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="submit" class="btn btn-caddy btn-sm w-100">Guardar Rol</button>
                                                    <button type="button" class="btn btn-light btn-sm w-100 mt-1 d-none" id="btnCancelarEdicionRol">Cancelar edición</button>
                                                </div>
                                            </form>
                                            <table class="table table-sm mt-3" id="tablaRoles">
                                                <thead>
                                                    <tr>
                                                        <th>Rol</th>
                                                        <th class="text-end">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Permisos -->
                                <div class="tab-pane fade" id="v-pills-permisos" role="tabpanel">
                                    <div class="card mb-3">
                                        <div class="card-header py-2"><strong>Permisos</strong></div>
                                        <div class="card-body">
                                            <p class="text-muted font-13">Los permisos con ícono de menú se sincronizan solos desde las opciones reales del sistema. Los permisos manuales los podés agregar acá.</p>
                                            <form id="formPermiso" class="row g-2 align-items-end mb-2">
                                                <div class="col-md-8">
                                                    <input type="text" id="permiso_nombre" class="form-control" placeholder="Nombre del permiso manual" required />
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="submit" class="btn btn-caddy btn-sm w-100">Crear Permiso</button>
                                                </div>
                                            </form>
                                            <table class="table table-sm mt-3" id="tablaPermisos">
                                                <thead>
                                                    <tr>
                                                        <th>Permiso</th>
                                                        <th>Sección</th>
                                                        <th class="text-end">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Asignación de Permisos -->
                                <div class="tab-pane fade" id="v-pills-asignacion" role="tabpanel">
                                    <div class="card mb-3">
                                        <div class="card-header py-2"><strong>Qué puede ver cada rol</strong></div>
                                        <div class="card-body">
                                            <form id="formAsignarPermisos">
                                                <select id="selectRoles" class="form-control mb-2"></select>
                                                <div id="checkboxPermisos" class="mb-2" style="max-height: 340px; overflow-y: auto;"></div>
                                                <button type="submit" class="btn btn-caddy btn-sm">Guardar Permisos del Rol</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Ver Permisos Asignados -->
                                <div class="tab-pane fade" id="v-pills-permisos-asignados" role="tabpanel">
                                    <div class="card mt-3">
                                        <div class="card-header py-2"><strong>Roles y Permisos Asignados</strong></div>
                                        <div class="card-body">
                                            <table id="tablaRolesPermisos" class="table table-striped table-bordered" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Rol</th>
                                                        <th>Permisos</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div> <!-- end tab-content -->
                        </div> <!-- end col-sm-9 -->
                    </div> <!-- end row -->
                </div> <!-- end content container-fluid -->

            </div>
            <!-- content -->

            <!-- Footer Start -->
            <div id="menuhyper_footer"></div>
            <!-- end Footer -->

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="../hyper/dist/assets/js/app.js"></script>

    <!-- Daterangepicker js -->
    <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>
    <script src="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script>

    <!-- Apex Charts js -->
    <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>
    <!-- Dashboard App js -->
    <script src="../hyper/dist/assets/js/pages/demo.dashboard.js"></script>
    <!-- Funciones -->
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="Procesos/js/usuarios.js"></script>
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>
</body>

</html>
