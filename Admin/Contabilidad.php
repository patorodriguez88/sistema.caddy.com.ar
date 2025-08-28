<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Contabilidad</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
      
        <!-- App favicon -->
        <link rel="shortcut icon" href="../images/favicon/favicon.ico">

        <!-- third party css -->
        <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
        <!-- third party css end -->

        <!-- App css -->
        <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
        <link href="../hyper/dist/saas/assets/vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />

      
    </head>
    <style>
    .is-valid {
        border-color: #28a745 !important;
    }

    .is-invalid {
        border-color: #dc3545 !important;
    }
    </style>
    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false}'>
        <!-- Begin page -->
        <div class="wrapper">

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">
                    <!-- Topbar Start -->
                    <div class="navbar-custom topnav-navbar" style="z-index:10">
                        <div class="container-fluid">
                          <div id="menuhyper_topnav"></div>
                        </div>
                    </div>
                    <!-- end Topbar -->
                         <div class="topnav">
                        <div class="container-fluid">
                            <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                                <div class="collapse navbar-collapse" id="topnav-menu-content">
                                  <div id="menuhyper"></div>
                                </div>
                            </nav>
                        </div>
                    </div>
                    <!-- Start Content-->
                    <div class="container-fluid">
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Sistema Caddy</a></li>
                                            <li class="breadcrumb-item active">Administracion</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Contabilidad</h4>
                              </div>
                            </div>
                        </div>     
              <!-- DESDE ACA -->

        			  <div class="row">
                            <div class="col-xl-3 col-lg-6 order-lg-1 order-xl-1">
                                <!-- start profile info -->
                                <div class="card">
                                    <div class="card-body">
                                        <div class="dropdown float-right">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-horizontal"></i>
                                            </a>
                                        </div>

                                        <div class="media">
                                            <div class="media-body">
                                                <p class="mb-1 mt-1 text-muted">Menu Asientos Contables</p>
                                            </div>
                                        </div>

                                        <div class="list-group list-group-flush mt-2">
                                            <a class="list-group-item list-group-item-action text-primary border-0" id="btn_nuevo_asiento" style="cursor: pointer;"><i class="uil uil-images mr-1"></i> Crear Asiento Contable</a>
                                            <a class="list-group-item list-group-item-action border-0" id="btn_modificar_asiento" style="cursor: pointer;"><i class="uil uil-comment-alt-message mr-1"></i> Modificar Asiento Contable</a>
                                            <a class="list-group-item list-group-item-action border-0" style="cursor: pointer;" id="btn_consulta_asiento"><i class="uil uil-calendar-alt mr-1"></i> Consulta Asiento Contable</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- end profile info -->

                                <!-- event info -->
                                <div class="card">
                                    <div class="card-body p-2">
									<div class="media">
                                            <div class="media-body">
                                                <p class="mb-1 mt-1 text-muted">Menu Informes</p>
                                            </div>
                                        </div>
                                        <div class="list-group list-group-flush my-2">
                                            <a class="list-group-item list-group-item-action border-0" id="btn_libro_diario" style="cursor: pointer;"><i class="uil uil-calendar-alt mr-1"></i> Libro Diario</a>
                                            <a class="list-group-item list-group-item-action border-0" id="btn_sumas_y_saldos" style="cursor: pointer;"><i class="uil uil-calender mr-1"></i> Sumas y Saldos</a>
                                            <a class="list-group-item list-group-item-action border-0" id="btn_libros_contables" style="cursor: pointer;"><i class="uil uil-bookmark mr-1"></i> Mayores</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- end event info -->
                            </div> <!-- end col -->


                            <!-- NUEVO ASIENTO CONTABLE -->
                            <div class="col-xl-9 col-lg-12 order-lg-2 order-xl-1">

                            <div id="card_buscar_asiento" class="card" style="display:none">
                                <div class="card-body">                                        
                                    <div class="tab-content">
                                        <div class="tab-pane show active p-3">
                                        <div class="row mb-3">
                                            <div class="col-auto">
                                                <label for="buscar_asiento">Buscar Asiento N°</label>
                                                <input type="number" class="form-control" id="buscar_asiento" placeholder="Ej: 1234">
                                            </div>
                                            <div class="col-auto align-self-end">
                                            <button id="btnBuscar" class="btn btn-info">Buscar Asiento</button>
                                        </div>
                                    </div>
                                </div>
                              </div>
                                </div> 
                            </div>
                                <!-- new post -->
                                <div id="card_nuevo_asiento" class="card">
                                    <div class="card-body p-0">                                        
                                        <div class="tab-content">
                                            <div class="tab-pane show active p-3" id="newpost">
                                                <h5 id="titulo_asiento" class="mb-3">Nuevo Asiento Contable</h5>
                                                <!-- comment box -->
                                                <form id="asientoForm">
                                                    <!-- <div id="campos-container"> -->
                                                        <div id="cabecera-asiento" class="row mb-2 align-items-end">
                                                                <div class="col-auto">
                                                                    <label for="n_asiento">Número de Asiento</label>
                                                                    <input class="form-control" id="n_asiento" name="n_asiento" blocked>
                                                                </div>

                                                                <div class="col-auto">
                                                                    <label for="example-date" class="form-label">Fecha</label>
                                                                    <input class="form-control" id="example-date" type="date" name="date">
                                                                </div>
                                                            </div>
                                                        <div id="campos-container">                 
                                                            <!-- </div> -->
                                                            <div class="row mb-2">
                                                                <!-- <div class="col-3"> -->
                                                                <!-- <input type="hidden" name="id[]" value=""> -->
                                                                    <!-- <input type="text" class="form-control" name="nombreCuenta[]" hidden> -->
                                                                <!-- </div> -->
                                                                <div class="col-7">
                                                                    <select class="form-control" name="cuenta[]">
                                                                        <option value="">Seleccione una cuenta</option>
                                                                    </select>
                                                                </div>
                                                                <input type="hidden" name="id[]" value="">
                                                            <input type="number" class="form-control" name="nasiento" id="nasiento" hidden>
                                                            <div class="col"><input type="number" class="form-control" name="debe[]" placeholder="Debe" step="0.01" ></div>
                                                            <div class="col"><input type="number" class="form-control" name="haber[]" placeholder="Haber" step="0.01"></div>                                                            
                                                            <div class="col">
                                                                <i onclick="eliminarCampo(this)" class="mdi mdi-24px mdi-trash-can text-danger"></i>
                                                                <i onclick="agregarCampo()" class="mdi mdi-24px mdi-plus text-primary"></i>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                <div class="row justify-content-end mt-3">
                                                    <div class="col-auto text-right">
                                                        <label for="total_asiento">Total del Asiento (Debe - Haber)</label>
                                                        <input type="text" id="total_asiento" class="form-control text-right" readonly>
                                                    </div>
                                                </div>

                                                    <div class="comment-area-box mt-3 border rounded">
                                                        <textarea id="observaciones" name="observaciones" rows="4" class="form-control border-0 resize-none" placeholder="Observaciones...."></textarea>
                                                    </div>
                                                        <div class="d-flex justify-content-end mt-3">
                                                            <button id="btnAceptar" type="button" class="btn btn-success" onclick="confirmarAsiento()">
                                                                <i class="uil uil-message mr-1"></i>Confirmar Asiento
                                                            </button>
                                                        </div>
                                                        <div id="mensaje" class="mt-3"></div>

                                                    </form>
                                                </div> <!-- end .border-->
                                                <!-- end comment box -->
                                            </div> <!-- end preview-->
                                        </div> <!-- end tab-content-->
                                    </div>
                                    <div id="card_informes" class="card" style="display:none;">
                                    <div class="card-body p-0">                                        
                                        <div class="tab-content">
                                            <div class="tab-pane show active p-3">
                                                <h5 class="mb-3">Libro Diario</h5>
                                                <div class="row">
                                                    <div class="col-md-3 mb-2">
                                                        <label for="date-informes" class="form-label">Desde</label>
                                                        <input class="form-control" id="date-informes" type="date" name="date">
                                                    </div>
                                                </div>
                                                        <div id="div_tabla_informes">                                          
                                                            <table id="tabla-informes" class="table table-sm dt-responsive nowrap w-100">
                                                                <thead>
                                                                    <tr>
                                                                        <th>NAsiento</th>
                                                                        <th>Fecha</th>
                                                                        <th>Cuenta</th>
                                                                        <th>Nombre Cuenta</th>
                                                                        <th>Debe</th>
                                                                        <th>Haber</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th colspan="4" class="text-right">Totales:</th>
                                                                        <th id="total-debe"></th>
                                                                        <th id="total-haber"></th>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="card_informes_sumas_y_saldos" class="card" style="display:none;">
                                    <div class="card-body p-0">                                        
                                        <div class="tab-content">
                                            <div class="tab-pane show active p-3">
                                                <h5  class="mb-3">Sumas y Saldos</h5>
                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <label for="date-informes-desde" class="form-label">Desde</label>
                                                        <input class="form-control" id="date-informes-desde" type="date" name="date">
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <label for="date-informes-hasta" class="form-label">Hasta</label>
                                                        <input class="form-control" id="date-informes-hasta" type="date" name="date">
                                                    </div>       
                                                    <div class="col-md-3 offset-md-9 mb-2">
                                                        <button id="btn_sumas_y_saldos_buscar" class="btn btn-primary btn-block">Buscar</button>
                                                    </div>
                                                </div>
                                                       
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- LIBROS MAYORES CONTABLES -->
                                    <div class="card" id="visor_pdf" style="display:none;">
                                        <div class="card-body">
                                            <h5 id="titulo_informes" class="card-title">Informe PDF</h5>
                                            <iframe id="iframe_pdf" src="" width="100%" height="600px" style="border: none;"></iframe>
                                        </div>
                                    </div>

                                    <div id="card_busqueda_asientos" class="card" style="display:none;">
                                        <div class="card-body">
                                            <h5 class="card-title">Consulta de Asientos Contables</h5>
                                            <form id="form_busqueda_asientos">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                                <input type="date" id="fecha_desde" name="fecha_desde" class="form-control">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                                <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-control">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                <label for="cuenta_desde" class="form-label">Cuenta Desde</label>
                                                <select id="cuenta_desde" name="cuenta_desde" class="form-control">
                                                    <!-- Opciones dinámicas -->
                                                </select>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                <label for="cuenta_hasta" class="form-label">Cuenta Hasta</label>
                                                <select id="cuenta_hasta" name="cuenta_hasta" class="form-control">
                                                    <!-- Opciones dinámicas -->
                                                </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3 offset-md-9 mb-2">
                                                <button type="submit" id="btn_buscar_asientos" class="btn btn-primary">Buscar</button>
                                            </div>
                                            </form>
                                        </div>
                                        </div>

                                        <!-- Resultado -->
                                        <div class="card">
                                            <div class="card-body">
                                            <div id="resultado_asientos" class="mt-4" style="display:none;">
                                            <h5 id="titulo_resultado_asientos">Resultados</h5>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered" id="tabla_resultado_asientos">
                                                    <thead>
                                                        <tr>
                                                        <th>Fecha</th>
                                                        <th>Número Asiento</th>
                                                        <th>Cuenta</th>
                                                        <th>Nombre Cuenta</th>
                                                        <th>Debe</th>
                                                        <th>Haber</th>
                                                        <th>Observaciones</th>
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
                                <!-- end new post -->


							<!-- HASTA ACA  -->

                <!-- Footer Start -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <script>document.write(new Date().getFullYear())</script> © Sistema - Caddy
                            </div>
                            <div class="col-md-6">
                                <div class="text-md-right footer-links d-none d-md-block">
                                    <a href="javascript: void(0);">About</a>
                                    <a href="javascript: void(0);">Support</a>
                                    <a href="javascript: void(0);">Contact Us</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- end Footer -->

            </div>
		<script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/app.min.js"></script>

        <!-- third party js -->
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
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script> -->
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script> -->
        <script src="../hyper/dist/saas/assets/js/vendor/jszip.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/pdfmake.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/vfs_fonts.js"></script>
        <!-- third party js ends -->
        
        <!-- funciones -->
        <script src="Procesos/js/contabilidad.js"></script>        
        <script src="../Menu/js/funciones.js"></script>
  </body>
</html>