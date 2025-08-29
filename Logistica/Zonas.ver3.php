<?
session_start();
include("../ConexionBD.php");
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Poligono</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="../hyper/dist/assets/images/favicon.ico">

        <!-- App css -->
        <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
    </head>
    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false}'>
        <!-- Begin page -->
        <div class="wrapper">
            <div class="content-page">
                <div class="content">
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
                    <!-- Topbar Start -->
                    <!-- Start Content-->
                    <div class="container-fluid">
                      <div id="info-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-information h1 text-info"></i>
                                        <h4 class="mt-2">Estamos moviendo los registros !</h4>
                                        <p id="info-alert-body" class="mt-3"> No cierres esta ventana. </p>
                                        <div class="spinner-grow text-primary" role="status"></div>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                        <!-- Center modal -->
                      
                      <div class="modal fade" id="renderizar-modal" tabindex="-1" role="dialog" aria-hidden="true">
                          <div class="modal-dialog modal-dialog-centered">
                              <div class="modal-content">
                                  <div class="modal-header">
                                      <h4 class="modal-title" id="myCenterModalLabel">Mover servicios a Recorrido</h4>
                                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                  </div>
                                  <div class="modal-body">
                                    <div class="col-lg-12 mt-3">
                                    <h5 id="texto_confirmacion"></h5>
                                    <div class="selector-recorrido form-group">
                                      <label>Seleccionar Recorrido</label>   
                                      <select id="recorrido_t" name="recorrido_t" class="form-control" data-toggle="select2" required></select>
                                      </select>
                                    </div>
                                    
                                  </div>
                                    <div class="button-list text-right">
                                        <input type="hidden" id="zona_rec">
                                      <button id="renderizar_ok" type="button" class="btn btn-primary">Aceptar</button>
                                      <button id="zona_rec_button" type="button" class="btn btn-warning" style="display:none">Aceptar</button>
                                  </div>
                                  </div>
                              </div><!-- /.modal-content -->
                          </div><!-- /.modal-dialog -->
                      </div><!-- /.modal -->
                      <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Logistica</a></li>
<!--                                             <li class="breadcrumb-item"><a href="javascript: void(0);"></a></li> -->
                                            <li class="breadcrumb-item active">Salidas de Hoy</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Salidas de Hoy <script>document.write(new Date().getUTCDate()+'.'+(new Date().getUTCMonth()+1)+'.'+new Date().getUTCFullYear())</script></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        <div class="row">
                           <div class="col-xl-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title mb-3">Seleccione los recorridos </h4>
                                      
                                      <!-- Multiple Select -->
                                          <div class="col-lg-12 mt-3">
                                            <div class="selector-recorrido1 form-group">
                                        <select id="select_rec_mapa" class="select2 form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Seleccionar Recorridos ...">
                                      <label>Seleccionar Recorrido</label>   
                                      <select id="recorrido_m" name="recorrido_m" class="form-control" data-toggle="select2" required></select>
                                      </select>
                                        </div>
                                      </div>
                                    <h4 class="header-title mb-3">Seleccione la Cantidad de Zonas </h4>
                                    <div class="row col-12 mt-1 ml-1">
                                        <button id="1" type="button" class="btn btn-dark"><i class="mdi mdi-24px mdi-numeric-1-circle"></i> </button>
                                        <button id="2" type="button" class="btn btn-dark ml-1"><i class="mdi mdi-24px mdi-numeric-2-circle"></i> </button>
                                        <button id="3" type="button" class="btn btn-dark ml-1"><i class="mdi mdi-24px mdi-numeric-3-circle"></i> </button>
                                        <button id="4" type="button" class="btn btn-dark ml-1"><i class="mdi mdi-24px mdi-numeric-4-circle"></i> </button>
                                        <button id="5" type="button" class="btn btn-dark ml-1"><i class="mdi mdi-24px mdi-numeric-5-circle"></i> </button>
                                    </div>
                                    <div class="row col-12 mt-1 ml-1">
                                        <button id="6" type="button" class="btn btn-dark"><i class="mdi mdi-24px mdi-numeric-6-circle"></i> </button>
                                        <button id="7" type="button" class="btn btn-dark ml-1"><i class="mdi mdi-24px mdi-numeric-7-circle"></i> </button>
                                        <button id="8" type="button" class="btn btn-dark ml-1"><i class="mdi mdi-24px mdi-numeric-8-circle"></i> </button>
                                        <button id="9" type="button" class="btn btn-dark ml-1"><i class="mdi mdi-24px mdi-numeric-9-circle"></i> </button>
                                        <button id="10" type="button" class="btn btn-dark ml-1"><i class="mdi mdi-24px mdi-numeric-10-circle"></i> </button>
                                        <button id="12" type="button" class="btn btn-dark ml-1"><i class="mdi mdi-24px mdi-numeric-12-circle"></i> </button>
                                    </div>
                                    
                                    <div class="mt-3"><i class="mdi mdi-square-rounded" style="color:rgb(63,91,169)"></i><a><b>Zona 1:</b><i onclick="asignar_zona(1)" class="mdi mdi-alpha-r-box text-warning"></i><span val="" class="badge badge-warning badge-pill 1"></span><b id="total_1"></b></a></div>
                                    <div><i class="mdi mdi-square-rounded" style="color:RGB(0,157,87)"></i><a><b>Zona 2: </b><i onclick="asignar_zona(2)" class="mdi mdi-alpha-r-box text-warning"></i><span class="badge badge-warning badge-pill 2"></span><b id="total_2"></b></a></div>
                                    <!-- <i class="mdi mdi-square-rounded"></i> -->
                                    <div><i class="mdi mdi-square-rounded" style="color:RGB(166,27,74)"></i><b>Zona 3: </a></b><i onclick="asignar_zona(3)" class="mdi mdi-alpha-r-box text-warning"></i><span class="badge badge-warning badge-pill 3"></span> <a><b id="total_3"></b></a></div>
                                    <div><i class="mdi mdi-square-rounded" style="color:RGB(0,157,87)"></i><b>Zona 4: </a></b><i onclick="asignar_zona(4)" class="mdi mdi-alpha-r-box text-warning"></i><span class="badge badge-warning badge-pill 4"></span><a><b id="total_4"></b></a></div>
                                    <div><i class="mdi mdi-square-rounded" style="color:RGB(244,235,55)"></i><b>Zona 5: </a><i onclick="asignar_zona(5)" class="mdi mdi-alpha-r-box text-warning"></i><span class="badge badge-warning badge-pill 5"></span></b><a><b id="total_5"></b></a></div>
                                    <div><i class="mdi mdi-square-rounded" style="color:RGB(124,53,146)"></i><b>Zona 6: </a><i onclick="asignar_zona(6)" class="mdi mdi-alpha-r-box text-warning"></i><span class="badge badge-warning badge-pill 6"></span></b><a><b id="total_6"></b></a></div>
                                    <div><i class="mdi mdi-square-rounded" style="color:RGB(238,156,150)"></i><b>Zona 7: </a><i onclick="asignar_zona(7)" class="mdi mdi-alpha-r-box text-warning"></i><span class="badge badge-warning badge-pill 7"></span></b><a><b id="total_7"></b></a></div>
                                    <div><i class="mdi mdi-square-rounded" style="color:RGB(121,80,70)"></i><b>Zona 8: </a></b><i onclick="asignar_zona(8)" class="mdi mdi-alpha-r-box text-warning"></i><span class="badge badge-warning badge-pill 8"></span><a><b id="total_8"></b></a></div>
                                    <div><i class="mdi mdi-square-rounded" style="color:RGB(219,68,54)"></i><b>Zona 9: </a></b><i onclick="asignar_zona(9)" class="mdi mdi-alpha-r-box text-warning"></i><span class="badge badge-warning badge-pill 9"></span><a><b id="total_9"></b></a></div>
                                    <div><i class="mdi mdi-square-rounded" style="color:RGB(147,215,232)"></i><b>Zona 10: </a></b><i onclick="asignar_zona(10)" class="mdi mdi-alpha-r-box text-warning"></i><span class="badge badge-warning badge-pill 10"></span><a><b id="total_10"></b></a></div>
                                    <div><i class="mdi mdi-square-rounded" style="color:RGB(147,215,232)"></i><b>Zona 12: </a></b><i onclick="asignar_zona(12)" class="mdi mdi-alpha-r-box text-warning"></i><span class="badge badge-warning badge-pill 10"></span><a><b id="total_12"></b></a></div>
                                    <div><b>Total de Clientes en Zonas:</b><b id="total_zonas"></b></a></div>
                                    
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                          
                           <div class="col-xl-8">
                                <div class="card">
                                    <div class="card-body">
                                      <div class="dropdown float-right">
