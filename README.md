# DRR SAKTI GEN V

DRR SAKTI GEN V adalah aplikasi Laravel untuk manajemen operasional field service, aset unit, update job, battery, charger, delivery unit, penarikan unit, kalender planning kerja, command center statistik, Management Sparepart Rental, Usage Review, dan Recommendation Control.

Project ini adalah **project lanjutan**, **bukan project baru**, dan sudah masuk tahap **produksi awal / initial production**. Setiap perubahan wajib kecil, aman, bertahap, bisa dites, dan tidak boleh merusak fitur stabil.

Repository:

```text
https://github.com/nimosasuga/drrsaktigenv.git
```

Local path:

```text
C:\laragon\www\drrsakti
```

Production domain:

```text
https://drrsakti.exprosalab.com
```

Recommended production root:

```text
/home/exprosal/drrsaktigenv
```

Recommended document root:

```text
/home/exprosal/drrsaktigenv/public
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
Cloudflare
```

Command standar lokal setelah pull/perubahan kode:

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

Build production lokal sebelum ZIP/upload:

```powershell
cd C:\laragon\www\drrsakti
php artisan optimize:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear
composer dump-autoload
npm run build
```

Command production/cPanel jika tersedia Terminal/SSH:

```bash
cd /home/exprosal/drrsaktigenv
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
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
11. Jangan commit `.env`, password database, credential cPanel, atau secret lain.

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

Format jawaban ideal:

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

Aturan department:

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

Department isolation diterapkan ke tabel operasional utama.

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
rental_sparepart_items
rental_sparepart_locations
rental_sparepart_stocks
rental_sparepart_movements
rental_sparepart_usage_reviews
sparepart_recommendation_controls
```

Helper department:

```text
app/Support/DepartmentScope.php
app/Support/DepartmentPartnerOptions.php
```

Fungsi penting `DepartmentScope`:

```php
DepartmentScope::apply($query, $tableName);
DepartmentScope::currentDepartment();
DepartmentScope::valueForCreate();
DepartmentScope::userCanSeeAllDepartments();
```

Partner operational form sudah difilter berdasarkan branch + department via:

