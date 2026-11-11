<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rw extends Model
{
    use HasFactory;

    protected $table = 'rw';

    protected $fillable = [
        'desa_id', 'nomor_rw', 'ketua_rw', 'kontak',
        'alamat', 'status',
    ];

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }

    public function rts()
    {
        return $this->hasMany(Rt::class);
    }

    public function rt()
    {
        return $this->hasMany(Rt::class);
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
