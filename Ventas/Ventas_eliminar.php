<?php
ob_start();
// session_start();
include_once "../ConexionBD.php";
$user = $_POST['user'];
$password = $_POST['password'];
$_SESSION['SituacionFiscal'] = 'Responsable Inscripto';

if (isset($_GET['idCliente'])) {
  if (($_GET['idClienteRelacion'] <> '') || ($_GET['idProveedor'] <> '')) {
    $idCA = $_GET['idProveedor'];
    $stmt = $mysqli->prepare("SELECT id, nombrecliente, Cuit FROM Clientes WHERE idProveedor = ? AND Relacion = ?");
    $stmt->bind_param("ss", $idCA, $_GET['idClienteRelacion']);
    $stmt->execute();
    $result = $stmt->get_result();
    $DatoCA = $result->fetch_assoc();
    $idCA = $DatoCA['id'];
    $_SESSION['ClienteActivo'] = $DatoCA['nombrecliente'];
    $_SESSION['idClienteActivo'] = $DatoCA['id'];
    $_SESSION['CuitActivo'] = $DatoCA['Cuit'];
  } elseif ($_GET['direccion_t'] <> '') {
    $idCA = $_GET['direccion_t'];
    $stmt = $mysqli->prepare("SELECT id, nombrecliente, Cuit FROM Clientes WHERE id = ?");
    $stmt->bind_param("s", $idCA);
    $stmt->execute();
    $result = $stmt->get_result();
    $DatoCA = $result->fetch_assoc();
    $_SESSION['ClienteActivo'] = $DatoCA['nombrecliente'];
    $_SESSION['idClienteActivo'] = $DatoCA['id'];
    $_SESSION['CuitActivo'] = $DatoCA['Cuit'];
  }
} else {
  $idCA = $_GET['idCliente'];
  $stmt = $mysqli->prepare("SELECT id, nombrecliente, Cuit FROM Clientes WHERE id = ?");
  $stmt->bind_param("s", $idCA);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($DatoCA = $result->fetch_assoc()) {
    $_SESSION['ClienteActivo'] = $DatoCA['nombrecliente'];
    $_SESSION['idClienteActivo'] = $DatoCA['id'];
    $_SESSION['CuitActivo'] = $DatoCA['Cuit'];
  } else {
    header('location:../Clientes/Clientes.php');
  }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//ES" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es">

<head>
  <!-- <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> -->
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>.::TRIANGULAR S.A.::.</title>
  <link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
  <link href="../css/popup.css" rel="stylesheet" type="text/css" />
  <script type="text/javascript" src="../scripts/jquery.js"></script>
  <script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
  <script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>




<style>
  .boton {
    background: none repeat scroll 0 0 #E24F30;
    border: 1px solid #C6C6C6;
    float: right;
    font-weight: bold;
    padding: 8px 26px;
    color: #FFFFFF;
    font-size: 12px;

  }
</style>
<script>
  function imprimir() {
    var desde = document.getElementById('desde_t').value;
    var hasta = document.getElementById('hasta_t').value;
    window.open('/SistemaTriangular/Ventas/Informes/Remitos_afpdf.php?Desde=' + desde + '&Hasta=' + hasta, '_blank');
  }
</script>
<script>
  function mostrar() {
    var n1 = parseFloat(document.cobranza.formadepago_t.value);
    // document.formulario1.importepago_t.value=n1;

    if (n1 == '111200') { //111200 
      document.getElementById('oculto').style.display = 'block';
      document.getElementById('oculto1').style.display = 'block';
      document.getElementById('BancoOculto').style.display = 'block';
    } else {
      document.getElementById('oculto').style.display = 'none';
      document.getElementById('oculto1').style.display = 'none';
      document.getElementById('BancoOculto').style.display = 'none';
    }
    if (n1 == '112400') { //111200 
      document.getElementById('NumeroChequeOculto').style.display = 'block';
      document.getElementById('FechaChequeOculto').style.display = 'block';
      document.getElementById('BancoOculto').style.display = 'block';
    } else {
      document.getElementById('NumeroChequeOculto').style.display = 'none';
      document.getElementById('FechaChequeOculto').style.display = 'none';
      document.getElementById('BancoOculto').style.display = 'none';
    }

  }
</script>

<script>
  function sumar() {
    var n1 = parseFloat(document.MyForm.importeneto_t.value);
    var n2 = parseFloat(document.MyForm.iva1_t.value);
    var n3 = parseFloat(document.MyForm.iva2_t.value);
    var n4 = parseFloat(document.MyForm.iva3_t.value);
    var n5 = parseFloat(document.MyForm.exento_t.value);
    document.MyForm.total_t.value = n1 + n2 + n3 + n4 + n5;

  }
</script>
<script>
  function sumar2() {
    var n5 = parseFloat(document.MyForm2.total_t.value);
    var n6 = parseFloat(document.MyForm2.valordeclarado_t.value);
    var n7 = parseFloat(document.MyForm2.seguro_t.value);
    var n8 = parseFloat(document.MyForm2.importeseguro_t.value);
    document.MyForm2.importeseguro_t.value = n6 * n7;
    document.MyForm2.importepago_t.value = n8 + n5;

  }
</script>
<script>
  function buscar() {
    var n1 = parseFloat(document.MyForm.servicio_t.value);
    var n2 = parseFloat(document.MyForm.cantidad_t.value);
    var n3 = document.MyForm.situacionfiscal_t.value;
    var n4 = ((n1 * n2) - ((n1 * n2) / 1.21));

    if (document.MyForm.situacionfiscal_t.value == 'Responsable Inscripto') {
      // 		document.MyForm.importeneto_t.value=Math.floor(n4);	
      document.MyForm.iva3_t.value = parseFloat(Math.round(n4 * 100) / 100).toFixed(2);
      document.MyForm.importeneto_t.value = parseFloat(Math.round(((n1 * n2) - n4) * 100) / 100).toFixed(2);
    } else {
      document.MyForm.importeneto_t.value = n1 * n2;
      document.MyForm.iva3_t.value = 0;
    }
  }
</script>


<?php
$_SESSION['fp'] = $_GET['formadepago_t'];
echo "<div id='contenedor'>";

if ($_GET['Imprimir'] == 'Si') {
  echo "<div id='cabecera'>";
  include("../Menu/MenuGestion.php");
  echo "</div>"; //cabecera    
} else {
  echo "<div id='cabecera'>";
  include("../Alertas/alertas.html");
  include("../Menu/MenuGestion.php");
  echo "</div>"; //cabecera    
  if ($_GET['UltimoPaso'] == 'Cobro') {
    echo "<div id='cuerpo'>";
  } else {
    echo "<div id='cuerpo'>";
    echo "<div id='lateral'>";
    include("Menu/MenuLateralVentas.php");
    echo "</div>"; //lateral
  }
}
echo  "<div id='principal'>";

setlocale(LC_ALL, 'es_AR');
$color = '#B8C6DE';
$font = 'white';
$color2 = 'white';

//-----------------------------------DESDE ACA LISTADO QUE MUESTRA LOS RECORRIDOS--------------
if ($_GET['Ventas'] == 'Recorridos') {
  setlocale(LC_ALL, 'es_AR');
  $cliente = $_GET['Cliente'];
  // $sql1=mysql_query("SELECT NdeCliente FROM Clientes WHERE nombrecliente = '$cliente'");
  //    $NdeCLiente=mysql_fetch_array($sql1);
  //    $dato=$NdeCLiente[NdeCLiente];
  //    print $dato;
  $dato = $_SESSION['idClienteActivo'];
  //---------------------------------REMITOS DE ENVIO-------------------------------------
  $ordenar = mysql_query("SELECT * FROM `Logistica` WHERE `Cliente` = '$dato' AND Facturado='0' AND Eliminado='0' ORDER BY Fecha,Recorrido ");

  $Extender = '6';
  // echo "dato:".$dato;
  $TotalRemitos = mysql_num_rows($ordenar);

  echo "<table class='login'>";
  echo "<caption>Listado de Recorridos Realizados $cliente</caption>";
  echo "<th>Fecha</th>";
  echo "<th>Numero de Orden</th>";
  echo "<th>Patente</td>";
  echo "<th>Recorrido</th>";
  echo "<th>Kilometros</th>";
  echo "<th>Seleccionar</th>";

  while ($row = mysql_fetch_array($ordenar)) {
    $sqlRecorridos = mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$row[Recorrido]'");
    $datosqlRecorridos = mysql_fetch_array($sqlRecorridos);
    if ($numfilas % 2 == 0) {
      echo "<tr style='background: #f2f2f2;' >";
    } else {
      echo "<tr style='background:$color2;' >";
    }

    $fecha = $row[Fecha];
    $arrayfecha = explode('-', $fecha, 3);
    echo "<td>" . $arrayfecha[2] . "/" . $arrayfecha[1] . "/" . $arrayfecha[0] . "</td>";
    echo "<td>$row[NumerodeOrden]</td>";
    echo "<td>$row[Patente]</td>";
    echo "<td>($row[Recorrido]) $datosqlRecorridos[Nombre]</td>";
    echo "<td>$row[KilometrosRecorridos]</td>";

    echo "<form action='Facturar.php' method='GET' >";
    echo "<td align='center'><input type='checkbox' name='box[]' value='$row[NumerodeOrden]'></td>";
    $numfilas++;
  }
  echo "<tfoot>";
  echo "<tr>";
  echo "<th colspan='11'>Total de Remitos: $TotalRemitos</th>";
  echo "</tr>";
  echo "</tfoot>";
  echo "</table>";
  echo "<div><input type='submit' class='boton' name='Aceptar' value='Aceptar'></div>";
  echo "</form>";
  echo "</div>"; // principal
  goto a;
}

//-----------------------------------HASTA ACA LISTADO QUE MUESTRA LOS RECORRIDOS--------------	
if ($_GET['Ventas'] == 'MostrarxNumeroEnvio') {
  if ($_GET[NumeroRemito] == '') {
    echo "<form class='login' action='Ventas.php?Ventas=MostrarxNumeroEnvio' method='GET'  style='float:center; width:500px;'>";
    echo "<div><titulo>Buscar x Numero de Remito</titulo></div>";
    echo "<div><hr></hr></div>";
    echo "<div><label>Numero de Remito:</label><input name='NumeroRemito' type='text'/></div>";
    echo "<div><input name='Continuar' class='bottom' type='submit' value='Buscar' ></label></div>";
    echo "</form>";
    goto a;
  }
}
if ($_GET[Continuar] == 'Buscar') {

  setlocale(LC_ALL, 'es_AR');

  $NumeroRemito = $_GET['NumeroRemito'];
  //---------------------------------REMITOS DE ENVIO-------------------------------------
  $SumarTotal = mysql_query("SELECT SUM(Debe)as Total FROM TransClientes WHERE  Eliminado='0' AND NumeroComprobante='$NumeroRemito'");
  $TotalSuma = mysql_fetch_array($SumarTotal);
  $TotalSumaRemitos = money_format('%i', $TotalSuma[Total]);

  $ordenar = "SELECT * FROM TransClientes WHERE  Eliminado='0' AND NumeroComprobante='$NumeroRemito' ORDER BY Fecha DESC";
  $MuestraRemitos = mysql_query($ordenar);
  $Extender = '15';
  // $row=mysql_fetch_array($MuestraRemitos);
  $TotalRemitos = mysql_num_rows($MuestraRemitos);

  echo "<table class='header'>";
  echo "<caption>Listado de Remitos de Envio $cliente</caption>";
  echo "</table>";
  echo "<table class='login'>";
  echo "<th style='width:6%'>Fecha</th>";
  echo "<th style='width:6%'>Numero</th>";
  echo "<th style='width:10%'>Destino</th>";
  echo "<th style='width:8%'>Servicio</th>";
  // echo "<td style='width:8px'>Observaciones</td>";
  echo "<th style='width:8%'>Entregado</th>";
  echo "<th style='width:8%'>Cod.Seg.</th>";
  // echo "<td style='width:8%'>Recorrido</td>";
  echo "<th style='width:8%'>Bultos</th>";
  echo "<th style='width:8%'>Total</th>";
  echo "<th style='width:8%'>Remito</th>";
  echo "<th style='width:8%'>Rotulo</th>";
  echo "<th style='width:8%'>Eliminar</th>";

  while ($row = mysql_fetch_row($MuestraRemitos)) {
    $u = $row[0];
    $n = $row[1];
    $NumRepo = $row[5];
    $Servicio = $row[4];
    $Observaciones = $row[30];
    $Numer = $row[16];
    $ImpNeto = $row[16];
    $Origen = $row[2];
    $Destino = $row[9];
    $Bultos = $row[23];
    $CodSeg = $row[16];
    if ($row[25] == 1) {
      $Entregado = 'Si';
      $font2 = 'black';
    } else {
      $Entregado = 'No';
      $font2 = 'red';
    }

    echo "<tr style='color:$font2;background:$color2;'>";
    $fecha = $row[1];
    $arrayfecha = explode('-', $fecha, 3);
    echo "<td style='width:6px'>" . $arrayfecha[2] . "/" . $arrayfecha[1] . "/" . $arrayfecha[0] . "</td>";
    echo "<td style='width:6px'>$NumRepo</td>";
    $TotalStock = money_format('%i', $row[7]);
    $Total = $row[7];
    echo "<td style='width:8%'>$Destino</td>";
    echo "<td style='width:8%'>$Servicio</td>";
    // 	echo "<td style='width:8px'>$Observaciones</td>";
    echo "<td style='width:8%'>$Entregado</td>";
    echo "<td style='width:8%'>$Numer</td>";
    // 	echo "<td style='width:8%'></td>";
    echo "<td style='width:8%'>$Bultos</td>";
    echo "<td style='width:8%'>$TotalStock</td>";
    echo "<input type='hidden' name='NumRepo' value='$NumRepo'>";
    echo "<input type='hidden' name='id' value='$u'>";
    echo "<td style='width:8%' align='center'><a target='_blank' href='Informes/Remitopdf.php?NR=$NumRepo'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
    echo "<td style='width:8%' align='center'><a target='_blank' href='Inforems/Rotulospdf.php?NR=$NumRepo'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
    echo "<td style='width:8%' align='center'><a href='Ventas.php?Ventas=Eliminar&CS=$CodSeg'><input type='image' src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:center;'></td>";
    echo "</form>";
  }
  echo "</tr>";
  echo "</table>";
  echo "</div>"; // principal
  echo "</div>"; //cuerpo
  echo "<div id='pie'>";
  echo "<table class='login'>";
  echo "<th>Total de Remitos: $TotalRemitos Total: $TotalSumaRemitos </th>";
  echo "</table>";
  echo "</div>"; //pie
  goto a;
}
//-----------------------------------DESDE ACA LISTADO QUE MUESTRA LAS VENTAS--------------	
if ($_GET['Ventas'] == 'MostrarEnvio') {
  setlocale(LC_ALL, 'es_AR');
  $cliente = $_GET['Cliente'];

  //---------------------------------REMITOS DE ENVIO-------------------------------------
  if ($_GET[Remitos] == 'Pendientes') {
    $Desde = $_POST[desde_t];
    $Hasta = $_POST[hasta_t];
    $Empresa = $_SESSION['ClienteActivo'];

    if ($Desde == '') {
      echo "<form class='Caddy' action='Ventas.php?Ventas=MostrarEnvio&Cliente=$Empresa&Remitos=Pendientes' method='post' style='width:500px'>";
      echo "<div><titulo>Ver Cuenta Corriente de $cliente</titulos></div>";
      echo "<div><hr></hr></div>";
      echo "<div><label>Desde</label><input name='desde_t' size='16' type='date' value='' style='float:right' required/></div>";
      echo "<div><label>Hasta</label><input name='hasta_t' size='16' type='date' value='' style='float:right' required/></div>";
      echo "<div><input class='bottom' type='submit' style='width:110px' value='Aceptar' ></div>";
      echo "</form>";
      goto a;
    }
    $Facturar = 'Si';
    $Titulo = 'Listado de Remitos de Envio a Facturar ';
    $ordenar = "SELECT * FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe>'0' AND Fecha>='$Desde' AND Fecha<='$Hasta' AND Facturado='0'";
  } else {
    $Facturar = 'No';
    $Titulo = 'Listado de Remitos de Envio ';
    $ordenar = "SELECT * FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' ORDER BY Fecha,NumeroComprobante DESC";
  }
  $MuestraRemitos = mysql_query($ordenar);
  $Extender = '15';
  // $row=mysql_fetch_array($MuestraRemitos);
  $TotalRemitos = mysql_num_rows($MuestraRemitos);

  echo "<table class='header'>";
  echo "<caption>$Titulo $cliente</caption>";
  echo "</table>";
  echo "<table class='login'>";
  echo "<th style='width:6%'>Fecha</th>";
  echo "<th style='width:6%'>Numero</th>";
  echo "<th style='width:10%'>Origen</th>";
  echo "<th style='width:10%'>Destino</th>";
  echo "<th style='width:10%'>Forma de Pago</th>";
  echo "<th style='width:8%'>Servicio</th>";
  echo "<th style='width:8%'>Entregado</th>";
  echo "<th style='width:8%'>Cod.Seg.</th>";
  echo "<th style='width:8%'>Bultos</th>";
  echo "<th style='width:8%'>Total</th>";
  // echo "<th style='width:8%'>Remito</th>";
  if ($Facturar == 'No') {
    // echo "<th style='width:8%'>Rotulo</th>";
  } elseif ($Facturar == 'Si') {
    echo "<th style='width:8%' onclick='todos()'>Facturar</th>";
  }
  $CuentoRemitos = 0;

  while ($row1 = mysql_fetch_array($MuestraRemitos)) {
    if ($row1[FormaDePago] == 'Origen') {
      $fp = 'RazonSocial';
      $Dato = 'Origen';
    } else {
      $fp = 'ClienteDestino';
      $Dato = 'Destino';
    }
    if ($row[id] <> '') {
      $coma = ',';
    } else {
      $coma = '';
    }
    $id = $row1[id] . $coma;
    // $SumarRemitos=0;
    $BuscarCliente = mysql_query("SELECT * FROM TransClientes WHERE $fp='$cliente' AND id IN($id) ORDER BY Fecha DESC");
    //   $BuscarCliente=mysql_query("SELECT * FROM TransClientes WHERE RazonSocial='$cliente' AND id IN($id) ORDER BY Fecha DESC");
    while ($row = mysql_fetch_row($BuscarCliente)) {
      $SumarRemitos = $SumarRemitos + $row[7];
      $Destino = $row[9];
      $TotalStock = money_format('%i', $row[7]);
      //   if($Dato=='Origen'){  
      //   $TotalStock= money_format('%i',$row[7]);
      //   }else{
      //   $TotalStock= money_format('%i',0);  
      //   }
      $Total = $row[7];

      $u = $row[0];
      $n = $row[1];
      $NumRepo = $row[5];
      $Servicio = $row[4];
      $Observaciones = $row[30];
      $Numer = $row[16];
      $Origen = $row[2];
      $Bultos = $row[23];
      $CodSeg = $row[16];
      $FormaDePago = $row[26];
      if ($row[25] == 1) {
        $Entregado = 'Si';
        $font2 = 'black';
        $checked = 'checked';
      } else {
        $Entregado = 'No';
        $font2 = 'red';
        $checked = '';
      }
      echo "<tr style='color:$font2;background:$color2;font-size:12px;'>";
      $fecha = $row[1];
      $arrayfecha = explode('-', $fecha, 3);
      echo "<td style='width:6px'>" . $arrayfecha[2] . "/" . $arrayfecha[1] . "/" . $arrayfecha[0] . "</td>";
      echo "<td style='width:6px'>$NumRepo</td>";
      echo "<td style='width:8%'>$Origen";
      echo "<td style='width:8%'>$Destino";
      echo "<td style='width:8%'>$FormaDePago";
      echo "<td style='width:8%'>$Servicio</td>";
      echo "<td style='width:8%'>$Entregado</td>";
      echo "<td style='width:8%'>$Numer</td>";
      echo "<td style='width:8%'>$Bultos</td>";
      echo "<td style='width:8%'>$TotalStock</td>";

      if ($Facturar == 'No') {
      } elseif ($Facturar == 'Si') {
        echo "<form class='limpio' action=''>";
        echo "<td><input type='checkbox' name='idTransClientes[]' value='$u' $checked></td>";
      }
      $CuentoRemitos++;
    }
  }

  echo "<input type='hidden' name='VentaDirecta' value='Si'>";
  echo "<input type='hidden' name='FacturarxRemito' value='Si'>";
  echo "<input type='hidden' id='desde_t' value='$Desde'>";
  echo "<input type='hidden' id='hasta_t' value='$Hasta'>";

  echo "<tfoot>";
  echo "<tr>";
  echo "<th colspan='11'>Total de Remitos: $ $SumarRemitos ($CuentoRemitos)</th>";
  echo "</tr>";
  echo "</tfoot>";
  echo "</table>";
  echo "<div><input type='submit' class='boton' name='boton' value='Aceptar'></div>";
  echo "<div><a class='boton' Onclick='imprimir()' style='font-weight:bold;cursor:pointer;height:15px;margin-right:5px'>Imprimir</a></div>";
  echo "</form>";
  echo "</div>"; // principal
  echo "</div>"; //cuerpo

  goto a;
}
//---------------------------------REMITOS DE RECEPCION-------------------------------------

if ($_GET['Ventas'] == 'MostrarRecepcion') {
  $cliente = $_GET['Cliente'];

  $ordenar = "SELECT * FROM TransClientes WHERE ClienteDestino='$cliente' AND TipoDeComprobante='Remito'AND Eliminado=0 ";
  $MuestraRemitos = mysql_query($ordenar);
  $Extender = '15';
  echo "<div style='height:90%; overflow:auto;'>";
  echo "<table class='login' border='0'  vspace='15px' style='float:left;width:100%;margin-left:20px;'>";
  echo "<caption>Listado de Remitos de Recepcion</caption>";
  echo "<th>Fecha</th>";
  echo "<th>Numero</th>";
  echo "<th>Servicio</th>";
  echo "<th>Observaciones</th>";
  echo "<th>Codigo Seg.</th>";
  echo "<th>Origen</th>";
  echo "<th>Destino</th>";
  echo "<th>Total</th>";
  echo "<th>Remito</th>";
  echo "<th>Rotulo</th>";
  echo "<th>Eliminar</th>";

  while ($row = mysql_fetch_row($MuestraRemitos)) {
    $u = $row[0];
    $n = $row[1];
    $NumRepo = $row[5];
    $Servicio = $row[4];
    $Observaciones = $row[30];
    $Numer = $row[16];
    $ImpNeto = $row[16];
    $Origen = $row[2];
    $Destino = $row[9];
    $CodigoSeguimiento = $row[16];
    echo "<tr style='font-size:11px;color:$font2;background:$color2;'>";
    $fecha = $row[1];
    $arrayfecha = explode('-', $fecha, 3);
    echo "<td>" . $arrayfecha[2] . "/" . $arrayfecha[1] . "/" . $arrayfecha[0] . "</td>";
    echo "<td>$NumRepo</td>";
    $TotalStock = money_format('%i', $row[7]);
    $Total = $row[7];
    echo "<td>$Servicio</td>";
    echo "<td style='width:100px'>$Observaciones</td>";
    echo "<td>$Numer</td>";
    echo "<td>$Origen</td>";
    echo "<td>$Destino</td>";
    echo "<td>$TotalStock</td>";
    echo "<input type='hidden' name='NumRepo' value='$NumRepo'>";
    echo "<input type='hidden' name='id' value='$u'>";
    echo "<td align='center'><a target='_blank' href='Informes/Remitopdf.php?CS=$CodigoSeguimiento'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
    echo "<td align='center'><a target='_blank' href='Informes/Rotulospdf.php?CS=$CodigoSeguimiento'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
    echo "<td align='center'><a href='Ventas.php?Ventas=Eliminar&CS=$CodSeg'><input type='image' src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:center;'></td>";
    echo "</form>";
  }
  echo "</tr></table>";
  echo "</div>";
  goto a;
}
//----------------------------------LISTADO QUE MUESTRA LAS VENTAS FACTURADAS------------------------


$cliente = $_GET['Cliente'];

if ($_GET['Facturas'] == 'Mostrar') {
  echo "<div style='overfow:auto;height:100px;'>";

  echo "<table class='login'>";
  echo "<caption>Facturas de Venta $cliente</caption>";
  echo "<th>Fecha</th>";
  // echo "<td>Razon Social</td>";
  // echo "<td>Cuit</td>";
  echo "<th>Comprobante</th>";
  echo "<th>Numero</th>";
  echo "<th>Importe Neto</th>";
  echo "<th>Iva 2.5</th>";
  echo "<th>Iva 10.5</th>";
  echo "<th>Iva 21</th>";
  echo "<th>Exento</th>";
  echo "<th>Total</th>";
  echo "<th>F</th>";
  echo "<th>E</th>";
  // echo "</table>";


  //   echo "<table class='login'>";  
  $ordenar = "SELECT * FROM IvaVentas WHERE RazonSocial='$cliente' ORDER BY Fecha ASC";
  $MuestraStock = mysql_query($ordenar);

  while ($row = mysql_fetch_row($MuestraStock)) {
    $u = $row[0];
    $n = $row[1];
    $f = $row[5];
    $fecha = $row[1];
    $arrayfecha = explode('-', $fecha, 3);
    $Fecha2 = $arrayfecha[2] . "/" . $arrayfecha[1] . "/" . $arrayfecha[0];
    echo "<tr><td style='width:5%'>$Fecha2</td>";
    // 	echo "<td>$row[2]</td>";
    // 	echo "<td>$row[3]</td>";
    echo "<td style='width:10%'>$row[4]</td>";
    echo "<td style='width:15%'>$row[5]</td>";
    echo "<td style='width:10%'>$ $row[6]</td>";
    echo "<td style='width:10%'>$ $row[7]</td>";
    echo "<td style='width:10%'>$ $row[8]</td>";
    echo "<td style='width:10%'>$ $row[9]</td>";
    echo "<td style='width:10%'>$ $row[10]</td>";
    echo "<td style='width:10%'>$ $row[11]</td>";
    echo "<input type='hidden' name='NumRepo' value='$n'>";
    echo "<input type='hidden' name='id' value='$u'>";
    echo "<input type='hidden' name='fac' value='$f'>";

    if (file_exists("../FacturasVenta/FA-" . $f . ".pdf")) {
      echo "<td align='center'><a target='_blank' href='../FacturasVenta/FA-$f.pdf'><input type='image' src='../images/botones/Factura.png' width='12' height='12' border='0' style='float:center;'></td>";
      echo "<td align='center'><a target='' href='Ventas.php?Modificar=Si&id=$u'><input type='image' src='../images/botones/lapiz.png' width='12' height='12' border='0' style='float:center;'></td>";
    } else {
      echo "<td></td>";
      echo "<td align='center'><a target='' href='Ventas.php?Modificar=Si&id=$u'><input type='image' src='../images/botones/lapiz.png' width='12' height='12' border='0' style='float:center;'></td>";
    }
  }
  echo "</tr></table>";
  echo "</div>";
  goto a;
}
//-------------------------------HASTA ACA TABLA CTAS CTES-------------------------
if ($_GET['Ventas'] == 'Eliminar') {
  header('location:../Inicio/VentanaEliminar.php?CS=' . $_GET['CS']);

  // $N=$_GET['NR'];
  // $eliminaTablaVentas="UPDATE `Ventas` SET Eliminado='1' WHERE NumeroRepo='$N'";
  // mysql_query($eliminaTablaVentas);
  // $eliminaTablaTransClientes="UPDATE `TransClientes` SET Eliminado='1' WHERE NumeroComprobante='$N'";
  // mysql_query($eliminaTablaTransClientes);


  //   $eliminaTablaCtasCtes="UPDATE `Ctasctes` SET Eliminado='1' WHERE NumeroVenta='$N'";
  // mysql_query($eliminaTablaCtasCtes);	
}

if ($_GET[Descargar] == 'Descargar') {
  $Desde = $_GET[desde_t];
  $Hasta = $_GET[hasta_t];
  header("location:GenerarExcelCtaCte.php?Desde=$Desde&Hasta=$Hasta");
}

if ($_GET['CtaCte'] == 'Aceptar') {
  $cliente = $_SESSION['ClienteActivo'];

  if ($_GET[desde_t] == '') {
    echo "<form class='Caddy' action='' method='get' style='width:500px'>";
    echo "<div><titulo>Ver Cuenta Corriente de $cliente</titulos></div>";
    echo "<div><hr></hr></div>";
    echo "<div><label>Desde</label><input name='desde_t' size='16' type='date' value='' style='float:right' required/></div>";
    echo "<div><label>Hasta</label><input name='hasta_t' size='16' type='date' value='' style='float:right' required/></div>";
    echo "<div><input name='CtaCte' class='bottom' type='submit' style='width:110px' value='Aceptar' ></div>";
    echo "<div><input name='Descargar' class='bottom' type='submit' style='width:110px' value='Descargar' ></div>";
    echo "</form>";
    goto a;
  }

  $Desde = $_GET[desde_t];
  $Hasta = $_GET[hasta_t];
  echo "<input type='hidden' id='desde_var' val='$Desde'>";
  echo "<input type='hidden' id='hasta_var' val='$Hasta'>";

  $Ordenar1 = "SELECT * FROM Ctasctes WHERE RazonSocial='$cliente' AND Eliminado='0' AND Fecha>='$Desde' AND Fecha<='$Hasta' ORDER BY Fecha,NumeroVenta ASC";
  $TransaccionesClientes = mysql_query($Ordenar1);

  $numfilas = mysql_num_rows($TransaccionesClientes);

  $color = '#B8C6DE';
  $font = 'white';
  $color1 = 'white';
  $font1 = 'black';
  $color2 = 'white';
  $font2 = 'black';

  //REMITOS DE ENVIO Y RECEPCION PENDIENTES DESDE EL INICIO DE LOS TIEMPOS
  $cuantos = 0;

  $sqlbuscopendientes0 = mysql_query("SELECT * FROM TransClientes WHERE TipoDeComprobante='Remito' AND (RazonSocial='$cliente' OR ClienteDestino='$cliente') AND Eliminado='0' AND Debe<>'0' AND Facturado='0' AND id>=2990"); //ID 2990 ES EL ID DESDE EL QUE COMENZAMOS A CARGAR LOS REMITOS EN LA CTA CTE DEL CLIENTE 
  while ($buscopendientes = mysql_fetch_array($sqlbuscopendientes0)) {
    if (($buscopendientes[RazonSocial] == $cliente) && ($buscopendientes[FormaDePago] == 'Origen')) {
      $Total += $buscopendientes[Debe];
      $cuantos++;
    } elseif (($buscopendientes[ClienteDestino] == $cliente) && ($buscopendientes[FormaDePago] == 'Destino')) {
      $Total1 += $buscopendientes[Debe];
      $cuantosRecibidos++;
    }
  }
  $Totalx = number_format($Total, 2, ',', '.');
  $Totalx1 = number_format($Total1, 2, ',', '.');

  echo "<table class='login'>";
  echo "<caption>Cuenta Corriente $cliente</caption>";
  $Cero = number_format(0, 2, ',', '.');
  echo "<th colspan='6'>Remitos pendientes</th>";
  echo "<th>Total</th>";
  echo "<tr><td colspan='6'>Remitos Enviados Pendientes de Facturacion $cuantos:</td><td>$ $Totalx</td></tr>";
  echo "<tr><td colspan='6'>Remitos Recibidos Pendientes de Facturacion $cuantosRecibidos:</td><td>$ $Totalx1</td></tr>";

  echo "<th>Fecha</th>";
  echo "<th>Razon Social</th>";
  echo "<th>Comprobante</th>";
  echo "<th>Numero</th>";
  echo "<th>Debe</th>";
  echo "<th>Haber</th>";
  echo "<th>Recibo</th>";

  while ($row = mysql_fetch_row($TransaccionesClientes)) {
    if ($numfilas % 2 == 0) {
      echo "<tr  style='background:#f2f2f2;' >";
    } else {
      echo "<tr  style='background:$color2;' >";
    }
    $fecha = $row[1];
    $arrayfecha = explode('-', $fecha, 3);
    echo "<td>" . $arrayfecha[2] . "/" . $arrayfecha[1] . "/" . $arrayfecha[0] . "</td>";
    echo "<td>$row[2]</td>";
    echo "<td>$row[4]</td>";
    if ($row[6] > 0) {
      echo "<td>$row[10]</td>"; //NUMERO DE FACTURA 
    } else {
      echo "<td>$row[5]</td>"; //NUMERO DE VENTA 
    }
    echo "<td>$ " . number_format($row[6], 2, ',', '.') . "</td>";
    echo "<td>$ " . number_format($row[7], 2, ',', '.') . "</td>";
    if ($row[7] <> 0) {
      echo "<td align='center'><a target='_blank' href='Informes/Recibopdf.php?NR=$row[5]'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
    } elseif ($row[6] <> 0) {
      echo "<td align='center'><a target='_blank' href='../FacturasVenta/FA-$row[10].pdf'><input type='image' src='../images/botones/Factura.png' width='15' height='15' border='0' style='float:center;'></td>";
    }
    $numfilas++;
  }
  setlocale(LC_ALL, 'es_AR');
  $SubTotal = money_format('%i', $row[5] * $row[6]);
  $result = mysql_query("SELECT SUM(Debe) as Debe, SUM(Haber) as Haber FROM Ctasctes WHERE RazonSocial='$cliente' AND Eliminado='0' AND Fecha>='$Desde' AND Fecha<='$Hasta';");
  $rowresult = mysql_fetch_array($result);
  $Debe = money_format('%i', $rowresult[Debe]);
  $Haber = money_format('%i', $rowresult[Haber]);
  $TotalPendientes = number_format($Total + $Total1, 2, ',', '.');
  $Saldo = money_format('%i', $rowresult[Debe] - $rowresult[Haber]);
  echo "</tr>";
  echo "<tfoot>";
  echo "<tr>";
  echo "<th colspan='4'></th>";
  echo "<th>Debe: $Debe</th>";
  echo "<th>Haber: $Haber</th>";
  echo "<th></th>";
  echo "</tr>";
  //       $TotalPendientes=number_format($Totalx+$Totalx1,2,',','.');
  echo "<tr><td colspan='4'>Remitos Pendientes de Facturacion:</td><td>$ $TotalPendientes</td><td></td><td></td></tr>";
  echo "<tr>";
  echo "<th colspan='4'></th>";
  echo "<th colspan='2'>Saldo: $Saldo</th>";
  echo "<th></th>";
  echo "</tr>";
  echo "</tfoot>";
  echo  "</table>";
  echo "</div>"; // principal
  echo "</div>"; //cuerpo

  goto a;
}
//-------------------------------HASTA ACA TABLA CTAS CTES-------------------------
//---------------------------------------------------------------------------------------------------------	
if ($_GET['ImputaPago'] == 'Aceptar') {
  $Fecha = date('Y-m-d');
  $RazonSocial = $_GET['razonsocial_t'];
  $Cuit = $_GET['cuit_t'];
  $TipoDeComprobante = $_GET['tipodecomprobante_t'];
  $NumeroComprobante = $_GET['numerodecomprobante_t'];
  $Importe = $_GET['importepago_t'];
  $Usuario = $_SESSION['Usuario'];
  $FormaDePago = $_GET[formadepago_t];


  $sqlidcliente = mysql_query("SELECT id FROM Clientes WHERE nombrecliente ='$RazonSocial'");
  $idCliente = mysql_fetch_array($sqlidcliente);

  $sqlbuscar = mysql_query("SELECT NumeroComprobante FROM TransClientes WHERE TipoDeComprobante='Recibo de Pago' AND NumeroComprobante='$NumeroComprobante'");

  if (mysql_num_rows($sqlbuscar) != '') {
    goto a;
    $error = 1;
  }

  $sql = "INSERT INTO TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Haber,FormaDePago)VALUES
('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Importe}','{$FormaDePago}')";
  mysql_query($sql);

  //------------INGRESA EL PAGO A CTAS CTES----------------------
  $sqlCtasctes = "INSERT INTO Ctasctes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroVenta,Haber,Usuario,idCliente)
VALUES('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Importe}','{$Usuario}','{$idCliente[id]}')";
  mysql_query($sqlCtasctes);
  //------------------------------------------------------------------------			

  //----------INGRESA LOS MOVIMINTOS EN TABLA IVA VENTAS---------------
  $CuantoHay = mysql_query("SELECT Ingresos,Total FROM IvaVentas WHERE NumeroComprobante='$NumeroComprobante'");
  $CuantoHayresult = mysql_result($CuantoHay, 0);
  $ImporteFinal = $Importe + $CuantoHayresult;

  $CuantoHay1 = mysql_query("SELECT Total FROM IvaVentas WHERE NumeroComprobante='$NumeroComprobante'");
  $CuantoHayresult1 = mysql_result($CuantoHay1, 0);
  $Saldo = $CuantoHayresult1 - $ImporteFinal;
  $sql1 = "UPDATE IvaVentas SET Ingresos='$ImporteFinal',Saldo='$Saldo' WHERE NumeroComprobante='$NumeroComprobante'";
  mysql_query($sql1);

  //-------INGRESA LOS MOVIMIENTOS EN TESORERIA---------------
  $Cuenta0 = $_SESSION['fp'];

  $BuscaCuenta = mysql_query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$Cuenta0'");
  $Cuenta1 = mysql_result($BuscaCuenta, 0);

  $BuscaCuentaProv = mysql_query("SELECT CtaAsignada,Cuit FROM Proveedores WHERE Cuit='$Cuit'");
  $CuentaEncontrada = mysql_result($BuscaCuentaProv, 0);

  // $BuscaCuentaProv2=mysql_query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$Cuenta0'");
  // $Cuenta2=mysql_result($BuscaCuentaProv2,0);
  $Cuenta2 = 'DEUDORES POR VENTAS';
  $Cuenta3 = '112200';
  // $BuscaCuentaProv3=mysql_query("SELECT Cuenta FROM PlanDeCuentas WHERE Cuenta='$Cuenta0'");
  // $Cuenta3=mysql_result($BuscaCuentaProv3,0);
  $Observaciones = $TipoDeComprobante . " Numero: " . $NumeroComprobante;
  $Debe2 = $_GET['debe2_t'];
  $Banco = $_GET['banco_t'];
  $FechaCheque = $_GET['fechacheque_t'];
  $NumeroCheque = $_GET['numerocheque_t'];
  $NumeroTrans = $_GET['numerotransaccion_t'];
  $FechaTrans = $_GET['fechatransaccion_t'];
  $Usuario = $_SESSION['Usuario'];
  $Sucursal = $_SESSION['Sucursal'];

  $BuscaNumAsiento = mysql_query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria WHERE Eliminado='0'");
  $row = mysql_fetch_row($BuscaNumAsiento);
  $NAsiento = trim($row[0]) + 1;

  $sql1 = "INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,Observaciones,Banco,FechaCheque,NumeroCheque,Usuario,Sucursal,NumeroAsiento,FechaTrans,NumeroTrans) VALUES 
	 ('{$Fecha}','{$Cuenta1}','{$Cuenta0}','{$Importe}','{$Observaciones}',
	 '{$Banco}','{$FechaCheque}','{$NumeroCheque}','{$Usuario}','{$Sucursal}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}')";
  mysql_query($sql1);
  $sql2 = "INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,Observaciones,Usuario,Sucursal,NumeroAsiento) VALUES 
	 ('{$Fecha}','{$Cuenta2}','{$Cuenta3}','{$Importe}','{$Observaciones}','{$Usuario}','{$Sucursal}','{$NAsiento}')";
  mysql_query($sql2);

  if ($NumeroCheque <> '') {
    $sql = "INSERT INTO Cheques(`Banco`, `NumeroCheque`, `Asiento`, `Proveedor`, `Importe`, `FechaCobro`, `Usuario`, `Terceros`) 
VALUES ('{$Banco}','{$NumeroCheque}','{$NAsiento}','{$CuentaEncontrada}','{$Importe}','{$FechaCheque}','{$Usuario}','1')";
    mysql_query($sql);
  }

  header("location:Ventas.php?CtaCte=Si");
}


if ($_GET['UltimoPaso'] == 'Cobro') {

  $N = $_GET['Remito'];
  $BuscarRemito = "SELECT * FROM TransClientes WHERE NumeroComprobante='$N' AND Eliminado='0'";
  $BuscarRemito2 = mysql_query($BuscarRemito);
  $fila = mysql_fetch_array($BuscarRemito2);
  $ordenar = "SELECT Max(NumeroComprobante) FROM TransClientes WHERE TipoDeComprobante='Recibo de Pago' AND Eliminado='0'";
  $MuestraStock = mysql_query($ordenar);
  if ($row = mysql_fetch_row($MuestraStock)) {
    $NumeroComprobante = trim($row[0]) + 1;
  }
  $Fecha = date('d/m/Y');
  $result = mysql_query("SELECT SUM(Debe-Haber) as Saldo FROM TransClientes WHERE RazonSocial='" . $_SESSION['ClienteActivo'] . "' AND Eliminado='0';");
  $rowresult = mysql_fetch_array($result);
  $Total = money_format('%i', $rowresult[Saldo]);

  $RazonSocial = $fila['RazonSocial'];
  $Domicilio = $fila['DomicilioOrigen'];
  $Cuit = $fila['Cuit'];


  echo "<form name='MyForm2' class='login' action='' method='GET' style='float:center; width:;'>";
  echo "<div><titulo>Cargar pago a Factura de " . $RazonSocial . "</titulo></div>";
  echo "<div><hr></hr><div>";
  echo "<div><label>Fecha:</label><input name='fecha_t' size='20' type='text' style='float:right;' value='$Fecha' required/></div>";
  echo "<div><label>Razon Social:</label><input name='razonsocial_t' size='50' type='text' value='$RazonSocial' OnFocus='this.blur()'></div>";
  echo "<div><label>Direccion:</label><input name='direccion_t' size='50' type='text' value='$Domicilio' OnFocus='this.blur()'></div>";
  echo "<input type='hidden' name='cuit_t' value='$Cuit'>";
  echo "<div><label>Tipo de Comprobante:</label><input name='tipodecomprobante_t' size='30' type='text' value='Recibo de Pago' OnFocus='this.blur()' /></div>";
  echo "<div><label>Numero de Venta:</label><input name='numerodecomprobante_t' size='30' type='text' value='" . $fila['NumeroComprobante'] . "' readonly/></div>";
  echo "<div><label>Numero de Recibo:</label><input name='numeroderecibo_t' size='30' type='text' value='$NumeroComprobante' readonly/></div>";
  echo "<div><label>Total:</label><input name='total_t' size='20' type='text' value='" . $fila['Debe'] . "' /></div>";

  if ($_GET['formadepago_t'] == '113010002') {
    $fp = $_GET['formadepago_t'];
    echo "<input type='hidden' name='formadepago_t' value='$fp'>";
    echo "<div><label>Forma de Pago:</label><input name='' value='Cheques de Terceros' size='20' type='text' disabled/></div>";
    echo "<div><label>Banco:</label><input name='banco_t' size='20' type='text' value='' /></div>";
    echo "<div><label>Numero de Cheque:</label><input name='numerocheque_t' size='20' type='text' value='' /></div>";
    echo "<div><label>Fecha de Cobro:</label><input name='fechacheque_t' size='20' type='text' value='' /></div>";
  } else {
    $Grupo = "SELECT NombreCuenta,Cuenta,Nivel FROM PlanDeCuentas WHERE Nivel=4 ORDER BY Cuenta ASC";
    $estructura = mysql_query($Grupo);
    echo "<input type='hidden' name='CargarCobranza' value='Si'>";
    echo "<input type='hidden' name='Factura' value='$Nc'>";
    echo "<div><label>Forma de Pago:</label><select name='formadepago_t' onchange='motrar()' style='float:center;width:250px;' size='1'>";
    while ($row = mysql_fetch_row($estructura)) {
      echo "<option value='" . $row[1] . "'>" . $row[0] . "</option>";
    }
    echo "<input type='hidden' name='Cuenta' value='$row[1]'>";
    echo "</select></div>";
  }
  echo "<div><label>Valor Declarado:</label><input name='valordeclarado_t' size='30' type='number' value='0'  step='0.01' onblur='sumar2()' /></div>";
  echo "<div><label>Alicuota Seguro:</label><input name='seguro_t' size='30' type='number' step='0.01' value='0.1' onblur='sumar2()' readonly/></div>";
  echo "<div><label>Total Seguro:</label><input name='importeseguro_t' size='30' type='number' value='0' step='0.01' readonly/></div>";
  echo "<div><label>Importe a Cobrar:</label><input name='importepago_t' size='30' type='number' value='" . $fila['Debe'] . "' step='0.01' readonly/></div>";
  echo "<div><input name='UltimoPaso' class='bottom' type='submit' value='Aceptar' style='width:100px' ></label></div>";
  echo "<div><input name='UltimoPaso' class='bottom' type='submit' value='Cancelar' style='width:100px' ></label></div>";
  echo "</form>";

  goto a;
}
//--------------------INGRESA PAGO ULTIMO PASO--------------
if ($_GET['UltimoPaso'] == 'Aceptar') {

  if ($_GET['importepago_t'] == '') {
    goto a;
  } else {
    //---------------INGRESA LOS MOVIMIENTOS EN TRANSACCIONES--------------------
    $Fecha = date('Y-m-d');
    $Total = $_GET['importepago_t'];
    $RazonSocial = $_GET['razonsocial_t'];
    $TipoDeComprobante = $_GET['tipodecomprobante_t'];
    $NumeroComprobante = $_GET['numerodecomprobante_t'];
    $Usuario = $_SESSION['Usuario'];
    $Recibo = $_GET['numeroderecibo_t'];
    $FormaDePago = $_GET[formadepago_t];

    //BUSCO EL ID DEL CLIENTE
    $sqlidcliente = mysql_query("SELECT id FROM Clientes WHERE nombrecliente ='$RazonSocial'");
    $idCliente = mysql_fetch_array($sqlidcliente);


    // COMPRUEBO SI LA ENTREGA NECESITA REDESPACHO
    $sqlredespacho = mysql_query("SELECT * FROM Localidades WHERE Localidad='$_SESSION[LocalidadDestino_t]'");
    $datosql = mysql_fetch_array($sqlredespacho);
    if ($datosql[Web] == 0) {
      $Redespacho = 1;
    } else {
      $Redespacho = 0;
    }


    $sqlTransacciones = "INSERT INTO TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Haber,NumeroVenta,Usuario,FormaDePago,Redespacho)
VALUES('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$Recibo}','{$Total}','{$NumeroComprobante}','{$Usuario}','{$FormaDePago}','{$Redespacho}')";
    mysql_query($sqlTransacciones);

    //------------INGRESA EL PAGOA CTAS CTES----------------------
    $sqlCtasctes = "INSERT INTO Ctasctes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroVenta,Haber,Usuario,idCliente)
VALUES('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$Recibo}','{$Total}','{$Usuario}','{$idCliente[id]}')";
    mysql_query($sqlCtasctes);
    //------------------------------------------------------------------------			


    //-------INGRESA LOS MOVIMIENTOS EN TESORERIA---------------
    $Sucursal = $_SESSION['Sucursal'];
    $Usuario = $_SESSION['Usuario'];
    $Observaciones = "Pago de " . $RazonSocial;
    if ($Compra == true) {
      $Cuenta1 = '115010003';
      $Cuenta2 = '211010001';
      $NombreCuenta1 = 'COSTO FLETE';
      $NombreCuenta2 = 'EDITORIALES A PAGAR';
      $Debe = $Total - $Iva1 - $Iva2 - $Iva3;
      $Haber = $Total;
    } else {
      $Cuenta1 = '000111100';
      $Cuenta2 = '000112200';
      $NombreCuenta1 = 'CAJA';
      $NombreCuenta2 = 'DEUDORES POR VENTAS';
      $Debe = $Total - $Iva1 - $Iva2 - $Iva3;
      $Haber = $Total;
    }
    $sql1 = "INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,Sucursal,Usuario,Observaciones) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','{$Debe}','{$Sucursal}','{$Usuario}','{$Observaciones}')";
    mysql_query($sql1);
    $sql2 = "INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,Sucursal,Usuario,Observaciones) VALUES ('{$Fecha}','{$NombreCuenta2}','{$Cuenta2}','{$Haber}','{$Sucursal}','{$Usuario}','{$Observaciones}')";
    mysql_query($sql2);
  }
  unset($NumeroRepo);
  unset($NumeroPedido);
  header("location:https://www.caddy.com.ar/SistemaTriangular/Inicio/Cpanel.php");
}
if ($_GET['UltimoPaso'] == 'Cancelar') {
  unset($NumeroRepo);
  unset($NumeroPedido);
  header("location:https://www.caddy.com.ar/SistemaTriangular/Inicio/Cpanel.php");
}
//------------------------HASTA ACA INGRESA PAGO ULTIMO PASO VENTA MOSTRADOR-----------
//-----------------------------------------DESDE ACA PARA ULTIMO PASO PAGO------------------------
if ($_GET['Cobranza'] == 'Si' or $_SESSION['fp'] <> '') {
  //         	  $_SESSION['idClienteActivo']=$DatoCA[id];

  $Cuit = $_SESSION['CuitActivo'];
  $datosEncontrados = "SELECT * FROM Clientes WHERE id='$_SESSION[idClienteActivo]'";
  $estructura2 = mysql_query($datosEncontrados);
  while ($row = mysql_fetch_row($estructura2)) {
    $Cuit1 = $row[24];
    $Nombre = $row[2];
    $Direccion = $row[17];
  }
  $Nc = $_GET['Factura'];

  $ordenar = "SELECT Max(NumeroComprobante) FROM TransClientes WHERE TipoDeComprobante='Recibo de Pago' AND Eliminado='0'";
  $MuestraStock = mysql_query($ordenar);
  if ($row = mysql_fetch_row($MuestraStock)) {
    $NComprobante = trim($row[0]) + 1;
  }
  $Fecha = date('d/m/Y');

  $result = mysql_query("SELECT SUM(Debe-Haber) as Saldo FROM Ctasctes WHERE RazonSocial='$Nombre' AND Eliminado='0';");
  // 						$result=mysql_query("SELECT SUM(Debe-Haber) as Saldo FROM TransClientes WHERE RazonSocial='$Nombre';");		
  $rowresult = mysql_fetch_array($result);
  $Total = money_format('%i', $rowresult[Saldo]);

  echo "<form name='cobranza' class='login' action='' method='GET' style='float:center; width:600px;'>";
  echo "<div><label style='float:center;color:red;font-size:22px'>Cargar pago a Factura de $Nombre</label></div>";
  echo "<div><hr></hr></div>";
  echo "<div><label>Fecha:</label><input name='fecha_t' size='20' type='text' style='float:right;' value='$Fecha' required/></div>";
  echo "<div><label>Razon Social:</label><input name='razonsocial_t' size='50' type='text' value='$Nombre' OnFocus='this.blur()'></div>";
  echo "<div><label>Direccion:</label><input name='direccion_t' size='50' type='text' value='$Direccion' OnFocus='this.blur()'></div>";
  echo "<input type='hidden' name='cuit_t' value='$Cuit'>";
  echo "<div><label>Tipo de Comprobante:</label><input name='tipodecomprobante_t' size='50' type='text' value='Recibo de Pago' OnFocus='this.blur()' /></div>";
  echo "<div><label>Numero de Comprobante:</label><input name='numerodecomprobante_t' size='50' type='text' value='$NComprobante' OnFocus='this.blur()'/></div>";

  echo "<div><label>Total:</label><input name='total_t' size='20' type='text' value='$Total' /></div>";

  // 							if($_SESSION['fp']=='112400'){
  // 							echo "<input type='hidden' name='formadepago_t' value='$fp'>";
  // 							echo "<div><label>Forma de Pago:</label><input name='' value='".$_SESSION['fp']."' size='20' type='text' disabled/></div>";
  // 							echo "<div><label>Banco:</label><input name='banco_t' size='20' type='text' value='' /></div>";
  // 							echo "<div><label>Numero de Cheque:</label><input name='numerocheque_t' size='20' type='text' value='' /></div>";
  // 							echo "<div><label>Fecha de Cobro:</label><input name='fechacheque_t' size='20' type='text' value='' /></div>";	

  // 							}else{
  $Grupo = "SELECT FormaDePago,CuentaContable FROM FormaDePago WHERE AdmiteCobranzas=1";
  $estructura = mysql_query($Grupo);
  echo "<input type='hidden' name='CargarCobranza' value='Si'>";
  echo "<input type='hidden' name='Factura' value='$Nc'>";

  echo "<div><label>Forma de Pago:</label><select name='formadepago_t' onchange='mostrar()'style='float:center;width:250px;' size='1'>";
  while ($row = mysql_fetch_row($estructura)) {
    echo "<option value='" . $row[1] . "'>" . $row[0] . "</option>";
  }
  echo "<input type='hidden' name='Cuenta' value='$row[1]'>";
  echo "</select></div>";

  // 							}     
  echo "<div id='BancoOculto' style='display:none;'><label>Banco:</label><input name='banco_t' size='30' type='text' value='' /></div>";
  echo "<div id='oculto' style='display:none;'><label>Numero Transferencia</label><input name='numerotransferencia_t' size='30' type='text' value='' /></div>";
  echo "<div id='oculto1' style='display:none;'><label>Fecha De Transferencia:</label><input name='fechatransferencia_t' size='30' type='date' value='' /></div>";
  echo "<div id='NumeroChequeOculto' style='display:none;'><label>Numero de Cheque:</label><input name='numerocheque_t' size='30' type='text' value='' /></div>";
  echo "<div id='FechaChequeOculto' style='display:none;'><label>Fecha De Pago:</label><input name='fechacheque_t' size='30' type='date' value='' /></div>";
  echo "<div><label>Importe a Cobrar:</label><input name='importepago_t' size='30' type='text' value='' /></div>";

  echo "<div><input name='ImputaPago' class='bottom' type='submit' value='Aceptar' ></label></div>";
  echo "</form>";

  goto a;
}


//-----------------------------------------DESDE ACA PARA AGREGAR FACTURAS DE VENTA------------------------
if ($_GET['Cargar'] == 'Si') {
  $Cuit = $_SESSION['CuitActivo'];

  // 		if ($_POST['razonsocial']==''){
  if ($Cuit == '') {
    echo "<form class='login' action='' method='post' style='float:center; width:500px;'>";
    echo "<div><label style='float:center;color:red;font-size:22px'>Cargar Factura Ventas</label></div>";
    $Grupo = "SELECT nombrecliente,Cuit FROM Clientes ORDER BY nombrecliente ASC";
    $estructura = mysql_query($Grupo);
    echo "<div><label>Razon Social:</label><select name='razonsocial' onchange='submit()' style='float:center;width:390px;' size='1'>";
    while ($row = mysql_fetch_row($estructura)) {
      echo "<option value='" . $row[1] . "'>" . $row[0] . "</option>";
    }
    echo "</select></div>";
    echo "</form>";
    goto a;
  }
  // $Cuit=$_POST['razonsocial'];
  // $Cuit=$_SESSION['NCliente'];
  $datosEncontrados = "SELECT * FROM Clientes WHERE Cuit='$Cuit'";
  $estructura2 = mysql_query($datosEncontrados);
  while ($row = mysql_fetch_row($estructura2)) {
    $Cuit1 = $row[24];
    $Nombre = $row[2];
    $Direccion = $row[17];
  }
  // $Fecha=date('d/m/Y');	
  echo "<form name='MyForm' class='login' action='prueba.php' method='POST' enctype='multipart/form-data' style='float:center;'>";
  echo "<div><titulo>Carga Manual de Factura de Venta AFIP</titulo></div>";
  echo "<div><hr></hr></div>";
  echo "<div><label>Fecha:</label><input name='fecha_t' size='20' type='date' style='float:right;' value='' required/></div>";
  echo "<div><label>Razon Social:</label><input name='razonsocial_t' size='50' type='text' value='$Nombre' readonly></div>";
  echo "<div><label>Direccion:</label><input name='direccion_t' size='50' type='text' value='$Direccion' readonly></div>";
  echo "<div><label>Cuit:</label><input name='cuit_t' size='20' type='text' value='$Cuit1' readonly></div>";

  $Grupo = "SELECT Codigo,Descripcion FROM AfipTipoDeComprobante WHERE 
	Descripcion='FACTURAS A' OR
	Descripcion='NOTAS DE CREDITO A' OR
	Descripcion='NOTAS DE DEBITO A' OR
	Descripcion='FACTURAS B' OR
	Descripcion='NOTAS DE CREDITO B' OR
	Descripcion='NOTAS DE DEBITO B' ORDER BY Codigo ASC";
  // 	$Grupo="SELECT Codigo,Descripcion FROM AfipTipoDeComprobante ORDER BY Codigo ASC";

  $estructura = mysql_query($Grupo);
  echo "<div><label>Tipo De Comprobante:</label><select name='tipodecomprobante_t' style='float:center;width:310px;' size='1'>";
  while ($row = mysql_fetch_row($estructura)) {
    echo "<option value='" . $row[1] . "'>" . $row[1] . "</option>";
  }
  echo "</select></div>";

  echo "<div><label>Numero de Comprobante:</label><input name='numerocomprobante_t' size='20' type='text' value='' /></div>";
  echo "<div><label>Es Compra de Mercaderia:</label><input name='compra_t' type='checkbox' value='S'/></div>";
  echo "<div><label>Importe Neto:</label><input name='importeneto_t' size='10' type='number' step='0.01' value='0' onblur='sumar()' required/></div>";
  echo "<div><label>Iva 2.5%:</label><input name='iva1_t' size='10' type='number' step='0.01' value='0' onblur='sumar()' required/></div>";
  echo "<div><label>Iva 10.5%:</label><input name='iva2_t' size='10' type='number' step='0.01' value='0' onblur='sumar()'/></div>";
  echo "<div><label>Iva 21%:</label><input name='iva3_t' size='10' type='number' step='0.01' value='0' onblur='sumar()' /></div>";
  echo "<div><label>Exento:</label><input name='exento_t' size='10' type='number' step='0.01' value='0' onblur='sumar()' /></div>";
  echo "<div><label>Total:</label><input name='total_t' size='10' type='number' step='0.01' value='' readonly/></div>";
  echo "<div><label>Subir imagen de Factura:</label><input type='FILE' name='imagen' id='imagen'></div>";
  echo "<div><input name='FacturaSimple' class='bottom' type='submit' value='Aceptar' ></label></div>";
  echo "</form>";
  goto a;
}
//---------------------------MODIFICAR FACTURAS DE VENTAS------------------------------------------
if ($_GET['ModificarFacVenta'] == 'Aceptar') {
  $id = $_GET['id'];
  $Usuario = $_SESSION['NombreUsuario'];
  $Fecha = $_GET['fecha_t'];
  $RazonSocial = $_GET['razonsocial_t'];
  $Cuit2 = $_GET['cuit_t'];
  $TipoDeComprobante = $_GET['tipodecomprobante_t'];
  $NumeroComprobante = $_GET['numerocomprobante_t'];
  $ImporteNeto = $_GET['importeneto_t'];
  $Iva1 = $_GET['iva1_t'];
  $Iva2 = $_GET['iva2_t'];
  $Iva3 = $_GET['iva3_t'];
  $Exento = $_GET['exento_t'];
  $Total = $_GET['total_t'];
  $Compra = $_GET['compra_t'];
  $sql = "UPDATE IvaVentas SET 
TipoDeComprobante='$TipoDeComprobante',
NumeroComprobante='$NumeroComprobante',
ImporteNeto='$ImporteNeto',
Iva1='$Iva1',
Iva2='$Iva2',
Iva3='$Iva3',
Exento='$Exento',
Total='$Total',
CompraMercaderia='$Compra' WHERE id='$id'";
  mysql_query($sql);
  header("location:Ventas.php?Ventas=Mostrar&Cliente=$RazonSocial");
}
//-------------------------------------------------------------------------------------------------

//---------------------------DESDE ACA PARA MODIFICAR FACTURA DE VENTA COMPROBANTE DE AFIP-------------
if ($_GET['Modificar'] == 'Si') {
  $Cuit = $_SESSION['CuitActivo'];
  $u = $_GET['id'];
  $datosEncontrados = "SELECT * FROM IvaVentas WHERE id='$u'";
  $estructura2 = mysql_query($datosEncontrados);
  while ($row = mysql_fetch_array($estructura2)) {

    $Fecha = $row[Fecha];
    $Fecha0 = '';


    echo "<form name='MyForm' class='login' action='' method='GET' enctype='multipart/form-data' style='float:center; width:500px;'>";
    echo "<div><label style='float:center;color:red;font-size:22px'>Modificacion de Factura de Venta AFIP</label></div>";
    echo "<div><label>Fecha:</label><input name='fecha_t' size='20' type='date' style='float:right;' value='$row[Fecha]' required/></div>";
    echo "<div><label>Razon Social:</label><input name='razonsocial_t' size='50' type='text' value='$row[RazonSocial]'></div>";
    // echo "<div><label>Direccion:</label><input name='direccion_t' size='50' type='text' value='$row[Direccion]'></div>";
    echo "<div><label>Cuit:</label><input name='cuit_t' size='20' type='text' value='$row[Cuit]' /></div>";
    echo "<div><label>Tipo De Comprobante:</label><input name='tipodecomprobante_t' size='20' type='text' value='$row[TipoDeComprobante]' size='1'></div>";
    echo "<div><label>Numero de Comprobante:</label><input name='numerocomprobante_t' size='20' type='text' value='$row[NumeroComprobante]' /></div>";
    echo "<div><label>Importe Neto:</label><input name='importeneto_t' size='10' type='number' step='0.01' value='$row[ImporteNeto]' onblur='sumar()' required/></div>";
    echo "<div><label>Iva 2.5%:</label><input name='iva1_t' size='10' type='number' step='0.01' value='$row[Iva1]' onblur='sumar()' required/></div>";
    echo "<div><label>Iva 10.5%:</label><input name='iva2_t' size='10' type='number' step='0.01' value='$row[Iva2]' onblur='sumar()'/></div>";
    echo "<div><label>Iva 21%:</label><input name='iva3_t' size='10' type='number' step='0.01' value='$row[Iva3]' onblur='sumar()' /></div>";
    echo "<div><label>Exento:</label><input name='exento_t' size='10' type='number' step='0.01' value='$row[Exento]' onblur='sumar()' /></div>";
    echo "<div><label>Total:</label><input name='total_t' size='10' type='number' step='0.01' value='$row[Total]' readonly/></div>";
    echo "<div><label>Es Compra de Mercaderia:</label><input name='compra_t' type='checkbox' value='S'/></div>";
    echo "<div><input name='ModificarFacVenta' class='bottom' type='submit' value='Aceptar' ></label></div>";
    echo "<input type='hidden' name='id' value='$row[id]'>";
    echo "</form>";
  }
  goto a;
}

//---------------------------HASTA ACA PARA MODIFICAR FACTURA DE VENTA COMPROBANTE DE AFIP--------------
//---------------------------------DESDE ACA PARA CARGAR VENTA DIRECTA----------------------------------
if ($_GET['VentaDirecta'] == 'Si') {
  $Cuit = $_SESSION['CuitActivo'];

  $BuscaNumRepo = mysql_query("SELECT MAX(NumeroRepo) AS NumeroRepo FROM Ventas");
  if ($row = mysql_fetch_row($BuscaNumRepo)) {
    $NRepo = trim($row[0]) + 1;
    $NRepoZ = sprintf("%010d", trim($row[0]) + 1);
  }

  if ($Cuit == '') {

    header('location:https://www.caddy.com.ar/SistemaTriangular/Clientes/Clientes.php?id=Modificar&Cuit=problema&idCliente=' . $_SESSION['idClienteActivo']);
    //       echo "<form class='login' action='' method='post' style='float:center; width:500px;'>";
    // 			$Grupo="SELECT nombrecliente,Cuit FROM Clientes ORDER BY nombrecliente ASC";
    // 			$estructura= mysql_query($Grupo);
    //       echo "<div><titulo>Buscar Cliente</titulo></div>";
    //       echo "<div><hr></hr></div>";
    // 			echo "<div><label>Razon Social:</label><select name='razonsocial' onchange='submit()' style='float:center;width:390px;' size='1'>";
    // 			while ($row = mysql_fetch_row($estructura)){
    // 			echo "<option value='".$row[1]."'>".$row[0]."</option>";
    // 			}
    // 			echo "</select></div>";
    // 		echo "</form>";	
    goto a;
  }
  $datosEncontrados = "SELECT * FROM Clientes WHERE Cuit='$Cuit'";
  $estructura2 = mysql_query($datosEncontrados);
  while ($row = mysql_fetch_row($estructura2)) {
    $idCliente2 = $row[0];
    $Cuit1 = $row[24];
    $Nombre = $row[2];
    $Direccion = $row[17];
    $SituacionFiscal = $row[21];
  }

  if ($_GET[Facturar] == 'Aceptar') {

    echo "<form name='MyForm' class='Caddy' action='prueba.php' method='POST' enctype='multipart/form-data' ><div>";
    echo "<div><label style='float:center;color:red;font-size:22px'>Facturacion x Recorrido</label></div>";
    echo "<div><hr></hr></div>";
    echo "<fieldset style='float:left;width:45%;'>";
    echo "<div><label>Fecha:</label><input name='fecha_t' size='20' type='date' style='float:right;' value='' required/></div>";
    echo "<div><label>Razon Social:</label><input name='razonsocial_t' size='40' type='text' value='$Nombre'></div>";
    echo "<div><label>Direccion:</label><input name='direccion_t' size='40' type='text' value='$Direccion'></div>";
    echo "<div><label>Cuit:</label><input name='cuit_t' size='40' type='text' value='$Cuit1' /></div>";
    echo "<div><label>Situacion Fiscal:</label><input name='situacionfiscal_t' size='40' type='text' value='$SituacionFiscal' /></div>";
    echo "<div><label>Concepto:</label><input name='titulo_t' size='40' type='text' value='Facturacion x Recorrido' /></div>";

    $Grupo = "SELECT Codigo,Descripcion FROM AfipTipoDeComprobante WHERE 
	Descripcion='FACTURAS A' OR
	Descripcion='NOTAS DE CREDITO A' OR
	Descripcion='NOTAS DE DEBITO A' OR
	Descripcion='FACTURAS B' OR
	Descripcion='NOTAS DE CREDITO B' OR
	Descripcion='NOTAS DE DEBITO B' ORDER BY Codigo ASC";

    $estructura = mysql_query($Grupo);
    echo "<div><label>Tipo De Comprobante:</label><select name='tipodecomprobante_t' style='float:center;width:260px;' size='1'>";
    while ($row = mysql_fetch_row($estructura)) {
      echo "<option value='" . $row[1] . "'>" . $row[1] . "</option>";
    }
    echo "</select></div>";
    echo "<div><label>Numero de Comprobante:</label><input name='numerocomprobante_t' size='40' type='text' value='' required/></div>";
    echo "<div><label>Subir imagen de Factura:</label><input type='FILE' name='imagen' id='imagen'></div></fieldset>";

    //SI VIENE DESDE FACTURACION X RECORRIDO
    $MuestraRemitos = mysql_query("SELECT * FROM Logistica");
    $total = 0;

    $box = $_GET[NumOrden];

    while ($row = mysql_fetch_array($MuestraRemitos)) {
      for ($i = 0; $i < count($box); $i++) {
        if ($row[NumerodeOrden] == $box[$i]) {
          $sqlCodigo = mysql_query("SELECT CodigoProductos FROM Recorridos WHERE Numero='" . $row[Recorrido] . "' ") or die(mysql_error());
          if (mysql_num_rows($sqlCodigo) <> 0) {
            $Codigo = mysql_result($sqlCodigo, 0);
          }
          $sqlprecio = mysql_query("SELECT PrecioVenta FROM Productos WHERE Codigo='$Codigo' ") or die(mysql_error());
          if (mysql_num_rows($sqlprecio) <> 0) {
            $DatoPrecio = mysql_result($sqlprecio, 0);
          }
          $total = $DatoPrecio + $total;
        }
      }
      $numfilas++;
    }
    $TotalaFacturar = money_format('%i', $total);
    $Neto = ($total / 1.21);
    $TotalConIva = $total;
    $Iva = $TotalConIva - $Neto;


    if ($_GET[NetoVentaDirecta] <> '0') {
      $VentaDirecta = number_format($Neto, 2, ".", "");
    } else {
      $VentaDirecta = '0';
    }
    if ($_GET[IvaVentaDirecta] <> '0') {
      $VentaDirecta1 = number_format($Iva, 2, ".", "");
    } else {
      $VentaDirecta1 = '0';
    }
    if ($_GET[TotalConIvaVentaDirecta] <> '0') {
      $VentaDirecta2 = number_format($TotalConIva, 2, ".", "");
    } else {
      $VentaDirecta2 = '0';
    }
    $box = $_GET['NumOrden'];
    $CantidadRemitos = count($box);
    for ($i = 0; $i < count($box); $i++) {

      echo "<input type='hidden' name='NumOrden[]' value='$box[$i]'/>";
      $Orden .= $box[$i];
      $Orden .= " /";
    }
    $_SESSION['NumOrden'] = $_GET['NumOrden'];

    echo "<fieldset style='float:left;width:45%;margin-left:15px;'>";
    echo "<div><label>Cantidad:</label><input name='cantidad_t' size='20' type='number' value='$CantidadRemitos' onblur='buscar()' /></div>";
    echo "<input name='idcliente_t' type='hidden' value='$idCliente2' />";
    echo "<div><label>Observaciones:</label><input name='observaciones_t' size='40' type='text' value='Facturacion de Ordenes $Orden' required/></div>";
    echo "<div><label>Importe Neto:</label><input  name='importeneto_t' size='10' type='text' value='$VentaDirecta' onblur='sumar()' required/></div>";
    echo "<div><label>Iva 2.5%:</label><input name='iva1_t' size='10' type='text'  value='0' onblur='sumar()' required/></div>";
    echo "<div><label>Iva 10.5%:</label><input name='iva2_t' size='10' type='text'  value='0' onblur='sumar()'/></div>";
    echo "<div><label>Iva 21%:</label><input name='iva3_t' size='10' type='text'  value='$VentaDirecta1' onblur='sumar()' /></div>";

    // echo "<input  name='importeneto_t' type='hidden' value='$Neto'/>";
    // echo "<input name='iva3_t' type='hidden'  value='$Iva'/>";
    // echo "<input name='total_t' type='hidden' value='$TotalConIva'/>";    

    echo "<div><label>Exento:</label><input name='exento_t' size='10' type='text'  value='0' onblur='sumar()' /></div>";
    echo "<div><label>Total:</label><input name='total_t' size='10' type='text' step='0.01' value='$VentaDirecta2' readonly/></div>";
    // echo "<div><label>Es Compra de Mercaderia:</label><input name='compra_t' type='checkbox' value='S'/></div>";
    echo "<div><input name='FacturacionxRecorrido' class='bottom' type='submit' value='Aceptar' ></label></div>";
    echo "</form></fieldset>";
    goto a;
  }

  if ($_GET['FacturarxRemito'] == 'Si') {

    echo "<form name='MyForm' class='Caddy' action='prueba.php' method='POST' enctype='multipart/form-data' ><div>";
    echo "<div><titulo>Facturacion</titulo></div>";
    echo "<div><hr></hr></div>";
    echo "<fieldset style='float:left;width:45%;'>";
    echo "<div><label>Fecha:</label><input name='fecha_t' size='20' type='date' style='float:right;' value='' required/></div>";
    echo "<div><label>Razon Social:</label><input name='razonsocial_t' size='40' type='text' value='$Nombre'></div>";
    echo "<div><label>Direccion:</label><input name='direccion_t' size='40' type='text' value='$Direccion'></div>";
    echo "<div><label>Cuit:</label><input name='cuit_t' size='40' type='text' value='$Cuit1' /></div>";
    echo "<div><label>Situacion Fiscal:</label><input name='situacionfiscal_t' size='40' type='text' value='$SituacionFiscal' /></div>";
    echo "<div><label>Concepto:</label><input name='titulo_t' size='40' type='text' value='Facturacion x Remito' /></div>";

    $Grupo = "SELECT Codigo,Descripcion FROM AfipTipoDeComprobante WHERE 
	Descripcion='FACTURAS A' OR
	Descripcion='NOTAS DE CREDITO A' OR
	Descripcion='NOTAS DE DEBITO A' OR
	Descripcion='FACTURAS B' OR
	Descripcion='NOTAS DE CREDITO B' OR
	Descripcion='NOTAS DE DEBITO B' ORDER BY Codigo ASC";

    $estructura = mysql_query($Grupo);
    echo "<div><label>Tipo de Comprobante:</label><select name='tipodecomprobante_t' style='float:center;width:250px;' size='1'>";
    while ($row = mysql_fetch_row($estructura)) {
      echo "<option value='" . $row[1] . "'>" . $row[1] . "</option>";
    }
    echo "</select></div>";
    echo "<div><label>Numero de Comprobante:</label><input name='numerocomprobante_t' size='40' type='text' value='' required/></div>";
    echo "<div><label>Subir imagen de Factura:</label><input type='FILE' name='imagen' id='imagen'></div></fieldset>";
    $_SESSION['idTransClientes'] = $_GET['idTransClientes'];

    $idTransClientes = $_GET['idTransClientes'];
    $CantidadRemitos = count($idTransClientes);
    $Dato = mysql_query("SELECT id,Debe FROM TransClientes");

    while ($row = mysql_fetch_array($Dato)) {

      for ($i = 0; $i <= $CantidadRemitos; $i++) {
        if ($idTransClientes[$i] == $row[id]) {
          $SubTotal[$i] = $row[Debe];
          $Total += $SubTotal[$i];
        }
      }
    }

    $NetoSinIva = $Total / 1.21;
    $Iva = $Total - $NetoSinIva;
    $NetoSinIvaF = number_format($NetoSinIva, 2, '.', ',');
    $IvaF = number_format($Iva, 2, '.', ',');
    $TotalF = number_format($Total, 2, '.', ',');

    echo "<fieldset style='float:left;width:45%;margin-left:15px;'>";
    echo "<div><label>Cantidad Remitos:</label><input name='cantidad_t' size='20' type='number' value='$CantidadRemitos'  readonly/></div>";
    echo "<div><label>Observaciones:</label><input name='observaciones_t' size='40' type='text' value='Facturacion de Ordenes $Orden' required/></div>";
    echo "<input name='importeneto_t' size='10' type='hidden' value='$NetoSinIva'/>";
    echo "<div><label>Importe Neto:</label><input name='1_t' size='10' type='text' step='0.01' value='$NetoSinIvaF' onblur='sumar()' required/></div>";
    echo "<input name='iva_t' type='hidden' value='$Iva'/>";
    echo "<div><label>Iva:</label><input name='2_t' size='10' type='text'  value='$IvaF' onblur='sumar()' /></div>";
    echo "<input name='exento_t' type='hidden' value='0'/>";
    echo "<div><label>Exento:</label><input name='3_t' size='10' type='text'  value='0' onblur='sumar()' /></div>";
    echo "<input name='total_t' size='10' type='hidden' value='$Total'/>";
    echo "<div><label>Total:</label><input name='4_t' size='10' type='text' step='0.01' value='$TotalF' readonly/></div>";
    if ($CantidadRemitos > 50) {
      $sugerido = '35 %';
    } elseif ($CantidadRemitos > 30) {
      $sugerido = '20 %';
    } elseif ($CantidadRemitos > 50) {
      $sugerido = '10 %';
    }
    echo "<div><label>Descuento Sugerido $sugerido:</label><input type='checkbox' name='descuentosugerido_t' Onclick='selecdescuento()'/></div>";
    echo "<div id='desc_id' style='display:none'><label>Descuento a Aplicar (en %):</label><input id='descuentootorgado_t' type='number' placeholder='Ejemplo 35'/></div>";
    echo "<div id='desc_botom' style='display:none;float:right'><a class='boton_fondo' id='descuento_button' onclick='Aplicar_Descuento()'>Aplicar Descuento</a></div>";

    echo "</fieldset>";
    echo "<fieldset style='float:left;width:95%;margin-left:15px;'>";
    echo "<div><table class='login'>";
    echo "<th>Codigo</th>";
    echo "<th>Servicio</th>";
    echo "<th>Cantidad</th>";
    echo "<th>Precio Unit.</th>";
    echo "<th>Alicuota IVA</th>";
    echo "<th>Subtotal c/IVA</th>";
    //   echo "<tr>";
    $Dato = mysql_query("SELECT * FROM TransClientes");
    while ($row = mysql_fetch_array($Dato)) {

      for ($i = 0; $i <= $CantidadRemitos; $i++) {
        if ($idTransClientes[$i] == $row[id]) {
          $SubTotal[$i] = $row[Debe];
          $Total += $SubTotal[$i];
          $NetoSinIva = $row[Debe] / 1.21;
          $Iva21 = $row[Debe] - $NetoSinIva;
          if ($numfilas % 2 == 0) {
            echo "<tr  style='font-size:12px;color:$font1;background: #f2f2f2;'>";
          } else {
            echo "<tr  style='font-size:12px;color:$font1;background:$color2;'>";
          }
          $NetoSinIvaF = number_format($NetoSinIva, 2, ',', '.');
          $IvaF = number_format($Iva21, 2, ',', '.');
          echo "<td>$row[NumeroComprobante]</td>";
          echo "<td>$row[TipoDeComprobante]</td>";
          echo "<td>$row[Cantidad]</td>";
          echo "<td>$NetoSinIvaF</td>";
          echo "<td>$IvaF</td>";
          echo "<td>$row[Debe]</td>";
          echo "</tr>";
        }
      }
      $numfilas++;
    }
    echo "</table></div>";
    echo "<div><input name='FacturacionxRemito' class='bottom' type='submit' value='Aceptar' ></div>";
    echo "</div></form></fieldset>";
    goto a;
  }
  if ($_GET['FacturaDirecta'] == 'Si') {
    //HASTA ACA VENTA X REMITOS

    echo "<fieldset style='float:left;width:45%;margin-left:15px;'>";

    $Grupo = "SELECT * FROM Productos ORDER BY Codigo ASC";
    $estructura = mysql_query($Grupo);
    echo "<div><label>Servicio:</label><select name='servicio_t' style='float:center;width:320px;' onblur='buscar()' size='1'>";
    while ($row = mysql_fetch_array($estructura)) {
      echo "<option value='" . $row[PrecioVenta] . "-" . $row[Titulo] . "'>" . $row[Titulo] . " / $ " . $row[PrecioVenta] . "</option>";
    }
    echo "</select></div>";

    echo "<div><label>Cantidad:</label><input name='cantidad_t' size='20' type='number' value='1' onblur='buscar()' /></div>";
    // echo "<div><label>Numero de Comprobante:</label><input name='numerocomprobante_t' size='20' type='text' value='$NRepoZ' /></div>";
    echo "<div><label>Observaciones:</label><input name='observaciones_t' size='40' type='text' value='' required/></div>";
    echo "<div><label>Importe Neto:</label><input name='importeneto_t' size='10' type='number' value='0' step='0.01' onblur='sumar()' required/></div>";
    echo "<div><label>Iva 2.5%:</label><input name='iva1_t' size='10' type='number' step='0.01' value='0' onblur='sumar()' required/></div>";
    echo "<div><label>Iva 10.5%:</label><input name='iva2_t' size='10' type='number' step='0.01' value='0' onblur='sumar()'/></div>";
    echo "<div><label>Iva 21%:</label><input name='iva3_t' size='10' type='number' step='0.01' value='0' onblur='sumar()' /></div>";
    echo "<div><label>Exento:</label><input name='exento_t' size='10' type='number' step='0.01' value='0' onblur='sumar()' /></div>";
    echo "<div><label>Total:</label><input name='total_t' size='10' type='number' step='0.01' value='0' readonly/></div>";
    // echo "<div><label>Es Compra de Mercaderia:</label><input name='compra_t' type='checkbox' value='S'/></div>";
    echo "<div><input name='FacturacionDirecta' class='bottom' type='submit' value='Aceptar' ></label></div>";
    echo "</form></fieldset>";
    goto a;
  }
}

// //---------------------------------HASTA ACA PARA CARGAR VENTA DIRECTA----------------------------------

a:
if ($error == 1) {
?>
  <script>
    alertify.error("Pago duplicado");
  </script>
<?
  $error = 0;
}
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor
?>
<script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
<script src="../hyper/dist/saas/assets/js/app.min.js"></script>
<script src="Procesos/js/descuento.js"></script>

</div>
</body>
</center>
<?php
ob_end_flush();
?>