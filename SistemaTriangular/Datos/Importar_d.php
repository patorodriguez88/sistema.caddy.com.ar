<?php
session_start();
date_default_timezone_set('America/Argentina/Buenos_Aires');
// include_once "../Conexion/Conexion.php";
$db_server   = 'localhost';
$db_name     = 'dinter6_triangularcopia';
$db_username = 'dinter6_usuarioweb';
$db_password = 'usuarioelectronico'; 
$db_connection_charset = 'utf8';
$filename           = '';     // Specify the dump filename to suppress the file selection dialog
$ajax               = false;   // AJAX mode: import will be done without refreshing the website
$linespersession    = 3000;   // Lines to be executed per one import session
$delaypersession    = 0; 
$csv_insert_table   = 'PreVenta';     // Destination table for CSV files
$csv_preempty_table = false;  // true: delete all entries from table specified in $csv_insert_table before processing
$csv_delimiter      = ';';    // Field delimiter in CSV file
$csv_add_quotes     = true;   // If your CSV data already have quotes around each field set it to false
$csv_add_slashes    = true;   // If your CSV data already have slashes in front of ' and " set it to false
$comment[]='#';                       // Standard comment lines are dropped by default
$comment[]='-- ';
$comment[]='DELIMITER';               // Ignore DELIMITER switch as it's not a valid SQL statement
$comment[]='/*!';                     // Or add your own string to leave out other proprietary things
$delimiter = ';';
$string_quotes = '"';                  // Change to '"' if your dump file uses double qoutes for strings
$max_query_lines = 2000;// How many lines may be considered to be one query (except text lines)
$upload_dir = dirname(__FILE__);// Where to put the upload files into (default: bigdump folder)

if ($ajax)
ob_start();
define ('VERSION','0.35b');
define ('DATA_CHUNK_LENGTH',16384);  // How many chars are read per time
define ('TESTMODE',false);           // Set to true to process the file without actually accessing the database
define ('BIGDUMP_DIR',dirname(__FILE__));
define ('PLUGIN_DIR',BIGDUMP_DIR.'/plugins/');

