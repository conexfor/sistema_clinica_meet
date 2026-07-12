@extends('components.layouts.admin')

@php $active = 'auditoria'; @endphp

@section('content')
<div class="max-w-screen-2xl mx-auto pb-16">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Log de auditoría</h1>
            <p class="text-sm text-ink-500 dark:text-sage-300 mt-1">Registro de acciones sensibles: inicios de sesión, cambios de precios y bajas.</p>
        </div>
    </div>

    <!-- Filtros por categoría -->
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <a href="{{ route('admin.reportes.auditoria') }}"
           class="px-4 py-2 rounded-xl text-[13px] font-semibold transition-colors {{ !$categoria ? 'bg-sage-600 text-white shadow-sm' : 'bg-white border border-cream-300 text-ink-500 hover:text-sage-700' }}">
            Todas
        </a>
        @foreach($categorias as $key => $label)
            <a href="{{ route('admin.reportes.auditoria', ['categoria' => $key]) }}"
               class="px-4 py-2 rounded-xl text-[13px] font-semibold transition-colors {{ $categoria === $key ? 'bg-sage-600 text-white shadow-sm' : 'bg-white border border-cream-300 text-ink-500 hover:text-sage-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="surface overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-cream-100">
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-200">
                    @php
                        $rolLabels = ['admin' => 'Administrador', 'doctor' => 'Doctor', 'patient' => 'Paciente', 'receptionist' => 'Recepcionista'];
                    @endphp
                    @forelse($eventos as $ev)
                        <tr class="hover:bg-cream-100/60 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap"><span class="text-sm text-ink-600">{{ $ev->created_at->format('d/m/Y H:i') }}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="text-sm font-semibold text-sage-900">{{ $ev->usuario->nombre ?? 'Sistema' }}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $rol = $ev->usuario->rol->nombre ?? ($ev->metadata['rol'] ?? null); @endphp
                                <span class="text-sm text-ink-600">{{ $rol ? ($rolLabels[$rol] ?? ucfirst($rol)) : '—' }}</span>
                            </td>
                            <td class="px-6 py-4"><span class="chip-sage">{{ $categorias[$ev->categoria] ?? ucfirst($ev->categoria) }}</span></td>
                            <td class="px-6 py-4"><span class="text-sm text-ink-600">{{ $ev->descripcion }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-16 text-center text-ink-500 text-sm">No hay eventos registrados todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($eventos->hasPages())
            <div class="p-6 bg-cream-100 border-t border-cream-300">{{ $eventos->links() }}</div>
        @endif
    </div>
</div>
@endsection
