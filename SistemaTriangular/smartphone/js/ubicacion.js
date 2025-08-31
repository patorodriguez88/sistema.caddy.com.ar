function localize() {
if (navigator.geolocation) {
navigator.geolocation.getCurrentPosition(mapa,error);
} else {
alert('Tu navegador no soporta geolocalizacion.');
}
}
function mapa(pos) { /************************ Aqui están las variables que te interesan***********************************/
var latitud = pos.coords.latitude;
var longitud = pos.coords.longitude;
var precision = pos.coords.accuracy;
// var contenedor = document.getElementById("map")
document.getElementById("lti").value=latitud;  
// document.getElementsByName("latitud").value=latitud;  
// document.getElementsByName("longitud").value=longitud;  
//   document.getElementById("lti").innerHTML=latitud;  
document.getElementById("lgi").value=longitud;
// document.getElementById("psc").innerHTML=precision;
}
function error(errorCode) {
if(errorCode.code == 1)
alert("No has permitido buscar tu localizacion")
else if (errorCode.code==2)
alert("Posicion no disponible")
else
alert("Ha ocurrido un error")
}