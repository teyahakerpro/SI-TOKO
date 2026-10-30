<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiPrediksiStok extends Model
{
    protected $table = 'ai_prediksi_stok';
    protected $primaryKey = 'id_prediksi';

    /** Tabel hanya punya created_at, tidak ada updated_at. */
    public const UPDATED_AT = null;

    protected $fillable = [
        'id_barang', 'tanggal_prediksi', 'estimasi_stok_habis',
        'rekomendasi_jumlah_restok', 'confidence_score',
    ];

    protected $casts = [
        'tanggal_prediksi'           => 'date',
        'estimasi_stok_habis'        => 'date',
        'rekomendasi_jumlah_restok'  => 'integer',
        'confidence_score'           => 'float',
        'created_at'                 => 'datetime',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(KatalogBarang::class, 'id_barang', 'id_barang');
    }
}
