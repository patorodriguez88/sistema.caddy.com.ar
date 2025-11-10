<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Usuarios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <link rel="shortcut icon" href="../images/favicon/favicon.ico">
    <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
    <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
</head>

<body class="loading" data-layout="topnav" data-layout-config='{layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
    <div class="wrapper">
        <div class="content-page">
            <div class="content">
                <div class="navbar-custom topnav-navbar" style="z-index:10">
                    <div class="container-fluid">
                        <div id="menuhyper_topnav"></div>
                    </div>
                </div>
                <div class="topnav">
                    <div class="container-fluid">
                        <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                            <div class="collapse navbar-collapse" id="topnav-menu-content">
                                <div id="menuhyper"></div>
                            </div>
                        </nav>
                    </div>
                </div>

                <div class="content container-fluid">
                    <h3 class="mt-3">ABM de Usuarios, Roles y Permisos</h3>

                    <div class="row mt-4">
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-sm-3 mb-2 mb-sm-0">
                                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                        <a class="nav-link active" id="v-pills-roles-tab" data-toggle="pill" href="#v-pills-roles" role="tab" aria-controls="v-pills-roles" aria-selected="true">Roles</a>
                                        <a class="nav-link" id="v-pills-permisos-tab" data-toggle="pill" href="#v-pills-permisos" role="tab" aria-controls="v-pills-permisos" aria-selected="false">Permisos</a>
                                        <a class="nav-link" id="v-pills-asignacion-tab" data-toggle="pill" href="#v-pills-asignacion" role="tab" aria-controls="v-pills-asignacion" aria-selected="false">Asignación de Permisos</a>
                                        <a class="nav-link" id="v-pills-permisos-asignados-tab" data-toggle="pill" href="#v-pills-permisos-asignados" role="tab" aria-controls="v-pills-permisos-asignados" aria-selected="false">Ver Permisos Asignados</a>

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
            </div> <!-- end content-page -->
        </div> <!-- end wrapper -->

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/app.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.keyTable.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="../Menu/js/funciones.js"></script>
        <script src="Procesos/js/usuarios.js"></script>
</body>

</html>