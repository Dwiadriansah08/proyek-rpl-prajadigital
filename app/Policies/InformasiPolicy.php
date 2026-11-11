<?php

namespace App\Policies;

use App\Models\Informasi;
use App\Models\User;

/**
 * Policy untuk otorisasi akses resource Informasi Publik.
 *
 * - super_admin: akses penuh
 * - rw: bisa membuat/mengedit informasi untuk wilayah RW-nya atau RT di bawahnya
 * - rt: bisa membuat/mengedit informasi untuk wilayah RT-nya
 * - warga: hanya bisa melihat informasi yang dipublikasikan dan sesuai target wilayah/role
 */
class InformasiPolicy
{
    /**
     * Tentukan apakah user dapat melihat daftar informasi.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Tentukan apakah user dapat melihat detail informasi sesuai target & kewenangan.
     */
    public function view(User $user, Informasi $informasi): bool
    {
        // Penulis selalu bisa melihat informasinya
        if ($informasi->author_id === $user->id) {
            return true;
        }

        // Super Admin & Admin Desa memiliki akses observabilitas penuh
        if ($user->hasRole(['super_admin', 'admin_desa'])) {
            return true;
        }

        // Draft/belum dipublikasi hanya boleh dilihat oleh pembuat atau super admin/admin desa
        if (! $informasi->status_publikasi && $informasi->author_id !== $user->id) {
            return false;
        }

        $userRoleName = $user->role?->name ?? 'warga';

        // 1. Cek apakah target pengumuman adalah role tertentu
        if ($informasi->target_type === 'role') {
            if ($informasi->target_role && $userRoleName !== $informasi->target_role) {
                return false;
            }
        }

        // 2. RW check: hanya bisa melihat informasi lingkup Desa atau RW-nya
        if ($user->hasRole('rw')) {
            if ($informasi->target_type === 'desa') {
                return true;
            }
            if ($informasi->rw_id && $informasi->rw_id != $user->rw_id) {
                return false;
            }

            return true;
        }

        // 3. RT check: hanya bisa melihat informasi lingkup Desa, RW induknya, atau RT-nya
        if ($user->hasRole('rt')) {
            if ($informasi->target_type === 'desa') {
                return true;
            }
            if ($informasi->rw_id && $informasi->rw_id != $user->rw_id) {
                return false;
            }
            if ($informasi->rt_id && $informasi->rt_id != $user->rt_id) {
                return false;
            }

            return true;
        }

        // 4. Warga check: periksa kecocokan data wilayah warga
        if ($user->hasRole('warga')) {
            $warga = $user->warga;
            $userRwId = $warga?->rw_id ?? $user->rw_id;
            $userRtId = $warga?->rt_id ?? $user->rt_id;

            // Target desa: terbuka untuk seluruh warga desa
            if ($informasi->target_type === 'desa' || (is_null($informasi->rw_id) && is_null($informasi->rt_id) && $informasi->target_type !== 'role')) {
                return true;
            }

            // Target RW: warga harus berada di RW yang sama
            if ($informasi->target_type === 'rw' || ($informasi->rw_id && is_null($informasi->rt_id))) {
                return $userRwId == $informasi->rw_id;
            }

            // Target RT: warga harus berada di RT yang sama
            if ($informasi->target_type === 'rt' || $informasi->rt_id) {
                return $userRwId == $informasi->rw_id && $userRtId == $informasi->rt_id;
            }

            // Jika target role dan rolenya cocok
            if ($informasi->target_type === 'role' && $informasi->target_role === 'warga') {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Tentukan apakah user dapat membuat informasi baru.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin_desa', 'rw', 'rt']);
    }

    /**
     * Tentukan apakah user dapat mengubah informasi.
     */
    public function update(User $user, Informasi $informasi): bool
    {
        if ($user->hasRole(['super_admin', 'admin_desa'])) {
            return true;
        }

        return $informasi->author_id == $user->id;
    }

    /**
     * Tentukan apakah user dapat menghapus informasi.
     */
    public function delete(User $user, Informasi $informasi): bool
    {
        if ($user->hasRole(['super_admin', 'admin_desa'])) {
            return true;
        }

        return $informasi->author_id == $user->id;
    }

    /**
     * Tentukan apakah user dapat memicu broadcast WhatsApp.
     */
    public function broadcast(User $user, Informasi $informasi): bool
    {
        if ($user->hasRole(['super_admin', 'admin_desa'])) {
            return true;
        }

        return $informasi->author_id == $user->id;
    }
}
