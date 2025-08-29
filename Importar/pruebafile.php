<?php
include('dbconect.php');
require_once('vendor/php-excel-reader/excel_reader2.php');
require_once('vendor/SpreadsheetReader.php');

?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Importaciones</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--         <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" /> -->
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
<!--         <link href="../hyper/dist/saas/assets/css/bootstrap.min.css" rel="stylesheet"> -->

    </head>
    <body class="loading" data-layout="topnav" data-layout-config='{layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}' >
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
                    <div class="container">


        <table class="table table-centered mb-0" style="font-size:8px">
        <thead>
            <tr>                
                  
                <th>Nombre Cliente</th>
                <th>Documento</th>
                <th>Mail</th>
                <th>Domicilio</th>
                <th>Piso</th>
                <th>Ciudad</th>
                <th>Provincia</th>
                <th>C.P.</th>
                <th>Telefono</th>
                <th>Celular</th>
                <th>Celular</th>
                <th>Observaciones</th>
                <th>Vendedor</th>
                <th>Contacto</th>
                <th>Paquete</th>
                <th>Precio</th>
                <th>Importe</th>
                <th>Recorrido</th>
            
            </tr>
        </thead>
        <tbody>
            

        <?
        if (isset($_POST["import"]))
        {
            
        $allowedFileType = ['application/vnd.ms-excel','text/xls','text/xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
          
          if(in_array($_FILES["file"]["type"],$allowedFileType)){
        
                $targetPath = 'subidas/'.$_FILES['file']['name'];
                move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);
                
                $Reader = new SpreadsheetReader($targetPath);
                
                $sheetCount = count($Reader->sheets());
        
        
        for($i=0;$i<$sheetCount;$i++)
        {
            $Reader->ChangeSheet($i);
            
            foreach ($Reader as $Row)
            {
          
                $nombrecliente = "";
                if(isset($Row[0])) {
                    $nombrecliente = mysqli_real_escape_string($con,$Row[0]);
                }
                
                $DocumentoNacional = "";
                if(isset($Row[1])) {
                    $DocumentoNacional = mysqli_real_escape_string($con,$Row[1]);
                }
				
                $Mail = "";
                if(isset($Row[2])) {
                    $Mail = mysqli_real_escape_string($con,$Row[2]);
                }
				
                $Direccion = "";
                if(isset($Row[3])) {
                    $Direccion	= mysqli_real_escape_string($con,$Row[3]);
                }
              
                $PisoDepto = "";
                if(isset($Row[4])) {
                    $PisoDepto	= mysqli_real_escape_string($con,$Row[4]);
                }

                $Ciudad = "";
                if(isset($Row[5])) {
                    $Ciudad	= mysqli_real_escape_string($con,$Row[5]);
                }
                $Provincia = "";
                if(isset($Row[6])) {
                    $Provincia	= mysqli_real_escape_string($con,$Row[6]);
                }

                $CodigoPostal = "";
                if(isset($Row[7])) {
                    $CodigoPostal	= mysqli_real_escape_string($con,$Row[7]);
                }

                $Telefono = "";
                if(isset($Row[8])) {
                    $Telefono	= mysqli_real_escape_string($con,$Row[8]);
                }

                $Celular2 = "";
                if(isset($Row[9])) {
                    $Celular2	= mysqli_real_escape_string($con,$Row[9]);
                }

                $Celular = "";
                if(isset($Row[10])) {
                    $Celular	= mysqli_real_escape_string($con,$Row[10]);
                }

                $Observaciones = "";
                if(isset($Row[11])) {
                    $Observaciones	= mysqli_real_escape_string($con,$Row[11]);
                }

                $idProveedor = "";
                if(isset($Row[12])) {
                    $idProveedor	= mysqli_real_escape_string($con,$Row[12]);
                }

                $Contacto = "";
                if(isset($Row[13])) {
                    $Contacto	= mysqli_real_escape_string($con,$Row[13]);
                }
                ?>
                
                <tr  style="font-size:7px">   
                <!-- <tbody> -->
                <td><?php   echo $Row[0]; ?></td>
                <td><?php   echo $Row[1]; ?></td>
                <td><?php   echo $Row[2]; ?></td>
                <td><?php   echo $Row[3]; ?></td>
                <td><?php   echo $Row[4]; ?></td>
                <td><?php   echo $Row[5]; ?></td>    
                <td><?php   echo $Row[6]; ?></td>    
                <td><?php   echo $Row[7]; ?></td> 
                <td><?php   echo $Row[8]; ?></td> 
                <td><?php   echo $Row[9]; ?></td> 
                <td><?php   echo $Row[10]; ?></td> 
                <td><?php   echo $Row[11]; ?></td> 
                <td><?php   echo $Row[12]; ?></td> 
                <td><?php   echo $Row[13]; ?></td> 
                <td><?php   echo $Row[14]; ?></td> 
                <td><?php   echo $Row[15]; ?></td> 
                <td><?php   echo $Row[16]; ?></td> 
                <td><?php   echo $Row[17]; ?></td> 
                </tr>   

            <?
                // if (!empty($nombres) || !empty($cargo) || !empty($celular) || !empty($descripcion)) {
                  
                //     $query = "insert into tbl_productos(nombres,cargo, celular, descripcion) values('".$nombres."','".$cargo."','".$celular."','".$descripcion."')";
                //     $resultados = mysqli_query($con, $query);
                
                //     if (! empty($resultados)) {
                //         $type = "success";
                //         $message = "Excel importado correctamente";
                //     } else {
                //         $type = "error";
                //         $message = "Hubo un problema al importar registros";
                //     }
                // }

             }
             ?>
             <?php  
            //  echo $Row[$i]; 
             ?>
                
                </tbody>

             </table>
             <?
        
         }
   unset($_POST["import"]);
  }
  else
  { 
        $type = "error";
        $message = "El archivo enviado es invalido. Por favor vuelva a intentarlo";
  }
}
?>
<!-- <!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">
<link rel="icon" href="favicon.ico">
<title>Importar archivo de Excel a MySQL usando PHP - BaulPHP</title>

