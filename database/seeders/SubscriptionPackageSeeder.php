<?php
// database/seeders/SubscriptionPackageSeeder.php

namespace Database\Seeders;

use App\Models\SubscriptionPackage;
use Illuminate\Database\Seeder;

class SubscriptionPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'role_name' => 'mekanik',
                'package_name' => 'Lisensi 1 Bulan',
                'duration_months' => 1,
                'price' => 10000,
            ],
            [
                'role_name' => 'koordinator',
                'package_name' => 'Lisensi 1 Bulan',
                'duration_months' => 1,
                'price' => 15000,
            ],
            [
                'role_name' => 'sect_head',
                'package_name' => 'Lisensi 1 Bulan',
                'duration_months' => 1,
                'price' => 20000,
            ],
        ];

        foreach ($packages as $pkg) {
            SubscriptionPackage::create($pkg);
        }
    }
}
