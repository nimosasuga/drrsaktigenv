# Global Golden Rules

## 1. Local adalah sumber kebenaran

Anggap folder local ini sebagai versi stabil:

```text
C:\laragon\www\drrsakti
```

Jika ada beda antara cPanel dan local, prioritaskan local kecuali user menyatakan sebaliknya.

## 2. Jangan rusak data production

Jangan menjalankan perintah destructive tanpa instruksi eksplisit user:

```bash
git reset --hard
git clean -fd
php artisan migrate:fresh
php artisan db:wipe
rm -rf
```

## 3. Jangan upload file sensitif

Jangan upload:

```text
.env
storage/
vendor/
node_modules/
bootstrap/cache/*.php
public/storage
*.log
```

Upload hanya file kode yang berubah.

## 4. Jalankan validasi sesuai jenis perubahan

Untuk controller/model PHP:

```bash
php -l path/to/file.php
```

Untuk Blade:

```bash
php artisan view:cache
```

Untuk route:

```bash
php artisan route:list --name=nama-route
```

Jika mengubah migration:

```bash
php artisan migrate --pretend
```

Lalu production:

```bash
/opt/cpanel/ea-php83/root/usr/bin/php artisan migrate --force
```

## 5. npm run build hanya jika perlu

Tidak perlu `npm run build` jika hanya mengubah:

- Controller PHP
- Model PHP
- Route PHP
- Blade biasa
- Migration

Perlu `npm run build` hanya jika mengubah:

- `resources/js`
- `resources/css`
- `vite.config.*`
- dependency frontend

Jika build diperlukan, upload hasil build:

```text
public/build/
```

## 6. UI/UX harus responsive

Semua perubahan view harus aman untuk:

- Desktop sidebar terbuka
- Desktop sidebar hide
- Tablet
- Mobile

Hindari tabel yang memaksa user scroll horizontal kecuali data memang sangat lebar. Untuk tabel besar, gunakan:

- card layout di mobile
- `overflow-x-auto` hanya di wrapper tabel
- teks panjang diberi wrap/truncate
- tombol aksi jangan menimpa kolom lain

## 7. Format export Excel-friendly Indonesia

Export CSV untuk Excel Indonesia gunakan delimiter:

```text
;
```

Content-Type:

```text
text/csv; charset=UTF-8
```

Nama file harus jelas dan mengandung tanggal/jam jika memungkinkan.

## 8. Backend harus tetap menjadi sumber validasi

Jangan hanya mengandalkan JavaScript untuk rule bisnis. Semua rule penting wajib ada di backend.

Contoh:

- Preventive Maintenance 1x per bulan per S/N
- Auto RFU untuk job lama
- Required field form
- Validasi battery type/brand
- Filter hak akses departemen

## 9. Jangan menghapus fitur yang sudah ada

Sebelum refactor:

- cari route
- cari controller aktif
- cek view
- cek model
- cek relasi

Di project ini ada beberapa controller lama yang masih ada, tetapi route aktif bisa memakai controller lain.

Contoh penting:

```text
UpdateJobSaveController = aktif untuk store/update update-jobs
JobController = masih aktif untuk index/create/show/edit/destroy
```

## 10. Jawaban ke user harus praktis

User biasanya butuh:

- file apa yang berubah
- perlu upload apa ke cPanel
- command cPanel apa
- perlu npm build atau tidak

Jawab ringkas jika user minta singkat.

