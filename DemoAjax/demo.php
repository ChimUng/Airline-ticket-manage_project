<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Máy Bay 3D</title>
  <style>
    body { margin: 0; overflow: hidden; }
    canvas { display: block; }
  </style>
</head>
<body>
  <!-- Import maps polyfill -->
  <script async src="https://unpkg.com/es-module-shims@1.6.3/dist/es-module-shims.js"></script>

  <script type="importmap">
    {
      "imports": {
        "three": "https://unpkg.com/three@0.158.0/build/three.module.js",
        "three/addons/": "https://unpkg.com/three@0.158.0/examples/jsm/"
      }
    }
  </script>

  <script type="module">
    import * as THREE from 'three';
    import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
    import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

    // Initialize the scene
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0xaee0ff);

    // Set up camera
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth/window.innerHeight, 0.1, 1000);
    camera.position.set(0, 2, 5);

    // Set up renderer
    const renderer = new THREE.WebGLRenderer({antialias: true});
    renderer.setSize(window.innerWidth, window.innerHeight);
    document.body.appendChild(renderer.domElement);

    // Set up controls
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;

    // Add lights
    const light = new THREE.DirectionalLight(0xffffff, 1);
    light.position.set(5, 10, 7.5);
    scene.add(light);
    scene.add(new THREE.AmbientLight(0xffffff, 0.5));

    // Create a fallback object if the model fails to load
    function createFallbackAirplane() {
      // Create a simple airplane shape
      const group = new THREE.Group();
      
      // Body
      const bodyGeometry = new THREE.CylinderGeometry(0.2, 0.2, 2, 8);
      bodyGeometry.rotateZ(Math.PI / 2);
      const bodyMaterial = new THREE.MeshPhongMaterial({ color: 0x4287f5 });
      const body = new THREE.Mesh(bodyGeometry, bodyMaterial);
      group.add(body);
      
      // Wings
      const wingGeometry = new THREE.BoxGeometry(0.05, 1.5, 0.5);
      const wingMaterial = new THREE.MeshPhongMaterial({ color: 0x71a5f9 });
      const wings = new THREE.Mesh(wingGeometry, wingMaterial);
      wings.position.set(0, 0, 0);
      group.add(wings);
      
      // Tail
      const tailGeometry = new THREE.BoxGeometry(0.02, 0.5, 0.5);
      const tailMaterial = new THREE.MeshPhongMaterial({ color: 0x71a5f9 });
      const tail = new THREE.Mesh(tailGeometry, tailMaterial);
      tail.position.set(-0.9, 0, 0);
      group.add(tail);
      
      return group;
    }

    // Try to load a working 3D model URL
    const loader = new GLTFLoader();
    loader.load(
      'https://threejs.org/examples/models/gltf/Parrot.glb', // Use a known working model from Three.js examples
      (gltf) => {
        const model = gltf.scene;
        model.scale.set(0.05, 0.05, 0.05); // Adjust scale as needed
        scene.add(model);
        
        // Animation loop
        function animate() {
          requestAnimationFrame(animate);
          model.rotation.y += 0.005;
          controls.update();
          renderer.render(scene, camera);
        }
        
        animate();
      },
      undefined,
      (error) => {
        console.error('Failed to load model, using fallback:', error);
        // If loading fails, use our fallback airplane
        const fallbackModel = createFallbackAirplane();
        scene.add(fallbackModel);
        
        // Animation loop with fallback
        function animate() {
          requestAnimationFrame(animate);
          fallbackModel.rotation.y += 0.005;
          controls.update();
          renderer.render(scene, camera);
        }
        
        animate();
      }
    );

    // Handle window resize
    window.addEventListener('resize', () => {
      camera.aspect = window.innerWidth / window.innerHeight;
      camera.updateProjectionMatrix();
      renderer.setSize(window.innerWidth, window.innerHeight);
    });
  </script>
</body>
</html>