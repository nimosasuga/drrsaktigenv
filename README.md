# DRR SAKTI GEN V

DRR SAKTI GEN V adalah aplikasi Laravel untuk operasional field service dan rental management. Aplikasi ini dipakai untuk mengelola aset unit, update job mekanik, battery, charger, delivery unit, penarikan unit, calendar planning, command center, payment/subscription, PWA, Management Sparepart Rental, Usage Review, dan Recommendation Control.

Project ini adalah **project lanjutan yang sudah masuk production**. Jangan memperlakukan project ini sebagai project baru.

---

## 1. Informasi Project

| Item | Nilai |
|---|---|
| Repository | `https://github.com/nimosasuga/drrsaktigenv.git` |
| Local path | `C:\laragon\www\drrsakti` |
| Production URL | `https://drrsakti.exprosalab.com` |
| Production root cPanel | `/home/exprosal/drrsaktigenv` |
| Production document root | `/home/exprosal/drrsaktigenv/public` |
| Status | Production aktif |

---

## 2. Stack

```text
Laravel 13
PHP 8.3
MySQL
Blade
Tailwind CSS
Vite
Laragon
HeidiSQL
cPanel
Cloudflare
PWA aktif
```

---

## 3. Prinsip Utama

1. Project ini **bukan project baru**.
2. Jangan melakukan rewrite besar untuk masalah kecil.
3. Jangan menghapus tombol, route, field, tampilan, menu, atau fitur stabil tanpa persetujuan eksplisit.
4. Jangan mengubah database tanpa alasan kuat dan persetujuan eksplisit.
5. Jangan menebak nama tabel atau kolom. Cek model, migration, controller, dan view target terlebih dahulu.
6. Perubahan harus kecil, aman, bertahap, mudah dites, dan mudah di-rollback.
7. Jangan commit atau upload `.env`, password database, token, credential cPanel, credential Cloudflare, atau secret apa pun.
8. Jangan upload `node_modules`, `.git`, database lokal, `storage/logs`, atau file tidak perlu ke production.
9. Jangan menjalankan `npm run build` dan upload production sebelum lokal dites aman.
10. Jangan mengganggu fitur stabil yang sedang dipakai operasional.

---

## 4. Command Lokal Standar

Jalankan dari PowerShell:

```powershell
cd C:\laragon\www\drrsakti

git pull origin main
php artisan optimize:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear
composer dump-autoload
npm run dev
```

Jika ada perubahan JavaScript atau CSS:

```powershell
npm run build
```

Jika ada migration baru:

```powershell
php artisan migrate
```

Cek route penting:

```powershell
php artisan route:list
php artisan route:list | findstr update-jobs
php artisan route:list | findstr sparepart-recommendations
```

Cek syntax PHP sebelum upload:

```powershell
php -l app\Http\Controllers\NamaController.php
```

---

## 5. Command Production cPanel

Masuk ke root project:

```bash
cd /home/exprosal/drrsaktigenv
```

Karena default CLI cPanel bisa memakai PHP 8.2, semua command artisan production wajib memakai PHP 8.3:

```bash
/opt/cpanel/ea-php83/root/usr/bin/php artisan optimize:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan config:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan route:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:clear
```

Jika ada migration dan sudah dites lokal:

```bash
/opt/cpanel/ea-php83/root/usr/bin/php artisan migrate --force
```

Jangan menjalankan migration kalau perubahan hanya Blade, controller, route, atau JavaScript biasa.

---

## 6. Aturan Upload ke cPanel

Upload patch kecil saja jika memungkinkan.

Jangan upload:

```text
.env
.git
node_modules
storage/logs
database lokal
file .sql lokal
file .sqlite lokal
vendor jika tidak ada perubahan composer
```

Jika perubahan hanya Blade:

```text
Upload file Blade terkait.
Clear cache Laravel di cPanel.
```

Jika perubahan controller atau route:

```text
Upload controller/route terkait.
Clear cache Laravel di cPanel.
```

Jika perubahan JS/CSS:

