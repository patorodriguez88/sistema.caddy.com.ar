<style>
.formnuevo{
font-family: sans-serif;
font-size: 15px;
font-weight: 400;
color: #17202A;
background: #ECF0F1;
margin: 0 0 25px;
overflow: hidden;
padding: 20px;
margin-left:2px;
  border-left: red 3px solid;

}
</style>  

<?php
ob_start();
session_start();
echo "<form class='formnuevo'>";
echo "<div style='color:red'>Cliente Emisor</div>";
echo "<div>".$_SESSION['NombreClienteA']."</div>";
echo "<div>".$_SESSION['SituacionFiscalEmisor_t']."</div>";
echo "<div>".$_SESSION['NCliente']."</div>";
echo "<div>".$_SESSION['DomicilioEmisor_t']."</div>";
echo "<div>".$_SESSION['LocalidadOrigen_t']."</div>";
echo "<div>".$_SESSION['TelefonoEmisor_t']."</div>";
echo "</form>";  
  
  
  


// $Empresa=$_SESSION['ClienteActivo'];
// $Cuit=$_SESSION['CuitActivo'];	
// $id=$_SESSION['idClienteActivo'];	
// $dato=$_SESSION['NdeCliente'];


// echo "<div id='cssmenu'>";
// echo "<ul>";
// echo "<li><a href='CompruebaCliente.php'><span>Cargar Envios</span></a></li>";
// // echo "<li><a href=''><span>Cargar Remito</span></a></li>";

// echo "<li><a href='Ventas.php?Facturas=Mostrar&Cliente=$Empresa'><span>Ver Facturas</span></a></li>";
// echo "   <li class='active has-sub'><a href=''><span>Ver Remitos</span></a>";
// echo "      <ul>";
// echo "         <li class='has-sub'><a href='#'><span>Remitos de Envio</span></a>";
// echo "            <ul>";
// echo "              <li><a href='Ventas.php?Ventas=MostrarxNumeroEnvio&Cliente=$Empresa'><span>X Numero de Remito</span></a></li>";
// echo "               <li><a href='Ventas.php?Ventas=MostrarEnvio&Cliente=$Empresa&Remitos=Pendientes'><span>Remitos a Facturar</span></a></li>";
// echo "               <li class='last'><a href='Ventas.php?Ventas=MostrarEnvio&Cliente=$Empresa'><span>Ver Todos</span></a></li>";
// echo "            </ul>";
// echo "         </li>";
// echo "         <li class='has-sub'><a href='Ventas.php?Ventas=MostrarRecepcion&Cliente=$Empresa'><span>Remitos de Recepcion</span></a>";
// // echo "            <ul>";
// // echo "               <li><a href='#'><span>Sub Product</span></a></li>";
// // echo "               <li class='last'><a href='#'><span>Sub Product</span></a></li>";
// // echo "            </ul>";
// echo "         </li>";
// echo "      </ul>";
// echo "   </li>";
// // echo "<li><a href='Ventas.php?Cargar=Si'><span>Cargar Factura AFIP</span></a></li>";
// $RemitosPendientes=mysql_query("SELECT * FROM TransClientes WHERE RazonSocial='".$_SESSION['ClienteActivo']."' AND TipoDeComprobante='Remito' AND Eliminado='0' AND Debe>'0' AND Facturado='0'");
// $TotalRemitos=mysql_num_rows($RemitosPendientes);

// $RecorridosPendientes=mysql_query("SELECT id FROM `Logistica` WHERE `Cliente` = '$id' AND Facturado='0' AND Eliminado='0'");   
// $TotalRecorridos=mysql_num_rows($RecorridosPendientes);
// echo "   <li class='active has-sub'><a href=''><span>Facturacion</span></a>";
// echo "      <ul>";
// echo "         <li><a href='Ventas.php?Cargar=Si'><span>Factura Simple</span></a>";
// echo "         <li><a href='Ventas.php?Ventas=MostrarEnvio&Cliente=$Empresa&Remitos=Pendientes'><span>Factura x Remitos ($TotalRemitos)</span></a>";
// echo "         <li><a href='Ventas.php?Ventas=Recorridos&Cliente=$Empresa'><span>Factura x Recorridos ($TotalRecorridos)</span></a>";
// echo "      </ul>";
// echo "    </li>";

// echo "<li><a href='Ventas.php?Cobranza=Si'><span>Cargar Cobranza</span></a></li>";
// // echo "<li><a href='Ventas.php?Ventas=Recorridos&Cliente=$Empresa'><span>Facturar Recorridos</span></a></li>";
// echo "<li><a href='../Clientes/Clientes.php?id=Modificar&idCliente=$id'><span>Modificar Datos</span></a></li>";
// echo "<li><a href='Ventas.php?CtaCte=Aceptar'><span>Cuenta Corriente</span></a></li>";
// // echo "   <li class='last'><a href='../Clientes/Clientes.php?Seguimiento=Si&Cliente=$Empresa'><span>Seguimiento de Envios</span></a></li>";
// echo "</ul>";
// echo "</div>";
// echo "<div style='clear: both'></div>";  

ob_end_flush();  