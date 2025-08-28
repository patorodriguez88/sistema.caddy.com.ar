/**
 * @param String name
 * @return String
 */

 function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}    

var prodId = getParameterByName('error');

if(prodId==1){

    $.NotificationApp.send("Error","Error en la importacion de del archivo","bottom-right","success","Icon");
    
}