<?php
// session_start();
include_once('../Conexion/Conexioni.php');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Caddy | Proveedores </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <!-- App favicon -->
    <!-- <link rel="shortcut icon" href="../hyper/dist/saas/assets/images/favicon.ico"> -->
    <link rel="shortcut icon" href="../images/favicon/favicon.ico">
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

<div id="danger-header-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="danger-header-modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-colored-header bg-danger">
                <h4 class="modal-title" id="danger-header-modalLabel">Eliminar Comprobante <a id="id_factura_borrar"></a></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">


                <p id="info_factura_borrar"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                <button id="btn_eliminar_ok" type="button" class="btn btn-danger">Eliminar</button>
                <button id="btn_eliminar_pago_ok" type="button" class="btn btn-danger">Eliminar Pago</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="standard-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="standard-modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="standard-modalLabel">Ingresar Foto del Comprobante <a id="id_comprobante_subir_html"></a></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            </div>
            <div class="modal-body">

                <!-- File Upload -->
                <form action="Procesos/php/upload.php" method="post" class="dropzone" id="myAwesomeDropzone" data-plugin="dropzone" data-previews-container="#file-previews"
                    data-upload-preview-template="#uploadPreviewTemplate" enctype="multipart/form-data">
                    <div class="fallback">
                        <input name="file" type="file" multiple />
                    </div>
                    <input name="id_comprobante_subir" type="hidden" />

                    <div class="dz-message needsclick">
                        <i class="h1 text-muted dripicons-cloud-upload"></i>
                        <h3>Presione aquí para subir una foto del comprobante.</h3>
                        <span class="text-muted font-13">(Intente que la
                            <strong>imagen</strong> este nitida y visible.)</span>
                    </div>
                </form>

                <!-- Preview -->
                <div class="dropzone-previews mt-3" id="file-previews">

                    <!-- file preview template -->
                    <div class="d-none" id="uploadPreviewTemplate">

                        <div class="card mt-1 mb-0 shadow-none border">
                            <div class="p-2">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <img data-dz-thumbnail src="#" class="avatar-sm rounded bg-light" alt="">
                                    </div>
                                    <div class="col ps-0">
                                        <a href="javascript:void(0);" class="text-muted fw-bold" data-dz-name></a>
                                        <p class="mb-0" data-dz-size></p>
                                    </div>
                                    <div class="col-auto">
                                        <!-- Button -->
                                        <a href="" class="btn btn-link btn-lg text-muted erase" data-dz-remove>
                                            <i class="dripicons-cross"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="text-right mt-xl-0 mt-2">
                    <button type="button" class="btn btn-light mb-2 me-2" data-dismiss="modal"> Cancelar</button>
                    <button id="standard-modal_btn_ok" type="button" class="btn btn-success mb-2 me-2" data-dismiss="modal">Aceptar</button>
                </div>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

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
                    <div class="row">
                        <div class="col-lg-12 mt-3">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Proveedores</a></li>
                                        <li class="breadcrumb-item">Datos</a></li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Proveedores</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="modal fade" id="modal_pagos_comprobantes" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div id="header-modal" class="modal-header modal-colored-header bg-warning">
                                    <h4 class="modal-title mr-5" id="myLargeModalLabel">Ingresar Pagos</h4>
                                </div>
                                <div class="modal-body">
                                    <!-- TOTAL ANTICIPOS -->
                                    <div class="col-lg-12 mt-0" id="total">
                                        <div class="col-12 mt-0 mt-sm-0">
                                            <h4 class="header-title">Anticipos Disponibles</h4>
                                            <table id="tabla_anticipos" class="table dt-responsive nowrap" style="font-size:10px">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Comprobante</th>
                                                        <th>Descripcion</th>
                                                        <th>Importe</th>
                                                        <th>Ver</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mt-3 mt-sm-0">
                                                <div class="float-right mt-3 mt-sm-0">
                                                    <input id="total_anticipos_control" type="hidden">
                                                    <p><b>Total Anticipos: </b> <span class="float-right ml-3" id="footer_total_anticipos"> </span> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- <button id="cargar_pagos_factura" type="button" class="btn btn-outline-success"><i class="uil-money-withdrawal"></i> Agregar Anticipo</button>                                         -->

                                    <!-- TOTAL COMPROBANTES -->
                                    <div class="col-lg-12 mt-3" id="total">
                                        <div id="tabla_pago_facturas" class="col-12 mt-3 mt-sm-0">
                                            <input type="hidden" value="" id="id_facturas">
                                            <h4 class="header-title">Comprobantes Seleccionados</h4>
                                            <table id="scroll-vertical-datatable" class="table dt-responsive nowrap" style="font-size:10px">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Comprobante</th>
                                                        <th>Descripcion</th>
                                                        <th>Importe</th>
                                                        <th>Pagos</th>
                                                        <th>Saldo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mt-3 mt-sm-0">
                                                <div class="float-right mt-3 mt-sm-0">
                                                    <p><b>Total Comprobantes: </b> <span class="float-right ml-3" id="footer_total"> </span> </p>
                                                    <input type="hidden" id="footer_total_input">
                                                    <input type="hidden" id="footer_total_saldo">
                                                    <p><b>Saldo: </b> <span class="float-right ml-3" id="footer_saldo"> </span> </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12 text-right mt-3">
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-warning" id="cargar_pago_btn_continuar">Continuar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="modal fade" id="modal_cargar_pagos" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div id="header-modal" class="modal-header modal-colored-header bg-warning">
                                    <h4 class="modal-title mr-5" id="modal_cargar_pagos_header">Ingresar Anticipos </h4>
                                </div>
                                <div class="modal-body">
                                    <form id="CargarPago" name='CargarPago' class='form-group' action='' method='POST'>
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="codigo">Fecha del Comprobante:</label>
                                                    <input name="fecha_t" type="date" id="fecha_p" class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-lg-5">
                                                <div class="form-group">
                                                    <label for="codigo">Razon Social</label>
                                                    <input type="text" id="razonsocial_p" class="form-control" readonly="">
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="cuit_t">Cuit</label>
                                                    <input type="text" id="cuit_p" name="cuit_p" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <label for="nasiento_p">Numero de Asiento:</label>
                                                <input class="form-control" name='nasiento_p' id='nasiento_p' size='20' type='text' value='' readonly />
                                            </div>
                                            <?php
                                            $Grupo = "SELECT id,FormaDePago,CuentaContable FROM FormaDePago WHERE FormaDePago<>'ANTICIPO A PROVEEDORES' ORDER BY FormaDePago ASC";
                                            $estructura = $mysqli->query($Grupo);
                                            ?>

                                            <div class="col-lg-5 mt-0">

                                                <div id="cuadro_forma_de_pago"></div>

                                            </div>
                                            <div class="col-lg-4 mt-0" id='total'>
                                                <label for="importepago_t">Importe a Pagar:</label>
                                                <input id='importepago_t' name='importepago_t' type='text' class='form-control' value='' require />
                                            </div>
                                            <?php

                                            //CHEQUES DE TERCEROS
                                            // $Grupo="SELECT * FROM Cheques WHERE Utilizado=0 AND Terceros=1 GROUP BY Banco";
                                            // $estructura= $mysqli->query($Grupo);
                                            ?>


                                            <div class="col-lg-6 mt-3" id='TercerosOculto' style='display:none;'>
                                                <label for="tercero_t">Cheques:</label>
                                                <select id='tercero_t' name='tercero_t' onchange='buscardatos(this.value)' class="form-control select3" data-toggle="select3">
                                                    <optgroup label="Seleccione un Cheque">
                                                        <option>Seleccione un Cheque</option>
                                                        <?php
                                                        // while ($row = $estructura->fetch_array(MYSQLI_ASSOC)){
                                                        //     echo "<option value='".$row[id]."'>".$row[FechaCobro]." / $ ".$row[Importe]."</option>";
                                                        // }
                                                        ?>
                                                    </optgroup>
                                                </select>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <?

                                            //CHEQUES PROPIOS
                                            // $Grupo="SELECT Banco FROM Chequeras GROUP BY Banco";
                                            // $estructura= $mysqli->query($Grupo);
                                            ?>
                                            <div class="col-lg-3 mt-3" id='BancoOcultopropio' style='display:none;'>
                                                <label for="banco_t">Banco:</label>
                                                <select id='banco_t' name='banco_t' class='form-control'>
                                                    <option value=''>Seleccione un Banco</option>
                                                    <?php
                                                    // while ($row = $estructura->fetch_array(MYSQLI_ASSOC)){
                                                    // echo "<option value='".$row[Banco]."'>$row[Banco]</option>";
                                                    // }
                                                    ?>
                                                </select>
                                            </div>
                                            <!-- <div class="col-lg-3 mt-3" id='NumeroChequeraOculto' style='display:none;'>
                                            <div id="num_chequera"></div>   
                                        </div> -->
                                            <div class="col-lg-3 mt-3" id='NumeroChequeOculto' style='display:none;'>
                                                <label for="numerocheque_t">N. Cheque:</label>
                                                <input type='number' id='numerocheque_t' name='numerocheque_t' class='form-control' value=''>
                                            </div>
                                            <div class="col-lg-3 mt-3" id='FechaChequeOculto' style='display:none;'>
                                                <label for="fechacheque_t">Fecha De Pago:</label>
                                                <input id='fechacheque_t' name='fechacheque_t' type='date' value='' class='form-control' />
                                            </div>

                                            <!-- TRANSFERENCIA BANCARIA -->
                                            <div>
                                                <input name='totalfacturas_t' value='' type='hidden' value='' class='form-control' />
                                            </div>

                                            <div class="col-lg-4 mt-3" id='oculto' style='display:none;'>
                                                <label for="numerotransferencia_t">Numero Transferencia</label>
                                                <input id='numerotransferencia_t' name='numerotransferencia_t' type='text' value='' class='form-control' />
                                            </div>
                                            <div class="col-lg-4 mt-3" id='oculto1' style='display:none;'>
                                                <label for="fechatransferencia_t">Fecha De Transferencia:</label>
                                                <input id='fechatransferencia_t' name='fechatransferencia_t' type='date' value='' class='form-control' />
                                            </div>
                                            <div class="col-lg-4 mt-3" id='BancoOculto' style='display:none;'>
                                                <label for="bancotransferencia_t">Banco:</label>
                                                <input id='bancotransferencia_t' name='bancotransferencia_t' type='text' value='' class='form-control' />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mt-1">
                                                <div class="float-right">
                                                    <!-- <p><b>Total Comprobantes:  </b> <span class="float-right" id="footer_total">   </span>  </p> -->
                                                    <p><b>Forma de Pago: </b> <span class="float-right" id="footer_1"> </span> </p>
                                                    <p><b id="footer_2"></b> <span class="float-right" id="footer_2_1"></span></p>
                                                    <p><b id="footer_3"></b> <span class="float-right" id="footer_3_1"></span></p>
                                                    <h3>
                                                        <p id='importepago_label_t'></p>
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12 text-right mt-3">
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-danger" id="cargar_pago_btn_cnl" data-dismiss="modal">Cancelar</button>
                                                    <button type="button" class="btn btn-success" id="cargar_pago_btn_ok" value="Aceptar">Aceptar</button>
                                                    <button type="button" class="btn btn-success" id="cargar_pago_btn_ok_n" value="Aceptar" style="display:none">Aceptar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->


                    <div class="modal fade" id="modal_cargar_factura" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div id="header-modal" class="modal-header modal-colored-header bg-success">

                                    <h4 class="modal-title mr-5" id="myLargeModalLabel"> Ingresar Comprobante </h4>

                                    <div class="float-right">
                                        <h5 class="modal-title mr-5" id="myLargeModalLabel"> Razon Social </h5>
                                        <h6 class="modal-title mr-5" id="myLargeModalLabel"> Cuit </h6>
                                    </div>


                                    <!-- Bool Switch-->
                                    <input type="checkbox" id="switch1" checked data-switch="success" />

                                    Operativo <label class="mr-0 ml-2" for="switch1" data-on-label="Si" data-off-label="No"></label>

                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>

                                <div class="modal-body">

                                    <form id="CargarFactura" name='CargarFactura' class='form-group' action='subir.php' method='POST' enctype='multipart/form-data'>

                                        <div class="row">
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="fecha_t">Fecha del Comprobante:</label>
                                                    <input name="fecha_t" type="date" id="fecha_t" class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-lg-5">
                                                <div class="form-group">
                                                    <label for="razonsocial_t">Razon Social</label>
                                                    <input type="text" id="razonsocial_t" class="form-control" readonly="">
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="cuit_t">Cuit</label>
                                                    <input type="text" id="cuit_t" name="cuit_t" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <input type='hidden' value='' name='ctaasignada_t' id='ctaasignada_t'>

                                        <div class="row">
                                            <div class="col-lg-5">
                                                <div class="form-group">
                                                    <label for="tipodecomprobante_t">Tipo de Comprobante</label>
                                                    <?php
                                                    // $Grupo="SELECT Codigo,Descripcion FROM AfipTipoDeComprobante ORDER BY Codigo ASC";
                                                    // $estructura= $mysqli->query($Grupo);
                                                    // echo "<select class='form-control' Onchange='comprobar2()' name='tipodecomprobante_t' id='tipodecomprobante_t' size='1' required/>";
                                                    // echo "<option value='0'>Seleccione una Opcion</option>";	   
                                                    // while ($row = $estructura->fetch_array(MYSQLI_ASSOC)){
                                                    // echo "<option value='".$row[Descripcion]."'>".$row[Codigo]." ".$row[Descripcion]."</option>";
                                                    // }
                                                    // echo "</select>";
                                                    ?>
                                                </div>
                                            </div>

                                            <div class="col-lg-2">
                                                <div class="form-group">
                                                    <label for='nasiento_t'>N Asiento:</label>
                                                    <input id="nasiento_t" name="nasiento_t" class="form-control" type="text" value="<? echo $NAsiento; ?>" readonly />
                                                </div>
                                            </div>
                                            <div class="col-lg-2">
                                                <div class="form-group">
                                                    <label for="numerodecomprobante_codigo_t">Código</label>
                                                    <input type="text" id="numerodecomprobante_codigo_t" name="numerocomprobante_t" class="form-control" placeholder="00000">
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="numerocomprobante_t">Numero de Comprobante</label>
                                                    <input type="text" id="numerocomprobante_t" name="numerocomprobante_t" class="form-control" placeholder="00000000">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-8">
                                                <div class="form-group">
                                                    <label for="descripcion_t">Descripcion</label>
                                                    <input type="text" id="descripcion_t" name="descripcion_t" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-lg-4" id="imp_neto_0">
                                                <div class="form-group">
                                                    <label for="importeneto_t">Importe Neto</label>
                                                    <input type="number" id="importeneto_t" name="importeneto_t" class="form-control" onblur="sumar();comprobar()">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="iva1_t">Iva 2.5%:</label>
                                                    <input class="form-control" name="iva1_t" id='iva1_t' size='10' type='number' step='0.01' value='0' onblur='sumar();comprobar()' required />
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="iva2_t">Iva 10.5%:</label>
                                                    <input class="form-control" id='iva2_t' size='10' type='number' step='0.01' value='0' onblur='sumar();comprobar()' />
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="iva3_t">Iva 21%:</label>
                                                    <input class="form-control" id='iva3_t' size='10' type='number' step='0.01' value='0' onblur='sumar();comprobar()' />
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="iva4_t">Iva 27%:</label>
                                                    <input class="form-control" id='iva4_t' size='10' type='number' step='0.01' value='0' onblur='sumar();comprobar()' />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="exento_t">Exento:</label>
                                                    <input class="form-control" id='exento_t' size='10' type='number' step='0.01' value='0' onblur='sumar();comprobar()' />
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="perciva_t">Percepcion Iva:</label>
                                                    <input class="form-control" id='perciva_t' size='10' type='number' step='0.01' value='0' onblur='sumar();comprobar()' />
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="perciibb_t">Percepcion IIBB1:</label>
                                                    <input class="form-control" id='perciibb_t' size='10' type='number' step='0.01' value='0' onblur='sumar();comprobar()' />
                                                </div>
                                            </div>
                                        </div>

                                        <?
                                        $_SESSION['Rubro'] = 'Si';
                                        $Grupo = "SELECT * FROM Vehiculos WHERE Activo='Si' AND Aliados='0'";
                                        $estructura = $mysqli->query($Grupo);
                                        ?>

                                        <div class="row">
                                            <div class="col-lg-4" id="solicita_vehiculo" style="display:none">
                                                <div class="form-group">
                                                    <label>Vehiculo:</label>
                                                    <select class="form-control" id='dominio_t' size='1' required>
                                                        <option value=''>Seleccione una Opción</option>
                                                        <?php
                                                        // while ($row = $estructura->fetch_array(MYSQLI_ASSOC)){ 

                                                        // echo "<option value='".$row[Dominio]."'>".$row[Marca]." ".$row[Modelo]." ".$row[Dominio]."</option>";

                                                        // }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-4" id="solicita_combustible" style="display:none">
                                                <div class="form-group">
                                                    <label>Combustible:</label>
                                                    <select class="form-control" name='combustible_t' size='1' required>
                                                        <option value=''>Seleccione una Opción</option>
                                                        <option value='Nafta Premium'>Nafta Premium</option>
                                                        <option value='Nafta Super'>Nafta Super</option>
                                                        <option value='Nafta Diesel'>Diesel</option>
                                                        <option value='G.N.C.'>G.N.C.</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group" id="solicita_combustible_l" style="display:none">
                                                    <label>Litros / Mts.3 Cargados:</label>
                                                    <input class="form-control" name='cantidad_t' size='10' type='number' step='0.01' value='' required />
                                                </div>
                                            </div>
                                        </div>

                                        <input id='totaliva_t' name='totaliva_t' size='10' type='hidden' step='0.01' value='' readonly />
                                        <input id='totalSiniva_t' name='totalSiniva_t' size='10' type='hidden' step='0.01' value='' readonly />

                                        <div class="row">
                                            <div class="col-lg-8">
                                                <!-- <div class="form-group">
                                            <label> Imagen del Comprobante: </label> 
                                            <input class="form-control" type='file' name='imagen' id='imagen' /> 
                                            </div> -->
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="form-group float-right">
                                                    <label>Total:</label>
                                                    <input name='total_t' id='total_t' type="text" class="form-control" data-toggle="input-mask" data-mask-format="#,##0.00" data-reverse="true" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div id="notificacion_alert" class="col-lg-8" style="display:none">
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <div class="alert alert-danger" role="alert">
                                                            <strong>Atención - </strong><a id="notificacion"></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id='codigodeaprobacion' class="col-lg-4" style='display:none'>
                                                <div class="form-group">
                                                    <div class="input-group input-group-merge">
                                                        <input class="form-control" name='codigodeaprobacion' id="codigodeaprobacion_t" type='password' placeholder="Ingrese el Código de Aprobación" onkeyup="javascript:this.value=this.value.toUpperCase();" required>
                                                        <div class="input-group-append" data-password="false">
                                                            <div class="input-group-text">
                                                                <span class="password-eye"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div id="alert_asana" class="alert alert-info" role="alert" display="display:none">
                                                <i class="dripicons-information mr-2"></i> Esto creará una tarea en <strong>Asana</strong> !
                                            </div>

                                            <div class="col-lg-12 text-right">
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-warning" id="cargar_factura_btn_verificar" style="display:none">Verificar</button>
                                                    <button type="button" class="btn btn-success" id="cargar_factura_btn_ok" value="Aceptar" disabled>Aceptar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <div id="success-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content modal-filled bg-success">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-checkmark h1"></i>
                                        <h4 class="mt-2">Exito!</h4>
                                        <p class="mt-3" id="success-alert-modal-text"></p>
                                        <button type="button" class="btn btn-light my-2" data-dismiss="modal">Continue</button>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <div id="danger-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content modal-filled bg-danger">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-wrong h1"></i>
                                        <h4 class="mt-2">Error!</h4>
                                        <p class="mt-3" id="danger-alert-modal-text"></p>
                                        <button type="button" class="btn btn-light my-2" data-dismiss="modal">Continue</button>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- Modal LOADING-->
                    <div class="modal fade" id="loading" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div id="loading-bg" class="modal-content modal-filled bg-primary">
                                <div class="modal-body">
                                    <div class="d-flex align-items-center">
                                        <strong id="loading-text">loading...</strong>
                                        <div class="spinner-border ml-auto" role="status" aria-hidden="true"></div>
                                        <div id="loading-icon" class="mdi mdi-24px mdi-cancel ml-auto" style="display:none"></div>
                                    </div>
                                </div>
                            </div> <!-- end modal content-->
                        </div> <!-- end modal dialog-->
                    </div> <!-- end modal-->


                    <!-- Single Select -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-sm-5" id="editor">
                                            <label for="buscarproveedor">Proveedor</label>
                                            <select id="buscarproveedor" class="form-control select2" data-toggle="select2">
                                                <option>Seleccionar Proveedor</option>
                                            </select>
                                        </div>

                                        <div class="btn-group float-right" id="botonera" style="display:none">
                                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_cargar_factura"> <i class="mdi mdi-file-document-edit-outline"> </i> Ingresar Comprobante</button>
                                            <button type="button" class="btn btn-warning float-right ml-1" data-toggle="modal" data-target="#modal_cargar_pagos"><i class="mdi mdi-cash"> </i> Ingresar Anticipo </button>
                                            <a id="btn_pago_facturas" class="btn btn-warning float-right ml-1" style="display:none"> <i class="mdi mdi-cash"> </i> Ingresar Pago </a>
                                        </div>

                                    </div>

                                    <!-- Checkout Steps -->
                                    <ul class="nav nav-pills bg-nav-pills nav-justified mb-3" id="steps" style="display:none">
                                        <li class="nav-item">
                                            <a href="#dashboard-information" data-toggle="tab" aria-expanded="true" class="nav-link rounded-0 active">
                                                <i class="mdi mdi-truck-fast font-18"></i>
                                                <span class="d-none d-lg-block">Tablero</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#billing-information" data-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
                                                <i class="mdi mdi-account-circle font-18"></i>
                                                <span class="d-none d-lg-block">Datos Proveedor</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#ctacte-information" data-toggle="tab" aria-expanded="false" class="nav-link rounded-0" id="botoncta">
                                                <i class="mdi mdi-cash-multiple font-18"></i>
                                                <span class="d-none d-lg-block">Cuenta Corriente</span>
                                            </a>
                                        </li>
                                    </ul>

                                    <div class="tab-content">

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
                                                                    <h5 class="text-muted font-weight-normal mt-0" title="Revenue">Promedio Mensual Año Actual</h5>
                                                                    <h3 class="mt-3 mb-3" id="compras_mes"></h3>
                                                                    <p class="mb-0 text-muted">
                                                                        <span class="badge badge-info mr-1">
                                                                            <i class="mdi mdi-arrow-down-bold" id="compras_mes_ant"></i> %</span>
                                                                        <span class="text-nowrap">Compara año anterior</span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col-->
                                                        <div class="col-xl-3 col-lg-6">
                                                            <div class="card widget-flat">
                                                                <div class="card-body">
                                                                    <div class="float-right">
                                                                        <i class="mdi mdi-currency-usd widget-icon bg-danger rounded-circle text-white"></i>
                                                                    </div>
                                                                    <h5 class="text-muted font-weight-normal mt-0" title="Revenue">Compras Este Año</h5>
                                                                    <h3 class="mt-3 mb-3" id="compras_ano"></h3>
                                                                    <p class="mb-0 text-muted">
                                                                        <span class="badge badge-info mr-1">
                                                                            <i class="mdi mdi-arrow-down-bold" id="compras_ano_ant"></i> %</span>
                                                                        <span class="text-nowrap">Comprara año anterior</span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div> <!-- end col-->
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
                                                        </div> <!-- end col-->
                                                        <div class="col-xl-3 col-lg-6">
                                                            <div id="card_saldo" class="card widget-flat bg-danger text-white">
                                                                <div class="card-body">
                                                                    <div class="float-right">
                                                                        <i class="mdi mdi-currency-btc widget-icon bg-danger rounded-circle text-white"></i>
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
                                                        </div> <!-- end col-->

                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="tab-pane" id="billing-information">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <h4 class="mt-2">Datos del Proveedor</h4>

                                                    <div class="text-sm-right">
                                                        <button id="agregar_botton" type="button" class="btn btn-warning" style="display:none"><i class="mdi mdi-rocket mr-1"></i> <span>Agregar Proveedor</span> </button>
                                                        <button id="guardar_botton" type="button" class="btn btn-success"><i class="mdi mdi-cloud mr-1"></i> <span>Guardar</span> </button>
                                                    </div>
                                                </div><!-- end col-->
                                            </div>
                                            <form>


                                                <div class="row">
                                                    <div class="col-lg-1 mt-3">
                                                        <div class="form-group">
                                                            <label for="codigo">Código</label>
                                                            <input type="text" id="codigo" class="form-control" readonly="">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4 mt-3">
                                                        <div class="form-group">
                                                            <label for="razonsocial">Razón Social</label>
                                                            <input type="text" id="razonsocial" class="form-control" readonly="">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 mt-3">
                                                        <div class="form-group">
                                                            <label for="direccion">Dirección</label>
                                                            <input type="text" id="direccion" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2 mt-3">
                                                        <div class="form-group">
                                                            <label for="localidad">Localidad</label>
                                                            <input type="text" id="localidad" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2 mt-3">
                                                        <div class="form-group">
                                                            <label for="provincia">Provincia</label>
                                                            <input type="text" id="provincia" class="form-control">
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-2 mt-3">
                                                        <div class="form-group">
                                                            <label for="codigopostal">Código Postal</label>
                                                            <input type="text" id="codigopostal" class="form-control">
                                                        </div>
                                                    </div>
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
                                                    <div class="col-lg-4 mt-3">
                                                        <div class="form-group">
                                                            <label for="contacto">Contacto</label>
                                                            <input type="text" id="contacto" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-3 mt-3">
                                                        <div class="form-group">
                                                            <label for="condicion">Condición</label>
                                                            <input type="text" id="condicion" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 mt-3">
                                                        <div class="form-group">
                                                            <label for="iva">Iva</label>
                                                            <input type="text" id="iva" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 mt-3">
                                                        <div class="form-group">
                                                            <label for="cuit">C.U.I.T</label>
                                                            <input type="text" id="cuit" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 mt-3">
                                                        <div class="form-group">
                                                            <label for="cai">C.A.I.</label>
                                                            <input type="text" id="cai" class="form-control">
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

                                                                    //   $sqlrubro="SELECT Rubro FROM Rubros ORDER BY Rubro ASC";
                                                                    //   if ($resultado = $mysqli->query($sqlrubro)){
                                                                    //       while($row = $resultado->fetch_assoc()){
                                                                    ?>
                                                                    <!-- <option value="<? echo $row['Rubro']; ?>"><? echo $row['Rubro']; ?></option> -->
                                                                    <?
                                                                    //  }
                                                                    // }
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
                                                    <div class="col-lg-10 mt-3">
                                                        <div class="form-group">
                                                            <label for="observaciones">Observaciones</label>
                                                            <input type="text" id="observaciones" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2 mt-3">
                                                        <div class="form-group">
                                                            <label for="max_days_pay">Max Dias</label>
                                                            <input type="number" id="max_days_pay" class="form-control" value="15">
                                                            <p class="text-muted">Max Dias P/Pago Comprobantes</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-2 mt-3">
                                                        <div class="form-group">
                                                            <label for="ingresosbrutos">Ingresos Brutos</label>
                                                            <input type="text" id="ingresosbrutos" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4 mt-3" id="cuenta_contable_select" style="display:none">
                                                        <div class="form-group">
                                                            <label for="nueva_cuentaasignada">Cuenta Contable</label>

                                                            <select id="nueva_cuentaasignada" class="form-control select2" data-toggle="select2">
                                                                <option>Seleccionar Cuenta Contable</a></option>
                                                                <optgroup label="Afip">

                                                                </optgroup>
                                                            </select>


                                                        </div>
                                                    </div>

                                                    <div class="col-lg-4 mt-3" id="cuenta_contable">
                                                        <div class="form-group">
                                                            <label for="cuentaasignada">Cuenta Contable</label>
                                                            <div class="input-group mb-3">
                                                                <input type="number" name='cuentaasignada' id="cuentaasignada" class="form-control" placeholder="Cuenta Contable" aria-label="Cuenta Contable" aria-describedby="button-addon2" readonly>
                                                                <div class="input-group-append">
                                                                    <button id="modificar_cuenta" type="button" class="btn btn-outline-info"><i class="uil-exchange-alt"></i> Cambiar</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2 mt-3">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="solicitavehiculo" name="solicitavehiculo">
                                                            <label class="custom-control-label" for="solicitavehiculo" data-on-label="1" data-off-label="0">Solicita Vehiculo</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2 mt-3">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="solicitacombustible" name="solicitacombustible">
                                                            <label class="custom-control-label" for="solicitacombustible" data-on-label="1" data-off-label="0">Solicita Combustible</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2 mt-3">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="tareas_asana" name="tareas_asana">
                                                            <label class="custom-control-label" for="tareas_asana" data-on-label="1" data-off-label="0">Tareas Asana</label>
                                                        </div>
                                                        <select id="asana_gid" class="form-control select2" data-toggle="select2">
                                                            <option>Select</option>
                                                        </select>


                                                    </div>
                                                </div>
                                            </form>
                                        </div><!--panel1-->
                                        <div class="tab-pane" id="ctacte-information">
                                            <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="btn-group float-right">
                                                            <!-- <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal_cargar_factura">Large Modal</button>                                                         -->
                                                            <!-- <a class="btn btn-warning float-right ml-1" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modal_cargar_factura" >Cargar Factura</a> -->
                                                            <!-- <a class="btn btn-success float-right ml-1">Pago a Cuenta</a> -->
                                                            <!--<a class="btn btn-success float-right ml-1">Pagar Factura</a>-->
                                                            <h3 class="header-title mt-1">Saldo Cuenta Corriente: <a class="text-info float-right" id="saldo_ctacte"></a></h3>
                                                        </div>

                                                        <!-- <h4 class="header-title mt-2">Cuenta Corriente Saldo: <a class="text-info float-right" id="saldo_ctacte"></a></h4> -->
                                                        <!-- <h4 class="header-title mt-2">Anticipos: <a class="text-success" id="anticipos_ctacte"></a></h4> -->
                                                        <div class="table-responsive">
                                                            <table class="table table-centered w-100" id="basic" style="font-size:13px">
                                                                <tbody>
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Fecha</th>
                                                                            <th>Comprobante</th>
                                                                            <th>Numero</th>
                                                                            <th>Descripcion</th>
                                                                            <th>Debe</th>
                                                                            <th>Haber</th>
                                                                            <th>Ver</th>
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
                                                                        <td></td>
                                                                        <td></td>
                                                                    </tr>
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div> <!-- end card-->
                                                    </div> <!-- end col-->
                                                </div>
                                            </div>
                                            <div class="row" id="row_contacto">
                                                <div class="col-lg-4">
                                                    <div class="card text-white bg-info overflow-hidden">
                                                        <div class="card-body">
                                                            <div class="toll-free-box text-center">
                                                                <h4> <i class="mdi mdi-headset"></i><a id="telefono_contacto"></a></h4>
                                                            </div>
                                                        </div> <!-- end card-body-->
                                                    </div>
                                                </div> <!-- end col-->
                                                <div class="col-lg-4">
                                                    <div class="card text-white bg-danger overflow-hidden">
                                                        <div class="card-body">
                                                            <div class="toll-free-box text-center">
                                                                <h4> <i class="mdi mdi-email"></i> <a id="mail_contacto"></a> </h4>
                                                            </div>
                                                        </div> <!-- end card-body-->
                                                    </div>
                                                </div> <!-- end col-->
                                                <div class="col-lg-4">
                                                    <div class="card bg-success text-white">
                                                        <div class="card-body">
                                                            <div class="toll-free-box text-center">
                                                                <h4> <i class="mdi mdi-account"></i><a id="contacto_contacto"></a> </h4>
                                                            </div>
                                                        </div> <!-- end card-body-->
                                                        <!--                                                 </div> -->
                                                    </div> <!-- end col-->
                                                </div>
                                            </div>
                                        </div> <!-- card body-->
                                    </div> <!--card-->
                                </div> <!--card12-->
                            </div><!--row-->
                        </div><!--container-fluid-->
                    </div><!--content-->
                    <!-- container -->
                    <!-- Footer Start -->
                    <footer class="footer">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-6">
                                    <script>
                                        document.write(new Date().getFullYear())
                                    </script> © Caddy - Sistema
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

            </div>
            <!-- END wrapper -->


            <!-- bundle -->
            <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
            <script src="../hyper/dist/saas/assets/js/app.min.js"></script>

            <!-- plugin js -->
            <script src="../hyper/dist/saas/assets/js/vendor/dropzone.min.js"></script>
            <!-- init js -->
            <script src="../hyper/dist/saas/assets/js/ui/component.fileupload.js"></script>

            <!-- third party js -->
            <!-- <script src="../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script> -->
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
            <script src="../hyper/dist/saas/assets/js/vendor/dataTables.checkboxes.min.js"></script>
            <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script>

            <!--  enlases externos para botonera-->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script><!--excel-->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script><!--pdf-->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script><!--pdf-->

            <!-- funciones -->
            <script src="Procesos/js/funciones.js"></script>
            <script src="../Menu/js/funciones.js"></script>
            <script src="Procesos/js/proveedores.js"></script>
            <script src="Procesos/js/proveedores_pagos.js"></script>
</body>

</html>