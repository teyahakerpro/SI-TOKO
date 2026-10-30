<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatLog extends Model
{
    protected $table = 'ai_chat_logs';
    protected $primaryKey = 'id_chat';

    /** Tabel hanya punya created_at, tidak ada updated_at. */
    public const UPDATED_AT = null;

    protected $fillable = ['id_karyawan', 'prompt_user', 'ai_response'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }
}
