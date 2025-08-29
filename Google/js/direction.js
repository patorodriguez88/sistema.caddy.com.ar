  function initMap(){
        var inputstart = document.getElementById('start');
        var autocomplete = new google.maps.places.Autocomplete(inputstart);
        autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();
        if (place.address_components) {
        var components= place.address_components;
        var ciudad='';
        var provincia='';  
          for (var i = 0, component; component = components[i]; i++) {
            console.log(component);
            if (component.types[0] == 'administrative_area_level_1') {
               provincia=component['long_name'];
              if(provincia!='Córdoba'){
              alertify.error('La Provincia de origen debe ser Córdoba '+ ' no ' + provincia);          
              document.getElementById('start').value = '';
              document.getElementById('start').focus();
              break;  
              }
            }else if (component.types[0] == 'locality') {
              ciudad = component['long_name'];
//               realizaProceso(ciudad);
//               if(document.getElementById('resultado').innerText==0){
//               alertify.error('La Localidad de origen '+ ciudad +' no se encuentra a nuestro alcance, analice redespacho');          
//               document.getElementById('start').value = '';
//               document.getElementById('start').focus();
//               break;
//               } 
//               document.getElementById('Provincia_t').value = provincia;
              document.getElementById('Ciudad_t').value = ciudad;
//             }
//             }else if(component.types[0] == 'administrative_area_level_3'){
//              document.getElementById('Barrio_t').value= component['long_name'];   
            }else if(component.types[0] == 'postal_code'){
             document.getElementById('Codigo_Postal_t').value= component['short_name'];   
           }else if(component.types[0] == 'street_number'){
             document.getElementById('Numero_t').value= component['long_name'];   
           }else if(component.types[0] == 'route'){
             document.getElementById('Calle_t').value= component['long_name'];   
           }
          }
        }
        }); 
  }
function verDireccion(){
        var inputstartm = document.getElementById('Direccion_t');
        var autocompletem = new google.maps.places.Autocomplete(inputstartm);
        autocompletem.addListener('place_changed', function() {
        var placem = autocompletem.getPlace();
        if (placem.address_components) {
        var componentsm= placem.address_components;
        var ciudadm='';
        var provinciam='';  
          for (var i = 0, componentm; componentm = componentsm[i]; i++) {
            console.log(componentm);
            if (componentm.types[0] == 'administrative_area_level_1') {
               provinciam=componentm['long_name'];
              if(provinciam!='Córdoba'){
              alertify.error('La Provincia de origen debe ser Córdoba '+ ' no ' + provinciam);          
              document.getElementById('Direccion_t').value = '';
              document.getElementById('Direccion_t').focus();
              break;  
              }
            }else if (componentm.types[0] == 'locality') {
              ciudadm = componentm['long_name'];
//               realizaProceso(ciudad);
//               if(document.getElementById('resultado').innerText==0){
//               alertify.error('La Localidad de origen '+ ciudad +' no se encuentra a nuestro alcance, analice redespacho');          
//               document.getElementById('start').value = '';
//               document.getElementById('start').focus();
//               break;
//               } 
//               document.getElementById('Provincia_t').value = provincia;
              document.getElementById('Ciudad_m').value = ciudadm;
//             }
            }else if(componentm.types[0] == 'administrative_area_level_3'){
             document.getElementById('Barrio_m').value= componentm['long_name'];   
            }else if(componentm.types[0] == 'postal_code'){
             document.getElementById('CodigoPostal_m').value= componentm['short_name'];   
           }else if(componentm.types[0] == 'street_number'){
             document.getElementById('Numero_m').value= componentm['long_name'];   
           }else if(componentm.types[0] == 'route'){
             document.getElementById('Calle_m').value= componentm['long_name'];   
           }
          }
        }
        }); 
    }