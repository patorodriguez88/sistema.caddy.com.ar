<?
ob_start();
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
                                        <h4 class="header-title mb-3">Salidas con Fecha de Entrega Hoy</h4>
                                          <div class="accordion custom-accordion" id="custom-accordion-one">
                                              <?
                                                $sql=mysql_query("SELECT Recorrido,NombreChofer,Hora,HoraRetorno,Estado,SUM(KilometrosRegreso-Kilometros)as TotalKm FROM Logistica 
                                                WHERE Fecha=CURDATE() AND Eliminado='0' GROUP BY Recorrido ");
                                              while($row=mysql_fetch_array($sql)){
                                                $sqlcolor=mysql_query("SELECT ColorSistema FROM Vehiculos INNER JOIN Logistica ON Vehiculos.Dominio=Logistica.Patente 
                                                                     WHERE Fecha=CURDATE() AND Eliminado='0' AND Recorrido='$row[Recorrido]'");
                                                $color=mysql_fetch_array($sqlcolor);
                                                $sqlsuma=mysql_query("SELECT SUM(Debe)as Total,COUNT(id)as TotalServicios FROM TransClientes WHERE Recorrido='$row[Recorrido]' AND FechaEntrega=CURDATE() AND Eliminado='0'");
                                                $suma=mysql_fetch_array($sqlsuma);
                                              
                                              ?>
                                                <div class="card mb-0">
                                                      <div class="card-header" id="heading<? echo $row[Recorrido];?>">
                                                          <h5 class="m-0">
                                                              <a class="custom-accordion-title d-block py-1"
                                                                  data-toggle="collapse" href="#collapse<? echo $row[Recorrido];?>"
                                                                  aria-expanded="false" aria-controls="collapse<? echo $row[Recorrido];?>" style="color:<? echo $color[ColorSistema];?>">
                                                                <i class="uil-location-point"></i> Recorrido <? echo $row[Recorrido].' ('.$suma[TotalServicios].')';?>
                                                                <i class="mdi mdi-chevron-down accordion-arrow"></i>
                                                              </a>
                                                          </h5>
                                                      </div>

                                                      <div id="collapse<? echo $row[Recorrido];?>" class="collapse"
                                                          aria-labelledby="heading<? echo $row[Recorrido];?>"
                                                          data-parent="#custom-accordion-one">
                                                          <div class="card-body">
                                                          <div><a><b>Estado Recorrido: </b> <? echo $row[Estado];?> </a></div>   
                                                          <div><a><b>Hora: </b> <? echo $row[Hora];?> hs. Hasta <? echo $row[HoraRetorno];?> </a></div> 
                                                          <div><a><b>Chofer: </b><? echo $row[NombreChofer];?> </a></div>
                                                          <div><a><b>Total: $ </b><? echo $suma[Total];?> </a></div>  
                                                          <div><a><b>Km. Totales: </b><? echo $row[TotalKm];?> </a></div>   
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
                                        <h4 class="header-title mb-3">Markers Google Map</h4>
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
        <script src="Mapas/js/mapa_recorridos.js"></script>
        <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap">
        </script>
  </body>
</html>