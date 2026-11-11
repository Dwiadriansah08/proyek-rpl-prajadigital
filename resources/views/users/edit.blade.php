<x-app-layout>
    <x-slot name="header">
        Edit User
    </x-slot>

    <div class="card p-4">
        <h5 class="mb-4 font-semibold">Formulir Edit User</h5>
        
        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor Telepon (WhatsApp)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </span>
                        <input type="text" name="nomor_telepon" class="form-control @error('nomor_telepon') is-invalid @enderror" value="{{ old('nomor_telepon', $user->nomor_telepon ?? $user->warga?->nomor_telepon) }}" placeholder="08xxxxxxxxxx">
                    </div>
                    @error('nomor_telepon')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username (Opsional)</label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" placeholder="username">
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Password Baru <span class="text-muted">(Kosongkan jika tidak diganti)</span></label>
                    <div class="input-group">
                        <input type="password" name="password" id="edit_user_password" class="form-control @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="edit_user_password" aria-label="Tampilkan password" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="edit_user_password_confirmation" class="form-control" minlength="8" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="edit_user_password_confirmation" aria-label="Tampilkan konfirmasi password" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="col-md-12 mt-4 pt-3 border-top">
                    <h6 class="font-semibold mb-3">Pengaturan Akses</h6>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ strtoupper($role->name) }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status Akun</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="isActive" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Aktif (Bisa Login)</label>
                    </div>
                </div>
                
                <div class="col-md-6" id="field_status_keluarga" style="display: none;">
                    <label class="form-label">Status dalam Keluarga</label>
                    <select name="status_dalam_keluarga" class="form-select">
                        @php $currentStatus = old('status_dalam_keluarga', $user->warga?->status_dalam_keluarga ?? 'Kepala Keluarga'); @endphp
                        <option value="Kepala Keluarga" {{ $currentStatus == 'Kepala Keluarga' ? 'selected' : '' }}>Kepala Keluarga</option>
                        <option value="Suami" {{ $currentStatus == 'Suami' ? 'selected' : '' }}>Suami</option>
                        <option value="Istri" {{ $currentStatus == 'Istri' ? 'selected' : '' }}>Istri</option>
                        <option value="Anak" {{ $currentStatus == 'Anak' ? 'selected' : '' }}>Anak</option>
                        <option value="Lainnya" {{ $currentStatus == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    <small class="text-muted">Menentukan metrik Kepala Keluarga pada statistik Dashboard</small>
                </div>
                <div class="col-md-6" id="field_jenis_kelamin" style="display: none;">
                    <label class="form-label">Jenis Kelamin</label>
                    @php $currentGender = old('jenis_kelamin', $user->warga?->jenis_kelamin ?? 'Laki-laki'); @endphp
                    <select name="jenis_kelamin" class="form-select">
                        <option value="Laki-laki" {{ $currentGender == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ $currentGender == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Akses Wilayah RW (Opsional)</label>
                    <select name="rw_id" class="form-select">
                        <option value="">Semua Wilayah</option>
                        @foreach($rws as $rw)
                            <option value="{{ $rw->id }}" {{ old('rw_id', $user->rw_id ?? $user->warga?->rw_id) == $rw->id ? 'selected' : '' }}>RW {{ $rw->nomor_rw }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Pilih jika role adalah Ketua RW/RT atau Warga wilayah</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Akses Wilayah RT (Opsional)</label>
                    <select name="rt_id" class="form-select">
                        <option value="">Semua Wilayah</option>
                        @foreach($rts as $rt)
                            <option value="{{ $rt->id }}" {{ old('rt_id', $user->rt_id ?? $user->warga?->rt_id) == $rt->id ? 'selected' : '' }}>RT {{ $rt->nomor_rt }} (RW {{ $rt->rw->nomor_rw }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Pilih jika role adalah Ketua RT atau Warga wilayah</small>
                </div>
                
            </div>
            
            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                <a href="{{ route('users.index') }}" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.querySelector('select[name="role_id"]');
        const fieldKeluarga = document.getElementById('field_status_keluarga');
        const fieldGender = document.getElementById('field_jenis_kelamin');

        function checkRole() {
            if (!roleSelect) return;
            const selectedText = roleSelect.options[roleSelect.selectedIndex]?.text?.toLowerCase() || '';
            if (selectedText.includes('warga')) {
                if (fieldKeluarga) fieldKeluarga.style.display = 'block';
                if (fieldGender) fieldGender.style.display = 'block';
            } else {
                if (fieldKeluarga) fieldKeluarga.style.display = 'none';
                if (fieldGender) fieldGender.style.display = 'none';
            }
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', checkRole);
            checkRole();
        }
    });
    </script>
</x-app-layout>
