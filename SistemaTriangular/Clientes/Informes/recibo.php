<?php

if (isset($_GET['token']) && $_GET['token'] != '') {

  include_once "Procesos/php/Conexion_unique.php";
  $mostrarBoton = false; // Establece esta variable según tu lógica para decidir si mostrar o no el botón


} else {

  include_once "../Conexion/Conexion.php";
  $mostrarBoton = true; // Establece esta variable según tu lógica para decidir si mostrar o no el botón

}

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <title>Sistema Caddy | </title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
  <meta content="Coderthemes" name="author" />

  <!-- Caddy favicon -->
  <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
  <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
  <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

  <!-- Plugin css -->
  <link href="/SistemaTriangular/hyper/dist/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css">
  <link href="/SistemaTriangular/hyper/dist/assets/vendor/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css">

  <!-- Datatables css -->
  <link href="/SistemaTriangular/hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
  <!-- For checkbox Select-->
  <link href="/SistemaTriangular/hyper/dist/assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css">
  <!-- For Buttons -->
  <link href="/SistemaTriangular/hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">
  <!-- Fixe header-->
  <link href="/SistemaTriangular/hyper/dist/assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css">

  <!-- Theme Config Js -->
  <script src="/SistemaTriangular/hyper/dist/assets/js/hyper-config.js"></script>

  <!-- Vendor css -->
  <link href="/SistemaTriangular/hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

  <!-- App css -->
  <link href="/SistemaTriangular/hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

  <!-- Icons css -->
  <link href="/SistemaTriangular/hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
  <link href="/SistemaTriangular/hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
  <link href="/SistemaTriangular/hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
</head>

<body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
  <!-- Begin page -->
  <div class="wrapper">

    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->

    <!-- <div class="content-page"> -->
    <div class="content">


      <!--DESDE ACA FACTURA -->
      <div class="row" id="factura_proforma">
        <div class="col-12">
          <div class="card">
            <div class="card-body">

              <!-- Invoice Logo-->
              <div class="clearfix">
                <div class="float-left mb-3">
                  <img src="../../images/LogoCaddy.png" alt="" height="70">
                </div>
                <div class="float-right">
                  <h2 class="mr-15" id="factura_titulo"></h2>
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
                  </address>
                </div>
                <!-- end col-->
                <div class="col-sm-4 offset-sm-2">
                  <div class="float-sm-end">
                    <h4 id="factura_titulo2"></h4>
                    <address>
                      <strong>N:</strong> <a></a>-
                      <a id="NumeroComprobante">00000000000</a><br>
                      <strong>Fecha Pago: <a id="FechaComprobante"></a></strong><br>
                      <strong>Id de Cliente: </strong><a id="factura_codigo"></a><br>
                      <strong>Estado del Coprobante: </strong><span id="estado" class="badge badge-success">Acreditado</span>
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
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-4">
                    <h5>Fecha de Impresion Comprobante</h5>
                    <p id="">
                      <script>
                        var f = new Date();
                        document.write(f.getDate() + "/" + (f.getMonth() + 1) + "/" + f.getFullYear());
                      </script>
                      <small class="text-muted"> Cuenta Corriente </small>
                    </p>
                  </div>
                </div>
              </div>
              <!-- end row -->

              <div class="row mt-2">
                <div class="col-sm-4">

                  <h4>Recibimos de <a id="factura_razonsocial"></a></h4>
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
                    <strong> Telefono:</strong> <abbr title="Phone">+54-</abbr><a id="factura_celular"></a><br>
                    <strong> Mail:</strong><a id="factura_email"></a>
                  </address>
                </div>
                <!-- end col-->
              </div>
              <!-- end row -->

              <div class="row">
                <div class="col-sm-6">
                  <div class="text-sm-left">
                    <!-- <img src="../hyper/dist/saas/assets/images/barcode.png" alt="barcode-image" class="img-fluid mr-2" /> -->
                  </div>
                </div>
                <!-- end col-->
                <div class="col-sm-6">
                  <div class="float-right mt-3 mt-sm-0 mr-3">
                    <p><b>Forma de Pago: </b> <span id="factura_formadepago" class="float-right"></span></p>
                    <p><b id="fp_name_0"></b> <span id="fp_text_0" class="float-right"></span></p>
                    <p><b id="fp_name_1"></b> <span id="fp_text_1" class="float-right"></span></p>
                    <p><b id="fp_name_2"></b> <span id="fp_text_2" class="float-right"></span></p>
                    <p>
                    <h3><b>Total Comprobante: </b><span id="factura_total"></h3>
                    </p>

                    <div class="clearfix"></div>
                  </div> <!-- end col -->
                </div>
              </div>
              <!-- end row-->
              <div class="row">
                <div class="col-sm-12">
                  <div class="clearfix pt-3">
                    <h6 class="text-muted">Observaciones:</h6>
                    <small id="nota_proforma" style="display:block">

                    </small>
                  </div>
                </div> <!-- end col -->
              </div>


              <!-- end buttons -->

            </div> <!-- end card-body-->

            <div class="col-sm-10" id="datos_cae" style="display:block">
              <div class="text-muted">
                <h6>usuario: <a id="usuario"></a><br> id: <a id="id"></a><br>
                  <a><?php
                      if ($_GET['token']) {
                        echo 'token: ' . $_GET['token'];
                      }
                      ?></a>
                </h6>
              </div>
              <div class="text-muted text-sm-right">
                <script>
                  document.write(new Date())
                </script> © Sistema Caddy
              </div>
            </div> <!-- end col-->
          </div> <!-- end card -->
        </div>
      </div>

      <!-- Vendor js -->
      <script src="/SistemaTriangular/hyper/dist/assets/js/vendor.min.js"></script>
      <script src="/SistemaTriangular/hyper/dist/assets/js/app.js"></script>
      <script src="/SistemaTriangular/hyper/dist/assets/vendor/moment/moment.min.js"></script>
      <script src="/SistemaTriangular/hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script>
      <?php include '/SistemaTriangular/Menu/php/script_maps-vector.php'; ?>
      <!-- DataTables -->
      <?php include '/SistemaTriangular/Menu/php/script_datatables.php'; ?>
      <!-- funciones -->
      <script src="/SistemaTriangular/Menu/js/funciones.js"></script>
      <script src="/SistemaTriangular/Clientes/Procesos/js/recibo.js"></script>
      <script src="/SistemaTriangular/Funciones/js/datosempresa.js"></script>
      <script src="/SistemaTriangular/Funciones/js/alertas.js"></script>
</body>

</html>