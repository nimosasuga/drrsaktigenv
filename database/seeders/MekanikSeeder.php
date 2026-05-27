<?php
// database/seeders/MekanikSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MekanikSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Budi Mekanik',
            'nrpp' => '11223344',
            'password' => Hash::make('password123'),
            'status_user' => 'mekanik',
            'branch' => 'Jakarta Selatan',
            'is_verified' => false,
        ]);
    }
}
