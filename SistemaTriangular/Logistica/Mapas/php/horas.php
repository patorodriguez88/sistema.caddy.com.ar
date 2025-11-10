<?
    $Hora='07:30:00';
    $duration='583';
    $minutos=number_format($duration/60,0);
    $segundos= $duration % 60;

    echo "$minutos minuto(s) $segundos segundo(s)";
    // $mifecha= date('Y-m-d H:i:s'); 
    // $NuevaFecha = strtotime ( , strtotime ($Hora)); 
    $newHora = new DateTime($Hora); 
    $newHora->modify('+0 hours'); 
    $newHora->modify('+'.$minutos.' minute'); 
    $newHora->modify('+'.$segundos.' second'); 
    $Hora_actual= $newHora->format('H:i:s');
echo $Hora_actual;
    // echo $NuevaFecha;


?>  