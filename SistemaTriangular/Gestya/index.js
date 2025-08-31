function initMap() {
    const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 12,
        center: { lat: -31.407295723281997, lng:  -64.18836518176542 },
        mapTypeId: "terrain",

      });
      const flightPlanCoordinates=[];
    $.ajax({        
        url:'Posiciones.php',
        type:'post',
        success: function (respuesta) {
          var jsonData = JSON.parse(respuesta);
            console.log(jsonData);
          for(var i=0;i<jsonData.lat.length;i++){
            flightPlanCoordinates.push({
                lat: Number(jsonData.lat[i]),
                lng: Number(jsonData.lng[i])
              });
          }

          const flightPath = new google.maps.Polyline({
            path: flightPlanCoordinates,
            geodesic: true,
            strokeColor: "#FF0000",
            strokeOpacity: 1.0,
            strokeWeight: 2,
          });
          flightPath.setMap(map);
      
        }
      });
  }

  window.initMap = initMap;