<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Karyawan extends Authenticatable
{
    protected $table = 'karyawan';
    protected $primaryKey = 'id_karyawan';
    public $timestamps = false;

    protected $fillable = ['nama', 'username', 'password_hash', 'peran'];

    protected $hidden = ['password_hash', 'remember_token'];

    /** Kolom password untuk login: hash bcrypt dari tabel karyawan. */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function isAdmin(): bool
    {
        return $this->peran === 'admin';
    }

    /** $karyawan->label_peran => 'Owner' | 'Kasir' */
    public function getLabelPeranAttribute(): string
    {
        return match ($this->peran) {
            'admin' => 'Owner',
            'kasir' => 'Kasir',
            default => 'Pengguna',
        };
    }

    public function nota(): HasMany
    {
        return $this->hasMany(NotaPenjualan::class, 'id_karyawan', 'id_karyawan');
    }
}
