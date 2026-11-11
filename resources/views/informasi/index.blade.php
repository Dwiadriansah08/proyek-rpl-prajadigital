<x-app-layout>
    <x-slot name="header">
        Informasi & Pengumuman
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4 rounded-3 shadow-sm">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 rounded-3 shadow-sm">
            <i class="bi bi-x-circle-fill fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div class="mb-3 mb-md-0">
                <h5 class="mb-1 fw-bold">
                    @if(auth()->user()->hasRole('warga'))
                        Informasi & Pengumuman Desa
                    @else
                        Pusat Pengumuman & Informasi Bertarget
                    @endif
                </h5>
                <p class="text-muted text-sm mb-0">Informasi resmi kegiatan, layanan desa, dan pengumuman terarah berbasis wilayah.</p>
            </div>
            @can('create', App\Models\Informasi::class)
            <a href="{{ route('informasi.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm">
                <i class="bi bi-megaphone-fill"></i> Buat Pengumuman Baru
            </a>
            @endcan
        </div>

        <form action="{{ route('informasi.index') }}" method="GET" class="mb-4">
            <div class="input-group" style="max-width: 450px;">
                <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control bg-light border-0" placeholder="Cari judul informasi..." value="{{ request('search') }}">
                @if(request('search'))
                <a href="{{ route('informasi.index') }}" class="btn btn-light border-0"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>

        <div class="row g-4">
            @forelse($informasis as $info)
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-shadow transition" style="transition: all 0.25s ease;">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div class="d-flex flex-wrap gap-1.5">
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-normal">{{ $info->kategori }}</span>
                                
                                {{-- Target Scope Badge --}}
                                @if($info->target_type === 'desa')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        🌐 Seluruh Desa
                                    </span>
                                @elseif($info->target_type === 'rw')
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2.5 py-1">
                                        🏢 RW {{ $info->rw?->nomor_rw ?? '-' }}
                                    </span>
                                @elseif($info->target_type === 'rt')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                        🏡 RT {{ $info->rt?->nomor_rt ?? '-' }} / RW {{ $info->rw?->nomor_rw ?? '-' }}
                                    </span>
                                @elseif($info->target_type === 'role')
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1">
                                        🛡️ {{ strtoupper($info->target_role ?? '-') }}
                                    </span>
                                @endif
                            </div>
                            <small class="text-muted"><i class="bi bi-calendar3 me-1"></i>{{ $info->created_at->format('d M Y') }}</small>
                        </div>

                        <h5 class="card-title fw-bold mb-2 text-dark">{{ $info->judul }}</h5>
                        <p class="card-text text-muted text-sm mb-3 flex-grow-1">{{ Str::limit(strip_tags($info->isi ?? $info->konten), 120) }}</p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                            <div class="text-sm text-muted">
                                <i class="bi bi-person me-1"></i> {{ $info->penulis?->name ?? 'Admin' }}
                            </div>
                            <a href="{{ route('informasi.show', $info->id) }}" class="btn btn-sm btn-light text-primary rounded-pill px-3 border">
                                Baca Detail <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    @can('update', $info)
                    <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-4 d-flex gap-2">
                        <a href="{{ route('informasi.edit', $info->id) }}" class="btn btn-sm btn-outline-secondary flex-grow-1 rounded-pill"><i class="bi bi-pencil me-1"></i> Edit</a>
                        <form action="{{ route('informasi.destroy', $info->id) }}" method="POST" class="flex-grow-1">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-sm btn-outline-danger w-100 rounded-pill"
                                data-confirm-delete
                                data-delete-title="Hapus Informasi Desa"
                                data-delete-message="Apakah Anda yakin ingin menghapus informasi atau pengumuman desa ini?"
                                data-delete-detail="<strong>Judul:</strong> {{ $info->judul }}<br><strong>Kategori:</strong> {{ ucfirst($info->kategori) }}<br><strong>Penulis:</strong> {{ $info->penulis?->name ?? '-' }}">
                                <i class="bi bi-trash me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                    @endcan
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5 text-muted bg-light rounded-4">
                    <i class="bi bi-inbox fs-1 mb-3 d-block opacity-50"></i>
                    <h6 class="font-medium mb-1">Belum Ada Informasi</h6>
                    <p class="text-sm">Belum ada informasi/pengumuman yang tersedia sesuai hak akses wilayah Anda saat ini.</p>
                </div>
            </div>
            @endforelse
        </div>
        
        <div class="mt-4">
            {{ $informasis->links() }}
        </div>
    </div>

    <style>
        .hover-shadow:hover { transform: translateY(-4px); box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.08)!important; }
    </style>
</x-app-layout>
