<?php

namespace Tests\Feature;

use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DokumenDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_sekolah_user_can_download_pks_document(): void
    {
        $sekolah = Sekolah::create([
            'npsn' => '10103650',
            'nama_sekolah' => 'SMP NEGERI 3 WOYLA TIMUR',
            'provinsi' => 'Aceh',
            'kabupaten' => 'Aceh Barat',
            'alamat' => 'Jl. Pendidikan No. 45, Woyla Timur',
            'nama_kepsek' => 'Dr. H. Ahmad Fauzi, M.Pd.',
            'nip_kepsek' => '197508122002121001',
            'nama_bendahara' => 'Cut Rina, S.Pd.',
            'nip_bendahara' => '198205142006042003',
        ]);

        $user = User::create([
            'name' => 'SMP NEGERI 3 WOYLA TIMUR',
            'npsn' => '10103650',
            'email' => 'sekolah@example.com',
            'password' => bcrypt('password'),
            'role' => 'sekolah',
            'sekolah_id' => $sekolah->id,
        ]);

        $verifikator = User::create([
            'name' => 'Drs. Hendro Sucipto, S.Kom.',
            'nip' => '197803152003121002',
            'email' => 'verifikator@example.com',
            'password' => bcrypt('password'),
            'role' => 'verifikator',
        ]);

        $response = $this->actingAs($user)->get('/sekolah/dokumen/download/pks');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    public function test_sekolah_user_can_download_buku_inventaris_document(): void
    {
        $sekolah = Sekolah::create([
            'npsn' => '10103650',
            'nama_sekolah' => 'SMP NEGERI 3 WOYLA TIMUR',
            'provinsi' => 'Aceh',
            'kabupaten' => 'Aceh Barat',
            'alamat' => 'Jl. Pendidikan No. 45, Woyla Timur',
            'nama_kepsek' => 'Dr. H. Ahmad Fauzi, M.Pd.',
            'nip_kepsek' => '197508122002121001',
            'nama_bendahara' => 'Cut Rina, S.Pd.',
            'nip_bendahara' => '198205142006042003',
        ]);

        $sekolah->rab()->create([
            'merek_tipe_laptop' => 'Chromebook / Laptop Standar TIK 2026',
            'spesifikasi_ringkas' => 'Processor 4 Core / 8 Thread, Layar 14 inch, RAM 8GB, SSD 256GB, OS GUI Legal',
            'jumlah_unit' => 8,
            'harga_satuan' => 8625000.00,
            'total_harga' => 69000000.00,
            'status' => 'Disetujui',
        ]);

        $user = User::create([
            'name' => 'SMP NEGERI 3 WOYLA TIMUR',
            'npsn' => '10103650',
            'email' => 'sekolah@example.com',
            'password' => bcrypt('password'),
            'role' => 'sekolah',
            'sekolah_id' => $sekolah->id,
        ]);

        $response = $this->actingAs($user)->get('/sekolah/dokumen/download/buku_inventaris');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    public function test_sekolah_user_can_download_pengantar_lpj_document(): void
    {
        $sekolah = Sekolah::create([
            'npsn' => '10103650',
            'nama_sekolah' => 'SMP NEGERI 3 WOYLA TIMUR',
            'provinsi' => 'Aceh',
            'kabupaten' => 'Aceh Barat',
            'alamat' => 'Jl. Pendidikan No. 45, Woyla Timur',
            'nama_kepsek' => 'Dr. H. Ahmad Fauzi, M.Pd.',
            'nip_kepsek' => '197508122002121001',
            'nama_bendahara' => 'Cut Rina, S.Pd.',
            'nip_bendahara' => '198205142006042003',
        ]);

        $user = User::create([
            'name' => 'SMP NEGERI 3 WOYLA TIMUR',
            'npsn' => '10103650',
            'email' => 'sekolah@example.com',
            'password' => bcrypt('password'),
            'role' => 'sekolah',
            'sekolah_id' => $sekolah->id,
        ]);

        $response = $this->actingAs($user)->get('/sekolah/dokumen/download/pengantar_lpj');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    public function test_sekolah_user_can_download_lpj_document(): void
    {
        $sekolah = Sekolah::create([
            'npsn' => '10103650',
            'nama_sekolah' => 'SMP NEGERI 3 WOYLA TIMUR',
            'provinsi' => 'Aceh',
            'kabupaten' => 'Aceh Barat',
            'alamat' => 'Jl. Pendidikan No. 45, Woyla Timur',
            'nama_kepsek' => 'Dr. H. Ahmad Fauzi, M.Pd.',
            'nip_kepsek' => '197508122002121001',
            'nama_bendahara' => 'Cut Rina, S.Pd.',
            'nip_bendahara' => '198205142006042003',
        ]);

        $sekolah->rab()->create([
            'merek_tipe_laptop' => 'Chromebook / Laptop Standar TIK 2026',
            'spesifikasi_ringkas' => 'Processor 4 Core / 8 Thread, Layar 14 inch, RAM 8GB, SSD 256GB, OS GUI Legal',
            'jumlah_unit' => 8,
            'harga_satuan' => 8625000.00,
            'total_harga' => 69000000.00,
            'status' => 'Disetujui',
        ]);

        $user = User::create([
            'name' => 'SMP NEGERI 3 WOYLA TIMUR',
            'npsn' => '10103650',
            'email' => 'sekolah@example.com',
            'password' => bcrypt('password'),
            'role' => 'sekolah',
            'sekolah_id' => $sekolah->id,
        ]);

        $response = $this->actingAs($user)->get('/sekolah/dokumen/download/lpj');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    public function test_sekolah_user_can_download_laporan_akhir_with_address_fields(): void
    {
        $sekolah = Sekolah::create([
            'npsn' => '10103650',
            'nama_sekolah' => 'SMP NEGERI 3 WOYLA TIMUR',
            'provinsi' => 'Aceh',
            'kabupaten' => 'Aceh Barat',
            'alamat' => 'Jl. Pendidikan',
            'rt' => '001',
            'rw' => '002',
            'nomor_bangunan' => '45',
            'desa_kelurahan' => 'Pasir Putih',
            'kecamatan' => 'Woyla Timur',
            'kode_pos' => '23685',
            'nama_kepsek' => 'Dr. H. Ahmad Fauzi, M.Pd.',
            'nip_kepsek' => '197508122002121001',
            'nama_bendahara' => 'Cut Rina, S.Pd.',
            'nip_bendahara' => '198205142006042003',
            'no_telepon' => '081234567890',
            'email_sekolah' => 'smpn3@sch.id',
        ]);

        $user = User::create([
            'name' => 'SMP NEGERI 3 WOYLA TIMUR',
            'npsn' => '10103650',
            'email' => 'sekolah@example.com',
            'password' => bcrypt('password'),
            'role' => 'sekolah',
            'sekolah_id' => $sekolah->id,
        ]);

        $response = $this->actingAs($user)->get('/sekolah/dokumen/download/laporan_akhir');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    public function test_sekolah_user_can_download_perbandingan_siplah_and_survey_harga(): void
    {
        $sekolah = Sekolah::create([
            'npsn' => '10103650',
            'nama_sekolah' => 'SMP NEGERI 3 WOYLA TIMUR',
            'provinsi' => 'Aceh',
            'kabupaten' => 'Aceh Barat',
            'alamat' => 'Jl. Pendidikan No. 45',
            'nama_kepsek' => 'Dr. H. Ahmad Fauzi, M.Pd.',
            'nip_kepsek' => '197508122002121001',
            'nama_bendahara' => 'Cut Rina, S.Pd.',
            'nip_bendahara' => '198205142006042003',
        ]);

        $user = User::create([
            'name' => 'SMP NEGERI 3 WOYLA TIMUR',
            'npsn' => '10103650',
            'email' => 'sekolah@example.com',
            'password' => bcrypt('password'),
            'role' => 'sekolah',
            'sekolah_id' => $sekolah->id,
        ]);

        $res1 = $this->actingAs($user)->get('/sekolah/dokumen/download/perbandingan_siplah');
        $res1->assertStatus(200);
        $res1->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $res2 = $this->actingAs($user)->get('/sekolah/dokumen/download/survey_harga');
        $res2->assertStatus(200);
        $res2->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    public function test_sekolah_can_delete_unapproved_document(): void
    {
        $sekolah = Sekolah::create([
            'npsn' => '10103650',
            'nama_sekolah' => 'SMP NEGERI 3 WOYLA TIMUR',
            'provinsi' => 'Aceh',
            'kabupaten' => 'Aceh Barat',
            'alamat' => 'Jl. Pendidikan No. 45',
            'nama_kepsek' => 'Dr. H. Ahmad Fauzi, M.Pd.',
            'nip_kepsek' => '197508122002121001',
            'nama_bendahara' => 'Cut Rina, S.Pd.',
            'nip_bendahara' => '198205142006042003',
        ]);

        $dokumen = $sekolah->dokumens()->create([
            'jenis_dokumen' => 'pks',
            'file_path' => 'dokumen/10103650/pks.docx',
            'status' => 'Menunggu Verifikasi',
        ]);

        $user = User::create([
            'name' => 'SMP NEGERI 3 WOYLA TIMUR',
            'npsn' => '10103650',
            'email' => 'sekolah@example.com',
            'password' => bcrypt('password'),
            'role' => 'sekolah',
            'sekolah_id' => $sekolah->id,
        ]);

        $response = $this->actingAs($user)->delete("/sekolah/dokumen/{$dokumen->id}");

        $response->assertRedirect();
        $dokumen->refresh();
        $this->assertNull($dokumen->file_path);
        $this->assertEquals('Belum Diunggah', $dokumen->status);
    }

    public function test_sekolah_cannot_delete_approved_document(): void
    {
        $sekolah = Sekolah::create([
            'npsn' => '10103650',
            'nama_sekolah' => 'SMP NEGERI 3 WOYLA TIMUR',
            'provinsi' => 'Aceh',
            'kabupaten' => 'Aceh Barat',
            'alamat' => 'Jl. Pendidikan No. 45',
            'nama_kepsek' => 'Dr. H. Ahmad Fauzi, M.Pd.',
            'nip_kepsek' => '197508122002121001',
            'nama_bendahara' => 'Cut Rina, S.Pd.',
            'nip_bendahara' => '198205142006042003',
        ]);

        $dokumen = $sekolah->dokumens()->create([
            'jenis_dokumen' => 'pks',
            'file_path' => 'dokumen/10103650/pks.docx',
            'status' => 'Disetujui',
        ]);

        $user = User::create([
            'name' => 'SMP NEGERI 3 WOYLA TIMUR',
            'npsn' => '10103650',
            'email' => 'sekolah@example.com',
            'password' => bcrypt('password'),
            'role' => 'sekolah',
            'sekolah_id' => $sekolah->id,
        ]);

        $response = $this->actingAs($user)->delete("/sekolah/dokumen/{$dokumen->id}");

        $response->assertRedirect();
        $dokumen->refresh();
        $this->assertNotNull($dokumen->file_path);
        $this->assertEquals('Disetujui', $dokumen->status);
    }
}
