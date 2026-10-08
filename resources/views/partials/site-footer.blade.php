<footer class="bg-sage-900 text-sage-200 pt-20 pb-10">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 lg:grid-cols-4 gap-12 border-b border-sage-700/50 pb-14">
        <div>
            <div class="flex items-center gap-3 mb-6">
                <span class="text-xl font-serif font-semibold text-white">{{ config('clinic.name') }}<span class="text-sand-400">.</span></span>
            </div>
            <p class="text-sm leading-relaxed text-sage-300">Centro de psicología y bienestar dedicado a acompañarte
                con calidez, ética y rigor profesional en cada etapa de tu vida.</p>
        </div>

        <div>
            <h5 class="text-white text-xs font-bold uppercase tracking-widest mb-6">Servicios</h5>
            <ul class="space-y-3 text-sm">
                <li><a href="{{ route('landing') }}#servicios" class="hover:text-sand-300 transition-colors">Psicoterapia individual</a></li>
                <li><a href="{{ route('landing') }}#servicios" class="hover:text-sand-300 transition-colors">Terapia de pareja</a></li>
                <li><a href="{{ route('landing') }}#servicios" class="hover:text-sand-300 transition-colors">Terapia infanto-juvenil</a></li>
                <li><a href="{{ route('landing') }}#servicios" class="hover:text-sand-300 transition-colors">Evaluación psicológica</a></li>
            </ul>
        </div>

        <div>
            <h5 class="text-white text-xs font-bold uppercase tracking-widest mb-6">Transparencia</h5>
            <ul class="space-y-3 text-sm">
                <li><a href="#" class="hover:text-sand-300 transition-colors">Política de privacidad</a></li>
                <li><a href="#" class="hover:text-sand-300 transition-colors">Términos del portal</a></li>
                <li><a href="#" class="hover:text-sand-300 transition-colors">Libro de reclamaciones</a></li>
            </ul>
        </div>

        <div>
            <h5 class="text-white text-xs font-bold uppercase tracking-widest mb-6">Contacto</h5>
            <div class="space-y-3 text-sm">
                <p class="flex items-start gap-2"><svg class="w-4 h-4 text-sand-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/></svg> {{ config('clinic.address') }}, {{ config('clinic.address_detail') }}</p>
                <p><a href="tel:{{ preg_replace('/[^\d+]/', '', config('clinic.phone')) }}" class="hover:text-sand-300 transition-colors">{{ config('clinic.phone') }}</a></p>
                <p><a href="mailto:{{ config('clinic.email') }}" class="hover:text-sand-300 transition-colors">{{ config('clinic.email') }}</a></p>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-6 mt-10 flex flex-col md:flex-row justify-between items-center gap-5">
        <p class="text-[11px] uppercase font-semibold tracking-widest text-sage-400">&copy; {{ date('Y') }} Centro de Psicología {{ config('clinic.name') }} · Todos los derechos reservados</p>
        <div class="flex gap-3">
            <a href="{{ config('clinic.linkedin') }}" class="w-9 h-9 rounded-full bg-sage-800 flex items-center justify-center text-xs font-bold hover:bg-sand-500 hover:text-white transition-colors">in</a>
            <a href="{{ config('clinic.instagram') }}" class="w-9 h-9 rounded-full bg-sage-800 flex items-center justify-center text-xs font-bold hover:bg-sand-500 hover:text-white transition-colors">ig</a>
        </div>
    </div>
</footer>
