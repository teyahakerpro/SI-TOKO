<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengeluaranToko extends Model
{
    protected $table = 'pengeluaran_toko';
    protected $primaryKey = 'id_pengeluaran';
    public $timestamps = false;

    protected $fillable = ['id_karyawan', 'nama_pengeluaran', 'jumlah', 'tanggal'];

    protected $casts = [
        'jumlah'  => 'decimal:2',
        'tanggal' => 'datetime',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }
}
