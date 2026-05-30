<?php
// PATH FILE: app/Http/Controllers/UpdateJobShareController.php

namespace App\Http\Controllers;

use App\Models\Job;
use Carbon\Carbon;

class UpdateJobShareController extends Controller
{
    public function message(int $id)
    {
        $job = Job::with(['installParts', 'recommendations'])->findOrFail($id);
        $message = $this->formatMessage($job);
        $whatsappUrl = 'https://wa.me/?text=' . urlencode($message);

        return redirect()->away($whatsappUrl);
    }

    private function formatDate($value): string
    {
        if (blank($value)) {
            return '-';
        }

        try {
            return Carbon::parse($value)->locale('id')->translatedFormat('l, d F Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function formatTime($value): string
    {
        if (blank($value)) {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function value($value, string $fallback = '-'): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : $fallback;
    }

    private function upper($value, string $fallback = '-'): string
    {
        return strtoupper($this->value($value, $fallback));
    }

    private function recommendationsText(Job $job): string
    {
        if ($job->recommendations->isEmpty()) {
            return "*PART NUMBER :* -\n*PART NAME :* -\n*QTY :* -\n*REMARKS :* -";
        }

        return $job->recommendations->map(function ($part) {
            return implode("\n", [
                '*PART NUMBER :* ' . $this->value($part->part_number),
                '*PART NAME :* ' . $this->value($part->part_name),
                '*QTY :* ' . $this->value($part->qty),
                '*REMARKS :* ' . $this->value($part->remarks),
            ]);
        })->implode("\n\n");
    }

    private function installPartsText(Job $job): string
    {
        if ($job->installParts->isEmpty()) {
            return "*PART NUMBER :* -\n*PART NAME :* -\n*QTY :* -\n*NO JOB :* -\n*NO PR :* -\n*REMARKS :* -";
        }

        return $job->installParts->map(function ($part) {
            return implode("\n", [
                '*PART NUMBER :* ' . $this->value($part->part_number),
                '*PART NAME :* ' . $this->value($part->part_name),
                '*QTY :* ' . $this->value($part->qty),
                '*NO JOB :* ' . $this->value($part->no_job),
                '*NO PR :* ' . $this->value($part->no_pr),
                '*REMARKS :* ' . $this->value($part->remarks),
            ]);
        })->implode("\n\n");
    }

    private function headerLines(Job $job): array
    {
        $partner = $this->value($job->partner, '');
        $manPower = trim($this->value($job->pic, '') . ($partner !== '' ? ' - ' . $partner : ''));
        $vehicle = trim($this->value($job->vehicle_type, '') . ' - ' . $this->value($job->nopol, ''));

        return [
            '*UPDATE JOB RENTAL* _' . $this->value($job->status_mekanik) . '_',
            $this->value($job->job_type),
            '',
            '*' . $this->upper($job->customer) . '*',
            '*LOCATION :* ' . $this->upper($job->location),
            '*DATE :* ' . $this->formatDate($job->work_date),
            '*IN :* ' . $this->formatTime($job->in_time),
            '*OUT :* ' . $this->formatTime($job->out_time),
            '*MAN POWER :* ' . $this->value($manPower),
            '*KENDARAAN :* ' . $this->value($vehicle),
        ];
    }

    private function detailUnitLines(Job $job): array
    {
        return [
            '',
            '> _*DETAIL UNIT*_',
            '*NOMOR LAMBUNG :* ' . $this->value($job->nomor_lambung),
            '*UNIT TYPE :* ' . $this->value($job->unit_type),
            '*SERIAL NUMBER :* ' . $this->value($job->serial_number),
            '*YEAR :* ' . $this->value($job->year),
            '*HOUR METER :* ' . $this->value($job->hour_meter),
        ];
    }

    private function jobDescriptionLines(Job $job): array
    {
        return [
            '',
            '> _*JOB DESCRIPTIONS*_',
            '*JOB TYPE :* ' . $this->value($job->job_type),
            '*PROBLEM DATE :* ' . $this->formatDate($job->problem_date),
            '*PROBLEM :* ' . $this->value($job->problem),
            '*STATUS :* ' . $this->value($job->status_unit),
            '*RFU DATE :* ' . $this->formatDate($job->rfu_date),
            '*ACTION :* ' . $this->value($job->action),
        ];
    }

    private function formatMessage(Job $job): string
    {
        return trim(implode("\n", array_merge(
            $this->headerLines($job),
            $this->detailUnitLines($job),
            $this->jobDescriptionLines($job),
            [
                '',
                '> _*RECOMMENDATIONS*_',
                $this->recommendationsText($job),
                '',
                '> _*INSTALL PART*_',
                $this->installPartsText($job),
            ]
        )));
    }
}
