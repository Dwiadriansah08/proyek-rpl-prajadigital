<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keluarga extends Model
{
    use HasFactory;

    protected $table = 'keluarga';

    protected $fillable = [
        'nomor_kk', 'kepala_keluarga', 'alamat', 'rw_id', 'rt_id',
    ];

    public function rw()
    {
        return $this->belongsTo(Rw::class);
    }

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public function members()
    {
        return $this->belongsToMany(Warga::class, 'keluarga_members');
    }
}
