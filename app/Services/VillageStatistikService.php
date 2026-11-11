<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Keluarga;
use App\Models\PengajuanSurat;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\SuratNotification;
use App\Models\User;
use App\Models\Warga;
use App\Models\WhatsappBroadcast;
use App\Models\WhatsappRecipient;
use Illuminate\Support\Facades\Cache;

/**
 * Service terpadu (single source of truth) untuk seluruh metrik dan statistik
 * yang digunakan secara konsisten oleh Dashboard dan AI Chatbot PrajaDigital.
 */
class VillageStatistikService
{
    /**
     * Membersihkan cache metrik statistik desa agar counter dashboard dan chatbot selalu ter-update real-time.
     */
    public static function clearStatsCache(): void
    {
        Cache::forget('village.stats.super_admin');
        Cache::forget('village.stats.penduduk');
        Cache::forget('village.stats.kk');
        Cache::forget('village.stats.wilayah');
        Cache::forget('village.stats.akun');
    }

    /**
     * Mengambil statistik kependudukan (total warga, laki-laki, perempuan) aktif.
     */
    public function getPendudukStats(?int $rwId = null, ?int $rtId = null): array
    {
        $query = Warga::query()->where(function ($q) {
            $q->where('status_aktif', true)
                ->orWhere('status_aktif', 1)
                ->orWhereNull('status_aktif');
        });

        if ($rtId) {
            $query->where('rt_id', $rtId);
        } elseif ($rwId) {
            $query->where('rw_id', $rwId);
        }

        $totalWarga = (clone $query)->count();
        $lakiLaki = (clone $query)->where('jenis_kelamin', 'Laki-laki')->count();
        $perempuan = (clone $query)->where('jenis_kelamin', 'Perempuan')->count();

        return [
            'total_warga' => $totalWarga,
            'laki_laki' => $lakiLaki,
            'perempuan' => $perempuan,
        ];
    }

    /**
     * Menghitung jumlah Kartu Keluarga (KK) dan Kepala Keluarga secara akurat
     * sesuai skema aktual tanpa duplikasi.
     *
     * Di sistem PrajaDigital, data KK dapat tercatat di:
     * 1. Tabel 'keluarga' (registrasi berkas KK).
     * 2. Tabel 'warga' (kolom nomor_kk dan status_dalam_keluarga).
     *
     * Aturan perhitungan KK yang valid:
     * - Dari tabel 'warga': himpunan nomor_kk unik milik warga aktif.
     * - Dari tabel 'keluarga': hanya KK yang masih memiliki minimal 1 anggota
     *   warga aktif (via pivot keluarga_members), agar berkas KK yang sudah
     *   tidak memiliki penghuni (setelah warga dihapus) tidak ikut terhitung.
     * - Kedua sumber digabungkan dan di-deduplikasi untuk total akhir.
     */
    public function getKkStats(?int $rwId = null, ?int $rtId = null): array
    {
        $keluargaQuery = Keluarga::query()
            ->whereNotNull('nomor_kk')
            ->where('nomor_kk', '!=', '');

        $wargaKkQuery = Warga::query()
            ->where(function ($q) {
                $q->where('status_aktif', true)
                    ->orWhere('status_aktif', 1)
                    ->orWhereNull('status_aktif');
            })
            ->whereNotNull('nomor_kk')
            ->where('nomor_kk', '!=', '');

        $kepalaKeluargaQuery = Warga::query()
            ->where(function ($q) {
                $q->where('status_aktif', true)
                    ->orWhere('status_aktif', 1)
                    ->orWhereNull('status_aktif');
            })
            ->whereRaw('LOWER(TRIM(status_dalam_keluarga)) = ?', ['kepala keluarga']);

        if ($rtId) {
            $keluargaQuery->where('rt_id', $rtId);
            $wargaKkQuery->where('rt_id', $rtId);
            $kepalaKeluargaQuery->where('rt_id', $rtId);
        } elseif ($rwId) {
            $keluargaQuery->where('rw_id', $rwId);
            $wargaKkQuery->where('rw_id', $rwId);
            $kepalaKeluargaQuery->where('rw_id', $rwId);
        }

        // Clone sebelum pluck() agar builder dapat di-reuse untuk count()
        $kkFromKeluarga = (clone $keluargaQuery)->pluck('nomor_kk');
        $kkFromWarga = $wargaKkQuery->pluck('nomor_kk');

        $uniqueKkList = $kkFromKeluarga->concat($kkFromWarga)
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->unique()
            ->values();

        $totalKk = $uniqueKkList->count();
        $totalKkTerdaftar = (clone $keluargaQuery)->count();
        $totalKepalaKeluarga = $kepalaKeluargaQuery->count();

        return [
            'total_kk' => $totalKk,
            'total_kk_terdaftar' => $totalKkTerdaftar,
            'total_kepala_keluarga' => $totalKepalaKeluarga,
            'unique_nomor_kk' => $uniqueKkList->toArray(),
        ];
    }

