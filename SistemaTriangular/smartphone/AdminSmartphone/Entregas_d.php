<?
ob_start();
session_start();
// include_once "../../ConexionSmartphone.php";
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangularcopia",$conexion);

$sqlC = mysql_query("SELECT * FROM Logistica WHERE id='10'");
$Dato=mysql_fetch_array($sqlC);
$Dato[Recorrido];

$sql=mysql_query("SELECT TransClientes.Retirado,TransClientes.Observaciones,TransClientes.Cantidad,TransClientes.CodigoSeguimiento,TransClientes.id,HojaDeRuta.id,TransClientes.RazonSocial,TransClientes.ClienteDestino,TransClientes.NumeroComprobante,TransClientes.Debe,TransClientes.Haber,TransClientes.DocumentoDestino,TransClientes.DomicilioDestino,TransClientes.LocalidadDestino FROM `TransClientes`,`HojaDeRuta` 
    WHERE TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
    AND TransClientes.Entregado='0'
    AND TransClientes.Recorrido='$Dato[Recorrido]' 
    AND TransClientes.Eliminado='0' 
    AND HojaDeRuta.Eliminado='0' 
    AND HojaDeRuta.Estado='Abierto' ORDER BY HojaDeRuta.Posicion ASC");
//     $sql=mysql_query("SELECT * FROM `TransClientes`,`HojaDeRuta` 
//     WHERE TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
//     AND TransClientes.Entregado='0'
//     AND TransClientes.Recorrido='$Dato[Recorrido]' 
//     AND TransClientes.Eliminado='0' 
//     AND HojaDeRuta.Eliminado='0' 
//     AND HojaDeRuta.Estado='Abierto' ORDER BY HojaDeRuta.Posicion ASC");                      

    $sqlRecorridos=mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$Dato[Recorrido]'");
    $datosql=mysql_fetch_array($sqlRecorridos);                      

    //CANTIDADES
    $sqlCantidad=mysql_query("SELECT COUNT(id)as Cantidad FROM HojaDeRuta WHERE Recorrido='$Dato[Recorrido]' AND Eliminado=0 AND Estado='Abierto' AND NumerodeOrden='$Dato[NumerodeOrden]'");
    $CantidadPendientes=mysql_fetch_array($sqlCantidad);
    $sqlCantidadTotal=mysql_query("SELECT COUNT(id)as Cantidad FROM HojaDeRuta WHERE Recorrido='$Dato[Recorrido]' AND Eliminado=0 AND NumerodeOrden='$Dato[NumerodeOrden]'");
    $TotalCantidad=mysql_fetch_array($sqlCantidadTotal);
    $Pendientes=$TotalCantidad[Cantidad]-$CantidadPendientes[Cantidad];
    
//     echo "<div id='recorrido'><h2>Recorrido: ".$_SESSION['RecorridoAsignado']."</h2></div>";//RECORRIDO                     
//     echo "<div id='circulo' style='right:100px;background:#82E0AA'><h2>$CantidadPendientes[Cantidad]</h2></div>";//ENTREGADOS
//     echo "<div id='circulo' style='right:55px;background:#F1948A'><h2>$Pendientes</h2></div>";//PENDIENTES
//     echo "<div id='circulo' style='background:gray'><h2>$TotalCantidad[Cantidad]</h2></div>";

  //PROXIMO RECORRIDO
    if(mysql_numrows($sql)==0){
    $sqlproximo = mysql_query("SELECT * FROM Logistica WHERE idUsuarioChofer='".$_SESSION['idusuario']."' AND Estado='Cargada' AND Eliminado='0' AND id<>'$Dato[id]'");
    $datoproximo=mysql_fetch_array($sqlproximo);
    }              
$i=0;


?>
<!DOCTYPE html>
<html lang="ES">
    <head>
        <meta charset="utf-8" />
        <title>Form Advanced | Hyper - Responsive Bootstrap 4 Admin Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">
      <!-- Firma -->

        <!-- App css -->
        <link href="../../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="../../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="../../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
        <link rel="stylesheet" href="../signature-pad/assets/jquery.signaturepad.css">

  </head>
  <script>
    Dropzone.autoDiscover = false;
  </script>
  <style>
    /* Mimic table appearance */
    div.table {
      display: table;
    }
    div.table .file-row {
      display: table-row;
    }
    div.table .file-row > div {
      display: table-cell;
      vertical-align: top;
      border-top: 1px solid #ddd;
      padding: 8px;
      background: #FFFFFF;
    }
    div.table .file-row:nth-child(odd) {
      background: #FFFFFF;
    }



    /* The total progress gets shown by event listeners */
    #total-progress {
      opacity: 0;
      transition: opacity 0.3s linear;
    }

    /* Hide the progress bar when finished */
    #previews .file-row.dz-success .progress {
      opacity: 0;
      transition: opacity 0.3s linear;
    }

    /* Hide the delete button initially */
    #previews .file-row .delete {
      display: none;
    }

    /* Hide the start and cancel buttons and show the delete button */

    #previews .file-row.dz-success .start,
    #previews .file-row.dz-success .cancel {
      display: none;
    }
    #previews .file-row.dz-success .delete {
      display: block;
    }


  </style>
  
    <body class="loading" data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false, "leftSidebarCondensed":false, "leftSidebarScrollable":false,"darkMode":false, "showRightSidebarOnStart": true}'>
        <!-- Begin page -->
        <div class="wrapper">
        <?
        include_once("../MenuSmartphone/MenuHyper.html");
        ?>

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">
                    <!-- Topbar Start -->
                    <div class="navbar-custom">
                        <ul class="list-unstyled topbar-right-menu float-right mb-0">
                        </ul>
                        <button class="button-menu-mobile open-left disable-btn">
                            <i class="mdi mdi-menu"></i>
                        </button>
                    </div>
                    <!-- end Topbar -->

                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Hyper</a></li>
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                            <li class="breadcrumb-item active">Form Advanced</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title" id="title"></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        <div class="row" id="todos">
                      <? 
                      
                      while($row=mysql_fetch_array($sql)){
                        $row[DomicilioDestino];
                          //ACA REEMPLAZAR ingBrutosOrigen por el ID DEL CLIENTE EMISOR
                        // $sqlBuscoidProveedor=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$row[ClienteDestino]' AND Relacion='$row[IngBrutosOrigen]'");
                        // $idProveedor=mysql_fetch_array($sqlBuscoidProveedor);  

                        $sqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]' ORDER BY id DESC");
                        $Seguimiento=mysql_fetch_array($sqlSeguimiento); 


                        if($row[Retirado]==0){
                          //ACA REEMPLAZAR ingBrutosOrigen por el ID DEL CLIENTE EMISOR
                        $sqlBuscoidProveedor=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$row[RazonSocial]' AND Relacion='$row[IngBrutosOrigen]'");
                        $idProveedor=mysql_fetch_array($sqlBuscoidProveedor);  
                        $Retirado=0;  
                        $Servicio='Retiro';
                        $Direccion=$row[DomicilioOrigen];
                        $NombreCliente=$row[RazonSocial];  
                          if(strlen($row[TelefonoOrigen])>='10'){
                            if(substr($row[TelefonoOrigen], 0, 2)<>'54'){
                            $Contacto='54'.$row[TelefonoOrigen];
                            }else{
                            $Contacto=$row[TelefonoOrigen];  
                            } 
                          $veocel=1;
                          }else{
                          $veocel=0;  
                          }  
                        }else{

                          $sqlBuscoidProveedor=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$row[ClienteDestino]' AND Relacion='$row[IngBrutosOrigen]'");
                          $idProveedor=mysql_fetch_array($sqlBuscoidProveedor);  
                          $Retirado=1;  
                          $Servicio='Entrega';    
                          $Direccion=$row[DomicilioDestino];
                          $NombreCliente=$row[ClienteDestino];    
                          if(strlen($row[TelefonoDestino])>='10'){
                              if(substr($row[TelefonoDestino], 0, 2)<>'54'){
                              $Contacto='54'.$row[TelefonoDestino];
                              }else{
                              $Contacto=$row[TelefonoDestino];  
                              }  
                              $veocel=1;
                              }else{
                              $veocel=0;
                            }
                          }  
                        
                      ?>
                        <div class="col-md-12">
                            <div class="card border-success border">
                                <div class="card-body">
                                  <?
                                      if($idProveedor[idProveedor]<>''){
                                      $idProveedortxt= "(".$idProveedor[idProveedor].")"; 
                                      }

                                      echo "<h4 class='card-title text-success'>($row[Posicion]) | $idProveedortxt $NombreCliente</h4>";
                                      echo "<p class='card-text'>$Direccion</p>";
                                      echo "<p class='card-text'>Remito: $row[NumeroComprobante] | $row[CodigoSeguimiento]</p>";	
                                      if($veocel==1){
                                      echo "<p class='card-text'>Celular: $Contacto <a style='float:right;margin-right:14%;' href='https://api.whatsapp.com/send?phone=$Contacto&text=Hola!,%20Mi%20nombre%20es%20$_SESSION[NombreUsuario]%20soy%20de%20Caddy%20Logística%20!%20Estoy%20en%20camino%20para%20entregarte%20tu%20pedido...'>
                                      <img id='1' src='../images/wp.png' width='30' height='30'/>$Celular[Celular]</a></p>";	
                                      }else{
                                      echo "<p class='card-text'>Celular: Sin Datos</p>";  
                                      }
                                      echo "<p class='card-text'>$Servicio de: $row[Cantidad] paquetes</p>";
                                      //-----START ASGINACIONES-----
                                      $FechaAsignacion=date('Y-m-d');
                                      $sqlasignaciones=mysql_query("SELECT * FROM Asignaciones WHERE idProveedor='$idProveedor[idProveedor]' and Relacion='$row[IngBrutosOrigen]' AND Fecha='$FechaAsignacion'");
                                      if(mysql_num_rows($sqlasignaciones)<>0){
                                        echo "<table class='rwd_auto'>";
                                        echo "<th>Nombre</th>";
                                        echo "<th>Edicion</th>";
                                        echo "<th>Cantidad</th>";
                                        while($datosasignaciones=mysql_fetch_array($sqlasignaciones)){
                                             $sqlasigproductos=mysql_query("SELECT * FROM AsignacionesProductos WHERE CodigoProducto='$datosasignaciones[CodigoProducto]' AND Relacion='$row[IngBrutosOrigen]'");
                                             $datosasigproducto=mysql_fetch_array($sqlasigproductos);
                                        echo "<tr>";
                                        echo "<td style='text-align:left'>$datosasigproducto[Nombre]</td>";
                                        echo "<td>$datosasignaciones[Edicion]</td>";
                                        echo "<td style='font-weight: bold;'>$datosasignaciones[Cantidad]</td>";
                                        echo "<tr>";
                                        }
                                        echo "</table>";
                                      //-----END ASIGNACIONES------
                                        }
                                  echo "<p class='card-text'>Observaciones:</p><textarea style='width:100%;height:100px;border:0px;background:none' type='text' readonly>$row[Observaciones]</textarea>";
