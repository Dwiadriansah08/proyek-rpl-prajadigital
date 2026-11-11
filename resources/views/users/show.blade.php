<x-app-layout>
    <x-slot name="header">
        Detail User
    </x-slot>

    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 font-semibold">Informasi Pengguna</h5>
            <div>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-sm ms-2"><i class="bi bi-pencil-square"></i> Edit</a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="text-sm text-gray-500 mb-1">Nama Lengkap</div>
                <div class="font-medium fs-5">{{ $user->name }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="text-sm text-gray-500 mb-1">Email</div>
                <div class="font-medium fs-5">{{ $user->email }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="text-sm text-gray-500 mb-1">Nomor Telepon (WhatsApp)</div>
                <div class="font-medium fs-5 font-monospace">
                    {{ $user->nomor_telepon ?? ($user->warga?->nomor_telepon ?? '-') }}
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="text-sm text-gray-500 mb-1">Role Sistem</div>
                <div class="font-medium">
                    <span class="badge bg-primary fs-6">{{ strtoupper($user->role->name ?? 'GUEST') }}</span>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="text-sm text-gray-500 mb-1">Status Akun</div>
                <div class="font-medium">
                    @if($user->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-danger">Nonaktif</span>
                    @endif
                </div>
            </div>
            
            <div class="col-12 mt-4 pt-3 border-top">
                <h6 class="font-semibold mb-3">Lingkup Wilayah (Berdasarkan Role)</h6>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="text-sm text-gray-500 mb-1">Desa</div>
                <div class="font-medium">{{ $user->desa ? $user->desa->nama : 'Semua Desa' }}</div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="text-sm text-gray-500 mb-1">RW</div>
                <div class="font-medium">{{ $user->rw ? 'RW ' . $user->rw->nomor_rw : 'Semua RW' }}</div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="text-sm text-gray-500 mb-1">RT</div>
                <div class="font-medium">{{ $user->rt ? 'RT ' . $user->rt->nomor_rt : 'Semua RT' }}</div>
            </div>

            @if($user->warga)
            <div class="col-12 mt-4 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-semibold text-primary mb-0"><i class="bi bi-file-person me-2"></i>Data Kependudukan Terkait</h6>
                    <a href="{{ route('warga.edit', $user->warga->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="bi bi-pencil me-1"></i> Edit Data Penduduk
                    </a>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="text-sm text-gray-500 mb-1">Nomor Induk Kependudukan (NIK)</div>
                <div class="font-medium font-monospace">{{ $user->warga->nik }}</div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="text-sm text-gray-500 mb-1">Nomor Kartu Keluarga (KK)</div>
                <div class="font-medium font-monospace">{{ $user->warga->nomor_kk }}</div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="text-sm text-gray-500 mb-1">Jenis Kelamin</div>
                <div class="font-medium">{{ $user->warga->jenis_kelamin }}</div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="text-sm text-gray-500 mb-1">Tempat, Tanggal Lahir</div>
                <div class="font-medium">{{ $user->warga->tempat_lahir ?? '-' }}, {{ $user->warga->tanggal_lahir ? \Carbon\Carbon::parse($user->warga->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="text-sm text-gray-500 mb-1">Status Perkawinan</div>
                <div class="font-medium">{{ $user->warga->status_perkawinan ?? '-' }}</div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="text-sm text-gray-500 mb-1">Pekerjaan</div>
                <div class="font-medium">{{ $user->warga->pekerjaan ?? '-' }}</div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="text-sm text-gray-500 mb-1">Nomor Telepon</div>
                <div class="font-medium">{{ $user->warga->nomor_telepon ?? '-' }}</div>
            </div>
            <div class="col-md-8 mb-3">
                <div class="text-sm text-gray-500 mb-1">Alamat Domisili</div>
                <div class="font-medium">{{ $user->warga->alamat ?? '-' }} (RT {{ $user->warga->rt?->nomor_rt ?? '-' }} / RW {{ $user->warga->rw?->nomor_rw ?? '-' }})</div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
