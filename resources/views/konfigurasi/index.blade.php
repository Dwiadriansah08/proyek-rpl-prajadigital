<x-app-layout>
    <x-slot name="header">
        Konfigurasi &amp; Status Sistem
    </x-slot>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- FLASH MESSAGES                                          --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @if(session("success"))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4 rounded-3 border-0 shadow-sm" role="alert" style="background: #f0fdf4; border-left: 4px solid #16a34a !important;">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div class="text-success fw-semibold">{{ session("success") }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session("info"))
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 mb-4 rounded-3 border-0 shadow-sm" role="alert" style="background: #eff6ff; border-left: 4px solid #3b82f6 !important;">
            <i class="bi bi-info-circle-fill text-primary fs-5"></i>
            <div class="text-primary fw-semibold">{{ session("info") }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session("error"))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4 rounded-3 border-0 shadow-sm" role="alert" style="background: #fef2f2; border-left: 4px solid #dc2626 !important;">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
            <div class="text-danger fw-semibold">{{ session("error") }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- 1. KARTU KONFIGURASI AI                                 --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="col-lg-6">
            <div class="card border-0 h-100 p-4 shadow-sm" style="border-radius: 20px;">
                @auth
                @if(auth()->user()->hasRole("super_admin"))
                <form method="POST" action="{{ route("konfigurasi.update-ai") }}" id="form-ai" class="d-flex flex-column h-100">
                    @csrf
                @else
                <div class="d-flex flex-column h-100">
                @endif
                @endauth

                    {{-- Header kartu --}}
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3" style="width:44px;height:44px;background:#EEF2FF;color:#4F46E5;">
                                <i class="bi bi-robot fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0" style="font-size:1.05rem;">Integrasi AI & Asisten Virtual</h5>
                                <span class="text-muted small">Status koneksi mesin kecerdasan buatan</span>
                            </div>
                        </div>
                        @if($aiConfig["is_configured"])
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                <i class="bi bi-check-circle me-1"></i> Terhubung
                            </span>
                        @elseif($aiConfig["is_fallback_configured"])
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill">
                                <i class="bi bi-shield-check me-1"></i> Cadangan Siap
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill">
                                <i class="bi bi-exclamation-triangle me-1"></i> Belum Dikonfigurasi
                            </span>
                        @endif
                    </div>

                    {{-- Info status & Inline Input --}}
                    <div class="table-responsive mb-3 flex-grow-1">
                        <table class="table table-borderless align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted small ps-0" style="width:32%;">AI Provider</td>
                                    <td class="fw-bold font-monospace text-dark">{{ strtoupper($aiConfig["provider"]) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small ps-0">Model Engine</td>
                                    <td>
                                        @auth
                                        @if(auth()->user()->hasRole("super_admin"))
                                            @php
                                                $currentModel = old('ai_model', old('model_engine', $aiConfig['model']));
                                                $knownModels = \App\Http\Controllers\KonfigurasiController::ALLOWED_AI_MODELS;
                                                $isCustomModel = $currentModel !== '' && !in_array($currentModel, $knownModels);
                                            @endphp
                                            <div class="d-flex flex-column gap-2">
                                                <select id="ai_model"
                                                        name="ai_model"
                                                        class="form-select form-select-sm font-monospace text-primary fw-semibold @error('ai_model') is-invalid @enderror @error('model_engine') is-invalid @enderror"
                                                        style="max-width: 360px;"
                                                        onchange="handleModelSelectChange(this)">
                                                    <optgroup label="⚡ Seri Flash-Lite (Super Cepat, Hemat &amp; Aktif)">
                                                        <option value="gemini-3.5-flash-lite" {{ ($currentModel === 'gemini-3.5-flash-lite' || empty($currentModel)) ? 'selected' : '' }}>
                                                            gemini-3.5-flash-lite — ⭐ Rekomendasi Utama (Instan ~1-2s, Hemat)
                                                        </option>
                                                        <option value="gemini-3.1-flash-lite" {{ $currentModel === 'gemini-3.1-flash-lite' ? 'selected' : '' }}>
                                                            gemini-3.1-flash-lite — Sangat Cepat &amp; Stabil Real-Time
                                                        </option>
                                                        <option value="gemini-flash-lite-latest" {{ $currentModel === 'gemini-flash-lite-latest' ? 'selected' : '' }}>
                                                            gemini-flash-lite-latest — Alias Resmi Flash-Lite Terbaru
                                                        </option>
                                                    </optgroup>
                                                    <optgroup label="🧠 Seri Flash Penalaran Tinggi (Analisis Kompleks)">
                                                        <option value="gemini-3.6-flash" {{ $currentModel === 'gemini-3.6-flash' ? 'selected' : '' }}>
                                                            gemini-3.6-flash — Penalaran Tinggi / Analisis (~3-5s)
                                                        </option>
                                                        <option value="gemini-3.8-flash" {{ $currentModel === 'gemini-3.8-flash' ? 'selected' : '' }}>
                                                            gemini-3.8-flash — Generasi Terbaru (Heavy Task)
                                                        </option>
                                                    </optgroup>
                                                    <optgroup label="✏️ Kustom / Eksperimental">
                                                        <option value="custom" {{ ($currentModel === 'custom' || $isCustomModel) ? 'selected' : '' }}>
                                                            Model Kustom (Input Manual)
                                                        </option>
                                                    </optgroup>
                                                </select>

                                                {{-- Custom model input, shown only when 'custom' is selected --}}
                                                <div id="custom-model-wrapper" style="{{ ($currentModel === 'custom' || $isCustomModel) ? '' : 'display:none;' }}">
                                                    <input type="text"
                                                           id="custom_model_engine"
                                                           name="custom_model_engine"
                                                           class="form-control form-control-sm font-monospace text-primary @error('custom_model_engine') is-invalid @enderror"
                                                           style="max-width: 360px;"
                                                           value="{{ old('custom_model_engine', $isCustomModel ? $currentModel : '') }}"
                                                           placeholder="mis. gemini-3.5-flash"
                                                           maxlength="100">
                                                    <div class="text-muted mt-1" style="font-size:0.7rem;">
                                                        <i class="bi bi-info-circle me-1"></i>Masukkan ID model resmi Google AI Studio
                                                    </div>
                                                    @error('custom_model_engine')
                                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                @if($aiConfig["using_override"])
                                                    <span class="badge" style="background:#eef2ff;color:#4f46e5;font-size:0.65rem;">override aktif</span>
                                                @endif
                                            </div>
                                            @error("ai_model")
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                            @error("model_engine")
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        @else
                                            <span class="fw-semibold font-monospace text-primary">
                                                {{ $aiConfig["model"] }}
                                                @if($aiConfig["using_override"])
                                                    <span class="badge ms-1" style="background:#eef2ff;color:#4f46e5;font-size:0.65rem;">override aktif</span>
                                                @endif
                                            </span>
                                        @endif
                                        @endauth
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted small ps-0 align-top pt-2">API Key Utama</td>
                                    <td>
                                        @auth
                                        @if(auth()->user()->hasRole("super_admin"))
                                            <div class="d-flex flex-column gap-1">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light text-muted border-end-0">
                                                        <i class="bi bi-key-fill"></i>
                                                    </span>
                                                    <input type="password"
                                                           id="ai_api_key"
                                                           name="ai_api_key"
                                                           class="form-control form-control-sm font-monospace border-start-0 border-end-0 @error("ai_api_key") is-invalid @enderror"
                                                           value="{{ old('ai_api_key', $aiConfig['api_key_raw'] ?? '') }}"
                                                           placeholder="{{ $aiConfig["is_configured"] ? $aiConfig["api_key_masked"] : "Masukkan API Key Gemini Utama..." }}"
                                                           autocomplete="new-password"
                                                           maxlength="512">
                                                    <button class="btn btn-outline-secondary" type="button" id="btn-toggle-ai-api-key" onclick="toggleSecretVisibility('ai_api_key', this)" title="Lihat/Sembunyikan">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                                @error("ai_api_key")
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                                <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                                                    @if($aiConfig["is_configured"])
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.7rem;">
                                                            <i class="bi bi-check-circle me-1"></i>Utama Aktif
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:0.7rem;">
                                                            <i class="bi bi-exclamation-circle me-1"></i>Belum dikonfigurasi
                                                        </span>
                                                    @endif

                                                    @if($aiConfig["source_primary"] === 'database')
                                                        <span class="badge" style="background:#dcfce7;color:#15803d;font-size:0.65rem;" title="Tersimpan terenkripsi di Database">
                                                            <i class="bi bi-database-check me-1"></i>Database (Encrypted)
                                                        </span>
                                                    @elseif($aiConfig["source_primary"] === 'env')
                                                        <span class="badge" style="background:#f1f5f9;color:#475569;font-size:0.65rem;" title="Fallback dari file .env">
                                                            <i class="bi bi-file-earmark-code me-1"></i>Fallback (.env)
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="font-monospace small text-secondary">
                                                <code>{{ $aiConfig["api_key_masked"] }}</code>
                                                @if($aiConfig["is_configured"])
                                                    <span class="badge bg-success-subtle text-success ms-1">Utama Aktif</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning ms-1">Belum dikonfigurasi</span>
                                                @endif
                                            </div>
                                        @endif
                                        @endauth
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted small ps-0 align-top pt-2">API Key Cadangan</td>
                                    <td>
                                        @auth
                                        @if(auth()->user()->hasRole("super_admin"))
                                            <div class="d-flex flex-column gap-1">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light text-muted border-end-0">
                                                        <i class="bi bi-shield-lock-fill"></i>
                                                    </span>
                                                    <input type="password"
                                                           id="ai_api_key_fallback"
                                                           name="ai_api_key_fallback"
                                                           class="form-control form-control-sm font-monospace border-start-0 border-end-0 @error("ai_api_key_fallback") is-invalid @enderror"
                                                           value="{{ old('ai_api_key_fallback', $aiConfig['api_key_fallback_raw'] ?? '') }}"
                                                           placeholder="{{ $aiConfig["is_fallback_configured"] ? $aiConfig["api_key_fallback_masked"] : "Masukkan API Key Cadangan (Opsional)..." }}"
                                                           autocomplete="new-password"
                                                           maxlength="512">
                                                    <button class="btn btn-outline-secondary" type="button" id="btn-toggle-ai-api-key-fallback" onclick="toggleSecretVisibility('ai_api_key_fallback', this)" title="Lihat/Sembunyikan">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                                @error("ai_api_key_fallback")
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                                <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                                                    @if($aiConfig["is_fallback_configured"])
                                                        <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size:0.7rem;">
                                                            <i class="bi bi-shield-check me-1"></i>Cadangan Siap
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:0.7rem;">
                                                            Belum diset (Opsional)
                                                        </span>
                                                    @endif

                                                    @if($aiConfig["source_fallback"] === 'database')
                                                        <span class="badge" style="background:#dcfce7;color:#15803d;font-size:0.65rem;" title="Tersimpan terenkripsi di Database">
                                                            <i class="bi bi-database-check me-1"></i>Database (Encrypted)
                                                        </span>
                                                    @elseif($aiConfig["source_fallback"] === 'env')
                                                        <span class="badge" style="background:#f1f5f9;color:#475569;font-size:0.65rem;" title="Fallback dari file .env">
                                                            <i class="bi bi-file-earmark-code me-1"></i>Fallback (.env)
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="font-monospace small text-secondary">
                                                <code>{{ $aiConfig["api_key_fallback_masked"] }}</code>
                                                @if($aiConfig["is_fallback_configured"])
                                                    <span class="badge bg-info-subtle text-info ms-1">Cadangan Siap</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary ms-1">Belum diset</span>
                                                @endif
                                            </div>
                                        @endif
                                        @endauth
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted small ps-0">Mode Keamanan</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-shield-check text-success me-1"></i> Dual-Key Auto-Fallback + Guarded Context
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer kartu: Tes & Tombol Simpan --}}
                    <div class="pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2 mt-auto">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" id="btn-test-ai" class="btn btn-sm" style="background:#eef2ff;color:#4f46e5;border:1px solid #c7d2fe;" onclick="testAiConnection('auto')">
                                <i class="bi bi-lightning-charge me-1"></i> Tes Koneksi AI
                            </button>
                            @if($aiConfig["is_fallback_configured"])
                            <button type="button" id="btn-test-ai-fallback" class="btn btn-sm btn-outline-secondary" onclick="testAiConnection('fallback')">
                                <i class="bi bi-shield-check me-1"></i> Tes Key Cadangan
                            </button>
                            @endif
                        </div>

                        @auth
                        @if(auth()->user()->hasRole("super_admin"))
                            <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm">
                                <i class="bi bi-floppy me-1"></i> Simpan Perubahan
                            </button>
                        @endif
                        @endauth
                    </div>
                    <div id="ai-test-result" class="small mt-2"></div>

                @auth
                @if(auth()->user()->hasRole("super_admin"))
                </form>
                @else
                </div>
                @endif
                @endauth
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- 2. KARTU KONFIGURASI WHATSAPP                          --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="col-lg-6">
            <div class="card border-0 h-100 p-4 shadow-sm" style="border-radius: 20px;">
                @auth
                @if(auth()->user()->hasRole("super_admin"))
                <form method="POST" action="{{ route("konfigurasi.update-whatsapp") }}" id="form-wa" class="d-flex flex-column h-100">
                    @csrf
                @else
                <div class="d-flex flex-column h-100">
                @endif
                @endauth

                    {{-- Header kartu --}}
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3" style="width:44px;height:44px;background:#F0FDF4;color:#16A34A;">
                                <i class="bi bi-whatsapp fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0" style="font-size:1.05rem;">WhatsApp Notification Gateway</h5>
                                <span class="text-muted small">Layanan notifikasi surat dan update warga</span>
                            </div>
                        </div>
                        @if($waConfig["is_simulation"])
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill">
                                <i class="bi bi-cpu me-1"></i> Mode Simulasi
                            </span>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                <i class="bi bi-broadcast me-1"></i> Siap Kirim
                            </span>
                        @endif
                    </div>

                    {{-- Info status & Inline Input --}}
                    <div class="table-responsive mb-3 flex-grow-1">
                        <table class="table table-borderless align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted small ps-0" style="width:32%;">Gateway Provider</td>
                                    <td class="fw-bold font-monospace text-dark">{{ strtoupper($waConfig["provider"]) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small ps-0">API Endpoint</td>
                                    <td class="font-monospace small text-break text-muted">{{ $waConfig["endpoint"] }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small ps-0 align-top pt-2">Token Fonnte</td>
                                    <td>
                                        @auth
                                        @if(auth()->user()->hasRole("super_admin"))
                                            <div class="d-flex flex-column gap-1">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light text-muted border-end-0">
                                                        <i class="bi bi-key-fill"></i>
                                                    </span>
                                                    <input type="password"
                                                           id="wa_token"
                                                           name="wa_token"
                                                           class="form-control form-control-sm font-monospace border-start-0 border-end-0 @error("wa_token") is-invalid @enderror"
                                                           value="{{ old('wa_token', $waConfig['token_raw'] ?? '') }}"
                                                           placeholder="{{ $waConfig["is_configured"] ? $waConfig["token_masked"] : "Masukkan Token Fonnte..." }}"
                                                           autocomplete="new-password"
                                                           maxlength="512">
                                                    <button class="btn btn-outline-secondary" type="button" id="btn-toggle-wa-token" onclick="toggleSecretVisibility('wa_token', this)" title="Lihat/Sembunyikan">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                                @error("wa_token")
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                                <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                                                    @if($waConfig["is_configured"])
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.7rem;">
                                                            <i class="bi bi-check-circle me-1"></i>Token Aktif
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:0.7rem;">
                                                            <i class="bi bi-exclamation-circle me-1"></i>Belum diset
                                                        </span>
                                                    @endif

                                                    @if($waConfig["source_token"] === 'database')
                                                        <span class="badge" style="background:#dcfce7;color:#15803d;font-size:0.65rem;" title="Tersimpan terenkripsi di Database">
                                                            <i class="bi bi-database-check me-1"></i>Database (Encrypted)
                                                        </span>
                                                    @elseif($waConfig["source_token"] === 'env')
                                                        <span class="badge" style="background:#f1f5f9;color:#475569;font-size:0.65rem;" title="Fallback dari file .env">
                                                            <i class="bi bi-file-earmark-code me-1"></i>Fallback (.env)
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="font-monospace small text-secondary">
                                                <code>{{ $waConfig["token_masked"] }}</code>
                                                @if($waConfig["is_configured"])
                                                    <span class="badge bg-success-subtle text-success ms-1">Token Aktif</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning ms-1">Belum diset</span>
                                                @endif
                                            </div>
                                        @endif
                                        @endauth
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted small ps-0">Mode Pengiriman</td>
                                    <td>
                                        @auth
                                        @if(auth()->user()->hasRole("super_admin"))
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="form-check form-check-inline mb-0">
                                                    <input class="form-check-input" type="radio" name="wa_simulation" id="wa_sim_on" value="1"
                                                        {{ $waConfig["is_simulation"] ? "checked" : "" }}>
                                                    <label class="form-check-label small" for="wa_sim_on">
                                                        <i class="bi bi-cpu text-info me-1"></i> Simulasi (Log saja)
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline mb-0">
                                                    <input class="form-check-input" type="radio" name="wa_simulation" id="wa_sim_off" value="0"
                                                        {{ !$waConfig["is_simulation"] ? "checked" : "" }}>
                                                    <label class="form-check-label small" for="wa_sim_off">
                                                        <i class="bi bi-broadcast text-success me-1"></i> Live (Kirim WA)
                                                    </label>
                                                </div>
                                            </div>
                                            @error("wa_simulation")
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        @else
                                            @if($waConfig["is_simulation"])
                                                <span class="badge bg-light text-muted border">
                                                    <i class="bi bi-cpu text-info me-1"></i> Simulasi (Log saja)
                                                </span>
                                            @else
                                                <span class="badge bg-success text-white">
                                                    <i class="bi bi-broadcast me-1"></i> Live Delivery
                                                </span>
                                            @endif
                                        @endif
                                        @endauth
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer kartu: Tes & Tombol Simpan --}}
                    <div class="pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2 mt-auto">
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" id="btn-test-wa" class="btn btn-sm" style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;" onclick="testWaConnection()">
                                <i class="bi bi-wifi me-1"></i> Tes Koneksi Fonnte
                            </button>
                            <span class="text-muted" style="font-size:0.75rem;">(Hanya cek device)</span>
                        </div>

                        @auth
                        @if(auth()->user()->hasRole("super_admin"))
                            <button type="submit" class="btn btn-sm btn-success px-3 shadow-sm">
                                <i class="bi bi-floppy me-1"></i> Simpan Perubahan
                            </button>
                        @endif
                        @endauth
                    </div>
                    <div id="wa-test-result" class="small mt-2"></div>

                @auth
                @if(auth()->user()->hasRole("super_admin"))
                </form>
                @else
                </div>
                @endif
                @endauth
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- 3. INFORMASI SERVER                                     --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="col-12">
            <div class="card border-0 p-4 shadow-sm" style="border-radius: 20px;">
                <div class="pb-3 mb-3 border-bottom">
                    <h5 class="fw-bold text-dark mb-0" style="font-size:1.05rem;">Informasi Server & Lingkungan Aplikasi</h5>
                    <span class="text-muted small">Ringkasan versi dan platform operasional desa</span>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="text-muted small">Aplikasi</div>
                            <div class="fw-bold text-dark">{{ $systemInfo["app_name"] }} ({{ $systemInfo["app_env"] }})</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="text-muted small">Framework &amp; Bahasa</div>
                            <div class="fw-bold text-dark">Laravel {{ $systemInfo["laravel_version"] }} / PHP {{ $systemInfo["php_version"] }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="text-muted small">Database Driver</div>
                            <div class="fw-bold text-dark font-monospace">{{ strtoupper($systemInfo["database_connection"]) }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="text-muted small">Timezone Server</div>
                            <div class="fw-bold text-dark font-monospace">{{ $systemInfo["timezone"] }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="text-muted small">Cache Store</div>
                            <div class="fw-bold text-dark font-monospace">{{ strtoupper(config("cache.default", "database")) }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="text-muted small">Queue Connection</div>
                            <div class="fw-bold text-dark font-monospace">{{ strtoupper(config("queue.default", "database")) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- 4. AUDIT LOG KONFIGURASI (Super Admin only)            --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        @auth
        @if(auth()->user()->hasRole("super_admin"))
        <div class="col-12">
            <div class="card border-0 p-4 shadow-sm" style="border-radius: 20px;">
                <div class="pb-3 mb-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i>
                    <div>
                        <h5 class="fw-bold text-dark mb-0" style="font-size:1.05rem;">Riwayat Perubahan Konfigurasi</h5>
                        <span class="text-muted small">5 perubahan terakhir oleh Super Admin</span>
                    </div>
                </div>

                @if($recentConfigLogs->isEmpty())
                    <div class="text-muted small text-center py-3">
                        <i class="bi bi-inbox fs-4 d-block mb-2 opacity-40"></i>
                        Belum ada perubahan konfigurasi yang tercatat.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small">
                                    <th class="fw-semibold">Waktu</th>
                                    <th class="fw-semibold">Aktivitas</th>
                                    <th class="fw-semibold">Oleh</th>
                                    <th class="fw-semibold">IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentConfigLogs as $log)
                                <tr>
                                    <td class="small text-muted font-monospace">{{ $log->created_at?->translatedFormat("d M Y H:i") }}</td>
                                    <td class="small">{{ $log->activity }}</td>
                                    <td class="small fw-semibold">{{ $log->user?->name ?? "(dihapus)" }}</td>
                                    <td class="small font-monospace text-muted">{{ $log->ip_address ?? "-" }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        @endif
        @endauth

    </div>{{-- /row --}}

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- JAVASCRIPT: Eye toggle & Tes koneksi via fetch             --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <script>
    function toggleSecretVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const icon = btn.querySelector('i') || btn;

        if (input.type === 'password') {
            // Teks BUKA / KELIATAN
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            // Teks TUTUP / TERSEMBUNYI
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    // Alias backward-compatibility
    function toggleVisibility(inputId, btn) {
        toggleSecretVisibility(inputId, btn);
    }

    function showTestResult(elId, btnId, success, message, extra) {
        const el = document.getElementById(elId);
        const btn = document.getElementById(btnId);
        if (btn) btn.disabled = false;
        if (!el) return;
        if (success) {
            el.innerHTML = "<span class=\"text-success\"><i class=\"bi bi-check-circle-fill me-1\"></i>" + message +
                (extra ? " <span class=\"text-muted\">(" + extra + ")</span>" : "") + "</span>";
        } else {
            el.innerHTML = "<span class=\"text-danger\"><i class=\"bi bi-x-circle-fill me-1\"></i>" + message + "</span>";
        }
    }

    function testAiConnection(target = 'auto') {
        const btn = document.getElementById("btn-test-ai");
        const btnFb = document.getElementById("btn-test-ai-fallback");
        const result = document.getElementById("ai-test-result");
        if (btn) btn.disabled = true;
        if (btnFb) btnFb.disabled = true;

        const targetLabel = target === 'fallback' ? 'Key Cadangan' : 'AI';
        result.innerHTML = "<span class=\"text-muted\"><span class=\"spinner-border spinner-border-sm me-1\"></span>Menguji koneksi " + targetLabel + "...</span>";

        fetch("{{ route("konfigurasi.test-ai") }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content,
                "Accept": "application/json",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ target: target })
        })
        .then(r => r.json().then(data => ({ status: r.status, ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (btn) btn.disabled = false;
            if (btnFb) btnFb.disabled = false;

            if (data.success) {
                const sampleText = data.sample ? " — \"" + data.sample + "\"" : "";
                const modelBadge = data.model ? " <span class=\"badge bg-light text-dark border\">" + data.model + "</span>" : "";
                result.innerHTML = "<span class=\"text-success\"><i class=\"bi bi-check-circle-fill me-1\"></i>" + data.message + modelBadge + (sampleText ? " <span class=\"text-muted small\">(" + sampleText + ")</span>" : "") + "</span>";
            } else {
                result.innerHTML = "<span class=\"text-danger\"><i class=\"bi bi-x-circle-fill me-1\"></i>" + (data.message || "Pengujian gagal.") + "</span>";
            }
        })
        .catch(e => {
            if (btn) btn.disabled = false;
            if (btnFb) btnFb.disabled = false;
            result.innerHTML = "<span class=\"text-danger\"><i class=\"bi bi-x-circle-fill me-1\"></i>Permintaan gagal: " + e.message + "</span>";
        });
    }

    function testWaConnection() {
        const btn = document.getElementById("btn-test-wa");
        const result = document.getElementById("wa-test-result");
        if (btn) btn.disabled = true;
        result.innerHTML = "<span class=\"text-muted\"><span class=\"spinner-border spinner-border-sm me-1\"></span>Menguji...</span>";

        fetch("{{ route("konfigurasi.test-wa") }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content,
                "Accept": "application/json",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({})
        })
        .then(r => r.json())
        .then(data => {
            showTestResult("wa-test-result", "btn-test-wa", data.success, data.message,
                data.success ? data.device : null);
        })
        .catch(e => {
            showTestResult("wa-test-result", "btn-test-wa", false, "Permintaan gagal: " + e.message, null);
        });
    }

    function handleModelSelectChange(select) {
        const wrapper = document.getElementById('custom-model-wrapper');
        if (!wrapper) return;
        if (select.value === 'custom') {
            wrapper.style.display = '';
            const customInput = document.getElementById('custom_model_engine');
            if (customInput) customInput.focus();
        } else {
            wrapper.style.display = 'none';
        }
    }
    </script>

</x-app-layout>
