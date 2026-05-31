# DRR SAKTI GEN V

DRR SAKTI GEN V adalah aplikasi Laravel untuk manajemen operasional field service, aset unit, update job, battery, charger, delivery unit, penarikan unit, subscription, kalender planning kerja, dan command center statistik.

Project ini adalah **project lanjutan**, **bukan project baru**, dan sudah masuk **produksi awal**. Setiap perubahan wajib kecil, aman, bertahap, bisa dites, dan tidak boleh merusak fitur stabil.

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
cPanel Production
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

Command production/cPanel setelah upload:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

---

## 2. Aturan Produksi

1. Project ini **bukan project baru**.
2. Jangan ubah arsitektur besar tanpa alasan kuat.
3. Jangan hapus fitur, route, tombol, field, tampilan, atau alur yang sudah stabil tanpa instruksi eksplisit.
4. Jangan ubah database kalau bisa diselesaikan di controller/view.
5. Jika harus menyentuh database, perubahan wajib kecil, jelas, dan bisa di-rollback.
6. Perubahan harus aman, bertahap, dan mudah dites.
7. Baca `README.md`, route, controller, model, migration, dan view target sebelum memberi patch.
8. Jangan pakai pendekatan tambal-menambal yang berisiko merusak fitur produksi awal.
9. Jangan mengubah search asset yang sudah stabil tanpa instruksi eksplisit.
10. Jangan melakukan rewrite besar hanya untuk masalah kecil.

Kolom modern yang digunakan sebagai standar:

```text
customer
location
serial_number
unit_type
status
year
nomor_lambung
department
position
```

Kolom lama tidak boleh dijadikan standar utama:

```text
nama_pelanggan
lokasi
```

Fallback boleh hanya jika diperlukan untuk kompatibilitas data lama.

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
9. Jangan mengubah endpoint vital yang sudah stabil kecuali memang diminta.

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

## 4. Role, Position, dan Department

Role utama:

```text
super_admin
admin
koordinator
sect_head
mekanik / user biasa
PLANNER
```

Kolom user terbaru:

```text
users.position   = FIELD / FMC
users.department = RENTAL / SERVICE
```

Aturan department terbaru:

```text
koordinator RENTAL  -> hanya melihat/mengelola data RENTAL
koordinator SERVICE -> hanya melihat/mengelola data SERVICE
sect_head RENTAL    -> hanya melihat/mengelola data RENTAL
sect_head SERVICE   -> hanya melihat/mengelola data SERVICE
admin               -> semua department
super_admin         -> semua department
mekanik/user biasa  -> read-only sesuai department
```

Role yang boleh lintas department:

```text
admin
super_admin
```

Role yang tidak boleh lintas department:

```text
koordinator
sect_head
mekanik / user biasa
```

Hak akses umum:

- `super_admin`: full access.
- `admin`: full akses operasional lintas department.
- `koordinator`: privileged access sesuai department.
- `sect_head`: privileged access sesuai department.
- `mekanik/user biasa`: akses terbatas/read-only sesuai department dan modul.
- `PLANNER`: tidak boleh create Delivery Unit dan tidak boleh create Penarikan Unit.

---

## 5. Department Isolation Core

Department isolation sudah diterapkan ke tabel operasional utama.

Tabel yang memiliki kolom `department`:

```text
users
unit_assets
update_jobs
batteries
chargers
deliveries
penarikans
work_plannings
```

Helper department:

```text
app/Support/DepartmentScope.php
```

Fungsi penting:

```php
DepartmentScope::apply($query, $tableName);
DepartmentScope::currentDepartment();
DepartmentScope::valueForCreate();
DepartmentScope::userCanSeeAllDepartments();
```

Global scope department aktif di model:

```text
app/Models/UnitAsset.php
app/Models/Job.php
app/Models/Battery.php
app/Models/Charger.php
app/Models/Delivery.php
```

Penarikan Unit memakai `DB::table('penarikans')`, jadi isolasi department dipasang langsung di:

```text
app/Http/Controllers/PenarikanController.php
```

Efek department isolation:

```text
RENTAL tidak melihat SERVICE
SERVICE tidak melihat RENTAL
admin/super_admin melihat semua
```

