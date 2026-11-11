<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Informasi extends Model
{
    use HasFactory;

    protected $table = 'informasi';

    protected $fillable = [
        'judul', 'isi', 'gambar', 'kategori',
        'author_id', 'tanggal', 'status_publikasi',
        'scope_wilayah', 'target_type', 'target_role', 'rw_id', 'rt_id',
    ];

    /**
     * Mutator/Accessor kompatibilitas:
     * Menyediakan alias $informasi->konten <-> $informasi->isi
     */
    public function getKontenAttribute(): ?string
    {
        return $this->attributes['isi'] ?? null;
    }

    public function setKontenAttribute(?string $value): void
    {
        $this->attributes['isi'] = $value;
    }

    /**
     * Menyediakan alias $informasi->is_published <-> $informasi->status_publikasi
     */
    public function getIsPublishedAttribute(): bool
    {
        return (bool) ($this->attributes['status_publikasi'] ?? true);
    }

    public function setIsPublishedAttribute($value): void
    {
        $this->attributes['status_publikasi'] = (bool) $value;
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Alias untuk relasi penulis
     */
    public function penulis()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function rw()
    {
        return $this->belongsTo(Rw::class);
    }

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public function broadcasts()
    {
        return $this->hasMany(WhatsappBroadcast::class);
    }

    public function latestBroadcast()
    {
        return $this->hasOne(WhatsappBroadcast::class)->latestOfMany();
    }
}
