<x-app-layout>
    <x-slot name="header">
        {{ auth()->user()->warga && auth()->user()->warga->id == $warga->id ? 'Profil Saya' : 'Detail Penduduk' }}
    </x-slot>

    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
            <div class="d-flex align-items-center mb-3 mb-md-0">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-person-circle" style="font-size: 2rem;"></i>
                </div>
                <div>
                    <h5 class="mb-0 font-semibold">{{ $warga->nama_lengkap }}</h5>
                    <div class="text-muted text-sm font-monospace">{{ $warga->nik }}</div>
                </div>
            </div>
            <div>
                @can('update', $warga)
                    <a href="{{ route('warga.edit', $warga->id) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm me-2">
                        <i class="bi bi-pencil-square me-1"></i> Edit Data
                    </a>
                @endcan
                <a href="{{ auth()->user()->hasRole('warga') ? route('dashboard') : route('warga.index') }}" class="btn btn-light btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
        
        <h6 class="font-semibold text-primary mb-3"><i class="bi bi-file-person me-2"></i>Data Pribadi</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Nama Lengkap</div>
                    <div class="font-medium text-gray-900">{{ $warga->nama_lengkap }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Tempat, Tanggal Lahir</div>
                    <div class="font-medium text-gray-900">{{ $warga->tempat_lahir ?? '-' }}, {{ $warga->tanggal_lahir ? \Carbon\Carbon::parse($warga->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Jenis Kelamin</div>
                    <div class="font-medium text-gray-900">{{ $warga->jenis_kelamin }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Agama</div>
                    <div class="font-medium text-gray-900">{{ $warga->agama }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Status Perkawinan</div>
                    <div class="font-medium text-gray-900">{{ $warga->status_perkawinan }}</div>
                </div>
            </div>
        </div>

        <h6 class="font-semibold text-primary mb-3 mt-4"><i class="bi bi-house-door me-2"></i>Data Kependudukan & Wilayah</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">NIK</div>
                    <div class="font-medium text-gray-900 font-monospace">{{ $warga->nik }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Nomor KK</div>
                    <div class="font-medium text-gray-900 font-monospace">{{ $warga->nomor_kk }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Status Dalam Keluarga</div>
                    <div class="font-medium text-gray-900">{{ $warga->status_dalam_keluarga }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Wilayah</div>
                    <div class="font-medium text-gray-900">RW {{ $warga->rw?->nomor_rw }} / RT {{ $warga->rt?->nomor_rt }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Status Kependudukan</div>
                    <div class="font-medium text-gray-900">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill fw-normal">{{ $warga->status_kependudukan }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Alamat Lengkap</div>
                    <div class="font-medium text-gray-900">{{ $warga->alamat ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
