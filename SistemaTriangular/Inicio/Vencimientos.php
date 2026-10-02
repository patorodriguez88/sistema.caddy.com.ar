<?php
// Home > Vencimientos: licencias de choferes, seguro / VTV / tarjeta verde y
// service de los vehículos, y cheques propios pendientes de débito. Reemplaza a
// la pantalla vieja, que usaba mysql_* (no existe en PHP 8) y daba error al entrar.
include_once "../Conexion/Conexioni.php";

$hoy = date('Y-m-d');
$en30 = date('Y-m-d', strtotime('+30 days'));

function vtoFilas(mysqli $db, string $sql): array
{
    $res = $db->query($sql);
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function vtoFecha(?string $f): string
{
    return ($f && !str_starts_with($f, '0000')) ? date('d/m/Y', strtotime($f)) : 'Sin dato';
}

// Rojo = vencido o sin dato, amarillo = vence en los próximos 30 días.
// Vehículos: solo la flota propia (Aliados = 0); los de aliados no tienen estas fechas cargadas.
function vtoClase(?string $f, string $hoy, string $en30): string
{
    if (!$f || str_starts_with($f, '0000') || $f < $hoy) {
        return 'danger';
    }
    return $f <= $en30 ? 'warning' : 'success';
}

$vehiculo = "CONCAT_WS(' ', Marca, Modelo, Dominio)";
$bloques = [
    [
        'titulo' => 'Licencias de conducir', 'icono' => 'mdi-card-account-details-outline',
        'filas'  => vtoFilas($mysqli, "SELECT NombreCompleto AS Nombre, VencimientoLicencia AS Fecha
                                         FROM Empleados WHERE Inactivo = 0 ORDER BY VencimientoLicencia"),
    ],
    [
        'titulo' => 'Seguro', 'icono' => 'mdi-shield-car',
        'filas'  => vtoFilas($mysqli, "SELECT $vehiculo AS Nombre, FechaVencSeguro AS Fecha
                                         FROM Vehiculos WHERE Estado <> 'Vendida' AND Aliados = 0 ORDER BY FechaVencSeguro"),
    ],
    [
        'titulo' => 'VTV / ITV', 'icono' => 'mdi-clipboard-check-outline',
        'filas'  => vtoFilas($mysqli, "SELECT $vehiculo AS Nombre, FechaVencITV AS Fecha
                                         FROM Vehiculos WHERE Estado <> 'Vendida' AND Aliados = 0 ORDER BY FechaVencITV"),
    ],
    [
        'titulo' => 'Tarjeta verde', 'icono' => 'mdi-card-text-outline',
        'filas'  => vtoFilas($mysqli, "SELECT $vehiculo AS Nombre, VencimientoTarjetaVerde AS Fecha
                                         FROM Vehiculos WHERE Estado <> 'Vendida' AND Aliados = 0 ORDER BY VencimientoTarjetaVerde"),
    ],
];

$services = vtoFilas($mysqli, "SELECT $vehiculo AS Nombre, Kilometros, ProximoService,
                                      (ProximoService - Kilometros) AS Faltan
                                 FROM Vehiculos WHERE Estado <> 'Vendida' AND Aliados = 0
                             ORDER BY Faltan");

$cheques = vtoFilas($mysqli, "SELECT FechaCobro, Banco, NumeroCheque, Proveedor, Importe
                                FROM Cheques
                               WHERE Terceros = 0 AND Utilizado = 1 AND Pagado = 0
                                 AND FechaCobro >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                            ORDER BY FechaCobro");
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Vencimientos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema Caddy" name="author" />

    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

    <style>
        .vto-lista { max-height: 340px; overflow-y: auto; }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid mt-3">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                                        <li class="breadcrumb-item active">Vencimientos</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Vencimientos</h4>
                            </div>
                            <p class="text-muted">
                                <span class="badge bg-danger">Vencido</span>
                                <span class="badge bg-warning text-dark">Vence en 30 días</span>
                                <span class="badge bg-success">Al día</span>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <?php foreach ($bloques as $b): ?>
                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3"><i class="mdi <?= $b['icono'] ?> me-1"></i><?= htmlspecialchars($b['titulo']) ?></h5>
                                        <div class="vto-lista">
                                            <?php if (!$b['filas']): ?>
                                                <p class="text-muted mb-0">Sin datos.</p>
                                            <?php endif; ?>
                                            <?php foreach ($b['filas'] as $f): ?>
                                                <div class="d-flex justify-content-between border-bottom py-1 small">
                                                    <span><?= htmlspecialchars((string) $f['Nombre']) ?></span>
                                                    <span class="badge bg-<?= vtoClase($f['Fecha'], $hoy, $en30) ?><?= vtoClase($f['Fecha'], $hoy, $en30) === 'warning' ? ' text-dark' : '' ?>"><?= vtoFecha($f['Fecha']) ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3"><i class="mdi mdi-wrench-outline me-1"></i>Próximo service (flota propia)</h5>
                                    <div class="vto-lista">
                                        <?php foreach ($services as $s):
                                            $sinDato = $s['Faltan'] === null || (float) $s['ProximoService'] <= 0;
                                            $faltan = (int) $s['Faltan'];
                                            $clase = $sinDato ? 'secondary' : ($faltan < 0 ? 'danger' : ($faltan < 2000 ? 'warning text-dark' : 'success')); ?>
                                            <div class="d-flex justify-content-between border-bottom py-1 small">
                                                <span><?= htmlspecialchars((string) $s['Nombre']) ?>
                                                    <span class="text-muted">(<?= number_format((float) $s['Kilometros'], 0, ',', '.') ?> km)</span></span>
                                                <span class="badge bg-<?= $clase ?>">
                                                    <?= $sinDato ? 'Sin dato' : ($faltan < 0 ? 'Pasado ' . number_format(-$faltan, 0, ',', '.') . ' km' : 'Faltan ' . number_format($faltan, 0, ',', '.') . ' km') ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3"><i class="mdi mdi-checkbook me-1"></i>Cheques propios pendientes de débito</h5>
                                    <div class="vto-lista">
                                        <?php if (!$cheques): ?>
                                            <p class="text-muted mb-0">No hay cheques propios pendientes.</p>
                                        <?php endif; ?>
                                        <?php foreach ($cheques as $c): ?>
                                            <div class="d-flex justify-content-between border-bottom py-1 small">
                                                <span><?= vtoFecha($c['FechaCobro']) ?> · N° <?= htmlspecialchars((string) $c['NumeroCheque']) ?> · <?= htmlspecialchars((string) $c['Proveedor']) ?></span>
                                                <span class="badge bg-<?= $c['FechaCobro'] <= $hoy ? 'danger' : 'light text-dark' ?>">$ <?= number_format((float) $c['Importe'], 2, ',', '.') ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="menuhyper_footer"></div>
        </div>
    </div>

    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/assets/js/app.js"></script>
    <script src="../Menu/js/funciones.js"></script>
</body>

</html>
