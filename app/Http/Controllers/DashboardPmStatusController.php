<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\UnitAsset;
use Illuminate\Http\Request;

class DashboardPmStatusController extends Controller
{
    public function __invoke(Request $request, string $status)
    {
        abort_unless(in_array($status, ['all', 'done', 'pending'], true), 404);

        $pmMonth = now();
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
