<?php
// Etiquetas QR de los bolsines (Logistica/Bolsines.php). El QR lleva solo el código (BOL-0001),
// que es lo que lee la app de reparto en Warehouse. Uso: BolsinesQR.php?ids=1,2,3 (sin ids: todos).
include_once "../Conexion/Conexioni.php";

$ids = array_filter(array_map('intval', explode(',', (string) ($_GET['ids'] ?? ''))));
$where = $ids ? 'AND id IN (' . implode(',', $ids) . ')' : '';
$res = $mysqli->query("SELECT Codigo, Descripcion FROM Bolsines WHERE Eliminado = 0 $where ORDER BY Codigo");
$bolsines = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Bolsines QR</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 10mm; }
        body.modal { padding: 16px; }
        body.modal .grilla { justify-content: center; }
        .grilla { display: flex; flex-wrap: wrap; gap: 6mm; }
        .etiqueta { width: 60mm; border: 1px dashed #999; padding: 4mm; text-align: center; page-break-inside: avoid; }
        .etiqueta img { width: 44mm; height: 44mm; }
        .codigo { font-size: 20pt; font-weight: bold; letter-spacing: 1px; margin-top: 2mm; }
        .leyenda { font-size: 9pt; color: #333; }
        .acciones { margin-bottom: 8mm; }
        @media print { .acciones { display: none; } body { padding: 0; } }
    </style>
</head>

<body class="<?= empty($_GET['modal']) ? '' : 'modal' ?>">
    <?php if (empty($_GET['modal'])): // en el modal de Bolsines.php imprime el botón del modal ?>
    <div class="acciones">
        <button onclick="window.print()">Imprimir</button>
        <?= count($bolsines) ?> etiqueta(s)
    </div>
    <?php endif; ?>
    <div class="grilla">
        <?php foreach ($bolsines as $b): $c = htmlspecialchars($b['Codigo']); ?>
            <div class="etiqueta">
                <img src="../Funciones/php/qr.php?s=8&d=<?= urlencode($b['Codigo']) ?>" alt="<?= $c ?>">
                <div class="codigo"><?= $c ?></div>
                <div class="leyenda">Caddy · Bolsín Interno<br>Vuelve siempre al depósito</div>
                <?php if (trim((string) $b['Descripcion']) !== ''): ?>
                    <div class="leyenda"><?= htmlspecialchars($b['Descripcion']) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>