//                                   echo "<p class='card-text'>$row[CodigoSeguimiento]</p>";	
//                                   echo "<input type='hidden' name='razonsocial_t' id='razonsocial_t' value='$row[NumeroComprobante]'>";
//                                   echo "<input type='hidden' name='retirado_t' id='retirado_t' value='$Retirado'>";

                                  $a=$row[id];
                                  echo "<a style='position:relative;bottom:0px;right:10px;float:left;margin-left:15%;' href='#'><img id='botonera1' src='../images/wrong.png' width='60' height='60' Onclick='verok($a,$i,this.id)' /></a>";
                                  echo "<a style='position:relative;bottom:7px;float:left;margin-left:3%;' href='http://maps.google.com/?q=$Direccion', '_system', 'location=yes');' target='_blank'><img src='../images/goto.png' width='70' height='70' /></a>";
                                  echo "<a style='float:left;margin-left:6%;' href='#'><img id='botonera2' src='../images/ok.png' width='60' height='60' Onclick='verok($a,$i,this.id)' /></a>";
                                  echo "<input type='hidden' name='id' value='$row[id]'>";
                                  echo "<input type='hidden' name='Posicion' value='$row[Posicion]'>";  
                            
                          
                                    $i++;  
                                  ?>
<!--                                   <a href="javascript: void(0);" class="btn btn-success btn-sm">Button</a> -->
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->
                      
                      <?
                      }
                      ?>
                        </div>     
                             
          <div class="row" id="okcerrar" style="display:none">
            <div class="col-md-12">                      
              <div class="card mb-md-0 mb-3">
                <div class="card-body">
                <div class="card-widgets">
                    <a href="javascript:;" data-toggle="reload"><i class="mdi mdi-refresh"></i></a>
