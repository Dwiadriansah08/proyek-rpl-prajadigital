<x-app-layout>
    <x-slot name="header">
        Detail Informasi / Pengumuman
    </x-slot>

    {{-- Flash Message Alert --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4 rounded-3 shadow-sm">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4 rounded-3 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>{{ session('warning') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 rounded-3 shadow-sm">
            <i class="bi bi-x-circle-fill fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm p-0 overflow-hidden mb-4" style="border-radius: 20px;">
        {{-- Header Area Pengumuman --}}
        <div class="p-4 p-md-5 border-bottom" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.08) 0%, rgba(240, 253, 244, 0.4) 100%);">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
                <div style="max-width: 800px;">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge bg-primary px-3 py-1.5 rounded-pill">{{ $informasi->kategori }}</span>
                        
                        {{-- Target Badge --}}
                        @if($informasi->target_type === 'desa')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill">
                                <i class="bi bi-globe me-1"></i> Target: Seluruh Warga Desa
                            </span>
                        @elseif($informasi->target_type === 'rw')
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-1.5 rounded-pill">
                                <i class="bi bi-diagram-3 me-1"></i> Target: Warga RW {{ $informasi->rw?->nomor_rw ?? '-' }}
                            </span>
                        @elseif($informasi->target_type === 'rt')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1.5 rounded-pill">
                                <i class="bi bi-signpost-split me-1"></i> Target: RT {{ $informasi->rt?->nomor_rt ?? '-' }} / RW {{ $informasi->rw?->nomor_rw ?? '-' }}
                            </span>
                        @elseif($informasi->target_type === 'role')
                            <span class="badge bg-purple-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill" style="background: #F3E8FF;">
                                <i class="bi bi-shield-check me-1"></i> Target Role: {{ strtoupper($informasi->target_role ?? '-') }}
                            </span>
                        @endif

                        {{-- Publikasi Badge --}}
                        @if($informasi->status_publikasi)
                            <span class="badge bg-success text-white px-3 py-1.5 rounded-pill">
                                <i class="bi bi-check2-circle me-1"></i> Terpublikasi
                            </span>
                        @else
                            <span class="badge bg-secondary text-white px-3 py-1.5 rounded-pill">
                                <i class="bi bi-eye-slash me-1"></i> Draf (Privat)
                            </span>
                        @endif
                    </div>

                    <h2 class="fw-bold text-dark mb-3" style="font-size: 1.65rem; line-height: 1.35;">{{ $informasi->judul }}</h2>
                    
                    <div class="text-muted small d-flex flex-wrap align-items-center gap-3">
                        <span><i class="bi bi-person-fill text-primary me-1"></i> Dibuat oleh: <strong>{{ $informasi->penulis?->name ?? 'Admin' }}</strong></span>
                        <span>•</span>
                        <span><i class="bi bi-calendar-event me-1"></i> {{ $informasi->created_at->translatedFormat('l, d F Y - H:i') }} WIB</span>
                    </div>
                </div>

                {{-- Action Panel Kanan (Jika berhak) --}}
                @can('update', $informasi)
                <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                    <a href="{{ route('informasi.edit', $informasi->id) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1.5 px-3 py-2 rounded-3">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    @if($informasi->status_publikasi)
                    <button type="button" class="btn btn-success d-inline-flex align-items-center gap-1.5 px-3 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBroadcastWa">
                        <i class="bi bi-whatsapp"></i> Siarkan via WhatsApp
                    </button>
                    @endif
                </div>
                @endcan
            </div>
        </div>
        
        {{-- Isi Konten --}}
        <div class="p-4 p-md-5 bg-white">
            <div class="lh-lg text-secondary" style="font-size: 1.05rem; white-space: pre-line;">
                {{ $informasi->isi ?? $informasi->konten }}
            </div>
            
            <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
                <a href="{{ route('informasi.index') }}" class="btn btn-light px-4 rounded-3 border">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    {{-- Riwayat Pengiriman WhatsApp (Untuk Pengelola / Penulis) --}}
    @if(!auth()->user()->hasRole('warga') && $informasi->broadcasts->count() > 0)
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 20px;">
        <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom">
            <div class="d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success" style="width: 40px; height: 40px;">
                <i class="bi bi-clock-history fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0">Riwayat Log Siaran WhatsApp</h6>
                <small class="text-muted">Daftar pengiriman pesan notifikasi WhatsApp untuk pengumuman ini</small>
            </div>
        </div>

        @foreach($informasi->broadcasts as $bc)
        <div class="border rounded-3 p-3 mb-3 bg-light">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                <div>
                    <span class="fw-bold text-dark"><i class="bi bi-send-check text-success me-1"></i> Siaran dikirim oleh {{ $bc->sender?->name }}</span>
                    <span class="text-muted small ms-2">({{ $bc->created_at->translatedFormat('d M Y, H:i') }} WIB)</span>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Target: {{ $bc->target_description }}</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $bc->success_count }} Berhasil</span>
                    @if($bc->failed_count > 0)
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $bc->failed_count }} Gagal</span>
                    @endif
                </div>
            </div>

            {{-- Detail Penerima Singkat --}}
            <div class="table-responsive bg-white rounded-2 border mt-2">
                <table class="table table-sm align-middle mb-0" style="font-size: 0.82rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Penerima</th>
                            <th>Nomor Telepon</th>
                            <th>Status Pengiriman</th>
                            <th>Waktu Kirim</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bc->recipients as $rcp)
                        <tr>
                            <td class="fw-semibold">{{ $rcp->recipient_name }}</td>
                            <td class="font-monospace text-muted">{{ $rcp->recipient_phone }}</td>
                            <td>
                                @if($rcp->status === 'berhasil')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i> Berhasil
                                    </span>
                                @elseif($rcp->status === 'gagal')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="{{ $rcp->error_message }}">
                                        <i class="bi bi-x-circle me-1"></i> Gagal ({{ Str::limit($rcp->error_message, 25) }})
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                        <i class="bi bi-hourglass-split me-1"></i> Menunggu
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $rcp->sent_at ? $rcp->sent_at->format('H:i:s') : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-2">Tidak ada data penerima detail.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- MODAL KONFIRMASI EKSPLISIT BROADCAST WHATSAPP --}}
    @can('update', $informasi)
    <div class="modal fade" id="modalBroadcastWa" tabindex="-1" aria-labelledby="modalBroadcastWaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <form action="{{ route('informasi.broadcast', $informasi->id) }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom px-4 pt-4 pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success" style="width: 44px; height: 44px;">
                                <i class="bi bi-whatsapp fs-4"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="modalBroadcastWaLabel">Konfirmasi Siaran Notifikasi WhatsApp</h5>
                                <small class="text-muted">Periksa kembali rincian sebelum menyiarkan pesan ke warga</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body px-4 py-3">
                        {{-- Mode Gateway Status Notice --}}
                        <div class="p-3 rounded-3 mb-3 {{ $isSimulationMode ? 'bg-info bg-opacity-10 border border-info border-opacity-25' : 'bg-success bg-opacity-10 border border-success border-opacity-25' }}">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi {{ $isSimulationMode ? 'bi-cpu text-info' : 'bi-broadcast text-success' }} fs-5"></i>
                                <div>
                                    <strong class="d-block text-dark">
                                        {{ $isSimulationMode ? 'Status Gateway: Mode Simulasi Aktif' : 'Status Gateway: Mode Siap Kirim (Live Fonnte)' }}
                                    </strong>
                                    <small class="text-muted">
                                        {{ $isSimulationMode 
                                            ? 'Pesan akan dicatat ke log sistem secara aman tanpa menghubungi gateway eksternal WhatsApp.' 
                                            : 'Pesan sungguhan akan dikirimkan ke nomor WhatsApp warga melalui API Fonnte.' }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Ringkasan Parameter Siaran --}}
                        <div class="table-responsive border rounded-3 mb-3">
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted small ps-3" style="width: 35%;">Judul Pengumuman</td>
                                        <td class="fw-bold text-dark">{{ $informasi->judul }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted small ps-3">Cakupan Target</td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                @if($informasi->target_type === 'desa') Seluruh Warga Desa
                                                @elseif($informasi->target_type === 'rw') Warga RW {{ $informasi->rw?->nomor_rw }}
                                                @elseif($informasi->target_type === 'rt') Warga RT {{ $informasi->rt?->nomor_rt }} / RW {{ $informasi->rw?->nomor_rw }}
                                                @elseif($informasi->target_type === 'role') Role: {{ strtoupper($informasi->target_role) }}
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted small ps-3">Estimasi Jumlah Penerima Valid</td>
                                        <td>
                                            <span class="badge bg-dark px-2.5 py-1 font-monospace fs-6">
                                                {{ $estimatedRecipients->count() }} Kontak
                                            </span>
                                            <small class="text-muted ms-2">(Hanya warga dengan nomor telepon valid & tidak duplikat)</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted small ps-3">Proteksi Anti-Duplikasi</td>
                                        <td class="text-success small fw-semibold">
                                            <i class="bi bi-shield-check me-1"></i> Aktif (Penerima yang sudah menerima siaran ini tidak akan dikirim ulang)
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Preview Template Pesan WhatsApp --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted mb-1">Pratinjau Format Pesan WhatsApp:</label>
                            <div class="p-3 bg-light border rounded-3 font-monospace small" style="white-space: pre-wrap; font-size: 0.82rem; background: #FAFBFD;">*PENGUMUMAN DESA PRAJADIGITAL*

Halo [Nama Penerima],
*{{ $informasi->judul }}*

{{ Str::limit(strip_tags($informasi->isi ?? $informasi->konten), 240) }}

Detail lengkap dapat diakses pada portal resmi PrajaDigital.
_Pesan otomatis dari Pelayanan Desa PrajaDigital._</div>
                        </div>

                        {{-- Checkbox Konfirmasi Wajib --}}
                        <div class="p-3 rounded-3 border bg-white mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="confirm_broadcast" id="confirmBroadcast" value="1" required>
                                <label class="form-check-label fw-semibold text-dark small" for="confirmBroadcast">
                                    Saya telah memeriksa target sasaran dan konten pesan. Saya secara sadar menyetujui pengiriman notifikasi WhatsApp ini kepada {{ $estimatedRecipients->count() }} penerima terdaftar.
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2 px-4 shadow-sm" id="btnSubmitBroadcast">
                            <i class="bi bi-send-fill"></i> Ya, Siarkan Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
</x-app-layout>
