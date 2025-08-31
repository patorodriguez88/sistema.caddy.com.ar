<?php
$host="localhost";
$user="dinter6_prodrig";
$pass="pato@4986";
$db="dinter6_triangular";

// $mysqli = new mysqli($host,$user,$pass,$db);
$connection = mysqli_connect($host,$user,$pass,$db) or die(mysqli_error($connection));
date_default_timezone_set('America/Argentina/Cordoba');

// header('Content-Type: application/json');
    if(isset($_GET['view']))
    {
        header('Content-Type: application/json');
        $start = mysqli_real_escape_string($connection,$_GET["start"]);
        $end = mysqli_real_escape_string($connection,$_GET["end"]);
        $result = mysqli_query($connection,"SELECT `id`, `start` ,`end` ,`title`,`className`,`Observaciones` FROM  `Calendario` where (date(start) >= '$start' AND date(end) <= '$end')");

      while($row = mysqli_fetch_assoc($result))
        {
            $events[] = $row; 
        }
        echo json_encode($events); 
        exit;
    }elseif($_POST['action'] == "add") // add new event
    {   
     if(!$_POST[id]){
      $result = mysqli_query($connection,"SELECT `id`,Nombre FROM  `Recorridos` where Numero='$_POST[title]'"); 
      $row = mysqli_fetch_assoc($result);
      $_POST[id]=$row[id];
      $_POST[Observaciones]=$row[Nombre]; 
     } 
      
        mysqli_query($connection,"INSERT INTO `Calendario` (
                    `title` ,
                    `start` ,
                    `end` ,
                    `className` ,
                    `idRelacion` ,
                    `Observaciones` ,
                    `Estado`
                    ) VALUES (
                    '".mysqli_real_escape_string($connection,$_POST["title"])."',
                    '".mysqli_real_escape_string($connection,date('Y-m-d H:i:s',strtotime($_POST["start"])))."',
                    '".mysqli_real_escape_string($connection,date('Y-m-d H:i:s',strtotime($_POST["end"])))."' ,
                    '".mysqli_real_escape_string($connection,$_POST["category"])."' ,
                    '".mysqli_real_escape_string($connection,$_POST["id"])."' ,
                    '".mysqli_real_escape_string($connection,$_POST["Observaciones"])."' ,
                    '".mysqli_real_escape_string($connection,"Pendiente")."' 
                    )");

        header('Content-Type: application/json');
        echo '{"id":"'.mysqli_insert_id($connection).'","Obs":"'.$_POST[Observaciones].'"}';
        exit;
    }
    elseif($_POST['action'] == "update")  // update event
    {
        mysqli_query($connection,"UPDATE `Calendario` set 
            `start` = '".mysqli_real_escape_string($connection,date('Y-m-d H:i:s',strtotime($_POST["start"])))."', 
            `end` = '".mysqli_real_escape_string($connection,date('Y-m-d H:i:s',strtotime($_POST["end"])))."'
            where id = '".mysqli_real_escape_string($connection,$_POST["id"])."'");
        exit;
    }
    elseif($_POST['action'] == "delete")  // remove event
    {
        mysqli_query($connection,"DELETE from `Calendario` where id = '".mysqli_real_escape_string($connection,$_POST["id"])."'");
        if (mysqli_affected_rows($connection) > 0) {
            echo "1";
        }
        exit;
    }
   
      
?>