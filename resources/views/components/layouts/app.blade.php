<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<script>
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
</script>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sia') }} · Clínica Psicológica</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:ital,wght@0,300..800;1,300..800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body
    x-data="{ scrolled: false }"
    @scroll.window="scrolled = (window.pageYOffset > 30)"
    class="antialiased min-h-screen relative text-ink-900 dark:text-cream-100">

    <!-- Fondo orgánico sereno -->
    <div class="fixed inset-0 -z-10 bg-organic">
        <div class="absolute inset-0 bg-dots opacity-60"></div>
    </div>

    @php
        // Navegación de la topbar según el rol, para mantener las mismas opciones
        // que en el resto de interfaces de cada rol (incluido el flujo de reserva).
        $navItems = [];
        if (auth()->check()) {
            $u = auth()->user();
            if ($u->isPatient()) {
                $navItems = [
                    ['route' => 'portal.index',    'label' => 'Mi panel',       'active' => request()->routeIs('portal.index')],
                    ['route' => 'booking.service', 'label' => 'Agendar sesión', 'active' => request()->routeIs('booking.*')],
                    ['route' => 'portal.history',  'label' => 'Historial',      'active' => request()->routeIs('portal.history')],
                ];
            } elseif ($u->isDoctor()) {
                $navItems = [
                    ['route' => 'doctor.dashboard',       'label' => 'Resumen',       'active' => request()->routeIs('doctor.dashboard')],
                    ['route' => 'doctor.schedule',        'label' => 'Mi agenda',     'active' => request()->routeIs('doctor.schedule')],
                    ['route' => 'doctor.patients.index',  'label' => 'Mis pacientes', 'active' => request()->routeIs('doctor.patients.*')],
                    ['route' => 'doctor.services',        'label' => 'Servicios',     'active' => request()->routeIs('doctor.services')],
                    ['route' => 'doctor.profile',         'label' => 'Mi perfil',     'active' => request()->routeIs('doctor.profile')],
                ];
            } elseif ($u->isReceptionist()) {
                $navItems = [
                    ['route' => 'receptionist.dashboard',      'label' => 'Resumen diario', 'active' => request()->routeIs('receptionist.dashboard')],
                    ['route' => 'receptionist.booking.create', 'label' => 'Agendar cita',   'active' => request()->routeIs('receptionist.booking.*')],
                ];
            } elseif ($u->isAdmin()) {
                $navItems = [
                    ['route' => 'admin.dashboard', 'label' => 'Panel', 'active' => request()->routeIs('admin.dashboard')],
                ];
            }
        }
    @endphp

    @if(auth()->check() && auth()->user()->isPatient())
        {{-- El paciente usa el mismo topbar que Mi panel / Historial --}}
        @include('portal.partials.nav', ['active' => request()->routeIs('portal.history') ? 'history' : (request()->routeIs('portal.index') ? 'index' : 'schedule')])
    @else
    <!-- Barra de navegación flotante -->
    <div class="px-4 sm:px-8 pt-5 z-50 sticky top-0">
        <nav
            :class="scrolled ? 'shadow-[0_12px_40px_-12px_rgba(46,58,51,0.18)] border-sage-200/70' : 'border-transparent'"
            class="max-w-7xl mx-auto w-full bg-white/85 dark:bg-sage-900/85 backdrop-blur-xl rounded-[1.75rem] px-5 sm:px-8 py-4 flex items-center justify-between border transition-all duration-500">

            <!-- Marca -->
            <a href="{{ route('inicio') }}" class="flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-2xl overflow-hidden shadow-sm ring-1 ring-cream-300 dark:ring-sage-700 group-hover:scale-105 group-hover:-rotate-2 transition-transform bg-white">
                    <img src="{{ asset('images/logo_sia.jpeg') }}" alt="Sia" class="w-full h-full object-contain p-1.5">
                </div>
                <span class="text-2xl font-serif font-semibold tracking-tight text-sage-800 dark:text-cream-100">Sia<span class="text-sand-500">.</span></span>
            </a>

            <!-- Navegación por rol -->
            @if(!empty($navItems))
                <div class="hidden lg:flex items-center gap-1">
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           @if($item['active']) aria-current="page" @endif
                           class="px-4 py-2 rounded-xl text-[13px] font-semibold transition-colors {{ $item['active'] ? 'bg-sage-600 text-white shadow-sm' : 'text-ink-500 dark:text-sage-300 hover:text-sage-700 hover:bg-sage-50 dark:hover:bg-sage-800' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Acciones -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Toggle de tema -->
                <button
                    x-data
                    @click="
                        document.documentElement.classList.toggle('dark');
                        localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
                    "
                    aria-label="Cambiar tema"
                    class="w-10 h-10 rounded-full flex items-center justify-center text-sage-600 dark:text-sand-300 hover:bg-sage-50 dark:hover:bg-sage-800 transition-colors">
                    <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m8.66-9h-1M4.34 12h-1m14.14 5.66l-.7-.7M6.34 6.34l-.7-.7m12.02 0l-.7.7M6.34 17.66l-.7.7M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>

                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2.5 cursor-pointer p-1.5 pr-4 bg-cream-100 dark:bg-sage-800 border border-cream-300 dark:border-sage-700 rounded-full transition-all hover:border-sage-300">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-sage-500 to-sage-700 text-white flex items-center justify-center text-xs font-bold">
                                {{ strtoupper(substr(auth()->user()->nombre ?? 'U', 0, 1)) }}
                            </div>
                            <span class="text-sm font-semibold text-ink-700 dark:text-cream-100 hidden sm:block">{{ auth()->user()->nombre }}</span>
                        </button>
                        <div x-show="open" x-cloak x-transition class="absolute right-0 mt-3 w-52 surface !rounded-2xl p-2 z-50">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-sand-700 dark:text-sand-300 font-semibold rounded-xl hover:bg-sand-50 dark:hover:bg-sage-800 transition-colors flex items-center gap-2.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    @if(!request()->routeIs('login') && !request()->routeIs('register'))
                        <a href="{{ route('login') }}" class="btn-primary !py-2.5 !px-6 text-sm">Ingresar</a>
                    @endif
                @endauth
            </div>
        </nav>
    </div>
    @endif

    <!-- Contenido principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-8 py-8 sm:py-12 relative z-10">
        @if (session('success'))
            <div class="alert-auto-dismiss mb-6 flex items-center gap-3 rounded-2xl border border-sage-200 bg-sage-50 dark:bg-sage-900 dark:border-sage-700 px-5 py-4 text-sage-800 dark:text-sage-100">
                <svg class="w-5 h-5 flex-shrink-0 text-sage-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="alert-auto-dismiss mb-6 flex items-center gap-3 rounded-2xl border border-sand-300 bg-sand-50 px-5 py-4 text-sand-800">
                <svg class="w-5 h-5 flex-shrink-0 text-sand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        // Auto-descartar alertas después de 4 segundos
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.alert-auto-dismiss').forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-16px)';
                    setTimeout(function() { alert.remove(); }, 600);
                }, 4000);
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
