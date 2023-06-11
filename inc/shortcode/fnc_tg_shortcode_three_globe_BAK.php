<?php

defined('ABSPATH') ?: exit();

/**
 * Shortcode to render globe on front
 */
add_shortcode('ciso_threat_globe', function () {


    ob_start(); ?>

    <!-- <script src="<?php echo CISO_TG_URI . 'inc/shortcode/assets/js/three.js' ?>"></script>
    <script src="<?php echo CISO_TG_URI . 'inc/shortcode/assets/js/three-globe.min.js' ?>"></script> -->

    <script src="//unpkg.com/three"></script>
  <script src="//unpkg.com/three-globe"></script>

    <div id="globeViz"></div>

    <script type="importmap">{ "imports": { "three": "https://unpkg.com/three/build/three.module.js" }}</script>

    <script type="module">
        import {
            TrackballControls
        } from '//unpkg.com/three/examples/jsm/controls/TrackballControls.js';
        Object.assign(THREE, {
            TrackballControls
        });

        fetch('<?php echo CISO_TG_URI . 'inc/shortcode/assets/geo_data/ne_110m_admin_0_countries.geojson' ?>').then(res => res.json()).then(countries => {

            // get features from geojson file (loaded into 'countries')
            const features = countries.features;

            // create threat arcs
            const arcsData = features.map((feature) => {

                // Get the first coordinate of the first Polygon in the feature.
                const [startLng, startLat] = feature.geometry.coordinates[0][0];

                // For the end point, let's just use a random point for now
                const endLat = (Math.random() - 0.5) * 180;
                const endLng = (Math.random() - 0.5) * 360;

                return {
                    startLat,
                    startLng,
                    endLat,
                    endLng,
                    color: '#c1042eb0'
                };
            });

            const Globe = new ThreeGlobe()
                // .globeImageUrl('//unpkg.com/three-globe/example/img/earth-dark.jpg')
                .globeImageUrl('<?php echo CISO_TG_URI . 'inc/shortcode/assets/img/rof/night_red_8k.jpg' ?>')
                .bumpImageUrl('<?php echo CISO_TG_URI . 'inc/shortcode/assets/img/three_globe/earth-topology.png' ?>')
                .arcsData(arcsData)
                .arcColor('color')
                .arcDashLength(0.5)
                .arcDashGap(2)
                .arcDashInitialGap(() => Math.random() * 5)
                .arcDashAnimateTime(3000)
                .arcStroke(1.2)
                .arcCurveResolution(512)
                .polygonsData(countries.features.filter(d => d.properties.ISO_A2 !== 'AQ'))
                .polygonCapColor(() => 'rgba(28, 96, 132, 0.12)')
                .polygonSideColor(() => 'rgba(200, 0, 0, 0.05)')
                .polygonStrokeColor(() => '#111');


            // Setup renderer
            const renderer = new THREE.WebGLRenderer();
            renderer.setSize(window.innerWidth, window.innerHeight);
            document.getElementById('globeViz').appendChild(renderer.domElement);

            // Setup scene
            const scene = new THREE.Scene();
            scene.add(Globe);
            scene.add(new THREE.AmbientLight(0xcccccc));
            scene.add(new THREE.DirectionalLight(0xffffff, 0.4));

            // Setup camera
            const camera = new THREE.PerspectiveCamera();
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            camera.position.z = 500;

            // Add camera controls
            const tbControls = new THREE.TrackballControls(camera, renderer.domElement);
            tbControls.minDistance = 101;
            tbControls.rotateSpeed = 5;
            tbControls.zoomSpeed = 0.8;

            // Kick-off renderer
            (function animate() { // IIFE
                // Frame cycle
                tbControls.update();

                Globe.rotation.y += 0.002;
                renderer.render(scene, camera);
                requestAnimationFrame(animate);
            })();

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
