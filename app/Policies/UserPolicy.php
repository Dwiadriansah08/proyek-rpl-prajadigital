<?php

namespace App\Policies;

use App\Models\User;

/**
 * Policy untuk otorisasi akses manajemen User.
 *
 * Hanya super_admin yang dapat mengelola data user.
 */
class UserPolicy
{
    /**
     * Tentukan apakah user dapat melihat daftar user.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin_desa']);
    }

    /**
     * Tentukan apakah user dapat melihat detail user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasRole(['super_admin', 'admin_desa']);
    }

    /**
     * Tentukan apakah user dapat membuat user baru.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin_desa']);
    }

    /**
     * Tentukan apakah user dapat mengubah data user.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('admin_desa')) {
            // Admin desa tidak dapat mengubah user super_admin
            return ! $model->hasRole('super_admin');
        }

        return false;
    }

    /**
     * Tentukan apakah user dapat menghapus user.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('admin_desa')) {
            return ! $model->hasRole('super_admin');
        }

        return false;
    }

    /**
     * Tentukan apakah user dapat me-restore user.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Tentukan apakah user dapat menghapus user secara permanen.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasRole('super_admin');
    }
}
