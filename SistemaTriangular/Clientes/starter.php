<?php
session_start();
include_once('../Conexion/Conexioni.php');

if ($_SESSION['NombreUsuario']==''){
header("location:https://www.caddy.com.ar/sistema");
}
?>
  <!DOCTYPE html>
  <html lang="es">

  <head>
    <meta charset="utf-8" />
    <title>Caddy | Clientes </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="../images/favicon/favicon.ico">

    <!-- App css -->
    <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
    <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />

     <!-- third party css -->
    <link href="../hyper/dist/saas/assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    
  </head>

  <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false}'>
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
            <div class="row">
              <div class="col-lg-12 mt-3">
                <div class="page-title-box">
                  <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                      <li class="breadcrumb-item"><a href="javascript: void(0);">Clientes</a></li>
                      <li class="breadcrumb-item">Datos</a>
                      </li>
                    </ol>
                  </div>
                  <h4 class="page-title">Clientes</h4>
                </div>
              </div>
            </div>
            <!-- end page title -->
        
        <!-- Danger Alert Modal -->
            
            <div id="danger-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content modal-filled bg-danger">
                        <div class="modal-body p-4">
                            <div class="text-center">
                                <i class="dripicons-wrong h1"></i>
                                <h4 class="mt-2">Error 401</h4>
                                <p class="mt-3">No cuentas con nivel suficiente para autorizar esta solicitud.</p>
                                <button id="danger-alert-modal-button" type="button" class="btn btn-light my-2" data-bs-dismiss="modal">Continue</button>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        
            <!-- //MODIFICAR GUIAS DE CARGA FACTURACION-->
                  <div class="modal fade" id="standard-modal-modificar" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog  modal-lg modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header">
                                  <h4 class="modal-title" id="myCenterModalLabel">MODIFICAR #</h4>
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
<!--                                             <div class="card"> -->
<!--                                                 <div class="card-body"> -->
                                                    <h4 id="myCenterModalLabel2" class="header-title mb-3"></h4>
                                                     <div class="table-responsive">
                                                       <table class="table dt-responsive nowrap w-100" style="font-size:10px" id="ventas_tabla">
<!--                                                         <table class="table table-sm table-centered mb-0" style="font-size:10px" id="ventas_tabla"> -->
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
                                                    <!-- end table-responsive -->
<!--                                                 </div> -->
<!--                                             </div> -->
                                        </div> <!-- end col -->
                                    </div>
                                
                              <div class="modal-footer mt-3">
                                <!-- <input type="hidden" id="id_modificar"> -->
                                <button type="button" class="btn btn-light" data-dismiss="modal" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Tooltip on bottom">Cerrar</button>
                                <button id="modificardireccion_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                          </div><!-- /.modal-content -->
                            </form>
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
                 </div>
        
      <!-- MODIFICAR VENTAS-->
        <div class="modal fade" id="bs-ventas-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content modal-filled bg-primary">
                            <div class="modal-header">
                                <h4 class="modal-title" id="header-ventas-modal">Modificar Ventas</h4>
<!--                                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">x</button> -->
                            </div>
                            <div class="modal-body">
                            <form id="form_bs-ventas-modal-lg">


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
                                                          $sql=$mysqli->query("SELECT id,Titulo,PrecioVenta FROM Productos");
                                                          while($row = $sql->fetch_array(MYSQLI_ASSOC)){
                                                          $tarifa=number_format($row[PrecioVenta],2,'.',',');
                                                          echo "<option style='font-size:6px' value='$row[id]'>$row[Titulo] | $ $tarifa</option>";    
                                                          }
                                                        ?>                                  
                                                      </optgroup>
                                                  </select>
                                                   </div>
                                                </div>
                                                <div class="row">
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
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                              <label class="form-label">Cantidad</label>
                                                              <input value="1" id="ventas_cantidad" type="number" class="form-control">
                                                              <span class="font-13 text-muted">Ej.: "2"</span>
                                                    </div>
                                                    <div class="col-md-4">
                                                              <label class="form-label">Precio</label>
                                                              <input id="ventas_precio" type="number" class="form-control">
                                                              <span class="font-13 text-muted">Ej.: "$ 350.00"</span>
                                                    </div>
                                                    <div class="col-md-5">
                                                              <label class="form-label">Total</label>
                                                              <input id="ventas_total" type="number" class="form-control" readonly>
                                                              <span class="font-13 text-muted">Ej.: "$ 700.00"</span>
                                                      </div>
                                                 </div>
                                              </div>
                                          </div>
                                      </div>
                                </div>
                              <div class="modal-footer mt-3">
                                <input type="hidden" id="id_modificar">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                <button id="modificarventas_ok" type="button" class="btn btn-success">Guardar Cambios</button>
                                <button id="agregarventas_ok" type="button" class="btn btn-success" style="display:none">Agregar Venta</button>
                            </div>
                              </form>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
      
              <!--ELIMINAR REGISTRO VENTAS MODAL-->
                    <div id="warning-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header modal-colored-header bg-warning">
                                <h4 class="modal-title" id="warning-header-modalLabel"><i class="mdi mdi-trash-can-outline"></i> Confirmar Eliminar Registro</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div id="warning-modal-body" class="modal-body">
                              
                             </div>
                            <input type="hidden" id="id_eliminar">  
                            <input type="hidden" id="codigoseguimiento_eliminar">  
                            <div class="modal-footer">
      
                              <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                              <button id="warning-modal-ok"type="button" class="btn btn-danger">Eliminar</button>
                              <button id="warning-modal-ventas-ok" type="button" class="btn btn-danger" style="display:none">Eliminar Ventas</button>
                            </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
        
<!--         //MODAL PARA INGRESAR RECORRIDOS A FACTURACION -->
            <div class="modal fade" id="bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Asignar Salidas a la Cuenta Corriente del Cliente</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body">
                        
                         <div class="table-responsive"> 
                                <table class="table dt-responsive nowrap w-100" id="asignacion_salidas" style="font-size:10px;margin: 0 auto;clear: both;">
                                  <thead>
                                      <tr>
                                        <th>Fecha</th>
                                        <th>Recorrido</th>
                                        <th>Numero de Orden | Recorrido</th>
                                        <th>Descripcion</th>
                                        <th>Importe</th>
                                        <th style="width: 20px;">
                                          <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck_recorridos">
                                            <label class="custom-control-label" for="customCheck_recorridos">&nbsp;</label>
                                          </div>
                                        </th>
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
                                    </tr>
                                  </tbody>
                                </table>
                               </div> 
                          
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->        
        <!-- //MODIFICAR CODIGO CLIENTE -->
            <div class="modal fade" id="standard-modal-codcliente" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header modal-colored-header bg-primary">
                            <h4 class="modal-title" id="myCenterModalLabel_codcliente"></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                          <div class="col-lg-12 mt-3">

                          <label>Codigo de Cliente</label>
                          <input id="codigocliente_t" type="text" class="form-control" data-toggle="input-mask">
                          <span class="font-13 text-muted">Ej.: 123456</span>

                          </div>
                          <div class="modal-footer mt-3">
                              <input type="hidden" id="cs_codigocliente">
                              <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                              <button id="modificarcodigocliente_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                          </div>
                  </div>
              </div>
            </div>              
        
        <!-- SEGUIIENTO MODAL -->
            <div class="modal fade" id="modal_seguimiento" tabindex="-1" role="dialog" aria-hidden="true" style="display:none">
                <div class="modal-dialog modal-lg">
                    <div id="modal_seguimiento_content" class="modal-content bg-primary">
                        <div id="modal_seguimiento_header" class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Seguimiento</h4>
                            <div class="text-sm-right">
                                <button id="cambiar_estado" type="button" class="btn btn-light" aria-hidden="true">Cambiar Estado</button>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
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
        
        <div id="fill-warning-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fill-warning-modalLabel" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content modal-filled bg-warning">
                      <div class="modal-header">
                          <h4 class="modal-title" id="fill-warning-modalLabel">Cambiar Estado de Servicio</h4>
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                      </div>
                      <div class="modal-body">
                        Estas por cambiar el estado del servicio a No entregado.
                        <div class="custom-control custom-checkbox  custom-checkbox-success mb-2">
                            <input type="checkbox" class="custom-control-input" id="nueva_visita_check" checked>
                            <label class="custom-control-label" for="nueva_visita_check">Ingresar Cargo por Nueva Visita en Cuenta Corriente</label>
                        </div>
                          <input type="hidden" id="codigo_seguimiento">
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                          <button id="cambiar_estado_ok" type="button" class="btn btn-outline-light">Save changes</button>
                      </div>
                  </div><!-- /.modal-content -->
              </div><!-- /.modal-dialog -->
          </div><!-- /.modal -->
        
            <!--MODAL APLICAR DESCUENTO -->
            <div id="descuento-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header modal-colored-header bg-warning">
                    <h4 class="modal-title" id="standard-modalLabel">Aplicar Descuento a Comprobante</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-lg-12 mt-2">
                        <div class="form-group">
                          <label>Descuento</label>
                          <input id="descuentootorgado_t" type="text" class="form-control" data-toggle="input-mask" data-mask-format="00.00%">
                          <span class="font-13 text-muted">Ej.: 10%</span>
                        </div>
                        <div class="text-right">
                          <button id="confirmardescuento_botton" type="button" class="btn btn-warning"><i class="mdi mdi-check-bold mr-1"></i> <span>Aplicar Descuento</span> </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!--end modal-->
                </div>
                <!-- end col-->
              </div>
            </div>
        
        
            <!--MODAL CONFIRMAR FACTURACION -->
            <div id="info-header-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="info-header-modalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header modal-colored-header bg-info">
                    <h4 class="modal-title" id="info-header-modalLabel">Facturacion</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                  </div>
                  <div class="modal-body">
                    <div 
                    <div class="row">
                      <div class="col-lg-6 mt-3">
                        <div class="form-group">
                          <label for="simpleinput">Fecha del Comprobante</label>
                          <input type="date" id="fecha_up" class="form-control">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 mt-2">
                        <div class="form-group">
                          <label for="simpleinput">Razon Social</label>
                          <input type="text" id="razonsocial_up" class="form-control">
                        </div>
                      </div>
                       <div class="col-lg-4 mt-2"> 
                        <div class="form-group">
                        <label for="example-email">Cuit</label>
                        <input type="text" id="cuit_up" name="ncomprobante_up" class="form-control" placeholder="Numero de Comprobante">
                      </div>
                     </div>
                    </div>
                   <div class="row">


                    <!-- Single Select -->
