<?php
// PATH FILE: app/Http/Controllers/UpdateJobSaveController.php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\UnitAsset;
use App\Models\JobInstallPart;
use App\Models\JobRecommendation;
use App\Services\RentalSparepartUsageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateJobSaveController extends Controller
{
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

    private function normalizeJobTypesForStorage($value): ?string
    {
        $items = is_array($value) ? $value : preg_split('/\s*,\s*/', (string) $value);

        $normalized = collect($items)
            ->map(fn($item) => $this->normalizeJobType($item))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return count($normalized) > 0 ? implode(', ', $normalized) : null;
    }

    private function selectedJobTypes($value): array
    {
        $normalized = $this->normalizeJobTypesForStorage($value);

        if (!$normalized) {
            return [];
        }

        return collect(preg_split('/\s*,\s*/', $normalized))
            ->filter()
            ->values()
            ->all();
    }

    private function hasPreventiveMaintenance($value): bool
    {
        return in_array('Preventive Maintenance', $this->selectedJobTypes($value), true);
    }

    private function applyPreventiveMaintenanceQuery($query): void
    {
        $query->where(function ($q) {
            $q->where('job_type', 'Preventive Maintenance')
                ->orWhere('job_type', 'PM')
                ->orWhere('job_type', 'like', '%Preventive Maintenance%');
        });
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

    private function canEditJob(Job $job): bool
    {
        $user = Auth::user();
        $role = $user->role ?? $user->status_user;
        $privilegedRoles = ['koordinator', 'sect_head', 'admin', 'super_admin'];

        return ((int) $user->id === (int) $job->user_id) || in_array($role, $privilegedRoles, true);
    }

    private function isWithdrawnAsset(string $serialNumber): bool
    {
        $asset = UnitAsset::where('serial_number', $serialNumber)->first();

        return $asset && strtoupper(trim((string) ($asset->status ?? ''))) === 'DITARIK';
    }

    private function preventiveMaintenanceMonthChanged(Job $job, array $validated): bool
    {
        $oldWorkDate = $job->work_date ? Carbon::parse($job->work_date) : null;
        $newWorkDate = Carbon::parse($validated['work_date']);

        $oldSerialNumber = strtoupper(trim((string) $job->serial_number));
        $newSerialNumber = strtoupper(trim((string) $validated['serial_number']));

        if (!$oldWorkDate) {
            return true;
        }

        return $oldSerialNumber !== $newSerialNumber
            || (int) $oldWorkDate->year !== (int) $newWorkDate->year
            || (int) $oldWorkDate->month !== (int) $newWorkDate->month;
    }

    private function keepLockedPreventiveMaintenance(Job $job, array $validated): array
    {
        if (!$this->hasPreventiveMaintenance($job->job_type)) {
            return $validated;
        }

        $types = $this->selectedJobTypes($validated['job_type'] ?? null);

        if (!in_array('Preventive Maintenance', $types, true)) {
            array_unshift($types, 'Preventive Maintenance');
        }

        $validated['job_type'] = $this->normalizeJobTypesForStorage($types);

        return $validated;
    }

    private function rejectDuplicatePreventiveMaintenance(array $validated, ?int $exceptJobId = null)
    {
        if (!$this->hasPreventiveMaintenance($validated['job_type'] ?? null)) {
            return null;
        }

        $workDate = Carbon::parse($validated['work_date']);
        $serialNumber = $validated['serial_number'];

        $query = Job::where('serial_number', $serialNumber)
            ->whereYear('work_date', $workDate->year)
            ->whereMonth('work_date', $workDate->month);

        $this->applyPreventiveMaintenanceQuery($query);

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

    private function normalizeRequest(Request $request, ?Job $job = null): void
    {
        $fallbackFields = [
            'work_date',
            'in_time',
            'out_time',
            'serial_number',
            'unit_type',
            'nomor_lambung',
            'year',
            'hour_meter',
            'customer',
            'location',
            'problem',
            'action',
            'job_type',
            'status_unit',
            'partner',
            'vehicle_type',
            'nopol',
            'problem_date',
            'rfu_date',
        ];

        $merged = [];

        foreach ($fallbackFields as $field) {
            $value = $request->input($field);

            if ($job && ($value === null || $value === '')) {
                $value = $job->{$field} ?? null;

                if ($value instanceof \DateTimeInterface) {
                    $value = $value->format(str_contains($field, 'date') || $field === 'work_date' ? 'Y-m-d' : 'H:i');
                }
            }

            $merged[$field] = $value;
        }

        $merged['job_type'] = $this->normalizeJobTypesForStorage($merged['job_type'] ?? null);
        $merged['status_unit'] = $this->normalizeStatusUnit($merged['status_unit'] ?? null);

        $request->merge($merged);
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'work_date' => ['required', 'date'],
            'in_time' => ['nullable', 'date_format:H:i'],
            'out_time' => ['nullable', 'date_format:H:i'],
            'serial_number' => ['required', 'string', 'max:100'],
            'unit_type' => ['required', 'string', 'max:100'],
            'nomor_lambung' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'string', 'max:20'],
            'hour_meter' => ['required', 'numeric', 'min:0'],
            'customer' => ['required', 'string', 'max:150'],
            'location' => ['required', 'string', 'max:150'],
            'problem' => ['required', 'string'],
            'action' => ['required', 'string'],
            'job_type' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $selected = $this->selectedJobTypes($value);
                    $invalid = array_diff($selected, $this->jobTypeOptions());

                    if (count($selected) < 1) {
                        $fail('Tipe pekerjaan wajib dipilih minimal satu.');
                        return;
                    }

                    if (count($invalid) > 0) {
                        $fail('Tipe pekerjaan tidak valid: ' . implode(', ', $invalid));
                    }
                },
            ],
            'status_unit' => ['required', 'string', Rule::in($this->statusUnitOptions())],
            'partner' => ['nullable', 'string', 'max:150'],
            'vehicle_type' => ['nullable', 'string', 'max:100'],
            'nopol' => ['nullable', 'string', 'max:100'],
            'problem_date' => ['nullable', 'date'],
            'rfu_date' => ['nullable', 'date'],
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeRequest($request);
        $validated = $this->validateRequest($request);
        logger()->warning('UPDATE_JOB_STORE_PAYLOAD_DEBUG', [
            'user_id' => Auth::id(),
            'has_inst_part_name' => $request->has('inst_part_name'),
            'inst_part_name' => $request->input('inst_part_name'),
            'inst_part_number' => $request->input('inst_part_number'),
            'inst_qty' => $request->input('inst_qty'),
            'has_rec_part_name' => $request->has('rec_part_name'),
            'rec_part_name' => $request->input('rec_part_name'),
            'rec_part_number' => $request->input('rec_part_number'),
            'rec_qty' => $request->input('rec_qty'),
            'all_keys' => array_keys($request->all()),
        ]);

        if ($this->isWithdrawnAsset($validated['serial_number'])) {
            return back()->withInput()->withErrors(['error' => 'Serial Number ' . $validated['serial_number'] . ' tidak bisa digunakan karena status unit asset sudah DITARIK.']);
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

            $this->syncInstallParts($request, $job, false);
            $this->syncRentalSparepartUsage($job);
            $this->syncRecommendations($request, $job, false);
            $job->load(['installParts', 'recommendations']);

            DB::commit();

            return redirect()->route('update-jobs.show', $job->id)->with('success', 'Update Job berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan Update Job: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, int $id)
    {
        $job = Job::findOrFail($id);

        if (!$this->canEditJob($job)) {
            return redirect()->route('update-jobs.show', $id)->withErrors(['error' => 'Akses Ditolak: Anda tidak memiliki hak untuk mengedit Pekerjaan mekanik lain.']);
        }

        $this->normalizeRequest($request, $job);
        $validated = $this->validateRequest($request);

        if ($this->isWithdrawnAsset($validated['serial_number'])) {
            return back()->withInput()->withErrors(['error' => 'Serial Number ' . $validated['serial_number'] . ' tidak bisa digunakan karena status unit asset sudah DITARIK.']);
        }

        $jobHadPreventiveMaintenance = $this->hasPreventiveMaintenance($job->job_type);
        $validated = $this->keepLockedPreventiveMaintenance($job, $validated);

        if (
            (!$jobHadPreventiveMaintenance || $this->preventiveMaintenanceMonthChanged($job, $validated))
            && ($duplicatePmResponse = $this->rejectDuplicatePreventiveMaintenance($validated, $job->id))
        ) {
            return $duplicatePmResponse;
        }

        DB::beginTransaction();

        try {
            $job->update($validated);
            $this->syncInstallParts($request, $job, true);
            $this->syncRentalSparepartUsage($job);
            $this->syncRecommendations($request, $job, true);
            $job->load(['installParts', 'recommendations']);

            DB::commit();

            return redirect()->route('update-jobs.show', $job->id)->with('success', 'Update Job berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Gagal memperbarui Update Job: ' . $e->getMessage()]);
        }
    }

    private function syncInstallParts(Request $request, Job $job, bool $replaceExisting): void
    {
        $partNames = (array) $request->input('inst_part_name', []);

        if (!$request->has('inst_part_name') || count($partNames) < 1) {
            return;
        }

        foreach ($partNames as $key => $partName) {
            $partName = trim((string) $partName);

            if ($partName === '') {
                continue;
            }

            $payload = [
                'job_id' => $job->id,
                'part_number' => $request->input("inst_part_number.{$key}"),
                'part_name' => $partName,
                'qty' => max(1, (int) $request->input("inst_qty.{$key}", 1)),
                'remarks' => $request->input("inst_remarks.{$key}"),
                'no_job' => $request->input("inst_no_job.{$key}"),
                'no_pr' => $request->input("inst_no_pr.{$key}"),
            ];

            $installPartId = (int) $request->input("inst_id.{$key}", 0);

            if ($installPartId > 0) {
                $installPart = JobInstallPart::query()
                    ->where('job_id', $job->id)
                    ->whereKey($installPartId)
                    ->first();

                if ($installPart) {
                    $installPart->fill($payload);

                    if ($installPart->isDirty()) {
                        $installPart->save();
                    }

                    continue;
                }
            }
            logger()->warning('UPDATE_JOB_CREATE_INSTALL_PART_DEBUG', $payload);

            JobInstallPart::create($payload);
        }
    }
    private function syncRentalSparepartUsage(Job $job): void
    {
        if (strtoupper(trim((string) $job->department)) !== 'RENTAL') {
            return;
        }

        app(RentalSparepartUsageService::class)->processJobInstallParts($job->fresh());
    }

    private function syncRecommendations(Request $request, Job $job, bool $replaceExisting): void
    {
        $partNames = (array) $request->input('rec_part_name', []);

        if (!$request->has('rec_part_name') || count($partNames) < 1) {
            return;
        }

        foreach ($partNames as $key => $partName) {
            $partName = trim((string) $partName);

            if ($partName === '') {
                continue;
            }

            $payload = [
                'job_id' => $job->id,
                'part_number' => $request->input("rec_part_number.{$key}"),
                'part_name' => $partName,
                'qty' => max(1, (int) $request->input("rec_qty.{$key}", 1)),
                'remarks' => $request->input("rec_remarks.{$key}"),
            ];

            $recommendationId = (int) $request->input("rec_id.{$key}", 0);

            if ($recommendationId > 0) {
                $recommendation = JobRecommendation::query()
                    ->where('job_id', $job->id)
                    ->whereKey($recommendationId)
                    ->first();

                if ($recommendation) {
                    $recommendation->fill($payload);

                    if ($recommendation->isDirty()) {
                        $recommendation->save();
                    }

                    continue;
                }
            }
            logger()->warning('UPDATE_JOB_CREATE_RECOMMENDATION_DEBUG', $payload);

            JobRecommendation::create($payload);
        }
    }
}