Bootstrap core CSS
<!-- <link href="dist/css/bootstrap.min.css" rel="stylesheet"> -->
<!-- Custom styles for this template -->
<!-- <link href="assets/sticky-footer-navbar.css" rel="stylesheet"> -->
<!-- <link href="assets/style.css" rel="stylesheet"> -->

<!-- </head>  -->

<body>
<!-- <header>  -->
  <!-- Fixed navbar -->
  <!-- <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark"> <a class="navbar-brand" href="#">BaulPHP</a> -->
    <!-- <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button> -->
    <!-- <div class="collapse navbar-collapse" id="navbarCollapse"> -->
      <!-- <ul class="navbar-nav mr-auto"> -->
        <!-- <li class="nav-item active"> <a class="nav-link" href="index.php">Inicio <span class="sr-only">(current)</span></a> </li> -->
      <!-- </ul> -->
      <!-- <form class="form-inline mt-2 mt-md-0"> -->
        <!-- <input class="form-control mr-sm-2" type="text" placeholder="Buscar" aria-label="Search"> -->
        <!-- <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Busqueda</button> -->
      <!-- </form> -->
    <!-- </div> -->
  <!-- </nav> -->
<!-- </header> -->

<!-- Begin page content -->





<div class="container">
  <h3 class="mt-5">Importar archivo de Excel a MySQL usando PHP</h3>
  <hr>
  <div class="row">
    <div class="col-12 col-md-12"> 
      <!-- Contenido -->

    <div class="outer-container">
        <form action="" method="post" name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data">
            <div>
                <label>Elija Archivo Excel</label> <input type="file" name="file" id="file" accept=".xls,.xlsx">
                <button type="submit" id="submit" name="import" class="btn-submit">Importar Registros</button>
        
            </div>
        
        </form>
        
    </div>
    <div id="response" class="<?php if(!empty($type)) { echo $type . " display-block"; } ?>"><?php if(!empty($message)) { echo $message; } ?></div>
    
         
<?php
    $sqlSelect = "SELECT * FROM Clientes_importacion";
    $result = mysqli_query($con, $sqlSelect);

if (mysqli_num_rows($result) > 0)
{
?>
        
    <table class='tutorial-table'>
        <thead>
            <tr>                
                <th>Nombres</th>
                <th>Cargo</th>
                <th>Celular</th>
                <th>Descripcion</th>

            </tr>
        </thead>
<?php
    while ($row = mysqli_fetch_array($result)) {
?>                  
        <tbody>
        <tr>
            <td><?php  echo $row['NombreCliente']; ?></td>
            <td><?php  echo $row['DocumentoNacional']; ?></td>
            <td><?php  echo $row['Mail']; ?></td>
            <td><?php  echo $row['Ciudad']; ?></td>
        </tr>
<?php
    }
?>
        </tbody>
    </table>
<?php 
} 
?>
      <!-- Fin Contenido --> 
    </div>
  </div>
  <!-- Fin row --> 

  
</div>
<!-- Fin container -->
<footer class="footer">
  <div class="container"> <span class="text-muted">
    <p>Códigos <a href="https://www.baulphp.com/importar-archivo-de-excel-a-mysql-usando-php" target="_blank">BaulPHP</a></p>
    </span> </div>
</footer>
<script src="assets/jquery-1.12.4-jquery.min.js"></script> 

<!-- Bootstrap core JavaScript
    ================================================== --> 
<!-- Placed at the end of the document so the pages load faster --> 

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
        <script src="../Menu/js/funciones.js"></script>


<!-- <script src="dist/js/bootstrap.min.js"></script> -->
</body>
</html>