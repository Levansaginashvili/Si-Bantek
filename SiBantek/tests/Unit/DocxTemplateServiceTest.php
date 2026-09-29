<?php

namespace Tests\Unit;

use App\Services\DocxTemplateService;
use Tests\TestCase;

class DocxTemplateServiceTest extends TestCase
{
    public function test_it_generates_laporan_awal_document_without_errors(): void
    {
        $templatePath = base_path('SiBantek_doc/LaporanAwal.docx');
        $this->assertFileExists($templatePath);

        $sekolah = [
            'nama_sekolah' => 'SMP Negeri 1 Contoh',
            'nama_kepsek' => 'Dr. Budi Santoso, M.Pd.',
            'nip_kepsek' => '197501012000031001',
            'nama_bendahara' => 'Siti Aminah, S.Pd.',
            'nip_bendahara' => '198002022005012002',
            'alamat' => 'Jl. Pendidikan No. 10',
            'kabupaten' => 'Kabupaten Sukabumi',
            'nama_bank' => 'Bank BJB',
            'nomor_rekening' => '00123456789',
            'atas_nama_rekening' => 'SMP NEGERI 1 CONTOH',
        ];

        $service = new DocxTemplateService;
        $outputPath = $service->fill($templatePath, 'laporan_awal', $sekolah);

        $this->assertFileExists($outputPath);
        $this->assertGreaterThan(0, filesize($outputPath));

        // Clean up
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }
    }

    public function test_it_generates_laporan_akhir_document_without_errors(): void
    {
        $templatePath = base_path('SiBantek_doc/LaporanAkhir.docx');
        $this->assertFileExists($templatePath);

        $sekolah = [
            'npsn' => '12345678',
            'nama_sekolah' => 'SMP Negeri 1 Contoh',
            'nama_kepsek' => 'Dr. Budi Santoso, M.Pd.',
            'nip_kepsek' => '197501012000031001',
            'nama_bendahara' => 'Siti Aminah, S.Pd.',
            'nip_bendahara' => '198002022005012002',
            'alamat' => 'Jl. Pendidikan No. 10',
            'kabupaten' => 'Kabupaten Sukabumi',
            'provinsi' => 'Jawa Barat',
        ];

        $service = new DocxTemplateService;
        $outputPath = $service->fill($templatePath, 'laporan_akhir', $sekolah);

        $this->assertFileExists($outputPath);
        $this->assertGreaterThan(0, filesize($outputPath));

        // Clean up
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }
    }

    public function test_it_generates_bast_document_without_errors(): void
    {
        $templatePath = base_path('SiBantek_doc/BAST.docx');
        $this->assertFileExists($templatePath);

        $sekolah = [
            'npsn' => '12345678',
            'nama_sekolah' => 'SMP Negeri 1 Contoh',
            'nama_kepsek' => 'Dr. Budi Santoso, M.Pd.',
            'nip_kepsek' => '197501012000031001',
            'nama_bendahara' => 'Siti Aminah, S.Pd.',
            'nip_bendahara' => '198002022005012002',
            'alamat' => 'Jl. Pendidikan No. 10',
            'kabupaten' => 'Kabupaten Sukabumi',
            'provinsi' => 'Jawa Barat',
        ];

        $rab = [
            'total_harga' => 69000000,
        ];

        $service = new DocxTemplateService;
        $outputPath = $service->fill($templatePath, 'bast', $sekolah, $rab);

        $this->assertFileExists($outputPath);
        $this->assertGreaterThan(0, filesize($outputPath));

        // Clean up
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }
    }

    public function test_it_generates_pks_document_without_errors(): void
    {
        $templatePath = base_path('SiBantek_doc/PerjanjianKerjasama.docx');
        $this->assertFileExists($templatePath);

        $sekolah = [
            'npsn' => '12345678',
            'nama_sekolah' => 'SMP Negeri 1 Contoh',
            'nama_kepsek' => 'Dr. Budi Santoso, M.Pd.',
            'nip_kepsek' => '197501012000031001',
            'alamat' => 'Jl. Pendidikan No. 10',
            'nama_ppk' => 'Hendro Sucipto, S.Kom.',
            'nip_ppk' => '197803152003121002',
        ];

        $rab = [
            'total_harga' => 69000000,
        ];

        $service = new DocxTemplateService;
        $outputPath = $service->fill($templatePath, 'pks', $sekolah, $rab);

        $this->assertFileExists($outputPath);
        $this->assertGreaterThan(0, filesize($outputPath));

        // Clean up
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }
    }

    public function test_it_generates_buku_inventaris_document_without_errors(): void
    {
        $templatePath = base_path('SiBantek_doc/IdentifikasiAlat.docx');
        $this->assertFileExists($templatePath);

        $sekolah = [
            'nama_sekolah' => 'SMP Negeri 1 Contoh',
        ];

        $rab = [
            'merek_tipe_laptop' => 'Chromebook / Laptop Standar TIK 2026',
            'spesifikasi_ringkas' => 'Processor 4 Core / 8 Thread, Layar 14 inch, RAM 8GB, SSD 256GB, OS GUI Legal',
            'jumlah_unit' => 8,
        ];

        $service = new DocxTemplateService;
        $outputPath = $service->fill($templatePath, 'buku_inventaris', $sekolah, $rab);

        $this->assertFileExists($outputPath);
        $this->assertGreaterThan(0, filesize($outputPath));

        // Clean up
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }
    }

    public function test_it_generates_pengantar_lpj_document_without_errors(): void
    {
        $templatePath = base_path('SiBantek_doc/PengantarLaporan.docx');
        $this->assertFileExists($templatePath);

        $sekolah = [
            'nama_sekolah' => 'SMP Negeri 1 Contoh',
            'nama_kepsek' => 'Dr. Budi Santoso, M.Pd.',
            'nip_kepsek' => '197501012000031001',
            'alamat' => 'Jl. Pendidikan No. 10',
            'kabupaten' => 'Kabupaten Sukabumi',
            'provinsi' => 'Jawa Barat',
        ];

        $service = new DocxTemplateService;
        $outputPath = $service->fill($templatePath, 'pengantar_lpj', $sekolah);

        $this->assertFileExists($outputPath);
        $this->assertGreaterThan(0, filesize($outputPath));

        // Clean up
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }
    }

    public function test_it_generates_lpj_document_without_errors(): void
    {
        $templatePath = base_path('SiBantek_doc/LaporanPenggunaanDana.docx');
        $this->assertFileExists($templatePath);

        $sekolah = [
            'nama_sekolah' => 'SMP Negeri 1 Contoh',
            'nama_kepsek' => 'Dr. Budi Santoso, M.Pd.',
            'nip_kepsek' => '197501012000031001',
            'nama_bendahara' => 'Siti Aminah, S.Pd.',
            'nip_bendahara' => '198002022005012002',
            'alamat' => 'Jl. Pendidikan No. 10',
            'kabupaten' => 'Kabupaten Sukabumi',
            'provinsi' => 'Jawa Barat',
        ];

        $rab = [
            'merek_tipe_laptop' => 'Chromebook / Laptop Standar TIK 2026',
            'spesifikasi_ringkas' => 'Processor 4 Core / 8 Thread, Layar 14 inch, RAM 8GB, SSD 256GB, OS GUI Legal',
            'jumlah_unit' => 8,
            'total_harga' => 69000000,
        ];

        $service = new DocxTemplateService;
        $outputPath = $service->fill($templatePath, 'lpj', $sekolah, $rab);

        $this->assertFileExists($outputPath);
        $this->assertGreaterThan(0, filesize($outputPath));

        // Clean up
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }
    }

    public function test_lpj_and_bast_dynamically_adapt_when_rab_changes(): void
    {
        $sekolah = [
            'nama_sekolah' => 'SMP Negeri 1 Contoh',
            'nama_kepsek' => 'Dr. Budi Santoso, M.Pd.',
            'nip_kepsek' => '197501012000031001',
            'nama_bendahara' => 'Siti Aminah, S.Pd.',
            'nip_bendahara' => '198002022005012002',
            'alamat' => 'Jl. Pendidikan No. 10',
            'kabupaten' => 'Kabupaten Sukabumi',
            'provinsi' => 'Jawa Barat',
        ];

        // Custom changed RAB: 68.500.000 (sisa: 864.000) with 10 units ASUS ExpertBook
        $customRab = [
            'merek_tipe_laptop' => 'ASUS / ExpertBook B1400',
            'spesifikasi_ringkas' => 'Intel Core i3, RAM 8GB, SSD 256GB',
            'jumlah_unit' => 10,
            'harga_satuan' => 6850000,
            'total_harga' => 68500000,
        ];

        $service = new DocxTemplateService;

        // Test LPJ
        $lpjPath = $service->fill(base_path('SiBantek_doc/LaporanPenggunaanDana.docx'), 'lpj', $sekolah, $customRab);
        $zip = new \ZipArchive;
        $zip->open($lpjPath);
        $xmlLpj = $zip->getFromName('word/document.xml');
        $zip->close();
        unlink($lpjPath);

        $this->assertStringContainsString('68.500.000', $xmlLpj);
        $this->assertStringContainsString('864.000', $xmlLpj);
        $this->assertStringContainsString('10 Unit', $xmlLpj);

        // Test BAST
        $bastPath = $service->fill(base_path('SiBantek_doc/BAST.docx'), 'bast', $sekolah, $customRab);
        $zip->open($bastPath);
        $xmlBast = $zip->getFromName('word/document.xml');
        $zip->close();
        unlink($bastPath);

        $this->assertStringContainsString('68.500.000', $xmlBast);
        $this->assertStringContainsString('864.000', $xmlBast);
        $this->assertStringContainsString('Enam Puluh Delapan Juta Lima Ratus Ribu', $xmlBast);
        $this->assertStringContainsString('Delapan Ratus Enam Puluh Empat Ribu', $xmlBast);
    }
}
