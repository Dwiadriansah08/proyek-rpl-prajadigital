<x-app-layout>
    <x-slot name="header">
        Edit Data RT
    </x-slot>

    <div class="card p-4" style="max-width: 720px;">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:48px;height:48px;background:linear-gradient(135deg,#0891B2,#06B6D4);color:#fff;font-size:1.2rem;">
                <i class="bi bi-signpost-split"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold">Edit Data RT</h5>
                <small class="text-muted">RT {{ $rt->nomor_rt }} — {{ $rt->ketua_rt }}</small>
            </div>
        </div>

        <form action="{{ route('rt.update', $rt->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">RW Induk <span class="text-danger">*</span></label>
                    <select name="rw_id" class="form-select @error('rw_id') is-invalid @enderror" required>
                        @foreach($rws as $rw)
                            <option value="{{ $rw->id }}" {{ old('rw_id', $rt->rw_id) == $rw->id ? 'selected' : '' }}>
                                RW {{ $rw->nomor_rw }}{{ $rw->ketua_rw ? ' — '.$rw->ketua_rw : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('rw_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nomor RT <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_rt" class="form-control @error('nomor_rt') is-invalid @enderror"
                           value="{{ old('nomor_rt', $rt->nomor_rt) }}" placeholder="contoh: 01" required>
                    @error('nomor_rt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Nama Ketua RT <span class="text-danger">*</span></label>
                    <input type="text" name="ketua_rt" class="form-control @error('ketua_rt') is-invalid @enderror"
                           value="{{ old('ketua_rt', $rt->ketua_rt) }}" required>
                    @error('ketua_rt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Nomor Kontak / WhatsApp</label>
                    <input type="text" name="kontak" class="form-control @error('kontak') is-invalid @enderror"
                           value="{{ old('kontak', $rt->kontak) }}" placeholder="08xxxxxxxxxx">
                    @error('kontak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                <a href="{{ route('rt.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-1"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
