const directions = [
    { lat: 50.262950, lng: -5.050700 },
    { lat: 51.507351, lng: -0.127758 },
    { lat: 52.205338, lng: 0.121817 },
    { lat: 52.486244, lng: -1.890401 },
    { lat: 52.954784, lng: -1.158109 },
    { lat: 53.383060, lng: -1.464800 },
    { lat: 53.480759, lng: -2.242631 },
    { lat: 53.799690, lng: -1.549100 }
];
// Break up our coordinates into chunks of 25 to avoid rate limits
let chunks = [];
let chunkSize = 25;

directions.forEach((waypoint, i) => {

let pi = Math.floor(i / (chunkSize - 1));
chunks[pi] = chunks[pi] || [];

if (chunks[pi].length === 0 && pi !== 0) {
    // make new chunks origin the same as last chunks destination
    chunks[pi] = [directions[i - 1]];
}

chunks[pi].push(waypoint);

});
// map callback once maps api loaded
function initMap() {

    let map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 54.4916899, lng: -2.211134 },
        zoom: 6,
        scrollwheel: true
    });
    
    }
    // build requests for api
let requests = [];

chunks.forEach((chunk) => {

    let origin = chunk[0];
    let destination = chunk[chunk.length - 1];

    // build waypoints without origin/destination
    let waypoints = chunk.slice(1, -1).map(waypoint => {
    return {
        location: new google.maps.LatLng(waypoint.lat, waypoint.lng),
        stopover: false
    }
    });

    requests.push({
    travelMode: 'DRIVING',
    origin: new google.maps.LatLng(origin.lat, origin.lng),
    destination: new google.maps.LatLng(destination.lat, destination.lng),
    waypoints: waypoints
    });

});
// return promise for each directions request
function buildRoute(requests) {

    let directionsService = new google.maps.DirectionsService();

    return Promise.all(requests.map((request) => {
    
    return new Promise(function(resolve) {
        directionsService.route(request, function(result, status) {
        if (status == google.maps.DirectionsStatus.OK) {
            return resolve(result.routes[0].legs[0]);
        }
        });
    });

    }));
}
// build up a polyline of our route
function animatePath(map, pathCoords) {

    let speed = 1000; // higher = slower/smoother
    
    let route = new google.maps.Polyline({
    path: [],
    geodesic: true,
    strokeColor: '#FF0000',
    strokeOpacity: 1.0,
    strokeWeight: 3,
    editable: false,
    map: map
    });

    // break into chunks for animation
    let chunk = Math.ceil(pathCoords.length / speed);
    let totalChunks = Math.ceil(pathCoords.length / chunk);
    let i = 1;

    function step() {

    // redraw polyline with bigger chunk
    route.setPath(pathCoords.slice(0, i * chunk));
    i++;

    if (i <= totalChunks) {
        window.requestAnimationFrame(step);
    }

    }

    window.requestAnimationFrame(step);

}
// wait for directions to be returned, then animate the route
buildRoute(requests).then((results) => {

    // flatten all paths into one set of coordinates
    let coords = results.flatMap((result) => {
    return result.steps.flatMap(step => step.path);
    });

    // finally animate path of our route
    animatePath(map, coords);

});