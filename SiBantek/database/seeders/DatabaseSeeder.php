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
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@kemendikdasmen.go.id',
            'password' => Hash::make('Admin#1234'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        // 2. Verifikator Account (Login: NIP = 197803152003121002, password = Verifikator#1234)
        User::create([
            'name' => 'Hendro sucipto, S.Kom',
            'nip' => '197803152003121002',
            'jabatan' => 'Pejabat pembuat komitmen',
            'email' => 'verifikator@kemendikdasmen.go.id',
            'password' => Hash::make('Verifikator#1234'),
            'role' => 'verifikator',
            'status' => 'aktif',
        ]);

        // 3. 173 Schools & Accounts (From Lampiran XIV SK PPK 2026)
        $this->call(SekolahPenerimaSeeder::class);
    }
}
