<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/PaymentSettingController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentSettingController extends Controller
{
    public function edit()
    {
        abort_unless($this->canManage(), 403);

        $setting = PaymentSetting::current();

        return view('payment-settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'max:100'],
            'receiver_name' => ['nullable', 'string', 'max:180'],
            'receiver_number' => ['nullable', 'string', 'max:180'],
            'admin_whatsapp' => ['nullable', 'string', 'max:30'],
            'payment_note' => ['nullable', 'string', 'max:3000'],
            'is_qris_active' => ['nullable', 'boolean'],
            'qris_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_qris' => ['nullable', 'boolean'],
        ]);

        $setting = PaymentSetting::current();

        $setting->payment_method = $validated['payment_method'];
        $setting->receiver_name = $validated['receiver_name'] ?? null;
        $setting->receiver_number = $validated['receiver_number'] ?? null;
        $setting->admin_whatsapp = $validated['admin_whatsapp'] ?? null;
        $setting->payment_note = $validated['payment_note'] ?? null;
        $setting->is_qris_active = (bool) ($validated['is_qris_active'] ?? false);

        if ($request->boolean('remove_qris')) {
            $this->deletePublicFile($setting->qris_image_path);
            $setting->qris_image_path = null;
            $setting->is_qris_active = false;
        }

        if ($request->hasFile('qris_image')) {
            $this->deletePublicFile($setting->qris_image_path);
            $setting->qris_image_path = $this->storeQrisImage($request);
            $setting->is_qris_active = true;
        }

        $setting->save();

        return back()->with('success', 'Payment settings berhasil diperbarui.');
    }

    private function storeQrisImage(Request $request): string
    {
        $file = $request->file('qris_image');
        $directory = public_path('uploads/payment-settings');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'qris-' . now()->format('YmdHis') . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/payment-settings/' . $filename;
    }

    private function deletePublicFile(?string $path): void
    {
        if (!$path) {
            return;
        }

        $fullPath = public_path($path);

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function canManage(): bool
    {
        $role = strtolower((string) (Auth::user()->status_user ?? Auth::user()->role ?? ''));

        return in_array($role, ['admin', 'super_admin'], true);
    }
}
