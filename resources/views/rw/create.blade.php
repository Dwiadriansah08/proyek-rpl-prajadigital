<x-app-layout>
    <x-slot name="header">
        Tambah RW Baru
    </x-slot>

    <div class="card p-4">
        <h5 class="mb-4 font-semibold">Formulir Tambah RW</h5>
        
        <form action="{{ route('rw.store') }}" method="POST">
            @csrf
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Desa / Kelurahan <span class="text-danger">*</span></label>
                    <select name="desa_id" class="form-select @error('desa_id') is-invalid @enderror" required>
                        @foreach($desas as $desa)
                            <option value="{{ $desa->id }}" {{ old('desa_id') == $desa->id ? 'selected' : '' }}>{{ $desa->nama }}</option>
                        @endforeach
                    </select>
                    @error('desa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor RW (contoh: 01) <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_rw" class="form-control @error('nomor_rw') is-invalid @enderror" value="{{ old('nomor_rw') }}" required>
                    @error('nomor_rw')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">Nama Ketua RW <span class="text-danger">*</span></label>
                    <input type="text" name="ketua_rw" class="form-control @error('ketua_rw') is-invalid @enderror" value="{{ old('ketua_rw') }}" required>
                    @error('ketua_rw')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Nomor Kontak / WhatsApp</label>
                    <input type="text" name="kontak" class="form-control @error('kontak') is-invalid @enderror" value="{{ old('kontak') }}" placeholder="08xxxxxxxxxx">
                    @error('kontak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <!-- Section Akun Login Ketua RW -->
                <div class="col-md-12 mt-4 pt-3 border-top">
                    <h6 class="font-semibold mb-1 text-primary"><i class="bi bi-shield-lock me-2"></i>Akun Login Ketua RW</h6>
                    <small class="text-muted">Akun login untuk Ketua RW ini agar dapat mengelola data dan menyetujui pengajuan surat di wilayahnya.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Login <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="rw@desa.com" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Username (Opsional)</label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="rw01">
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password" id="rw_password" class="form-control @error('password') is-invalid @enderror" minlength="8" required placeholder="Minimal 8 karakter" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="rw_password" aria-label="Tampilkan password" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="rw_password_confirmation" class="form-control" minlength="8" required placeholder="Ulangi password" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="rw_password_confirmation" aria-label="Tampilkan konfirmasi password" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                <a href="{{ route('rw.index') }}" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary px-4">Simpan RW & Akun</button>
            </div>
        </form>
    </div>
</x-app-layout>
