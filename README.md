# DRR SAKTI GEN V

DRR SAKTI GEN V adalah aplikasi Laravel untuk manajemen pekerjaan field service, aset unit, battery, charger, delivery, dan penarikan unit. Project ini sudah berada di tahap produksi awal, sehingga setiap perubahan harus kecil, terukur, dan tidak merusak fitur stabil.

Repository: `https://github.com/nimosasuga/drrsaktigenv.git`

---

## 1. Status Project

Project ini adalah project lanjutan, bukan project baru.

Stack utama:

- Laravel 13
- PHP 8.3
- MySQL
- Blade
- Tailwind CSS
- Vite
- Laragon local development
- HeidiSQL untuk inspeksi database
- PowerShell untuk command lokal

Local path utama:

```text
C:\laragon\www\drrsakti
```

Command standar setelah perubahan kode:

```powershell
cd C:\laragon\www\drrsakti
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

Wajib dipatuhi:

1. Jangan mulai ulang project.
2. Jangan ubah arsitektur besar tanpa alasan kuat.
3. Jangan menghapus fitur, tombol, route, field, atau tampilan yang sudah stabil tanpa instruksi eksplisit.
4. Jangan ubah database jika bisa diselesaikan di controller/view.
5. Gunakan perubahan kecil, bertahap, dan mudah dites.
6. Selalu cek file yang sudah ada sebelum memberi solusi.
7. Untuk perubahan UI, ikuti gaya modul yang sudah stabil.
8. Untuk relasi histori pekerjaan asset, gunakan `serial_number` terlebih dahulu. `unit_asset_id` dapat dibuat nanti setelah modul stabil.
9. Gunakan kolom modern:

```text
customer
location
serial_number
unit_type
status
```

Jangan gunakan kolom lama:

```text
nama_pelanggan
lokasi
```

Jika menemukan kode lama memakai `nama_pelanggan` atau `lokasi`, lakukan fallback seperlunya, tetapi standar baru tetap `customer` dan `location`.

---

## 3. Aturan Canvas / File Saat Dibantu AI

Jika project dilanjutkan menggunakan ChatGPT, Gemini, Claude, atau AI coding assistant lain, pakai aturan ini:

1. Satu canvas hanya untuk satu file kode.
2. Nama canvas harus sama persis dengan path file.
3. Baris paling atas isi canvas wajib memuat path file.
4. Jika path file sudah pernah ada, update canvas lama. Jangan buat canvas baru untuk file yang sama.
5. Jangan campur controller, model, route, blade, migration, dan JavaScript dalam satu canvas.
6. Jika perlu ubah tiga file, buat atau update tiga canvas terpisah.
7. Selalu sertakan potongan target agar posisi tempel/perubahan jelas.

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

## 4. Role dan Akses

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
- user biasa / mekanik: akses terbatas sesuai modul.
- `PLANNER`: tidak boleh create Delivery Unit dan tidak boleh create Penarikan Unit.

---

## 5. Modul Stabil Saat Ini

### 5.1 Authentication

Fitur:

- Login
- Logout
- Dashboard
- Auth middleware

Login menggunakan identitas internal seperti NRPP/password sesuai kebutuhan project.

### 5.2 Subscription

Fitur:

- Halaman pilih lisensi
- Payment
- Waiting approval
- Middleware `CheckSubscription`
- Konfirmasi pembayaran diarahkan ke WhatsApp admin:

```text
085133331467
```

Halaman waiting memiliki tombol manual Chat Admin WhatsApp.

### 5.3 Super Admin

Fitur:

- Verifikasi lisensi
- Manajemen pengguna
- Middleware `CheckSuperAdmin`
- Halaman verifikasi lisensi sudah dibuat lebih jelas agar admin melihat data pembayaran sebelum approve.

### 5.4 Manajemen Aset / Unit Asset

Fitur:

- Index grouping berdasarkan customer/location.
- Searchbox dan filter.
- User biasa read-only.
- CRUD hanya untuk role privileged.
- Histori pekerjaan mekanik disambungkan awal menggunakan `serial_number`.

Relasi histori saat ini:

```text
unit_assets.serial_number = jobs.serial_number
```

Status asset penting:

```text
DITARIK
```

Jika sebuah unit sudah masuk Penarikan Unit, status asset akan berubah menjadi `DITARIK`.

### 5.5 Update Job

Fitur:

- Index
- Create
- Edit
- Show
- Search asset by serial number
- UI grouped bertingkat:

```text
Bulan & Tahun
└── PIC
    └── Customer / Lokasi
        └── Detail Unit
