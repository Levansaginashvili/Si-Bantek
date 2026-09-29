<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dokumen extends Model
{
    use HasFactory;

    protected $fillable = [
        'sekolah_id',
        'jenis_dokumen',
        'file_path',
        'meta',
        'status',
        'catatan_revisi',
        'verified_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'verified_at' => 'datetime',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }
}
