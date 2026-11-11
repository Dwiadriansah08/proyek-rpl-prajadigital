<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PrajaDigital') }}</title>

        <!-- Google Fonts: Plus Jakarta Sans -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --saas-primary: #4F46E5;
                --saas-primary-hover: #4338CA;
                --saas-dark: #0F172A;
                --saas-slate-100: #F1F5F9;
                --saas-slate-200: #E2E8F0;
                --saas-slate-400: #94A3B8;
                --saas-slate-600: #475569;
                --saas-slate-700: #334155;
            }

            *, *::before, *::after { box-sizing: border-box; }

            body {
                font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background-color: #F8FAFC;
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                overflow-x: hidden;
            }

            .auth-wrapper {
                width: 100%;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 32px 16px;
            }

            .auth-card {
                width: 100%;
                max-width: 440px;
                background: #FFFFFF;
                border: 1px solid var(--saas-slate-200);
                border-radius: 18px;
                padding: 36px 32px;
                box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.03);
            }

            .auth-form-title {
                font-size: 1.35rem;
                font-weight: 800;
                color: #0F172A;
                letter-spacing: -0.02em;
                margin: 0 0 6px;
            }

            .auth-form-subtitle {
                font-size: 0.85rem;
                color: #64748B;
                margin: 0 0 24px;
            }

            .auth-brand-logo {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%);
                color: #ffffff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 1.35rem;
                box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
            }

            .auth-card label {
                display: block;
                font-size: 0.825rem;
                font-weight: 600;
                color: #1E293B;
                margin-bottom: 6px;
            }

            .auth-card input[type="text"],
            .auth-card input[type="email"],
            .auth-card input[type="password"],
            .auth-card select {
                width: 100%;
                padding: 10px 14px;
                font-size: 0.9rem;
                font-family: inherit;
                color: #0F172A;
                background: #F8FAFC;
                border: 1.5px solid var(--saas-slate-200);
                border-radius: 10px;
                outline: none;
                transition: border-color 0.2s, background-color 0.2s, box-shadow 0.2s;
            }

            .auth-card input:focus,
            .auth-card select:focus {
                background: #FFFFFF;
                border-color: var(--saas-primary);
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
            }

            .auth-card button[type="submit"] {
                width: 100%;
                padding: 11px 20px;
                font-size: 0.925rem;
                font-weight: 700;
                font-family: inherit;
                color: #FFFFFF;
                background: var(--saas-primary);
                border: none;
                border-radius: 10px;
                cursor: pointer;
                box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);
                transition: background-color 0.18s, transform 0.1s, box-shadow 0.18s;
            }

            .auth-card button[type="submit"]:hover {
                background: var(--saas-primary-hover);
                box-shadow: 0 6px 18px rgba(79, 70, 229, 0.35);
                transform: translateY(-1px);
            }

            .auth-card button[type="submit"]:active {
                transform: translateY(0);
            }

            .auth-card .toggle-password {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                color: #94A3B8;
                cursor: pointer;
                padding: 4px;
                font-size: 1.05rem;
                transition: color 0.15s;
            }

            .auth-card .toggle-password:hover {
                color: #334155;
            }

            @media (max-width: 575.98px) {
                .auth-card {
                    padding: 28px 20px;
                    border-radius: 16px;
                    border: none;
                    box-shadow: none;
                    background: transparent;
                }
            }
        </style>
    </head>
    <body>
        <div class="auth-wrapper">
            <div class="auth-card">
                <!-- Branding PrajaDigital Sederhana -->
                <div class="text-center mb-4">
                    <div class="auth-brand-logo mb-2">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="fw-bold text-dark" style="font-size: 1.2rem; letter-spacing: -0.02em;">PrajaDigital</div>
                    <div class="text-muted small" style="font-size: 0.78rem;">Sistem Informasi Wilayah</div>
                </div>

                {{ $slot }}
            </div>
        </div>

        <script>
            // Global toggle password handler
            document.addEventListener('click', function(e) {
                const toggleBtn = e.target.closest('.toggle-password');
                if (!toggleBtn) return;

                e.preventDefault();
                const targetId = toggleBtn.getAttribute('data-target');
                const input = targetId ? document.getElementById(targetId) : toggleBtn.parentElement.querySelector('input');
                if (!input) return;

                const icon = toggleBtn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) {
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    }
                    toggleBtn.setAttribute('aria-label', 'Sembunyikan password');
                } else {
                    input.type = 'password';
                    if (icon) {
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                    toggleBtn.setAttribute('aria-label', 'Tampilkan password');
                }
            });
        </script>
    </body>
</html>
