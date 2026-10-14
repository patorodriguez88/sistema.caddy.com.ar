<?php
date_default_timezone_set('America/Argentina/Cordoba');
require_once __DIR__.'/../Conexion/Conexioni.php';
require_once __DIR__.'/../Menu/php/permisos_menu.php';
if (!nivelPuedeEntrarAlSistema($_SESSION['Nivel'] ?? 0) || !tieneMenuPermiso('Home','Panel Admin')) {
    http_response_code(403); exit('No tenés permiso para consultar el Panel Admin.');
}
$initialMonth = $_GET['month'] ?? date('Y-m');
if (!is_string($initialMonth) || !preg_match('/^(19|20|21)\d{2}-(0[1-9]|1[0-2])$/D', $initialMonth)) $initialMonth=date('Y-m');
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">
<head>
    <meta charset="utf-8">
    <title>Inteligencia logística | Sistema Caddy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../images/favicon/favicon-32x32.png">
    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet">
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" id="app-style">
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet">
    <link href="../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet">
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="css/panel.css" rel="stylesheet">
    <link href="css/dashboard_logistica.css?v=<?= filemtime(__DIR__.'/css/dashboard_logistica.css') ?>" rel="stylesheet">
</head>
<body>
<div class="wrapper">
    <?php include __DIR__.'/../Menu/head.html'; include __DIR__.'/../Menu/topnav.html'; ?>
    <div class="content-page"><div class="content"><main class="container-fluid cf-page lg-page">
        <header class="cf-header">
            <div><div class="cf-eyebrow">Home / Panel Admin</div><h1 class="cf-title">Inteligencia logística</h1><div class="cf-sub">Rentabilidad, productividad y composición de la operación.</div></div>
            <div class="cf-actions"><a href="dashboard_.php" class="btn btn-light"><i class="ri-funds-line"></i> Resultados Home</a><button id="export-report" class="btn btn-outline-primary" disabled><i class="ri-download-2-line"></i> Exportar análisis</button></div>
        </header>
        <form id="dashboard-filters" class="lg-panel lg-filters">
            <div><label for="filter-month" class="form-label">Mes de servicio</label><input type="month" id="filter-month" name="month" value="<?= htmlspecialchars($initialMonth,ENT_QUOTES,'UTF-8') ?>" class="form-control" required></div>
            <div><label for="filter-client" class="form-label">Cliente facturable</label><select id="filter-client" name="client" class="form-select"><option value="">Todos los clientes</option></select></div>
            <div><label for="filter-fleet" class="form-label">Flota</label><select id="filter-fleet" name="fleet" class="form-select"><option value="">Todas las flotas</option></select></div>
            <div><label for="filter-service" class="form-label">Servicio</label><select id="filter-service" name="service" class="form-select"><option value="">Todos los servicios</option></select></div>
            <div class="lg-filter-actions"><button class="btn btn-primary" id="apply-filters"><i class="ri-filter-3-line"></i> Aplicar</button><button type="button" id="clear-filters" class="btn btn-light">Limpiar</button><button type="button" class="btn btn-light" data-bs-toggle="collapse" data-bs-target="#advanced-filters" aria-expanded="false" aria-controls="advanced-filters">Más filtros</button></div>
            <div class="collapse" id="advanced-filters"><div class="lg-advanced">
                <?php foreach (['driver'=>'Repartidor','route'=>'Ruta / recorrido','trip'=>'Salida / flete','zone'=>'Zona de entrega (localidad)','province'=>'Provincia','integration'=>'Integración'] as $key=>$label): ?>
                <div><label for="filter-<?= $key ?>" class="form-label"><?= $label ?></label><select id="filter-<?= $key ?>" name="<?= $key ?>" class="form-select"><option value="">Todos</option></select></div>
                <?php endforeach; ?>
            </div></div>
        </form>
        <div id="dashboard-status" class="lg-status" role="status" aria-live="polite">Preparando el análisis…</div>
        <div id="dashboard-error" class="alert alert-danger d-none" role="alert"></div>
        <div id="dashboard-content" hidden>
            <section class="lg-period"><div><span class="lg-dot"></span><strong id="period-label"></strong><span class="text-muted" id="period-note"></span></div><span class="lg-chip">Fecha de servicio · ARS</span></section>
            <div id="empty-report" class="alert alert-info d-none">No hay actividad para este mes y estos filtros. Seleccioná otro período o limpiá los filtros.</div>
            <section class="cf-kpis" id="financial-kpis" aria-label="Indicadores de rentabilidad"></section>
            <section class="lg-insights lg-panel"><div class="lg-section-title"><div><div class="cf-eyebrow">Lectura ejecutiva</div><h2>Qué nos dice la operación</h2></div><i class="ri-lightbulb-flash-line"></i></div><div id="executive-insights" class="lg-insight-grid"></div></section>
            <div class="row g-3 mb-3">
                <section class="col-xl-8"><div class="lg-panel h-100"><div class="lg-section-title"><div><h2>Evolución del negocio</h2><p>12 meses de servicios · conserva los filtros seleccionados</p></div></div><div id="chart-trend" class="lg-chart" aria-label="Gráfico de evolución mensual"></div></div></section>
                <section class="col-xl-4"><div class="lg-panel h-100"><div class="lg-section-title"><div><h2>Incidencia del flete</h2><p>Costos registrados / ventas sin IVA</p></div></div><div id="chart-incidence" class="lg-chart"></div><p id="incidence-analysis" class="lg-chart-note"></p></div></section>
            </div>
            <section class="lg-panel mb-3"><div class="lg-section-title"><div><div class="cf-eyebrow">Utilización y productividad</div><h2>La operación en números</h2><p>Estado actual de los servicios del mes · puede cambiar con entregas posteriores</p></div></div><div id="operation-kpis" class="lg-operation-grid"></div></section>
            <div class="row g-3 mb-3">
                <section class="col-xl-7"><div class="lg-panel h-100"><div class="lg-section-title"><div><h2>Contribución por cliente</h2><p>Los 10 clientes con mayor venta · venta menos flete registrado</p></div></div><div id="chart-clients" class="lg-chart"></div></div></section>
                <section class="col-xl-5"><div class="lg-panel h-100"><div class="lg-section-title"><div><h2>Composición de servicios</h2><p>Participación en la venta sin IVA</p></div></div><div id="chart-services" class="lg-chart"></div></div></section>
            </div>
            <section class="lg-panel mb-3"><div class="lg-section-title"><div><h2>Rentabilidad en detalle</h2><p>Cambiá la dimensión para detectar concentración, costos y oportunidades.</p></div><div class="lg-table-controls"><label for="group-dimension" class="visually-hidden">Dimensión del análisis</label><select id="group-dimension" class="form-select">
                <option value="client">Por cliente</option><option value="driver">Por repartidor</option><option value="fleet">Propio / externo</option><option value="service">Por tipo de servicio</option><option value="route">Por ruta / recorrido</option><option value="trip">Por salida / flete</option><option value="zone">Por zona de entrega</option><option value="province">Por provincia</option><option value="integration">Por integración</option>
            </select><label for="group-search" class="visually-hidden">Buscar en el análisis</label><input id="group-search" type="search" class="form-control" placeholder="Buscar en el análisis"></div></div>
                <div class="table-responsive"><table class="table table-hover align-middle lg-table"><thead><tr><th><button type="button" data-sort="name">Dimensión ↕</button></th><th><button type="button" data-sort="services">Servicios ↕</button></th><th><button type="button" data-sort="sale">Venta sin IVA ↕</button></th><th><button type="button" data-sort="cost">Flete registrado ↕</button></th><th><button type="button" data-sort="contribution">Contribución ↕</button></th><th><button type="button" data-sort="margin">Margen ↕</button></th><th><button type="button" data-sort="incidence">Flete / venta ↕</button></th><th><button type="button" data-sort="coverage">Con costo ↕</button></th><th><button type="button" data-sort="km">Km reales ↕</button></th><th>Venta / km*</th><th>Flete / km*</th></tr></thead><tbody id="dimension-rows"></tbody><tfoot id="dimension-total"></tfoot></table></div>
                <div class="lg-table-footer"><span id="table-count"></span><div><button id="table-prev" class="btn btn-sm btn-light">Anterior</button><span id="table-page" class="mx-2"></span><button id="table-next" class="btn btn-sm btn-light">Siguiente</button></div></div>
                <p class="lg-chart-note">* Solo servicios con kilómetros reales vinculados; los km de una salida se distribuyen entre sus servicios. Una cobertura de costos baja hace que el margen sea provisional. No se imputan gastos generales a clientes.</p>
            </section>
            <div class="row g-3 mb-3">
                <section class="col-xl-8"><div class="lg-panel h-100"><div class="lg-section-title"><div><h2>Pulso diario</h2><p>Venta y flete por fecha de servicio dentro del mes</p></div></div><div id="chart-daily" class="lg-chart"></div></div></section>
                <section class="col-xl-4"><div class="lg-panel h-100"><div class="lg-section-title"><div><h2>Mix de flota</h2><p>Contribución y cobertura por operación</p></div></div><div id="fleet-analysis"></div></div></section>
            </div>
            <section class="lg-panel mb-3"><div class="lg-section-title"><div><div class="cf-eyebrow">Contexto de gastos · empresa completa</div><h2>Gastos y conciliación con resultados</h2><p>Sin filtros operativos. Fecha del asiento de Tesorería o del gasto extra.</p></div><a href="../Admin/Resultados.php" class="btn btn-sm btn-light">Ver Resultados por servicio <i class="ri-arrow-right-up-line"></i></a></div>
                <div class="lg-expense-grid"><div><div id="expense-summary" class="lg-expense-summary"></div><p class="lg-chart-note">Esta referencia usa ventas brutas del modelo y gastos registrados. No equivale al resultado contable ni incluye la comisión teórica de cobranza del 5% de Home. Los fletes pueden estar incluidos en Tesorería: no se vuelven a restar de esta referencia.</p></div><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Cuenta de gasto</th><th class="text-end">Importe registrado</th><th class="text-end">Participación</th></tr></thead><tbody id="expense-rows"></tbody></table></div></div>
            </section>
            <section class="lg-panel lg-methodology"><div class="lg-section-title"><div><h2>Calidad de datos y criterios del análisis</h2><p>Una lectura transparente de lo que está medido y de lo que falta registrar.</p></div></div><div id="quality-alerts"></div>
                <details><summary>Ver cómo se calcula cada indicador</summary><div class="lg-method-grid">
                    <p><strong>Fecha y unidad.</strong> Los envíos se agrupan por código y por TransClientes.Fecha; no por fecha de pago o rendición. Los movimientos de haber y eliminados quedan fuera. Una orden sin envíos usa Logistica.Fecha. Se incluyen colectas cuando están registradas como servicio: son trabajo operativo, no entregas finales.</p>
                    <p><strong>Venta.</strong> Debe / 1,21, siguiendo Resultados. Para recorridos: cargo activo de Ctasctes asociado a idLogistica; si no es positivo, PrecioRecorrido. Se reparte entre los servicios sin venta de esa orden, incluso si están en otro mes. Sin servicios elegibles, se muestra una línea de orden. No se usa TotalFacturado, porque puede repetir la factura completa.</p>
                    <p><strong>Flete.</strong> Suma de PrecioPagado + CobranzaIntegrada por código en Externos_rendicion, aunque se liquide después. Para flota propia se usa CostoKmTotalImputado; para externa, Costo_rendicion de la orden. Se distribuye el remanente no cubierto por rendiciones, evitando contarlo dos veces. Sin registro positivo, el costo figura como faltante.</p>
                    <p><strong>Contribución y margen.</strong> Venta sin IVA menos flete registrado. Margen = contribución / venta; incidencia = flete / venta. Incluye servicios sin costo para reconciliar la venta, por lo que es provisional si falta cobertura. Los costos se toman por el importe guardado; no se presume ni modifica su tratamiento de IVA.</p>
                    <p><strong>Clientes y dimensiones.</strong> Cliente facturable según FormaDePago (origen o destino). Si falta el ID, se vincula por nombre exacto únicamente cuando identifica un solo cliente; en otro caso figura Sin ID. Ruta y salida corresponden a la orden vinculada. Zona de entrega = localidad del destinatario. Repartidor externo proviene de la rendición; si hay varios se muestra el conjunto. Propio/externo usa la rendición o Vehiculos.Aliados; la vinculación actual puede cambiar al editar vehículos o recorridos.</p>
                    <p><strong>Integraciones y kilómetros.</strong> Tienda Nube usa Origen = API_TIENDANUBE. Mercado Libre usa identificadores de 11 dígitos que empiezan con 4. Los demás orígenes se muestran tal como están registrados o sin identificar. No se estiman km ausentes. Venta/km y flete/km consideran únicamente hechos con km registrados.</p>
                    <p><strong>Cobranza y gastos.</strong> Los importes not_invoice rendidos en Ventas se suman como ingreso operativo según Resultados. Los recibos de clientes y los pagos a fleteros no cambian la fecha del servicio. Gastos suma Tesorería con MuestraGastos=1 y NoOperativo=0, más GastosExtras si existe; no se reparte ni se vuelve a descontar del margen por cliente.</p>
                    <p><strong>Comparación.</strong> Mes seleccionado contra mes anterior completo, con los mismos filtros. Un mes en curso se identifica como parcial; el porcentaje compara acumulados de distinta duración. La tendencia muestra los 12 meses terminados en el período elegido.</p>
                </div></details>
            </section>
        </div>
    </main></div><div id="menuhyper_footer"></div></div>
</div>
<script src="../hyper/dist/assets/js/vendor.min.js"></script>
<script src="../hyper/dist/assets/js/app.js"></script>
<script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="../Menu/js/funciones.js"></script>
<script src="js/dashboard_logistica.js?v=<?= filemtime(__DIR__.'/js/dashboard_logistica.js') ?>"></script>
</body></html>
