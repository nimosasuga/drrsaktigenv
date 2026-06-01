<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Models/PaymentSetting.php
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_method',
        'receiver_name',
        'receiver_number',
        'admin_whatsapp',
        'qris_image_path',
        'is_qris_active',
        'payment_note',
    ];

    protected $casts = [
        'is_qris_active' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'payment_method' => 'Transfer Manual',
            'receiver_name' => 'Admin DRR SAKTI',
            'receiver_number' => '-',
            'admin_whatsapp' => '6285133331467',
            'is_qris_active' => false,
            'payment_note' => 'Selesaikan pembayaran sesuai nominal tagihan, lalu konfirmasi ke admin melalui WhatsApp.',
        ]);
    }

    public function qrisUrl(): ?string
    {
        return $this->qris_image_path ? asset($this->qris_image_path) : null;
    }

    public function adminWhatsappNumber(): string
    {
        $number = preg_replace('/\D+/', '', (string) $this->admin_whatsapp);

        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        return $number ?: '6285133331467';
    }
}
