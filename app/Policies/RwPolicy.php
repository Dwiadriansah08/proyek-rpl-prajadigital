<?php

namespace App\Policies;

use App\Models\Rw;
use App\Models\User;

/**
 * Policy untuk otorisasi akses resource RW.
 *
 * - super_admin: akses penuh ke semua RW
 * - rw: hanya bisa melihat/mengedit RW miliknya sendiri
 */
class RwPolicy
{
    /**
     * Tentukan apakah user dapat melihat daftar RW.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin_desa', 'rw']);
    }

    /**
     * Tentukan apakah user dapat melihat detail RW tertentu.
     */
    public function view(User $user, Rw $rw): bool
    {
        if ($user->hasRole(['super_admin', 'admin_desa'])) {
            return true;
        }
        if ($user->hasRole('rw') && $user->rw_id == $rw->id) {
            return true;
        }

        return false;
    }

    /**
     * Tentukan apakah user dapat membuat RW baru.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin_desa']);
    }

    /**
     * Tentukan apakah user dapat mengubah data RW.
     */
    public function update(User $user, Rw $rw): bool
    {
        if ($user->hasRole(['super_admin', 'admin_desa'])) {
            return true;
        }
        if ($user->hasRole('rw') && $user->rw_id == $rw->id) {
            return true;
        }

        return false;
    }

    /**
     * Tentukan apakah user dapat menghapus RW.
     */
    public function delete(User $user, Rw $rw): bool
    {
        return $user->hasRole(['super_admin', 'admin_desa']);
    }

    /**
     * Tentukan apakah user dapat me-restore RW.
     */
    public function restore(User $user, Rw $rw): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Tentukan apakah user dapat menghapus RW secara permanen.
     */
    public function forceDelete(User $user, Rw $rw): bool
    {
        return $user->hasRole('super_admin');
    }
}
