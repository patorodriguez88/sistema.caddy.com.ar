<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once "../../../Conexion/Conexioni.php";

if (isset($_POST['MuestroFotos']) && $_POST['MuestroFotos'] == 1) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $ruta = "../../../../AppRecorridos/Proceso/php/images/" . $CodigoSeguimiento . "/";

    if (is_dir($ruta)) {
        $filehandle = opendir($ruta);
        $w = 1;

        while (($file = readdir($filehandle)) !== false) {
            if ($file != "." && $file != "..") {
                $nameimage = $ruta . $file;
                $imgURL = $nameimage;
                $nombreLimpio = htmlspecialchars($file);
                $idHtml = 'foto_' . md5($file); // ID único para borrarlo desde JS

                echo '<div class="col-md-4" id="' . $idHtml . '">';
                echo '  <div class="card d-block">';
                echo '    <div class="card-body">';
                echo '      <h5 class="card-title">Foto ' . $w . '</h5>';
                echo '      <h6 class="card-subtitle text-muted">Nombre: ' . $nombreLimpio . '</h6>';
                echo '    </div>';
                echo '    <img class="img-fluid" src="' . $imgURL . '" alt="Foto">';
                echo '    <div class="card-body text-center">';
                echo '      <button class="btn btn-sm btn-outline-danger" onclick="eliminarFoto(\'' . $CodigoSeguimiento . '\', \'' . $nombreLimpio . '\', \'' . $idHtml . '\')">';
                echo '        <i class="mdi mdi-delete"></i> Eliminar';
                echo '      </button>';
                echo '    </div>';
                echo '  </div>';
                echo '</div>';

                $w++;
            }
        }

        closedir($filehandle);
    } else {
        echo '<div class="col-12 text-danger">No se encontró la carpeta con las fotos del código ingresado.</div>';
    }
}

