// This example adds a user-editable rectangle to the map.
// When the user changes the bounds of the rectangle,
// an info window pops up displaying the new bounds.
let rectangle;
let map;
let infoWindow;
let milat;
let milng;


function initMap() {
  map = new google.maps.Map(document.getElementById("map"), {
    center: { lat: 44.5452, lng: -78.5389 },
    zoom: 9,
  });

  const milat=-25.363;
  const milng=131.044;


  const myLatLng = { lat: milat, lng: milng };
  
  new google.maps.Marker({
    position: myLatLng,
    map,
    title: "Hello World!",
  });
  
  
  const bounds = {
    north: 44.599,
    south: 44.49,
    east: -78.443,
    west: -78.649,
  };
  // Define the rectangle and set its editable property to true.
  rectangle = new google.maps.Rectangle({
    bounds: bounds,
    editable: true,
    draggable: true,
  });
  rectangle.setMap(map);
  // Add an event listener on the rectangle.
  rectangle.addListener("bounds_changed", showNewRect);
  // Define an info window on the map.
  infoWindow = new google.maps.InfoWindow();
}

/** Show the new coordinates for the rectangle in an info window. */
function showNewRect() {
  const ne = rectangle.getBounds().getNorthEast();
  const sw = rectangle.getBounds().getSouthWest();
  const contentString =
    "<b>Rectangle moved." + milat + "</b><br>" +
    "New north-east corner: " +
    ne.lat() +
    ", " +
    ne.lng() +
    "<br>" +
    "New south-west corner: " +
    sw.lat() +
    ", " +
    sw.lng();
  // Set the info window's content and position.
  infoWindow.setContent(contentString);
  infoWindow.setPosition(ne);
  infoWindow.open(map);
  $.ajax({
      data: {'nelat':ne.lat(),'nelng':ne.lng(),'swlat':sw.lat(),'swlng':sw.lng()},
      url:'zonas.php',
      type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
      success: function(response)
       {
          var jsonData = JSON.parse(response);
         if(jsonData.success==1){
           alert('ok');
         }
       }
  });
}