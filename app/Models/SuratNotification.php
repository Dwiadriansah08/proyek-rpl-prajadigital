<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratNotification extends Model
{
    use HasFactory;

    protected $table = 'surat_notifications';

    protected $fillable = [
        'pengajuan_surat_id',
        'event_status',
        'recipient_role',
        'recipient_user_id',
        'recipient_warga_id',
        'recipient_phone',
        'message',
        'status',
        'response_payload',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function pengajuanSurat()
    {
        return $this->belongsTo(PengajuanSurat::class, 'pengajuan_surat_id');
    }

    public function recipientUser()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function recipientWarga()
    {
        return $this->belongsTo(Warga::class, 'recipient_warga_id');
    }
}
