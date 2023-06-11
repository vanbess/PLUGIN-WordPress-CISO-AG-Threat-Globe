<?php

defined('ABSPATH') ?: exit();

/**
 * Shortcode to render globe on front
 */
add_shortcode('ciso_threat_globe', function () {

    // setup glb path
    $glb_path = CISO_TG_URI . 'inc/shortcode/assets/glb/globe9.glb';

    ob_start(); ?>

    <script src="https://cdn.jsdelivr.net/npm/three@0.134.0/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.134.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.134.0/examples/js/controls/OrbitControls.js"></script>

    <!-- container center (model container) -->
    <div id="globeProper"></div>

    <script>
        // setup jQuery
        $ = jQuery;

        // add container fluid to globe parent container
        $('#cisoGlobeCont').parents('.av-section-cont-open').removeClass('container').addClass('container-fluid');

        /*******************************
         * GLOBE IMPLEMENTATION STARTS
         *******************************/

        /* Set up the scene, camera, and renderer */
        var scene = new THREE.Scene();
        var camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        var renderer = new THREE.WebGLRenderer({
            antialias: true
        });

        /* Append object to canvas */
        var canvas = document.getElementById('globeProper');
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        canvas.appendChild(renderer.domElement);

        /* Create an array to store selectable objects */
        var selectableObjects = [];

        /* Function to add objects to the selectable list */
        function addToSelectable(obj) {
            selectableObjects.push(obj);
        }

        /* Load the GLB model */

        // init GTLF/GLB loader
        var loader = new THREE.GLTFLoader();

        const CLOUD_LAYER = 'we';

        // load
        loader.load(
            '<?php echo $glb_path; ?>',
            function(gltf) {
                var model = gltf.scene;
                model.scale.set(2.5, 2.5, 2.5);
                scene.add(model);

                // Traverse the model's children to find selectable objects
                model.traverse(function(obj) {
                    if (obj.isMesh) {
                        // // addToSelectable(obj);
                        // console.log(obj.name);
                        if (obj.name === 'Assembly-51') { // replace with the actual name of your cloud layer mesh
                            obj.material.transparent = true;
                            obj.material.depthWrite = false;
                            obj.layers.set(CLOUD_LAYER);
                        }
                    }
                });

                // Calculate the bounding box of the model
                var box = new THREE.Box3().setFromObject(model);
                var center = box.getCenter(new THREE.Vector3());

                // Set up mouse tracking (used to disable zoom on scroll if pointer not over object)
                let isMouseOverObject = false;

                // Track mouse position relative to the canvas
                const canvasBounds = renderer.domElement.getBoundingClientRect();
                const mouse = new THREE.Vector2();

                function updateMousePosition(event) {
                    mouse.x = ((event.clientX - canvasBounds.left) / canvasBounds.width) * 2 - 1;
                    mouse.y = -((event.clientY - canvasBounds.top) / canvasBounds.height) * 2 + 1;
                }

                // Event listener for mousemove event
                renderer.domElement.addEventListener('mousemove', (event) => {
                    updateMousePosition(event);

                    // Use raycasting to determine if the mouse is over the object
                    const raycaster = new THREE.Raycaster();
                    raycaster.setFromCamera(mouse, camera);
                    const intersects = raycaster.intersectObject(model);

                    if (intersects.length > 0) {
                        // Mouse is over the object
                        isMouseOverObject = true;
                    } else {
                        // Mouse is not over the object
                        isMouseOverObject = false;
                    }
                });


                // Adjust the camera position based on the model size
                var size = box.getSize(new THREE.Vector3());
                var maxDim = Math.max(size.x, size.y, size.z);
                var fov = camera.fov * (Math.PI / 180);
                // var distance = Math.abs(maxDim / Math.sin(fov / 2));
                // camera.position.z = distance;

                // Set up OrbitControls
                var controls = new THREE.OrbitControls(camera, renderer.domElement);
                controls.enableDamping = true; // Add damping effect to smooth interactions
                controls.target.set(0, 0, 0); // Set the target to the center of the scene

                // Set the center as the target for OrbitControls
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

                // Register an event listener to pause the animation mixer during manual interaction
                var isInteracting = false;
                controls.addEventListener('start', function() {
                    isInteracting = true;
                    mixer.timeScale = 0; // Pause the animation
                });

                controls.addEventListener('end', function() {
                    isInteracting = false;
                    mixer.timeScale = 1; // Resume the animation
                });

                // Change scene background color
                scene.background = new THREE.Color(0x222222);

                // Set up raycaster
                var raycaster = new THREE.Raycaster();
                raycaster.layers.set(CLOUD_LAYER);

                // Add a mouse click event listener
                window.addEventListener('click', onMouseClick, false);

                function onMouseClick(event) {

                    raycaster.setFromCamera(mouse, camera);

                    mouse.x = ((event.clientX - canvasBounds.left) / canvasBounds.width) * 2 - 1;
                    mouse.y = -((event.clientY - canvasBounds.top) / canvasBounds.height) * 2 + 1;

                    const intersects = raycaster.intersectObjects(scene.children, true);
                    for (let i = 0; i < intersects.length; i++) {
                        // Check if the intersected object is not in the cloud layer
                        if (!intersects[i].object.layers.test(CLOUD_LAYER)) {
                            // Handle intersection...

                            const clickedMesh = intersects[i].object;

                            // Access the position of the clicked mesh
                            const meshPosition = clickedMesh.position;

                            console.log(meshPosition);

                        }
                    }

                    // // Calculate normalized device coordinates
                    // const canvasBounds = renderer.domElement.getBoundingClientRect();

                    // // Update the picking ray with the camera and mouse coordinates
                    // raycaster.setFromCamera(mouse, camera);

                    // // Find intersections
                    // const intersects = raycaster.intersectObjects(scene.children, true);

                    // if (intersects.length > 0) {
                    //     // The first intersection point corresponds to the clicked mesh
                    //     const clickedMesh = intersects[0].object;

                    //     // Access the position of the clicked mesh
                    //     const meshPosition = clickedMesh.position;

                    //     console.log(clickedMesh);

                    //     console.log('Clicked mesh position:', meshPosition);
                    // }
                }

                // // Disable or hide meshes based on their names
                // function disableMeshesByName(scene, names) {
                //     names.forEach(name => {
                //         const mesh = scene.getObjectByName(name);
                //         if (mesh) {
                //             mesh.visible = false; // Disable or hide the mesh
                //         }
                //     });
                // }

                // // Example usage: Disable or hide meshes with names 'Mesh1' and 'Mesh2'
                // disableMeshesByName(scene, ['Assembly-51']);

                /* Event listener for scroll event */
                window.addEventListener('wheel', (event) => {
                    if (isMouseOverObject) {
                        // Zoom only when the mouse is over the object
                        const delta = event.deltaY;
                        const zoomSpeed = 0.1;

                        // Update camera position based on the scroll direction
                        if (delta < 0) {
                            camera.position.z -= zoomSpeed;
                        } else {
                            camera.position.z += zoomSpeed;
                        }
                    }
                });

                /* Animate the model */
                function animate() {
                    requestAnimationFrame(animate);
                    if (!isInteracting) {
                        mixer.update(0.01); // Adjust animation speed if needed
                    }
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
    </script>


<?php
    return ob_get_clean();
});
