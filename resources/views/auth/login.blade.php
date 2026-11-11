<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Masuk') }} &mdash; {{ config('app.name', 'PrajaDigital') }}</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts & Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --brand-primary: #4F46E5;
            --brand-primary-hover: #4338CA;
            --brand-indigo: #6366F1;
            --brand-indigo-light: #EEF2FF;
            --brand-dark: #0F172A;
            --brand-dark-surface: #1E293B;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-color: #0F172A;
        }

        /* Ambient Glow & Radial Meshes on Showcase */
        .showcase-mesh-bg {
            background-color: #090D16;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.22) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(168, 85, 247, 0.18) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(6, 182, 212, 0.15) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(79, 70, 229, 0.2) 0px, transparent 50%);
        }

        .showcase-grid-pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1.25px, transparent 1.25px);
            background-size: 24px 24px;
        }

        /* Subtle Floating Keyframes for Glow Orbs */
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-16px) scale(1.05); }
        }

        @keyframes floatReverse {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(16px) scale(0.96); }
        }

        .animate-float-1 {
            animation: floatSlow 10s infinite ease-in-out;
        }

        .animate-float-2 {
            animation: floatReverse 12s infinite ease-in-out;
        }

        /* Glassmorphic Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.09);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(255, 255, 255, 0.16);
            transform: translateY(-2px);
        }

        /* Login Input Styling with Double-Ring Accent */
        .form-login-input {
            width: 100%;
            background-color: #FFFFFF;
            border: 1.5px solid #E2E8F0;
            color: #0F172A;
            border-radius: 14px;
            font-size: 0.925rem;
            font-weight: 500;
            line-height: 1.5;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .form-login-input:hover {
            border-color: #CBD5E1;
        }

        .form-login-input:focus {
            background-color: #FFFFFF;
            border-color: #4F46E5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.14), 0 1px 2px rgba(15, 23, 42, 0.05);
        }

        .form-login-input.has-error {
            border-color: #F43F5E;
            box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.12);
        }

        .form-login-input::placeholder {
            color: #94A3B8;
            font-weight: 400;
        }

        /* Custom Stylized Checkbox */
        .custom-login-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 1.75px solid #CBD5E1;
            border-radius: 6px;
            outline: none;
            background-color: #FFFFFF;
            cursor: pointer;
            position: relative;
            vertical-align: middle;
            transition: all 0.2s ease;
        }

        .custom-login-checkbox:hover {
            border-color: #4F46E5;
        }

        .custom-login-checkbox:checked {
            background-color: #4F46E5;
            border-color: #4F46E5;
            box-shadow: 0 2px 5px rgba(79, 70, 229, 0.3);
        }

        .custom-login-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 2px;
            width: 4.5px;
            height: 9px;
            border: solid #FFFFFF;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .custom-login-checkbox:focus-visible {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.25);
        }

        /* Submit Button with Rich Gradient & Shadow */
        .btn-brand-submit {
            background: linear-gradient(135deg, #4F46E5 0%, #6366F1 50%, #4338CA 100%);
            background-size: 200% auto;
            color: #FFFFFF;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            box-shadow: 0 10px 22px -5px rgba(79, 70, 229, 0.42), 0 4px 6px -2px rgba(79, 70, 229, 0.2);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-brand-submit:hover:not(:disabled) {
            background-position: right center;
            box-shadow: 0 16px 28px -5px rgba(79, 70, 229, 0.52), 0 6px 12px -2px rgba(79, 70, 229, 0.28);
            transform: translateY(-1.5px);
        }

        .btn-brand-submit:active:not(:disabled) {
            transform: translateY(0.5px) scale(0.99);
            box-shadow: 0 6px 14px -3px rgba(79, 70, 229, 0.4);
        }

        .btn-brand-submit:disabled {
            opacity: 0.8;
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        /* Pulse Animation for Live Status */
        @keyframes pulsePing {
            0% { transform: scale(1); opacity: 0.9; }
            70% { transform: scale(2.2); opacity: 0; }
            100% { transform: scale(1); opacity: 0; }
        }

        .ping-indicator {
            animation: pulsePing 2.2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        /* Shimmer Entrance */
        @keyframes entranceSlideUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-card-entrance {
            animation: entranceSlideUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">

    <div class="min-h-screen w-full flex flex-col lg:flex-row">

        <!-- =================================================================== -->
        <!-- SISI KIRI (DESKTOP): BRANDING & SYSTEM SHOWCASE                     -->
        <!-- =================================================================== -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-7/12 showcase-mesh-bg relative overflow-hidden flex-col justify-between p-10 xl:p-14 text-white border-r border-indigo-900/30">
            
            <!-- Background Ornaments: Grid Pattern & Ambient Glows -->
            <div class="absolute inset-0 showcase-grid-pattern opacity-60 pointer-events-none"></div>
            <div class="absolute -top-32 -left-32 w-[32rem] h-[32rem] rounded-full bg-indigo-600/25 blur-3xl pointer-events-none animate-float-1"></div>
            <div class="absolute top-1/2 -right-28 w-96 h-96 rounded-full bg-purple-600/20 blur-3xl pointer-events-none animate-float-2"></div>
            <div class="absolute -bottom-28 left-1/4 w-[28rem] h-[28rem] rounded-full bg-cyan-500/15 blur-3xl pointer-events-none"></div>

            <!-- Top Header Showcase -->
            <div class="relative z-10 flex items-center justify-between">
                <!-- Branding Logo -->
                <div class="flex items-center gap-3.5 group">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-500 via-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/35 ring-1 ring-white/20 transition-transform duration-300 group-hover:scale-105">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-extrabold text-xl tracking-tight text-white flex items-center gap-2">
                            <span>PrajaDigital</span>
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-indigo-500/25 text-indigo-200 border border-indigo-400/30">Portal Wilayah</span>
                        </div>
                        <div class="text-xs text-indigo-200/80 font-medium">Sistem Informasi & Layanan Digital Terpadu</div>
                    </div>
                </div>

                <!-- Link Kembali ke Beranda -->
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-indigo-200 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 px-4 py-2 rounded-xl backdrop-blur-sm transition-all duration-200 hover:-translate-x-0.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Beranda</span>
                </a>
            </div>

            <!-- Center Content Showcase -->
            <div class="relative z-10 my-auto py-10 max-w-xl">
                <!-- Status Badge -->
                <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-indigo-500/15 border border-indigo-400/25 text-indigo-200 text-xs font-semibold backdrop-blur-md mb-6 shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="ping-indicator absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                    </span>
                    <span>Pelayanan Publik & Kependudukan Mandiri</span>
                </div>

                <!-- Main Hero Headline -->
                <h1 class="text-3xl xl:text-4xl 2xl:text-5xl font-extrabold text-white tracking-tight leading-[1.18] mb-4">
                    Transformasi Layanan Wilayah yang <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 via-violet-300 to-pink-300">Cepat, Transparan & Terpercaya.</span>
                </h1>

                <!-- Subtitle -->
                <p class="text-sm xl:text-base text-slate-300/90 leading-relaxed mb-8 font-normal">
                    Platform cerdas terintegrasi untuk penerbitan surat pengantar online, administrasi kependudukan warga, hingga koordinasi berjenjang RT, RW, dan Desa yang akuntabel.
                </p>

                <!-- 3 Pillars Feature Highlights -->
                <div class="space-y-3.5 mb-8">
                    <!-- Feature 1: Surat Pengantar Digital -->
                    <div class="glass-card flex items-start gap-3.5 p-3.5 rounded-2xl">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/25 to-indigo-600/10 text-indigo-300 flex items-center justify-center shrink-0 border border-indigo-400/30 shadow-inner">
                            <i class="bi bi-file-earmark-text text-xl"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white flex items-center gap-2">
                                <span>Layanan Surat Pengantar Instan</span>
                                <span class="text-[10px] font-semibold text-indigo-300 bg-indigo-500/20 px-2 py-0.5 rounded-md border border-indigo-500/30">Otomatis</span>
                            </div>
                            <div class="text-xs text-slate-300/80 mt-0.5 leading-normal">
                                Pengajuan berkas administrasi dan surat permohonan warga tanpa harus antre fisik.
                            </div>
                        </div>
                    </div>

                    <!-- Feature 2: Koordinasi RT, RW, dan Desa -->
                    <div class="glass-card flex items-start gap-3.5 p-3.5 rounded-2xl">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500/25 to-violet-600/10 text-violet-300 flex items-center justify-center shrink-0 border border-violet-400/30 shadow-inner">
                            <i class="bi bi-diagram-3 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white flex items-center gap-2">
                                <span>Integrasi Berjenjang RT, RW & Desa</span>
                                <span class="text-[10px] font-semibold text-violet-300 bg-violet-500/20 px-2 py-0.5 rounded-md border border-violet-500/30">Sinkron</span>
                            </div>
                            <div class="text-xs text-slate-300/80 mt-0.5 leading-normal">
                                Monitoring data kependudukan terpadu dengan persetujuan bertingkat yang akurat.
                            </div>
                        </div>
                    </div>

                    <!-- Feature 3: Keamanan Data & Privasi -->
                    <div class="glass-card flex items-start gap-3.5 p-3.5 rounded-2xl">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500/25 to-cyan-600/10 text-cyan-300 flex items-center justify-center shrink-0 border border-cyan-400/30 shadow-inner">
                            <i class="bi bi-shield-lock text-xl"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white flex items-center gap-2">
                                <span>Keamanan & Hak Akses Terproteksi</span>
                                <span class="text-[10px] font-semibold text-cyan-300 bg-cyan-500/20 px-2 py-0.5 rounded-md border border-cyan-500/30">RBAC</span>
                            </div>
                            <div class="text-xs text-slate-300/80 mt-0.5 leading-normal">
                                Standar enkripsi tingkat tinggi untuk menjaga privasi data sensitif keluarga dan warga.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Glassmorphic System Status Metric Card -->
                <div class="p-4 rounded-2xl bg-gradient-to-r from-white/[0.08] to-white/[0.03] border border-white/15 backdrop-blur-md flex items-center justify-between shadow-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                            <i class="bi bi-check-circle-fill text-xl"></i>
                        </div>
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-300">Infrastruktur Layanan</div>
                            <div class="text-sm font-bold text-white flex items-center gap-1.5">
                                <span>Operasional Normal</span>
                                <span class="text-[11px] text-emerald-400 font-semibold">(24/7 Aktif)</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-300">Standar Proteksi</div>
                        <div class="text-sm font-bold text-indigo-200 flex items-center justify-end gap-1">
                            <i class="bi bi-shield-check text-emerald-400 text-xs"></i>
                            <span>SSL 256-Bit</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Footer Showcase -->
            <div class="relative z-10 pt-4 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
                <div class="flex items-center gap-2">
                    <i class="bi bi-shield-shaded text-emerald-400"></i>
                    <span>Terenkripsi Standar Nasional • Aman & Terverifikasi</span>
                </div>
                <div class="font-mono text-[11px] text-indigo-300/70">v2.6 Enterprise</div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- SISI KANAN: FORM LOGIN CARD (RESPONSIF MOBILE & DESKTOP)            -->
        <!-- =================================================================== -->
        <div class="w-full lg:w-1/2 xl:w-5/12 min-h-screen flex flex-col justify-between items-center p-5 sm:p-8 lg:p-10 xl:p-12 bg-gradient-to-b from-slate-50 via-white to-slate-100/90 relative">
            
            <!-- Mobile Top Navbar (Tampil pada Layar Kecil) -->
            <div class="w-full max-w-[430px] flex items-center justify-between lg:hidden mb-4 pt-1">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-md shadow-indigo-600/30">
                        <i class="bi bi-shield-check text-lg"></i>
                    </div>
                    <div>
                        <span class="font-extrabold text-base text-slate-900 tracking-tight">PrajaDigital</span>
                        <div class="text-[10px] text-slate-500 font-medium">Portal Layanan Wilayah</div>
                    </div>
                </div>
                <a href="{{ url('/') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50/80 border border-indigo-100 transition-colors">
                    <i class="bi bi-arrow-left"></i>
                    <span>Beranda</span>
                </a>
            </div>

            <!-- Wrapper Form Login di Tengah Layar -->
            <div class="w-full max-w-[430px] my-auto animate-card-entrance">
                
                <!-- Card Container Form Login -->
                <div class="bg-white rounded-3xl p-6 sm:p-9 shadow-[0_20px_50px_rgba(15,23,42,0.06),0_1px_3px_rgba(15,23,42,0.05)] border border-slate-200/90 relative">
                    
                    <!-- Form Header -->
                    <div class="mb-6">
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100/90 px-3 py-1 rounded-full mb-3 shadow-xs">
                            <i class="bi bi-shield-lock-fill text-indigo-600"></i>
                            <span>Autentikasi Pengguna</span>
                        </div>
                        <h2 class="text-2xl sm:text-[1.75rem] font-extrabold text-slate-900 tracking-tight leading-snug">
                            Selamat Datang
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                            Masukkan email atau username beserta kata sandi untuk mengakses akun PrajaDigital Anda.
                        </p>
                    </div>

                    <!-- Alert Session Status (Success / Notifikasi) -->
                    @if (session('status'))
                        <div class="mb-5 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/90 text-emerald-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs">
                            <i class="bi bi-check-circle-fill text-emerald-600 text-lg shrink-0 mt-0.5"></i>
                            <div class="font-medium text-emerald-900 leading-snug">{{ session('status') }}</div>
                        </div>
                    @endif

                    <!-- Alert Errors ($errors->any()) -->
                    @if ($errors->any())
                        <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200/90 text-rose-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs">
                            <i class="bi bi-exclamation-triangle-fill text-rose-600 text-lg shrink-0 mt-0.5"></i>
                            <div class="flex-1">
                                <div class="font-bold text-rose-900 mb-1">Gagal Masuk</div>
                                <ul class="list-disc pl-4 space-y-0.5 text-rose-700 text-xs">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- ======================================================= -->
                    <!-- FORM LOGIN UTAMA (ATRIBUT WAJIB DIPERTAHANKAN)          -->
                    <!-- ======================================================= -->
                    <form method="POST" action="{{ route('login') }}" id="loginForm" class="space-y-4" novalidate>
                        @csrf

                        <!-- Input Email / Username -->
                        <div>
                            <label for="email" class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                                {{ __('Email atau Username') }}
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="bi bi-person text-base"></i>
                                </div>
                                <input 
                                    id="email" 
                                    type="text" 
                                    name="email" 
                                    value="{{ old('email') }}" 
                                    required 
                                    autofocus 
                                    autocomplete="username" 
                                    placeholder="nama@email.com atau username" 
                                    class="form-login-input pl-10 pr-4 py-2.5 sm:py-3 @error('email') has-error @enderror"
                                />
                            </div>
                            @error('email')
                                <p class="text-xs text-rose-600 font-semibold flex items-center gap-1.5 mt-1.5">
                                    <i class="bi bi-x-circle-fill text-rose-500"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        <!-- Input Kata Sandi & Show/Hide Password -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-xs sm:text-sm font-bold text-slate-700">
                                    {{ __('Kata Sandi') }}
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                                        {{ __('Lupa Password?') }}
                                    </a>
                                @endif
                            </div>
                            <div class="relative flex items-center">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="bi bi-shield-lock text-base"></i>
                                </div>
                                <input 
                                    id="password" 
                                    type="password" 
                                    name="password" 
                                    required 
                                    autocomplete="current-password" 
                                    placeholder="••••••••" 
                                    class="form-login-input pl-10 pr-11 py-2.5 sm:py-3 @error('password') has-error @enderror"
                                />
                                <!-- Tombol Toggle Show/Hide Password -->
                                <button 
                                    type="button" 
                                    id="togglePasswordBtn" 
                                    class="toggle-password absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-indigo-600 focus:outline-none transition-colors cursor-pointer" 
                                    aria-label="Tampilkan kata sandi" 
                                    tabindex="-1"
                                >
                                    <i id="togglePasswordIcon" class="bi bi-eye text-base"></i>
                                </button>
                            </div>

                            <!-- Alert Caps Lock Aktif (UX Helper) -->
                            <div id="capsLockAlert" class="hidden mt-1.5 p-1.5 px-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-semibold items-center gap-1.5">
                                <i class="bi bi-exclamation-triangle-fill text-amber-500"></i>
                                <span>Perhatian: Tombol Caps Lock aktif.</span>
                            </div>

                            @error('password')
                                <p class="text-xs text-rose-600 font-semibold flex items-center gap-1.5 mt-1.5">
                                    <i class="bi bi-x-circle-fill text-rose-500"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        <!-- Checkbox "Ingat Saya" (Remember Me) -->
                        <div class="flex items-center justify-between pt-1">
                            <label for="remember" class="inline-flex items-center cursor-pointer select-none group">
                                <input 
                                    id="remember" 
                                    type="checkbox" 
                                    name="remember" 
                                    class="custom-login-checkbox"
                                />
                                <span class="ml-2.5 text-xs sm:text-sm text-slate-600 group-hover:text-slate-900 transition-colors font-medium">
                                    {{ __('Ingat Saya di perangkat ini') }}
                                </span>
                            </label>
                        </div>

                        <!-- Tombol Masuk dengan Indikator Loading -->
                        <div class="pt-2">
                            <button 
                                type="submit" 
                                id="submitBtn" 
                                class="btn-brand-submit w-full py-3 sm:py-3.5 px-5 flex items-center justify-center gap-2 group"
                            >
                                <span id="btnText" class="inline-flex items-center gap-2">
                                    <span>{{ __('Masuk ke Akun') }}</span>
                                    <i class="bi bi-box-arrow-in-right text-base group-hover:translate-x-1 transition-transform duration-200"></i>
                                </span>
                                <span id="btnLoading" class="hidden items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Memproses...</span>
                                </span>
                            </button>
                        </div>

                        <!-- Registrasi Akun Warga Baru -->
                        @if (Route::has('register'))
                            <div class="pt-4 mt-2 border-t border-slate-100 text-center">
                                <p class="text-xs sm:text-sm text-slate-500">
                                    Belum memiliki akun warga?
                                    <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline ms-1 transition-colors">
                                        {{ __('Daftar di sini') }}
                                    </a>
                                </p>
                            </div>
                        @endif
                    </form>
                </div>

                <!-- Footer Keamanan & Hak Cipta -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-slate-400 font-medium">
                        &copy; 2026 PrajaDigital. Hak Cipta Dilindungi.
                    </p>
                    <div class="mt-1.5 flex items-center justify-center gap-2 text-[11px] text-slate-400">
                        <span>Sistem Informasi Wilayah</span>
                        <span>•</span>
                        <span class="text-indigo-600 font-semibold flex items-center gap-1">
                            <i class="bi bi-lock-fill text-[10px]"></i> Keamanan Terjamin
                        </span>
                    </div>
                </div>

            </div>

            <!-- Bottom Spacer untuk menjaga keseimbangan layout vertikal pada Desktop -->
            <div class="w-full max-w-[430px] hidden lg:block"></div>
        </div>

    </div>

    <!-- SCRIPT CLIENT UX: TOGGLE SHOW/HIDE PASSWORD, CAPS LOCK DETECTION & ANTI DOUBLE-SUBMIT -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Toggle Show/Hide Password
            const togglePasswordBtn = document.getElementById('togglePasswordBtn');
            const passwordInput = document.getElementById('password');
            const togglePasswordIcon = document.getElementById('togglePasswordIcon');

            if (togglePasswordBtn && passwordInput && togglePasswordIcon) {
                togglePasswordBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        togglePasswordIcon.classList.remove('bi-eye');
                        togglePasswordIcon.classList.add('bi-eye-slash');
                        togglePasswordBtn.setAttribute('aria-label', 'Sembunyikan kata sandi');
                    } else {
                        passwordInput.type = 'password';
                        togglePasswordIcon.classList.remove('bi-eye-slash');
                        togglePasswordIcon.classList.add('bi-eye');
                        togglePasswordBtn.setAttribute('aria-label', 'Tampilkan kata sandi');
                    }
                });
            }

            // 2. Deteksi Caps Lock pada Input Password
            const capsLockAlert = document.getElementById('capsLockAlert');
            if (passwordInput && capsLockAlert) {
                const checkCapsLock = function (e) {
                    if (e.getModifierState && e.getModifierState('CapsLock')) {
                        capsLockAlert.classList.remove('hidden');
                        capsLockAlert.classList.add('flex');
                    } else {
                        capsLockAlert.classList.add('hidden');
                        capsLockAlert.classList.remove('flex');
                    }
                };

                passwordInput.addEventListener('keydown', checkCapsLock);
                passwordInput.addEventListener('keyup', checkCapsLock);
                passwordInput.addEventListener('blur', function () {
                    capsLockAlert.classList.add('hidden');
                    capsLockAlert.classList.remove('flex');
                });
            }

            // 3. Tombol Loading Spinner & Pencegahan Double Submit
            const loginForm = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');
            const emailInput = document.getElementById('email');

            if (loginForm && submitBtn && btnText && btnLoading) {
                loginForm.addEventListener('submit', function (e) {
                    // Validasi kelengkapan isian form dasar
                    if (!emailInput.value.trim() || !passwordInput.value) {
                        return; // Biarkan browser atau validasi form menangani
                    }

                    // Tampilkan status memproses dan nonaktifkan tombol
                    submitBtn.disabled = true;
                    btnText.classList.add('hidden');
                    btnText.classList.remove('inline-flex');
                    btnLoading.classList.remove('hidden');
                    btnLoading.classList.add('inline-flex');

                    // Reset tombol jika halaman dikembalikan via cache browser (bfcache / tombol Back)
                    window.addEventListener('pageshow', function () {
                        submitBtn.disabled = false;
                        btnText.classList.remove('hidden');
                        btnText.classList.add('inline-flex');
                        btnLoading.classList.add('hidden');
                        btnLoading.classList.remove('inline-flex');
                    });
                });
            }
        });
    </script>
</body>
</html>
