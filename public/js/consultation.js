(() => {
    const dialog = document.getElementById('consultation-dialog');
    const form = document.getElementById('consultation-form');
    if (!dialog || !form) return;

    const status = dialog.querySelector('[data-consultation-status]');
    const serviceSelect = form.querySelector('[name="service"]');
    const submitButton = form.querySelector('button[type="submit"]');
    const buttonLabel = form.querySelector('[data-consultation-button]');
    const fallbackLink = dialog.querySelector('[data-wa-fallback]');
    const closeButton = dialog.querySelector('[data-consultation-close]');
    let lastTrigger = null;

    document.querySelectorAll('[data-consultation-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            lastTrigger = trigger;
            status.textContent = '';
            status.classList.remove('is-error');
            fallbackLink.hidden = true;
            form.reset();
            const packageName = trigger.dataset.package;
            if (packageName && [...serviceSelect.options].some((option) => option.value === packageName)) serviceSelect.value = packageName;
            dialog.showModal();
            form.querySelector('[name="name"]').focus({ preventScroll: true });
        });
    });

    const closeDialog = () => dialog.close();
    closeButton.addEventListener('click', closeDialog);
    dialog.addEventListener('click', (event) => { if (event.target === dialog) closeDialog(); });
    dialog.addEventListener('close', () => lastTrigger?.focus({ preventScroll: true }));

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        status.textContent = '';
        status.classList.remove('is-error');
        fallbackLink.hidden = true;

        // Open synchronously in the submit gesture so browsers do not block the WhatsApp tab.
        const whatsappTab = window.open('about:blank', '_blank');
        if (whatsappTab) whatsappTab.opener = null;
        submitButton.disabled = true;
        buttonLabel.textContent = 'Mengirim permintaan...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) {
                whatsappTab?.close();
                const firstError = Object.values(result.errors || {}).flat()[0];
                throw new Error(firstError || 'Permintaan belum berhasil dikirim. Periksa koneksi lalu coba lagi.');
            }

            form.reset();
            if (result.whatsapp_url) {
                if (whatsappTab) {
                    whatsappTab.location.href = result.whatsapp_url;
                    status.textContent = 'Permintaan tersimpan di dashboard admin. WhatsApp terbuka dengan pesan yang siap dikirim.';
                } else {
                    fallbackLink.href = result.whatsapp_url;
                    fallbackLink.hidden = false;
                    status.textContent = 'Permintaan tersimpan di dashboard admin. Buka WhatsApp untuk mengirim detail konsultasi.';
                }
            } else {
                whatsappTab?.close();
                status.textContent = 'Permintaan sudah tersimpan di dashboard admin. Nomor WhatsApp admin belum diatur; admin dapat mengisinya dari menu Profil admin.';
            }
        } catch (error) {
            whatsappTab?.close();
            status.textContent = error.message || 'Terjadi kendala. Silakan coba lagi.';
            status.classList.add('is-error');
            dialog.querySelector('[aria-invalid="true"]')?.focus();
        } finally {
            submitButton.disabled = false;
            buttonLabel.textContent = 'Ajukan konsultasi';
        }
    });
})();
