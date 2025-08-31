<?
session_start();
include_once "../../../Conexion/Conexioni.php";
header("Content-Type: text/html;charset=utf-8"); 


function geolocalizar($Direccion){
    // urlencode codifica datos de texto modificando simbolos como acentos
    $direccion = urlencode($Direccion);
    // envio la consulta a Google map api
    $url= "https://maps.google.com/maps/api/geocode/json?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&address={$direccion}";
//     $url = "http://maps.google.com/maps/api/geocode/json?address={$direccion}";
    // recibo la respuesta en formato Json
    $datosjson = file_get_contents($url);
//     print "datos:".$datosjson;
    // decodificamos los datos Json
    $datosmapa = json_decode($datosjson, true);
    // si recibimos estado o status igual a OK, es porque se encontro la direccion
    if($datosmapa['status']==='OK'){
        // asignamos los datos
        $latitud = $datosmapa['results'][0]['geometry']['location']['lat'];
        $longitud = $datosmapa['results'][0]['geometry']['location']['lng'];
        $localizacion = $datosmapa['results'][0]['formatted_address'];
        $types=$datosmapa['result'][0]['types'];
           // Guardamos los datos en una matriz
            $datosmapa = array();           
                array_push(
                $datosmapa,
                $latitud,
                $longitud,
                $localizacion,
                $types
                );
            return $datosmapa;
        }
} 

$idoferta= $mysqli->query("SELECT MAX(id) AS id FROM Clientes");
if ($row = $idoferta->fetch_array(MYSQLI_ASSOC)) {
 $id = trim($row[id])+1;
 }

$NdeCliente=$id;
$nombrecliente=utf8_encode($_POST['nombrecliente']);
$Direccion=addslashes($_POST['Direccion']);

$datosmapa = geolocalizar($_POST['Direccion']);
$latitud = $datosmapa[0];
$longitud = $datosmapa[1];
$types= $datosmapa[3];
// $localizacion = $datosmapa[2];

$Calle=addslashes(utf8_encode($_POST['Calle']));
$Numero=$_POST['Numero'];
$Pisodepto=$_POST['Pisodepto'];
$Barrio=$_POST['Barrio'];  
$Ciudad=$_POST['Ciudad'];
$Provincia=utf8_encode($_POST['Provincia']);
$Telefono=$_POST['Telefono'];
$Celular=$_POST['Celular'];
$Rubro=$_POST['Rubro'];
$Mail=$_POST['Mail'];
$Pais=$_POST['Pais'];
$CodigoPostal=$_POST['CodigoPostal'];
$Observaciones=$_POST['Observaciones'];
$idProveedor=$_POST['idProveedor'];  
$PAGINAWEB=$_POST['PAGINAWEB'];
$DocumentoNacional=$_POST['DocumentoNacional'];
$Categoria=$_POST['Categoria'];
$SituacionFiscal=$_POST['SituacionFiscal'];
$ExtTrabajo=$_POST['Ext_Trabajo_t'];
$Kilometros=$_POST['distancia'];
$Interno=$_POST['Interno_t'];
$Tipo_Documento=$_POST['Tipo_Documento_t'];
$CondicionAnteIva=$_POST['CondicionAnteIva_t'];

if($_POST['Cuit_t']<>''){
$Cuit=$_POST['Cuit_t'];
}else{
$Cuit=$NdeCliente;  
}  
$user_id=$_POST['user_id_t'];
$Usuario=$_POST['Usuario_t'];
$PASSWORD=$_POST['PASSWORD_t'];
$Recorrido=$_POST['Recorrido_t'];
$Relacion=$_POST['Relacion'];  

if(($nombrecliente<>'')||($Direccion<>'')){
    $sql="INSERT INTO Clientes(NdeCliente,nombrecliente,DocumentoNacional,Distribuidora,Rubro,Mail,Ciudad,Provincia,Pais,
    CodigoPostal,Telefono,ExtTrabajo,Celular2,Celular,Direccion,PaginaWeb,Observaciones,Categoria,SituacionFiscal,
    TipoDocumento,CondicionAnteIva,Cuit,userid,Usuario,PASSWORD,Recorrido,Relacion,Calle,Numero,PisoDepto,Barrio,
    idProveedor,Kilometros,Latitud,Longitud,Types)VALUES('{$NdeCliente}','{$nombrecliente}','{$DocumentoNacional}','{$Distribuidora}','{$Rubro}',
    '{$Mail}','{$Ciudad}','{$Provincia}','{$Pais}','{$CodigoPostal}','{$Telefono}','{$Ext_Trabajo}','{$Interno}',
    '{$Celular}','{$Direccion}','{$PAGINA_WEB}','{$Observaciones}','{$Categoria}','{$SituacionFiscal}',
    '{$Tipo_Documento}','{$CondicionAnteIva}','{$Cuit}','{$user_id}','{$Usuario}','{$PASSWORD}',
    '{$Recorrido}','{$Relacion}','{$Calle}','{$Numero}','{$Pisodepto}','{$Barrio}','{$idProveedor}',
    '{$Kilometros}','{$latitud}','{$longitud}','{$types}')";
  
$mysqli->query($sql);

echo json_encode(array('success' => 1,'id'=> $NdeCliente,'NombreCliente'=>$nombrecliente,'Direccion'=>$_POST[Direccion]));

}else{

    echo json_encode(array('success' => 0));  

}