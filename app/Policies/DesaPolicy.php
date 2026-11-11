<?php

namespace App\Policies;

use App\Models\Desa;
use App\Models\User;

/**
 * Policy untuk otorisasi akses resource Desa.
 *
 * Hanya super_admin yang dapat mengelola data Desa.
 */
class DesaPolicy
{
    /**
     * Tentukan apakah user dapat melihat daftar desa.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Tentukan apakah user dapat melihat detail desa.
     */
    public function view(User $user, Desa $desa): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Tentukan apakah user dapat membuat desa baru.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Tentukan apakah user dapat mengubah data desa.
     */
    public function update(User $user, Desa $desa): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Tentukan apakah user dapat menghapus desa.
     */
    public function delete(User $user, Desa $desa): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Tentukan apakah user dapat me-restore desa.
     */
    public function restore(User $user, Desa $desa): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Tentukan apakah user dapat menghapus desa secara permanen.
     */
    public function forceDelete(User $user, Desa $desa): bool
    {
        return $user->hasRole('super_admin');
    }
}
