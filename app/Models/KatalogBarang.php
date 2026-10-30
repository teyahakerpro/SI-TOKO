<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KatalogBarang extends Model
{
    /** Stok di bawah atau sama dengan nilai ini dianggap "menipis" (sama dengan view v_stok_sedikit). */
    public const AMBANG_STOK_MENIPIS = 10;

    protected $table = 'katalog_barang';
    protected $primaryKey = 'id_barang';
    public $timestamps = false;

    protected $fillable = [
        'id_kategori', 'nama', 'barcode', 'gambar', 'satuan', 'harga_beli', 'harga_jual',
        'stok_sekarang', 'stok_minimum_ai', 'ai_tags',
    ];

    protected $casts = [
        'harga_beli'      => 'decimal:2',
        'harga_jual'      => 'decimal:2',
        'stok_sekarang'   => 'integer',
        'stok_minimum_ai' => 'integer',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBarang::class, 'id_kategori', 'id_kategori');
    }

    public function itemPenjualan(): HasMany
    {
        return $this->hasMany(ItemPenjualan::class, 'id_barang', 'id_barang');
    }

    public function riwayatStok(): HasMany
    {
        return $this->hasMany(RiwayatPergerakanStok::class, 'id_barang', 'id_barang');
    }

    /** URL gambar produk atau null jika belum ada. */
    public function getGambarUrlAttribute(): ?string
    {
        if ($this->gambar && file_exists(public_path('storage/' . $this->gambar))) {
            return asset('storage/' . $this->gambar);
        }

        return null;
    }

    /** $produk->status_stok => 'habis' | 'menipis' | 'aman' */
    public function getStatusStokAttribute(): string
    {
        return match (true) {
            $this->stok_sekarang <= 0                         => 'habis',
            $this->stok_sekarang <= self::AMBANG_STOK_MENIPIS => 'menipis',
            default                                           => 'aman',
        };
    }

    /** $produk->harga_format => "Rp 65.000" (harga jual) */
    public function getHargaFormatAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->harga_jual, 0, ',', '.');
    }

    /** $produk->harga_beli_format => "Rp 60.000" */
    public function getHargaBeliFormatAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->harga_beli, 0, ',', '.');
    }

    /** $produk->laba_per_unit => selisih harga jual dan harga beli */
    public function getLabaPerUnitAttribute(): float
    {
        return (float) $this->harga_jual - (float) $this->harga_beli;
    }

    /** $produk->laba_format => "Rp 5.000" atau "-Rp 500" */
    public function getLabaFormatAttribute(): string
    {
        $laba = $this->laba_per_unit;

        return ($laba < 0 ? '-' : '') . 'Rp ' . number_format(abs($laba), 0, ',', '.');
    }
}
