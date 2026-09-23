<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarisLaptop extends Model
{
    use HasFactory;

    protected $fillable = [
        'sekolah_id',
        'nomor_unit',
        'nomor_seri',
        'merek_tipe',
        'foto_stiker_path',
        'qr_code_key',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }
}
