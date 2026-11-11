<?php

namespace App\Policies;

use App\Models\Keluarga;
use App\Models\User;

/**
 * Policy untuk otorisasi akses resource Keluarga (KK).
 *
 * - super_admin: akses penuh ke semua data keluarga
 * - rw: bisa mengelola keluarga di wilayah RW-nya
 * - rt: bisa mengelola keluarga di wilayah RT-nya
 */
class KeluargaPolicy
{
    /**
     * Tentukan apakah user dapat melihat daftar keluarga.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'rw', 'rt']);
    }

    /**
     * Tentukan apakah user dapat melihat detail keluarga tertentu.
     */
    public function view(User $user, Keluarga $keluarga): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        if ($user->hasRole('rw') && $keluarga->rw_id == $user->rw_id) {
            return true;
        }
        if ($user->hasRole('rt') && $keluarga->rt_id == $user->rt_id) {
            return true;
        }

        return false;
    }

    /**
     * Tentukan apakah user dapat membuat keluarga baru.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'rw', 'rt']);
    }

    /**
     * Tentukan apakah user dapat mengubah data keluarga.
     */
    public function update(User $user, Keluarga $keluarga): bool
    {
        return $this->view($user, $keluarga);
    }

    /**
     * Tentukan apakah user dapat menghapus keluarga.
     */
    public function delete(User $user, Keluarga $keluarga): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        if ($user->hasRole('rw') && $keluarga->rw_id == $user->rw_id) {
            return true;
        }
        if ($user->hasRole('rt') && $keluarga->rt_id == $user->rt_id) {
            return true;
        }

        return false;
    }

    /**
     * Tentukan apakah user dapat me-restore keluarga.
     */
    public function restore(User $user, Keluarga $keluarga): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Tentukan apakah user dapat menghapus keluarga secara permanen.
     */
    public function forceDelete(User $user, Keluarga $keluarga): bool
    {
        return $user->hasRole('super_admin');
    }
}
