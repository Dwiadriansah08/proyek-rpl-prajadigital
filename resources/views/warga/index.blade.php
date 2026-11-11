<x-app-layout>
    <x-slot name="header">
        Data Kependudukan
    </x-slot>

    <div class="card border-0 p-4" style="border-radius: 20px;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
            <div>
                <h5 class="fw-bold text-dark mb-1" style="font-size: 1.15rem;">Direktori Penduduk Desa</h5>
                <p class="text-muted small mb-0">Manajemen basis data kependudukan dan registrasi kartu identitas</p>
            </div>
            @if(auth()->user()->hasRole(['super_admin', 'rw', 'rt']))
                <a href="{{ route('warga.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 shadow-sm">
                    <i class="bi bi-person-plus-fill"></i> Tambah Penduduk
                </a>
            @endif
        </div>

        <!-- Search Bar and Filters -->
        <form action="{{ route('warga.index') }}" method="GET" class="mb-4">
            <div class="d-flex flex-column flex-sm-row gap-2" style="max-width: 500px;">
                <div class="input-group">
                    <span class="input-group-text bg-slate-50 border-end-0 text-muted" style="border-color: var(--saas-border);"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari berdasarkan NIK atau Nama..." value="{{ request('search') }}">
                </div>
                <button class="btn btn-light px-3 fw-semibold flex-shrink-0" type="submit">Cari Data</button>
                @if(request('search'))
                    <a href="{{ route('warga.index') }}" class="btn btn-outline-secondary px-3 flex-shrink-0" title="Reset filter"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nomor Induk Kependudukan (NIK)</th>
                        <th>Nama Lengkap</th>
                        <th>Gender</th>
                        <th>Alamat Domisili</th>
                        <th>Wilayah Administrasi</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warga as $w)
                    <tr>
                        <td>
                            <span class="badge font-monospace" style="background: #F1F5F9; color: #334155; font-size: 0.8rem; letter-spacing: 0.05em;">{{ $w->nik }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 fw-bold" style="width: 34px; height: 34px; background: #EEF2FF; color: #4F46E5; font-size: 0.82rem;">
                                    {{ strtoupper(substr($w->nama_lengkap, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $w->nama_lengkap }}</div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        @if($w->user)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.72rem;">
                                                <i class="bi bi-shield-check me-1"></i>Akun: {{ $w->user->username ?? $w->user->email }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.72rem;">
                                                <i class="bi bi-person-x me-1"></i>Belum Ada Akun
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if(strtoupper(substr($w->jenis_kelamin, 0, 1)) === 'L')
                                <span class="badge" style="background: #EEF2FF; color: #4F46E5;">Laki-laki</span>
                            @else
                                <span class="badge" style="background: #FDF2F8; color: #DB2777;">Perempuan</span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size: 0.84rem;">{{ Str::limit($w->alamat, 35) }}</td>
                        <td>
                            <span class="badge" style="background: #F8FAFC; color: #475569; border: 1px solid var(--saas-border);">RW {{ $w->rw?->nomor_rw ?? '-' }} / RT {{ $w->rt?->nomor_rt ?? '-' }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('warga.show', $w->id) }}" class="btn btn-sm btn-light p-1 px-2 text-primary" title="Detail Data" style="border-radius: 8px;"><i class="bi bi-eye"></i></a>
                                @can('update', $w)
                                    <a href="{{ route('warga.edit', $w->id) }}" class="btn btn-sm btn-light p-1 px-2 text-warning" title="Edit Data" style="border-radius: 8px;"><i class="bi bi-pencil"></i></a>
                                @endcan
                                @can('delete', $w)
                                    <form action="{{ route('warga.destroy', $w->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-light p-1 px-2 text-danger" title="Hapus Data" style="border-radius: 8px;"
                                            data-confirm-delete
                                            data-delete-title="Hapus Data Penduduk"
                                            data-delete-message="Apakah Anda yakin ingin menghapus data penduduk ini? Akun login terkait (jika ada) dan permohonan surat juga akan terpengaruh."
                                            data-delete-detail="<strong>Nama:</strong> {{ $w->nama_lengkap }}<br><strong>NIK:</strong> {{ $w->nik }}<br><strong>Wilayah:</strong> RW {{ $w->rw?->nomor_rw ?? '-' }} / RT {{ $w->rt?->nomor_rt ?? '-' }}">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-2 d-block opacity-40 mb-2"></i>
                            <div class="fw-semibold">Tidak ada data penduduk yang cocok.</div>
                            <div class="small">Coba periksa kata kunci pencarian atau tambah penduduk baru.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 pt-3 border-top">
            {{ $warga->links() }}
        </div>
    </div>
</x-app-layout>
