<?
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);  
date_default_timezone_set('America/Argentina/Buenos_Aires');
$CodigoSeguimiento=$_POST['codigoseguimiento_t'];

if($_POST['cobro']=='COBRADO'){
//ACTUALIZO TRANSCLIENTES
$sqlT="UPDATE TransClientes SET EnvioCobrado=1 WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0";
mysql_query($sqlT);

  if($_POST[emailrecibo]<>''){  
  $SqlBuscoCliente=mysql_query("SELECT RazonSocial,NumeroComprobante,ClienteDestino FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0");
  $DatoSqlBuscoCliente=mysql_fetch_array($SqlBuscoCliente);
  $NombreCliente=$DatoSqlBuscoCliente[RazonSocial]; 
  $_SESSION['ClienteOrigen']=$DatoSqlBuscoCliente[RazonSocial];  
  $SqlBuscaDestino=mysql_query("SELECT nombrecliente,idProveedor,NdeCliente FROM Clientes WHERE nombrecliente='$DatoSqlBuscoCliente[ClienteDestino]'");
  $SqlResultD=mysql_fetch_array($SqlBuscaDestino);
  // BUSCO EL NUMERO DE CLIENTE DEL EMISOR
  $SqlBuscoClientes=mysql_query("SELECT NdeCliente FROM Clientes WHERE nombrecliente='$DatoSqlBuscoCliente[RazonSocial]'");
  $DatoClientes=mysql_fetch_array($SqlBuscoClientes);


  $MailSeleccionado=$_POST['emailrecibo']; 

  $asunto = "Cobranza Caddy De: $NombreCliente A: $SqlResultD[nombrecliente]($SqlResultD[idProveedor])..."; 
    //Env���en formato HTM
    $headers = "MIME-Version: 1.0\r\n"; 
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
    $headers .= 'From: hola@caddy.com.ar' ."\r\n";
  // 	$headers .= "CC:$MailCliente' .\r\n"; 
    $mensaje ="<html><body>
     <div  style='margin-top:0;margin-left:10%;width:80%;height:100%;background:#4D1A50;color:'>";
    if($_POST['impcliente']<>''){
    $ImporteCliente=$_POST['impcliente']+$_POST['impcaddy'];  
    $Titulo="<th>Recibimos $ $ImporteCliente por cuenta y orden de '.$NombreCliente.'</th>";  
      }
    if($_POST['impcaddy']<>''){
    $ImporteCaddy=$_POST['impcaddy'];  
    $Titulo="<th>Recibimos $ $ImporteCaddy en concepto de pago por los servicios prestados por Caddy Logistica</th>";  
    } 
      $mensaje .="<img style='height: 50%;width: 30%;display:block;position: absolute;transform: translate(-50%, -50%);top:25%;left:50%' 
      src='data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDIxLjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkNhcGFfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiCgkgdmlld0JveD0iMCAwIDI0NC4xIDkxLjMiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDI0NC4xIDkxLjM7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4KPHN0eWxlIHR5cGU9InRleHQvY3NzIj4KCS5zdDB7ZmlsbDojRkZGRkZGO30KPC9zdHlsZT4KPGc+Cgk8Zz4KCQk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNOTEuMiw3Mi4zaC0xdi00LjhoLTEuOXYtMC45SDkzdjAuOWgtMS45VjcyLjN6Ii8+CgkJPHBhdGggY2xhc3M9InN0MCIgZD0iTTk4LjEsNzIuM2wtMS40LTJoLTEuM3YyaC0xdi01LjhoMi42YzEuNCwwLDIuMiwwLjcsMi4yLDEuOGMwLDEtMC42LDEuNS0xLjQsMS44bDEuNiwyLjJIOTguMXogTTk2LjgsNjcuNQoJCQloLTEuNXYxLjloMS41YzAuNywwLDEuMi0wLjQsMS4yLTFDOTguMSw2Ny44LDk3LjYsNjcuNSw5Ni44LDY3LjV6Ii8+CgkJPHBhdGggY2xhc3M9InN0MCIgZD0iTTEwNi4yLDcyLjNoLTEuMWwtMC42LTEuNGgtMi44bC0wLjYsMS40aC0xLjFsMi42LTUuOGgxTDEwNi4yLDcyLjN6IE0xMDMuMSw2Ny43bC0xLDIuM2gyTDEwMy4xLDY3Ljd6Ii8+CgkJPHBhdGggY2xhc3M9InN0MCIgZD0iTTExMS41LDY2LjZoMXY1LjhoLTAuOWwtMy4yLTQuMXY0LjFoLTF2LTUuOGgxbDMuMiw0VjY2LjZ6Ii8+CgkJPHBhdGggY2xhc3M9InN0MCIgZD0iTTExNi41LDY5YzEuMiwwLjMsMS45LDAuNywxLjksMS43YzAsMS4xLTAuOSwxLjctMi4xLDEuN2MtMC45LDAtMS44LTAuMy0yLjUtMC45bDAuNi0wLjcKCQkJYzAuNiwwLjUsMS4xLDAuNywxLjksMC43YzAuNiwwLDEtMC4zLDEtMC43YzAtMC40LTAuMi0wLjYtMS4zLTAuOWMtMS4zLTAuMy0yLTAuNy0yLTEuN2MwLTEsMC44LTEuNywyLTEuN2MwLjksMCwxLjUsMC4zLDIuMSwwLjcKCQkJbC0wLjYsMC44Yy0wLjUtMC40LTEuMS0wLjYtMS42LTAuNmMtMC42LDAtMSwwLjMtMSwwLjdDMTE1LjEsNjguNSwxMTUuMyw2OC43LDExNi41LDY5eiIvPgoJCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xMjEuOSw3MC41aC0xLjJ2MS45aC0xdi01LjhoMi4zYzEuNCwwLDIuMiwwLjgsMi4yLDEuOUMxMjQuMyw2OS44LDEyMy4yLDcwLjUsMTIxLjksNzAuNXogTTEyMiw2Ny41aC0xLjIKCQkJdjIuMWgxLjJjMC44LDAsMS4zLTAuNCwxLjMtMUMxMjMuMyw2Ny44LDEyMi44LDY3LjUsMTIyLDY3LjV6Ii8+CgkJPHBhdGggY2xhc3M9InN0MCIgZD0iTTEyOC4zLDcyLjRjLTEuOCwwLTMuMS0xLjMtMy4xLTNjMC0xLjYsMS4zLTMsMy4xLTNjMS44LDAsMy4xLDEuMywzLjEsM0MxMzEuNCw3MS4xLDEzMC4yLDcyLjQsMTI4LjMsNzIuNHoKCQkJIE0xMjguMyw2Ny40Yy0xLjIsMC0yLDAuOS0yLDJjMCwxLjEsMC44LDIuMSwyLDIuMWMxLjIsMCwyLTAuOSwyLTJDMTMwLjMsNjguMywxMjkuNSw2Ny40LDEyOC4zLDY3LjR6Ii8+CgkJPHBhdGggY2xhc3M9InN0MCIgZD0iTTEzNi42LDcyLjNsLTEuNC0yaC0xLjN2MmgtMXYtNS44aDIuNmMxLjQsMCwyLjIsMC43LDIuMiwxLjhjMCwxLTAuNiwxLjUtMS40LDEuOGwxLjYsMi4ySDEzNi42egoJCQkgTTEzNS40LDY3LjVoLTEuNXYxLjloMS41YzAuNywwLDEuMi0wLjQsMS4yLTFDMTM2LjYsNjcuOCwxMzYuMiw2Ny41LDEzNS40LDY3LjV6Ii8+CgkJPHBhdGggY2xhc3M9InN0MCIgZD0iTTE0MS42LDcyLjNoLTF2LTQuOGgtMS45di0wLjloNC44djAuOWgtMS45VjcyLjN6Ii8+CgkJPHBhdGggY2xhc3M9InN0MCIgZD0iTTE0OS4xLDY3LjVoLTMuM1Y2OWgzdjAuOWgtM3YxLjVoMy40djAuOWgtNC40di01LjhoNC40VjY3LjV6Ii8+CgkJPHBhdGggY2xhc3M9InN0MCIgZD0iTTE1Niw3Mi4zaC0xdi0yLjNsLTIuMy0zLjVoMS4ybDEuNiwyLjVsMS43LTIuNWgxLjJMMTU2LDcwVjcyLjN6Ii8+CgkJPHBhdGggY2xhc3M9InN0MCIgZD0iTTE2Mi4xLDY2LjZoMXY0LjhoMy4xdjAuOWgtNC4xVjY2LjZ6Ii8+CgkJPHBhdGggY2xhc3M9InN0MCIgZD0iTTE2OS45LDcyLjRjLTEuOCwwLTMuMS0xLjMtMy4xLTNjMC0xLjYsMS4zLTMsMy4xLTNjMS44LDAsMy4xLDEuMywzLjEsM0MxNzMsNzEuMSwxNzEuNyw3Mi40LDE2OS45LDcyLjR6CgkJCSBNMTY5LjksNjcuNGMtMS4yLDAtMiwwLjktMiwyYzAsMS4xLDAuOCwyLjEsMiwyLjFjMS4yLDAsMi0wLjksMi0yQzE3MS45LDY4LjMsMTcxLjEsNjcuNCwxNjkuOSw2Ny40eiIvPgoJCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xNzcuMiw3Mi40Yy0xLjgsMC0zLjEtMS4zLTMuMS0zYzAtMS42LDEuMy0zLDMtM2MxLDAsMS43LDAuMywyLjMsMC44bC0wLjcsMC44Yy0wLjUtMC40LTAuOS0wLjYtMS42LTAuNgoJCQljLTEuMSwwLTEuOSwwLjktMS45LDJjMCwxLjIsMC44LDIuMSwyLDIuMWMwLjYsMCwxLjEtMC4yLDEuNC0wLjRWNzBoLTEuNXYtMC45aDIuNXYyLjRDMTc5LjEsNzIsMTc4LjMsNzIuNCwxNzcuMiw3Mi40eiIvPgoJCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xODIuMyw2Ni42djUuOGgtMXYtNS44SDE4Mi4zeiBNMTgxLjQsNjYuMWwwLjktMS4zbDAuOSwwLjRsLTEsMC45SDE4MS40eiIvPgoJCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xODYuMyw2OWMxLjIsMC4zLDEuOSwwLjcsMS45LDEuN2MwLDEuMS0wLjksMS43LTIuMSwxLjdjLTAuOSwwLTEuOC0wLjMtMi41LTAuOWwwLjYtMC43CgkJCWMwLjYsMC41LDEuMSwwLjcsMS45LDAuN2MwLjYsMCwxLTAuMywxLTAuN2MwLTAuNC0wLjItMC42LTEuMy0wLjljLTEuMy0wLjMtMi0wLjctMi0xLjdjMC0xLDAuOC0xLjcsMi0xLjdjMC45LDAsMS41LDAuMywyLjEsMC43CgkJCWwtMC42LDAuOGMtMC41LTAuNC0xLjEtMC42LTEuNi0wLjZjLTAuNiwwLTEsMC4zLTEsMC43QzE4NC45LDY4LjUsMTg1LjIsNjguNywxODYuMyw2OXoiLz4KCQk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMTkyLDcyLjNoLTF2LTQuOGgtMS45di0wLjloNC44djAuOUgxOTJWNzIuM3oiLz4KCQk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMTk2LjIsNjYuNnY1LjhoLTF2LTUuOEgxOTYuMnoiLz4KCQk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMjAwLjcsNzIuNGMtMS43LDAtMy0xLjMtMy0zYzAtMS42LDEuMy0zLDMtM2MxLjEsMCwxLjcsMC40LDIuMywwLjlsLTAuNywwLjdjLTAuNS0wLjQtMS0wLjctMS42LTAuNwoJCQljLTEuMSwwLTEuOSwwLjktMS45LDJjMCwxLjEsMC44LDIuMSwxLjksMi4xYzAuNywwLDEuMi0wLjMsMS43LTAuN2wwLjcsMC43QzIwMi40LDcyLDIwMS44LDcyLjQsMjAwLjcsNzIuNHoiLz4KCQk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMjEwLDcyLjNoLTEuMWwtMC42LTEuNGgtMi44bC0wLjYsMS40aC0xLjFsMi42LTUuOGgxTDIxMCw3Mi4zeiBNMjA2LjksNjcuN2wtMSwyLjNoMkwyMDYuOSw2Ny43eiIvPgoJPC9nPgoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTg5LjMsNDUuNGMwLDQuNiwzLjcsOC4zLDguMyw4LjNjMi42LDAsNS4xLTEuMiw2LjctMy4zYzEuMi0xLjYsMy40LTEuOSw1LTAuN2MxLjYsMS4yLDEuOSwzLjUsMC43LDUKCQljLTIuOSwzLjktNy41LDYuMi0xMi40LDYuMmMtOC41LDAtMTUuNS03LTE1LjUtMTUuNWMwLTguNSw3LTE1LjUsMTUuNS0xNS41YzQuOSwwLDkuNSwyLjQsMTIuNSw2LjNjMS4yLDEuNiwwLjgsMy44LTAuOCw1CgkJYy0xLjYsMS4yLTMuOCwwLjgtNS0wLjhjLTEuNi0yLjEtNC0zLjQtNi43LTMuNEM5My4xLDM3LjEsODkuMyw0MC44LDg5LjMsNDUuNHoiLz4KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xNDMuNSw1Ny4zYzAsMi0xLjYsMy42LTMuNiwzLjZjLTEuNiwwLTMtMS4xLTMuNC0yLjVjLTIuNSwxLjYtNS40LDIuNS04LjUsMi41Yy04LjUsMC0xNS41LTctMTUuNS0xNS41CgkJYzAtOC41LDctMTUuNSwxNS41LTE1LjVjMy4xLDAsNiwwLjksOC41LDIuNWMwLjQtMS40LDEuOC0yLjUsMy40LTIuNWMyLDAsMy42LDEuNiwzLjYsMy42VjU3LjN6IE0xMzYuMyw0NS4xCgkJYy0wLjItNC40LTMuOS03LjktOC4zLTcuOWMtNC42LDAtOC4zLDMuNy04LjMsOC4zczMuNyw4LjMsOC4zLDguM2M0LjQsMCw4LjEtMy41LDguMy03LjlWNDUuMXoiLz4KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xNzcuMiw1Ny40YzAsMi0xLjYsMy42LTMuNiwzLjZjLTIuMS0wLjEtMy4xLTEuMi0zLjQtMi41Yy0yLjUsMS42LTUuNCwyLjUtOC41LDIuNWMtOC41LDAtMTUuNS03LTE1LjUtMTUuNQoJCWMwLTguNSw3LTE1LjUsMTUuNS0xNS41YzMuMSwwLDUuOSwwLjksOC4zLDIuNVYyMC45YzAtMiwxLjYtMy42LDMuNi0zLjZjMiwwLDMuNiwxLjYsMy42LDMuNlY1Ny40eiBNMTcwLDQ1LjUKCQljMC00LjctMy45LTguMy04LjMtOC4zYy00LjYsMC04LjMsMy43LTguMyw4LjNzMy43LDguMyw4LjMsOC4zQzE2Ni4zLDUzLjgsMTcwLDUwLjEsMTcwLDQ1LjV6Ii8+Cgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMjExLDU3LjRjMCwyLTEuNiwzLjYtMy42LDMuNmMtMi4xLTAuMS0zLjEtMS4yLTMuNC0yLjVjLTIuNSwxLjYtNS40LDIuNS04LjUsMi41Yy04LjUsMC0xNS41LTctMTUuNS0xNS41CgkJYzAtOC41LDctMTUuNSwxNS41LTE1LjVjMy4xLDAsNS45LDAuOSw4LjMsMi41VjIwLjljMC0yLDEuNi0zLjYsMy42LTMuNmMyLDAsMy42LDEuNiwzLjYsMy42VjU3LjR6IE0yMDMuOCw0NS41CgkJYzAtNC43LTMuOS04LjMtOC4zLTguM2MtNC42LDAtOC4zLDMuNy04LjMsOC4zczMuNyw4LjMsOC4zLDguM0MyMDAuMSw1My44LDIwMy44LDUwLjEsMjAzLjgsNDUuNXoiLz4KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0yMzcuNCwzMmMwLjYtMS4xLDEuOC0xLjksMy4xLTEuOWMyLDAsMy42LDEuNiwzLjYsMy42YzAsMC4zLTAuMSwwLjktMC42LDJsLTE3LjYsMzQuNWMtMC42LDEuMy0xLjksMi0zLjIsMgoJCWMtMC41LDAtMS4xLTAuMS0xLjYtMC40Yy0xLjgtMC45LTIuNS0zLjEtMS41LTQuOGw1LTkuOWwtMTEtMjEuOGMtMC45LTEuOC0wLjItMy45LDEuNi00LjhjMS44LTAuOSwzLjktMC4yLDQuOCwxLjZsOC43LDE3LjEKCQlMMjM3LjQsMzJ6Ii8+Cgk8Zz4KCQk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNNjcuMywyNS4xbC03LjUtNC41TDU3LjMsMTljMi0yLDMuMy00LjgsMy4zLTcuOUM2MC42LDUsNTUuNiwwLDQ5LjQsMEM0NCwwLDM5LjUsMy44LDM4LjUsOC45CgkJCWMtMS4zLTAuMi0yLjctMC4yLTQsMEMzMy40LDMuOCwyOC45LDAsMjMuNiwwYy02LjIsMC0xMS4yLDUtMTEuMiwxMS4yYzAsMywxLjIsNS44LDMuMiw3LjhsLTQuOSwzbC01LjEsMy4xCgkJCUMyLjEsMjcuMiwwLDMwLjYsMCwzNC40djMxLjNjMCwzLjcsMi4xLDcuMiw1LjYsOS4zbDIzLjcsMTQuM2MyLjIsMS4zLDQuNywyLDcuMiwyYzIuNSwwLDUtMC43LDcuMi0yTDY3LjMsNzUKCQkJYzMuNS0yLjEsNS42LTUuNiw1LjYtOS4zVjM0LjRDNzIuOSwzMC42LDcwLjgsMjcuMiw2Ny4zLDI1LjF6IE00OS40LDVjMy40LDAsNi4yLDIuOCw2LjIsNi4yYzAsMi4yLTEuMSw0LjEtMi44LDUuMmwtMy4yLTEuOQoJCQlsLTYuMS0zLjZjLTAuMS0wLjEtMC4yLTAuMS0wLjMtMC4yQzQzLjYsNy40LDQ2LjIsNSw0OS40LDV6IE0yMy42LDVjMy4yLDAsNS44LDIuNCw2LjIsNS41Yy0wLjEsMC4xLTAuMywwLjItMC40LDAuMmwtOS4yLDUuNQoJCQljLTEuNi0xLjEtMi43LTMtMi43LTUuMUMxNy4zLDcuNywyMC4xLDUsMjMuNiw1eiBNNjcuNiw2NS43YzAsMi4yLTEuMiw0LjItMy4zLDUuNUw0MC42LDg1LjRjLTIuNSwxLjUtNS44LDEuNS04LjQsMEw4LjUsNzEuMQoJCQljLTItMS4yLTMuMy0zLjMtMy4zLTUuNVYzNC40YzAtMi4yLDEuMi00LjIsMy4zLTUuNWwyMy43LTE0LjNjMS4zLTAuOCwyLjctMS4yLDQuMi0xLjJjMS41LDAsMi45LDAuNCw0LjIsMS4ybDIzLjcsMTQuMwoJCQljMiwxLjIsMy4zLDMuMywzLjMsNS41VjY1Ljd6Ii8+CgkJPHBhdGggY2xhc3M9InN0MCIgZD0iTTU0LjYsMzUuNkw0NS4yLDQxYy02LjctNS4yLTEzLjEtMi44LTE2LjUtMC4xbC04LjUtNWMtMS4zLTAuOC0zLTAuNC0zLjgsMC45Yy0wLjgsMS4zLTAuNCwzLDAuOSwzLjgKCQkJbDE1LjksOS41YzAuNiwwLjQsMSwxLDEsMS43djkuM2gtNC4xYy0xLjUsMC0yLjcsMS4yLTIuNywyLjdjMCwxLjUsMS4yLDIuNywyLjcsMi43aDAuN3YxLjdjMCwzLjQsMi44LDYuMiw2LjIsNi4yCgkJCWMzLjQsMCw2LjItMi44LDYuMi02LjJ2LTEuN2gxLjFjMS41LDAsMi43LTEuMiwyLjctMi43YzAtMS41LTEuMi0yLjctMi43LTIuN2gtNC41di05LjNjMC0wLjcsMC40LTEuMywxLTEuN2wxNi43LTkuOAoJCQljMS4zLTAuOCwxLjctMi41LDEtMy44QzU3LjYsMzUuMiw1NS45LDM0LjgsNTQuNiwzNS42eiBNMzksNjguMmMwLDEuMS0wLjksMS45LTEuOSwxLjljLTEuMSwwLTEuOS0wLjktMS45LTEuOXYtMS43SDM5VjY4LjJ6CgkJCSBNMzcsNDUuOGwtMy40LTIuMmMxLjctMC44LDQuMS0xLjIsNi44LDAuMUwzNyw0NS44eiIvPgoJPC9nPgo8L2c+Cjwvc3ZnPgo='
      width='0' height='0'></div>
      <div style='width: 50%;
      height: auto;
      display: block;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      background:#E24F30;width:60%;height:28%;border-radius: 53px 0px 53px 0px;
      -moz-border-radius: 53px 0px 53px 0px;
      -webkit-border-radius: 53px 0px 53px 0px;
      border: 0px solid #000000;'>
      <h1 style='color:#fff;font-family:'Gotham-Bold';
      font-size: 1.50vw;
      color: rgba(255, 255, 255, 1);
      margin-top:10%'>$NombreCliente !</br>
      Recibimos tu Pago de $ $ImporteCliente !</br>
      Muchas gracias!</br></h1>
      <h1 style='color:white;font-family: 'Gotham-Bold';
      font-size: 1.50vw;
      color: rgba(255, 255, 255, 1);'>El equipo de Caddy.</h1>
      </div>";
  //   $mensaje .="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
  // 	<tr align='center' style='background:#4D1A50; color:white; font-size:8px;'>
  // 	<td colspan='6' style='font-size:22px'>Recibo de pago $ImporteCliente</td></tr>
  // 	<tr align='center' style='background:#4D1A50; color:white; font-size:12px;'>
  //   $Titulo
  //   <td align='right' colspan='6' style='font-size:16px'><strong>Muchas gracias!</strong></td></tr></table>";
  $mensaje .="</b></body></html>";
  //SI LA COBRANZA ES DEL CLIENTE LE ENVIO UN MAIL AL CLIENTE
    if($_POST[impcliente]<>0){  
//     mail($MailSeleccionado,$asunto,$mensaje,$headers);
    }
  //SI LA COBRANZA ES DE CADDY NO ENVIO NADA
  }
echo json_encode(array('resultado' => 1));  
}else{
echo json_encode(array('resultado' => 0));  
}
  
