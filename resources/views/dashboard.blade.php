@php
    use App\Models\LandingImage;

    $clinic = config('clinic');
    $images = collect($landingImages ?? [])->keyBy('position');
    $img = fn (string $position) => LandingImage::resolve($images, $position);

    $bookingUrl = route('booking.register');
    $telHref = 'tel:' . preg_replace('/[^\d+]/', '', $clinic['phone']);
    $waHref = 'https://wa.me/' . $clinic['whatsapp'];

    $sections = [
        ['id' => 'enfoque', 'n' => '00', 'label' => 'Enfoque'],
        ['id' => 'servicios', 'n' => '01', 'label' => 'Servicios'],
        ['id' => 'nosotros', 'n' => '02', 'label' => 'Nosotros'],
        ['id' => 'espacio', 'n' => '03', 'label' => 'El espacio'],
        ['id' => 'contacto', 'n' => '04', 'label' => 'Contacto'],
    ];

    $services = [
        ['slot' => 'service_1', 'name' => 'Psicoterapia individual', 'desc' => 'Un espacio propio para comprender lo que te ocurre, ordenar lo que pesa y recuperar el margen para decidir. Sesiones semanales o quincenales, a tu ritmo.'],
        ['slot' => 'service_2', 'name' => 'Terapia de pareja', 'desc' => 'Para volver a escucharse cuando la conversación se ha vuelto difícil. Trabajamos el vínculo, los acuerdos y la forma de estar juntos.'],
        ['slot' => 'service_3', 'name' => 'Procesos de transición', 'desc' => 'Duelos, cambios de etapa, maternidad, mudanzas o decisiones laborales. Acompañamiento para atravesar lo que se mueve sin perderte en ello.'],
        ['slot' => 'service_4', 'name' => 'Consulta online', 'desc' => 'El mismo criterio y la misma cercanía, desde donde estés. Videollamada segura y horarios pensados para encajar en tu semana.'],
    ];

    $arrow = '<svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>';
    $arrowDown = '<svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 5v14M6 13l6 6 6-6"/></svg>';
    $arrowUp = '<svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 19V5M6 11l6-6 6 6"/></svg>';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $clinic['name'] }} · Psicología clínica en {{ $clinic['city'] }}</title>
    <meta name="description" content="Psicoterapia individual, de pareja y online en {{ $clinic['city'] }}. Un espacio privado para volver a ti, con tiempo, escucha y una mirada que no juzga.">
    <meta name="theme-color" content="#0B3553">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link rel="preload" as="image" href="{{ $img('hero_image')['url'] }}">

    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
</head>

