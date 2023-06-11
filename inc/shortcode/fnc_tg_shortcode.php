<?php

defined('ABSPATH') ?: exit();

/**
 * Shortcode to render globe on front
 */
add_shortcode('ciso_threat_globe', function () {


    // get acf field with key field_646b76d7fb7fd
    $globe_text = get_field('field_646b76d7fb7fd', 'option');

    // encode $globe_text to json for use in our js
    $globe_text_json = json_encode($globe_text);

    // add encoded json to our js
    echo '<script>const cisoGlobeText = ' . $globe_text_json . ';</script>';

    ob_start(); ?>

    <script src="//unpkg.com/three"></script>
    <script src="//unpkg.com/globe.gl"></script>
    <script src="https://npmcdn.com/@turf/turf/turf.min.js"></script>

    <!-- container to hold random code -->
    <div id="cisoRandomCodeCont" style="position: absolute; top: 0; left: 0; z-index: 9999; width: 100%; height: 100%; pointer-events: none; overflow: hidden;"></div>

    <!-- globe left and right popup parent container with id cisoCountryTextCont -->
    <div id="cisoCountryTextCont" style="position: absolute; top: 0; left: 0; z-index: 9999; width: 100%; height: 100%; pointer-events: none; display: none;">


        <!-- insert eye png img, aligned to horizontal and vertical center of parent container -->
        <img src="<?php echo CISO_TG_URI . 'inc/shortcode/assets/img/rof/eye.png' ?>" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; width: 100%; height: 100%;">


        <!-- left hidden data container id cisoGlobePopupLeft -->
        <div id="cisoGlobePopupLeft" style="position: absolute; top: 31vh; left: 7vw; z-index: 9999; color: white; padding: 20px; border-radius: 5px; width: 23vw; text-align: right;"></div>

        <!-- right hidden data container id cisoGlobePopupRight -->
        <div id="cisoGlobePopupRight" style="position: absolute; width: 23vw; top: 36vh; right: 7vw; z-index: 9999; color: white; padding: 20px; border-radius: 5px;"></div>

    </div>

    <div id="globeViz" style="border-bottom: 10px solid transparent; border-bottom-left-radius: 150px; overflow: hidden;"></div>

    <!-- container which holds call to action text and website logo -->
    <div id="cisoGlobeCallToActionCont" style="position: absolute; bottom: 3vh; right: 19vw; z-index: 9999; pointer-events: none; height: 13vh; width: 79vw; background: white; border: 0px solid transparent; border-bottom-left-radius: 122px; border-top-right-radius: 3px; border-bottom-right-radius: 3px; border-top-left-radius: 3px;">

        <!-- insert call to action text -->
        <div id="cisoGlobeCallToActionText" style="position: absolute; bottom: 3.5vh; left: 0; z-index: 9999; color: #242424; padding: 20px; border-radius: 5px; width: 78vw; text-align: right; font-size: 60px; font-weight: bold; pointer-events: none; text-transform: uppercase;
    text-shadow: -2px 1px 2px lightgrey;">Is your bussiness under attack?</div>

        <!-- insert website logo -->
        <img src="<?php echo CISO_TG_URI . 'inc/shortcode/assets/img/globe-logo.png' ?>" style="position: absolute; bottom: 0; right: -18.5vw; z-index: 9999; pointer-events: none;">

    </div>

    <script>
        $ = jQuery;

        // fetch you geojson data
        fetch('<?php echo CISO_TG_URI . 'inc/shortcode/assets/geo_data/countries.geojson' ?>').then(res => res.json()).then(countries => {

            /**
             * Define country features from geojson
             */
            const features = countries.features;

            // log country names to console
            // console.log(features.map((feature) => feature.properties.NAME));

            // log country text to console
            console.log(cisoGlobeText);


            /**
             * Countries allowed (according DLA Piper website)
             */
            const allowedCountries = [
                'Morocco',
                'South Africa',
                'Australia',
                'China',
                'Japan',
                'South Korea',
                'New Zealand',
                'Singapore',
                'Thailand',
                'Austria',
                'Belgium',
                'Czech Republic',
                'Denmark',
                'Finland',
                'France',
                'Germany',
                'Hungary',
                'Ireland',
                'Italy',
                'Luxembourg',
                'Netherlands',
                'Norway',
                'Poland',
                'Portugal',
                'Romania',
                'Slovakia',
                'Spain',
                'Sweden',
                'United Kingdom',
                'Argentina',
                'Brazil',
                'Chile',
                'Columbia',
                'Mexico',
                'Peru',
                'Puerto Rico',
                'Bahrain',
                'Oman',
                'Qatar',
                'United Arab Emirates',
                'Canada',
                'United States of America',
            ];

            /**
             * Set up our Threat Arcs data
             */
            const arcsData = features
                .map((feature) => {
                    let startLng, startLat;

                    if (feature.geometry.type === 'Polygon') {
                        [startLng, startLat] = feature.geometry.coordinates[0][0];
                    } else if (feature.geometry.type === 'MultiPolygon') {
                        const multiPolygonCoordinates = feature.geometry.coordinates;
                        const multiPolygon = turf.multiPolygon(multiPolygonCoordinates);
                        const centroid = turf.centroid(multiPolygon);

                        [startLng, startLat] = centroid.geometry.coordinates;
                    }

                    // Randomly select a different feature
                    const randomFeatureIndex = Math.floor(Math.random() * features.length);
                    const randomFeature = features[randomFeatureIndex];

                    // Get a random coordinate from the selected feature
                    let randomCoordinate;
                    if (randomFeature.geometry.type === 'Polygon') {
                        randomCoordinate = randomFeature.geometry.coordinates[0][0];
                    } else if (randomFeature.geometry.type === 'MultiPolygon') {
                        const randomMultiPolygonCoordinates = randomFeature.geometry.coordinates;
                        const randomMultiPolygon = turf.multiPolygon(randomMultiPolygonCoordinates);
                        const randomCentroid = turf.centroid(randomMultiPolygon);

                        randomCoordinate = randomCentroid.geometry.coordinates;
                    }

                    const [endLng, endLat] = randomCoordinate;

                    return {
                        startLat,
                        startLng,
                        endLat,
                        endLng,
                        color: 'rgba(193, 4, 46, 0.39)',
                    };
                });


            /**
             * Setup county names object so that we can use it to assign different label sizes in lablesData per country
             */
            const countryNamesSizes = {

                'Morocco': 2,
                'South Africa': 2,
                'Australia': 2,
                'China': 3,
                'Japan': 1.5,
                'South Korea': 1,
                'New Zealand': 1,
                'Singapore': 1,
                'Thailand': 2,
                'Austria': 1,
                'Belgium': 1,
                'Czech Republic': 1,
                'Denmark': 1,
                'Finland': 1.5,
                'France': 1.5,
                'Germany': 1,
                'Hungary': 1,
                'Ireland': 1,
                'Italy': 1.5,
                'Luxembourg': 1,
                'Netherlands': 1,
                'Norway': 2,
                'Poland': 1,
                'Portugal': 1,
                'Romania': 1,
                'Slovakia': 1,
                'Spain': 1,
                'Sweden': 1,
                'United Kingdom': 1,
                'Argentina': 2,
                'Brazil': 3,
                'Chile': 1.5,
                'Columbia': 1,
                'Mexico': 2,
                'Peru': 1.5,
                'Puerto Rico': 1.5,
                'Bahrain': 1,
                'Oman': 1,
                'Qatar': 1,
                'United Arab Emirates': 1.5,
                'Canada': 2,
                'United States of America': 2
            };

            /**
             * Set up our county labels data based on allowed list of countries
             */
            const labelsData = features
                .filter((feature) => {
                    // Filter the features based on the allowed countries
                    const name = feature.properties.NAME; // Adjust this based on your GeoJSON file
                    return allowedCountries.includes(name);
                })
                .map((feature) => {

                    let lng, lat;

                    // if simple polygon
                    if (feature.geometry.type === 'Polygon') {

                        const centroid = turf.centroid(feature);

                        lng = centroid.geometry.coordinates[0];
                        lat = centroid.geometry.coordinates[1]

                        // if multi polygon
                    } else if (feature.geometry.type === 'MultiPolygon') {

                        const multiPolygonCoordinates = feature.geometry.coordinates;
                        const multiPolygon = turf.multiPolygon(multiPolygonCoordinates);

                        const centroid = turf.centroid(multiPolygon);

                        lng = centroid.geometry.coordinates[0];
                        lat = centroid.geometry.coordinates[1]

                    }

                    const name = feature.properties.NAME;

                    return {
                        lat,
                        lng,
                        name,
                        color: 'white',
                        size: countryNamesSizes[name]
                    };
                });

            /**
             * Loop to fine tune our label positioning
             */
            $.each(labelsData, function(i, data) {

                // US
                if (data.name === 'United States of America') {
                    data.lng = -100;
                    data.lat = 38;
                    data.name = 'United States';
                }

                // Argentina
                if (data.name === 'Argentina') {
                    data.lng = -60;
                    data.lat = -30;
                }

                // Canada
                if (data.name === 'Canada') {
                    data.lng = -100;
                    data.lat = 55;
                }
            });

            /**
             * Our Globe/planet/world initializer
             */
            const world = Globe()
                .globeImageUrl('<?php echo CISO_TG_URI . 'inc/shortcode/assets/img/rof/night_red_8k.jpg' ?>')
                .bumpImageUrl('<?php echo CISO_TG_URI . 'inc/shortcode/assets/img/three_globe/earth-topology.png' ?>')
                .atmosphereAltitude(0.2)
                .arcsData(arcsData)
                .arcColor('color')
                .arcDashLength(0.5)
                .arcDashGap(2)
                .arcDashInitialGap(() => Math.random() * 5)
                .arcDashAnimateTime(1500)
                .arcStroke(1.2)
                .arcCurveResolution(32)
                .arcAltitudeAutoScale(0.4)
                .onArcClick(d => {
                    // disable arc click
                    return;
                })
                .onArcHover(d => {
                    // disable arc hover
                    return;
                })
                .labelsData(labelsData)
                .labelText('name')
                .labelSize('size')
                .labelResolution(3)
                .labelAltitude(0.0035)
                .labelColor('color')
                .polygonsData(countries.features.filter(d => d.properties.ISO_A2 !== 'AQ'))
                .polygonCapColor(() => 'rgba(28, 96, 132, 0.12)')
                .polygonSideColor(() => 'rgba(200, 0, 0, 0.05)')
                .polygonStrokeColor(() => '#111')
                .polygonAltitude(0.005)
                .onPolygonClick(d => {
                    // if county in allowed list
                    if (allowedCountries.includes(d.properties.NAME)) {

                        // query cisoGlobalText for relevant data: select_country = country name, threat_rating = threat rating, impact_rating = impact rating, privacy_law = privacy law for that country
                        const select_country = cisoGlobeText.filter(({
                            select_country
                        }) => select_country === d.properties.NAME);

                        // check if threat rating is defined, set to correct key if false, else set to N/A
                        const threat_rating = select_country.length ? select_country[0].threat_rating : 'N/A';

                        // check if impact rating is defined, set to correct key if false, else set to N/A
                        const impact_rating = select_country.length ? select_country[0].impact_rating : 'N/A';

                        // check if privacy law is defined, set to correct key if false, else set to Privacy law still to be provided
                        const privacy_law = select_country.length ? select_country[0].privacy_law : 'Privacy Law still to be provided';

                        // add country name and privacy law to left popup
                        $('#cisoGlobePopupLeft').html('<h3 style="color: white; font-size: 24px;">' + d.properties.NAME + '</h3><p>Privacy Law: ' + privacy_law + '</p>');

                        // add threat rating and impact rating to right popup and calculate threat x impact rating, to be displayed as Threat X Impact Rating
                        $('#cisoGlobePopupRight').html('<h3 style="color: white; font-size: 24px;">Threat Ratings</h3><p style="color: #c40f38; font-weight: bold;">' + d.properties.NAME + ' Cyber Threat Rating</p><p style="font-size: 18px;">Threat Rating: <span style="color: #c40f38; font-weight: bold;">' + threat_rating + '</span></p><p>Impact rating: <span style="color: #c40f38;font-weight: bold;">' + impact_rating + '</span></p><p>Threat X Impact: <span style="color: #c40f38;font-weight: bold;">' + threat_rating * impact_rating + '</span></p>');

                        // fade in country text container
                        $('#cisoCountryTextCont').fadeIn(1000);

                        // fade out country text container after 10 seconds
                        setTimeout(() => {
                            $('#cisoCountryTextCont').fadeOut(1000);
                        }, 10000);

                        // temporary disable auto rotate
                        world.controls().autoRotateSpeed = 0;

                        // start autorotate after 10 seconds
                        setTimeout(() => {
                            world.controls().autoRotateSpeed = 1;

                        }, 10000);
                    }
                })

            (document.getElementById('globeViz'))

            /**
             * Interaction related code
             */

            // disable mouse wheel zoom on enter globe container
            $('#globeViz').on('mouseenter', function() {
                world.controls().enableZoom = false;
            });

            // when user clicks on globe container, enable mouse wheel zoom
            $('#globeViz').on('click', function() {
                world.controls().enableZoom = true;
            });

            // when a user stops interacting with the globe itself for 5 seconds, disable mouse wheel zoom
            world.controls().addEventListener('end', function() {
                setTimeout(() => {
                    world.controls().enableZoom = false;
                }, 5000);
            });

            // disable zoom on container mouse leave
            $('#globeViz').on('mouseleave', function() {
                world.controls().enableZoom = false;
            });

            /**
             * Set up controls such as auto rotation, panning, zoom and so on
             */
            world.controls().autoRotate = true;
            world.controls().enablePan = false;
            world.controls().autoRotateSpeed = 0.85;
            world.controls().zoomSpeed = 200;

            /**
             * Set up camera
             */
            world.camera().fov = 45;
            world.camera().zoom = 0.7;
            world.camera().updateProjectionMatrix();

            /**
             * Setup lights
             */

            // ambient light
            const ambientLight = new THREE.AmbientLight(0xffffff);
            ambientLight.intensity = 4; // Increase the intensity for a brighter ambient light
            ambientLight.position.set(0, 0, 0);
            scene.add(ambientLight);

            // add another directional light of subtle yellowish orange color
            const directionalLight4 = new THREE.DirectionalLight(0xffe6b3, 1);
            directionalLight4.intensity = 0.25; // Increase the intensity for a brighter directional light
            directionalLight4.position.set(-5, 3, -5);
            scene.add(directionalLight4);

            // add another directional light representing the color of the moon
            const directionalLight5 = new THREE.DirectionalLight(0x999999, 1);
            directionalLight5.intensity = 0.25; // Increase the intensity for a brighter directional light
            directionalLight5.position.set(5, -3, -5);
            scene.add(directionalLight5);

            // add dark grey radial gradient background to scene
            scene.background = new THREE.Color(0x121212);

        });
    </script>

    <!-- set container to full width -->
    <script>
        $ = jQuery;
        $('#cisoGlobeCont').parents('.av-section-cont-open').removeClass('container').addClass('container-fluid');
        $('#cisoGlobeCont').parents('.content').css('padding', '0');
    </script>
<?php
    return ob_get_clean();
});
