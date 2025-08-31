<?php
session_start();
include_once('../Conexion/Conexioni.php');
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Caddy | Orden De Compra </title>
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
        <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
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
                                            <li class="breadcrumb-item">Proveedores</a></li>
                                            <li class="breadcrumb-item">Orden de Compra</a></li>                                        
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Ordenes de Compra</h4>
                                </div>
                            </div>
                        </div>     

          
                        <!-- OBSERVACIONES PRESUPUESTO --> 
                        <div class="modal fade" id="observar_presupuesto" tabindex="1040" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header modal-colored-header bg-warning">
                                        <h4 class="modal-title">Agregar Observaciones</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group mb-3">
                                                    
                                                    <div class="form-group">
                                                        <label>Observaciones</label>
                                                        <p class="text-muted font-13">
                                                            Agregue aquí las observaciones, las mismas seran informadas a todos los involucrados en esta OC.
                                                        </p>
                                                        <textarea id="observar_presupuesto_text" observar_presupuesto_text data-toggle="maxlength" class="form-control" maxlength="225" rows="3" 
                                                            placeholder="Esta área tiene un limite de 225 caracteres."></textarea>
                                                    </div> 
                                                    
                                                </div>
                                            </div>                                            
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group mb-3">                        
                                                    <div class="text-sm-right">
                                                        <button id="observar_presupuesto_ok" type="button" class="btn btn-success">Aceptar</button>
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal" >Cancelar</button>
                                                    </div>
                                                </div>     
                                            </div>                                           
                                            </div>

                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->


                        <!-- ACEPTAR ORDEN DE COMPRA Y SOLICITAR N CANTIDAD DE PRESUPUESTOS --> 
                        <div class="modal fade" id="aceptar_orden" tabindex="1041" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header modal-colored-header bg-warning">
                                        <h4 class="modal-title" id="aceptar_orden_title">Aceptar Orden de Compra</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <div class="form-group mb-3">
                                                    <label for="idOrden_estado_aceptar">Id Orden</label>
                                                    <input class="form-control" type="number" id="idOrden_estado_aceptar" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">                                            
                                                <div class="form-group ml-3 mb-3">
                                                    <label>N Presupuestos</label>
                                                    <input id="aceptar_orden_cant_presupuestos" data-toggle="touchspin" type="text" value="0" max="10">
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group mb-3">                        
                                                    <div class="text-sm-right">
                                                        <button id="aceptar_orden_ok" type="button" class="btn btn-success">Aceptar</button>
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal" >Cancelar</button>
                                                    </div>
                                                </div>     
                                            </div>                                           
                                            </div>

                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->

                        <!-- APROBAR ORDEN DE COMPRA --> 
                        <div class="modal fade" id="aprobar_orden" tabindex="1041" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header modal-colored-header bg-info">
                                        <h4 class="modal-title" id="aprobar_orden_title">Aprobar Orden de Compra</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <div class="form-group mb-3">
                                                    <label for="idOrden_estado_aprobar">Id Orden</label>
                                                    <input class="form-control" type="number" id="idOrden_estado_aprobar" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-5">                                            
                                                <div class="form-group ml-3 mb-3">
                                                    <label>Fecha de Pago</label>
                                                    <input id="aprobar_orden_fecha" type="date" class="form-control">
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group mb-3">                        
                                                    <div class="text-sm-right">
                                                        <button id="aprobar_orden_ok" type="button" class="btn btn-success">Aceptar</button>
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal" >Cancelar</button>
                                                    </div>
                                                </div>     
                                            </div>                                           
                                            </div>

                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->

                        <!-- APROBAR PRESUPUESTO --> 
                        <div class="modal fade" id="aprobar_presupuesto" tabindex="1041" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="aprobar_presupuesto_title">Aceptar Presupuesto</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-lg-8">
                                                            <div class="form-group mb-3">                                                    
                                                                <p class="form-group" id="aprobar_presupuesto_text"></p>                                                    
                                                            </div>
                                                        </div>                                            
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="form-group mb-3">                        
                                                                <div class="text-sm-right">
                                                                    <button id="aprobar_presupuesto_ok" type="button" class="btn btn-success">Aceptar</button>
                                                                    <button type="button" class="btn btn-danger" data-dismiss="modal" >Cancelar</button>
                                                                </div>
                                                            </div>     
                                                        </div>                                           
                                                        </div>

                                                </div>
                                            </div><!-- /.modal-content -->
                                        </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->




                        <div id="warning-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-warning h1 text-warning"></i>
                                        <h4 class="mt-2"id="warning-alert-modal_title"></h4>
                                        <p class="mt-3" id="warning-alert-modal_text"></p>
                                        <button id="warning-alert-modal_button_ok" type="button" class="btn btn-success my-2" data-dismiss="modal">Continuar</button>
                                        <button type="button" class="btn btn-danger my-2" data-dismiss="modal">Cancelar</button>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                        <!-- AGREGAR PRESUPUESTO -->
                        <div id="presupuesto_new" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header modal-colored-header bg-success">
                                        <h4 class="modal-title" id="presupuesto_new_title">Agregar Nuevo Presupuesto</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">
                                            <div class="row">
                                                <div class="col-lg-2">
                                                    <div class="form-group mb-3">                                                        
                                                        <label>id Orden:</label>
                                                        <input id="id_p" name='id_p' type='number' class="form-control" readonly> 
                                                        <input id="id_o" name='id_o' type='hidden' value='123' class="form-control" readonly> 
                                                        
                                                    </div>
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="form-group mb-3">                                                        
                                                        <label>Fecha:</label>
                                                        <input id='fecha_p' value='<?php echo date('d/m/Y');?>' type='text' class="form-control"/>                                                        
                                                    </div>
                                                </div>
                                                <div class="col-lg-8">
                                                    <div class="form-group mb-3">                                                    
                                                        <label>Proveedor:</label>
                                                        <select id="proveedor_p" name='proveedor_p' class="form-control" require>                                                   
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                                
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group mb-3">
                                                        <label>Descripcion:</label>
                                                        <input type='text' id='descripcion_p' maxlength='100' class="form-control"  placeholder='Ej.: Compra de Repuestos' required/>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group mb-3">
                                                            <label>Forma De Pago:</label>
                                                            <input type='text' id='formadepago_p' maxlength='100' class="form-control"  placeholder='Ej.: Stock minimo' required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group mb-3">
                                                        <label>Cantidad:</label>
                                                        <input id='cantidad_p' type='number' class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group mb-3">
                                                        <label>Total:</label>
                                                        <input id='total_p' type='number' step='.01' class="form-control"  placeholder='Si no se conoce dejar en 0'>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group mb-3">
                                                        <label>Observaciones:</label>
                                                        <textarea id='observaciones_p' rows='2' cols='130' class="form-control" ></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="archivo_subido" class="col-xl-3 col-lg-6" style="display:none">
                                                        <div class="card m-1 shadow-none border">
                                                            <div class="p-2">
                                                                <div class="row align-items-center">
                                                                    <div class="col-auto">
                                                                        <div class="avatar-sm">
                                                                            <span class="avatar-title bg-light text-secondary rounded">
                                                                                <i class="mdi mdi-file-pdf-outline font-18"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col pl-0">
                                                                        <a href="javascript:void(0);" class="text-muted font-weight-bold" id="archivo_subido_nombre"></a>
                                                                        <p class="mb-0 font-13" id="archivo_subido_size"></p>
                                                                        
                                                                    </div>
                                                                    <div class="col pl-0">                                                                    
                                                                    <i style="cursor:pointer" id="archivo_subido_borrar" class="mdi mdi-24px mdi-trash-can text-danger ml-3"></i>
                                                                    </div>
                                                                </div> <!-- end row -->
                                                            </div> <!-- end .p-2-->
                                                        </div> <!-- end col -->
                                                    </div>
                                            <div id="subir_archivo">
                                            <input type="file" id="archivoInput">
                                            <button class="btn btn-success" onclick="subirArchivo()">Subir Archivo</button>
                                            </div>
                                            <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group mb-3">                        
                                                    <div class="text-sm-right">
                                                        <button id="presupuesto_editar_ok" type="button" class="btn btn-success">Guardar</button>
                                                        <button id="presupuesto_editar_cnl"type="button" class="btn btn-danger">Cancelar</button>
                                                        <button id="presupuesto_aceptar" type="button" class="btn btn-success">Aceptar</button>
                                                        <button id="presupuesto_aceptar_cnl" type="button" class="btn btn-danger" data-dismiss="modal" >Cancelar</button>
                                                    </div>
                                                </div>     
                                            </div>                                           
                                            </div>
                                        </form>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->


                        <!-- AGREGAR / EDITAR ORDEN DE COMPRA -->
                        <div id="ordendecompra_new" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="ordendecompra_new_title">Agregar Nueva Orden de Compra</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="Formulario_new" action='' method='post'>
                                            <div class="row">
                                                <div class="col-lg-2">
                                                    <div class="form-group mb-2">
                                                        
                                                        <label>id:</label>
                                                        <input id="id_t" name='id_t' type='text' value='' class="form-control" readonly>                                                        
                                                        
                                                    </div>
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="form-group mb-2">                                                        
                                                        <label>Fecha:</label>
                                                        <input id='fecha_t' value='<?php echo date('d/m/Y');?>' type='text' class="form-control"  readonly/>                                                        
                                                    </div>
                                                </div>
                                            <!-- </div> -->
                                            
                                            <!-- <div class="row"> -->
                                                <div class="col-lg-4">
                                                    <div class="form-group mb-2">
                                                        <label>Titulo:</label>
                                                        <input type='text' id='titulo_t' maxlength='100' class="form-control"  placeholder='Ej.: Compra de Repuestos' required/>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-lg-4">
                                                    <div class="form-group mb-2">
                                                        <label>Tipo de Orden:</label>
                                                        <select id="tipodeorden_t" name='tipodeorden_t' class="form-control" require>
                                                   
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="form-group mb-2">
                                                    <label for="usuariocarga_t">Usuario Carga:</label>
                                                    <input type='text' id='usuariocarga_t' class="form-control" disabled>
                                                    </div>    
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="form-group mb-2">
                                                        <label for="motivo_t">Motivo:</label>
                                                        <input type='text' id='motivo_t' maxlength='100' class="form-control"  placeholder='Ej.: Stock minimo' required>
                                                    </div>
                                                </div>
                                            </div>

                                        <div class="row">
                                                <div class="col-lg-4">
                                                    <div class="form-group mb-2">
                                                        <label>Precio Estimado:</label>
                                                        <input id='precio_t' type='number' step='.01' class="form-control"  placeholder='Si no se conoce dejar en 0'>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group mb-2">
                                                        <label>Fecha Solicitada:</label>
                                                        <input id='fechadeorden_t' type='date' class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group mb-2">
                                                        <label>Fecha Aprobada:</label>
                                                        <input id='fechaaprobado_t' type='date' class="form-control">
                                                    </div>
                                                </div>
                                        </div>

                                        <div class="form-group mb-2">
                                            <label>Observaciones: <i id="observaciones_agregar"class="mdi mdi-pencil text-warning ml-2"></i></label>
                                            <textarea id='observaciones_t' rows='2' cols='130' class="form-control" ></textarea>
                                        </div>
                                        <div class="form-group mb-2">    
                                        <div class="alert alert-success" role="alert" style="display:none">
                                            <strong id="alert_title"> </strong><a id="alert_text"></a>
                                        </div>

                                        <table class="table table-hover table-centered mb-0" id="presupuestos" style="font-size:9px">
                                            <thead>
                                                <tr>
                                                    <!-- <th>id Orden</th> -->
                                                    <th>Fecha|User</th>
                                                    <th>Proveedor</th>
                                                    <th>Descripcion</th>
                                                    <th>Forma De Pago</th>
                                                    <th>Cantidad</th>
                                                    <th>Total</th>
                                                    <!-- <th>Usuario</th> -->
                                                    <th>Adjunto</th>
                                                    <th>Accion</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>

                                        </div>           
                                        <div class="form-group mb-2">
                                            <div class="text-sm-left">    
                                                <button id="presupuestos_agregar" type="button" class="btn btn-info" style="display:none">Agregar Presupuesto</button>                                            
                                            </div>
                        
                                             <div class="text-sm-right">
                                                <button id="ordendecompra_aprobar" type="button" class="btn btn-success" style="display:none">Aprobar Orden de Compra</button>
                                                <button id="ordendecompra_estado_aceptar" type="button" class="btn btn-info" style="display:none">Aceptar Orden de Compra</button> 
                                                <button id="ordendecompra_aceptar" type="button" class="btn btn-success">Aceptar</button>
                                                <button id="ordendecompra_cerrar" type="button" class="btn btn-danger" data-dismiss="modal" >Cancelar</button>
                                            </div>
                                            </div>
                                        </form>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->


                     


                          <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-4">
                                                
                                            </div>
                                            <div class="col-sm-12">



                                    <div class="row">
                                        <div class="col-12">
                                            <div class="d-flex justify-content-end align-items-rigth">                                                    
                                                <div class="col-4">            
                                                    <div class="card text-white bg-secondary">
                                                            <div class="card-body" style="cursor:pointer">
                                                                <blockquote id="button_pendientes" class="card-bodyquote">
                                                                    <p id="pendientes_total_importe">Total Cargadas: </p><h3 id="total_cargadas"> </h3>
                                                                    <footer>Ordenes de Compra Cargadas <cite id="total_cargadas_cant" title="Source Title"></cite>
                                                                    </footer>
                                                                </blockquote>
                                                            </div> <!-- end card-body-->
                                                        
                                                    </div>
                                                    </div>
                                                <div class="col-4">               
                                                    <div class="card text-white bg-info">
                                                        <div class="card-body" style="cursor:pointer">
                                                            <blockquote class="card-bodyquote">
                                                                <p id="solucionados_total_importe">Total Aceptadas: </p><h3 id="total_aceptadas"></h3>
                                                                <footer>Ordenes de Compra Aceptadas <cite id="total_aceptadas_cant" title="Source Title"></cite>
                                                                </footer>
                                                            </blockquote>
                                                        </div> <!-- end card-->    
                                                    </div> <!-- end card-->
                                                </div>
                                                <div class="col-4">               
                                                    <div class="card text-white bg-dark">
                                                        <div class="card-body" style="cursor:pointer">
                                                            <blockquote  class="card-bodyquote">
                                                                <p id="solucionados_total_importe">Total Pendientes: </p><h3 id="total_pendientes"></h3>
                                                                <footer>O.C. Cargadas + Aceptadas <cite id="total_pendientes_cant" title="Source Title"></cite>
                                                                </footer>
                                                            </blockquote>
                                                        </div> <!-- end card-->    
                                                    </div> <!-- end card-->
                                                </div>

                                                </div>
                                            </div>
                                        </div>

                                                <div class="text-sm-left">                                            
                                                                                        
                                                <button id="filtro_new"  type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#ordendecompra_new"><i class="mdi mdi-cart-arrow-down mr-1"></i> <span>Nueva Orden</span> </button>
    
                                            </div>    
                                            
                                                <div class="text-sm-right">
                                                <button id="filtro_cargadas" type="button" class="btn btn-sm btn-secondary btn-rounded">Cargadas</button>
                                                <button id="filtro_aceptadas" type="button" class="btn btn-sm btn-info btn-rounded">Aceptadas</button>
                                                <button id="filtro_aprobadas" type="button" class="btn btn-sm btn-success btn-rounded">Aprobadas</button>
                                                <button id="filtro_pagadas" type="button" class="btn btn-sm btn-warning btn-rounded">Pagadas</button>
                                                <button id="filtro_rechazadas"  type="button" class="btn btn-sm btn-danger btn-rounded">Rechazadas</button>
                                                
                                                </div>
                                            </div><!-- end col-->
                                        </div>                     <!-- Single Select -->
                                          <div class="table-responsive">
                                            <table class="table table-centered mb-0 no-footer dataTable" id="ordendecompra" style="font-size:12px">
                                                <h4 id="ordendecompra_title" class="header-title">ORDENES DE COMPRA EN ESTADO # CARGADA</h4>
                                                <thead class="thead-light">
                                                    <tr>                                                        
                                                        <th>Id</th>
                                                        <th>Fecha</th>
                                                        <th>Tipo de Orden</th>
                                                        <th>Motivo</th>
                                                        <th>Maximo</th>
                                                        <th>Solicitado</th>
                                                        <th>A Pagar</th>
                                                        <th>Estado</th>
                                                        <th>Accion</th>
                                                        
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
                        <!-- container -->
                                  <!-- Footer Start -->
                                  <footer class="footer">
                                      <div class="container-fluid">
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <script>document.write(new Date().getFullYear())</script> © Sistema - Caddy
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="text-md-right footer-links d-none d-md-block">
                                                      <!-- <a href="javascript: void(0);">About</a> -->
                                                      <!-- <a href="javascript: void(0);">Support</a> -->
                                                      <!-- <a href="javascript: void(0);">Contact Us</a> -->
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
      
      <!-- Datatables js -->
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>
      

        <!-- funciones -->
        <script src="Procesos/js/ordendecompra.js"></script>
        <script src="../Menu/js/funciones.js"></script>

        <!-- file -->
        <script src="../hyper/dist/saas/assets/js/vendor/dropzone.min.js"></script>
        <!-- init js -->
        <script src="../hyper/dist/saas/assets/js/ui/component.fileupload.js"></script>

</body>
</html>