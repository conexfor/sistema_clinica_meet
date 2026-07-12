@extends('components.layouts.admin')

@php $active = 'pacientes'; @endphp

@section('header_title', 'Pacientes Registrados')

@section('content')
<div class="max-w-7xl mx-auto pb-10">
    <div class="mb-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Pacientes registrados</h1>
            <p class="text-ink-500 dark:text-sage-300 mt-1">Listado completo de personas registradas en el sistema.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-outline !rounded-xl text-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver al panel
        </a>
    </div>

    <div class="surface overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-cream-100 border-b border-cream-300">
                        <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">Paciente</th>
                        <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">Contacto</th>
                        <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">DNI / documento</th>
                        <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">Distrito</th>
                        <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">Registro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-200">
                    @forelse ($pacientes as $usuario)
                    <tr class="hover:bg-cream-100/60 transition-colors group">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-sage-100 text-sage-700 flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-110 transition-transform uppercase">{{ substr($usuario->nombre, 0, 2) }}</div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-sage-900 dark:text-cream-100">{{ $usuario->nombre }}</span>
                                    <span class="text-xs text-ink-500 font-medium">{{ $usuario->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-sm font-bold text-ink-600">{{ $usuario->telefono ?? 'No registrado' }}</td>
                        <td class="px-8 py-5 text-sm font-bold text-ink-600">{{ $usuario->paciente->dni ?? '---' }}</td>
                        <td class="px-8 py-5">
                            <span class="px-3 py-1 bg-cream-200 text-ink-600 rounded-lg text-[10px] font-bold uppercase tracking-widest">{{ $usuario->paciente->distrito ?? 'No especificado' }}</span>
                        </td>
                        <td class="px-8 py-5 text-sm text-ink-500 font-medium">{{ $usuario->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-8 py-20 text-center text-ink-500 italic">No hay pacientes registrados aún.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-6 bg-cream-100 border-t border-cream-300">{{ $pacientes->links() }}</div>
    </div>
</div>
@endsection
