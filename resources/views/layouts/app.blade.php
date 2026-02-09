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

    <!-- Heroicons -->
    <script src="https://cdn.jsdelivr.net/npm/heroicons@2.0.18/24/outline/index.js" type="module"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    {{-- Alpine.js est déjà inclus dans Livewire 3, ne pas le charger séparément --}}

    <style>
        [x-cloak] { display: none !important; }
    </style>
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
            <nav class="flex-1 overflow-y-auto py-4 px-3">
                <ul class="space-y-1">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-white' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    @auth


                        @if(auth()->check() && auth()->user()->isAdmin())
                            <!-- Utilisateurs -->
                            <li>
                                <a href="{{ route('users.index') }}" 
                                   class="flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('users.*') ? 'bg-gray-700 text-white' : '' }}">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    <span>Utilisateurs</span>
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
            </nav>

            <!-- Footer Sidebar -->
            <div class="px-4 py-4 border-t border-gray-700">
                <div class="text-xs text-gray-400 mb-2">
                    Version 1.0.0
                </div>
                <a href="#" class="text-sm text-gray-300 hover:text-white transition-colors">
                    Aide
                </a>
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
                <!-- Left: Hamburger + Breadcrumb -->
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Breadcrumb -->
                    <nav class="hidden md:flex items-center space-x-2 text-sm text-gray-500">
                        <a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a>
                        @if(isset($breadcrumbs))
                            @foreach($breadcrumbs as $breadcrumb)
                                <span>/</span>
                                <a href="{{ $breadcrumb['url'] ?? '#' }}" class="hover:text-gray-700">{{ $breadcrumb['label'] }}</a>
                            @endforeach
                        @endif
                    </nav>
                </div>

                <!-- Right: Notifications + Profile -->
                <div class="flex items-center space-x-4">
                    @auth
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>

                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open"
                                class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr(auth()->user()->users ?? auth()->user()->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="hidden md:block text-left">
                                        <div class="text-sm font-medium text-gray-900">{{ auth()->user()->users ?? auth()->user()->name ?? 'Utilisateur' }}</div>
                                        <div class="text-xs text-gray-500">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ auth()->user()->isAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
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
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Paramètres</a>
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
                <!-- Page Header -->
                @if (isset($header))
                    <div class="bg-white border-b border-gray-200 px-4 md:px-6 py-4">
                        {{ $header }}
                    </div>
                @endif

                <!-- Main Content -->
                <div class="p-4 md:p-6">
                    {{ $slot }}
                </div>

                <!-- Footer -->
                <footer class="bg-white border-t border-gray-200 px-4 md:px-6 py-4 mt-auto">
                    <p class="text-sm text-gray-500 text-center">© 2026 GIMTEL</p>
                </footer>
            </main>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div 
        x-data="toastNotifications()"
        x-init="init()"
        class="fixed top-4 right-4 z-50 space-y-2"
    >
        <template x-for="(toast, index) in toasts" :key="index">
            <div 
                x-show="toast.show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="transform translate-x-full opacity-0"
                x-transition:enter-end="transform translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="transform translate-x-0 opacity-100"
                x-transition:leave-end="transform translate-x-full opacity-0"
                :class="{
                    'bg-green-50 border-green-200 text-green-800': toast.type === 'success',
                    'bg-red-50 border-red-200 text-red-800': toast.type === 'error',
                    'bg-blue-50 border-blue-200 text-blue-800': toast.type === 'info',
                    'bg-yellow-50 border-yellow-200 text-yellow-800': toast.type === 'warning'
                }"
                class="max-w-sm w-full border rounded-lg shadow-lg p-4 flex items-center justify-between"
            >
                <div class="flex items-center space-x-3">
                    <svg x-show="toast.type === 'success'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <svg x-show="toast.type === 'error'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <svg x-show="toast.type === 'info'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <svg x-show="toast.type === 'warning'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm font-medium" x-text="toast.message"></p>
                </div>
                <button @click="removeToast(index)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <script>
        // Messages flash de session (échappés pour éviter les erreurs JavaScript)
        @php
            $success = session('success');
            $error = session('error');
            $info = session('info');
            $warning = session('warning');
        @endphp
        var sessionMessages = {
            success: @json($success ?? null),
            error: @json($error ?? null),
            info: @json($info ?? null),
            warning: @json($warning ?? null)
        };
        
        function toastNotifications() {
            return {
                toasts: [],
                init() {
                    // Écouter les messages flash de session
                    if (sessionMessages.success) {
                        this.add(sessionMessages.success, 'success');
                    }
                    if (sessionMessages.error) {
                        this.add(sessionMessages.error, 'error');
                    }
                    if (sessionMessages.info) {
                        this.add(sessionMessages.info, 'info');
                    }
                    if (sessionMessages.warning) {
                        this.add(sessionMessages.warning, 'warning');
                    }
                },
                add(message, type) {
                    if (typeof type === 'undefined') {
                        type = 'info';
                    }
                    var toast = {
                        message: message,
                        type: type,
                        show: true
                    };
                    this.toasts.push(toast);
                    var self = this;
                    setTimeout(function() {
                        self.removeToast(self.toasts.indexOf(toast));
                    }, 5000);
                },
                removeToast(index) {
                    if (index > -1) {
                        var self = this;
                        this.toasts[index].show = false;
                        setTimeout(function() {
                            self.toasts.splice(index, 1);
                        }, 300);
                    }
                }
            }
        }
    </script>

    @livewireScripts
    
    <script>
        // Gestion de l'expiration de session (30 minutes d'inactivité)
        (function() {
            const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes en millisecondes
            const WARNING_TIME = 5 * 60 * 1000; // Avertir 5 minutes avant expiration
            let lastActivity = Date.now();
            let warningShown = false;
            let timeoutId;
            
            // Fonction pour réinitialiser le timer
            function resetTimer() {
                lastActivity = Date.now();
                warningShown = false;
                clearTimeout(timeoutId);
                scheduleWarning();
            }
            
            // Fonction pour programmer l'avertissement
            function scheduleWarning() {
                clearTimeout(timeoutId);
                const timeUntilWarning = SESSION_TIMEOUT - WARNING_TIME;
                
                timeoutId = setTimeout(function() {
                    if (!warningShown) {
                        showSessionWarning();
                        warningShown = true;
                    }
                }, timeUntilWarning);
            }
            
            // Fonction pour afficher l'avertissement
            function showSessionWarning() {
                const minutesLeft = Math.floor(WARNING_TIME / 60000);
                const minuteText = minutesLeft > 1 ? 'minutes' : 'minute';
                const message = 'Votre session expirera dans ' + minutesLeft + ' ' + minuteText + ' d\'inactivité. Cliquez sur "Prolonger" pour continuer.';
                
                // Créer une notification
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg shadow-lg z-50 max-w-md';
                
                // Construire le HTML de la notification
                const notificationHTML = '<div class="flex items-start">' +
                    '<svg class="w-5 h-5 text-yellow-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">' +
                    '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>' +
                    '</svg>' +
                    '<div class="flex-1">' +
                    '<p class="text-sm font-medium text-yellow-800 mb-3">' + message + '</p>' +
                    '<div class="flex space-x-2">' +
                    '<button onclick="extendSession()" class="px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">' +
                    'Prolonger la session' +
                    '</button>' +
                    '<button onclick="this.parentElement.parentElement.parentElement.remove()" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors">' +
                    'Fermer' +
                    '</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
                
                notification.innerHTML = notificationHTML;
                document.body.appendChild(notification);
                
                // Auto-supprimer après 10 secondes si pas d'action
                setTimeout(function() {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 10000);
            }
            
            // Fonction pour prolonger la session
            window.extendSession = function() {
                // Faire une requête pour mettre à jour la session
                var dashboardUrl = '/dashboard';
                fetch(dashboardUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                })
                .then(function() {
                    resetTimer();
                    // Supprimer toutes les notifications d'avertissement
                    document.querySelectorAll('.fixed.top-4.left-1\\/2').forEach(function(el) {
                        if (el.textContent.includes('session expirera')) {
                            el.remove();
                        }
                    });
                })
                .catch(function(err) {
                    console.error('Erreur lors de la prolongation de session:', err);
                });
            };
            
            // Écouter les événements d'activité utilisateur
            const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
            events.forEach(function(event) {
                document.addEventListener(event, resetTimer, { passive: true });
            });
            
            // Initialiser le timer au chargement de la page
            scheduleWarning();
        })();
    </script>
</body>
</html>
