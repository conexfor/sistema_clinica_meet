@extends('components.layouts.admin')

@php $active = 'categorias'; @endphp

@section('content')
<div class="max-w-6xl mx-auto pb-20">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-10 gap-6">
        <div>
            <h1 class="font-serif text-3xl md:text-4xl text-sage-900 dark:text-cream-100">Categorías de servicios</h1>
            <p class="text-ink-500 dark:text-sage-300 mt-1">Organiza los servicios de la clínica en categorías.</p>
        </div>
        <a href="{{ route('admin.categorias.create') }}" class="btn-primary !rounded-2xl px-8 py-4 text-xs uppercase tracking-widest">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Nueva categoría
        </a>
    </div>

    <div class="surface overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-cream-100">
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Descripción</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Servicios</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-200">
                    @forelse($categorias as $categoria)
                        <tr class="hover:bg-cream-100/60 transition-colors">
                            <td class="px-6 py-4"><span class="text-sm font-bold text-sage-900">{{ $categoria->nombre }}</span></td>
                            <td class="px-6 py-4"><span class="text-sm text-ink-500">{{ Str::limit($categoria->descripcion, 60) ?: '—' }}</span></td>
                            <td class="px-6 py-4"><span class="text-sm font-semibold text-ink-600">{{ $categoria->servicios_count }}</span></td>
                            <td class="px-6 py-4">
                                @if($categoria->esta_activo)
                                    <span class="chip-sage">Activa</span>
                                @else
                                    <span class="chip-sand">Inactiva</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.categorias.edit', $categoria->id) }}" class="p-2 text-ink-400 hover:text-sage-600 transition-colors" title="Editar">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </a>
                                    <form action="{{ route('admin.categorias.toggle', $categoria->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="p-2 {{ $categoria->esta_activo ? 'text-ink-400 hover:text-sand-600' : 'text-sage-500 hover:text-sage-700' }} transition-colors" title="{{ $categoria->esta_activo ? 'Desactivar' : 'Activar' }}">
                                            @if($categoria->esta_activo)
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><line x1="9" x2="15" y1="9" y2="15"/><line x1="15" x2="9" y1="9" y2="15"/></svg>
                                            @else
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                            @endif
                                        </button>
                                    </form>
                                    @if($categoria->servicios_count === 0)
                                        <form action="{{ route('admin.categorias.destroy', $categoria->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta categoría?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 text-ink-400 hover:text-sand-600 transition-colors" title="Eliminar">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <p class="font-serif text-lg text-sage-900 mb-1">No hay categorías registradas</p>
                                <p class="text-sm text-ink-500">Crea tu primera categoría para clasificar los servicios.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
