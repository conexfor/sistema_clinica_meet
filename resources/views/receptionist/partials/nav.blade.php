@php
    $navItems = [];
    if (auth()->check()) {
        $navItems[] = ['route' => 'receptionist.dashboard', 'label' => 'Resumen diario', 'key' => 'dashboard'];
        if (auth()->user()->hasAction('book_appointments')) {
            $navItems[] = ['route' => 'receptionist.booking.create', 'label' => 'Agendar cita', 'key' => 'booking'];
        }
        // Reportes: prepararemos un desplegable más abajo que incluya el dashboard del rol
        // y solo el/los reportes activos (mostrando solo el reporte activo cuando corresponda).
        // Nota: algunos roles no tienen la acción genérica `view_reports`, pero sí acciones
        // específicas como `reportes_finanzas` — por eso detectamos sub-reportes visibles.
        $reportItems = [
            ['route' => 'receptionist.dashboard', 'label' => 'Resumen', 'key' => 'dashboard'],
            ['route' => 'receptionist.reportes.finanzas', 'label' => 'Finanzas', 'key' => 'finanzas', 'action' => 'reportes_finanzas'],
            ['route' => 'receptionist.reportes.desempeno', 'label' => 'Desempeño', 'key' => 'desempeno', 'action' => 'reportes_desempeno'],
            ['route' => 'receptionist.reportes.ocupacion', 'label' => 'Ocupación', 'key' => 'ocupacion', 'action' => 'reportes_ocupacion'],
        ];
        $subReportItems = array_filter($reportItems, function($it) { return ($it['key'] ?? '') !== 'dashboard'; });
        $visibleSubs = array_values(array_filter($subReportItems, function($it) { return isset($it['action']) && auth()->check() && auth()->user()->hasAction($it['action']); }));
            if (auth()->user()->hasAnyAction(['citas_index'])) {
                $navItems[] = ['route' => 'receptionist.citas.index', 'label' => 'Citas', 'key' => 'citas'];
            }
            if (auth()->user()->hasAnyAction(['pacientes_index'])) {
                $navItems[] = ['route' => 'receptionist.pacientes.index', 'label' => 'Pacientes', 'key' => 'pacientes'];
            }
        if (auth()->user()->hasAnyAction(['manage_services', 'servicios_index', 'servicios_create', 'servicios_edit', 'servicios_toggle', 'servicios_destroy'])) {
            $navItems[] = ['route' => 'receptionist.servicios.index', 'label' => 'Servicios', 'key' => 'servicios'];
        }
        if (auth()->user()->hasAnyAction(['manage_specialists', 'especialistas_index', 'especialistas_create', 'especialistas_edit'])) {
            $navItems[] = ['route' => 'receptionist.especialistas.index', 'label' => 'Especialistas', 'key' => 'especialistas'];
        }
        if (auth()->user()->hasAnyAction(['manage_users', 'usuarios_index', 'usuarios_toggle_estado'])) {
            $navItems[] = ['route' => 'receptionist.usuarios.index', 'label' => 'Usuarios', 'key' => 'usuarios'];
        }
        if (auth()->user()->hasAnyAction(['roles_index', 'roles_update'])) {
            $navItems[] = ['route' => 'receptionist.roles.index', 'label' => 'Roles', 'key' => 'roles'];
        }
        if (auth()->user()->hasAnyAction(['categorias_index'])) {
            $navItems[] = ['route' => 'receptionist.categorias.index', 'label' => 'Categorías', 'key' => 'categorias'];
        }
        if (auth()->user()->hasAnyAction(['lista_espera_index'])) {
            $navItems[] = ['route' => 'receptionist.lista-espera.index', 'label' => 'Lista de espera', 'key' => 'lista-espera'];
        }
        if (auth()->user()->hasAnyAction(['soporte_index'])) {
            $navItems[] = ['route' => 'receptionist.soporte.index', 'label' => 'Soporte', 'key' => 'soporte'];
        }
    }
    $active = $active ?? 'dashboard';

    // Notificaciones del recepcionista (mismas que cualquier usuario del sistema).
    $notificaciones = collect();
    $notiNoLeidas = 0;
    if (auth()->check()) {
        $notificaciones = \App\Models\Notificacion::where('usuario_id', auth()->id())->latest()->take(8)->get();
        $notiNoLeidas = \App\Models\Notificacion::where('usuario_id', auth()->id())->where('leida', false)->count();
    }
@endphp

