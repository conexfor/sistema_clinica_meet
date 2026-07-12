{{--
    Modal de confirmación reutilizable.
    Cualquier <form data-confirm="mensaje"> abre este modal en vez del confirm() del navegador.
    Atributos opcionales del form:
      - data-confirm-ok="Etiqueta del botón"
      - data-confirm-variant="danger"  (botón rojo para acciones destructivas)
--}}
<div id="confirmModal" class="fixed inset-0 z-[120] hidden" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
    <div class="absolute inset-0 bg-sage-950/60 backdrop-blur-sm" data-confirm-close></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-4">
        <div class="surface p-8 text-center">
            <div id="confirmModalIcon" class="w-16 h-16 rounded-2xl bg-sage-100 text-sage-600 flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 id="confirmModalTitle" class="font-serif text-xl text-sage-900 mb-2">Confirmar acción</h3>
            <p id="confirmModalMessage" class="text-sm text-ink-500 mb-7">¿Estás seguro?</p>
            <div class="flex gap-3">
                <button type="button" data-confirm-close class="flex-1 btn bg-cream-200 text-ink-700 hover:bg-cream-300 py-3">Cancelar</button>
                <button type="button" id="confirmModalOk" class="flex-1 btn-primary py-3">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modal   = document.getElementById('confirmModal');
    const message = document.getElementById('confirmModalMessage');
    const okBtn   = document.getElementById('confirmModalOk');
    const iconBox = document.getElementById('confirmModalIcon');
    let pendingForm = null;

    function closeModal() {
        modal.classList.add('hidden');
        pendingForm = null;
    }

    function openModal(form) {
        pendingForm = form;
        message.textContent = form.dataset.confirm || '¿Estás seguro?';
        okBtn.textContent   = form.dataset.confirmOk || 'Confirmar';

        const danger = form.dataset.confirmVariant === 'danger';
        okBtn.className  = 'flex-1 py-3 ' + (danger ? 'btn bg-red-600 text-white hover:bg-red-700' : 'btn-primary');
        iconBox.className = 'w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-5 ' +
            (danger ? 'bg-red-100 text-red-600' : 'bg-sage-100 text-sage-600');

        modal.classList.remove('hidden');
        okBtn.focus();
    }

    // Interceptar el envío de cualquier formulario con data-confirm
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form && form.dataset && form.dataset.confirm) {
            e.preventDefault();
            openModal(form);
        }
    }, true);

    okBtn.addEventListener('click', function () {
        const form = pendingForm;
        closeModal();
        if (form) form.submit(); // submit() nativo no vuelve a disparar el evento 'submit'
    });

    modal.querySelectorAll('[data-confirm-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
})();
</script>
