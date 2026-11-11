<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desa extends Model
{
    use HasFactory;

    protected $table = 'desa';

    protected $fillable = [
        'nama', 'kecamatan', 'kabupaten_kota', 'provinsi',
        'alamat', 'kode_pos', 'logo', 'kepala_desa',
        'kontak', 'deskripsi',
    ];

    public function rws()
    {
        return $this->hasMany(Rw::class);
    }
}
