<!-- resources/views/auth/login.blade.php -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DRR SAKTI GEN V</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Menggunakan font Inter untuk kesan korporat yang sangat bersih -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-100 text-slate-800 antialiased selection:bg-blue-200 selection:text-blue-900">

    <!-- Wrapper Utama dengan background global -->
    <div class="min-h-screen flex relative overflow-hidden bg-linear-to-br from-slate-50 via-slate-100 to-slate-200">

        <!-- Canvas untuk Efek Partikel Global (Sekarang di luar, menutupi seluruh layar) -->
        <canvas id="particle-canvas" class="absolute inset-0 z-0 opacity-50"></canvas>

        <!-- BAGIAN KIRI: Branding (Disembunyikan di layar HP/Tablet kecil, muncul di Desktop) -->
        <!-- Ditambahkan z-10 agar berada di atas partikel -->
        <div
            class="hidden lg:flex lg:w-1/2 flex-col justify-between p-12 relative z-10 border-r border-slate-200/50 bg-white/40 backdrop-blur-sm">
            <div class="relative z-10 mt-10">
                <img src="{{ asset('images/icon.png') }}" alt="Logo" class="h-14 w-auto mb-10 drop-shadow-md"
                    onerror="this.outerHTML='<div class=\'h-14 w-14 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-xl mb-10 shadow-lg\'>DRR</div>'">
                <h1 class="text-4xl font-bold tracking-tight text-slate-900 mb-4 drop-shadow-sm">DRR SAKTI <br><span
                        class="text-blue-600">GEN V</span></h1>
                <p class="text-lg text-slate-600 max-w-md leading-relaxed font-medium">Sistem manajemen aset dan
                    operasional enterprise yang terintegrasi penuh. Terukur, aman, dan dapat diandalkan.</p>
            </div>

            <div class="relative z-10">
                <p class="text-sm font-semibold text-slate-500">&copy; {{ date('Y') }} DRR SAKTI. Hak Cipta Dilindungi.
                </p>
            </div>
        </div>

        <!-- BAGIAN KANAN: Form Login (Penuh di Mobile, Setengah di Desktop) -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 relative z-10">
            <!-- Box Form dengan Shadow Maksimal untuk Kontras -->
            <div
                class="w-full max-w-md bg-white p-8 sm:p-10 rounded-3xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.15)] border border-slate-100 ring-1 ring-slate-900/5">

                <!-- Logo untuk versi Mobile -->
                <div class="lg:hidden flex justify-center mb-8">
                    <img src="{{ asset('images/icon.png') }}" alt="Logo" class="h-16 w-auto drop-shadow-md"
                        onerror="this.outerHTML='<div class=\'h-16 w-16 bg-blue-600 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg\'>DRR</div>'">
                </div>

                <div class="mb-8 text-center lg:text-left">
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">Masuk ke Akun</h2>
                    <p class="text-slate-500 text-sm font-medium">Gunakan NRPP dan kata sandi Anda untuk mengakses
                        sistem.</p>
                </div>

                <!-- Alert Error -->
                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-md shadow-sm">
                    <div class="flex">
                        <div class="shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-bold">NRPP atau Kata Sandi salah.</p>
                        </div>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="nrpp" class="block text-sm font-semibold text-slate-700 mb-1.5">ID Karyawan /
                            NRPP</label>
                        <input type="text" id="nrpp" name="nrpp" required autofocus
                            class="block w-full px-4 py-3 rounded-xl border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all sm:text-sm bg-slate-50 focus:bg-white shadow-inner"
                            placeholder="Masukkan NRPP Anda">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Kata
                            Sandi</label>
                        <input type="password" id="password" name="password" required
                            class="block w-full px-4 py-3 rounded-xl border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all sm:text-sm bg-slate-50 focus:bg-white shadow-inner"
                            placeholder="••••••••">
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer shadow-sm">
                            <label for="remember" class="ml-2 block text-sm font-medium text-slate-600 cursor-pointer">
                                Ingat saya
                            </label>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit"
                            class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-blue-600/30 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                            Otentikasi Sistem
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script Efek Partikel Light Theme (Global) -->
    <script>
        const canvas = document.getElementById('particle-canvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            let width, height;
            let particles = [];

            function resize() {
                if (canvas.parentElement.clientWidth > 0) {
                    width = canvas.width = canvas.parentElement.clientWidth;
                    height = canvas.height = canvas.parentElement.clientHeight;
                }
            }

            class Particle {
                constructor() {
                    this.x = Math.random() * width;
                    this.y = Math.random() * height;
                    this.vx = (Math.random() - 0.5) * 0.4;
                    this.vy = (Math.random() - 0.5) * 0.4;
                    this.radius = Math.random() * 2 + 0.5;
                }
                update() {
                    this.x += this.vx;
                    this.y += this.vy;
                    if (this.x < 0 || this.x > width) this.vx *= -1;
                    if (this.y < 0 || this.y > height) this.vy *= -1;
                }
                draw() {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                    ctx.fillStyle = 'rgba(59, 130, 246, 0.4)'; // Biru DRR transparan
                    ctx.fill();
                }
            }

            function init() {
                resize();
                window.addEventListener('resize', resize);
                particles = [];

                // Deteksi layar: Jika HP partikel lebih sedikit agar tidak lag
                const isMobile = window.innerWidth < 768;
                const particleCount = isMobile ? 30 : 70;

                for (let i = 0; i < particleCount; i++) {
                    particles.push(new Particle());
                }
                animate();
            }

            function animate() {
                requestAnimationFrame(animate);
                if (width === 0 || height === 0) return;

                ctx.clearRect(0, 0, width, height);

                particles.forEach(p => {
                    p.update();
                    p.draw();
                });

                for (let i = 0; i < particles.length; i++) {
                    for (let j = i + 1; j < particles.length; j++) {
                        const dx = particles[i].x - particles[j].x;
                        const dy = particles[i].y - particles[j].y;
                        const distance = Math.sqrt(dx * dx + dy * dy);

                        // Jarak koneksi garis
                        const maxDistance = window.innerWidth < 768 ? 100 : 130;

                        if (distance < maxDistance) {
                            ctx.beginPath();
                            ctx.moveTo(particles[i].x, particles[i].y);
                            ctx.lineTo(particles[j].x, particles[j].y);
                            // Transparansi garis berdasarkan jarak
                            const opacity = (1 - (distance / maxDistance)) * 0.15;
                            ctx.strokeStyle = `rgba(59, 130, 246, ${opacity})`;
                            ctx.lineWidth = 1;
                            ctx.stroke();
                        }
                    }
                }
            }

            // Langsung panggil init() agar partikel jalan di HP, Tablet, maupun Desktop
            init();
        }
    </script>
</body>

</html>
