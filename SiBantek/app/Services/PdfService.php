<?php

namespace App\Services;

use App\Models\Rab;
use App\Models\Sekolah;

class PdfService
{
    public function generateHtml(string $type, Sekolah $sekolah, ?Rab $rab = null): string
    {
        $kepsek = e($sekolah->nama_kepsek ?? '[Nama Kepala Sekolah]');
        $nip = e($sekolah->nip_kepsek ?? '[NIP Kepala Sekolah]');
        $sekolahNama = e($sekolah->nama_sekolah);
        $npsn = e($sekolah->npsn);
        $alamat = e($sekolah->alamat ?? '[Alamat Sekolah]');
        $kab = e($sekolah->kabupaten);
        $prov = e($sekolah->provinsi);
        $tgl = date('d F Y');

        $style = '
        <style>
            body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; color: #000; margin: 40px; }
            .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
            .header h3, .header h4 { margin: 2px 0; text-transform: uppercase; font-weight: bold; }
            .title { text-align: center; font-weight: bold; text-transform: uppercase; margin: 20px 0; text-decoration: underline; }
            .content { text-align: justify; }
            .table-data { width: 100%; border-collapse: collapse; margin: 15px 0; }
            .table-data th, .table-data td { border: 1px solid #000; padding: 6px 10px; text-align: left; }
            .table-data th { background-color: #f2f2f2; text-align: center; }
            .ttd-container { width: 100%; margin-top: 40px; page-break-inside: avoid; }
            .ttd-box { width: 45%; float: right; text-align: center; }
            .ttd-box-left { width: 45%; float: left; text-align: center; }
            .clear { clear: both; }
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
        </style>';

        $printBtn = '<div class="no-print" style="margin-bottom:20px; text-align:right;">
            <button onclick="window.print()" style="background:#1e3a8a; color:#fff; padding:10px 20px; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Cetak / Simpan PDF</button>
        </div>';

        switch ($type) {
            case 'sptjm':
                return "<html><head><title>SPTJM - {$sekolahNama}</title>{$style}</head><body>{$printBtn}
                <div class='header'>
                    <h4>KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</h4>
                    <h3>{$sekolahNama}</h3>
                    <p style='font-size:10pt; margin:0;'>{$alamat}, {$kab}, {$prov}</p>
                </div>
                <div class='title'>SURAT PERNYATAAN TANGGUNG JAWAB MUTLAK (SPTJM)</div>
                <div class='content'>
                    <p>Yang bertanda tangan di bawah ini:</p>
                    <table style='margin-left: 20px;'>
                        <tr><td width='160'>Nama</td><td>: {$kepsek}</td></tr>
                        <tr><td>NIP</td><td>: {$nip}</td></tr>
                        <tr><td>Jabatan</td><td>: Kepala {$sekolahNama}</td></tr>
                        <tr><td>NPSN</td><td>: {$npsn}</td></tr>
                        <tr><td>Alamat Sekolah</td><td>: {$alamat}</td></tr>
                    </table>
                    <p>Dengan ini menyatakan dengan sesungguhnya bahwa:</p>
                    <ol>
                        <li>Bertindak atas nama jabatan, bertanggung jawab penuh atas pelaksanaan Program Bantuan Pemerintah Peralatan Pembelajaran TIK SMP Tahun Anggaran 2026.</li>
                        <li>Sanggup memulai pekerjaan pengadaan peralatan TIK selambat-lambatnya 14 (empat belas) hari kalender sejak dana bantuan diterima.</li>
                        <li>Sanggup melaksanakan pengadaan minimal 8 unit laptop sesuai spesifikasi minimal yang ditetapkan oleh Direktorat SMP.</li>
                        <li>Sanggup memberikan laporan pertanggungjawaban akhir pekerjaan sesuai ketentuan yang berlaku.</li>
                    </ol>
                    <p>Demikian Surat Pernyataan ini dibuat dengan sebenarnya dan penuh rasa tanggung jawab.</p>
                </div>
                <div class='ttd-container'>
                    <div class='ttd-box'>
                        <p>{$kab}, {$tgl}<br>Kepala Sekolah,</p>
                        <br><br><br><br>
                        <p><strong><u>{$kepsek}</u></strong><br>NIP. {$nip}</p>
                    </div>
                    <div class='clear'></div>
                </div>
                </body></html>";

            case 'pks':
                return "<html><head><title>PKS - {$sekolahNama}</title>{$style}</head><body>{$printBtn}
                <div class='header'>
                    <h4>KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</h4>
                    <h3>DIREKTORAT JENDERAL PENDIDIKAN ANAK USIA DINI, PENDIDIKAN DASAR, DAN PENDIDIKAN MENENGAH</h3>
                    <p style='font-size:9pt;'>DIREKTORAT SEKOLAH MENENGAH PERTAMA</p>
                </div>
                <div class='title'>PERJANJIAN KERJA SAMA BANTUAN TIK SMP 2026</div>
                <div class='content'>
                    <p>Pada hari ini tanggal {$tgl}, kami yang bertanda tangan di bawah ini:</p>
                    <p>1. Pejabat Pembuat Komitmen Direktorat SMP, bertindak untuk dan atas nama Direktorat SMP (PIHAK KESATU).</p>
                    <p>2. <strong>{$kepsek}</strong>, Kepala {$sekolahNama} (NPSN: {$npsn}), bertindak untuk dan atas nama {$sekolahNama} (PIHAK KEDUA).</p>
                    <p>Para pihak sepakat untuk melaksanakan Perjanjian Kerja Sama Bantuan Pemerintah Peralatan Pembelajaran TIK SMP Tahun 2026 dengan besaran dana bantuan Rp 69.364.000,00 (Enam puluh sembilan juta tiga ratus enam puluh empat ribu rupiah) khusus untuk pengadaan peralatan laptop.</p>
                </div>
                <div class='ttd-container'>
                    <div class='ttd-box-left'>
                        <p>PIHAK KESATU<br>PPK Direktorat SMP</p>
                        <br><br><br><br>
                        <p><strong><u>Pejabat Pembuat Komitmen</u></strong></p>
                    </div>
                    <div class='ttd-box'>
                        <p>PIHAK KEDUA<br>Kepala {$sekolahNama}</p>
                        <br><br><br><br>
                        <p><strong><u>{$kepsek}</u></strong><br>NIP. {$nip}</p>
                    </div>
                    <div class='clear'></div>
                </div>
                </body></html>";

            case 'pakta_integritas':
                return "<html><head><title>Pakta Integritas - {$sekolahNama}</title>{$style}</head><body>{$printBtn}
                <div class='header'>
                    <h4>KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</h4>
                    <h3>{$sekolahNama}</h3>
                </div>
                <div class='title'>PAKTA INTEGRITAS</div>
                <div class='content'>
                    <p>Saya yang bertanda tangan di bawah ini, <strong>{$kepsek}</strong> (NIP: {$nip}), selaku Kepala {$sekolahNama}, menyatakan bahwa:</p>
                    <ol>
                        <li>Tidak akan melakukan praktik Korupsi, Kolusi, dan Nepotisme (KKN).</li>
                        <li>Akan menggunakan dana Bantuan Peralatan TIK SMP Tahun 2026 sesuai dengan ketentuan yang ditetapkan.</li>
                        <li>Bersedia diaudit oleh instansi yang berwenang atas penggunaan dana bantuan pemerintah.</li>
                    </ol>
                </div>
                <div class='ttd-container'>
                    <div class='ttd-box'>
                        <p>{$kab}, {$tgl}<br>Kepala Sekolah,</p>
                        <br><br><br><br>
                        <p><strong><u>{$kepsek}</u></strong><br>NIP. {$nip}</p>
                    </div>
                    <div class='clear'></div>
                </div>
                </body></html>";

            case 'rab':
                $merek = e($rab->merek_tipe_laptop ?? 'Laptop Standar TIK 2026');
                $qty = (int)($rab->jumlah_unit ?? 8);
                $harga = number_format($rab->harga_satuan ?? 8625000, 0, ',', '.');
                $total = number_format($rab->total_harga ?? 69000000, 0, ',', '.');

                return "<html><head><title>RAB - {$sekolahNama}</title>{$style}</head><body>{$printBtn}
                <div class='header'>
                    <h4>KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</h4>
                    <h3>{$sekolahNama}</h3>
                </div>
                <div class='title'>RENCANA ANGGARAN BIAYA (RAB) PERALATAN TIK</div>
                <div class='content'>
                    <table class='table-data'>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Jenis Barang</th>
                                <th>Spesifikasi</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan (Rp)</th>
                                <th>Total Biaya (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style='text-align:center;'>1</td>
                                <td>Laptop ({$merek})</td>
                                <td>Min 4 Core/8 Thread, 13-14 inch, 8GB RAM, 256GB SSD, GUI OS Legal</td>
                                <td style='text-align:center;'>{$qty} Unit</td>
                                <td style='text-align:right;'>Rp {$harga}</td>
                                <td style='text-align:right;'>Rp {$total}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan='5' style='text-align:right;'>Total Anggaran Bantuan:</th>
                                <th style='text-align:right;'>Rp {$total}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class='ttd-container'>
                    <div class='ttd-box'>
                        <p>{$kab}, {$tgl}<br>Kepala Sekolah,</p>
                        <br><br><br><br>
                        <p><strong><u>{$kepsek}</u></strong><br>NIP. {$nip}</p>
                    </div>
                    <div class='clear'></div>
                </div>
                </body></html>";

            default:
                return "<html><head><title>Dokumen - {$sekolahNama}</title>{$style}</head><body>{$printBtn}
                <div class='header'>
                    <h3>{$sekolahNama}</h3>
                </div>
                <div class='title'>DOKUMEN RESMI BANTUAN TIK 2026</div>
                <p>Dokumen untuk {$sekolahNama} (NPSN: {$npsn}).</p>
                </body></html>";
        }
    }
}
