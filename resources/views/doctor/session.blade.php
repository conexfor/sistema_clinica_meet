@extends('components.layouts.doctor')

@php $active = 'dashboard'; @endphp

@section('content')
<div class="max-w-6xl mx-auto pb-16" x-data="{ tab: 'tareas' }">

    <!-- Volver -->
    <a href="{{ route('doctor.dashboard') }}" class="inline-flex items-center gap-2 text-[11px] font-bold text-ink-500 uppercase tracking-widest hover:text-sage-600 transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Volver al panel
    </a>

    <!-- Cabecera de la sesión -->
    <div class="surface overflow-hidden mb-8">
        <div class="p-8 md:p-10 text-white relative bg-gradient-to-br from-sage-700 to-sage-900">
            <div class="absolute top-0 right-0 w-64 h-64 bg-sand-300/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 rounded-3xl bg-white/10 border border-white/20 flex items-center justify-center text-2xl font-bold shrink-0">
                        {{ strtoupper(substr($cita->paciente->nombre ?? 'P', 0, 1)) }}
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-sand-300">Espacio de sesión</span>
                        <h1 class="font-serif text-2xl md:text-3xl leading-tight">{{ $cita->paciente->nombre }}</h1>
                        <p class="text-cream-100/80 text-sm mt-1">
                            @if($cita->disponibilidad)
                                {{ $cita->disponibilidad->fecha->locale('es')->isoFormat('dddd D [de] MMMM') }} ·
                                {{ \Carbon\Carbon::parse($cita->disponibilidad->hora_inicio)->format('h:i A') }}
                            @endif
                            · {{ $cita->modalidad_id == \App\Models\Modalidad::VIRTUAL ? 'Virtual' : 'Presencial' }}
                        </p>
                    </div>
                </div>

                @php $citaActiva = in_array($cita->estado, [\App\Models\Cita::STATUS_RESERVADA, \App\Models\Cita::STATUS_ATTENTION]); @endphp
                @php $meetDisponible = $cita->disponibilidad && $cita->start_at->copy()->subMinutes(30)->isPast(); @endphp
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                    @if($cita->modalidad_id == \App\Models\Modalidad::VIRTUAL && $citaActiva)
                        @if($meetDisponible)
                            <a href="{{ route('doctor.join_meeting', $cita) }}" target="_blank" rel="noopener"
                               class="btn bg-white text-sage-700 hover:bg-cream-100 px-6 py-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Abrir Google Meet
                            </a>
                        @else
                            <span class="btn bg-white/10 text-cream-100/70 border border-white/20 px-6 py-3 cursor-not-allowed" title="El enlace se habilita 30 minutos antes del inicio">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Meet disponible 30 min antes
                            </span>
                        @endif
                    @endif

                    @if($citaActiva)
                        <form action="{{ route('doctor.appointments.update_status', $cita) }}" method="POST" data-confirm="¿Marcar que el paciente NO asistió? Quedará registrado como inasistencia." data-confirm-ok="Marcar inasistencia">
                            @csrf @method('PATCH')
                            <input type="hidden" name="estado" value="no_asistio">
                            <button type="submit" class="btn bg-white text-sand-700 border border-sand-300 hover:bg-sand-50 px-6 py-3 w-full">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                No asistió
                            </button>
                        </form>
                        <form action="{{ route('doctor.appointments.update_status', $cita) }}" method="POST" data-confirm="¿Finalizar esta sesión? Se marcará como realizada." data-confirm-ok="Finalizar sesión">
                            @csrf @method('PATCH')
                            <input type="hidden" name="estado" value="realizada">
                            <button type="submit" class="btn bg-sand-400 text-white hover:bg-sand-500 px-6 py-3 w-full">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Finalizar sesión
                            </button>
                        </form>
                    @elseif($cita->estado === \App\Models\Cita::STATUS_NO_SHOW)
                        <span class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-full bg-white/15 border border-white/20 text-sm font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            Paciente no asistió
                        </span>
                    @elseif($cita->estado === \App\Models\Cita::STATUS_COMPLETED)
                        <span class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-full bg-white/15 border border-white/20 text-sm font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Sesión finalizada
                        </span>
                    @elseif($cita->estado === \App\Models\Cita::STATUS_RESCHEDULED)
                        <span class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-full bg-white/15 border border-white/20 text-sm font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Cita reprogramada
                        </span>
                    @elseif($cita->estado === \App\Models\Cita::STATUS_CANCELLED)
                        <span class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-full bg-white/15 border border-white/20 text-sm font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Cita cancelada
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pestañas -->
        <div class="flex border-b border-cream-300 px-2 sm:px-4 overflow-x-auto">
            <button @click="tab = 'tareas'" :class="tab === 'tareas' ? 'text-sage-700 border-sage-600' : 'text-ink-500 border-transparent hover:text-sage-600'"
                    class="px-5 py-4 text-sm font-bold border-b-2 transition-colors whitespace-nowrap">
                Tareas del paciente
                <span class="ml-1 text-[11px] px-2 py-0.5 rounded-full bg-sage-100 text-sage-700">{{ $tareas->count() }}</span>
            </button>
            <button @click="tab = 'resumen'" :class="tab === 'resumen' ? 'text-sage-700 border-sage-600' : 'text-ink-500 border-transparent hover:text-sage-600'"
                    class="px-5 py-4 text-sm font-bold border-b-2 transition-colors whitespace-nowrap">
                Resumen de la sesión
                @if(filled($cita->resumen_sesion))<span class="ml-1 w-1.5 h-1.5 inline-block rounded-full bg-sage-500 align-middle"></span>@endif
            </button>
            <button @click="tab = 'animo'" :class="tab === 'animo' ? 'text-sage-700 border-sage-600' : 'text-ink-500 border-transparent hover:text-sage-600'"
                    class="px-5 py-4 text-sm font-bold border-b-2 transition-colors whitespace-nowrap">
                Estado de ánimo
            </button>
        </div>
    </div>

    <!-- Panel: Tareas -->
    <div x-show="tab === 'tareas'" x-cloak class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <!-- Crear tarea -->
        <div class="lg:col-span-2">
            <div class="surface p-6 md:p-8 lg:sticky lg:top-8">
                <h2 class="font-serif text-xl text-sage-900 mb-1">Asignar tarea</h2>
                <p class="text-ink-500 text-sm mb-6">El paciente la verá en su portal y podrá marcarla como completada.</p>
                <form action="{{ route('doctor.tasks.store', $cita) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="titulo" class="label">Título</label>
                        <input type="text" id="titulo" name="titulo" required maxlength="255" placeholder="Ej. Registro diario de emociones" class="field @error('titulo') !border-sand-400 @enderror">
                        @error('titulo') <p class="text-sand-700 text-[11px] font-bold mt-2 ml-1 italic">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="descripcion" class="label">Detalle (opcional)</label>
                        <textarea id="descripcion" name="descripcion" rows="4" maxlength="2000" placeholder="Instrucciones o contexto para el paciente" class="field resize-none">{{ old('descripcion') }}</textarea>
                    </div>
                    <button type="submit" class="btn-primary w-full py-4 text-xs uppercase tracking-[0.15em]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Añadir tarea
                    </button>
                </form>
            </div>
        </div>

        <!-- Lista de tareas -->
        <div class="lg:col-span-3 space-y-4">
            @forelse($tareas as $tarea)
                <div class="surface p-6 flex items-start gap-4 {{ $tarea->completada ? 'opacity-70' : '' }}">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 {{ $tarea->completada ? 'bg-sage-100 text-sage-600' : 'bg-cream-100 text-ink-400' }}">
                        @if($tarea->completada)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-ink-900 {{ $tarea->completada ? 'line-through text-ink-500' : '' }}">{{ $tarea->titulo }}</h3>
                        @if($tarea->descripcion)
                            <p class="text-sm text-ink-500 mt-1 leading-relaxed">{{ $tarea->descripcion }}</p>
                        @endif
                        <p class="text-[10px] font-bold uppercase tracking-widest mt-3 {{ $tarea->completada ? 'text-sage-600' : 'text-ink-400' }}">
                            {{ $tarea->completada ? 'Completada por el paciente' . ($tarea->completada_at ? ' · ' . $tarea->completada_at->diffForHumans() : '') : 'Pendiente' }}
                        </p>
                    </div>
                    <form action="{{ route('doctor.tasks.destroy', $tarea) }}" method="POST" data-confirm="¿Eliminar esta tarea?" data-confirm-ok="Eliminar" data-confirm-variant="danger">
                        @csrf @method('DELETE')
                        <button type="submit" aria-label="Eliminar tarea" class="w-9 h-9 rounded-xl text-ink-400 hover:text-sand-700 hover:bg-sand-50 flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            @empty
                <div class="surface p-12 text-center">
                    <div class="w-16 h-16 bg-cream-100 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">📝</div>
                    <h3 class="font-serif text-lg text-sage-900">Aún no hay tareas</h3>
                    <p class="text-ink-500 text-sm mt-1">Añade la primera tarea para acompañar el proceso del paciente.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Panel: Resumen -->
    <div x-show="tab === 'resumen'" x-cloak>
        <div class="surface p-6 md:p-10 max-w-3xl">
            <div class="flex flex-wrap items-center gap-3 mb-1">
                <h2 class="font-serif text-xl text-sage-900">Resumen de la sesión</h2>
                @if($cita->resumen_compartido && filled($cita->resumen_sesion))
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-sage-700 bg-sage-100 px-3 py-1 rounded-full uppercase tracking-widest">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Compartido con el paciente
                    </span>
                @elseif(filled($cita->resumen_sesion))
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-sand-700 bg-sand-100 px-3 py-1 rounded-full uppercase tracking-widest">Privado (no compartido)</span>
                @endif
            </div>
            <p class="text-ink-500 text-sm mb-6">Redacta lo trabajado en la sesión. Si activas “compartir”, el paciente podrá leerlo en su línea de tiempo.</p>
            <form action="{{ route('doctor.session.resumen', $cita) }}" method="POST" class="space-y-6">
                @csrf @method('PUT')
                <div>
                    <label for="resumen_sesion" class="label">Notas de la sesión</label>
                    <textarea id="resumen_sesion" name="resumen_sesion" rows="8" maxlength="5000" placeholder="Ej. Trabajamos en la reestructuración cognitiva sobre el miedo al fracaso…" class="field resize-none">{{ old('resumen_sesion', $cita->resumen_sesion) }}</textarea>
                </div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="resumen_compartido" value="1" @checked(old('resumen_compartido', $cita->resumen_compartido)) class="peer sr-only">
                    <span class="w-11 h-6 rounded-full bg-cream-300 peer-checked:bg-sage-500 relative transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
                    <span class="text-sm font-semibold text-ink-700">Compartir este resumen con el paciente</span>
                </label>
                <div class="pt-2">
                    <button type="submit" class="btn-primary px-10 py-4 text-xs uppercase tracking-[0.15em]">Guardar resumen</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Panel: Estado de ánimo -->
    <div x-show="tab === 'animo'" x-cloak>
        <div class="surface p-6 md:p-10 max-w-3xl">
            <h2 class="font-serif text-xl text-sage-900 mb-1">Estado de ánimo del paciente</h2>
            <p class="text-ink-500 text-sm mb-6">Registra cómo llegó y cómo terminó el paciente (1 = muy bajo, 5 = muy bien). El paciente también puede autoevaluarse desde su portal.</p>
            @php $animos = [1 => '😟', 2 => '😕', 3 => '😐', 4 => '🙂', 5 => '😄']; @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                @foreach(['inicial' => 'Al inicio', 'final' => 'Al final'] as $momento => $label)
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-ink-500 mb-3">{{ $label }}</p>
                    <form action="{{ route('doctor.session.animo', $cita) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="momento" value="{{ $momento }}">
                        <div class="flex gap-2">
                            @foreach($animos as $val => $emoji)
                                <label class="cursor-pointer">
                                    <input type="radio" name="valor" value="{{ $val }}" class="peer sr-only" onchange="this.form.submit()" @checked($cita->{'animo_'.$momento} == $val)>
                                    <span class="w-11 h-11 flex items-center justify-center text-xl rounded-2xl border border-cream-300 bg-cream-100 peer-checked:border-sage-500 peer-checked:bg-sage-50 peer-checked:ring-2 peer-checked:ring-sage-500/30 hover:border-sage-300 transition-all">{{ $emoji }}</span>
                                </label>
                            @endforeach
                        </div>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
