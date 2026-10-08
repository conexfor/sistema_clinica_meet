@php
    $current = $current ?? null;
    $active = $active ?? '';
@endphp

<!-- Barra superior de contacto -->
<div class="bg-sage-800 text-sage-100 py-2.5 text-[11px] font-semibold tracking-wide hidden md:block">
    <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
        <div class="flex gap-8">
            <span class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-sand-300" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.1 15.1 0 006.59 6.59l2.2-2.2a1 1 0 011.11-.27c1.12.45 2.33.69 3.58.69a1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.24 2.46.69 3.58a1 1 0 01-.27 1.11z"/></svg>
                Central: {{ config('clinic.phone') }}
            </span>
            <span class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-sand-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/></svg>                
                {{ config('clinic.address') }} · {{ config('clinic.address_detail') }}
            </span>
        </div>
        <div class="flex gap-6 items-center">
            <span>{{ config('clinic.hours') }}</span>
            @guest
                <a href="{{ route('login') }}" class="text-sand-300 hover:text-white transition-colors">Portal del paciente</a>
            @endguest
        </div>
    </div>
</div>

<!-- Navegación principal -->
<header class="w-full bg-cream-50/90 backdrop-blur-xl border-b border-cream-300 sticky top-0 z-[100] transition-all"
    :class="{ 'shadow-[0_10px_30px_-12px_rgba(34,51,61,0.15)]': scrolled }">
    <nav class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('landing') }}" class="flex items-center gap-3">
            <span class="text-2xl font-serif font-semibold tracking-tight text-sage-800">{{ config('clinic.name') }}<span class="text-sand-500">.</span></span>
        </a>

        @php
            $navItems = [];
            if (auth()->check()) {
                $user = auth()->user();
                $rol = $user->rol ?? null;
                $prefix = 'admin';
                if ($rol && ($rol->nombre ?? '') === 'receptionist') {
                    $prefix = 'receptionist';
                }

                if ($rol && in_array($rol->nombre, ['admin', 'receptionist'], true)) {
                    $navItems[] = ['route' => ($prefix === 'receptionist' ? 'receptionist.dashboard' : 'admin.dashboard'), 'label' => 'Resumen diario', 'key' => 'dashboard'];
                }
                if ($user->hasAction('book_appointments')) {
                    $navItems[] = ['route' => ($prefix === 'receptionist' ? 'receptionist.booking.create' : 'booking.create'), 'label' => 'Agendar cita'];
                }
                // Construir listado de reportes visibles (no añadimos un enlace plano aquí,
                // renderizaremos el desplegable más abajo para incluir también el dashboard del rol)
                $reportDropdown = null;
                if ($rol) {
                    $reportItems = [
                        ['route' => ($prefix === 'receptionist' ? 'receptionist.dashboard' : 'admin.dashboard'), 'label' => 'Resumen', 'key' => 'dashboard'],
                        ['route' => ($prefix === 'receptionist' ? 'receptionist.reportes.finanzas' : 'admin.reportes.finanzas'), 'label' => 'Finanzas', 'key' => 'finanzas', 'action' => 'reportes_finanzas'],
                        ['route' => ($prefix === 'receptionist' ? 'receptionist.reportes.desempeno' : 'admin.reportes.desempeno'), 'label' => 'Desempeño', 'key' => 'desempeno', 'action' => 'reportes_desempeno'],
                        ['route' => ($prefix === 'receptionist' ? 'receptionist.reportes.ocupacion' : 'admin.reportes.ocupacion'), 'label' => 'Ocupación', 'key' => 'ocupacion', 'action' => 'reportes_ocupacion'],
                        ['route' => ($prefix === 'receptionist' ? 'receptionist.dashboard' : 'admin.reportes.control'), 'label' => 'Panel de control', 'key' => 'control', 'action' => 'reportes_control'],
                        ['route' => ($prefix === 'receptionist' ? 'receptionist.dashboard' : 'admin.reportes.auditoria'), 'label' => 'Auditoría', 'key' => 'auditoria', 'action' => 'reportes_auditoria'],
                    ];
                    $subReportItems = array_filter($reportItems, function($it) { return ($it['key'] ?? '') !== 'dashboard'; });
                    $visibleSubs = array_values(array_filter($subReportItems, function($it) { return isset($it['action']) && auth()->check() && auth()->user()->hasAction($it['action']); }));
                    $shownSubs = $visibleSubs;
                    $reportDropdown = ['reportItems' => $reportItems, 'visibleSubs' => $visibleSubs, 'shownSubs' => $shownSubs];
                    if (!empty($visibleSubs)) {
                        // Quitar el enlace `Resumen diario` del nav principal porque lo incluiremos en el dropdown
                        $navItems = array_values(array_filter($navItems, function($it){ return ($it['key'] ?? '') !== 'dashboard'; }));
                    }
                }
                if ($rol && auth()->user()->hasAnyAction(['citas_index'])) {
                    $navItems[] = ['route' => "{$prefix}.citas.index", 'label' => 'Citas'];
                }
                if ($rol && auth()->user()->hasAnyAction(['pacientes_index'])) {
                    $navItems[] = ['route' => "{$prefix}.pacientes.index", 'label' => 'Pacientes'];
                }
                if ($rol && auth()->user()->hasAnyAction(['manage_services','servicios_index'])) {
                    $navItems[] = ['route' => "{$prefix}.servicios.index", 'label' => 'Servicios'];
                }
                if ($rol && auth()->user()->hasAnyAction(['manage_specialists','especialistas_index'])) {
                    $navItems[] = ['route' => "{$prefix}.especialistas.index", 'label' => 'Especialistas'];
                }
                if ($rol && auth()->user()->hasAnyAction(['manage_users','usuarios_index'])) {
                    $navItems[] = ['route' => "{$prefix}.usuarios.index", 'label' => 'Usuarios'];
                }
                if ($rol && auth()->user()->hasAnyAction(['roles_index'])) {
                    $navItems[] = ['route' => "{$prefix}.roles.index", 'label' => 'Roles'];
                }
                if ($rol && auth()->user()->hasAnyAction(['categorias_index'])) {
                    $navItems[] = ['route' => "{$prefix}.categorias.index", 'label' => 'Categorías'];
                }
                if ($rol && auth()->user()->hasAnyAction(['lista_espera_index'])) {
                    $navItems[] = ['route' => "{$prefix}.lista-espera.index", 'label' => 'Lista de espera'];
                }
                if ($rol && auth()->user()->hasAnyAction(['soporte_index'])) {
                    $navItems[] = ['route' => "{$prefix}.soporte.index", 'label' => 'Soporte'];
                }
            } else {
                // Public anchors for guests
                $navItems = [
                    ['href' => route('landing') . '#servicios', 'label' => 'Servicios'],
                    ['href' => route('landing') . '#nosotros', 'label' => 'Nosotros'],
                    ['href' => route('landing') . '#clinica', 'label' => 'El espacio'],
                    ['href' => route('landing') . '#contacto', 'label' => 'Contacto'],
                ];
            }
        @endphp

        <div class="hidden lg:flex items-center gap-9 text-sm font-semibold text-ink-700">
            {{-- Si hay reportes visibles, renderizamos el dropdown primero --}}
            @if(!empty($reportDropdown) && !empty($reportDropdown['visibleSubs']))
                @php
                    $reportItems = $reportDropdown['reportItems'];
                    $visibleSubs = $reportDropdown['visibleSubs'];
                    $shownSubs = $reportDropdown['shownSubs'];
                    $reportKeys = array_column($reportItems, 'key');
                @endphp
                @if(!empty($visibleSubs))
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-[13px] font-semibold transition-colors {{ in_array($active ?? '', $reportKeys) ? 'bg-sage-600 text-white shadow-sm' : 'text-ink-500 dark:text-sage-300 hover:text-sage-700 hover:bg-sage-50 dark:hover:bg-sage-800' }}">
                            Reportes
                            <svg :class="{'rotate-180': open}" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak x-transition class="absolute left-0 mt-2 w-52 surface !rounded-2xl p-2 z-[60]">
                            <a href="{{ route($reportItems[0]['route']) }}" class="block px-4 py-2.5 rounded-xl text-[13px] font-semibold transition-colors {{ ($active ?? '') === 'dashboard' ? 'bg-sage-50 dark:bg-sage-800 text-sage-700 dark:text-cream-100' : 'text-ink-600 dark:text-sage-300 hover:bg-sage-50 dark:hover:bg-sage-800 hover:text-sage-700' }}">Resumen</a>
                            @foreach($shownSubs as $item)
                                <a href="{{ route($item['route']) }}" class="block px-4 py-2.5 rounded-xl text-[13px] font-semibold transition-colors {{ ($active ?? '') === $item['key'] ? 'bg-sage-50 dark:bg-sage-800 text-sage-700 dark:text-cream-100' : 'text-ink-600 dark:text-sage-300 hover:bg-sage-50 dark:hover:bg-sage-800 hover:text-sage-700' }}">{{ $item['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ route($reportItems[0]['route']) }}" class="px-4 py-2 rounded-xl text-[13px] font-semibold transition-colors {{ ($active ?? '') === 'dashboard' ? 'bg-sage-600 text-white shadow-sm' : 'text-ink-500 dark:text-sage-300 hover:text-sage-700 hover:bg-sage-50 dark:hover:bg-sage-800' }}">Resumen diario</a>

                @endif
            @endif

            @foreach($navItems as $item)
                @if(!empty($visibleSubs) && (($item['key'] ?? '') === 'dashboard'))
                    @continue
                @endif
                @if(isset($item['route']))
                    @if(Route::has($item['route']))
                        <a href="{{ route($item['route']) }}" class="hover:text-sage-600 transition-colors">{{ $item['label'] }}</a>
                    @else
                        <a href="#" class="opacity-60 cursor-not-allowed">{{ $item['label'] }}</a>
                    @endif
                @else
                    <a href="{{ $item['href'] }}" class="hover:text-sage-600 transition-colors">{{ $item['label'] }}</a>
                @endif
            @endforeach
        </div>

        <div class="flex items-center gap-8">
            @auth
                @if(auth()->user()->isPatient())
                    <a href="{{ route('portal.index') }}" class="btn-primary text-sm">Mi portal</a>
                @else
                    <a href="{{ route('inicio') }}" class="btn-primary text-sm">Panel</a>
                @endif
            @else
                @if($current !== 'register')
                    <a href="{{ route('booking.register') }}" class="btn-primary text-sm relative z-10">Reservar cita</a>
                @endif
            @endauth
        </div>
    </nav>
</header>
