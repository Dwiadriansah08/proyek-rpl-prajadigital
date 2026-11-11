<x-app-layout>
    <x-slot name="header">
        Profil Desa
    </x-slot>

    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 font-semibold">Profil Desa / Kelurahan</h5>
            <a href="{{ route('desa.edit', $desa->id) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-pencil-square me-1"></i> Edit Profil
            </a>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Nama Desa / Kelurahan</label>
                <div class="font-medium fs-5">{{ $desa->nama }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Kecamatan</label>
                <div class="font-medium fs-5">{{ $desa->kecamatan }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Kabupaten / Kota</label>
                <div class="font-medium fs-5">{{ $desa->kabupaten_kota }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Provinsi</label>
                <div class="font-medium fs-5">{{ $desa->provinsi }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
