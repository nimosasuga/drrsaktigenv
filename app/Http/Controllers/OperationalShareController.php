<?php
// PATH FILE: app/Http/Controllers/OperationalShareController.php

namespace App\Http\Controllers;

use App\Models\Battery;
use App\Models\Charger;
use App\Models\Delivery;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OperationalShareController extends Controller
{
    public function battery(int $id)
    {
        $battery = Battery::with(['installParts', 'recommendations'])->findOrFail($id);

        return $this->redirectToWhatsapp($this->formatBattery($battery));
    }

    public function charger(int $id)
    {
        $charger = Charger::with(['installParts', 'recommendations'])->findOrFail($id);

        return $this->redirectToWhatsapp($this->formatCharger($charger));
    }

    public function delivery(int $id)
    {
        $delivery = Delivery::findOrFail($id);

        return $this->redirectToWhatsapp($this->formatDelivery($delivery));
    }

    public function penarikan(int $id)
    {
        $penarikan = DB::table('penarikans')->where('id', $id)->first();
        abort_if(!$penarikan, 404);

        return $this->redirectToWhatsapp($this->formatPenarikan($penarikan));
    }

    private function redirectToWhatsapp(string $message)
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
    <title>Bagikan ke WhatsApp</title>
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
        .button-secondary {
            margin-top: 10px;
            background: #0f172a;
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
        <p class="title">Bagikan ke WhatsApp</p>
        <p class="text">Tekan tombol di bawah untuk membuka aplikasi WhatsApp.</p>
        <a class="button" href="{$appUrl}">Buka WhatsApp</a>
        <a class="button button-secondary" href="{$webUrl}" target="_blank" rel="noopener noreferrer">Buka WhatsApp Web</a>
        <p class="muted">Halaman ini tidak akan membuka WhatsApp otomatis sebelum tombol ditekan.</p>
    </div>
</body>
</html>
HTML;

        return response($html);
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

    private function dateValue($value): string
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

    private function timeValue($value): string
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

    private function recommendationsText($item): string
    {
        if (!$item->relationLoaded('recommendations') || $item->recommendations->isEmpty()) {
            return "*PART NUMBER :* -\n*PART NAME :* -\n*QTY :* -\n*REMARKS :* -";
        }

        return $item->recommendations->map(function ($part) {
            return implode("\n", [
                '*PART NUMBER :* ' . $this->value($part->part_number),
                '*PART NAME :* ' . $this->value($part->part_name),
                '*QTY :* ' . $this->value($part->qty),
                '*REMARKS :* ' . $this->value($part->remarks),
            ]);
        })->implode("\n\n");
    }

    private function installPartsText($item): string
    {
        if (!$item->relationLoaded('installParts') || $item->installParts->isEmpty()) {
            return "*PART NUMBER :* -\n*PART NAME :* -\n*QTY :* -\n*NO JOB :* -\n*NO PR :* -\n*REMARKS :* -";
        }

        return $item->installParts->map(function ($part) {
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

    private function headerLines(string $title, object $item): array
    {
        $partner = $this->value($item->partner ?? null, '');
        $manPower = trim($this->value($item->pic ?? null, '') . ($partner !== '' ? ' - ' . $partner : ''));
        $vehicle = trim($this->value($item->vehicle ?? null, '') . ' - ' . $this->value($item->nopol ?? null, ''));

        return [
            '*' . $title . '* _' . $this->value($item->status_mekanik ?? null) . '_',
            $this->value($item->job_type ?? null),
            '',
            '*' . $this->upper($item->customer ?? null) . '*',
            '*LOCATION :* ' . $this->upper($item->location ?? null),
            '*DATE :* ' . $this->dateValue($item->date ?? null),
            '*IN :* ' . $this->timeValue($item->in_time ?? null),
            '*OUT :* ' . $this->timeValue($item->out_time ?? null),
            '*MAN POWER :* ' . $this->value($manPower),
            '*KENDARAAN :* ' . $this->value($vehicle),
        ];
    }

    private function detailUnitLines(object $item): array
    {
        return [
            '',
            '> _*DETAIL UNIT*_',
            '*UNIT TYPE :* ' . $this->value($item->unit_type ?? null),
            '*SERIAL NUMBER :* ' . $this->value($item->serial_number ?? null),
            '*YEAR :* ' . $this->value($item->year ?? null),
            '*HOUR METER :* ' . $this->value($item->hour_meter ?? null),
        ];
    }

    private function jobDescriptionLines(object $item): array
    {
        return [
            '',
            '> _*JOB DESCRIPTIONS*_',
            '*JOB TYPE :* ' . $this->value($item->job_type ?? null),
            '*PROBLEM DATE :* ' . $this->dateValue($item->problem_date ?? null),
            '*PROBLEM :* ' . $this->value($item->problem ?? null),
            '*STATUS :* ' . $this->value($item->status_unit ?? null),
            '*RFU DATE :* ' . $this->dateValue($item->rfu_date ?? null),
            '*ACTION :* ' . $this->value($item->action ?? null),
        ];
    }

    private function noteLines($note): array
    {
        return [
            '',
            '> _*NOTE*_',
            $this->value($note),
        ];
    }

    private function formatBattery(Battery $battery): string
    {
        return trim(implode("\n", array_merge(
            $this->headerLines('MANAGEMENT BATTERY', $battery),
            [
                '',
                '> _*DETAIL UNIT*_',
                '*UNIT TYPE :* ' . $this->value($battery->unit_type),
                '*SERIAL NUMBER :* ' . $this->value($battery->serial_number),
                '*BATTERY TYPE :* ' . $this->value($battery->battery_type),
                '*BATTERY SN :* ' . $this->value($battery->sn_battery),
                '*BATTERY YEAR :* ' . $this->value($battery->battery_year),
            ],
            $this->jobDescriptionLines($battery),
            [
                '',
                '> _*RECOMMENDATIONS*_',
                $this->recommendationsText($battery),
                '',
                '> _*INSTALL PART*_',
                $this->installPartsText($battery),
            ]
        )));
    }

    private function formatCharger(Charger $charger): string
    {
        return trim(implode("\n", array_merge(
            $this->headerLines('MANAGEMENT CHARGER', $charger),
            [
                '',
                '> _*DETAIL UNIT*_',
                '*UNIT TYPE :* ' . $this->value($charger->unit_type),
                '*SERIAL NUMBER :* ' . $this->value($charger->serial_number),
                '*CHARGER TYPE :* ' . $this->value($charger->charger_type),
                '*CHARGER SN :* ' . $this->value($charger->sn_charger),
                '*CHARGER YEAR :* ' . $this->value($charger->charger_year),
            ],
            $this->jobDescriptionLines($charger),
            [
                '',
                '> _*RECOMMENDATIONS*_',
                $this->recommendationsText($charger),
                '',
                '> _*INSTALL PART*_',
                $this->installPartsText($charger),
            ]
        )));
    }

    private function formatDelivery(Delivery $delivery): string
    {
        return trim(implode("\n", array_merge(
            $this->headerLines('DELIVERY UNIT', $delivery),
            $this->detailUnitLines($delivery),
            [
                '*STATUS :* ' . $this->value($delivery->status_unit),
                '',
                '> _*EQUIPMENT*_',
                '*BATTERY TYPE :* ' . $this->value($delivery->battery_type),
                '*BATTERY SN :* ' . $this->value($delivery->battery_sn),
                '*CHARGER TYPE :* ' . $this->value($delivery->charger_type),
                '*CHARGER SN :* ' . $this->value($delivery->charger_sn),
                '*TROLLY :* ' . $this->value($delivery->trolly),
            ],
            $this->noteLines($delivery->note)
        )));
    }

    private function formatPenarikan(object $penarikan): string
    {
        return trim(implode("\n", array_merge(
            $this->headerLines('PENARIKAN UNIT', $penarikan),
            $this->detailUnitLines($penarikan),
            [
                '*STATUS :* ' . $this->value($penarikan->status_unit ?? null),
                '',
                '> _*EQUIPMENT*_',
                '*BATTERY TYPE 1 :* ' . $this->value($penarikan->battery_type ?? null),
                '*BATTERY SN 1 :* ' . $this->value($penarikan->battery_sn ?? null),
                '*BATTERY TYPE 2 :* ' . $this->value($penarikan->battery_type_2 ?? null),
                '*BATTERY SN 2 :* ' . $this->value($penarikan->battery_sn_2 ?? null),
                '*CHARGER TYPE :* ' . $this->value($penarikan->charger_type ?? null),
                '*CHARGER SN :* ' . $this->value($penarikan->charger_sn ?? null),
                '*TROLLY 1 :* ' . $this->value($penarikan->trolly ?? null),
                '*TROLLY 2 :* ' . $this->value($penarikan->trolly_2 ?? null),
                '*TROLLY 3 :* ' . $this->value($penarikan->trolly_3 ?? null),
            ],
            $this->noteLines($penarikan->note ?? null)
        )));
    }
}
