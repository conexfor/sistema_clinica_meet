@extends('components.layouts.doctor')

@php $active = 'patients'; @endphp

@section('header_title', 'Historial Clínico')

@section('content')
<div class="max-w-6xl mx-auto pb-10" x-data="{ modal: null }">

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-6">
        <div class="flex items-center gap-4 md:gap-6">
            <a href="{{ route('doctor.patients.index') }}" class="w-12 h-12 rounded-2xl bg-white border border-cream-300 text-ink-500 flex items-center justify-center hover:bg-cream-100 transition-colors shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="font-serif text-2xl md:text-4xl text-sage-900 dark:text-cream-100">{{ $paciente->usuario->nombre }}</h1>
                <p class="text-xs md:text-sm text-ink-500 font-medium mt-1">Expediente #PS-{{ date('Y') }}-{{ str_pad($paciente->id, 3, '0', STR_PAD_LEFT) }}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <button type="button" @click="modal = 'crisis'" class="btn bg-white border border-red-200 text-red-600 hover:bg-red-50 px-6 py-3 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Reportar crisis
            </button>
            <button type="button" @click="modal = 'nota'" class="btn-primary px-8 py-3 text-sm">Nueva nota</button>
        </div>
    </div>

    <!-- Banner de crisis activa -->
    @if($crisisActiva)
    <div class="mb-8 rounded-3xl border border-red-200 bg-red-50 p-5 md:p-6 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-red-700">Paciente con alerta de crisis</p>
            <p class="text-sm text-red-600/90 mt-0.5">{{ $crisisActiva->contenido }}</p>
            <p class="text-[10px] font-bold text-red-500/70 uppercase tracking-widest mt-1">Reportada {{ $crisisActiva->created_at->diffForHumans() }}</p>
        </div>
        <form action="{{ route('doctor.patients.notes.destroy', $crisisActiva) }}" method="POST" data-confirm="¿Marcar la crisis como resuelta y quitar la alerta?" data-confirm-ok="Sí, resolver">
            @csrf @method('DELETE')
            <button type="submit" class="btn bg-white border border-red-200 text-red-600 hover:bg-red-100 px-4 py-2 text-xs whitespace-nowrap">Marcar resuelta</button>
        </form>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Resumen del paciente -->
        <div class="lg:col-span-1 space-y-6">
            <div class="surface p-6 md:p-8">
                <div class="flex flex-col items-center text-center mb-8">
                    <div class="w-24 h-24 rounded-full bg-sage-100 text-sage-700 font-bold text-3xl flex items-center justify-center border-4 border-white shadow-soft ring-1 ring-cream-300 mb-4 uppercase">
                        {{ substr($paciente->usuario->nombre, 0, 2) }}
                    </div>
                    <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100">{{ $paciente->usuario->nombre }}</h3>
                    <p class="text-xs md:text-sm font-bold text-sand-600 uppercase tracking-widest mt-1">Consulta general</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500 mb-2">Información personal</p>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm font-bold">
                                <span class="text-ink-500">Email</span>
                                <span class="text-ink-900 dark:text-cream-100 text-xs">{{ $paciente->usuario->email }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm font-bold">
                                <span class="text-ink-500">Estado</span>
                                <span class="px-2 py-0.5 bg-sage-100 text-sage-700 rounded-full text-[10px]">Activo</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-6 border-t border-cream-300">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500 mb-2">Resumen clínico</p>
                        <p class="text-sm font-medium text-ink-600 leading-relaxed italic">"Información clínica base del paciente."</p>
                    </div>
                </div>
            </div>

            <!-- Estado de ánimo por sesión -->
            @php
                $emojiAnimo = [1 => '😟', 2 => '😕', 3 => '😐', 4 => '🙂', 5 => '😄'];
                $labelAnimo = [1 => 'Muy bajo', 2 => 'Bajo', 3 => 'Neutral', 4 => 'Bien', 5 => 'Muy bien'];
            @endphp
            <div class="surface p-6 md:p-8">
                <div class="flex items-center justify-between mb-5">
                    <h4 class="font-serif text-lg text-sage-900 dark:text-cream-100">Estado de ánimo</h4>
                    <span class="text-[10px] font-bold text-ink-400 uppercase tracking-widest">Inicio → Fin</span>
                </div>

                @forelse($animoSesiones->reverse() as $sesion)
                    @php
                        $ini = $sesion->animo_inicial; $fin = $sesion->animo_final;
                        $delta = ($ini !== null && $fin !== null) ? $fin - $ini : null;
                    @endphp
                    <div class="flex items-center gap-3 py-3 {{ !$loop->last ? 'border-b border-cream-200 dark:border-sage-800' : '' }}">
                        <span class="text-[11px] font-bold text-ink-500 w-16 shrink-0">
                            {{ optional($sesion->disponibilidad)->fecha?->locale('es')->isoFormat('D MMM') ?? '—' }}
                        </span>
                        <div class="flex items-center gap-1.5 flex-1">
                            <span class="text-2xl" title="Inicio: {{ $ini ? $labelAnimo[$ini] : 'sin registro' }}">{{ $ini ? $emojiAnimo[$ini] : '⚪' }}</span>
                            <svg class="w-4 h-4 text-ink-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 5l7 7-7 7"/></svg>
                            <span class="text-2xl" title="Fin: {{ $fin ? $labelAnimo[$fin] : 'sin registro' }}">{{ $fin ? $emojiAnimo[$fin] : '⚪' }}</span>
                        </div>
                        @if($delta !== null)
                            <span class="text-[10px] font-bold px-2 py-1 rounded-full shrink-0 {{ $delta > 0 ? 'bg-sage-100 text-sage-700' : ($delta < 0 ? 'bg-sand-100 text-sand-700' : 'bg-cream-200 text-ink-600') }}">
                                {{ $delta > 0 ? '▲ Mejoró' : ($delta < 0 ? '▼ Bajó' : '= Igual') }}
                            </span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-ink-500 italic text-center py-6">Aún sin registros de ánimo en las sesiones.</p>
                @endforelse

                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-5 pt-4 border-t border-cream-200 dark:border-sage-800">
                    @foreach($emojiAnimo as $v => $e)
                        <span class="text-[10px] font-semibold text-ink-500 flex items-center gap-1">{{ $e }} {{ $labelAnimo[$v] }}</span>
                    @endforeach
                </div>
            </div>

            <!-- Notas clínicas -->
            <div class="surface p-6 md:p-8">
                <div class="flex items-center justify-between mb-5">
                    <h4 class="font-serif text-lg text-sage-900 dark:text-cream-100">Notas clínicas</h4>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-cream-200 text-ink-600">{{ $notas->count() }}</span>
                </div>
                <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    @forelse($notas as $nota)
                    <div class="p-4 rounded-2xl border {{ $nota->esCrisis() ? 'border-red-200 bg-red-50' : 'border-cream-300 bg-cream-100/60' }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-sm font-bold {{ $nota->esCrisis() ? 'text-red-700' : 'text-ink-900' }}">
                                    @if($nota->esCrisis())<span class="mr-1">⚠️</span>@endif{{ $nota->titulo }}
                                </p>
                                <p class="text-xs text-ink-600 mt-1 leading-relaxed whitespace-pre-line">{{ $nota->contenido }}</p>
                                <p class="text-[10px] font-bold text-ink-400 uppercase tracking-widest mt-2">{{ $nota->created_at->diffForHumans() }}</p>
                            </div>
                            <form action="{{ route('doctor.patients.notes.destroy', $nota) }}" method="POST" data-confirm="¿Eliminar esta nota?" data-confirm-ok="Eliminar" data-confirm-variant="danger">
                                @csrf @method('DELETE')
                                <button type="submit" aria-label="Eliminar nota" class="w-8 h-8 rounded-lg text-ink-400 hover:text-sand-700 hover:bg-white flex items-center justify-center transition-colors shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-ink-500 italic text-center py-4">Sin notas clínicas todavía.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Historial de sesiones -->
        <div class="lg:col-span-2 space-y-8">
            <div class="surface p-6 md:p-10">
                <h3 class="font-serif text-xl md:text-2xl text-sage-900 dark:text-cream-100 mb-8">Historial de sesiones</h3>

                <div class="relative space-y-10 before:content-[''] before:absolute before:left-[1.1rem] md:before:left-[1.35rem] before:top-2 before:bottom-2 before:w-px md:before:w-0.5 before:bg-cream-300">
                    @forelse($citas as $cita)
                    @php
                        $tareasSesion = $cita->tareas ?? collect();
                        $tieneDatos = filled($cita->resumen_sesion) || $tareasSesion->isNotEmpty() || $cita->animo_inicial !== null || $cita->animo_final !== null;
                    @endphp
                    <div class="relative pl-10 md:pl-12" x-data="{ open: false }">
                        <div class="absolute left-0 top-1 w-9 h-9 md:w-11 md:h-11 bg-sage-600 rounded-2xl flex items-center justify-center text-white shadow-soft z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 10l5.553-2.776A1 1 0 0122 8.143v7.714a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="bg-cream-100/60 dark:bg-sage-900/50 p-6 md:p-8 rounded-3xl border border-transparent hover:border-cream-300 transition-all">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2">
                                <span class="text-[10px] font-bold text-sand-600 uppercase tracking-[0.2em]">{{ $cita->disponibilidad->fecha->format('d M, Y') }} · {{ \Carbon\Carbon::parse($cita->disponibilidad->hora_inicio)->format('h:i A') }}</span>
                                <span class="inline-block self-start px-3 py-1 {{ $cita->estado === \App\Models\Cita::STATUS_COMPLETED ? 'bg-sage-100 text-sage-700' : ($cita->estado === \App\Models\Cita::STATUS_CANCELLED ? 'bg-slate-200 text-slate-700' : 'bg-sand-100 text-sand-700') }} rounded-full text-[10px] font-bold uppercase tracking-widest">{{ $cita->status_badge['label'] }}</span>
                            </div>
                            <h4 class="font-serif text-lg md:text-xl text-sage-900 dark:text-cream-100 mb-2">Sesión clínica</h4>
                            <p class="text-xs md:text-sm text-ink-500 font-medium leading-relaxed">
                                Registro de la sesión del {{ $cita->disponibilidad->fecha->locale('es')->translatedFormat('l d \d\e F') }}.
                            </p>

                            @if($cita->estado === \App\Models\Cita::STATUS_CANCELLED)
                                <div class="mt-3 rounded-2xl bg-slate-100 border border-slate-200 p-3">
                                    <p class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">Cancelada por {{ $cita->cancelado_por }}</p>
                                    @if($cita->motivo_cancelacion)
                                        <p class="text-xs text-ink-600 italic mt-1">“{{ $cita->motivo_cancelacion }}”</p>
                                    @endif
                                </div>
                            @endif

                            @if($tieneDatos)
                            <button type="button" @click="open = !open" class="mt-4 inline-flex items-center gap-2 text-[11px] font-bold text-sage-700 dark:text-sage-300 bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 px-4 py-2 rounded-full hover:border-sage-300 transition-colors">
                                <span x-text="open ? 'Ocultar datos de la sesión' : 'Ver datos de la sesión'"></span>
                                <svg class="w-3.5 h-3.5 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="open" x-transition x-cloak class="mt-4 space-y-4">
                                {{-- Resumen --}}
                                @if(filled($cita->resumen_sesion))
                                <div class="rounded-2xl bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 p-4">
                                    <p class="text-[10px] font-bold text-sage-600 uppercase tracking-widest mb-1.5 flex items-center gap-2">
                                        Resumen de la sesión
                                        @if($cita->resumen_compartido)<span class="text-[9px] px-2 py-0.5 rounded-full bg-sage-100 text-sage-700">Compartido</span>@else<span class="text-[9px] px-2 py-0.5 rounded-full bg-sand-100 text-sand-700">Privado</span>@endif
                                    </p>
                                    <p class="text-sm text-ink-700 dark:text-sage-200 leading-relaxed whitespace-pre-line">{{ $cita->resumen_sesion }}</p>
                                </div>
                                @endif

                                {{-- Ánimo --}}
                                @if($cita->animo_inicial !== null || $cita->animo_final !== null)
                                <div class="rounded-2xl bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 p-4 flex items-center gap-3">
                                    <p class="text-[10px] font-bold text-sage-600 uppercase tracking-widest">Ánimo</p>
                                    <span class="text-2xl">{{ $cita->animo_inicial ? ($emojiAnimo[$cita->animo_inicial] ?? '') : '⚪' }}</span>
                                    <svg class="w-4 h-4 text-ink-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 5l7 7-7 7"/></svg>
                                    <span class="text-2xl">{{ $cita->animo_final ? ($emojiAnimo[$cita->animo_final] ?? '') : '⚪' }}</span>
                                </div>
                                @endif

                                {{-- Tareas --}}
                                @if($tareasSesion->isNotEmpty())
                                <div class="rounded-2xl bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 p-4">
                                    <p class="text-[10px] font-bold text-sage-600 uppercase tracking-widest mb-2">Tareas asignadas</p>
                                    <div class="space-y-1.5">
                                        @foreach($tareasSesion as $t)
                                        <div class="flex items-start gap-2 text-sm">
                                            <span class="mt-0.5 w-4 h-4 rounded flex items-center justify-center shrink-0 {{ $t->completada ? 'bg-sage-500 text-white' : 'border-2 border-cream-400' }}">
                                                @if($t->completada)<svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                                            </span>
                                            <span class="{{ $t->completada ? 'line-through text-ink-400' : 'text-ink-700 dark:text-sage-200' }}">{{ $t->titulo }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="flex flex-col items-center py-12 text-center">
                        <div class="w-12 h-12 bg-cream-100 rounded-2xl flex items-center justify-center text-2xl mb-4">📂</div>
                        <p class="text-ink-500 font-bold text-sm">Sin historial de citas.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Nueva nota clínica -->
    <div x-show="modal === 'nota'" x-cloak class="fixed inset-0 z-[100]">
        <div class="absolute inset-0 bg-sage-950/60 backdrop-blur-sm" @click="modal = null"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-4">
            <div class="surface p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-serif text-2xl text-sage-900">Nueva nota clínica</h3>
                    <button type="button" @click="modal = null" class="text-ink-400 hover:text-ink-700"><svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form action="{{ route('doctor.patients.notes.store', $paciente->usuario_id) }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="nota_titulo" class="label">Título</label>
                        <input type="text" id="nota_titulo" name="titulo" required maxlength="255" placeholder="Ej. Evolución del cuadro de ansiedad" class="field">
                    </div>
                    <div>
                        <label for="nota_contenido" class="label">Contenido</label>
                        <textarea id="nota_contenido" name="contenido" rows="5" required maxlength="5000" placeholder="Observaciones clínicas de este paciente…" class="field resize-none"></textarea>
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="button" @click="modal = null" class="flex-1 btn bg-cream-200 text-ink-700 hover:bg-cream-300 py-3">Cancelar</button>
                        <button type="submit" class="flex-1 btn-primary py-3">Guardar nota</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Reportar crisis -->
    <div x-show="modal === 'crisis'" x-cloak class="fixed inset-0 z-[100]">
        <div class="absolute inset-0 bg-sage-950/60 backdrop-blur-sm" @click="modal = null"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-4">
            <div class="surface p-8 border-t-4 border-red-400">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-serif text-2xl text-red-700">Reportar crisis</h3>
                    <button type="button" @click="modal = null" class="text-ink-400 hover:text-ink-700"><svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <p class="text-sm text-ink-500 mb-6">Registra el evento de crisis. Quedará como alerta destacada en la ficha del paciente.</p>
                <form action="{{ route('doctor.patients.crisis', $paciente->usuario_id) }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="crisis_contenido" class="label">¿Qué ocurrió?</label>
                        <textarea id="crisis_contenido" name="contenido" rows="5" required maxlength="5000" placeholder="Describe la situación de crisis y las acciones tomadas…" class="field resize-none"></textarea>
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="button" @click="modal = null" class="flex-1 btn bg-cream-200 text-ink-700 hover:bg-cream-300 py-3">Cancelar</button>
                        <button type="submit" class="flex-1 btn bg-red-600 text-white hover:bg-red-700 py-3">Registrar crisis</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
