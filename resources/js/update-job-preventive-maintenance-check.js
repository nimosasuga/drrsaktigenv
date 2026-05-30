// PATH FILE: resources/js/update-job-preventive-maintenance-check.js

function normalizeJobType(value) {
    const normalized = String(value || '').trim().toUpperCase();

    if (normalized === 'PM') {
        return 'Preventive Maintenance';
    }

    if (normalized === 'PREVENTIVE MAINTENANCE') {
        return 'Preventive Maintenance';
    }

    return String(value || '').trim();
}

function buildPreventiveMaintenancePopup() {
    const existingPopup = document.getElementById('pm-duplicate-popup');

    if (existingPopup) {
        return existingPopup;
    }

    const popup = document.createElement('div');
    popup.id = 'pm-duplicate-popup';
    popup.className = 'fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm';
    popup.innerHTML = `
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-red-100 overflow-hidden">
            <div class="px-6 py-5 bg-red-50 border-b border-red-100">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-red-800">Preventive Maintenance Sudah Ada</h3>
                        <p class="mt-1 text-sm text-red-700">Unit ini sudah memiliki PM pada bulan yang sama.</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-5">
                <p id="pm-duplicate-message" class="text-sm leading-6 text-slate-700"></p>
                <div class="mt-5 rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-xs text-slate-600">
                    Silakan pilih S/N lain, ubah tanggal ke bulan berbeda, atau ganti tipe pekerjaan jika bukan Preventive Maintenance.
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="button" id="pm-duplicate-close" class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
                    Mengerti
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(popup);

    popup.querySelector('#pm-duplicate-close').addEventListener('click', function () {
        popup.classList.add('hidden');
        popup.classList.remove('flex');
    });

    popup.addEventListener('click', function (event) {
        if (event.target === popup) {
            popup.classList.add('hidden');
            popup.classList.remove('flex');
        }
    });

    return popup;
}

function showPreventiveMaintenancePopup(message) {
    const popup = buildPreventiveMaintenancePopup();
    const messageElement = popup.querySelector('#pm-duplicate-message');

    messageElement.textContent = message || 'Preventive Maintenance untuk unit ini sudah pernah dibuat pada bulan yang sama.';
    popup.classList.remove('hidden');
    popup.classList.add('flex');
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-job');
    const serialNumberInput = document.getElementById('serial_number');
    const jobTypeInput = document.getElementById('job_type');
    const workDateInput = document.getElementById('work_date');
    const submitButton = document.getElementById('btn-submit');

    if (!form || !serialNumberInput || !jobTypeInput || !workDateInput) {
        return;
    }

    let checkTimeout = null;
    let lastCheckKey = '';
    let blockedMessage = '';
    let activeController = null;

    function setBlockedState(message) {
        blockedMessage = message || '';
        serialNumberInput.setCustomValidity(blockedMessage);

        if (submitButton) {
            submitButton.disabled = Boolean(blockedMessage);
            submitButton.classList.toggle('opacity-60', Boolean(blockedMessage));
            submitButton.classList.toggle('cursor-not-allowed', Boolean(blockedMessage));
        }
    }

    function clearBlockedState() {
        setBlockedState('');
    }

    function shouldCheck() {
        return serialNumberInput.value.trim() !== ''
            && workDateInput.value.trim() !== ''
            && normalizeJobType(jobTypeInput.value) === 'Preventive Maintenance';
    }

    function runCheck(showPopup = false) {
        clearTimeout(checkTimeout);

        if (!shouldCheck()) {
            clearBlockedState();
            lastCheckKey = '';
            return;
        }

        const serialNumber = serialNumberInput.value.trim();
        const workDate = workDateInput.value.trim();
        const jobType = jobTypeInput.value.trim();
        const exceptJobId = form.dataset.jobId || '';
        const checkKey = `${serialNumber}|${workDate}|${jobType}|${exceptJobId}`;

        if (checkKey === lastCheckKey && blockedMessage) {
            if (showPopup) {
                showPreventiveMaintenancePopup(blockedMessage);
            }
            return;
        }

        lastCheckKey = checkKey;

        checkTimeout = setTimeout(function () {
            if (activeController) {
                activeController.abort();
            }

            activeController = new AbortController();

            const params = new URLSearchParams({
                serial_number: serialNumber,
                work_date: workDate,
                job_type: jobType,
            });

            if (exceptJobId) {
                params.append('except_job_id', exceptJobId);
            }

            fetch(`/update-jobs/check-preventive-maintenance?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                },
                signal: activeController.signal,
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Gagal mengecek data Preventive Maintenance.');
                    }

                    return response.json();
                })
                .then(function (data) {
                    if (data.blocked) {
                        setBlockedState(data.message);
                        showPreventiveMaintenancePopup(data.message);
                        return;
                    }

                    clearBlockedState();
                })
                .catch(function (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    clearBlockedState();
                });
        }, 250);
    }

    serialNumberInput.addEventListener('change', function () {
        runCheck(true);
    });

    serialNumberInput.addEventListener('blur', function () {
        runCheck(true);
    });

    jobTypeInput.addEventListener('change', function () {
        runCheck(true);
    });

    workDateInput.addEventListener('change', function () {
        runCheck(true);
    });

    document.addEventListener('click', function (event) {
        const option = event.target.closest('#sn-dropdown > div');

        if (option) {
            setTimeout(function () {
                runCheck(true);
            }, 80);
        }
    });

    form.addEventListener('submit', function (event) {
        if (blockedMessage) {
            event.preventDefault();
            showPreventiveMaintenancePopup(blockedMessage);
            serialNumberInput.reportValidity();
        }
    }, true);

    runCheck(false);
});