Data baru otomatis mengisi `department` dari user login, kecuali admin/super_admin memilih jalur khusus yang memang lintas department.

---

## 6. Backfill Department Data Lama

Backfill data lama tersedia melalui Artisan command di:

```text
routes/console.php
```

Command audit:

```powershell
php artisan drr:department-audit
php artisan drr:department-audit --details
```

Backfill dari `users.department`:

```powershell
php artisan drr:department-backfill-users
php artisan drr:department-backfill-users --commit
```

Backfill dari `unit_assets.department`:

```powershell
php artisan drr:department-backfill-assets
php artisan drr:department-backfill-assets --commit
```

Mapping manual:

```powershell
php artisan drr:department-map RENTAL --customer="NAMA CUSTOMER" --commit
php artisan drr:department-map SERVICE --customer="NAMA CUSTOMER" --location="LOKASI" --commit
php artisan drr:department-map RENTAL --serial="SERIAL_NUMBER" --commit
php artisan drr:department-map SERVICE --customer="PT ABC" --table=unit_assets --commit
```

Semua command mapping/backfill default **dry-run** jika tidak memakai `--commit`.

Urutan backfill aman:

```powershell
php artisan drr:department-audit --details
php artisan drr:department-backfill-users
php artisan drr:department-backfill-users --commit
php artisan drr:department-audit --details
php artisan drr:department-backfill-assets
php artisan drr:department-backfill-assets --commit
php artisan drr:department-audit --details
```

Jangan menebak department customer/lokasi tanpa dasar. Salah mapping department lebih berbahaya daripada data kosong.

---

## 7. Modul Stabil

### 7.1 Authentication

Stabil:

- Login
- Logout
- Dashboard
- Auth middleware
- Login menggunakan NRPP dan password

---

### 7.2 Subscription

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

### 7.3 Super Admin / Admin Users

Stabil:

- Verifikasi lisensi
- Manajemen pengguna
- Middleware `CheckSuperAdmin`
- Field user terbaru:

```text
position
 department
```

Catatan: `department` harus diisi untuk user operasional agar isolasi data berjalan benar.

---

### 7.4 Unit Asset / Manajemen Aset

Stabil:

- Grouping berdasarkan `customer/location`
- Searchbox dan filter
- User biasa read-only
- CRUD hanya privileged role sesuai department
- Histori pekerjaan memakai `serial_number`
- Status asset `DITARIK` dipakai untuk unit yang sudah ditarik
- Department scope aktif

Status asset penting:

```text
DITARIK
```

Jika unit masuk Penarikan Unit, status asset berubah menjadi `DITARIK`.

Histori asset saat ini masih berdasarkan:

```text
unit_assets.serial_number = update_jobs.serial_number
```

`unit_asset_id` bisa dibuat nanti setelah modul utama benar-benar stabil.

---

### 7.5 Update Job

Stabil:

- Index/create/edit/show/destroy
- Search asset by serial number stabil melalui:

```text
app/Http/Controllers/UpdateJobAssetSearchController.php
```

Route search asset:

```php
Route::get('/update-jobs/search-assets', UpdateJobAssetSearchController::class)->name('update-jobs.search-assets');
```

Search asset **jangan diutak-atik sembarangan** karena ini jalur vital form create/edit.

UI index grouped bertingkat:

```text
Bulan & Tahun
└── PIC
    └── Customer / Location
        └── Detail Unit
```

Stabil lain:

- Mobile compact seperti aplikasi Android
- Floating add button
- Histori rekomendasi part berdasarkan serial number
- Asset dengan status `DITARIK` tidak boleh dipakai untuk Update Job
- Blocking dilakukan di UI dan controller
- Field `year` dan `nomor_lambung` sudah didukung
- Field `year` dan `nomor_lambung` dipasang langsung di Blade, bukan via JS injector
- Department scope aktif

File JS injector lama sudah dihapus dan jangan dibuat/import lagi:

```text
resources/js/update-job-extra-fields.js
resources/js/update-job-extra-fields-stable.js
```

Jangan aktifkan ulang import ini:

```js
import "./update-job-extra-fields";
```

Create/update save flow distabilkan melalui:

```text
app/Http/Controllers/UpdateJobSaveController.php
```

Route store/update Update Job:

