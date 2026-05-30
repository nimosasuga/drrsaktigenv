// PATH FILE: resources/js/command-center-import-trigger.js

document.addEventListener('DOMContentLoaded', function () {
    const commandCenterTitle = Array.from(document.querySelectorAll('h2'))
        .find((title) => title.textContent.trim() === 'Import / Export Excel-Friendly');

    if (!commandCenterTitle) {
        return;
    }

    const section = commandCenterTitle.closest('section');

    if (!section) {
        return;
    }

    const importLabels = Array.from(section.querySelectorAll('span'))
        .filter((element) => element.textContent.trim() === 'Import CSV');

    importLabels.forEach((label) => {
        const card = label.closest('.rounded-3xl');
        const fileInput = card ? card.querySelector('input[type="file"][name="file"]') : null;

        if (!fileInput) {
            return;
        }

        label.setAttribute('role', 'button');
        label.setAttribute('tabindex', '0');
        label.setAttribute('title', 'Klik untuk memilih file CSV');
        label.classList.add('cursor-pointer', 'hover:border-amber-300', 'hover:bg-amber-50', 'hover:text-amber-700');

        const openFilePicker = function () {
            fileInput.click();
        };

        label.addEventListener('click', openFilePicker);
        label.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openFilePicker();
            }
        });

        fileInput.addEventListener('change', function () {
            const selectedFile = fileInput.files && fileInput.files.length > 0
                ? fileInput.files[0].name
                : 'Import CSV';

            label.textContent = selectedFile;
            label.classList.add('border-amber-300', 'bg-amber-50', 'text-amber-700');
        });
    });
});