```text
1. Jalankan npm run build di lokal.
2. Upload public/build.
3. Clear cache Laravel di cPanel.
4. Purge Cloudflare jika tampilan belum berubah.
```

Jika perubahan PWA:

```text
Jangan ubah PWA tanpa persetujuan eksplisit.
Jika sudah disetujui, upload file PWA terkait dan lakukan cache clear + Cloudflare purge.
```

---

## 7. Struktur Akses User

Project memakai data user berikut:

```text
status_user
branch
position
department
```

Jangan menjadikan `role` sebagai standar utama jika data produksi memakai `status_user`.

Status user utama:

```text
mekanik
koordinator
sect_head
admin
super_admin
```

Department utama:

```text
RENTAL
SERVICE
```

Aturan akses umum:

| User | Akses |
|---|---|
| `super_admin` | Semua fitur dan semua department |
| `admin` | Semua fitur operasional dan semua department |
| `koordinator RENTAL` | Modul RENTAL sesuai izin |
| `sect_head RENTAL` | Modul RENTAL sesuai izin |
| `koordinator SERVICE` | Modul SERVICE sesuai izin |
| `sect_head SERVICE` | Modul SERVICE sesuai izin |
| `mekanik` | Operasional terbatas sesuai kebutuhan |

Akses Management Sparepart dan Recommendation Control:

Boleh:

```text
admin
super_admin
koordinator RENTAL
sect_head RENTAL
```

Tidak boleh:

```text
mekanik RENTAL
mekanik SERVICE
koordinator SERVICE
sect_head SERVICE
```

---

## 8. Department Isolation

Department isolation sudah aktif. Data RENTAL dan SERVICE tidak boleh tercampur.

Aturan utama:

```text
User RENTAL hanya melihat data RENTAL.
User SERVICE hanya melihat data SERVICE.
Admin dan super_admin bisa lintas department.
```

File penting:

```text
app/Support/DepartmentScope.php
app/Support/DepartmentPartnerOptions.php
```

Partner operational form sudah difilter berdasarkan branch dan department.

---

## 9. Modul Stabil

### 9.1 Auth dan Subscription

Stabil:

```text
Login NRPP + password
Subscription package
Payment flow
Waiting verification
Admin/super_admin verifikasi pembayaran
Payment Settings
```

Payment Settings route:

```text
/payment-settings
```

File penting:

```text
app/Models/PaymentSetting.php
app/Http/Controllers/PaymentSettingController.php
resources/views/payment-settings/edit.blade.php
resources/views/subscription/payment.blade.php
app/Http/Controllers/SubscriptionController.php
```

QRIS disimpan di:

```text
public/uploads/payment-settings
```

---

### 9.2 Layout dan Navigasi

Stabil:

```text
Sidebar kiri
Bottom navigation
Popup Job
Profile page
```

Bottom navigation:

```text
Home | Kalender | Job | Ingat | Profile
```

Popup Job:

```text
Update Job
Manajemen Battery
Management Charger
Delivery Unit
Penarikan Unit
```

Jangan membuat menu double. Jangan menyuntik atau menghapus menu dengan JavaScript liar.

File penting:

```text
resources/views/layouts/app.blade.php
```

---

### 9.3 Update Job

Stabil:

```text
Index/create/edit/show/destroy
Search asset by serial number
Multi select Tipe Pekerjaan
Preventive Maintenance 1x per serial number per bulan
PM existing dikunci saat edit
Unit dengan status DITARIK tidak boleh dipakai update job
Install Part terhubung ke Usage Review
Recommendation Part terhubung ke Recommendation Control
Histori rekomendasi part berdasarkan S/N tampil ringkas
Share WhatsApp report
Department scope aktif
```

Tipe pekerjaan resmi:

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

File penting:

```text
app/Http/Controllers/UpdateJobSaveController.php
app/Http/Controllers/UpdateJobPreventiveMaintenanceCheckController.php
app/Http/Controllers/UpdateJobAssetSearchController.php
resources/views/update-jobs/create.blade.php
resources/views/update-jobs/edit.blade.php
resources/views/update-jobs/show.blade.php
resources/js/update-job-field-options.js
resources/js/update-job-preventive-maintenance-check.js
```

