<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stok dan riwayat_pergerakan_stok diubah otomatis oleh trigger database
 * (trg_setelah_tambah_item / trg_setelah_hapus_item). Jangan update stok manual saat transaksi.
 */
class ItemPenjualan extends Model
{
    protected $table = 'item_penjualan';
    protected $primaryKey = 'id_item_penjualan';
    public $timestamps = false;

    protected $fillable = [
        'id_nota', 'id_barang', 'jumlah', 'harga_jual_saat_itu', 'hpp_saat_itu', 'subtotal',
    ];

    protected $casts = [
        'jumlah'              => 'decimal:2',
        'harga_jual_saat_itu' => 'decimal:2',
        'hpp_saat_itu'        => 'decimal:2',
        'subtotal'            => 'decimal:2',
    ];

    public function nota(): BelongsTo
    {
        return $this->belongsTo(NotaPenjualan::class, 'id_nota', 'id_nota');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(KatalogBarang::class, 'id_barang', 'id_barang');
    }
}
