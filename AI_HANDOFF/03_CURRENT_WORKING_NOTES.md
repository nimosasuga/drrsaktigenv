# Current Working Notes

## Update Jobs - PM Check

Rule:

- Saat create/update job, Preventive Maintenance hanya boleh 1x per bulan untuk serial number yang sama.
- Jika PM sudah ada di bulan yang sama, pilihan PM harus disabled dan muncul icon gembok.
- User tetap boleh pilih job type lain.
- `work_date` default hari ini dari Blade.
- Check PM harus jalan setelah S/N dipilih tanpa user harus mengganti tanggal.

Controller terkait:

```text
app/Http/Controllers/UpdateJobPreventiveMaintenanceCheckController.php
app/Http/Controllers/UpdateJobSaveController.php
```

View terkait:

```text
resources/views/update-jobs/create.blade.php
resources/views/update-jobs/edit.blade.php
```

## Update Jobs - Battery Info

Kolom baru:

```text
battery_type
battery_brand
```

Wajib diisi pada add job dan edit job.

Pilihan saat ini di backend:

Battery Type:

```text
LEAD ACID
LITHIUM
```

Battery Brand:

```text
EIKTO
ENEROC
JUNGHEINRICH
YUASA
GS
MIDAC
```

File terkait:

```text
database/migrations/2026_06_27_000000_add_battery_info_to_update_jobs_table.php
database/migrations/2026_06_27_000001_add_battery_info_to_unit_assets_table.php
app/Models/Job.php
app/Models/UnitAsset.php
app/Http/Controllers/UpdateJobSaveController.php
app/Http/Controllers/UpdateJobShareController.php
resources/views/update-jobs/create.blade.php
resources/views/update-jobs/edit.blade.php
```

## Update Jobs - Auto RFU

Rule:

Jika job baru/edit untuk S/N yang sama disimpan dengan status `RFU`, maka job sebelumnya yang masih:

```text
Breakdown
Monitoring
B/D
BD
Standby
```

akan otomatis berubah menjadi:

```text
RFU
```

Jika tanggal RFU kosong, backend otomatis memakai `work_date` dari job RFU terbaru.

Jika tanggal problem pada job RFU kosong, backend mengambil dari job lama yang masih open problem.

Lead time RFU dihitung:

```text
rfu_date - problem_date
```

Contoh:

```text
01 Juli 2026 ke 06 Juli 2026 = 5 hari
```

File terkait:

```text
app/Http/Controllers/UpdateJobSaveController.php
app/Http/Controllers/UpdateJobAssetSearchController.php
resources/views/update-jobs/create.blade.php
resources/views/update-jobs/edit.blade.php
resources/views/update-jobs/show.blade.php
app/Http/Controllers/UpdateJobShareController.php
app/Http/Controllers/CommandCenterCsvController.php
```

## Dashboard PM Status

Halaman:

```text
/dashboard/pm-status/all
/dashboard/pm-status/done
/dashboard/pm-status/pending
```

Sudah ada tombol download:

```text
/dashboard/pm-status/{status}/export
```

Format export:

- CSV Excel-friendly
- delimiter `;`
- Content-Type `text/csv; charset=UTF-8`
- mengikuti filter search aktif

File terkait:

```text
app/Http/Controllers/DashboardPmStatusController.php
resources/views/dashboard-pm-status/index.blade.php
routes/web.php
```

## Asset Management

Status asset baru:

```text
ACTIVE
INACTIVE
```

Legacy active:

```text
RENTAL
BACKUP
READY
STANDBY
```

Legacy inactive:

```text
DITARIK
BREAKDOWN
```

Helper aktif/inaktif ada di:

```text
app/Models/UnitAsset.php
```

## Command Center

Command Center sudah punya export CSV Excel-friendly untuk beberapa modul.

Controller:

```text
app/Http/Controllers/CommandCenterCsvController.php
```

Untuk update jobs, export sudah membawa kolom battery type, battery brand, dan lead time RFU.

## Bulk License

Fitur bulk lisensi sudah ada:

```text
app/Http/Controllers/LicenseBulkController.php
app/Models/LicenseBatch.php
app/Models/LicenseBatchItem.php
resources/views/admin/license-bulk/
```

Routes:

```text
admin/lisensi-bulk
admin/lisensi-bulk/users/export
admin/lisensi-bulk/audit/{batch}
admin/lisensi-bulk/audit/{batch}/export
```

Menu sidebar label:

```text
Bulk License
```

## Calendar Planning

Calendar planning sudah dipisahkan berdasarkan department dan punya collapse:

```text
Week > Day > Detail planning
```

File terkait:

```text
app/Http/Controllers/CalendarController.php
resources/views/calendar/planning.blade.php
```

