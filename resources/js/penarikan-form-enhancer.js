// PATH FILE: resources/js/penarikan-form-enhancer.js

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action*="penarikans"]');
    const serialInput = form?.querySelector('input[name="serial_number"]');

    if (!form || !serialInput) {
        return;
    }

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

    form.querySelectorAll('input.uppercase, textarea.uppercase').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.toUpperCase();
        });
    });
});
