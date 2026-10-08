@extends(auth()->check() && auth()->user()->isReceptionist() ? 'components.layouts.receptionist' : 'components.layouts.app')

@section('content')
<div class="relative min-h-screen pb-20">
    <div class="max-w-5xl mx-auto pt-6 px-2 sm:px-6">
        @include('booking.partials.steps', ['current' => 1])

        <div class="mb-12 max-w-2xl">
            <span class="chip-sage mb-5">Paso 1 · Servicio</span>
            <h1 class="font-serif text-3xl md:text-4xl lg:text-5xl text-sage-900 dark:text-cream-100 mt-4 mb-3 leading-tight">
                ¿Qué servicio necesitas <span class="italic text-sand-600">hoy</span>?
            </h1>
            <p class="text-ink-500 dark:text-sage-300 text-base md:text-lg">Elige la atención y la modalidad que mejor se adapten a ti.</p>
        </div>

        <form action="{{ route('booking.specialist') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" role="radiogroup" aria-label="Servicios disponibles">
                @foreach($servicios as $servicio)
                @php
                    $cat = strtolower($servicio->categoria ?? '');
                    $nombre = strtolower($servicio->nombre);
                    $servicioIcon = match(true) {
                        str_contains($nombre, 'medita')                             => '🌸',
                        $cat === 'yoga' || str_contains($nombre, 'yoga')            => '🧘',
                        $cat === 'psicologia' || str_contains($nombre, 'psicolog')  => '🧠',
                        $cat === 'terapia' || str_contains($nombre, 'terapia')      => '💬',
                        $cat === 'masajes' || str_contains($nombre, 'masaje')       => '💆',
                        default                                                      => '🌿',
                    };
                @endphp
                <label class="relative group cursor-pointer block h-full">
                    <input type="radio" name="servicio_id" value="{{ $servicio->id }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }}>
                    <div class="surface surface-hover h-full p-6 md:p-7 flex flex-col transition-all duration-300 peer-checked:border-sage-500 peer-checked:ring-2 peer-checked:ring-sage-500/25 peer-checked:bg-sage-50/60 dark:peer-checked:bg-sage-900/50 peer-focus-visible:ring-2 peer-focus-visible:ring-sage-500 peer-focus-visible:ring-offset-2 group-hover:border-sage-300">

                        {{-- Cabecera: ícono + estado de selección --}}
                        <div class="flex items-start justify-between mb-5">
                            <span class="w-12 h-12 rounded-2xl bg-sage-100 dark:bg-sage-800 flex items-center justify-center text-2xl group-has-[>input:checked]:bg-sage-500 transition-colors" aria-hidden="true">
                                {{ $servicioIcon }}
                            </span>
                            <span class="w-6 h-6 rounded-full bg-sage-500 text-white flex items-center justify-center opacity-0 scale-75 group-has-[>input:checked]:opacity-100 group-has-[>input:checked]:scale-100 transition-all duration-300" aria-hidden="true">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        </div>

                        {{-- Nombre + descripción --}}
                        <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100 mb-1.5">{{ $servicio->nombre }}</h3>
                        <p class="text-ink-500 dark:text-sage-300 text-sm leading-relaxed line-clamp-2">{{ $servicio->descripcion }}</p>

                        {{-- Precio --}}
                        <div class="mt-auto pt-6 flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-sage-800 dark:text-cream-100">S/ {{ number_format($servicio->precio, 0) }}</span>
                            <span class="text-xs text-ink-500 dark:text-sage-400">/ {{ $servicio->duracion }} min por sesión</span>
                        </div>

                        {{-- Modalidad: control segmentado --}}
                        <div class="mt-4 flex gap-1 p-1 rounded-2xl bg-cream-100 dark:bg-sage-950/60 border border-cream-300/60 dark:border-sage-800" role="radiogroup" aria-label="Modalidad para {{ $servicio->nombre }}">
                            @foreach($servicio->modalidades as $modalidad)
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="modalidad_id[{{ $servicio->id }}]" value="{{ $modalidad->id }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }} onclick="event.stopPropagation()">
                                <div class="px-2 py-2 rounded-xl text-[11px] font-semibold text-center text-ink-500 dark:text-sage-300 transition-all peer-checked:bg-white dark:peer-checked:bg-sage-700 peer-checked:text-sage-700 dark:peer-checked:text-white peer-checked:shadow-sm peer-focus-visible:ring-2 peer-focus-visible:ring-sage-500">
                                    {{ $modalidad->nombre }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </label>
                @endforeach
            </div>

            {{-- Cantidad de sesiones + continuar --}}
            <div class="mt-8 surface p-6 md:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                    <div>
                        <h3 class="font-serif text-xl md:text-2xl text-sage-900 dark:text-cream-100 mb-1">¿Cuántas sesiones?</h3>
                        <p class="text-ink-500 dark:text-sage-300 text-sm">Reserva una o varias sesiones consecutivas.</p>
                    </div>
                    <div class="flex gap-1 p-1 rounded-2xl bg-cream-100 dark:bg-sage-950/60 border border-cream-300/60 dark:border-sage-800" role="radiogroup" aria-label="Cantidad de sesiones">
                        @foreach([1, 2, 3, 4] as $n)
                        <label class="cursor-pointer">
                            <input type="radio" name="sessions" value="{{ $n }}" class="peer sr-only" {{ $n == 1 ? 'checked' : '' }}>
                            <div class="w-11 h-11 flex items-center justify-center rounded-xl text-sm font-bold text-ink-500 dark:text-sage-300 transition-all peer-checked:bg-sage-600 peer-checked:text-white peer-checked:shadow-sm peer-focus-visible:ring-2 peer-focus-visible:ring-sage-500" title="{{ $n }} {{ $n > 1 ? 'sesiones' : 'sesión' }}">
                                {{ $n }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-cream-300 dark:border-sage-700">
                    <button type="submit" class="btn-primary w-full py-4 text-sm uppercase tracking-[0.15em]">
                        Siguiente: elegir profesional
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