```text
app/Support/DepartmentPartnerOptions.php
app/Providers/AppServiceProvider.php
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

Jangan menebak department customer/lokasi tanpa dasar. Salah mapping department lebih berbahaya daripada data kosong.

---

## 7. Modul Stabil

### 7.1 Authentication

Stabil:

- Login menggunakan NRPP dan password.
- Logout.
- Dashboard.
- Auth middleware.

---

### 7.2 Subscription

Stabil:

- Subscription index.
- Payment.
- Waiting approval.
- Middleware `CheckSubscription`.
- Konfirmasi pembayaran redirect ke WhatsApp admin.
- Waiting page punya tombol Chat Admin WhatsApp manual.

---

### 7.3 Super Admin / Admin Users

Stabil:

- Verifikasi lisensi.
- Manajemen pengguna.
- Middleware `CheckSuperAdmin`.
- Field user `position` dan `department`.

Catatan: `department` harus diisi untuk user operasional agar isolasi data berjalan benar.

---

### 7.4 Unit Asset / Manajemen Aset

Stabil:

- Grouping berdasarkan `customer/location`.
- Searchbox dan filter.
- User biasa read-only.
- CRUD hanya privileged role sesuai department.
- Histori pekerjaan memakai `serial_number`.
- Status asset `DITARIK` dipakai untuk unit yang sudah ditarik.
- Department scope aktif.

Status asset penting:

```text
DITARIK
```

Jika unit masuk Penarikan Unit, status asset berubah menjadi `DITARIK`.

---

### 7.5 Update Job

Stabil:

- Index/create/edit/show/destroy.
- Search asset by serial number stabil melalui `UpdateJobAssetSearchController`.
- Create/update save flow distabilkan melalui `UpdateJobSaveController`.
- Mobile compact seperti aplikasi Android.
- Floating add button.
- Histori rekomendasi part berdasarkan serial number.
- Asset dengan status `DITARIK` tidak boleh dipakai untuk Update Job.
- Field `year` dan `nomor_lambung` sudah didukung.
- Department scope aktif.
- Partner dropdown sudah terisolasi by branch + department.

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

Histori rekomendasi part di form Update Job hanya menampilkan kolom:

```text
Tanggal
No Job
Part Number
Part Name
Qty
Status
```

---

### 7.6 Management Battery

Stabil:

- Index/create/edit/show.
- Autocomplete asset.
- Install parts.
- Recommendation parts.
- UI mengikuti Update Job dengan nuansa emerald/lime/cyan.
- Grouping bertingkat.
- Grafik pekerjaan populer Top 1-3.
- Share WhatsApp floating button aktif di detail Battery.
- Department scope aktif.
- Partner dropdown terisolasi by branch + department.

---

### 7.7 Management Charger

Stabil:

- Index/create/edit/show/update/destroy.
- Autocomplete asset.
- Install parts.
- Recommendation parts.
- UI mengikuti Update Job dengan nuansa amber/violet/indigo.
- Grouping bertingkat.
- Grafik pekerjaan populer Top 1-3.
- Share WhatsApp floating button aktif di detail Charger.
- Department scope aktif.
- Partner dropdown terisolasi by branch + department.

---

### 7.8 Delivery Unit

Stabil:

- Migration/model/controller/route/view sudah dibuat.
- Index/create/show/edit/store/update/destroy.
- Search asset serial number.
- `job_type` fixed: `DELIVERY UNIT`.
- `status_unit`: `RFU / BREAKDOWN`.
- PLANNER tidak boleh create Delivery Unit.
- Edit/delete hanya PIC atau privileged role.
- UI mengikuti Update Job dengan nuansa purple/sky/cyan.
- Grouping bertingkat.
- Share WhatsApp floating button aktif di detail Delivery.
- Department scope aktif.
- Partner dropdown terisolasi by branch + department.

---

### 7.9 Penarikan Unit

Stabil:

- Migration/controller/route/view sudah dibuat.
- Controller memakai `DB::table('penarikans')`.
- Index/create/show/edit/store/update/destroy.
- PLANNER tidak boleh create.
- Edit/delete hanya PIC atau privileged role.
- `job_type` fixed: `TARIK UNIT`.
- `status_unit`: `RFU / BREAKDOWN`.
- Autocomplete S/N mulai 1 karakter.
- Customer/location/unit_type/year/hour_meter otomatis dan readonly.
- Autosave draft via localStorage.
- Detail Penarikan menampilkan Battery 2 dan Trolly 2-3.
- Setelah simpan/update Penarikan, status `unit_assets.status` sesuai serial number berubah menjadi `DITARIK`.
- Share WhatsApp floating button aktif di detail Penarikan.
- Department scope aktif melalui controller.
- Partner dropdown terisolasi by branch + department.

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

Aturan kalender:

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

Analisa performa:

- Produktivitas per bulan.
- Beban kerja per customer/location.
- Rasio RFU vs Breakdown.
- Unit paling sering bermasalah.
- Rekomendasi part terbanyak.
- Status per PIC.

File penting:

```text
app/Http/Controllers/CommandCenterController.php
app/Http/Controllers/CommandCenterCsvController.php
resources/views/command-center/index.blade.php
```

---

## 10. Management Sparepart Rental

Management Sparepart Rental adalah modul stok sparepart khusus department `RENTAL`.

Route utama:

```text
/rental-spareparts
/rental-spareparts/in/create
/rental-spareparts/out/create
/rental-spareparts/movements
/rental-spareparts/reviews
/rental-spareparts/import-batches
/rental-spareparts/adjustments/create
```

Tabel utama:

```text
rental_sparepart_items
rental_sparepart_locations
rental_sparepart_stocks
rental_sparepart_movements
rental_sparepart_usage_reviews
rental_sparepart_import_batches
```

File penting:

```text
app/Models/RentalSparepartItem.php
app/Models/RentalSparepartLocation.php
app/Models/RentalSparepartStock.php
app/Models/RentalSparepartMovement.php
app/Models/RentalSparepartUsageReview.php
app/Models/RentalSparepartImportBatch.php
app/Http/Controllers/RentalSparepartController.php
app/Http/Controllers/RentalSparepartOutController.php
app/Http/Controllers/RentalSparepartMovementController.php
app/Http/Controllers/RentalSparepartUsageReviewController.php
app/Http/Controllers/RentalSparepartUsageReviewApprovalController.php
app/Http/Controllers/RentalSparepartStockController.php
app/Http/Controllers/RentalSparepartStockExportController.php
app/Http/Controllers/RentalSparepartImportController.php
app/Http/Controllers/RentalSparepartImportBatchController.php
app/Http/Controllers/RentalSparepartAdjustmentImportController.php
```

Fitur stabil:

- Dashboard stok sparepart.
- Filter stok.
- Active Stock / Archived Stock.
- Export Active/Archived Stock CSV Excel-friendly dengan delimiter `;`.
- Barang Masuk / Part IN.
- Barang Keluar / Part OUT.
- Histori Movement IN/OUT/ADJUSTMENT.
- Import Stok Awal / Barang Masuk Massal.
- Preview import sebelum confirm.
- Import batch history.
- Rollback import batch.
- Correction / Adjustment Stock.
- Review Usage.
- Archive / Restore Stock.

Archive/restore behavior:

```text
Archive stock:
- Tidak hard delete.
- stock_lifecycle_status = ARCHIVED.
- qty_on_hand menjadi 0.
- qty lama disimpan di archived_qty_on_hand.
- Movement ADJUSTMENT dibuat.