<!--                     <a data-toggle="collapse" href="#cardCollpase1" role="button" aria-expanded="false" aria-controls="cardCollpase1"><i class="mdi mdi-minus"></i></a> -->
                    <a href="#" data-toggle="remove"><i class="mdi mdi-close"></i></a>
                </div>
                  <h5 class="card-title mb-0 text-danger">Cobrar</h5>
                    <div id="cardCollpase1" class="collapse pt-3 show">
                      <form action='' class='login' style='width:auto;height:100%;min-height:360px;margin-top:2px;margin-bottom:10px;' method='post'>
                        <h5>EL ENVIO REQUIERE GESTION DE COBRO</h5>
                          <h1 style='padding:0'><span id='domiciliocobrar'></span>
                            <h1><span id='importecobro'></span></h1>
                              <h1><span id='importecobrocaddy'></span></h1>
                                <input type='hidden' id='importecobrocliente_m'>
                                  <input type='hidden' id='importecobrocaddy_m'>
                                    <h2 style='font-weight:bold;color:red'><span id='importecobrototal'></span></h2>
                                     <div class="form-group">
                                      <label for="cobrado">Importe Cobro</label>
                                       <input type="text" id="cobrado" name="importecobrado" class="form-control" placeholder='$ Colocar aqui Importe Cobrado si difiere del total'>
                                        </div>
