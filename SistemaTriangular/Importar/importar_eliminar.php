<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Importaciones</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--         <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" /> -->
        <meta content="Coderthemes" name="author" />
        <!-- App favicon -->
<!--         <link rel="shortcut icon" href="assets/images/favicon.ico"> -->
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
        <link href="assets/sticky-footer-navbar.css" rel="stylesheet">
        <link href="assets/style.css" rel="stylesheet">
<!--         <link href="../hyper/dist/saas/assets/css/bootstrap.min.css" rel="stylesheet"> -->

    </head>
    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false}'>
    <!-- <body class="loading" data-layout="topnav" data-layout-config='{layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}' > -->
      <!-- Begin page -->

        <div class="wrapper">
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
<!--                   WARNING MODAL -->
                  <div id="warning-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header modal-colored-header bg-warning">
                                <h4 class="modal-title" id="warning-modal-title"><i class="mdi mdi-trash-can-outline"></i> Confirmar Eliminar Registro <a id="id_eliminar"></a></h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div id="warning-modal-body" class="modal-body">
                               
                              </div>
                              <div class="modal-footer">
                                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                                  <button id="warning-modal-ok"type="button" class="btn btn-danger">Eliminar</button>
                              </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
<!--                   IMPORTAR MODAL -->
                  <div id="importar-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header modal-colored-header bg-warning">
                                <h4 class="modal-title" id="warning-modal-title"><i class="mdi mdi-application-import"></i> Confirmar Importar Registro <a id="id_eliminar"></a></h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div id="importar-modal-body" class="modal-body">
                               
                              </div>
                              <div class="modal-footer">
                                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                                  <button id="importar-modal-ok"type="button" class="btn btn-danger">Aceptar</button>
                              </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
                  
                    <div id="info-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                    <i class="dripicons-information h1 text-info"></i>
                                    <h4 class="mt-2">Estamos subiendo el Excel !</h4>
                                    <p id="info-alert-body" class="mt-3"> No cierres esta ventana. </p>
                                    <div class="spinner-grow text-primary" role="status"></div>
