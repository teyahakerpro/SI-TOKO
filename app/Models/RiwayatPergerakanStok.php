<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * jenis_pergerakan : masuk | keluar | penyesuaian
 * id_referensi     : id_nota (jika keluar karena penjualan)
 */
class RiwayatPergerakanStok extends Model
{
    protected $table = 'riwayat_pergerakan_stok';
    protected $primaryKey = 'id_riwayat';
    public $timestamps = false;

    protected $fillable = [
        'id_barang', 'jenis_pergerakan', 'id_referensi', 'jumlah_perubahan', 'sisa_stok', 'tanggal',
    ];

    protected $casts = [
        'jumlah_perubahan' => 'decimal:2',
        'sisa_stok'        => 'decimal:2',
        'tanggal'          => 'datetime',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(KatalogBarang::class, 'id_barang', 'id_barang');
    }
}