```php
Route::post('/update-jobs', [UpdateJobSaveController::class, 'store'])->name('update-jobs.store');
Route::put('/update-jobs/{id}', [UpdateJobSaveController::class, 'update'])->name('update-jobs.update');
Route::patch('/update-jobs/{id}', [UpdateJobSaveController::class, 'update']);
Route::resource('update-jobs', JobController::class)->except(['store', 'update']);
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

Preventive Maintenance dibatasi:

```text
1 serial_number hanya boleh 1x Preventive Maintenance dalam 1 bulan.
```

---

### 7.6 Management Battery

Stabil:

- Index/create/edit/show
- Autocomplete asset
- Install parts
- Recommendation parts
- UI mengikuti Update Job dengan nuansa emerald/lime/cyan
- Grouping bertingkat
- Ada `Pekerjaan Populer Top 1-3` dengan grafik batang responsive
- Share WhatsApp floating button aktif di detail Battery
- Department scope aktif

---

### 7.7 Management Charger

Stabil:

- Index/create/edit/show/update/destroy
- Autocomplete asset
- Install parts
- Recommendation parts
- UI mengikuti Update Job dengan nuansa amber/violet/indigo
- Grouping bertingkat
- Ada `Pekerjaan Populer Top 1-3`
- Share WhatsApp floating button aktif di detail Charger
- Department scope aktif

---

### 7.8 Delivery Unit

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
- Department scope aktif

---

### 7.9 Penarikan Unit

Stabil:

- Migration/controller/route/view sudah dibuat
- Controller memakai `DB::table('penarikans')`
- Index/create/show/edit/store/update/destroy
- PLANNER tidak boleh create
- Edit/delete hanya PIC atau privileged role
- `job_type` fixed: `TARIK UNIT`
- `status_unit`: `RFU / BREAKDOWN`
- Autocomplete S/N mulai 1 karakter
- Customer/location/unit_type/year/hour_meter otomatis dan readonly
- Autosave draft via localStorage
- Warning keluar halaman tidak muncul saat klik Simpan
- Detail Penarikan menampilkan Battery 2 dan Trolly 2-3
- Tombol delete Penarikan ada di detail dengan permission sama seperti Update Job
- Setelah simpan/update Penarikan, status `unit_assets.status` sesuai serial_number berubah menjadi `DITARIK`
- Backup update status DITARIK juga sudah disiapkan di controller agar aman jika cPanel menolak trigger
- Share WhatsApp floating button aktif di detail Penarikan
- Report Penarikan tidak memakai `PENARIKAN CODE`
- Department scope aktif melalui controller

Mendukung:

```text
Battery Type 1
Battery SN 1
Battery Type 2
Battery SN 2
Trolly 1
Trolly 2
Trolly 3
```

Trigger MySQL yang pernah dipakai/direncanakan:

```text
trg_penarikans_after_insert_status
trg_penarikans_after_update_status
```

---

## 8. Kalender / Calendar Planning

Kalender sudah berubah dari view statis menjadi modul planning kerja.

File penting:

```text
app/Http/Controllers/CalendarController.php
app/Models/WorkPlanning.php
resources/views/calendar/index.blade.php
database/migrations/2026_05_31_000002_create_work_plannings_table.php
```

Tabel:

```text
work_plannings
```

Form planning berisi:

```text
Tanggal
Mekanik
Partner
Customer
Lokasi
Jenis Pekerjaan
Catatan
```

Aturan kalender terbaru:

```text
Semua user RENTAL melihat semua planning department RENTAL.
Semua user SERVICE melihat semua planning department SERVICE.
admin dan super_admin melihat semua planning.
```

Aksi create/update/delete planning boleh untuk:

```text
koordinator
sect_head
admin
super_admin
```

Read-only:

```text
mekanik / user biasa
```

Aturan department kalender:

- Customer/lokasi kalender berasal dari `unit_assets.department`.
- Planning tidak bisa memilih customer/lokasi department lain.
- Partner harus satu department dengan mekanik.
- Koordinator dan sect_head hanya mengelola planning department sendiri.
- Admin dan super_admin boleh lintas department.

Validasi server-side sudah mencegah manipulasi request manual dari browser.

---

## 9. Command Center

Command Center dibuat untuk role:

```text
koordinator
sect_head
admin
super_admin
```

Fitur:

- Statistik performa operasional.
- Export CSV Excel-friendly.
- Import CSV insert-only.
- Filter dashboard berdasarkan department.
- Export CSV berdasarkan department.
- Import CSV untuk user non-admin/super_admin dipaksa memakai department user login.

Filter:

```text
modul
PIC
customer
location
status
bulan
tahun
```

Analisa performa:

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

File penting:

```text
app/Http/Controllers/CommandCenterController.php
app/Http/Controllers/CommandCenterCsvController.php
resources/views/command-center/index.blade.php
```

---

## 10. WhatsApp Share Report

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

Report tidak memakai emoji dan tidak memakai footer:

```text
Dibagikan dari DRR SAKTI GEN V oleh ...
```

Format report disamakan antar modul:

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

## 11. Bottom Navigation

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

Penarikan Unit pernah diarahkan lewat JS patch:

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

## 12. File Penting

Jangan hapus tanpa alasan:

```text
app/Support/DepartmentScope.php
app/Models/WorkPlanning.php
app/Http/Controllers/CalendarController.php
app/Http/Controllers/CommandCenterController.php
app/Http/Controllers/CommandCenterCsvController.php
app/Http/Controllers/UpdateJobAssetSearchController.php
app/Http/Controllers/UpdateJobSaveController.php
app/Http/Controllers/UpdateJobShareController.php
app/Http/Controllers/OperationalShareController.php
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

