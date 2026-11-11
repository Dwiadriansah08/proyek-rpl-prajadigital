<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappRecipient extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_recipients';

    protected $fillable = [
        'broadcast_id',
        'informasi_id',
        'warga_id',
        'user_id',
        'recipient_name',
        'recipient_phone',
        'status',
        'response_payload',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function broadcast()
    {
        return $this->belongsTo(WhatsappBroadcast::class, 'broadcast_id');
    }

    public function informasi()
    {
        return $this->belongsTo(Informasi::class);
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