<!--                                         <a id="header-title2" class="header-title mb-3"></a> -->
                                        <i id="marker" class="mdi mdi-18px mdi-map-marker"></i>
                                            <a id="cantidad" class="header-title- mb-3 card-drop"></a>
<!--                                         <input type='number' id="cantidad_n"> -->
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <!-- <div class="dropdown-menu dropdown-menu-right"> -->
                                                <!-- item-->
                                                <!-- <a id="cambiar_recorrido" role="button" class="dropdown-item">Cambiar Recorrido</a> -->
                                                <!-- item-->
<!--                                                 <a id="todos_recorrido" role="button" class="dropdown-item"> Ver Todos</a> -->
                                                <!-- item-->
<!--                                                 <a id="asignacion_recorrido" role="button" class="dropdown-item">Asignar</a> -->
                                                <!-- item-->
<!--                                                 <a href="javascript:void(0);" class="dropdown-item">Action</a> -->
                                            <!-- </div> -->
                                        </div>
                                            <h4 class="header-title mb-3">Zonas Google Map </h4>                                
                                        <div id="map" class="gmaps" style="min-height: 400px;"></div>

                                        <div class="tab-content">
                                            <div class="tab-pane show active mb-3" id="default-buttons-preview">
                                                <div class="button-list text-right mt-2">
                                                    <!-- <button type="button" class="btn btn-primary" data-toggle="modal"  data-target="#zona-modal">Agregar Zona</button> -->
<!--                                                     <button type="button" class="btn btn-secondary" data-toggle="modal"  data-target="#renderizar-modal">Secondary</button> -->
                                                    <button id="ver_mapa" type="button" class="btn btn-secondary" >Actualizar Mapa</button>
                                                    <button id="cambiar_recorrido" type="button" class="btn btn-success" >Confirmar Nueva Asignacion</button>
                                                </div>
                                             </div>
                                            </div>
                                      



                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!--end col-->
                        </div>
                        <!-- end row-->
                    </div> 
                    <!-- container -->

                </div> 
                <!-- content -->
                
                

                <!-- Footer Start -->
                <!-- <footer class="footer">
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
                </footer> -->
                <!-- end Footer -->
            </div>
        </div>
        <!-- END wrapper -->
        <!-- bundle -->
        <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/app.min.js"></script>
        <!-- third party js -->
        <!-- google maps api -->
        <script src="../hyper/dist/saas/assets/js/vendor/gmaps.min.js"></script>
        <!-- third party js ends -->
        <script src="../Menu/js/funciones.js"></script>
        <!-- <script src="Mapas/js/zonas.js"></script> -->
        <script src="Mapas/js/zonas.poly3.js"></script>
        <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
        <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap&libraries=geometry">
        </script>
  </body>
</html>