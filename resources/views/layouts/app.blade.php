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
            <!-- Hamburger Button untuk Mobile -->
            <button onclick="toggleSidebar()"
                class="lg:hidden p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Spacer untuk mendorong menu logout ke kanan -->
            <div class="flex-1"></div>

            <!-- Menu Kanan (Logout) -->
            <div class="flex items-center space-x-4">
                <!-- Tombol Notifikasi (Dummy) -->
                <button class="p-2 text-slate-400 hover:text-slate-500 relative">
                    <span
                        class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                </button>

                <!-- Pemisah -->
                <div class="hidden sm:block h-6 w-px bg-slate-200"></div>

                <!-- Form Logout -->
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit"
                        class="flex items-center text-sm font-medium text-slate-600 hover:text-red-600 transition-colors px-2 py-1 rounded-md hover:bg-red-50">
                        <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- AREA KONTEN DINAMIS -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 pb-28 sm:p-6 sm:pb-28 lg:p-8 lg:pb-28">
            <!-- Di sinilah isi halaman (seperti dashboard.blade.php) akan disuntikkan -->
            @yield('content')
        </main>

    </div>


    <!-- FLOATING BOTTOM NAVIGATION -->
    @if(Auth::check())
    <nav
        class="fixed bottom-0 left-0 right-0 z-50 border-t border-slate-200 bg-white/95 px-2 pb-2 pt-2 shadow-[0_-10px_30px_rgba(15,23,42,0.12)] backdrop-blur lg:bottom-6 lg:left-1/2 lg:right-auto lg:w-auto lg:-translate-x-1/2 lg:rounded-3xl lg:border lg:px-3 lg:shadow-2xl">
        <div class="grid grid-cols-5 items-end gap-1 lg:flex lg:items-center lg:gap-1">
            <a href="{{ route('dashboard') }}"
                class="flex flex-col items-center justify-center gap-1 rounded-2xl px-2 py-2 text-[11px] font-semibold transition lg:flex-row lg:px-4 lg:text-xs
                {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l9-9 9 9M5 10v10h14V10" />
                </svg>
                <span>Home</span>
            </a>

            <a href="{{ route('calendar.index') }}"
                class="flex flex-col items-center justify-center gap-1 rounded-2xl px-2 py-2 text-[11px] font-semibold transition lg:flex-row lg:px-4 lg:text-xs
                {{ request()->routeIs('calendar.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                </svg>
                <span>Kalender</span>
            </a>

            <!-- ========================================== -->
            <!-- MENU JOB DINAMIS (FLOATING POP-UP) -->
            <!-- ========================================== -->
            <div x-data="{ openJobMenu: false }" class="relative flex flex-col items-center justify-center">

                <!-- Backdrop/Overlay Transparan (Klik di luar untuk menutup) -->
                <div x-show="openJobMenu" @click="openJobMenu = false" x-transition.opacity.duration.300ms
                    class="fixed inset-0 z-40 bg-slate-900/10 backdrop-blur-[2px] lg:bg-transparent lg:backdrop-blur-none"
                    style="display: none;"></div>

                <!-- Pop-up Menu Box -->
                <div x-show="openJobMenu" @click.away="openJobMenu = false" style="display: none;"
                    x-transition:enter="transition-all ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-y-16 scale-50 rotate-[-10deg] blur-sm"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100 rotate-0 blur-0"
                    x-transition:leave="transition-all ease-in duration-300"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100 blur-0"
                    x-transition:leave-end="opacity-0 translate-y-12 scale-75 blur-sm"
                    class="absolute bottom-[calc(100%+1rem)] left-1/2 -translate-x-1/2 w-70 p-2.5 bg-white/95 backdrop-blur-xl border border-slate-200/80 shadow-2xl rounded-4xl z-50 flex flex-col gap-2 origin-bottom">

                    <!-- 1. Update Job (Blue) -->
                    <a href="{{ route('update-jobs.index') }}"
                        class="group relative flex items-center gap-4 px-4 py-3 rounded-2xl bg-white hover:bg-blue-50 border border-transparent hover:border-blue-100 transition-all duration-300 active:scale-95 shadow-sm hover:shadow-md">
                        <div
                            class="bg-blue-100 text-blue-600 p-2.5 rounded-xl group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-blue-700">Update Job</span>
                    </a>

                    <!-- 2. Battery (Emerald) -->
                    <a href="{{ route('batteries.index') }}"
                        class="group relative flex items-center gap-4 px-4 py-3 rounded-2xl bg-white hover:bg-emerald-50 border border-transparent hover:border-emerald-100 transition-all duration-300 active:scale-95 shadow-sm hover:shadow-md">
                        <div
                            class="bg-emerald-100 text-emerald-600 p-2.5 rounded-xl group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M4 7v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M11 11h2v2h-2zm-4 0h2v2H7zm8 0h2v2h-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M22 10v4">
                                </path>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-emerald-700">Manajemen
                            Battery</span>
                    </a>

                    <!-- 3. Charger (Amber) -->
                    <a href="{{ route('chargers.index') }}"
                        class="group relative flex items-center gap-4 px-4 py-3 rounded-2xl bg-white hover:bg-amber-50 border border-transparent hover:border-amber-100 transition-all duration-300 active:scale-95 shadow-sm hover:shadow-md">
                        <div
                            class="bg-amber-100 text-amber-600 p-2.5 rounded-xl group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M13 10V3L4 14h7v7l9-11h-7z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-amber-700">Management
                            Charger</span>
                    </a>

                    <!-- 4. Delivery (Purple) -->
                    <a href="{{ route('deliveries.index') }}"
                        class="group relative flex items-center gap-4 px-4 py-3 rounded-2xl bg-white hover:bg-purple-50 border border-transparent hover:border-purple-100 transition-all duration-300 active:scale-95 shadow-sm hover:shadow-md">
                        <div
                            class="bg-purple-100 text-purple-600 p-2.5 rounded-xl group-hover:scale-110 group-hover:-translate-x-1 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M8 14h8m-8 4h8M5 8h14M3 8l1.5-4h15L21 8M3 8v10a2 2 0 002 2h14a2 2 0 002-2V8">
                                </path>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-purple-700">Delivery Unit</span>
                    </a>

                    <!-- 5. Penarikan (Rose) -->
                    <a href="{{ route('penarikans.index') }}"
                        class="group relative flex items-center gap-4 px-4 py-3 rounded-2xl bg-white hover:bg-rose-50 border border-transparent hover:border-rose-100 transition-all duration-300 active:scale-95 shadow-sm hover:shadow-md">
                        <div
                            class="bg-rose-100 text-rose-600 p-2.5 rounded-xl group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M3 3h18v18H3zM8 12h8m-8-4h8m-8 8h4"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-rose-700">Penarikan Unit</span>
                    </a>

                    <!-- Triangle Pointer (Panah ke bawah) -->
                    <div
                        class="absolute -bottom-2.5 left-1/2 -translate-x-1/2 w-5 h-5 bg-white border-b border-r border-slate-200/80 rotate-45 rounded-sm z-[-1]">
                    </div>
                </div>

                <!-- Tombol Trigger Utama (Job) -->
                <button type="button" @click="openJobMenu = !openJobMenu"
                    class="relative -mt-6 flex flex-col items-center justify-center gap-1 rounded-full bg-blue-600 px-4 py-4 text-[11px] font-bold text-white shadow-xl ring-4 ring-blue-100 transition hover:bg-blue-700 lg:mt-0 lg:flex-row lg:rounded-2xl lg:px-5 lg:py-3 lg:text-xs">

                    <!-- Efek rotasi saat menu terbuka -->
                    <svg class="h-6 w-6 transition-transform duration-500 ease-out lg:h-5 lg:w-5"
                        :class="openJobMenu ? 'rotate-360 scale-110 text-white' : ''" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span class="transition-colors duration-300">Job</span>
                </button>
            </div>
            <!-- ========================================== -->

            <a href="{{ route('reminders.index') }}"
                class="flex flex-col items-center justify-center gap-1 rounded-2xl px-2 py-2 text-[11px] font-semibold transition lg:flex-row lg:px-4 lg:text-xs
                {{ request()->routeIs('reminders.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0" />
                </svg>
                <span>Ingat</span>
            </a>

            <a href="{{ route('profile.index') }}"
                class="flex flex-col items-center justify-center gap-1 rounded-2xl px-2 py-2 text-[11px] font-semibold transition lg:flex-row lg:px-4 lg:text-xs
                {{ request()->routeIs('profile.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Profile</span>
            </a>
        </div>
    </nav>
    @endif

    <!-- Script Vanilla JS untuk Toggle Sidebar Mobile -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            // Toggle class transform untuk memunculkan/menyembunyikan sidebar
            sidebar.classList.toggle('-translate-x-full');

            // Toggle class hidden pada background hitam transparan
            overlay.classList.toggle('hidden');
        }
    </script>
</body>

</html>