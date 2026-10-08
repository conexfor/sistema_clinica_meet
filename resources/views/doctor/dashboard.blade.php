@extends('components.layouts.doctor')

@php $active = 'dashboard'; @endphp

@section('content')
<div x-data="{ quickNote: false, calOpen: false }">

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="font-serif text-3xl text-sage-900">Hoy, {{ now()->locale('es')->isoFormat('dddd D [de] MMMM') }}</h1>
            <p class="text-sm text-ink-500 mt-1">Tu jornada de un vistazo: agenda, seguimientos y solicitudes.</p>
        </div>
        <a href="{{ route('doctor.schedule') }}" class="btn-primary !py-2.5 text-sm whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nueva disponibilidad
        </a>
    </div>

    <!-- Tiempo entre sesiones + resumen del día -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Contador de tiempo libre -->
        <div class="lg:col-span-2 rounded-3xl p-8 shadow-calm relative overflow-hidden bg-gradient-to-br from-sage-700 to-sage-900 text-white">
            <div class="absolute top-0 right-0 w-64 h-64 bg-sand-300/15 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-sand-300 mb-4">Tiempo entre sesiones</p>
                @if($nextAppointment)
                    <div data-countdown data-start="{{ $nextAppointment->start_at->toIso8601String() }}">
                        <p class="text-sm text-cream-100/80 mb-1">Tu próxima cita es a las
                            <span class="font-bold text-white">{{ $nextAppointment->start_at->format('h:i A') }}</span>
                            @if(!$nextAppointment->start_at->isToday())
                                ({{ $nextAppointment->start_at->locale('es')->isoFormat('ddd D MMM') }})
                            @endif
                        </p>
                        <div class="flex items-baseline gap-2 mt-3">
                            <span data-countdown-value class="font-serif text-5xl md:text-6xl leading-none">—</span>
                        </div>
                        <p data-countdown-label class="text-cream-100/70 text-sm mt-2">libres para tomar notas o descansar</p>
                        <div class="mt-6 flex items-center gap-3 bg-white/10 border border-white/10 rounded-2xl px-4 py-3 w-fit">
                            <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center text-sm font-bold">
                                {{ strtoupper(substr($nextAppointment->paciente->nombre ?? 'P', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-widest text-cream-200/70 font-bold">Con</p>
                                <p class="text-sm font-bold">{{ $nextAppointment->paciente->nombre ?? 'Paciente' }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="py-4">
                        <p class="font-serif text-3xl mb-2">Sin próximas citas 🌿</p>
                        <p class="text-cream-100/80 text-sm">Aprovecha para ponerte al día con tus notas.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Mini-stats del día -->
        <div class="flex flex-col gap-6">
            <div class="surface p-6 flex-1 flex flex-col justify-center">
                <div class="w-10 h-10 rounded-2xl bg-sage-100 text-sage-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="font-serif text-4xl text-sage-900 mb-1">{{ $todaySessionsCount }}</h3>
                <p class="text-xs font-bold text-ink-500 uppercase tracking-widest">Sesiones hoy</p>
            </div>
            <div class="surface p-6 flex-1 flex flex-col justify-center">
                <div class="w-10 h-10 rounded-2xl bg-sand-100 text-sand-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7l2 2 4-4"/></svg>
                </div>
                <h3 class="font-serif text-4xl text-sage-900 mb-1">{{ $pendingTasks->count() }}</h3>
                <p class="text-xs font-bold text-ink-500 uppercase tracking-widest">Tareas por seguir</p>
            </div>
        </div>
    </div>

    <!-- Agenda de hoy -->
    <div class="surface overflow-hidden mb-8">
        <div class="p-6 border-b border-cream-300 flex items-center justify-between">
            <h3 class="font-serif text-xl text-sage-900">Agenda de hoy</h3>
            <button type="button" @click="calOpen = true" class="btn-outline !py-2 text-sm">Ver calendario</button>
        </div>
        <div class="divide-y divide-cream-200">
            @forelse($todaySessions as $cita)
            @php
                $estadoChip = match($cita->estado) {
                    \App\Models\Cita::STATUS_COMPLETED => ['Realizada', 'bg-sage-600 text-white'],
                    \App\Models\Cita::STATUS_ATTENTION => ['En atención', 'bg-sand-100 text-sand-700'],
                    default => ['Reservada', 'bg-sage-100 text-sage-700'],
                };
                $realizada = $cita->estado === \App\Models\Cita::STATUS_COMPLETED;
            @endphp
            <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4 {{ $realizada ? 'opacity-70' : '' }}">
                <div class="w-16 text-center shrink-0">
                    <p class="font-serif text-xl text-sage-900">{{ \Carbon\Carbon::parse($cita->disponibilidad->hora_inicio)->format('h:i') }}</p>
                    <p class="text-[10px] font-bold text-ink-400 uppercase">{{ \Carbon\Carbon::parse($cita->disponibilidad->hora_inicio)->format('A') }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-sage-100 text-sage-700 flex items-center justify-center font-bold text-sm shrink-0">
                    {{ strtoupper(substr($cita->paciente->nombre ?? 'P', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-ink-900">{{ $cita->paciente->nombre ?? 'Paciente' }}</p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <x-session-badge :cita="$cita" />
                        <span class="inline-flex py-1 px-2.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $estadoChip[1] }}">{{ $estadoChip[0] }}</span>
                    </div>
                </div>
                <a href="{{ route('doctor.session', $cita) }}" class="btn-primary !py-2 !px-4 text-xs uppercase tracking-widest shrink-0">
                    {{ $realizada ? 'Ver sesión' : ($cita->modalidad_id == \App\Models\Modalidad::VIRTUAL ? 'Entrar a sesión' : 'Abrir sesión') }}
                </a>
            </div>
            @empty
            <div class="p-10 text-center">
                <div class="w-16 h-16 bg-cream-100 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-3">☕</div>
                <p class="font-serif text-lg text-sage-900">No tienes sesiones hoy</p>
                <p class="text-ink-500 text-sm mt-1">Un buen día para revisar seguimientos y notas.</p>
            </div>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Bandeja de solicitudes -->
        <div class="surface overflow-hidden">
            <div class="p-6 border-b border-cream-300 flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-sand-100 text-sand-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <h3 class="font-serif text-xl text-sage-900">Bandeja de solicitudes</h3>
                @if($solicitudesPendientes->count() > 0)
                    <span class="ml-1 text-xs font-bold px-2.5 py-1 rounded-full bg-red-500 text-white">{{ $solicitudesPendientes->count() }}</span>
                @endif
            </div>
            <div class="divide-y divide-cream-200 max-h-[420px] overflow-y-auto">
                @forelse($solicitudesPendientes as $solicitud)
                <div class="p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 rounded-full bg-sage-100 text-sage-700 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ strtoupper(substr($solicitud->paciente->nombre ?? 'P', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-ink-900">{{ $solicitud->paciente->nombre }}</p>
                            <p class="text-xs text-ink-500">
                                @if($solicitud->cita->disponibilidad)
                                    {{ $solicitud->cita->disponibilidad->fecha->locale('es')->isoFormat('D MMM') }},
                                    {{ \Carbon\Carbon::parse($solicitud->cita->disponibilidad->hora_inicio)->format('h:i A') }}
                                    <span class="mx-1 text-ink-400">→</span>
                                @endif
                                <span class="font-bold text-sage-700">{{ $solicitud->fecha_solicitada->locale('es')->isoFormat('D MMM') }}, {{ \Carbon\Carbon::parse($solicitud->hora_solicitada)->format('h:i A') }}</span>
                            </p>
                        </div>
                    </div>
                    @if($solicitud->motivo)
                        <p class="text-xs text-ink-500 italic mb-3">“{{ $solicitud->motivo }}”</p>
                    @endif
                    <div class="flex items-center gap-2">
                        <form action="{{ route('doctor.solicitudes.approve', $solicitud) }}" method="POST" data-confirm="¿Aprobar y reprogramar la cita a este horario?" data-confirm-ok="Aprobar">
                            @csrf
                            <button type="submit" class="btn-primary !py-1.5 !px-4 text-xs uppercase tracking-widest">Aprobar</button>
                        </form>
                        <form action="{{ route('doctor.solicitudes.reject', $solicitud) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-outline !py-1.5 !px-4 text-xs uppercase tracking-widest">Rechazar</button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="p-10 text-center">
                    <div class="w-14 h-14 bg-sage-50 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-3">✅</div>
                    <p class="text-sm font-semibold text-ink-600">Sin solicitudes pendientes</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="space-y-8">
            <!-- Recordatorios de mañana -->
            <div class="surface p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-serif text-xl text-sage-900">Recordatorios de mañana</h3>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-ink-400">{{ now()->addDay()->locale('es')->isoFormat('ddd D MMM') }}</span>
                </div>
                <div class="flex items-center gap-4 mb-5">
                    <div class="text-center">
                        <p class="font-serif text-3xl text-sage-900">{{ $recordatorios->count() }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Enviados</p>
                    </div>
                    <div class="h-10 w-px bg-cream-300"></div>
                    <div class="text-center">
                        <p class="font-serif text-3xl text-sand-600">{{ max(0, $citasManana - $recordatorios->count()) }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Pendientes</p>
                    </div>
                    <div class="flex-1 flex justify-end">
                        <form action="{{ route('doctor.recordatorios.enviar') }}" method="POST">
                            @csrf
                            <button type="submit" @if($citasManana - $recordatorios->count() <= 0) disabled @endif
                                class="btn-primary !py-2 !px-4 text-xs uppercase tracking-widest disabled:opacity-40 disabled:cursor-not-allowed">
                                Enviar ahora
                            </button>
                        </form>
                    </div>
                </div>
                @if($recordatorios->isNotEmpty())
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        @foreach($recordatorios as $r)
                        <div class="flex items-center gap-2 text-xs text-ink-600">
                            <svg class="w-3.5 h-3.5 text-sage-500 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span class="font-semibold">{{ $r->paciente->nombre ?? 'Paciente' }}</span>
                            <span class="text-ink-400">· notificado {{ $r->enviado_at?->diffForHumans() }} ({{ $r->canal }})</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-ink-500 italic">Aún no se han enviado recordatorios para mañana.</p>
                @endif
            </div>

            <!-- Tareas por seguir -->
            <div class="surface overflow-hidden">
                <div class="p-6 border-b border-cream-300">
                    <h3 class="font-serif text-xl text-sage-900">Tareas por seguir</h3>
                    <p class="text-xs text-ink-500 mt-1">Pendientes que dejaste a tus pacientes.</p>
                </div>
                <div class="divide-y divide-cream-200 max-h-64 overflow-y-auto">
                    @forelse($pendingTasks as $tarea)
                    <div class="p-4 flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-sand-400 mt-2 shrink-0"></div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-ink-900">{{ $tarea->titulo }}</p>
                            <p class="text-xs text-ink-500 mt-0.5">
                                {{ $tarea->paciente->nombre ?? 'Paciente' }} ·
                                @if($tarea->proxima_cita)
                                    preguntar el <span class="font-bold text-sage-700">{{ \Carbon\Carbon::parse($tarea->proxima_cita)->locale('es')->isoFormat('D MMM') }}</span>
                                @else
                                    <span class="text-sand-600 font-semibold">sin próxima cita agendada</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center">
                        <p class="text-sm text-ink-500">No hay tareas pendientes de seguimiento.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Botón flotante: nota rápida post-sesión -->
    @if($todaySessions->isNotEmpty())
    <button @click="quickNote = true" class="fixed bottom-6 left-6 z-[90] w-16 h-16 rounded-2xl bg-sage-600 hover:bg-sage-700 text-white shadow-lift flex items-center justify-center transition-all hover:scale-105" title="Nota rápida post-sesión">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
    </button>

    <!-- Modal nota rápida -->
    <div x-show="quickNote" x-cloak class="fixed inset-0 z-[100]" x-data="quickResumen()">
        <div class="absolute inset-0 bg-sage-950/60 backdrop-blur-sm" @click="quickNote = false"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-4">
            <div class="surface p-8">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-serif text-2xl text-sage-900">Nota rápida de sesión</h3>
                    <button @click="quickNote = false" class="text-ink-400 hover:text-ink-700"><svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <p class="text-sm text-ink-500 mb-6">Registra el resumen clínico sin salir de aquí.</p>

                <form :action="action" method="POST" class="space-y-5">
                    @csrf @method('PUT')
                    <div>
                        <label class="label">Sesión de hoy</label>
                        <select x-model="citaId" @change="sync()" class="field cursor-pointer">
                            @foreach($todaySessions as $cita)
                                <option value="{{ $cita->id }}"
                                    data-resumen="{{ $cita->resumen_sesion }}"
                                    data-compartido="{{ $cita->resumen_compartido ? 1 : 0 }}">
                                    {{ \Carbon\Carbon::parse($cita->disponibilidad->hora_inicio)->format('h:i A') }} · {{ $cita->paciente->nombre ?? 'Paciente' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Resumen clínico</label>
                        <textarea name="resumen_sesion" x-model="resumen" rows="5" class="field resize-none" placeholder="Ej. Trabajamos en la reestructuración cognitiva sobre el miedo al fracaso…"></textarea>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="resumen_compartido" value="1" x-model="compartido" class="peer sr-only">
                        <span class="w-11 h-6 rounded-full bg-cream-300 peer-checked:bg-sage-500 relative transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
                        <span class="text-sm font-semibold text-ink-700">Compartir con el paciente</span>
                    </label>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="quickNote = false" class="flex-1 btn bg-cream-200 text-ink-700 hover:bg-cream-300 py-3">Cancelar</button>
                        <button type="submit" class="flex-1 btn-primary py-3">Guardar resumen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal: calendario de citas -->
    <div x-show="calOpen" x-cloak class="fixed inset-0 z-[100]" x-data="citasCalendar()">
        <div class="absolute inset-0 bg-sage-950/60 backdrop-blur-sm" @click="calOpen = false"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl p-4">
            <div class="surface p-6 sm:p-8">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h3 class="font-serif text-2xl text-sage-900 dark:text-cream-100">Calendario de citas</h3>
                        <p class="text-sm text-ink-500 dark:text-sage-300">Días con reservas marcados según su estado.</p>
                    </div>
                    <button @click="calOpen = false" class="text-ink-400 hover:text-ink-700 dark:hover:text-cream-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Navegación de mes -->
                <div class="flex items-center justify-between mb-4">
                    <button type="button" @click="prev()" class="w-9 h-9 rounded-full flex items-center justify-center text-ink-500 dark:text-sage-300 hover:bg-cream-100 dark:hover:bg-sage-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <p class="font-serif text-lg text-sage-900 dark:text-cream-100 capitalize" x-text="monthNames[month] + ' ' + year"></p>
                    <button type="button" @click="next()" class="w-9 h-9 rounded-full flex items-center justify-center text-ink-500 dark:text-sage-300 hover:bg-cream-100 dark:hover:bg-sage-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                <!-- Cabecera de días -->
                <div class="grid grid-cols-7 gap-1 mb-1">
                    <template x-for="d in dow" :key="d">
                        <div class="text-center text-[10px] font-bold uppercase tracking-wider text-ink-400 dark:text-sage-400 py-1" x-text="d"></div>
                    </template>
                </div>

                <!-- Celdas -->
                <div class="grid grid-cols-7 gap-1">
                    <template x-for="(cell, i) in cells()" :key="i">
                        <div>
                            <template x-if="cell === null"><div class="h-12"></div></template>
                            <template x-if="cell !== null">
                                <button type="button" @click="select(cell)"
                                    class="h-12 w-full rounded-xl flex flex-col items-center justify-center gap-1 transition-colors"
                                    :class="{
                                        'bg-sage-600 text-white': selected === key(cell),
                                        'bg-cream-100 dark:bg-sage-800/60 hover:bg-cream-200 dark:hover:bg-sage-800': selected !== key(cell) && hasAny(cell),
                                        'hover:bg-cream-100 dark:hover:bg-sage-800/40': selected !== key(cell) && !hasAny(cell),
                                        'ring-2 ring-sage-400': isToday(cell) && selected !== key(cell)
                                    }">
                                    <span class="text-sm font-semibold leading-none"
                                        :class="selected === key(cell) ? 'text-white' : 'text-ink-700 dark:text-cream-100'" x-text="cell"></span>
                                    <span class="flex items-center gap-0.5 h-1.5">
                                        <template x-if="cats(cell).reservada"><span class="w-1.5 h-1.5 rounded-full bg-sage-500"></span></template>
                                        <template x-if="cats(cell).realizada"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span></template>
                                        <template x-if="cats(cell).cancelada"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span></template>
                                    </span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Leyenda -->
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-5 pt-4 border-t border-cream-300 dark:border-sage-700">
                    <span class="flex items-center gap-2 text-xs font-semibold text-ink-600 dark:text-sage-300"><span class="w-2.5 h-2.5 rounded-full bg-sage-500"></span> Programada</span>
                    <span class="flex items-center gap-2 text-xs font-semibold text-ink-600 dark:text-sage-300"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Realizada</span>
                    <span class="flex items-center gap-2 text-xs font-semibold text-ink-600 dark:text-sage-300"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Cancelada</span>
                </div>

                <!-- Detalle del día seleccionado -->
                <div x-show="selected" x-cloak class="mt-5">
                    <p class="text-xs font-bold uppercase tracking-widest text-ink-400 dark:text-sage-400 mb-2" x-text="selectedLabel()"></p>
                    <div class="space-y-2 max-h-44 overflow-y-auto">
                        <template x-for="(item, i) in selectedList()" :key="i">
                            <div class="flex items-center gap-3 rounded-xl bg-cream-50 dark:bg-sage-800/50 px-3 py-2">
                                <span class="w-2 h-2 rounded-full shrink-0" :class="{'bg-sage-500': item.cat==='reservada','bg-emerald-500': item.cat==='realizada','bg-red-500': item.cat==='cancelada'}"></span>
                                <span class="text-sm font-semibold text-ink-800 dark:text-cream-100 shrink-0" x-text="item.hora"></span>
                                <span class="text-sm text-ink-500 dark:text-sage-300 truncate" x-text="item.paciente"></span>
                                <span class="ml-auto text-[10px] font-bold uppercase tracking-wider shrink-0" :class="{'text-sage-600 dark:text-sage-300': item.cat==='reservada','text-emerald-600': item.cat==='realizada','text-red-500': item.cat==='cancelada'}" x-text="item.label"></span>
                            </div>
                        </template>
                        <template x-if="selectedList().length === 0">
                            <p class="text-sm text-ink-500 dark:text-sage-300">Sin citas este día.</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="application/json" id="doctor-cal-data">@json($calendarDays)</script>
<script>
    // Componente del calendario de citas (modal "Ver calendario")
    document.addEventListener('alpine:init', () => {
        Alpine.data('citasCalendar', () => ({
            days: JSON.parse(document.getElementById('doctor-cal-data').textContent || '{}'),
            cur: new Date(),
            selected: null,
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            dow: ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'],
            get year() { return this.cur.getFullYear(); },
            get month() { return this.cur.getMonth(); },
            prev() { this.cur = new Date(this.year, this.month - 1, 1); this.selected = null; },
            next() { this.cur = new Date(this.year, this.month + 1, 1); this.selected = null; },
            key(d) { return `${this.year}-${String(this.month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`; },
            cells() {
                const first = new Date(this.year, this.month, 1);
                const startDow = (first.getDay() + 6) % 7; // lunes = 0
                const total = new Date(this.year, this.month + 1, 0).getDate();
                const arr = [];
                for (let i = 0; i < startDow; i++) arr.push(null);
                for (let d = 1; d <= total; d++) arr.push(d);
                return arr;
            },
            entries(d) { return this.days[this.key(d)] || []; },
            hasAny(d) { return this.entries(d).length > 0; },
            cats(d) {
                const e = this.entries(d);
                return {
                    reservada: e.some(x => x.cat === 'reservada'),
                    realizada: e.some(x => x.cat === 'realizada'),
                    cancelada: e.some(x => x.cat === 'cancelada'),
                };
            },
            isToday(d) {
                const t = new Date();
                return d === t.getDate() && this.month === t.getMonth() && this.year === t.getFullYear();
            },
            select(d) { const k = this.key(d); this.selected = (this.selected === k ? null : k); },
            selectedList() { return this.selected ? (this.days[this.selected] || []) : []; },
            selectedLabel() {
                if (!this.selected) return '';
                const [y, m, dd] = this.selected.split('-').map(Number);
                return `${dd} de ${this.monthNames[m - 1]} de ${y}`;
            },
        }));
    });

    // Contador de tiempo libre antes de la próxima cita
    document.querySelectorAll('[data-countdown]').forEach(function (el) {
        const start = new Date(el.dataset.start).getTime();
        const valueEl = el.querySelector('[data-countdown-value]');
        const labelEl = el.querySelector('[data-countdown-label]');

        function tick() {
            const diff = start - Date.now();
            if (diff <= 0) {
                valueEl.textContent = 'En curso';
                if (labelEl) labelEl.textContent = 'tu sesión ya debería estar comenzando';
                return;
            }
            const totalMin = Math.floor(diff / 60000);
            const h = Math.floor(totalMin / 60);
            const m = totalMin % 60;
            valueEl.textContent = h > 0 ? `${h}h ${m}m` : `${m} min`;
        }
        tick();
        setInterval(tick, 30000);
    });

    // Estado del modal de nota rápida (prefill según la sesión elegida)
    function quickResumen() {
        return {
            citaId: '{{ $todaySessions->first()->id ?? '' }}',
            resumen: @js($todaySessions->first()->resumen_sesion ?? ''),
            compartido: {{ ($todaySessions->first()->resumen_compartido ?? false) ? 'true' : 'false' }},
            get action() { return `{{ url('doctor/sesion') }}/${this.citaId}/resumen`; },
            sync() {
                const opt = document.querySelector(`select option[value="${this.citaId}"]`);
                if (!opt) return;
                this.resumen = opt.dataset.resumen || '';
                this.compartido = opt.dataset.compartido === '1';
            }
        };
    }
</script>
@endpush
