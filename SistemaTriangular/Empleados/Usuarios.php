<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Usuarios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="shortcut icon" href="../images/favicon/apple-icon.png">

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
                    <!-- <div class="content container-fluid"> -->
                    <h3 class="mt-3">ABM de Usuarios, Roles y Permisos</h3>

                    <div class="row mt-4">
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-sm-3 mb-2 mb-sm-0">
                                    <div class="nav nav-pills flex-column" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                        <button class="nav-link active" id="v-pills-roles-tab" data-bs-toggle="pill" data-bs-target="#v-pills-roles" type="button" role="tab" aria-controls="v-pills-roles" aria-selected="true">Roles</button>
                                        <button class="nav-link" id="v-pills-permisos-tab" data-bs-toggle="pill" data-bs-target="#v-pills-permisos" type="button" role="tab" aria-controls="v-pills-permisos" aria-selected="false">Permisos</button>
                                        <button class="nav-link" id="v-pills-asignacion-tab" data-bs-toggle="pill" data-bs-target="#v-pills-asignacion" type="button" role="tab" aria-controls="v-pills-asignacion" aria-selected="false">Asignación de Permisos</button>
                                        <button class="nav-link" id="v-pills-permisos-asignados-tab" data-bs-toggle="pill" data-bs-target="#v-pills-permisos-asignados" type="button" role="tab" aria-controls="v-pills-permisos-asignados" aria-selected="false">Ver Permisos Asignados</button>

                                    </div>
                                </div>

                                <div class="col-sm-9">
                                    <div class="tab-content" id="v-pills-tabContent">
                                        <div class="tab-pane fade show active" id="v-pills-roles" role="tabpanel" aria-labelledby="v-pills-roles-tab">
                                            <div class="card mb-3">
                                                <div class="card-header bg-success text-white"><strong>Asignar Roles a Usuarios</strong></div>
                                                <div class="card-body">
                                                    <form id="formAsignar">
                                                        <select id="usuario_select" class="form-control mb-2"></select>
                                                        <select id="rol_select" class="form-control mb-2"></select>
                                                        <button type="submit" class="btn btn-success btn-sm">Asignar Rol</button>
                                                    </form>
                                                    <ul class="list-group mt-3" id="listaRolesUsuario"></ul>
                                                </div>
                                            </div>

                                            <div class="card mb-3">
                                                <div class="card-header bg-success text-white"><strong>Crear Roles</strong></div>
                                                <div class="card-body">
                                                    <form id="formRol">
                                                        <input type="hidden" id="rol_id" />
                                                        <input type="text" id="rol_nombre" class="form-control mb-2" placeholder="Nombre del rol" required />
                                                        <button type="submit" class="btn btn-success btn-sm">Guardar Rol</button>
                                                    </form>
                                                    <table class="table table-sm mt-3" id="tablaRoles">
                                                        <thead>
                                                            <tr>
                                                                <th>Rol</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="v-pills-permisos" role="tabpanel" aria-labelledby="v-pills-permisos-tab">
                                            <div class="card mb-3">
                                                <div class="card-header bg-warning text-white"><strong>Permisos</strong></div>
                                                <div class="card-body">
                                                    <form id="formPermiso">
                                                        <input type="hidden" id="permiso_id" />
                                                        <input type="text" id="permiso_nombre" class="form-control mb-2" placeholder="Nombre del permiso" required />
                                                        <button type="submit" class="btn btn-warning text-white btn-sm">Crear Permiso</button>
                                                    </form>
                                                    <table class="table table-sm mt-3" id="tablaPermisos">
                                                        <thead>
                                                            <tr>
                                                                <th>Permiso</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="v-pills-asignacion" role="tabpanel" aria-labelledby="v-pills-asignacion-tab">
                                            <div class="card mb-3">
                                                <div class="card-header bg-danger text-white"><strong>Asignar Permisos a Rol</strong></div>
                                                <div class="card-body">
                                                    <form id="formAsignarPermisos">
                                                        <select id="selectRoles" class="form-control mb-2"></select>
                                                        <div id="checkboxPermisos" class="mb-2" style="max-height: 200px; overflow-y: auto;"></div>
                                                        <button type="submit" class="btn btn-danger btn-sm">Asignar Permisos</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="v-pills-permisos-asignados" role="tabpanel" aria-labelledby="v-pills-permisos-asignados-tab">
                                            <div class="card mt-3">
                                                <div class="card-header bg-danger text-white"><strong>Roles y Permisos Asignados</strong></div>
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
                        </div> <!-- end col-md-10 -->
                    </div> <!-- end row -->
                </div> <!-- end content container-fluid -->

                <!-- </div> -->
                <!-- container -->

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