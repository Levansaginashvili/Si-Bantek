<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Account (Login: username = admin, password = Admin#1234)
        User::updateOrCreate(
            ['email' => 'admin@kemendikdasmen.go.id'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('Admin#1234'),
                'role' => 'admin',
                'status' => 'aktif',
            ]
        );

        // 2. Verifikator Account (Login: NIP = 197803152003121002, password = Verifikator#1234)
        User::updateOrCreate(
            ['email' => 'verifikator@kemendikdasmen.go.id'],
            [
                'name' => 'Hendro sucipto, S.Kom',
                'nip' => '197803152003121002',
                'jabatan' => 'Pejabat pembuat komitmen',
                'alamat' => 'Kompleks Kemendikbudristek Gedung E Lt. 17, Jl. Jenderal Sudirman, Senayan, Jakarta Pusat',
                'password' => Hash::make('Verifikator#1234'),
                'role' => 'verifikator',
                'status' => 'aktif',
            ]
        );

        // 3. 173 Schools & Accounts (From Lampiran XIV SK PPK 2026)
        $this->call(SekolahPenerimaSeeder::class);
    }
}
