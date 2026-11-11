<x-app-layout>
    <x-slot name="header">
        Manajemen Wilayah
    </x-slot>

    {{-- Tab Navigasi RW / RT --}}
    <div class="mb-4">
        <ul class="nav" style="gap:6px; border-bottom: 2px solid #E2E8F0; padding-bottom: 0;">
            <li class="nav-item">
                <a href="{{ route('rw.index') }}"
                   class="nav-link px-4 py-2 fw-semibold"
                   style="border-radius:10px 10px 0 0; border:none; color:#4F46E5; background:#EEF2FF; font-size:0.9rem; border-bottom: 3px solid #4F46E5; margin-bottom:-2px;">
                    <i class="bi bi-diagram-3 me-2"></i>Manajemen RW
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('rt.index') }}"
                   class="nav-link px-4 py-2 fw-semibold"
                   style="border-radius:10px 10px 0 0; border:none; color:#64748B; background:transparent; font-size:0.9rem; border-bottom: 3px solid transparent; margin-bottom:-2px;">
                    <i class="bi bi-signpost-split me-2"></i>Manajemen RT
                </a>
            </li>
        </ul>
    </div>

    <div class="card p-0 overflow-hidden">
        {{-- Card Header --}}
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-3 px-4">
            <div>
                <h5 class="mb-0 fw-bold" style="font-size:1.05rem;">
                    <i class="bi bi-diagram-3 me-2 text-primary"></i>Daftar Rukun Warga (RW)
                </h5>
                <p class="mb-0 text-muted" style="font-size:0.8rem; margin-top:2px;">
                    Total {{ $rws->total() }} wilayah RW terdaftar
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <form action="{{ route('rw.index') }}" method="GET" class="d-flex gap-2" id="rwSearchForm">
                    <div class="input-group" style="min-width:220px; max-width:300px;">
                        <span class="input-group-text bg-white border-end-0" style="border-radius:10px 0 0 10px;">
                            <i class="bi bi-search text-muted" style="font-size:0.85rem;"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                               placeholder="Cari nomor/ketua RW..."
                               value="{{ request('search') }}"
                               style="border-radius: 0 10px 10px 0;">
                    </div>
                    @if(request('search'))
                        <a href="{{ route('rw.index') }}" class="btn btn-light" title="Reset Pencarian">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </form>
                @can('create', App\Models\Rw::class)
                <a href="{{ route('rw.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 px-3">
                    <i class="bi bi-plus-circle-fill"></i> Tambah RW
                </a>
                @endcan
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mx-4 mt-3 mb-0" style="border-radius:10px; font-size:0.875rem;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mx-4 mt-3 mb-0" style="border-radius:10px; font-size:0.875rem;">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Table --}}
        <div class="table-responsive mt-3">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;" class="ps-4">#</th>
                        <th>Nomor RW</th>
                        <th>Ketua RW</th>
                        <th>Desa/Kelurahan</th>
                        <th>Kontak</th>
                        <th class="text-center">Jumlah RT</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rws as $i => $rw)
                    <tr>
                        <td class="ps-4 text-muted" style="font-size:0.8rem;">
                            {{ $rws->firstItem() + $i }}
                        </td>
                        <td>
                            <span class="badge d-inline-flex align-items-center gap-1 px-3 py-2"
                                  style="background: linear-gradient(135deg, #4F46E5, #7C3AED); color:#fff; border-radius:8px; font-size:0.85rem; font-weight:700; letter-spacing:0.01em;">
                                <i class="bi bi-diagram-3"></i> RW {{ $rw->nomor_rw }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:34px;height:34px;background:linear-gradient(135deg,#EEF2FF,#E0E7FF);color:#4F46E5;font-weight:700;font-size:0.8rem;">
                                    {{ strtoupper(substr($rw->ketua_rw, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size:0.875rem; color:#0F172A;">{{ $rw->ketua_rw }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border" style="font-weight:500; font-size:0.78rem;">
                                <i class="bi bi-building me-1"></i>{{ $rw->desa->nama ?? '-' }}
                            </span>
                        </td>
                        <td>
                            @if($rw->kontak)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $rw->kontak) }}" target="_blank"
                                   class="text-success text-decoration-none d-inline-flex align-items-center gap-1"
                                   style="font-size:0.82rem;">
                                    <i class="bi bi-whatsapp"></i> {{ $rw->kontak }}
                                </a>
                            @else
                                <span class="text-muted" style="font-size:0.82rem;">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('rt.index', ['rw_id' => $rw->id]) }}"
                               class="badge text-decoration-none"
                               style="background:#EEF2FF; color:#4F46E5; border-radius:8px; font-size:0.82rem; padding:6px 12px; font-weight:600;">
                                {{ $rw->rts->count() }} RT
                            </a>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('update', $rw)
                                <a href="{{ route('rw.edit', $rw->id) }}"
                                   class="btn btn-sm btn-light border"
                                   title="Edit RW"
                                   style="border-radius:8px; width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center;">
                                    <i class="bi bi-pencil text-primary" style="font-size:0.85rem;"></i>
                                </a>
                                @endcan
                                @can('delete', $rw)
                                <form action="{{ route('rw.destroy', $rw->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="btn btn-sm btn-light border"
                                        title="Hapus RW"
                                        style="border-radius:8px; width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center;"
                                        data-confirm-delete
                                        data-delete-title="Hapus Wilayah RW"
                                        data-delete-message="Tindakan ini akan ditolak jika RW masih memiliki RT atau Warga terkait."
                                        data-delete-detail="<strong>Wilayah:</strong> RW {{ $rw->nomor_rw }}<br><strong>Ketua:</strong> {{ $rw->ketua_rw }}<br><strong>Desa:</strong> {{ $rw->desa->nama ?? '-' }}">
                                        <i class="bi bi-trash text-danger" style="font-size:0.85rem;"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center gap-2 text-muted">
                                <i class="bi bi-diagram-3 opacity-30" style="font-size:2.5rem;"></i>
                                <p class="mb-0 fw-semibold">Belum ada data RW</p>
                                @if(request('search'))
                                    <small>Tidak ada hasil untuk "<strong>{{ request('search') }}</strong>"</small>
                                    <a href="{{ route('rw.index') }}" class="btn btn-sm btn-light border mt-1">Reset Pencarian</a>
                                @else
                                    <small>Klik tombol "Tambah RW" untuk menambahkan wilayah baru.</small>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($rws->hasPages())
        <div class="px-4 py-3 border-top d-flex justify-content-end">
            {{ $rws->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
