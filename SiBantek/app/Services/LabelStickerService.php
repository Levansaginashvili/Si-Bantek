<?php

namespace App\Services;

use App\Models\Sekolah;

class LabelStickerService
{
    public function generateStickersHtml(Sekolah $sekolah, int $totalUnit = 8): string
    {
        $sekolahNama = e($sekolah->nama_sekolah);
        $npsn = e($sekolah->npsn);
        $kab = e($sekolah->kabupaten);
        $prov = e($sekolah->provinsi);

        $stickers = '';
        for ($i = 1; $i <= $totalUnit; $i++) {
            $unitCode = sprintf("LAP-2026-%03d", $i);
            $qrData = urlencode("ASSET|{$npsn}|{$unitCode}");
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={$qrData}";

            $stickers .= "
            <div class='sticker-box'>
                <div class='sticker-header'>
                    <strong>BANTUAN PERALATAN TIK SMP 2026</strong><br>
                    <span>DIREKTORAT SMP - KEMENDIKDASMEN</span>
                </div>
                <div class='sticker-body'>
                    <div class='qr-code'>
                        <img src='{$qrUrl}' alt='QR Code'>
                    </div>
                    <div class='sticker-info'>
                        <p class='school-name'>{$sekolahNama}</p>
                        <p><strong>NPSN:</strong> {$npsn}</p>
                        <p><strong>Kode Aset:</strong> <span class='badge-code'>{$unitCode}</span></p>
                        <p><strong>Item:</strong> Laptop Pembelajaran</p>
                        <p><strong>Tahun:</strong> 2026</p>
                    </div>
                </div>
                <div class='sticker-footer'>
                    ASET NEGARA / SEKOLAH - DILINDUNGI ATURAN
                </div>
            </div>";
        }

        return "<html>
        <head>
            <title>Label Stiker Aset Laptop - {$sekolahNama}</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f8fafc; margin: 20px; color: #0f172a; }
                .grid-container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; max-width: 900px; margin: 0 auto; }
                .sticker-box { background: #ffffff; border: 2px solid #1e3a8a; border-radius: 8px; padding: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: relative; }
                .sticker-header { background: #1e3a8a; color: #ffffff; text-align: center; padding: 6px; border-radius: 4px; font-size: 10pt; line-height: 1.2; }
                .sticker-header span { font-size: 8pt; opacity: 0.9; }
                .sticker-body { display: flex; align-items: center; margin-top: 10px; gap: 12px; }
                .qr-code img { width: 80px; height: 80px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px; }
                .sticker-info { font-size: 9pt; line-height: 1.4; flex: 1; }
                .sticker-info p { margin: 2px 0; }
                .school-name { font-weight: bold; color: #1e3a8a; font-size: 10pt; text-transform: uppercase; }
                .badge-code { background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-family: monospace; }
                .sticker-footer { text-align: center; font-size: 7.5pt; font-weight: bold; color: #64748b; border-top: 1px dashed #cbd5e1; margin-top: 10px; padding-top: 4px; letter-spacing: 0.5px; }
                @media print {
                    body { background: #fff; margin: 0; }
                    .no-print { display: none; }
                    .grid-container { gap: 10px; }
                    .sticker-box { page-break-inside: avoid; }
                }
            </style>
        </head>
        <body>
            <div class='no-print' style='max-width:900px; margin: 0 auto 20px auto; text-align: right;'>
                <button onclick='window.print()' style='background:#1e3a8a; color:#fff; padding:10px 20px; border:none; border-radius:4px; cursor:pointer; font-weight:bold;'>Cetak Stiker Label</button>
            </div>
            <div class='grid-container'>
                {$stickers}
            </div>
        </body>
        </html>";
    }
}