Catatan penting:

```text
Jangan mengubah logika PM 1x/bulan tanpa tes create dan edit.
Jangan menghapus lock PM existing saat edit.
Jika ubah JS Update Job, jalankan npm run build dan upload public/build.
```

---

### 9.4 Battery Management

Stabil:

```text
Index/create/edit/show
Autocomplete asset
Install parts
Recommendation parts
Share WhatsApp report
Department scope aktif
Export Command Center termasuk install/recommendation parts
```

---

### 9.5 Charger Management

Stabil:

```text
Index/create/edit/show
Autocomplete asset
Install parts
Recommendation parts
Share WhatsApp report
Department scope aktif
Export Command Center termasuk install/recommendation parts
```

---

### 9.6 Delivery Unit

Stabil:

```text
Index/create/edit/show
Search asset
Share WhatsApp report
Department scope aktif
PLANNER tidak boleh create
```

---

### 9.7 Penarikan Unit

Stabil:

```text
Index/create/edit/show
Search asset
Setelah penarikan disimpan, status unit_assets menjadi DITARIK
Battery dan trolly dicatat
Share WhatsApp report
Department scope aktif
PLANNER tidak boleh create
```

---

### 9.8 Calendar / Planning

Stabil:

```text
Calendar planning aktif
Semua user dalam department sama bisa melihat planning department tersebut
Mekanik hanya melihat
Create/update/delete hanya untuk koordinator, sect_head, admin, super_admin
Data RENTAL dan SERVICE tetap terpisah
```

---

### 9.9 Command Center

Stabil:

```text
Statistik operasional
Export/import CSV
Filter department
Analisa pekerjaan, customer, lokasi, PIC, status, rekomendasi part
Export Update Job, Battery, dan Charger sudah menyertakan install/recommendation parts
```

Jangan ubah Command Center tanpa persetujuan eksplisit.

---

## 10. Management Sparepart Rental

Stabil:

```text
Dashboard stock
Active Stock / Archived Stock
Barang Masuk / Stock IN
Barang Keluar / Stock OUT
Movement IN/OUT/ADJUSTMENT
Import CSV
Export CSV Excel-friendly delimiter ;
Import History
Rollback import batch
Correction stock
Archive / Restore stock
Usage Review
Approval usage
Stock ARCHIVED tidak boleh dipakai approve
```

File penting:

```text
app/Http/Middleware/EnsureRentalSparepartManager.php
app/Support/RentalSparepartUsageReviewService.php
app/Observers/JobInstallPartObserver.php
app/Models/RentalSparepartItem.php
app/Models/RentalSparepartStock.php
app/Models/RentalSparepartMovement.php
app/Models/RentalSparepartUsageReview.php
```

---

## 11. Usage Review

Alur:

```text
Mekanik input Install Part di Update Job
Sistem cek stock sparepart rental
Sistem membuat Usage Review
Koordinator RENTAL approve/reject
Jika approve, stock berkurang
Movement OUT otomatis dibuat
```

Status:

```text
PENDING_REVIEW
NEED_SOURCE_SELECTION
APPROVED
REJECTED
CANCELLED_BY_JOB_EDIT
```

---

## 12. Recommendation Control

Alur:

```text
Mekanik input Recommendation Part di Update Job
Sistem membuat Recommendation Control
Koordinator review/approve/need supply/mark supplied/close
Jika Mark Supplied tanpa source stock existing, sistem membuat Stock IN
Jika install part disetujui, qty_installed naik otomatis
Status menjadi PARTIAL_INSTALLED atau CLOSED sesuai kondisi
```

Status:

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

Route utama:

```text
/sparepart-recommendations
/sparepart-recommendations/units
/sparepart-recommendations/units/export
/sparepart-recommendations/units/{serialNumber}
/sparepart-recommendations/parts
```

