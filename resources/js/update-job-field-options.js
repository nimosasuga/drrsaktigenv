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

        if (menu.parentElement !== document.body) {
            document.body.appendChild(menu);
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
