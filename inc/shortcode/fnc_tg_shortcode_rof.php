<?php

defined('ABSPATH') ?: exit();

/**
 * Shortcode to render globe on front
 */
add_shortcode('ciso_threat_globe', function () {

    // setup glb path
    $glb_path = CISO_TG_URI . 'inc/shortcode/assets/glb/globe9.glb';

    // get acf field with key field_646b76d7fb7fd
    $globe_text = get_field('field_646b76d7fb7fd', 'option');

    // encode $globe_text to json for use in our js
    $globe_text_json = json_encode($globe_text);

    // add encoded json to our js
    echo '<script>const cisoGlobeText = ' . $globe_text_json . ';</script>';

    ob_start(); ?>

    <!-- Remove this when import maps will be widely supported -->
    <script async src="https://unpkg.com/es-module-shims@1.6.3/dist/es-module-shims.js"></script>

    <script type="importmap">
        {
				"imports": {
					"three": "/wp-content/plugins/ciso-threat-globe/inc/shortcode/assets/js/three/build/three.module.js",
					"three/addons/": "/wp-content/plugins/ciso-threat-globe/inc/shortcode/assets/js/three/addons/",
					"turf": "/wp-content/plugins/ciso-threat-globe/inc/shortcode/assets/js/utils/turf.min.js"
				}
			}
		</script>


    <!-- script which loads our model -->
    <script type="module">
        // import three.js
        import * as THREE from 'three';

        // import orbit controls
        import {
            OrbitControls
        } from 'three/addons/controls/OrbitControls.js';

        // import gtlf loader
        import {
            GLTFLoader
        } from 'three/addons/loaders/GLTFLoader.js';

        // import turf
        import * as turf from 'turf';

        /* Set up the scene, camera, and renderer */
        var scene = new THREE.Scene();
        var camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        var renderer = new THREE.WebGLRenderer({
            antialias: true
        });

        /* Append object to canvas */
        var canvas = document.getElementById('globeViz');
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        canvas.appendChild(renderer.domElement);

        /* Load the GLB model */

        // init GTLF/GLB loader
        var loader = new GLTFLoader();

        // load
        loader.load(
            '<?php echo $glb_path; ?>',
            
            function(gltf) {

                var model = gltf.scene;
                model.scale.set(2.5, 2.5, 2.5);
                scene.add(model);

                // Traverse the model's children to find selectable objects
                model.traverse(function(obj) {
                    
                    // if object name is "clouds", make children clickthrough
                    if (obj.name === 'clouds') {

                        // make children clickthrough
                        obj.children.forEach(function(child) {
                            child.renderOrder = 1;
                            child.material.depthTest = false;
                        });
                    } 
                });

                // Calculate the bounding box of the model
                var box = new THREE.Box3().setFromObject(model);

                // Track mouse position relative to the canvas
                const canvasBounds = renderer.domElement.getBoundingClientRect();
                const mouse = new THREE.Vector2();

                function updateMousePosition(event) {
                    mouse.x = ((event.clientX - canvasBounds.left) / canvasBounds.width) * 2 - 1;
                    mouse.y = -((event.clientY - canvasBounds.top) / canvasBounds.height) * 2 + 1;
                }


                // Adjust the camera position based on the model size
                var size = box.getSize(new THREE.Vector3());
                var maxDim = Math.max(size.x, size.y, size.z);
                var fov = camera.fov * (Math.PI / 180);

                // Set up OrbitControls
                var controls = new OrbitControls(camera, renderer.domElement);
                controls.enableDamping = true; // Add damping effect to smooth interactions
                controls.target.set(0, 0, 0); // Set the target to the center of the scene

                // Set the center as the target for OrbitControls
                var center = box.getCenter(new THREE.Vector3());
                controls.target.copy(center);

                // Set up camera position
                camera.position.z = 5;
                camera.position.y = 2.5;

                // Add ambient light to scene
                var ambientLight = new THREE.AmbientLight(0xffffff, 0.75);
                scene.add(ambientLight);

                // Add directional light to scene
                var directionalLight = new THREE.DirectionalLight(0xffffff, 0.9);
                directionalLight.position.set(1, 0.5, 0);
                scene.add(directionalLight);

                // Create the animation mixer
                var mixer = new THREE.AnimationMixer(model);
                gltf.animations.forEach(function(clip) {
                    mixer.clipAction(clip).play();
                });

                // Change scene background color
                scene.background = new THREE.Color(0x222222);

                // Set up raycaster
                var raycaster = new THREE.Raycaster();


                /* Animate the model */
                function animate() {
                    requestAnimationFrame(animate);
                    mixer.update(0.01); // Adjust animation speed if needed
                    controls.update(); // Update the OrbitControls
                    renderer.render(scene, camera);
                }
                animate();
            },
            undefined,
            function(error) {
                console.error(error);
            },

        );

        // find globeViz parent .av-section-cont-open, remove class .container and add class .container-fluid
        jQuery('#globeViz').parents('.av-section-cont-open').removeClass('container').addClass('container-fluid');
        jQuery('#globeViz').parents('.content').css('padding', '0px');
    </script>

    <div id="loadingScreen"></div>


    <div id="globeViz" style="border-bottom: 10px solid transparent; border-bottom-left-radius: 150px; overflow: hidden;"></div>

<?php
    return ob_get_clean();
});