<div class="px-4 sm:px-8 pt-4 pb-2 z-50 sticky top-0">
    <nav class="w-full max-w-screen-2xl mx-auto bg-white/85 dark:bg-sage-900/85 backdrop-blur-xl shadow-soft rounded-2xl px-4 sm:px-6 py-3 flex items-center justify-between border border-cream-300 dark:border-sage-700">
        <a href="{{ route('inicio') }}" class="flex items-center gap-3">
            <span class="text-xl font-serif font-semibold tracking-tight text-sage-800 dark:text-cream-100">{{ config('clinic.name') }}<span class="text-sand-500">.</span></span>
        </a>

        <div class="hidden lg:flex items-center gap-1">
            {{-- Render dropdown de Reportes para recepcionista si corresponde --}}
            @php
                $reportItems = [
                    ['route' => 'receptionist.dashboard', 'label' => 'Resumen', 'key' => 'dashboard'],
                    ['route' => 'receptionist.reportes.finanzas', 'label' => 'Finanzas', 'key' => 'finanzas', 'action' => 'reportes_finanzas'],
                    ['route' => 'receptionist.reportes.desempeno', 'label' => 'Desempeño', 'key' => 'desempeno', 'action' => 'reportes_desempeno'],
                    ['route' => 'receptionist.reportes.ocupacion', 'label' => 'Ocupación', 'key' => 'ocupacion', 'action' => 'reportes_ocupacion'],
                ];
                $subReportItems = array_filter($reportItems, function($it) { return ($it['key'] ?? '') !== 'dashboard'; });
                $visibleSubs = array_values(array_filter($subReportItems, function($it) { return isset($it['action']) && auth()->check() && auth()->user()->hasAction($it['action']); }));
                $shownSubs = $visibleSubs;
            @endphp

            @if(!empty($visibleSubs))
                {{-- Evitar duplicar el enlace `Resumen diario` en el nav principal cuando ya lo incluimos en el dropdown --}}
                @php $navItems = array_values(array_filter($navItems, function($it){ return ($it['key'] ?? '') !== 'dashboard'; })); @endphp
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-[13px] font-semibold transition-colors {{ in_array($active, array_column($reportItems, 'key')) ? 'bg-sage-600 text-white shadow-sm' : 'text-ink-500 dark:text-sage-300 hover:text-sage-700 hover:bg-sage-50 dark:hover:bg-sage-800' }}">
                        Reportes
                        <svg :class="{'rotate-180': open}" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak x-transition class="absolute left-0 mt-2 w-52 surface !rounded-2xl p-2 z-[60]">
                            <a href="{{ route('receptionist.dashboard') }}" class="block px-4 py-2.5 rounded-xl text-[13px] font-semibold transition-colors {{ $active === 'dashboard' ? 'bg-sage-50 dark:bg-sage-800 text-sage-700 dark:text-cream-100' : 'text-ink-600 dark:text-sage-300 hover:bg-sage-50 dark:hover:bg-sage-800 hover:text-sage-700' }}">Resumen</a>
                        @foreach($shownSubs as $item)
                            <a href="{{ route($item['route']) }}" class="block px-4 py-2.5 rounded-xl text-[13px] font-semibold transition-colors {{ $active === $item['key'] ? 'bg-sage-50 dark:bg-sage-800 text-sage-700 dark:text-cream-100' : 'text-ink-600 dark:text-sage-300 hover:bg-sage-50 dark:hover:bg-sage-800 hover:text-sage-700' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            @else
                    <a href="{{ route('receptionist.dashboard') }}" class="px-4 py-2 rounded-xl text-[13px] font-semibold transition-colors {{ $active === 'dashboard' ? 'bg-sage-600 text-white shadow-sm' : 'text-ink-500 dark:text-sage-300 hover:text-sage-700 hover:bg-sage-50 dark:hover:bg-sage-800' }}">Resumen diario</a>
            @endif

            @foreach ($navItems as $item)
                @if((($item['key'] ?? '') === 'dashboard'))
                    @continue
                @endif
                <a href="{{ route($item['route']) }}"
                    class="px-2.5 py-2 rounded-xl text-[13px] font-semibold transition-colors {{ $active === $item['key'] ? 'bg-sage-600 text-white shadow-sm' : 'text-ink-500 dark:text-sage-300 hover:text-sage-700 hover:bg-sage-50 dark:hover:bg-sage-800' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Campanita de notificaciones -->
            <div x-data="{ openN: false }" class="relative">
                <button @click="openN = !openN" @click.away="openN = false" aria-label="Notificaciones"
                        class="relative w-10 h-10 rounded-full flex items-center justify-center text-ink-500 dark:text-sage-300 hover:text-sage-700 hover:bg-sage-50 dark:hover:bg-sage-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($notiNoLeidas > 0)
                        <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">{{ $notiNoLeidas > 9 ? '9+' : $notiNoLeidas }}</span>
                    @endif
                </button>

                <div x-show="openN" x-cloak x-transition class="absolute right-0 mt-3 w-80 max-w-[calc(100vw-2rem)] surface !rounded-2xl z-50 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-cream-300 dark:border-sage-700">
                        <p class="font-serif text-base text-sage-900 dark:text-cream-100">Notificaciones</p>
                        @if($notiNoLeidas > 0)
                            <form method="POST" action="{{ route('receptionist.notificaciones.leer') }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-[10px] font-bold text-sage-600 hover:text-sage-800 uppercase tracking-widest">Marcar leídas</button>
                            </form>
                        @endif
                    </div>
                    <div class="max-h-96 overflow-y-auto divide-y divide-cream-200 dark:divide-sage-800">
                        @forelse($notificaciones as $noti)
                            <a href="{{ $noti->href }}" class="block px-4 py-3 hover:bg-cream-100/60 dark:hover:bg-sage-800/60 transition-colors {{ $noti->leida ? '' : 'bg-sage-50/60 dark:bg-sage-800/40' }}">
                                <div class="flex items-start gap-3">
                                    @if(!$noti->leida)<span class="mt-1.5 w-2 h-2 rounded-full bg-sage-500 shrink-0"></span>@else<span class="mt-1.5 w-2 h-2 rounded-full bg-cream-300 dark:bg-sage-700 shrink-0"></span>@endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-ink-900 dark:text-cream-100">{{ $noti->titulo }}</p>
                                        <p class="text-xs text-ink-500 dark:text-sage-300 mt-0.5 leading-relaxed">{{ $noti->mensaje }}</p>
                                        <p class="text-[10px] font-bold text-ink-400 uppercase tracking-widest mt-1">{{ $noti->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="px-4 py-10 text-center">
                                <div class="w-12 h-12 bg-cream-100 dark:bg-sage-800 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-3">🔔</div>
                                <p class="text-sm text-ink-500 dark:text-sage-300">No tienes notificaciones.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div x-data="{ open: false }" class="relative">
                <div @click="open = !open" @click.away="open = false" class="flex items-center gap-3 pl-3 cursor-pointer">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-sage-500 to-sage-700 text-white flex items-center justify-center text-sm font-bold ring-2 ring-white shadow-sm">
                        {{ strtoupper(substr(auth()->user()->nombre ?? 'R', 0, 1)) }}
                    </div>
                    <div class="hidden md:block text-left">
                        <p class="text-[13px] font-bold text-ink-900 dark:text-cream-100">{{ auth()->user()->nombre ?? 'Recepción' }}</p>
                        <p class="text-[11px] text-ink-500 dark:text-sage-400">{{ auth()->user()->email ?? 'recepcion@conexfor.com' }}</p>
                    </div>
                    <svg :class="{'rotate-180': open}" class="w-4 h-4 text-ink-500 dark:text-sage-300 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div x-show="open" x-cloak x-transition class="absolute right-0 mt-3 w-60 surface !rounded-2xl p-2 z-50">
                    <a href="{{ route('profile') }}" class="w-full text-left px-4 py-2.5 text-sm text-ink-700 dark:text-sage-200 hover:bg-sage-50 dark:hover:bg-sage-800 hover:text-sage-700 rounded-xl transition-colors flex items-center gap-2.5 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Mi perfil
                    </a>
                    <button type="button" @click="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');" class="w-full text-left px-4 py-2.5 text-sm text-ink-700 dark:text-sage-200 hover:bg-sage-50 dark:hover:bg-sage-800 hover:text-sage-700 rounded-xl transition-colors flex items-center gap-2.5 font-medium">
                        <svg class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m8.66-9h-1M4.34 12h-1m14.14 5.66l-.7-.7M6.34 6.34l-.7-.7m12.02 0l-.7.7M6.34 17.66l-.7.7M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg class="w-4 h-4 block dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        Modo oscuro / claro
                    </button>
                    <div class="h-px bg-cream-300 dark:bg-sage-700 my-1.5 mx-2"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-sand-700 dark:text-sand-300 hover:bg-sand-50 dark:hover:bg-sage-800 rounded-xl transition-colors flex items-center gap-2.5 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
</div>
