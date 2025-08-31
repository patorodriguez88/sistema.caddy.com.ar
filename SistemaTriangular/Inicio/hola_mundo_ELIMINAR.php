<?php
// session_start();
// include_once "../Conexion/Conexion.php";
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Panel</title>
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
                    <div class="navbar-custom topnav-navbar" style="z-index:10">
                        <div class="container-fluid">
                            <?
                            include_once("../Menu/MenuHyper_topnav.html");
                            ?>
                        </div>
                    </div>
                    <!-- end Topbar -->
                         <div class="topnav">
                        <div class="container-fluid">
                            <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                                <div class="collapse navbar-collapse" id="topnav-menu-content">
                                  <?
                                  include_once("../Menu/MenuHyper.html");
                                  ?>
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
                                    <h4 class="page-title" id="mes"></h4>
                                </div>
                            </div>
                        </div>     
                      



                <!-- MODAL VER RECORRIDO GESTYA -->
                <!-- Full width modal -->
                <!-- <button  type="button" class="btn btn-primary" data-toggle="modal" data-target="#full-width-modal">Full width Modal</button> -->

                <div id="full-width-modal_order" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-full-width">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="fullWidthModalLabel">RECORRIDO BY GESTYA
                                </h4>
                                                            
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">
                            <!-- //DESDE America -->
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">                                     
                                            <div class="col-lg-12">
                                                <!-- <div class="card"> -->
                                                <!-- <h4 class="header-title">Recorrido by Gestya</h4> -->
                                                <input type="hidden" id="id_logistica">
                                                <small id="header_title_map" class="mb-2"></small>
                                                  <div id="map_inicio" class="gmaps" style="margin-top:0px;position: relative; overflow: hidden;max-width: 100%;height:500px">
                                                  </div>
                                                <!-- </div> end col -->
                                            </div>
                                            
                                        </div> <!-- end row-->
                                     </div> <!--end card-body -->
                                </div> <!-- end card-->
                            </div><!-- end col-->
                        </div> <!-- end row-->

                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->



                      <!-- Large modal -->
                      <div id="bs-example-modal-lg" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-full-width">
                              <div class="modal-content"> 
                                  <div class="modal-header">
                                      <h4 class="modal-title" id="myLargeModalLabel">Large modal</h4>
                                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                  </div>
                                  <div class="modal-body">
                                    <div class="row">
                                    <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                          <div class="card">
                                            <div style="display:none" id="idRecorridoPendientes"></div>
                                              <div class="card-body">
                                                <div class="button-list">
                                                <button type="button" class="btn btn-secondary float-right" data-toggle="modal" data-target="#remitos-modal"><i class='mdi mdi-18px mdi-printer'></i> Imprimir Remitos</button>
                                                <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#rotulos-modal"><i class='mdi mdi-18px mdi-printer'></i> Imprimir Rótulos</button>
                                                </div>
                                                  <h4 class="header-title mt-2">LOGISTICA | HOJAS DE RUTA | ENVIOS PENDIENTES X RECORRIDO </h4>
                                                  <div class="table-responsive">
                                                      <table class="table table-centered table-hover mb-0" id="pendientes">
                                                            <thead>
                                                                <tr>
                                                                  <th>Fecha</th>
                                                                  <th>Origen</th> 
                                                                  <th>Destino</th> 
                                                                  <th>Notas (Uso Interno)</th>
                                                                  <th>Seguimiento</th>
                                                                  <th>Remito</th>
                                                                  <th>Rotulo</th>
                                                                </tr>
                                                            </thead>
                                                              <tbody>
                                                               <tr>
                                                                 <th></th> 
                                                                 <th></th> 
                                                                 <th></th> 
                                                                 <th></th> 
                                                                 <th></th> 
                                                                 <th></th> 
                                                                 <th></th> 
                                                                </tr>  
                                                          </tbody>
                                                      </table>
                                                  </div> <!-- end table-responsive-->
                                              </div> <!-- end card-body-->
                                          </div> <!-- end card-->
                                      </div> <!-- end col-->  
                                    </div>
                                  </div>
                              </div><!-- /.modal-content -->
                          </div><!-- /.modal-dialog -->
                      </div><!-- /.modal -->
                     
                      <!--DEPOSITO -->
                      <div id="deposito-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fill-warning-modalLabel" aria-hidden="true">
                      <div class="modal-dialog">
                          <div class="modal-content modal-filled bg-warning">
                              <div class="modal-header">
                                  <h4 class="modal-title" id="fill-warning-modalLabel">ENVIAR SERVICIOS A DEPOSITO</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div id="deposito-modal-body" class="modal-body">
                                 
                              </div>
                              <div class="modal-footer">
                                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                                  <button id="deposito-modal-ok" type="button" class="btn btn-outline-light">Aceptar</button>
                              </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
                      
                      
                  <div class="modal fade" id="notas-modal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header modal-colored-header bg-info">
                                <h4 class="modal-title" id="myCenterModalLabel">Center modal</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">
                            <div class="form-group">
                                <label>Nota Interna</label>
                                <input id="notas_id" type="hidden">
                                <p class="text-muted font-13">
                                    Esta nota es únicamente para uso interno del sector operaciones.
                                </p>
                                <textarea id="notas_txt" data-toggle="maxlength" class="form-control" maxlength="225" rows="3" 
                                    placeholder="Máximo 225 caracteres."></textarea>
                            </div>
                            </div>
                            <div class="modal-footer">
                                  <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                                  <button id="notas-modal-ok" type="button" class="btn btn-success">Aceptar</button>
                              </div>

                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->

                     <!--ROTULOS--> 
                      <div id="rotulos-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fill-primary-modalLabel" aria-hidden="true">
                          <div class="modal-dialog modal-dialog-centered">
                              <div class="modal-content modal-filled bg-primary">
                                  <div class="modal-header">
                                  
                                      <h4 class="modal-title" id="fill-primary-modalLabel">Imprimir Rótulos Recorrido </h4>
                                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                  </div>
                                  <div id="body-rotulos" class="modal-body">
                                      
                                  </div>
                                  <div class="modal-footer">
                                      <select id="selected_device" onchange=onDeviceSelected(this);></select> 
                                      <button type="button" class="btn btn-light" data-dismiss="modal"> Cancelar </button>
                                      <button id="imp_rot_rec_700x200" type="button" class="btn btn-outline-light"> Imprimir 70cm. x 20 cm.</button>
                                      <button id="imp_rot_rec" type="button" class="btn btn-outline-light"> Imprimir </button>
                                      <button id="imp_rot" type="button" class="btn btn-success"> Imprimir </button>
                                  </div>
                              </div><!-- /.modal-content -->
                          </div><!-- /.modal-dialog -->
                      </div><!-- /.modal -->
                      <!--REMITOS -->
                      <div id="remitos-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fill-primary-modalLabel" aria-hidden="true">
                          <div class="modal-dialog modal-dialog-centered">
                              <div class="modal-content modal-filled bg-secondary">
                                  <div class="modal-header">
                                      <h4 class="modal-title" id="fill-primary-modalLabel">Imprimir Remitos Recorrido </h4>
                                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                  </div>
                                  <div id="body-remitos" class="modal-body">
                                  </div>
                                  <div class="modal-footer">
