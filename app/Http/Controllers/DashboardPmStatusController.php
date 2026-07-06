<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\UnitAsset;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardPmStatusController extends Controller
{
    public function __invoke(Request $request, string $status)
    {
        abort_unless(in_array($status, ['all', 'done', 'pending'], true), 404);

        $pmMonth = now();
        $query = $this->pmStatusAssetQuery($request, $status, $pmMonth);

        $assets = $query
            ->orderBy('customer')
            ->orderBy('location')
            ->orderBy('serial_number')
            ->paginate(50)
            ->withQueryString();

        $pmJobsBySerial = collect();

        if ($assets->count() > 0) {
            $serialNumbers = $assets->getCollection()
                ->pluck('serial_number')
                ->filter()
                ->values();

            $pmJobsBySerial = $this->pmJobQuery($pmMonth->year, $pmMonth->month)
                ->whereIn('serial_number', $serialNumbers)
                ->orderByDesc('work_date')
                ->orderByDesc('id')
                ->get()
                ->unique('serial_number')
                ->keyBy('serial_number');
        }

        $title = match ($status) {
            'done' => 'Unit Sudah PM',
            'pending' => 'Unit Belum PM',
            default => 'Detail Status PM',
        };

        return view('dashboard-pm-status.index', compact(
            'assets',
            'pmJobsBySerial',
            'pmMonth',
            'status',
            'title'
        ));
    }

    public function export(Request $request, string $status)
    {
        abort_unless(in_array($status, ['all', 'done', 'pending'], true), 404);

        $pmMonth = now();
        $title = match ($status) {
            'done' => 'unit-sudah-pm',
            'pending' => 'unit-belum-pm',
            default => 'status-pm',
        };
        $filename = $title . '-' . $pmMonth->format('Ym') . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($request, $status, $pmMonth) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'periode_pm',
                'status_filter',
                'status_pm',
                'serial_number',
                'nomor_lambung',
                'customer',
                'location',
                'branch',
                'unit_type',
                'jenis_unit',
                'year',
                'status_asset',
                'pm_work_date',
                'pm_job_type',
                'pm_pic',
                'pm_hour_meter',
                'pm_status_unit',
                'pm_problem_date',
                'pm_rfu_date',
                'pm_lead_time_rfu_hari',
                'pm_problem',
                'pm_action',
            ], ';');

            $this->pmStatusAssetQuery($request, $status, $pmMonth)
                ->orderBy('customer')
                ->orderBy('location')
                ->orderBy('serial_number')
                ->chunk(500, function ($assets) use ($handle, $pmMonth, $status) {
                    $pmJobsBySerial = $this->pmJobsBySerial($assets->pluck('serial_number'), $pmMonth);

                    foreach ($assets as $asset) {
                        $pmJob = $pmJobsBySerial->get($asset->serial_number);

                        fputcsv($handle, [
                            $pmMonth->translatedFormat('F Y'),
                            $this->statusTitle($status),
                            $pmJob ? 'Sudah PM' : 'Belum PM',
                            $asset->serial_number,
                            $asset->nomor_lambung,
                            $asset->customer,
                            $asset->location,
                            $asset->branch,
                            $asset->unit_type,
                            $asset->jenis_unit,
                            $asset->year,
                            $asset->status,
                            $this->dateValue($pmJob?->work_date),
                            $pmJob?->job_type,
                            $pmJob?->pic,
                            $pmJob?->hour_meter,
                            $pmJob?->status_unit,
                            $this->dateValue($pmJob?->problem_date),
                            $this->dateValue($pmJob?->rfu_date),
                            $this->leadTimeRfuValue($pmJob),
                            $pmJob?->problem,
                            $pmJob?->action,
                        ], ';');
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function pmStatusAssetQuery(Request $request, string $status, Carbon $pmMonth)
    {
        $pmSerialSubquery = $this->pmJobQuery($pmMonth->year, $pmMonth->month)
            ->select('serial_number')
            ->distinct();

        $query = $this->eligibleAssetQuery();

        if ($status === 'done') {
            $query->whereIn('serial_number', $pmSerialSubquery);
        } elseif ($status === 'pending') {
            $query->whereNotIn('serial_number', $pmSerialSubquery);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));

            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('branch', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('year', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    private function pmJobsBySerial($serialNumbers, Carbon $pmMonth)
    {
        $serialNumbers = collect($serialNumbers)->filter()->values();

        if ($serialNumbers->isEmpty()) {
            return collect();
        }

        return $this->pmJobQuery($pmMonth->year, $pmMonth->month)
            ->whereIn('serial_number', $serialNumbers)
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->get()
            ->unique('serial_number')
            ->keyBy('serial_number');
    }

    private function statusTitle(string $status): string
    {
        return match ($status) {
            'done' => 'Unit Sudah PM',
            'pending' => 'Unit Belum PM',
            default => 'Semua Status PM',
        };
    }

    private function dateValue(mixed $date): ?string
    {
        return $date ? Carbon::parse($date)->format('Y-m-d') : null;
    }

    private function leadTimeRfuValue(?Job $job): ?int
    {
        if (!$job) {
            return null;
        }

        if ($job->lead_time_rfu !== null && $job->lead_time_rfu !== '') {
            return (int) $job->lead_time_rfu;
        }

        if (!$job->problem_date || !$job->rfu_date) {
            return null;
        }

        return max(0, (int) Carbon::parse($job->problem_date)->startOfDay()->diffInDays(Carbon::parse($job->rfu_date)->startOfDay()));
    }

    private function eligibleAssetQuery()
    {
        return UnitAsset::query()
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->whereRaw(UnitAsset::activeStatusSql());
    }

    private function pmJobQuery(int $year, int $month)
    {
        return Job::query()
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->where(function ($query) {
                $query->where('job_type', 'Preventive Maintenance')
                    ->orWhere('job_type', 'PM')
                    ->orWhere('job_type', 'like', '%Preventive Maintenance%');
            });
    }
}
