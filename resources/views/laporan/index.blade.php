<x-app-layout>
    <x-slot name="header">
        Laporan & Rekapitulasi Pelayanan
    </x-slot>

    <!-- Filter & Export Controls Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <form method="GET" action="{{ route('laporan.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Bulan Pelayanan</label>
                <select name="bulan" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->locale('id')->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Tahun</label>
                <select name="tahun" class="form-select">
                    @for($y = now()->year; $y >= now()->year - 4; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="MENUNGGU_ADMIN_DESA" {{ request('status') === 'MENUNGGU_ADMIN_DESA' ? 'selected' : '' }}>Menunggu Verifikasi Desa</option>
                    <option value="MENUNGGU_RW" {{ request('status') === 'MENUNGGU_RW' ? 'selected' : '' }}>Menunggu RW</option>
                    <option value="MENUNGGU_RT" {{ request('status') === 'MENUNGGU_RT' ? 'selected' : '' }}>Menunggu RT</option>
                    <option value="SELESAI" {{ request('status') === 'SELESAI' ? 'selected' : '' }}>Selesai</option>
                    <option value="DITOLAK" {{ request('status') === 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-3 rounded-3 flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('laporan.export-pdf', request()->query()) }}" class="btn btn-danger px-3 rounded-3 shadow-sm" title="Export PDF">
                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                </a>
                <a href="{{ route('laporan.export-excel', request()->query()) }}" class="btn btn-success px-3 rounded-3 shadow-sm" title="Export Excel / CSV">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel
                </a>
            </div>
        </form>
    </div>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <div class="card border-0 p-3 h-100" style="border-radius: 16px;">
                <div class="text-muted small fw-semibold text-uppercase">Total Pengajuan</div>
                <h3 class="fw-bolder mb-0 text-dark mt-2">{{ number_format($summary['total']) }}</h3>
                <span class="text-muted small mt-1">berkas {{ $namaBulan }}</span>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card border-0 p-3 h-100" style="border-radius: 16px; background: #ECFDF5;">
                <div class="text-success small fw-semibold text-uppercase">Selesai</div>
                <h3 class="fw-bolder mb-0 text-success mt-2">{{ number_format($summary['selesai']) }}</h3>
                <span class="text-muted small mt-1">diterbitkan</span>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card border-0 p-3 h-100" style="border-radius: 16px; background: #EEF2FF;">
                <div class="text-primary small fw-semibold text-uppercase">Verifikasi Desa</div>
                <h3 class="fw-bolder mb-0 text-primary mt-2">{{ number_format($summary['menunggu_desa']) }}</h3>
                <span class="text-muted small mt-1">menunggu lurah</span>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card border-0 p-3 h-100" style="border-radius: 16px; background: #FFFBEB;">
                <div class="text-warning small fw-semibold text-uppercase">Menunggu RW</div>
                <h3 class="fw-bolder mb-0 text-warning mt-2">{{ number_format($summary['menunggu_rw']) }}</h3>
                <span class="text-muted small mt-1">verifikasi RW</span>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card border-0 p-3 h-100" style="border-radius: 16px; background: #F8FAFC;">
                <div class="text-secondary small fw-semibold text-uppercase">Menunggu RT</div>
                <h3 class="fw-bolder mb-0 text-secondary mt-2">{{ number_format($summary['menunggu_rt']) }}</h3>
                <span class="text-muted small mt-1">tahap RT</span>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card border-0 p-3 h-100" style="border-radius: 16px; background: #FEF2F2;">
                <div class="text-danger small fw-semibold text-uppercase">Ditolak</div>
                <h3 class="fw-bolder mb-0 text-danger mt-2">{{ number_format($summary['ditolak']) }}</h3>
                <span class="text-muted small mt-1">perlu revisi</span>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">
                Rekap Pelayanan Periode: {{ $namaBulan }} {{ $tahun }}
            </h5>
            <span class="badge bg-light text-dark border">
                Menampilkan {{ $pengajuan->count() }} data
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">No</th>
                        <th class="border-0">Tanggal</th>
                        <th class="border-0">Pemohon</th>
                        <th class="border-0">Wilayah</th>
                        <th class="border-0">Jenis Surat</th>
                        <th class="border-0">Nomor Surat</th>
                        <th class="border-0">Status</th>
                        <th class="border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuan as $idx => $s)
                    <tr>
                        <td class="text-muted small">{{ $pengajuan->firstItem() + $idx }}</td>
                        <td class="text-muted small">{{ $s->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $s->warga?->nama_lengkap }}</div>
                            <small class="text-muted font-monospace">{{ $s->warga?->nik }}</small>
                        </td>
                        <td class="small text-muted">
                            RT {{ $s->warga?->rt?->nomor_rt ?? '-' }} / RW {{ $s->warga?->rw?->nomor_rw ?? '-' }}
                        </td>
                        <td class="fw-medium">{{ $s->template?->nama_surat }}</td>
                        <td class="font-monospace small text-primary">{{ $s->nomor_surat ?? '-' }}</td>
                        <td>
                            @php
                                $badgeClass = 'bg-secondary';
                                if($s->status == 'SELESAI') $badgeClass = 'bg-success';
                                elseif($s->status == 'DITOLAK') $badgeClass = 'bg-danger';
                                elseif($s->status == 'MENUNGGU_ADMIN_DESA') $badgeClass = 'bg-primary';
                                elseif(str_contains($s->status, 'MENUNGGU')) $badgeClass = 'bg-warning text-dark';
                            @endphp
                            <span class="badge {{ $badgeClass }} rounded-pill px-3 fw-normal">
                                {{ str_replace('_', ' ', $s->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('surat.show', $s->id) }}" class="btn btn-sm btn-light text-primary rounded-pill px-3">
                                Rincian
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            Tidak ada arsip pelayanan pada periode {{ $namaBulan }} {{ $tahun }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $pengajuan->links() }}
        </div>
    </div>
</x-app-layout>

