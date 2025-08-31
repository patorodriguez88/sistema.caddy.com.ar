<?php
include('dbconect.php');
 
if (isset($_POST['submit']))
{
     $total=0;
    $fileMimes = array(
        'text/x-comma-separated-values',
        'text/comma-separated-values',
        'application/octet-stream',
        'application/vnd.ms-excel',
        'application/x-csv',
        'text/x-csv',
        'text/csv',
        'application/csv',
        'application/excel',
        'application/vnd.msexcel',
        'text/plain'
    );
 
    // Validate selected file is a CSV file or not
    if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $fileMimes))
    {
        $_SESSION[Relacion]=$_POST[relacion_nc];
        $Relacion=$_SESSION[Relacion];

        // Open uploaded CSV file with read-only mode
        $csvFile = fopen($_FILES['file']['tmp_name'], 'r');

        // Skip the first line
        // fgetcsv($csvFile);

        // Parse data from CSV file line by line        
        while (($getData = fgetcsv($csvFile, 10000, ";")) !== FALSE)
        {
            // Get row data
            $idProveedor = $getData[0];
            $Cantidad = $getData[1];            
            $Importe= $getData[2];
            $Relacion='36';//DINTER S.A.
            $Recorrido='1135';//PAGINA 12
            // If user already exists in the database with the same email
            // $query = "SELECT idProveedor FROM Clientes WHERE idProveedoremail = '" . $getData[1] . "'";
            $query="SELECT * FROM Clientes WHERE idProveedor='$idProveedor' AND Relacion='$Relacion'";

            $check = mysqli_query($con, $query);

            if ($check)
            {
                $row = mysqli_fetch_array($check);
                    $NCliente=$row['NdeCliente'];
                    $nombrecliente=$row['nombrecliente'];
                    $DocumentoNacional=$row['DocumentoNacional'];
                    $Mail=$row['Mail'];
                    $Ciudad=$row['Ciudad'];
                    $Provincia=$row['Provincia'];
                    $CodigoPostal=$row['CodigoPostal'];
                    $Telefono=$row['Telefono'];
                    $Celular2=$row['Celular2'];
                    $Celular=$row['Celular'];
                    $Direccion=$row['Direccion'];
                    $Observaciones=$row['Observaciones'];
                    $Relacion=$row['Relacion'];
                    $PisoDepto=$row['PisoDepto'];
                    $idProveedor=$row['idProveedor'];
                    $Contacto=$row['Contacto'];
                    $latitud=$row['Latitud'];
                    $longitud=$row['Longitud'];
                    $Km=$row['Km'];
                    // $Recorrido=$row['Recorrido'];

                       $query ="INSERT INTO Clientes_importacion(`NdeCliente`,`nombrecliente`,`DocumentoNacional`,`Mail`,`Ciudad`,`Provincia`,`CodigoPostal`,`Telefono`,`Celular2`,`Celular`,`Direccion`,`Observaciones`,`Relacion`,`PisoDepto`,`idProveedor`,`Contacto`,`Latitud`,`Longitud`,`Cantidad`,`Precio`,`Km`,`Recorrido`)  
                       VALUES('".$NCliente."','".$nombrecliente."','".$DocumentoNacional."','".$Mail."','".$Ciudad."','".$Provincia."','".$CodigoPostal."','".$Telefono."','".$Celular2."','".$Celular."','".$Direccion."','".$Observaciones."','".$Relacion."','".$PisoDepto."','".$idProveedor."','".$Contacto."','".$latitud."','".$longitud."','".$Cantidad."','".$Importe."','".$Km."','.$Recorrido.')";                 
                
                   $resultados = mysqli_query($con, $query);
                   $total=$total+1;
                


                // mysqli_query($con, "UPDATE users SET name = '" . $name . "', created_at = NOW() WHERE email = '" . $email . "'");
            }
            else
            {
                $total=$total+1;
                // mysqli_query($con, "INSERT INTO users (name, email, created_at, updated_at) VALUES ('" . $name . "', '" . $email . "', NOW(), NOW())");
            }
        }

        // Close opened CSV file
        fclose($csvFile);

        header("Location: index_3.php?sufi".date('H:m:i'));         
    }
    else
    {
        echo "Please select valid file";
    }
}

?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Importaciones</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta content="Coderthemes" name="author" />
        <!-- App favicon -->