<!--                     <div class="row" id="selectA" style="display:none"> -->
                      <div class="col-lg-6 mt-2" id="selectA" style="display:none">
                        <label for="comprobante_selectA">Comprobante</label>
                          <select id="comprobante_selectA" class="form-control select2" data-toggle="select2">
                            <option>Seleccione una Opción</option>
                              <optgroup label="Comprobantes Disponibles">
                              <option value="0">FACTURA PROFORMA</option>
                              <option value="1">FACTURAS A</option>
                              <option value="2">NOTAS DE DEBITO A</option>
                              <option value="3">NOTAS DE CREDITO A</option>                            
                            </optgroup>
                          </select>
                      </div>
<!--                     </div> -->
                    
<!--                      <div class="row" id="selectB" style="display:none"> -->
                      <div class="col-lg-6 mt-2" id="selectB" style="display:none">
                        <label for="comprobante_selectB">Comprobante</label>
                        <select id="comprobante_selectB" class="form-control select2" data-toggle="select2">
                          
                          <optgroup label="Comprobantes Disponibles">
                              <option value="0">FACTURA PROFORMA</option>
                              <option value="6">FACTURAS B</option>
                              <option value="7">NOTAS DE DEBITO B</option>
                              <option value="8">NOTAS DE CREDITO B</option>
                          </optgroup>
                      </select>
                       </div>
<!--                     </div> -->
                      <div class="col-lg-6 mt-2" id="comprobante_up_display">
                        <div class="form-group">
                          <label for="simpleinput">Comprobante</label>
                            <div class="input-group mb-3">
                              <input type="text" id="comprobante_up" class="form-control" readonly>
                              <div class="input-group-append">
                              <button id="modificar_comprobante" type="button" class="btn btn-outline-info"><i class="uil-exchange-alt"></i> Cambiar</button>
                            </div>
                          </div>
                        </div>
                      </div>
                     <input type="hidden" id="select_up" class="form-control" placeholder="Comprobante" readonly>
                     <div class="col-lg-6 mt-2"> 
                         <div class="form-group">
                           <label for="example-email">Numero de Comprobante</label>
                           <input type="text" id="ncomprobante_up" name="ncomprobante_up" class="form-control" data-toggle="input-mask" data-mask-format="00000-00000000" placeholder="Numero de Comprobante">
                           <span class="font-13 text-muted">E.j.: "00001-00000123"</span>
                         </div>
                     </div>
                   </div>
                   <div class="row">
                      <div class="col-lg-4 mt-2"> 
                        <div class="form-group">
                        <label for="neto_up">Importe Neto</label>
                         <input type="text" id="neto_up" class="form-control" data-toggle="input-mask" data-mask-format="000.000.000.000.000,00" data-reverse="true">
                      </div>
                     </div>
                       <div class="col-lg-4 mt-2"> 
                        <div class="form-group">
                        <label for="iva_up">Importe Iva</label>
                         <input type="text" id="iva_up" class="form-control" data-toggle="input-mask" data-mask-format="000.000.000.000.000,00" data-reverse="true">
                      </div>
                     </div>
                     <div class="col-lg-4 mt-2"> 
                       <div class="form-group">
                         <label for="total_up">Importe Total</label>
                         <input type="text" id="total_up" class="form-control" data-toggle="input-mask" data-mask-format="000.000.000.000.000,00" data-reverse="true">
                         <span class="font-13 text-muted">E.j.: " $ 100"</span>
                       </div>
                     </div>
                   </div>
                  </div>
                  <div class="modal-footer">
                    <a id="confirmarfactura_boton_cnl" class="btn btn-danger">Cancelar</a>
                    <a id="confirmarfactura_boton" class="btn btn-success" data-dismiss="modal">Confirmar</a>
                  </div>
                </div>
                <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
        
            <!--MODAL CONFIRMAR FACTURACION X RECORRIDOS-->
            <div id="Facturacion_recorridos_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Facturacion_recorridos-modalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header modal-colored-header bg-info">
                    <h4 class="modal-title" id="Facturacion_recorridos-modalLabel">Facturacion</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-lg-6 mt-2">
                        <div class="form-group">
                          <label for="fecha_up_r">Fecha del Comprobante</label>
                          <input type="date" id="fecha_up_r" class="form-control">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 mt-2">
                        <div class="form-group">
                          <label for="razonsocial_up_r">Razon Social</label>
                          <input type="text" id="razonsocial_up_r" class="form-control">
                        </div>
                      </div>
                       <div class="col-lg-4 mt-2"> 
                        <div class="form-group">
                        <label for="cuit_up_r">Cuit</label>
                        <input type="text" id="cuit_up_r" class="form-control" placeholder="Cuit">
                      </div>
                     </div>
                    </div>
                    <div class="row">
                    <!-- Single Select -->
<!--                     <div class="row" id="selectA_r" style="display:none"> -->
                      <div class="col-lg-6 mt-2" id="selectA_r" style="display:none">
                        <label for="comprobante_selectA_r">Comprobante</label>
                          <select id="comprobante_selectA_r" class="form-control select2" data-toggle="select2" onChange="buscarcomprobante(this.value)">
                            <option>Seleccione una Opción</option>
                              <optgroup label="Comprobantes Disponibles">
                              <option value="0">FACTURA PROFORMA</option>
                              <option value="1">FACTURAS A</option>
                              <option value="2">NOTAS DE DEBITO A</option>
                              <option value="3">NOTAS DE CREDITO A</option>                            
                            </optgroup>
                          </select>
                      </div>
<!--                     </div> -->
                    
<!--                      <div class="row" id="selectB_r" style="display:none"> -->
                      <div class="col-lg-6 mt-2" id="selectB_r" style="display:none">
                        <label for="comprobante_selectB_r">Comprobante</label>
                        <select id="comprobante_selectB_r" class="form-control select2" data-toggle="select2" onChange="buscarcomprobante(this.value)" >
                          <optgroup label="Comprobantes disponibles">
                              <option value="0">FACTURA PROFORMA</option>
                              <option value="6">FACTURAS B</option>
                              <option value="7">NOTAS DE DEBITO B</option>
                              <option value="8">NOTAS DE CREDITO B</option>
                          </optgroup>
                      </select>
                       </div>
<!--                     </div> -->
                    
