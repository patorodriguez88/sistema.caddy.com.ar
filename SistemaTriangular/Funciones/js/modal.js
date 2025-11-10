function traeryMotrarModal() {
  $('#modal_seguimiento').modal('show');
    /* [parametros si son necesarios] deberás pasarlo en su parte
       respectiva ya sea necesario por GET o POST; en el ejemplo
       no los puse en ningun lado */

    $.ajax( {
        url:"../Funciones/php/modal.php",
        cache:false, /* Evitamos cache */
        dataType: 'html', /* Se recibirá contenido HTML */
        success: function (data) {
         $('#modal_seguimiento').show(); 
                /* data es el HTML a insertar; deberás insertarlo
                   según como corresponda a la model que usas, a continuación
                   como yo la use usando BootstrapDialog (estructura básica). */
//             modal_seguimiento.show({
//                 /* Incluir ya sea fijo el título o colocarlo dentro de los parámetros de la función */
//                 title: "[titulo de la ventana]",
//                 message: data, /* aqui lo recibido (el HTML de la modal) */
//                 closeByBackdrop: false,
//                 closeByKeyboard: true,
//                 closable: true,
//                 size: modal_seguimiento.SIZE_SMALL,
//                 type: modal_seguimiento.TYPE_INFO,
//                 buttons: [
//                         {
//                             label: 'Guardar', /* Nombre del botón (en este caso "Guardar" */
//                             action: function(dialogRef){

//                                    /* Lo que se hara en caso de pulsar botón "guardar" */

//                             }
//                         },
//                         {
//                             label: 'Cerrar', /* Nombre del botón (en este caso "Cerrar" */
//                             action: function(dialogRef){
//                                     dialogRef.close(); /* Cerrar la modal sin hacer nada más */
//                             }
//                         }
//                 ]
//             });
        },
        error: function (jqXHR, textStatus, errorThrown) {
              /* Acción en caso de fallar/error el/en ajax */
        }
    }
    );

} // traeryMotrarModal
