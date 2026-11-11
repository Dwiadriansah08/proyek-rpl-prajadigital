<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rt extends Model
{
    use HasFactory;

    protected $table = 'rt';

    protected $fillable = [
        'rw_id', 'nomor_rt', 'ketua_rt', 'kontak',
        'alamat', 'status',
    ];

    public function rw()
    {
        return $this->belongsTo(Rw::class);
    }

    public function wargas()
    {
        return $this->hasMany(Warga::class);
    }

    public function warga()
    {
        return $this->hasMany(Warga::class);
    }
}
