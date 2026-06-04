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

        function selectedValues() {
            return options
                .filter((option) => option.checked)
                .map((option) => option.value.trim())
                .filter(Boolean);
        }

        function syncValue() {
            const values = selectedValues();
            const text =
                values.length > 0 ? values.join(", ") : "Pilih Tipe Pekerjaan";

            hiddenInput.value = values.join(", ");
            label.textContent = text;
            label.title = text;

            hiddenInput.dispatchEvent(new Event("change", { bubbles: true }));
        }

        function closeMenu() {
            menu.classList.add("hidden");
        }

        button.addEventListener("click", (event) => {
            event.preventDefault();
            menu.classList.toggle("hidden");
        });

        options.forEach((option) => {
            option.addEventListener("change", syncValue);
        });

        document.addEventListener("click", (event) => {
            if (!root.contains(event.target)) {
                closeMenu();
            }
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                closeMenu();
            }
        });

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
