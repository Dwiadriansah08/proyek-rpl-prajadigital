<x-app-layout>
    <x-slot name="header">
        Layanan Pengajuan Surat
    </x-slot>

    <div class="mb-4">
        <h5 class="font-semibold mb-1">Pilih Jenis Surat</h5>
        <p class="text-muted text-sm">Pilih jenis surat yang ingin Anda ajukan dari daftar layanan kami di bawah ini.</p>
    </div>

    <!-- Katalog Surat -->
    <div class="row g-4 mb-5">
        @forelse($templates as $t)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-shadow transition" style="transition: all 0.3s;">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-file-earmark-text fs-3"></i>
                    </div>
                    <h5 class="card-title font-semibold mb-2">{{ $t->nama_surat }}</h5>
                    <p class="card-text text-muted text-sm mb-4 flex-grow-1">{{ $t->deskripsi ?? 'Pengajuan untuk ' . $t->nama_surat }}</p>
                    <button type="button" class="btn btn-outline-primary w-100 rounded-pill mt-auto" onclick="selectSurat({{ $t->id }}, '{{ $t->nama_surat }}')">
                        Pilih Surat Ini
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                <h6 class="text-muted font-medium mb-0">Belum Ada Layanan Surat</h6>
                <p class="text-sm text-gray-500">Saat ini belum ada template surat yang aktif.</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Form Pengajuan (Hidden by default) -->
    <div id="formPengajuanContainer" class="card border-0 shadow-sm rounded-4 p-4 mb-5" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <h5 class="mb-0 font-semibold text-primary">
                <i class="bi bi-pencil-square me-2"></i> Formulir Pengajuan: <span id="selectedSuratName" class="text-dark"></span>
            </h5>
            <button type="button" class="btn-close" aria-label="Close" onclick="closeForm()"></button>
        </div>
        
        <form action="{{ route('surat.store') }}" method="POST">
            @csrf
            <input type="hidden" name="template_id" id="template_id" value="" required>
            
            <div class="row g-4">
                <div class="col-md-12">
                    <label class="form-label font-medium text-gray-700">Keperluan <span class="text-danger">*</span></label>
                    <textarea name="keperluan" class="form-control bg-light border-0 @error('keperluan') is-invalid @enderror" rows="4" required placeholder="Jelaskan secara detail keperluan pembuatan surat ini agar mudah diproses oleh RT/RW...">{{ old('keperluan') }}</textarea>
                    <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i> Mohon isi dengan jelas (misal: "Untuk persyaratan pembuatan KTP baru" atau "Untuk melamar pekerjaan").</div>
                    @error('keperluan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div class="mt-4 pt-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light rounded-pill px-4" onclick="closeForm()">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-send me-1"></i> Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>

    <script>
        function selectSurat(id, nama) {
            document.getElementById('template_id').value = id;
            document.getElementById('selectedSuratName').textContent = nama;
            
            var formContainer = document.getElementById('formPengajuanContainer');
            formContainer.style.display = 'block';
            
            // Scroll to form
            formContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Focus on keperluan
            setTimeout(function() {
                document.querySelector('textarea[name="keperluan"]').focus();
            }, 500);
        }

        function closeForm() {
            document.getElementById('formPengajuanContainer').style.display = 'none';
            document.getElementById('template_id').value = '';
            document.querySelector('textarea[name="keperluan"]').value = '';
        }

        // Auto open if validation failed previously
        @if($errors->any() && old('template_id'))
            document.addEventListener('DOMContentLoaded', function() {
                var oldId = {{ old('template_id') }};
                var name = "Surat Terpilih";
                @foreach($templates as $t)
                    if(oldId == {{ $t->id }}) name = "{{ $t->nama_surat }}";
                @endforeach
                selectSurat(oldId, name);
            });
        @endif
    </script>
    <style>
        .hover-shadow:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    </style>
</x-app-layout>