```

- Tampilan mobile dibuat compact seperti aplikasi Android.
- Floating add button.
- Info card disederhanakan: total unit BD dan troubleshooting bulan berjalan vs bulan sebelumnya.
- Form Update Job memiliki histori rekomendasi part berdasarkan S/N.
- Saat user input S/N, histori rekomendasi part muncul otomatis:

```text
Tanggal
Part Number
Part Name
Qty
```

- Jika asset dengan serial number tertentu sudah berstatus `DITARIK`, serial number tersebut tidak boleh dipakai di Update Job.
- Pengamanan dilakukan dua lapis:
  - UI: tombol simpan dikunci dan warning muncul.
  - Controller: submit tetap ditolak walaupun user mengetik manual.

### 5.6 Management Battery

Fitur:

- Index
- Create
- Edit
- Show
- Autocomplete asset
- UI mengikuti pola Update Job tetapi dengan nuansa berbeda.
- Grouping bertingkat:

```text
Bulan & Tahun
└── PIC
    └── Customer / Lokasi
        └── Detail Battery
```

- Card `Battery SN / Unique battery` dihapus.
- Card `RFU / Ready battery unit` dihapus.
- Ditambahkan card `Pekerjaan Populer Top 1-3` dengan rounded table dan grafik batang responsive.

Nuansa visual:

```text
Electric Battery: emerald / lime / cyan
```

### 5.7 Management Charger

Fitur:

- Index
- Create
- Edit
- Show
- Update
- Destroy
- Install parts
- Recommendation parts
- Autocomplete asset
- UI mengikuti pola Update Job tetapi dengan nuansa berbeda.
- Grouping bertingkat:

```text
Bulan & Tahun
└── PIC
    └── Customer / Lokasi
        └── Detail Charger
```

- Summary:
  - Charger Job
  - Charger BD
  - Pekerjaan Populer Top 1-3

Nuansa visual:

```text
Voltage Charger: amber / violet / indigo
```

### 5.8 Delivery Unit

Fitur:

- Migration deliveries
- Model Delivery
- DeliveryController
- Route deliveries
- Index/create/show/edit
- Store/update/destroy
- Search asset serial number
- `job_type` fixed: `DELIVERY UNIT`
- `status_unit`: `RFU` / `BREAKDOWN`
- PLANNER tidak boleh create Delivery Unit
- Edit/delete hanya PIC atau privileged role
- UI mengikuti pola Update Job tetapi dengan nuansa berbeda.
- Grouping bertingkat:

```text
Bulan & Tahun
└── PIC
    └── Customer / Lokasi
        └── Detail Delivery
