@extends('components.layouts.admin')

@php $active = 'lista-espera'; @endphp

@section('content')
<div class="max-w-screen-2xl mx-auto pb-16" x-data="{ showForm: false }">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Lista de espera</h1>
            <p class="text-sm text-ink-500 dark:text-sage-300 mt-1">Pacientes que solicitaron cita sin horario disponible.</p>
        </div>
        <button @click="showForm = !showForm" class="btn-primary !rounded-2xl px-6 py-3 text-xs uppercase tracking-widest">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Agregar manual
        </button>
    </div>

    <!-- Formulario alta manual -->
    <div x-show="showForm" x-cloak x-transition class="surface p-6 mb-8">
        <h3 class="font-serif text-lg text-sage-900 mb-4">Nueva solicitud</h3>
        <form action="{{ route('admin.lista-espera.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="label">Nombre *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required class="field">
                @error('nombre') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono') }}" class="field">
            </div>
            <div>
                <label class="label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="field">
            </div>
            <div>
                <label class="label">Servicio</label>
                <select name="servicio_id" class="field appearance-none">
                    <option value="">— Cualquiera —</option>
                    @foreach($servicios as $s)<option value="{{ $s->id }}">{{ $s->nombre }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="label">Especialista</label>
                <select name="especialista_id" class="field appearance-none">
                    <option value="">— Cualquiera —</option>
                    @foreach($especialistas as $e)<option value="{{ $e->id }}">{{ $e->usuario->nombre ?? 'Especialista' }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="label">Modalidad</label>
                <select name="modalidad_id" class="field appearance-none">
                    <option value="">— Cualquiera —</option>
                    @foreach($modalidades as $m)<option value="{{ $m->id }}">{{ $m->nombre }}</option>@endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="label">Notas</label>
                <textarea name="notas" rows="2" class="field resize-none">{{ old('notas') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="btn-primary px-8 py-3">Guardar solicitud</button>
            </div>
        </form>
    </div>

    <!-- Filtros por estado -->
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <a href="{{ route('admin.lista-espera.index') }}" class="px-4 py-2 rounded-xl text-[13px] font-semibold {{ !$estado ? 'bg-sage-600 text-white shadow-sm' : 'bg-white border border-cream-300 text-ink-500 hover:text-sage-700' }}">Todas</a>
        @foreach(['pendiente' => 'Pendientes', 'contactado' => 'Contactados', 'resuelto' => 'Resueltos'] as $key => $label)
            <a href="{{ route('admin.lista-espera.index', ['estado' => $key]) }}" class="px-4 py-2 rounded-xl text-[13px] font-semibold {{ $estado === $key ? 'bg-sage-600 text-white shadow-sm' : 'bg-white border border-cream-300 text-ink-500 hover:text-sage-700' }}">
                {{ $label }} <span class="opacity-70">({{ $conteos[$key] }})</span>
            </a>
        @endforeach
    </div>

    <div class="surface overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-cream-100">
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Solicitó</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Paciente</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Preferencia</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-200">
                    @forelse($solicitudes as $sol)
                        <tr class="hover:bg-cream-100/60 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap"><span class="text-sm text-ink-500">{{ $sol->created_at->format('d/m/Y') }}</span></td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-sage-900">{{ $sol->nombre_mostrado }}</p>
                                <p class="text-xs text-ink-500">{{ $sol->telefono ?? $sol->email ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-ink-600">{{ $sol->servicio->nombre ?? 'Cualquier servicio' }}</p>
                                <p class="text-xs text-ink-500">
                                    {{ $sol->especialista->usuario->nombre ?? 'Cualquier especialista' }}
                                    @if($sol->modalidad) · {{ $sol->modalidad->nombre }} @endif
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                @php $badge = ['pendiente' => 'chip-sand', 'contactado' => 'chip', 'resuelto' => 'chip-sage'][$sol->estado] ?? 'chip'; @endphp
                                <span class="{{ $badge }}">{{ ucfirst($sol->estado) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    @if($sol->estado !== 'contactado')
                                        <form action="{{ route('admin.lista-espera.estado', $sol->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="estado" value="contactado">
                                            <button type="submit" class="px-3 py-1.5 text-xs font-bold text-ink-500 hover:text-sage-700 rounded-lg hover:bg-sage-50" title="Marcar como contactado">Contactado</button>
                                        </form>
                                    @endif
                                    @if($sol->estado !== 'resuelto')
                                        <form action="{{ route('admin.lista-espera.estado', $sol->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="estado" value="resuelto">
                                            <button type="submit" class="px-3 py-1.5 text-xs font-bold text-sage-600 hover:text-sage-800 rounded-lg hover:bg-sage-50" title="Marcar como resuelto">Resuelto</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.lista-espera.destroy', $sol->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta solicitud?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-ink-400 hover:text-sand-600" title="Eliminar">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-16 text-center text-ink-500 text-sm">No hay solicitudes en lista de espera.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($solicitudes->hasPages())
            <div class="p-6 bg-cream-100 border-t border-cream-300">{{ $solicitudes->links() }}</div>
        @endif
    </div>
</div>
@endsection
