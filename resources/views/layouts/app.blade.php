<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DRR SAKTI GEN V</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js (WAJIB UNTUK MENU JOB) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body
    data-user-role="{{ strtolower((string) (Auth::user()->status_user ?? Auth::user()->role ?? '')) }}"
    data-user-department="{{ strtoupper((string) (Auth::user()->department ?? '')) }}"
    class="bg-slate-50 text-slate-800 antialiased flex h-screen overflow-hidden selection:bg-blue-200 selection:text-blue-900">

    <!-- Latar Belakang Gelap untuk Mobile Sidebar -->
    <div id="sidebar-overlay"
        class="fixed inset-0 bg-slate-900/50 z-20 hidden lg:hidden backdrop-blur-sm transition-opacity"
        onclick="toggleSidebar()"></div>

    <!-- SIDEBAR (Menu Kiri) -->
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-30 w-72 bg-white border-r border-slate-200 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto flex flex-col transition-transform duration-300 ease-in-out shadow-2xl lg:shadow-none">

        <!-- Logo Sidebar -->
        <div class="h-16 flex items-center px-6 border-b border-slate-100 shrink-0">
            <img src="{{ asset('images/icon.png') }}" alt="Logo" class="h-8 w-auto mr-3"
                onerror="this.style.display='none'">
            <span class="text-xl font-bold text-slate-900 tracking-tight">DRR <span
                    class="text-blue-600">SAKTI</span></span>
        </div>

        <!-- Menu Navigasi -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-4">Menu Utama</p>

            <!-- Menu Item (Active State) -->
            <!-- Menu Item (Active State) Dashboard -->
            <a href="{{ route('dashboard') }}"
                class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                    </path>
                </svg>
                Dashboard
            </a>

            <!-- Menu Item (Manajemen Aset) -->
            <a href="{{ route('assets.index') }}"
                class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('assets.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('assets.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                Manajemen Aset
            </a>

            @php
            $sidebarRoleText = strtolower(trim(implode(' ', array_filter([
            (string) (Auth::user()->role ?? ''),
            (string) (Auth::user()->status_user ?? ''),
            ]))));

            $sidebarRoleText = str_replace(['-', '_'], ' ', $sidebarRoleText);

            $canOpenCommandCenter =
            str_contains($sidebarRoleText, 'koordinator') ||
            str_contains($sidebarRoleText, 'coordinator') ||
            str_contains($sidebarRoleText, 'sect head') ||
            str_contains($sidebarRoleText, 'secthead') ||
            str_contains($sidebarRoleText, 'admin') ||
            str_contains($sidebarRoleText, 'super admin') ||
            str_contains($sidebarRoleText, 'superadmin');
            @endphp

            @if($canOpenCommandCenter)
            <a href="{{ route('command-center.index') }}"
                class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-colors mt-1 {{ request()->routeIs('command-center.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('command-center.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 3a1 1 0 012 0v18a1 1 0 11-2 0V3zM4 13a1 1 0 012 0v8a1 1 0 11-2 0v-8zM18 7a1 1 0 012 0v14a1 1 0 11-2 0V7z">
                    </path>
                </svg>
                Command Center
            </a>
            @endif

            <!-- Menu Item (Profil Saya) -->
            <a href="{{ route('profile.index') }}"
                class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-colors mt-1 {{ request()->routeIs('profile.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('profile.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Profil Saya
            </a>

            <!-- Menu Khusus Super Admin -->
            @if(Auth::user()->status_user === 'super_admin')
            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-6">Manajemen Admin</p>
            <a href="{{ route('admin.subscriptions') }}"
                class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors {{ request()->routeIs('admin.subscriptions') ? 'bg-blue-50 text-blue-700' : '' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.subscriptions') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                    </path>
                </svg>
                Verifikasi Pembayaran
            </a>

            <!-- Menu Baru: Manajemen Pengguna -->
            <a href="{{ route('admin.users.index') }}"
                class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors mt-1 {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.users.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                    </path>
                </svg>
                Manajemen Pengguna
            </a>
            @endif
        </nav>

        <!-- Sidebar Footer (User Info Singkat) -->
        <div class="p-4 border-t border-slate-100 shrink-0 mb-24 lg:mb-0">
            <div class="flex items-center">
                <div
                    class="h-9 w-9 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold shrink-0">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="ml-3 overflow-hidden flex-1">
                    <p class="text-sm font-medium text-slate-900 truncate flex items-center">
                        {{ Auth::user()->name }}
                        @if(Auth::user()->is_verified || Auth::user()->status_user === 'super_admin')
                        <!-- Badge Biru Verified -->
                        <svg class="w-4 h-4 text-blue-500 ml-1 shrink-0" viewBox="0 0 24 24" fill="currentColor"
                            title="Akun Terverifikasi">
                            <path fill-rule="evenodd"
                                d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 11.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
                                clip-rule="evenodd" />
                        </svg>
                        @endif
                    </p>
                    <p class="text-xs text-slate-500 truncate">{{ str_replace('_', ' ', Auth::user()->role ??
                        Auth::user()->status_user) }}</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- KONTEN UTAMA (Kanan) -->
    <div class="flex-1 flex flex-col min-w-0 bg-slate-50">

        <!-- TOPBAR (Header Atas) -->
        <header
            class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 shrink-0 z-10 shadow-sm">
            <div class="flex items-center">
                <button onclick="toggleSidebar()" class="lg:hidden mr-4 text-slate-500 hover:text-slate-700 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                        </path>
                    </svg>
                </button>
                <h1 class="text-lg font-bold text-slate-800">DRR SAKTI GEN V</h1>
            </div>

            <div class="flex items-center gap-4">
                <!-- Notifikasi Icon -->
                <button class="text-slate-400 hover:text-slate-600 relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                    <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                </button>

                <!-- User Dropdown Sederhana -->
                <div class="hidden sm:block text-right">
                    <p class="text-sm font-semibold text-slate-700">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-400">{{ str_replace('_', ' ', Auth::user()->status_user) }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="p-2 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                        title="Keluar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- AREA KONTEN YANG BISA DISCROLL -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 pb-24 lg:pb-6">
            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>
</body>

</html>
