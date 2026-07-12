@extends('components.layouts.admin')

@php $active = 'soporte'; @endphp

@section('content')
<div class="max-w-screen-2xl mx-auto pb-16" x-data="{ openTicket: null }">
    <div class="mb-8">
        <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Bandeja de soporte</h1>
        <p class="text-sm text-ink-500 dark:text-sage-300 mt-1">Incidencias reportadas por pacientes y doctores.</p>
    </div>

    <!-- Filtros por estado -->
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <a href="{{ route('admin.soporte.index') }}" class="px-4 py-2 rounded-xl text-[13px] font-semibold {{ !$estado ? 'bg-sage-600 text-white shadow-sm' : 'bg-white border border-cream-300 text-ink-500 hover:text-sage-700' }}">Todas</a>
        @foreach(['abierto' => 'Abiertas', 'en_proceso' => 'En proceso', 'resuelto' => 'Resueltas'] as $key => $label)
            <a href="{{ route('admin.soporte.index', ['estado' => $key]) }}" class="px-4 py-2 rounded-xl text-[13px] font-semibold {{ $estado === $key ? 'bg-sage-600 text-white shadow-sm' : 'bg-white border border-cream-300 text-ink-500 hover:text-sage-700' }}">
                {{ $label }} <span class="opacity-70">({{ $conteos[$key] }})</span>
            </a>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse($tickets as $ticket)
            <div class="surface overflow-hidden">
                <div class="p-5 flex items-start justify-between gap-4 cursor-pointer hover:bg-cream-100/50 transition-colors" @click="openTicket = (openTicket === {{ $ticket->id }} ? null : {{ $ticket->id }})">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            @php $b = ['abierto' => 'chip-sand', 'en_proceso' => 'chip', 'resuelto' => 'chip-sage'][$ticket->estado] ?? 'chip'; @endphp
                            <span class="{{ $b }}">{{ ucfirst(str_replace('_',' ',$ticket->estado)) }}</span>
                            <span class="text-xs font-semibold text-ink-500">{{ $ticket->categoria_label }}</span>
                        </div>
                        <h3 class="text-sm font-bold text-sage-900 truncate">{{ $ticket->asunto }}</h3>
                        <p class="text-xs text-ink-500 mt-0.5">
                            {{ $ticket->usuario->nombre ?? 'Usuario' }}
                            @if($ticket->rol) · {{ ucfirst($ticket->rol) }} @endif
                            · {{ $ticket->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <svg :class="{'rotate-180': openTicket === {{ $ticket->id }}}" class="w-5 h-5 text-ink-400 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>

                <div x-show="openTicket === {{ $ticket->id }}" x-transition x-cloak class="px-5 pb-5 border-t border-cream-200">
                    <p class="text-sm text-ink-700 whitespace-pre-line mt-4 mb-4 bg-cream-100/60 rounded-2xl p-4">{{ $ticket->mensaje }}</p>

                    @if($ticket->respuesta)
                        <div class="bg-sage-50 border border-sage-200 rounded-2xl p-4 mb-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-sage-600 mb-1">Respuesta · {{ $ticket->respondidoPor->nombre ?? 'Admin' }}</p>
                            <p class="text-sm text-sage-900 whitespace-pre-line">{{ $ticket->respuesta }}</p>
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row gap-3">
                        <form action="{{ route('admin.soporte.respond', $ticket->id) }}" method="POST" class="flex-1 flex flex-col gap-2">
                            @csrf
                            <textarea name="respuesta" rows="2" required placeholder="Escribe una respuesta…" class="field resize-none">{{ $ticket->respuesta }}</textarea>
                            <button type="submit" class="btn-primary px-6 py-2.5 self-start text-xs uppercase tracking-widest">Responder y resolver</button>
                        </form>
                        @if($ticket->estado === 'abierto')
                            <form action="{{ route('admin.soporte.estado', $ticket->id) }}" method="POST" class="shrink-0">
                                @csrf @method('PATCH')
                                <input type="hidden" name="estado" value="en_proceso">
                                <button type="submit" class="btn-outline px-5 py-2.5 text-xs uppercase tracking-widest">Marcar en proceso</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="surface p-16 text-center text-ink-500 text-sm">No hay incidencias en esta bandeja.</div>
        @endforelse
    </div>

    @if($tickets->hasPages())
        <div class="mt-6">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
