<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Caddy | Plan de Cuentas </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        
        <link rel="shortcut icon" href="../images/favicon/favicon.ico">
        <!-- third party css -->
        <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
      <!-- third party css end -->

        <!-- App css -->
        <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
        <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
      </head>

    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
        <!-- Begin page -->
        <div class="wrapper">

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">
                    <!-- Topbar Start -->
                    <div class="navbar-custom topnav-navbar">
                        <div class="container-fluid">
                            <div id="menuhyper_topnav">
                        </div>
                    </div>
                    </div>
                    <!-- end Topbar -->

                    <div class="topnav">
                        <div class="container-fluid">
                            <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                                <div class="collapse navbar-collapse" id="topnav-menu-content">
                                    <div id="menuhyper">
                                </div>
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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Plan de Cuentas</a></li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Plan de Cuentas</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                            <!-- Modal editar cuenta -->
                            <div class="modal fade" id="modalEditarCuenta" tabindex="-1" role="dialog" aria-labelledby="editarCuentaLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                <form id="formEditarCuenta" autocomplete="off">
                                    <div class="modal-header">
                                    <h5 class="modal-title" id="editarCuentaLabel">Editar Cuenta</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    </div>
                                    <div class="modal-body">
                                    <!-- Inputs -->
                                    <input type="hidden" id="edit-id" name="id">
                                    
                                    <div class="form-group">
                                        <label for="edit-nivel">Nivel</label>
                                        <input type="number" class="form-control" id="edit-nivel" name="Nivel" min="1" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="edit-cuenta">Cuenta</label>                                        
                                        <input type="text" id="edit-cuenta" name="Cuenta"  maxlength="6" pattern="^\d{6}$" title="Solo 6 dígitos numéricos" class="form-control" autocomplete="off" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="edit-nombre">Nombre Cuenta</label>
                                        <input type="text" class="form-control" id="edit-nombre" name="NombreCuenta" required>
                                    </div>                                    

                                    <div class="form-group">
                                        <label for="edit-tipo">Tipo de Cuenta</label>
                                        <select class="form-control" id="edit-tipo" name="TipoCuenta">
                                        <option value="ACTIVO">Activo</option>
                                        <option value="PASIVO">Pasivo</option>
                                        <option value="PATRIMONIO NETO">Patrimonio Neto</option>
                                        <option value="R+">R+</option>
                                        <option value="R-">R-</option>
                                        </select>
                                    </div>

                                    <div class="form-group presupuesto-campos" style="display: none;">
                                        <label for="edit-presupuestoMensual">Presupuesto mensual</label>
                                        <input type="number" class="form-control" id="edit-presupuestoMensual" name="PresupuestoMensual" step="0.01" min="0">
                                        </div>

                                        <div class="form-group presupuesto-campos" style="display: none;">
                                        <label for="edit-presupuestoAnual">Presupuesto anual</label>
                                        <input type="number" class="form-control" id="edit-presupuestoAnual" name="PresupuestoAnual" step="0.01" min="0">
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit-formaPago" name="FormaPago">
                                        <label class="form-check-label" for="edit-formaPago">Utilizar como forma de pago</label>
                                        </div>

                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit-ordenCompra" name="OrdenCompra">
                                        <label class="form-check-label" for="edit-ordenCompra">Utilizar para Orden de compra</label>
                                        </div>

                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit-anticipoEmpleado" name="AnticipoEmpleado">
                                        <label class="form-check-label" for="edit-anticipoEmpleado">Utilizar como Origen en Anticipo a Empleados</label>
                                        </div>

                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit-sinCodigo" name="SinCodigo">
                                        <label class="form-check-label" for="edit-sinCodigo">Autorizar facturas sin código</label>
                                        </div>

                                        <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit-cuentaBancaria" name="CuentaBancaria">
                                        <label class="form-check-label" for="edit-cuentaBancaria">Es una cuenta bancaria ? </label>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    </div>
                                </form>
                                </div>
                            </div>
                            </div>

                          <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-4">
<!--                                                 <a href="javascript:void(0);" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> Add Sellers</a> -->
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="text-sm-right">
                                                <button id="btnNuevaCuenta" class="btn btn-primary btn-sm mb-2"><i class="mdi mdi-plus"></i> Nueva cuenta</button>
<!--                                                     <button type="button" class="btn btn-success mb-2 mr-1"><i class="mdi mdi-settings"></i></button> -->
<!--                                                     <button type="button" class="btn btn-light mb-2 mr-1">Import</button> -->
<!--                                                     <button type="button" class="btn btn-light mb-2">Export</button> -->
                                                </div>
                                            </div><!-- end col-->
                                        </div>                     <!-- Single Select -->
                                          <div class="table-responsive">
                                            <table class="table table-centered table-borderless table-hover w-100 dt-responsive nowrap" id="plandecuentas">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Nivel</th>
                                                        <th>Cuenta</th>
                                                        <th>Nombre Cuenta</th>
                                                        <th>Tipo de Cuenta</th>
                                                        <th>Modificar</th>                                                        
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                          </div>

                            </div><!--container-fluid-->
                              
                        </div><!--content>-->
                        







                                  <!-- Footer Start -->
                                  <footer class="footer">
                                      <div class="container-fluid">
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <script>document.write(new Date().getFullYear())</script> © Hyper - Coderthemes.com
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

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->
        </div>
        <!-- END wrapper -->

   <!-- bundle -->
        <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/app.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
        <!-- third party js -->
        <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script>
        <!-- third party js ends -->
      
      <!-- Datatables js -->
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>
      
      <!--    enlases externos para botonera-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script><!--excel-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script><!--pdf-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script><!--pdf-->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- funciones -->
        <script src="Procesos/js/plandecuentas.js"></script>
        <script src="../Menu/js/funciones.js"></script>
        <!-- end demo js-->
        
</body>
</html>