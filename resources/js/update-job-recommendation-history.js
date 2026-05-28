// resources/js/update-job-recommendation-history.js

function createRecommendationHistorySection() {
    const form = document.getElementById('form-job');
    const serialInput = document.getElementById('serial_number');

    if (!form || !serialInput || document.getElementById('sn-recommendation-history-section')) {
        return null;
    }

    const section = document.createElement('section');
    section.id = 'sn-recommendation-history-section';
    section.className = 'bg-white rounded-3xl shadow-sm border border-blue-100 overflow-hidden';
    section.innerHTML = `
        <div class="px-6 py-4 border-b border-blue-100 bg-blue-50/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold text-blue-900 uppercase tracking-wider">Histori Rekomendasi Part Berdasarkan S/N</h2>
                <p class="mt-1 text-xs font-medium text-blue-700/80">Data muncul otomatis setelah Serial Number dipilih atau diketik.</p>
            </div>
            <span id="sn-recommendation-history-count" class="inline-flex w-fit items-center rounded-full bg-white px-3 py-1 text-[11px] font-bold text-blue-700 border border-blue-100">0 Data</span>
        </div>
        <div class="p-4 sm:p-6">
            <div id="sn-recommendation-history-empty" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center">
                <p class="text-sm font-bold text-slate-600">Belum ada Serial Number yang dipilih.</p>
                <p class="mt-1 text-xs text-slate-500">Pilih S/N unit untuk melihat histori rekomendasi part terdahulu.</p>
            </div>

            <div id="sn-recommendation-history-loading" class="hidden rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4 text-sm font-bold text-blue-700">
                Memuat histori rekomendasi part...
            </div>

            <div id="sn-recommendation-history-table-wrap" class="hidden overflow-hidden rounded-2xl border border-slate-200">
                <div class="hidden grid-cols-12 gap-3 border-b border-slate-200 bg-slate-50 px-3 py-2 text-[10px] font-black uppercase tracking-wider text-slate-500 sm:grid">
                    <div class="col-span-2">Tanggal</div>
                    <div class="col-span-3">Part Number</div>
                    <div class="col-span-5">Part Name</div>
                    <div class="col-span-2 text-right">Qty</div>
                </div>
                <div id="sn-recommendation-history-body" class="divide-y divide-slate-100"></div>
            </div>
        </div>
    `;

    const submitBlock = form.querySelector('#btn-submit')?.closest('.flex.justify-end');
    if (submitBlock) {
        form.insertBefore(section, submitBlock);
    } else {
        form.appendChild(section);
    }

    return section;
}

function renderRecommendationRows(rows) {
    const emptyBox = document.getElementById('sn-recommendation-history-empty');
    const loadingBox = document.getElementById('sn-recommendation-history-loading');
    const tableWrap = document.getElementById('sn-recommendation-history-table-wrap');
    const tableBody = document.getElementById('sn-recommendation-history-body');
    const counter = document.getElementById('sn-recommendation-history-count');

    if (!emptyBox || !loadingBox || !tableWrap || !tableBody || !counter) {
        return;
    }

    loadingBox.classList.add('hidden');
    tableBody.innerHTML = '';
    counter.textContent = `${rows.length} Data`;

    if (!rows.length) {
        tableWrap.classList.add('hidden');
        emptyBox.classList.remove('hidden');
        emptyBox.innerHTML = `
            <p class="text-sm font-bold text-slate-600">Belum ada histori rekomendasi part untuk S/N ini.</p>
            <p class="mt-1 text-xs text-slate-500">Jika nanti mekanik menambahkan rekomendasi part, histori akan tampil di sini.</p>
        `;
        return;
    }

    emptyBox.classList.add('hidden');
    tableWrap.classList.remove('hidden');

    rows.forEach((row) => {
        const item = document.createElement('div');
        item.className = 'grid grid-cols-1 gap-2 px-3 py-3 text-sm sm:grid-cols-12 sm:items-center sm:gap-3';
        item.innerHTML = `
            <div class="flex items-center justify-between sm:col-span-2 sm:block">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 sm:hidden">Tanggal</span>
                <span class="font-bold text-slate-700">${row.date ?? '-'}</span>
            </div>
            <div class="flex items-center justify-between sm:col-span-3 sm:block">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 sm:hidden">Part Number</span>
                <span class="font-bold text-blue-700">${row.part_number ?? '-'}</span>
            </div>
            <div class="sm:col-span-5">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 sm:hidden">Part Name</p>
                <p class="font-bold text-slate-800">${row.part_name ?? '-'}</p>
            </div>
            <div class="flex items-center justify-between sm:col-span-2 sm:block sm:text-right">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 sm:hidden">Qty</span>
                <span class="inline-flex min-w-8 justify-center rounded-full bg-blue-50 px-2 py-1 text-xs font-black text-blue-700 border border-blue-100">${row.qty ?? 1}</span>
            </div>
        `;
        tableBody.appendChild(item);
    });
}

