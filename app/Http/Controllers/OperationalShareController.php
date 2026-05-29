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
        return redirect()->away('https://wa.me/?text=' . urlencode($message));
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

    private function headerLines($title, $item): array
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
                '',
                '> _*JOB DESCRIPTIONS*_',
                '*CATEGORY :* ' . $this->value($battery->category_job),
                '*JOB TYPE :* ' . $this->value($battery->job_type),
                '*PROBLEM DATE :* ' . $this->dateValue($battery->problem_date),
                '*PROBLEM :* ' . $this->value($battery->problem),
                '*STATUS :* ' . $this->value($battery->status_unit),
                '*RFU DATE :* ' . $this->dateValue($battery->rfu_date),
                '*ACTION :* ' . $this->value($battery->action),
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
                '',
                '> _*JOB DESCRIPTIONS*_',
                '*CATEGORY :* ' . $this->value($charger->category_job),
                '*JOB TYPE :* ' . $this->value($charger->job_type),
                '*PROBLEM DATE :* ' . $this->dateValue($charger->problem_date),
                '*PROBLEM :* ' . $this->value($charger->problem),
                '*STATUS :* ' . $this->value($charger->status_unit),
                '*RFU DATE :* ' . $this->dateValue($charger->rfu_date),
                '*ACTION :* ' . $this->value($charger->action),
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
            [
                '',
                '> _*DETAIL UNIT*_',
                '*UNIT TYPE :* ' . $this->value($delivery->unit_type),
                '*SERIAL NUMBER :* ' . $this->value($delivery->serial_number),
                '*YEAR :* ' . $this->value($delivery->year),
                '*HOUR METER :* ' . $this->value($delivery->hour_meter),
                '*STATUS UNIT :* ' . $this->value($delivery->status_unit),
                '',
                '> _*EQUIPMENT DELIVERY*_',
                '*BATTERY TYPE :* ' . $this->value($delivery->battery_type),
                '*BATTERY SN :* ' . $this->value($delivery->battery_sn),
                '*CHARGER TYPE :* ' . $this->value($delivery->charger_type),
                '*CHARGER SN :* ' . $this->value($delivery->charger_sn),
                '*TROLLY :* ' . $this->value($delivery->trolly),
                '',
                '> _*NOTE*_',
                $this->value($delivery->note),
            ]
        )));
    }

    private function formatPenarikan(object $penarikan): string
    {
        return trim(implode("\n", array_merge(
            $this->headerLines('PENARIKAN UNIT', $penarikan),
            [
                '',
                '*PENARIKAN CODE :* ' . $this->value($penarikan->penarikan_code ?? null),
                '',
                '> _*DETAIL UNIT*_',
                '*UNIT TYPE :* ' . $this->value($penarikan->unit_type ?? null),
                '*SERIAL NUMBER :* ' . $this->value($penarikan->serial_number ?? null),
                '*YEAR :* ' . $this->value($penarikan->year ?? null),
                '*HOUR METER :* ' . $this->value($penarikan->hour_meter ?? null),
                '*STATUS UNIT :* ' . $this->value($penarikan->status_unit ?? null),
                '',
                '> _*EQUIPMENT PENARIKAN*_',
                '*BATTERY TYPE 1 :* ' . $this->value($penarikan->battery_type ?? null),
                '*BATTERY SN 1 :* ' . $this->value($penarikan->battery_sn ?? null),
                '*BATTERY TYPE 2 :* ' . $this->value($penarikan->battery_type_2 ?? null),
                '*BATTERY SN 2 :* ' . $this->value($penarikan->battery_sn_2 ?? null),
                '*CHARGER TYPE :* ' . $this->value($penarikan->charger_type ?? null),
                '*CHARGER SN :* ' . $this->value($penarikan->charger_sn ?? null),
                '*TROLLY 1 :* ' . $this->value($penarikan->trolly ?? null),
                '*TROLLY 2 :* ' . $this->value($penarikan->trolly_2 ?? null),
                '*TROLLY 3 :* ' . $this->value($penarikan->trolly_3 ?? null),
                '',
                '> _*NOTE*_',
                $this->value($penarikan->note ?? null),
            ]
        )));
    }
}
