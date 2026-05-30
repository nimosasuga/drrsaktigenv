# DRR SAKTI GEN V

DRR SAKTI GEN V adalah aplikasi Laravel untuk manajemen operasional field service, aset unit, update job, battery, charger, delivery unit, penarikan unit, subscription, dan command center statistik. Project ini **project lanjutan** dan sudah masuk **produksi awal**, sehingga setiap perubahan wajib kecil, aman, bertahap, dan bisa dites.

Repository:

```text
https://github.com/nimosasuga/drrsaktigenv.git
```

Local path:

```text
C:\laragon\www\drrsakti
```

---

## 1. Stack Project

```text
Laravel 13
PHP 8.3
MySQL
Laragon
HeidiSQL
Blade
Tailwind CSS
Vite
PowerShell
```

Command standar setelah pull/perubahan kode:

```powershell
cd C:\laragon\www\drrsakti
git pull origin main
php artisan optimize:clear
php artisan view:clear
php artisan route:clear
composer dump-autoload
npm run dev
```

Jika ada migration baru:

```powershell
php artisan migrate
```

---

## 2. Aturan Produksi

1. Project ini **bukan project baru**.
2. Jangan ubah arsitektur besar tanpa alasan kuat.
3. Jangan hapus fitur, route, tombol, field, tampilan, atau alur yang sudah stabil tanpa instruksi eksplisit.
4. Jangan ubah database kalau bisa diselesaikan di controller/view.
5. Perubahan harus kecil, aman, bertahap, dan mudah dites.
6. Baca struktur/file yang ada sebelum memberi solusi.
7. Gunakan kolom modern:

```text
customer
location
serial_number
unit_type
status
year
nomor_lambung
```

8. Jangan gunakan kolom lama sebagai standar utama:

```text
nama_pelanggan
lokasi
```

Fallback boleh hanya jika diperlukan untuk kompatibilitas data lama.

9. Histori asset saat ini masih berdasarkan:

```text
unit_assets.serial_number = update_jobs.serial_number
```

10. `unit_asset_id` bisa dibuat nanti setelah modul utama benar-benar stabil.

---

## 3. Aturan AI / Canvas / File

Jika project dilanjutkan dengan ChatGPT, Gemini, Claude, atau AI coding assistant lain, aturan wajib:

1. Satu canvas hanya untuk satu file kode.
2. Nama canvas harus sama persis dengan path file.
3. Baris paling atas isi canvas wajib memuat path file.
4. Jika file sudah pernah ada, update file lama. Jangan buat file duplikat.
5. Jangan campur controller, model, route, blade, migration, dan JavaScript dalam satu canvas.
6. Jika ubah tiga file, buat/update tiga file/canvas terpisah.
7. Jangan memberi solusi sebelum membaca file target.
8. Jangan pakai pendekatan tambal JS jika field bisa dipasang langsung di Blade.
9. Jangan mengubah endpoint search asset yang sudah stabil kecuali memang diminta.

Format jawaban wajib:

```text
1. TUJUAN
2. FILE YANG DIUBAH
3. COMMAND POWERSHELL
4. POTONGAN TARGET FILE
5. KODE LENGKAP / PATCH
6. CARA TESTING
7. JIKA ERROR
8. LANGKAH BERIKUTNYA
```

---

## 4. Role dan Hak Akses

Role utama:

```text
super_admin
admin
koordinator
sect_head
mekanik / user biasa
PLANNER
```

Hak akses umum:

- `super_admin`: full access.
- `admin`: full akses operasional.
- `koordinator`: privileged access operasional.
- `sect_head`: privileged access operasional.
- mekanik/user biasa: akses terbatas sesuai data dan modul.
- `PLANNER`: tidak boleh create Delivery Unit dan tidak boleh create Penarikan Unit.

---

## 5. Modul Stabil

### 5.1 Authentication

Stabil:

- Login
- Logout
- Dashboard
- Auth middleware

---

### 5.2 Subscription

Stabil:

- Subscription index
- Payment
- Waiting approval
- Middleware `CheckSubscription`
- Konfirmasi pembayaran redirect ke WhatsApp admin:

```text
085133331467
```

- Waiting page punya tombol Chat Admin WhatsApp manual.

---

### 5.3 Super Admin

Stabil:

- Verifikasi lisensi
- Manajemen pengguna
- Middleware `CheckSuperAdmin`
- Verifikasi lisensi sudah dibuat lebih jelas agar admin melihat data pembayaran sebelum approve.

---

### 5.4 Unit Asset / Manajemen Aset

Stabil:

- Grouping berdasarkan `customer/location`
- Searchbox dan filter
- User biasa read-only
- CRUD hanya privileged role
- Histori pekerjaan memakai `serial_number`
- Status asset `DITARIK` dipakai untuk unit yang sudah ditarik

Status asset penting:

```text
DITARIK
```

Jika unit masuk Penarikan Unit, status asset berubah menjadi `DITARIK`.

---

### 5.5 Update Job

Stabil:

- Index/create/edit/show/destroy
- Search asset by serial number stabil melalui:

```text
app/Http/Controllers/UpdateJobAssetSearchController.php
```

- Route search asset:

```php
Route::get('/update-jobs/search-assets', UpdateJobAssetSearchController::class)->name('update-jobs.search-assets');
```

- Search asset **jangan diutak-atik sembarangan** karena ini jalur vital form create/edit.
- UI index grouped bertingkat:

```text
Bulan & Tahun
└── PIC
    └── Customer / Location
        └── Detail Unit
```

- Mobile compact seperti aplikasi Android
- Floating add button
- Histori rekomendasi part berdasarkan serial number:

```text
Tanggal
Part Number
Part Name
Qty
```

- Asset dengan status `DITARIK` tidak boleh dipakai untuk Update Job.
- Blocking dilakukan di UI dan controller.
- Field `year` dan `nomor_lambung` sudah didukung.
- Field `year` dan `nomor_lambung` **dipasang langsung di Blade**, bukan via JS injector.
- File JS injector lama sudah dihapus:

```text
resources/js/update-job-extra-fields.js
```

- Jangan aktifkan ulang import ini:

```js
import "./update-job-extra-fields";
```

- Create/update save flow distabilkan melalui:

```text
app/Http/Controllers/UpdateJobSaveController.php
```

- Route store/update Update Job:

```php
Route::post('/update-jobs', [UpdateJobSaveController::class, 'store'])->name('update-jobs.store');
Route::put('/update-jobs/{id}', [UpdateJobSaveController::class, 'update'])->name('update-jobs.update');
Route::patch('/update-jobs/{id}', [UpdateJobSaveController::class, 'update']);
Route::resource('update-jobs', JobController::class)->except(['store', 'update']);
```

- `index/create/show/edit/destroy` tetap memakai `JobController`.
- `store/update` memakai `UpdateJobSaveController` agar edit tidak memaksa isi ulang field lama.
- Saat edit action saja, field lama fallback dari database.
- Install Part / Recommendation tidak dihapus jika form tidak mengirim array part.
- Setelah create/update berhasil, redirect ke detail job:

```text
/update-jobs/{id}
```

- Preventive Maintenance dibatasi:

```text
1 serial_number hanya boleh 1x Preventive Maintenance dalam 1 bulan.
```

Tipe pekerjaan resmi Update Job:

```text
Preventive Maintenance
Install Part
Troubleshooting
Inspection
Repair
```

Status akhir unit resmi:

```text
RFU
Breakdown
Monitoring
Waiting Part
```

---

### 5.6 Management Battery

Stabil:

- Index/create/edit/show
- Autocomplete asset
- Install parts
- Recommendation parts
- UI mengikuti Update Job dengan nuansa emerald/lime/cyan
- Grouping bertingkat
- Card `Battery SN / Unique battery` sudah dihapus
- Card `RFU / Ready battery unit` sudah dihapus
- Ada `Pekerjaan Populer Top 1-3` dengan grafik batang responsive
- Share WhatsApp floating button aktif di detail Battery