<!--                      <div class="row" id="comprobante_up_display_r"> -->
                      <div class="col-lg-6 mt-2" id="comprobante_up_display_r">
                        <div class="form-group">
                          <label for="simpleinput">Comprobante</label>
                            <div class="input-group mb-3">
                              <input type="text" id="comprobante_up_r" class="form-control" readonly>
                              <div class="input-group-append">
                              <button id="modificar_comprobante_r" type="button" class="btn btn-outline-info"><i class="uil-exchange-alt"></i> Cambiar</button>
                            </div>
                          </div>
                        </div>
                      </div>
                     <input type="hidden" id="select_up_r" class="form-control" placeholder="Comprobante">
                     <div class="col-lg-6 mt-2"> 
                         <div class="form-group">
                           <label for="example-email">Numero de Comprobante</label>
                           <input type="text" id="ncomprobante_up_r" class="form-control" data-toggle="input-mask" data-mask-format="00000-00000000" placeholder="Numero de Comprobante">
                           <span class="font-13 text-muted">E.j.: "00001-00000123"</span>
                         </div>
                     </div>
                   </div>
                     <div class="row">
                      <div class="col-lg-4 mt-2"> 
                        <div class="form-group">
                        <label for="neto_up_r">Importe Neto</label>
                         <input type="text" id="neto_up_r" class="form-control" data-toggle="input-mask" data-mask-format="000.000.000.000.000,00" data-reverse="true">
                      </div>
                     </div>
                       <div class="col-lg-4 mt-2"> 
                        <div class="form-group">
                        <label for="iva_up_r">Importe Iva</label>
                         <input type="text" id="iva_up_r" class="form-control" data-toggle="input-mask" data-mask-format="000.000.000.000.000,00" data-reverse="true">
                      </div>
                     </div>
                     <div class="col-lg-4 mt-2"> 
                       <div class="form-group">
                         <label for="total_up_r">Importe Total</label>
                         <input type="text" id="total_up_r" class="form-control" data-toggle="input-mask" data-mask-format="000.000.000.000.000,00" data-reverse="true">
                         <span class="font-13 text-muted">E.j.: " $ 100"</span>
                       </div>
                     </div>
                   </div>
                  </div>
                  <div class="modal-footer">
                    <a id="confirmarfactura_boton_cnl_r" class="btn btn-danger">Cancelar</a>
                    <a id="confirmarfacturaxrecorrido_boton" class="btn btn-success" data-dismiss="modal">Confirmar</a>
                  </div>
                </div>
                <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
        
        
        
            <!--MODAL CARGAR PAGO-->
            <div id="standard-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                  <div class="modal-header modal-colored-header bg-success">
                    <h4 class="modal-title" id="standard-modalLabel">Cargar Pago</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                  </div>
                  <div class="modal-body">

                    <!--                               <div class="row" id="form_pago"> -->
                    <div class="row">
                      <div class="col-lg-3 mt-3">
                        <div class="form-group">
                          <label>Fecha</label>
                          <input id="fecha" type="text" class="form-control" data-toggle="input-mask" data-mask-format="00/00/0000">
                          <span class="font-13 text-muted">e.g "DD/MM/YYYY"</span>
                        </div>
                      </div>
                      <div class="col-lg-4 mt-3">
                        <div class="selector-formadepago form-group">
                          <label for="formadepago">Forma de Pago</label>
                          <select id="formadepago" name="formadepago" class="form-control" data-toggle="select2" required></select>
                        </div>
                      </div>
                      <!--EFECTIVO    -->
                      <div class="col-lg-4 mt-3" id="efectivo" style="display:none">
                        <div class="form-group">
                          <label>Importe</label>
                          <input id="importe_efectivo" type="text" class="form-control" data-toggle="input-mask" data-mask-format="###0.00" data-reverse="true">
                          <span class="font-13 text-muted">Importe</span>
                        </div>
                      </div>
                    </div>
                    <!--TRANSFERENCIA-->
                    <div class="row" id="transferencia" style="display:none">
                      <div class="col-lg-4 mt-3">
                        <div class="form-group">
                          <label>Numero de Operacion</label>
                          <input id="numero_transferencia" type="text" class="form-control" data-toggle="input-mask" data-mask-format="0000-0000">
                          <span class="font-13 text-muted">Ej.: "123456789"</span>
                        </div>
                      </div>
                      <div class="col-lg-4 mt-3">
                        <div class="form-group">
                          <label>Fecha Transferencia</label>
                          <input id="fecha_transferencia" type="text" class="form-control" data-toggle="input-mask" data-mask-format="00/00/0000">
                          <span class="font-13 text-muted">e.g "DD/MM/YYYY"</span>
                        </div>
                      </div>
                      <div class="col-lg-4 mt-3">
                        <div class="form-group">
                          <label>Importe</label>
                          <input id="importe_transferencia" type="text" class="form-control" data-toggle="input-mask" data-mask-format="###0.00" data-reverse="true">
                          <span class="font-13 text-muted">e.g "#.##0,00"</span>
                        </div>
                      </div>
                    </div>

                    <!--CHEQUES-->
                    <div class="row" id="cheques" style="display:none">
                      <div class="col-lg-3 mt-3">
                        <div class="form-group">
                          <label>Banco</label>
                          <input id="banco_cheque" type="text" class="form-control">
                          <span class="font-13 text-muted">Ej.:Banco Macro</span>
                        </div>
                      </div>
                      <div class="col-lg-3 mt-3">
                        <div class="form-group">
                          <label>Numero de Cheque</label>
                          <input id="numero_cheque" type="text" class="form-control" data-toggle="input-mask" data-mask-format="000000000">
                          <span class="font-13 text-muted">e.g "(xx) xxxxx-xxxx"</span>
                        </div>
                      </div>
                      <div class="col-lg-3 mt-3">
                        <div class="form-group">
                          <label>Fecha de Pago</label>
                          <input id="fecha_cheque" type="text" class="form-control" data-toggle="input-mask" data-mask-format="00/00/0000">
                          <span class="font-13 text-muted">e.g "DD/MM/YYYY"</span> </div>
                      </div>
                      <div class="col-lg-3 mt-3">
                        <div class="form-group">
                          <label>Importe a Cobrar</label>
                          <input id="importe_cheque" type="text" class="form-control" data-toggle="input-mask" data-mask-format="#.##0,00" data-reverse="true">
                          <span class="font-13 text-muted">e.g "xxx.xxx.xxxx-xx"</span>
                        </div>
                      </div>
                    </div>
                    <div class="text-right">
                      <button id="confirmarpago_botton" type="button" class="btn btn-success"><i class="mdi mdi-check-bold mr-1"></i> <span>Confirmar Pago</span> </button>
                    </div>
                  </div>
                </div>
              </div>
              <!--end modal-->
            </div>
        
        
        
            <!-- end col-->
        
        <!--END MODAL CARGAR PAGO-->
        <!--MODAL NUEVO CLIENTE -->
                          <style>
                          .modal{
                              z-index: 20;   
                          
                            }
                          .modal-backdrop{
                              z-index: 10;        
                          }
                          </style>
        
                          <div id="nuevocliente-modal-lg" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true">
                              <div class="modal-dialog modal-lg modal-dialog-centered">
                                  <div class="modal-content">
                                    <form method="POST" class="needs-validation"  data-toggle="validator" data-disable="false" >
                                      <div class="modal-header modal-colored-header bg-success">
                                          <h4 class="modal-title" id="success-header-modalLabel">Crear Nuevo Cliente</h4>
                                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                      </div>
                                      <div class="modal-body">
                                        <div id="errorname" class="alert alert-warning" role="alert" style="display:none">
                                          <strong>Atención - </strong><a id="errorname_label"></a>
                                        </div>
                                        <div class="row">
                                          <div class="col-lg-5 mt-3">
                                             <label>Nombre Cliente</label>
                                             <input class="form-control" type='text' value='' id='nombrecliente_nc' placeholder='Nombre Cliente' onblur='ComprobarNombre(this.value)'> 
                                          </div>
                                          <div class="col-lg-7 mt-3">
                                             <label>Direccion</label>
                                              <input type='text' class="form-control" name='direccion_nc' id='direccion_nc' placeholder='Direccion: Calle Numero'>                                          
                                          </div>
                                        </div>
                                        <div class="row">
                                        <div class="col-lg-6 mt-3">
                                             <label>E-mail</label>
                                              <input type='mail' value='' class='form-control' name='email_nc' id='email_nc' placeholder='Direccion de E-mail'>                                          
                                          </div>
                                          <div class="col-lg-3 mt-3">
                                             <label>Telefono</label>
                                             <input class="form-control" type='phone' id='telefono_nc' name='telefono_nc' value='' placeholder='Telefono' required>
                                          </div>
                                          <div class="col-lg-3 mt-3">
                                             <label>Celular</label>
                                             <input class="form-control" type='phone' id='celular_nc' name='celular_nc' value='' placeholder='Celular' required>
                                          </div>
                                        </div>
                                        <div class="row">
                                          <div class="col-lg-8 mt-3">
                                             <label>Observaciones</label>
                                              <input class='form-control' type='text' value='' id='observaciones_nc' placeholder='Alguna Observacion ?'>
                                          </div>
                                          <input type='hidden' name='Calle_nc'  id='Calle_nc'>
                                          <input type='hidden' name='Barrio_nc' id='Barrio_nc'>
                                          <input type='hidden' name='Numero_nc' id='Numero_nc'>
                                          <input type='hidden' name='ciudad_nc' id='ciudad_nc'>
                                          <input type='hidden' name='cp_nc'     id='cp_nc'>
                                          
                                          
                                        <div class="col-lg-4 mt-3">
                                      <label>Relacion</label>
                                        <select name="relacion_nc" id="relacion_nc" class="form-control select2" data-toggle="select2" required>
                                          <option value="">Seleccione un Cliente para la Relacion</option>  
                                          <?php
                                            $sqlclientes=$mysqli->query("SELECT id,nombrecliente FROM Clientes");
                                              while($row = $sqlclientes->fetch_assoc()){
                                              echo "<option value='$row[id]'>$row[nombrecliente]</option>";    
                                              }
                                            ?> 
                                       </select>
                                        </div>
                                          </div>
                                      </form>
                                         </div>
                                      <div class="modal-footer">
                                          <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                                          <button id="AgregarCliente" type="button" class="btn btn-success">Agregar Cliente</button>
                                      </div>
                                  </div><!-- /.modal-content -->
                              </div><!-- /.modal-dialog -->
                          </div><!-- /.modal -->
                      <!--         END MODAL NUEVO CLIENTE -->
        
        <!-- Standard modal -->
            <!--                      <div class="modal fade" id="bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="mySmallModalLabel">Observaciones</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div id="observaciones_body" class="modal-body">
                                    
                                </div>
                            </div>
                      </div>
                    </div>
               -->
            <!-- Single Select -->
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <div class="row mb-2 d-print-none">
                      <div class="col-sm-6" id="editor">
                        <div class="selector-cliente form-group">
                            <label for="buscarcliente"> Buscar Cliente </label>
                            <select id="buscarcliente" name="buscarcliente" class="form-control" data-toggle="select2" required></select>
                        </div>
                      </div>
                      
                      <div class="col-sm-6" id="editor">
                        <div class="text-sm-right">
                          
