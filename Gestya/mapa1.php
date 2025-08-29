<html>
  <head>
    <title>Simple Polylines</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <meta charset="utf-8">	
    
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>

    <!-- <link rel="stylesheet" type="text/css" href="style.css" /> -->
    <!-- <script type="" src="index.js"></script> -->
    <script type="text/javascript" src="index.js"></script>
  </head>
  <body>
    <div id="map"></div>
<style>
   #map {
    height: 100%;
  }
  
  /* 
   * Optional: Makes the sample page fill the window. 
   */
  html,
  body {
    height: 100%;
    margin: 0;
    padding: 0;
  }
  </style>

    <!-- 
     The `defer` attribute causes the callback to execute after the full HTML
     document has been parsed. For non-blocking uses, avoiding race conditions,
     and consistent behavior across browsers, consider loading using Promises
     with https://www.npmjs.com/package/@googlemaps/js-api-loader.
    -->
    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap&v=weekly"
      defer
    ></script>
  </body>
</html>