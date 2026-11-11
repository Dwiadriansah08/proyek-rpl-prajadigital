<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappBroadcast extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_broadcasts';

    protected $fillable = [
        'informasi_id',
        'sender_id',
        'target_description',
        'total_recipients',
        'success_count',
        'failed_count',
        'status',
        'error_summary',
    ];

    public function informasi()
    {
        return $this->belongsTo(Informasi::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients()
    {
        return $this->hasMany(WhatsappRecipient::class, 'broadcast_id');
    }
}