<!--                                   <button type="button" class="btn btn-info my-2" data-dismiss="modal">Continue</button> -->
                                  </div>
                              </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
              <!-- Begin page content -->
              <div id="success-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                  <div class="modal-dialog modal-sm modal-dialog-centered">
                      <div class="modal-content">
                          <div class="modal-body p-4">
                              <div class="text-center">
                                  <i class="dripicons-calendar h1"></i>
                                  <h4 class="mt-2">Fechas Disponibles !</h4>
                                  <p id="success-info" class="mt-3"></p>
                                  <span id="fecha_1" class="badge bg-secondary text-light" style="cursor:point">Primary</span>    
                                  <span id="fecha_2" class="badge bg-secondary text-light" style="cursor:point">Primary</span>    
                                  <span id="fecha_3" class="badge bg-secondary text-light" style="cursor:point">Primary</span>    
                                  <span id="fecha_4" class="badge bg-secondary text-light" style="cursor:point">Primary</span>    
                                  <span id="fecha_5" class="badge bg-secondary text-light" style="cursor:point">Primary</span>    
                                  <button type="button" class="btn btn-light my-2" data-dismiss="modal">Continue</button>
                              </div>
                          </div>
                      </div><!-- /.modal-content -->
                  </div><!-- /.modal-dialog -->
              </div><!-- /.modal -->
              <style>
              .modal{
              z-index: 20;   
              }
              .modal-backdrop{
              z-index: 10;        
              }
              </style>
                <!-- //MODIFICAR DIRECCION -->
                  <div class="modal fade" id="standard-modal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header">
                                  <h4 class="modal-title" id="myCenterModalLabel">Actualizar Cliente</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div class="modal-body">
                               <div class="col-lg-12 mt-3">
                                 <label>Direccion</label>
                                  <input type='text' class="form-control" name='direccion_nc' id='direccion_nc' placeholder='Direccion: Calle Numero'>                                          
                                  <input type='hidden' name='Calle_nc'  id='Calle_nc'>
                                  <input type='hidden' name='Barrio_nc' id='Barrio_nc'>
                                  <input type='hidden' name='Numero_nc' id='Numero_nc'>
                                  <input type='hidden' name='ciudad_nc' id='ciudad_nc'>
                                  <input type='hidden' name='cp_nc'     id='cp_nc'>
                                  <input type='hidden' name='id_nc'     id='id_nc'>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                <button id="modificardireccion_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
                  
                  
                  <div class="container">                      
                    <h3 class="mt-5">IMPORTACION DE CLIENTES Y VENTAS CADDY N  <a id="num_comp"><?php echo $_SESSION['NumeroComprobante'];?></a></h3>
                    <hr>
                    <div class="row">
                      <div class="col-12 col-md-12"> 
                        <div class="card">
                        <div class="card-body" id="outer">
                            <div class="col-12 col-md-12">
                              <div class="form-group">
                                <label>Archivo</label>
                                <p class="text-muted font-13">
                                    Para importar archivos de excel a la base de datos del sistea, deberas realizarlo con un archivo compatible. Podes descargar un ejemplo de plantilla desde aqui 
                                  <code><a href="https://www.caddy.com.ar/SistemaTriangular/Importar/example.xlsx" download="ejemplo">ejemplo.xlsx</a></code>  
                                </p>
                              </div>
                            </div>



                          <form class="col-12 col-md-12" id="frmExcelImport" enctype="multipart/form-data">
                            <div class="row">  
                               <div class="col-lg-4 mt-2 mb-2">
                                <label>Cliente Origen</label>
                                  <select name="relacion_nc" id="relacion_nc" class="form-control select2" data-toggle="select2" >
                                    <option value="">Seleccione un Cliente para la Relacion</option>  
                                <!-- <option value="36">UNO</option> -->
                                      <!-- // $sqlclientes=mysqli_query($con,"SELECT id,nombrecliente FROM Clientes");
                                      //   while($row = mysqli_fetch_array($sqlclientes)){
                                      //   echo "<option value='$row[id]'>$row[nombrecliente]</option>";    
                                      //   } -->
                                      
                                 </select>
                              </div>
                              <div class="col-lg-4 mt-2 mb-2">
                                <label>Tarifa</label>
                                  <select name="tarifa" id="tarifa" class="form-control select2" data-toggle="select2" >
                                    <option value="">Seleccione una Tarifa</option>  
                                <!-- <option value="36">Tarifa</option> -->
                                      <!-- // $sqlclientes=mysqli_query($con,"SELECT id,nombrecliente FROM Clientes");
                                      //   while($row = mysqli_fetch_array($sqlclientes)){
                                      //   echo "<option value='$row[id]'>$row[nombrecliente]</option>";    
                                      //   } -->
                                      
                                 </select>
                              </div>
                                    <div class="col-lg-4 mt-2 mb-2">
                                        <label>Fecha Entrega</label> 
                                        <input class="form-control" type="date" name="fecha_nc" id="fecha_nc">
                                    </div>
                                    <div class="col-lg-4 mt-2 mb-2">
                                        <label>Usar Recorrido según Fecha </label> 
                                        <input class="form-control" type="date" name="fecha_rec" id="fecha_rec">
                                    </div>    
                                    </div>                                   
                                    
                              <div>
                                  <div class="row">
                                    <div class="col-12 mt-2">
                                     <label>Elija Archivo Excel</label> <input type="file" name="file" id="file" accept=".xls,.xlsx,.csv">
                                    </div>
                                    <div class="col-12 right">
                                     <!-- <button type="submit" class="btn btn-success float-right" data-toggle="modal" data-target="#info-alert-modal">Importar Registros</button> -->
                                     <button type="submit" class="btn btn-success float-right" >Importar Registros</button>
                                    </div>
                                </div>
                            </div> 
                          </form>

                      </div>
                        </div>
                            <div id="response">
                            </div>
                            <div id="tableContainer">
                            </div> <!-- Contenedor para la tabla generada -->
                          </div>

                       <div class="col-lg-12 mt-0 mb-2">
                        <div id="response" class="<?php if(!empty($type)) { echo $type . " display-block"; } ?>"><?php if(!empty($message)) { echo $message; } ?></div>
                        <input id="totalregistros" type="hidden" value="<?php echo $total;?>">
                      </div>
                    </div>

                    <!-- <div id="tabla_group" style="display:none"> -->
                           <!-- <div class="row"> -->
                                <!-- <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1"> -->
                                  <!-- <div class="card"> -->
                                      <!-- <div class="card-body">
                                          <h4 id="seguimiento_header" class="header-title mt-2">ENTREGAS | RECORRIDO </h4>
                                          <table class="table table-centered mb-0" id="seguimiento_group" style="font-size:12px">
                                            <thead>
                                                <tr>
                                                  <th>Fecha</th>
                                                  <th>Usuario</th>
                                                  <th>Cliente Origen</th>                                                  
                                                  <th>Numero Comprobante</th>
                                                  <th>Total</th> 
                                                  <th>Accion</th>                                                  
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div> -->
                                <!-- </div> -->
                           <!-- </div> -->
                      <!-- </div> -->
                    <!-- </div>   -->


                      <div class="row" id="card" style="display:none">
                        <div class="col-md-4 mt-2">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                      <h3 class="card-title" id="clientesnuevos_card"><?php echo $total;?> Registros Nuevos</h3> 
                                      <blockquote  class="card-bodyquote">
                                            <p>Estos clientes se cargaron en la tabla Pre Ventas. Numero <?php echo $_SESSION['NumeroComprobante'];?>.</p>
                                        </blockquote>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        
                            <div class="col-md-5 mt-2">
                                <div class="card text-white bg-danger">
                                    <div class="card-body">
                                      <h3 class="card-title" id="clientesexistentes_card"><?php echo $total_ns;?> Registros Duplicados</h3>    
                                      <blockquote  class="card-bodyquote">
                                            <p>Estos clientes No se cargaran en la tabla Pre Ventas, ya que generarian registros duplicados.</p>
                                        </blockquote>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        <div class="col-md-3 mt-2">
                                <div class="card text-white bg-warning">
                                    <div class="card-body">
                                      <h3 class="card-title" id="ventas_card"><?php echo $total_ns+$total;?> Registros</h3>    
                                      <blockquote  class="card-bodyquote">
                                            <p>Total de Registros contabilizados en el archivo importado.</p>
                                        </blockquote>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div>
                        <!-- end row -->

                    <?php
                    // $sqlSelect = "SELECT * FROM PreVenta WHERE Cargado=0 AND Eliminado=0 ORDER BY id ";
                    // $result = mysqli_query($con, $sqlSelect);
                    // if (mysqli_num_rows($result) > 0)
                    // {
                    ?>
                        <!-- <div id="tabla">
                           <div class="row">
                                <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                  <div class="card">
                                      <div class="card-body">
                                          <h4 id="seguimiento_header" class="header-title mt-2">ENTREGAS | RECORRIDO </h4>
                                          <table class="table table-centered mb-0" id="seguimiento" style="font-size:12px">
                                            <thead>
                                                <tr>
                                                  <th>Datos Importacion</th>
                                                  <th>Cantidad</th>
                                                  <th>Total</th>
                                                  <th>Recorrido</th>
                                                  <th>F.Entrega</th>
                                                  <th>Estado</th>
                                                  <th>Accion</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                           </div> -->
                    <div class="tab-content mb-2">
                           <div class="tab-pane show active" id="default-buttons-preview">
                              <div class="button-list text-right">
                                <!-- <a id="VaciarTabla" class="btn btn-danger">Vaciar Tabla</a>  -->
                                <!-- <a id="ImportarTabla" class="btn btn-warning text-white">Confirmar Importacion</a>    -->
                                <!-- <a id="VerAgrupados" class="btn btn-warning text-white">Ver Agrupados</a> -->
                                <a id="submitMapping" class="btn btn-success text-white">Comenzar</a>
                                <!-- <a id="Eliminar_lote" class="btn btn-danger text-white">Eliminar Todas</a> -->
                                <!-- <a href="https://www.caddy.com.ar/SistemaTriangular/Importar/index2.php" class="btn btn-primary text-white">Volver a Empezar</a> -->
                            </div>
                          </div>
                        </div>
                      </div>
                  </div>  
      <!-- Start Content-->                    
      <!-- Fin Contenido --> 
    </div>
  </div>
  <!-- Fin row --> 

  
</div>
<!-- Fin container -->
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
        <script src="Procesos/js/funciones_.js"></script>
        <script src="../Menu/js/funciones.js"></script>
        <!-- Direcciones -->
        <!-- <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&libraries=places&callback=BuscarDireccion">
        </script> -->

  </body>
</html>