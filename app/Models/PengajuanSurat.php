<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanSurat extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_surat';

    protected $fillable = [
        'warga_id', 'template_id', 'nomor_surat',
        'data_tambahan', 'status', 'catatan',
        'approved_by', 'approved_at', 'approved_by_admin_desa_at', 'file_pdf',
    ];

    protected $casts = [
        'data_tambahan' => 'array',
        'approved_at' => 'datetime',
        'approved_by_admin_desa_at' => 'datetime',
    ];

    public function warga()
    {
        return $this->belongsTo(Warga::class);
    }

    public function template()
    {
        return $this->belongsTo(SuratTemplate::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function notifications()
    {
        return $this->hasMany(SuratNotification::class, 'pengajuan_surat_id');
    }
}
