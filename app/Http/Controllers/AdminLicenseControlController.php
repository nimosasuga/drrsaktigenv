<?php
// PATH FILE: app/Http/Controllers/AdminLicenseControlController.php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SubscriptionPackage;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

        $packages = SubscriptionPackage::orderBy('role_name')
            ->orderBy('price')
            ->get();

        $usersQuery = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('nrpp', 'like', '%' . $search . '%')
                        ->orWhere('branch', 'like', '%' . $search . '%')
                        ->orWhere('position', 'like', '%' . $search . '%')
                        ->orWhere('department', 'like', '%' . $search . '%')
                        ->orWhere('status_user', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                if ($status === 'none') {
                    $query->whereNotExists(function ($licenseQuery) {
                        $licenseQuery->selectRaw('1')
                            ->from('user_subscriptions')
                            ->whereColumn('user_subscriptions.user_id', 'users.id');
                    });

                    return;
                }

                if (!in_array($status, self::STATUSES, true)) {
                    return;
                }

                $query->whereExists(function ($licenseQuery) use ($status) {
                    $licenseQuery->selectRaw('1')
                        ->from('user_subscriptions')
                        ->whereColumn('user_subscriptions.user_id', 'users.id')
                        ->when($status === 'active', function ($activeQuery) {
                            $activeQuery->where('user_subscriptions.status', 'active')
                                ->where(function ($dateQuery) {
                                    $dateQuery->whereNull('user_subscriptions.expired_at')
                                        ->orWhere('user_subscriptions.expired_at', '>', now());
                                });
                        })
                        ->when($status === 'expired', function ($expiredQuery) {
                            $expiredQuery->where(function ($nested) {
                                $nested->where('user_subscriptions.status', 'expired')
                                    ->orWhere(function ($autoExpiredQuery) {
                                        $autoExpiredQuery->where('user_subscriptions.status', 'active')
                                            ->whereNotNull('user_subscriptions.expired_at')
                                            ->where('user_subscriptions.expired_at', '<=', now());
                                    });
                            });
                        })
                        ->when(!in_array($status, ['active', 'expired'], true), function ($statusQuery) use ($status) {
                            $statusQuery->where('user_subscriptions.status', $status);
                        });
                });
            })
            ->when($packageId !== '', function ($query) use ($packageId) {
                $query->whereExists(function ($licenseQuery) use ($packageId) {
                    $licenseQuery->selectRaw('1')
                        ->from('user_subscriptions')
                        ->whereColumn('user_subscriptions.user_id', 'users.id')
                        ->where('user_subscriptions.subscription_package_id', $packageId);
                });
            })
            ->orderBy('name');

        $users = $usersQuery->paginate(15)->withQueryString();
        $allUsers = User::orderBy('name')->get(['id', 'name', 'nrpp', 'status_user', 'branch', 'position', 'department']);

        $userIds = $users->getCollection()->pluck('id')->all();
        $licenseByUser = UserSubscription::with(['user', 'package', 'payment'])
            ->whereIn('user_id', $userIds)
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('user_id')
            ->map(function ($licenses) {
                return $licenses->first();
            });

        $summary = [
            'users_total' => User::count(),
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
            'none' => User::whereNotExists(function ($licenseQuery) {
                $licenseQuery->selectRaw('1')
                    ->from('user_subscriptions')
                    ->whereColumn('user_subscriptions.user_id', 'users.id');
            })->count(),
        ];

        return view('admin.licenses.index', compact('users', 'allUsers', 'licenseByUser', 'packages', 'summary'));
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
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'bulk_action' => ['required', Rule::in(['activate_paid', 'activate', 'expire', 'cancel', 'delete'])],
        ]);

        $userIds = array_values(array_unique(array_map('intval', $validated['user_ids'])));
        $action = $validated['bulk_action'];
        $processed = 0;
        $skipped = 0;

        DB::transaction(function () use ($userIds, $action, &$processed, &$skipped) {
            $users = User::whereIn('id', $userIds)->lockForUpdate()->get();

            foreach ($users as $user) {
                $package = $this->findPackageForUser($user);
                $license = UserSubscription::where('user_id', $user->id)->latest('updated_at')->lockForUpdate()->first();

                if ($action === 'delete') {
                    if ($license) {
                        $license->delete();
                        $processed++;
                        continue;
                    }

                    $skipped++;
                    continue;
                }

                if (in_array($action, ['activate_paid', 'activate'], true)) {
                    if (!$package) {
                        $skipped++;
                        continue;
                    }

                    $startedAt = now()->startOfDay();
                    $expiredAt = $startedAt->copy()->addMonths(max(1, (int) $package->duration_months))->endOfDay();

                    if (!$license) {
                        $license = UserSubscription::create([
                            'user_id' => $user->id,
                            'subscription_package_id' => $package->id,
                            'status' => 'active',
                            'started_at' => $startedAt,
                            'expired_at' => $expiredAt,
                        ]);
                    } else {
                        $license->update([
                            'subscription_package_id' => $package->id,
                            'status' => 'active',
                            'started_at' => $startedAt,
                            'expired_at' => $expiredAt,
                        ]);
                    }

                    if ($action === 'activate_paid') {
                        Payment::create([
                            'user_id' => $user->id,
                            'subscription_package_id' => $package->id,
                            'user_subscription_id' => $license->id,
                            'amount' => (int) $package->price,
                            'payment_status' => 'paid',
                            'paid_at' => now(),
                            'verified_by' => Auth::id(),
                            'verified_at' => now(),
                            'note' => 'Bulk paid activation from admin license control.',
                        ]);
                    }

                    $processed++;
                    continue;
                }

                if (!$license) {
                    $skipped++;
                    continue;
                }

                if ($action === 'expire') {
                    $license->update([
                        'status' => 'expired',
                        'expired_at' => now(),
                    ]);
                    $processed++;
                    continue;
                }

                if ($action === 'cancel') {
                    $license->update([
                        'status' => 'cancelled',
                    ]);
                    $processed++;
                }
            }
        });

        return redirect()
            ->route('admin.licenses.index')
            ->with('success', 'Bulk action selesai. Diproses: ' . $processed . '. Dilewati: ' . $skipped . '.');
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
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'subscription_package_id' => ['required', 'integer', 'exists:subscription_packages,id'],
            'status' => ['required', Rule::in(self::STATUSES)],
            'started_at' => ['nullable', 'date'],
            'expired_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $package = SubscriptionPackage::findOrFail($validated['subscription_package_id']);

        if (!$this->isPackageAllowedForUser($package, $user)) {
            throw ValidationException::withMessages([
                'subscription_package_id' => 'Paket lisensi tidak sesuai dengan role/status user ' . strtoupper((string) $user->status_user) . '.',
            ]);
        }

        return $validated;
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

    private function findPackageForUser(User $user): ?SubscriptionPackage
    {
        $userRole = $this->normalizeLicenseRole($user->status_user);

        return SubscriptionPackage::get()
            ->first(function (SubscriptionPackage $package) use ($userRole) {
                return $this->normalizeLicenseRole($package->role_name) === $userRole;
            });
    }

    private function isPackageAllowedForUser(SubscriptionPackage $package, User $user): bool
    {
        return $this->normalizeLicenseRole($package->role_name) === $this->normalizeLicenseRole($user->status_user);
    }

    private function normalizeLicenseRole(?string $role): string
    {
        $role = strtolower(trim((string) $role));
        $role = str_replace(['-', '_'], ' ', $role);
        $role = preg_replace('/\s+/', ' ', $role) ?: '';

        return match ($role) {
            'sect head', 'secthead' => 'sect_head',
            'super admin', 'superadmin' => 'super_admin',
            default => str_replace(' ', '_', $role),
        };
    }
}
