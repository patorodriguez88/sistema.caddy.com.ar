<?php
include_once "../Conexion/Conexioni.php";
$mostrarBoton = true; // Establece esta variable según tu lógica para decidir si mostrar o no el botón
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <title>Sistema Caddy | </title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
  <meta content="Coderthemes" name="author" />
  <link rel="shortcut icon" href="../images/favicon/apple-icon.png">
  <link href="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css">
  <link href="../hyper/dist/assets/vendor/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css">
  <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
  <link href="../hyper/dist/assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css">
  <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">
  <link href="../hyper/dist/assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css">
  <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

  <!-- App css -->
  <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

  <!-- Icons css -->
  <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
  <link href="../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
  <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

</head>
<style>
  .invoice-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 0.125rem 0.25rem rgba(15, 23, 42, 0.08);
  }

  .invoice-header {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 1rem;
    margin-bottom: 1.25rem;
  }

  .invoice-brand img {
    max-height: 68px;
    width: auto;
  }

  .invoice-letter {
    width: 64px;
    height: 64px;
    border: 2px solid #212529;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0 auto;
  }

  .invoice-title-box {
    text-align: end;
  }

  .invoice-title-box h2,
  .invoice-title-box h4 {
    margin-bottom: 0;
  }

  .invoice-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1rem;
    height: 100%;
  }

  .invoice-section h5,
  .invoice-section h6 {
    margin-bottom: .75rem;
    font-weight: 700;
  }

  .invoice-label {
    color: #6c757d;
    font-weight: 600;
    min-width: 120px;
    display: inline-block;
  }

  .invoice-client-box {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem;
    background: #fff;
    height: 100%;
  }

  .invoice-totals {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem 1.25rem;
  }

  .invoice-total-final {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0d6efd;
    border-top: 1px dashed #ced4da;
    padding-top: .75rem;
    margin-top: .75rem;
  }

  .invoice-notes {
    background: #fff8e1;
    border: 1px solid #ffe69c;
    border-radius: 12px;
    padding: 1rem;
  }

  .invoice-afip-box {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem;
    background: #fff;
    height: 100%;
  }

  .invoice-actions .btn {
    min-width: 130px;
  }

  @media print {
    @page {
      size: A4;
      margin: 10mm;
    }

    html,
    body {
      background: #fff !important;
      margin: 0 !important;
      padding: 0 !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    body {
      font-size: 11px !important;
      line-height: 1.3 !important;
    }

    h1,
    h2,
    h3 {
      font-size: 16px !important;
    }

    h4,
    h5 {
      font-size: 13px !important;
    }

    .invoice-section h5,
    .invoice-client-box h5,
    .invoice-afip-box h5,
    .invoice-totals h5 {
      font-size: 12px !important;
    }

    .invoice-section h6,
    .invoice-client-box h6,
    .invoice-afip-box h6,
    .invoice-notes h6 {
      font-size: 11px !important;
    }

    .invoice-section,
    .invoice-client-box,
    .invoice-afip-box,
    .invoice-notes,
    .invoice-totals,
    .invoice-label,
    .invoice-section div,
    .invoice-client-box div,
    .invoice-afip-box div,
    .invoice-notes small,
    .text-muted,
    span,
    p,
    small {
      font-size: 10.5px !important;
    }

    .table,
    table {
      width: 100% !important;
      border-collapse: collapse !important;
      font-size: 9.5px !important;
    }

    .table th,
    table th {
      font-size: 9.5px !important;
      font-weight: 600 !important;
    }

    .table td,
    table td {
      font-size: 9.5px !important;
    }

    .page-title-box,
    .footer,
    .modal,
    .btn,
    .d-print-none,
    #compose-modal,
    #standard-modal-codcliente {
      display: none !important;
    }

    .wrapper,
    .content,
    .container-fluid {
      width: 100% !important;
      max-width: 100% !important;
      margin: 0 !important;
      padding: 0 !important;
      background: #fff !important;
    }

    .card,
    .invoice-card {
      border: 0 !important;
      box-shadow: none !important;
      border-radius: 0 !important;
      margin: 0 !important;
      padding: 0 !important;
      background: #fff !important;
    }

    .invoice-header {
      border-bottom: 1px solid #d9d9d9 !important;
      padding-bottom: 8px !important;
      margin-bottom: 10px !important;
    }

    .row {
      display: flex !important;
      flex-wrap: wrap !important;
      margin-left: -4px !important;
      margin-right: -4px !important;
      margin-bottom: 4px !important;
    }

    .row>[class*="col-"] {
      padding-left: 4px !important;
      padding-right: 4px !important;
      margin-bottom: 4px !important;
    }

    .col-lg-7 {
      flex: 0 0 58.333333% !important;
      max-width: 58.333333% !important;
    }

    .col-lg-5 {
      flex: 0 0 41.666667% !important;
      max-width: 41.666667% !important;
    }

    .col-md-4 {
      flex: 0 0 33.333333% !important;
      max-width: 33.333333% !important;
    }

    .col-md-2 {
      flex: 0 0 16.666667% !important;
      max-width: 16.666667% !important;
    }

    .col-md-6 {
      flex: 0 0 50% !important;
      max-width: 50% !important;
    }

    .col-12 {
      flex: 0 0 100% !important;
      max-width: 100% !important;
    }

    .invoice-section,
    .invoice-client-box,
    .invoice-totals,
    .invoice-afip-box,
    .invoice-notes {
      background: #fff !important;
      border: 1px solid #d9d9d9 !important;
      box-shadow: none !important;
      border-radius: 8px !important;
      padding: 8px !important;
      margin-bottom: 4px !important;
      height: 100% !important;
      break-inside: avoid !important;
      page-break-inside: avoid !important;
    }

    .invoice-brand img {
      max-height: 48px !important;
    }

    .invoice-letter {
      width: 40px !important;
      height: 40px !important;
      font-size: 20px !important;
      border: 1.5px solid #222 !important;
      margin: 0 auto !important;
    }

    .invoice-title-box {
      text-align: right !important;
    }

    .invoice-title-box h2 {
      font-size: 18px !important;
      margin-bottom: 2px !important;
    }

    .invoice-title-box h5 {
      font-size: 11px !important;
      margin-bottom: 0 !important;
    }

    .table th,
    .table td,
    table th,
    table td {
      border: 1px solid #d9d9d9 !important;
      padding: 4px !important;
      vertical-align: middle !important;
    }

    .table thead th {
      background: #f3f3f3 !important;
      color: #000 !important;
    }

    .table-responsive {
      overflow: visible !important;
    }

    .invoice-total-final {
      color: #000 !important;
      border-top: 1px dashed #bdbdbd !important;
      font-size: 13px !important;
    }

    /* Compactar espacios entre campos */
    .invoice-section .mb-2,
    .invoice-client-box .mb-2,
    .invoice-afip-box .mb-2,
    .invoice-totals .mb-2 {
      margin-bottom: 3px !important;
    }

    .invoice-section .mb-3,
    .invoice-client-box .mb-3,
    .invoice-afip-box .mb-3,
    .invoice-totals .mb-3 {
      margin-bottom: 5px !important;
    }

    .invoice-section div,
    .invoice-client-box div,
    .invoice-afip-box div,
    .invoice-totals div {
      line-height: 1.1 !important;
    }

    .invoice-label {
      min-width: 90px !important;
      margin-right: 4px !important;
    }

    #row_tabla_facturacion {
      display: none !important;
    }

    #row_tabla_recorridos {
      display: flex !important;
    }
  }
