<x-app-layout>
    <x-slot name="header">
        Detail Kartu Keluarga
    </x-slot>

    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 font-semibold">Informasi Kartu Keluarga</h5>
            <div>
                <a href="{{ route('keluarga.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
                <a href="{{ route('keluarga.edit', $keluarga->id) }}" class="btn btn-primary btn-sm ms-2"><i class="bi bi-pencil-square"></i> Edit</a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="text-sm text-gray-500 mb-1">Nomor Kartu Keluarga (KK)</div>
                <div class="font-monospace fs-5 text-primary">{{ $keluarga->nomor_kk }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="text-sm text-gray-500 mb-1">Kepala Keluarga</div>
                <div class="font-medium fs-5">{{ $keluarga->kepala_keluarga }}</div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="text-sm text-gray-500 mb-1">Wilayah</div>
                <div class="font-medium">RT {{ $keluarga->rt->nomor_rt }} / RW {{ $keluarga->rw->nomor_rw }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="text-sm text-gray-500 mb-1">Alamat Lengkap</div>
                <div class="font-medium">{{ $keluarga->alamat }}</div>
            </div>
        </div>
    </div>

    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 font-semibold text-primary">Anggota Keluarga (Warga)</h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>NIK</th>
                        <th>Nama Lengkap</th>
                        <th>L/P</th>
                        <th>Agama</th>
                        <th>Pekerjaan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($keluarga->members as $warga)
                    <tr>
                        <td class="font-monospace text-muted">{{ $warga->nik }}</td>
                        <td class="font-medium">{{ $warga->nama_lengkap }}</td>
                        <td>{{ $warga->jenis_kelamin == 'Laki-laki' ? 'L' : 'P' }}</td>
                        <td>{{ $warga->agama }}</td>
                        <td>{{ $warga->pekerjaan }}</td>
                        <td class="text-end">
                            <a href="{{ route('warga.show', $warga->id) }}" class="btn btn-sm btn-light text-primary"><i class="bi bi-eye"></i> Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-3 text-muted">Belum ada anggota keluarga yang didaftarkan ke KK ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
