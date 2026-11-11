<?php

namespace App\Policies;

use App\Models\PengajuanSurat;
use App\Models\User;

class PengajuanSuratPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Filtered in controller query
    }

    public function view(User $user, PengajuanSurat $pengajuanSurat): bool
    {
        if ($user->hasRole(['super_admin', 'admin_desa'])) {
            return true;
        }

        $warga = $pengajuanSurat->warga;
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
        return $user->hasRole('warga');
    }

    public function update(User $user, PengajuanSurat $pengajuanSurat): bool
    {
        return $this->view($user, $pengajuanSurat);
    }

    public function process(User $user, PengajuanSurat $pengajuanSurat): bool
    {
        if ($user->hasRole(['super_admin', 'admin_desa'])) {
            return true;
        }

        $warga = $pengajuanSurat->warga;
        if ($user->hasRole('rw') && $warga->rw_id == $user->rw_id) {
            return true;
        }
        if ($user->hasRole('rt') && $warga->rt_id == $user->rt_id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, PengajuanSurat $pengajuanSurat): bool
    {
        if ($user->hasRole(['super_admin', 'admin_desa'])) {
            return true;
        }

        $warga = $pengajuanSurat->warga;
        if ($user->hasRole('warga') && $warga->nik == $user->warga?->nik && $pengajuanSurat->status === 'DRAFT') {
            return true;
        }

        return false;
    }
}
