<x-app-layout>
    <x-slot name="header">
        Tambah RT Baru
    </x-slot>

    <div class="card p-4">
        <h5 class="mb-4 font-semibold">Formulir Tambah RT</h5>
        
        <form action="{{ route('rt.store') }}" method="POST">
            @csrf
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">RW (Induk) <span class="text-danger">*</span></label>
                    <select name="rw_id" class="form-select @error('rw_id') is-invalid @enderror" required>
                        @foreach($rws as $rw)
                            <option value="{{ $rw->id }}" {{ old('rw_id') == $rw->id ? 'selected' : '' }}>RW {{ $rw->nomor_rw }}</option>
                        @endforeach
                    </select>
                    @error('rw_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor RT (contoh: 01) <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_rt" class="form-control @error('nomor_rt') is-invalid @enderror" value="{{ old('nomor_rt') }}" required>
                    @error('nomor_rt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">Nama Ketua RT <span class="text-danger">*</span></label>
                    <input type="text" name="ketua_rt" class="form-control @error('ketua_rt') is-invalid @enderror" value="{{ old('ketua_rt') }}" required>
                    @error('ketua_rt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Nomor Kontak / WhatsApp</label>
                    <input type="text" name="kontak" class="form-control @error('kontak') is-invalid @enderror" value="{{ old('kontak') }}" placeholder="08xxxxxxxxxx">
                    @error('kontak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <!-- Section Akun Login Ketua RT -->
                <div class="col-md-12 mt-4 pt-3 border-top">
                    <h6 class="font-semibold mb-1 text-primary"><i class="bi bi-shield-lock me-2"></i>Akun Login Ketua RT</h6>
                    <small class="text-muted">Akun login untuk Ketua RT ini agar dapat mengelola data dan memverifikasi pengajuan surat di wilayahnya.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Login <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="rt@desa.com" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Username (Opsional)</label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="rt01_rw01">
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password" id="rt_password" class="form-control @error('password') is-invalid @enderror" minlength="8" required placeholder="Minimal 8 karakter" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="rt_password" aria-label="Tampilkan password" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="rt_password_confirmation" class="form-control" minlength="8" required placeholder="Ulangi password" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="rt_password_confirmation" aria-label="Tampilkan konfirmasi password" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                <a href="{{ route('rt.index') }}" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary px-4">Simpan RT & Akun</button>
            </div>
        </form>
    </div>
</x-app-layout>
