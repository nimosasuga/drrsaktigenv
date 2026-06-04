<?php
// PATH FILE: app/Http/Controllers/UpdateJobShareController.php

namespace App\Http\Controllers;

use App\Models\Job;
use Carbon\Carbon;

class UpdateJobShareController extends Controller
{
    public function message(int $id)
    {
        $job = Job::with(['user', 'installParts', 'recommendations'])->findOrFail($id);

        return $this->openWhatsapp($this->formatMessage($job));
    }

    private function openWhatsapp(string $message)
    {
        $encodedMessage = rawurlencode($message);
        $appUrl = 'whatsapp://send?text=' . $encodedMessage;
        $webUrl = 'https://wa.me/?text=' . $encodedMessage;

        $html = <<<HTML
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Membuka WhatsApp...</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }
        .box {
            width: min(92vw, 420px);
            padding: 24px;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
            text-align: center;
        }
        .title {
            margin: 0 0 8px;
            font-size: 18px;
            font-weight: 700;
        }
        .text {
            margin: 0 0 18px;
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
        }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 44px;
            border-radius: 12px;
            background: #16a34a;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }
        .muted {
            margin-top: 12px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="box">
        <p class="title">Membuka WhatsApp...</p>
        <p class="text">Jika WhatsApp tidak terbuka otomatis, tekan tombol di bawah.</p>
        <a class="button" href="{$appUrl}">Buka WhatsApp</a>
        <p class="muted">Fallback browser akan aktif otomatis jika aplikasi WhatsApp tidak tersedia.</p>
    </div>

    <script>
        const appUrl = "{$appUrl}";
        const webUrl = "{$webUrl}";

        window.location.href = appUrl;

        setTimeout(function () {
            window.location.href = webUrl;
        }, 1200);
    </script>
</body>
</html>
HTML;

        return response($html);
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

    private function userPosition(Job $job): string
    {
        $position = $this->value($job->user?->position, '');

        return $position !== '' ? $position : $this->value($job->status_mekanik);
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
            '*UPDATE JOB RENTAL* _' . $this->userPosition($job) . '_',
            $this->value($job->job_type),
            '',
            '*' . $this->upper($job->customer) . '*',
            '*LOCATION :* ' . $this->upper($job->location),
            '*DATE :* ' . $this->formatDate($job->work_date),
            '*START :* ' . $this->formatTime($job->in_time),
            '*FINISH :* ' . $this->formatTime($job->out_time),
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
