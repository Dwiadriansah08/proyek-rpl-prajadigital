<x-app-layout>
    <x-slot name="header">
        Edit Profil Desa
    </x-slot>

    <div class="card p-4">
        <h5 class="mb-4 font-semibold">Formulir Edit Profil Desa</h5>
        
        <form action="{{ route('desa.update', $desa->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Desa / Kelurahan <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $desa->nama) }}" required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kecamatan <span class="text-danger">*</span></label>
                    <input type="text" name="kecamatan" class="form-control @error('kecamatan') is-invalid @enderror" value="{{ old('kecamatan', $desa->kecamatan) }}" required>
                    @error('kecamatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kabupaten / Kota <span class="text-danger">*</span></label>
                    <input type="text" name="kabupaten_kota" class="form-control @error('kabupaten_kota') is-invalid @enderror" value="{{ old('kabupaten_kota', $desa->kabupaten_kota) }}" required>
                    @error('kabupaten_kota')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Provinsi <span class="text-danger">*</span></label>
                    <input type="text" name="provinsi" class="form-control @error('provinsi') is-invalid @enderror" value="{{ old('provinsi', $desa->provinsi) }}" required>
                    @error('provinsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                <a href="{{ route('desa.index') }}" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-app-layout>
