<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * metode_bayar : tunai | transfer | hutang
 * status_bayar : lunas | belum_lunas
 */
class NotaPenjualan extends Model
{
    protected $table = 'nota_penjualan';
    protected $primaryKey = 'id_nota';
    public $timestamps = false;

    protected $fillable = [
        'nomor_nota', 'id_karyawan', 'id_pelanggan', 'tanggal', 'total_belanja',
        'diskon', 'total_akhir', 'metode_bayar', 'status_bayar',
    ];

    protected $casts = [
        'tanggal'       => 'datetime',
        'total_belanja' => 'decimal:2',
        'diskon'        => 'decimal:2',
        'total_akhir'   => 'decimal:2',
    ];

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(DataPelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemPenjualan::class, 'id_nota', 'id_nota');
    }
}
