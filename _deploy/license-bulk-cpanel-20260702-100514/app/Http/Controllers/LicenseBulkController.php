<?php

namespace App\Http\Controllers;

use App\Models\LicenseBatch;
use App\Models\LicenseBatchItem;
use App\Models\SubscriptionPackage;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class LicenseBulkController extends Controller
{
    private const ACTIONS = [
        'activate' => 'Aktifkan Lisensi',
        'extend' => 'Perpanjang Lisensi',
        'cancel' => 'Batalkan Lisensi',
        'set_expired' => 'Set Tanggal Expired',
        'change_package' => 'Ubah Paket Lisensi',
    ];

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'role', 'department', 'branch', 'license_status']);
        $usersQuery = $this->filteredUsers($filters)->with(['latestSubscription.package']);

        $users = $usersQuery
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.license-bulk.index', [
            'users' => $users,
            'packages' => $this->packages(),
            'batches' => LicenseBatch::with(['creator', 'package'])->latest()->limit(8)->get(),
            'roles' => User::where('status_user', '!=', 'super_admin')->distinct()->orderBy('status_user')->pluck('status_user'),
            'departments' => User::whereNotNull('department')->distinct()->orderBy('department')->pluck('department'),
            'branches' => User::whereNotNull('branch')->distinct()->orderBy('branch')->pluck('branch'),
            'actions' => self::ACTIONS,
            'filters' => $filters,
        ]);
    }

    public function preview(Request $request)
    {
        $payload = $this->validatedPayload($request);
        $users = $this->selectedUsers($payload['user_ids']);
        $package = $this->selectedPackage($payload);
        $rows = $this->previewRows($users, $payload, $package);

        return view('admin.license-bulk.preview', [
            'payload' => $payload,
            'users' => $users,
            'package' => $package,
            'rows' => $rows,
            'actionLabel' => self::ACTIONS[$payload['action']],
        ]);
    }

    public function show(LicenseBatch $batch)
    {
        $batch->load(['creator', 'package']);

        $items = $batch->items()
            ->with(['user', 'previousPackage', 'newPackage'])
            ->orderBy('id')
            ->paginate(50);

        return view('admin.license-bulk.show', [
            'batch' => $batch,
            'items' => $items,
            'actionLabel' => self::ACTIONS[$batch->action] ?? strtoupper($batch->action),
        ]);
    }

    public function export(LicenseBatch $batch): StreamedResponse
    {
        $batch->load(['creator', 'package']);
        $actionLabel = self::ACTIONS[$batch->action] ?? strtoupper($batch->action);
        $fileName = 'audit-lisensi-bulk-' . $batch->id . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($batch, $actionLabel) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            $this->writeCsvRow($handle, ['Audit Lisensi Bulk']);
            $this->writeCsvRow($handle, ['Batch ID', $batch->id]);
            $this->writeCsvRow($handle, ['Aksi', $actionLabel]);
            $this->writeCsvRow($handle, ['Admin', $batch->creator?->name ?? '-']);
            $this->writeCsvRow($handle, ['Paket', $batch->package?->package_name ?? '-']);
            $this->writeCsvRow($handle, ['Total User', $batch->processed_users . '/' . $batch->total_users]);
            $this->writeCsvRow($handle, ['Waktu Proses', $this->formatCsvDate($batch->created_at)]);
            $this->writeCsvRow($handle, ['Catatan', $batch->note ?? '-']);
            $this->writeCsvRow($handle, []);

            $this->writeCsvRow($handle, [
                'Nama User',
                'NRPP',
                'Role',
                'Department',
                'Aksi',
                'Status Sebelum',
                'Paket Sebelum',
                'Mulai Sebelum',
                'Expired Sebelum',
                'Status Baru',
                'Paket Baru',
                'Mulai Baru',
                'Expired Baru',
                'Catatan',
            ]);

            $batch->items()
                ->with(['user', 'previousPackage', 'newPackage'])
                ->orderBy('id')
                ->chunk(200, function ($items) use ($handle) {
                    foreach ($items as $item) {
                        $this->writeCsvRow($handle, [
                            $item->user?->name ?? 'User terhapus',
                            $item->user?->nrpp ?? '-',
                            $item->user?->status_user ?? '-',
                            $item->user?->department ?? '-',
                            strtoupper(str_replace('_', ' ', $item->action)),
                            strtoupper(str_replace('_', ' ', $item->previous_status ?? '-')),
                            $item->previousPackage?->package_name ?? '-',
                            $this->formatCsvDate($item->previous_started_at),
                            $this->formatCsvDate($item->previous_expired_at),
                            strtoupper(str_replace('_', ' ', $item->new_status ?? '-')),
                            $item->newPackage?->package_name ?? '-',
                            $this->formatCsvDate($item->new_started_at),
                            $this->formatCsvDate($item->new_expired_at),
                            $item->note ?? '-',
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportUsers(Request $request): StreamedResponse
    {
        $filters = $request->only(['q', 'role', 'department', 'branch', 'license_status']);
        $fileName = 'data-user-lisensi-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            $this->writeCsvRow($handle, ['Data User dan Lisensi']);
            $this->writeCsvRow($handle, ['Tanggal Export', now()->format('d/m/Y H:i:s')]);
            $this->writeCsvRow($handle, ['Filter Nama/NRPP', $filters['q'] ?? 'Semua']);
            $this->writeCsvRow($handle, ['Filter Role', $filters['role'] ?? 'Semua']);
            $this->writeCsvRow($handle, ['Filter Department', $filters['department'] ?? 'Semua']);
            $this->writeCsvRow($handle, ['Filter Branch', $filters['branch'] ?? 'Semua']);
            $this->writeCsvRow($handle, ['Filter Status Lisensi', $filters['license_status'] ?? 'Semua']);
            $this->writeCsvRow($handle, []);

            $this->writeCsvRow($handle, [
                'User ID',
                'Nama',
                'NRPP',
                'Role',
                'Branch',
                'Position',
                'Department',
                'Status Verifikasi User',
                'Tanggal Verifikasi User',
                'Subscription ID',
                'Status Lisensi Data',
                'Status Lisensi Efektif',
                'Paket Lisensi',
                'Role Paket',
                'Durasi Paket Bulan',
                'Harga Paket',
                'Tanggal Mulai Lisensi',
                'Tanggal Expired Lisensi',
                'Sisa Hari',
                'Payment ID',
                'Status Pembayaran',
                'Nominal Pembayaran',
                'Tanggal Bayar',
                'Tanggal Verifikasi Pembayaran',
                'User Dibuat',
                'User Diupdate',
            ]);

            $this->filteredUsers($filters)
                ->with(['latestSubscription.package', 'latestSubscription.payment'])
                ->orderBy('name')
                ->chunk(300, function ($users) use ($handle) {
                    foreach ($users as $user) {
                        $subscription = $user->latestSubscription;
                        $package = $subscription?->package;
                        $payment = $subscription?->payment;
                        $effectiveStatus = $this->effectiveLicenseStatus($subscription);
                        $daysRemaining = $subscription?->expired_at
                            ? now()->diffInDays($subscription->expired_at, false)
                            : null;

                        $this->writeCsvRow($handle, [
                            $user->id,
                            $user->name,
                            $user->nrpp,
                            $user->status_user,
                            $user->branch ?? '-',
                            $user->position ?? '-',
                            $user->department ?? '-',
                            $user->is_verified ? 'VERIFIED' : 'BELUM VERIFIED',
                            $this->formatCsvDate($user->verified_at),
                            $subscription?->id ?? '-',
                            $subscription?->status ?? 'belum_ada',
                            $effectiveStatus,
                            $package?->package_name ?? '-',
                            $package?->role_name ?? '-',
                            $package?->duration_months ?? '-',
                            $package?->price ?? '-',
                            $this->formatCsvDate($subscription?->started_at),
                            $this->formatCsvDate($subscription?->expired_at),
                            $daysRemaining ?? '-',
                            $payment?->id ?? '-',
                            $payment?->payment_status ?? '-',
                            $payment?->amount ?? '-',
                            $this->formatCsvDate($payment?->paid_at),
                            $this->formatCsvDate($payment?->verified_at),
                            $this->formatCsvDate($user->created_at),
                            $this->formatCsvDate($user->updated_at),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function confirm(Request $request)
    {
        $payload = $this->validatedPayload($request);
        $users = $this->selectedUsers($payload['user_ids']);
        $package = $this->selectedPackage($payload);

        DB::transaction(function () use ($users, $payload, $package) {
            $batch = LicenseBatch::create([
                'action' => $payload['action'],
                'subscription_package_id' => $package?->id,
                'duration_months' => $this->durationMonths($payload, $package),
                'expired_at' => $payload['expired_at'] ?? null,
                'total_users' => $users->count(),
                'processed_users' => 0,
                'status' => 'completed',
                'note' => $payload['note'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $processed = 0;

            foreach ($users as $user) {
                $subscription = $user->subscriptions()->latest()->first();
                $before = $this->snapshot($subscription);
                $result = $this->applyAction($user, $subscription, $payload, $package);
                $after = $this->snapshot($result);

                LicenseBatchItem::create([
                    'license_batch_id' => $batch->id,
                    'user_id' => $user->id,
                    'user_subscription_id' => $result?->id,
                    'previous_subscription_package_id' => $before['package_id'],
                    'new_subscription_package_id' => $after['package_id'],
                    'action' => $payload['action'],
                    'previous_status' => $before['status'],
                    'new_status' => $after['status'],
                    'previous_started_at' => $before['started_at'],
                    'previous_expired_at' => $before['expired_at'],
                    'new_started_at' => $after['started_at'],
                    'new_expired_at' => $after['expired_at'],
                    'note' => $payload['note'] ?? null,
                ]);

                $processed++;
            }

            $batch->update(['processed_users' => $processed]);
        });

        return redirect()
            ->route('admin.license-bulk.index')
            ->with('success', 'Bulk lisensi berhasil diproses untuk ' . $users->count() . ' user.');
    }

    private function filteredUsers(array $filters)
    {
        return User::query()
            ->where('status_user', '!=', 'super_admin')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('nrpp', 'like', "%{$q}%");
                });
            })
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('status_user', $role))
            ->when($filters['department'] ?? null, fn ($query, $department) => $query->where('department', $department))
            ->when($filters['branch'] ?? null, fn ($query, $branch) => $query->where('branch', $branch))
            ->when($filters['license_status'] ?? null, function ($query, $status) {
                match ($status) {
                    'none' => $query->doesntHave('subscriptions'),
                    'active' => $query->whereHas('latestSubscription', fn ($sub) => $sub->where('status', 'active')->where('expired_at', '>', now())),
                    'expired' => $query->whereHas('latestSubscription', fn ($sub) => $sub->where(function ($inner) {
                        $inner->where('status', 'expired')->orWhere('expired_at', '<=', now());
                    })),
                    'pending' => $query->whereHas('latestSubscription', fn ($sub) => $sub->where('status', 'pending')),
                    'cancelled' => $query->whereHas('latestSubscription', fn ($sub) => $sub->where('status', 'cancelled')),
                    default => null,
                };
            });
    }

    private function validatedPayload(Request $request): array
    {
        $payload = $request->validate([
            'action' => ['required', Rule::in(array_keys(self::ACTIONS))],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'subscription_package_id' => ['nullable', 'integer', 'exists:subscription_packages,id'],
            'duration_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'expired_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (in_array($payload['action'], ['activate', 'extend', 'change_package'], true) && empty($payload['subscription_package_id'])) {
            throw ValidationException::withMessages([
                'subscription_package_id' => 'Paket lisensi wajib dipilih untuk aksi ini.',
            ]);
        }

        if ($payload['action'] === 'set_expired' && empty($payload['expired_at'])) {
            throw ValidationException::withMessages([
                'expired_at' => 'Tanggal expired wajib diisi untuk aksi set tanggal expired.',
            ]);
        }

        return $payload;
    }

    private function selectedUsers(array $userIds)
    {
        return User::with(['latestSubscription.package'])
            ->whereIn('id', $userIds)
            ->where('status_user', '!=', 'super_admin')
            ->orderBy('name')
            ->get();
    }

    private function packages()
    {
        return SubscriptionPackage::where('is_active', true)->orderBy('role_name')->orderBy('package_name')->get();
    }

    private function selectedPackage(array $payload): ?SubscriptionPackage
    {
        if (empty($payload['subscription_package_id'])) {
            return null;
        }

        return SubscriptionPackage::find($payload['subscription_package_id']);
    }

    private function previewRows($users, array $payload, ?SubscriptionPackage $package): array
    {
        return $users->map(function (User $user) use ($payload, $package) {
            $subscription = $user->latestSubscription;
            $before = $this->snapshot($subscription);
            $after = $this->simulateAfter($user, $subscription, $payload, $package);

            return [
                'user' => $user,
                'before' => $before,
                'after' => $after,
            ];
        })->all();
    }

    private function simulateAfter(User $user, ?UserSubscription $subscription, array $payload, ?SubscriptionPackage $package): array
    {
        if ($payload['action'] === 'cancel' && !$subscription) {
            return $this->snapshot(null);
        }

        $durationMonths = $this->durationMonths($payload, $package);
        $startedAt = $subscription?->started_at ?? now();
        $expiredAt = $subscription?->expired_at;
        $status = $subscription?->status;
        $packageId = $subscription?->subscription_package_id;

        if ($payload['action'] === 'cancel') {
            $status = 'cancelled';
        } elseif ($payload['action'] === 'set_expired') {
            $status = 'active';
            $expiredAt = Carbon::parse($payload['expired_at'])->endOfDay();
            $packageId = $package?->id ?? $packageId ?? $this->fallbackPackageFor($user)?->id;
        } elseif ($payload['action'] === 'change_package') {
            $status = 'active';
            $packageId = $package?->id;
            $expiredAt = $expiredAt ?: now()->addMonths($durationMonths);
        } else {
            $status = 'active';
            $packageId = $package?->id;
            $baseDate = $payload['action'] === 'extend' && $expiredAt && $expiredAt->isFuture()
                ? $expiredAt->copy()
                : now();
            $expiredAt = $baseDate->addMonths($durationMonths);
        }

        return [
            'package_id' => $packageId,
            'package_name' => $package?->package_name ?? $subscription?->package?->package_name ?? $this->fallbackPackageFor($user)?->package_name ?? '-',
            'status' => $status,
            'started_at' => $startedAt,
            'expired_at' => $expiredAt,
        ];
    }

    private function applyAction(User $user, ?UserSubscription $subscription, array $payload, ?SubscriptionPackage $package): ?UserSubscription
    {
        if ($payload['action'] === 'cancel' && !$subscription) {
            $user->update([
                'is_verified' => false,
                'verified_at' => null,
            ]);

            return null;
        }

        $after = $this->simulateAfter($user, $subscription, $payload, $package);
        $targetPackageId = $after['package_id'];

        if (!$targetPackageId) {
            $fallback = $this->fallbackPackageFor($user);
            $targetPackageId = $fallback?->id;
        }

        if (!$targetPackageId) {
            throw new \RuntimeException('Paket lisensi tidak ditemukan untuk user ' . $user->name);
        }

        $subscription = $subscription ?: new UserSubscription(['user_id' => $user->id]);
        $subscription->fill([
            'subscription_package_id' => $targetPackageId,
            'started_at' => $after['started_at'] ?? now(),
            'expired_at' => $after['expired_at'],
            'status' => $after['status'],
        ]);
        $subscription->save();

        $user->update([
            'is_verified' => $after['status'] === 'active',
            'verified_at' => $after['status'] === 'active' ? ($user->verified_at ?? now()) : null,
        ]);

        return $subscription->fresh();
    }

    private function snapshot(?UserSubscription $subscription): array
    {
        return [
            'package_id' => $subscription?->subscription_package_id,
            'package_name' => $subscription?->package?->package_name ?? '-',
            'status' => $subscription?->status ?? 'belum_ada',
            'started_at' => $subscription?->started_at,
            'expired_at' => $subscription?->expired_at,
        ];
    }

    private function durationMonths(array $payload, ?SubscriptionPackage $package): int
    {
        return max(1, (int) ($payload['duration_months'] ?? $package?->duration_months ?? 1));
    }

    private function fallbackPackageFor(User $user): ?SubscriptionPackage
    {
        return SubscriptionPackage::where('role_name', $user->status_user)
            ->where('is_active', true)
            ->first();
    }

    private function effectiveLicenseStatus(?UserSubscription $subscription): string
    {
        if (!$subscription) {
            return 'belum_ada';
        }

        if ($subscription->status === 'active' && $subscription->expired_at && $subscription->expired_at->isPast()) {
            return 'expired';
        }

        return $subscription->status;
    }

    private function writeCsvRow($handle, array $row): void
    {
        fputcsv($handle, $row, ';');
    }

    private function formatCsvDate($date): string
    {
        return $date ? $date->format('d/m/Y H:i:s') : '-';
    }
}
