<?php
// database/seeders/SuperAdminSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin DRR',
            'nrpp' => '12345678',
            'password' => Hash::make('password123'),
            'status_user' => 'super_admin',
            'branch' => 'Pusat',
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }
}