Restore stock:
- stock_lifecycle_status = ACTIVE.
- qty_on_hand dikembalikan dari archived_qty_on_hand.
- Movement ADJUSTMENT dibuat.
```

Import template sparepart memakai delimiter:

```text
;
```

Parser import tetap bisa membaca:

```text
;
,
```

---

## 11. Update Job Install Part → Usage Review → Movement OUT

Alur Tahap 9 sudah aktif:

```text
Mekanik input install part di Update Job
→ sistem mencari stok rental
→ membuat Usage Review
→ jika cocok, qty_reserved bertambah
→ koordinator approve
→ qty_on_hand berkurang
→ movement OUT dibuat
```

File penting:

```text
app/Support/RentalSparepartUsageReviewService.php
app/Observers/JobInstallPartObserver.php
app/Http/Controllers/RentalSparepartUsageReviewApprovalController.php
app/Support/SparepartRecommendationInstallationSyncService.php
```

Pencocokan stok:

```text
1. Part Number + No Job cocok
2. Part Number + SN Unit cocok
3. Part Number saja
4. Tidak ditemukan
```

Status review:

```text
PENDING_REVIEW
NEED_SOURCE_SELECTION
APPROVED
REJECTED
CANCELLED_BY_JOB_EDIT
```

Approval review hanya boleh memakai stock:

```text
department = RENTAL
stock_lifecycle_status = ACTIVE
```

Stock `ARCHIVED` tidak boleh dipakai untuk approval.

---

## 12. Recommendation Control

Recommendation Control mengubah rekomendasi sparepart dari Update Job menjadi pipeline kebutuhan part.

Route utama:

```text
/sparepart-recommendations
```

Tabel utama:

```text
sparepart_recommendation_controls
```

File penting:

```text
app/Models/SparepartRecommendationControl.php
app/Http/Controllers/SparepartRecommendationControlController.php
app/Http/Controllers/UpdateJobRecommendationHistoryController.php
app/Support/SparepartRecommendationControlService.php
app/Support/SparepartRecommendationSupplyStockService.php
app/Support/SparepartRecommendationInstallationSyncService.php
resources/views/sparepart-recommendations/index.blade.php
resources/js/update-job-recommendation-history.js
resources/js/rental-sparepart-sidebar-link.js
```

Status recommendation:

```text
RECOMMENDED
REVIEWED
APPROVED
REJECTED
NEED_SUPPLY
SUPPLIED
PARTIAL_INSTALLED
INSTALLED
CLOSED
CANCELLED
```

Supply status:

```text
NOT_SUPPLIED
NEED_SUPPLY
PARTIAL_SUPPLIED
SUPPLIED
NOT_REQUIRED
```

Source type:

```text
STOCK
PURCHASE
MANUAL
BORROWED
```

Alur aktif:

```text
Mekanik input Recommendation Part di Update Job
→ sistem membuat record sparepart_recommendation_controls
→ koordinator review/approve/need supply/mark supplied/close
→ jika Mark Supplied tanpa Source Stock Existing, sistem membuat Stock IN baru
→ jika install part disetujui di Usage Review, qty_installed naik otomatis
→ status menjadi PARTIAL_INSTALLED atau CLOSED
```

Mark Supplied behavior:

```text
Jika Source Stock Existing kosong:
- Membuat / update item sparepart.
- Membuat / update lokasi penyimpanan.
- Membuat stock ACTIVE.
- Menambah qty_on_hand.
- Membuat movement IN.
- Mengisi customer, location, type unit, dan SN unit otomatis dari rekomendasi Update Job.
- Menyimpan no job jika diisi.