<!--                           <button id="agregartarifa_botton" type="button" class="btn btn-warning" style="display:none"><i class="mdi mdi-rocket mr-1"></i> <span>Agregar Tarifa</span> </button> -->
                          <button id="descuento_botton" type="button" class="btn btn-warning" style="display:none" data-toggle="modal" data-target="#descuento-modal"><i class="mdi mdi-account-cash-outline mr-1"></i> <span>Aplicar Descuento</span> </button>
                          <button id="cargarpago_botton" type="button" class="btn btn-success" style="display:none" data-toggle="modal" data-target="#standard-modal"><i class="mdi mdi-account-cash-outline mr-1"></i> <span>Cargar Pago</span> </button>
                          <a  id="crearcliente" class="btn btn-success" data-parent="Origen" data-toggle="modal" data-target="#nuevocliente-modal-lg"><i class="mdi mdi-18px mdi-account-multiple-plus"></i> Agregar Cliente</a>     
                          <button id="guardar_botton" type="button" class="btn btn-success" style="display:none"><i class="mdi mdi-cloud mr-1"></i> <span>Guardar</span> </button>
                        </div>
                      </div>
                    </div>
                    
                    <ul class="nav nav-pills bg-nav-pills nav-justified mb-3 d-print-none" id="steps" style="display:none">
                      <li class="nav-item">
                        <a href="#dashboard-information" data-toggle="tab" aria-expanded="true" class="nav-link rounded-0 active" id="botontablero">
                          <i class="mdi mdi-truck-fast font-18"></i>
                          <span class="d-none d-lg-block">Tablero</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="#billing-information" data-toggle="tab" aria-expanded="false" class="nav-link rounded-0" id="botondatos">
                          <i class="mdi mdi-account-circle font-18"></i>
                          <span class="d-none d-lg-block">Datos Cliente</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="#ctacte-information" data-toggle="tab" aria-expanded="false" class="nav-link rounded-0" id="botoncta">
                        <i class="mdi mdi-cash-multiple font-18"></i>
                        <span class="d-none d-lg-block">Cuenta Corriente</span>
                      </a>
                      </li>
                      <li class="nav-item">
                        <a href="#relaciones-information" data-toggle="tab" aria-expanded="false" class="nav-link rounded-0" id="botonrelacion">
                          <i class="mdi mdi-account-arrow-right font-18"></i>
                          <span class="d-none d-lg-block">Relaciones</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="#tarifas-information" data-toggle="tab" aria-expanded="false" class="nav-link rounded-0" id="botontarifas">
                          <i class="mdi mdi-account-cash font-18"></i>
                          <span class="d-none d-lg-block">Tarifas</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="#facturacion-information" data-toggle="tab" aria-expanded="false" class="nav-link rounded-0" id="botonfacturacion">
                          <i class="mdi mdi-cash-multiple font-18"></i>
                          <span class="d-none d-lg-block">Guias de Carga</span>
                      </a>
                      </li>
                      <li class="nav-item">
                        <a href="#estadisticas-information" data-toggle="tab" aria-expanded="false" class="nav-link rounded-0" id="botonestadisticas">
                          <i class="mdi mdi-chart-line font-18"></i>
                          <span class="d-none d-lg-block">Estadisticas</span>
                      </a>
                      </li>
                    </ul>

                    <div class="tab-content" style="display:none" id="dashboard-content">
                      <div class="tab-pane show active" id="dashboard-information">
                        <div class="row">
                          <div class="col-sm-12">
                            <h4 class="mt-2">Tablero</h4>

                            <div class="row">
                              <div class="col-xl-3 col-lg-6">
                                <div class="card widget-flat">
                                  <div class="card-body">
                                    <div class="float-right">
                                      <i class="mdi mdi-currency-usd widget-icon bg-danger rounded-circle text-white"></i>
                                    </div>
                                    <h5 class="text-muted font-weight-normal mt-0" title="Revenue">Promedio Mensual</h5>
                                    <h3 class="mt-3 mb-3" id="ventas_mes"></h3>
                                    <p class="mb-0 text-muted">
                                      <span class="badge badge-info mr-1">
                                                                <i class="mdi mdi-arrow-down-bold" id="ventas_mes_ant"></i> %</span>
                                      <span class="text-nowrap">Compara año anterior</span>
                                    </p>
                                  </div>
                                </div>
                              </div>
                              <!-- end col-->
                              <div class="col-xl-3 col-lg-6">
                                <div class="card widget-flat">
                                  <div class="card-body">
                                    <div class="float-right">
                                      <i class="mdi mdi-currency-usd widget-icon bg-danger rounded-circle text-white"></i>
                                    </div>
                                    <h5 class="text-muted font-weight-normal mt-0" title="Revenue">Ventas Este Año</h5>
                                    <h3 class="mt-3 mb-3" id="ventas_ano"></h3>
                                    <p class="mb-0 text-muted">
                                      <span class="badge badge-info mr-1">
                                                                <i class="mdi mdi-arrow-down-bold" id="ventas_ano_ant"></i> %</span>
                                      <span class="text-nowrap">Comprara año anterior</span>
                                    </p>
                                  </div>
                                </div>
                              </div>
                              <!-- end col-->
                              <div class="col-xl-3 col-lg-6">
                                <div class="card widget-flat">
                                  <div class="card-body">
                                    <div class="float-right">
                                      <i class="mdi mdi-currency-usd widget-icon bg-danger rounded-circle text-white"></i>
                                    </div>
                                    <h5 class="text-muted font-weight-normal mt-0" title="Revenue">Ultima Factura del <a class="text-muted font-weight-normal mt-0" id="fecha"></a></h5>
                                    <h3 class="mt-3 mb-3" id="debe"></h3>

                                    <p class="mb-0 text-muted">
                                      <span class="badge badge-info mr-1">
                                                                <i class="mdi mdi-arrow-down-bold" id="tipo"></i> % </span>
                                      <span class="text-nowrap" id="numero"></span>
                                    </p>
                                  </div>
                                </div>
                              </div>
                              <!-- end col-->
                              <div class="col-xl-3 col-lg-6">
                                <div id="card_saldo" class="card widget-flat bg-danger text-white">
                                  <div class="card-body">
                                    <div class="float-right">
                                      <i class="mdi mdi-currency-usd widget-icon bg-danger rounded-circle text-white"></i>
                                    </div>
                                    <h5 class="text-white font-weight-normal mt-0" title="Revenue">Saldo Actual</h5>
                                    <h3 class="mt-3 mb-1" id="saldo"></h3>
                                    <p class="mb-0 text-white">
                                      <!--                                                             <span class="badge badge-info mr-1">
                                                              <i id="fecha_ult_pago"></i> </span> -->
                                      <a id="fecha_ult_pago"></a>
                                      <span class="text-nowrap text-white" id="importe_ult_pago">Último Pago</span>
                                    </p>
                                  </div>
                                </div>
                              </div>
                              <!-- end col-->

                            </div>
                          </div>
                        </div>
                      </div>


                      <div class="tab-pane" id="billing-information">
                        <div class="row">
                          <div class="col-sm-12">
                            <h4 class="mt-2">Datos del Cliente</h4>

                               <ul class="nav nav-tabs nav-bordered mb-3">
                                  <li class="nav-item">
                                      <a href="#home-b1" data-toggle="tab" aria-expanded="true" class="nav-link active">
                                          <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                          <span class="d-none d-md-block">Datos Generales</span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a href="#profile-b1" data-toggle="tab" aria-expanded="false" class="nav-link">
                                          <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                          <span id="pefril_facturacion"class="d-none d-md-block">Datos Facturacion</span>
                                      </a>
                                  </li>
                              </ul>
                          </div>
                          <!-- end col-->
                        </div>
                         <div class="tab-content">
                            <div class="tab-pane show active" id="home-b1">
                              <form id="form_clientes">
                           <div class="row">
                            <div class="col-lg-1 mt-3">
                              <div class="form-group">
                                <label for="codigo">Código</label>
                                <input type="text" id="codigo" class="form-control" readonly="">
                              </div>
                            </div>
                            <div class="col-lg-4 mt-3">
                              <div class="form-group">
                                <label for="razonsocial">Nombre Cliente</label>
                                <input type="text" id="razonsocial" class="form-control" readonly="">
                              </div>
                            </div>
                            <div class="col-lg-5 mt-3">
                              <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <input type="text" name="direccion" id="direccion" class="form-control">
                              </div>
                            </div>
                            <div class="col-lg-2 mt-3">
                              <div class="form-group">
                                <label for="pisodepto">Piso Depto.</label>
                                <input type="text" id="pisodepto" class="form-control">
                              </div>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col-lg-5 mt-3">
                              <div class="form-group">
                                <label for="localidad">Localidad</label>
                                <input type="text" id="localidad" class="form-control">
                              </div>
                            </div>
                            <div class="col-lg-5 mt-3">
                              <div class="form-group">
                                <label for="provincia">Provincia</label>
                                <input type="text" id="provincia" class="form-control">
                              </div>
                            </div>
                            <div class="col-lg-2 mt-3">
                              <div class="form-group">
                                <label for="codigopostal">Código Postal</label>
                                <input type="text" id="codigopostal" class="form-control">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-lg-3 mt-3">
                              <div class="form-group">
                                <label for="telefono">Telefono</label>
                                <input type="text" id="telefono" class="form-control">
                              </div>
                            </div>
                            <div class="col-lg-3 mt-3">
                              <div class="form-group">
                                <label for="celular">Celular</label>
                                <input type="text" id="celular" class="form-control">
                              </div>
                            </div>
                            <div class="col-lg-3 mt-3">
                              <div class="form-group">
                                <label for="celular2">Celular 2</label>
                                <input type="text" id="celular2" class="form-control">
                              </div>
                            </div>
                            <div class="col-lg-3 mt-3">
                              <div class="form-group">
                                <label for="contacto">Contacto</label>
                                <input type="text" id="contacto" class="form-control">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-lg-4 mt-3">
                              <div class="form-group">
                                <label for="rubro">Rubro</label>
                                <select id="rubro" class="form-control select2" data-toggle="select2">
                                                  <option>Seleccionar Rubro</option>
                                                  <optgroup label="Rubro">
                                                  <?
                                                  $sqlrubro="SELECT Rubro FROM Rubros ORDER BY Rubro ASC";
                                                  if ($resultado = $mysqli->query($sqlrubro)){
                                                      while($row = $resultado->fetch_assoc()){
                                                  ?>
                                                  <option value="<? echo $row[id];?>"><? echo $row[Rubro];?></option>
                                                  <?
                                                     }
                                                    }
                                                  ?>  
                                                    </optgroup>
                                                   </select>
                              </div>
                            </div>
                            <div class="col-lg-4 mt-3">
                              <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="Email">
                              </div>
                            </div>
                            <div class="col-lg-4 mt-3">
                              <div class="form-group">
                                <label for="web">Web</label>
                                <input type="text" id="web" class="form-control">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-lg-4 mt-3">
                              <div class="selector-condicion form-group">
                                <label for="condicion">Condición de I.V.A.</label>
                                <select id="condicion" name="condicion" class="form-control" data-toggle="select2" required></select>
                                <!--                                                   <input type="text" id="condicion" class="form-control"> -->
                              </div>
                            </div>
                            <div class="col-lg-4 mt-3">
                              <div class="form-group">
                                <label for="cuit">C.U.I.T</label>
                                <input type="text" id="cuit" class="form-control">
                              </div>
                            </div>
                            <div class="col-lg-4 mt-3">
                              <div class="form-group">
                                <label for="cai">C.A.I.</label>
                                <input type="text" id="cai" class="form-control">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-lg-12 mt-3">
                              <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <input type="text" id="observaciones" class="form-control">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-lg-4 mt-3" id="relacion_select" style="display:none">
                              <div class="form-group">
                                <label for="nueva_relacion">Relacion</label>
                                <div class="input-group mb-3">
                                <select id="nueva_relacion" class="form-control select2" data-toggle="select2">
                                  <option>Seleccionar Relacion</a></option>
                                    <optgroup label="Relacion">
                                    <?
                                      $sqlcuenta="SELECT id,nombrecliente FROM Clientes ORDER BY nombrecliente ASC";
                                      if ($resultado = $mysqli->query($sqlcuenta)){
                                          while($row = $resultado->fetch_assoc()){
                                          ?>
                                          <option value="<? echo $row[id];?>"><? echo $row[id]." | ".$row[nombrecliente];?></option>
                                           <?
                                          }
                                        }
                                       ?>
                                  </optgroup>
                                 </select>
                              </div>
                            </div>
                          </div>

                            <div class="col-lg-4 mt-3" id="relacion">
                              <div class="form-group">
                                <label for="relacion">Relaciones</label>
                                <div class="input-group mb-3">
                                  <input type="text" name="relacionasignada_label" id="relacionasignada_label" onclick='javascript.void(0)' class="form-control" placeholder="Relacion" aria-label="Relacion" aria-describedby="button-addon2" readonly>
                                  <input type="hidden" name="relacionasignada" id="relacionasignada" onclick='javascript.void(0)' class="form-control" placeholder="Relacion" aria-label="Relacion" aria-describedby="button-addon2">
                                    <div class="input-group-append">
                                    <button id="modificar_relacion" type="button" class="btn btn-outline-info"><i class="uil-exchange-alt"></i> Cambiar</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="col-lg-4 mt-3">
                              <div class="form-group">
                                <div class="custom-control custom-switch">
                                  <input type="checkbox" class="custom-control-input" id="accesoweb" name="accesoweb">
                                  <label class="custom-control-label" for="accesoweb" data-on-label="1" data-off-label="0">Acceso Web</label>
                                </div>
                                <div class="input-group mt-1">
                                  <input type="text" name="claveweb_label" id="claveweb_label" onclick='javascript.void(0)' class="form-control" placeholder="Escriba la clave web aquí" aria-label="Clave Web" aria-describedby="button-addon2" readonly>
                                  <!--                                                       <input type="hidden" name="relacionasignada" id="relacionasignada" onclick='javascript.void(0)' class="form-control" placeholder="Relacion" aria-label="Relacion" aria-describedby="button-addon2"> -->
                                  <div class="input-group-append">
                                    <button id="claveweb_button" type="button" class="btn btn-outline-info"><i class="mdi mdi-18px mdi-eye"></i></button>
                                    <button style="display:none" id="claveweb_button2" type="button" class="btn btn-outline-info"><i class="mdi mdi-18px mdi-eye-off"></i></button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="col-lg-3 mt-3">
                              <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="retira" name="retira">
                                <label class="custom-control-label" for="retira" data-on-label="1" data-off-label="0">Solo Entregas</label>

                              </div>
                              <div class="mt-3">
                                <a id="retira_label"></a>
                              </div>
                            </div>
                          </div>
                        </form>
                      </div>
                      <div class="tab-pane" id="profile-b1">
                          <div class="row">
                            <div class="col-lg-4 mt-3">
                              <div class="form-group">
                                <label for="razonsocial_facturacion">Razon Social</label>
                                <input type="text" id="razonsocial_facturacion" class="form-control">
                              </div>
                            </div>
                            <div class="col-lg-8 mt-3">
                              <div class="form-group">
                                <label for="direccion_facturacion">Dirección</label>
                                <input type="text" name="direccion_facturacion" id="direccion_facturacion" class="form-control">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            
                            
                          <!--CONDICION DE IVA  -->
                               <div class="col-lg-4 mt-3" id="condicion_select" style="display:none">
                                  <div class="selector-condicion form-group">
                                    <label for="nueva_condicion_facturacion">Condición de I.V.A.</label>
                                    <select id="nueva_condicion_facturacion" name="nueva_condicion_facturacion" class="form-control" data-toggle="select2" required></select>
                                  </div>
                                </div>
                                  <div class="col-lg-4 mt-3" id="condicion_div">
                                    <div class="form-group" id="condicion_div">
                                    <label for="condicion_facturacion">Condición de I.V.A.</label>
                                    <div class="input-group mb-3">
                                      <input type="text" name="condicion_facturacion" id="condicion_facturacion" onclick='javascript.void(0)' class="form-control" placeholder="Condicion de I.V.A." aria-label="Relacion" aria-describedby="button-addon2" readonly>