</style>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const params = new URLSearchParams(window.location.search);
    if (params.get("print") === "1") {
      setTimeout(function() {
        window.focus();
        window.print();
      }, 700);
    }
  });
</script>

<body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
  <!-- Begin page -->
  <div class="wrapper">

    <div class="content">

      <div class="modal fade" id="standard-modal-codcliente" tabindex="-1" aria-labelledby="myCenterModalLabel_codcliente" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="myCenterModalLabel_codcliente">Modificar código de cliente</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="codigocliente_t" class="form-label">Código de Cliente</label>
                <input id="codigocliente_t" type="text" class="form-control">
                <div class="form-text">Ingresá el código del cliente a asociar al comprobante.</div>
              </div>
              <input type="hidden" id="cs_codigocliente">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                <i class="mdi mdi-close mdi-18px me-1"></i>Cancelar
              </button>
              <button id="modificarcodigocliente_ok" type="button" class="btn btn-success">
                <i class="mdi mdi-content-save mdi-18px me-1"></i>Guardar
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- Start Content-->
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="page-title-box">
              <div class="page-title-right">
                <ol class="breadcrumb m-0">
                </ol>
              </div>
            </div>
          </div>
        </div>

        <!-- Large modal -->

        <!-- Compose Modal -->
        <div id="compose-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="compose-header-modalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header modal-colored-header bg-primary">
                <h4 class="modal-title" id="compose-header-modalLabel">Enviar Factura</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              </div>


              <div class="modal-body p-3">
                <form id="miFormulario" class="p-1" action="../Mail/invoice.php" method="post">
                  <div class="form-group mb-2">
                    <label for="txtSelect">To</label>
                    <select name="txtSelect" id="txtSelect" class="select2 form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                      <optgroup label="Contactos" id="email_contactos">
                      </optgroup>
                    </select>

                  </div>
                  <div class="form-group mb-2">
                    <label for="txtAsunto">Subject</label>
                    <input type="text" name="txtAsunto" id="txtAsunto" class="form-control" placeholder="subject">
                  </div>

                  <input type="hidden" name="txtId" id="txtId" value="">
                  <input type="hidden" name="txtToken" id="txtToken">
                  <input type="hidden" name="txtEmail" id="txtEmail">
                  <input type="hidden" name="txtName" id="txtName">
                  <input type="hidden" name="txtComprobante" id="txtComprobante" value="1">
                  <input type="hidden" name="txtTotal" id="txtTotal">
                  <input type="hidden" name="txtPeriodo" id="txtPeriodo">
                  <input type="hidden" name="txtVencimiento" id="txtVencimiento">
                  <input type="hidden" name="txtNumfactura" id="txtNumfactura">

                  <button id="button_sendmail" type="button" class="btn btn-primary" data-dismiss="modal"><i class="mdi mdi-send mr-1"></i> Send Message</button>
                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                </form>
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <!--DESDE ACA FACTURA -->
        <div class="row" id="factura_proforma">
          <div class="col-12">
            <div class="card invoice-card">
              <div class="card-body">

                <!-- Header -->
                <div class="invoice-header">
                  <div class="row align-items-center gy-3">
                    <div class="col-md-4">
                      <div class="invoice-brand">
                        <img src="../images/LogoCaddy.png" alt="Logo Caddy" class="img-fluid">
                      </div>
                    </div>

                    <div class="col-md-4 text-center">
                      <div class="invoice-letter" id="letra"></div>
                    </div>

                    <div class="col-md-4">
                      <div class="invoice-title-box">
                        <h2 id="factura_titulo" class="fw-bold text-uppercase"></h2>
                        <h5 id="factura_titulo2" class="text-muted mt-1"></h5>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Empresa / Comprobante -->
                <div class="row g-3 mb-3">
                  <div class="col-lg-7">
                    <div class="invoice-section">
                      <h5 class="mb-3">Datos del emisor</h5>
                      <h4 id="Emp_RazonSocial" class="mb-3"></h4>

                      <div class="mb-2"><span class="invoice-label">Dirección:</span> <span id="Emp_Direccion"></span></div>
                      <div class="mb-2"><span class="invoice-label">CUIT:</span> <span id="Emp_Cuit"></span></div>
                      <div class="mb-2"><span class="invoice-label">IIBB:</span> <span id="Emp_IngresosBrutos"></span></div>
                      <div class="mb-0"><span class="invoice-label">Teléfono:</span> <span id="Emp_Telefono"></span></div>
                    </div>
                  </div>

                  <div class="col-lg-5">
                    <div class="invoice-section">
                      <h5 class="mb-3">Datos del comprobante</h5>

                      <div class="mb-2"><span class="invoice-label">Número:</span> <span id="NumeroComprobante">00000000000</span></div>
                      <div class="mb-2"><span class="invoice-label">Fecha:</span> <span id="FechaComprobante"></span></div>
                      <div class="mb-2"><span class="invoice-label">Id Cliente:</span> <span id="factura_codigo"></span></div>
                      <div class="mb-2">
                        <span class="invoice-label">Estado:</span>
                        <span id="estado" class="badge bg-danger">Pendiente</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Condiciones -->
                <div class="row g-3 mb-4">
                  <div class="col-md-4">
                    <div class="invoice-section">
                      <h6>Condición del emisor</h6>
                      <div>Responsable Inscripto</div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="invoice-section">
                      <h6>Periodo facturado</h6>
                      <div><span class="invoice-label">Desde:</span> <span id="desde_f"></span></div>
                      <div><span class="invoice-label">Hasta:</span> <span id="hasta_f"></span></div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="invoice-section">
                      <h6>Vencimiento de pago</h6>
                      <div id="venc_pago"></div>
                      <small class="text-muted">Cuenta corriente</small>
                    </div>
                  </div>
                </div>

                <!-- Cliente -->
                <div class="row g-3 mb-4">
                  <div class="col-md-4">
                    <div class="invoice-client-box">
                      <h5 class="mb-3" id="factura_razonsocial">Razón Social</h5>
                      <div class="mb-2"><span class="invoice-label">Dirección:</span> <span id="factura_direccion"></span></div>
                      <div class="mb-0"><span class="invoice-label">Condición:</span> <span id="factura_condicion"></span></div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="invoice-client-box">
                      <h5 class="mb-3">Datos fiscales</h5>
                      <div class="mb-2"><span class="invoice-label">CUIT:</span> <span id="factura_cuit"></span></div>
                      <div class="mb-0"><span class="invoice-label">IIBB:</span> <span id="factura_ingresosbrutos"></span></div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="invoice-client-box">
                      <h5 class="mb-3">Contacto</h5>
                      <div class="mb-2"><span class="invoice-label">Teléfono:</span> <span>+54-<span id="factura_celular"></span></span></div>
                      <div class="mb-0"><span class="invoice-label">Email:</span> <span id="factura_email"></span></div>
                    </div>
                  </div>
                </div>

                <!-- Tabla servicios -->
                <div class="row mb-4" id="row_tabla_facturacion" style="display:none;">
                  <div class="col-12">
                    <div class="table-responsive">
                      <table class="table table-sm table-hover table-bordered align-middle mb-0 w-100" id="tabla_facturacion_proforma" style="font-size:11px">
                        <thead class="table-light">
                          <tr>
                            <th>Fecha</th>
                            <th>Seguimiento</th>
                            <th>Comprobante</th>
                            <th>Cliente Destino</th>
                            <th id="codigo_cliente">Código Cliente</th>
                            <th class="text-end">Importe</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-end"></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Tabla recorridos -->
                <div class="row mb-4" id="row_tabla_recorridos" style="display:none">
                  <div class="col-12">
                    <div class="table-responsive">
                      <table class="table table-sm table-hover table-bordered align-middle mb-0 w-100" id="tabla_facturacion_proforma_recorridos" style="font-size:11px">
                        <thead class="table-light">
                          <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Comprobante</th>
                            <th>Observaciones</th>
                            <th class="text-end">Importe</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-end"></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Totales -->
                <div class="row justify-content-end mb-4">
                  <div class="col-lg-5">
                    <div class="invoice-totals">
                      <input type="hidden" id="factura_neto_f">
                      <input type="hidden" id="factura_iva_f">
                      <input type="hidden" id="factura_total_f">

                      <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold">Total Neto:</span>
                        <span id="factura_neto"></span>
                      </div>

                      <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold">Descuento (%):</span>
                        <span id="factura_descuento"></span>
                      </div>

                      <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold">IVA (21%):</span>
                        <span id="factura_iva"></span>
                      </div>

                      <div class="d-flex justify-content-between invoice-total-final">
                        <span>Total Comprobante:</span>
                        <span id="factura_total"></span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Observaciones -->
                <div class="row mb-4">
                  <div class="col-12">
                    <div class="invoice-notes">
                      <h6 class="text-muted mb-2">Observaciones</h6>

                      <small id="nota_factura" style="display:none">
                        Producido el vencimiento la mora será automática, aplicándose una tasa de interés
                        del 8,30 % mensual por el término de la misma.
                        Los remitos relacionados con la presente factura se detallan en el Extracto N°..
                      </small>

                      <small id="nota_proforma" style="display:block">
                        Los siguientes servicios fueron prestados por Caddy al cliente que figura en el comprobante y están sujetos
                        a verificación y control.
                        Los importes que figuran en este comprobante están sujetos a variaciones de acuerdo a la situación impositiva
                        que se deduzca de la documentación entregada por el cliente.
                      </small>
                    </div>
                  </div>
                </div>

                <!-- AFIP / QR -->
                <div class="row g-3 mb-4">
                  <div class="col-md-2">
                    <div class="invoice-afip-box text-center">

                      <?php
                      $idCtaCte = isset($_GET['id']) ? (int)$_GET['id'] : 0;

                      $sql = "SELECT IvaVentas.* 
        FROM IvaVentas 
        INNER JOIN Ctasctes ON Ctasctes.idIvaVentas = IvaVentas.id 
        WHERE Ctasctes.id = ?";
                      $stmt = $mysqli->prepare($sql);
                      $stmt->bind_param("i", $idCtaCte);
                      $stmt->execute();
                      $result = $stmt->get_result();
                      $row = $result->fetch_array(MYSQLI_ASSOC);
                      $stmt->close();

                      if (!empty($row['id'])) {

                        $a = $row['TipoDeComprobante'];

                        $sql_tipo_comp = "SELECT Codigo FROM AfipTipoDeComprobante WHERE Descripcion = ?";
                        $stmt = $mysqli->prepare($sql_tipo_comp);
                        $stmt->bind_param("s", $a);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $dato_tipo_comp = $result->fetch_array(MYSQLI_ASSOC);
                        $TipoComp = !empty($dato_tipo_comp['Codigo']) ? (int)$dato_tipo_comp['Codigo'] : 0;
                        $stmt->close();

                        $Ncomp = explode('-', (string)$row['NumeroComprobante']);
                        $PtoVta = isset($Ncomp[0]) ? (int)$Ncomp[0] : 0;
                        $Numero = isset($Ncomp[1]) ? (int)$Ncomp[1] : 0;
                        $Documento = preg_replace("/[^0-9]/", "", (string)$row['Cuit']);

                        $url = 'https://www.afip.gob.ar/fe/qr/';
                        $PNG_TEMP_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
                        $PNG_WEB_DIR = '/SistemaTriangular/Clientes/temp/';

                        require_once "../phpqrcode/qrlib.php";

                        $datos_cmp_base_64 = json_encode([
                          "ver" => 1,
                          "fecha" => $row['Fecha'],
                          "cuit" => (int)30715344943,
                          "ptoVta" => $PtoVta,
                          "tipoCmp" => $TipoComp,
                          "nroCmp" => $Numero,
                          "importe" => (float)$row['Total'],
                          "moneda" => "PES",
                          "ctz" => 1,
                          "tipoDocRec" => 80,
                          "nroDocRec" => (int)$Documento,
                          "tipoCodAut" => "E",
                          "codAut" => (int)$row['CAE']
                        ]);

                        $datos_cmp_base_64 = base64_encode($datos_cmp_base_64);
                        $to_qr = $url . '?p=' . $datos_cmp_base_64;

                        if (!file_exists($PNG_TEMP_DIR)) {
                          mkdir($PNG_TEMP_DIR, 0777, true);
                        }

                        $filename = $PNG_TEMP_DIR . 'qr_' . (int)$row['id'] . '_' . md5($to_qr) . '.png';

                        if (!file_exists($filename)) {
                          QRcode::png($to_qr, $filename, 'L', 3, 2);
                        }

                        if (file_exists($filename)) {
                          echo '<img id="img_qr" src="' . $PNG_WEB_DIR . basename($filename) . '?v=' . time() . '" class="img-fluid" alt="QR AFIP">';
                        } else {
                          echo '<div class="text-danger small">No se pudo generar el QR</div>';
                        }
                      ?>

                    </div>
                  </div>

                  <div id="afip_pie" class="col-md-6">
                    <div class="invoice-afip-box">
                      <img src="../afip.php/images/qr.png" alt="AFIP QR" height="60" class="mb-2" /><br>
                      <h5 class="mb-2">Comprobante Autorizado</h5>
                      <h6 class="mb-0">Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación.</h6>
                    </div>
                  </div>

                  <div id="afip_pie1" class="col-md-4">
                    <div class="invoice-afip-box">
                      <h5 class="mb-3">Datos de autorización</h5>
                      <div class="mb-2"><span class="invoice-label">CAE:</span> <span id="CAE"></span></div>
                      <div class="mb-0"><span class="invoice-label">Vto. CAE:</span> <span id="VencimientoCAE"></span></div>
                    </div>
                  </div>
                <?php } ?>
                </div>


              </div>

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <!-- Vendor js -->
  <script src="../hyper/dist/assets/js/vendor.min.js"></script>
  <script src="../hyper/dist/assets/js/hyper-config.js"></script>
  <script src="../hyper/dist/assets/js/app.js"></script>
  <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>
  <script src="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script>
  <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script>
  <?php include '../Menu/php/script_maps-vector.php'; ?>
  <!-- DataTables -->
  <?php include '../Menu/php/script_datatables.php'; ?>
  <!-- funciones -->
  <script src="Procesos/js/invoice.js"></script>
  <script src="../Funciones/js/datosempresa.js"></script>
</body>

</html>