<!--         <link rel="shortcut icon" href="assets/images/favicon.ico"> -->
        <!-- third party css -->
        <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
        <!-- third party css end -->
        <!-- App css -->
        <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
        <link href="assets/sticky-footer-navbar.css" rel="stylesheet">
        <link href="assets/style.css" rel="stylesheet">
        <!-- <link href="../hyper/dist/saas/assets/css/bootstrap.min.css" rel="stylesheet"> -->

    </head>
    <body class="loading" data-layout="topnav" data-layout-config='{layoutBoxed":false,"darkMode":false}' >
      <!-- Begin page -->


        <div class="wrapper">
            <div class="content-page">
                <div class="content">
                    <!-- Topbar Start -->
                    <div class="navbar-custom topnav-navbar" style="z-index:10">
                        <div class="container-fluid">
                            <?
                            include_once("../Menu/MenuHyper_topnav.html");
                            ?>
                        </div>
                    </div>
                    <!-- end Topbar -->
                         <div class="topnav">
                        <div class="container-fluid">
                            <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                                <div class="collapse navbar-collapse" id="topnav-menu-content">
                                  <?
                                  include_once("../Menu/MenuHyper.html");
                                  ?>
                                </div>
                            </nav>
                        </div>
                    </div>
<!--                   WARNING MODAL -->
                  <div id="warning-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header modal-colored-header bg-warning">
                                <h4 class="modal-title" id="warning-modal-title"><i class="mdi mdi-trash-can-outline"></i> Confirmar Eliminar Registro <a id="id_eliminar"></a></h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div id="warning-modal-body" class="modal-body">
                               
                              </div>
                              <div class="modal-footer">
                                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                                  <button id="warning-modal-ok"type="button" class="btn btn-danger">Eliminar</button>
                              </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
<!--                   IMPORTAR MODAL -->
                  <div id="importar-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header modal-colored-header bg-warning">
                                <h4 class="modal-title" id="warning-modal-title"><i class="mdi mdi-application-import"></i> Confirmar Importar Registro <a id="id_eliminar"></a></h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div id="importar-modal-body" class="modal-body">
                               
                              </div>
                              <div class="modal-footer">
                                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                                  <button id="importar-modal-ok"type="button" class="btn btn-danger">Aceptar</button>
                              </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
                  
                    <div id="info-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                    <i class="dripicons-information h1 text-info"></i>
                                    <h4 class="mt-2">Estamos subiendo el Excel !</h4>
                                    <p id="info-alert-body" class="mt-3"> No cierres esta ventana. </p>
                                    <div class="spinner-grow text-primary" role="status"></div>
