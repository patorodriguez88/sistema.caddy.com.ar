<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
include_once "../../../Empleados/Procesos/php/asana_api.php";
$dominio='AA523RS';
$sql_projects=$mysqli->query("SELECT Dominio,gid_projets_asana FROM `Vehiculos` WHERE Dominio='".$dominio."'");
$row_projects=$sql_projects->fetch_array(MYSQLI_ASSOC);

$projects=$row_projects['gid_projets_asana'];
echo $projects;

// $projects='1207004592303956';
        $name='prueba';
        $notes='notas';
        $due_on='2024-04-09';
        $assignee='1207004401160014';
        $workspace='734348733635084';

        // $gid_asana=Create_task($projects,$titulo,$nota,$fecha,$assignee,$workspace);
        $gid_asana = Create_task('1207004592303956','Titulo','notas','1207004401160014','734348733635084');    
        echo $gid_asana;

        ?>