<!--                                       <select id="selected_device" onchange=onDeviceSelected(this);></select>  -->
                                      <button type="button" class="btn btn-light" data-dismiss="modal"> Cancelar </button>
                                      <button id="imp_rem_rec" type="button" class="btn btn-outline-light"> Imprimir </button>
                                      <button id="imp_rem" type="button" class="btn btn-success"> Imprimir </button>
                                  </div>
                              </div><!-- /.modal-content -->
                          </div><!-- /.modal-dialog -->
                      </div><!-- /.modal -->
                      
                      <!-- SEGUIIENTO MODAL -->
                        <div class="modal fade" id="modal_seguimiento" tabindex="-1" role="dialog" aria-hidden="true" style="display:none">
                            <div class="modal-dialog modal-lg">
                                <div id="modal_seguimiento_content" class="modal-content bg-primary">
                                    <div id="modal_seguimiento_header" class="modal-header">
                                        <h4 class="modal-title" id="myCenterModalLabel">Seguimiento</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="header-title mb-3">Informacion de Origen</h4>
                                                    <h5 id="cliente_origen_seguimiento"></h5>
                                                  <ul id="cliente_origen_direcccion_seguimiento" class="list-unstyled mb-0">

                                                  </ul>
                                                </div>
                                            </div>
                                        </div> <!-- end col -->

                                        <div class="col-lg-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="header-title mb-3">Informacion de Destino</h4>
                                                    <h5 id="cliente_destino_seguimiento"></h5>
                                                    <ul id="cliente_destino_direcccion_seguimiento" class="list-unstyled mb-0">
                                                    </ul>
                                                </div>
                                            </div>
                                        </div> <!-- end col --> 
                                      <div class="col-lg-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 id="header_title_guia_seguimiento" class="header-title mb-3">Informacion de la Guia</h4>
                                                    <h5 id="guia_seguimiento"></h5>
                                                  <table id="info_guia_seguimiento" class="table table-sm table-centered table-borderless mb-0">
                                                  </table>
                                                </div>
                                            </div>
                                        </div> <!-- end col --> 
                                      </div>

                                      <div class="row">
                                        <div class="col-lg-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 id="myCenterModalLabel2" class="header-title mb-3"></h4>

                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-centered mb-0" style="font-size:10px" id="seguimiento_tabla">
                                                            <thead class="thead-light">
                                                            <tr>
                                                                <th>Fecha</th>
                                                                <th>Hora</th>
                                                                <th>Usuario</th>
                                                                <th>Observaciones</th>
                                                                <th>Estado</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr id="tr_seguimiento">
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- end table-responsive -->
                                                </div>
                                            </div>
                                        </div> <!-- end col -->
                                    </div>
                                    <!-- end row -->
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->
                  <!--END SEGUIMIENTO MODAL-->
                     <!-- Large modal -->
                      <div id="pendientesmapa" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-full-width">
                              <div class="modal-content"> 
                                  <div class="modal-header">
                                      <h4 class="modal-title" id="tabla_pendientesmapa_title">Large modal</h4>
                                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                  </div>
                                  <div class="modal-body">
                                    <div class="row">
                                    <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                          <div class="card">
                                              <div class="card-body">
                                                  <h4 class="header-title mt-2">LOGISTICA | HOJAS DE RUTA | ENVIOS PENDIENTES X RECORRIDO </h4>

                                                  <div class="table-responsive">
                                                      <table class="table table-centered table-nowrap table-hover mb-0" id="tabla_pendientesmapa">
                                                          <tbody>
                                                            <thead>
                                                                <tr>
                                                                <th>Estado</th>
                                                                <th>Numero</th>
                                                                <th>Fecha</th>
                                                                <th>Codigo</th>
                                                                <th>Origen</th>
                                                                <th>N Provee.</th>
                                                                <th>Destino</th>
                                                                <th>Servicio</th>
                                                                <th>Recorrido</th>
                                                                <th>Bultos</th>
                                                                <th>Total</th>
                                                                <th>Remito</th>
                                                                <th>Rotulo</th>
                                                                <th>Seguimiento</th>
                                                                <th>Eliminar</th>                                                               
                                                              </tr>
                                                          </tbody>
                                                      </table>
                                                  </div> <!-- end table-responsive-->
                                              </div> <!-- end card-body-->
                                          </div> <!-- end card-->
                                      </div> <!-- end col-->  
                                    </div>
                                  </div>
                              </div><!-- /.modal-content -->
                          </div><!-- /.modal-dialog -->
                      </div><!-- /.modal -->
                        <div class="row">
                          <div class="col-xl-12 col-lg-12">

                            <div id="alerta_services" class="alert alert-warning" data-dismiss="alert" role="alert" aria-hidden="true" style="display:none">
                              <i class="dripicons-checkmark mr-2" id="alerta_services_label"></i>
                            </div>

                            <div id="alerta_licencias" class="alert alert-danger" data-dismiss="alert" role="alert" aria-hidden="true" style="display:none">
                              <i class="dripicons-checkmark mr-2" id="alerta_licencias_label"></i>
                            </div>
                             <div id="alerta_seguros" class="alert alert-danger" data-dismiss="alert" role="alert" aria-hidden="true" style="display:none">
                              <i class="dripicons-checkmark mr-2" id="alerta_seguros_label"></i>
                            </div>
                             <div id="alerta_itv" class="alert alert-warning" data-dismiss="alert" role="alert" aria-hidden="true" style="display:none">
                              <i class="dripicons-checkmark mr-2" id="alerta_itv_label"></i>
                            </div>

                          </div>
                      </div>
                        <!-- end page title --> 
                                    <div class="row" id="preventa" style='display:none'>
                                      <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="header-title mt-2">PREVENTA | VENTAS SOLICITADAS VIA WEB </h4>

                                                    <div class="table-responsive">
                                                        <table class="table table-centered table-nowrap table-hover mb-0" id="tabla_preventa">
                                                            <tbody>
                                                              <thead class="text-danger">
                                                                  <tr class="bg-danger text-white">
                                                                    <th>Origen</th>
                                                                    <th>Domicilio</th> 
                                                                    <th>Cantidad</th> 
                                                                    <th>Ver</th>
                                                                  </tr>
                                                            </tbody>
                                                        </table>
                                                    </div> <!-- end table-responsive-->
                                                </div> <!-- end card-body-->
                                            </div> <!-- end card-->
                                        </div> <!-- end col-->  
                                      </div>
                                        <div class="row">
                                            <div class="col-xl-3 col-lg-6">
                                                <div class="card widget-flat">
                                                    <div class="card-body">
                                                        <div class="float-right">
                                                            <i class="mdi mdi-truck-check widget-icon bg-danger rounded-circle text-white"></i>
                                                        </div>
                                                        <h5 class="text-muted font-weight-normal mt-0" title="Revenue">Envios Simples</h5>
                                                        <h3 class="mt-3 mb-3" id="entregas_dia"></h3>
                                                        <span class="text-nowrap" id="entregas_mes"></span>