---

### 5.7 Management Charger

Stabil:

- Index/create/edit/show/update/destroy
- Autocomplete asset
- Install parts
- Recommendation parts
- UI mengikuti Update Job dengan nuansa amber/violet/indigo
- Grouping bertingkat
- Ada `Pekerjaan Populer Top 1-3`
- Share WhatsApp floating button aktif di detail Charger

---

### 5.8 Delivery Unit

Stabil:

- Migration/model/controller/route/view sudah dibuat
- Index/create/show/edit/store/update/destroy
- Search asset serial number
- `job_type` fixed: `DELIVERY UNIT`
- `status_unit`: `RFU / BREAKDOWN`
- PLANNER tidak boleh create Delivery Unit
- Edit/delete hanya PIC atau privileged role
- UI mengikuti Update Job dengan nuansa purple/sky/cyan
- Grouping bertingkat
- Share WhatsApp floating button aktif di detail Delivery

---

### 5.9 Penarikan Unit

Stabil:

- Migration/controller/route/view sudah dibuat
- Controller sementara memakai `DB::table('penarikans')`
- Index/create/show/edit/store/update/destroy
- PLANNER tidak boleh create
- Edit/delete hanya PIC atau privileged role
- `job_type` fixed: `TARIK UNIT`
- `status_unit`: `RFU / BREAKDOWN`
- Autocomplete S/N mulai 1 karakter
- Customer/location/unit_type/year/hour_meter otomatis dan readonly
- Mendukung:

```text
Battery Type 1
Battery SN 1
Battery Type 2
Battery SN 2
Trolly 1
Trolly 2
Trolly 3
```

- Autosave draft via localStorage
- Warning keluar halaman tidak muncul saat klik Simpan
- Bug edit `in_time/out_time` format `H:i` sudah diperbaiki
- Detail Penarikan menampilkan Battery 2 dan Trolly 2-3
- Tombol delete Penarikan ada di detail dengan permission sama seperti Update Job
- Setelah simpan/update Penarikan, status `unit_assets.status` sesuai serial_number berubah menjadi `DITARIK`
- Saat ini update status asset memakai MySQL trigger:

```text
trg_penarikans_after_insert_status
trg_penarikans_after_update_status
```

- Backup update status DITARIK juga sudah disiapkan di controller agar aman jika cPanel menolak trigger.
- Share WhatsApp floating button aktif di detail Penarikan.
- Report Penarikan tidak memakai `PENARIKAN CODE`.

---

## 6. Command Center

Command Center sudah dibuat untuk role:

```text
koordinator
sect_head
super_admin
admin
```

Fitur:

- Menu Command Center muncul di sidebar untuk role fleksibel seperti controller.
- Statistik performa operasional.
- Export CSV Excel-friendly.
- Import CSV insert-only.
- Filter:

```text
modul
PIC
customer
location
status
bulan
tahun
```

Analisa performa tajam:

- Produktivitas per bulan
- Beban kerja per customer/location
- Rasio RFU vs Breakdown
- Unit paling sering bermasalah
- Rekomendasi part terbanyak
- Status per PIC

Indikator warna risiko:

```text
Hijau  = RFU tinggi / aman
Merah  = Breakdown tinggi / risiko
Amber  = Monitoring / Waiting Part perlu diawasi
```

Status analisa resmi:

```text
RFU
Breakdown
Monitoring
Waiting Part
```

Tipe pekerjaan analisa resmi:

```text
Preventive Maintenance
Install Part
Troubleshooting
Inspection
Repair
```

---

## 7. WhatsApp Share Report

Share WhatsApp sudah aktif untuk detail:

```text
/update-jobs/{id}
/batteries/{id}
/chargers/{id}
/deliveries/{id}
/penarikans/{id}
```

Pola:

```text
Halaman detail/view
→ tombol floating WhatsApp
→ route share-message
→ controller susun report
→ redirect langsung ke WhatsApp
```

Icon floating memakai Bootstrap WhatsApp SVG.