<!--                                   <button type="button" class="btn btn-info my-2" data-dismiss="modal">Continue</button> -->
                                  </div>
                              </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
              <!-- Begin page content -->
              <div id="success-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                  <div class="modal-dialog modal-sm">
                      <div class="modal-content modal-filled bg-success">
                          <div class="modal-body p-4">
                              <div class="text-center">
                                  <i class="dripicons-checkmark h1"></i>
                                  <h4 class="mt-2">Importacion Exitosa !</h4>
                                  <p id="success-info" class="mt-3"></p>
                                  <button type="button" class="btn btn-light my-2" data-dismiss="modal">Continue</button>
                              </div>
                          </div>
                      </div><!-- /.modal-content -->
                  </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
              <style>
              .modal{
              z-index: 20;   
              }
              .modal-backdrop{
              z-index: 10;        
              }
              </style>
                <!-- //MODIFICAR DIRECCION -->
                  <div class="modal fade" id="standard-modal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header">
                                  <h4 class="modal-title" id="myCenterModalLabel">Actualizar Cliente</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div class="modal-body">
                               <div class="col-lg-12 mt-3">
                                 <label>Direccion</label>
                                  <input type='text' class="form-control" name='direccion_nc' id='direccion_nc' placeholder='Direccion: Calle Numero'>                                          
                                  <input type='hidden' name='Calle_nc'  id='Calle_nc'>
                                  <input type='hidden' name='Barrio_nc' id='Barrio_nc'>
                                  <input type='hidden' name='Numero_nc' id='Numero_nc'>
                                  <input type='hidden' name='ciudad_nc' id='ciudad_nc'>
                                  <input type='hidden' name='cp_nc'     id='cp_nc'>
                                  <input type='hidden' name='id_nc'     id='id_nc'>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                <button id="modificardireccion_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
                  
                  
                  <div class="container">
                    <h3 class="mt-5">IMPORTACION PAGINA 12</h3>
                    <hr>
                    <div class="row">
                      <div class="col-12 col-md-12"> 
                        <div class="card">
                        <div class="card-body" id="outer">
                            <div class="col-12 col-md-12">
                              <div class="form-group">
                                <label>Archivo</label>
                                <p class="text-muted font-13">
                                    Para importar archivos de CSV a la base de datos del sistema, deberas realizarlo con un archivo compatible. 
                                </p>
                              </div>
                            </div>


                          <form class="col-12 col-md-12" action="" method="post" name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data">
                              <div class="col-lg-12 mt-2 mb-2">
                                <p>Cliente: DINTER S.A. [36]</p> 
                                <p>Producto: DIARIO PAGINA 12</p>
                                <p>Recorrido: RECORRIDO 1135 [Recorrido Pagina 12]</p>
                                  
                              </div>

                              <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="customFileInput" name="file">

                                    <label class="custom-file-label" for="customFileInput" value="<? echo $_FILES['file']['name'];?>" disabled>Seleccione un Archivo</label>
                                </div>
                              <div class="input-group-append">
                                <input type="submit" name="submit" value="upload" class="btn btn-primary">
                            </div>
                        </form>

                      </div>
                        </div>
                      </div>
                      <div class="col-lg-12 mt-2 mb-2">
                      <div id="response" class="<?php if(!empty($type)) { echo $type . " display-block"; } ?>"><?php if(!empty($message)) { echo $message; } ?></div>
                      <input id="totalregistros" type="hidden" value="<? echo $total;?>">
                      </div>
                    </div>
                      <div class="row" id="card" style="display:none">
                        <div class="col-md-4 mt-2">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                      <h3 class="card-title" id="clientesnuevos_card"></h3> 
                                      <blockquote  class="card-bodyquote">
                                            <p>Estos clientes serán cargados a la tabla Clientes.</p>
                                        </blockquote>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        
                            <div class="col-md-5 mt-2">
                                <div class="card text-white bg-danger">
                                    <div class="card-body">
                                      <h3 class="card-title" id="clientesexistentes_card"></h3>    
                                      <blockquote  class="card-bodyquote">
                                            <p>Estos clientes No se cargaran en la tabla Clientes, ya que generarian registros duplicados.</p>
                                        </blockquote>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        <div class="col-md-3 mt-2">
                                <div class="card text-white bg-warning">
                                    <div class="card-body">
                                      <h3 class="card-title" id="ventas_card"></h3>    
                                      <blockquote  class="card-bodyquote">
                                            <p>Estos registros seran ingresados en tabla Pre Venta.</p>
                                        </blockquote>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div>
                        <!-- end row -->
                        
                        <?php
                    $sqlSelect = "SELECT * FROM Clientes_importacion ORDER BY id ";
                    $result = mysqli_query($con, $sqlSelect);
                    if (mysqli_num_rows($result) > 0)
                    {
                    ?>
                        <div id="tabla">
                           <div class="row">
                                <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                  <div class="card">
                                      <div class="card-body">
                                          <h4 id="seguimiento_header" class="header-title mt-2">ENTREGAS | RECORRIDO </h4>
                                          <table class="table table-centered mb-0" id="seguimiento" style="font-size:12px">
                                            <thead>
                                                <tr>
                                                  <th>Datos Importacion</th>
                                                  <th>Cantidad</th>
                                                  <th>Estado</th>
                                                  <th>Accion</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                    </div>
                                    <div class="tab-content mb-2">
                                        <div class="tab-pane show active" id="default-buttons-preview">
                                            <div class="button-list">
                                                <a id="VaciarTabla" class="btn btn-danger">Vaciar Tabla</a> 
                                                <a id="ImportarTabla" class="btn btn-success text-white">Confirmar Importacion</a>   
                                            </div>
                                        </div>
                                    </div>
                                
                           </div>
                      </div>
                    </div>  
                     <!-- Start Content-->
                    
               <?php 
                } 
             
      ?>
      <!-- Fin Contenido --> 
    </div>
  </div>
  <!-- Fin row --> 

  
</div>
<!-- Fin container -->
    <!-- END wrapper -->
        <!-- bundle -->
        <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/app.min.js"></script>

        <!-- third party js -->
        <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.keyTable.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script>
<!--         <script src="https://editor.datatables.net/extensions/Editor/js/dataTables.editor.min.js"></script> -->
        <!-- third party js ends -->
        <!-- end demo js-->
        <!-- funciones -->
<!--         <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
        <script src="Procesos/js/funciones.js"></script>
<!--         <script src="../Google/geolocalizar.js"></script> -->
<!--         <script src="../Funciones/js/seguimiento.js"></script> -->
        <script src="../Menu/js/funciones.js"></script>
                            <!-- Direcciones -->
        <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&libraries=places&callback=BuscarDireccion">
        </script>

  </body>
</html>


              