<script>

  (function() {

  angular.module('mainModule')
  .factory('viajeSrv',['$http','$q', function($http, $q){
     var service = {};
     var cancelers = {};

     service.cancel = function(funcname) {
        if ( cancelers[funcname] !== undefined ) {
             cancelers[funcname].resolve({httpCanceled: true});
        }
     }

     service.makeReport = function(vehi_id,fromByBackEnd,toByBackEnd,type,por_evento,minutos,velocidad,even_ini,even_fin,mod,vehi_nombre,combustible,currentLang,client_id,empr_id,even_desc_ini,even_desc_fin,archivo_db_lite) {
         var def = $q.defer();
         cancelers['makeReport'] = $q.defer();
         $http({
             method: 'post',
             url: '/STREETX/SERVER/REPORTS/TRAVEL/VIAJESRV.JSS?ACTION=makeReport',
             timeout: cancelers['makeReport'].promise,
             data: { 'vehi_id':vehi_id,'fromByBackEnd':fromByBackEnd,'toByBackEnd':toByBackEnd,'type':type,'por_evento':por_evento,'minutos':minutos,'velocidad':velocidad,'even_ini':even_ini,'even_fin':even_fin,'mod':mod,'vehi_nombre':vehi_nombre,'combustible':combustible,'currentLang':currentLang,'client_id':client_id,'empr_id':empr_id,'even_desc_ini':even_desc_ini,'even_desc_fin':even_desc_fin,'archivo_db_lite':archivo_db_lite,'__jssExtra':{"sourceModule":{"translateKey":"MAIN.MODULES.REPORT_TRAVEL"},"timer":false} }
         }).then(function(response) { delete cancelers['makeReport'];def.resolve(response.data);}, function(response) { cancelers['makeReport'];def.reject(response); });
         return def.promise;
     };

     service.getMapPosi = function(vehi_id,from,to,currentLang,archivo_db_lite) {
         var def = $q.defer();
         cancelers['getMapPosi'] = $q.defer();
         $http({
             method: 'post',
             url: '/STREETX/SERVER/REPORTS/TRAVEL/VIAJESRV.JSS?ACTION=getMapPosi',
             timeout: cancelers['getMapPosi'].promise,
             data: { 'vehi_id':vehi_id,'from':from,'to':to,'currentLang':currentLang,'archivo_db_lite':archivo_db_lite,'__jssExtra':{"sourceModule":{"translateKey":"MAIN.MODULES.REPORT_TRAVEL"},"timer":false} }
         }).then(function(response) { delete cancelers['getMapPosi'];def.resolve(response.data);}, function(response) { cancelers['getMapPosi'];def.reject(response); });
         return def.promise;
     };

     service.getEventByVehicles = function(oVehiIds) {
         var def = $q.defer();
         cancelers['getEventByVehicles'] = $q.defer();
         $http({
             method: 'post',
             url: '/STREETX/SERVER/REPORTS/TRAVEL/VIAJESRV.JSS?ACTION=getEventByVehicles',
             timeout: cancelers['getEventByVehicles'].promise,
             data: { 'oVehiIds':oVehiIds,'__jssExtra':{"sourceModule":{"translateKey":"MAIN.MODULES.REPORT_TRAVEL"},"timer":false} }
         }).then(function(response) { delete cancelers['getEventByVehicles'];def.resolve(response.data);}, function(response) { cancelers['getEventByVehicles'];def.reject(response); });
         return def.promise;
     };

     service.getUbicacionPagina = function(ids,empresaId,vehiculoId,dbName) {
         var def = $q.defer();
         cancelers['getUbicacionPagina'] = $q.defer();
         $http({
             method: 'post',
             url: '/STREETX/SERVER/REPORTS/TRAVEL/VIAJESRV.JSS?ACTION=getUbicacionPagina',
             timeout: cancelers['getUbicacionPagina'].promise,
             data: { 'ids':ids,'empresaId':empresaId,'vehiculoId':vehiculoId,'dbName':dbName,'__jssExtra':{"sourceModule":{"translateKey":"MAIN.MODULES.REPORT_TRAVEL"},"timer":false} }
         }).then(function(response) { delete cancelers['getUbicacionPagina'];def.resolve(response.data);}, function(response) { cancelers['getUbicacionPagina'];def.reject(response); });
         return def.promise;
     };

   return service; 
  }]);

})();
  
  this.setHeader('content-type', 'application/json; charset=utf-8')  
        $.ajax({
        data: {
        "user":"triangular",
        "pwd":"4986",
        "action":"DATOSACTUALES",
        },
        url:"http://infoweb.gestya.com/Api/WServiceDev.jss",
        type:"post",
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
        success: function (response) {
          console.log(response);
//           var jsonData = JSON.parse(response);
//           var jsonData= response.json();
// "nombre": "prueba01",
// "alias": "movil 1",
// "patente": "AFV 132",
// "gps": "123456789101177",
// "latitud": "-34.55568800",
// "longitud": "-58.46693600",
// "fecha": "21/09/2016 11:48:32",
// "sentido": "217",
// "velocidad": "0 ",
// "evento": "8"  },
// console.debug(response);
// console.debug(jsonData.nombre);
// console.log(jsonData.nombre);

        }
        });


  
//   fetch(myRequest)
//   .then(response => {
//     if (response.status === 200) {
//       return response.json();
//     } else {
//       throw new Error('Something went wrong on api server!');
//     }
//   })
//   .then(response => {
//     console.debug(response);
//     // ...
//   }).catch(error => {
//     console.error(error);
//   });
</script>
  