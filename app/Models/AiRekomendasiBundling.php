<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRekomendasiBundling extends Model
{
    protected $table = 'ai_rekomendasi_bundling';
    protected $primaryKey = 'id_rekomendasi';

    /** Tabel hanya punya updated_at, tidak ada created_at. */
    public const CREATED_AT = null;

    protected $fillable = ['id_barang_utama', 'id_barang_pasangan', 'skor_asosiasi', 'status_aktif'];

    protected $casts = [
        'skor_asosiasi' => 'float',
        'status_aktif'  => 'boolean',
        'updated_at'    => 'datetime',
    ];

    public function utama(): BelongsTo
    {
        return $this->belongsTo(KatalogBarang::class, 'id_barang_utama', 'id_barang');
    }

    public function pasangan(): BelongsTo
    {
        return $this->belongsTo(KatalogBarang::class, 'id_barang_pasangan', 'id_barang');
    }
}
