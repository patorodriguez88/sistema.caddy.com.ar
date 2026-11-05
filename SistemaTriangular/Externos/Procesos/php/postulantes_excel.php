<?php
// Excel con los que se postularon en "Trabajá con nosotros" de web.caddy.com.ar: el formulario
// (guardar_chofer.php del repo de la web) los guarda en logs/choferes.csv, fuera de la base.
// Son datos personales: mismo permiso que la pantalla de Externos.
include_once "../../../Conexion/Conexioni.php";
require_once __DIR__ . '/../../../Menu/php/permisos_menu.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function postulantes_error(int $codigo, string $mensaje): void
{
    http_response_code($codigo);
    header('Content-Type: text/plain; charset=utf-8');
    echo $mensaje;
    exit;
}

/** La web y el sistema están en la misma cuenta del hosting, pero la carpeta de la web no se sabe de antemano. */
function postulantes_archivo(): ?string
{
    $base = preg_match('#^(/home\d*/[^/]+)/#', __DIR__, $m) ? $m[1] : dirname(__DIR__, 5);
    $candidatos = array_merge(
        [dirname(__DIR__, 5) . '/web.caddy.com.ar/logs/choferes.csv'],
        glob($base . '/web.caddy.com.ar*/logs/choferes.csv') ?: [],
        glob($base . '/*/web.caddy.com.ar*/logs/choferes.csv') ?: []
    );
    foreach ($candidatos as $archivo) {
        if (is_readable($archivo)) {
            return $archivo;
        }
    }
    return null;
}

if (!tieneMenuPermiso('Rec.Humanos', 'Externos')) {
    postulantes_error(403, 'No tenés permiso para descargar este archivo.');
}
$archivo = postulantes_archivo();
if ($archivo === null) {
    postulantes_error(404, 'No se encontró el archivo de postulantes de la web (logs/choferes.csv).');
}
$fh = fopen($archivo, 'r');
if (!$fh) {
    postulantes_error(500, 'No se pudo leer el archivo de postulantes.');
}

$encabezado = fgetcsv($fh, 0, ';', '"', '\\') ?: [];
$filas = [];
while (($fila = fgetcsv($fh, 0, ';', '"', '\\')) !== false) {
    if ($fila !== [null]) {
        $filas[] = $fila;
    }
}
fclose($fh);
$filas = array_reverse($filas); // los últimos que se postularon, arriba

$titulos = array_map(fn($t) => ucfirst(str_replace('_', ' ', (string) $t)), $encabezado);
$spreadsheet = new Spreadsheet();
$hoja = $spreadsheet->getActiveSheet();
$hoja->setTitle('Postulantes');
$nro = 1;
foreach (array_merge([$titulos], $filas) as $fila) {
    foreach (array_values($fila) as $i => $valor) {
        $valor = (string) $valor;
        if (!mb_check_encoding($valor, 'UTF-8')) {
            $valor = mb_convert_encoding($valor, 'UTF-8', 'Windows-1252');
        }
        // Siempre como texto: lo escribe cualquiera desde la web, y un "=..." no puede quedar como fórmula.
        $hoja->setCellValueExplicit([$i + 1, $nro], $valor, DataType::TYPE_STRING);
    }
    $nro++;
}
$columnas = max(count($titulos), 1);
$hoja->getStyle([1, 1, $columnas, 1])->getFont()->setBold(true);
$hoja->freezePane('A2');
$hoja->setAutoFilter([1, 1, $columnas, max($nro - 1, 1)]);
for ($i = 1; $i <= $columnas; $i++) {
    $hoja->getColumnDimensionByColumn($i)->setAutoSize(true);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Postulantes_web_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: no-store');
(new Xlsx($spreadsheet))->save('php://output');
