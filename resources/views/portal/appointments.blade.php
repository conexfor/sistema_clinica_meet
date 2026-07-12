@extends('components.layouts.portal')

@php $active = 'index'; @endphp

@section('content')

        <!-- Bienvenida -->
        <div class="mb-8">
            <h1 class="font-serif text-3xl text-sage-900 mb-1">Hola, {{ auth()->user()->nombre }} 🌿</h1>
            <p class="text-sm text-ink-500 font-medium">Este es tu espacio personal. Cuida tu proceso, un paso a la vez.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

            @php
                $now = now();
                $nextAppointment = $appointments->filter(fn($a) =>
                    in_array($a->estado, [\App\Models\Cita::STATUS_RESERVADA, \App\Models\Cita::STATUS_ATTENTION]) && $a->end_at->gt($now)
                )->first();
            @endphp

            <!-- Próxima sesión -->
            <div class="lg:col-span-2 rounded-3xl p-8 shadow-calm relative overflow-hidden flex flex-col justify-between h-full bg-gradient-to-br from-sage-700 to-sage-900">
                <div class="absolute top-0 right-0 w-64 h-64 bg-sand-300/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>

                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-2">
                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 text-cream-100 rounded-full text-xs font-bold uppercase tracking-widest border border-white/10">
                                Próxima sesión
                            </div>
                            @if($nextAppointment)
                                <x-session-badge :cita="$nextAppointment" />
                            @endif
                        </div>
                        @if($nextAppointment)
                            @php $meetHeroDisponible = $nextAppointment->disponibilidad && $nextAppointment->start_at->copy()->subMinutes(30)->isPast(); @endphp
                            @if($nextAppointment->modalidad_id == \App\Models\Modalidad::VIRTUAL && $meetHeroDisponible)
                                <a href="{{ route('booking.join_meeting', $nextAppointment->id) }}" target="_blank" class="px-4 py-2 bg-white text-sage-700 hover:bg-cream-100 rounded-full text-sm font-bold shadow transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    Acceder a Meet
                                </a>
                            @elseif($nextAppointment->modalidad_id == \App\Models\Modalidad::VIRTUAL)
                                <span class="px-4 py-2 bg-white/15 text-cream-100 rounded-full text-sm font-bold border border-white/20 flex items-center gap-2" title="El enlace se habilita 30 minutos antes del inicio">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Meet disponible 30 min antes
                                </span>
                            @else
                                <span class="px-4 py-2 bg-white/15 text-cream-100 rounded-full text-sm font-bold border border-white/20 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Presencial
                                </span>
                            @endif
                        @endif
                    </div>

                    @if($nextAppointment)
                        <div class="pb-2">
                            <h2 class="font-serif text-3xl md:text-5xl text-white leading-tight mb-2">
                                {{ $nextAppointment->disponibilidad->fecha->locale('es')->isoFormat('dddd D [de] MMMM') }}
                            </h2>
                            <p class="text-xl md:text-2xl font-semibold text-sand-300 mb-8">
                                {{ \Carbon\Carbon::parse($nextAppointment->disponibilidad->hora_inicio)->format('h:i A') }} —
                                {{ \Carbon\Carbon::parse($nextAppointment->hora_fin)->format('h:i A') }}
                            </p>
                            <div class="flex items-center gap-4 bg-white/10 backdrop-blur-sm px-5 py-3 rounded-2xl border border-white/10 w-fit">
                                <div class="w-10 h-10 bg-white/15 rounded-full flex items-center justify-center text-lg font-bold text-white border border-white/20">
                                    {{ strtoupper(substr($nextAppointment->especialista->usuario->nombre ?? 'D', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase text-cream-200/70 tracking-widest">Profesional asignado</p>
                                    <p class="text-sm font-bold text-white">Ps. {{ $nextAppointment->especialista->usuario->nombre }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center text-center py-4 h-full">
                            <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center text-3xl mb-4 border border-white/10">🌿</div>
                            <h3 class="font-serif text-2xl text-white mb-2">Sin próximas sesiones</h3>
                            <p class="text-cream-200/80 text-sm font-medium mb-6 max-w-sm mx-auto">No tienes citas agendadas. Mantener la constancia es parte del proceso.</p>
                            <a href="{{ route('booking.service') }}" class="btn bg-white text-sage-700 hover:bg-cream-100 px-6 py-2.5">Agendar sesión</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- KPIs -->
            <div class="flex flex-col gap-6">
                <div class="surface p-6 flex flex-col justify-center flex-1">
                    <div class="w-10 h-10 rounded-2xl bg-sage-100 text-sage-600 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-serif text-4xl text-sage-900 mb-1">{{ $stats['kpis']['citas_asistidas'] ?? 0 }}</h3>
                    <p class="text-xs font-bold text-ink-500 uppercase tracking-widest">Sesiones completadas</p>
                </div>
                @php
                    $proximasSesiones = $appointments->filter(fn($a) =>
                        in_array($a->estado, [\App\Models\Cita::STATUS_RESERVADA, \App\Models\Cita::STATUS_ATTENTION])
                        && $a->end_at->gt(now())
                    )->count();
                @endphp
                <div class="surface p-6 flex flex-col justify-center flex-1">
                    <div class="w-10 h-10 rounded-2xl bg-sand-100 text-sand-600 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="font-serif text-4xl text-sage-900 mb-1">{{ $proximasSesiones }}</h3>
                    <p class="text-xs font-bold text-ink-500 uppercase tracking-widest">Próximas sesiones</p>
                </div>
            </div>
        </div>

        <!-- Registro de ánimo (inicio / fin de sesión) -->
        @if($animoPrompt)
        <div class="surface p-6 md:p-8 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                <div class="flex-1">
                    <h3 class="font-serif text-xl text-sage-900">
                        {{ $animoPrompt['momento'] === 'inicial' ? '¿Cómo te sientes hoy?' : '¿Cómo terminaste tu última sesión?' }}
                    </h3>
                    <p class="text-sm text-ink-500 mt-1">Tu respuesta queda registrada en tu sesión y ayuda a tu especialista a acompañarte mejor.</p>
                </div>
                <form action="{{ route('portal.citas.animo', $animoPrompt['cita']) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="momento" value="{{ $animoPrompt['momento'] }}">
                    <div class="flex gap-2 justify-center">
                        @foreach([1 => '😟', 2 => '😕', 3 => '😐', 4 => '🙂', 5 => '😄'] as $v => $e)
                            <label class="cursor-pointer">
                                <input type="radio" name="valor" value="{{ $v }}" class="peer sr-only" onchange="this.form.submit()">
                                <span class="w-12 h-12 flex items-center justify-center text-2xl rounded-2xl border border-cream-300 bg-cream-100 hover:border-sage-300 peer-checked:border-sage-500 peer-checked:bg-sage-50 transition-all">{{ $e }}</span>
                            </label>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Estado de solicitudes de reprogramación -->
        @php $solicitudesVisibles = $solicitudes->filter(fn($s) => $s->estado !== \App\Models\SolicitudReprogramacion::ESTADO_APROBADA || $s->respondida_at?->gt(now()->subDays(3))); @endphp
        @if($solicitudesVisibles->isNotEmpty())
        <div id="solicitudes" class="surface p-6 md:p-8 mb-8 scroll-mt-24">
            <h3 class="font-serif text-xl text-sage-900 mb-5">Tus solicitudes de reprogramación</h3>
            <div class="space-y-3">
                @foreach($solicitudesVisibles as $solicitud)
                    @php
                        $estadoMap = [
                            'pendiente' => ['En revisión', 'bg-sand-100 text-sand-700', '⏳'],
                            'aprobada'  => ['Aprobada', 'bg-sage-600 text-white', '✅'],
                            'rechazada' => ['Rechazada', 'bg-sand-200 text-sand-800', '✕'],
                        ];
                        $meta = $estadoMap[$solicitud->estado] ?? ['—', 'bg-cream-200 text-ink-600', ''];
                    @endphp
                    <div class="p-4 rounded-2xl bg-cream-100/60 border border-cream-300">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-ink-900">
                                    Nuevo horario: {{ $solicitud->fecha_solicitada->locale('es')->isoFormat('dddd D [de] MMMM') }},
                                    {{ \Carbon\Carbon::parse($solicitud->hora_solicitada)->format('h:i A') }}
                                </p>
                                @if($solicitud->estado === 'rechazada' && $solicitud->respuesta_especialista)
                                    <p class="text-xs text-ink-500 mt-1 italic">Respuesta: “{{ $solicitud->respuesta_especialista }}”</p>
                                @endif
                            </div>
                            <span class="inline-flex items-center gap-1.5 self-start sm:self-auto py-1.5 px-4 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $meta[1] }}">
                                <span>{{ $meta[2] }}</span> {{ $meta[0] }}
                            </span>
                        </div>

                        @if($solicitud->estado === 'rechazada')
                            @php
                                $doctorTel = preg_replace('/\D/', '', optional(optional($solicitud->cita->especialista)->usuario)->telefono ?? '');
                                if ($doctorTel && strlen($doctorTel) === 9) { $doctorTel = '51' . $doctorTel; } // Perú por defecto
                                $waMsg = rawurlencode('Hola, soy ' . (auth()->user()->nombre ?? 'un paciente') . '. Mi solicitud de reprogramación fue rechazada y quisiera coordinar otro horario para mi cita.');
                                $waUrl = $doctorTel ? "https://web.whatsapp.com/send?phone={$doctorTel}&text={$waMsg}" : null;
                            @endphp
                            <div class="flex flex-wrap items-center gap-2 mt-4 pt-4 border-t border-cream-300">
                                <a href="{{ route('portal.reschedule.create', $solicitud->cita_id) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-sage-600 hover:bg-sage-700 px-4 py-2 rounded-full transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Elegir otro horario
                                </a>
                                @if($waUrl)
                                <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-[#25D366] hover:brightness-95 px-4 py-2 rounded-full transition-all">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    WhatsApp
                                </a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Tareas por realizar -->
        @if($tareas->isNotEmpty())
        <div id="tareas" class="surface overflow-hidden mb-8 scroll-mt-24">
            <div class="p-6 border-b border-cream-300 flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-sage-100 text-sage-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <h3 class="font-serif text-xl text-sage-900">Tareas por realizar</h3>
                @php $pendientes = $tareas->where('completada', false)->count(); @endphp
                @if($pendientes > 0)
                    <span class="ml-1 text-xs font-bold px-2.5 py-1 rounded-full bg-sage-500 text-white">{{ $pendientes }}</span>
                @endif
            </div>
            <div class="divide-y divide-cream-200">
                @foreach($tareas as $tarea)
                <div class="p-5 flex items-start gap-4 {{ $tarea->completada ? 'bg-cream-100/40' : '' }}">
                    <form action="{{ route('portal.tasks.toggle', $tarea) }}" method="POST" class="shrink-0 pt-0.5">
                        @csrf @method('PATCH')
                        <button type="submit" aria-label="{{ $tarea->completada ? 'Marcar como pendiente' : 'Marcar como completada' }}"
                                class="w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-colors {{ $tarea->completada ? 'bg-sage-500 border-sage-500 text-white' : 'border-cream-400 hover:border-sage-400' }}">
                            @if($tarea->completada)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </button>
                    </form>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-ink-900 {{ $tarea->completada ? 'line-through text-ink-500' : '' }}">{{ $tarea->titulo }}</h4>
                        @if($tarea->descripcion)
                            <p class="text-sm text-ink-500 mt-1 leading-relaxed">{{ $tarea->descripcion }}</p>
                        @endif
                        <p class="text-[10px] font-bold uppercase tracking-widest text-ink-400 mt-2">
                            Asignada por Ps. {{ $tarea->especialista->usuario->nombre ?? 'tu especialista' }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Actividad reciente -->
        <div id="actividad" class="surface overflow-hidden scroll-mt-24">
            <div class="p-6 border-b border-cream-300 flex items-center justify-between">
                <h3 class="font-serif text-xl text-sage-900">Mi actividad reciente</h3>
                <a href="{{ route('portal.history') }}" class="btn-outline !py-2 text-sm">Ver historial completo</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-cream-100">
                            <th class="px-6 py-4 text-xs font-bold text-ink-500 uppercase tracking-wider">Fecha y hora</th>
                            <th class="px-6 py-4 text-xs font-bold text-ink-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-4 text-xs font-bold text-ink-500 uppercase tracking-wider">Terapeuta</th>
                            <th class="px-6 py-4 text-xs font-bold text-ink-500 uppercase tracking-wider">Modalidad</th>
                            <th class="px-6 py-4 text-xs font-bold text-ink-500 uppercase tracking-wider text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cream-200">
                        @forelse($recentHistory as $cita)
                        <tr class="hover:bg-cream-100/60 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-sage-900 block mb-0.5">{{ \Carbon\Carbon::parse($cita->disponibilidad->fecha)->locale('es')->isoFormat('dddd D MMM, YYYY') }}</span>
                                <span class="text-xs text-ink-500 font-semibold">{{ \Carbon\Carbon::parse($cita->disponibilidad->hora_inicio)->format('h:i A') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $est = strtolower($cita->estado);
                                    $bg = 'bg-cream-200 text-ink-600';
                                    if(in_array($est, ['reservada'])) $bg = 'bg-sage-100 text-sage-700';
                                    if(in_array($est, ['realizada', 'completada', 'completed'])) $bg = 'bg-sage-600 text-white';
                                    if(in_array($est, ['cancelada'])) $bg = 'bg-sand-200 text-sand-800';
                                @endphp
                                <span class="inline-flex py-1 px-3 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $bg }}">{{ $cita->estado }}</span>
                                <x-cancellation-note :cita="$cita" />
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-sage-100 text-sage-700 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($cita->especialista->usuario->nombre ?? 'D', 0, 1)) }}
                                    </div>
                                    <span class="text-sm text-ink-700 font-bold">{{ $cita->especialista->usuario->nombre ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4"><x-session-badge :cita="$cita" /></td>
                            <td class="px-6 py-4 text-right">
                                @if(in_array($cita->estado, [\App\Models\Cita::STATUS_RESERVADA, \App\Models\Cita::STATUS_RESCHEDULED]))
                                    <div class="flex items-center justify-end gap-2">
                                        @php $meetRowDisponible = $cita->disponibilidad && $cita->start_at->copy()->subMinutes(30)->isPast(); @endphp
                                        @if($cita->modalidad_id == \App\Models\Modalidad::VIRTUAL && $meetRowDisponible)
                                            <a href="{{ route('booking.join_meeting', $cita->id) }}" target="_blank" class="btn-primary !py-2 !px-4 text-xs">Ir a consulta</a>
                                        @elseif($cita->modalidad_id == \App\Models\Modalidad::VIRTUAL)
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-ink-500 bg-cream-100 px-3 py-1.5 rounded-full" title="El enlace se habilita 30 minutos antes del inicio">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Disponible 30 min antes
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-sage-700 bg-sage-50 px-3 py-1.5 rounded-full">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                Presencial
                                            </span>
                                        @endif
                                        <a href="{{ route('portal.reschedule.create', $cita->id) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-ink-600 border border-cream-300 hover:border-sage-300 hover:text-sage-700 px-3 py-1.5 rounded-full transition-colors" title="Solicitar cambio de horario">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Reprogramar
                                        </a>
                                        <button type="button" onclick="openCancelModal({{ $cita->id }})" class="inline-flex items-center gap-1.5 text-xs font-bold text-red-600 border border-red-200 hover:bg-red-50 px-3 py-1.5 rounded-full transition-colors" title="Cancelar cita">
                                            Cancelar
                                        </button>
                                    </div>
                                @elseif(in_array($cita->estado, [\App\Models\Cita::STATUS_COMPLETED]) && !$cita->calificacion)
                                    <button type="button" onclick="openRateModal({{ $cita->id }}, 'Ps. {{ $cita->especialista->usuario->nombre ?? '' }}')" class="inline-flex text-xs font-bold text-sand-700 bg-sand-100 hover:bg-sand-200 px-4 py-2 rounded-full transition-colors">Calificar</button>
                                @else
                                    <span class="text-xs font-semibold text-ink-500 italic">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-sm font-medium text-ink-500">Aún no tienes un historial documentado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    <!-- Modal de calificación -->
    <div id="rateModal" class="fixed inset-0 z-[101] hidden">
        <div class="absolute inset-0 bg-sage-950/60 backdrop-blur-sm" onclick="toggleRateModal(false)"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
            <div class="surface p-8 text-center">
                <div class="w-20 h-20 bg-sand-100 rounded-3xl flex items-center justify-center text-4xl mx-auto mb-6">🌟</div>
                <h3 class="font-serif text-2xl text-sage-900 mb-1">Calificar sesión</h3>
                <p id="rateSpecialistName" class="text-xs font-bold text-ink-500 mb-8 uppercase tracking-widest">Ps. Doctor</p>
                <form id="rateForm" method="POST">
                    @csrf
                    <div class="flex justify-center gap-2 mb-6">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" onclick="setRating({{ $i }})" class="star-btn transition-all hover:scale-125" data-star="{{ $i }}">
                            <svg class="w-10 h-10 text-cream-300 transition-colors" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </button>
                        @endfor
                    </div>
                    <input type="hidden" name="calificacion" id="ratingInput" required>
                    <textarea name="comentario_paciente" rows="2" placeholder="Tu opinión nos ayuda a mejorar..." class="field resize-none mb-6"></textarea>
                    <div class="flex gap-4">
                        <button type="button" onclick="toggleRateModal(false)" class="flex-1 btn bg-cream-200 text-ink-700 hover:bg-cream-300 py-3.5">Volver</button>
                        <button type="submit" class="flex-1 btn-primary py-3.5">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de cancelación -->
    <div id="cancelModal" class="fixed inset-0 z-[101] hidden">
        <div class="absolute inset-0 bg-sage-950/60 backdrop-blur-sm" onclick="toggleCancelModal(false)"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-6">
            <div class="surface p-8 border-t-4 border-red-400">
                <h3 class="font-serif text-2xl text-red-700 mb-1">Cancelar cita</h3>
                <p class="text-sm text-ink-500 mb-6">¿Seguro que quieres cancelar esta cita? Puedes contarnos el motivo (opcional).</p>
                <form id="cancelForm" method="POST" class="space-y-5">
                    @csrf @method('PATCH')
                    <div>
                        <label for="pac_motivo" class="label">Motivo (opcional)</label>
                        <textarea id="pac_motivo" name="motivo_cancelacion" rows="3" maxlength="500" class="field resize-none" placeholder="Ej. Tengo un imprevisto…"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="toggleCancelModal(false)" class="flex-1 btn bg-cream-200 text-ink-700 hover:bg-cream-300 py-3">Volver</button>
                        <button type="submit" class="flex-1 btn bg-red-600 text-white hover:bg-red-700 py-3">Sí, cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleRateModal(show) {
            const modal = document.getElementById('rateModal');
            if (show) modal.classList.remove('hidden'); else modal.classList.add('hidden');
        }
        function openRateModal(citaId, specialistName) {
            const form = document.getElementById('rateForm');
            form.action = `/portal/citas/${citaId}/calificar`;
            document.getElementById('rateSpecialistName').textContent = specialistName;
            setRating(0);
            toggleRateModal(true);
        }
        function setRating(val) {
            document.getElementById('ratingInput').value = val;
            document.querySelectorAll('.star-btn svg').forEach((star, index) => {
                if (index < val) star.classList.replace('text-cream-300', 'text-sand-400');
                else star.classList.replace('text-sand-400', 'text-cream-300');
            });
        }

        function toggleCancelModal(show) {
            const modal = document.getElementById('cancelModal');
            if (show) modal.classList.remove('hidden'); else modal.classList.add('hidden');
        }
        function openCancelModal(citaId) {
            document.getElementById('cancelForm').action = `/portal/citas/${citaId}/cancelar`;
            toggleCancelModal(true);
        }
    </script>
@endpush
