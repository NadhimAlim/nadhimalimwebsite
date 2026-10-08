(() => {
    const makeLayer = (className, content) => {
        const layer = document.createElement('div');
        layer.className = `feedback-backdrop ${className}`;
        layer.innerHTML = content;
        document.body.append(layer);
        return layer;
    };

    const confirmLayer = makeLayer('feedback-confirm-backdrop', `
        <section class="feedback-dialog" role="dialog" aria-modal="true" aria-labelledby="feedback-confirm-title" aria-describedby="feedback-confirm-message">
            <div class="feedback-dialog-icon"><i class="bi bi-question-lg"></i></div>
            <h2 id="feedback-confirm-title">Konfirmasi tindakan</h2>
            <p id="feedback-confirm-message"></p>
            <div class="feedback-dialog-actions"><button type="button" data-cancel-action>Batal</button><button type="button" data-confirm-action>Lanjutkan</button></div>
        </section>`);
    const loadingLayer = makeLayer('feedback-loading-backdrop', `
        <section class="feedback-loading-card" role="status" aria-live="polite" aria-atomic="true">
            <div class="feedback-spinner" aria-hidden="true"></div><strong data-loading-title>Memproses...</strong><p data-loading-description>Mohon tunggu sebentar.</p>
        </section>`);

    let pendingForm = null;
    const closeConfirm = () => {
        confirmLayer.classList.remove('is-visible');
        pendingForm = null;
    };
    const showLoading = (form) => {
        const title = loadingLayer.querySelector('[data-loading-title]');
        const description = loadingLayer.querySelector('[data-loading-description]');
        const method = (form.querySelector('input[name="_method"]')?.value || form.method || 'GET').toUpperCase();
        const isUpload = form.enctype === 'multipart/form-data' && Array.from(form.querySelectorAll('input[type="file"]')).some((input) => input.files?.length);
        const isDelete = method === 'DELETE';
        title.textContent = form.dataset.loadingTitle || (isDelete ? 'Menghapus data...' : isUpload ? 'Mengunggah file...' : 'Menyimpan perubahan...');
        description.textContent = form.dataset.loadingDescription || 'Mohon tunggu sebentar.';
        loadingLayer.classList.add('is-visible');
        form.querySelectorAll('button[type="submit"],input[type="submit"]').forEach((button) => { button.disabled = true; });
    };

    confirmLayer.querySelector('[data-cancel-action]').addEventListener('click', closeConfirm);
    confirmLayer.querySelector('[data-confirm-action]').addEventListener('click', () => {
        if (!pendingForm) return;
        const form = pendingForm;
        form.dataset.feedbackConfirmed = 'true';
        closeConfirm();
        form.requestSubmit();
    });
    confirmLayer.addEventListener('click', (event) => { if (event.target === confirmLayer) closeConfirm(); });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && confirmLayer.classList.contains('is-visible')) closeConfirm(); });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.confirm && form.dataset.feedbackConfirmed !== 'true') {
            event.preventDefault();
            pendingForm = form;
            confirmLayer.querySelector('#feedback-confirm-message').textContent = form.dataset.confirm;
            confirmLayer.classList.add('is-visible');
            confirmLayer.querySelector('[data-confirm-action]').focus();
            return;
        }
        delete form.dataset.feedbackConfirmed;
        if (form.dataset.ajaxForm !== 'true') showLoading(form);
    }, true);

    window.addEventListener('pageshow', () => loadingLayer.classList.remove('is-visible'));
})();
