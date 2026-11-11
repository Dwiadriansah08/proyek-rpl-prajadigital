<x-app-layout>
    <x-slot name="header">
        Edit Data RW
    </x-slot>

    <div class="card p-4" style="max-width: 720px;">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:48px;height:48px;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;font-size:1.2rem;">
                <i class="bi bi-diagram-3"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold">Edit Data RW</h5>
                <small class="text-muted">RW {{ $rw->nomor_rw }} — {{ $rw->ketua_rw }}</small>
            </div>
        </div>

        <form action="{{ route('rw.update', $rw->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Desa / Kelurahan <span class="text-danger">*</span></label>
                    <select name="desa_id" class="form-select @error('desa_id') is-invalid @enderror" required>
                        @foreach($desas as $desa)
                            <option value="{{ $desa->id }}" {{ old('desa_id', $rw->desa_id) == $desa->id ? 'selected' : '' }}>{{ $desa->nama }}</option>
                        @endforeach
                    </select>
                    @error('desa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nomor RW <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_rw" class="form-control @error('nomor_rw') is-invalid @enderror"
                           value="{{ old('nomor_rw', $rw->nomor_rw) }}" placeholder="contoh: 01" required>
                    @error('nomor_rw')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Nama Ketua RW <span class="text-danger">*</span></label>
                    <input type="text" name="ketua_rw" class="form-control @error('ketua_rw') is-invalid @enderror"
                           value="{{ old('ketua_rw', $rw->ketua_rw) }}" required>
                    @error('ketua_rw')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Nomor Kontak / WhatsApp</label>
                    <input type="text" name="kontak" class="form-control @error('kontak') is-invalid @enderror"
                           value="{{ old('kontak', $rw->kontak) }}" placeholder="08xxxxxxxxxx">
                    @error('kontak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                <a href="{{ route('rw.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-1"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
