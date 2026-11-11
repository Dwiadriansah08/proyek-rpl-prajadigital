<x-app-layout>
    <x-slot name="header">
        Buat Informasi / Pengumuman
    </x-slot>

    <div class="card p-4" style="max-width: 860px; border-radius: 18px;">
        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
            <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary" style="width: 48px; height: 48px;">
                <i class="bi bi-megaphone-fill fs-4"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold">Formulir Pengumuman Baru</h5>
                <p class="text-muted small mb-0">Tentukan sasaran target penerima dan isi pengumuman desa</p>
            </div>
        </div>
        
        <form action="{{ route('informasi.store') }}" method="POST" id="formInformasi">
            @csrf
            
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Judul Pengumuman <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul') }}" placeholder="Contoh: Jadwal Posyandu Balita & Lansia" required>
                    @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                        <option value="Pengumuman" {{ old('kategori') == 'Pengumuman' ? 'selected' : '' }}>Pengumuman</option>
                        <option value="Kegiatan" {{ old('kategori') == 'Kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                        <option value="Berita" {{ old('kategori') == 'Berita' ? 'selected' : '' }}>Berita</option>
                        <option value="Jadwal" {{ old('kategori') == 'Jadwal' ? 'selected' : '' }}>Jadwal</option>
                        <option value="Lainnya" {{ old('kategori') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Section Target Sasaran Pengumuman --}}
                <div class="col-12 mt-4 pt-3 border-top">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-crosshair2 text-primary fs-5"></i>
                        <h6 class="mb-0 fw-bold">Target Penerima Pengumuman</h6>
                    </div>
                    <p class="text-muted small mb-3">Pilih cakupan penerima yang berhak melihat dan menerima informasi ini.</p>
                    
                    @php
                        $user = auth()->user();
                    @endphp

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cakupan Sasaran <span class="text-danger">*</span></label>
                            <select name="target_type" id="targetTypeSelect" class="form-select @error('target_type') is-invalid @enderror" required>
                                @if($user->hasRole('super_admin'))
                                    <option value="desa" {{ old('target_type') == 'desa' ? 'selected' : '' }}>🌐 Seluruh Warga Desa (Satu Desa)</option>
                                    <option value="rw" {{ old('target_type') == 'rw' ? 'selected' : '' }}>🏢 RW Tertentu</option>
                                    <option value="rt" {{ old('target_type') == 'rt' ? 'selected' : '' }}>🏡 RT Tertentu (Pilih RW Dulu)</option>
                                    <option value="role" {{ old('target_type') == 'role' ? 'selected' : '' }}>🛡️ Role Pengguna Tertentu</option>
                                @elseif($user->hasRole('rw'))
                                    <option value="rw" {{ old('target_type') == 'rw' ? 'selected' : '' }}>🏢 Seluruh Warga RW {{ $user->rw?->nomor_rw }}</option>
                                    <option value="rt" {{ old('target_type') == 'rt' ? 'selected' : '' }}>🏡 RT Tertentu (Di bawah RW {{ $user->rw?->nomor_rw }})</option>
                                @else
                                    <option value="rt" selected>🏡 Warga RT {{ $user->rt?->nomor_rt }} / RW {{ $user->rw?->nomor_rw }}</option>
                                @endif
                            </select>
                            @error('target_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Dropdown Pilihan RW --}}
                        <div class="col-md-3" id="wrapperRw" style="display: none;">
                            <label class="form-label fw-semibold">Pilih RW <span class="text-danger">*</span></label>
                            <select name="rw_id" id="rwSelect" class="form-select @error('rw_id') is-invalid @enderror">
                                <option value="">-- Pilih RW --</option>
                                @foreach($rws as $rw)
                                    <option value="{{ $rw->id }}" {{ old('rw_id') == $rw->id ? 'selected' : '' }}>RW {{ $rw->nomor_rw }} ({{ $rw->ketua_rw ?? 'Ketua RW' }})</option>
                                @endforeach
                            </select>
                            @error('rw_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Dropdown Pilihan RT --}}
                        <div class="col-md-3" id="wrapperRt" style="display: none;">
                            <label class="form-label fw-semibold">Pilih RT <span class="text-danger">*</span></label>
                            <select name="rt_id" id="rtSelect" class="form-select @error('rt_id') is-invalid @enderror">
                                <option value="">-- Pilih RT --</option>
                                @foreach($rts as $rt)
                                    <option value="{{ $rt->id }}" data-rw="{{ $rt->rw_id }}" {{ old('rt_id') == $rt->id ? 'selected' : '' }}>RT {{ $rt->nomor_rt }} (RW {{ $rt->rw?->nomor_rw }})</option>
                                @endforeach
                            </select>
                            @error('rt_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Dropdown Pilihan Role --}}
                        @if($user->hasRole('super_admin'))
                        <div class="col-md-6" id="wrapperRole" style="display: none;">
                            <label class="form-label fw-semibold">Pilih Role Pengguna <span class="text-danger">*</span></label>
                            <select name="target_role" id="roleSelect" class="form-select @error('target_role') is-invalid @enderror">
                                <option value="">-- Pilih Role Target --</option>
                                <option value="super_admin" {{ old('target_role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="rw" {{ old('target_role') == 'rw' ? 'selected' : '' }}>Ketua RW</option>
                                <option value="rt" {{ old('target_role') == 'rt' ? 'selected' : '' }}>Ketua RT</option>
                                <option value="warga" {{ old('target_role') == 'warga' ? 'selected' : '' }}>Warga Biasa</option>
                            </select>
                            @error('target_role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="col-12 mt-3">
                    <label class="form-label fw-semibold">Isi / Konten Pengumuman <span class="text-danger">*</span></label>
                    <textarea name="isi" class="form-control @error('isi') is-invalid @enderror" rows="7" placeholder="Tuliskan isi pengumuman lengkap secara jelas..." required>{{ old('isi', old('konten')) }}</textarea>
                    @error('isi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="col-12 mt-3">
                    <div class="p-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold text-dark">Status Publikasi Langsung</div>
                            <small class="text-muted">Jika diaktifkan, pengumuman langsung dapat dilihat target sasaran dan dapat disiarkan melalui WhatsApp.</small>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input fs-5" type="checkbox" role="switch" name="status_publikasi" id="statusPublikasi" value="1" checked>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                <a href="{{ route('informasi.index') }}" class="btn btn-light px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Simpan Pengumuman
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const targetType = document.getElementById('targetTypeSelect');
            const wrapperRw = document.getElementById('wrapperRw');
            const wrapperRt = document.getElementById('wrapperRt');
            const wrapperRole = document.getElementById('wrapperRole');
            const rwSelect = document.getElementById('rwSelect');
            const rtSelect = document.getElementById('rtSelect');

            function filterRtOptions(selectedRwId) {
                if (!rtSelect) return;
                const options = rtSelect.querySelectorAll('option');
                options.forEach(opt => {
                    if (!opt.value) return; // skip placeholder
                    const rwAttr = opt.getAttribute('data-rw');
                    if (!selectedRwId || rwAttr === selectedRwId) {
                        opt.style.display = '';
                        opt.disabled = false;
                    } else {
                        opt.style.display = 'none';
                        opt.disabled = true;
                    }
                });
            }

            function updateVisibility() {
                const val = targetType ? targetType.value : '';
                
                if (wrapperRw) wrapperRw.style.display = (val === 'rw' || val === 'rt') ? 'block' : 'none';
                if (wrapperRt) wrapperRt.style.display = (val === 'rt') ? 'block' : 'none';
                if (wrapperRole) wrapperRole.style.display = (val === 'role') ? 'block' : 'none';

                if (rwSelect && val === 'rt') {
                    filterRtOptions(rwSelect.value);
                }
            }

            if (targetType) {
                targetType.addEventListener('change', updateVisibility);
            }
            if (rwSelect) {
                rwSelect.addEventListener('change', function() {
                    filterRtOptions(this.value);
                });
            }

            updateVisibility();
        });
    </script>
    @endpush
</x-app-layout>
