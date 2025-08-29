<?php
include_once "../Conexion/Conexioni.php";
if($_SESSION['Usuario']==''){
header('location:https://www.caddy.com.ar/sistema');    
}
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Pendientes</title>
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
    <body class="loading" data-layout="topnav" data-layout-config='{layoutBoxed":false,"darkMode":false}' >
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
                  <!-- //MODIFICAR RECORRIDO -->
                  <div class="modal fade" id="standard-modal-rec" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header modal-colored-header bg-primary">
                                  <h4 class="modal-title" id="myCenterModalLabel_rec">MODIFICAR RECORRIDO #</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                            <div class="col-lg-12 mt-3">
                              <div class="selector-recorrido form-group">
                                <label>Seleccionar Recorrido</label>   
                                <select id="recorrido_t" name="recorrido_t" class="form-control" data-toggle="select2" required></select>
                                </select>
                              </div>
                            </div>
                            <div class="modal-footer mt-3">
                                <input type="hidden" id="cs_modificar_REC">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                <button id="modificarrecorrido_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </div>
                    </div>
                  </div>                  

                <!-- //MODIFICAR-->
                <div class="modal fade" id="standard-modal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog  modal-lg modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header">
                                  <h4 class="modal-title" id="myCenterModalLabel_modificar">MODIFICAR #</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                            <form id="form">
                              <div class="modal-body mb-3">
                               <div class="row">
                                <div class="col-lg-4 mt-3">
                                  <input id="id_trans" type="hidden">

                                  <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="entregado" name="entregado">
                                    <label id="entregado_t_label" class="custom-control-label" for="entregado" data-on-label="1" data-off-label="0">Entregado al Cliente</label>
                                 </div>
                                 </div>
                               <div class="col-lg-4 mt-3">
                                 <div class="form-group">
                                <label>Fecha Entrega</label>
                                   <input type="text" class="form-control date" id="fecha_receptor" data-toggle="date-picker" data-single-date-picker="true" name="fecha_receptor">
                                 </div>
                                 </div>
                               <div class="col-lg-3 mt-3">
                                 <div class="form-group">
                                  <label>Hora de Entrega</label>
                                  <div class="input-group">
                                      <input type="text" class="form-control" data-toggle='timepicker' data-show-meridian="false" id="hora_receptor" name="hora_receptor">
                                      <div class="input-group-append">
                                          <span class="input-group-text"><i class="dripicons-clock"></i></span>
                                      </div>
                                  </div>
                              </div>
                                 </div>
                                </div>
                                <div class="row">
                                 <div class="col-lg-4 mt-3">
                                 <div class="custom-control custom-switch">
                                <label>Nombre Receptor</label>
                                   <input type="text" class="form-control" id="nombre_receptor" name="nombre_receptor">
                                 </div>
                                 </div>
                               <div class="col-lg-4 mt-3">
                                 <div class="custom-control custom-switch">
                                  <label>Dni Receptor</label>
                                   <input type="text" class="form-control" id="dni_receptor" name="dni_receptor">
                                 </div>
                                 </div>
                                  <div class="col-lg-4 mt-3">
                                 <div class="custom-control custom-switch">
                                  <label>Observaciones</label>
                                   <input type="text" class="form-control" id="observaciones_receptor" name="observaciones_receptor">
                                 </div>
                                 </div>
                              </div>
                                
                                
                                <div class="row">
                                    <div class="col-lg-12 mt-3">
                                    <h4 id="myCenterModalLabel2" class="header-title mb-3"></h4>
                                        <div class="table-responsive">
                                        <table class="table dt-responsive nowrap w-100" style="font-size:10px" id="ventas_tabla">
                                            <thead class="thead-light">
                                            <tr>
                                                <th>id</th>
                                                <th>Fecha</th>
                                                <th>Codigo</th>
                                                <th>Titulo</th>
                                                <th>Total</th>
                                                <th>Accion</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        </div>
                                    </div> <!-- end col -->
                                </div>
                                
                              <div class="modal-footer mt-3">
                                <input type="hidden" id="id_modificar">
                                <button type="button" class="btn btn-light" data-dismiss="modal" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Tooltip on bottom">Cerrar</button>
                                <button id="modificardireccion_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                              </div>
                          </div>
                            </form>
                      </div>
                  <!-- </div> -->
                 </div>



                                    
                </div>
            </div>
        </div>

                <!-- Ventas modal -->
        <div class="modal fade" id="bs-ventas-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content modal-filled bg-primary">
                    <div class="modal-header">
                        <h4 class="modal-title" id="header-ventas-modal">Modificar Ventas</h4>
                        <input id="idPedido" type="hidden">