<!--                                       <input type="hidden" name="condicion_facturacion" id="condicion_facturacion" onclick='javascript.void(0)' class="form-control" placeholder="Relacion" aria-label="Relacion" aria-describedby="button-addon2"> -->
                                      <div class="input-group-append">
                                    <button id="modificar_condicion_facturacion" type="button" class="btn btn-outline-info"><i class="uil-exchange-alt"></i> Cambiar</button>
                                  </div>
                                </div>
                              </div>  
                            </div>
                            
                            
                            <div class="col-lg-4 mt-3">
                              <div class="selector-tipodocumento form-group">
                                <label for="tipodocumento_facturacion">Tipo de Documento</label>
                                <select id="tipodocumento_facturacion" name="tipodocumento_facturacion" class="form-control" data-toggle="select2" required></select>
                              </div>
                            </div>                            <div class="col-lg-4 mt-3">
                              <div class="form-group">
                                <label for="cuit_facturacion">C.U.I.T</label>
                                <input type="text" id="cuit_facturacion" class="form-control">
                              </div>
                            </div>
                            <div class="col-lg-4 mt-3">
                              <div class="form-group">
                                <label for="cai_facturacion">C.A.I.</label>
                                <input type="text" id="cai_facturacion" class="form-control">
                              </div>
                            </div>
                          </div>
                      </div>
                    </div>
                      </div>
                      <!--panel1-->
                      <div class="tab-pane" id="ctacte-information">
                        <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1 mb-3">
                          <h4 class="mt-2" id="titulo_ctacte">Cuenta Corriente</h4>
                          <div class="row" id="div_ctacte">
                            <div class="col-xl-3 col-lg-6">
                              <div class="card widget-flat text-black">
                                <div class="card-body">
                                  <div class="float-right">
                                    <i class="mdi mdi-currency-usd widget-icon bg-danger rounded-circle text-white"></i>
                                  </div>
                                  <h5 class="text-black font-weight-normal mt-0" title="Revenue">Saldo Cta Cte</h5>
                                  <h3 class="mt-3 mb-1" id="saldo_ctacte"></h3>
                                </div>
                              </div>
                            </div>
                            <div class="col-xl-3 col-lg-6">
                              <div class="card widget-flat text-black">
                                <div class="card-body">
                                  <div class="float-right">
                                    <i class="mdi mdi-currency-usd widget-icon bg-danger rounded-circle text-white"></i>
                                  </div>
                                  <h5 class="text-black font-weight-normal mt-0" title="Revenue">Remitos No Facturados</h5>
                                  <h3 class="mt-3 mb-1" id="totalenviados_label"></h3>
                                </div>
                              </div>
                            </div>
                            <div class="col-xl-3 col-lg-6">
                              <div class="card widget-flat text-black">
                                <div class="card-body">
                                  <div class="float-right">
                                    <i class="mdi mdi-currency-usd widget-icon bg-danger rounded-circle text-white"></i>
                                  </div>
                                  <h5 class="text-black font-weight-normal mt-0" title="Revenue">Remitos Recibidos</h5>
                                  <h3 class="mt-3 mb-1" id="totalrecibidos_label"></h3>
                                </div>
                              </div>
                            </div>
                            <div class="col-xl-3 col-lg-6">
                              <div class="card widget-flat text-black bg-danger">
                                <div class="card-body">
                                  <div class="float-right">
                                    <i class="mdi mdi-currency-usd widget-icon bg-danger rounded-circle text-white"></i>
                                  </div>
                                  <h5 class="text-white font-weight-normal mt-0" title="Revenue">Saldo Total</h5>
                                  <h3 class="text-white mt-3 mb-1" id="total_saldo"></h3>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- end col-->
                          <!--                                               <div class="row float-right"> -->
                          <!--                                                 <h4 class="header-title mt-2 float-right">Cuenta Corriente: <a class="text-info"></a></h4> -->
                          <!--                                                 <h4 class="header-title mt-2 float-right">Remitos Enviados sin Facturar: <a class="text-info" id="totalenviados_label"></a></h4> -->
                          <!--                                               </div> -->
                          <div class="table-responsive" id="div_basic">
                            <table class="table dt-responsive nowrap w-100" id="basic" style="font-size:13px">
                              <tbody>
                                <thead>
                                  <tr>
                                    <th>Fecha</th>
                                    <th>Razon Social</th>
                                    <th>Comprobante</th>
                                    <th>Debe</th>
                                    <th>Haber</th>
                                    <th>Ver</th>
                                  </tr>
                              </tbody>
                              <tfoot>
                                <tr>
                                  <th></th>
                                  <th></th>
                                  <th></th>
                                  <th></th>
                                  <th></th>
                                  <th></th>
                                </tr>
                              </tfoot>
                            </table>
                          </div>
                          <!-- end table-->
                        </div>
                      </div>

                      <div class="tab-pane" id="relaciones-information">
                        <div class="row">
                          <div class="col-sm-12 mb-3">
                            <h4 class="mt-2">Relaciones</h4>
