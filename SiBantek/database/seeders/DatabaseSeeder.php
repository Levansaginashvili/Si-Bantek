<?php

namespace Database\Seeders;

use App\Models\Dokumen;
use App\Models\Rab;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Account (Login: username = admin, password = password)
        User::create([
            'name' => 'Administrator Direktorat SMP',
            'username' => 'admin',
            'email' => 'admin@kemendikdasmen.go.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Verifikator Account (Login: NIP = 197803152003121002, password = password)
        User::create([
            'name' => 'Drs. Budi Santoso, M.Si.',
            'nip' => '197803152003121002',
            'email' => 'verifikator@kemendikdasmen.go.id',
            'password' => Hash::make('password'),
            'role' => 'verifikator',
        ]);

        // 3. Real Baseline School (From Lampiran XIV SK 0479/PPK/KU-SMP/2026)
        $sekolah = Sekolah::create([
            'npsn' => '10110698',
            'nama_sekolah' => 'SMP NEGERI 3 WOYLA TIMUR',
            'provinsi' => 'Prov. Aceh',
            'kabupaten' => 'Kab. Aceh Barat',
            'alamat' => 'Jl. Pendidikan No. 3, Woyla Timur, Kab. Aceh Barat',
            'nama_kepsek' => 'Dr. H. Ahmad Fauzi, M.Pd.',
            'nip_kepsek' => '197508122002121001',
            'status_dana' => 'Belum Disalurkan',
            'status_dokumen' => 'Belum Lengkap',
        ]);

        // 4. Sekolah Account (Login: NPSN = 10110698, password = password)
        User::create([
            'name' => 'SMP NEGERI 3 WOYLA TIMUR',
            'npsn' => '10110698',
            'email' => 'smpn3woylatimur@kemendikdasmen.go.id',
            'password' => Hash::make('password'),
            'role' => 'sekolah',
            'sekolah_id' => $sekolah->id,
        ]);

        // 5. Initialize Required Document Rows for the School
        $types = [
            'pks',
            'pakta_integritas',
            'sptjm',
            'laporan_awal',
            'rab',
            'perbandingan_siplah',
            'invoice_siplah',
            'bast',
            'foto_fisik_laptop',
            'buku_inventaris',
            'dokumentasi_pemanfaatan',
            'lpj',
        ];

        foreach ($types as $type) {
            Dokumen::create([
                'sekolah_id' => $sekolah->id,
                'jenis_dokumen' => $type,
                'status' => 'Belum Diunggah',
            ]);
        }

        // 6. Initialize Default RAB for School
        Rab::create([
            'sekolah_id' => $sekolah->id,
            'merek_tipe_laptop' => 'Chromebook / Laptop Standar TIK 2026',
            'spesifikasi_ringkas' => 'Processor 4 Core / 8 Thread, Layar 14 inch, RAM 8GB, SSD 256GB, OS GUI Legal',
            'jumlah_unit' => 8,
            'harga_satuan' => 8625000.00,
            'total_harga' => 69000000.00,
            'status' => 'Draft',
        ]);
    }
}
