<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warga;

class WargaPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Filtered in controller query
    }

    public function view(User $user, Warga $warga): bool
    {
        if ($user->hasRole(['super_admin', 'admin_desa'])) {
            return true;
        }
        if ($user->hasRole('rw') && $warga->rw_id == $user->rw_id) {
            return true;
        }
        if ($user->hasRole('rt') && $warga->rt_id == $user->rt_id) {
            return true;
        }
        if ($user->hasRole('warga') && $warga->nik == $user->warga?->nik) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin_desa', 'rw', 'rt']);
    }

    public function update(User $user, Warga $warga): bool
    {
        return $this->view($user, $warga);
    }

    public function delete(User $user, Warga $warga): bool
    {
        if ($user->hasRole(['super_admin', 'admin_desa'])) {
            return true;
        }
        if ($user->hasRole('rw') && $warga->rw_id == $user->rw_id) {
            return true;
        }
        if ($user->hasRole('rt') && $warga->rt_id == $user->rt_id) {
            return true;
        }

        return false;
    }
}
