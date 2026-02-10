<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ 
    sidebarOpen: true, 
    profileOpen: false,
    isDesktop: window.innerWidth >= 768,
    init() {
        // Initialiser isDesktop au chargement
        this.isDesktop = window.innerWidth >= 768;
        if (this.isDesktop) {
            this.sidebarOpen = true;
        }
        // Écouter les changements de taille d'écran
        window.addEventListener('resize', () => {
            this.isDesktop = window.innerWidth >= 768;
            if (this.isDesktop) {
                this.sidebarOpen = true;
            }
        });
    }
}" :class="{ 'overflow-hidden': sidebarOpen && !isDesktop }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Application de gestion d'inventaire professionnelle">

    <title>{{ config('app.name', 'Inventaire Pro') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    {{-- Alpine.js est déjà inclus dans Livewire 3, ne pas le charger séparément --}}

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside 
            x-show="sidebarOpen || isDesktop"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-gray-800 text-white flex flex-col"
            :class="{ 'translate-x-0': isDesktop || sidebarOpen }"
        >
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-6 bg-gray-900 border-b border-gray-700">
                <div class="flex items-center space-x-2">
                    <svg class="w-8 h-8 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span class="font-bold text-lg">GIMTEL</span>
                </div>
                <button @click="sidebarOpen = false" class="md:hidden text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3"
                 x-data="{
                    openMenu: '',
                    init() {
                        {{-- Auto-ouvrir le sous-menu pertinent --}}
                        @hasanyrole('admin|admin_stock')
                            @if(request()->routeIs('stock.produits.*') || request()->routeIs('stock.sorties.*') || request()->routeIs('stock.magasins.*') || request()->routeIs('stock.categories.*') || request()->routeIs('stock.fournisseurs.*') || request()->routeIs('stock.clients.*') || (request()->routeIs('stock.entrees.*') && request('usage') === 'commande_carte'))
                                this.openMenu = 'stock';
                            @elseif(request()->routeIs('stock.produits-appro.*') || request()->routeIs('stock.magasins-appro.*') || request()->routeIs('stock.categories-appro.*') || request()->routeIs('stock.fournisseurs-appro.*') || request()->routeIs('stock.demandeurs.*') || request()->routeIs('stock.dashboard-appro') || (request()->routeIs('stock.entrees.*') && request('usage') === 'appro'))
                                this.openMenu = 'appro';
                            @endif
                        @endhasanyrole
                    }
                 }">
                <ul class="space-y-1">

                    {{-- ============================================================
                         DASHBOARD
                    ============================================================ --}}
                    <li>
                        <a href="{{ route('dashboard') }}"
                           class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                  {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    @auth
                    {{-- ============================================================
                         CLIENT
                    ============================================================ --}}
                    @if(auth()->user()->isClient())
                        <li class="pt-4 mt-3">
                            <p class="px-3 mb-2 text-[11px] font-semibold text-gray-500 uppercase tracking-widest">Espace client</p>
                        </li>
                        <li>
                            <a href="{{ route('client.dashboard') }}"
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                      {{ request()->routeIs('client.dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Passer une commande
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('client.commande') }}"
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                      {{ request()->routeIs('client.commande*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                                Mes commandes
                            </a>
                        </li>
                    @endif

                    {{-- ============================================================
                         DEMANDEUR INTERNE
                    ============================================================ --}}
                    @if(auth()->user()->isDemandeurInterne())
                        <li class="pt-4 mt-3">
                            <p class="px-3 mb-2 text-[11px] font-semibold text-gray-500 uppercase tracking-widest">Approvisionnement</p>
                        </li>
                        <li>
                            <a href="{{ route('demandes-appro.create') }}"
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                      {{ request()->routeIs('demandes-appro.create') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Nouvelle demande
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('demandes-appro.index') }}"
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                      {{ request()->routeIs('demandes-appro.index') || request()->routeIs('demandes-appro.show') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                Mes demandes
                            </a>
                        </li>
                    @endif

                    {{-- ============================================================
                         DIRECTION MOYENS GENERAUX
                    ============================================================ --}}
                    @if(auth()->user()->isDirectionMoyensGeneraux())
                        <li class="pt-4 mt-3">
                            <p class="px-3 mb-2 text-[11px] font-semibold text-gray-500 uppercase tracking-widest">Moyens Generaux</p>
                        </li>
                        <li>
                            <a href="{{ route('dmg.demandes.index') }}"
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                      {{ request()->routeIs('dmg.demandes.index') || request()->routeIs('dmg.demandes.show') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                Demandes d'appro
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dmg.demandes.create') }}"
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                      {{ request()->routeIs('dmg.demandes.create') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Nouvelle demande
                            </a>
                        </li>
                    @endif

                    {{-- ============================================================
                         DIRECTION PRODUCTION
                    ============================================================ --}}
                    @if(auth()->user()->isDirectionProduction())
                        <li class="pt-4 mt-3">
                            <p class="px-3 mb-2 text-[11px] font-semibold text-gray-500 uppercase tracking-widest">Production</p>
                        </li>
                        <li>
                            <a href="{{ route('production.orders') }}"
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                      {{ request()->routeIs('production.orders') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Gestion commandes
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('production.commander-pour-client') }}"
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                      {{ request()->routeIs('production.commander-pour-client') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                Commander pour client
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('production.reservations') }}"
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                      {{ request()->routeIs('production.reservations') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Reservations clients
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('stock.produits.index') }}"
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                      {{ request()->routeIs('stock.produits.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                Produits
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('stock.entrees.index', ['usage' => 'commande_carte']) }}"
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                      {{ request()->routeIs('stock.entrees.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Entrees stock
                            </a>
                        </li>
                    @endif

                    {{-- ============================================================
                         STOCK - COMMANDES / CARTES  (Admin & Admin Stock)
                    ============================================================ --}}
                    @hasanyrole('admin|admin_stock')
                        <li class="pt-4 mt-3">
                            <p class="px-3 mb-2 text-[11px] font-semibold text-gray-500 uppercase tracking-widest">Stock</p>
                        </li>

                        {{-- -- Commandes / Cartes (accordeon) -- --}}
                        <li>
                            <button @click="openMenu = (openMenu === 'stock') ? '' : 'stock'"
                                    class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150"
                                    :class="openMenu === 'stock' ? 'bg-gray-700/80 text-white' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white'">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    Commandes / Cartes
                                </span>
                                <svg class="w-4 h-4 transition-transform duration-200 flex-shrink-0" :class="openMenu === 'stock' && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <ul x-show="openMenu === 'stock'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="mt-1 ml-3 space-y-0.5 border-l-2 border-gray-700 pl-3">
                                <li>
                                    <a href="{{ route('stock.produits.index') }}"
                                       class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.produits.*') ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                        Produits
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock.entrees.index', ['usage' => 'commande_carte']) }}"
                                       class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.entrees.*') && request('usage') === 'commande_carte' ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Entrees
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock.sorties.index') }}"
                                       class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.sorties.*') ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m4-8l-4-4m0 0L16 8m4-4v12"></path>
                                        </svg>
                                        Sorties
                                    </a>
                                </li>
                                @role('admin')
                                <li class="pt-1.5 mt-1.5 border-t border-gray-700/50">
                                    <span class="flex items-center px-3 py-1.5 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Parametres</span>
                                </li>
                                <li>
                                    <a href="{{ route('stock.magasins.index') }}"
                                       class="flex items-center px-3 py-1.5 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.magasins.*') ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        Magasins
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock.categories.index') }}"
                                       class="flex items-center px-3 py-1.5 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.categories.*') ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        Categories
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock.fournisseurs.index') }}"
                                       class="flex items-center px-3 py-1.5 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.fournisseurs.*') ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        Fournisseurs
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock.clients.index') }}"
                                       class="flex items-center px-3 py-1.5 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.clients.*') ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        Clients
                                    </a>
                                </li>
                                @endrole
                            </ul>
                        </li>

                        {{-- -- Approvisionnement (accordeon) -- --}}
                        <li>
                            <button @click="openMenu = (openMenu === 'appro') ? '' : 'appro'"
                                    class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150"
                                    :class="openMenu === 'appro' ? 'bg-gray-700/80 text-white' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white'">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                    </svg>
                                    Approvisionnement
                                </span>
                                <svg class="w-4 h-4 transition-transform duration-200 flex-shrink-0" :class="openMenu === 'appro' && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <ul x-show="openMenu === 'appro'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="mt-1 ml-3 space-y-0.5 border-l-2 border-gray-700 pl-3">
                                <li>
                                    <a href="{{ route('stock.dashboard-appro') }}"
                                       class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.dashboard-appro') ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock.produits-appro.index') }}"
                                       class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.produits-appro.*') ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                        Produits
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock.entrees.index', ['usage' => 'appro']) }}"
                                       class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.entrees.*') && request('usage') === 'appro' ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Entrees
                                    </a>
                                </li>
                                @role('admin')
                                <li class="pt-1.5 mt-1.5 border-t border-gray-700/50">
                                    <span class="flex items-center px-3 py-1.5 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Parametres</span>
                                </li>
                                <li>
                                    <a href="{{ route('stock.magasins-appro.index') }}"
                                       class="flex items-center px-3 py-1.5 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.magasins-appro.*') ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        Magasins
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock.categories-appro.index') }}"
                                       class="flex items-center px-3 py-1.5 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.categories-appro.*') ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        Categories
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock.fournisseurs-appro.index') }}"
                                       class="flex items-center px-3 py-1.5 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.fournisseurs-appro.*') ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        Fournisseurs
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock.demandeurs.index') }}"
                                       class="flex items-center px-3 py-1.5 text-sm rounded-lg transition-colors duration-150
                                              {{ request()->routeIs('stock.demandeurs.*') ? 'text-indigo-400 bg-indigo-500/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}">
                                        Demandeurs
                                    </a>
                                </li>
                                @endrole
                            </ul>
                        </li>
                    @endhasanyrole

                    {{-- ============================================================
                         ADMINISTRATION (Admin uniquement)
                    ============================================================ --}}
                    @role('admin')
                        <li class="pt-4 mt-3">
                            <p class="px-3 mb-2 text-[11px] font-semibold text-gray-500 uppercase tracking-widest">Administration</p>
                        </li>
                        <li>
                            <a href="{{ route('users.index') }}"
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                      {{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                Utilisateurs
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('settings.mail') }}"
                               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150
                                      {{ request()->routeIs('settings.mail') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Configuration e-mail
                            </a>
                        </li>
                    @endrole
                    @endauth
                </ul>
            </nav>

            <!-- Footer Sidebar -->
            <div class="px-4 py-3 border-t border-gray-700/50">
                @auth
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->users ?? 'U', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-200 truncate">{{ auth()->user()->users ?? 'Utilisateur' }}</p>
                        <p class="text-[11px] text-gray-500 truncate">{{ auth()->user()->role_name }}</p>
                    </div>
                </div>
                @endauth
                <p class="text-[10px] text-gray-600">GIMTEL v1.0</p>
            </div>
        </aside>

        <!-- Overlay mobile -->
        <div 
            x-show="sidebarOpen"
            @click="sidebarOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 md:hidden"
            x-cloak
        ></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 shadow-sm h-16 flex items-center justify-between px-4 md:px-6 z-30">
                <!-- Left: Hamburger -->
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>

                <!-- Right: Notifications + Profile -->
                <div class="flex items-center space-x-4">
                    @auth
                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open"
                                class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr(auth()->user()->users ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="hidden md:block text-left">
                                        <div class="text-sm font-medium text-gray-900">{{ auth()->user()->users ?? 'Utilisateur' }}</div>
                                        <div class="text-xs text-gray-500">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ auth()->user()->isAdmin() ? 'bg-purple-100 text-purple-800' : (auth()->user()->isAdminStock() ? 'bg-indigo-100 text-indigo-800' : (auth()->user()->isAgent() ? 'bg-blue-100 text-blue-800' : (auth()->user()->isClient() ? 'bg-emerald-100 text-emerald-800' : (auth()->user()->isDirectionProduction() ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800')))) }}">
                                                {{ auth()->user()->role_name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div 
                                x-show="open"
                                @click.away="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50 border border-gray-200"
                                x-cloak
                            >
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mon profil</a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <!-- Main Content -->
                <div class="p-4 md:p-6">
                    {{ $slot }}
                </div>

                <!-- Footer -->
                <footer class="bg-white border-t border-gray-200 px-4 md:px-6 py-4 mt-auto">
                    <p class="text-sm text-gray-500 text-center">© 2025 GIMTEL</p>
                </footer>
            </main>
        </div>
    </div>

    @livewireScripts
    
    @stack('scripts')
</body>
</html>

