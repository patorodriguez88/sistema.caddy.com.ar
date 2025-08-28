<?
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);  

header("Content-Type: text/html;charset=utf-8"); 

$idoferta= mysql_query("SELECT MAX(id) AS id FROM Clientes");
if ($row = mysql_fetch_row($idoferta)) {
 $id = trim($row[0])+1;
 }

$NdeCliente=$id;
$nombrecliente=$_POST['nombrecliente'];
$Direccion=addslashes(utf8_decode($_POST['Direccion']));
$Calle=addslashes($_POST['Calle']);
$Numero=$_POST['Numero'];
$Pisodepto=$_POST['Pisodepto'];
$Barrio=$_POST['Barrio'];  
$Ciudad=utf8_decode($_POST['Ciudad']);
$Provincia=$_POST['Provincia'];
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
    idProveedor,Kilometros)VALUES('{$NdeCliente}','{$nombrecliente}','{$DocumentoNacional}','{$Distribuidora}','{$Rubro}',
    '{$Mail}','{$Ciudad}','{$Provincia}','{$Pais}','{$CodigoPostal}','{$Telefono}','{$Ext_Trabajo}','{$Interno}',
    '{$Celular}','{$Direccion}','{$PAGINA_WEB}','{$Observaciones}','{$Categoria}','{$SituacionFiscal}',
    '{$Tipo_Documento}','{$CondicionAnteIva}','{$Cuit}','{$user_id}','{$Usuario}','{$PASSWORD}',
    '{$Recorrido}','{$Relacion}','{$Calle}',
    '{$Numero}',
    '{$Pisodepto}',
    '{$Barrio}',
    '{$idProveedor}',
    '{$Kilometros}')";
  
mysql_query($sql);
echo json_encode(array('success' => 1,'id'=> $NdeCliente,'NombreCliente'=>$nombrecliente));
}else{
echo json_encode(array('success' => 0));  
}