<!--                                                         <span class="text-nowrap" id="enviospendientes"</span> -->
                                                        <p class="mb-0 text-muted">
                                                            <span id="entregas_porc_color" class="badge badge-info mr-1">
                                                                <i  id="entregas_porc"></i> </span>
                                                            <span class="text-nowrap" id="entregas_mesant"></span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div> <!-- end col-->
                
                                            <div class="col-xl-3 col-lg-6">
                                                <div class="card widget-flat">
                                                    <div class="card-body">
                                                        <div class="float-right">
                                                            <i class="mdi mdi-truck widget-icon bg-danger rounded-circle text-white"></i>
                                                        </div>
                                                        <h5 class="text-muted font-weight-normal mt-0" title="Revenue">Envios Recorridos</h5>
                                                        <h3 class="mt-3 mb-3" id="entregasr_dia"></h3>
                                                        <span class="text-nowrap" id="entregasr_mes"></span>
                                                        <p class="mb-0 text-muted">
                                                            <span id="entregasr_porc_color" class="badge badge-info mr-1">
                                                                <i  id="entregasr_porc"></i> </span>
                                                            <span class="text-nowrap" id="entregasr_mesant"></span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div> <!-- end col-->
                
                                            <div class="col-xl-3 col-lg-6">
                                                <div class="card widget-flat">
                                                    <div class="card-body">
                                                        <div class="float-right">
                                                            <i class="mdi mdi-account-multiple widget-icon bg-danger rounded-circle text-white"></i>
                                                        </div>
                                                        <h5 class="text-muted font-weight-normal mt-0" title="Revenue">Clientes Activos</h5>
                                                        <h3 class="mt-3 mb-3" id="clientes_dia"></h3>
                                                        <span class="text-nowrap" id="clientes_mes"></span>
                                                        <p class="mb-0 text-muted">
                                                            <span id="clientes_porc_color" class="badge badge-info mr-1">
                                                                <i  id="clientes_porc"></i> </span>
                                                            <span class="text-nowrap" id="clientes_mesant"></span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div> <!-- end col-->
                
                                            <div class="col-xl-3 col-lg-6">
                                                <div class="card widget-flat">
                                                    <div class="card-body">
                                                        <div class="float-right">
                                                            <i class="mdi mdi-truck-fast widget-icon bg-danger rounded-circle text-white"></i>
                                                        </div>
                                                        <h5 class="text-muted font-weight-normal mt-0" title="Revenue">Kilometros Recorridos</h5>
                                                        <h3 class="mt-3 mb-3" id="kilometros_dia"></h3>
                                                        <span class="text-nowrap" id="kilometros_mes"></span>
                                                        <p class="mb-0 text-muted">
                                                            <span id="kilometros_porc_color" class="badge badge-info mr-1">
                                                                <i  id="kilometros_porc"></i> </span>
                                                            <span class="text-nowrap" id="kilometros_mesant"></span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div> <!-- end col-->
                
                                        </div> <!-- end row -->