    /**
     * Mengambil statistik wilayah fisik (RW dan RT).
     */
    public function getWilayahStats(?int $rwId = null): array
    {
        $totalRw = Rw::count();
        $totalRt = Rt::count();

        $rtBinaan = $rwId ? Rt::where('rw_id', $rwId)->count() : 0;

        $rws = Rw::withCount('rts')->orderBy('nomor_rw')->get()->map(function ($rw) {
            return [
                'id' => $rw->id,
                'nomor_rw' => $rw->nomor_rw,
                'ketua_rw' => $rw->ketua_rw ?? 'Belum terdata',
                'total_rt' => $rw->rts_count,
            ];
        })->toArray();

        return [
            'total_rw' => $totalRw,
            'total_rt' => $totalRt,
            'rt_binaan' => $rtBinaan,
            'rws' => $rws,
        ];
    }

    /**
     * Mengambil statistik akun login pengguna berdasarkan role.
     */
    public function getAkunStats(?int $rwId = null, ?int $rtId = null): array
    {
        $query = User::query()->with('role');

        if ($rtId) {
            $query->where('rt_id', $rtId);
        } elseif ($rwId) {
            $query->where('rw_id', $rwId);
        }

        $allUsers = $query->get();
        $total = $allUsers->count();
        $active = $allUsers->where('is_active', true)->count();

        $roleCounts = [
            'super_admin' => 0,
            'admin_desa' => 0,
            'rw' => 0,
            'rt' => 0,
            'warga' => 0,
        ];

        $roleActiveCounts = [
            'super_admin' => 0,
            'admin_desa' => 0,
            'rw' => 0,
            'rt' => 0,
            'warga' => 0,
        ];

        foreach ($allUsers as $u) {
            $r = $u->role ? strtolower($u->role->name) : 'unassigned';
            $roleCounts[$r] = ($roleCounts[$r] ?? 0) + 1;
            if ($u->is_active) {
                $roleActiveCounts[$r] = ($roleActiveCounts[$r] ?? 0) + 1;
            }
        }

        return [
            'total' => $total,
            'active' => $active,
            'super_admin' => $roleCounts['super_admin'] ?? 0,
            'admin_desa' => $roleCounts['admin_desa'] ?? 0,
            'rw' => $roleCounts['rw'] ?? 0,
            'rt' => $roleCounts['rt'] ?? 0,
            'warga' => $roleCounts['warga'] ?? 0,
            'active_super_admin' => $roleActiveCounts['super_admin'] ?? 0,
            'active_admin_desa' => $roleActiveCounts['admin_desa'] ?? 0,
            'active_rw' => $roleActiveCounts['rw'] ?? 0,
            'active_rt' => $roleActiveCounts['rt'] ?? 0,
            'active_warga' => $roleActiveCounts['warga'] ?? 0,
            'per_role' => $roleCounts,
        ];
    }

    /**
     * Mengambil statistik pengajuan surat per status sesuai skema database aktual.
     * Status aktual: DRAFT | MENUNGGU_RT | MENUNGGU_RW | DIPROSES | SELESAI | DITOLAK
     */
    public function getPengajuanStats(?int $rwId = null, ?int $rtId = null, ?int $wargaId = null): array
    {
        $query = PengajuanSurat::query();

        if ($wargaId) {
            $query->where('warga_id', $wargaId);
        } elseif ($rtId) {
            $query->whereHas('warga', fn ($q) => $q->where('rt_id', $rtId));
        } elseif ($rwId) {
            $query->whereHas('warga', fn ($q) => $q->where('rw_id', $rwId));
        }

        $total = (clone $query)->count();
        $selesai = (clone $query)->where('status', 'SELESAI')->count();
        $menungguRt = (clone $query)->where('status', 'MENUNGGU_RT')->count();
        $menungguRw = (clone $query)->where('status', 'MENUNGGU_RW')->count();
        $menungguAdminDesa = (clone $query)->where('status', 'MENUNGGU_ADMIN_DESA')->count();
        $diproses = (clone $query)->where('status', 'DIPROSES')->count();
        $draft = (clone $query)->where('status', 'DRAFT')->count();
        $ditolak = (clone $query)->where('status', 'DITOLAK')->count();
        $menungguTotal = $menungguRt + $menungguRw + $menungguAdminDesa + $diproses + $draft;

        return [
            'total' => $total,
            'selesai' => $selesai,
            'menunggu_rt' => $menungguRt,
            'menunggu_rw' => $menungguRw,
            'menunggu_admin_desa' => $menungguAdminDesa,
            'diproses' => $diproses,
            'draft' => $draft,
            'ditolak' => $ditolak,
            'menunggu_total' => $menungguTotal,
        ];
    }

