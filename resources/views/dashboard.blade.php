<x-app-layout>
    <x-slot name="header">
        @if(auth()->user()->hasRole('warga'))
            Halo, {{ auth()->user()->warga?->nama_lengkap ?? auth()->user()->name }}! 👋
        @else
            Dashboard Eksekutif {{ auth()->user()->role?->name ? ucwords(str_replace('_', ' ', auth()->user()->role->name)) : '' }}
        @endif
    </x-slot>

    @if(auth()->user()->hasRole('warga'))
        <!-- ========== DASHBOARD WARGA ========== -->
        <!-- Welcome Hero Banner -->
        <div class="card border-0 mb-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #1E1B4B 0%, #312E81 50%, #4338CA 100%); color: #ffffff; border-radius: 20px;">
            <div class="position-absolute end-0 top-0 bottom-0 opacity-10 d-none d-lg-block pointer-events-none" style="transform: rotate(-10deg) translate(20px, -20px);">
                <i class="bi bi-shield-check" style="font-size: 16rem;"></i>
            </div>
            <div class="card-body p-4 p-md-5 position-relative z-1">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-10 text-white mb-3" style="font-size: 0.8rem; backdrop-filter: blur(8px);">
                            <span class="badge bg-emerald-400 text-dark rounded-circle p-1" style="width: 8px; height: 8px; background: #34D399;"></span>
                            <span>Portal Digital Warga Aktif</span>
                        </div>
                        <h2 class="fw-bold tracking-tight mb-2 text-white" style="font-size: 1.85rem;">Pelayanan Surat & Informasi Desa dalam Satu Sentuhan</h2>
                        <p class="text-white text-opacity-80 mb-4" style="max-width: 620px; font-size: 0.95rem; line-height: 1.6;">
                            Ajukan surat pengantar resmi secara online tanpa antre, pantau status verifikasi RT/RW hingga selesai, dan temukan pengumuman desa terkini kapan pun Anda butuhkan.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('surat.create') }}" class="btn btn-light px-4 py-2 fw-bold text-indigo-700 shadow-sm" style="border-radius: 12px; color: #4338CA;">
                                <i class="bi bi-plus-circle me-1"></i> Ajukan Surat Baru
                            </a>
                            <a href="{{ route('informasi.index') }}" class="btn btn-outline-light px-4 py-2 fw-semibold" style="border-radius: 12px; border-color: rgba(255,255,255,0.3);">
                                <i class="bi bi-newspaper me-1"></i> Informasi Desa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 h-100 p-4" style="border-radius: 18px; cursor: pointer;" onclick="window.location.href='{{ route('surat.index') }}'">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-semibold" style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.04em;">Pengajuan Aktif</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 42px; height: 42px; background: #EEF2FF; color: #4F46E5;">
                            <i class="bi bi-hourglass-split fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bolder mb-0 text-dark" style="font-size: 2rem;">{{ number_format($stats['pengajuan_saya'] ?? 0) }}</h3>
                        <span class="text-muted small">berkas</span>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-lg-3">
                <div class="card border-0 h-100 p-4" style="border-radius: 18px; cursor: pointer;" onclick="window.location.href='{{ route('surat.index') }}'">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-semibold" style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.04em;">Surat Selesai</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 42px; height: 42px; background: #ECFDF5; color: #10B981;">
                            <i class="bi bi-check2-circle fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bolder mb-0 text-dark" style="font-size: 2rem;">{{ number_format($stats['pengajuan_selesai'] ?? 0) }}</h3>
                        <span class="text-success small fw-semibold"><i class="bi bi-arrow-up-right"></i> Siap ambil</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 h-100 p-4" style="border-radius: 18px; cursor: pointer;" onclick="window.location.href='{{ route('surat.index') }}'">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-semibold" style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.04em;">Menunggu Respon</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 42px; height: 42px; background: #FFFBEB; color: #F59E0B;">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bolder mb-0 text-dark" style="font-size: 2rem;">{{ number_format($stats['menunggu_tindakan'] ?? 0) }}</h3>
                        <span class="text-muted small">dalam proses</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 h-100 p-4" style="border-radius: 18px; cursor: pointer;" onclick="window.location.href='{{ route('surat.index') }}'">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-semibold" style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.04em;">Dokumen Arsip</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 42px; height: 42px; background: #F0FDF4; color: #059669;">
                            <i class="bi bi-folder2-open fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bolder mb-0 text-dark" style="font-size: 2rem;">{{ number_format($stats['dokumen_saya'] ?? 0) }}</h3>
                        <span class="text-muted small">tersimpan</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Shortcuts -->
        <div class="mb-4">
            <h6 class="fw-bold text-dark mb-3" style="font-size: 0.95rem; letter-spacing: -0.01em;">Pintasan Cepat</h6>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <a href="{{ route('surat.create') }}" class="card border-0 p-3 text-decoration-none h-100 d-flex flex-row align-items-center gap-3" style="border-radius: 16px;">
                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 44px; height: 44px; background: #EEF2FF; color: #4F46E5;">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 0.88rem;">Ajukan Surat</div>
                            <div class="text-muted small" style="font-size: 0.75rem;">Buat permohonan baru</div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <a href="{{ route('surat.index') }}" class="card border-0 p-3 text-decoration-none h-100 d-flex flex-row align-items-center gap-3" style="border-radius: 16px;">
                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 44px; height: 44px; background: #F0FDF4; color: #16A34A;">
                            <i class="bi bi-file-earmark-check fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 0.88rem;">Arsip Saya</div>
                            <div class="text-muted small" style="font-size: 0.75rem;">Unduh dokumen selesai</div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <a href="{{ route('informasi.index') }}" class="card border-0 p-3 text-decoration-none h-100 d-flex flex-row align-items-center gap-3" style="border-radius: 16px;">
                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 44px; height: 44px; background: #FFFBEB; color: #D97706;">
                            <i class="bi bi-megaphone fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 0.88rem;">Kabar Desa</div>
                            <div class="text-muted small" style="font-size: 0.75rem;">Pengumuman & agenda</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Tables & Activity Feed -->
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 p-4 h-100" style="border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">Pengajuan Surat Terbaru</h5>
                            <p class="text-muted small mb-0">Status tindak lanjut pengajuan surat terakhir Anda</p>
                        </div>
                        <a href="{{ route('surat.index') }}" class="btn btn-sm btn-light px-3 py-1.5 fw-semibold" style="border-radius: 8px;">
                            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    
                    @if(isset($pengajuan_terbaru) && $pengajuan_terbaru->count() > 0)
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Jenis Pelayanan</th>
                                        <th>Tanggal Pengajuan</th>
                                        <th>Status Terkini</th>
                                        <th class="text-end">Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pengajuan_terbaru as $p)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 36px; height: 36px; background: #EEF2FF; color: #4F46E5;">
                                                    <i class="bi bi-file-text"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $p->template->nama_surat }}</div>
                                                    <div class="text-muted small" style="font-size: 0.75rem;">Nomor Ref: #{{ str_pad($p->id, 5, '0', STR_PAD_LEFT) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted" style="font-size: 0.84rem;">{{ $p->created_at->format('d M Y, H:i') }}</td>
                                        <td>
                                            @php
                                                $badgeStyle = 'background: #F1F5F9; color: #475569;';
                                                if($p->status == 'SELESAI') $badgeStyle = 'background: #ECFDF5; color: #059669;';
                                                elseif($p->status == 'DITOLAK') $badgeStyle = 'background: #FEF2F2; color: #DC2626;';
                                                elseif(str_contains($p->status, 'MENUNGGU')) $badgeStyle = 'background: #FFFBEB; color: #D97706;';
                                            @endphp
                                            <span class="badge border-0" style="{{ $badgeStyle }} font-size: 0.75rem;">
                                                {{ str_replace('_', ' ', $p->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('surat.show', $p->id) }}" class="btn btn-sm btn-light px-3 py-1" style="font-size: 0.78rem; border-radius: 8px;">
                                                Rincian
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 60px; height: 60px; background: #F1F5F9; color: #94A3B8;">
                                <i class="bi bi-inbox fs-3"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Belum Ada Pengajuan Surat</h6>
                            <p class="text-muted small mb-3">Semua riwayat permohonan surat Anda akan ditampilkan secara transparan di sini.</p>
                            <a href="{{ route('surat.create') }}" class="btn btn-primary btn-sm px-4 py-2">
                                <i class="bi bi-plus-circle me-1"></i> Ajukan Surat Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 p-4 h-100" style="border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Pengumuman Desa</h5>
                        <a href="{{ route('informasi.index') }}" class="text-primary small fw-semibold text-decoration-none">Semua</a>
                    </div>
                    
                    @if(isset($informasi) && $informasi->count() > 0)
                        <div class="d-flex flex-column gap-3">
                            @foreach($informasi->take(4) as $info)
                            <a href="{{ route('informasi.show', $info->id) }}" class="text-decoration-none">
                                <div class="d-flex align-items-start gap-3 p-2.5 rounded-3 border border-transparent hover-border transition" style="transition: all 0.15s ease;">
                                    <div class="rounded-3 p-2 text-center flex-shrink-0" style="min-width: 48px; background: #EEF2FF; color: #4F46E5;">
                                        <span class="d-block fw-bolder fs-5 lh-1">{{ $info->created_at->format('d') }}</span>
                                        <span class="d-block text-uppercase fw-bold" style="font-size: 0.65rem;">{{ $info->created_at->format('M') }}</span>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark line-clamp-1" style="font-size: 0.88rem;">{{ $info->judul }}</h6>
                                        <p class="mb-0 text-muted small" style="font-size: 0.78rem; line-height: 1.4;">{{ Str::limit(strip_tags($info->konten), 65) }}</p>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-bell-slash fs-3 mb-2 d-block opacity-40"></i>
                            <p class="small">Belum ada informasi terbaru.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    @elseif(auth()->user()->hasRole('admin_desa'))
        <!-- ========== DASHBOARD KHUSUS ADMIN DESA ========== -->
        <!-- 4 Widget Ringkasan Utama Admin Desa -->
        <div class="row g-3 mb-4">
            <!-- 1. Total Warga -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 p-4 h-100" style="border-radius: 18px; cursor: pointer;" onclick="window.location.href='{{ route('warga.index') }}'">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Total Warga</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 42px; height: 42px; background: #EEF2FF; color: #4F46E5;">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bolder mb-0 text-dark" style="font-size: 2.2rem;">{{ number_format($stats['total_warga'] ?? 0) }}</h3>
                        <span class="text-muted small">jiwa</span>
                    </div>
                    <div class="mt-2 text-muted small">
                        <i class="bi bi-person-check-fill text-success me-1"></i> Data kependudukan aktif
                    </div>
                </div>
            </div>

            <!-- 2. Total RT / RW -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 p-4 h-100" style="border-radius: 18px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Total RT / RW</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 42px; height: 42px; background: #F8FAFC; color: #0284C7;">
                            <i class="bi bi-diagram-3-fill fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-3">
                        <div>
                            <span class="fw-bolder fs-3 text-dark">{{ $stats['total_rw'] ?? 0 }}</span>
                            <span class="text-muted small ms-1">RW</span>
                        </div>
                        <span class="text-muted">/</span>
                        <div>
                            <span class="fw-bolder fs-3 text-dark">{{ $stats['total_rt'] ?? 0 }}</span>
                            <span class="text-muted small ms-1">RT</span>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        <i class="bi bi-geo-alt-fill text-info me-1"></i> Seluruh unit administratif
                    </div>
                </div>
            </div>

            <!-- 3. Permohonan Menunggu Verifikasi Desa -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 p-4 h-100" style="border-radius: 18px; cursor: pointer; border-left: 4px solid #4F46E5 !important;" onclick="window.location.href='{{ route('surat.index', ['status' => 'MENUNGGU_ADMIN_DESA']) }}'">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Verifikasi Desa</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 42px; height: 42px; background: #EEF2FF; color: #4F46E5;">
                            <i class="bi bi-clipboard2-check-fill fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bolder mb-0 text-indigo-700" style="font-size: 2.2rem; color: #4F46E5;">{{ number_format($stats['menunggu_verifikasi_desa'] ?? 0) }}</h3>
                        <span class="text-muted small">berkas</span>
                    </div>
                    <div class="mt-2 text-primary small fw-semibold">
                        <i class="bi bi-arrow-right-circle me-1"></i> Menunggu persetujuan akhir
                    </div>
                </div>
            </div>

            <!-- 4. Surat Selesai Bulan Ini -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 p-4 h-100" style="border-radius: 18px; cursor: pointer;" onclick="window.location.href='{{ route('laporan.index') }}'">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Selesai Bulan Ini</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 42px; height: 42px; background: #ECFDF5; color: #10B981;">
                            <i class="bi bi-patch-check-fill fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bolder mb-0 text-success" style="font-size: 2.2rem;">{{ number_format($stats['surat_selesai_bulan_ini'] ?? 0) }}</h3>
                        <span class="text-muted small">surat</span>
                    </div>
                    <div class="mt-2 text-muted small">
                        <i class="bi bi-calendar2-check me-1"></i> Periode {{ now()->translatedFormat('F Y') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Row: Grafik Tren Bulanan & Informasi -->
        <div class="row g-4 mb-4">
            <!-- Grafik Tren Pengajuan Surat Bulanan -->
            <div class="col-lg-8">
                <div class="card border-0 p-4 h-100" style="border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Tren Pengajuan Surat Bulanan</h5>
                            <p class="text-muted small mb-0">Statistik permohonan surat 6 bulan terakhir</p>
                        </div>
                        <a href="{{ route('laporan.index') }}" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold">
                            Lihat Laporan Lengkap <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="position-relative" style="height: 300px;">
                        <canvas id="trenSuratChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Publikasi & Pengumuman Desa -->
            <div class="col-lg-4">
                <div class="card border-0 p-4 h-100" style="border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Pengumuman Desa</h5>
                        <a href="{{ route('informasi.index') }}" class="text-primary small fw-semibold text-decoration-none">Kelola</a>
                    </div>
                    @if(isset($informasi) && $informasi->count() > 0)
                        <div class="d-flex flex-column gap-3">
                            @foreach($informasi->take(4) as $info)
                            <a href="{{ route('informasi.show', $info->id) }}" class="text-decoration-none">
                                <div class="p-2.5 rounded-3 bg-slate-50 border border-light">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 0.68rem;">{{ $info->kategori ?? 'Umum' }}</span>
                                        <span class="text-muted" style="font-size: 0.72rem;">{{ $info->created_at->diffForHumans() }}</span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">{{ $info->judul }}</h6>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-megaphone fs-3 mb-2 d-block opacity-40"></i>
                            <p class="small">Belum ada pengumuman desa.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if(isset($stats['monthly_trend']))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const canvas = document.getElementById('trenSuratChart');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($stats['monthly_trend']['labels'] ?? []),
                        datasets: [
                            {
                                label: 'Total Diajukan',
                                data: @json($stats['monthly_trend']['total'] ?? []),
                                backgroundColor: 'rgba(79, 70, 229, 0.75)',
                                borderColor: '#4F46E5',
                                borderWidth: 1,
                                borderRadius: 8,
                            },
                            {
                                label: 'Selesai Diterbitkan',
                                data: @json($stats['monthly_trend']['selesai'] ?? []),
                                backgroundColor: 'rgba(16, 185, 129, 0.75)',
                                borderColor: '#10B981',
                                borderWidth: 1,
                                borderRadius: 8,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 16,
                                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '600' }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            });
        </script>
        @endif

    @else
        <!-- ========== DASHBOARD EKSEKUTIF (SUPER ADMIN / RW / RT) ========== -->
        <!-- Analytics Stat KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 p-4 h-100" style="border-radius: 18px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Total Penduduk</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background: #EEF2FF; color: #4F46E5;">
                            <i class="bi bi-people fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bolder mb-0 text-dark" style="font-size: 2.2rem;">{{ number_format($stats['total_warga'] ?? 0) }}</h3>
                        <span class="text-muted small">jiwa</span>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-check-circle-fill text-success me-1"></i> Data kependudukan terverifikasi
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 p-4 h-100" style="border-radius: 18px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Kepala Keluarga</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background: #F0FDF4; color: #16A34A;">
                            <i class="bi bi-house-door fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bolder mb-0 text-dark" style="font-size: 2.2rem;">{{ number_format($stats['total_kepala_keluarga'] ?? 0) }}</h3>
                        <span class="text-muted small">jiwa</span>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-card-checklist me-1"></i> {{ number_format($stats['total_kk'] ?? 0) }} Kartu Keluarga (KK)
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 p-4 h-100" style="border-radius: 18px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Layanan Surat</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background: #FFFBEB; color: #D97706;">
                            <i class="bi bi-envelope-check fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bolder mb-0 text-dark" style="font-size: 2.2rem;">{{ number_format($stats['pengajuan'] ?? 0) }}</h3>
                        <span class="text-muted small">berkas</span>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-clock me-1"></i> Total riwayat permohonan
                    </div>
                </div>
            </div>

            @if(isset($stats['total_rw']) || isset($stats['total_rt']))
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 p-4 h-100" style="border-radius: 18px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Cakupan Wilayah</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background: #F8FAFC; color: #0284C7;">
                            <i class="bi bi-map fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-3">
                        @if(isset($stats['total_rw']))
                        <div>
                            <span class="fw-bolder fs-4 text-dark">{{ $stats['total_rw'] }}</span>
                            <span class="text-muted small ms-1">RW</span>
                        </div>
                        @endif
                        @if(isset($stats['total_rt']))
                        <div>
                            <span class="fw-bolder fs-4 text-dark">{{ $stats['total_rt'] }}</span>
                            <span class="text-muted small ms-1">RT</span>
                        </div>
                        @endif
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-geo-alt me-1"></i> Seluruh unit administratif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Demographic Composition & Publications -->
        <div class="row g-4 mb-4">
            <!-- Visualisasi Demografi Penduduk -->
            <div class="col-lg-5">
                <div class="card border-0 p-4 h-100" style="border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Komposisi Gender</h5>
                            <p class="text-muted small mb-0">Rasio Laki-laki vs Perempuan</p>
                        </div>
                        <span class="badge" style="background: #EEF2FF; color: #4F46E5;">Statistik Aktif</span>
                    </div>

                    @if(isset($stats['laki_laki']) && isset($stats['perempuan']) && ($stats['laki_laki'] > 0 || $stats['perempuan'] > 0))
                        <div class="position-relative d-flex align-items-center justify-content-center" style="height: 240px;">
                            <canvas id="genderChart"></canvas>
                        </div>
                        <div class="row mt-4 pt-3 border-top g-2 text-center">
                            <div class="col-6">
                                <div class="p-2 rounded-3" style="background: #EEF2FF;">
                                    <div class="text-muted small fw-semibold">Laki-Laki</div>
                                    <div class="fw-bold fs-5 text-indigo-700" style="color: #4F46E5;">{{ number_format($stats['laki_laki']) }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded-3" style="background: #FDF2F8;">
                                    <div class="text-muted small fw-semibold">Perempuan</div>
                                    <div class="fw-bold fs-5 text-pink-600" style="color: #DB2777;">{{ number_format($stats['perempuan']) }}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-pie-chart fs-2 mb-2 d-block opacity-40"></i>
                            <p class="small">Belum cukup data untuk menampilkan visualisasi gender.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Publikasi & Berita Wilayah -->
            <div class="col-lg-7">
                <div class="card border-0 p-4 h-100" style="border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Informasi & Pengumuman Terbaru</h5>
                            <p class="text-muted small mb-0">Arsip publikasi yang disiarkan kepada warga</p>
                        </div>
                        <a href="{{ route('informasi.index') }}" class="btn btn-sm btn-light px-3 py-1.5 fw-semibold" style="border-radius: 8px;">
                            Kelola Informasi
                        </a>
                    </div>

                    @if(isset($informasi) && $informasi->count() > 0)
                        <div class="d-flex flex-column gap-3">
                            @foreach($informasi->take(5) as $info)
                            <a href="{{ route('informasi.show', $info->id) }}" class="text-decoration-none">
                                <div class="p-3 rounded-3 border border-light bg-slate-50 hover-bg-light transition d-flex justify-content-between align-items-center">
                                    <div class="pe-3">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge border-0" style="background: #EEF2FF; color: #4F46E5; font-size: 0.7rem;">{{ $info->kategori ?? 'Umum' }}</span>
                                            <span class="text-muted" style="font-size: 0.75rem;">{{ $info->created_at->diffForHumans() }}</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">{{ $info->judul }}</h6>
                                        <p class="text-muted small mb-0" style="font-size: 0.8rem;">{{ Str::limit(strip_tags($info->konten), 90) }}</p>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-megaphone fs-3 mb-2 d-block opacity-40"></i>
                            <p class="small">Belum ada riwayat pengumuman publik yang diposting.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if(isset($stats['laki_laki']) && isset($stats['perempuan']) && ($stats['laki_laki'] > 0 || $stats['perempuan'] > 0))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const canvas = document.getElementById('genderChart');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Laki-laki', 'Perempuan'],
                        datasets: [{
                            data: [{{ $stats['laki_laki'] }}, {{ $stats['perempuan'] }}],
                            backgroundColor: ['#4F46E5', '#EC4899'],
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 16,
                                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '600' },
                                    color: '#475569'
                                }
                            }
                        }
                    }
                });
            });
        </script>
        @endif
    @endif
</x-app-layout>