<!--                                     </div> -->
<!--                                 </div> <!-- end card-box--> 
<!--                             </div> <!-- end col --> 
<!--                         </div> -->
                        <!-- end row-->
                        <div class="row">
                           <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                <div class="card">
                                    <div class="card-body">
                                        <a target="_blank" href="https://www.sistemacaddy.com.ar/SistemaTriangular/Logistica/SalidasdeHoy.php" class="btn btn-outline-warning btn-rounded float-right mb-3">
                                           <i class='mdi mdi-18px mdi-map-search-outline'></i>Abrir Mapa</a>
                                        </a>
                                        <h4 class="header-title mt-2">  Transporte  </h4>

                                        <div class="table-responsive">
                                            <table class="table table-centered table-nowrap table-hover mb-0" id="transporte">
                                                <tbody>
                                                  <thead>
                                                      <tr>
                                                        <th>Color</th>
                                                        <th>Orden</th> 
                                                        <th>Fecha</th> 
                                                        <th>Hora</th>
                                                        <th>Dominio</th> 
                                                        <th>Chofer</th> 
                                                        <th>Acomp.</th> 
                                                        <th>Recorrido</th>
                                                        <th>Estado</th> 
                                                        <th>Accion</th> 
                                                    </tr>
                                                  </thead> 
                                                </tbody>
                                            </table>
                                        </div> <!-- end table-responsive-->
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div>
                      <div class="row">
                      <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                            <div class="card">
                                <div class="card-body">
                                    <a href="" class="btn btn-sm btn-link float-right mb-3">Export
