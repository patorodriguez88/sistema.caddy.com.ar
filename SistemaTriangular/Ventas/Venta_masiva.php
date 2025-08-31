<?php
session_start();
// include_once "../ConexionBD.php";
include_once "../Conexion/Conexion.php";
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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Ventas</a></li>
                                            <li class="breadcrumb-item active">Venta Simple</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Ventas</h4>
                              </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                      <div id="success-header-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="success-header-modalLabel" aria-hidden="true">
                          <div class="modal-dialog">
                              <div class="modal-content">
                                  <div class="modal-header modal-colored-header bg-success">
                                      <h4 class="modal-title" id="success-header-modalLabel">Ultimo Paso</h4>
                                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                  </div>
                                  <div class="modal-body">
                                  Cargaste con éxito la venta, el codigo de seguimiento es <a id='codseg'></a>, ahora seleccioná que sigue...
                                  </div>
                                  <div class="modal-footer">
<!--                                       <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#standard-modal">Cargar Pago</button> -->
<!--                                       <button id="cobrar"   type="button" class="btn btn-success" data-target="#standard-modal">Cargar Pago</button> -->
                                      <button id="imprimir" type="button" class="btn btn-warning">Imprimir Remito</button>
                                      <button id="terminar" type="button" class="btn btn-light" data-dismiss="modal">Terminar</button>
                                                                            
                                  </div>
                              </div><!-- /.modal-content -->
                          </div><!-- /.modal-dialog -->
                      </div><!-- /.modal -->

                      <div id="warning-redespacho-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-warning h1 text-warning"></i>
                                        <h4 class="mt-2">Falta Información</h4>
                                        <p id="warning-redespacho-text" class="mt-3"></p>
                                        
                                        <button type="button" class="btn btn-warning my-2" data-dismiss="modal">Continue</button>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                      <!-- REDESPACHO MODAL-->

                        <div id="redespacho-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <div class="modal-body">
                                        <div class="text-center mt-2 mb-4">
                                            <h4>Requerimiento de Redespacho</h4>
                                        </div>

                                        <form class="pl-3 pr-3" action="#">
                                        <div class="row">
                                           <div class="col-lg-6 mt-3">
                                             <div class="form-group">
                                                <label for="fecha_redespacho">Fecha</label>
                                                <input class="form-control" type="text" id="fecha_redespacho" required="" placeholder="Ingrese el Proveedor del Servicio">
                                             </div>
                                            </div>
                                            <div class="col-lg-6 mt-3">
                                             <div class="form-group">
                                                <label for="codigoseguimiento_redespacho">Codigo Seguimiento</label>
                                                <input class="form-control" type="text" id="codigoseguimiento_redespacho" required="" placeholder="Ingrese el Proveedor del Servicio">
                                             </div>
                                            </div>
                                        </div>

                                            <div class="form-group">
                                                <label for="destino_redespacho">Destino</label>
                                                <input class="form-control" type="text" id="destino_redespacho" required="" placeholder="Ingrese el Destino">
                                            </div>

                                            <div class="form-group">
                                                <label for="proveedor_redespacho">Proveedor</label>
                                                <!-- <input class="form-control" type="text" id="proveedor_redespacho" required="" placeholder="Ingrese el Proveedor del Servicio"> -->
                                            <!-- </div>     -->


                                            <!-- <label id="pagadestino_label">Cliente Destino  <i id="pagadestino_icon" class="mdi mdi-18px mdi-account-cash text-success" style="display:none"></i></label> -->
                                                <select id="proveedor_redespacho" class="form-control select2" data-toggle="select2">
                                                <option value="">Seleccione un Proveedor para el Redespacho</option>
                                                <optgroup label="Proveedores de Transporte">
                                                    <?php
                                                    $sqlclientes=mysql_query("SELECT id,RazonSocial FROM Proveedores WHERE Rubro='Transporte'");
                                                    while($row = mysql_fetch_array($sqlclientes)){
                                                    echo "<option value='$row[id]'>$row[RazonSocial]</option>";    
                                                    }
                                                    ?>                                  
                                                </optgroup>
                                                </select>
                                            </div>




                                            <div class="form-group">
                                                <label for="observaciones_redespacho">Observaciones</label>
                                                <input class="form-control" type="text" required="" id="observaciones_redespacho" placeholder="Ingrese alguna Observacion">
                                            </div>

                                            <div class="form-group">
                                                <label for="codigo_redespacho">Codigo Redespacho</label>
                                                <input class="form-control" type="text" required="" id="codigo_redespacho" placeholder="Ingrese un Codigo de Seguimiento para el redepacho.">
                                            </div>
                                            <div class="form-group">
                                                <label for="importe_redespacho">Importe Redespacho</label>
                                                <input class="form-control" type="number" required="" id="importe_redespacho" placeholder="Ingrese un importe para el redepacho.">
                                            </div>
                                            

                                            <!-- <div class="form-group">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="customCheck1">
                                                    <label class="custom-control-label" for="customCheck1">I accept <a href="#">Terms and Conditions</a></label>
                                                </div> 
                                            </div> -->

                                            <div class="form-group text-right">
                                            <a id="cancelar_redespacho" class="btn btn-danger" type="submit"><i class="mdi mdi-cancel"> </i> Cerrar</a>    
                                            <a id="aceptar_redespacho" class="btn btn-success" type="submit"><i class="mdi mdi-check-bold""> </i> Aceptar</a>
                                            </div>

                                        </form>

                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->

                      <div id="standard-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
                          <div class="modal-dialog modal-lg">
                              <div class="modal-content">
                                  <div class="modal-header modal-colored-header bg-primary">
                                      <h4 class="modal-title" id="standard-modalLabel">Cargar Pago</h4>
                                    
                                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                  </div>
                                  <div class="modal-body">
                                    <div class="row">
                                       <div class="col-lg-4 mt-3">
                                          <div class="form-group">
                                              <label>Fecha</label>
                                              <input name="fecha_t" id="fecha_t" type="text" class="form-control" data-provide="typeahead" id="the-basics" placeholder="Fecha">
                                          </div>
                                      </div>  
                                       <div class="col-lg-4 mt-3">
                                          <div class="form-group">
                                              <label>Razon Social</label>
                                              <input id="razonsocial_t" name="razonsocial_t" class="form-control" type="text" placeholder="Razon Social">
                                          </div>
                                      </div>
                                       <div class="col-lg-4 mt-3">
                                         <div class="form-group">
                                              <label>Direccion</label>
                                              <input type="text" class="form-control" data-provide="typeahead" id="direccion_t" name="direccion_t" placeholder="Direccion">
                                        </div>
                                      </div>
                                    </div>
                                    <div class="row">
                                       <div class="col-lg-4 mt-3">
                                          <div class="form-group">
                                              <label>Tipo de Comprobante</label>
                                              <input type="text" class="form-control" data-provide="typeahead" id="tipodecomprobante_t" name="tipodecomprobante_t" placeholder="Tipo de Comprobante">
                                          </div>
                                      </div>
                                      <div class="col-lg-4 mt-3">
                                          <div class="form-group">
                                              <label>Numero de Comprobante</label>
                                              <input id="umerodecomprobante_t" name="umerodecomprobante_t" class="form-control" type="number" placeholder="Numero de Venta">
                                          </div>
                                      </div>
                                      <div class="col-lg-4 mt-3">
                                          <div class="form-group">
                                              <label>Numero de Recibo</label>
                                              <input type="number" class="form-control" data-provide="typeahead" id="numeroderecibo_t" name="numeroderecibo_t">
                                          </div>
                                      </div>
                                    </div>
                                    <div class="row">
                                      <div class="col-lg-4 mt-3">
                                        <div class="form-group">
                                          <label>Valor Declarado</label>
                                          <input type="text" class="form-control" data-provide="typeahead" id="multiple-datasets">
                                        </div>
                                      </div>
                                      <div class="col-lg-4 mt-3">
                                        <div class="form-group">
                                          <label>Alicuota Seguro</label>
                                          <input type="text" class="form-control" data-provide="typeahead" id="multiple-datasets">
                                        </div>
                                      </div>
                                      <div class="col-lg-4 mt-3">
                                        <div class="form-group">
                                          <label>Valor Seguro</label>
                                          <input type="text" class="form-control" data-provide="typeahead" id="multiple-datasets">
                                        </div>
                                      </div>
                                    </div>
                                      <div class="row">
                                        <div class="col-lg-4 mt-3">
                                          <div class="form-group">
                                            <label>Cheques de Terceros</label>
                                            <input name='' class="form-control" value='Cheques de Terceros' type='text' disabled/>
                                          </div>
                                        </div>
                                        <div class="col-lg-2 mt-3">
                                          <div class="form-group">
                                            <label>Banco</label>
                                            <input name='banco_t' class="form-control" type='text' value='' />
                                          </div>
                                        </div>
                                        <div class="col-lg-2 mt-3">
                                          <div class="form-group">
                                            <label>Numero Cheque</label>
                                            <input name='numerocheque_t' class="form-control" type='text' value='' />
                                          </div>
                                        </div>
                                        <div class="col-lg-4 mt-3">
                                          <div class="form-group">
                                            <label>Fecha Cheque</label>
                                            <input name='fechacheque_t' class="form-control" type='text' value='' />
                                          </div>
                                        </div>
                                        </div>
                                    <div class="row justify-content-end">
                                      <div class="col-lg-2 mt-3 right">
                                        <div class="form-group">
                                          <label>Total</label>
                                          <input type="text" class="form-control" data-provide="typeahead" id="total_t" name="total_t">
                                        </div>
                                      </div>
                                      <div class="col-lg-2 mt-3">
                                          <div class="form-group">
                                            <label>Forma de Pago</label>
                                            <input type="text" class="form-control" data-provide="typeahead" id="multiple-datasets">
                                          </div>
                                        </div>
                                      
                                      </div>
                                    </div>
                                  <div class="modal-footer">
                                      <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                      <button type="button" class="btn btn-primary">Save changes</button>
                                  </div>
                                </div>
                              </div><!-- /.modal-content -->
                          </div><!-- /.modal-dialog -->
