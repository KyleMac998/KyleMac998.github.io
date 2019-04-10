
<!-- Fetching and processing data and using this with Google Maps
     Data feed URLs can be found on following page: https://earthquake.usgs.gov/earthquakes/feed/v1.0/geojson.php
     Author: Peter Barrie
     Date: 7 August 2018
Specifically:
    Web page with Google map. Click button to populate map with earthquake markers from a specific USGS data feed. Markers are not labelled.
    Note that these examples use data sources that end with .geojson. Do NOT use sources that end with .geojsonp
-->
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
<html>
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body,
        #container {
            height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: helvetica, arial, sans-serif;
        }

        #container .feed-heading {
            height: 60px;
            padding: 1em 0 0 1em;
            margin: 0;
        }


        #feedColumn {
            width: 30%;
            float: left;
            padding: 1em;
        }

        #feedSelector {
            top: 1em;
            left: 1em;
            background-color: #fff;
        }

        .child-prop,
        .feed-name,
        .feed-date {
            text-transform: capitalize;
        }

        .feed-date {
            width: 50%;
            border-bottom: 1px solid #999;
            margin: 2em 0em 1em 0em;
            padding-bottom: 0.2em;
            font-weight: bold;
        }

        .feed-name {
            padding: 0.5em;
            margin: 0 0.5em 0 0;
            background-color: #777;
            color: #fff;
            border-radius: 0.3em;
            cursor: pointer;
            transition: all linear 0.2s;
            border: none;
            outline: none;
            font-size: 1em;
        }

        .feed-name:hover {
            background-color: #555;
        }

        .child-prop {
            display: inline-block;
            margin: 0 0 0.5em 0;
        }

        /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */

        #map {
            top: -500px;
            height: 70%;
            width: 70%;
            float: right;
        }

        /* Optional settings. Do as you wish with these*/

        html,
        body {
            height: 96%;
            margin: 1%;
            padding: 0;
        }

        #other {
            height: auto;
            width: 50%;
        }
    </style>
</head>

<body>

    <div id='container'>
        <!-- On our web page, show a link to the earthquake data. This is just for learning purposes. -->
        <div id="feedColumn">
            <div id="feedSelector"></div>
        </div>
    </div>
    <div id="map"></div>

    <script>

    var map;
    //initMap() called when Google Maps API code is loaded - when web page is opened/refreshed
    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 2,
            center: new google.maps.LatLng(2.8, -187.3), // Center Map. Set this to any location that you like
            mapTypeId: 'terrain' // can be any valid type
        });
    }
        //The following data is used when constructing buttons. You will have to extend this, based upon the feeds at: https://earthquake.usgs.gov/earthquakes/feed/v1.0/geojson.php
        var quakeFeeds = {
            "past hour" : {
                "significant earthquakes": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/significant_hour.geojson",
                "all 4.5+": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/4.5_hour.geojson",
                "all 2.5+": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/2.5_hour.geojson",
                "all 1.0+": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/1.0_hour.geojson",
                "all earthquakes": "http://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_hour.geojson",

            },
            "past day": {
                "significant earthquakes": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/significant_day.geojson",
                "all 4.5": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/4.5_day.geojson",
                "all 2.5+": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/2.5_day.geojson",
                "all 1.0": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/1.0_day.geojson",
                "all earthquakes": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/4.5_day.geojson",

            },
            "past week": {

              "significant earthquakes": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/significant_week.geojson",
              "all 4.5": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/4.5_week.geojson",
              "all 2.5+": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/2.5_week.geojson",
              "all 1.0": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/1.0_week.geojson",
              "all earthquakes": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_week.geojson",

            },
            "past month": {
              "significant earthquakes": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/significant_month.geojson",
              "all 4.5": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/4.5_month.geojson",
              "all 2.5+": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/2.5_month.geojson",
              "all 1.0": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/1.0_month.geojson",
              "all earthquakes": "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_month.geojson",
            },


        };

        /* Function to construct a set of web page buttons of class: 'feed-name' where each button has a stored URL property */
        function makeChildProps(obj, currentProp) {
            var childProps = '';

            for (var prop in obj[currentProp]) {
                var el = "<div class='child-prop'><button class='feed-name' data-feedurl='" + obj[currentProp][prop] + "'>" + prop + "</button></div>";
                childProps += el;
            }

            return childProps;
        }

        /* construct the buttons (that include the geojson URL properties) */
        for (var prop in quakeFeeds) {
            if (!quakeFeeds.hasOwnProperty(prop)) {
                continue;
            }
            $('#feedSelector').append("<div class='feed-date'>" + prop + "</div>" + makeChildProps(quakeFeeds, prop));
            console.log(makeChildProps(quakeFeeds, prop));
        }
        /* end construction of buttons */

        /* respond to a button press of any button of 'feed-name' class */

        var map;
        //initMap() called when Google Maps API code is loaded - when web page is opened/refreshed
        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 2,
                center: new google.maps.LatLng(2.8, -187.3), // Center Map. Set this to any location that you like
                mapTypeId: 'terrain' // can be any valid type
            });
        }

        $(document).ready(function () {
          $('.feed-name').click(function (e) {
              // We fetch the earthquake feed associated with the actual button that has been pressed.
              // In this example we are not plotting on a map, just demonstrating how to get the data.

              // Set Google map  to its start state
              map = new google.maps.Map(document.getElementById('map'), {
                  zoom: 2,
                  center: new google.maps.LatLng(2.8, -187.3), // Center Map. Set this to any location that you like
                  mapTypeId: 'terrain' // can be any valid type
              });

              $.ajax({
                  url: $(e.target).data('feedurl'), // The GeoJSON URL associated with a specific button was stored in the button's properties when the button was created
                  success: function (data) {  // We've received the GeoJSON data
                      i = 0;
                      var markers = []; // We store markers in array
                      $.each(data.features, function (key, val) {  // Just get a single value ('place') and save it in an array
                      var coords = val.geometry.coordinates;
                      var latLng = new google.maps.LatLng(coords[1], coords[0]);


                      // Now create a new marker on the map
                      var marker = new google.maps.Marker({
                          position: latLng,
                          map: map,

                      });
                      var infowindow = new google.maps.InfoWindow({
                          content: "<h3>" + val.properties.title + "</h3><p><a href='" + val.properties.url + "' target=_blank>Details</a></p>"
                      });
                      marker.addListener('click', function (data) {
                          infowindow.open(map, marker); // Open the Google maps marker infoWindow
                      });

                      markers[i++] = marker; // Add the marker to array to be used by clusterer

                      });
                      var markerCluster = new MarkerClusterer(map, markers,
                          { imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m' });
                  }
              });
          });

        });
    </script>

    <!-- Need the following code for clustering Google maps markers-->
    <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js">
    </script>
    <!-- Need the following code for Google Maps. PLEASE INSERT YOUR OWN GOOGLE MAPS KEY BELOW -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDYDa0yOFJwOkLdW7fcpmXHtWDyXSZEHyI&callback=initMap">
    </script>

</body>

</html>