```

Nuansa visual:

```text
Logistic Route: purple / sky / cyan
```

### 5.9 Penarikan Unit

Modul baru sudah dibuat.

File utama:

```text
database/migrations/2026_05_30_000000_create_penarikans_table.php
database/migrations/2026_05_30_010000_add_extra_battery_and_trolly_to_penarikans_table.php
database/migrations/2026_05_30_020000_add_penarikan_asset_status_triggers.php
app/Http/Controllers/PenarikanController.php
resources/views/penarikans/index.blade.php
resources/views/penarikans/create.blade.php
resources/views/penarikans/show.blade.php
resources/views/penarikans/edit.blade.php
resources/js/penarikan-menu-link.js
resources/js/penarikan-form-enhancer.js
```

Catatan:

- Model `app/Models/Penarikan.php` sempat gagal dibuat karena push file diblokir, sehingga controller sementara memakai `DB::table('penarikans')`.
- Ini stabil untuk tahap sekarang, tetapi nanti bisa dirapikan ke Eloquent model jika diperlukan.

Fitur:

- PLANNER tidak boleh create Penarikan Unit.
- Edit/delete hanya PIC pembuat data atau role privileged.
- `job_type` fixed: `TARIK UNIT`.
- `status_unit`: `RFU` / `BREAKDOWN`.
- Data teknisi otomatis dari user login.
- Partner dari user satu branch.
- Search asset berdasarkan serial number.
- Customer/location/unit_type/year/hour_meter diisi otomatis dan readonly.
- Autocomplete S/N berjalan mulai 1 karakter.
- Mendukung kebutuhan lapangan:

```text
Battery Type 1
Battery SN 1
Battery Type 2
Battery SN 2
Trolly 1
Trolly 2
Trolly 3
```

- Draft form otomatis disimpan di browser localStorage agar progres tidak hilang jika user keluar tanpa sengaja.
- Saat user kembali ke form, data dipulihkan dan muncul notifikasi.
- Warning keluar halaman tidak muncul ketika user benar-benar klik Simpan.
- Saat Penarikan Unit berhasil disimpan atau diupdate, status `unit_assets.status` berdasarkan `serial_number` otomatis berubah menjadi:

```text
DITARIK
```

Implementasi status asset saat ini memakai MySQL trigger:

```text
trg_penarikans_after_insert_status
trg_penarikans_after_update_status
```

Jika hosting/cPanel menolak trigger, pindahkan logika update status ke `PenarikanController` sebagai backup.

Nuansa visual:

```text
Return Route: rose / red / slate
```

---

## 6. Bottom Navigation

Urutan floating bottom navigation saat ini:

```text
Home | Kalender | Job | Ingat | Profile
```

Menu Job membuka popup:

```text
Update Job
Management Battery
Management Charger
Delivery Unit
Penarikan Unit
```

Penarikan Unit saat ini diarahkan melalui JS patch `penarikan-menu-link.js` dari `href="#"` ke `/penarikans`.

Nanti jika layout sudah aman, patch langsung di `resources/views/layouts/app.blade.php`:

```blade
<a href="{{ route('penarikans.index') }}">
```

---

## 7. Route Penting

Update Job:

```php
Route::get('/update-jobs/search-assets', [JobController::class, 'searchAssets'])->name('update-jobs.search-assets');
Route::get('/update-jobs/recommendation-history', [JobController::class, 'recommendationHistory'])->name('update-jobs.recommendation-history');
Route::resource('update-jobs', JobController::class);
```

Battery:

```php
Route::get('/batteries/search-assets', [BatteryController::class, 'searchAssets'])->name('batteries.search-assets');
Route::resource('batteries', BatteryController::class);
```

Charger:

```php
Route::get('/chargers/search-assets', [ChargerController::class, 'searchAssets'])->name('chargers.search-assets');
Route::resource('chargers', ChargerController::class);
```

Delivery:

```php
Route::get('/deliveries/search-assets', [DeliveryController::class, 'searchAssets'])->name('deliveries.search-assets');
Route::resource('deliveries', DeliveryController::class);
```

Penarikan:

```php
Route::get('/penarikans/search-assets', [PenarikanController::class, 'searchAssets'])->name('penarikans.search-assets');
Route::resource('penarikans', PenarikanController::class);
```

Assets:

```php
Route::resource('assets', UnitAssetController::class);
```

---

## 8. Timeline Pekerjaan Terakhir

Ringkasan perubahan penting yang sudah dilakukan:

1. Instruksi pembayaran diarahkan ke WhatsApp admin.
2. Halaman waiting diberi tombol Chat Admin WhatsApp manual.
3. Super Admin verifikasi lisensi diperjelas agar data pembayaran terlihat sebelum approve.
4. Update Job index dibuat grouped bertingkat dan mobile friendly.
5. Header Update Job tidak sticky.
6. Battery index dibuat mengikuti pola Update Job, lalu card yang tidak perlu dihapus.
7. Battery ditambah Pekerjaan Populer Top 1-3 dengan grafik batang.
8. Charger index dibuat mengikuti pola Update Job dengan nuansa Voltage Charger.
9. Delivery Unit index dibuat mengikuti pola Update Job dengan nuansa Logistic Route.
10. Form Update Job ditambah histori rekomendasi part berdasarkan serial number.
11. Floating bottom navigation diubah menjadi Home, Kalender, Job, Ingat, Profile.
12. Modul Penarikan Unit dibuat dari alur Dart.
13. Penarikan Unit diberi autocomplete serial number dan field asset readonly.
14. Penarikan Unit mendukung dua battery dan tiga trolly.
15. Penarikan Unit diberi autosave draft dan warning keluar halaman.
16. Bug warning saat klik Simpan diperbaiki.
17. Bug format `in_time/out_time` saat edit Penarikan diperbaiki.
18. Status asset otomatis menjadi `DITARIK` saat Penarikan Unit disimpan.
19. Update Job diblokir untuk asset dengan status `DITARIK`.

---

## 9. Plan Selanjutnya

Prioritas aman berikutnya:

1. Rapikan `PenarikanController` dari `DB::table` ke model Eloquent `Penarikan` jika pembuatan model sudah aman.
2. Tambahkan backup update status `DITARIK` di controller selain trigger, supaya aman untuk cPanel/shared hosting yang menolak trigger.
3. Tampilkan Battery 2 dan Trolly 2-3 di halaman detail Penarikan Unit.
4. Tambahkan tombol delete Penarikan Unit di halaman detail dengan permission yang sudah ada.
5. Patch langsung menu Job di `resources/views/layouts/app.blade.php` agar Penarikan Unit tidak bergantung pada JS link patch.
6. Audit seluruh form create/edit agar field waktu selalu format `H:i`.
7. Audit seluruh fitur terhadap status asset `DITARIK` supaya tidak bisa dipakai di proses operasional yang tidak sesuai.
8. Stabilkan export/import data jika diperlukan setelah modul inti terkunci.
9. Buat dokumentasi deployment cPanel setelah lokal benar-benar stabil.
10. Jangan menambah modul besar baru sebelum modul Penarikan, Update Job, Battery, Charger, Delivery, dan Asset benar-benar dites end-to-end.

---

## 10. Checklist Testing Produksi Lokal

Setelah pull perubahan terbaru:

```powershell
cd C:\laragon\www\drrsakti
git pull origin main
php artisan migrate
php artisan optimize:clear
php artisan view:clear
php artisan route:clear
composer dump-autoload
npm run dev
```

Cek route:

```powershell
php artisan route:list | findstr update-jobs
php artisan route:list | findstr batteries
php artisan route:list | findstr chargers
php artisan route:list | findstr deliveries
php artisan route:list | findstr penarikans
```

Cek trigger Penarikan:

```sql
SHOW TRIGGERS LIKE 'penarikans';
```

Cek asset ditarik:

```sql
SELECT serial_number, status, updated_at
FROM unit_assets
WHERE status = 'DITARIK'
ORDER BY updated_at DESC;
```

Cek asset tidak bisa dipakai di Update Job:

```text
/update-jobs/create
```

Input S/N dengan status `DITARIK`. Hasil benar:

- Warning merah muncul.
- Tombol Simpan terkunci.
- Jika submit dipaksa, controller menolak.

---

## 11. Prinsip Utama

Project ini bukan demo. Project ini sudah masuk fase produksi awal.

Yang harus dijaga:

```text
Stabilitas > gaya baru
Perubahan kecil > bongkar besar
Validasi server > hanya UI
Mobile first > desktop belakangan
Serial number sekarang > unit_asset_id nanti
```

Setiap fitur baru harus tetap mengikuti struktur project yang sudah berjalan.
