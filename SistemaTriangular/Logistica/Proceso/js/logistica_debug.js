function verificar_rec(i){
    if(i==""){
        document.getElementById('nuevo_recorrido_t').checked=true;
    }else{
        document.getElementById('nuevo_recorrido_t').checked=false;
    }
}
function sendForm() {
    var valido = document.getElementById("kmregreso").value; //DEBERIAS REALIZAR LAS VALIDACIONES
    var kmsalida = parseFloat(document.getElementById("kmsalida").value);
    var valido1 = document.getElementById("comp").value; //DEBERIAS REALIZAR LAS VALIDACIONES
    var valido11= parseFloat(valido1);
    var maximo=(valido11*30)/100+(valido11)+(kmsalida);
  //    alert(maximo);
    if (valido > maximo) {
    alertify.log("Km Regreso " + valido + " superan los permitidos " +maximo+ " ( km. "+valido1+ "km. +/- 30%)","",0);
    document.getElementById("Orden").disabled=true;
    document.getElementById("Orden").style='opacity:0.5;filter:aplpha(opacity=50)';
    } else {
    document.getElementById("Orden").disabled=false;
    document.getElementById("Orden").style='';
    }
  }
  function verificar(){
    alertify.error("SEGURO VENCIDO, IMPOSIBLE SEGUIR"); 
    document.getElementById('botonCargar').style.disabled=true;
    document.getElementById("botonCargar").style='opacity:0.5;filter:aplpha(opacity=50)';
    }