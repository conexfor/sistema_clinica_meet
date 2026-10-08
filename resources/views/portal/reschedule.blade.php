@extends(auth()->check() && auth()->user()->isReceptionist() ? 'components.layouts.receptionist' : 'components.layouts.portal')

@php $active = 'index'; @endphp

@section('content')
<div class="max-w-3xl mx-auto pb-16">

    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('portal.index') }}" class="w-11 h-11 rounded-2xl bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 text-ink-500 flex items-center justify-center hover:bg-sage-50 dark:hover:bg-sage-700 transition-colors shadow-soft shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="font-serif text-3xl text-sage-900 mb-1">Solicitar cambio de horario</h1>
            <p class="text-sm text-ink-500 font-medium">
                Elige un nuevo horario disponible con
                <span class="font-bold text-sage-700">Ps. {{ $cita->especialista->usuario->nombre }}</span>.
                Tu especialista confirmará el cambio.
            </p>
        </div>
    </div>

    <!-- Cita actual -->
    <div class="surface p-6 mb-6 flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-cream-200 text-ink-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-ink-400">Horario actual</p>
            <p class="text-sm font-bold text-ink-900">
                {{ $cita->disponibilidad->fecha->locale('es')->isoFormat('dddd D [de] MMMM') }},
                {{ \Carbon\Carbon::parse($cita->disponibilidad->hora_inicio)->format('h:i A') }}
            </p>
        </div>
    </div>

    <!-- Selector de fecha -->
    <div class="surface p-6 mb-6">
        <label for="fecha-picker" class="label">Fecha deseada</label>
        <input type="date" id="fecha-picker" value="{{ $fecha }}" min="{{ now()->toDateString() }}"
               onchange="window.location.href='{{ route('portal.reschedule.create', $cita) }}?fecha=' + this.value"
               class="field max-w-xs">
        @if(!empty($availableDates))
            <p class="text-[11px] text-ink-500 font-medium mt-3">
                Próximas fechas con cupos:
                @foreach(array_slice($availableDates, 0, 6) as $d)
                    <a href="{{ route('portal.reschedule.create', $cita) }}?fecha={{ $d }}" class="inline-block px-2 py-0.5 rounded-lg bg-sage-50 text-sage-700 font-bold hover:bg-sage-100 transition-colors mr-1 mb-1">{{ \Carbon\Carbon::parse($d)->locale('es')->isoFormat('D MMM') }}</a>
                @endforeach
            </p>
        @endif
    </div>

    <form method="POST" action="{{ $formAction ?? route('portal.reschedule.store', $cita) }}" x-data="{ selected: '' }">
        @csrf
        <input type="hidden" name="fecha_solicitada" value="{{ $fecha }}">
        <input type="hidden" name="hora_solicitada" x-model="selected">

        <!-- Slots -->
        <div class="surface p-6 md:p-8 mb-6">
            <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-ink-500 mb-6">Horarios disponibles</h3>

            @php $hayCupos = collect($availability)->flatten(1)->isNotEmpty(); @endphp
            @if($hayCupos)
                <div class="space-y-8">
                    @foreach(['morning' => ['Mañana','🌅'], 'afternoon' => ['Tarde','☀️'], 'evening' => ['Noche','🌙']] as $key => $meta)
                        @if(isset($availability[$key]) && $availability[$key]->count() > 0)
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-xl">{{ $meta[1] }}</span>
                                <h4 class="text-xs font-bold uppercase tracking-[0.25em] text-ink-500">{{ $meta[0] }}</h4>
                                <div class="h-px bg-cream-300 flex-1"></div>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" role="radiogroup" aria-label="Horarios de la {{ strtolower($meta[0]) }}">
                                @foreach($availability[$key] as $slot)
                                <label class="cursor-pointer">
                                    <input type="radio" name="slot_id" value="{{ $slot['id'] }}" class="peer sr-only"
                                           x-on:change="selected = '{{ $slot['start'] }}'" required>
                                    <div class="p-4 rounded-2xl border border-cream-300 bg-cream-100/50 text-center transition-all hover:border-sage-300 peer-checked:bg-gradient-to-br peer-checked:from-sage-500 peer-checked:to-sage-700 peer-checked:border-sage-600 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-sage-500 peer-focus-visible:ring-offset-2">
                                        <p class="text-sm font-bold text-ink-900 peer-checked:text-white">{{ $slot['display'] }}</p>
                                        <p class="text-[10px] font-semibold text-ink-500 peer-checked:text-sage-100 mt-0.5">{{ $slot['duration'] }} min</p>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="py-12 text-center">
                    <div class="w-16 h-16 bg-cream-100 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">🗓️</div>
                    <p class="font-serif text-lg text-sage-900">Sin horarios ese día</p>
                    <p class="text-ink-500 text-sm mt-1">Prueba con otra fecha usando el selector de arriba.</p>
                </div>
            @endif
        </div>

        @if($hayCupos)
        <div class="surface p-6 md:p-8 mb-6">
            <label for="motivo" class="label">Motivo (opcional)</label>
            <textarea id="motivo" name="motivo" rows="3" maxlength="1000" placeholder="Cuéntale a tu especialista por qué necesitas el cambio" class="field resize-none">{{ old('motivo') }}</textarea>
        </div>

        @if(auth()->check() && auth()->user()->isReceptionist())
            <div class="surface p-6 md:p-8 mb-6">
                <label class="label">¿Quién solicita el cambio?</label>
                <select name="solicitante" class="field">
                    <option value="paciente" {{ (old('solicitante') ?? ($pending->solicitante ?? 'paciente')) === 'paciente' ? 'selected' : '' }}>Paciente</option>
                    <option value="especialista" {{ (old('solicitante') ?? ($pending->solicitante ?? 'paciente')) === 'especialista' ? 'selected' : '' }}>Especialista</option>
                </select>
            </div>
        @endif

        <button type="submit" class="btn-primary w-full py-4 text-xs uppercase tracking-[0.2em]">
            Enviar solicitud de cambio
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/><path stroke-linecap="round" stroke-linejoin="round" d="m12 5 7 7-7 7"/></svg>
        </button>
        @endif
    </form>
</div>
@endsection
