# Project Context

## Identitas Project

Nama aplikasi: DRR SAKTI GEN V

Stack utama:

- Laravel 13
- Blade
- Tailwind CSS
- MySQL/MariaDB
- Laragon local development
- cPanel production

Path local stabil:

```text
C:\laragon\www\drrsakti
```

Domain production:

```text
https://drrsakti.exprosalab.com
```

Path production cPanel yang sering dipakai:

```text
/home/exprosal/drrsaktigenv
```

## Modul Utama

- Dashboard
- Dashboard PM Status
- Manajemen Aset
- Update Jobs
- Command Center
- Smart Analytics
- Sparepart Management
- Recommendation Control
- Calendar Planning
- Bulk License
- Payment Settings
- User Management

## Catatan Teknis Penting

Project production membutuhkan PHP minimal 8.3.

Jika menjalankan artisan di cPanel, gunakan:

```bash
/opt/cpanel/ea-php83/root/usr/bin/php artisan ...
```

Jangan memakai PHP default cPanel jika masih PHP 8.2 karena Composer/Laravel akan error.

## Route Penting

Update Jobs:

```text
GET    /update-jobs
POST   /update-jobs
PUT    /update-jobs/{id}
GET    /update-jobs/search-assets
GET    /update-jobs/check-preventive-maintenance
GET    /update-jobs/{id}/share-message
```

Dashboard PM Status:

```text
GET /dashboard/pm-status/{status}
GET /dashboard/pm-status/{status}/export
```

Status PM:

```text
all
done
pending
```