function setRecommendationLoading(serialNumber) {
    const emptyBox = document.getElementById('sn-recommendation-history-empty');
    const loadingBox = document.getElementById('sn-recommendation-history-loading');
    const tableWrap = document.getElementById('sn-recommendation-history-table-wrap');
    const counter = document.getElementById('sn-recommendation-history-count');

    if (!emptyBox || !loadingBox || !tableWrap || !counter) {
        return;
    }

    counter.textContent = serialNumber ? 'Loading...' : '0 Data';
    tableWrap.classList.add('hidden');
    emptyBox.classList.add('hidden');
    loadingBox.classList.remove('hidden');
}

async function loadRecommendationHistory(serialNumber) {
    const value = String(serialNumber || '').trim();

    if (!value) {
        renderRecommendationRows([]);
        const emptyBox = document.getElementById('sn-recommendation-history-empty');
        if (emptyBox) {
            emptyBox.innerHTML = `
                <p class="text-sm font-bold text-slate-600">Belum ada Serial Number yang dipilih.</p>
                <p class="mt-1 text-xs text-slate-500">Pilih S/N unit untuk melihat histori rekomendasi part terdahulu.</p>
            `;
        }
        return;
    }

    setRecommendationLoading(value);

    try {
        const response = await fetch(`/update-jobs/recommendation-history?serial_number=${encodeURIComponent(value)}`);
        if (!response.ok) {
            throw new Error('Gagal mengambil histori rekomendasi part.');
        }

        const rows = await response.json();
        renderRecommendationRows(Array.isArray(rows) ? rows : []);
    } catch (error) {
        renderRecommendationRows([]);
        const emptyBox = document.getElementById('sn-recommendation-history-empty');
        if (emptyBox) {
            emptyBox.innerHTML = `
                <p class="text-sm font-bold text-red-600">Gagal memuat histori rekomendasi part.</p>
                <p class="mt-1 text-xs text-slate-500">Periksa koneksi atau coba pilih S/N ulang.</p>
            `;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const section = createRecommendationHistorySection();
    const serialInput = document.getElementById('serial_number');

    if (!section || !serialInput) {
        return;
    }

    let timer = null;
    const triggerLoad = () => {
        clearTimeout(timer);
        timer = setTimeout(() => loadRecommendationHistory(serialInput.value), 250);
    };

    serialInput.addEventListener('input', triggerLoad);
    serialInput.addEventListener('change', triggerLoad);

    document.addEventListener('click', (event) => {
        const selectedAsset = event.target.closest('#sn-dropdown > div');
        if (!selectedAsset) {
            return;
        }

        setTimeout(() => loadRecommendationHistory(serialInput.value), 80);
    });

    if (serialInput.value.trim()) {
        loadRecommendationHistory(serialInput.value);
    }
});
