// PATH FILE: resources/js/penarikan-form-draft.js

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action$="/penarikans"], form[action*="/penarikans/"]');
    const isCreatePage = window.location.pathname === '/penarikans/create';
    const draftKey = 'drrsakti:penarikan:create:draft';
    const pendingClearKey = 'drrsakti:penarikan:create:pending-clear';

    if (!isCreatePage) {
        if (localStorage.getItem(pendingClearKey) === '1') {
            localStorage.removeItem(draftKey);
            localStorage.removeItem(pendingClearKey);
        }
        return;
    }

    if (!form) {
        return;
    }

    const showDraftNotice = (message, type = 'info') => {
        if (document.getElementById('penarikan-draft-notice')) return;

        const notice = document.createElement('div');
        notice.id = 'penarikan-draft-notice';
        notice.className = type === 'warning'
            ? 'mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-800 shadow-sm'
            : 'mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-700 shadow-sm';
        notice.textContent = message;

        form.parentElement?.insertBefore(notice, form);
    };

    const getDraftData = () => {
        const formData = new FormData(form);
        const data = {};

        formData.forEach((value, key) => {
            if (key !== '_token' && key !== '_method') {
                data[key] = value;
            }
        });

        return data;
    };

    const hasMeaningfulData = (data) => {
        return Object.entries(data).some(([key, value]) => {
            if (['date', 'status_unit'].includes(key)) return false;
            return String(value || '').trim() !== '';
        });
    };

    const saveDraft = () => {
        const data = getDraftData();

        if (!hasMeaningfulData(data)) {
            return;
        }

        localStorage.setItem(draftKey, JSON.stringify({
            saved_at: new Date().toISOString(),
            data,
        }));
    };

    const restoreDraft = () => {
        const rawDraft = localStorage.getItem(draftKey);
        if (!rawDraft) return;

        let draft = null;
        try {
            draft = JSON.parse(rawDraft);
        } catch (error) {
            localStorage.removeItem(draftKey);
            return;
        }

        if (!draft?.data || !hasMeaningfulData(draft.data)) {
            return;
        }

        Object.entries(draft.data).forEach(([key, value]) => {
            const field = form.querySelector(`[name="${key}"]`);
            if (!field) return;
            field.value = value ?? '';
        });

        showDraftNotice('Progres form Penarikan Unit berhasil dipulihkan dari penyimpanan lokal browser.');
    };

    restoreDraft();

    let saveTimer = null;
    form.addEventListener('input', () => {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveDraft, 250);
    });

    form.addEventListener('change', () => {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveDraft, 100);
    });

    form.addEventListener('submit', () => {
        saveDraft();
        localStorage.setItem(pendingClearKey, '1');
    });

    document.querySelectorAll('a[href]').forEach((link) => {
        link.addEventListener('click', () => {
            saveDraft();
            if (hasMeaningfulData(getDraftData())) {
                showDraftNotice('Progres Penarikan Unit sudah disimpan otomatis. Jika kembali ke form ini, data akan dipulihkan.', 'warning');
            }
        });
    });

    window.addEventListener('beforeunload', (event) => {
        const data = getDraftData();
        if (!hasMeaningfulData(data)) return;

        saveDraft();
        event.preventDefault();
        event.returnValue = 'Progres form Penarikan Unit sudah disimpan otomatis. Tetap keluar halaman?';
    });
});
