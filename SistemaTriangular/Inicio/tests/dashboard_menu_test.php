<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
session_save_path(sys_get_temp_dir());
require_once __DIR__.'/../../Menu/php/permisos_menu.php';
$assert=function($ok,$message){if(!$ok)throw new RuntimeException($message);};
$assert(menuSlug('Home','Resultados del negocio')===menuSlug('Home','Resultados'),'Business rename retains assigned permission');
$assert(menuSlug('Home','Rentabilidad operativa')===menuSlug('Home','Panel Admin'),'Operations rename retains assigned permission');
$map=mapaPaginasMenu(__DIR__.'/../../Menu/topnav.html');
$assert(in_array('home_resultados',$map['sistematriangular/inicio/dashboard_']??[],true),'Business page retains URL permission');
$assert(in_array('home_panel_admin',$map['sistematriangular/inicio/cpaneladmin']??[],true),'Operations page retains URL permission');
$assert(menuSlug('Admin','Resultados')==='admin_resultados','Unrelated results permissions unchanged');
session_write_close();
echo "OK: both menu renames retain existing role permissions and page mappings.\n";
