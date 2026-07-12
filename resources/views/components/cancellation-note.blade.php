@props(['cita'])

@if(($cita->estado ?? null) === \App\Models\Cita::STATUS_CANCELLED)
    <div {{ $attributes->merge(['class' => 'mt-1.5']) }}>
        <p class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Cancelada por {{ $cita->cancelado_por === 'Especialista' ? 'el especialista' : 'el paciente' }}
        </p>
        @if(filled($cita->motivo_cancelacion))
            <p class="text-xs text-ink-500 dark:text-sage-300 italic mt-0.5">“{{ $cita->motivo_cancelacion }}”</p>
        @endif
    </div>
@endif
