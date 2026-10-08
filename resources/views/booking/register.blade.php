@extends(auth()->check() && auth()->user()->isReceptionist() ? 'components.layouts.receptionist' : 'components.layouts.app')

@section('content')
<div class="relative min-h-screen pb-20">
    <div class="max-w-3xl mx-auto pt-6 px-2 sm:px-6">

        <!-- Pasos -->
        <div class="flex items-center justify-center gap-4 mb-14">
            <a href="{{ route('booking.schedule') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-2xl bg-sage-100 text-sage-600 flex items-center justify-center border border-sage-200 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </div>
                <span class="font-bold text-ink-500 text-[10px] uppercase tracking-widest hidden sm:block">Horario</span>
            </a>
            <div class="w-12 h-px bg-sage-300"></div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-sage-600 text-white flex items-center justify-center shadow-lg shadow-sage-200"><span class="font-bold text-sm">02</span></div>
                <span class="font-bold text-sage-800 text-[10px] uppercase tracking-widest">Detalles</span>
            </div>
            <div class="w-12 h-px bg-cream-300"></div>
            <div class="flex items-center gap-3 opacity-40">
                <div class="w-10 h-10 rounded-2xl bg-white border border-cream-300 text-ink-500 flex items-center justify-center"><span class="font-bold text-sm">03</span></div>
                <span class="font-bold text-ink-500 text-[10px] uppercase tracking-widest hidden sm:block">Pago</span>
            </div>
        </div>

        <div class="surface p-8 md:p-12">
            <div class="text-center mb-10">
                <div class="w-16 h-16 bg-sage-100 rounded-2xl flex items-center justify-center mx-auto mb-6 text-sage-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h2 class="font-serif text-3xl md:text-4xl text-sage-900 dark:text-cream-100 mb-4">Detalles del paciente</h2>
                <p class="text-ink-500 dark:text-sage-300 max-w-sm mx-auto">Queremos conocerte un poco mejor para brindarte la mejor atención posible.</p>
            </div>

            <form action="{{ route('booking.checkout') }}" method="GET" class="space-y-7">
                <input type="hidden" name="availability_id" value="{{ request('availability_id') }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                    <div>
                        <label class="label">Nombre completo</label>
                        <input type="text" value="{{ auth()->user()->nombre }}" readonly class="field !bg-cream-100 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="label">Correo electrónico</label>
                        <input type="email" value="{{ auth()->user()->email }}" readonly class="field !bg-cream-100 cursor-not-allowed">
                    </div>
                </div>

                <div class="text-left">
                    <label class="label">Número de teléfono</label>
                    <input type="tel" name="telefono" placeholder="+51 000 000 000" class="field">
                </div>

                <div class="text-left">
                    <label class="label">¿Qué te trae a consulta hoy?</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3" role="radiogroup" aria-label="Motivo de consulta">
                        @foreach(['Ansiedad', 'Estrés', 'Depresión', 'Otro'] as $focus)
                        <label class="cursor-pointer group">
                            <input type="radio" name="focus" value="{{ $focus }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }}>
                            <div class="text-center p-4 rounded-2xl border border-cream-300 dark:border-sage-700 bg-cream-100/60 dark:bg-sage-800 peer-checked:border-sage-500 peer-checked:bg-sage-50 dark:peer-checked:bg-sage-900 peer-focus-visible:ring-2 peer-focus-visible:ring-sage-500 peer-focus-visible:ring-offset-2 transition-all group-hover:border-sage-300">
                                <span class="text-xs font-bold uppercase tracking-widest text-ink-500 dark:text-sage-300 peer-checked:text-sage-700 dark:peer-checked:text-sage-200 transition-colors">{{ $focus }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="btn-primary w-full py-5 text-xs uppercase tracking-[0.2em]">
                        Proceder al pago
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                    <p class="mt-5 text-[10px] font-bold text-ink-500 flex items-center justify-center gap-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Tus datos están protegidos y son confidenciales
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
