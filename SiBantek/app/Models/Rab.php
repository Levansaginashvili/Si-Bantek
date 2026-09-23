<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rab extends Model
{
    use HasFactory;

    protected $fillable = [
        'sekolah_id',
        'merek_tipe_laptop',
        'spesifikasi_ringkas',
        'jumlah_unit',
        'harga_satuan',
        'total_harga',
        'status',
        'catatan_revisi',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }
}
