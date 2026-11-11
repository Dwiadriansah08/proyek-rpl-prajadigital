<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warga extends Model
{
    use HasFactory;

    protected $table = 'warga';

    protected $fillable = [
        'nik', 'nomor_kk', 'nama_lengkap', 'tempat_lahir',
        'tanggal_lahir', 'jenis_kelamin', 'agama', 'status_perkawinan',
        'pendidikan', 'pekerjaan', 'nomor_telepon', 'email',
        'alamat', 'rw_id', 'rt_id', 'status_dalam_keluarga',
        'status_kependudukan', 'foto', 'status_aktif',
    ];

    public function rw()
    {
        return $this->belongsTo(Rw::class);
    }

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public function keluargas()
    {
        return $this->belongsToMany(Keluarga::class, 'keluarga_members');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
