<?php
// PATH FILE: app/Http/Controllers/AdminLicenseControlController.php

namespace App\Http\Controllers;

use App\Models\SubscriptionPackage;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminLicenseControlController extends Controller
{
    private const STATUSES = [
        'pending',
        'active',
        'expired',
        'cancelled',
    ];

    public function index(Request $request)
    {
        $this->authorizeLicenseControl();

        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $packageId = (string) $request->query('package_id', '');

        $licensesQuery = UserSubscription::with(['user', 'package', 'payment'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('nrpp', 'like', '%' . $search . '%')
                            ->orWhere('branch', 'like', '%' . $search . '%')
                            ->orWhere('position', 'like', '%' . $search . '%')
                            ->orWhere('department', 'like', '%' . $search . '%')
                            ->orWhere('status_user', 'like', '%' . $search . '%');
                    })->orWhereHas('package', function ($packageQuery) use ($search) {
                        $packageQuery->where('package_name', 'like', '%' . $search . '%')
                            ->orWhere('role_name', 'like', '%' . $search . '%');
                    });
                });
            })
            ->when(in_array($status, self::STATUSES, true), function ($query) use ($status) {
                if ($status === 'active') {
                    $query->where('status', 'active')
                        ->where(function ($dateQuery) {
                            $dateQuery->whereNull('expired_at')
                                ->orWhere('expired_at', '>', now());
                        });

                    return;
                }

                if ($status === 'expired') {
                    $query->where(function ($expiredQuery) {
                        $expiredQuery->where('status', 'expired')
                            ->orWhere(function ($autoExpiredQuery) {
                                $autoExpiredQuery->where('status', 'active')
                                    ->whereNotNull('expired_at')
                                    ->where('expired_at', '<=', now());
                            });
                    });

                    return;
                }

                $query->where('status', $status);
            })
            ->when($packageId !== '', function ($query) use ($packageId) {
                $query->where('subscription_package_id', $packageId);
            })
            ->latest('updated_at');

        $licenses = $licensesQuery->paginate(15)->withQueryString();

        $packages = SubscriptionPackage::orderBy('role_name')
            ->orderBy('price')
            ->get();

        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'nrpp', 'status_user', 'branch', 'position', 'department']);

        $summary = [
            'total' => UserSubscription::count(),
            'active' => UserSubscription::where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('expired_at')
                        ->orWhere('expired_at', '>', now());
                })
                ->count(),
            'pending' => UserSubscription::where('status', 'pending')->count(),
            'expired' => UserSubscription::where(function ($query) {
                $query->where('status', 'expired')
                    ->orWhere(function ($expiredQuery) {
                        $expiredQuery->where('status', 'active')
                            ->whereNotNull('expired_at')
                            ->where('expired_at', '<=', now());
                    });
            })->count(),
            'cancelled' => UserSubscription::where('status', 'cancelled')->count(),
        ];

        return view('admin.licenses.index', compact('licenses', 'packages', 'users', 'summary'));
    }

    public function store(Request $request)
    {
        $this->authorizeLicenseControl();

        $validated = $this->validateLicensePayload($request);

        UserSubscription::create($this->prepareLicensePayload($validated));

        return redirect()
            ->route('admin.licenses.index')
            ->with('success', 'Lisensi pengguna berhasil dibuat.');
    }

    public function update(Request $request, UserSubscription $license)
    {
        $this->authorizeLicenseControl();

        $validated = $this->validateLicensePayload($request);

        $license->update($this->prepareLicensePayload($validated, $license));

        return redirect()
            ->route('admin.licenses.index', $request->query())
            ->with('success', 'Lisensi pengguna berhasil diperbarui.');
    }

    public function destroy(UserSubscription $license)
    {
        $this->authorizeLicenseControl();

        $license->delete();

        return redirect()
            ->route('admin.licenses.index')
            ->with('success', 'Lisensi pengguna berhasil dihapus.');
    }

    public function bulk(Request $request)
    {
        $this->authorizeLicenseControl();

        $validated = $request->validate([
            'license_ids' => ['required', 'array', 'min:1'],
            'license_ids.*' => ['integer', 'exists:user_subscriptions,id'],
            'bulk_action' => ['required', Rule::in(['activate', 'expire', 'cancel', 'delete'])],
        ]);

        $licenseIds = array_values(array_unique(array_map('intval', $validated['license_ids'])));
        $action = $validated['bulk_action'];

        DB::transaction(function () use ($licenseIds, $action) {
            $licenses = UserSubscription::with('package')
                ->whereIn('id', $licenseIds)
                ->lockForUpdate()
                ->get();

            foreach ($licenses as $license) {
                if ($action === 'delete') {
                    $license->delete();
                    continue;
                }

                if ($action === 'activate') {
                    $durationMonths = max(1, (int) ($license->package->duration_months ?? 1));
                    $startedAt = $license->started_at ?? now();

                    $license->update([
                        'status' => 'active',
                        'started_at' => $startedAt,
                        'expired_at' => $license->expired_at ?? $startedAt->copy()->addMonths($durationMonths),
                    ]);

                    continue;
                }

                if ($action === 'expire') {
                    $license->update([
                        'status' => 'expired',
                        'expired_at' => now(),
                    ]);

                    continue;
                }

                if ($action === 'cancel') {
                    $license->update([
                        'status' => 'cancelled',
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.licenses.index')
            ->with('success', 'Bulk action lisensi berhasil diproses.');
    }

    private function authorizeLicenseControl(): void
    {
        $statusUser = strtolower(trim((string) (Auth::user()->status_user ?? '')));
        $statusUser = str_replace(['-', ' '], '_', $statusUser);

        if (!in_array($statusUser, ['admin', 'super_admin'], true)) {
            abort(403, 'Akses ditolak. Kontrol Lisensi hanya untuk admin dan super_admin.');
        }
    }

    private function validateLicensePayload(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'subscription_package_id' => ['required', 'integer', 'exists:subscription_packages,id'],
            'status' => ['required', Rule::in(self::STATUSES)],
            'started_at' => ['nullable', 'date'],
            'expired_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ]);
    }

    private function prepareLicensePayload(array $validated, ?UserSubscription $license = null): array
    {
        $package = SubscriptionPackage::findOrFail($validated['subscription_package_id']);
        $status = $validated['status'];
        $startedAt = filled($validated['started_at'] ?? null)
            ? Carbon::parse($validated['started_at'])->startOfDay()
            : null;

        $expiredAt = filled($validated['expired_at'] ?? null)
            ? Carbon::parse($validated['expired_at'])->endOfDay()
            : null;

        if ($status === 'active' && blank($startedAt)) {
            $startedAt = $license?->started_at ?? now();
        }

        if ($status === 'active' && blank($expiredAt)) {
            $baseDate = $startedAt ?? $license?->started_at ?? now();
            $expiredAt = $baseDate->copy()
                ->addMonths(max(1, (int) $package->duration_months))
                ->endOfDay();
        }

        return [
            'user_id' => $validated['user_id'],
            'subscription_package_id' => $validated['subscription_package_id'],
            'status' => $status,
            'started_at' => $startedAt,
            'expired_at' => $expiredAt,
        ];
    }
}
