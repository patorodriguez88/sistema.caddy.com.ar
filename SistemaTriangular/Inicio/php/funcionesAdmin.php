<?php
session_start();
include_once "../../Conexion/Conexioni.php";

//MUESTRA GASTOS
if (isset($_POST['MuestraGastos'])) {
  $sql = "SELECT MuestraGastos FROM PlanDeCuentas WHERE Cuenta='$_POST[Cuenta]'";
  $Resultado = $mysqli->query($sql);
  $row = $Resultado->fetch_array(MYSQLI_ASSOC);
  if ($row['MuestraGastos'] == 0) {
    $sql = $mysqli->query("UPDATE PlanDeCuentas SET MuestraGastos=1 WHERE Cuenta='$_POST[Cuenta]'");
  } else {
    $sql = $mysqli->query("UPDATE PlanDeCuentas SET MuestraGastos=0 WHERE Cuenta='$_POST[Cuenta]'");
  }
  echo json_encode(array('success' => 1));
}

//TABLA GASTOS DETALLES
if (isset($_POST['GastosDetalles'])) {
  $sql = "SELECT t.Cuenta,t.NombreCuenta,SUM(Debe)as Debe,p.MuestraGastos FROM Tesoreria as t INNER JOIN PlanDeCuentas as p ON t.Cuenta=p.Cuenta 
  WHERE t.NoOperativo='0' AND t.Fecha>='$_POST[inicio]' AND t.Fecha<='$_POST[final]' AND Debe<>0 AND t.Eliminado=0 GROUP BY t.Cuenta";
  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

//TABLA CLIENTES
if (isset($_POST['Clientes'])) {
  $sql = "SELECT (SUM(Debe)/SUM(Cantidad))as Prom,if(FormaDePago='Origen',IngBrutosOrigen,idClienteDestino)as idCliente,if(FormaDePago='Origen',RazonSocial,ClienteDestino)as Cliente,SUM(Debe)as Total,SUM(Cantidad)as Cantidad FROM 
  TransClientes WHERE Eliminado=0  AND Debe<>0 AND CodigoSeguimiento<>'' AND Fecha>='$_POST[inicio]' AND Fecha<='$_POST[final]'  
 GROUP BY Cliente ORDER BY Total DESC LIMIT 0,20";
  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}
//IMPRIMIR TODOS LOS REMITOS
if (isset($_POST['RemitosRec'])) {
  $rec = $_POST['rec'];
  $sql = "SELECT * FROM TransClientes WHERE Eliminado=0 AND Entregado=0 AND Recorrido='$_POST[rec]' AND CodigoSeguimiento<>''";
  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    header('Location:https://www.sistemacaddy.com.ar/SistemaTriangular/Ventas/Informes/RemitopdfAut.php?CS="' . $fila['CodigoSeguimiento'] . '"');
  }
  $contar = $Resultado->num_rows;
  echo json_encode(array('data' => $rows, $contar));
}