Jika Source Stock Existing dipilih:
- Tidak membuat stok IN baru.
- Menandai supply dari stok existing.
- Deteksi cross allocation jika SN source stock berbeda dari SN rekomendasi.
```

Histori rekomendasi part di Update Job hanya menampilkan ringkasan:

```text
Tanggal
No Job
Part Number
Part Name
Qty
Status
```

Detail lengkap kontrol rekomendasi tetap ada di:

```text
/sparepart-recommendations
```

---

## 13. WhatsApp Share Report

Share WhatsApp aktif untuk detail:

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

---

## 14. Menu / Navigation

Main Menu/sidebar sudah memakai nama bahasa Inggris dan diurutkan A-Z.

Menu utama:

```text
Asset Management
Command Center
Dashboard
Management Sparepart
My Profile
Recommendation Control
```

Bottom nav:

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

JS menu helper:

```text
resources/js/rental-sparepart-sidebar-link.js
resources/js/penarikan-menu-link.js
```

---

## 15. Production Deployment cPanel

Recommended structure:

```text
/home/exprosal/drrsaktigenv/.env
/home/exprosal/drrsaktigenv/app
/home/exprosal/drrsaktigenv/bootstrap
/home/exprosal/drrsaktigenv/config
/home/exprosal/drrsaktigenv/database
/home/exprosal/drrsaktigenv/public
/home/exprosal/drrsaktigenv/resources
/home/exprosal/drrsaktigenv/routes
/home/exprosal/drrsaktigenv/storage
/home/exprosal/drrsaktigenv/vendor
/home/exprosal/drrsaktigenv/artisan
```

Document root subdomain:

```text
/home/exprosal/drrsaktigenv/public
```

Jangan arahkan subdomain ke root Laravel. Domain harus masuk ke folder `public`.

File `.env` production berada di:

```text
/home/exprosal/drrsaktigenv/.env
```

Jangan letakkan `.env` di:

```text
/home/exprosal/drrsaktigenv/public/.env
```

Database production:

```text
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=exprosal_exprosa_drrsakti
DB_USERNAME=exprosal_exprosa_drruser
DB_PASSWORD=ISI_DI_SERVER_JANGAN_COMMIT
```

Recommended `.env` production settings:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://drrsakti.exprosalab.com
LOG_LEVEL=error
SESSION_DRIVER=file
SESSION_DOMAIN=.exprosalab.com
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

File/folder yang tidak boleh masuk ZIP upload:

```text
.env
.git
node_modules
storage/logs/*.log
database/*.sqlite
```

File/folder yang wajib ada di ZIP upload:

```text
app
bootstrap
config
database
public
resources
routes
storage
vendor
artisan
composer.json
composer.lock
package.json
vite.config.js
public/build
```

PowerShell ZIP clean:

```powershell
$src = "C:\laragon\www\drrsakti"
$stage = "C:\laragon\www\drrsakti_upload_clean"
$zip = "C:\laragon\www\drrsakti_upload_clean.zip"

if (Test-Path $stage) { Remove-Item $stage -Recurse -Force }
if (Test-Path $zip) { Remove-Item $zip -Force }

New-Item -ItemType Directory -Path $stage | Out-Null

robocopy $src $stage /E `
/XD ".git" "node_modules" ".idea" ".vscode" `
/XF ".env" "*.log" "*.sqlite" "Thumbs.db" "desktop.ini"

Compress-Archive -Path "$stage\*" -DestinationPath $zip -Force
Write-Host "ZIP SELESAI:" $zip
```

Check ZIP result:

```powershell
Test-Path "C:\laragon\www\drrsakti_upload_clean\.env"
Test-Path "C:\laragon\www\drrsakti_upload_clean\node_modules"
Test-Path "C:\laragon\www\drrsakti_upload_clean\.git"
Test-Path "C:\laragon\www\drrsakti_upload_clean\public\build"
Test-Path "C:\laragon\www\drrsakti_upload_clean\vendor"
```

Expected:

```text
False  .env
False  node_modules
False  .git
True   public/build
True   vendor
```

---

## 16. Production Database Import

Export dari lokal memakai HeidiSQL sebagai full SQL, bukan CSV per tabel.

Database lokal:

```text
drrsakti_db
```

Database cPanel:

```text
exprosal_exprosa_drrsakti
```

Sebelum export lokal, boleh kosongkan tabel sementara:

```sql
SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE sessions;
TRUNCATE TABLE cache;
TRUNCATE TABLE cache_locks;
TRUNCATE TABLE jobs;
TRUNCATE TABLE job_batches;
TRUNCATE TABLE failed_jobs;
SET FOREIGN_KEY_CHECKS=1;
```

Jika tabel tidak ada, hapus baris tersebut.

HeidiSQL export setting:

```text
Export database as SQL
Pilih semua table
Structure: CREATE
Data: INSERT
Centang DROP TABLE jika tersedia
Encoding: UTF-8
Output: Single .sql file
```

Jika phpMyAdmin error pada baris seperti ini:

```sql
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
```

Biasanya aman jika semua tabel sudah masuk, karena itu hanya restore setting foreign key di akhir import. Setelah import, jalankan:

```sql
SET FOREIGN_KEY_CHECKS=1;
SHOW TABLES;
SELECT COUNT(*) AS total_users FROM users;
SELECT COUNT(*) AS total_migrations FROM migrations;
SELECT COUNT(*) AS total_stocks FROM rental_sparepart_stocks;
SELECT COUNT(*) AS total_recommendations FROM sparepart_recommendation_controls;
```

---

## 17. robots.txt dan .htaccess Production

File robots harus berada di:

```text
/home/exprosal/drrsaktigenv/public/robots.txt
```

Recommended robots.txt agar tidak diindex:

```txt
User-agent: *
Disallow: /

User-agent: Googlebot
Disallow: /

User-agent: Bingbot
Disallow: /

User-agent: GPTBot
Disallow: /

User-agent: ChatGPT-User
Disallow: /

User-agent: CCBot
Disallow: /

User-agent: Google-Extended
Disallow: /

User-agent: anthropic-ai
Disallow: /

User-agent: ClaudeBot
Disallow: /

User-agent: PerplexityBot
Disallow: /

User-agent: Applebot-Extended
Disallow: /

User-agent: Bytespider
Disallow: /
```

File `.htaccess` aktif berada di:

```text
/home/exprosal/drrsaktigenv/public/.htaccess
```

Bagian cPanel PHP handler jangan dihapus:

```apache
# php -- BEGIN cPanel-generated handler, do not edit
<IfModule mime_module>
  AddHandler application/x-httpd-ea-php83 .php .php8 .phtml
</IfModule>
# php -- END cPanel-generated handler, do not edit
```

Jika memakai Cloudflare:

```text
SSL/TLS encryption mode: Full atau Full (strict)
Always Use HTTPS: ON
Automatic HTTPS Rewrites: ON
Jangan pakai Flexible
```

Untuk aplikasi login, Cloudflare cache sebaiknya bypass:

```text
URL: drrsakti.exprosalab.com/*
Cache: Bypass
```

---

## 18. File Penting Jangan Dihapus

```text
app/Support/DepartmentScope.php
app/Support/DepartmentPartnerOptions.php
app/Support/RentalSparepartUsageReviewService.php
app/Support/SparepartRecommendationControlService.php
app/Support/SparepartRecommendationSupplyStockService.php
app/Support/SparepartRecommendationInstallationSyncService.php
app/Observers/JobInstallPartObserver.php
app/Models/SparepartRecommendationControl.php
app/Models/RentalSparepartStock.php
app/Http/Controllers/CalendarController.php
app/Http/Controllers/CommandCenterController.php
app/Http/Controllers/CommandCenterCsvController.php
app/Http/Controllers/UpdateJobAssetSearchController.php
app/Http/Controllers/UpdateJobSaveController.php
app/Http/Controllers/UpdateJobRecommendationHistoryController.php
app/Http/Controllers/RentalSparepartUsageReviewApprovalController.php
app/Http/Controllers/SparepartRecommendationControlController.php
app/Http/Controllers/PenarikanController.php
resources/js/update-job-recommendation-history.js
resources/js/rental-sparepart-sidebar-link.js
resources/js/rental-sparepart-archive-ui.js
resources/js/update-job-copy-report.js
resources/js/operational-share-report.js
```

File yang sudah dihapus dan jangan dibuat/import lagi:

```text
resources/js/update-job-extra-fields.js
resources/js/update-job-extra-fields-stable.js
```

---

## 19. Testing Minimum Setelah Pull / Upload

Jalankan lokal:

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
7. Simpan Update Job.
8. Pastikan redirect ke detail job.
9. Edit job, ubah `action` saja.
10. Simpan.
11. Pastikan tidak diminta isi ulang partner/job type/status/part.
12. Test Preventive Maintenance: satu S/N hanya boleh 1x dalam 1 bulan.

Test department isolation:

1. Login sebagai `koordinator RENTAL`.
2. Pastikan data SERVICE tidak muncul di Asset, Update Job, Battery, Charger, Delivery, Penarikan, Command Center, Calendar, Sparepart, dan Recommendation Control.
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

Test Sparepart Rental:

1. Buka `/rental-spareparts`.
2. Cek Active Stock / Archived Stock.
3. Archive satu stok.
4. Cek stok pindah ke Archived Stock.
5. Restore stok.
6. Export Active Stock.
7. Export Archived Stock.
8. Import CSV Barang Masuk.
9. Confirm import.
10. Cek Import History.
11. Rollback batch.
12. Correction Stock.
13. Cek Histori Movement.

Test Usage Review:

1. Buat Update Job department RENTAL dengan Install Part.
2. Buka `/rental-spareparts/reviews`.
3. Pastikan review muncul.
4. Approve review.
5. Pastikan movement OUT dibuat.
6. Pastikan stock ARCHIVED tidak bisa dipakai approve.

Test Recommendation Control:

1. Buat Update Job dengan Recommendation Part.
2. Buka `/sparepart-recommendations`.
3. Pastikan record control muncul.
4. Mark Reviewed / Approve / Need Supply.
5. Mark Supplied tanpa Source Stock Existing.
6. Pastikan Stock IN tercipta di `/rental-spareparts`.
7. Pastikan movement IN tercipta di `/rental-spareparts/movements`.
8. Buat Install Part dengan SN + Part Number yang sama.
9. Approve Usage Review.
10. Pastikan qty_installed naik dan status recommendation menjadi PARTIAL_INSTALLED atau CLOSED.

---

## 20. Status Terakhir

Status terakhir sebelum production upload:

```text
- Department isolation aktif.
- Partner operational form difilter department.
- Calendar planning aktif dan isolated.
- Management Sparepart Rental aktif.
- Import/export sparepart aktif dan Excel-friendly.
- Archive/Restore stock aktif.
- Usage Review dari Update Job Install Part aktif.
- Approval Usage Review membuat movement OUT.
- Recommendation Control aktif.
- Mark Supplied dari Recommendation Control bisa membuat Stock IN.
- Histori rekomendasi di Update Job sudah diringkas menjadi 6 kolom.
- robots.txt disiapkan agar tidak diindex.
- cPanel deployment diarahkan ke /public.
```
