@php
    $steps = [
        1 => 'Servicio',
        2 => 'Profesional',
        3 => 'Horario',
        4 => 'Pago',
    ];
    $current = $current ?? 1;
    $total = count($steps);

    /**
     * $stepLinks: mapa [numeroPaso => URL] con los parámetros ya seleccionados.
     * Solo se puede volver a un paso ANTERIOR y que tenga URL configurada.
     * El paso 1 (Servicio) siempre está disponible.
     */
    $stepLinks = ($stepLinks ?? []) + [1 => route('booking.service')];
@endphp

<nav aria-label="Progreso de la reserva" class="mb-12 sm:mb-14">
    <!-- Indicador compacto en móvil -->
    <p class="sm:hidden text-center text-[11px] font-bold uppercase tracking-[0.2em] text-ink-500 mb-4">
        Paso {{ $current }} de {{ $total }} · <span class="text-sage-700 dark:text-sage-300">{{ $steps[$current] }}</span>
    </p>

    <ol class="flex items-center justify-center gap-2 sm:gap-4" role="list">
        @foreach ($steps as $n => $label)
            @php
                $isDone = $n < $current;
                $isActive = $n == $current;
                $clickable = $isDone && isset($stepLinks[$n]);
            @endphp
            <li class="flex items-center gap-2 sm:gap-3 {{ (!$isDone && !$isActive) ? 'opacity-40' : '' }}"
                @if($isActive) aria-current="step" @endif>
                @php
                    $badge = 'w-9 h-9 sm:w-10 sm:h-10 rounded-2xl flex items-center justify-center transition-all '
                        . ($isActive
                            ? 'bg-sage-600 text-white shadow-lg shadow-sage-200 dark:shadow-none'
                            : ($isDone
                                ? 'bg-sage-100 text-sage-600 border border-sage-200 dark:bg-sage-800 dark:text-sage-200 dark:border-sage-700'
                                : 'bg-white dark:bg-sage-900 border border-cream-300 dark:border-sage-700 text-ink-500'));
                @endphp
                @if ($clickable)
                    <a href="{{ $stepLinks[$n] }}" class="{{ $badge }} hover:scale-105 hover:bg-sage-200 dark:hover:bg-sage-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage-500 focus-visible:ring-offset-2"
                       aria-label="Volver al paso {{ $n }}: {{ $label }}">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </a>
                @else
                    <span class="{{ $badge }}">
                        @if ($isDone)
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        @else
                            <span class="font-bold text-sm">0{{ $n }}</span>
                        @endif
                    </span>
                @endif
                @if ($clickable)
                    <a href="{{ $stepLinks[$n] }}" class="font-bold text-[10px] uppercase tracking-widest hidden sm:block text-ink-500 hover:text-sage-700 dark:hover:text-sage-200 transition-colors focus-visible:outline-none focus-visible:underline">{{ $label }}</a>
                @else
                    <span class="font-bold text-[10px] uppercase tracking-widest hidden sm:block {{ $isActive ? 'text-sage-800 dark:text-cream-100' : 'text-ink-500' }}">{{ $label }}</span>
                @endif
            </li>
            @if (!$loop->last)
                <li aria-hidden="true" class="w-6 sm:w-12 h-px {{ $n < $current ? 'bg-sage-300' : 'bg-cream-300 dark:bg-sage-700' }}"></li>
            @endif
        @endforeach
    </ol>
</nav>
