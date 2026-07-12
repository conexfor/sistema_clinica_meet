import './bootstrap';

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
