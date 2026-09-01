<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'mandeh.online') }}</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Work+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md h-full antialiased overflow-hidden" x-data="{ sidebarOpen: false, showRegisterModal: false }">
    <x-registration-modal />
    <div class="flex h-full w-full relative overflow-hidden">
        
        <!-- Sidebar Navigation (Drawer) -->
        <aside class="fixed inset-y-0 left-0 z-[60] w-[280px] bg-surface-container-lowest border-r border-outline-variant/10 shadow-modal transform transition-transform duration-300 lg:static lg:translate-x-0 lg:shadow-none"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               x-cloak>
            <div class="h-full flex flex-col p-md bg-surface-container-lowest">
                <!-- Sidebar Header -->
                <div class="flex items-center justify-between mb-lg">
                    <a href="/" class="flex items-center gap-xs">
                        <span class="material-symbols-outlined text-primary text-3xl">anchor</span>
                        <span class="text-headline-sm font-bold text-primary tracking-tight">mandeh.online</span>
                    </a>
                    <button @click="sidebarOpen = false" class="text-on-surface-variant lg:hidden p-2 hover:bg-surface-variant rounded-full">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="flex flex-col gap-1 overflow-y-auto flex-1 scrollbar-hide">
                    <a href="/" class="flex items-center gap-md p-md rounded-2xl transition-all {{ request()->is('/') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-variant' }}">
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
                    <div class="my-md border-t border-outline-variant/10"></div>
                    <a href="#" class="flex items-center gap-md p-md rounded-2xl transition-all text-on-surface-variant hover:bg-surface-variant">
                        <span class="material-symbols-outlined">security</span>
                        <span class="text-label-lg">Safety Guide</span>
                    </a>
                    <a href="#" class="flex items-center gap-md p-md rounded-2xl transition-all text-on-surface-variant hover:bg-surface-variant">
                        <span class="material-symbols-outlined">info</span>
                        <span class="text-label-lg">About Mandeh</span>
                    </a>
                </nav>

                <!-- Sidebar Footer (Auth) -->
                <div class="mt-auto pt-md border-t border-outline-variant/10">
                    @auth
                        <div class="flex flex-col gap-1">
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-md p-md rounded-2xl text-on-surface-variant hover:bg-surface-variant transition-all">
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover border-2 border-primary/20">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-on-primary text-label-md font-bold shrink-0">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="flex flex-col">
                                    <span class="text-label-lg font-bold truncate max-w-[150px]">{{ explode(' ', Auth::user()->name)[0] }}</span>
                                    <span class="text-[10px] opacity-70">Lihat Profil</span>
                                </div>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-md p-md rounded-2xl text-error hover:bg-error/10 transition-all" title="Keluar">
                                    <span class="material-symbols-outlined">logout</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex flex-col gap-2 p-sm">
                            <a href="{{ route('login') }}" class="w-full py-3 bg-primary text-on-primary rounded-xl font-bold text-center hover:opacity-90 transition-opacity">Masuk</a>
                            <button type="button" @click="showRegisterModal = true" class="w-full py-3 border border-outline text-primary rounded-xl font-bold text-center hover:bg-primary/5 transition-colors">Daftar</button>
                        </div>
                    @endauth
                </div>
            </div>
        </aside>

        <!-- Overlay for Mobile -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-transition:enter="transition opacity-0 duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-primary/20 backdrop-blur-sm z-[55] lg:hidden"
             x-cloak></div>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 h-full relative">
            <!-- Top Header (Content Specific) -->
            <header class="h-[64px] flex items-center justify-between px-gutter border-b border-outline-variant/10 bg-surface/95 backdrop-blur-md z-40">
                <div class="flex items-center gap-sm">
                    <button @click="sidebarOpen = true" class="p-2 hover:bg-surface-variant rounded-full text-on-surface-variant lg:hidden">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <h1 class="text-title-medium font-bold text-on-surface lg:hidden">mandeh.online</h1>
                </div>
                <div class="flex items-center gap-md">
                    <!-- Right side header actions if any -->
                    <button class="p-2 hover:bg-surface-variant rounded-full text-on-surface-variant">
                        <span class="material-symbols-outlined">notifications</span>
                    </button>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto relative bg-background">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