if (isset($_POST['MuestroQuicks'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $ruta = "../../../../AppRecorridos/Proceso/php/images/" . $CodigoSeguimiento . "/"; // Indicar la ruta
    $filehandle = opendir($ruta); // Abrir archivos de la carpeta
    $w = 1;
    $file = readdir($filehandle);
    $total_imagenes = count(glob($ruta . '{*.jpg,*.gif,*.png,*.jpeg}', GLOB_BRACE));
    echo '<div class="col-12">';
    echo '<div class="card d-block">';
    echo '<div class="card-body">';
    echo '<h4 class="header-title mb-3"> Archivos Adjuntos</h4>';

?>
    <div class="row col-12 mx-n1 g-0">
        <!-- <div class="col-xxl-6 col-lg-6"> -->
        <div class="col-3 col-lg-3">
            <div role="button" onclick="verguia();" class="card m-2 shadow-none border">
                <div class="p-2">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-secondary rounded">
                                    <i class="mdi mdi-printer font-18 text-success"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col ps-0">
                            <a href="#" class="text-muted fw-bold">Guia</a>
                            <p class="mb-0 font-13">PDF</p>
                        </div>
                    </div> <!-- end row -->
                </div> <!-- end .p-2-->
            </div> <!-- end col -->
        </div> <!-- end col-->
        <?php
        if ($filehandle) {
            $text = 'success';
        } else {
            $text = 'muted';
        }
        ?>
        <div class="col-xxl-3 col-lg-3">
            <!-- <div role="button" onclick="ver();" class="card m-2 shadow-none border"> -->
            <div role="button" onclick="verFotosYSubir($('#inputcodigo').val())" class="card m-2 shadow-none border">
                <!-- <button onclick="verFotosYSubir($('#inputcodigo').val())">Ver/Subir Fotos</button> -->
                <div class="p-2">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-secondary rounded">
                                    <i class="mdi mdi-camera font-18 text-<? echo $text; ?>"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col ps-0">
                            <a class="text-muted fw-bold">Fotos </a>
                            <p class="mb-0 font-13"><? echo $total_imagenes; ?></p>
                        </div>
                    </div> <!-- end row -->
                </div> <!-- end .p-2-->
            </div> <!-- end col -->
        </div> <!-- end col-->
        <div class="col-xxl-3 col-lg-3">
            <div role="button" onclick="verrotulo();" class="card m-2 shadow-none border">
                <div class="p-2">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-secondary rounded">
                                    <i class="mdi mdi-card-bulleted font-18 text-success"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col ps-0">
                            <a class="text-muted fw-bold">Rotulo </a>
                            <p class="mb-0 font-13"> PDF </p>
                        </div>
                    </div> <!-- end row -->
                </div> <!-- end .p-2-->
            </div> <!-- end col -->
        </div> <!-- end col-->
        <div class="col-xxl-3 col-lg-3">
            <div onclick="verfirma();" class="card m-2 shadow-none border">
                <div class="p-2">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-secondary rounded">
                                    <i class="mdi mdi-signature font-18 text-muted"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col ps-0">
                            <a class="text-muted fw-bold"> Firma </a>
                            <p class="mb-0 font-13"> 0 </p>
                        </div>
                    </div> <!-- end row -->
                </div> <!-- end .p-2-->
            </div> <!-- end col -->
        </div> <!-- end col-->
    <?php
    echo '</div>';
    echo '</div>';
    echo '</div>';
    // Liberar resultados
    // mysql_free_result($file);
}
if (isset($_POST['Tracker'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];

    $sql = "SELECT id,Estado FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento' ORDER BY id DESC LIMIT 0,1";
    $Resultado = $mysqli->query($sql);
    $rows = array();
    $row = $Resultado->fetch_array(MYSQLI_ASSOC);
    $Estado = $row['Estado'];

    echo '<div class="row justify-content-center">';
    echo '<div class="col-lg-8 col-md-10 col-sm-11">';
    echo '<div class="horizontal-steps mt-4 mb-4 pb-5">';
    echo '<div class="horizontal-steps-content">';

    if (($Estado == 'En Origen') || ($Estado == 'EnOrigen')) {
        $EnOrigen = 'current';
        $width = '0%';
    } else {
        $EnOrigen = '';
    }
    if ($Estado == 'Retirado del Cliente') {
        $Retirado = 'current';
        $width = '33%';
    } else {
        $Retirado = '';
    }
    if ($Estado == 'En Transito') {
        $EnTransito = 'current';
        $width = '66%';
    } else {
        $EnTransito = '';
    }
    if ($Estado == 'Entregado al Cliente') {
        $Entregado = 'current';
        $width = '100%';
    } else {
        $Entregado = '';
    }
    if ($Estado == 'Devuelto al Cliente') {
        $Devuelto = 'current';
        $DevueltoEstado = 'Devuelto';
        $width = '100%';
    } else {
        $EntregadoEstado = 'Entregado al Cliente';
        $Entregado = '';
    }

    if ($Estado == 'No se pudo entregar') {
        $Devuelto = 'current';
        $DevueltoEstado = '<a class="text-danger"> Entrega Fallida</a>';
        $EntregadoEstado = "";
        $width = '100%';
    }
    $Devuelto = "";
    $DevueltoEstado = "";
    $width = 0;

    echo '<div class="step-item ' . $EnOrigen . '">';
    echo '<span data-toggle="tooltip" data-placement="bottom" title="" data-original-title="20/08/2018 07:24 PM">En Origen</span>';
    echo '</div>';
    echo '<div class="step-item ' . $Retirado . '">';
    echo '<span data-toggle="tooltip" data-placement="bottom" title="" data-original-title="20/08/2018 07:24 PM">Retirado</span>';
    echo '</div>';
    echo '<div class="step-item ' . $EnTransito . '">';
    echo '<span data-toggle="tooltip" data-placement="bottom" title="" data-original-title="20/08/2018 07:24 PM">En Transito</span>';
    echo '</div>';
    echo '<div class="step-item ' . $Entregado . '' . $Devuelto . '">';
    echo '<span data-toggle="tooltip" data-placement="bottom" title="" data-original-title="20/08/2018 07:24 PM">' . $EntregadoEstado . '' . $DevueltoEstado . '</span>';
    echo '</div>';
    echo '</div>';
    echo '<div class="process-line" style="width:' . $width . '"></div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    // Liberar resultados
    // mysql_free_result();
}
if (isset($_POST['FormaDePago'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $Valor = $_POST['FormaDePago_valor'];

    $mysqli->query("UPDATE TransClientes SET FormaDePago='$Valor' WHERE CodigoSeguimiento='$CodigoSeguimiento'");

    $id = $mysqli->query("SELECT id,if(FormaDePago='Origen',RazonSocial,ClienteDestino)as Cliente, 
  if(FormaDePago='Origen',IngBrutosOrigen,idClienteDestino)as idCliente
  FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");
    $row = $id->fetch_array(MYSQLI_ASSOC);

    if ($row['id'] <> '') {

        $mysqli->query("UPDATE Ctasctes SET RazonSocial='$row[Cliente]',idCliente='$row[idCliente]',Update_info='Servicios/Procesos/php/fotos.php (242)' WHERE idTransClientes='$row[id]' LIMIT 1");
    }

    echo json_encode(array('success' => 1, 'dato' => $row['id'], 'dato1' => $row['Cliente']));
}

if (isset($_POST['CobrarEnvio'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $Valor = $_POST['CobrarEnvio_valor'];
    $mysqli->query("UPDATE TransClientes SET CobrarEnvio='$Valor' WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0 LIMIT 1");
    echo json_encode(array('success' => 1));
}
if (isset($_POST['CobrarCaddy'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $Valor = $_POST['CobrarCaddy_valor'];
    $mysqli->query("UPDATE TransClientes SET CobrarCaddy='$Valor' WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0 LIMIT 1");
    echo json_encode(array('success' => 1));
}
if (isset($_POST['EstadoHDR'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $Valor = $_POST['EstadoHDR_valor'];
    $mysqli->query("UPDATE HojaDeRuta SET Estado='$Valor' WHERE Seguimiento='$CodigoSeguimiento' AND Eliminado=0 LIMIT 1");

    echo json_encode(array('success' => 1));
}
if (isset($_POST['Pagador'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $Resultado = $mysqli->query("SELECT FormaDePago FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");
    $row = $Resultado->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('success' => 1, 'FormaDePago' => $row['FormaDePago']));
}
if (isset($_POST['Corregir_estado'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $Estado = $_POST['Estado'];
    $mysqli->query("UPDATE TransClientes SET Estado='$Estado' WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0 LIMIT 1");
    echo json_encode(array('success' => 1));
}
    ?>