<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tabel ini punya created_at & updated_at, jadi timestamps dibiarkan aktif. */
class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';
    protected $primaryKey = 'id_pengeluaran';

    protected $fillable = ['tanggal', 'kategori', 'keterangan', 'nominal', 'id_karyawan'];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'integer',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }
}