<body class="lnd">

    {{-- ===================== Navegación ===================== --}}
    <header class="lnd-nav" data-nav>
        <div class="wrap lnd-nav__inner">
            <a href="#inicio" class="lnd-brand" aria-label="{{ $clinic['name'] }}, volver al inicio">
                <span class="lnd-brand__name">{{ $clinic['name'] }}<span>.</span></span>
                <span class="lnd-brand__desc">{{ $clinic['tagline'] }}</span>
            </a>

            <nav aria-label="Principal">
                <ul class="lnd-nav__links">
                    @foreach (array_slice($sections, 1) as $s)
                        <li><a href="#{{ $s['id'] }}">{{ $s['label'] }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <div class="lnd-nav__actions">
                @auth
                    <a href="{{ auth()->user()->isPatient() ? route('portal.index') : route('inicio') }}" class="lnd-nav__cta">
                        {{ auth()->user()->isPatient() ? 'Mi portal' : 'Panel' }} {!! $arrow !!}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="lnd-nav__login">Acceder</a>
                    <a href="{{ $bookingUrl }}" class="lnd-nav__cta">Agendar consulta {!! $arrow !!}</a>
                @endauth
                <button type="button" class="lnd-nav__toggle" data-menu-open aria-controls="lnd-menu" aria-expanded="false">
                    <span></span><span></span>
                    <span class="sr-only">Abrir menú</span>
                </button>
            </div>
        </div>
    </header>

    {{-- Menú móvil: índice editorial --}}
    <div class="lnd-menu" id="lnd-menu" data-menu role="dialog" aria-modal="true" aria-label="Menú" aria-hidden="true">
        <div class="lnd-menu__top">
            <span class="lnd-brand__name">{{ $clinic['name'] }}<span>.</span></span>
            <button type="button" class="lnd-menu__close" data-menu-close><span class="sr-only">Cerrar menú</span></button>
        </div>
        <ol>
            @foreach ($sections as $i => $s)
                <li style="--i: {{ $i }}"><a href="#{{ $s['id'] }}"><small>{{ $s['n'] }}</small>{{ $s['label'] }}</a></li>
            @endforeach
        </ol>
        @auth
            <a href="{{ auth()->user()->isPatient() ? route('portal.index') : route('inicio') }}" class="lnd-link lnd-menu__cta">{{ auth()->user()->isPatient() ? 'Mi portal' : 'Panel' }} {!! $arrow !!}</a>
        @else
            <a href="{{ route('login') }}" class="lnd-link lnd-menu__cta">Acceder al portal {!! $arrow !!}</a>
        @endauth
        <div class="lnd-menu__foot">
            <span class="meta">Dónde estamos</span>
            {{ $clinic['address'] }}<br>{{ $clinic['address_detail'] }}<br>{{ $clinic['hours'] }}
        </div>
    </div>

    <main id="inicio">

        {{-- ===================== Hero ===================== --}}
        <section class="lnd-hero" aria-labelledby="hero-title">
            <div class="lnd-hero__media">
                <img src="{{ $img('hero_image')['url'] }}" alt="{{ $img('hero_image')['alt'] }}" style="object-position: {{ $img('hero_image')['focus'] }}" fetchpriority="high">
            </div>

            <div class="lnd-hero__content">
                <p class="lnd-hero__kicker label">Psicología clínica <span aria-hidden="true">·</span> {{ $clinic['city'] }}</p>

                <h1 id="hero-title" class="lnd-hero__title display">
                    Un lugar para<br>volver a <em class="accent">ti.</em>
                </h1>

                <div class="lnd-hero__side">
                    <p>Psicoterapia para adultos y parejas en un espacio privado y luminoso. Sin prisas, sin juicios, con el tiempo que cada proceso necesita.</p>
                    <a href="#enfoque" class="lnd-link">Conocer el enfoque {!! $arrowDown !!}</a>
                </div>

                <div class="lnd-hero__foot">
                    <span class="meta">{{ $clinic['address_detail'] }}</span>
                    <a href="#enfoque" class="lnd-scroll meta" aria-label="Desplazarse al contenido">
                        Desliza <span class="lnd-scroll__line" aria-hidden="true"></span>
                    </a>
                </div>
            </div>
        </section>

        {{-- ===================== 00 · Manifiesto ===================== --}}
        <section id="enfoque" class="lnd-section lnd-manifesto" aria-labelledby="manifesto-title">
            <div class="wrap lnd-manifesto__grid">
                <p class="label" data-reveal><span class="num">00</span><span class="rule"></span>Una forma de acompañar</p>

                <p class="lnd-manifesto__lead" data-reveal style="--d:.1s">
                    No venimos a corregirte.<br>Venimos a escucharte.
                </p>

                <h2 id="manifesto-title" class="lnd-manifesto__title display" data-reveal style="--d:.15s">
                    Cada proceso merece <em class="accent">tiempo</em>, <em class="accent">escucha</em> y una mirada que no juzga.
                </h2>

                <div class="lnd-manifesto__body" data-reveal style="--d:.2s">
                    <p>Entendemos la terapia como un encuentro entre personas. Un lugar donde puedes llegar como estás, sin tener que explicarlo todo desde el primer día.</p>
                    <p>Trabajamos con enfoques contrastados y con algo igual de importante: la calma, la constancia y el respeto por tus tiempos.</p>
                </div>
            </div>
        </section>

        {{-- ===================== 01 · Servicios ===================== --}}
        <section id="servicios" class="lnd-section lnd-services" aria-labelledby="services-title" data-services>
            <div class="wrap">
                <header class="lnd-head">
                    <p class="label" data-reveal><span class="num">01</span><span class="rule"></span>Servicios</p>
                    <h2 id="services-title" class="lnd-head__title display" data-reveal style="--d:.1s">
                        Acompañamiento <em>a medida</em>
                    </h2>
                </header>

                <div class="lnd-services__grid">
                    <div class="lnd-services__media" data-reveal>
                        <div class="lnd-services__frame">
                            @foreach ($services as $i => $s)
                                <img src="{{ $img($s['slot'])['url'] }}" alt="{{ $img($s['slot'])['alt'] }}" style="object-position: {{ $img($s['slot'])['focus'] }}" loading="lazy" data-service-image class="{{ $i === 0 ? 'is-active' : '' }}">
                            @endforeach
                        </div>
                        <div class="lnd-services__caption meta">
                            <span data-service-caption>{{ $services[0]['name'] }}</span>
                            <span>{{ $clinic['name'] }} · {{ $clinic['city'] }}</span>
                        </div>
                    </div>

                    <ol class="lnd-services__list">
                        @foreach ($services as $i => $s)
                            <li class="lnd-service {{ $i === 0 ? 'is-active' : '' }}" data-service="{{ $s['name'] }}" data-reveal style="--d:{{ $i * 0.08 }}s">
                                <a href="{{ $bookingUrl }}">
                                    <span class="lnd-service__num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <h3 class="lnd-service__name">{{ $s['name'] }}</h3>
                                    <span class="lnd-service__arrow">{!! $arrow !!}</span>
                                    <p class="lnd-service__desc"><span>{{ $s['desc'] }}</span></p>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </section>

        {{-- ===================== 02 · Nosotros ===================== --}}
        <section id="nosotros" class="lnd-about" aria-labelledby="about-title">
            <div class="wrap lnd-about__grid">
                <div class="lnd-about__media">
                    <img src="{{ $img('about_image')['url'] }}" alt="{{ $img('about_image')['alt'] }}" style="object-position: {{ $img('about_image')['focus'] }}" loading="lazy">
                    <span class="meta">La sala · luz de mañana</span>
                </div>

                <div class="lnd-about__content">
                    <p class="label" data-reveal><span class="num">02</span><span class="rule"></span>Nosotros</p>

                    <h2 id="about-title" class="lnd-about__title display" data-reveal style="--d:.1s">
                        Presencia antes que <em class="accent">distancia.</em>
                    </h2>

                    <div class="lnd-about__text" data-reveal style="--d:.15s">
                        <p>Somos un equipo reducido, a propósito. Preferimos conocer bien a cada persona que acompañamos y cuidar los detalles de cada proceso.</p>
                        <p>Psicólogas con experiencia clínica y en formación continua, que comparten un mismo criterio: la terapia es, antes que nada, un encuentro humano.</p>
                    </div>

                    <ul class="lnd-about__list" data-reveal style="--d:.2s">
                        <li>Equipo pequeño y estable</li>
                        <li>Supervisión clínica entre colegas</li>
                        <li>Formación continua en enfoques contrastados</li>
                    </ul>

                    <div class="lnd-facts">
                        <div class="lnd-fact" data-reveal>
                            <p class="lnd-fact__value">12<sup>+</sup></p>
                            <p class="lnd-fact__note">años acompañando procesos</p>
                        </div>
                        <div class="lnd-fact" data-reveal style="--d:.1s">
                            <p class="lnd-fact__value">03</p>
                            <p class="lnd-fact__note">especialistas en el equipo</p>
                        </div>
                        <div class="lnd-fact" data-reveal style="--d:.2s">
                            <p class="lnd-fact__value">01</p>
                            <p class="lnd-fact__note">criterio común de atención</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===================== 03 · El espacio ===================== --}}
        <section id="espacio" class="lnd-section lnd-space" aria-labelledby="space-title">
            <div class="wrap">
                <header class="lnd-head">
                    <p class="label" data-reveal><span class="num">03</span><span class="rule"></span>El espacio</p>
                    <h2 id="space-title" class="lnd-head__title display" data-reveal style="--d:.1s">
                        Diseñado para bajar el <em class="accent">ruido.</em>
                    </h2>
                    <p class="lnd-head__text" data-reveal style="--d:.15s">
                        Luz natural, materiales cálidos y aislamiento acústico. Un lugar en el que es fácil imaginarse estando, pensado para que lo único importante sea la conversación.
                    </p>
                </header>

                <div class="lnd-gallery">
                    @php
                        $figures = [
                            ['slot' => 'space_1', 'mod' => 'wide', 'title' => 'Umbral', 'text' => 'La llegada y la transición: dejar fuera la prisa.'],
                            ['slot' => 'space_2', 'mod' => 'narrow', 'title' => 'Materia', 'text' => 'Texturas, superficies y contacto.'],
                            ['slot' => 'space_3', 'mod' => 'tall', 'title' => 'Intimidad', 'text' => 'Comodidad, silencio y privacidad.'],
                        ];
                    @endphp
                    @foreach ($figures as $i => $f)
                        <figure class="lnd-figure lnd-figure--{{ $f['mod'] }}" data-reveal style="--d:{{ $i * 0.1 }}s">
                            <div class="lnd-figure__img">
                                <img src="{{ $img($f['slot'])['url'] }}" alt="{{ $img($f['slot'])['alt'] }}" style="object-position: {{ $img($f['slot'])['focus'] }}" loading="lazy">
                            </div>
                            <figcaption>
                                <span class="num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <strong>{{ $f['title'] }}</strong>
                                <span>{{ $f['text'] }}</span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ===================== 04 · Contacto ===================== --}}
        <section id="contacto" class="lnd-section lnd-contact" aria-labelledby="contact-title">
            <div class="wrap lnd-contact__grid">
                <p class="label" data-reveal><span class="num">04</span><span class="rule"></span>Contacto</p>

                <h2 id="contact-title" class="lnd-contact__title display" data-reveal style="--d:.1s">
                    A veces, empezar es simplemente <em class="accent">hablar.</em>
                </h2>

                <div class="lnd-contact__body" data-reveal style="--d:.15s">
                    <p>La primera conversación es breve y sin compromiso. Nos cuentas qué te trae y te orientamos sobre el acompañamiento que mejor encaja contigo.</p>

                    <ul class="lnd-contact__points">
                        <li><strong>20 minutos</strong><span>Primera conversación, aproximadamente.</span></li>
                        <li><strong>&lt; 24 h</strong><span>Respuesta en horario laborable.</span></li>
                        <li><strong>Personal</strong><span>Te responde una persona del equipo.</span></li>
                        <li><strong>Confidencial</strong><span>Lo que compartes queda entre nosotras.</span></li>
                    </ul>

                    <div class="lnd-contact__actions">
                        <a href="{{ $bookingUrl }}" class="lnd-btn">Solicitar una cita {!! $arrow !!}</a>
                        <a href="{{ $waHref }}" class="lnd-link" target="_blank" rel="noopener">Escribir por WhatsApp {!! $arrow !!}</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- ===================== Footer ===================== --}}
    <footer class="lnd-footer">
        <div class="wrap">
            <div class="lnd-footer__grid">
                <div class="lnd-footer__brand">
                    <p class="lnd-brand__name">{{ $clinic['name'] }}.</p>
                    <p>{{ $clinic['tagline'] }} · {{ $clinic['city'] }}</p>
                </div>

                <div class="lnd-footer__col">
                    <h3>Contacto</h3>
                    <ul>
                        <li><a href="{{ $telHref }}">{{ $clinic['phone'] }}</a></li>
                        <li><a href="{{ $waHref }}" target="_blank" rel="noopener">WhatsApp</a></li>
                        <li><a href="mailto:{{ $clinic['email'] }}">{{ $clinic['email'] }}</a></li>
                    </ul>
                </div>

                <div class="lnd-footer__col lnd-footer__col--wide">
                    <h3>Visítanos</h3>
                    <p>{{ $clinic['address'] }}<br><span class="muted">{{ $clinic['address_detail'] }}</span></p>
                    <p class="muted" style="margin-top:.75rem">{{ $clinic['hours'] }}</p>
                </div>

                <div class="lnd-footer__col">
                    <h3>Síguenos</h3>
                    <ul>
                        <li><a href="{{ $clinic['instagram'] }}" target="_blank" rel="noopener">Instagram</a></li>
                        <li><a href="{{ $clinic['linkedin'] }}" target="_blank" rel="noopener">LinkedIn</a></li>
                    </ul>
                </div>
            </div>

            <div class="lnd-footer__bottom">
                <span>&copy; {{ date('Y') }} {{ $clinic['name'] }} · {{ $clinic['tagline'] }}</span>
                <nav aria-label="Legal">
                    <a href="#">Aviso legal</a>
                    <a href="#">Privacidad</a>
                </nav>
                <a href="#inicio" class="lnd-top">Volver al inicio {!! $arrowUp !!}</a>
            </div>
        </div>
    </footer>

</body>

</html>