//GASTOS MES ACTUAL
if (isset($_POST['Gastos'])) {

  $sqlgastos = $mysqli->query("SELECT SUM(Debe)as Debe FROM `Tesoreria` INNER JOIN PlanDeCuentas ON Tesoreria.Cuenta=PlanDeCuentas.Cuenta 
WHERE PlanDeCuentas.MuestraGastos=1 AND Fecha>='$_POST[inicio]' AND Fecha<='$_POST[final]' AND Tesoreria.Eliminado='0' 
AND PlanDeCuentas.MuestraGastos='1' AND Tesoreria.NoOperativo='0'");

  $rowgastos = $sqlgastos->fetch_array(MYSQLI_ASSOC);
  $sqlVentas = $mysqli->query("SELECT SUM(Debe)as id FROM TransClientes WHERE Eliminado='0' AND Debe>0 AND Fecha>='$_POST[inicio]' AND Fecha<='$_POST[final]'");
  $rowVentas = $sqlVentas->fetch_array(MYSQLI_ASSOC);

  $sqlRecorridos = $mysqli->query("SELECT SUM(subquery.TotalFacturado) AS id
  FROM (
      SELECT Logistica.TotalFacturado AS TotalFacturado
      FROM Logistica
      WHERE Eliminado = 0 
      AND Fecha >= '$_POST[inicio]' 
      AND Fecha <= '$_POST[final]' 
      AND NumeroF <> ''
      GROUP BY NumeroF
  ) AS subquery;");

  $rowRecorridos = $sqlRecorridos->fetch_array(MYSQLI_ASSOC);
  $Ventas = $rowVentas['id'] + $rowRecorridos['id'];

  $sqlMes = $mysqli->query("SELECT SUM(Debe)as id FROM Ctasctes WHERE  Eliminado=0 AND Debe>0 
  AND Fecha>='$_POST[inicio]' AND Fecha<='$_POST[final]'");
  $rowMes = $sqlMes->fetch_array(MYSQLI_ASSOC);
  $sqlMesant = $mysqli->query("SELECT SUM(Debe)as id FROM Ctasctes WHERE  Eliminado=0 AND Debe>0 
  AND Fecha>='$_POST[inicio]' AND Fecha<='$_POST[final]'");
  $rowMesant = $sqlMesant->fetch_array(MYSQLI_ASSOC);
  $Porcentaje = number_format((($rowMesant['id'] - $rowMes['id']) / $rowMesant['id']) * 100, 2, '.', ',');
  if ($rowMesant['id'] > $rowMes['id']) {
    $tendencia = '2';
  } else if ($rowMesant['id'] == $rowMes['id']) {
    $tendencia = '0';
  } else if ($rowMesant['id'] < $rowMes['id']) {
    $tendencia = '1';
  }
  //RECORRIDOS
  $sqlR = $mysqli->query("SELECT SUM(Debe)as id FROM Ctasctes WHERE FacturacionxRecorrido='1' AND Eliminado='0' AND Fecha=CURDATE() AND Debe<>'0'");
  $rowR = $sqlR->fetch_array(MYSQLI_ASSOC);
  $sqlMesR = $mysqli->query("SELECT SUM(Debe)as id FROM Ctasctes WHERE FacturacionxRecorrido='1' AND Eliminado='0' AND Debe<>0 
  AND YEAR(Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Fecha)= MONTH(CURRENT_DATE())");
  $rowMesR = $sqlMesR->fetch_array(MYSQLI_ASSOC);
  $sqlMesantR = $mysqli->query("SELECT SUM(Debe)as id FROM Ctasctes WHERE FacturacionxRecorrido='1'  AND Eliminado='0' AND Debe<>0 
  AND YEAR(Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Fecha)= (MONTH(CURRENT_DATE())-1)");
  $rowMesantR = $sqlMesantR->fetch_array(MYSQLI_ASSOC);
  $PorcentajeR = number_format((($rowMesantR['id'] - $rowMesR['id']) / $rowMesantR['id']) * 100, 2, '.', ',');
  if ($rowMesantR['id'] > $rowMesR['id']) {
    $tendenciaR = '2';
  } else if ($rowMesantR['id'] == $rowMesR['id']) {
    $tendenciaR = '0';
  } else if ($rowMesantR['id'] < $rowMesR['id']) {
    $tendenciaR = '1';
  }

  //TOTAL
  $Resultado = number_format(($Ventas - $rowgastos['Debe']), 2, '.', ',');
  $rowT = $Ventas + $rowR['id'];
  $rowMesT = $rowMes['id'] + $rowMesR['id'];
  $rowMesantT = $rowMesant['id'] + $rowMesantR['id'];

  $PorcentajeT = number_format((($rowMesantT - $rowMesT) / $rowMesantT) * 100, 2, '.', ',');

  if ($rowMesantT > $rowMesT) {
    $tendenciaT = '2';
  } else if ($rowMesantT == $rowMesT) {
    $tendenciaT = '0';
  } else if ($rowMesantT < $rowMesT) {
    $tendenciaT = '1';
  }

  echo json_encode(array('success' => 1, 'TotalGastos' => $rowgastos['Debe'], 'TotalMes' => $Ventas, 'Resultado' => $Resultado));
}

if (isset($_POST['Ventas'])) {

  $sql = $mysqli->query("SELECT MONTHNAME(v.Fecha) AS Mes,SUM(v.Debe) AS Total FROM TransClientes v WHERE YEAR(v.fecha) = YEAR(CURRENT_DATE()) 
  AND v.Eliminado=0 GROUP BY Mes ORDER BY YEAR(v.Fecha),MONTH(v.Fecha)");
  $Mes = array();
  $Total = array();
  while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
    //   $rows[]=$row;  
    $Mes[] = $row['Mes'];
    $Total[] = $row['Total'];
  }
  echo json_encode(array('x' => $Mes, 'y' => $Total));
}



if (isset($_POST['GastosGrafico'])) {

  $sql = $mysqli->query("SELECT MONTHNAME(v.Fecha) AS Mes,SUM(v.Debe) AS Total FROM Tesoreria v INNER JOIN PlanDeCuentas p ON v.Cuenta=p.Cuenta 
WHERE YEAR(v.fecha) = YEAR(CURRENT_DATE()) AND v.Eliminado=0 AND p.MuestraGastos=1 GROUP BY Mes ORDER BY YEAR(v.Fecha),MONTH(v.Fecha)");
  $Mes = array();
  $Total = array();

  while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
    $Mes[] = $row['Mes'];
    $Total[] = $row['Total'];
  }

  echo json_encode(array('x' => $Mes, 'y' => $Total));
}



if (isset($_POST['VentasRec'])) {

  $sql = $mysqli->query("SELECT MONTHNAME(Fecha)AS Mes,SUM(IF(ImporteF=0,TotalFacturado,ImporteF))AS Total FROM `Logistica` WHERE Eliminado=0 AND YEAR(Fecha) = YEAR(CURRENT_DATE()) GROUP BY MONTH(Fecha)");
  $Mes = array();
  $Total = array();

  while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
    //$rows[]=$row;  
    $Mes[] = $row['Mes'];
    $Total[] = $row['Total'];
  }
  echo json_encode(array('x' => $Mes, 'y' => $Total));
}
//PAQUETES ENVIADOS
if (isset($_POST['Envios'])) {

  $sql = $mysqli->query("SELECT MONTHNAME(v.Fecha) AS Mes,COUNT(v.id) AS Total FROM TransClientes v 
WHERE YEAR(v.Fecha) = YEAR(CURRENT_DATE()) AND v.Eliminado=0 AND v.Haber=0 AND Devuelto=0 GROUP BY Mes ORDER BY YEAR(v.Fecha),MONTH(v.Fecha)");

  $Mes = array();
  $Total = array();
  while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
    $Mes[] = $row['Mes'];
    $Total[] = $row['Total'];
  }
  echo json_encode(array('x' => $Mes, 'y' => $Total));
}

//VENTAS MENSUALES X RECORRIDO 
if (isset($_POST['Envios0'])) {
  $sql = $mysqli->query("SELECT MONTHNAME(v.Fecha) AS Mes,COUNT(v.id) AS Total FROM TransClientes v 
WHERE YEAR(v.Fecha) = YEAR(CURRENT_DATE()-1) AND v.Eliminado=0 AND v.Haber=0 GROUP BY Mes ORDER BY YEAR(v.Fecha-1),MONTH(v.Fecha)");
  $Mes = array();
  $Total = array();
  while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
    $Mes[] = $row['Mes'];
    $Total[] = $row['Total'];
  }
  echo json_encode(array('x' => $Mes, 'y' => $Total));
}

if (isset($_POST['VentasClientes'])) {
  $sql = $mysqli->query("SELECT MONTHNAME(t.Fecha) AS Mes,SUM(t.Debe) AS Total FROM TransClientes t WHERE IF(FormaDePago='Origen',t.IngBrutosOrigen,t.idClienteDestino)='$_POST[idCliente]' AND YEAR(t.Fecha) = YEAR(CURRENT_DATE()) 
AND t.Eliminado='0' GROUP BY Mes ORDER BY YEAR(t.Fecha),MONTH(t.Fecha)");
  $Mes = array();
  $Total = array();
  while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
    //   $rows[]=$row;  
    $Mes[] = $row['Mes'];
    $Total[] = $row['Total'];
  }
  echo json_encode(array('x' => $Mes, 'y' => $Total));
}

if (isset($_POST['Best_guest'])) {
  $sql = "SELECT RazonSocial,COUNT(id)as Total FROM TransClientes v 
  WHERE YEAR(v.Fecha) = YEAR(CURRENT_DATE()-1) AND v.Eliminado=0 
  AND v.Haber=0 GROUP BY RazonSocial ORDER BY Total DESC LIMIT 0,6";
  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('success' => 1, 'datos' => $rows));
}

//TOTAL FACTURACION X MES

if (isset($_POST['Envios1'])) {

  //RECORRIDOS
  $sql = $mysqli->query("SELECT MONTHNAME(Fecha)AS Mes,SUM(IF(ImporteF=0,TotalFacturado,ImporteF))AS Total FROM `Logistica` WHERE Eliminado=0 AND YEAR(Fecha) = YEAR(CURRENT_DATE()) GROUP BY MONTH(Fecha)");

  $Mes_r = array();
  $Total_r = array();

  while ($row_r = $sql->fetch_array(MYSQLI_ASSOC)) {
    $Mes_r[] = $row_r['Mes'];
    $Total_r[] = $row_r['Total'];
  }

  $sql = $mysqli->query("SELECT MONTHNAME(v.Fecha) AS Mes,SUM(v.Debe) AS Total FROM TransClientes v 
    WHERE YEAR(v.Fecha) = YEAR(CURRENT_DATE()-1) AND v.Eliminado=0 AND v.Haber=0 GROUP BY Mes 
    ORDER BY YEAR(v.Fecha-1),MONTH(v.Fecha)");

  $Mes = array();
  $Total = array();

  while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
    $Mes[] = $row['Mes'];
    $Total[] = $row['Total'];
  }

  echo json_encode(array('x' => $Mes, 'y' => $Total, 'xr' => $Mes_r, 'yr' => $Total_r));
}
