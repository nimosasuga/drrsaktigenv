// PATH FILE: resources/js/update-job-extra-fields.js

function isUpdateJobFormPage() {
    return /^\/update-jobs\/(create|\d+\/edit)$/.test(window.location.pathname);
}

function isEditPage() {
    return /^\/update-jobs\/\d+\/edit$/.test(window.location.pathname);
}

function getEditJobId() {
    const match = window.location.pathname.match(/^\/update-jobs\/(\d+)\/edit$/);
    return match ? match[1] : null;
}

function makeEditableExtraInput({ name, id, label, value }) {
    const wrapper = document.createElement('div');
    wrapper.dataset.updateJobExtraField = id;
    wrapper.innerHTML = `
        <label for="${id}" class="block text-xs font-medium text-slate-700 mb-1">${label}</label>
        <input type="text" name="${name}" id="${id}" value="${value || ''}"
            placeholder="Bisa otomatis dari asset / bisa isi manual"
            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
    `;

    return wrapper;
}

function insertExtraFields() {
    if (!isUpdateJobFormPage() || document.getElementById('nomor_lambung')) {
        return;
    }

    const unitTypeInput = document.getElementById('unit_type');
    if (!unitTypeInput) {
        return;
    }

    const unitTypeWrapper = unitTypeInput.closest('div');
    if (!unitTypeWrapper || !unitTypeWrapper.parentElement) {
        return;
    }

    const nomorLambungField = makeEditableExtraInput({
        name: 'nomor_lambung',
        id: 'nomor_lambung',
        label: 'Nomor Lambung',
        value: '',
    });

    const yearField = makeEditableExtraInput({
        name: 'year',
        id: 'year',
        label: 'Year',
        value: '',
    });

    unitTypeWrapper.insertAdjacentElement('afterend', yearField);
    unitTypeWrapper.insertAdjacentElement('afterend', nomorLambungField);
}

function setInputValue(input, value, { force = false, allowBlank = false } = {}) {
    if (!input) {
        return;
    }

    const normalizedValue = String(value || '').trim();

    if (!allowBlank && normalizedValue === '') {
        return;
    }

    if (force || input.value.trim() === '') {
        input.value = normalizedValue;
    }
}

function fillExtraFields(data, options = {}) {
    const nomorLambungInput = document.getElementById('nomor_lambung');
    const yearInput = document.getElementById('year');

    setInputValue(nomorLambungInput, data.nomor_lambung, options);
    setInputValue(yearInput, data.year, options);
}

async function loadEditJobExtraFields() {
    const id = getEditJobId();
    if (!id) {
        return;
    }

    try {
        const response = await fetch(`/update-jobs/${id}/extra-fields`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        fillExtraFields(data, { force: true, allowBlank: true });
    } catch (error) {
        console.error(error);
    }
}

async function loadAssetExtraFields(serialNumber, { force = false } = {}) {
    if (!serialNumber) {
        return;
    }

    try {
        const response = await fetch(`/update-jobs/extra-fields/asset?serial_number=${encodeURIComponent(serialNumber)}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        fillExtraFields(data, { force, allowBlank: false });
    } catch (error) {
        console.error(error);
    }
}

function observeSerialNumberSelection() {
    const serialNumberInput = document.getElementById('serial_number');
    if (!serialNumberInput) {
        return;
    }

    const initialValue = serialNumberInput.value.trim();
    let previousValue = serialNumberInput.value;

    const maybeLoadAssetFields = () => {
        const currentValue = serialNumberInput.value.trim();
        if (!currentValue) {
            return;
        }

        const serialChangedFromInitial = currentValue !== initialValue;
        const shouldForce = !isEditPage() || serialChangedFromInitial;

        loadAssetExtraFields(currentValue, { force: shouldForce });
    };

    serialNumberInput.addEventListener('change', maybeLoadAssetFields);
    serialNumberInput.addEventListener('blur', maybeLoadAssetFields);

    setInterval(() => {
        const currentValue = serialNumberInput.value;
        if (currentValue !== previousValue) {
            previousValue = currentValue;
            maybeLoadAssetFields();
        }
    }, 500);
}

document.addEventListener('DOMContentLoaded', () => {
    insertExtraFields();
    loadEditJobExtraFields();
    observeSerialNumberSelection();
});
