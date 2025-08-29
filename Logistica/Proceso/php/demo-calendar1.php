<?php
$host="localhost";
$user="dinter6_prodrig";
$pass="pato@4986";
$db="dinter6_triangular";
$connection = mysqli_connect($host,$user,$pass,$db) or die(mysqli_error($connection));
date_default_timezone_set('America/Argentina/Cordoba');

    
if(isset($_POST['action']) or isset($_GET['view'])) //show all Calendario
{
    if(isset($_GET['view']))
    {
        header('Content-Type: application/json');
        $start = mysqli_real_escape_string($connection,$_GET["start"]);
        $end = mysqli_real_escape_string($connection,$_GET["end"]);
        
        $result = mysqli_query($connection,"SELECT `id`, `start` ,`end` ,`title` FROM  `Calendario` where (date(start) >= '$start' AND date(start) <= '$end')");
        while($row = mysqli_fetch_assoc($result))
        {
            $Calendario[] = $row; 
        }
        echo json_encode($Calendario); 
        exit;
    }
    elseif($_POST['action'] == "add") // add new event
    {   
        mysqli_query($connection,"INSERT INTO `Calendario` (
                    `title` ,
                    `start` ,
                    `end` 
                    )
                    VALUES (
                    '".mysqli_real_escape_string($connection,$_POST["title"])."',
                    '".mysqli_real_escape_string($connection,date('Y-m-d H:i:s',strtotime($_POST["start"])))."',
                    '".mysqli_real_escape_string($connection,date('Y-m-d H:i:s',strtotime($_POST["end"])))."'
                    )");
        header('Content-Type: application/json');
        echo '{"id":"'.mysqli_insert_id($connection).'"}';
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
}
if($_POST['Renderize']==1){
$sql=mysqli_query("SELECT * FROM Logistica WHERE DAY(Fecha) = DAY(CURRENT_DATE()) AND MONTH(Fecha) = MONTH(CURRENT_DATE()) AND YEAR(Fecha) = YEAR(CURRENT_DATE()) AND Eliminado=0");
$colorbg="bg-danger";
while($row = mysqli_fetch_assoc($sql)){
mysqli_query("INSERT INTO `Calendario`(`start`, `end`, `title`, `className`, `idRelacion`, `Observaciones`, `Estado`, `NumerodeOrden`) VALUES ('{$row[Fecha]}','{$row[Fecha]}','{$row[Recorrido]}','{$colorbg}','{$row[id]}','{$row[Observaciones]}','{$row[Estado]}','{$row[NumerodeOrden]}')");
}

}
?>