// PATH FILE: resources/js/update-job-field-options.js

function normalizeLegacyValue(value) {
    const normalized = String(value || '').trim().toUpperCase();

    const map = {
        PM: 'Preventive Maintenance',
        BM: 'Troubleshooting',
        PDI: 'Inspection',
        'B/D': 'Breakdown',
        BD: 'Breakdown',
        BREAKDOWN: 'Breakdown',
        STANDBY: 'Monitoring',
    };

    return map[normalized] || value;
}

function replaceSelectOptions(select, placeholder, options) {
    if (!select) {
        return;
    }

    const currentValue = normalizeLegacyValue(select.value);
    select.innerHTML = '';

    const emptyOption = document.createElement('option');
    emptyOption.value = '';
    emptyOption.textContent = placeholder;
    select.appendChild(emptyOption);

    options.forEach((optionValue) => {
        const option = document.createElement('option');
        option.value = optionValue;
        option.textContent = optionValue;

        if (currentValue === optionValue) {
            option.selected = true;
        }

        select.appendChild(option);
    });
}

function standardizeUpdateJobFieldOptions() {
    const form = document.getElementById('form-job');

    if (!form) {
        return;
    }

    replaceSelectOptions(
        document.getElementById('job_type'),
        'Pilih Tipe Pekerjaan',
        [
            'Preventive Maintenance',
            'Install Part',
            'Troubleshooting',
            'Inspection',
            'Repair',
        ]
    );

    replaceSelectOptions(
        document.getElementById('status_unit'),
        'Pilih Status Akhir Unit',
        [
            'RFU',
            'Breakdown',
            'Monitoring',
            'Waiting Part',
        ]
    );
}

document.addEventListener('DOMContentLoaded', standardizeUpdateJobFieldOptions);