Fungsi halaman:

| Halaman | Fungsi |
|---|---|
| `/sparepart-recommendations` | Landing page 2 mode: Mode Unit dan Mode Sparepart |
| `/sparepart-recommendations/units` | Group rekomendasi berdasarkan serial number unit |
| `/sparepart-recommendations/units/{serialNumber}` | Detail rekomendasi per serial number |
| `/sparepart-recommendations/parts` | List sparepart recommendation dan action approval |
| `/sparepart-recommendations/units/export` | Export CSV Excel Indonesia berdasarkan filter atau semua data |

File penting:

```text
app/Models/SparepartRecommendationControl.php
app/Support/SparepartRecommendationControlService.php
app/Support/SparepartRecommendationSupplyStockService.php
app/Support/SparepartRecommendationInstallationSyncService.php
app/Http/Controllers/SparepartRecommendationControlController.php
resources/views/sparepart-recommendations/index.blade.php
resources/views/sparepart-recommendations/units.blade.php
resources/views/sparepart-recommendations/unit-show.blade.php
resources/views/sparepart-recommendations/parts.blade.php
```

Catatan penting:

```text
Jangan menduplikasi action form Recommendation Control di banyak halaman tanpa alasan kuat.
Action approval utama tetap di Mode Sparepart.
Detail Unit boleh punya shortcut ke List Sparepart terfilter.
```

---

## 13. PWA

PWA sudah aktif dan stabil.

Fitur:

```text
Install ke home screen
Manifest aktif
Service worker aktif
Offline fallback aktif
Icon aplikasi aktif
Shortcut Update Job
Shortcut Calendar
```

File PWA:

```text
public/manifest.webmanifest
public/offline.html
public/sw.js
resources/js/pwa-register.js
resources/js/app.js
```

Aturan PWA:

```text
Jangan cache dashboard.
Jangan cache update job.
Jangan cache sparepart.
Jangan cache form kerja.
Jangan cache data user/login.
Cache hanya asset static dan offline fallback.
Jangan ubah PWA tanpa persetujuan eksplisit.
```

Jika tampilan production tidak berubah setelah upload:

```text
1. Pastikan public/build sudah upload jika ada perubahan JS/CSS.
2. Clear Laravel cache.
3. Purge Cloudflare.
4. Tutup PWA total lalu buka ulang.
5. Jika masih bandel, uninstall dan install ulang PWA.
```

---

## 14. File Sensitif yang Jangan Dirubah Sembarangan

```text
resources/views/layouts/app.blade.php
routes/web.php
app/Support/DepartmentScope.php
app/Support/DepartmentPartnerOptions.php
app/Http/Middleware/EnsureRentalSparepartManager.php
app/Support/RentalSparepartUsageReviewService.php
app/Support/SparepartRecommendationControlService.php
app/Support/SparepartRecommendationSupplyStockService.php
app/Support/SparepartRecommendationInstallationSyncService.php
app/Observers/JobInstallPartObserver.php
resources/js/app.js
resources/js/pwa-register.js
public/sw.js
public/manifest.webmanifest
public/offline.html
```

Jika harus mengubah file di atas, perubahan wajib kecil, jelas, dites lokal, dan punya rollback.

---

## 15. Cara Kerja Aman untuk Perubahan Baru

Sebelum edit:

```text
1. Pull repo terbaru.
2. Baca README.md.
3. Baca file target.
4. Cek route/controller/model/view yang terhubung.
5. Pastikan fitur yang disentuh bukan fitur stabil yang dilarang diubah.
```

Saat edit:

```text
1. Ubah file sesedikit mungkin.
2. Jangan menyentuh database kalau tidak perlu.
3. Jangan mengubah navigasi utama tanpa ACC.
4. Jangan mengubah PWA tanpa ACC.
5. Jangan membuat patch liar.
```

Setelah edit:

