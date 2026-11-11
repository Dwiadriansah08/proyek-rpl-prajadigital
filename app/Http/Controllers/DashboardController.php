<?php

namespace App\Http\Controllers;

use App\Models\Informasi;
use App\Models\PengajuanSurat;
use App\Services\VillageStatistikService;
use Illuminate\View\View;

/**
 * Controller untuk menampilkan Dashboard pengguna.
 *
 * Menyajikan statistik yang relevan berdasarkan role
 * dan informasi publik terbaru, terintegrasi melalui VillageStatistikService.
 */
class DashboardController extends Controller
{
    public function __construct(
        protected ?VillageStatistikService $statistikService = null
    ) {
        $this->statistikService = $statistikService ?? app(VillageStatistikService::class);
    }

    /**
     * Tampilkan halaman dashboard utama.
     */
    public function index(): View
    {
        $user = auth()->user();
        $stats = [];
        $pengajuan_terbaru = null;

        if ($user->hasRole('super_admin')) {
            $stats = $this->getSuperAdminStats();
        } elseif ($user->hasRole('admin_desa')) {
            $stats = $this->getAdminDesaStats();
        } elseif ($user->hasRole('rw')) {
            $stats = $this->getRwStats($user->rw_id);
        } elseif ($user->hasRole('rt')) {
            $stats = $this->getRtStats($user->rt_id);
        } else {
            $stats = $this->getWargaStats($user->warga?->id);
            if ($user->warga) {
                $pengajuan_terbaru = PengajuanSurat::with('template')
                    ->where('warga_id', $user->warga->id)
                    ->latest()
                    ->take(3)
                    ->get();
            }
        }

        $informasi = Informasi::latest()->take(5)->get();

        return view('dashboard', compact('stats', 'informasi', 'pengajuan_terbaru'));
    }

    /**
     * Dapatkan statistik keseluruhan untuk Super Admin.
     */
    private function getSuperAdminStats(): array
    {
        return $this->statistikService->getSuperAdminDashboardStats();
    }

    /**
     * Dapatkan statistik khusus untuk Admin Desa.
     */
    private function getAdminDesaStats(): array
    {
        return $this->statistikService->getAdminDesaDashboardStats();
    }

    /**
     * Dapatkan statistik khusus untuk RW.
     */
    private function getRwStats(?int $rwId): array
    {
        return $this->statistikService->getRwDashboardStats($rwId);
    }

    /**
     * Dapatkan statistik khusus untuk RT.
     */
    private function getRtStats(?int $rtId): array
    {
        return $this->statistikService->getRtDashboardStats($rtId);
    }

    /**
     * Dapatkan statistik khusus untuk Warga.
     */
    private function getWargaStats(?int $wargaId): array
    {
        return $this->statistikService->getWargaDashboardStats($wargaId);
    }
}