<!--                             <p class="text-muted font-14"> -->
                              En la siguiente tabla figuran los clientes relacionados con el cliente principal, en la seccion Adm.Web se puede seleccionar los clientes relacionados que el cliente principal puede administrar desde su pataforma web.
                            <h5>El cliente esta administrando las siguientes cuentas:</h5>
                               <div class="col-sm-12 mb-3">                           
                                 <span id="admin_envios" class="badge badge-outline-primary" style="font-size:8px"></span>
                               </div>
<!--                             </p> -->
                            <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                              <div class="table-responsive">
                                <table class="table dt-responsive nowrap w-100" id="relaciones_tabla" style="font-size:13px">
                                  <tbody>
                                    <thead>
                                      <tr>
                                        <th>Id Proveedor</th>
                                        <th>Nombre</th>
                                        <th>Direccion</th>
                                        <th>Telefono</th>
                                        <th>Adm. Web</th>
                                      </tr>
                                  </tbody>
                                  <tfoot>
                                    <tr>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                    </tr>
                                  </tfoot>
                                </table>
                              </div>
                              <!-- end card-->
                            </div>
                            <!-- end col-->
                          </div>
                        </div>
                      </div>
                      <div class="tab-pane" id="facturacion-information">
                        <div class="row" id="factura_primerpaso">
                          <div class="col-sm-12 mb-3">
                            <h4 class="mt-2">Facturacion</h4>
                            <ul class="nav nav-tabs nav-bordered mb-3">
                                  <li class="nav-item">
                                      <a id="guias_todas_boton" href="#guias_todas" data-toggle="tab" aria-expanded="true" class="nav-link active">
                                          <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                          <span class="d-none d-md-block">Guias a Facturar</span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a id="recorridos_boton" href="#recorridos" data-toggle="tab" aria-expanded="false" class="nav-link">
                                          <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                          <span class="d-none d-md-block">Recorridos a Facturar</span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a id="guias_recibidas_boton" href="#guias_recibidas" data-toggle="tab" aria-expanded="false" class="nav-link">
                                          <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                          <span class="d-none d-md-flex">Guias Recibidas</span>
                                      </a>
                                  </li>
                                 <li class="nav-item">
                                      <a id="guias_enviadas_boton" href="#guias_enviadas" data-toggle="tab" aria-expanded="false" class="nav-link">
                                          <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                          <span class="d-none d-md-flex">Guias Enviadas</span>
                                      </a>
                                  </li>
                              </ul>
                            
                            
                       <div class="tab-content">
                        <div class="tab-pane show active" id="guias_todas">
                          <div id="div_filtro" class="d-print-none text-right">
                            <div class="custom-control custom-control-inline mt-3">
                              <label for="min">Desde</label>
                              <input class="form-control" type="date" id="min" name="min">
                            </div>
                            <div class="custom-control custom-control-inline">
                              <label for="max">Hasta</label>
                              <input class="form-control" type="date" id="max" name="max">
                            </div>
                            <div class="custom-control custom-checkbox custom-control-inline">
                            <a id="filtro" type="button" class="btn btn-info float-right mt-3"><i class="mdi mdi-filter mr-1"></i><span> Filtro </span></a>                            
                            </div>
                        </div>
                            <div class="table-responsive">
                              <table class="table table-centered mb-0" id="facturacion_tabla" style="font-size:12px">
                                <thead>
                                      <tr>
                                          <th>Fecha</th>
                                          <th>Comprobante</th>
                                          <th>Cliente Destino</th>
                                          <th>Codigo Cliente</th>
                                          <th>Seguimiento</th>
                                          <th>Importe</th>
                                          <th>Accion</th>
                                        <th style="width: 20px;">
                                          <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck1">
                                            <label class="custom-control-label" for="customCheck1">&nbsp;</label>
                                          </div>
                                        </th>
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
                                      <th></th>
                                    </tr>
                                  </tbody>
                                </table>
                               
                              <button id="facturar_boton" type="button" class="btn btn-info float-right mt-3"><i class="mdi mdi-printer mr-1"></i> <span> Generar Comprobante </span> </button>
                              </div>
                          </div>
                       
                           <div class="tab-pane" id="guias_recibidas">
                             <div class="table-responsive">
                                <table class="table table-centered mb-0" id="guias_recibidas_tabla" style="font-size:11px">
                                    <thead>
                                      <tr>
                                        <th>Fecha</th>
                                        <th>Comprobante</th>
                                        <th>Cliente Origen</th>
                                        <th>Direccion</th>
                                        <th>Detalle</th>
                                        <th>Seguimiento</th>
                                        <th>Importe</th>
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
                             </div>
                            </div>
                              <div class="tab-pane" id="guias_enviadas">
                                <div class="row">
                                  <div class="col-12">
                                    <div class="table-responsive">
                                    <table id="guias_enviadas_tabla" class="table table-centered mb-0" style="font-size:11px">
                                    <thead>
                                        <tr>
                                          <th>Fecha</th>
                                          <th>Comprobante</th>
                                          <th>Cliente Destino</th>
                                          <th>Direccion</th>
                                          <th>Detalle</th>
                                          <th>Seguimiento</th>
                                          <th>Importe</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                          <th></th>
                                          <th></th>
                                          <th></th>
                                          <th></th>
                                          <th></th>
                                          <th></th>
                                          <th></th>
                                    </tbody>
                                    </table>
                                    </div>
                                  </div>
                                </div>
                            </div>
                             <div class="tab-pane" id="recorridos">
                               <div class="table-responsive"> 
                                <table class="table dt-responsive nowrap w-100" id="recorridos_tabla" style="font-size:10px;margin: 0 auto;clear: both;">
                                  <thead>
                                      <tr>
                                        <th>Fecha</th>
                                        <th>Recorrido</th>
                                        <th>Numero de Orden | Recorrido</th>
                                        <th>Descripcion</th>
                                        <th>Importe</th>
                                        <th style="width: 20px;">
                                          <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck_recorridos">
                                            <label class="custom-control-label" for="customCheck_recorridos">&nbsp;</label>
                                          </div>
                                        </th>
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
                                    </tr>
                                  </tbody>
                                </table>
                               </div>
                               <div class="d-print-none mt-4">
                                        <div class="text-right">
                                          <a id="ingresar_recorridos" type="button" class="btn btn-primary" data-toggle="modal" data-target="#bs-example-modal-lg"><i class="mdi mdi-printer mr-1"></i>Ingresar Recorridos</a>
                                          <a id="facturar_recorridos_boton" type="button" class="btn btn-warning"><i class="mdi mdi-printer mr-1"></i> <span> Generar Comprobante </span></a>
                                      </div>
                                    </div>   
                               
                            </div>
                            </div>
                          </div>
                        </div>