## 13. Testing Minimum Setelah Pull

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

Jika ada migration:

```powershell
php artisan migrate
```

Test umum:

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

Test department isolation:

1. Login sebagai `koordinator RENTAL`.
2. Pastikan data SERVICE tidak muncul di Asset, Update Job, Battery, Charger, Delivery, Penarikan, Command Center, dan Calendar.
3. Login sebagai `sect_head SERVICE`.
4. Pastikan data RENTAL tidak muncul.
5. Login sebagai `admin` atau `super_admin`.
6. Pastikan semua department terlihat.

Test Calendar:

1. Buat planning RENTAL sebagai koordinator/sect_head RENTAL.
2. Login sebagai mekanik RENTAL.
3. Pastikan semua planning RENTAL terlihat walau mekanik tersebut bukan PIC/partner yang ditunjuk.
4. Pastikan mekanik tidak melihat form create/update/delete.
5. Login sebagai mekanik SERVICE.
6. Pastikan planning RENTAL tidak muncul.

Test Command Center:

1. Login sebagai koordinator/sect_head RENTAL.
2. Buka `/command-center`.
3. Pastikan filter PIC/customer/location hanya department RENTAL.
4. Export CSV dan pastikan data SERVICE tidak ikut.
5. Import CSV sebagai user SERVICE dan pastikan department dipaksa SERVICE.

---

## 14. Commit Penting Terakhir

```text
4516ce29fb7497efed79b169a79dbbc98b177742 - Calendar: semua user department melihat planning department-nya
166198023ef2cafa9cd45b3441aa588ae1bddcc9 - Calendar hardening customer/lokasi department
70a84c91e9c8c916414a143f0fe4161d297d6b13 - Backfill command department
885f254a07d6846ad283483c1d429736db8e0d42 - Command Center dashboard isolation
74ff810a5851ccf6d0dffdea8dc0ab092442f40e - Command Center CSV isolation
20d3ec0d7a055303b503be7f9a6fc67d44d007e1 - DepartmentScope: sect_head tidak lintas department
```

---

## 15. Plan Berikutnya

Prioritas aman berikutnya:

1. Test end-to-end department isolation di semua role.
2. Audit semua form create/edit agar value lama selalu muncul saat edit.
3. Rapikan `JobController` setelah `UpdateJobSaveController` terbukti stabil.
4. Patch menu Penarikan langsung di `app.blade.php` jika layout aman, agar tidak bergantung JS patch.
5. Audit report WhatsApp agar field kosong `-` tidak terlalu banyak.
6. Buat dokumentasi deployment cPanel setelah lokal benar-benar stabil.

Jangan tambah modul besar baru sebelum modul ini dites end-to-end:

```text
Asset
Update Job
Battery
Charger
Delivery
Penarikan
Calendar Planning
Command Center
WhatsApp Share
Department Isolation
```
