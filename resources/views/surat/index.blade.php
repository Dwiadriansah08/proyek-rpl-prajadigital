<x-app-layout>
    <x-slot name="header">
        Pengajuan Surat
    </x-slot>

    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 font-semibold">
                @if(auth()->user()->hasRole('warga'))
                    Riwayat Pengajuan & Dokumen
                @else
                    Daftar Pengajuan Surat
                @endif
            </h5>
            @if(auth()->user()->hasRole('warga'))
                <a href="{{ route('surat.create') }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Buat Pengajuan
                </a>
            @endif
        </div>

        @if(auth()->user()->hasRole('warga'))
            <ul class="nav nav-pills mb-4 pb-2 border-bottom" id="suratTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 me-2" id="riwayat-tab" data-bs-toggle="pill" data-bs-target="#riwayat" type="button" role="tab">Riwayat Pengajuan</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4" id="dokumen-tab" data-bs-toggle="pill" data-bs-target="#dokumen" type="button" role="tab">Dokumen Saya</button>
                </li>
            </ul>

            <div class="tab-content" id="suratTabsContent">
                <!-- Tab Riwayat -->
                <div class="tab-pane fade show active" id="riwayat" role="tabpanel" tabindex="0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border-bottom-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 rounded-start">Tgl Pengajuan</th>
                                    <th class="border-0">Jenis Surat</th>
                                    <th class="border-0">Keperluan</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0 rounded-end text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pengajuan as $s)
                                <tr>
                                    <td class="border-bottom-0 py-3 text-muted">{{ $s->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="border-bottom-0 py-3 font-medium">{{ $s->template->nama_surat }}</td>
                                    <td class="border-bottom-0 py-3 text-muted">{{ Str::limit($s->data_tambahan['keperluan'] ?? '-', 30) }}</td>
                                    <td class="border-bottom-0 py-3">
                                        @php
                                            $badgeClass = 'bg-secondary';
                                            if($s->status == 'SELESAI') $badgeClass = 'bg-success';
                                            elseif($s->status == 'DITOLAK') $badgeClass = 'bg-danger';
                                            elseif(str_contains($s->status, 'MENUNGGU')) $badgeClass = 'bg-warning text-dark';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-pill px-3 fw-normal">{{ str_replace('_', ' ', $s->status) }}</span>
                                    </td>
                                    <td class="border-bottom-0 py-3 text-end">
                                        <a href="{{ route('surat.show', $s->id) }}" class="btn btn-sm btn-light text-primary rounded-pill px-3">Detail</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-3 opacity-50"></i>
                                        Belum ada data pengajuan surat.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Dokumen -->
                <div class="tab-pane fade" id="dokumen" role="tabpanel" tabindex="0">
                    <div class="row g-4">
                        @php
                            $dokumen_saya = $pengajuan->where('status', 'SELESAI')->whereNotNull('file_pdf');
                        @endphp
                        @forelse($dokumen_saya as $doc)
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100 bg-light">
                                <div class="card-body p-4 d-flex flex-column text-center">
                                    <i class="bi bi-file-earmark-pdf text-danger mb-3" style="font-size: 3rem;"></i>
                                    <h6 class="font-semibold mb-1">{{ $doc->template->nama_surat }}</h6>
                                    <p class="text-xs text-muted mb-4">{{ $doc->nomor_surat ?? 'Selesai pada: ' . $doc->approved_at?->format('d/m/Y') }}</p>
                                    
                                    <div class="mt-auto d-flex gap-2">
                                        <a href="{{ route('surat.show', $doc->id) }}" class="btn btn-outline-secondary btn-sm flex-grow-1 rounded-pill">Detail</a>
                                        <a href="{{ route('surat.download', $doc->id) }}" class="btn btn-success btn-sm flex-grow-1 rounded-pill"><i class="bi bi-download"></i> Unduh</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="bi bi-folder-x fs-2 d-block mb-3 opacity-50"></i>
                            Belum ada dokumen yang tersedia untuk diunduh.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @else
            <!-- Filter Status Nav Pills for RT/RW/Admin -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="{{ route('surat.index') }}" class="btn btn-sm rounded-pill px-3 {{ !request('status') ? 'btn-primary' : 'btn-light border' }}">
                    Semua
                </a>
                @if(auth()->user()->hasRole(['admin_desa', 'super_admin']))
                <a href="{{ route('surat.index', ['status' => 'MENUNGGU_ADMIN_DESA']) }}" class="btn btn-sm rounded-pill px-3 {{ request('status') === 'MENUNGGU_ADMIN_DESA' ? 'btn-primary' : 'btn-light border' }}">
                    <i class="bi bi-clock-history me-1"></i> Menunggu Verifikasi Desa
                    @php
                        $countDesa = \App\Models\PengajuanSurat::where('status', 'MENUNGGU_ADMIN_DESA')->count();
                    @endphp
                    @if($countDesa > 0)
                        <span class="badge bg-danger rounded-pill ms-1">{{ $countDesa }}</span>
                    @endif
                </a>
                @endif
                <a href="{{ route('surat.index', ['status' => 'MENUNGGU_RW']) }}" class="btn btn-sm rounded-pill px-3 {{ request('status') === 'MENUNGGU_RW' ? 'btn-primary' : 'btn-light border' }}">
                    Menunggu RW
                </a>
                <a href="{{ route('surat.index', ['status' => 'MENUNGGU_RT']) }}" class="btn btn-sm rounded-pill px-3 {{ request('status') === 'MENUNGGU_RT' ? 'btn-primary' : 'btn-light border' }}">
                    Menunggu RT
                </a>
                <a href="{{ route('surat.index', ['status' => 'SELESAI']) }}" class="btn btn-sm rounded-pill px-3 {{ request('status') === 'SELESAI' ? 'btn-primary' : 'btn-light border' }}">
                    Selesai
                </a>
                <a href="{{ route('surat.index', ['status' => 'DITOLAK']) }}" class="btn btn-sm rounded-pill px-3 {{ request('status') === 'DITOLAK' ? 'btn-primary' : 'btn-light border' }}">
                    Ditolak
                </a>
            </div>

            <!-- RT/RW/Admin View -->
            <div class="table-responsive">
                <table class="table table-hover align-middle border-bottom-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 rounded-start">Tgl Pengajuan</th>
                            <th class="border-0">Pemohon</th>
                            <th class="border-0">Jenis Surat</th>
                            <th class="border-0">Keperluan</th>
                            <th class="border-0">Status</th>
                            <th class="border-0 rounded-end text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuan as $s)
                        <tr>
                            <td class="border-bottom-0 py-3 text-muted">{{ $s->created_at->format('d/m/Y H:i') }}</td>
                            <td class="border-bottom-0 py-3">
                                <div class="font-medium">{{ $s->warga->nama_lengkap }}</div>
                                <small class="text-muted font-monospace">{{ $s->warga->nik }}</small>
                            </td>
                            <td class="border-bottom-0 py-3 font-medium">{{ $s->template->nama_surat }}</td>
                            <td class="border-bottom-0 py-3 text-muted">{{ Str::limit($s->data_tambahan['keperluan'] ?? '-', 30) }}</td>
                            <td class="border-bottom-0 py-3">
                                @php
                                    $badgeClass = 'bg-secondary';
                                    if($s->status == 'SELESAI') $badgeClass = 'bg-success';
                                    elseif($s->status == 'DITOLAK') $badgeClass = 'bg-danger';
                                    elseif($s->status == 'MENUNGGU_ADMIN_DESA') $badgeClass = 'bg-primary';
                                    elseif(str_contains($s->status, 'MENUNGGU')) $badgeClass = 'bg-warning text-dark';
                                @endphp
                                <span class="badge {{ $badgeClass }} fw-normal rounded-pill px-3">{{ str_replace('_', ' ', $s->status) }}</span>
                            </td>
                            <td class="border-bottom-0 py-3 text-end">
                                @if(auth()->user()->hasRole(['admin_desa', 'super_admin']) && $s->status === 'MENUNGGU_ADMIN_DESA')
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('surat.show', $s->id) }}" class="btn btn-sm btn-light text-primary rounded-pill px-2.5" title="Detail">
                                            Detail
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalApprove{{ $s->id }}">
                                            <i class="bi bi-check-circle me-1"></i> Setujui (Terbitkan Surat)
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" data-bs-toggle="modal" data-bs-target="#modalReject{{ $s->id }}">
                                            <i class="bi bi-x-circle me-1"></i> Tolak
                                        </button>
                                    </div>

                                    <!-- Modal Approve Admin Desa -->
                                    <div class="modal fade" id="modalApprove{{ $s->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <form action="{{ route('surat.process', $s->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="action" value="approve">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold text-dark">
                                                            <i class="bi bi-patch-check-fill text-success me-2"></i> Konfirmasi Penerbitan Surat
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body py-3">
                                                        <p class="text-muted small mb-3">
                                                            Anda akan menyetujui pengajuan surat resmi ini pada tingkat akhir. Dokumen PDF berstempel resmi akan dibuat otomatis dan dikirimkan via Email & Notifikasi WhatsApp ke warga.
                                                        </p>
                                                        <div class="p-3 rounded-3 bg-light border">
                                                            <div class="row g-2 small">
                                                                <div class="col-4 text-muted">Pemohon:</div>
                                                                <div class="col-8 fw-semibold text-dark">{{ $s->warga->nama_lengkap }}</div>
                                                                <div class="col-4 text-muted">Jenis Surat:</div>
                                                                <div class="col-8 fw-semibold text-dark">{{ $s->template->nama_surat }}</div>
                                                                <div class="col-4 text-muted">Keperluan:</div>
                                                                <div class="col-8 text-dark">{{ $s->data_tambahan['keperluan'] ?? '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                                                            <i class="bi bi-check2-circle me-1"></i> Ya, Setujui & Terbitkan
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Reject Admin Desa -->
                                    <div class="modal fade" id="modalReject{{ $s->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <form action="{{ route('surat.process', $s->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="action" value="reject">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold text-danger">
                                                            <i class="bi bi-x-circle-fill text-danger me-2"></i> Tolak Pengajuan Surat
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body py-3">
                                                        <p class="text-muted small mb-2">
                                                            Berikan alasan penolakan agar warga pemohon dapat memperbaiki persyaratan.
                                                        </p>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-danger">Alasan Penolakan <span class="text-danger">*</span></label>
                                                            <textarea name="catatan" class="form-control" rows="3" required placeholder="Contoh: Berkas tidak sesuai atau persyaratan belum lengkap..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                                                            <i class="bi bi-x-lg me-1"></i> Tolak Pengajuan
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ route('surat.show', $s->id) }}" class="btn btn-sm btn-light text-primary rounded-pill px-3">Detail</a>
                                    @if($s->status == 'SELESAI' && $s->file_pdf)
                                        <a href="{{ route('surat.download', $s->id) }}" class="btn btn-sm btn-success rounded-pill px-3"><i class="bi bi-download"></i> PDF</a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-3 opacity-50"></i>
                                Belum ada data pengajuan surat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
        
        <div class="mt-4">
            {{ $pengajuan->links() }}
        </div>
    </div>
</x-app-layout>
