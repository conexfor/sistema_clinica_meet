@extends('components.layouts.admin')

@php $active = 'usuarios'; @endphp

@section('content')
<div class="max-w-screen-2xl mx-auto pb-16">
    <div class="mb-8">
        <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Usuarios</h1>
        <p class="text-sm text-ink-500 dark:text-sage-300 mt-1">Activa o desactiva el acceso de cualquier cuenta del sistema.</p>
    </div>

    <!-- Filtros por rol -->
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <a href="{{ route('admin.usuarios.index') }}" class="px-4 py-2 rounded-xl text-[13px] font-semibold {{ !$rol ? 'bg-sage-600 text-white shadow-sm' : 'bg-white border border-cream-300 text-ink-500 hover:text-sage-700' }}">Todos</a>
        @foreach($roles as $key => $label)
            <a href="{{ route('admin.usuarios.index', ['rol' => $key]) }}" class="px-4 py-2 rounded-xl text-[13px] font-semibold {{ $rol === $key ? 'bg-sage-600 text-white shadow-sm' : 'bg-white border border-cream-300 text-ink-500 hover:text-sage-700' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="surface overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-cream-100">
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-200">
                    @php $rolLabelsSingular = ['admin' => 'Administrador', 'doctor' => 'Doctor', 'patient' => 'Paciente', 'receptionist' => 'Recepcionista']; @endphp
                    @forelse($usuarios as $u)
                        @php $esYo = $u->id === auth()->id(); @endphp
                        <tr class="hover:bg-cream-100/60 transition-colors {{ $u->esta_activo ? '' : 'opacity-70' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-sage-900">{{ $u->nombre }}</span>
                                @if($esYo)<span class="ml-2 chip-sage">Tú</span>@endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="text-sm text-ink-500">{{ $u->email }}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="text-sm text-ink-600">{{ $rolLabelsSingular[$u->rol->nombre ?? ''] ?? ucfirst($u->rol->nombre ?? '—') }}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($u->esta_activo)
                                    <span class="chip-sage">Activo</span>
                                @else
                                    <span class="chip-sand">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($esYo)
                                    <span class="text-xs text-ink-400">—</span>
                                @else
                                    <form action="{{ route('admin.usuarios.estado', $u->id) }}" method="POST" class="inline"
                                          data-confirm="¿Seguro que deseas {{ $u->esta_activo ? 'DESACTIVAR' : 'ACTIVAR' }} la cuenta de {{ $u->nombre }}?{{ $u->esta_activo ? ' No podrá iniciar sesión.' : '' }}"
                                          data-confirm-ok="{{ $u->esta_activo ? 'Desactivar' : 'Activar' }}"
                                          @if($u->esta_activo) data-confirm-variant="danger" @endif>
                                        @csrf @method('PATCH')
                                        @if($u->esta_activo)
                                            <button type="submit" class="px-4 py-2 text-xs font-bold text-sand-700 hover:bg-sand-50 rounded-xl transition-colors">Desactivar</button>
                                        @else
                                            <button type="submit" class="px-4 py-2 text-xs font-bold text-sage-600 hover:bg-sage-50 rounded-xl transition-colors">Activar</button>
                                        @endif
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-16 text-center text-ink-500 text-sm">No hay usuarios para este filtro.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($usuarios->hasPages())
            <div class="p-6 bg-cream-100 border-t border-cream-300">{{ $usuarios->links() }}</div>
        @endif
    </div>
</div>
@endsection
