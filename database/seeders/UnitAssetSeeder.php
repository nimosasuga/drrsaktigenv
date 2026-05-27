<?php
// database/seeders/UnitAssetSeeder.php

namespace Database\Seeders;

use App\Models\UnitAsset;
use Illuminate\Database\Seeder;

class UnitAssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'kode_unit' => 'EX-101',
                'model_unit' => 'Excavator PC200',
                'serial_number' => 'SN-10029381',
                'status' => 'ready',
            ],
            [
                'kode_unit' => 'DT-205',
                'model_unit' => 'Dump Truck HD465',
                'serial_number' => 'SN-99887766',
                'status' => 'maintenance',
            ],
            [
                'kode_unit' => 'DZ-001',
                'model_unit' => 'Dozer D85ESS',
                'serial_number' => 'SN-11223344',
                'status' => 'breakdown',
            ],
        ];

        foreach ($units as $unit) {
            UnitAsset::create($unit);
        }
    }
}
