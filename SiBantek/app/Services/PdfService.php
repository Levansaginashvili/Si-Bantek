<?php

namespace App\Services;

use App\Models\Rab;
use App\Models\Sekolah;

class PdfService
{
    /**
     * Generate HTML document formatted to match the official Panduan Pelaksanaan Bantuan TIK 2026,
     * auto-populated with school profile data, principal info, and RAB details.
     */
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
        $tahun = date('Y');

        $style = '
        <style>
            @page {
                size: A4;
                margin: 20mm 20mm 20mm 25mm;
            }
            body {
                font-family: "Times New Roman", Times, serif;
                font-size: 11pt;
                line-height: 1.4;
                color: #000;
                margin: 0 auto;
                max-width: 800px;
                padding: 30px;
                background-color: #fff;
            }
            .header-kop {
                text-align: center;
                border-bottom: 3px double #000;
                padding-bottom: 8px;
                margin-bottom: 18px;
            }
            .header-kop h4 {
                margin: 0;
                font-size: 11pt;
                font-weight: normal;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .header-kop h3 {
                margin: 2px 0;
                font-size: 12pt;
                font-weight: bold;
                text-transform: uppercase;
            }
            .header-kop p {
                margin: 0;
                font-size: 9.5pt;
                font-style: italic;
            }
            .doc-title {
                text-align: center;
                font-weight: bold;
                font-size: 12pt;
                text-transform: uppercase;
                margin: 18px 0 14px 0;
            }
            .content {
                text-align: justify;
            }
            .field-table {
                margin: 10px 0 15px 20px;
                border-collapse: collapse;
                width: calc(100% - 20px);
            }
            .field-table td {
                padding: 3px 6px;
                vertical-align: top;
            }
            .grid-table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
                font-size: 10pt;
            }
            .grid-table th, .grid-table td {
                border: 1px solid #000;
                padding: 6px 8px;
                text-align: left;
            }
            .grid-table th {
                background-color: #f5f5f5;
                text-align: center;
                font-weight: bold;
            }
            .ttd-container {
                width: 100%;
                margin-top: 35px;
                page-break-inside: avoid;
            }
            .ttd-left {
                width: 48%;
                float: left;
                text-align: center;
            }
            .ttd-right {
                width: 48%;
                float: right;
                text-align: center;
            }
            .clear {
                clear: both;
            }
            .btn-print {
                background: #1e2d5a;
                color: #ffffff;
                padding: 10px 22px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-weight: bold;
                font-size: 13px;
                font-family: sans-serif;
                box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            }
            .btn-print:hover {
                background: #162247;
            }
            @media print {
                body {
                    padding: 0;
                    margin: 0;
                    box-shadow: none;
                }
                .no-print {
                    display: none !important;
                }
            }
        </style>';

        $printBar = '<div class="no-print" style="margin-bottom: 25px; text-align: right; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
            <span style="font-family: sans-serif; font-size: 12px; color: #64748b; margin-right: 12px;">Format resmi ber-header terisi data sekolah otomatis</span>
            <button onclick="window.print()" class="btn-print">Cetak / Simpan PDF</button>
        </div>';

