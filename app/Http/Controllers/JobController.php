<?php
// app/Http/Controllers/JobController.php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\RentalSparepartUsageReview;
use App\Models\User;
use App\Models\UnitAsset;
use App\Support\DepartmentPartnerOptions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    /**
     * Helper untuk mengecek Hak Akses (Otorisasi)
     */
    private function canEditJob(Job $job)
    {
        $user = Auth::user();
        $role = $user->role ?? $user->status_user;

        // Daftar role yang bisa bebas edit/hapus semua job
        $privilegedRoles = ['koordinator', 'sect_head', 'admin', 'super_admin'];

        // True JIKA dia pembuatnya ATAU dia punya role level atas
        return ($user->id === $job->user_id) || in_array($role, $privilegedRoles);
    }

    private function jobTypeOptions(): array
    {
        return [
            'Preventive Maintenance',
            'Install Part',
            'Troubleshooting',
            'Inspection',
            'Repair',
        ];
    }

    private function statusUnitOptions(): array
    {
        return [
            'RFU',
            'Breakdown',
            'Monitoring',
            'Waiting Part',
        ];
    }

    private function batteryTypeOptions(): array
    {
        return ['LEAD ACID', 'LITHIUM'];
    }

    private function batteryBrandOptions(): array
    {
        return ['EIKTO', 'ENEROC', 'JUNGHEINRICH', 'YUASA', 'GS', 'MIDAC'];
    }

    private function normalizeJobType(?string $value): ?string
    {
        $value = trim((string) $value);

        return match (strtoupper($value)) {
            'PM' => 'Preventive Maintenance',
            'BM' => 'Troubleshooting',
            'PDI' => 'Inspection',
            default => $value !== '' ? $value : null,
        };
    }

    private function normalizeStatusUnit(?string $value): ?string
    {
        $value = trim((string) $value);

        return match (strtoupper($value)) {
            'B/D', 'BD', 'BREAKDOWN' => 'Breakdown',
            'STANDBY' => 'Monitoring',
            default => $value !== '' ? $value : null,
        };
    }

    private function isRfuStatus(string $status): bool
    {
        return strtoupper(trim((string) $status)) === 'RFU';
    }

    private function isBreakdownStatus(string $status): bool
    {
        $normalized = strtoupper(trim((string) $status));

        return in_array($normalized, ['B/D', 'BD', 'BREAKDOWN'], true)
            || str_contains($normalized, 'BREAKDOWN');
    }

    private function isWithdrawnAssetStatus(string $status): bool
    {
        return in_array(strtoupper(trim((string) $status)), UnitAsset::inactiveStatusValues(), true);
    }

    private function assetWithdrawnError(string $serialNumber): string
    {
        return "Serial Number {$serialNumber} tidak bisa digunakan untuk Update Job karena status unit asset tidak aktif.";
    }

    private function rejectIfAssetWithdrawn(string $serialNumber)
    {
        $asset = UnitAsset::where('serial_number', $serialNumber)->first();

        if ($asset && $this->isWithdrawnAssetStatus($asset->status ?? null)) {
            return back()
                ->withInput()
                ->withErrors(['error' => $this->assetWithdrawnError($serialNumber)]);
        }

        return null;
    }

    private function rejectDuplicatePreventiveMaintenance(array $validated, ?int $exceptJobId = null)
    {
        if (($validated['job_type'] ?? null) !== 'Preventive Maintenance') {
            return null;
        }

        $workDate = Carbon::parse($validated['work_date']);
        $serialNumber = $validated['serial_number'];

        $query = Job::where('serial_number', $serialNumber)
            ->where('job_type', 'Preventive Maintenance')
            ->whereYear('work_date', $workDate->year)
            ->whereMonth('work_date', $workDate->month);

        if ($exceptJobId) {
            $query->where('id', '!=', $exceptJobId);
        }

        if (!$query->exists()) {
            return null;
        }

        return back()
            ->withInput()
            ->withErrors([
                'job_type' => 'Preventive Maintenance untuk S/N ' . $serialNumber . ' hanya boleh 1x dalam bulan ' . $workDate->translatedFormat('F Y') . '.',
            ]);
    }

    private function isTroubleshootingJob(Job $job): bool
    {
        $haystack = strtoupper(trim(implode(' ', [
            (string) ($job->job_type ?? ''),
            (string) ($job->problem ?? ''),
            (string) ($job->action ?? ''),
        ])));

        return str_contains($haystack, 'TROUBLE');
    }

    private function countRfu(mixed $jobs): int
    {
        return $jobs->filter(fn($job) => $this->isRfuStatus($job->status_unit))->count();
    }

    private function countBreakdown(mixed $jobs): int
    {
        return $jobs->filter(fn($job) => $this->isBreakdownStatus($job->status_unit))->count();
    }

    private function countTroubleshooting(mixed $jobs): int
    {
        return $jobs->filter(fn($job) => $this->isTroubleshootingJob($job))->count();
    }

    /**
     * Menampilkan daftar job dengan grouping bertingkat:
     * Bulan & Tahun -> PIC -> Customer / Lokasi -> Detail Unit.
     */
    public function index(Request $request)
    {
        $query = Job::with('user');

        // Default aman: tampilkan tahun berjalan agar halaman tidak memuat seluruh histori pekerjaan.
        $selectedYear = (int) $request->input('year_filter', now()->year);
        $hasDateRange = $request->filled('date_from') || $request->filled('date_to');

        if ($request->filled('month_filter')) {
            $parts = explode('-', $request->month_filter);

            if (count($parts) === 2) {
                $query->whereYear('work_date', $parts[0])
                    ->whereMonth('work_date', $parts[1]);
            }
        } elseif (!$hasDateRange) {
            $query->whereYear('work_date', $selectedYear);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('work_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('work_date', '<=', $request->date_to);
        }

        if ($request->filled('customer_filter')) {
            $query->where('customer', $request->customer_filter);
        }

        if ($request->filled('pic_filter')) {
            $query->where('pic', $request->pic_filter);
        }

        if ($request->filled('location_filter')) {
            $query->where('location', $request->location_filter);
        }

        if ($request->filled('status_filter')) {
            $query->where('status_unit', $request->status_filter);
        }

        if ($request->filled('job_type_filter')) {
            $query->where('job_type', $request->job_type_filter);
        }

        if ($request->filled('serial_number_filter')) {
            $serialNumber = trim($request->serial_number_filter);

            $query->where('serial_number', 'like', "%{$serialNumber}%");
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%")
                    ->orWhere('nomor_lambung', 'like', "%{$search}%")
                    ->orWhere('year', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('pic', 'like', "%{$search}%")
                    ->orWhere('job_type', 'like', "%{$search}%")
                    ->orWhere('problem', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $jobs = $query
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->get();

        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonthNoOverflow()->startOfMonth();

        $currentMonthJobs = Job::whereBetween('work_date', [
            $currentMonth->copy()->startOfMonth()->toDateString(),
            $currentMonth->copy()->endOfMonth()->toDateString(),
        ])->get();

        $previousMonthJobs = Job::whereBetween('work_date', [
            $previousMonth->copy()->startOfMonth()->toDateString(),
            $previousMonth->copy()->endOfMonth()->toDateString(),
        ])->get();

        $troubleshootingCurrent = $this->countTroubleshooting($currentMonthJobs);
        $troubleshootingPrevious = $this->countTroubleshooting($previousMonthJobs);
        $troubleshootingDelta = $troubleshootingCurrent - $troubleshootingPrevious;

        $summary = [
            'total_bd_units' => $jobs
                ->filter(fn($job) => $this->isBreakdownStatus($job->status_unit))
                ->pluck('serial_number')
                ->filter()
                ->unique()
                ->count(),
            'troubleshooting_current_month' => $troubleshootingCurrent,
            'troubleshooting_previous_month' => $troubleshootingPrevious,
            'troubleshooting_delta' => $troubleshootingDelta,
            'troubleshooting_trend' => $troubleshootingDelta > 0 ? 'Naik' : ($troubleshootingDelta < 0 ? 'Turun' : 'Stabil'),
            'current_month_label' => $currentMonth->translatedFormat('F Y'),
            'previous_month_label' => $previousMonth->translatedFormat('F Y'),
        ];

        $groupedJobs = $jobs
            ->groupBy(function ($job) {
                return $job->work_date
                    ? Carbon::parse($job->work_date)->translatedFormat('F Y')
                    : 'Tanpa Tanggal';
            })
            ->map(function ($monthJobs, $monthName) {
                return [
                    'name' => $monthName,
                    'total' => $monthJobs->count(),
                    'pic_total' => $monthJobs->pluck('pic')->filter()->unique()->count(),
                    'customer_location_total' => $monthJobs->unique(function ($job) {
                        return ($job->customer ?: 'Tanpa Customer') . '|' . ($job->location ?: 'Tanpa Lokasi');
                    })->count(),
                    'rfu_total' => $this->countRfu($monthJobs),
                    'breakdown_total' => $this->countBreakdown($monthJobs),
                    'pics' => $monthJobs
                        ->groupBy(fn($job) => $job->pic ?: 'Tanpa PIC')
                        ->map(function ($picJobs, $picName) {
                            return [
                                'name' => $picName,
                                'total' => $picJobs->count(),
                                'customer_location_total' => $picJobs->unique(function ($job) {
                                    return ($job->customer ?: 'Tanpa Customer') . '|' . ($job->location ?: 'Tanpa Lokasi');
                                })->count(),
                                'rfu_total' => $this->countRfu($picJobs),
                                'breakdown_total' => $this->countBreakdown($picJobs),
                                'customer_locations' => $picJobs
                                    ->groupBy(function ($job) {
                                        return ($job->customer ?: 'Tanpa Customer') . ' / ' . ($job->location ?: 'Tanpa Lokasi');
                                    })
                                    ->map(function ($locationJobs, $customerLocationName) {
                                        return [
                                            'name' => $customerLocationName,
                                            'total' => $locationJobs->count(),
                                            'unit_total' => $locationJobs->pluck('serial_number')->filter()->unique()->count(),
                                            'rfu_total' => $this->countRfu($locationJobs),
                                            'breakdown_total' => $this->countBreakdown($locationJobs),
                                            'jobs' => $locationJobs->values(),
                                        ];
                                    }),
                            ];
                        }),
                ];
            });

        $customers = Job::whereNotNull('customer')
            ->where('customer', '!=', '')
            ->select('customer')
            ->distinct()
            ->orderBy('customer')
            ->pluck('customer');

        $pics = Job::whereNotNull('pic')
            ->where('pic', '!=', '')
            ->select('pic')
            ->distinct()
            ->orderBy('pic')
            ->pluck('pic');

        $locations = Job::whereNotNull('location')
            ->where('location', '!=', '')
            ->select('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        $statuses = Job::whereNotNull('status_unit')
            ->where('status_unit', '!=', '')
            ->select('status_unit')
            ->distinct()
            ->orderBy('status_unit')
            ->pluck('status_unit');

        $jobTypes = Job::whereNotNull('job_type')
            ->where('job_type', '!=', '')
            ->select('job_type')
            ->distinct()
            ->orderBy('job_type')
            ->pluck('job_type');

        $years = Job::whereNotNull('work_date')
            ->selectRaw('YEAR(work_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->values();

        return view('update-jobs.index', compact(
            'groupedJobs',
            'summary',
            'customers',
            'pics',
            'locations',
            'statuses',
            'jobTypes',
            'years',
            'selectedYear'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        $branch = $user->branch ?? 'HO / Pusat';
        $partners = DepartmentPartnerOptions::forUser($user, $branch);

        $jobTypeOptions = $this->jobTypeOptions();
        $statusUnitOptions = $this->statusUnitOptions();
        $batteryTypeOptions = $this->batteryTypeOptions();
        $batteryBrandOptions = $this->batteryBrandOptions();

        return view('update-jobs.create', compact('user', 'branch', 'partners', 'jobTypeOptions', 'statusUnitOptions', 'batteryTypeOptions', 'batteryBrandOptions'));
    }

    public function searchAssets(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $includeWithdrawn = $request->boolean('include_withdrawn');

        if ($search === '') {
            return response()->json([]);
        }

        $assets = UnitAsset::where(function ($query) use ($search) {
            $query->where('serial_number', 'LIKE', "%{$search}%")
                ->orWhere('unit_type', 'LIKE', "%{$search}%")
                ->orWhere('nomor_lambung', 'LIKE', "%{$search}%")
                ->orWhere('year', 'LIKE', "%{$search}%")
                ->orWhere('customer', 'LIKE', "%{$search}%")
                ->orWhere('location', 'LIKE', "%{$search}%");
        })
            ->when(!$includeWithdrawn, function ($query) {
                $query->whereRaw(UnitAsset::activeStatusSql());
            })
            ->take(10)
            ->get();

        $mapped = $assets->map(function ($asset) {
            $isWithdrawn = $this->isWithdrawnAssetStatus($asset->status ?? null);

            return [
                'serial_number' => $asset->serial_number,
                'unit_type'     => $asset->unit_type ?? $asset->unit_model ?? $asset->tipe_unit ?? '',
                'nomor_lambung' => $asset->nomor_lambung ?? '',
                'year'          => $asset->year ?? '',
                'customer'      => $asset->customer ?? $asset->nama_pelanggan ?? '',
                'location'      => $asset->location ?? $asset->lokasi ?? '',
                'status'        => $asset->status ?? '',
                'is_withdrawn'  => $isWithdrawn,
                'blocked_reason' => $isWithdrawn ? $this->assetWithdrawnError((string) $asset->serial_number) : null,
            ];
        });

        return response()->json($mapped);
    }

    public function recommendationHistory(Request $request)
    {
        $serialNumber = trim((string) $request->get('serial_number', ''));

        if ($serialNumber === '') {
            return response()->json([]);
        }

        $jobs = Job::with('recommendations')
            ->where('serial_number', $serialNumber)
            ->whereHas('recommendations')
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->take(15)
            ->get();

        $history = $jobs
            ->flatMap(function ($job) {
                return $job->recommendations->map(function ($recommendation) use ($job) {
                    return [
                        'date' => $job->work_date ? Carbon::parse($job->work_date)->format('d/m/Y') : '-',
                        'part_number' => $recommendation->part_number ?: '-',
                        'part_name' => $recommendation->part_name ?: '-',
                        'qty' => $recommendation->qty ?: 1,
                    ];
                });
            })
            ->take(30)
            ->values();

        return response()->json($history);
    }

    public function store(Request $request)
    {
        $request->merge([
            'job_type' => $this->normalizeJobType($request->input('job_type')),
            'status_unit' => $this->normalizeStatusUnit($request->input('status_unit')),
        ]);

        $validated = $request->validate([
            'work_date'     => 'required|date',
            'in_time'       => 'required|date_format:H:i',
            'out_time'      => 'required|date_format:H:i',
            'serial_number' => 'required|string|max:100',
            'unit_type'     => 'required|string|max:100',
            'nomor_lambung' => 'nullable|string|max:100',
            'year'          => 'nullable|string|max:20',
            'hour_meter'    => 'required|numeric|min:0',
            'battery_type'   => ['required', 'string', Rule::in($this->batteryTypeOptions())],
            'battery_brand'  => ['required', 'string', Rule::in($this->batteryBrandOptions())],
            'customer'      => 'required|string|max:150',
            'location'      => 'required|string|max:150',
            'problem'       => 'required|string',
            'action'        => 'required|string',
            'job_type'      => ['required', 'string', Rule::in($this->jobTypeOptions())],
            'status_unit'   => ['required', 'string', Rule::in($this->statusUnitOptions())],
            'partner'       => 'nullable|string|max:150',
            'vehicle_type'  => 'nullable|string|max:100',
            'nopol'         => 'nullable|string|max:100',
            'problem_date'  => 'nullable|date',
            'rfu_date'      => 'nullable|date',
        ]);

        $validated['partner'] = DepartmentPartnerOptions::normalizePartner($validated['partner'] ?? null, Auth::user());

        if ($blockedResponse = $this->rejectIfAssetWithdrawn($validated['serial_number'])) {
            return $blockedResponse;
        }

        if ($duplicatePmResponse = $this->rejectDuplicatePreventiveMaintenance($validated)) {
            return $duplicatePmResponse;
        }

        DB::beginTransaction();

        try {
            $job = new Job($validated);
            $job->user_id = Auth::id();
            $job->branch = Auth::user()->branch ?? 'HO / Pusat';
            $job->pic = Auth::user()->name;
            $job->status_mekanik = Auth::user()->role ?? Auth::user()->status_user;
            $job->save();

            if ($request->has('inst_part_name')) {
                foreach ($request->inst_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $job->installParts()->create([
                            'part_number' => $request->inst_part_number[$key] ?? null,
                            'part_name'   => $part_name,
                            'qty'         => $request->inst_qty[$key] ?? 1,
                            'remarks'     => $request->inst_remarks[$key] ?? null,
                            'no_job'      => $request->inst_no_job[$key] ?? null,
                            'no_pr'       => $request->inst_no_pr[$key] ?? null,
                        ]);
                    }
                }
            }

            if ($request->has('rec_part_name')) {
                foreach ($request->rec_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $job->recommendations()->create([
                            'part_number' => $request->rec_part_number[$key] ?? null,
                            'part_name'   => $part_name,
                            'qty'         => $request->rec_qty[$key] ?? 1,
                            'remarks'     => $request->rec_remarks[$key] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('update-jobs.show', $job->id)->with('success', 'Update Job berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    public function show(Request $request, mixed $id)
    {
        $job = Job::with(['user', 'installParts', 'recommendations'])->findOrFail($id);
        $partNumberFilter = trim((string) $request->query('part_number', ''));
        $normalizePartNumber = function ($value): string {
            $value = strtoupper(trim((string) ($value ?? '')));

            return preg_replace('/\s+/', '', $value) ?: '';
        };
        $partNumberFilterKey = $normalizePartNumber($partNumberFilter);

        $historyJobs = Job::with(['installParts', 'recommendations'])
            ->where('serial_number', $job->serial_number)
            ->where(function ($query) {
                $query->whereHas('installParts')
                    ->orWhereHas('recommendations');
            })
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->get();

        $rawRecommendationHistories = $historyJobs
            ->flatMap(function ($historyJob) {
                return $historyJob->recommendations->map(function ($recommendation) use ($historyJob) {
                    return [
                        'job' => $historyJob,
                        'part' => $recommendation,
                    ];
                });
            })
            ->values();

        $rawInstallPartHistories = $historyJobs
            ->flatMap(function ($historyJob) {
                return $historyJob->installParts->map(function ($installPart) use ($historyJob) {
                    return [
                        'job' => $historyJob,
                        'part' => $installPart,
                    ];
                });
            })
            ->values();

        $partNumberOptions = $rawRecommendationHistories
            ->merge($rawInstallPartHistories)
            ->map(fn($history) => trim((string) ($history['part']->part_number ?? '')))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $installedQtyByPartNumber = $rawInstallPartHistories
            ->filter(fn($history) => $normalizePartNumber($history['part']->part_number ?? '') !== '')
            ->groupBy(fn($history) => $normalizePartNumber($history['part']->part_number ?? ''))
            ->map(fn($items) => $items->sum(fn($history) => (int) ($history['part']->qty ?? 0)));

        $recommendationHistories = $rawRecommendationHistories
            ->map(function ($history) use ($normalizePartNumber, $installedQtyByPartNumber) {
                $partNumberKey = $normalizePartNumber($history['part']->part_number ?? '');

                return array_merge($history, [
                    'part_number_key' => $partNumberKey,
                    'is_installed' => $partNumberKey !== '' && $installedQtyByPartNumber->has($partNumberKey),
                    'installed_qty' => $partNumberKey !== '' ? (int) ($installedQtyByPartNumber->get($partNumberKey, 0)) : 0,
                ]);
            })
            ->when($partNumberFilterKey !== '', fn($histories) => $histories->filter(fn($history) => $history['part_number_key'] === $partNumberFilterKey))
            ->values();

        $installPartHistories = $rawInstallPartHistories
            ->map(function ($history) use ($normalizePartNumber) {
                return array_merge($history, [
                    'part_number_key' => $normalizePartNumber($history['part']->part_number ?? ''),
                ]);
            })
            ->when($partNumberFilterKey !== '', fn($histories) => $histories->filter(fn($history) => $history['part_number_key'] === $partNumberFilterKey))
            ->values();

        $totalRecommendedQty = $recommendationHistories->sum(fn($history) => (int) ($history['part']->qty ?? 0));
        $totalInstalledQty = $installPartHistories->sum(fn($history) => (int) ($history['part']->qty ?? 0));

        $sparepartReviews = RentalSparepartUsageReview::query()
            ->with(['stock.item', 'stock.location', 'movement'])
            ->where('department', 'RENTAL')
            ->where('job_id', $job->id)
            ->orderByDesc('id')
            ->get();

        $sparepartReviewsByInstallPart = $sparepartReviews
            ->whereNotNull('job_install_part_id')
            ->groupBy('job_install_part_id');

        return view('update-jobs.show', compact(
            'job',
            'sparepartReviews',
            'sparepartReviewsByInstallPart',
            'recommendationHistories',
            'installPartHistories',
            'partNumberFilter',
            'partNumberOptions',
            'totalRecommendedQty',
            'totalInstalledQty'
        ));
    }

    public function edit(mixed $id)
    {
        $job = Job::with(['installParts', 'recommendations'])->findOrFail($id);

        // Cek Hak Akses
        if (!$this->canEditJob($job)) {
            return redirect()->route('update-jobs.show', $id)->withErrors(['error' => 'Akses Ditolak: Anda tidak memiliki hak untuk mengedit Pekerjaan mekanik lain.']);
        }

        $user = Auth::user();
        $branch = $user->branch ?? 'HO / Pusat';
        $partners = DepartmentPartnerOptions::forUser($user, $branch);

        $job->job_type = $this->normalizeJobType($job->job_type);
        $job->status_unit = $this->normalizeStatusUnit($job->status_unit);
        $jobTypeOptions = $this->jobTypeOptions();
        $statusUnitOptions = $this->statusUnitOptions();
        $batteryTypeOptions = $this->batteryTypeOptions();
        $batteryBrandOptions = $this->batteryBrandOptions();

        return view('update-jobs.edit', compact('job', 'user', 'branch', 'partners', 'jobTypeOptions', 'statusUnitOptions', 'batteryTypeOptions', 'batteryBrandOptions'));
    }

    public function update(Request $request, mixed $id)
    {
        $job = Job::findOrFail($id);

        // Cek Hak Akses
        if (!$this->canEditJob($job)) {
            return redirect()->route('update-jobs.show', $id)->withErrors(['error' => 'Akses Ditolak: Anda tidak memiliki hak untuk mengedit Pekerjaan mekanik lain.']);
        }

        $request->merge([
            'job_type' => $this->normalizeJobType($request->input('job_type')),
            'status_unit' => $this->normalizeStatusUnit($request->input('status_unit')),
        ]);

        $validated = $request->validate([
            'work_date'     => 'required|date',
            'in_time'       => 'required|date_format:H:i',
            'out_time'      => 'required|date_format:H:i',
            'serial_number' => 'required|string|max:100',
            'unit_type'     => 'required|string|max:100',
            'nomor_lambung' => 'nullable|string|max:100',
            'year'          => 'nullable|string|max:20',
            'hour_meter'    => 'required|numeric|min:0',
            'battery_type'   => ['required', 'string', Rule::in($this->batteryTypeOptions())],
            'battery_brand'  => ['required', 'string', Rule::in($this->batteryBrandOptions())],
            'customer'      => 'required|string|max:150',
            'location'      => 'required|string|max:150',
            'problem'       => 'required|string',
            'action'        => 'required|string',
            'job_type'      => ['required', 'string', Rule::in($this->jobTypeOptions())],
            'status_unit'   => ['required', 'string', Rule::in($this->statusUnitOptions())],
            'partner'       => 'nullable|string|max:150',
            'vehicle_type'  => 'nullable|string|max:100',
            'nopol'         => 'nullable|string|max:100',
            'problem_date'  => 'nullable|date',
            'rfu_date'      => 'nullable|date',
        ]);

        $validated['partner'] = DepartmentPartnerOptions::normalizePartner($validated['partner'] ?? null, Auth::user());

        if ($blockedResponse = $this->rejectIfAssetWithdrawn($validated['serial_number'])) {
            return $blockedResponse;
        }

        if ($duplicatePmResponse = $this->rejectDuplicatePreventiveMaintenance($validated, $job->id)) {
            return $duplicatePmResponse;
        }

        DB::beginTransaction();

        try {
            $job->update($validated);

            // Hapus & input ulang install parts
            $job->installParts()->delete();
            if ($request->has('inst_part_name')) {
                foreach ($request->inst_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $job->installParts()->create([
                            'part_number' => $request->inst_part_number[$key] ?? null,
                            'part_name'   => $part_name,
                            'qty'         => $request->inst_qty[$key] ?? 1,
                            'remarks'     => $request->inst_remarks[$key] ?? null,
                            'no_job'      => $request->inst_no_job[$key] ?? null,
                            'no_pr'       => $request->inst_no_pr[$key] ?? null,
                        ]);
                    }
                }
            }

            // Hapus & input ulang recommendations
            $job->recommendations()->delete();
            if ($request->has('rec_part_name')) {
                foreach ($request->rec_part_name as $key => $part_name) {
                    if (!empty($part_name)) {
                        $job->recommendations()->create([
                            'part_number' => $request->rec_part_number[$key] ?? null,
                            'part_name'   => $part_name,
                            'qty'         => $request->rec_qty[$key] ?? 1,
                            'remarks'     => $request->rec_remarks[$key] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('update-jobs.show', $job->id)->with('success', 'Update Job berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal memperbarui data: ' . $e->getMessage()]);
        }
    }

    public function destroy(mixed $id)
    {
        $job = Job::findOrFail($id);

        if (!$this->canEditJob($job)) {
            return redirect()->route('update-jobs.show', $id)->withErrors(['error' => 'Akses Ditolak: Anda tidak memiliki hak untuk menghapus data ini.']);
        }

        $job->delete();
        return redirect()->route('update-jobs.index')->with('success', 'Update Job berhasil dihapus.');
    }
}