<!--                                         <i class="mdi mdi-download ml-1"></i> -->
                                    </a>
                                    <h4 class="header-title mt-2">LOGISTICA | HOJAS DE RUTA ACTIVAS</h4>
                                    <p class="text-muted font-14 mb-3">
                                            Todos los envios pendientes con Estado Abierto en hoja de Ruta y que pertenezcan al recorrido.
                                        </p>
                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-hover mb-0" id="logistica">
                                            <tbody>
                                              <thead>
                                                  <tr>
                                                    <th>Color</th>
                                                    <th>Recorrido</th> 
                                                    <th>Vehiculo</th> 
                                                    <th>Chofer</th>
                                                    <th>Pendientes</th>
                                                    <th>Ver</th>
                                                    
                                                  </tr>
                                            </tbody>
                                        </table>
                                    </div> <!-- end table-responsive-->
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->  
                      </div>
                     <div class="row">
                      <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                            <div class="card">
                                <div class="card-body">
<!--                                     <a href="" class="btn btn-sm btn-link float-right mb-3">Export
                                        <i class="mdi mdi-download ml-1"></i>
                                    </a> -->
                                    <h4 class="header-title mt-2">LOGISTICA | ENVIOS PENDIENTES</h4>
                                    <p class="text-muted font-14 mb-3">
                                            Todos los envios sin entrega y que pertenezcan al recorrido.
                                        </p>
                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-hover mb-0" id="logistica1">
                                            <tbody>
                                              <thead>
                                                  <tr>
                                                    <th>Color</th>
                                                    <th>Recorrido</th> 
                                                    <th>Zona</th> 
                                                    <th>Pendientes</th>
                                                    <th>Ver</th>
                                                    <!-- <th>Cambiar</th> -->
                                                    <th>Vaciar</th>
                                                  </tr>
                                            </tbody>
                                        </table>
                                    </div> <!-- end table-responsive-->
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->  
                      </div>
                          <div class="row">
                            <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                <div class="card">
                                    <div class="card-body">
<!--                                         <a href="" class="btn btn-sm btn-link float-right mb-3">Export
                                            <i class="mdi mdi-download ml-1"></i>
                                        </a> -->
                                        <h4 class="header-title mt-2">Flota</h4>

                                        <div class="table-responsive">
                                            <table class="table table-centered table-nowrap table-hover mb-0" id="flota">
                                                <tbody>
                                                  <thead>
                                                      <tr>
                                                        <th>Marca | Modelo</th>
                                                        <th>Dominio</th> 
                                                        <th>Año</th> 
                                                        <th>Kilometros</th>
                                                        <th>Estado</th>
                                                      </tr>
                                                </tbody>
                                            </table>
                                        </div> <!-- end table-responsive-->
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div>
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
                                <script>document.write(new Date().getFullYear())</script> © Triangular S.A.
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
        <!-- third party js ends -->

        <!-- funciones -->
        <script src="js/funcionesCpanel.js"></script>
        <script src="../Funciones/js/seguimiento.js"></script>
        <script src="../Menu/js/funciones.js"></script>        
        <script src="js/mapa_inicio.js"></script>

        <!-- Funciones Imprimir Rotulos -->
        <script type="text/javascript" src="../Ticket/zebra/BrowserPrint-3.0.216.min.js"></script>
        <script type="text/javascript" src="../Ticket/zebra/BrowserPrint-Zebra-1.0.216.min.js"></script>      
        <script type="text/javascript" src="../Ticket/Procesos/js/ticketscript.js"></script>
        <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&v=weekly"
        defer></script>
  </body>
</html>