        switch ($type) {

            // Lampiran VI: Format Surat Pernyataan Tanggung Jawab Mutlak (SPTJM)
            case 'sptjm':
                return "<html><head><title>SPTJM - {$sekolahNama}</title>{$style}</head><body>{$printBar}
                <div class='header-kop'>
                    <h3>KOP {$sekolahNama}</h3>
                    <p style='font-style:normal; font-size:10pt;'>{$alamat}, {$kab}, {$prov}</p>
                </div>
                <div class='doc-title' style='margin-bottom: 20px;'>SURAT PERNYATAAN TANGGUNG JAWAB MUTLAK</div>
                <div class='content'>
                    <p>Yang bertanda tangan di bawah ini, saya :</p>
                    <table class='field-table' style='margin-left: 0; width: 100%;'>
                        <tr><td width='140'>Nama</td><td width='15'>:</td><td><strong>{$kepsek}</strong></td></tr>
                        <tr><td>NIP/NIK</td><td>:</td><td>{$nip}</td></tr>
                        <tr><td>Jabatan</td><td>:</td><td>Kepala Sekolah</td></tr>
                        <tr><td>Alamat</td><td>:</td><td>{$alamat}</td></tr>
                    </table>
                    <p style='margin-top: 15px;'>bertindak atas nama jabatan, dengan ini menyatakan bahwa:</p>
                    <ol style='padding-left: 22px; line-height: 1.6;'>
                        <li style='margin-bottom: 6px;'>Memiliki surat keputusan pengangkatan sebagai kepala satuan pendidikan dari pejabat yang berwenang.</li>
                        <li style='margin-bottom: 6px;'>Sanggup memulai pekerjaan Program Pengadaan Sarana TIK Satuan Pendidikan Sekolah Menengah Pertama (SMP) Tahun Anggaran 2026 selambat-lambatnya 14 (empat belas) hari kalender sejak dana masuk ke rekening sekolah.</li>
                        <li style='margin-bottom: 6px;'>Sanggup untuk melaksanakan dan menyelesaikan pekerjaan Program Pengadaan Sarana TIK Satuan Pendidikan Sekolah Menengah Pertama (SMP) Tahun Anggaran 2026 sesuai Perjanjian Kerja Sama (PKS).</li>
                        <li style='margin-bottom: 6px;'>Sanggup memberikan laporan pertanggungjawaban akhir pelaksanaan pekerjaan Program Pengadaan Sarana TIK Satuan Pendidikan Sekolah Menengah Pertama (SMP) Tahun Anggaran 2026 selambat-lambatnya 14 (empat belas) hari kerja setelah jangka waktu pelaksanaan pekerjaan selesai.</li>
                        <li style='margin-bottom: 6px;'>Bersedia bertanggung jawab penuh atas semua pengeluaran dan pemanfaatan seluruh dana yang digunakan dalam rangka pelaksanaan Bantuan Pemerintah Program Pengadaan Sarana TIK Satuan Pendidikan Sekolah Menengah Pertama (SMP) mengacu Panduan Pelaksanaan dan Laporan Bantuan Pemerintah Program Pengadaan Sarana TIK Satuan Pendidikan Sekolah Menengah Pertama (SMP) Tahun Anggaran 2026 serta Perjanjian Kerja Sama (PKS).</li>
                    </ol>
                    <p style='margin-top: 15px;'>Apabila pernyataan ini tidak benar dan atau di kemudian hari saya melakukan tidak memenuhi pernyataan tersebut atau lalai, maka saya bersedia mempertanggungjawabkannya sesuai dengan ketentuan perundang-undangan.</p>
                </div>
                <div class='ttd-container' style='margin-top: 30px;'>
                    <div class='ttd-right' style='width: 320px; text-align: left; margin-left: auto;'>
                        <table style='width: 100%; border-collapse: collapse; font-size: 11pt;'>
                            <tr><td width='80'>Dibuat di</td><td width='15'>:</td><td>{$kab}</td></tr>
                            <tr><td>Tanggal</td><td>:</td><td>{$tgl}</td></tr>
                        </table>
                        <p style='margin-top: 5px; margin-bottom: 0;'>Kepala Sekolah Menengah Pertama</p>
                        <p style='margin-top: 0; margin-bottom: 30px;'><strong>{$sekolahNama}</strong></p>
                        
                        <div style='border: 1px dashed #999; width: 110px; padding: 6px; text-align: center; font-size: 9pt; color: #666; margin-bottom: 35px;'>
                            Materai<br>Rp 10.000,00
                        </div>
                        
                        <p style='margin-bottom: 2px;'><strong><u>{$kepsek}</u></strong></p>
                        <p style='margin-top: 0;'>NIP {$nip}</p>
                    </div>
                    <div class='clear'></div>
                </div>
                </body></html>";

            // Lampiran III: Format Perjanjian Kerja Sama (PKS)
            case 'pks':
                return "<html><head><title>PKS - {$sekolahNama}</title>{$style}</head><body>{$printBar}
                <div class='header-kop'>
                    <h4>KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</h4>
                    <h3>DIREKTORAT JENDERAL PENDIDIKAN ANAK USIA DINI, PENDIDIKAN DASAR, DAN PENDIDIKAN MENENGAH</h3>
                    <p>DIREKTORAT SEKOLAH MENENGAH PERTAMA</p>
                </div>
                <div class='doc-title'>PERJANJIAN KERJA SAMA<br><span style='font-size:10pt; font-weight:normal;'>PELAKSANAAN BANTUAN PEMERINTAH PERALATAN PEMBELAJARAN TIK SMP TAHUN {$tahun}</span></div>
                <div class='content'>
                    <p>Pada hari ini tanggal <strong>{$tgl}</strong>, kami yang bertanda tangan di bawah ini:</p>
                    <table class='field-table'>
                        <tr><td width='160'>1. Nama / Jabatan</td><td width='15'>:</td><td>Pejabat Pembuat Komitmen (PPK) Direktorat SMP</td></tr>
                        <tr><td>Alamat Kedudukan</td><td>:</td><td>Kompleks Kemendikdasmen Gedung E Lt. 17, Jl. Jenderal Sudirman Senayan, Jakarta</td></tr>
                        <tr><td colspan='3'>Selanjutnya disebut sebagai <strong>PIHAK KESATU</strong>.</td></tr>
                    </table>
                    <table class='field-table'>
                        <tr><td width='160'>2. Nama Kepala Sekolah</td><td width='15'>:</td><td><strong>{$kepsek}</strong></td></tr>
                        <tr><td>NIP</td><td>:</td><td>{$nip}</td></tr>
                        <tr><td>Jabatan</td><td>:</td><td>Kepala {$sekolahNama} (NPSN: {$npsn})</td></tr>
                        <tr><td>Alamat Sekolah</td><td>:</td><td>{$alamat}, {$kab}, {$prov}</td></tr>
                        <tr><td colspan='3'>Selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong>.</td></tr>
                    </table>
                    <p>PIHAK KESATU dan PIHAK KEDUA sepakat untuk mengikatkan diri dalam Perjanjian Kerja Sama Bantuan Pemerintah Peralatan Pembelajaran TIK SMP Tahun {$tahun} dengan ketentuan sebagai berikut:</p>
                    <p><strong>PASAL 1: NILAI BANTUAN & ALOKASI</strong><br>Besaran alokasi dana bantuan yang dialokasikan kepada PIHAK KEDUA adalah sebesar <strong>Rp 69.364.000,00 (Enam puluh sembilan juta tiga ratus enam puluh empat ribu rupiah)</strong> khusus dipergunakan untuk pengadaan peralatan laptop TIK.</p>
                    <p><strong>PASAL 2: HAK DAN KEWAJIBAN</strong><br>1. PIHAK KESATU berhak melakukan verifikasi dan pemantauan atas pelaksanaan pengadaan.<br>2. PIHAK KEDUA berkewajiban melaksanakan pengadaan laptop melalui SIPLah dan menyampaikan Laporan Pertanggungjawaban (LPJ) setelah pengadaan selesai.</p>
                </div>
                <div class='ttd-container'>
                    <div class='ttd-left'>
                        <p>PIHAK KESATU<br>PPK Direktorat SMP</p>
                        <br><br><br><br>
                        <p><strong><u>Pejabat Pembuat Komitmen</u></strong><br>NIP. 19780101 200501 1 001</p>
                    </div>
                    <div class='ttd-right'>
                        <p>PIHAK KEDUA<br>Kepala {$sekolahNama}</p>
                        <br><br><br><br>
                        <p><strong><u>{$kepsek}</u></strong><br>NIP. {$nip}</p>
                    </div>
                    <div class='clear'></div>
                </div>
                </body></html>";

            // Lampiran V: Contoh Pakta Integritas
            case 'pakta_integritas':
                return "<html><head><title>Pakta Integritas - {$sekolahNama}</title>{$style}</head><body>{$printBar}
                <div class='header-kop'>
                    <h4>KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</h4>
                    <h3>{$sekolahNama}</h3>
                    <p>{$alamat}, {$kab}, {$prov}</p>
                </div>
                <div class='doc-title'>PAKTA INTEGRITAS<br><span style='font-size:10pt; font-weight:normal;'>PROGRAM BANTUAN PERALATAN PEMBELAJARAN TIK SMP TAHUN {$tahun}</span></div>
                <div class='content'>
                    <p>Saya yang bertanda tangan di bawah ini:</p>
                    <table class='field-table'>
                        <tr><td width='180'>Nama Kepala Sekolah</td><td width='15'>:</td><td><strong>{$kepsek}</strong></td></tr>
                        <tr><td>NIP</td><td>:</td><td>{$nip}</td></tr>
                        <tr><td>Jabatan</td><td>:</td><td>Kepala {$sekolahNama}</td></tr>
                        <tr><td>NPSN</td><td>:</td><td>{$npsn}</td></tr>
                        <tr><td>Alamat Sekolah</td><td>:</td><td>{$alamat}, {$kab}</td></tr>
                    </table>
                    <p>Dalam rangka pelaksanaan kegiatan Bantuan Pemerintah Peralatan Pembelajaran TIK SMP Tahun {$tahun}, dengan ini menyatakan komitmen penuh bahwa saya:</p>
                    <ol style='padding-left: 20px; line-height: 1.6;'>
                        <li>Tidak akan melakukan praktik Korupsi, Kolusi, dan Nepotisme (KKN) dalam seluruh tahapan pengadaan peralatan TIK.</li>
                        <li>Akan menggunakan dana bantuan sebesar Rp 69.364.000,00 secara transparan, akuntabel, dan sesuai dengan Panduan Pelaksanaan yang berlaku.</li>
                        <li>Menjamin tidak menerima atau memberikan imbalan (gratifikasi) kepada pihak mana pun yang berkaitan dengan penetapan bantuan.</li>
                        <li>Bersedia dikenakan sanksi moral, sanksi administratif, serta dituntut sesuai hukum yang berlaku apabila terbukti melakukan pelanggaran atas Pakta Integritas ini.</li>
                    </ol>
                </div>
                <div class='ttd-container'>
                    <div class='ttd-right'>
                        <p>{$kab}, {$tgl}<br>Kepala {$sekolahNama},</p>
                        <br><br><br><br>
                        <p><strong><u>{$kepsek}</u></strong><br>NIP. {$nip}</p>
                    </div>
                    <div class='clear'></div>
                </div>
                </body></html>";

            // Lampiran IV: Format Rencana Anggaran Biaya (RAB)
            case 'rab':
                $merek = e($rab->merek_tipe_laptop ?? 'Chromebook / Laptop Standar TIK');
                $specs = e($rab->spesifikasi_ringkas ?? 'Min 4 Core/8 Thread, Layar 13-14 inch, RAM 8GB, SSD 256GB, OS GUI Legal');
                $qty = (int)($rab->jumlah_unit ?? 8);
                $hargaSatuan = $rab->harga_satuan ?? 8625000;
                $totalHarga = $rab->total_harga ?? ($qty * $hargaSatuan);
                $sisa = 69364000 - $totalHarga;

                $hargaFmt = number_format($hargaSatuan, 0, ',', '.');
                $totalFmt = number_format($totalHarga, 0, ',', '.');
                $sisaFmt = number_format($sisa, 0, ',', '.');

                return "<html><head><title>RAB - {$sekolahNama}</title>{$style}</head><body>{$printBar}
                <div class='header-kop'>
                    <h4>KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</h4>
                    <h3>{$sekolahNama}</h3>
                    <p>{$alamat}, {$kab}, {$prov}</p>
                </div>
                <div class='doc-title'>RENCANA ANGGARAN BIAYA (RAB)<br><span style='font-size:10pt; font-weight:normal;'>PENGADAAN PERALATAN PEMBELAJARAN TIK TAHUN {$tahun}</span></div>
                <div class='content'>
                    <table class='field-table'>
                        <tr><td width='160'>Nama Sekolah</td><td width='15'>:</td><td><strong>{$sekolahNama}</strong></td></tr>
                        <tr><td>NPSN</td><td>:</td><td>{$npsn}</td></tr>
                        <tr><td>Alokasi Dana Bantuan</td><td>:</td><td>Rp 69.364.000,00</td></tr>
                    </table>
                    <table class='grid-table'>
                        <thead>
                            <tr>
                                <th width='40'>No</th>
                                <th>Rincian Barang / Komponen</th>
                                <th>Spesifikasi Teknis Ringkas</th>
                                <th width='70'>Volume</th>
                                <th width='120'>Harga Satuan (Rp)</th>
                                <th width='130'>Total Biaya (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style='text-align:center;'>1</td>
                                <td><strong>Laptop Bantuan TIK</strong><br><span style='font-size:9.5pt; color:#444;'>Merek/Tipe: {$merek}</span></td>
                                <td>{$specs}</td>
                                <td style='text-align:center;'>{$qty} Unit</td>
                                <td style='text-align:right;'>Rp {$hargaFmt}</td>
                                <td style='text-align:right;'>Rp {$totalFmt}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan='5' style='text-align:right;'>Total Rencana Pengeluaran:</th>
                                <th style='text-align:right;'>Rp {$totalFmt}</th>
                            </tr>
                            <tr>
                                <th colspan='5' style='text-align:right;'>Sisa Dana Bantuan (Wajib Disetor ke Kas Negara):</th>
                                <th style='text-align:right; color:#1e2d5a;'>Rp {$sisaFmt}</th>
                            </tr>
                        </tfoot>
                    </table>
                    <p style='font-size:9.5pt; font-style:italic; margin-top: 10px;'>
                        *Catatan: Sisa alokasi dana bantuan sebesar Rp {$sisaFmt} wajib dikembalikan ke Kas Negara melalui SIMPONI/MPN Billing setelah transaksi pengadaan SIPLah selesai.
                    </p>
                </div>
                <div class='ttd-container'>
                    <div class='ttd-right'>
                        <p>{$kab}, {$tgl}<br>Kepala Sekolah,</p>
                        <br><br><br><br>
                        <p><strong><u>{$kepsek}</u></strong><br>NIP. {$nip}</p>
                    </div>
                    <div class='clear'></div>
                </div>
                </body></html>";

            // Lampiran VII: Contoh Berita Acara Serah Terima (BAST)
            case 'bast':
                $qty = (int)($rab->jumlah_unit ?? 8);
                return "<html><head><title>BAST - {$sekolahNama}</title>{$style}</head><body>{$printBar}
                <div class='header-kop'>
                    <h4>KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</h4>
                    <h3>{$sekolahNama}</h3>
                </div>
                <div class='doc-title'>BERITA ACARA SERAH TERIMA (BAST)<br><span style='font-size:10pt; font-weight:normal;'>PERALATAN PEMBELAJARAN TIK TAHUN {$tahun}</span></div>
                <div class='content'>
                    <p>Pada hari ini tanggal <strong>{$tgl}</strong>, kami yang bertanda tangan di bawah ini:</p>
                    <table class='field-table'>
                        <tr><td width='160'>1. Penyedia / Mitratoko</td><td width='15'>:</td><td>Mitra Penyedia SIPLah Resmi</td></tr>
                        <tr><td colspan='3'>Selanjutnya disebut sebagai <strong>PIHAK KESATU (Penyedia)</strong>.</td></tr>
                    </table>
                    <table class='field-table'>
                        <tr><td width='160'>2. Nama Kepala Sekolah</td><td width='15'>:</td><td><strong>{$kepsek}</strong></td></tr>
                        <tr><td>NIP</td><td>:</td><td>{$nip}</td></tr>
                        <tr><td>Sekolah / NPSN</td><td>:</td><td>{$sekolahNama} (NPSN: {$npsn})</td></tr>
                        <tr><td colspan='3'>Selanjutnya disebut sebagai <strong>PIHAK KEDUA (Penerima)</strong>.</td></tr>
                    </table>
                    <p>PIHAK KESATU menyerahkan kepada PIHAK KEDUA, dan PIHAK KEDUA menyatakan telah menerima peralatan TIK berupa laptop sebanyak <strong>{$qty} unit</strong> dalam keadaan 100% baru, lengkap, dan berfungsi baik sesuai dengan hasil pemeriksaan fisik.</p>
                </div>
                <div class='ttd-container'>
                    <div class='ttd-left'>
                        <p>PIHAK KESATU<br>Penyedia SIPLah</p>
                        <br><br><br><br>
                        <p><strong><u>Pimpinan Penyedia</u></strong></p>
                    </div>
                    <div class='ttd-right'>
                        <p>PIHAK KEDUA<br>Kepala {$sekolahNama}</p>
                        <br><br><br><br>
                        <p><strong><u>{$kepsek}</u></strong><br>NIP. {$nip}</p>
                    </div>
                    <div class='clear'></div>
                </div>
                </body></html>";

            // Default Template Fallback
            default:
                return "<html><head><title>Dokumen Official - {$sekolahNama}</title>{$style}</head><body>{$printBar}
                <div class='header-kop'>
                    <h4>KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</h4>
                    <h3>{$sekolahNama}</h3>
                    <p>{$alamat}, {$kab}, {$prov}</p>
                </div>
                <div class='doc-title'>DOKUMEN RESMI BANTUAN PERALATAN TIK {$tahun}</div>
                <div class='content'>
                    <p>Dokumen resmi untuk <strong>{$sekolahNama}</strong> (NPSN: {$npsn}).</p>
                    <table class='field-table'>
                        <tr><td width='160'>Kepala Sekolah</td><td width='15'>:</td><td>{$kepsek}</td></tr>
                        <tr><td>NIP</td><td>:</td><td>{$nip}</td></tr>
                        <tr><td>Kabupaten / Provinsi</td><td>:</td><td>{$kab}, {$prov}</td></tr>
                    </table>
                </div>
                <div class='ttd-container'>
                    <div class='ttd-right'>
                        <p>{$kab}, {$tgl}<br>Kepala Sekolah,</p>
                        <br><br><br><br>
                        <p><strong><u>{$kepsek}</u></strong><br>NIP. {$nip}</p>
                    </div>
                    <div class='clear'></div>
                </div>
                </body></html>";
        }
    }
}
