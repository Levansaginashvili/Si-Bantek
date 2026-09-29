<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sekolah extends Model
{
    use HasFactory;

    protected $fillable = [
        'npsn',
        'nama_sekolah',
        'provinsi',
        'kabupaten',
        'alamat',
        'rt',
        'rw',
        'nomor_bangunan',
        'desa_kelurahan',
        'kecamatan',
        'kode_pos',
        'nama_kepsek',
        'nip_kepsek',
        'nama_bendahara',
        'nip_bendahara',
        'nama_bank',
        'nomor_rekening',
        'atas_nama_rekening',
        'no_telepon',
        'email_sekolah',
        'nama_ketua_komite',
        'status_dana',
        'status_dokumen',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function dokumens(): HasMany
    {
        return $this->hasMany(Dokumen::class);
    }

    public function rab(): HasOne
    {
        return $this->hasOne(Rab::class);
    }

    public function inventarisLaptops(): HasMany
    {
        return $this->hasMany(InventarisLaptop::class);
    }

    public function updateStatusDokumen(): void
    {
        // Tabel 5.1 Panlak — Dokumen Pendukung Laporan
        $requiredTypes = [
            'pks',
            'pakta_integritas',
            'sptjm',
            'rab',
            'laporan_awal',
            'perbandingan_siplah',
            'surat_pemesanan_siplah',
            'invoice_siplah',
            'bast',
            'buku_inventaris',
            'dokumentasi_pemanfaatan',
            'laporan_akhir',
            'pengantar_lpj',
            'lpj',
        ];

        // Sisa dana logic: Jika total belanja RAB < 69.364.000, bukti_setor_sisa_dana menjadi WAJIB
        $totalBelanja = $this->rab ? (float) $this->rab->total_harga : 0;
        $sisaDana = max(0, 69364000 - $totalBelanja);

        if ($sisaDana > 0 && $this->rab && $this->rab->status === 'Disetujui') {
            $requiredTypes[] = 'bukti_setor_sisa_dana';
        }

        $approvedCount = $this->dokumens()
            ->whereIn('jenis_dokumen', $requiredTypes)
            ->where('status', 'Disetujui')
            ->count();

        $this->status_dokumen = ($approvedCount >= count($requiredTypes)) ? 'Lengkap' : 'Belum Lengkap';
        $this->save();
    }
}
