<?php

namespace App\Http\Traits;

use App\Models\Rt;
use App\Models\Rw;
use Illuminate\Database\Eloquent\Collection;

/**
 * Trait untuk menyediakan data dropdown RW dan RT
 * yang sudah difilter berdasarkan wilayah (role) pengguna.
 *
 * Mengeliminasi duplikasi pola filter RW/RT yang berulang
 * di banyak controller (Warga, Keluarga, dll.).
 */
trait FiltersByWilayah
{
    /**
     * Ambil daftar RW yang dapat diakses berdasarkan role user.
     *
     * - super_admin: semua RW
     * - rw: hanya RW miliknya
     * - rt: hanya RW induk dari RT-nya
     *
     * @return Collection<int, Rw>
     */
    protected function getAccessibleRws(): Collection
    {
        $user = auth()->user();

        if ($user->hasRole('rw') || $user->hasRole('rt')) {
            return Rw::where('id', $user->rw_id)->get();
        }

        return Rw::all();
    }

    /**
     * Ambil daftar RT yang dapat diakses berdasarkan role user.
     *
     * - super_admin: semua RT
     * - rw: semua RT di bawah RW-nya
     * - rt: hanya RT miliknya
     *
     * @return Collection<int, Rt>
     */
    protected function getAccessibleRts(): Collection
    {
        $user = auth()->user();

        if ($user->hasRole('rw')) {
            return Rt::where('rw_id', $user->rw_id)->get();
        }

        if ($user->hasRole('rt')) {
            return Rt::where('id', $user->rt_id)->get();
        }

        return Rt::all();
    }
}
