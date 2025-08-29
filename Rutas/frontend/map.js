let map;
let markers = [];
let routePaths = [];
const routeColors = ['#007bff', '#28a745', '#ffc107', '#dc3545'];

window.onload = () => {
    initMap();

    document.getElementById('routeForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const waypoints = parseMultipleLatLng(document.getElementById('waypoints').value);
        const stopTimes = document.getElementById('stopTimes').value.split(',').map(time => parseInt(time.trim()));
        const vehicleType = document.getElementById('vehicleType').value;
        const driversCount = parseInt(document.getElementById('driversCount').value);
        const maxKm = parseFloat(document.getElementById('maxKm').value);
        const maxMinutes = parseFloat(document.getElementById('maxMinutes').value);
        const startTime = document.getElementById('startTime').value;
        const timeWindows = document.getElementById('timeWindows').value.split(',').map(tw => tw.trim());

        const payload = {
            waypoints,
            stopTimes,
            vehicleType,
            driversCount,
            maxKm: isNaN(maxKm) ? null : maxKm,
            maxMinutes: isNaN(maxMinutes) ? null : maxMinutes,
            startTime,
            timeWindows
        };

        console.log("Enviando payload al backend:", JSON.stringify(payload, null, 2)); // 📌 Verifica datos enviados

        document.getElementById('summary').innerHTML = '';

        try {
            const response = await fetch('../backend/getRoute.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            // const textResponse = await response.text();

//ver
            let textResponse = await response.text();
            try {
                const result = JSON.parse(textResponse);
            } catch (error) {
                console.error("No se pudo parsear JSON. Respuesta recibida:", textResponse);
                alert("Error en la respuesta del servidor. Ver consola.");
                return;
            }
//ver

            console.log("Respuesta del backend:", textResponse);

            const result = JSON.parse(textResponse);

            // if (result.status === 'success' && result.data.length > 0) {
            //     clearMap();
            //     result.data.forEach((route, index) => drawRoute(route, index));
            //     alert(`Se calcularon ${result.data.length} rutas independientes.`);
            // } else {
            //     alert('Error: ' + result.message);
            // }
            if (result.status === 'success' && result.data.routes.length > 0) {
                clearMap();
                result.data.routes.forEach((route, index) => drawRoute(route, index));
            
                // Mostrar el resumen en la interfaz
                let summaryHTML = "<h3>Resumen de rutas</h3><ul>";
                result.data.summary.forEach(route => {
                    summaryHTML += `
                        <li>
                            <b style="color:${route.color}">Ruta ${route.routeIndex}</b>: 
                            ${route.distance_km} km, 
                            ${route.waypoints_count} puntos, 
                            ${route.estimated_time} hs.
                        </li>
                    `;
                });
                summaryHTML += "</ul>";
            
                document.getElementById('summary').innerHTML = summaryHTML;
                alert(`Se calcularon ${result.data.routes.length} rutas independientes.`);
            }else{
                console.error("Error recibido del backend:", result.message);
                alert('Error: ' + result.message);

            }
        } catch (error) {
            // console.error("Error al parsear JSON:", error, "Respuesta:", textResponse);
            alert("Error en la respuesta del servidor. Revisa la consola para más detalles.");
        }
    });
};

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: -31.4201, lng: -64.1888 },
        zoom: 12
    });
}

function clearMap() {
    markers.forEach(marker => marker.setMap(null));
    markers = [];
    routePaths.forEach(path => path.setMap(null));
    routePaths = [];
}

// function parseMultipleLatLng(value) {
//     return value.split(';').map(coord => {
//         const [lat, lng] = coord.split(',').map(Number);
//         return { lat, lng };
//     });
// }
function parseMultipleLatLng(value) {
    return value.split(';').map(coord => {
        const parts = coord.split(',');
        if (parts.length !== 2) {
            console.error("Formato incorrecto en coordenada:", coord);
            return null;
        }
        const lat = parseFloat(parts[0].trim());
        const lng = parseFloat(parts[1].trim());
        if (isNaN(lat) || isNaN(lng)) {
            console.error("Valores inválidos en coordenada:", coord);
            return null;
        }
        return { lat, lng };
    }).filter(coord => coord !== null);
}

