<?php

defined('ABSPATH') ?: exit();

/**
 * Shortcode to render globe on front
 */
add_shortcode('ciso_threat_globe', function () {


    ob_start(); ?>

    <script src="<?php echo CISO_TG_URI . 'inc/shortcode/assets/js/three.js' ?>"></script>
    <script src="<?php echo CISO_TG_URI . 'inc/shortcode/assets/js/three-globe.min.js' ?>"></script>
    <script src="https://npmcdn.com/@turf/turf/turf.min.js"></script>
    <script src="//unpkg.com/three-geojson-geometry"></script>


    <div id="globeViz"></div>

    <script type="importmap">{ "imports": { "three": "https://unpkg.com/three/build/three.module.js", "turf": "https://npmcdn.com/@turf/turf/turf.min.js"}}</script>

    <script type="module">
        $ = jQuery;

        import {
            TrackballControls
        } from '//unpkg.com/three/examples/jsm/controls/TrackballControls.js';
        // import {
        //     GeoJsonGeometry
        // } from '//unpkg.com/three-geojson-geometry@1.3.1/dist/three-geojson-geometry.min.js';
        Object.assign(THREE, {
            TrackballControls
        });

        // import flatten from '@turf/flatten';

        let container = document.getElementById('globeViz');

        fetch('<?php echo CISO_TG_URI . 'inc/shortcode/assets/geo_data/ne_110m_admin_0_countries.geojson' ?>').then(res => res.json()).then(countries => {

            // get features from geojson file (loaded into 'countries')
            const features = countries.features;

            // DEBUG
            // $.each(features, function (i, data) { 
            //      console.log(data.properties.SOVEREIGNT);
            // });

            // Define the list of allowed countries for labels/arcs
            const allowedCountries = [
                'Morocco',
                'South Africa',
                'Australia',
                'China',
                'Hong Kong SAR China',
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

            // create threat arcs based on geo json data
            const arcsData = features.map((feature) => {

                //  lat and long variables
                let startLng, startLat;

                // Get the first coordinate of the first Polygon in the feature.
                if (feature.geometry.type === 'Polygon') {
                    [startLng, startLat] = feature.geometry.coordinates[0][0];
                } else if (feature.geometry.type === 'MultiPolygon') {
                    // Handle MultiPolygon. Here, we're just taking the first coordinate of the first Polygon.
                    // Adjust as necessary for your use case.
                    // [startLng, startLat] = feature.geometry.coordinates[0][0][0];

                    const multiPolygonCoordinates = feature.geometry.coordinates;
                    const multiPolygon = turf.multiPolygon(multiPolygonCoordinates);
                    const centroid = turf.centroid(multiPolygon);

                    [startLng, startLat] = centroid.geometry.coordinates;
                }

                // For the end point, let's just use a random point for now
                const endLat = (Math.random() - 0.5) * 180;
                const endLng = (Math.random() - 0.5) * 360;

                return {
                    startLat,
                    startLng,
                    endLat,
                    endLng,
                    color: 'rgba(193, 4, 46, 0.39)'
                };
            });

            function getRandomNumber(min, max) {
                return Math.random() * (max - min) + min;
            }

            const labelsData = features
                .filter((feature) => {
                    // Filter the features based on the allowed countries
                    const name = feature.properties.NAME; // Adjust this based on your GeoJSON file
                    return allowedCountries.includes(name);
                })
                .map((feature) => {

                    const centroid = turf.centroid(feature);
                    const [lng, lat] = centroid.geometry.coordinates;
                    const name = feature.properties.NAME;

                    return {
                        lat,
                        lng,
                        name,
                        color: '#666666',
                        size: feature.properties.LABELRANK > 3 ? 1.5 : feature.properties.LABELRANK / 1.5
                        // size: Math.random() *3
                    };
                });

            /**
             * Init Globe
             */
            const Globe = new ThreeGlobe()
                // .globeImageUrl('//unpkg.com/three-globe/example/img/earth-dark.jpg')
                .globeImageUrl('<?php echo CISO_TG_URI . 'inc/shortcode/assets/img/rof/night_red_8k.jpg' ?>')
                .bumpImageUrl('<?php echo CISO_TG_URI . 'inc/shortcode/assets/img/three_globe/earth-topology.png' ?>')
                .showAtmosphere(true)
                // .showGraticules(true)
                .atmosphereColor('red')
                .atmosphereAltitude(0.15)
                .arcsData(arcsData)
                .arcColor('color')
                .arcDashLength(0.5)
                .arcDashGap(2)
                .arcDashInitialGap(() => Math.random() * 5)
                .arcDashAnimateTime(2700)
                .arcStroke(1.2)
                .arcCurveResolution(512)
                .arcAltitudeAutoScale(0.5)
                .globeMaterial(new THREE.MeshPhongMaterial({
                    color: 0xffffff
                }))
                // .polygonsData(countries.features.filter(d => d.properties.ISO_A2 !== 'AQ'))
                .polygonsData(countries.features)
                .polygonCapColor(() => 'rgba(28, 96, 132, 0.12)')
                .polygonSideColor(() => 'rgba(200, 0, 0, 0.05)')
                .polygonStrokeColor(() => '#111')
                .polygonAltitude(0.015)
                .labelsData(labelsData)
                .labelText('name')
                .labelSize('size')
                .labelResolution(5)
                .labelAltitude(0.02)
                .labelColor('color');



            // Setup renderer
            const renderer = new THREE.WebGLRenderer({
                antialias: true
            });
            renderer.setSize(window.innerWidth, window.innerHeight);
            container.appendChild(renderer.domElement);



            // Setup scene
            const scene = new THREE.Scene();
            scene.add(Globe);

            // /// Array to hold all country meshes
            // const countryMeshes = [];

            // // For each country
            // const countryData = countries.features.flatMap((feature) => {
            //     const centroid = turf.centroid(feature);
            //     const [lng, lat] = centroid.geometry.coordinates;
            //     const name = feature.properties.NAME;

            //     const featureType = feature.geometry.type;
            //     const polygons = featureType === 'Polygon' ? [feature.geometry.coordinates] : feature.geometry.coordinates;

            //     return polygons.map((coordinates) => {
            //         coordinates = coordinates[0];

            //         const geometry = new THREE.GeoJsonGeometry({
            //             type: featureType,
            //             coordinates: [coordinates]
            //         }, 0.02); // Increase or decrease this for polygon extrusion

            //         const material = new THREE.MeshBasicMaterial({
            //             color: 0xffffff,
            //             opacity: 0.5,
            //             transparent: true
            //         }); // Change the material as desired
            //         const mesh = new THREE.Mesh(geometry, material);
            //         mesh.userData = {
            //             lat,
            //             lng,
            //             name
            //         };
            //         countryMeshes.push(mesh);

            //         return mesh;
            //     });
            // });

            // countryMeshes.forEach(mesh => scene.add(mesh));

            // camera.position.z = 2; // Set camera position

            // // // Raycaster for mouse interaction
            // // const raycaster = new THREE.Raycaster();
            // // const mouse = new THREE.Vector2();

            // // Event listener for mouse movement
            // window.addEventListener('click', (event) => {
            //     event.preventDefault();

            //     mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
            //     mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

            //     raycaster.setFromCamera(mouse, camera);

            //     const intersects = raycaster.intersectObjects(countryMeshes);

            //     if (intersects.length > 0) {
            //         // Get first intersected object
            //         const {
            //             object
            //         } = intersects[0];

            //         // Do something with the country data
            //         console.log(object.userData);
            //     }
            // }, false);

            // Array to hold all country meshes
            const countryMeshes = [];

            // For each country
            countries.features.forEach((feature) => {
                // flatten MultiPolygons into individual Polygons
                const flattened = turf.flatten(feature);
                flattened.features.forEach(flatFeature => {
                    const {
                        geometry
                    } = flatFeature;
                    const [lng, lat] = turf.centroid(geometry).geometry.coordinates;
                    const name = feature.properties.NAME;

                    const coordinates = geometry.coordinates[0];

                    const threeGeometry = new THREE.GeoJsonGeometry({
                        type: 'Polygon',
                        coordinates: [coordinates]
                    }, 0.05);

                    const material = new THREE.MeshBasicMaterial({
                        color: 0xffffff,
                        opacity: 0.5,
                        transparent: true
                    });
                    const mesh = new THREE.Mesh(threeGeometry, material);
                    mesh.userData = {
                        lat,
                        lng,
                        name
                    };

                    countryMeshes.push(mesh);
                });
            });

            countryMeshes.forEach(mesh => scene.add(mesh));

            // camera.position.z = 2; // Set camera position

            // console.log(countryMeshes);

            // Raycaster for mouse interaction
            const raycaster = new THREE.Raycaster();
            const mouse = new THREE.Vector2();

            // Event listener for mouse movement
            window.addEventListener('click', (event) => {
                event.preventDefault();

                mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
                mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

                
                raycaster.setFromCamera(mouse, camera);
                
                const intersects = raycaster.intersectObjects(countryMeshes);
                
                console.log(intersects);
                if (intersects.length > 0) {
                    // Get first intersected object
                    const {
                        object
                    } = intersects[0];

                    // Do something with the country data
                    console.log(object.userData);
                }
            }, false);


            const ambientLight = new THREE.AmbientLight(0xffffff);
            ambientLight.intensity = 4; // Increase the intensity for a brighter ambient light
            scene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
            directionalLight.intensity = 2; // Increase the intensity for a brighter directional light
            scene.add(directionalLight);

            // Setup camera
            const camera = new THREE.PerspectiveCamera();
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            camera.position.z = 400;

            // Add camera controls
            const tbControls = new THREE.TrackballControls(camera, renderer.domElement);
            tbControls.minDistance = 101;
            tbControls.rotateSpeed = 3;
            tbControls.zoomSpeed = 0.8;

            // Custom function to check for label collision
            function checkLabelCollision(label1, label2) {
                // Calculate the distance between two labels using their positions and sizes
                const distance = Math.sqrt((label2.lng - label1.lng) + (label2.lat - label1.lat)) * 2;
                const minDistance = label1.size + label2.size; // Minimum required distance between labels

                return distance < minDistance; // Return true if labels collide, false otherwise
            }

            // Custom function to adjust label positions to prevent collision
            function adjustLabelPositions(labels) {
                for (let i = 0; i < labels.length; i++) {
                    for (let j = i + 1; j < labels.length; j++) {
                        const label1 = labels[i];
                        const label2 = labels[j];

                        if (checkLabelCollision(label1, label2)) {
                            // Labels collide, adjust their positions to prevent overlapping
                            // You can implement various strategies here, such as moving labels vertically or horizontally, increasing spacing, or adjusting font size
                            // For simplicity, let's move the second label vertically
                            label2.lat += 0.1; // Adjust the value as needed
                            // label2.size /= 1.25;
                        }
                    }
                }
            }


            // Kick-off renderer
            (function animate() { // IIFE
                // Frame cycle
                tbControls.update();

                // Perform label collision detection and adjust label positions
                adjustLabelPositions(labelsData);

                Globe.rotation.y += 0.002;
                renderer.render(scene, camera);
                requestAnimationFrame(animate);
            })();

            /**
             * Raycasting test
            //  */
            // function onPointerMove(event) {

            //     pointer.x = (event.clientX / renderer.domElement.clientWidth) * 2 - 1;
            //     pointer.y = -(event.clientY / renderer.domElement.clientHeight) * 2 + 1;
            //     raycaster.setFromCamera(pointer, camera);

            //     // See if the ray from the camera into the world hits one of our meshes
            //     const intersects = raycaster.intersectObject(Globe, TextTrackCue);

            //     // Toggle rotation bool for meshes that we clicked
            //     if (intersects.length > 0) {

            //         helper.position.set(0, 0, 0);
            //         helper.lookAt(intersects[0].face.normal);

            //         helper.position.copy(intersects[0].point);

            //     }

            // }

        });
    </script>

    <!-- set container to full width -->
    <script>
        // setup jQuery
        $ = jQuery;

        // add container fluid to globe parent container
        $('#cisoGlobeCont').parents('.av-section-cont-open').removeClass('container').addClass('container-fluid');
    </script>

    <style>
        .hotspot-marker {
            position: absolute;
            top: 0;
            left: 0;
            transform: translate(-50%, -50%);
            /* Customize the appearance of the hotspot marker */
        }
    </style>

<?php
    return ob_get_clean();
});
