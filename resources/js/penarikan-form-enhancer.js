// PATH FILE: resources/js/penarikan-form-enhancer.js

document.addEventListener('DOMContentLoaded', () => {
    const isCreatePage = window.location.pathname === '/penarikans/create';
    const draftKey = 'drrsakti:penarikan:create:draft';
    const pendingClearKey = 'drrsakti:penarikan:create:pending-clear';

    if (!isCreatePage && localStorage.getItem(pendingClearKey) === '1') {
        localStorage.removeItem(draftKey);
        localStorage.removeItem(pendingClearKey);
    }

    const form = document.querySelector('form[action*="penarikans"]');
    const serialInput = form?.querySelector('input[name="serial_number"]');

    if (!form || !serialInput) {
        return;
    }

    let isSubmitting = false;

    const findInput = (name) => form.querySelector(`[name="${name}"]`);
    const autoFields = ['customer', 'location', 'unit_type', 'year', 'hour_meter'];

    autoFields.forEach((name) => {
        const input = findInput(name);
        if (!input) return;
        input.readOnly = true;
        input.classList.add('bg-slate-50', 'cursor-not-allowed');
        input.setAttribute('tabindex', '-1');
    });

    const setValue = (name, value) => {
        const input = findInput(name);
        if (input) input.value = value || '';
    };

    const createNotice = (message, type = 'info') => {
        let notice = document.getElementById('penarikan-draft-notice');
        if (!notice) {
            notice = document.createElement('div');
            notice.id = 'penarikan-draft-notice';
            form.parentElement?.insertBefore(notice, form);
        }

        notice.className = type === 'warning'
            ? 'mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-800 shadow-sm'
            : 'mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-700 shadow-sm';
        notice.textContent = message;
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
        if (!isCreatePage || isSubmitting) return;

        const data = getDraftData();
        if (!hasMeaningfulData(data)) return;

        localStorage.setItem(draftKey, JSON.stringify({
            saved_at: new Date().toISOString(),
            data,
        }));
    };

    const restoreDraft = () => {
        if (!isCreatePage) return;

        const rawDraft = localStorage.getItem(draftKey);
        if (!rawDraft) return;

        let draft = null;
        try {
            draft = JSON.parse(rawDraft);
        } catch (error) {
            localStorage.removeItem(draftKey);
            return;
        }

        if (!draft?.data || !hasMeaningfulData(draft.data)) return;

        Object.entries(draft.data).forEach(([key, value]) => {
            const field = findInput(key);
            if (field) field.value = value ?? '';
        });

        createNotice('Progres form Penarikan Unit berhasil dipulihkan dari penyimpanan lokal browser.');
    };

    const createDropdown = () => {
        let dropdown = document.getElementById('penarikan-sn-suggest');
        if (dropdown) return dropdown;

        dropdown = document.createElement('div');
        dropdown.id = 'penarikan-sn-suggest';
        dropdown.className = 'absolute z-50 mt-2 hidden max-h-72 w-full overflow-y-auto rounded-2xl border border-rose-100 bg-white shadow-2xl';

        const wrapper = serialInput.parentElement;
        if (wrapper) {
            wrapper.classList.add('relative');
            wrapper.appendChild(dropdown);
        }

        return dropdown;
    };

    const dropdown = createDropdown();

    const hideDropdown = () => {
        dropdown.classList.add('hidden');
        dropdown.innerHTML = '';
    };

    const renderItems = (items) => {
        dropdown.innerHTML = '';

        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'px-4 py-3 text-sm font-bold text-slate-500';
            empty.textContent = 'Serial number tidak ditemukan.';
            dropdown.appendChild(empty);
            dropdown.classList.remove('hidden');
            return;
        }

        items.forEach((item) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'block w-full border-b border-slate-100 px-4 py-3 text-left hover:bg-rose-50';
            button.innerHTML = `
                <div class="text-sm font-black text-slate-900">${item.serial_number || '-'}</div>
                <div class="mt-1 text-xs font-semibold text-slate-500">${item.customer || '-'} / ${item.location || '-'} / ${item.unit_type || '-'}</div>
            `;
            button.addEventListener('click', () => {
                serialInput.value = item.serial_number || '';
                setValue('customer', item.customer);
                setValue('location', item.location);
                setValue('unit_type', item.unit_type);
                setValue('year', item.year);
                setValue('hour_meter', item.hour_meter);
                hideDropdown();
                saveDraft();
            });
            dropdown.appendChild(button);
        });

        dropdown.classList.remove('hidden');
    };

    let timer = null;
    serialInput.addEventListener('input', () => {
        clearTimeout(timer);
        const q = serialInput.value.trim();

        autoFields.forEach((name) => setValue(name, ''));

        if (!q) {
            hideDropdown();
            saveDraft();
            return;
        }

        timer = setTimeout(async () => {
            try {
                const response = await fetch(`/penarikans/search-assets?q=${encodeURIComponent(q)}`);
                if (!response.ok) throw new Error('Search failed');
                const items = await response.json();
                renderItems(Array.isArray(items) ? items : []);
            } catch (error) {
                renderItems([]);
            }
        }, 180);

        saveDraft();
    });

    document.addEventListener('click', (event) => {
        if (!dropdown.contains(event.target) && event.target !== serialInput) {
            hideDropdown();
        }
    });

    const fieldClass = 'w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase';
    const extraSection = document.createElement('section');
    extraSection.className = 'rounded-3xl border border-rose-100 bg-white p-5 shadow-sm sm:p-6';
    extraSection.innerHTML = `
        <h2 class="mb-5 text-sm font-black uppercase tracking-wider text-slate-800">Tambahan Lapangan</h2>
        <div class="grid gap-5 md:grid-cols-2">
            <div><label class="mb-1 block text-xs font-bold text-slate-600">Battery Type 2</label><input type="text" name="battery_type_2" class="${fieldClass}" placeholder="Opsional untuk unit 2 battery"></div>
            <div><label class="mb-1 block text-xs font-bold text-slate-600">Battery SN 2</label><input type="text" name="battery_sn_2" class="${fieldClass}" placeholder="Opsional untuk unit 2 battery"></div>
            <div><label class="mb-1 block text-xs font-bold text-slate-600">Trolly 2</label><input type="text" name="trolly_2" class="${fieldClass}" placeholder="Opsional"></div>
            <div><label class="mb-1 block text-xs font-bold text-slate-600">Trolly 3</label><input type="text" name="trolly_3" class="${fieldClass}" placeholder="Opsional"></div>
        </div>
    `;

    const submitArea = form.querySelector('button[type="submit"]')?.closest('.flex');
    if (submitArea && !form.querySelector('[name="battery_type_2"]')) {
        form.insertBefore(extraSection, submitArea);
    }

    restoreDraft();

    let draftTimer = null;
    form.addEventListener('input', () => {
        clearTimeout(draftTimer);
        draftTimer = setTimeout(saveDraft, 250);
    });

    form.addEventListener('change', () => {
        clearTimeout(draftTimer);
        draftTimer = setTimeout(saveDraft, 100);
    });

    form.addEventListener('submit', () => {
        isSubmitting = true;
        localStorage.setItem(pendingClearKey, '1');
    });

    document.querySelectorAll('a[href]').forEach((link) => {
        link.addEventListener('click', () => {
            if (!isCreatePage || isSubmitting) return;
            saveDraft();
            if (hasMeaningfulData(getDraftData())) {
                createNotice('Progres Penarikan Unit sudah disimpan otomatis. Jika kembali ke form ini, data akan dipulihkan.', 'warning');
            }
        });
    });

    window.addEventListener('beforeunload', (event) => {
        if (!isCreatePage || isSubmitting) return;
        if (!hasMeaningfulData(getDraftData())) return;

        saveDraft();
        event.preventDefault();
        event.returnValue = 'Progres form Penarikan Unit sudah disimpan otomatis. Tetap keluar halaman?';
    });

    form.querySelectorAll('input.uppercase, textarea.uppercase').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.toUpperCase();
        });
    });
});
