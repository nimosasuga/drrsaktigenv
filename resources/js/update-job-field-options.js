// PATH FILE: resources/js/update-job-field-options.js

function normalizeLegacyValue(value) {
    const normalized = String(value || "")
        .trim()
        .toUpperCase();

    const map = {
        PM: "Preventive Maintenance",
        BM: "Troubleshooting",
        PDI: "Inspection",
        "B/D": "Breakdown",
        BD: "Breakdown",
        BREAKDOWN: "Breakdown",
        STANDBY: "Monitoring",
    };

    return map[normalized] || String(value || "").trim();
}

function splitJobTypeValue(value) {
    return String(value || "")
        .split(",")
        .map((item) => normalizeLegacyValue(item))
        .filter(Boolean);
}

function replaceSelectOptions(select, placeholder, options) {
    if (!select || select.tagName !== "SELECT") {
        return;
    }

    const currentValue = normalizeLegacyValue(select.value);
    select.innerHTML = "";

    const emptyOption = document.createElement("option");
    emptyOption.value = "";
    emptyOption.textContent = placeholder;
    select.appendChild(emptyOption);

    options.forEach((optionValue) => {
        const option = document.createElement("option");
        option.value = optionValue;
        option.textContent = optionValue;

        if (currentValue === optionValue) {
            option.selected = true;
        }

        select.appendChild(option);
    });
}

function initializeMultiJobTypeDropdown() {
    let activeMenu = null;
    let activeButton = null;

    function positionMenu(menu, button) {
        if (!menu || !button || menu.classList.contains("hidden")) {
            return;
        }

        const rect = button.getBoundingClientRect();
        const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
        const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
        const horizontalPadding = 12;
        const menuWidth = Math.min(rect.width, viewportWidth - (horizontalPadding * 2));
        const left = Math.min(
            Math.max(horizontalPadding, rect.left),
            Math.max(horizontalPadding, viewportWidth - menuWidth - horizontalPadding),
        );
        const bottomSpace = viewportHeight - rect.bottom;
        const topSpace = rect.top;
        const shouldOpenUp = bottomSpace < 220 && topSpace > bottomSpace;
        const maxHeight = Math.max(
            180,
            Math.min(320, (shouldOpenUp ? topSpace : bottomSpace) - 16),
        );

        menu.style.position = "fixed";
        menu.style.left = `${left}px`;
        menu.style.width = `${menuWidth}px`;
        menu.style.maxHeight = `${maxHeight}px`;
        menu.style.overflowY = "auto";
        menu.style.zIndex = "999999";

        if (shouldOpenUp) {
            menu.style.top = "auto";
            menu.style.bottom = `${viewportHeight - rect.top + 8}px`;
        } else {
            menu.style.top = `${rect.bottom + 8}px`;
            menu.style.bottom = "auto";
        }
    }

    function closeActiveMenu() {
        if (activeMenu) {
            activeMenu.classList.add("hidden");
        }

        activeMenu = null;
        activeButton = null;
    }

    document.querySelectorAll("[data-multi-job-type]").forEach((root) => {
        const hiddenInput = root.querySelector("[data-multi-job-type-value]");
        const button = root.querySelector("[data-multi-job-type-button]");
        const label = root.querySelector("[data-multi-job-type-label]");
        const menu = root.querySelector("[data-multi-job-type-menu]");
        const options = Array.from(
            root.querySelectorAll("[data-multi-job-type-option]"),
        );

        if (
            !hiddenInput ||
            !button ||
            !label ||
            !menu ||
            options.length === 0
        ) {
            return;
        }

        const form = root.closest("form");
        const formAction = String(form?.getAttribute("action") || form?.action || "");
        const isEditForm = /\/update-jobs\/\d+(?:\b|\/|\?|#)/.test(formAction);
        const hasExistingPreventiveMaintenance = splitJobTypeValue(hiddenInput.value).includes(
            "Preventive Maintenance",
        );
        const lockPreventiveMaintenance = isEditForm && hasExistingPreventiveMaintenance;
        const preventiveMaintenanceOption = options.find(
            (option) => normalizeLegacyValue(option.value) === "Preventive Maintenance",
        );

        if (lockPreventiveMaintenance && preventiveMaintenanceOption) {
            preventiveMaintenanceOption.checked = true;
            preventiveMaintenanceOption.disabled = true;
            preventiveMaintenanceOption.setAttribute("aria-disabled", "true");
            preventiveMaintenanceOption.dataset.lockedPreventiveMaintenance = "true";

            const optionLabel = preventiveMaintenanceOption.closest("label");
            if (optionLabel) {
                optionLabel.classList.add("cursor-not-allowed", "bg-blue-50", "text-blue-800", "pr-2");
                optionLabel.title = "Preventive Maintenance terkunci karena job ini sudah memiliki PM. Tambahkan tipe pekerjaan lain tanpa menghapus PM.";

                if (!optionLabel.querySelector("[data-pm-locked-badge]")) {
                    const badge = document.createElement("span");
                    badge.dataset.pmLockedBadge = "true";
                    badge.className = "ml-auto inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700";
                    badge.setAttribute("aria-label", "Preventive Maintenance terkunci");
                    badge.setAttribute("title", "Terkunci");
                    badge.innerHTML = `
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4" />
                        </svg>
                    `;
                    optionLabel.appendChild(badge);
                }
            }
        }

        if (menu.parentElement !== document.body) {
            document.body.appendChild(menu);
        }

        function selectedValues() {
            const values = options
                .filter((option) => option.checked)
                .map((option) => option.value.trim())
                .filter(Boolean);

            if (lockPreventiveMaintenance && !values.includes("Preventive Maintenance")) {
                values.unshift("Preventive Maintenance");
            }

            return Array.from(new Set(values));
        }

        function syncValue() {
            if (lockPreventiveMaintenance && preventiveMaintenanceOption) {
                preventiveMaintenanceOption.checked = true;
            }

            const values = selectedValues();
            const text =
                values.length > 0 ? values.join(", ") : "Pilih Tipe Pekerjaan";

            hiddenInput.value = values.join(", ");
            label.textContent = text;
            label.title = text;

            hiddenInput.dispatchEvent(new Event("change", { bubbles: true }));
        }

        function openMenu() {
            if (activeMenu && activeMenu !== menu) {
                closeActiveMenu();
            }

            menu.classList.remove("hidden");
            activeMenu = menu;
            activeButton = button;
            positionMenu(menu, button);
        }

        function closeMenu() {
            if (activeMenu === menu) {
                activeMenu = null;
                activeButton = null;
            }

            menu.classList.add("hidden");
        }

        button.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();

            if (menu.classList.contains("hidden")) {
                openMenu();
                return;
            }

            closeMenu();
        });

        options.forEach((option) => {
            option.addEventListener("change", syncValue);
        });

        document.addEventListener("click", (event) => {
            if (!root.contains(event.target) && !menu.contains(event.target)) {
                closeMenu();
            }
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                closeMenu();
            }
        });

        window.addEventListener("resize", () => positionMenu(menu, button));
        window.addEventListener("scroll", () => positionMenu(menu, button), true);

        syncValue();
    });
}

function standardizeUpdateJobFieldOptions() {
    const form = document.getElementById("form-job");

    if (!form) {
        return;
    }

    replaceSelectOptions(
        document.getElementById("status_unit"),
        "Pilih Status Akhir Unit",
        ["RFU", "Breakdown", "Monitoring", "Waiting Part"],
    );

    initializeMultiJobTypeDropdown();
}

document.addEventListener("DOMContentLoaded", standardizeUpdateJobFieldOptions);
