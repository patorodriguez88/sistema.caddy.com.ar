<?php
require_once "../../../Conexion/Conexioni.php";
require_once "../../../vendor/autoload.php"; // si usas Composer (PhpSpreadsheet)
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// === Nombre dinámico del archivo ===
$meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
$mesActual = strtoupper($meses[(int)date('n') - 1]);
$anioActual = date('Y');

$nombreArchivo = "TarifasxLocalidad_Caddy_{$mesActual}{$anioActual}.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$nombreArchivo\"");

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Localidades y Tarifas');
// Encabezado del export (una vez)
mb_internal_encoding('UTF-8');

function titlecase_es($s)
{
    $s = mb_convert_case(mb_strtolower($s), MB_CASE_TITLE, 'UTF-8'); // Title Case básico
    // Excepciones en medio de la frase
    $small = ['De', 'Del', 'La', 'Las', 'El', 'Los', 'Y', 'E', 'A', 'Al', 'En', 'Por', 'Para', 'Con', 'O', 'U'];
    $pal = preg_split('/(\s+)/u', $s, -1, PREG_SPLIT_DELIM_CAPTURE); // conserva espacios
    foreach ($pal as $i => $p) {
        if ($i === 0) continue; // primera palabra queda capitalizada
        if (in_array($p, $small, true)) {
            $pal[$i] = mb_strtolower($p, 'UTF-8');
        }
    }
    return implode('', $pal);
}

// === Obtener datos de Localidades ===
$soloWeb = isset($_GET['solo_web']) ? (int)$_GET['solo_web'] : 0;

// === Cabeceras ===
$headers = ['Localidad', 'Provincia', 'Días de Salida', 'Tarifa 1', 'Tarifa 2', 'Tarifa 3', 'Tarifa 4', 'Tarifa 5', 'Tarifa 6', 'Tarifa 7', 'Tarifa 8'];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $col++;
}
// === Agregar descripción debajo de "Tarifa N" en el encabezado ===
// Buscamos la PRIMERA descripción por número de Tarifa (Tarifa 1..8), sin importar A/B/C
$descPorNumero = [];
$sqlDesc = "
  SELECT Titulo, IFNULL(Descripcion,'') AS Descripcion, Kilometros
  FROM Productos
  WHERE Grupo = 'Web'
  ORDER BY Titulo ASC, Kilometros ASC
";
if ($resDesc = $mysqli->query($sqlDesc)) {
    while ($r = $resDesc->fetch_assoc()) {
        if (preg_match('/Tarifa\s*(\d+)/i', (string)$r['Titulo'], $m)) {
            $n = (int)$m[1]; // número de tarifa
            if ($n >= 1 && $n <= 8) {
                // guardamos la primera descripción no vacía que encontremos
                if (!isset($descPorNumero[$n]) || $descPorNumero[$n] === '') {
                    $descPorNumero[$n] = (string)$r['Descripcion'];
                }
            }
        }
    }
    $resDesc->free();
}

// Escribimos "Tarifa N\nDescripción" en D1..K1 (sin cambiar el resto)
for ($n = 1; $n <= 8; $n++) {
    $colLetra = chr(ord('C') + $n); // D..K
    $titulo  = "Tarifa {$n}";
    $desc    = $descPorNumero[$n] ?? '';

    if ($desc !== '') {
        // Usamos RichText para poner el título en negrita y la descripción debajo
        $rich = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        $run1 = $rich->createTextRun($titulo);
        $run1->getFont()->setBold(true);
        $rich->createText("\n" . $desc);

        $sheet->setCellValue($colLetra . '1', $rich);
    } else {
        // Si no hay descripción, dejamos el título tal cual
        $sheet->setCellValue($colLetra . '1', $titulo);
    }

    // Aseguramos salto de línea y centrado vertical en el encabezado
    $sheet->getStyle($colLetra . '1')->getAlignment()->setWrapText(true);
    $sheet->getStyle($colLetra . '1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
}

// Un poco más de altura para que entre el título + descripción
$sheet->getRowDimension(1)->setRowHeight(44);

// === Obtener datos de Localidades ===
// $sqlLocalidades = "
//   SELECT id, Localidad, Provincia, DiaSalida, Km
//   FROM Localidades
//   ORDER BY Localidad ASC";
$sqlLocalidades = "
  SELECT id, Localidad, Provincia, DiaSalida, Km
  FROM Localidades";
if ($soloWeb === 1) {
    // Solo visitadas por Caddy
    $sqlLocalidades .= " WHERE Web = 1";
} // else: todas (sin filtro)

$sqlLocalidades .= " ORDER BY Localidad ASC";

$resLoc = $mysqli->query($sqlLocalidades);

if (!$resLoc) {
    http_response_code(500);
    die("Error SQL Localidades: " . $mysqli->error);
}


$resLoc = $mysqli->query($sqlLocalidades);
if (!$resLoc) {
    die("Error SQL Localidades: " . $mysqli->error);
}

$rowNum = 2;
while ($loc = $resLoc->fetch_assoc()) {
    $Localidad = $loc['Localidad'];
    $Provincia = $loc['Provincia'];
    $Dias = $loc['DiaSalida'];
    $KmLocalidad = floatval($loc['Km']);

    // === Buscar tarifas aplicables ===
    $sqlTarifas = "
      SELECT Titulo, PrecioVenta, Kilometros 
      FROM Productos
      WHERE Grupo = 'Web'
      ORDER BY Kilometros ASC";
    $resTarifas = $mysqli->query($sqlTarifas);

    $tarifas = [];
    if ($resTarifas) {
        while ($t = $resTarifas->fetch_assoc()) {
            // Aplica si el kilometraje máximo de la tarifa >= localidad
            if ($KmLocalidad <= floatval($t['Kilometros'])) {
                $tarifas[] = $t['PrecioVenta'];
            }
            // si querés mostrar todas, aunque no apliquen, sacá el if
        }
        $resTarifas->free();
    }

    // Limitar a 8 tarifas
    $tarifas = array_slice($tarifas, 0, 8);

    // === Escribir fila ===
    $sheet->setCellValue('A' . $rowNum, titlecase_es($Localidad));
    $sheet->setCellValue('B' . $rowNum, titlecase_es($Provincia));
    $sheet->setCellValue('C' . $rowNum, $Dias);

    $colIndex = 'D';
    foreach ($tarifas as $tar) {
        $sheet->setCellValue($colIndex . $rowNum, $tar);
        // formato moneda
        $sheet->getStyle($colIndex . $rowNum)
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
        $colIndex++;
    }

    $rowNum++;
}

// === Formato general ===
foreach (range('A', 'L') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// === Descargar ===
// Separadores regionales
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator(',');
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setThousandsSeparator('.');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
