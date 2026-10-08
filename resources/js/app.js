import './bootstrap';

/**
 * Parallax ligero basado en scroll + movimiento del mouse.
 * Solo se activa si la página tiene elementos [data-parallax] (landing pages),
 * así que no afecta al resto del panel. Respeta prefers-reduced-motion.
 */
(function () {
    const scrollEls = document.querySelectorAll('[data-parallax]');
    const mouseEls = document.querySelectorAll('[data-parallax-mouse]');
    if (!scrollEls.length && !mouseEls.length) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion) return;

    let ticking = false;

    function updateScroll() {
        const y = window.scrollY;
        scrollEls.forEach(function (el) {
            const speed = parseFloat(el.dataset.parallax) || 0.15;
            el.style.transform = 'translate3d(0, ' + (y * speed) + 'px, 0)';
        });
        ticking = false;
    }

    window.addEventListener('scroll', function () {
        if (!ticking) {
            window.requestAnimationFrame(updateScroll);
            ticking = true;
        }
    }, { passive: true });

    updateScroll();

    if (mouseEls.length) {
        window.addEventListener('mousemove', function (e) {
            const cx = e.clientX / window.innerWidth - 0.5;
            const cy = e.clientY / window.innerHeight - 0.5;
            mouseEls.forEach(function (el) {
                const strength = parseFloat(el.dataset.parallaxMouse) || 18;
                el.style.transform = 'translate3d(' + (cx * strength) + 'px, ' + (cy * strength) + 'px, 0)';
            });
        }, { passive: true });
    }
})();

/**
 * Guard global anti doble-envío.
 * Evita que un formulario se envíe varias veces por clicks repetidos o doble
 * Enter. Al primer submit válido, marca el formulario y deshabilita sus botones.
 *
 * - Se ejecuta en fase de captura para adelantarse a otros handlers.
 * - Deshabilita los botones en un setTimeout(0) para no perder su name/value
 *   en el envío que sí debe pasar.
 * - Un formulario puede excluirse agregando el atributo `data-allow-multiple`.
 */
document.addEventListener(
    'submit',
    function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.hasAttribute('data-allow-multiple')) return;

        // Los formularios con confirmación los maneja confirm-modal.js: abre un
        // modal (preventDefault) y sólo envía al confirmar, por lo que ya son
        // seguros ante doble envío. Si los bloqueáramos aquí, al cancelar el
        // modal el botón quedaría deshabilitado para siempre.
        if (form.dataset.confirm) return;

        // Ya se está enviando: bloquear cualquier envío extra.
        if (form.dataset.submitting === 'true') {
            e.preventDefault();
            e.stopPropagation();
            return;
        }

        form.dataset.submitting = 'true';

        const buttons = form.querySelectorAll(
            'button[type="submit"], input[type="submit"], button:not([type])'
        );
        setTimeout(function () {
            buttons.forEach(function (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-60', 'cursor-not-allowed');
            });
        }, 0);
    },
    true
);

/**
 * Al volver con el botón "atrás" (bfcache), restaurar el estado de los
 * formularios para que sus botones no queden deshabilitados.
 */
window.addEventListener('pageshow', function (event) {
    if (!event.persisted) return;
    document.querySelectorAll('form[data-submitting="true"]').forEach(function (form) {
        form.dataset.submitting = 'false';
        form.querySelectorAll('button, input[type="submit"]').forEach(function (btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-60', 'cursor-not-allowed');
        });
    });
});

/**
 * Toggle de visibilidad de contraseñas para inputs con `data-password-toggle`.
 */
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-password-toggle]');
    if (!btn) return;
    const selector = btn.getAttribute('data-password-toggle');
    const input = document.querySelector(selector);
    if (!input) return;

    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');

    if (isPassword) {
        btn.classList.add('text-sage-700');
    } else {
        btn.classList.remove('text-sage-700');
    }

    const showIcon = btn.querySelector('.password-show-icon');
    const hideIcon = btn.querySelector('.password-hide-icon');
    if (showIcon && hideIcon) {
        if (isPassword) {
            showIcon.classList.add('hidden');
            hideIcon.classList.remove('hidden');
        } else {
            showIcon.classList.remove('hidden');
            hideIcon.classList.add('hidden');
        }
    }
});
