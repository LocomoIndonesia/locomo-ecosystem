<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'mandeh.online') }}</title>

    <!-- SEO & Social Media Meta Tags -->
    <meta name="description" content="Digital Platform for Maritime Tourism & Local Business at Mandeh, West Sumatra. Discover Destinations, Services, and Reports in real-time.">
    <meta name="keywords" content="Mandeh, West Sumatra, Tourism, Maritime, Local Business, Sumatra Barat">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="mandeh.online - Maritime Digital Hub">
    <meta property="og:description" content="Discover the beauty of Mandeh Islands. Explore local services, destinations, and real-time maritime reports.">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="mandeh.online - Maritime Digital Hub">
    <meta property="twitter:description" content="Discover the beauty of Mandeh Islands. Explore local services, destinations, and real-time maritime reports.">
    <meta property="twitter:image" content="{{ asset('images/og-image.png') }}">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Work+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md min-h-screen flex flex-col antialiased relative" x-data="{ sidebarOpen: false, showRegisterModal: false }">
    <x-registration-modal />
    
    <!-- TopAppBar -->
    <header class="sticky top-0 z-[100] flex justify-between items-center px-gutter py-4 w-full bg-surface/95 backdrop-blur-md shadow-sm">
        <div class="flex items-center gap-sm flex-shrink-0">
            <!-- Burger Menu Button (Mobile) -->
            <button @click="sidebarOpen = true" class="p-2 hover:bg-surface-variant rounded-full text-on-surface-variant md:hidden">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <a href="/" class="flex items-center gap-xs">
                <span class="material-symbols-outlined text-primary">anchor</span>
                <span class="text-headline-md font-bold text-primary tracking-tight hidden sm:inline">mandeh.online</span>
                <span class="text-headline-sm font-bold text-primary tracking-tight sm:hidden">mandeh</span>
            </a>
        </div>
        <div class="flex items-center gap-2 sm:gap-md">
            @auth
                <div class="flex items-center gap-2.5">
                    {{-- Avatar / Initials + First Name --}}
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                        @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover border-2 border-primary/30">
                        @else
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-on-primary text-label-md font-bold shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <span class="text-label-md font-semibold text-on-surface hidden sm:inline truncate max-w-[120px]">{{ explode(' ', Auth::user()->name)[0] }}</span>
                    </a>

                    {{-- Logout Icon-Only --}}
                    <form method="POST" action="{{ route('logout') }}" class="flex items-center">
                        @csrf
                        <button type="submit" class="p-2 rounded-full text-on-surface-variant hover:bg-error/10 hover:text-error transition-colors" title="Keluar">
                            <span class="material-symbols-outlined text-[22px]">logout</span>
                        </button>
                    </form>
                </div>
            @else
                <nav class="flex items-center gap-3 sm:gap-4">
                    <a href="{{ route('login') }}" class="flex items-center gap-1.5 text-label-md font-semibold text-primary hover:text-secondary whitespace-nowrap">
                        <span class="material-symbols-outlined text-[20px]">login</span>
                        <span>Masuk</span>
                    </a>
                </nav>
            @endauth
        </div>
    </header>

    <div class="flex flex-1 relative z-10 overflow-hidden">
        <!-- Sidebar Navigation (Drawer) -->
        <aside class="fixed inset-y-0 left-0 z-[110] w-[280px] bg-surface-container-lowest/90 backdrop-blur-xl border-r border-outline-variant/10 shadow-modal transform transition-transform duration-300 md:static md:translate-x-0 md:shadow-none md:z-0 md:h-auto"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
               x-cloak>

            <div class="h-full flex flex-col p-md">
                <div class="flex items-center justify-between mb-lg lg:hidden">
                    <span class="font-bold text-primary">Menu</span>
                    <button @click="sidebarOpen = false" class="text-on-surface-variant">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <nav class="flex flex-col gap-1">
                    <a href="/" class="flex items-center gap-md p-md rounded-2xl transition-all {{ request()->is('/') ? 'bg-primary/5 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-variant' }}">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->is('/') ? 1 : 0 }};">explore</span>
                        <span class="text-label-lg">Explorer</span>
                    </a>
                    <a href="{{ route('business.index') }}" class="flex items-center gap-md p-md rounded-2xl transition-all {{ request()->is('services*') ? 'bg-primary/5 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-variant' }}">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->is('services*') ? 1 : 0 }};">grid_view</span>
                        <span class="text-label-lg">Services</span>
                    </a>
                    <a href="{{ route('report.create') }}" class="flex items-center gap-md p-md rounded-2xl transition-all {{ request()->is('report*') ? 'bg-primary/5 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-variant' }}">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->is('report*') ? 1 : 0 }};">campaign</span>
                        <span class="text-label-lg">Alerts & Reports</span>
                    </a>
                    <a href="#" class="flex items-center gap-md p-md rounded-2xl transition-all text-on-surface-variant hover:bg-surface-variant">
                        <span class="material-symbols-outlined">security</span>
                        <span class="text-label-lg">Safety Guide</span>
                    </a>
                    @auth
                        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'admin_manager')
                            <a href="{{ route('vanguard.admin.surveys') }}" class="flex items-center gap-md p-md rounded-2xl text-emerald-400 hover:bg-emerald-500/5 transition-all border border-emerald-500/10 mt-2">
                                <span class="material-symbols-outlined">admin_panel_settings</span>
                                <span class="text-label-lg font-bold">Admin Panel</span>
                            </a>
                        @endif
                    @endauth
                </nav>

                <div class="mt-auto pt-md border-t border-outline-variant/10">
                    @auth
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-md p-md rounded-2xl text-on-surface-variant hover:bg-surface-variant transition-all">
                            <span class="material-symbols-outlined">account_circle</span>
                            <span class="text-label-lg">My Dashboard</span>
                        </a>
                    @endauth
                </div>
            </div>
        </aside>

        <!-- Overlay for Mobile Sidebar -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-transition:enter="transition opacity-0 duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-primary/20 backdrop-blur-sm z-[105] md:hidden"></div>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 relative">
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>
</html>
