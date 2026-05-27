<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin DRR',
            'nrpp' => '12345678', // NRPP Super Admin
            'role' => 'super_admin',
            'password' => Hash::make('password123'), // Default password
        ]);

        User::create([
            'name' => 'Budi Mekanik',
            'nrpp' => '87654321',
            'role' => 'mekanik',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Agus Koordinator',
            'nrpp' => '11223344',
            'role' => 'koordinator',
            'password' => Hash::make('password123'),
        ]);
    }
}
