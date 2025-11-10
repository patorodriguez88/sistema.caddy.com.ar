<?
session_start();
include("../ConexionBD.php");

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Salidas Hoy</title>
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
                      <div class="modal fade" id="zona-modal" tabindex="-1" role="dialog" aria-hidden="true">
                          <div class="modal-dialog modal-dialog-centered">
                              <div class="modal-content">
                                  <div class="modal-header">
                                      <h4 class="modal-title" id="myCenterModalLabel">Agregar Zona</h4>
                                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                  </div>
                                  <div class="modal-body">
                                    <!-- Date Picker -->
                                  <div class="form-group mb-3">
                                      <label>Asigne un Nombre a la Zona</label>
                                      <input type="text" class="form-control" id="nombrezona">
                                      <span class="font-13 text-muted">Ej.: Zona1</span>
                                  </div>
                                  <div class="button-list text-right">
                                      <button id="agregarzonas" type="button" class="btn btn-primary">Aceptar</button>
                                  </div>
                                  </div>
                              </div><!-- /.modal-content -->
                          </div><!-- /.modal-dialog -->
                      </div><!-- /.modal -->
                      <div class="modal fade" id="renderizar-modal" tabindex="-1" role="dialog" aria-hidden="true">
                          <div class="modal-dialog modal-dialog-centered">
                              <div class="modal-content">
                                  <div class="modal-header">
                                      <h4 class="modal-title" id="myCenterModalLabel">Mover servicios a Recorrido</h4>
                                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                  </div>
                                  <div class="modal-body">
                                    <div class="col-lg-12 mt-3">
                                    <div class="selector-recorrido form-group">
                                      <label>Seleccionar Recorrido</label>   
                                      <select id="recorrido_t" name="recorrido_t" class="form-control" data-toggle="select2" required></select>
                                      </select>
                                    </div>
                                  </div>
                                    <div class="button-list text-right">
                                      <button id="renderizar_ok" type="button" class="btn btn-primary">Aceptar</button>
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
                                        <h4 class="header-title mb-3">Geolocalizacion Zonas </h4>
                                           <div class="tab-content">
                                            <div class="tab-pane show active mb-3" id="default-buttons-preview">
                                                <div class="button-list">
                                                    <button type="button" class="btn btn-primary" data-toggle="modal"  data-target="#zona-modal">Agregar Zona</button>
<!--                                                     <button type="button" class="btn btn-secondary" data-toggle="modal"  data-target="#renderizar-modal">Secondary</button> -->
                                              </div>
                                             </div>
                                              </div>
                                      
                                      <!-- Multiple Select -->
                                          <div class="col-lg-12 mt-3">
                                            <div class="selector-recorrido1 form-group">
                                        <select id="select_rec_mapa" class="select2 form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Seleccionar Recorridos ...">
                                      <label>Seleccionar Recorrido</label>   
                                      <select id="recorrido_m" name="recorrido_m" class="form-control" data-toggle="select2" required></select>
                                      </select>
                                        </div>
                                      </div>
                                          <div class="accordion custom-accordion" id="custom-accordion-one">
                                              <?
                                                $sql=mysql_query("SELECT * FROM ZonasMapa");
                                              while($row=mysql_fetch_array($sql)){
                                              ?>
                                                <div class="card mb-0">
                                                      <div class="card-header" id="<? echo $row[Nombre];?>">
                                                          <h5 class="m-0">
                                                              <a class="custom-accordion-title d-block py-1"
                                                                  data-toggle="collapse" href="#collapse<? echo $row[Nombre];?>"
                                                                  aria-expanded="false" aria-controls="collapse<? echo $row[Nombre];?>"
                                                                <i class="uil-location-point"></i> <? echo $row[Nombre];?>
                                                                <i class="mdi mdi-chevron-down accordion-arrow"></i>
                                                              </a>
                                                          </h5>
                                                      </div>

                                                      <div id="collapse<? echo $row[Nombre];?>" class="collapse"
                                                          aria-labelledby="heading"
                                                          data-parent="#custom-accordion-one">
                                                          <div class="card-body">
                                                          <div><a><b>Latitud Norte: </b> <? echo $row[LatitudN];?> </a></div>   
                                                          <div><a><b>Latitud Sur: </b> <? echo $row[LatitudS];?> </a></div> 
                                                          <div><a><b>Longitud Este: </b><? echo $row[LongitudE];?> </a></div>
                                                          <div><a><b>Longitud Oeste: </b><? echo $row[LongitudO];?> </a></div>  
                                                          </div>
                                                      </div>
                                                  </div>
                                               <?
                                                }    
                                                ?>
                                              </div>
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
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <!-- item-->
                                                <a id="cambiar_recorrido" role="button" class="dropdown-item">Cambiar Recorrido</a>
                                                <!-- item-->
<!--                                                 <a id="todos_recorrido" role="button" class="dropdown-item"> Ver Todos</a> -->
                                                <!-- item-->
<!--                                                 <a id="asignacion_recorrido" role="button" class="dropdown-item">Asignar</a> -->
                                                <!-- item-->
<!--                                                 <a href="javascript:void(0);" class="dropdown-item">Action</a> -->
                                            </div>
                                        </div>
                                            <h4 class="header-title mb-3">Zonas Google Map </h4>                                
                                        <div id="map" class="gmaps" style="min-height: 400px;"></div>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div>
                        <!-- end row-->
                    </div> <!-- container -->

                </div> <!-- content -->
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
        <script src="Mapas/js/zonas.js"></script>
        <!-- <script src="Mapas/js/zonas.poly.js"></script> -->
        <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>


        <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBFDH8-tnISZXhe9BAfWw9BS-uzCv9yhvk&callback=initMap">
        </script>
  </body>
</html>