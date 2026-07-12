@php $current = $current ?? null; @endphp

<!-- Barra superior de contacto -->
<div class="bg-sage-800 text-sage-100 py-2.5 text-[11px] font-semibold tracking-wide hidden md:block">
    <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
        <div class="flex gap-8">
            <span class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-sand-300" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.1 15.1 0 006.59 6.59l2.2-2.2a1 1 0 011.11-.27c1.12.45 2.33.69 3.58.69a1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.24 2.46.69 3.58a1 1 0 01-.27 1.11z"/></svg>
                Central: 01 234 5678
            </span>
            <span class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-sand-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/></svg>
                Av. Larco 1234, Miraflores
            </span>
        </div>
        <div class="flex gap-6 items-center">
            <span>Lun – Sáb · 8:00 a 20:00</span>
            <a href="{{ route('login') }}" class="text-sand-300 hover:text-white transition-colors">Portal del paciente</a>
        </div>
    </div>
</div>

<!-- Navegación principal -->
<header class="w-full bg-cream-50/90 backdrop-blur-xl border-b border-cream-300 sticky top-0 z-[100] transition-all"
    :class="{ 'shadow-[0_10px_30px_-12px_rgba(34,51,61,0.15)]': scrolled }">
    <nav class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('landing') }}" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl overflow-hidden ring-1 ring-cream-300 shadow-sm bg-white">
                <img src="{{ asset('images/logo_sia.jpeg') }}" alt="Sia" class="w-full h-full object-contain p-1.5">
            </div>
            <span class="text-2xl font-serif font-semibold tracking-tight text-sage-800">Sia<span class="text-sand-500">.</span></span>
        </a>

        <div class="hidden lg:flex items-center gap-9 text-sm font-semibold text-ink-700">
            <a href="{{ route('landing') }}#servicios" class="hover:text-sage-600 transition-colors">Servicios</a>
            <a href="{{ route('landing') }}#nosotros" class="hover:text-sage-600 transition-colors">Nosotros</a>
            <a href="{{ route('landing') }}#clinica" class="hover:text-sage-600 transition-colors">El espacio</a>
            <a href="{{ route('landing') }}#contacto" class="hover:text-sage-600 transition-colors">Contacto</a>
        </div>

        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('inicio') }}" class="btn-primary text-sm">Mi portal</a>
            @else
                @if($current !== 'login')
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex btn-ghost text-sm">Ingresar</a>
                @endif
                @if($current !== 'register')
                    <a href="{{ route('register') }}" class="btn-primary text-sm">Reservar cita</a>
                @endif
            @endauth
        </div>
    </nav>
</header>
