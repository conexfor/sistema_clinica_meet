<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sia') }} · Centro de Psicología y Bienestar</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300..800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</head>

<body x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 40)" class="bg-cream-100 text-ink-900">

    @include('partials.site-header')

    <main class="w-full">

        <!-- Hero -->
        <section class="relative overflow-hidden bg-mesh">
            <!-- Orbes decorativos, muy sutiles -->
            <div class="orb -top-24 -right-24 w-[28rem] h-[28rem] bg-sage-300/30" data-parallax="0.04" data-parallax-mouse="6"></div>
            <div class="orb bottom-0 right-1/4 w-72 h-72 bg-sand-200/30" data-parallax="0.03" style="animation-delay:-7s"></div>

            <div class="relative max-w-7xl mx-auto px-6 pt-14 md:pt-20 pb-24 grid lg:grid-cols-2 gap-16 lg:gap-20 items-start">
                <div class="reveal visible lg:pt-6">
                    <span class="chip-sage glass-sm !bg-white/50 shadow-sm mb-8">
                        <span class="relative flex w-2 h-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sage-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full w-2 h-2 bg-sage-500"></span>
                        </span>
                        Psicología clínica basada en evidencia
                    </span>
                    <h1 class="font-serif text-5xl md:text-6xl leading-[1.08] text-sage-900 mb-8">
                        Un espacio sereno para<br>
                        <span class="italic text-gradient">reencontrarte contigo.</span>
                    </h1>
                    <p class="text-lg text-ink-700 leading-relaxed max-w-xl mb-11">
                        Acompañamiento psicológico cálido y profesional. Terapia individual, de pareja e infanto-juvenil
                        en un entorno pensado para tu calma y tu bienestar emocional.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('register') }}" class="btn-primary px-8 py-4 text-base">Agenda tu primera sesión</a>
                        <a href="#servicios" class="btn-outline px-8 py-4 text-base">Conoce los servicios</a>
                    </div>

                    <div class="flex items-center gap-8 mt-14">
                        <div>
                            <p class="font-serif text-3xl text-sage-700">+12</p>
                            <p class="text-xs text-ink-500 font-medium">años acompañando</p>
                        </div>
                        <div class="w-px h-10 bg-cream-300"></div>
                        <div>
                            <p class="font-serif text-3xl text-sage-700">+35</p>
                            <p class="text-xs text-ink-500 font-medium">especialistas</p>
                        </div>
                        <div class="w-px h-10 bg-cream-300"></div>
                        <div>
                            <p class="font-serif text-3xl text-sage-700">98%</p>
                            <p class="text-xs text-ink-500 font-medium">recomendarían</p>
                        </div>
                    </div>
                </div>

                <div class="relative" data-parallax-mouse="5">
                    <div class="relative rounded-[2.5rem] overflow-hidden shadow-[0_30px_70px_-20px_rgba(46,58,51,0.35)] aspect-[4/5] ring-1 ring-white/60 animate-float">
                        <!-- Ilustración serena a medida -->
                        <svg viewBox="0 0 400 500" class="w-full h-full" preserveAspectRatio="xMidYMid slice" role="img" aria-label="Ilustración de calma y bienestar">
                            <defs>
                                <linearGradient id="siaSky" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#e2ebe2"/>
                                    <stop offset="55%" stop-color="#c5d7c7"/>
                                    <stop offset="100%" stop-color="#9dbaa1"/>
                                </linearGradient>
                                <linearGradient id="siaHill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#5a7d6a"/>
                                    <stop offset="100%" stop-color="#3c5446"/>
                                </linearGradient>
                                <radialGradient id="siaSun" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#f3eadd"/>
                                    <stop offset="100%" stop-color="#d9be9c"/>
                                </radialGradient>
                            </defs>

                            <!-- Cielo -->
                            <rect width="400" height="500" fill="url(#siaSky)"/>

                            <!-- Sol -->
                            <circle cx="200" cy="180" r="66" fill="url(#siaSun)"/>
                            <circle cx="200" cy="180" r="86" fill="none" stroke="#f3eadd" stroke-opacity="0.4" stroke-width="2"/>

                            <!-- Colinas -->
                            <path d="M0 380 Q100 320 210 360 T400 350 V500 H0 Z" fill="#74977c" fill-opacity="0.55"/>
                            <path d="M0 420 Q120 360 240 405 T400 400 V500 H0 Z" fill="url(#siaHill)"/>

                            <!-- Hojas / planta -->
                            <g stroke="#3c5446" stroke-width="3" stroke-linecap="round" fill="#496755">
                                <path d="M64 420 Q60 360 84 320" fill="none"/>
                                <path d="M84 320 Q64 322 60 350 Q84 352 84 320 Z"/>
                                <path d="M74 372 Q52 370 48 396 Q72 396 74 372 Z"/>
                            </g>
                            <g stroke="#3c5446" stroke-width="3" stroke-linecap="round" fill="#496755">
                                <path d="M340 420 Q346 366 322 328" fill="none"/>
                                <path d="M322 328 Q342 332 344 358 Q322 358 322 328 Z"/>
                                <path d="M332 378 Q354 378 356 402 Q334 402 332 378 Z"/>
                            </g>

                            <!-- Figura en meditación -->
                            <g fill="#2f4337">
                                <ellipse cx="200" cy="452" rx="92" ry="20" fill="#26362d" fill-opacity="0.25"/>
                                <path d="M138 452 Q150 402 200 402 Q250 402 262 452 Z"/>
                                <path d="M138 452 Q140 430 176 428 L176 452 Z" fill="#26362d"/>
                                <path d="M262 452 Q260 430 224 428 L224 452 Z" fill="#26362d"/>
                                <circle cx="200" cy="360" r="30"/>
                                <path d="M170 402 Q200 384 230 402 Q214 420 200 420 Q186 420 170 402 Z"/>
                            </g>
                            <circle cx="200" cy="352" r="40" fill="none" stroke="#f3eadd" stroke-opacity="0.5" stroke-width="2"/>
                        </svg>
                        <div class="absolute inset-0 bg-gradient-to-t from-sage-900/25 via-transparent to-white/10"></div>
                        <div class="absolute inset-0 ring-1 ring-inset ring-white/20 rounded-[2.5rem]"></div>
                    </div>

                    <div class="absolute -bottom-6 -left-6 glass rounded-3xl p-5 flex items-center gap-4 max-w-[15rem]">
                        <div class="w-12 h-12 rounded-2xl bg-sage-100/80 flex items-center justify-center text-sage-600 flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-sage-800">Confidencialidad total</p>
                            <p class="text-xs text-ink-500">Tu proceso, en un espacio seguro.</p>
                        </div>
                    </div>

                    <div class="absolute -top-5 -right-5 glass rounded-2xl px-4 py-3 flex items-center gap-2 shadow-lg">
                        <div class="flex text-sand-500">
                            @for ($i = 0; $i < 5; $i++)
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.958c.299.921-.755 1.688-1.54 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.784.57-1.838-.197-1.54-1.118l1.287-3.958a1 1 0 00-.363-1.118L2.983 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.958z"/></svg>
                            @endfor
                        </div>
                        <span class="text-xs font-bold text-sage-800">4.9 / 5</span>
                    </div>
                </div>
            </div>

            <p class="relative text-center text-[11px] font-semibold text-ink-500 tracking-widest uppercase pb-8 flex items-center justify-center gap-4">
                <span class="w-12 h-px bg-cream-300"></span>
                Centro de Psicología Sia &copy; 2026 · Miraflores · San Isidro
                <span class="w-12 h-px bg-cream-300"></span>
            </p>

            <div class="relative flex justify-center pb-6">
                <a href="#servicios" class="flex flex-col items-center gap-1 text-ink-400 hover:text-sage-600 transition-colors animate-bounce-soft" aria-label="Bajar a servicios">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </a>
            </div>
        </section>

        <!-- Servicios -->
        <section id="servicios" class="relative max-w-7xl mx-auto px-6 py-28 md:py-36 reveal" x-data="{ visible: false }" x-intersect.once="visible = true" :class="{ 'visible': visible }">
            <div class="text-center max-w-2xl mx-auto mb-20">
                <span class="eyebrow">Áreas de acompañamiento</span>
                <h2 class="font-serif text-4xl text-sage-900 mt-3 mb-4">Terapia adaptada a cada etapa de la vida</h2>
                <p class="text-ink-600">Cada proceso es único. Diseñamos un acompañamiento a tu medida, con calidez y rigor profesional.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                @php
                    $servicios = [
                        ['t' => 'Terapia individual', 'd' => ['Ansiedad y estrés', 'Depresión y ánimo', 'Autoestima y seguridad'], 'i' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'a' => 'sage'],
                        ['t' => 'Terapia de pareja', 'd' => ['Resolución de conflictos', 'Comunicación asertiva', 'Vínculo y confianza'], 'i' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'a' => 'sand'],
                        ['t' => 'Infanto-juvenil', 'd' => ['Aprendizaje y atención', 'Desarrollo emocional', 'Acompañamiento familiar'], 'i' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'a' => 'sage'],
                    ];
                @endphp
                @foreach ($servicios as $idx => $s)
                    <div class="group surface surface-hover relative overflow-hidden p-9 flex flex-col reveal reveal-delay-{{ $idx + 1 }}" x-data="{ v: false }" x-intersect.once="v = true" :class="{ 'visible': v }">
                        <span class="absolute top-0 left-0 right-0 h-1 scale-x-0 origin-left transition-transform duration-500 group-hover:scale-x-100" style="background: linear-gradient(90deg, var(--color-sage-400), var(--color-sand-400));"></span>
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-7 {{ $s['a'] === 'sand' ? 'bg-sand-100 text-sand-600' : 'bg-sage-100 text-sage-600' }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['i'] }}"/></svg>
                        </div>
                        <h3 class="font-serif text-2xl text-sage-900 mb-4">{{ $s['t'] }}</h3>
                        <ul class="space-y-2.5 mb-8 flex-1">
                            @foreach ($s['d'] as $item)
                                <li class="flex items-center gap-3 text-sm text-ink-600">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $s['a'] === 'sand' ? 'bg-sand-500' : 'bg-sage-500' }}"></span>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('register') }}" class="text-sm font-bold {{ $s['a'] === 'sand' ? 'text-sand-600' : 'text-sage-600' }} inline-flex items-center gap-1.5 hover:gap-2.5 transition-all">
                            Reservar
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Nosotros / filosofía -->
        <section id="nosotros" class="relative overflow-hidden bg-sage-800 text-cream-100 py-28 md:py-36 reveal" x-data="{ visible: false }" x-intersect.once="visible = true" :class="{ 'visible': visible }">
            <div class="absolute inset-0 bg-mesh-dark opacity-90"></div>
            <div class="orb top-0 left-1/4 w-80 h-80 bg-sand-400/10" data-parallax="0.03"></div>

            <div class="relative max-w-5xl mx-auto px-6 text-center">
                <span class="text-xs font-bold uppercase tracking-[0.25em] text-sand-300">Nuestra filosofía</span>
                <h2 class="font-serif text-3xl md:text-4xl leading-snug mt-5 mb-8">
                    “Cuidar la mente es un acto de valentía. Nuestra labor es acompañarte en ese camino con
                    <span class="italic text-sand-300">respeto, calidez y profesionalismo.</span>”
                </h2>
                <div class="grid sm:grid-cols-3 gap-6 mt-16 text-left">
                    @php
                        $valores = [
                            ['t' => 'Escucha genuina', 'd' => 'Un espacio sin juicios donde puedes ser tú mismo.'],
                            ['t' => 'Rigor clínico', 'd' => 'Enfoques con respaldo científico y ético.'],
                            ['t' => 'Cercanía humana', 'd' => 'Acompañamiento cálido en cada paso del proceso.'],
                        ];
                    @endphp
                    @foreach ($valores as $v)
                        <div class="glass-dark rounded-3xl p-8 transition-transform duration-300 hover:-translate-y-1.5">
                            <div class="w-10 h-10 rounded-xl bg-sand-300/20 text-sand-300 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <h4 class="font-bold text-lg mb-1.5">{{ $v['t'] }}</h4>
                            <p class="text-sm text-cream-200/80">{{ $v['d'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- El espacio / galería -->
        <section id="clinica" class="max-w-7xl mx-auto px-6 py-28 md:py-36 reveal grid lg:grid-cols-2 gap-16 lg:gap-20 items-center" x-data="{ visible: false }" x-intersect.once="visible = true" :class="{ 'visible': visible }">
            <div>
                <div class="section-divider">
                    <span class="eyebrow">El espacio</span>
                    <h2 class="font-serif text-4xl text-sage-900 mt-3">Ambientes pensados para tu calma</h2>
                </div>
                <p class="text-ink-600 mb-8">Nuestras sedes combinan privacidad acústica, luz natural y una atmósfera cálida
                    para que cada sesión se sienta como un refugio.</p>
                <div class="space-y-4 mb-10">
                    @php
                        $features = [
                            ['t' => 'Privacidad total', 'd' => 'Aislamiento acústico que protege cada conversación.'],
                            ['t' => 'Ambiente controlado', 'd' => 'Luz e iluminación diseñadas para reducir el estrés.'],
                        ];
                    @endphp
                    @foreach ($features as $f)
                        <div class="flex gap-4 p-5 surface-glass transition-transform duration-300 hover:-translate-y-1">
                            <div class="w-9 h-9 rounded-xl bg-sage-100 text-sage-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-sage-900">{{ $f['t'] }}</h4>
                                <p class="text-sm text-ink-500">{{ $f['d'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('register') }}" class="btn-accent px-7 py-3.5">Agenda un recorrido</a>
            </div>

            <div class="grid grid-cols-2 gap-5">
                @php
                    $galeria = [
                        '650658521_1505733138225960_4746970566053627693_n.jpg',
                        '625971996_1471348794997728_8591781840000181479_n.jpg',
                        '625958492_1471348741664400_4645495522416018553_n.jpg',
                        '626256792_1471348691664405_1024310863585606939_n.jpg',
                    ];
                @endphp
                @foreach ($galeria as $i => $img)
                    <div class="group relative rounded-3xl overflow-hidden ring-1 ring-cream-300 shadow-soft aspect-[3/4] {{ $i % 2 === 1 ? 'mt-10' : '' }}" data-parallax="{{ $i % 2 === 0 ? '0.015' : '0.03' }}">
                        <img src="{{ asset('images/' . $img) }}" class="w-full h-full object-cover scale-105 group-hover:scale-110 transition-transform duration-700 ease-out">
                        <div class="absolute inset-0 bg-gradient-to-t from-sage-900/50 via-sage-900/0 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="absolute inset-0 ring-1 ring-inset ring-white/10"></div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- CTA final -->
        <section id="contacto" class="max-w-7xl mx-auto px-6 pb-28 md:pb-36">
            <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-sage-600 via-sage-700 to-sage-800 px-8 py-20 md:p-24 text-center">
                <div class="orb -top-16 -right-16 w-72 h-72 bg-sand-300/20" data-parallax="0.02"></div>
                <div class="absolute inset-0 ring-1 ring-inset ring-white/10 rounded-[2.5rem]"></div>

                <div class="relative">
                    <span class="chip-sand glass-sm !bg-white/15 !text-white mb-6">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.958c.299.921-.755 1.688-1.54 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.784.57-1.838-.197-1.54-1.118l1.287-3.958a1 1 0 00-.363-1.118L2.983 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.958z"/></svg>
                        Cupos limitados esta semana
                    </span>
                    <h2 class="font-serif text-4xl md:text-5xl text-white mb-5">Da el primer paso hoy</h2>
                    <p class="text-cream-100/85 max-w-2xl mx-auto mb-10 text-lg">Comienza tu proceso con una evaluación
                        inicial sin compromiso. Estamos aquí para escucharte.</p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('register') }}" class="btn bg-white text-sage-700 hover:bg-cream-100 px-9 py-4 text-base">Reservar mi cita</a>
                        <a href="tel:+012345678" class="btn glass-sm !bg-white/10 text-white hover:!bg-white/20 px-9 py-4 text-base">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11 11 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            01 234 5678
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    @include('partials.site-footer')
</body>

</html>
