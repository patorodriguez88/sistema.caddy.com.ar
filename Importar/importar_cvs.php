<?php
include_once "../Conexion/Conexioni.php";
require_once('vendor/SpreadsheetReader.php');

$Reader = new SpreadsheetReader('subidas/REC_TOT-9.CSV');
$Sheets = $Reader -> Sheets();

foreach ($Sheets as $Index => $Name)
{
    echo 'Sheet #'.$Index.': '.$Name;

    $Reader -> ChangeSheet($Index);

    foreach ($Reader as $Row)
    {
        print_r($Row);
    }
}
?>