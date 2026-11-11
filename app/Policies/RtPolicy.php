<?php

namespace App\Policies;

use App\Models\Rt;
use App\Models\User;

/**
 * Policy untuk otorisasi akses resource RT.
 *
 * - super_admin: akses penuh ke semua RT
 * - rw: bisa melihat/mengelola RT di bawah RW-nya
 * - rt: hanya bisa melihat/mengedit RT miliknya sendiri
 */
class RtPolicy
{
    /**
     * Tentukan apakah user dapat melihat daftar RT.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin_desa', 'rw', 'rt']);
    }

    /**
     * Tentukan apakah user dapat melihat detail RT tertentu.
     */
    public function view(User $user, Rt $rt): bool
    {
        if ($user->hasRole(['super_admin', 'admin_desa'])) {
            return true;
        }
        if ($user->hasRole('rw') && $rt->rw_id == $user->rw_id) {
            return true;
        }
        if ($user->hasRole('rt') && $user->rt_id == $rt->id) {
            return true;
        }

        return false;
    }

    /**
     * Tentukan apakah user dapat membuat RT baru.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin_desa', 'rw']);
    }

    /**
     * Tentukan apakah user dapat mengubah data RT.
     */
    public function update(User $user, Rt $rt): bool
    {
        return $this->view($user, $rt);
    }

    /**
     * Tentukan apakah user dapat menghapus RT.
     */
    public function delete(User $user, Rt $rt): bool
    {
        if ($user->hasRole(['super_admin', 'admin_desa'])) {
            return true;
        }
        if ($user->hasRole('rw') && $rt->rw_id == $user->rw_id) {
            return true;
        }

        return false;
    }

    /**
     * Tentukan apakah user dapat me-restore RT.
     */
    public function restore(User $user, Rt $rt): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Tentukan apakah user dapat menghapus RT secara permanen.
     */
    public function forceDelete(User $user, Rt $rt): bool
    {
        return $user->hasRole('super_admin');
    }
}
