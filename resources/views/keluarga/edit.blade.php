<x-app-layout>
    <x-slot name="header">
        Edit Kartu Keluarga
    </x-slot>

    <div class="card p-4">
        <h5 class="mb-4 font-semibold">Formulir Edit KK</h5>
        
        <form action="{{ route('keluarga.update', $keluarga->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nomor KK <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_kk" class="form-control @error('nomor_kk') is-invalid @enderror" value="{{ old('nomor_kk', $keluarga->nomor_kk) }}" required>
                    @error('nomor_kk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kepala Keluarga <span class="text-danger">*</span></label>
                    <input type="text" name="kepala_keluarga" class="form-control @error('kepala_keluarga') is-invalid @enderror" value="{{ old('kepala_keluarga', $keluarga->kepala_keluarga) }}" required>
                    @error('kepala_keluarga')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="col-12">
                    <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2" required>{{ old('alamat', $keluarga->alamat) }}</textarea>
                    @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                <a href="{{ route('keluarga.index') }}" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-app-layout>