<!--                                           <div><input type='tel' id='cobrado' name='importecobrado' placeholder='$ Colocar aqui Importe Cobrado si difiere del total' style='width:100%;margin-top:0px' ></div> -->
                                          <input type='hidden' name='cscobro' id='cscobro' value=''>
                                           <div class="form-group">
                                            <label for="observaciones_t">Importe Cobro</label>
                                              <input type="text" name='observaciones_t' id='observaciones_t' class="form-control" value='' onKeyDown='valida_longitud()' onKeyUp='valida_longitud()' placeholder='Escribe comentarios aquí...'>
                                            </div>
                                           <div class="form-group">
                                            <label for="observaciones_t">Importe Cobro</label>
                                               <input type='email' id='emailrecibo' name='nombre' class="form-control" placeholder='Email para el recibo de pago' ></div>
                                           </div>
                                            <a id='botonfinalcobro' type="button" class="btn btn-block btn-lg btn-success" onClick='cobrofinalizado(this.value)'>Cobrado</a>
                                            <a id='botonfinalcobro1' type="button" class="btn btn-block btn-lg btn-danger" onClick='cobrofinalizado(this.value)'>No pude Cobrar</a>
                                     </form>	
                                  </div>

                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                          </div>
                      </div>
                        <div class="col-md-4" id="ok" style="display:none">
                            <div id="fondo" class="card border-success border">
                              <div class="card-body">
