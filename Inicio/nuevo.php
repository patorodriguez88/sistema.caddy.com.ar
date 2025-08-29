<?
ob_start();
session_start();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>My CSS experiment</title>
    <link rel="stylesheet" href="../css/nuevo.css">
  </head>
  <body>
<? 
echo "<div id='contenedor'>"; 
  echo "<div id='cabecera'>"; 
  include("../Menu/MenuGestion.php"); 
  echo "</div>";//cabecera 
     echo "<div id='cuerpo'>"; 
      echo "<div id='lateral'>"; 
                      echo "<div id='cssmenu'>";
                      echo "<ul>";
                      echo "<li><a href='Ventas.php?Facturas=Mostrar&Cliente=$Empresa'><span>Ver Facturas</span></a></li>";
                      echo "   <li class='active has-sub'><a href=''><span>Ver Remitos</span></a>";
                      echo "      <ul>";
                      echo "         <li class='has-sub'><a href='Ventas.php?Ventas=MostrarEnvio&Cliente=$Empresa'><span>Remitos de Envio</span></a>";
                      // echo "            <ul>";
                      // echo "              <li><a href='#'><span>Sub Product</span></a></li>";
                      // echo "               <li class='last'><a href='#'><span>Sub Product</span></a></li>";
                      // echo "            </ul>";
                      echo "         </li>";
                      echo "         <li class='has-sub'><a href='Ventas.php?Ventas=MostrarRecepcion&Cliente=$Empresa'><span>Remitos de Recepcion</span></a>";
                      // echo "            <ul>";
                      // echo "               <li><a href='#'><span>Sub Product</span></a></li>";
                      // echo "               <li class='last'><a href='#'><span>Sub Product</span></a></li>";
                      // echo "            </ul>";
                      echo "         </li>";
                      echo "      </ul>";
                      echo "   </li>";
                      echo "   <li><a href='CompruebaCliente.php'><span>Cargar Envios</span></a></li>";
                      echo "<li><a href='Ventas.php?VentaDirecta=Si'><span>Cargar Venta</span></a></li>";
                      echo "<li><a href='Ventas.php?Cargar=Si'><span>Cargar Factura</span></a></li>";
                      echo "<li><a href='Ventas.php?Cobranza=Si'><span>Cargar Cobranza</span></a></li>";
                      echo "<li><a href='../Clientes/Clientes.php?id=Modificar&IdCliente=$id'><span>Modificar Datos</span></a></li>";
                      echo "<li><a href='Ventas.php?CtaCte=Si'><span>Cuenta Corriente</span></a></li>";
                      echo "   <li class='last'><a href='Ventas.php?Ventas=Mostrar&Cliente=$Empresa'><span>Panel de Control</span></a></li>";
                      echo "</ul>";
                      echo "</div>";
                      echo "<div style='clear: both'></div>";  
                      echo "</div>"; //lateral
                          echo  "<div id='principal'>"; 
                          echo "<form name='MyForm' class='login' action='' method='GET' enctype='multipart/form-data' >";
                          echo "<div><titulo>Carga Manual de Factura de Venta AFIP</titulo></div>";
                          echo "<div><label>Fecha:</label><input name='fecha_t' size='20' type='date' value='' required/></div>";
                          echo "<div><label>Razon Social:</label><input name='razonsocial_t' size='50' type='text' value='$Nombre'></div>";
                          echo "<div><label>Direccion:</label><input name='direccion_t' size='50' type='text' value='$Direccion'></div>";
                          echo "<div><label>Cuit:</label><input name='cuit_t' size='20' type='text' value='$Cuit1' /></div>";

                            $Grupo="SELECT Codigo,Descripcion FROM AfipTipoDeComprobante WHERE 
                            Descripcion='FACTURAS A' OR
                            Descripcion='NOTAS DE CREDITO A' OR
                            Descripcion='NOTAS DE DEBITO A' OR
                            Descripcion='FACTURAS B' OR
                            Descripcion='NOTAS DE CREDITO B' OR
                            Descripcion='NOTAS DE DEBITO B' ORDER BY Codigo ASC";
                          echo "<div><label>Es Compra de Mercaderia:</label><input name='compra_t' type='checkbox' value='S'/></div>";
                          echo "<div><input name='Alta'  type='submit' value='Aceptar' ></label></div>";
                          echo "</form>";
                           echo "<table class='login'>";
                          echo "<tr>";
                          echo "<th colspan='12' >LOGISTICA</th>";
                          echo "</tr>";
                          echo "<tr>";
                          echo "<th  colspan='12'>Listado de Remitos pendientes de entrega</th>";
                          echo "</tr>";
                          echo "<tr>";
                          echo "<td>Numero</td>";
                          echo "<td>Fecha</td>";
                          echo "<td>Codigo</td>";
                          echo "<td>Origen</td>";
                          echo "<td>Destino</td>";
                          echo "<td>Entrega En</td>";
                          echo "<td>Recorrido</td>";
                          echo "<td>Bultos</td>";
                          echo "<td>Total</td>";
                          echo "<td>Remito</td>";
                          echo "<td>Rotulo</td>";
                          echo "<td>Eliminar</td></tr>";
                          echo "</table>";    
                          echo "</div>"; // 
     echo "</div>"; //cuerpo
                              echo "<div id='pie'>"; 
                              echo "© 2005 DesarrolloWeb.com"; 
                              echo "</div>"; //pie
echo "</div>";  //contenedor
ob_end_flush();
?>  
