<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mindoro Golden Eagle Distribution Corporation</title>
    <!-- Favicon -->
    @if($generalSetting && $generalSetting->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset($generalSetting->favicon) }}">
    @endif
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Three.js for 3D animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .floating-text {
            animation: float 6s ease-in-out infinite;
        }
        .glow-effect {
            box-shadow: 0 0 15px #4634ff, 0 0 25px #4634ff, 0 0 35px #4634ff;
        }
    </style>
</head>
<body class="antialiased bg-[#1a2035] text-white">
    <div id="canvas-container" class="fixed top-0 left-0 w-full h-full z-0"></div>
    <div class="relative z-10 flex flex-col h-screen">
        <header class="w-full p-6 bg-black bg-opacity-20 backdrop-blur-md border-b border-gray-700/50">
            <div class="container mx-auto flex justify-end items-center">
                <div class="flex items-center space-x-6">
                    <a href="https://www.facebook.com/mgedcorp" target="_blank" class="text-gray-400 hover:text-white transition-transform transform hover:scale-110">
                        <i data-lucide="facebook" class="w-6 h-6"></i>
                    </a>
                    <a href="https://mail.google.com/mail/u/0/#inbox?compose=DmwnWsCPcSJpNkzSDxTSlKxMJqhdvLHgRxcXwVJxcDQKBJKPGNLFRTkVbZhwZpLCwpxgQNqGVwNV" target="_blank" class="text-gray-400 hover:text-white transition-transform transform hover:scale-110">
                        <i data-lucide="mail" class="w-6 h-6"></i>
                    </a>
                    <a href="{{ route('login') }}" class="bg-[#4634ff] text-white font-semibold py-2 px-6 rounded-2xl shadow-lg hover:shadow-2xl hover:bg-opacity-90 transition-all transform hover:-translate-y-0.5 glow-effect">
                        Login
                    </a>
                </div>
            </div>
        </header>
        <main class="flex-grow flex items-center justify-center text-center">
            <div class="floating-text">
                <h1 class="text-5xl md:text-7xl font-bold tracking-tight mb-4" style="text-shadow: 0 0 15px rgba(255,255,255,0.3);">
                    Mindoro Golden Eagle Distribution Corporation
                </h1>
                <p class="text-xl md:text-2xl text-gray-300">
                    We are the exclusive of some products here in Mindoro.
                </p>
            </div>
        </main>
    </div>
    <script>
        lucide.createIcons();
        // Basic Three.js scene for background
        let scene, camera, renderer;
        function init() {
            scene = new THREE.Scene();
            camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            renderer = new THREE.WebGLRenderer({ alpha: true });
            renderer.setSize(window.innerWidth, window.innerHeight);
            document.getElementById('canvas-container').appendChild(renderer.domElement);
            // Add some particles
            const particles = new THREE.BufferGeometry();
            const particleCount = 5000;
            const posArray = new Float32Array(particleCount * 3);
            for (let i = 0; i < particleCount * 3; i++) {
                posArray[i] = (Math.random() - 0.5) * 10;
            }
            particles.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
            const material = new THREE.PointsMaterial({
                size: 0.005,
                color: '#4634ff'
            });
            const particleMesh = new THREE.Points(particles, material);
            scene.add(particleMesh);
            camera.position.z = 5;
            animate();
        }
        function animate() {
            requestAnimationFrame(animate);
            renderer.render(scene, camera);
        }
        init();
    </script>
</body>
</html>