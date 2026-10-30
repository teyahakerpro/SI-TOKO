<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBarang extends Model
{
    protected $table = 'kategori_barang';
    protected $primaryKey = 'id_kategori';
    public $timestamps = false;

    protected $fillable = ['nama'];

    public function produk(): HasMany
    {
        return $this->hasMany(KatalogBarang::class, 'id_kategori', 'id_kategori');
    }
}