<!--                                   <blockquote class="card-bodyquote"> -->
<!--                                     <form action="/" method="post"  id="myAwesomeDropzone" data-plugin="dropzone" data-previews-container="#file-previews"
                                          data-upload-preview-template="#uploadPreviewTemplate" enctype='multipart/formdata'> -->
<!--                                     <form action='' method='post' enctype='multipart/formdata'> -->
                                      <h2 style='font-weight: bold;'>(<span id='posicion'></span>) <span style='text-transform: uppercase' id='nombrecliente'></span></h2>
                                      <h4><span id='domicilio'></span></h4>
<!--                                       <input type='text' class="form-control" name='caracteres' size='4' style='border:none;rigth:0px' readonly></h1> -->
                                      <input type='text' class="form-control col-md-12" id='nombre' name='nombre' placeholder='Nombre del Receptor' required>
                                      <input type='text' class="form-control col-md-12" id='dni' name='dni' placeholder='D.N.I del Receptor' >
                                      <h1 style='text-danger'><span id='importecobro'></span></h1>
                                      <input type='hidden' id='nombrecliente_t' name='nombrecliente' value=''>
                                      <input type='hidden' id='lti' name='latitud' value=''>	
                                      <input type='hidden' id='lgi' name='longitud' value=''>
                                      <input class='form-control' type='hidden' name='cs' id='cs' value=''>
                                      <input type='hidden' name='codigoseguimiento_t' id='codigoseguimiento_t' value=''>  
                                      <input type='hidden' name='entregado_t' id='entregado_t' value=''>
                                      <div id='botones' style='display:none'>
  
                                      <button id='razones1' value='NADIE EN CASA' class='btn btn-secondary col-md-12 mt-1' onClick='select(this.id)'>NADIE EN CASA</button>
                                      <button id='razones2' value='DIRECCION EQUIVOCADA' class='btn btn-secondary col-md-12 mt-1' onClick='select(this.id)'>DIRECCION EQUIVOCADA</button>
                                      <button id='razones3' value='OTRAS RAZONES' class='btn btn-secondary col-md-12 mt-1' onClick='select(this.id)'>OTRAS RAZONES</button>
                                      <input type='hidden' id='razones'>
                                      </div>
                                      <input class="form-control col-md-12 mt-1" name='observaciones_t' id='observaciones' placeholder='Escribe comentarios aquí...'>
                                      <div id='cuerpo'></div>
<!--                                       <div><a id='startbutton'  class='button-select' style='height:auto;width:180px;border-radius:1px;color:white'>Solicitar Firma</a></div> -->

                                      <div id='firma_d' style='display:none'>
                                      <label>Solicita al Cliente que firme la Recepcion aqui...</label>
                                      <div id='firma' class='sigPad' style='outline: 1px dashed #aaa;height:198px;width:100%'>
                                              <div>
                                                <div class='typed' style='display:none'></div>
                                                <canvas id='firmapad' class='pad' width='350px' height='198px' ></canvas>
                                                <input type='hidden' name='output' id='output' class='output'>
                                                <fieldset style='width:100%;height:20%;border:0'>
                                                <a class='clearButton' style='position:relative;bottom:0px;right:10px;float:left;margin-left:15%;' href='#'><img id='endbutton' src='../images/wrong.png' width='60' height='60' /></a>
                                                <a class='clearButton' style='position:relative;bottom:7px;float:left;margin-left:3%;' href='#clear'><img src='../images/goto.png' width='70' height='70' /></a>
                                                <a style='float:left;margin-left:6%;' href='#'><img src='../images/ok.png' width='60' height='60' Onclick='firmaof()' /></a>
                                                </fieldset>
                                              </div>
                                            </div>
                                        </div>
                                      <input type='hidden' id='idveo'>
