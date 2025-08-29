<?php
// session_start();
include_once "../Conexion/Conexioni.php";
if($_SESSION['Nivel']<>1){
header('Location:https://www.sistemacaddy.com.ar/SistemaTriangular/Inicio/Cpanel.php?err');    
}
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Venta Simple</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="../images/favicon/apple-icon.png">
        <!-- third party css -->
        <link href="../hyper/dist/saas/assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
        <!-- third party css end -->
        <!-- App css -->
        <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
      
    </head>

    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
        <!-- Begin page -->
        <div class="wrapper">
            <!-- @@include('./partials/left-sidebar.html') -->


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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Panel de Control</a></li>
                                            <li class="breadcrumb-item active">Venta Simple</li>
                                        </ol>                            
                                    </div>
                                    <h2> <span class="badge bg-primary text-light cursor" id="mes" role="button"></span></h2>
                                    <!-- <h4 class="page-title" id="mes"></h4> -->
                                </div>
                            </div>
                        </div>     


                        
                        <div class="modal fade" id="modal-clientes" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="modal-clientes-title">Ventas Anuales </h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">

                                        <div class="row">
                                            <div class="col-xl-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h4 class="header-title mb-4">Ventas Mensuales</h4>
                                                        <div id="chartClientes" class="apex-charts" data-colors="#fa5c7c"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->

                        <!-- end page title --> 

                            <div id="meses" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header modal-colored-header bg-primary">
                                            <h4 class="modal-title" id="primary-header-modalLabel">Seleccione un Mes</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                                        </div>
                                        <div class="modal-body">
                                            <select class="form-control select2" data-toggle="select2" id="mes_select">
                                                <option value="">Seleccione un mes</option>
                                                <optgroup label="Meses">
                                                    <option value="1">Enero</option>
                                                    <option value="2">Febrero</option>
                                                    <option value="3">Marzo</option>
                                                    <option value="4">Abril</option>
                                                    <option value="5">Mayo</option>
                                                    <option value="6">Junio</option>
                                                    <option value="7">Julio</option>
                                                    <option value="8">Agosto</option>
                                                    <option value="9">Septiembre</option>
                                                    <option value="10">Octubre</option>
                                                    <option value="11">Noviembre</option>
                                                    <option value="12">Diciembre</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                            <button id="button_mes_select" type="button" class="btn btn-primary">Guardar</button>
                                        </div>
                                    </div><!-- /.modal-content -->
                                </div><!-- /.modal-dialog -->
                            </div><!-- /.modal -->
                            <!-- Large modal -->
                            
                            <div class="modal fade" id="gastos-mensuales-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="myLargeModalLabel">Gastos Seleccionados</h4>
                                            
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        </div>
                                        <div class="modal-body">
                                        <p class="text-muted font-14" id="sub_header_gastos">Desde el 01/04/2022 Hasta el 30/04/2022</p>
                                                <div class="row mt-3">
                                                    <div class="col-12">
                                                        <div class="table-responsive">
                                                            <table id="gastos_detalle" class="table table-striped dt-responsive nowrap w-100 h-6">
                                                               <thead class="thead-light"> 
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>Cuenta</th>
                                                                        <th>Nombre Cuenta</th>
                                                                        <th>Debe</th>
                                                                    </tr>
                                                                 </thead>
                                                                 <tbody>
                                                                 </tbody>
                                                                 <tfoot>
                                                                 </tfoot>
                                                            </table>
                                                        </div> <!-- end table-responsive -->
                                                    </div> <!-- end col -->
                                                </div> <!-- end row-->


                                        </div>
                                    </div><!-- /.modal-content -->
                                </div><!-- /.modal-dialog -->
                            </div><!-- /.modal -->

                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                
                                                <!-- <div class="row">
                                                    <div class="col-md-4">
                                                        <div id="spark1" class="apex-charts" data-colors="#DCE6EC"></div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div id="spark2" class="apex-charts" data-colors="#DCE6EC"></div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div id="spark3" class="apex-charts" data-colors="#0acf97"></div>
                                                    </div>
                                                </div> -->
                                                <!-- end row -->
                                           <div class="row">
                                            <div class="col-xl-4 col-lg-6">
                                                <div class="card widget-flat">
                                                    <div class="card-body bg-success">
                                                        <div class="float-right">
                                                            <i class="mdi mdi-truck-check widget-icon bg-danger rounded-circle text-white"></i>
                                                        </div>
                                                        <h5 class="text-white font-weight-normal mt-0" title="Revenue">Ventas Mensuales</h5>
                                                        <h3 class="mt-3 mb-3 text-white" id="TotalIngresos"></h3>
                                                        <!-- <span class="text-nowrap" id="entregas_mes"></span> -->
                                                        <!-- <p class="mb-0 text-muted"> -->
                                                            <!-- <span class="badge badge-info mr-1"> -->
                                                                <!-- <i  id="entregas_porc"></i> </span> -->
                                                            <!-- <span class="text-nowrap" id="entregas_mesant"></span> -->
                                                        <!-- </p> -->
                                                    </div>
                                                </div>
                                            </div> <!-- end col-->
                                            <div class="col-xl-4 col-lg-6" style="display:none">
                                                <div class="card widget-flat">
                                                    <div class="card-body">
                                                        <div class="float-right">
                                                            <i class="mdi mdi-truck widget-icon bg-danger rounded-circle text-white"></i>
                                                        </div>
                                                        <h5 class="text-muted font-weight-normal mt-0" title="Revenue">Envios Recorridos</h5>
                                                        <h3 class="mt-3 mb-3" id="entregasr_dia"></h3>
                                                        <span class="text-nowrap" id="entregasr_mes"></span>
                                                        <p class="mb-0 text-muted">
                                                            <span class="badge badge-info mr-1">
                                                                <i  id="entregasr_porc"></i> </span>
                                                            <span class="text-nowrap" id="entregasr_mesant"></span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div> <!-- end col-->
                                            <div class="col-xl-4 col-lg-6">
                                                <div class="card widget-flat">
                                                    <div class="card-body bg-danger">
                                                        <div class="dropdown float-right">
                                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                                                <i class="mdi mdi-dots-vertical text-white"></i>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <!-- item-->
                                                                <a id="ver_gastos_btn" class="dropdown-item">Ver Gastos</a>
                                                                <!-- item-->
                                                                <!-- <a href="javascript:void(0);" class="dropdown-item">Action</a> -->
                                                            </div>
                                                        </div>

                                                        <div class="float-right">
                                                            <i class="mdi mdi-truck widget-icon bg-danger rounded-circle text-white"></i>
                                                        </div>
                                                        <h5 class="text-white font-weight-normal mt-0" title="Revenue">Gastos Mensuales</h5>
                                                        <h3 class="mt-3 mb-3 text-white" id="TotalGastos"></h3>
                                                        <!-- <span class="text-nowrap" id="gastos_mes"></span> -->
                                                        <!-- <p class="mb-0 text-muted"> -->
                                                            <!-- <span class="badge badge-info mr-1"> -->
                                                                <!-- <i  id="gastos_porc"></i> </span> -->
                                                            <!-- <span class="text-nowrap" id="gastos_mesant"></span> -->
                                                        <!-- </p> -->
                                                    </div>
                                                </div>
                                            </div> <!-- end col-->
                                            <div class="col-xl-4 col-lg-6">
                                                <div class="card widget-flat">
                                                    <div class="card-body">
                                                        <div class="float-right">
                                                            <i class="mdi mdi-truck widget-icon bg-danger rounded-circle text-white"></i>
                                                        </div>
                                                        <h5 class="text-muted font-weight-normal mt-0" title="Revenue">Resultado Mensual</h5>
                                                        <h3 class="mt-3 mb-3" id="resultado_mes"></h3>
                                                        <!-- <span class="text-nowrap" id="_mes"></span> -->
                                                        <!-- <p class="mb-0 text-muted"> -->
                                                            <!-- <span class="badge badge-info mr-1"> -->
                                                                <!-- <i  id="entregast_porc"></i> </span> -->
                                                            <!-- <span class="text-nowrap" id="entregast_mesant"></span> -->
                                                        <!-- </p> -->
                                                    </div>
                                                </div>
                                            </div> <!-- end col-->
                                        </div> <!-- end row -->
                                    

                                    <!-- CUADRO GASTOS-->
                                        <div class="col-xl-6" style="display:none">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h4 class="header-title mb-4">Dashed Line Chart</h4>
                                                    <div id="line-expenses" class="apex-charts" data-colors="#6c757d,#0acf97,#39afd1"></div>
                                                </div>                                       
                                        </div>
                                    </div>


                                <!-- TABLA RECORRIDOS -->
                                    <div class="row">
                                    <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
            <!--                                         <a href="" class="btn btn-sm btn-link float-right mb-3">Export
                                                        <i class="mdi mdi-download ml-1"></i>
                                                    </a> -->
                                                    <h4 class="header-title mt-2">HOJA DE RUTA | ENTREGAS DE HOY </h4>
                                                    <div class="table-responsive">
                                                        <table class="table table-centered table-nowrap table-hover mb-0" id="desempeno_dia">
                                                            <tbody>
                                                            <thead>
                                                                <tr>
                                                                    <th>Orden N</th>
                                                                    <th>Nombre Completo</th>
                                                                    <th>Dominio</th>
                                                                    <th>Recorrido</th>
                                                                    <th>Entregas</th>
                                                                    <th>Cerradas</th>
                                                                    <th>Progreso</th>
                                                                    <th>Ver</th>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div> <!-- end table-responsive-->
                                                </div> <!-- end card-body-->
                                            </div> <!-- end card-->
                                        </div> <!-- end col-->
                                    
                                    <div class="col-12" style="display:none" id="panel1">
                                            <div class="card">
                                                <div class="card-body">
                                                    <a id='cerrar_panel1' class="float-right close-btn">
                                                        <i class="mdi mdi-close"></i>
                                                    </a>

                                                    <h4 id="header_panel1" class="header-title mt-2"></h4>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-centered mb-0" id="seguimiento" style="font-size:10px">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Informacion</th>
                                                                        <th>Seguimiento</th>
                                                                        <th>Servicio</th>
                                                                    </tr>
                                                                </thead>
                                                            <tbody>
                                                        </tbody>
                                                    </table>
                                                    </div> <!-- end table-responsive-->
                                                </div> <!-- end card-body-->
                                            </div> <!-- end card-->
                                        </div> <!-- end col-->

                                    </div>





                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h4 class="header-title">TOTAL DE VENTAS POR PAQUETE POR CLIENTE EN $</h4>
                                                        <p class="text-muted font-14">Corresponde a todos los clientes que enviaron paquetes en el mes de <a id="mes_clientes"></a>,
                                                             el total de estas ventas es de <a id="total_clientes"></a>. No estan incluidos los envios x recorridos.</p>
                                                            <div class="row mt-3">
                                                                <div class="col-12">
                                                                    <div class="table-responsive">
                                                                        <table id="clientes" class="table table-striped dt-responsive nowrap w-100">
                                                                        <thead class="thead-light"> 
                                                                                <tr>
                                                                                    <th>Cliente</th>
                                                                                    <th>Cantidad</th>
                                                                                    <th>Total</th>
                                                                                    <th>Promedio</th>
                                                                                    <th>Grafico</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                            </tbody>
                                                                            <tfoot>
                                                                            </tfoot>
                                                                        </table>
                                                                    </div> <!-- end table-responsive -->
                                                                </div> <!-- end col -->
                                                            </div> <!-- end row-->
                                                    </div><!-- end card body-->
                                                </div><!--end card -->
                                            </div><!--end col-xl-12 -->
                                        </div>

                                    <!-- end row-->
                                    <div class="row">
                                            <div class="col-lg-8">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h4 class="header-title">Ventas Mensuales x Paquete PESOS</h4>
                                                        <p class="text-muted font-14 mb-3">Cantidad Total de Ventas por paquete en Pesos.</p>
                                                        <div id="chart" class="apex-charts" data-colors="#fa5c7c"></div>
                                                    </div>
                                                </div>
                                            </div>
                                    

                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        
                                        <h4 class="header-title">Ranking Anual de Clientes TOP 6</h4>
                                        <p class="text-muted font-14">Cantidad Total de Servicios Solicitados.</p>
                                        <h5 id="cliente_1" class="mb-1 mt-3 font-weight-normal"></h5>
                                        <div class="progress-w-percent">
                                            <span id="cliente_1_total" class="progress-value font-weight-bold"> </span>
                                            <div class="progress progress-sm">
                                                <div id="theprogressbar_1" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>

                                        <h5 id="cliente_2" class="mb-1 mt-0 font-weight-normal"></h5>
                                        <div class="progress-w-percent">
                                            <span id="cliente_2_total" class="progress-value font-weight-bold"> </span>
                                            <div class="progress progress-sm">
                                                <div id="theprogressbar_2" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>

                                        <h5 id="cliente_3" class="mb-1 mt-0 font-weight-normal"></h5>
                                        <div class="progress-w-percent">
                                            <span id="cliente_3_total" class="progress-value font-weight-bold"> </span>
                                            <div class="progress progress-sm">
                                                <div id="theprogressbar_3" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>

                                        <h5 id="cliente_4" class="mb-1 mt-0 font-weight-normal"></h5>
                                        <div class="progress-w-percent">
                                            <span id="cliente_4_total" class="progress-value font-weight-bold"> </span>
                                            <div class="progress progress-sm">
                                                <div id="theprogressbar_4" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <h5 id="cliente_5" class="mb-1 mt-0 font-weight-normal"></h5>
                                        <div class="progress-w-percent">
                                            <span  id="cliente_5_total" class="progress-value font-weight-bold"> </span>
                                            <div class="progress progress-sm">
                                                <div id="theprogressbar_5" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <h5 id="cliente_6" class="mb-1 mt-0 font-weight-normal"></h5>
                                        <div class="progress-w-percent">
                                            <span id="cliente_6_total" class="progress-value font-weight-bold"> </span>
                                            <div class="progress progress-sm">
                                                <div id="theprogressbar_6" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div>
                        <!-- end row -->
                                    
                                    
                                        <!-- </div> -->
                                    
                                    
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="header-title">PAQUETES ENVIADOS UNIDADES</h4>
                                                    <p class="text-muted font-14 mb-3">Cantidad Total de Paquetes Enviados x mes x Unidades.</p>
                                                    <div id="chart_envios" class="apex-charts" data-colors="#6c757d"></div>
                                                </div>                                        
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="header-title">Ventas Mensuales x Recorrido</h4>
                                                    <p class="text-muted font-14 mb-3">Ventas totales Mensuales x Recorrido.</p>
                                                <div id="chartrec" class="apex-charts" data-colors="#F76161"></div>
                                            </div>
                                        </div>
                                    </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="header-title">TOTAL X MES EN PESOS</h4>
                                                    <p class="text-muted font-14 mb-3">Cantidad Total de Ventas en PesosPaquetes Enviados x mes x Unidades.</p>
                                                    <div id="chart_envios_total" class="apex-charts" data-colors="#6c757d"></div>
                                                </div>                                        
                                            </div>
                                        </div>
                                    </div>


                                </div>
                                <!-- end row-->
                            </div>
                        </div>

                <!-- content -->
              <div class="spinner-border avatar-md text-primary" role="status" style="display:none"></div>
              <!-- <div class="spinner-grow avatar-md text-secondary" role="status"></div> -->
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

        <!-- Right Sidebar -->
        <div class="right-bar">

            <div class="rightbar-title">
                <a href="javascript:void(0);" class="right-bar-toggle float-right">
                    <i class="dripicons-cross noti-icon"></i>
                </a>
                <h5 class="m-0">Settings</h5>
            </div>

            <div class="rightbar-content h-100" data-simplebar>

                <div class="p-3">
                    <div class="alert alert-warning" role="alert">
                        <strong>Customize </strong> the overall color scheme, sidebar menu, etc.
                    </div>

                    <!-- Settings -->
                    <h5 class="mt-3">Color Scheme</h5>
                    <hr class="mt-1" />

                    <div class="custom-control custom-switch mb-1">
                        <input type="radio" class="custom-control-input" name="color-scheme-mode" value="light"
                            id="light-mode-check" checked />
                        <label class="custom-control-label" for="light-mode-check">Light Mode</label>
                    </div>

                    <div class="custom-control custom-switch mb-1">
                        <input type="radio" class="custom-control-input" name="color-scheme-mode" value="dark"
                            id="dark-mode-check" />
                        <label class="custom-control-label" for="dark-mode-check">Dark Mode</label>
                    </div>

                    <!-- Width -->
                    <h5 class="mt-4">Width</h5>
                    <hr class="mt-1" />
                    <div class="custom-control custom-switch mb-1">
                        <input type="radio" class="custom-control-input" name="width" value="fluid" id="fluid-check" checked />
                        <label class="custom-control-label" for="fluid-check">Fluid</label>
                    </div>
                    <div class="custom-control custom-switch mb-1">
                        <input type="radio" class="custom-control-input" name="width" value="boxed" id="boxed-check" />
                        <label class="custom-control-label" for="boxed-check">Boxed</label>
                    </div>

                    <!-- Left Sidebar-->
                    <h5 class="mt-4">Left Sidebar</h5>
                    <hr class="mt-1" />
                    <div class="custom-control custom-switch mb-1">
                        <input type="radio" class="custom-control-input" name="theme" value="default" id="default-check"
                            checked />
                        <label class="custom-control-label" for="default-check">Default</label>
                    </div>

                    <div class="custom-control custom-switch mb-1">
                        <input type="radio" class="custom-control-input" name="theme" value="light" id="light-check" />
                        <label class="custom-control-label" for="light-check">Light</label>
                    </div>

                    <div class="custom-control custom-switch mb-3">
                        <input type="radio" class="custom-control-input" name="theme" value="dark" id="dark-check" />
                        <label class="custom-control-label" for="dark-check">Dark</label>
                    </div>

                    <div class="custom-control custom-switch mb-1">
                        <input type="radio" class="custom-control-input" name="compact" value="fixed" id="fixed-check"
                            checked />
                        <label class="custom-control-label" for="fixed-check">Fixed</label>
                    </div>

                    <div class="custom-control custom-switch mb-1">
                        <input type="radio" class="custom-control-input" name="compact" value="condensed"
                            id="condensed-check" />
                        <label class="custom-control-label" for="condensed-check">Condensed</label>
                    </div>

                    <div class="custom-control custom-switch mb-1">
                        <input type="radio" class="custom-control-input" name="compact" value="scrollable"
                            id="scrollable-check" />
                        <label class="custom-control-label" for="scrollable-check">Scrollable</label>
                    </div>

                    <button class="btn btn-primary btn-block mt-4" id="resetBtn">Reset to Default</button>

                    <a href="https://themes.getbootstrap.com/product/hyper-responsive-admin-dashboard-template/"
                        class="btn btn-danger btn-block mt-3" target="_blank"><i class="mdi mdi-basket mr-1"></i> Purchase Now</a>
                </div> <!-- end padding-->

            </div>
        </div>

        <div class="rightbar-overlay"></div>
        <!-- /Right-bar -->
                <!-- bundle -->
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script><!--excel-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script><!--pdf-->
        <!-- demo app -->
        <script src="../hyper/dist/saas/assets/js/pages/demo.datatable-init.js"></script>
        <!-- end demo js-->
        <!-- funciones -->
        <script src="js/funcionesAdmin.js"></script>
        <script src="js/mapagestya.js"></script>
        <script src="../Menu/js/funciones.js"></script>
        <script src="js/demo.apex-line.js"></script>

        
        <!-- <script src="../hyper/dist/saas/assets/js/pages/demo.dashboard.js"></script> -->
        <!-- end demo js-->
        <!-- third party:js -->
        <!-- <script src="https://apexcharts.com/samples/assets/stock-prices.js"></script> -->
        <!-- <script src="js/apex-line-expenses.js"></script> -->
        <script src="https://apexcharts.com/samples/assets/irregular-data-series.js"></script>
         
        <script src="../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script>
        <!-- <script src="../hyper/dist/saas/assets/js/pages/demo.apex-line.js"></script> -->
                <script src="js/apex-line-expenses.js"></script>
  </body>
</html>