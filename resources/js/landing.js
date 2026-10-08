/**
 * Interacciones de la landing pública.
 * Sin dependencias: navegación que se transforma, menú móvil, servicios con
 * crossfade de imagen y revelado progresivo. Respeta prefers-reduced-motion.
 */

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* --- Navegación: cambia de estado al salir del hero ---------------------- */
const nav = document.querySelector('[data-nav]');
if (nav) {
    const update = () => nav.classList.toggle('is-scrolled', window.scrollY > 40);
    update();
    window.addEventListener('scroll', update, { passive: true });
}

/* --- Menú móvil a pantalla completa -------------------------------------- */
const menu = document.querySelector('[data-menu]');
if (menu) {
    const openers = document.querySelectorAll('[data-menu-open]');

    const setOpen = (open) => {
        menu.classList.toggle('is-open', open);
        menu.setAttribute('aria-hidden', String(!open));
        menu.inert = !open;
        openers.forEach((btn) => btn.setAttribute('aria-expanded', String(open)));
        document.documentElement.style.overflow = open ? 'hidden' : '';
        if (open) menu.querySelector('[data-menu-close]')?.focus();
    };

    menu.inert = true;
    openers.forEach((btn) => btn.addEventListener('click', () => setOpen(true)));
    menu.querySelectorAll('[data-menu-close], a').forEach((el) => el.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menu.classList.contains('is-open')) {
            setOpen(false);
            openers[0]?.focus();
        }
    });
}

/* --- Servicios: el elemento activo cambia la fotografía ------------------ */
const services = document.querySelector('[data-services]');
if (services) {
    const items = services.querySelectorAll('[data-service]');
    const images = services.querySelectorAll('[data-service-image]');
    const caption = services.querySelector('[data-service-caption]');

    const activate = (index) => {
        items.forEach((item, i) => item.classList.toggle('is-active', i === index));
        images.forEach((img, i) => img.classList.toggle('is-active', i === index));
        if (caption) caption.textContent = items[index].dataset.service;
    };

    items.forEach((item, i) => {
        item.addEventListener('mouseenter', () => activate(i));
        item.addEventListener('focusin', () => activate(i));
    });
}

/* --- Revelado progresivo al hacer scroll --------------------------------- */
const revealEls = document.querySelectorAll('[data-reveal]');
if (reduceMotion || !('IntersectionObserver' in window)) {
    revealEls.forEach((el) => el.classList.add('is-in'));
} else {
    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-in');
                io.unobserve(entry.target);
            });
        },
        { rootMargin: '0px 0px -12% 0px', threshold: 0.08 }
    );
    revealEls.forEach((el) => io.observe(el));
}
