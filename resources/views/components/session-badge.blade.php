@props(['cita'])

@php
    $estado = $cita->estado ?? null;
    $anulada = in_array($estado, [\App\Models\Cita::STATUS_CANCELLED, \App\Models\Cita::STATUS_RESCHEDULED]);
    $esVirtual = (int) ($cita->modalidad_id ?? 0) === \App\Models\Modalidad::VIRTUAL;

    // Código de color: Verde = Virtual (Meet) · Azul = Presencial · Gris = Cancelada/Reprogramada
    if ($anulada) {
        $classes = 'bg-slate-100 text-slate-600 border-slate-200';
        $label   = $estado === \App\Models\Cita::STATUS_CANCELLED ? 'Cancelada' : 'Reprogramada';
        $icon    = '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>';
    } elseif ($esVirtual) {
        $classes = 'bg-emerald-100 text-emerald-700 border-emerald-200';
        $label   = 'Virtual (Meet)';
        $icon    = '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>';
    } else {
        $classes = 'bg-blue-100 text-blue-700 border-blue-200';
        $label   = 'Presencial';
        $icon    = '<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>';
    }
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-[10px] font-bold uppercase tracking-wider border $classes"]) }}>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">{!! $icon !!}</svg>
    {{ $label }}
</span>
