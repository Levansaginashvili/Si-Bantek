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
        'nama_kepsek',
        'nip_kepsek',
        'nama_bendahara',
        'nip_bendahara',
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
        $requiredTypes = [
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

        $approvedCount = $this->dokumens()
            ->whereIn('jenis_dokumen', $requiredTypes)
            ->where('status', 'Disetujui')
            ->count();

        $this->status_dokumen = ($approvedCount >= count($requiredTypes)) ? 'Lengkap' : 'Belum Lengkap';
        $this->save();
    }
}