function drawRoute(route, index) {
    if (!route.legs || route.legs.length === 0) return;

    const path = [];
    const bounds = new google.maps.LatLngBounds();
    let stopNumber = 1;

    route.legs.forEach((leg, legIndex) => {
        const legPolyline = leg.polyline.encodedPolyline;
        if (!legPolyline) return;

        const legPath = decodePolyline(legPolyline);
        path.push(...legPath);

        const startLocation = {
            lat: leg.startLocation.latLng.latitude,
            lng: leg.startLocation.latLng.longitude
        };

        let marker;
        if (legIndex === 0) {
            marker = new google.maps.Marker({
                position: startLocation,
                map: map,
                label: { text: `${stopNumber++}`, color: "white", fontWeight: "bold", fontSize: "16px" },
                icon: { url: "http://maps.google.com/mapfiles/ms/icons/green-dot.png", scaledSize: new google.maps.Size(48, 48) },
                title: `Inicio Ruta ${index + 1}`
            });
        } else {
            marker = new google.maps.Marker({
                position: startLocation,
                map: map,
                label: { text: `${stopNumber++}`, color: "black", fontWeight: "bold", fontSize: "16px" },
                title: `Parada ${stopNumber - 1} - Ruta ${index + 1}`
            });
        }

        markers.push(marker);
        bounds.extend(startLocation);

        // Agregar InfoWindow a los marcadores
        const infoWindow = new google.maps.InfoWindow({
            content: marker.title
        });

        marker.addListener('mouseover', () => infoWindow.open(map, marker));
        marker.addListener('mouseout', () => infoWindow.close());

        if (legIndex === route.legs.length - 1) {
            const endLocation = {
                lat: leg.endLocation.latLng.latitude,
                lng: leg.endLocation.latLng.longitude
            };
            const endMarker = new google.maps.Marker({
                position: endLocation,
                map: map,
                label: { text: `${stopNumber}`, color: "white", fontWeight: "bold", fontSize: "16px" },
                icon: { url: "http://maps.google.com/mapfiles/ms/icons/red-dot.png", scaledSize: new google.maps.Size(48, 48) },
                title: `Destino Ruta ${index + 1}`
            });

            markers.push(endMarker);
            bounds.extend(endLocation);

            // Agregar InfoWindow al marcador de destino
            const endInfoWindow = new google.maps.InfoWindow({
                content: endMarker.title
            });

            endMarker.addListener('mouseover', () => endInfoWindow.open(map, endMarker));
            endMarker.addListener('mouseout', () => endInfoWindow.close());
        }
    });

    const color = routeColors[index % routeColors.length];

    const routePath = new google.maps.Polyline({
        path,
        geodesic: true,
        strokeColor: color,
        strokeOpacity: 0.9,
        strokeWeight: 5
    });

    routePath.setMap(map);
    routePaths.push(routePath);
    map.fitBounds(bounds);
}

// function drawRoute(route, index) {
//     if (!route.legs || route.legs.length === 0) return;

//     const path = [];
//     const bounds = new google.maps.LatLngBounds();
//     let stopNumber = 1;

//     route.legs.forEach((leg, legIndex) => {
//         const legPolyline = leg.polyline.encodedPolyline;
//         if (!legPolyline) return;

//         const legPath = decodePolyline(legPolyline);
//         path.push(...legPath);

//         const startLocation = {
//             lat: leg.startLocation.latLng.latitude,
//             lng: leg.startLocation.latLng.longitude
//         };

//         if (legIndex === 0) {
//             markers.push(new google.maps.Marker({
//                 position: startLocation,
//                 map: map,
//                 label: { text: `${stopNumber++}`, color: "white", fontWeight: "bold", fontSize: "16px" },
//                 icon: { url: "http://maps.google.com/mapfiles/ms/icons/green-dot.png", scaledSize: new google.maps.Size(48, 48) },
//                 title: `Inicio Ruta ${index + 1}`
//             }));
//         } else {
//             markers.push(new google.maps.Marker({
//                 position: startLocation,
//                 map: map,
//                 label: { text: `${stopNumber++}`, color: "black", fontWeight: "bold", fontSize: "16px" },
//                 title: `Parada ${stopNumber - 1} - Ruta ${index + 1}`
//             }));
//         }
//         bounds.extend(startLocation);

//         if (legIndex === route.legs.length - 1) {
//             const endLocation = {
//                 lat: leg.endLocation.latLng.latitude,
//                 lng: leg.endLocation.latLng.longitude
//             };
//             markers.push(new google.maps.Marker({
//                 position: endLocation,
//                 map: map,
//                 label: { text: `${stopNumber}`, color: "white", fontWeight: "bold", fontSize: "16px" },
//                 icon: { url: "http://maps.google.com/mapfiles/ms/icons/red-dot.png", scaledSize: new google.maps.Size(48, 48) },
//                 title: `Destino Ruta ${index + 1}`
//             }));
//             bounds.extend(endLocation);
//         }
//     });

//     const color = routeColors[index % routeColors.length];

//     const routePath = new google.maps.Polyline({
//         path,
//         geodesic: true,
//         strokeColor: color,
//         strokeOpacity: 0.9,
//         strokeWeight: 5
//     });

//     routePath.setMap(map);
//     routePaths.push(routePath);
//     map.fitBounds(bounds);
// }

function decodePolyline(encoded) {
    let points = [], index = 0, lat = 0, lng = 0;
    while (index < encoded.length) {
        let b, shift = 0, result = 0;
        do { b = encoded.charCodeAt(index++) - 63; result |= (b & 0x1f) << shift; shift += 5; } while (b >= 0x20);
        lat += (result & 1) ? ~(result >> 1) : (result >> 1);
        shift = result = 0;
        do { b = encoded.charCodeAt(index++) - 63; result |= (b & 0x1f) << shift; shift += 5; } while (b >= 0x20);
        lng += (result & 1) ? ~(result >> 1) : (result >> 1);
        points.push({ lat: lat / 1e5, lng: lng / 1e5 });
    }
    return points;
}