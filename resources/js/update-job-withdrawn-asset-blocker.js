// PATH FILE: resources/js/update-job-withdrawn-asset-blocker.js

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-job');
    const serialInput = document.getElementById('serial_number');
    const submitButton = document.getElementById('btn-submit');

    if (!form || !serialInput || !submitButton) {
        return;
    }

    let warning = document.getElementById('withdrawn-asset-warning');
    if (!warning) {
        warning = document.createElement('div');
        warning.id = 'withdrawn-asset-warning';
        warning.className = 'mt-2 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-bold text-red-700';
        serialInput.parentElement?.appendChild(warning);
    }

    const defaultSubmitText = document.getElementById('btn-text')?.textContent || 'Simpan Update Job';

    const lockSubmit = (message) => {
        warning.textContent = message;
        warning.classList.remove('hidden');
        serialInput.classList.remove('border-slate-300', 'focus:border-blue-500', 'focus:ring-blue-500');
        serialInput.classList.add('border-red-400', 'focus:border-red-500', 'focus:ring-red-500');
        submitButton.disabled = true;
        submitButton.classList.add('opacity-50', 'cursor-not-allowed');
        const btnText = document.getElementById('btn-text');
        if (btnText) btnText.textContent = 'S/N Ditarik';
    };

    const unlockSubmit = () => {
        warning.classList.add('hidden');
        warning.textContent = '';
        serialInput.classList.remove('border-red-400', 'focus:border-red-500', 'focus:ring-red-500');
        serialInput.classList.add('border-slate-300', 'focus:border-blue-500', 'focus:ring-blue-500');
        submitButton.disabled = false;
        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        const btnText = document.getElementById('btn-text');
        if (btnText) btnText.textContent = defaultSubmitText;
    };

    const checkWithdrawnStatus = async () => {
        const value = serialInput.value.trim();

        if (!value) {
            unlockSubmit();
            return;
        }

        try {
            const response = await fetch(`/update-jobs/search-assets?q=${encodeURIComponent(value)}&include_withdrawn=1`);
            if (!response.ok) return;

            const assets = await response.json();
            const exactAsset = Array.isArray(assets)
                ? assets.find((asset) => String(asset.serial_number || '').toUpperCase() === value.toUpperCase())
                : null;

            if (exactAsset?.is_withdrawn) {
                lockSubmit(exactAsset.blocked_reason || `Serial Number ${value} tidak bisa digunakan karena status unit asset sudah DITARIK.`);
                return;
            }

            unlockSubmit();
        } catch (error) {
            unlockSubmit();
        }
    };

    let timer = null;
    serialInput.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(checkWithdrawnStatus, 220);
    });

    serialInput.addEventListener('change', checkWithdrawnStatus);
    form.addEventListener('submit', (event) => {
        if (submitButton.disabled) {
            event.preventDefault();
            serialInput.focus();
        }
    });

    checkWithdrawnStatus();
});
