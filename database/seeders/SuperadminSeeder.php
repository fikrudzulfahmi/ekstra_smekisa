<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@ekstra.com'],
            [
                'name'     => 'Super Administrator',
                'password' => Hash::make('admin@superadmin123'),
                'role'     => 'superadmin',
            ]
        );
    }
}