Report tidak memakai emoji karena sebagian perangkat/WhatsApp tidak membaca emoji dengan baik.

Report tidak memakai footer:

```text
Dibagikan dari DRR SAKTI GEN V oleh ...
```

Controller share:

```text
app/Http/Controllers/UpdateJobShareController.php
app/Http/Controllers/OperationalShareController.php
```

JS floating button:

```text
resources/js/update-job-copy-report.js
resources/js/operational-share-report.js
```

Format report sudah disamakan antar modul:

```text
HEADER
CUSTOMER / LOCATION / DATE / IN / OUT / MAN POWER / KENDARAAN
DETAIL UNIT
JOB DESCRIPTIONS / EQUIPMENT
RECOMMENDATIONS
INSTALL PART
NOTE
```

---

## 8. Bottom Navigation

Urutan bottom nav:

```text
Home | Kalender | Job | Ingat | Profile
```

Popup Job:

```text
Update Job
Management Battery
Management Charger
Delivery Unit
Penarikan Unit
```

Penarikan Unit awalnya diarahkan lewat JS patch:

```text
resources/js/penarikan-menu-link.js
```

Jika layout sudah benar-benar aman, bisa dipatch langsung di:

```text
resources/views/layouts/app.blade.php
```

menjadi:

```blade
route('penarikans.index')
```

---

## 9. File Penting Terakhir

Jangan hapus tanpa alasan:

```text
app/Http/Controllers/UpdateJobAssetSearchController.php
app/Http/Controllers/UpdateJobSaveController.php
app/Http/Controllers/UpdateJobShareController.php
app/Http/Controllers/OperationalShareController.php
app/Http/Controllers/CommandCenterController.php
app/Http/Controllers/PenarikanController.php
resources/js/update-job-copy-report.js
resources/js/operational-share-report.js
resources/js/update-job-detail-extra-fields.js
resources/js/update-job-withdrawn-asset-blocker.js
resources/js/update-job-field-options.js
resources/js/penarikan-menu-link.js
resources/js/penarikan-form-enhancer.js
```

File yang sudah dihapus dan jangan dibuat/import lagi:

```text
resources/js/update-job-extra-fields.js
resources/js/update-job-extra-fields-stable.js
```

---

## 10. Testing Minimum Setelah Pull

Jalankan:

```powershell
cd C:\laragon\www\drrsakti
git pull origin main
php artisan optimize:clear
php artisan view:clear
php artisan route:clear
composer dump-autoload
npm run dev
```

Test wajib:

1. Login sebagai mekanik.
2. Buka `/update-jobs/create`.
3. Ketik serial number minimal 2 karakter.
4. Pastikan dropdown asset muncul.
5. Pilih asset.
6. Pastikan customer/location/unit_type terisi.
7. Isi `year` dan `nomor_lambung` manual jika perlu.
8. Simpan.
9. Pastikan redirect ke detail job.
10. Edit job, ubah `action` saja.
11. Simpan.
12. Pastikan tidak diminta isi ulang partner/job type/status/part.
13. Klik floating WhatsApp di detail.
14. Pastikan report terbuka di WhatsApp.
15. Test Preventive Maintenance: satu S/N hanya boleh 1x dalam 1 bulan.

---

## 11. Plan Berikutnya

Prioritas aman berikutnya:

1. Audit semua form create/edit agar value lama selalu muncul saat edit.
2. Rapikan `JobController` setelah `UpdateJobSaveController` terbukti stabil.
3. Patch menu Penarikan langsung di `app.blade.php` jika layout aman, agar tidak bergantung JS patch.
4. Audit report WhatsApp agar field kosong `-` tidak terlalu banyak.
5. Stabilkan export/import Command Center jika sudah dibutuhkan.
6. Buat dokumentasi deployment cPanel setelah lokal benar-benar stabil.

Jangan tambah modul besar baru sebelum modul ini dites end-to-end:

```text
Asset
Update Job
Battery
Charger
Delivery
Penarikan
Command Center
WhatsApp Share
```
