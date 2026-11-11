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
                   style="border-radius:10px 10px 0 0; border:none; color:#64748B; background:transparent; font-size:0.9rem; border-bottom: 3px solid transparent; margin-bottom:-2px;">
                    <i class="bi bi-diagram-3 me-2"></i>Manajemen RW
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('rt.index') }}"
                   class="nav-link px-4 py-2 fw-semibold"
                   style="border-radius:10px 10px 0 0; border:none; color:#4F46E5; background:#EEF2FF; font-size:0.9rem; border-bottom: 3px solid #4F46E5; margin-bottom:-2px;">
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
                    <i class="bi bi-signpost-split me-2 text-primary"></i>Daftar Rukun Tetangga (RT)
                </h5>
                <p class="mb-0 text-muted" style="font-size:0.8rem; margin-top:2px;">
                    Total {{ $rts->total() }} wilayah RT terdaftar
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <form action="{{ route('rt.index') }}" method="GET" class="d-flex gap-2" id="rtSearchForm">
                    {{-- Filter RW --}}
                    @if(isset($rwList) && $rwList->count())
                    <select name="rw_id" class="form-select" style="min-width:130px; max-width:180px; font-size:0.85rem;" onchange="this.form.submit()">
                        <option value="">Semua RW</option>
                        @foreach($rwList as $rwItem)
                            <option value="{{ $rwItem->id }}" {{ request('rw_id') == $rwItem->id ? 'selected' : '' }}>
                                RW {{ $rwItem->nomor_rw }}
                            </option>
                        @endforeach
                    </select>
                    @endif
                    <div class="input-group" style="min-width:220px; max-width:300px;">
                        <span class="input-group-text bg-white border-end-0" style="border-radius:10px 0 0 10px;">
                            <i class="bi bi-search text-muted" style="font-size:0.85rem;"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                               placeholder="Cari nomor/ketua RT..."
                               value="{{ request('search') }}"
                               style="border-radius: 0 10px 10px 0;">
                    </div>
                    @if(request('search') || request('rw_id'))
                        <a href="{{ route('rt.index') }}" class="btn btn-light" title="Reset Pencarian">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </form>
                @can('create', App\Models\Rt::class)
                <a href="{{ route('rt.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 px-3">
                    <i class="bi bi-plus-circle-fill"></i> Tambah RT
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
                        <th>Nomor RT</th>
                        <th>Ketua RT</th>
                        <th>RW Induk</th>
                        <th>Kontak</th>
                        <th class="text-center">Jumlah Warga</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rts as $i => $rt)
                    <tr>
                        <td class="ps-4 text-muted" style="font-size:0.8rem;">
                            {{ $rts->firstItem() + $i }}
                        </td>
                        <td>
                            <span class="badge d-inline-flex align-items-center gap-1 px-3 py-2"
                                  style="background: linear-gradient(135deg, #0891B2, #06B6D4); color:#fff; border-radius:8px; font-size:0.85rem; font-weight:700; letter-spacing:0.01em;">
                                <i class="bi bi-signpost-split"></i> RT {{ $rt->nomor_rt }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:34px;height:34px;background:linear-gradient(135deg,#ECFEFF,#CFFAFE);color:#0891B2;font-weight:700;font-size:0.8rem;">
                                    {{ strtoupper(substr($rt->ketua_rt, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size:0.875rem; color:#0F172A;">{{ $rt->ketua_rt }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($rt->rw)
                            <a href="{{ route('rw.index', ['search' => $rt->rw->nomor_rw]) }}"
                               class="badge text-decoration-none"
                               style="background:linear-gradient(135deg,#EEF2FF,#E0E7FF); color:#4F46E5; border-radius:8px; font-size:0.82rem; padding:6px 12px; font-weight:600;">
                                <i class="bi bi-diagram-3 me-1"></i>RW {{ $rt->rw->nomor_rw }}
                            </a>
                            @else
                                <span class="text-muted" style="font-size:0.82rem;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($rt->kontak)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $rt->kontak) }}" target="_blank"
                                   class="text-success text-decoration-none d-inline-flex align-items-center gap-1"
                                   style="font-size:0.82rem;">
                                    <i class="bi bi-whatsapp"></i> {{ $rt->kontak }}
                                </a>
                            @else
                                <span class="text-muted" style="font-size:0.82rem;">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge"
                                  style="background:#F0FDF4; color:#16A34A; border-radius:8px; font-size:0.82rem; padding:6px 12px; font-weight:600;">
                                {{ $rt->wargas_count ?? $rt->wargas->count() }} Warga
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('update', $rt)
                                <a href="{{ route('rt.edit', $rt->id) }}"
                                   class="btn btn-sm btn-light border"
                                   title="Edit RT"
                                   style="border-radius:8px; width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center;">
                                    <i class="bi bi-pencil text-primary" style="font-size:0.85rem;"></i>
                                </a>
                                @endcan
                                @can('delete', $rt)
                                <form action="{{ route('rt.destroy', $rt->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="btn btn-sm btn-light border"
                                        title="Hapus RT"
                                        style="border-radius:8px; width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center;"
                                        data-confirm-delete
                                        data-delete-title="Hapus Wilayah RT"
                                        data-delete-message="Tindakan ini akan ditolak jika RT masih memiliki data Warga terkait."
                                        data-delete-detail="<strong>Wilayah:</strong> RT {{ $rt->nomor_rt }} / RW {{ $rt->rw?->nomor_rw ?? '-' }}<br><strong>Ketua:</strong> {{ $rt->ketua_rt }}">
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
                                <i class="bi bi-signpost-split opacity-30" style="font-size:2.5rem;"></i>
                                <p class="mb-0 fw-semibold">Belum ada data RT</p>
                                @if(request('search') || request('rw_id'))
                                    <small>Tidak ada hasil untuk filter yang dipilih</small>
                                    <a href="{{ route('rt.index') }}" class="btn btn-sm btn-light border mt-1">Reset Filter</a>
                                @else
                                    <small>Klik tombol "Tambah RT" untuk menambahkan wilayah baru.</small>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($rts->hasPages())
        <div class="px-4 py-3 border-top d-flex justify-content-end">
            {{ $rts->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