<!--                                       </form> -->
<!--                                       </blockquote> -->
                            <!--NUEVO CODIGO -->
                                      <div id="actions" class="row">
                                      <div id="camfirmbut" class="col-md-12">
                                        <!-- The fileinput-button span is used to style the file input field as button -->
                                        <span class="btn btn-success fileinput-button mt-1 col-md-12">
                                            <i class="glyphicon glyphicon-plus"></i>
                                            <span>Tomar Foto</span>
                                        </span>
                                        <button id='startbutton' type="button" class="btn btn-warning cancel text-white mt-1 col-md-12">
                                        <i class="glyphicon glyphicon-ban-circle"></i>
                                        <span>Incluir Firma</span>
                                        </button>
                                      </div>

                                      <div class="col-lg-5">
                                        <!-- The global file processing state -->
                                        <span class="fileupload-process">
                                          <div id="total-progress" class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                            <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                                          </div>
                                        </span>
                                      </div>

                                    </div>
                                     <div class="table table-striped files" id="previews">
                                      <div id="template" class="file-row">
                                        <!-- This is used as the file preview template -->
                                        <div>
                                            <span class="preview"><img data-dz-thumbnail /></span>
                                        </div>
                                        <div>
                                            <p class="name" data-dz-name></p>
                                            <strong class="error text-danger" data-dz-errormessage></strong>
                                        </div>
                                        <div>
                                            <p class="size" data-dz-size></p>
                                            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                              <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                                            </div>
                                        </div>
                                        <div>
                                          <button class="btn btn-primary start">
                                              <i class="glyphicon glyphicon-upload"></i>
                                              <span>Start</span>
                                          </button>
                                          <button data-dz-remove class="btn btn-warning cancel">
                                              <i class="glyphicon glyphicon-ban-circle"></i>
                                              <span>Cancel</span>
                                          </button>
                                          <button data-dz-remove class="btn btn-danger delete">
                                            <i class="glyphicon glyphicon-trash"></i>
                                            <span>Delete</span>
                                          </button>
                                        </div>
                                      </div>
                                    
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                          <input id='botonfinal' class="btn btn-primary" type='button' value='MARCAR COMO FINALIZADO' onClick='finalizado()'>

                            </div> <!-- end col-->

                    </div> <!-- container -->
                </div> <!-- content -->

                <!-- Footer Start -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <script>document.write(new Date().getFullYear())</script> © Hyper - Coderthemes.com
                            </div>
                            <div class="col-md-6">
                                <div class="text-md-right footer-links d-none d-md-block">
                                    <a href="javascript: void(0);">About</a>
                                    <a href="javascript: void(0);">Support</a>
                                    <a href="javascript: void(0);">Contact Us</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- end Footer -->


            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->


        </div>
        <!-- END wrapper -->

        <!-- bundle -->
        <script src="../../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/app.min.js"></script>

        <!-- Typehead -->
        <script src="../../hyper/dist/saas/assets/js/vendor/handlebars.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/typeahead.bundle.min.js"></script>
        <!-- Funciones -->
        <script src="../MenuSmartphone/Procesos/js/funciones.js"></script>
        <script src="Procesos/js/funciones.js"></script>

        <!-- Demo -->
        <script src="../../hyper/dist/saas/assets/js/pages/demo.typehead.js"></script>

        <!-- plugin js -->
        <script src="../../hyper/dist/saas/assets/js/vendor/dropzone.min.js"></script>
        <!-- init js -->
        <script src="../../hyper/dist/saas/assets/js/ui/component.fileupload.js"></script>
<!-- NUEVO CODIGO -->
        <!--Procesos -->
           <script src="Procesos/js/dropzone.js"></script>
<!--           FIRMA -->
        <script src="../signature-pad/jquery.signaturepad.js"></script>
        <script>
          $(document).ready(function() {
            $('.sigPad').signaturePad({drawOnly:true});
          });
      </script>
<!--      <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.6.1.min.js"></script> -->
<!-- HASTA ACA FIRMA -->
    </body>
</html>
