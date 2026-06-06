// PATH FILE: resources/js/update-job-form-stabilizer.js

(function () {
    function normalizeStatus(value) {
        const normalized = String(value || '').trim().toUpperCase();

        if (normalized === 'B/D' || normalized === 'BD' || normalized === 'BREAKDOWN') {
            return 'Breakdown';
        }

        if (normalized === 'STANDBY') {
            return 'Monitoring';
        }

        if (normalized === 'RFU') {
            return 'RFU';
        }

        if (normalized === 'MONITORING') {
            return 'Monitoring';
        }

        if (normalized === 'WAITING PART') {
            return 'Waiting Part';
        }

        return String(value || '').trim();
    }

    function stabilizeStatusSelect(form) {
        const select = form.querySelector('#status_unit[name="status_unit"]');

        if (!select || select.tagName !== 'SELECT') {
            return;
        }

        const current = normalizeStatus(select.value);
        const selected = ['RFU', 'Breakdown', 'Monitoring', 'Waiting Part'].includes(current) ? current : 'RFU';

        select.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Pilih Status Akhir Unit';
        select.appendChild(placeholder);

        ['RFU', 'Breakdown', 'Monitoring', 'Waiting Part'].forEach((value) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            option.selected = value === selected;
            select.appendChild(option);
        });

        select.required = true;
        select.value = selected;
    }

    function appendSyncedHidden(form, name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value || '';
        input.setAttribute('data-update-job-stabilizer-sync', '1');
        form.appendChild(input);
    }

    function rowValue(row, selector) {
        const input = row.querySelector(selector);
        return input ? input.value : '';
    }

    function syncDynamicRows(form) {
        form.querySelectorAll('[data-update-job-stabilizer-sync="1"]').forEach((input) => input.remove());

        document.querySelectorAll('#inst-container .inst-item').forEach((row) => {
            appendSyncedHidden(form, 'safe_inst_id[]', rowValue(row, 'input[name="inst_id[]"]'));
            appendSyncedHidden(form, 'safe_inst_part_number[]', rowValue(row, 'input[name="inst_part_number[]"]'));
            appendSyncedHidden(form, 'safe_inst_part_name[]', rowValue(row, 'input[name="inst_part_name[]"]'));
            appendSyncedHidden(form, 'safe_inst_qty[]', rowValue(row, 'input[name="inst_qty[]"]'));
            appendSyncedHidden(form, 'safe_inst_no_job[]', rowValue(row, 'input[name="inst_no_job[]"]'));
            appendSyncedHidden(form, 'safe_inst_no_pr[]', rowValue(row, 'input[name="inst_no_pr[]"]'));
            appendSyncedHidden(form, 'safe_inst_remarks[]', rowValue(row, 'input[name="inst_remarks[]"]'));
        });

        document.querySelectorAll('#rec-container .rec-item').forEach((row) => {
            appendSyncedHidden(form, 'safe_rec_id[]', rowValue(row, 'input[name="rec_id[]"]'));
            appendSyncedHidden(form, 'safe_rec_part_number[]', rowValue(row, 'input[name="rec_part_number[]"]'));
            appendSyncedHidden(form, 'safe_rec_part_name[]', rowValue(row, 'input[name="rec_part_name[]"]'));
            appendSyncedHidden(form, 'safe_rec_qty[]', rowValue(row, 'input[name="rec_qty[]"]'));
            appendSyncedHidden(form, 'safe_rec_remarks[]', rowValue(row, 'input[name="rec_remarks[]"]'));
        });
    }

    function stabilizeUpdateJobForm() {
        const form = document.getElementById('form-job');

        if (!form || !String(form.action || '').includes('update-jobs')) {
            return;
        }

        stabilizeStatusSelect(form);

        form.addEventListener('submit', function () {
            stabilizeStatusSelect(form);
            syncDynamicRows(form);
        }, true);
    }

    document.addEventListener('DOMContentLoaded', stabilizeUpdateJobForm);
})();
