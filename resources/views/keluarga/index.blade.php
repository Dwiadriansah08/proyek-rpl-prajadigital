<x-app-layout>
    <x-slot name="header">
        Manajemen Kartu Keluarga (KK)
    </x-slot>

    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 font-semibold">Daftar Kartu Keluarga</h5>
            <a href="{{ route('keluarga.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-circle me-1"></i> Tambah KK Baru
            </a>
        </div>

        <form action="{{ route('keluarga.index') }}" method="GET" class="mb-4">
            <div class="input-group" style="max-width: 400px;">
                <input type="text" name="search" class="form-control" placeholder="Cari No. KK / Kepala Keluarga..." value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Cari</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nomor KK</th>
                        <th>Kepala Keluarga</th>
                        <th>Wilayah (RT/RW)</th>
                        <th>Alamat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($keluargas as $kk)
                    <tr>
                        <td class="font-monospace text-primary">{{ $kk->nomor_kk }}</td>
                        <td class="font-medium">{{ $kk->kepala_keluarga }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">RT {{ $kk->rt->nomor_rt }} / RW {{ $kk->rw->nomor_rw }}</span>
                        </td>
                        <td>{{ Str::limit($kk->alamat, 30) }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('keluarga.show', $kk->id) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('keluarga.edit', $kk->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('keluarga.destroy', $kk->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-confirm-delete
                                    data-delete-title="Hapus Kartu Keluarga"
                                    data-delete-message="Apakah Anda yakin ingin menghapus data Kartu Keluarga ini? Warga yang terikat dengan KK ini akan kehilangan relasi keluarganya."
                                    data-delete-detail="<strong>No. KK:</strong> {{ $kk->nomor_kk }}<br><strong>Kepala Keluarga:</strong> {{ $kk->kepala_keluarga }}<br><strong>Wilayah:</strong> RT {{ $kk->rt?->nomor_rt ?? '-' }} / RW {{ $kk->rw?->nomor_rw ?? '-' }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data Kartu Keluarga.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $keluargas->links() }}
        </div>
    </div>
</x-app-layout>
