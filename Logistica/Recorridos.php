<?php
include_once "../Conexion/Conexioni.php";
if ($_SESSION['Usuario'] == '') {
    header('location:https://www.sistemacaddy.com.ar/sistema');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Recorridos</title>
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

<body class="loading" data-layout="topnav" data-layout-config='{layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
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

                <!-- MODAL FIJOS -->

                <div class="modal fade" id="bs-fijos-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myLargeModalLabel">Servicios Fijos</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-10">
                                            <label>Seleccione un Servicio</label>

                                            <? $sql = "SELECT a.id,a.Cliente as Destino,
                                                    b.RazonSocial as Origen, 
                                                    b.ClienteDestino as Destino,
                                                    b.ingBrutosOrigen as idOrigen,
                                                    b.idClienteDestino as idDestino,
                                                    b.Debe,
                                                    b.EntregaEn,
                                                    b.Retirado
                                                    FROM Roadmap a INNER JOIN TransClientes b ON a.idTransClientes=b.id 
                                                    WHERE a.Recorrido='$_SESSION[Recorrido]' AND Asignado='Unica Vez' AND a.Eliminado=0";

                                            ?>

                                            <select class="form-control select2" data-toggle="select2">
                                                <option>Seleccionar Cliente Origen</option>
                                                <optgroup label="Clientes">
                                                    <?
                                                    if ($resultado = $mysqli->query($sql)) {
                                                        while ($row = $resultado->fetch_assoc()) {
                                                            if ($row['Retirado'] == 0) {
                                                                $Servicio = 'Retiro';
                                                            } else {
                                                                $Servicio = 'Entrega';
                                                            }
                                                    ?>
                                                            <option value="<? echo $row[id]; ?>"><? echo 'Origen: ' . $row[Origen] . ' Destino: <b>' . $row[Destino] . '</b> $ ' . $row[Debe] . ' ' . $row[EntregaEn] . ' ' . $Servicio; ?></option>
                                                    <?
                                                        }
                                                    }
                                                    ?>
                                                </optgroup>
                                            </select>

                                        </div>
                                        <div class="col-2">
                                            <a id="sumar"></a><i class="mdi mdi-18px mdi-table-plus"></i>
                                        </div>
                                    </div><!--row-->
                                </div>

                                <table class="table table-striped table-centered mb-0" id="envios_fijos" style="font-size:12px">
                                    <thead>
                                        <tr>
                                            <th>Origen</th>
                                            <th>Destino</th>
                                            <th>Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>


                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->


                <!-- ELIMINAR REGISTRO DEJAR FIJOS -->
                <div id="remove_permanent_warning-header-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header modal-colored-header bg-warning">
                                <h4 class="modal-title" id="warning-header-modalLabel">Modal Heading</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">
                                Estas por eliminar un Servicio fijo. Deseas Continuar?.
                            </div>
                            <div class="modal-footer">

                                <button id="btn_remove_permanent" type="button" class="btn btn-success">Aceptar</button>
                                <button id="btn_not_remove_permanent" type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->



                <!-- //MODIFICAR RECORRIDO -->
                <div class="modal fade" id="standard-modal-rec" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div id="standard-modal-rec-header" class="modal-header modal-colored-header bg-success">
                                <h4 class="modal-title" id="myCenterModalLabel_rec">AGREGAR NUEVO RECORRIDO</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <form id="modal-rec-form">
                                <input id="id_mod_rec" style="display:none">
                                <div class="col-lg-12">
                                    <h4 class="mt-2">Nuevo Recorrido</h4>

                                    <p class="text-muted mb-4">Agregue un Recorrido para poder generar las Hojas de Ruta.</p>
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="billing-last-name">Numero</label>
                                                <input type="text" id="recorrido_number" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <label>Nombre Recorrido</label>
                                                <input type="text" id="recorrido_name" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Zona</label>
                                                <input type="text" id="recorrido_zone" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Dias de Salida del Recorrido</label>
                                                <select id="dates" name="dates[]" class="select2 form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                                                    <optgroup label="Dias de la Semana">
                                                        <option value="Lunes">Lunes</option>
                                                        <option value="Martes">Martes</option>
                                                        <option value="Miercoles">Miercoles</option>
                                                        <option value="Jueves">Jueves</option>
                                                        <option value="Viernes">Viernes</option>
                                                        <option value="Sabado">Sabado</option>
                                                        <option value="Domingo">Domingo</option>
                                                    </optgroup>
                                                </select>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="billing-first-name">Kilometros</label>
                                                <input type="text" id="recorrido_km" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="recorrido_toll">Peajes</label>
                                                <input type="text" id="recorrido_toll" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="recorrido_color">Color</label>
                                                <input type="color" id="recorrido_color" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Cliente</label>

                                                <? $sql = "SELECT id,nombrecliente,Direccion FROM Clientes ORDER BY nombrecliente ASC"; ?>

                                                <select id="recorrido_guest" class="form-control select2" data-toggle="select2">
                                                    <option>Seleccionar un Cliente</option>
                                                    <optgroup label="Clientes">
                                                        <?
                                                        if ($resultado = $mysqli->query($sql)) {
                                                            while ($row = $resultado->fetch_assoc()) {
                                                        ?>
                                                                <option value="<? echo $row[id]; ?>"><? echo $row[id] . ' - ' . $row[nombrecliente] . '  (Dir.:' . $row[Direccion] . ')'; ?></option>
                                                        <?
                                                            }
                                                        }
                                                        ?>
                                                    </optgroup>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Servicio</label>
                                                <? $sql = "SELECT Codigo,Titulo,PrecioVenta FROM Productos ORDER BY Titulo ASC"; ?>
                                                <select id="recorrido_service" class="form-control select2" data-toggle="select2">
                                                    <option>Seleccionar un Servicio</option>
                                                    <optgroup label="Servicios">
                                                        <?
                                                        if ($resultado = $mysqli->query($sql)) {
                                                            while ($row = $resultado->fetch_assoc()) {
                                                        ?>
                                                                <option value="<? echo $row[Codigo]; ?>"><? echo $row[Codigo] . ' - ' . $row[Titulo] . ' $ ' . $row[PrecioVenta]; ?></option>
                                                        <?
                                                            }
                                                        }
                                                        ?>
                                                    </optgroup>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer mt-3">
                                    <!-- Success Switch-->
                                    <p for="fijo_switch"> Dejar Fijo siempre </p>
                                    <input type="checkbox" id="fijo_switch" checked data-switch="success" />
                                    <label for="fijo_switch" data-on-label="Yes" data-off-label="No"></label>

                                    <input type="hidden" id="cs_modificar_REC">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                    <button id="recorrido_ok" type="button" class="btn btn-success">Aceptar</button>
                                    <button id="recorrido_mod_ok" type="button" class="btn btn-warning">Aceptar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                            <div class="card">
                                <div class="card-body">
                                    <h4 id="seguimiento_header" class="header-title mt-2">RECORRIDOS CADDY LOGISTICA </h4>
                                    <button id="agregar_rec_btn" type="button" class="btn btn-success mb-2" data-toggle="modal" data-target="#standard-modal-rec"><i class="mdi mdi-map-marker-plus-outline mr-1"></i> <span>Agregar Recorrido</span> </button>
                                    <table class="table table-striped table-centered mb-0" id="recorridos" style="font-size:12px">
                                        <thead>
                                            <tr>
                                                <th>Numero</th>
                                                <th>Nombre</th>
                                                <th>Kilometros</th>
                                                <th>Peajes</th>
                                                <th>Servicio</th>
                                                <th>Envios Fijos</th>
                                                <th>Dias Salida</th>
                                                <th>Color</th>
                                                <th>Estado</th>
                                                <th>Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                                <button id="warning-modal-ok" type="button" class="btn btn-danger">Eliminar</button>
                                <button id="warning-modal-ventas-ok" type="button" class="btn btn-danger" style="display:none">Eliminar Ventas</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
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
    <!--         <script src="https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js"></script> -->
    <!-- third party js ends -->
    <!-- end demo js-->
    <!-- funciones -->
    <script src="Proceso/js/recorridos.js"></script>
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <!-- Funciones Imprimir Rotulos -->
</body>

</html>