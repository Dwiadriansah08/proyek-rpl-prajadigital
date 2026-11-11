<x-app-layout>
    <x-slot name="header">
        Detail Pengajuan Surat
    </x-slot>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
            <h5 class="mb-2 mb-md-0 font-semibold">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i> Informasi Pengajuan
            </h5>
            <div class="d-flex gap-2">
                <a href="{{ route('surat.index') }}" class="btn btn-light btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
                @if($surat->status == 'SELESAI' && $surat->file_pdf)
                    <a href="{{ route('surat.download', $surat->id) }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                        <i class="bi bi-download me-1"></i> Download PDF
                    </a>
                @endif
            </div>
        </div>
        
        {{-- Status Visual Tracker (only for Warga) --}}
        @if(auth()->user()->hasRole('warga'))
        @php
            $steps = [
                'MENUNGGU_RT' => 'Verifikasi RT',
                'MENUNGGU_RW' => 'Verifikasi RW',
                'MENUNGGU_ADMIN_DESA' => 'Verifikasi Desa',
                'SELESAI' => 'Selesai'
            ];
            $currentStatus = $surat->status;
            $isDitolak = $currentStatus === 'DITOLAK';
            $stepKeys = array_keys($steps);
            $currentIdx = array_search($currentStatus, $stepKeys);
            if ($currentIdx === false && !$isDitolak) $currentIdx = -1;
        @endphp
        <div class="mb-4 p-4 bg-light rounded-4">
            <div class="d-flex justify-content-between align-items-center position-relative" style="max-width: 500px; margin: 0 auto;">
                {{-- Progress line --}}
                <div class="position-absolute" style="top: 18px; left: 30px; right: 30px; height: 3px; background: #dee2e6; z-index: 0;"></div>
                @if(!$isDitolak && $currentIdx > 0)
                <div class="position-absolute" style="top: 18px; left: 30px; width: {{ $currentIdx / (count($steps)-1) * 100 }}%; max-width: calc(100% - 60px); height: 3px; background: #198754; z-index: 1;"></div>
                @endif

                @foreach($steps as $key => $label)
                @php
                    $stepIdx = array_search($key, $stepKeys);
                    $isCompleted = !$isDitolak && $currentIdx !== false && $stepIdx <= $currentIdx;
                    $isCurrent = $key === $currentStatus;
                @endphp
                <div class="text-center position-relative" style="z-index: 2;">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mx-auto mb-2 {{ $isDitolak ? 'bg-danger text-white' : ($isCompleted ? 'bg-success text-white' : 'bg-white border text-muted') }}" style="width: 36px; height: 36px; {{ $isCurrent && !$isDitolak ? 'box-shadow: 0 0 0 4px rgba(25,135,84,0.25);' : '' }}">
                        @if($isDitolak && $isCurrent)
                            <i class="bi bi-x-lg" style="font-size: 0.8rem;"></i>
                        @elseif($isCompleted)
                            <i class="bi bi-check-lg" style="font-size: 0.9rem;"></i>
                        @else
                            <span class="fw-bold" style="font-size: 0.75rem;">{{ $stepIdx + 1 }}</span>
                        @endif
                    </div>
                    <div class="text-xs {{ $isCurrent ? 'font-semibold text-dark' : 'text-muted' }}">{{ $label }}</div>
                </div>
                @endforeach
            </div>
            @if($isDitolak)
            <div class="text-center mt-3">
                <span class="badge bg-danger rounded-pill px-3 py-1 fw-normal">Pengajuan Ditolak</span>
            </div>
            @endif
        </div>
        @else
        {{-- Admin/RT/RW: simple badge --}}
        <div class="mb-3">
            @php
                $badgeClass = 'bg-secondary';
                if($surat->status == 'SELESAI') $badgeClass = 'bg-success';
                elseif($surat->status == 'DITOLAK') $badgeClass = 'bg-danger';
                elseif(str_contains($surat->status, 'MENUNGGU')) $badgeClass = 'bg-warning text-dark';
            @endphp
            <span class="text-sm text-gray-500 me-2">Status:</span>
            <span class="badge {{ $badgeClass }} fs-6 rounded-pill px-3 fw-normal">{{ str_replace('_', ' ', $surat->status) }}</span>
        </div>
        @endif
        
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Tanggal Pengajuan</div>
                    <div class="font-medium text-gray-900">{{ $surat->created_at->translatedFormat('d F Y, H:i') }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Jenis Surat</div>
                    <div class="font-medium text-gray-900">{{ $surat->template->nama_surat }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Pemohon</div>
                    <div class="font-medium text-gray-900">{{ $surat->warga->nama_lengkap }}</div>
                    <div class="text-muted text-sm font-monospace">{{ $surat->warga->nik }}</div>
                </div>
            </div>
            @if($surat->nomor_surat)
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 h-100">
                    <div class="text-sm text-gray-500 mb-1">Nomor Surat</div>
                    <div class="font-medium text-gray-900 font-monospace">{{ $surat->nomor_surat }}</div>
                </div>
            </div>
            @endif
            
            <div class="col-12">
                <div class="p-3 bg-light rounded-3">
                    <div class="text-sm text-gray-500 mb-1">Keperluan</div>
                    <div class="font-medium text-gray-900">{{ $surat->data_tambahan['keperluan'] ?? '-' }}</div>
                </div>
            </div>
            
            @if($surat->catatan)
            <div class="col-12">
                <div class="p-3 rounded-3 border border-danger" style="background-color: #fef2f2;">
                    <div class="text-sm text-danger mb-1 font-medium"><i class="bi bi-exclamation-triangle me-1"></i> Catatan Penolakan</div>
                    <div class="text-danger">{{ $surat->catatan }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @can('process', $surat)
        @php
            $canRtProcess = ($surat->status === 'MENUNGGU_RT' && auth()->user()->hasRole('rt'));
            $canRwProcess = ($surat->status === 'MENUNGGU_RW' && auth()->user()->hasRole('rw'));
            $canAdminDesaProcess = ($surat->status === 'MENUNGGU_ADMIN_DESA' && auth()->user()->hasRole(['admin_desa', 'super_admin']));
            $canProcessNow = $canRtProcess || $canRwProcess || $canAdminDesaProcess;
        @endphp

        @if($canProcessNow)
        <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-primary border-4">
            <h5 class="mb-4 font-semibold text-primary">
                <i class="bi bi-clipboard-check me-2"></i>
                @if($canAdminDesaProcess)
                    Verifikasi Akhir & Penerbitan Surat (Kantor Desa)
                @else
                    Proses Pengajuan Surat
                @endif
            </h5>
            
            <form action="{{ route('surat.process', $surat->id) }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label font-medium">Tindakan Persetujuan</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="action" id="actionApprove" value="approve" checked onchange="toggleCatatan()">
                            <label class="form-check-label text-success font-medium" for="actionApprove">
                                <i class="bi bi-check-circle"></i> 
                                @if($canAdminDesaProcess)
                                    Setujui (Terbitkan Surat)
                                @else
                                    Setujui Pengajuan
                                @endif
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="action" id="actionReject" value="reject" onchange="toggleCatatan()">
                            <label class="form-check-label text-danger font-medium" for="actionReject">
                                <i class="bi bi-x-circle"></i> Tolak Pengajuan
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3" id="catatanBox" style="display: none;">
                    <label class="form-label text-danger font-medium">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="catatan" id="catatan" class="form-control bg-light border-0" rows="3" placeholder="Berikan alasan mengapa pengajuan ini ditolak..."></textarea>
                </div>
                
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-send me-1"></i> 
                        @if($canAdminDesaProcess)
                            Terbitkan Surat Resmi
                        @else
                            Submit Proses
                        @endif
                    </button>
                </div>
            </form>
        </div>
        
        <script>
            function toggleCatatan() {
                var isReject = document.getElementById('actionReject').checked;
                var catatanBox = document.getElementById('catatanBox');
                var catatanInput = document.getElementById('catatan');
                
                if (isReject) {
                    catatanBox.style.display = 'block';
                    catatanInput.required = true;
                } else {
                    catatanBox.style.display = 'none';
                    catatanInput.required = false;
                }
            }
        </script>
        @endif
    @endcan
</x-app-layout>
