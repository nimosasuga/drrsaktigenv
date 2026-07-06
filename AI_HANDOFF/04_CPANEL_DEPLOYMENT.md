# cPanel Deployment Guide

## PHP cPanel

Project membutuhkan PHP 8.3.

Selalu gunakan:

```bash
/opt/cpanel/ea-php83/root/usr/bin/php
```

Contoh:

```bash
cd /home/exprosal/drrsaktigenv
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:cache
```

## Upload File

Upload hanya file yang berubah.

Jangan upload:

```text
.env
vendor/
node_modules/
storage/
bootstrap/cache/*.php
public/storage
*.log
```

## Command Umum Setelah Upload

Jika mengubah Blade:

```bash
cd /home/exprosal/drrsaktigenv
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:cache
```

Jika mengubah route:

```bash
cd /home/exprosal/drrsaktigenv
/opt/cpanel/ea-php83/root/usr/bin/php artisan route:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:cache
```

Jika mengubah config:

```bash
cd /home/exprosal/drrsaktigenv
/opt/cpanel/ea-php83/root/usr/bin/php artisan config:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:cache
```

Jika ada migration baru:

```bash
cd /home/exprosal/drrsaktigenv
/opt/cpanel/ea-php83/root/usr/bin/php artisan migrate --force
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:cache
```

## npm run build

Tidak perlu:

```bash
npm run build
```

jika hanya mengubah:

- PHP controller
- PHP model
- route
- Blade
- migration

Perlu `npm run build` hanya jika mengubah:

- `resources/js`
- `resources/css`
- `vite.config.*`
- package frontend

Jika build dilakukan, upload:

```text
public/build/
```

## Checklist Setelah Upload

1. Buka halaman yang berubah.
2. Pastikan tidak ada error 500.
3. Jika error 500, cek `storage/logs/laravel.log`.
4. Jika route tidak muncul, jalankan `route:clear`.
5. Jika tampilan lama masih muncul, jalankan `view:clear` dan `view:cache`.
6. Jika class/controller tidak terbaca, cek file path dan namespace.