header("Expires: Mon, 1 Dec 2003 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// header("Pragma: no-cache");

// @ini_set('auto_detect_line_endings', true);
// @set_time_limit(0);

if (function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get"))
  @date_default_timezone_set(@date_default_timezone_get());

// Clean and strip anything we don't want from user's input [0.27b]

foreach ($_REQUEST as $key => $val) 
  {
    $val = preg_replace("/[^_A-Za-z0-9-\.&= ;\$]/i",'', $val);
    $_REQUEST[$key] = $val;
  }
// print $_POST[cargar];
  if($_POST[cargar]=='Aceptar'){
   
    $dato=explode(',',$_POST[imp_razonsocial],2);
    $_SESSION[imp_razonsocial]=$dato[1];//$RazonSocial='DINTERSA S.A. CBA'  
    $_SESSION[imp_ncliente]=$dato[0];//$NCliente='36';
    $_SESSION[imp_fecha]=$_POST[fecha_i];
}  
    
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Importaciones</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
      
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

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
    </head>

    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": false}'>
        <!-- Begin page -->
        <div class="wrapper">

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

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
                  
                    <!-- Start Content-->
                    <div class="container-fluid">
                      <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Importación</a></li>
                                            <li class="breadcrumb-item active">Importación</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Fecha <script>document.write(new Date().getUTCDate()+'.'+(new Date().getUTCMonth()+1)+'.'+new Date().getUTCFullYear())</script></h4>
                                </div>
                            </div>
                        </div>     
                     
                            <div class="row">
                              <div class="card col-12 mx-auto">
                                <div class="card-body">
                                  <h4 class="header-title">Datos de Importación</h4>
                                  <p class="text-muted font-14">Seleccione un cliente que da origen a la importación.</p>   
                                    <!-- SELECCIONE UN CLIENTE QUE DA ORIGEN A LA IMPORTACION -->
                                    <form name="" method="POST">        
                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label id="cliente_existe">Cliente</label>
                                                    <?php    
                                                    // $dbconnection = @mysql_connect($db_server,$db_username,$db_password);
                                                    $mysqli = new mysqli($db_server,$db_username,$db_password,$db_name);
                                                    mysqli_set_charset($mysqli,"utf8"); 
                                                    
                                                    if ($mysqli) 
                                                    // $db = mysql_select_db($db_name);
                                                    $sql=$mysqli->query("SELECT id,nombrecliente FROM Clientes");
                                                    ?>
                                                    <select name="imp_razonsocial" class="form-control select2" data-toggle="select2">
                                                    <optgroup label="Seleccione un Cliente">

                                                    <?
                                                    while($row=$sql->fetch_array(MYSQLI_ASSOC)){    
                                                        
                                                    echo "<option value='$row[id],$row[nombrecliente]'>".$row['nombrecliente']."</option>";
                                                    
                                                    }
                                                    ?>
                                                    </optgroup>
                                                    </select>
                                                    </div>
                                                </div>

                                          <div class="col-3">
                                            <div class="form-group">
                                                <label>Fecha</label>
                                                <input type="date" name="fecha_i" class="form-control select2">
                                            </div>  
                                        </div>  
                                     

                                    <div class="col-3 mt-3">
                                      <div class="form-group" >
                                        <label></label>
                                        
                                        <button  type="submit" name="cargar" id="cargar" value="Aceptar" class="btn btn-primary float-right">Aceptar</button>
                                        </form>
                                    </div>
                                    </div>
                                    </div>



                                    <div class="row">
                                        <div class="col-12 mx-auto mt-3">
                                        <!-- <div class="card-body"> -->
                                            <h4 class="header-title">Agregar Archivos</h4>
                                            <p class="text-muted font-14">Seleccione archivos para la importacion.</p>   
                                                <div class="form-group">
                                                <form method="POST" action="<?php echo ($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                                                <div class="row"> 
                                                    <div class="col-10">
                                                    <input type="hidden" name="MAX_FILE_SIZE" value="$upload_max_filesize">
                                                        <label>Seleccione el archivo:  </label>
                                                        <input type="file" name="dumpfile" accept="*/*" size="60">
                                                    </div>
                                                </div>
                                                    <div class="row">
                                                    <div class="col-12">
                                                    <input class="btn btn-primary float-right" type="submit" name="uploadbutton" value="Upload">
                                                    </div>
                                                    </div>
                                                </form>
                                                <!-- </div> -->
                                            </div>
                                        <!-- </div> -->





                                  <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                              <table class="table table-sm table-centered mb-0">
                                                <?php
                                              function skin_open() 
                                              {
                                                echo ('<table class="table table-sm table-centered mb-0">');
                                              }

                                              function skin_close() 
                                              {
                                                echo ('</table>');
                                              }

                                              skin_open();
                                              echo ('<caption>Archivos subidos al Servidor</caption>');
                                              skin_close();

                                              do_action('after_headline');

                                              $error = false;
                                              $file  = false;

                                              // Calculate PHP max upload size (handle settings like 10M or 100K)

                                              if (!$error)
                                              { $upload_max_filesize=ini_get("upload_max_filesize");
                                                if (preg_match("/([0-9]+)K/i",$upload_max_filesize,$tempregs)) $upload_max_filesize=$tempregs[1]*1024;
                                                if (preg_match("/([0-9]+)M/i",$upload_max_filesize,$tempregs)) $upload_max_filesize=$tempregs[1]*1024*1024;
                                                if (preg_match("/([0-9]+)G/i",$upload_max_filesize,$tempregs)) $upload_max_filesize=$tempregs[1]*1024*1024*1024;
                                              }
                                                
                                              do_action ('script_runs');

                                              // Handle file upload

                                              if (!$error && isset($_REQUEST["uploadbutton"]))
                                              { if (is_uploaded_file($_FILES["dumpfile"]["tmp_name"]) && ($_FILES["dumpfile"]["error"])==0)
                                                { 
                                                  $uploaded_filename=str_replace(" ","_",$_FILES["dumpfile"]["name"]);
                                                  $uploaded_filename=preg_replace("/[^_A-Za-z0-9-\.]/i",'',$uploaded_filename);
                                                  $uploaded_filepath=str_replace("\\","/",$upload_dir."/".$uploaded_filename);

                                                  do_action('file_uploaded');

                                                  if (file_exists($uploaded_filename))
                                                  { echo (
                                                    
                                                    "<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> El archivo $uploaded_filename ya existe! Borrelo e intente nuevamente!</div>");

                                                  }
                                                  else if (!preg_match("/(\.(sql|gz|csv))$/i",$uploaded_filename))
                                                   {echo ('<div class="alert alert-danger" role="alert"><i class="dripicons-wrong mr-2"></i> 1. Solamente puede subir archivos con extension .sql .gz or .csv </p></div>');


                                                  // { echo ("<p class=\"error\" style='margin-left:20px'>1. Solamente puede subir archivos con extension .sql .gz or .csv </p>\n");
                                                  }
                                                  else if (!@move_uploaded_file($_FILES["dumpfile"]["tmp_name"],$uploaded_filepath))
                                                  { echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> Error moving uploaded file ".$_FILES["dumpfile"]["tmp_name"]." to the $uploaded_filepath</div>");
                                                    echo ("<p>Verifique los permisos para subir archivos a $upload_dir (must be 777)!</p>\n");
                                                  }
                                                  else
                                                  { 
                                                    
                                                    echo ("<div class='alert alert-success' role='alert'><i class='dripicons-success mr-2'></i> Subio con exito el archivo y fue guardado en: $uploaded_filename</div>");
                                                    
                                                  }
                                                }
                                                else
                                                { echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> Error al subir el archivo  ".$_FILES["dumpfile"]["name"]."</div>");
                                                }
                                              }


                                              // Handle file deletion (delete only in the current directory for security reasons)

                                              if (!$error && isset($_REQUEST["delete"]) && $_REQUEST["delete"]!=basename($_SERVER["SCRIPT_FILENAME"]))
                                              { if (preg_match("/(\.(sql|gz|csv))$/i",$_REQUEST["delete"]) && @unlink($upload_dir.'/'.$_REQUEST["delete"])) 
                                                  echo ("<div class='alert alert-success' role='alert'><i class='dripicons-warning mr-2'></i> ".$_REQUEST["delete"]." fue removido exitosamente</div>");
                                                else
                                                  echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> No se puede borrar el archivo ".$_REQUEST["delete"]."</div>");
                                              }

                                              // Connect to the database, set charset and execute pre-queries

                                              if (!$error && !TESTMODE)
                                              { $dbconnection = @mysql_connect($db_server,$db_username,$db_password);
                                                if ($dbconnection) 
                                                  $db = mysql_select_db($db_name);
                                                if (!$dbconnection || !$db) 
                                                { echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> Fallo la conexion a la base de datos due to ".mysql_error()."</div>");
                                                  echo ("<p>Edit the database settings in BigDump configuration, or contact your database provider.</p>");
                                                  $error=true;
                                                }
                                                if (!$error && $db_connection_charset!=='')
                                                  @mysql_query("SET NAMES $db_connection_charset", $dbconnection);

                                                if (!$error && isset ($pre_query) && sizeof ($pre_query)>0)
                                                { reset($pre_query);
                                                  foreach ($pre_query as $pre_query_value)
                                                  {	if (!@mysql_query($pre_query_value, $dbconnection))
                                                    { echo ("<p class=\"error\">Error with pre-query.</p>\n");
                                                      echo ("<p>Query: ".trim(nl2br(htmlentities($pre_query_value)))."</p>\n");
                                                      echo ("<p>MySQL: ".mysql_error()."</p>\n");
                                                      $error=true;
                                                      break;
                                                  }
                                                  }
                                                }
                                              }
                                              else
                                              { $dbconnection = false;
                                              }

                                              do_action('database_connected');

                                              // List uploaded files in multifile mode

                                              if (!$error && !isset($_REQUEST["fn"]) && $filename=="")
                                              { if ($dirhandle = opendir($upload_dir)) 
                                                { 
                                                  $files=array();
                                                  while (false !== ($files[] = readdir($dirhandle)));
                                                  closedir($dirhandle);
                                                  $dirhead=false;

                                                  if (sizeof($files)>0)
                                                  { 
                                                    sort($files);
                                                    foreach ($files as $dirfile)
                                                    { 
                                                      if ($dirfile != "." && $dirfile != ".." && $dirfile!=basename($_SERVER["SCRIPT_FILENAME"]) && preg_match("/\.(sql|gz|csv)$/i",$dirfile))
                                                      { if (!$dirhead)
                                                        { echo ("<table class='table table-sm table-centered mb-0'>");
                                              
                                                        echo ("<th>Cliente Emisor</th><th>Fecha Salida</th><th>Filename</th><th>Size</th><th>Date&amp;Time</th><th>Type</th><th>&nbsp;</th><th>Eliminar</th>\n");
                                                          $dirhead=true;
                                                        }
                                                        echo ("<tr><td style='color:red'>$_SESSION[imp_razonsocial]</td><td>$_SESSION[imp_fecha]</td><td>$dirfile</td><td class=\"right\">".filesize($upload_dir.'/'.$dirfile)."</td><td>".date ("Y-m-d H:i:s", filemtime($upload_dir.'/'.$dirfile))."</td>");

                                                        if (preg_match("/\.sql$/i",$dirfile))
                                                          echo ("<td>SQL</td>");
                                                        elseif (preg_match("/\.gz$/i",$dirfile))
                                                          echo ("<td>GZip</td>");
                                                        elseif (preg_match("/\.csv$/i",$dirfile))
                                                          echo ("<td>CSV</td>");
                                                        else
                                                          echo ("<td>Misc</td>");

                                                        if ((preg_match("/\.gz$/i",$dirfile) && function_exists("gzopen")) || preg_match("/\.sql$/i",$dirfile) || preg_match("/\.csv$/i",$dirfile))
                                                          
                                                        if($_SESSION[imp_razonsocial]<>""){
                                                        
                                                            echo ("<td><a href=\"".$_SERVER["PHP_SELF"]."?start=1&amp;fn=".urlencode($dirfile)."&amp;foffset=0&amp;totalqueries=0&amp;delimiter=".urlencode($delimiter)."\">Subir Archivo</a>  $dirfile a $db_name</td>\n <td><a href=\"".$_SERVER["PHP_SELF"]."?delete=".urlencode($dirfile)."\"><i class='mdi mdi-24px mdi-trash-can-outline'></i></a></td></tr>\n");
                                                        
                                                        }else{
                                                        
                                                            echo ("<td><a class='text-danger'>Completar el Cliente Emisor antes de subir $db_name a $db_server</a></td>\n <td><a href=\"".$_SERVER["PHP_SELF"]."?delete=".urlencode($dirfile)."\"><i class='mdi mdi-24px mdi-trash-can-outline'></i></a></td></tr>\n");
                                                        }
                                                          
                                                        else
                                                          echo ("<td>&nbsp;</td>\n <td>&nbsp;</td></tr>\n");
                                                      }
                                                    }
                                                  }

                                                  if ($dirhead) 
                                                    echo ("</table>\n");
                                                  else 
                                                    echo ("<p style='margin-left:20px'>2. No uploaded SQL, GZ or CSV files found in the working directory</p>\n");
                                                }
                                                else
                                                { echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i>Error listing directory $upload_dir</div>");
                                                  $error=true;
                                                }

                                              }
                                              ?>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                
                                    <?php

                                    // Single file mode

                                    if (!$error && !isset ($_REQUEST["fn"]) && $filename!="")
                                    { echo ("<p><a href=\"".$_SERVER["PHP_SELF"]."?start=1&amp;fn=".urlencode($filename)."&amp;foffset=0&amp;totalqueries=0\">Start Import</a> from $filename into $db_name at $db_server</p>\n");
                                    }


                                    // File Upload Form

                                    if (!$error && !isset($_REQUEST["fn"]) && $filename=="")
                                    { 

                                    // Test permissions on working directory

                                    do { $tempfilename=$upload_dir.'/'.time().".tmp"; } while (file_exists($tempfilename)); 
                                    if (!($tempfile=@fopen($tempfilename,"w")))
                                    { echo ("<p>Upload form disabled. Permissions for the working directory <i>$upload_dir</i> <b>must be set writable for the webserver</b> in order ");
                                        echo ("to upload files here. Alternatively you can upload your dump files via FTP.</p>\n");
                                    }
                                    else
                                    { fclose($tempfile);
                                        unlink ($tempfilename);
                                        echo ("<div class='alert alert-danger' role='alert'>"+
                                        "<i class='dripicons-wrong mr-2'></i> Recuerde que no se podran subir archivos mayores a $upload_max_filesize bytes (".round ($upload_max_filesize/1024/1024)." Mbytes)"+
                                        "</div>");

                                    }
                                    }

                                    // Print the current mySQL connection charset

                                    if (!$error && !TESTMODE && !isset($_REQUEST["fn"]))
                                    { 
                                    $result = mysql_query("SHOW VARIABLES LIKE 'character_set_connection';");
                                    $row = mysql_fetch_assoc($result);
                                    if ($row) 
                                    { $charset = $row['Value'];

                                    }
                                    }

                                    // Open the file

                                    if (!$error && isset($_REQUEST["start"]))
                                    { 

                                    // Set current filename ($filename overrides $_REQUEST["fn"] if set)

                                    if ($filename!="")
                                        $curfilename=$filename;
                                    else if (isset($_REQUEST["fn"]))
                                        $curfilename=urldecode($_REQUEST["fn"]);
                                    else
                                        $curfilename="";

                                    // Recognize GZip filename

                                    if (preg_match("/\.gz$/i",$curfilename)) 
                                        $gzipmode=true;
                                    else
                                        $gzipmode=false;

                                    if ((!$gzipmode && !$file=@fopen($upload_dir.'/'.$curfilename,"r")) || ($gzipmode && !$file=@gzopen($upload_dir.'/'.$curfilename,"r")))
                                    { echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> Can't open ".$curfilename." for import</div>");
                                        echo ("<p>Please, check that your dump file name contains only alphanumerical characters, and rename it accordingly, for example: $curfilename.".
                                            "<br>Or, specify \$filename in bigdump.php with the full filename. ".
                                            "<br>Or, you have to upload the $curfilename to the server first.</p>\n");
                                        $error=true;
                                    }

                                    // Get the file size (can't do it fast on gzipped files, no idea how)

                                    else if ((!$gzipmode && @fseek($file, 0, SEEK_END)==0) || ($gzipmode && @gzseek($file, 0)==0))
                                    { if (!$gzipmode) $filesize = ftell($file);
                                        else $filesize = gztell($file);                   // Always zero, ignore
                                    }
                                    else
                                    { echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> I can't seek into $curfilename</div>");
                                        $error=true;
                                    }

                                    // Stop if csv file is used, but $csv_insert_table is not set

                                    if (!$error && ($csv_insert_table == "") && (preg_match("/(\.csv)$/i",$curfilename)))
                                    { echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> You have to specify \$csv_insert_table when using a CSV file. </div>");
                                        $error=true;
                                    }
                                    }


                                    // *******************************************************************************************
                                    // START IMPORT SESSION HERE
                                    // *******************************************************************************************

                                    if (!$error && isset($_REQUEST["start"]) && isset($_REQUEST["foffset"]) && preg_match("/(\.(sql|gz|csv))$/i",$curfilename))
                                    {

                                    do_action('session_start');

                                    // Check start and foffset are numeric values

                                    if (!is_numeric($_REQUEST["start"]) || !is_numeric($_REQUEST["foffset"]))
                                    { echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> UNEXPECTED: Non-numeric values for start and foffset</div>");
                                        $error=true;
                                    }
                                    else
                                    {	$_REQUEST["start"]   = floor($_REQUEST["start"]);
                                        $_REQUEST["foffset"] = floor($_REQUEST["foffset"]);
                                    }

                                    // Set the current delimiter if defined

                                    if (isset($_REQUEST["delimiter"]))
                                        $delimiter = $_REQUEST["delimiter"];

                                    // Empty CSV table if requested

                                    if (!$error && $_REQUEST["start"]==1 && $csv_insert_table != "" && $csv_preempty_table)
                                    { 
                                        $query = "DELETE FROM `$csv_insert_table`";
                                        if (!TESTMODE && !mysql_query(trim($query), $dbconnection))
                                        { echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> Error when deleting entries from $csv_insert_table.</div>");
                                        echo ("<p>Query: ".trim(nl2br(htmlentities($query)))."</p>\n");
                                        echo ("<p>MySQL: ".mysql_error()."</p>\n");
                                        $error=true;
                                        }
                                    }
                                    
                                    // Print start message

                                    if (!$error)
                                    { skin_open();
                                        if (TESTMODE) 
                                        echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> TEST MODE ENABLED</div>");
                                        echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> Processing file: <b>".$curfilename."</b></div>");
                                        echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> Starting from line: ".$_REQUEST["start"]."</div>");	
                                        skin_close();
                                    }

                                    // Check $_REQUEST["foffset"] upon $filesize (can't do it on gzipped files)

                                    if (!$error && !$gzipmode && $_REQUEST["foffset"]>$filesize)
                                    { echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> UNEXPECTED: Can't set file pointer behind the end of file</div>");
                                        $error=true;
                                    }

                                    // Set file pointer to $_REQUEST["foffset"]

                                    if (!$error && ((!$gzipmode && fseek($file, $_REQUEST["foffset"])!=0) || ($gzipmode && gzseek($file, $_REQUEST["foffset"])!=0)))
                                    { echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> UNEXPECTED: Can't set file pointer to offset: ".$_REQUEST["foffset"]."</div>");
                                        $error=true;
                                    }

                                    // Start processing queries from $file

                                    if (!$error)
                                    { $query="";
                                        $queries=0;
                                        $totalqueries=$_REQUEST["totalqueries"];
                                        $linenumber=$_REQUEST["start"];
                                        $querylines=0;
                                        $inparents=false;

                                    // Stay processing as long as the $linespersession is not reached or the query is still incomplete

                                        while ($linenumber<$_REQUEST["start"]+$linespersession || $query!="")
                                        {

                                    // Read the whole next line

                                        $dumpline = "";
                                        while (!feof($file) && substr ($dumpline, -1) != "\n" && substr ($dumpline, -1) != "\r")
                                        { if (!$gzipmode)
                                            $dumpline .= fgets($file, DATA_CHUNK_LENGTH);
                                            else
                                            $dumpline .= gzgets($file, DATA_CHUNK_LENGTH);
                                        }
                                        if ($dumpline==="") break;

                                    // Remove UTF8 Byte Order Mark at the file beginning if any

                                        if ($_REQUEST["foffset"]==0)
                                            $dumpline=preg_replace('|^\xEF\xBB\xBF|','',$dumpline);
                                        
                                    //elimino los espacios en blanco
                                            $dumpline=trim($dumpline);
                                        
                                    // Create an SQL query from CSV line
                                    $NCliente=$_SESSION[imp_ncliente];
                                        
                                        if (($csv_insert_table != "") && (preg_match("/(\.csv)$/i",$curfilename)))
                                        {
                                            if ($csv_add_slashes)
                                            $dumpline = addslashes($dumpline);
                                            $dumpline = explode($csv_delimiter,$dumpline);
                                            if ($csv_add_quotes)
                                            $dumpline = "'".implode("','",$dumpline)."'";
                                            else
                                            $dumpline = implode(",",$dumpline);
                                            
                                            
                                            $dumpline = 'INSERT INTO '.$csv_insert_table.' (NCliente,idProveedor,Cantidad,ValorDeclarado,Recorrido) VALUES ('.$NCliente.','.$dumpline.');';
                                            
                                        }

                                    // Handle DOS and Mac encoded linebreaks (I don't know if it really works on Win32 or Mac Servers)

                                        $dumpline=str_replace("\r\n", "\n", $dumpline);
                                        $dumpline=str_replace("\r", "\n", $dumpline);
                                                
                                    // DIAGNOSTIC
                                    echo ("<p>Line $linenumber: $dumpline</p>\n");

                                    // Recognize delimiter statement

                                        if (!$inparents && strpos ($dumpline, "DELIMITER ") === 0)
                                            $delimiter = str_replace ("DELIMITER ","",trim($dumpline));

                                    // Skip comments and blank lines only if NOT in parents

                                        if (!$inparents)
                                        { $skipline=false;
                                            reset($comment);
                                            foreach ($comment as $comment_value)
                                            { 

                                    // DIAGNOSTIC
                                            echo ($comment_value);
                                            if (trim($dumpline)=="" || strpos (trim($dumpline), $comment_value) === 0)
                                            { $skipline=true;
                                                break;
                                            }
                                            }
                                            if ($skipline)
                                            { $linenumber++;

                                    // DIAGNOSTIC
                                    echo ("<p>Comment line skipped</p>\n");

                                            continue;
                                            }
                                        }

                                    // Remove double back-slashes from the dumpline prior to count the quotes ('\\' can only be within strings)
                                        
                                        $dumpline_deslashed = str_replace ("\\\\","",$dumpline);

                                    // Count ' and \' (or " and \") in the dumpline to avoid query break within a text field ending by $delimiter

                                        $parents=substr_count ($dumpline_deslashed, $string_quotes)-substr_count ($dumpline_deslashed, "\\$string_quotes");
                                        if ($parents % 2 != 0)
                                            $inparents=!$inparents;

                                    // Add the line to query

                                        $query .= $dumpline;

                                    // Don't count the line if in parents (text fields may include unlimited linebreaks)
                                        
                                        if (!$inparents)
                                            $querylines++;
                                        
                                    // Stop if query contains more lines as defined by $max_query_lines

                                        if ($querylines>$max_query_lines)
                                        {
                                            echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> Stopped at the line $linenumber. </div>");
                                            echo ("<p>At this place the current query includes more than ".$max_query_lines." dump lines. That can happen if your dump file was ");
                                            echo ("created by some tool which doesn't place a semicolon followed by a linebreak at the end of each query, or if your dump contains ");
                                            echo ("extended inserts or very long procedure definitions. Please read the <a href=\"http://www.ozerov.de/bigdump/usage/\">BigDump usage notes</a> ");
                                            echo ("for more infos. Ask for our support services ");
                                            echo ("in order to handle dump files containing extended inserts.</p>\n");
                                            $error=true;
                                            break;
                                        }


                                        if ((preg_match('/'.preg_quote($delimiter,'/').'$/',trim($dumpline)) || $delimiter=='') && !$inparents)
                                        { 

                                    // Cut off delimiter of the end of the query

                                            $query = substr(trim($query),0,-1*strlen($delimiter));


                                            if (!TESTMODE && !mysql_query($query, $dbconnection))
                                            { 
                                            header('location:https://www.caddy.com.ar/SistemaTriangular/Datos/Importar_d?error=1');
                                            
                                            echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> Error at the line $linenumber: ". trim($dumpline)."</div>");
                                            echo ("<p>Query: ".trim(nl2br(htmlentities($query)))."</p>\n");
                                            echo ("<p>MySQL: ".mysql_error()."</p>\n");
                                            $error=true;
                                            break;
                                            }
                                            
                                            $totalqueries++;
                                            $queries++;
                                            $query="";
                                            $querylines=0;
                                        }
                                        $linenumber++;
                                        }




                                    //BUSCO LOS CLIENTES QUE ESTEN RELACIONADOS CON LAS ENTREGAS DE DINTER Y QUE NO TENGAN CARGADOS LOS DATOS
                                    $sql=mysql_query("SELECT * FROM PreVenta WHERE Eliminado='0' AND Cargado='0' AND NumeroVenta='0' AND NCliente='$NCliente'");
                                    //DETERMINO LAS VARIABLES FIJAS PARA LA IMPORTACION
                                    $NCliente=$_SESSION[imp_ncliente];
                                    $RazonSocial=$_SESSION[imp_razonsocial];
                                    $TipoDeComprobante='SOLICITUD WEB';
                                    $NumeroComprobante='49';
                                        
                                    while($row=mysql_fetch_array($sql)){
                                    $sqlclienteOrigen=mysql_query("SELECT * FROM Clientes WHERE id='$NCliente'");
                                    $datoclienteOrigen=mysql_fetch_array($sqlclienteOrigen);
                                    
                                    $sqlclientes=mysql_query("SELECT * FROM Clientes WHERE idProveedor='$row[idProveedor]' AND Relacion='$NCliente'");
                                    $datosqlclientes=mysql_fetch_array($sqlclientes);

                                    //QUE PASA SI NO ENCUENTRO DEL CLIENTE?  
                                    if($datosqlclientes[Recorrido]==''){
                                    // Busco el utlimo Recorrido del cliente para cargarlo en Preventas
                                    $sqlh=mysql_query("SELECT Recorrido FROM HojaDeRuta WHERE Eliminado=0 AND id=(SELECT MAX(id)as id FROM HojaDeRuta WHERE idCliente='$datosqlclientes[id]')");
                                    $datosqlhojaderuta=mysql_fetch_array($sqlh);
                                    $Recorrido=$datosqlhojaderuta[Recorrido];  
                                    $updatepreventa=mysql_query("UPDATE PreVenta SET Recorrido='$Recorrido' WHERE id='$row[id]'");  
                                    }
                                    
                                        //AGREGO LOS DATOS PENDIENTES
                                        $Fecha=date('Y-m-d');  
                                        $updatepreventa=mysql_query("UPDATE PreVenta SET 
                                        Fecha='$Fecha',
                                        RazonSocial='$RazonSocial',
                                        NCliente='$NCliente',
                                        TipoDeComprobante='$TipoDeComprobante',
                                        NumeroComprobante='$NumeroComprobante',
                                        ClienteDestino='$datosqlclientes[nombrecliente]',
                                        idClienteDestino='$datosqlclientes[id]',
                                        DomicilioDestino='$datosqlclientes[Direccion]',
                                        LocalidadDestino='$datosqlclientes[Ciudad]',
                                        NumeroVenta='$NumeroFecha',
                                        DomicilioOrigen='$datoclienteOrigen[Direccion]',
                                        LocalidadOrigen='$datoclienteOrigen[Ciudad]',
                                        Usuario='$_SESSION[Usuario]',
                                        EntregaEn='Domicilio',
                                        FechaEntrega='$Fecha0'
                                        WHERE id='$row[id]'");
                                        }

                                    }

                                    // Get the current file position

                                    if (!$error)
                                    { if (!$gzipmode) 
                                        $foffset = ftell($file);
                                        else
                                        $foffset = gztell($file);
                                        if (!$foffset)
                                        { echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> UNEXPECTED: Can't read the file pointer offset</div>");
                                        $error=true;
                                        }
                                    }

                                    // Print statistics

                                    skin_open();

                                    // echo ("<p class=\"centr\"><b>Statistics</b></p>\n");

                                    if (!$error)
                                    { 
                                        $lines_this   = $linenumber-$_REQUEST["start"];
                                        $lines_done   = $linenumber-1;
                                        $lines_togo   = ' ? ';
                                        $lines_tota   = ' ? ';
                                        
                                        $queries_this = $queries;
                                        $queries_done = $totalqueries;
                                        $queries_togo = ' ? ';
                                        $queries_tota = ' ? ';

                                        $bytes_this   = $foffset-$_REQUEST["foffset"];
                                        $bytes_done   = $foffset;
                                        $kbytes_this  = round($bytes_this/1024,2);
                                        $kbytes_done  = round($bytes_done/1024,2);
                                        $mbytes_this  = round($kbytes_this/1024,2);
                                        $mbytes_done  = round($kbytes_done/1024,2);
                                    
                                        if (!$gzipmode)
                                        {
                                        $bytes_togo  = $filesize-$foffset;
                                        $bytes_tota  = $filesize;
                                        $kbytes_togo = round($bytes_togo/1024,2);
                                        $kbytes_tota = round($bytes_tota/1024,2);
                                        $mbytes_togo = round($kbytes_togo/1024,2);
                                        $mbytes_tota = round($kbytes_tota/1024,2);
                                        
                                        $pct_this   = ceil($bytes_this/$filesize*100);
                                        $pct_done   = ceil($foffset/$filesize*100);
                                        $pct_togo   = 100 - $pct_done;
                                        $pct_tota   = 100;


                                        if ($bytes_togo==0) 
                                        { $lines_togo   = '0'; 
                                            $lines_tota   = $linenumber-1; 
                                            $queries_togo = '0'; 
                                            $queries_tota = $totalqueries; 
                                        }

                                        $pct_bar    = "<div style=\"height:15px;width:$pct_done%;background-color:#000080;margin:0px;\"></div>";
                                        }
                                        else
                                        {
                                        $bytes_togo  = ' ? ';
                                        $bytes_tota  = ' ? ';
                                        $kbytes_togo = ' ? ';
                                        $kbytes_tota = ' ? ';
                                        $mbytes_togo = ' ? ';
                                        $mbytes_tota = ' ? ';
                                        
                                        $pct_this    = ' ? ';
                                        $pct_done    = ' ? ';
                                        $pct_togo    = ' ? ';
                                        $pct_tota    = 100;
                                        $pct_bar     = str_replace(' ','&nbsp;','<tt>[         Not available for gzipped files          ]</tt>');
                                        }
                                        
                                        echo ("
                                        <center>
                                        <table class='table table-sm table-centered mb-0'>
                                        <tr><th class=\"bg4\"> </th><th class=\"bg4\">Session</th><th class=\"bg4\">Done</th><th class=\"bg4\">To go</th><th class=\"bg4\">Total</th></tr>
                                        <tr><th class=\"bg4\">Lines</th><td class=\"bg3\">$lines_this</td><td class=\"bg3\">$lines_done</td><td class=\"bg3\">$lines_togo</td><td class=\"bg3\">$lines_tota</td></tr>
                                        <tr><th class=\"bg4\">Queries</th><td class=\"bg3\">$queries_this</td><td class=\"bg3\">$queries_done</td><td class=\"bg3\">$queries_togo</td><td class=\"bg3\">$queries_tota</td></tr>
                                        <tr><th class=\"bg4\">Bytes</th><td class=\"bg3\">$bytes_this</td><td class=\"bg3\">$bytes_done</td><td class=\"bg3\">$bytes_togo</td><td class=\"bg3\">$bytes_tota</td></tr>
                                        <tr><th class=\"bg4\">KB</th><td class=\"bg3\">$kbytes_this</td><td class=\"bg3\">$kbytes_done</td><td class=\"bg3\">$kbytes_togo</td><td class=\"bg3\">$kbytes_tota</td></tr>
                                        <tr><th class=\"bg4\">MB</th><td class=\"bg3\">$mbytes_this</td><td class=\"bg3\">$mbytes_done</td><td class=\"bg3\">$mbytes_togo</td><td class=\"bg3\">$mbytes_tota</td></tr>
                                        <tr><th class=\"bg4\">%</th><td class=\"bg3\">$pct_this</td><td class=\"bg3\">$pct_done</td><td class=\"bg3\">$pct_togo</td><td class=\"bg3\">$pct_tota</td></tr>
                                        <tr><th class=\"bg4\">% bar</th><td class=\"bgpctbar\" colspan=\"4\">$pct_bar</td></tr>
                                        </table>
                                        </center>
                                        \n");

                                    // Finish message and restart the script

                                        if ($linenumber<$_REQUEST["start"]+$linespersession)
                                        { echo ("<div class='alert alert-success' role='alert'><i class='dripicons-warning mr-2'></i> Congratulations: End of file reached, assuming OK</div>");
                                        echo ("<div class='alert alert-success' role='alert'><i class='dripicons-warning mr-2'></i> IMPORTANT: REMOVE YOUR DUMP FILE and BIGDUMP SCRIPT FROM SERVER NOW!</div>");
                                        
                                        echo "Hola Mundo";


                                        ?>



                                        


                                    <script>
                                        // setTimeout(function() {
                                        //     window.location.href = "https://www.caddy.com.ar/SistemaTriangular/Inicio/Cpanel.php";
                                        // }, 3000);
                                    </script>
                                        <?
                                            
                                        echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> Thank you for using this tool! Please rate <a href=\"http://www.hotscripts.com/listing/bigdump/?RID=403\" target=\"_blank\">Bigdump at Hotscripts.com</a></div>");
                                        echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> You can send me some bucks or euros as appreciation via PayPal. Thank you!</div>");
                                        do_action('script_finished');
                                        $error=true; // This is a semi-error telling the script is finished
                                        }
                                        else
                                        { if ($delaypersession!=0)
                                            echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> Now I'm <b>waiting $delaypersession milliseconds</b> before starting next session...</div>");
                                        if (!$ajax) 
                                            echo ("<script language=\"JavaScript\" type=\"text/javascript\">window.setTimeout('location.href=\"".$_SERVER["PHP_SELF"]."?start=$linenumber&fn=".urlencode($curfilename)."&foffset=$foffset&totalqueries=$totalqueries&delimiter=".urlencode($delimiter)."\";',500+$delaypersession);</script>\n");

                                        echo ("<noscript>\n");
                                        echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> <a href=\"".$_SERVER["PHP_SELF"]."?start=$linenumber&amp;fn=".urlencode($curfilename)."&amp;foffset=$foffset&amp;totalqueries=$totalqueries&amp;delimiter=".urlencode($delimiter)."\">Continue from the line $linenumber</a> (Enable JavaScript to do it automatically)</div>");
                                        echo ("</noscript>\n");
                                    
                                        echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> Press <b><a href=\"".$_SERVER["PHP_SELF"]."\">STOP</a></b> to abort the import <b>OR WAIT!</b></div>");
                                        }
                                    }
                                    else 
                                        echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> Stopped on error</div>");

                                    skin_close();

                                    }

                                    if ($error)
                                    echo ("<div class='alert alert-warning' role='alert'><i class='dripicons-warning mr-2'></i> <a href=\"".$_SERVER["PHP_SELF"]."\">Start from the beginning</a> (DROP the old tables before restarting)</div>");

                                    if ($dbconnection) mysql_close($dbconnection);
                                    if ($file && !$gzipmode) fclose($file);
                                    else if ($file && $gzipmode) gzclose($file);

                                    do_action('end_of_body');


                                    // If error or finished put out the whole output from above and stop

                                    if ($error) 
                                    {
                                    $out1 = ob_get_contents();
                                    ob_end_clean();
                                    echo $out1;
                                    die;
                                    }

                                    // If Ajax enabled and in import progress creates responses  (XML response or script for the initial page)

                                    if ($ajax && isset($_REQUEST['start']))
                                    {
                                    if (isset($_REQUEST['ajaxrequest'])) 
                                    {	ob_end_clean();
                                        create_xml_response();
                                        die;
                                    } 
                                    else 
                                        create_ajax_script();	  
                                    }

                                    // Anyway put out the output from above

                                    ob_flush();

                                    // THE MAIN SCRIPT ENDS HERE

                                    // *******************************************************************************************
                                    // Plugin handling (EXPERIMENTAL)
                                    // *******************************************************************************************

                                    function do_action($tag)
                                    { global $plugin_actions;
                                    
                                    if (isset($plugin_actions[$tag]))
                                    { reset ($plugin_actions[$tag]);
                                        foreach ($plugin_actions[$tag] as $action)
                                        call_user_func_array($action, array());
                                    }
                                    }

                                    function add_action($tag, $function)
                                    {
                                        global $plugin_actions;
                                        $plugin_actions[$tag][] = $function;
                                    }

                                    // *******************************************************************************************
                                    // 				AJAX utilities
                                    // *******************************************************************************************

                                    function create_xml_response() 
                                    {
                                    global $linenumber, $foffset, $totalqueries, $curfilename, $delimiter,
                                                    $lines_this, $lines_done, $lines_togo, $lines_tota,
                                                    $queries_this, $queries_done, $queries_togo, $queries_tota,
                                                    $bytes_this, $bytes_done, $bytes_togo, $bytes_tota,
                                                    $kbytes_this, $kbytes_done, $kbytes_togo, $kbytes_tota,
                                                    $mbytes_this, $mbytes_done, $mbytes_togo, $mbytes_tota,
                                                    $pct_this, $pct_done, $pct_togo, $pct_tota,$pct_bar;

                                        header('Content-Type: application/xml');
                                        header('Cache-Control: no-cache');
                                        
                                        echo '<?xml version="1.0" encoding="ISO-8859-1"?>';
                                        echo "<root>";

                                    // data - for calculations

                                        echo "<linenumber>$linenumber</linenumber>";
                                        echo "<foffset>$foffset</foffset>";
                                        echo "<fn>$curfilename</fn>";
                                        echo "<totalqueries>$totalqueries</totalqueries>";
                                        echo "<delimiter>$delimiter</delimiter>";

                                    // results - for page update

                                        echo "<elem1>$lines_this</elem1>";
                                        echo "<elem2>$lines_done</elem2>";
                                        echo "<elem3>$lines_togo</elem3>";
                                        echo "<elem4>$lines_tota</elem4>";
                                        
                                        echo "<elem5>$queries_this</elem5>";
                                        echo "<elem6>$queries_done</elem6>";
                                        echo "<elem7>$queries_togo</elem7>";
                                        echo "<elem8>$queries_tota</elem8>";
                                        
                                        echo "<elem9>$bytes_this</elem9>";
                                        echo "<elem10>$bytes_done</elem10>";
                                        echo "<elem11>$bytes_togo</elem11>";
                                        echo "<elem12>$bytes_tota</elem12>";
                                                
                                        echo "<elem13>$kbytes_this</elem13>";
                                        echo "<elem14>$kbytes_done</elem14>";
                                        echo "<elem15>$kbytes_togo</elem15>";
                                        echo "<elem16>$kbytes_tota</elem16>";
                                        
                                        echo "<elem17>$mbytes_this</elem17>";
                                        echo "<elem18>$mbytes_done</elem18>";
                                        echo "<elem19>$mbytes_togo</elem19>";
                                        echo "<elem20>$mbytes_tota</elem20>";
                                        
                                        echo "<elem21>$pct_this</elem21>";
                                        echo "<elem22>$pct_done</elem22>";
                                        echo "<elem23>$pct_togo</elem23>";
                                        echo "<elem24>$pct_tota</elem24>";
                                        echo "<elem_bar>".htmlentities($pct_bar)."</elem_bar>";
                                                    
                                        echo "</root>";		
                                    }


                                    function create_ajax_script() 
                                    {
                                    global $linenumber, $foffset, $totalqueries, $delaypersession, $curfilename, $delimiter;

                                    ?>

                                        <script type="text/javascript" language="javascript">			

                                        // creates next action url (upload page, or XML response)
                                        function get_url(linenumber,fn,foffset,totalqueries,delimiter) {
                                            return "<?php echo $_SERVER['PHP_SELF'] ?>?start="+linenumber+"&fn="+fn+"&foffset="+foffset+"&totalqueries="+totalqueries+"&delimiter="+delimiter+"&ajaxrequest=true";
                                        }
                                        
                                        // extracts text from XML element (itemname must be unique)
                                        function get_xml_data(itemname,xmld) {
                                            return xmld.getElementsByTagName(itemname).item(0).firstChild.data;
                                        }
                                        
                                        function makeRequest(url) {
                                            http_request = false;
                                            if (window.XMLHttpRequest) { 
                                            // Mozilla etc.
                                                http_request = new XMLHttpRequest();
                                                if (http_request.overrideMimeType) {
                                                    http_request.overrideMimeType("text/xml");
                                                }
                                            } else if (window.ActiveXObject) { 
                                            // IE
                                                try {
                                                    http_request = new ActiveXObject("Msxml2.XMLHTTP");
                                                } catch(e) {
                                                    try {
                                                        http_request = new ActiveXObject("Microsoft.XMLHTTP");
                                                    } catch(e) {}
                                                }
                                            }
                                            if (!http_request) {
                                                    alert("Cannot create an XMLHTTP instance");
                                                    return false;
                                            }
                                            http_request.onreadystatechange = server_response;
                                            http_request.open("GET", url, true);
                                            http_request.send(null);
                                        }
                                        
                                        function server_response() 
                                        {

                                        // waiting for correct response
                                        if (http_request.readyState != 4)
                                            return;

                                        if (http_request.status != 200) 
                                        {
                                            alert("Page unavailable, or wrong url!")
                                            return;
                                        }
                                            
                                            // r = xml response
                                            var r = http_request.responseXML;
                                            
                                            //if received not XML but HTML with new page to show
                                            if (!r || r.getElementsByTagName('root').length == 0) 
                                            {	var text = http_request.responseText;
                                                document.open();
                                                document.write(text);		
                                                document.close();	
                                                return;		
                                            }
                                            
                                            // update "Starting from line: "
                                            document.getElementsByTagName('p').item(1).innerHTML = 
                                                "Starting from line: " + 
                                                r.getElementsByTagName('linenumber').item(0).firstChild.nodeValue;
                                            
                                            // update table with new values
                                            for(i = 1; i <= 24; i++)
                                                document.getElementsByTagName('td').item(i).firstChild.data = get_xml_data('elem'+i,r);
                                            
                                            // update color bar
                                            document.getElementsByTagName('td').item(25).innerHTML = 
                                                r.getElementsByTagName('elem_bar').item(0).firstChild.nodeValue;
                                                
                                            // action url (XML response)	 
                                            url_request =  get_url(
                                                get_xml_data('linenumber',r),
                                                get_xml_data('fn',r),
                                                get_xml_data('foffset',r),
                                                get_xml_data('totalqueries',r),
                                                get_xml_data('delimiter',r));
                                            
                                            // ask for XML response	
                                            window.setTimeout("makeRequest(url_request)",500+<?php echo $delaypersession; ?>);
                                        }

                                        // First Ajax request from initial page

                                        var http_request = false;
                                        
                                        var url_request =  get_url(<?php echo ($linenumber.',"'.urlencode($curfilename).'",'.$foffset.','.$totalqueries.',"'.urlencode($delimiter).'"') ;?>);
                                        // window.setTimeout("makeRequest(url_request)",500+
                                        <?php 
                                        // echo $delaypersession; 
                                        ?>
                                        // );
                                        </script>
                                    
                                    <?php
                                    }
                                    a:  
                                    ?>
                                        
                                                    <!-- content -->
                                                <div class="spinner-border avatar-md text-primary" role="status" style="display:none"></div>
                                                <!-- <div class="spinner-grow avatar-md text-secondary" role="status"></div> -->
                                                    <!-- Footer Start -->
                                                    <footer class="footer">
                                                        <div class="container-fluid">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <script>document.write(new Date().getFullYear())</script> © Triangular S.A.
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
                                                </div>
                                                <!-- ============================================================== -->
                                                <!-- End Page content -->
                                                <!-- ============================================================== -->
                                            </div>
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
                                            <!-- third party js ends -->

                                            <!-- demo app -->
                                            <script src="../hyper/dist/saas/assets/js/pages/demo.datatable-init.js"></script>
                                            <!-- end demo js-->
                                            <!-- funciones -->
                                            <script src="../Menu/js/funciones.js"></script>
                                            <script src="Procesos/js/importar.js"></script>
                                            <!-- demo app -->
                                            <!-- <script src="../hyper/dist/saas/assets/js/pages/demo.dashboard.js"></script> -->
                                            <!-- end demo js-->

                                    </body>
                                    </html>