<!--                       </div> -->
                        <div class="row" id="factura_proforma" style="display:none">
                          <div class="col-12">
                            <div class="card">
                              <div class="card-body">

                                <!-- Invoice Logo-->
                                <div class="clearfix">
                                  <div class="float-left mb-3">
                                    <img src="../images/LogoCaddy.png" alt="" height="70">
                                  </div>
                                  <div class="float-right">
                                    <h2 class="m-0" id="factura_titulo"></h2>
                                  </div>
                                </div>

                                <!-- Invoice Detail-->
                                <div class="row">
                                  <div class="col-sm-6">
                                    <h4 id="Emp_RazonSocial"></h4>
                                    <address>
                                     <strong> Direccion:</strong> <a id="Emp_Direccion"></a><br>
                                     <strong> Cuit:</strong> <a id="Emp_Cuit"></a><br>
                                     <strong> IIBB:</strong> <a id="Emp_IngresosBrutos"></a><br>
                                     <strong> Telefono:</strong> <abbr title="Phone"></abbr><a id="Emp_Telefono"></a>
                                  </address> </div>
                                  <!-- end col-->
                                  <div class="col-sm-4 offset-sm-2">
                                    <div class="float-sm-end">
                                      <h4 id="factura_titulo2"></h4>
                                      <address>
                                      <strong>N:</strong> <a>00001</a>-
                                      <a id="NumeroComprobante">00000000000</a><br>
                                      <strong>Fecha: </strong> <script>var f = new Date();document.write(f.getDate() + "/" + (f.getMonth() +1) + "/" + f.getFullYear());</script><br>
                                      <strong>Id de Cliente: </strong><a id="factura_codigo"></a><br>
                                      <strong>Estado del Coprobante: </strong><span id="estado" class="badge badge-success">Pendiente</span>
                                  </address>
                                    </div>
                                  </div>
                                  <!-- end col-->
                                </div>


                                <div class="row">
                                  <div class="col-md-4">
                                    <div class="mb-4">
                                      <h5>Condicion Emisor:</h5>
                                      <a>Responsable Insripto</a>
                                    </div>
                                  </div>
                                  <div class="col-md-4">
                                    <div class="mb-4">
                                      <h5>Periodo Facturado</h5>
                                      <strong>Desde: </strong><a id="desde_f"></a>
                                      <strong>Hasta: </strong><a id="hasta_f"></a>
                                    </div>
                                  </div>
                                  <div class="col-md-4">
                                    <div class="mb-4">
                                      <h5>Fecha de Vencimiento para el pago</h5>
                                      <p id="">
                                        <script>var f = new Date();document.write(f.getDate()+14 + "/" + (f.getMonth() +1) + "/" + f.getFullYear());</script>
                                         <small class="text-muted"> Cuenta Corriente </small></p>
                                    </div>
                                  </div>
                                </div>
                                <!-- end row -->

                                <div class="row mt-2">
                                  <div class="col-sm-4">
                                    <h4><a id="factura_razonsocial"></a></h4>
                                    <address>
                                     <strong> Direccion:</strong> <a id="factura_direccion"></a><br>
                                     <strong> Condicion:</strong> <a id="factura_condicion"></a><br>
                                  </address>
                                  </div>
                                  <div class="col-sm-4 mt-3">
                                    <h4></h4>
                                    <address>
                                   <strong> Cuit:</strong> <a id="factura_cuit"></a><br>
                                   <strong> IIBB:</strong> <a id="factura_ingresosbrutos"></a><br>
                                </address>
                                  </div>
                                  <!-- end col-->
                                  <div class="col-sm-4 mt-3">
                                    <h4></h4>
                                    <address>
                                   <strong> Telefono:</strong> <abbr title="Phone">+54-</abbr><a id="factura_celular"></a>
                                   <strong> Mail:</strong><a id="factura_email"></a>
                                </address>
                                  </div>
                                  <!-- end col-->
                                </div>
                                <!-- end row -->
                                <div class="row"  id="row_tabla_facturacion">
                                  <div class="col-lg-12">
                                    <div class="table-responsive">
                                      <table class="table table-sm table-centered table dt-responsive mb-0 w-100" id="tabla_facturacion_proforma" style="font-size:11px">
                                        <thead>
                                            <tr>
                                              <th>Fecha</th>
                                              <th>Seguimiento</th>
                                              <th>Comprobante</th>
                                              <th>Cliente Destino</th>
                                              <th>Codigo Cliente</th>
                                              <th>Importe</th>
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
                                          </tr>
                                        </tbody>
                                      </table>
                                    </div>
                                  </div>
                                </div>
                                <div class="row" id="row_tabla_recorridos">
                                  <div class="col-lg-12">
                                    <div class="table-responsive">
                                      <table class="table dt-responsive nowrap w-100" id="tabla_facturacion_proforma_recorridos">
                                          <thead>
                                            <tr>
                                              <th>Fecha</th>
                                              <th>Tipo</th>
                                              <th>Comprobante</th>
                                              <th>Observaciones</th>
                                              <th>Importe</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                          <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                          </tr>
                                        </tfoot>
                                      </table>
                                    </div>
                                  </div>
                                </div>
                                <!-- end row -->

                                <div class="row">
                                  <div class="col-sm-6">
                                    <div class="text-sm-left">
                                      <!--<img src="../hyper/dist/saas/assets/images/barcode.png" alt="barcode-image" class="img-fluid mr-2" /> -->
                                    </div>
                                  </div>
                                  <!-- end col-->
                                  <div class="col-sm-6">
                                    <div class="float-right mt-3 mt-sm-0">
                                      <input type="hidden" id="factura_neto_f">
                                      <input type="hidden" id="factura_iva_f">
                                      <input type="hidden" id="factura_total_f">
                                      <p><b>Total Neto:  </b> <span id="factura_neto" class="float-right"></span></p>
                                      <p><b>Descuento (%): </b> <span id="factura_descuento" class="float-right"></span></p>
                                      <p><b>Total IVA (21 %):  </b> <span id="factura_iva" class="float-right"></span></p>
                                      <p>
                                        <h4><b>Total Comprobante:  </b><span id="factura_total"></h4></p>

                                      <div class="clearfix"></div>
                                  </div> <!-- end col -->
                                </div>
                            </div>
                                    <!-- end row-->
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="clearfix pt-3">
                                                <h6 class="text-muted">Observaciones:</h6>
                                                <small id="nota_factura" style="display:none">
                                                    Producido el vencimiento la mora será automática, aplicándose una tasa de interés 
                                                    del 8,30 % mensual por el término de la misma.
                                                    Los remitos relacionados con la presente factura se detallan en el Extracto N°..
                                                </small>
                                              <small id="nota_proforma" style="display:none">
                                                   Los siguientes ervicios fueron prestados por Caddy al cliente que figura en el comprobante y estan sujetos 
                                                   a verificación y control. 
                                                   Los importes que figuran en este comprobante están sujetos a variaciones de acuerdo a situación impositiva 
                                                   que se deduzca de la documentación entregada por el cliente.
                                              </small>
                                                </div>
                                        </div> <!-- end col -->
                                      </div>
                                   <div class="col-sm-12" id="datos_cae" style="display:none">
                                            <div class="text-sm-right">
                                             <h5>CAE: <a id="CAE"></a>  Fecha Vencimiento CAE: <a id="VencimientoCAE"></a></h5>   
                                            </div>
                                   </div> <!-- end col-->

                                    <div class="d-print-none mt-4">
                                        <div class="text-right">
                                          <!-- <button type="button" class="btn btn-success" data-toggle="modal" data-target="#success-alert-modal">Success Alert</button> -->
                                            <a href="javascript:window.print()" class="btn btn-primary"><i class="mdi mdi-printer"></i> Imprimir</a>
                                            <a id="Facturacion_recorridos_button" type="button" class="btn btn-success" data-toggle="modal" data-target="#Facturacion_recorridos_modal"><i class="mdi mdi-check-bold mr-1"></i> Confirmar</a>  
                                            <a id="info-header-modal_button" type="button" class="btn btn-success" data-toggle="modal" data-target="#info-header-modal"><i class="mdi mdi-check-bold mr-1"></i> Confirmar</a>  
                                            <a id="cancelarfactura_boton" href="javascript: void(0);" class="btn btn-danger"><i class="mdi mdi-close-thick mr-1"></i>Cancelar</a>  
                                      </div>
                                    </div>   
                                    <!-- end buttons -->

                                </div> <!-- end card-body-->
                            </div> <!-- end card -->
                        </div> <!-- end col-->
                    </div>
                                          <!-- end row -->
                </div>   
                                <div class="tab-pane d-print-none" id="tarifas-information">
                                    <div class="row">
                                      <div class="col-sm-12">
                                        <h4 class="mt-2">Tarifas</h4>
                                      <div class="table-responsive">
                                          <table class="table dt-responsive nowrap w-100" id="tarifas_tabla">
                                            <tbody>
                                              <thead>
                                                <tr>
                                                  <th>Tarifa</th>
                                                  <th>Maximo Kilometros</th>
                                                  <th>Precio</th>
                                                  <th></th>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                              <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                              </tr>
                                            </tfoot>
                                          </table>
                                        </div>
                                      </div>
                                    </div>
                                  </div>

                                    <div class="tab-pane" id="estadisticas-information">
                                        <div class="row">
                                        <div class="col-sm-12 mb-3">
                                            <h4 class="mt-2">Estadisticas</h4>
                                            <h5>Ventas mensuales para el cliente seleccionado.</h5>
                                                <div class="col-sm-12 mb-3">                           
                                                <span id="admin_envios" class="badge badge-outline-primary" style="font-size:8px"></span>
                                                </div>
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h4 class="header-title mb-4">Ventas Mensuales</h4>
                                                            <div id="chart" class="apex-charts" data-colors="#fa5c7c"></div>
                                                        </div>
                                                    </div>
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h4 class="header-title mb-4">PAQUETES ENVIADOS</h4>
                                                            <div id="chart_envios" class="apex-charts" data-colors="#6c757d"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>                                
                      <!-- end card-->
                             </div> <!-- card body-->
                            </div> <!--card-->
                          </div> <!--card12-->
                         </div><!--row-->
                       </div><!--container-fluid-->
                      </div><!--content>
                <!-- container -->          
                </div>
            </div>
                <!-- END wrapper -->
                <!-- bundle -->
              <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
              <script src="../hyper/dist/saas/assets/js/app.min.js"></script>
              <!-- third party js -->
              <script src="../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script>
              <script src="../hyper/dist/saas/assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
              <script src="../hyper/dist/saas/assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
              <script src="../Conexion/Procesos/js/users.js"></script>
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
              <!--    enlases externos para botonera-->
              <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
              <!--excel-->
              <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
              <!--pdf-->
              <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
              <!--pdf-->
              <script src="https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js"></script>
              <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script>
              <script src="../hyper/dist/saas/assets/js/vendor/dataTables.checkboxes.min.js"></script>
              <!-- third party js ends -->
              <!-- demo app -->
              <script src="../hyper/dist/saas/assets/js/pages/demo.datatable-init.js"></script>
              <!-- end demo js-->
                      <!-- third party:js -->
                <script src="../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script>
                <script src="https://apexcharts.com/samples/assets/stock-prices.js"></script>
                <script src="https://apexcharts.com/samples/assets/irregular-data-series.js"></script>

              <!-- funciones -->
              <script src="Procesos/js/funciones.js"></script>
              <script src="../Menu/js/funciones.js"></script>
              <script src="../Funciones/js/datosempresa.js"></script>
              <script src="Procesos/js/cargarpago.js"></script>
              <script src="../Funciones/js/seguimiento.js"></script>
              <script src="Procesos/js/descuento.js"></script>
              <script src="Procesos/js/abmventas.js"></script>
              <script src="Procesos/js/flash.js"></script>
              <!-- end demo js-->
              <script src="Procesos/js/demo.apex-line.js"></script>
              <!-- Direcciones -->
              <script async defer
              src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&libraries=places&callback=BuscarDireccion">
              </script>
    </body>
</html>