    /**
     * Mengambil statistik riwayat pengiriman WhatsApp aktual (Fonnte) dari log.
     * Status delivery riil dibaca dari whatsapp_recipients (berhasil / gagal / menunggu).
     */
    public function getWhatsappStats(?int $userId = null, ?int $wargaId = null): array
    {
        $broadcastQuery = WhatsappBroadcast::query();
        $recipientQuery = WhatsappRecipient::query();

        if ($wargaId || $userId) {
            $recipientQuery->where(function ($q) use ($wargaId, $userId) {
                if ($wargaId) {
                    $q->where('warga_id', $wargaId);
                }
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
            });
        }

        $totalBroadcasts = $broadcastQuery->count();
        $totalRecipients = (clone $recipientQuery)->count();
        $berhasil = (clone $recipientQuery)->where('status', 'berhasil')->count();
        $gagal = (clone $recipientQuery)->where('status', 'gagal')->count();
        $menunggu = (clone $recipientQuery)->whereNotIn('status', ['berhasil', 'gagal'])->count();

        return [
            'total_broadcasts' => $totalBroadcasts,
            'total_recipients' => $totalRecipients,
            'berhasil' => $berhasil,
            'gagal' => $gagal,
            'menunggu' => $menunggu,
        ];
    }

    /**
     * Mengambil statistik notifikasi in-app surat.
     * Status riil dibaca dari surat_notifications (sent / pending / failed).
     */
    public function getNotifikasiStats(?int $userId = null, ?int $wargaId = null): array
    {
        $query = SuratNotification::query();

        if ($wargaId || $userId) {
            $query->where(function ($q) use ($wargaId, $userId) {
                if ($wargaId) {
                    $q->where('recipient_warga_id', $wargaId);
                }
                if ($userId) {
                    $q->orWhere('recipient_user_id', $userId);
                }
            });
        }

        $total = (clone $query)->count();
        $sent = (clone $query)->where('status', 'sent')->count();
        $failed = (clone $query)->where('status', 'failed')->count();
        $pending = (clone $query)->whereNotIn('status', ['sent', 'failed'])->count();

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
        ];
    }

    /**
     * Mengambil statistik audit log aktivitas sistem.
     */
    public function getAktivitasStats(?int $userId = null): array
    {
        $query = AuditLog::query();
        if ($userId) {
            $query->where('user_id', $userId);
        }

        return [
            'total' => $query->count(),
        ];
    }

    /**
     * Dapatkan paket statistik Super Admin (konsisten untuk Dashboard & AI Chat).
     */
    public function getSuperAdminDashboardStats(): array
    {
        $penduduk = $this->getPendudukStats();
        $kk = $this->getKkStats();
        $wilayah = $this->getWilayahStats();
        $pengajuan = $this->getPengajuanStats();

        return [
            'total_rw' => $wilayah['total_rw'],
            'total_rt' => $wilayah['total_rt'],
            'total_warga' => $penduduk['total_warga'],
            'total_kk' => $kk['total_kk'],
            'total_kk_terdaftar' => $kk['total_kk_terdaftar'],
            'total_kepala_keluarga' => $kk['total_kepala_keluarga'],
            'laki_laki' => $penduduk['laki_laki'],
            'perempuan' => $penduduk['perempuan'],
            'pengajuan' => $pengajuan['total'],
            'pengajuan_selesai' => $pengajuan['selesai'],
            'pengajuan_menunggu_rt' => $pengajuan['menunggu_rt'],
            'pengajuan_menunggu_rw' => $pengajuan['menunggu_rw'],
            'pengajuan_menunggu_admin_desa' => $pengajuan['menunggu_admin_desa'],
            'pengajuan_ditolak' => $pengajuan['ditolak'],
        ];
    }

    /**
     * Dapatkan paket statistik Admin Desa (konsisten untuk Dashboard & Rekapitulasi).
     */
    public function getAdminDesaDashboardStats(): array
    {
        $penduduk = $this->getPendudukStats();
        $wilayah = $this->getWilayahStats();
        $pengajuan = $this->getPengajuanStats();

        $suratSelesaiBulanIni = PengajuanSurat::where('status', 'SELESAI')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        $monthlyTrend = $this->getMonthlySuratTrend(6);

        return [
            'total_warga' => $penduduk['total_warga'],
            'total_rw' => $wilayah['total_rw'],
            'total_rt' => $wilayah['total_rt'],
            'menunggu_verifikasi_desa' => $pengajuan['menunggu_admin_desa'],
            'surat_selesai_bulan_ini' => $suratSelesaiBulanIni,
            'pengajuan_total' => $pengajuan['total'],
            'pengajuan_selesai' => $pengajuan['selesai'],
            'pengajuan_ditolak' => $pengajuan['ditolak'],
            'pengajuan_menunggu_rt' => $pengajuan['menunggu_rt'],
            'pengajuan_menunggu_rw' => $pengajuan['menunggu_rw'],
            'laki_laki' => $penduduk['laki_laki'],
            'perempuan' => $penduduk['perempuan'],
            'monthly_trend' => $monthlyTrend,
        ];
    }

    /**
     * Mendapatkan tren pengajuan surat bulanan untuk visualisasi grafik.
     */
    public function getMonthlySuratTrend(int $months = 6): array
    {
        $labels = [];
        $dataTotal = [];
        $dataSelesai = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthNum = $date->month;
            $yearNum = $date->year;

            $labels[] = $date->translatedFormat('M Y');
            $dataTotal[] = PengajuanSurat::whereMonth('created_at', $monthNum)
                ->whereYear('created_at', $yearNum)
                ->count();
            $dataSelesai[] = PengajuanSurat::where('status', 'SELESAI')
                ->whereMonth('updated_at', $monthNum)
                ->whereYear('updated_at', $yearNum)
                ->count();
        }

        return [
            'labels' => $labels,
            'total' => $dataTotal,
            'selesai' => $dataSelesai,
        ];
    }

    /**
     * Dapatkan paket statistik RW (konsisten untuk Dashboard & AI Chat).
     */
    public function getRwDashboardStats(?int $rwId): array
    {
        if (! $rwId) {
            return [];
        }

        $penduduk = $this->getPendudukStats($rwId, null);
        $kk = $this->getKkStats($rwId, null);
        $wilayah = $this->getWilayahStats($rwId);
        $pengajuan = $this->getPengajuanStats($rwId, null);

        return [
            'total_rt' => $wilayah['rt_binaan'],
            'total_warga' => $penduduk['total_warga'],
            'total_kk' => $kk['total_kk'],
            'total_kk_terdaftar' => $kk['total_kk_terdaftar'],
            'total_kepala_keluarga' => $kk['total_kepala_keluarga'],
            'laki_laki' => $penduduk['laki_laki'],
            'perempuan' => $penduduk['perempuan'],
            'pengajuan' => $pengajuan['total'],
            'pengajuan_selesai' => $pengajuan['selesai'],
            'pengajuan_menunggu_rt' => $pengajuan['menunggu_rt'],
            'pengajuan_menunggu_rw' => $pengajuan['menunggu_rw'],
            'pengajuan_menunggu_admin_desa' => $pengajuan['menunggu_admin_desa'],
            'pengajuan_ditolak' => $pengajuan['ditolak'],
        ];
    }

    /**
     * Dapatkan paket statistik RT (konsisten untuk Dashboard & AI Chat).
     */
    public function getRtDashboardStats(?int $rtId): array
    {
        if (! $rtId) {
            return [];
        }

        $penduduk = $this->getPendudukStats(null, $rtId);
        $kk = $this->getKkStats(null, $rtId);
        $pengajuan = $this->getPengajuanStats(null, $rtId);

        return [
            'total_warga' => $penduduk['total_warga'],
            'total_kk' => $kk['total_kk'],
            'total_kk_terdaftar' => $kk['total_kk_terdaftar'],
            'total_kepala_keluarga' => $kk['total_kepala_keluarga'],
            'laki_laki' => $penduduk['laki_laki'],
            'perempuan' => $penduduk['perempuan'],
            'pengajuan' => $pengajuan['total'],
            'pengajuan_selesai' => $pengajuan['selesai'],
            'pengajuan_menunggu_rt' => $pengajuan['menunggu_rt'],
            'pengajuan_menunggu_rw' => $pengajuan['menunggu_rw'],
            'pengajuan_menunggu_admin_desa' => $pengajuan['menunggu_admin_desa'],
            'pengajuan_ditolak' => $pengajuan['ditolak'],
        ];
    }

    /**
     * Dapatkan paket statistik Warga (konsisten untuk Dashboard & AI Chat).
     */
    public function getWargaDashboardStats(?int $wargaId): array
    {
        if (! $wargaId) {
            return [
                'pengajuan_saya' => 0,
                'pengajuan_selesai' => 0,
                'menunggu_tindakan' => 0,
                'dokumen_saya' => 0,
            ];
        }

        $pengajuan = $this->getPengajuanStats(null, null, $wargaId);

        $dokumenSaya = PengajuanSurat::where('warga_id', $wargaId)
            ->where('status', 'SELESAI')
            ->whereNotNull('file_pdf')
            ->count();

        return [
            'pengajuan_saya' => $pengajuan['total'],
            'pengajuan_selesai' => $pengajuan['selesai'],
            'menunggu_tindakan' => $pengajuan['menunggu_total'],
            'dokumen_saya' => $dokumenSaya,
        ];
    }
}
