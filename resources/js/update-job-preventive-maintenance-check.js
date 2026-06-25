// PATH FILE: resources/js/update-job-preventive-maintenance-check.js

function normalizeJobType(value) {
    const normalized = String(value || "")
        .trim()
        .toUpperCase();

    if (normalized === "PM") {
        return "Preventive Maintenance";
    }

    if (normalized === "PREVENTIVE MAINTENANCE") {
        return "Preventive Maintenance";
    }

    return String(value || "").trim();
}

function splitJobTypeValue(value) {
    return String(value || "")
        .split(",")
        .map((item) => normalizeJobType(item))
        .filter(Boolean);
}

function getSelectedJobTypes(input) {
    if (!input) {
        return [];
    }

    if (input.multiple) {
        return Array.from(input.selectedOptions || [])
            .map((option) => normalizeJobType(option.value))
            .filter(Boolean);
    }

    return splitJobTypeValue(input.value);
}

function resolveEditJobId(form) {
    if (!form) {
        return "";
    }

    if (form.dataset.jobId) {
        return String(form.dataset.jobId).trim();
    }

    const action = String(form.getAttribute("action") || form.action || "");
    const match = action.match(/\/update-jobs\/(\d+)(?:\b|\/|\?|#)/);

    return match ? match[1] : "";
}

function buildPreventiveMaintenancePopup() {
    const existingPopup = document.getElementById("pm-duplicate-popup");

    if (existingPopup) {
        return existingPopup;
    }

    const popup = document.createElement("div");
    popup.id = "pm-duplicate-popup";
    popup.className =
        "fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm";
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

    popup
        .querySelector("#pm-duplicate-close")
        .addEventListener("click", function () {
            popup.classList.add("hidden");
            popup.classList.remove("flex");
        });

    popup.addEventListener("click", function (event) {
        if (event.target === popup) {
            popup.classList.add("hidden");
            popup.classList.remove("flex");
        }
    });

    return popup;
}

function showPreventiveMaintenancePopup(message) {
    const popup = buildPreventiveMaintenancePopup();
    const messageElement = popup.querySelector("#pm-duplicate-message");

    messageElement.textContent =
        message ||
        "Preventive Maintenance untuk unit ini sudah pernah dibuat pada bulan yang sama.";
    popup.classList.remove("hidden");
    popup.classList.add("flex");
}

function lockPmCheckbox() {
    const pmOption = document.querySelector(
        'input[data-multi-job-type-option][value="Preventive Maintenance"]',
    );

    if (!pmOption || pmOption.dataset.pmCheckLocked === "true") return;

    pmOption.checked = false;
    pmOption.dispatchEvent(new Event("change", { bubbles: true }));
    pmOption.disabled = true;
    pmOption.dataset.pmCheckLocked = "true";
    pmOption.setAttribute("aria-disabled", "true");

    const label = pmOption.closest("label");

    if (label) {
        label.classList.add(
            "cursor-not-allowed",
            "bg-blue-50",
            "text-blue-800",
            "pr-2",
        );
        label.title =
            "Preventive Maintenance sudah ada di bulan ini.";

        if (!label.querySelector("[data-pm-locked-badge]")) {
            const badge = document.createElement("span");
            badge.dataset.pmLockedBadge = "true";
            badge.className =
                "ml-auto inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700";
            badge.setAttribute("aria-label", "Preventive Maintenance terkunci");
            badge.setAttribute("title", "Terkunci");
            badge.innerHTML = `
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4" />
                </svg>
            `;
            label.appendChild(badge);
        }
    }
}

function unlockPmCheckbox() {
    const pmOption = document.querySelector(
        'input[data-multi-job-type-option][value="Preventive Maintenance"]',
    );

    if (!pmOption || pmOption.dataset.pmCheckLocked !== "true") return;

    pmOption.disabled = false;
    pmOption.removeAttribute("aria-disabled");
    delete pmOption.dataset.pmCheckLocked;

    const label = pmOption.closest("label");

    if (label) {
        label.classList.remove(
            "cursor-not-allowed",
            "bg-blue-50",
            "text-blue-800",
            "pr-2",
        );
        label.title = "";

        const badge = label.querySelector("[data-pm-locked-badge]");

        if (badge) {
            badge.remove();
        }
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("form-job");
    const serialNumberInput = document.getElementById("serial_number");
    const jobTypeInput = document.getElementById("job_type");
    const workDateInput = document.getElementById("work_date");
    const submitButton = document.getElementById("btn-submit");

    if (!form || !serialNumberInput || !jobTypeInput || !workDateInput) {
        return;
    }

    let checkTimeout = null;
    let lastCheckKey = "";
    let pmLocked = false;
    let activeController = null;

    function canCheckPm() {
        return (
            serialNumberInput.value.trim() !== "" &&
            workDateInput.value.trim() !== ""
        );
    }

    function runCheck(showPopup = false) {
        clearTimeout(checkTimeout);

        if (!canCheckPm()) {
            unlockPmCheckbox();
            pmLocked = false;
            lastCheckKey = "";
            return;
        }

        const serialNumber = serialNumberInput.value.trim();
        const workDate = workDateInput.value.trim();
        const exceptJobId = resolveEditJobId(form);
        const checkKey = `${serialNumber}|${workDate}|${exceptJobId}`;

        if (checkKey === lastCheckKey && !showPopup) {
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
                job_type: "Preventive Maintenance",
            });

            if (exceptJobId) {
                params.append("except_job_id", exceptJobId);
            }

            fetch(
                `/update-jobs/check-preventive-maintenance?${params.toString()}`,
                {
                    headers: {
                        Accept: "application/json",
                    },
                    signal: activeController.signal,
                },
            )
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error(
                            "Gagal mengecek data Preventive Maintenance.",
                        );
                    }

                    return response.json();
                })
                .then(function (data) {
                    if (data.blocked) {
                        pmLocked = true;
                        lockPmCheckbox();

                        if (showPopup) {
                            showPreventiveMaintenancePopup(data.message);
                        }

                        return;
                    }

                    pmLocked = false;
                    unlockPmCheckbox();
                })
                .catch(function (error) {
                    if (error.name === "AbortError") {
                        return;
                    }

                    pmLocked = false;
                    unlockPmCheckbox();
                });
        }, 250);
    }

    serialNumberInput.addEventListener("change", function () {
        runCheck(true);
    });

    serialNumberInput.addEventListener("input", function () {
        runCheck(false);
    });

    serialNumberInput.addEventListener("blur", function () {
        runCheck(true);
    });

    jobTypeInput.addEventListener("change", function () {
        runCheck(true);
    });

    workDateInput.addEventListener("change", function () {
        runCheck(true);
    });

    window.drrsaktiRunPmCheck = function (showPopup) {
        runCheck(showPopup);
    };

    runCheck(false);
});