<!--                       </div><!-- /.modal --> 
                      <!-- Full width modal -->
                          <style>
                          .modal{
                              z-index: 20;   
                          
                            }
                          .modal-backdrop{
                              z-index: 10;        
                          }
                          </style>
                          <div id="bs-example-modal-lg" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true">
                              <div class="modal-dialog modal-lg">
                                  <div class="modal-content">
                                    <form method="POST" class="needs-validation"  data-toggle="validator" data-disable="false" >
                                      <div class="modal-header">
                                          <h4 class="modal-title" id="fullWidthModalLabel"><a id="datonuevocliente"></a></h4>
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
                                            $sqlclientes=mysql_query("SELECT id,nombrecliente FROM Clientes");
                                              while($row = mysql_fetch_array($sqlclientes)){
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
                                          <button id="AgregarCliente" type="button" class="btn btn-primary">Agregar Cliente</button>
                                      </div>
                                  </div><!-- /.modal-content -->
                              </div><!-- /.modal-dialog -->
                          </div><!-- /.modal -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                      <form action="Procesos/php/ConfirmarVenta.php" class="needs-validation"  data-toggle="validator" data-disable="false" method="POST">
                                        <h2 class="header-title">Venta Simple <a id="nventa" class="badge badge-primary"></a> <a id="seguimiento" class="badge badge-success"></a> <a id="distancia" class="badge badge-danger"></a> <a id="duration" class="badge badge-danger"></a><a id="redespacho" class="badge badge-warning text-white"></a></h2>
<!--                                         <p class="text-muted font-14">Select2 gives you a customizable select box with support for searching, tagging, remote data sets, infinite scrolling, and many other highly used options.</p> -->
                                        <div class="tab-content" data-select2-id="7">
                                            <div class="tab-pane show active" id="select2-preview" data-select2-id="select2-preview">
                                                <div class="row">
                                                  
                                                     <div id="row_fp_tercero" class="col-lg-4 mt-3" style="display:none">
                                                       <label id="pagatercero_label">Cliente Acreedor: <i id="pagatercero_icon" class="mdi mdi-15px mdi-account-cash text-success" style="display:none"></i></label>
                                                              <select id="clientefacturacion_t" class="form-control select2" data-toggle="select2" Onchange="oculto_origen(this.value)" >
                                                                  <option value="">Seleccione un Cliente Acreedor</option>  
                                                                    <?php
                                                                      $sqlclientes=mysql_query("SELECT id,nombrecliente FROM Clientes");
                                                                        while($row = mysql_fetch_array($sqlclientes)){
                                                                        echo "<option value='$row[id]'>$row[nombrecliente]</option>";    
                                                                        }
                                                                      ?> 
                                                              </select>
                                                       </div>
                                                  
                                                    <div id="nuevoclienteorigen" class="col-lg-5 mt-2">
                                                      <label id="pagaorigen_label">Cliente Origen  <i id="pagaorigen_icon" class="mdi mdi-15px mdi-account-cash text-success" style="display:none"></i></label>
                                                        <select id="id_origen" class="form-control select2" data-toggle="select2" Onchange="oculto_origen(this.value)" >
                                                            <option value="">Seleccione un Cliente de Origen</option>  
                                                              <?php
                                                                $sqlclientes=mysql_query("SELECT id,nombrecliente FROM Clientes");
                                                                  while($row = mysql_fetch_array($sqlclientes)){
                                                                  echo "<option value='$row[id]'>$row[nombrecliente]</option>";    
                                                                  }
                                                                ?> 
                                                        </select>
                                                   <div class="text-muted font-14" id="origen_ok" style="display:none"><i id="origen_ok_icon" class="mdi mdi-18px mdi-sync text-muted" style="display:none"></i></div>
                                                      <div id="spinner" style="display:none" class="spinner-border spinner-border-sm" role="status"></div>
                                                    </div> <!-- end col -->
                                                  <div id="nuevoclienteorigen2" class="col-lg-5 mt-2" style="display:none">
                                                      <label id="pagaorigen_label">Cliente Origen  <i id="pagaorigen_icon" class="mdi mdi-15px mdi-account-cash text-success" style="display:none"></i></label>
                                                      <input type="text" id="id_origen2_label" class="form-control">
                                                      <p class="text-muted font-14" id="origen2_ok" style="display:none"></p>
                                                      <input type="hidden" id="id_origen2">
                                                  </div>

                                                  <div class="col-lg-1 mt-2">
                                                  <label>Crear</label>
                                                  <input id="valorcrear" type="hidden">
                                                  <a  id="crearorigen" class="btn btn-success" data-parent="Origen" data-toggle="modal" data-target="#bs-example-modal-lg"><i class="mdi mdi-18px mdi-account-multiple-plus"></i></a>     
                                                  </div>
                                                    <div id="nuevoclientedestino" class="col-lg-5 mt-2">
                                                      <label id="pagadestino_label">Cliente Destino  <i id="pagadestino_icon" class="mdi mdi-18px mdi-account-cash text-success" style="display:none"></i></label>
                                                          <select id="id_destino" class="form-control select2" data-toggle="select2" Onchange="oculto_destino(this.value)">
                                                            <option value="">Seleccione un Cliente de Destino</option>
                                                            <optgroup label="Clientes Registrados">
                                                              <?php
                                                              $sqlclientes=mysql_query("SELECT id,nombrecliente FROM Clientes");
                                                                while($row = mysql_fetch_array($sqlclientes)){
                                                                echo "<option value='$row[id]'>$row[nombrecliente]</option>";    
                                                                }
                                                              ?>                                  
                                                            </optgroup>
                                                        </select>
                                                        <p class="text-muted font-14" id="destino_ok" style="display:none"></p>
                                                      </div>  
                                                      <div id="nuevoclientedestino2" class="col-lg-5 mt-2" style="display:none">
                                                          <label id="pagaorigen_label">Cliente Destino  <i id="pagadestino_icon" class="mdi mdi-15px mdi-account-cash text-success" style="display:none"></i></label>
                                                          <input type="text" id="id_destino2_label" class="form-control">
                                                          <p class="text-muted font-14" id="destino2_ok" style="display:none"></p>
                                                          <input type="hidden" id="id_destino2">
                                                      </div>

                                                      <div class="col-lg-1 mt-2">
                                                      <label>Crear</label>
                                                      <a  id="creardestino" class="btn btn-success" data-parent="Destino" data-toggle="modal" data-target="#bs-example-modal-lg"><i class="mdi mdi-18px mdi-account-multiple-plus"></i></a>     
                                                    </div><!-- end col -->
                                                  </div> <!-- end row -->
                                                </div>                                            
                                            </div> <!-- end preview-->
<!--                                         </div> end tab-content-->
                                             <div class="row">
                                                 <div class="col-lg-4 mt-3">
                                                   <label>Codigo de Cliente Nº Factura/Remito/Orden/etc.</label>
                                                  <input id="codigocliente" name="codigocliente" type="text" class="form-control" value="" placeholder="Numero de Remito del Cliente..." aria-label="Recipient's username">                                                 </div>
                                                <div class="col-lg-6 mt-3">
                                                   <label>Observaciones</label>
                                                  <input id="observaciones" name="observaciones" type="text" class="form-control" value="" placeholder="Observaciones ..." aria-label="Recipient's username"> 
                                                </div>
                                                <div class="col-lg-2 mt-3">
                                                   <label>Redespacho</label>
                                                   <!-- <button id="button-redespacho" type="button" class="btn btn-primary" data-toggle="modal" data-target="#redespacho-modal"><i class="mdi mdi-alpha-r-box"> </i> Redespacho</button> -->
                                                   <button id="button-redespacho" type="button" class="btn btn-secondary text-white"><i class="mdi mdi-alpha-r-box"> </i> Redespacho</button>
                                                </div>
                                             </div>

                                            <div class="row" id="row_fp">
                                                <div class="col-lg-4 mt-3">
                                                   <label>Forma de Pago</label>
                                                   <select name="formadepago_t" class="form-control select2" Onchange="formadepago(this.value)" required>
                                                    <option value="">Forma De Pago</option> 
                                                    <option value="Origen">Paga en Origen</option>
                                                    <option value="Destino">Paga en Destino</option>
<!--                                                     <option value="Tercero">Paga otro Cliente</option>   -->
                                                   </select>
                                                </div>
                                                <div class="col-lg-4 mt-3">
                                                   <label>Modalidad de Cobro</label>
                                                   <select id="cobranzadelenvio_t" class="form-control select2" required>
                                                    <option value="">Modalidad de Cobro</option> 
                                                    <option value="1">Contra Retiro o Entrega del Envio</option>
                                                    <option value="0">Otra Forma de Pago</option>
                                                   </select>
                                                </div>
                                                <div class="col-lg-4 mt-3">
                                                  <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="customSwitch1" name="my-checkbox" onclick="cobroacuenta(this.value)">
                                                    <label class="custom-control-label" for="customSwitch1">Cobro a Cuenta</label>
                                                  </div>
                                                  <div class="input-group">
                                                    <input  id="cobroacuenta_input" name="cobroacuenta_input" type="number" class="form-control mt-1" placeholder="Importe Cobro a Cuenta ..." aria-label="Recipient's username" disabled>
                                                    <div class="input-group-append">
                                                        <button id="cobroacuenta_button" class="btn btn-dark mt-1" type="button" disabled>Agregar</button>
                                                    </div>
                                                  </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-2 mt-3">
                                                   <label>Sucursal | Domicilio</label>
                                                   <select id="entregaen_t" name="entregaen_t" class="form-control select2" required>
                                                    <option value="">Entrega</option> 
                                                    <option value="Retira">Retira en Sucursal</option>
                                                    <option value="Domicilio">Domicilio</option>
                                                   </select>
                                                </div>
                                                <div class="col-lg-2 mt-3">
                                                   <label>Retira | Entrega</label>
                                                   <select id="retiro_t" name="retiro_t" class="form-control select2" required>
                                                    <option value="">Seleccione una Opcion</option> 
                                                    <option value="0">Retiro y Entrega</option>
                                                    <option value="1">Solo Entrega</option>
                                                   </select>
                                                </div>
                                              <div class="col-lg-4 mt-3">
                                                <div class="selector-recorrido form-group">
                                                  <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="customSwitchRecorrido" name="my-checkbox-recorrido" onclick="todoslosrec();">
                                                   <label class="custom-control-label mb-1" for="customSwitchRecorrido">Recorrido | Todos</label>
                                                  </div>
                                                     <select id="recorrido_t" name="recorrido_t" class="form-control" data-toggle="select2" required></select>
                                                  </select>
                                                </div>
                                                </div>
                                              <div class="col-lg-4 mt-3">
                                                  <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="customSwitch2" name="my-checkbox2" onclick="valordeclarado(this.value)">
                                                    <label class="custom-control-label" for="customSwitch2">Valor Declarado</label>
                                                  </div>
                                                  <div class="input-group">
                                                    <input  id="valordeclarado_input" value="5000" min="5000" name="valordeclarado_input" type="number" class="form-control mt-1" placeholder="Importe Valor Declarado ..." aria-label="Recipient's username" disabled>
                                                    <div class="input-group-append">
                                                        <button id="valordeclarado_button" class="btn btn-dark mt-1" type="button" disabled>Agregar</button>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            <div class="row">
                                             <div class="col-lg-12 mt-3">
                                               <p class="mb-1 font-weight-bold text-muted">Seleccione un servicio para la venta...</p>
<!--                                                 <p class="text-muted font-14">
                                                  Seleccione un servicio para la venta...

                                               </p> -->
                                              </div>
                                              <div class="col-lg-1 mt-3">
                                             <label id="">Cantidad</label>  
                                               <input id="cantidad" type="number" min="0" class="form-control" value="1" placeholder="Cantidad ..." aria-label="Recipient's username" Onchange="calcularcantidad(this.value)"> 
                                               </div>  
                                                <div class="col-lg-3 mt-3">
                                                  <label id="">Servicio</label>
                                                  <select id="servicio" class="form-control servicio font-7" data-toggle="select2" Onchange="cargar(this.value)">
                                                      <option>Seleccione una Opcion</option>
                                                      <optgroup label="Tarifas Sistema">
                                                        <?php
                                                        $sqlclientes=mysql_query("SELECT id,Titulo,PrecioVenta FROM Productos");
                                                          while($row = mysql_fetch_array($sqlclientes)){
                                                          $tarifa=number_format($row[PrecioVenta],2,'.',',');
                                                          echo "<option style='font-size:6px' value='$row[id]'>$row[Titulo] | $ $tarifa</option>";    
                                                          }
                                                        ?>                                  
                                                      </optgroup>
                                                  </select>
                                                </div>
                                                <div class="col-lg-2 mt-3">
                                                  <label id="">Comentario</label>  
                                                  <input id="comentario" type="text" class="form-control" value="" placeholder="Comentario ..." aria-label="Recipient's username"> 
                                               </div>  

                                              <div class="col-lg-1 mt-3">
                                                  <label id="">Codigo</label>  
                                                  <input id="codigo" type="text" class="form-control" value="0" placeholder="Codigo ..." aria-label="Recipient's username" disabled> 
                                               </div>  

                                              <div class="col-lg-2 mt-3">
                                                  <label id="">Importe</label>  
                                                  <input id="precioventa" type="number" class="form-control" value="0" step="0.01" placeholder="Importe ..." OnChange="calculo(this.value);"> 
                                               </div>  
                                                <div class="col-lg-2 mt-3">
                                                  <label id="">Total</label>  
                                                  <input id="total" type="number" class="form-control" value="0" placeholder="Importe ..." aria-label="Recipient's username" disabled> 
                                                </div>  
                                                <div class="col-lg-1 mt-3">
                                                  <label>Subir</label>
                                                  <a id="subir" class="btn btn-success" Onclick="subir();"><i class="mdi mdi-thumb-up-outline"></i> </a>  
<!--                                                 <a name="subir" id="subir" class="btn btn-success disabled" type="submit"><i class="mdi mdi-thumb-up-outline"></i> </a>   -->

                                              </div>
                                            <div class="col-lg-12 mt-3">
                                            <div class="tab-content">
                                            <div class="tab-pane show active" id="basic-datatable-preview">
                                                <table class="table dt-responsive nowrap w-100" id="basic">
                                                    <thead>
                                                        <tr>
                                                            <th>Codigo</th>
                                                            <th>Titulo</th>
                                                            <th>Cantidad</th>
                                                            <th>Precio</th>
                                                            <th>Total</th>
                                                            <th>Comentario</th>
                                                            <th>Eliminar</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>                                           
                                            </div> <!-- end preview-->
                                        </div>
                                      </div>
                                      </div>
                                          <div class="row">
                                          <div class="col-lg-8 mt-3">
                                            <div class="border p-3 mt-4 mt-lg-0 rounded">
                                              <h4 class="header-title mb-3">Resumen de la Orden</h4>
                                                <div class="table-responsive">
                                                  <table class="table table-sm table-centered mb-0" id="basic-total">
                                                <thead>
                                                    <tr>
                                                        <th>Importe Neto</th>
                                                        <th>Iva</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                              </table>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="col-lg-4 mt-3">
                                          <input type='hidden' name='km_nc' id='km_nc'>
                                          <input type='hidden' name='google_km' id='google_km'>
                                          <input type='hidden' name='google_time' id='google_time'>
                                          <input type='hidden' name='redespacho_nc' id='redespacho_nc'>

<!--                                         <a class="btn btn-success" id="confirmarenvio"><i class="mdi mdi-thumb-up-outline"></i> Confirmar Envio </a>   -->
                                        <button value="confirmarenvio" name="SolicitaEnvio" type="submit" class="btn btn-danger">Confirmar Envio</button>  
                                        </div>
                                      </div>    
                                      </form>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
<!--                         </div>  -->
                        <!-- end row-->
                    </div>
                    <!-- container -->
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
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.keyTable.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script>
        <!-- third party js ends -->

        <!-- demo app -->
        <script src="../hyper/dist/saas/assets/js/pages/demo.datatable-init.js"></script>
        <!-- end demo js-->
        <!-- funciones -->
        <script src="Procesos/js/funciones.js"></script>
        <script src="../Menu/js/funciones_venta_masiva.js"></script>
        <script src="Procesos/js/redespacho.js"></script>

          <!-- demo app -->
        <script src="../hyper/dist/saas/assets/js/pages/demo.dashboard.js"></script>
        <!-- end demo js-->
        <!-- Direcciones -->
        <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&libraries=places&callback=BuscarDireccion">
        </script>
<!--         <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->
  </body>
</html>