<!--                                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">x</button> -->
                    </div>
                        <div class="modal-body">
                            <div class="tab-content">
                                    <div class="tab-pane show active" id="input-masks-preview">
                                        <div class="row">
                                        
                                            <div class="col-md-12">
                                                <div class="row">
                                                
                                                    <div class="col-md-3">
                                                            <label class="form-label">Fecha</label>
                                                            <input id="ventas_fecha" type="date" class="form-control" data-toggle="input-mask" data-mask-format="00/00/0000">
                                                            <span class="font-13 text-muted">Ej.: "DD/MM/YYYY"</span>
                                                    </div>
                                            
                                            
                                                <div class="col-md-9">
                                                <label id="">Servicio</label>
                                                <select id="servicio" class="form-control servicio font-7" data-toggle="select2" Onchange="cargar(this.value)">
                                                    <option>Seleccione una Opcion</option>
                                                    <optgroup label="Tarifas Sistema">
                                                    <?php
                                                        // $sql=$mysqli->query("SELECT id,Titulo,PrecioVenta FROM Productos");
                                                        // while($row = $sql->fetch_array(MYSQLI_ASSOC)){
                                                        // $tarifa=number_format($row[PrecioVenta],2,'.',',');
                                                        // echo "<option style='font-size:6px' value='$row[id]'>$row[Titulo] | $ $tarifa</option>";    
                                                        // }
                                                    ?>                                  
                                                    </optgroup>
                                                </select>
                                                </div>
                                            </div>
                                            <!-- <div class="row">
                                                <div class="col-md-2">
                                                            <label class="form-label">Codigo</label>
                                                            <input id="ventas_codigo" type="number" class="form-control" data-toggle="input-mask" data-mask-format="00:00:00">
                                                            <span class="font-13 text-muted">Ej.: "0000000009"</span>
                                                </div>
                                                <div class="col-md-5">
                                                            <label class="form-label">Titulo</label>
                                                            <input id="ventas_titulo" type="text" class="form-control" readonly>
                                                            <span class="font-13 text-muted">Ej.: "FLETE INTERNO"</span>
                                                </div>
                                                <div class="col-md-5">
                                                            <label class="form-label">Observaciones</label>
                                                            <input id="ventas_observaciones" type="text" class="form-control">
                                                            <span class="font-13 text-muted">Ej.: "COMISION POR COBRANZA"</span>
                                                </div>
                                            </div> -->
                                                <!-- <div class="row">
                                                    <div class="col-md-3">
                                                                <label class="form-label">Cantidad</label>
                                                                <input value="1" id="ventas_cantidad" type="number" class="form-control">
                                                                <span class="font-13 text-muted">Ej.: "$ 350.00"</span>
                                                    </div>
                                                    <div class="col-md-4">
                                                                <label class="form-label">Precio</label>
                                                                <input id="ventas_precio" type="number" class="form-control" >
                                                                <span class="font-13 text-muted">Ej.: "$ 350.00"</span>
                                                    </div>
                                                    <div class="col-md-5">
                                                            <label class="form-label">Total</label>
                                                            <input id="ventas_total" type="text" class="form-control" data-toggle="input-mask" data-mask-format="000.000.000.000.000,00" data-reverse="true" readonly>
                                                            <span class="font-13 text-muted">Ej.: "$ 350.00"</span>
                                                    </div>
                                                </div>
                                            </div> -->
                                        </div>
                                    </div>
                            </div>
                            
                              <!-- <div class="modal-footer mt-3">
                                <input type="hidden" id="id_modificar">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                <button id="modificarventas_ok" type="button" class="btn btn-success">Guardar Cambios</button>
                                <button id="agregarventas_ok" type="button" class="btn btn-success" style="display:none">Agregar Venta</button>
                            </div> -->
                            </div>
                        </div>
                    </div>
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
        <!--    enlases externos para botonera-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <!--excel-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <!--pdf-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <!--pdf-->
        <!-- funciones -->
        <script src="Procesos/js/pendientes.js"></script>
        <script src="../Funciones/js/seguimiento.js"></script>
        <script src="../Menu/js/funciones.js"></script>
        <!-- Funciones Imprimir Rotulos -->
  </body>
</html>