```text
1. Jalankan php -l untuk file PHP yang diubah.
2. Clear cache lokal.
3. Test alur utama.
4. Jika ada JS/CSS, jalankan npm run build.
5. Commit ke GitHub.
6. Upload patch kecil ke cPanel.
7. Clear cache production.
8. Test production.
```

---

## 16. Format Jawaban AI yang Diinginkan

Setiap solusi coding sebaiknya memakai format:

```text
1. Tujuan perubahan
2. File yang dicek
3. File yang diubah
4. Kode/patch
5. Command lokal
6. Cara test lokal
7. File yang harus upload ke cPanel
8. Command cPanel
9. Risiko
10. Rollback jika error
```

---

## 17. Checklist Deploy Patch Kecil

### Jika hanya Blade

```text
Upload file Blade terkait.
Jalankan clear cache di cPanel.
Test halaman terkait.
```

### Jika controller/route

```text
Upload controller/route terkait.
Jalankan clear cache di cPanel.
Cek route jika perlu.
Test fitur terkait.
```

### Jika JS/CSS

```text
npm run build
Upload public/build
Upload file JS/CSS sumber jika ingin repo production lengkap
Clear cache cPanel
Purge Cloudflare
Test browser/PWA
```

### Jika migration

```text
Backup database production.
Upload migration.
Jalankan migrate --force memakai PHP 8.3.
Test fitur.
Siapkan rollback manual.
```

---

## 18. ZIP Patch via PowerShell

Contoh membuat ZIP patch kecil:

```powershell
cd C:\laragon\www\drrsakti

$Project = "C:\laragon\www\drrsakti"
$Stamp = Get-Date -Format "yyyyMMdd-HHmmss"
$PatchName = "drrsakti-patch-$Stamp"
$DeployDir = Join-Path $Project "_deploy"
$PatchRoot = Join-Path $DeployDir $PatchName
$ZipPath = Join-Path $DeployDir "$PatchName.zip"

$Files = @(
    "routes\web.php",
    "app\Http\Controllers\ContohController.php",
    "resources\views\contoh\index.blade.php"
)

if (Test-Path $PatchRoot) {
    Remove-Item $PatchRoot -Recurse -Force
}

New-Item -ItemType Directory -Path $PatchRoot -Force | Out-Null

foreach ($File in $Files) {
    $Source = Join-Path $Project $File
    $Destination = Join-Path $PatchRoot $File
    $DestinationDir = Split-Path $Destination -Parent

    if (!(Test-Path $Source)) {
        throw "File tidak ditemukan: $Source"
    }

    New-Item -ItemType Directory -Path $DestinationDir -Force | Out-Null
    Copy-Item $Source $Destination -Force
}

if (Test-Path $ZipPath) {
    Remove-Item $ZipPath -Force
}

Compress-Archive -Path "$PatchRoot\*" -DestinationPath $ZipPath -Force
Write-Host "ZIP berhasil dibuat: $ZipPath"
```

Sesuaikan daftar `$Files` dengan file yang benar-benar berubah.

---

## 19. Yang Tidak Boleh Dilakukan di Production

```text
Jangan hapus folder production tanpa backup.
Jangan upload database lokal ke production.
Jangan replace .env production.
Jangan hapus public/uploads.
Jangan hapus storage production.
Jangan hapus vendor jika composer tidak tersedia di cPanel.
Jangan menjalankan migrate --force tanpa kebutuhan jelas.
Jangan mengubah fitur stabil tanpa ACC.
```

---

## 20. Ringkasan Operasional

Jika ada bug kecil:

```text
Cari file target.
Patch kecil.
Test lokal.
Upload file terkait saja.
Clear cache.
Test production.
```

Jika ada fitur baru:

```text
Rancang kecil.
Pisah fase.
Jangan sentuh modul stabil.
Test lokal.
Deploy bertahap.
```

Jika production error:

```text
Jangan panik.
Rollback file terakhir.
Clear cache.
Cek log.
Jangan langsung ubah banyak file sekaligus.
```

DRR SAKTI GEN V sudah production. Prioritas utama adalah stabilitas operasional mekanik, koordinator, admin, dan super_admin.
