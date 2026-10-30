<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataPelanggan extends Model
{
    protected $table = 'data_pelanggan';
    protected $primaryKey = 'id_pelanggan';
    public $timestamps = false;

    protected $fillable = ['nama', 'telepon', 'total_hutang'];

    protected $casts = [
        'total_hutang' => 'decimal:2',
    ];

    public function nota(): HasMany
    {
        return $this->hasMany(NotaPenjualan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
