<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\TahunPelajaran;

class AwalSeeder extends Seeder
{
    public function run(): void
    {
        // Akun admin
        User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@presensi.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        // Tahun pelajaran aktif
        TahunPelajaran::create([
            'nama'     => '2025/2026',
            'is_aktif' => true,
        ]);
    }
}
