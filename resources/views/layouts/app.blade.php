<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem Informasi Wilayah') }}</title>

        <!-- Google Fonts: Plus Jakarta Sans & Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
        
        <!-- Icons & Frameworks -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <style>
            :root {
                --saas-primary: #4F46E5;
                --saas-primary-hover: #4338CA;
                --saas-primary-light: #EEF2FF;
                --saas-accent: #06B6D4;
                --saas-dark: #0F172A;
                --saas-dark-surface: #1E293B;
                --saas-slate-50: #F8FAFC;
                --saas-slate-100: #F1F5F9;
                --saas-slate-200: #E2E8F0;
                --saas-slate-300: #CBD5E1;
                --saas-slate-400: #94A3B8;
                --saas-slate-500: #64748B;
                --saas-slate-600: #475569;
                --saas-slate-700: #334155;
                --saas-slate-800: #1E293B;
                --saas-slate-900: #0F172A;
                
                --saas-border: rgba(226, 232, 240, 0.85);
                --saas-card-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.05), 0 10px 25px -5px rgba(15, 23, 42, 0.04);
                --saas-card-shadow-hover: 0 10px 30px -10px rgba(79, 70, 229, 0.12), 0 20px 25px -5px rgba(15, 23, 42, 0.06);
            }

            *, *::before, *::after {
                box-sizing: border-box;
            }

            body { 
                font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
                background: linear-gradient(180deg, #F8FAFC 0%, #F1F5F9 100%);
                color: #0F172A; 
                letter-spacing: -0.015em;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            /* ========== FULL-WIDTH PREMIUM HORIZONTAL NAVBAR ========== */
            .saas-navbar {
                background: rgba(255, 255, 255, 0.88);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border-bottom: 1px solid var(--saas-border);
                position: sticky;
                top: 0;
                z-index: 1030;
                transition: all 0.2s ease-in-out;
            }

            .saas-nav-container {
                max-width: 1400px;
                margin: 0 auto;
                padding: 0 24px;
                height: 72px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
            }

            /* Brand */
            .saas-brand {
                display: flex;
                align-items: center;
                gap: 12px;
                text-decoration: none;
                flex-shrink: 0;
            }

            .saas-brand-logo {
                width: 42px;
                height: 42px;
                border-radius: 12px;
                background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
                color: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
                box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
                transition: transform 0.2s ease;
            }

            .saas-brand:hover .saas-brand-logo {
                transform: scale(1.04);
            }

            .saas-brand-text {
                display: flex;
                flex-direction: column;
            }

            .saas-brand-title {
                font-size: 1.05rem;
                font-weight: 800;
                color: #0F172A;
                letter-spacing: -0.025em;
                line-height: 1.15;
            }

            .saas-brand-subtitle {
                font-size: 0.72rem;
                font-weight: 600;
                color: var(--saas-slate-400);
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            /* Main Navigation Links (Desktop) */
            .saas-nav-links {
                display: flex;
                align-items: center;
                gap: 4px;
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .saas-nav-link {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 14px;
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--saas-slate-600);
                border-radius: 10px;
                text-decoration: none;
                transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
                white-space: nowrap;
            }

            .saas-nav-link i {
                font-size: 1.05rem;
                color: var(--saas-slate-400);
                transition: color 0.18s ease;
            }

            .saas-nav-link:hover {
                color: var(--saas-primary);
                background: rgba(238, 242, 255, 0.7);
            }

            .saas-nav-link:hover i {
                color: var(--saas-primary);
            }

            .saas-nav-link.active {
                color: var(--saas-primary);
                background: #EEF2FF;
                font-weight: 700;
            }

            .saas-nav-link.active i {
                color: var(--saas-primary);
            }

            /* Dropdown Menu styling */
            .saas-dropdown-menu {
                border: 1px solid var(--saas-border);
                border-radius: 16px;
                box-shadow: 0 10px 40px -10px rgba(15, 23, 42, 0.12), 0 4px 12px -2px rgba(15, 23, 42, 0.05);
                padding: 8px;
                min-width: 220px;
                background: #ffffff;
                animation: saasDropdownIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            }

            @keyframes saasDropdownIn {
                from { opacity: 0; transform: translateY(8px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .saas-dropdown-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 9px 12px;
                font-size: 0.84rem;
                font-weight: 500;
                color: var(--saas-slate-700);
                border-radius: 8px;
                text-decoration: none;
                transition: all 0.15s ease;
            }

            .saas-dropdown-item i {
                font-size: 1rem;
                color: var(--saas-slate-400);
            }

            .saas-dropdown-item:hover {
                color: var(--saas-primary);
                background: #F8FAFC;
            }

            .saas-dropdown-item:hover i {
                color: var(--saas-primary);
            }

            .saas-dropdown-item.active {
                color: var(--saas-primary);
                background: #EEF2FF;
                font-weight: 600;
            }

            /* Right actions */
            .saas-nav-actions {
                display: flex;
                align-items: center;
                gap: 14px;
                flex-shrink: 0;
            }

            /* Notification Icon Button */
            .saas-icon-btn {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--saas-border);
                background: #ffffff;
                color: var(--saas-slate-600);
                position: relative;
                cursor: pointer;
                transition: all 0.15s ease;
                text-decoration: none;
            }

            .saas-icon-btn:hover {
                background: #F8FAFC;
                border-color: var(--saas-slate-300);
                color: #0F172A;
            }

            .saas-notif-ping {
                position: absolute;
                top: 7px;
                right: 7px;
                width: 9px;
                height: 9px;
                background: #EF4444;
                border-radius: 50%;
                border: 2px solid #ffffff;
            }

            /* Profile Pill */
            .saas-user-pill {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 5px 12px 5px 6px;
                border-radius: 9999px;
                border: 1px solid var(--saas-border);
                background: #ffffff;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.18s ease;
            }

            .saas-user-pill:hover {
                border-color: var(--saas-slate-300);
                background: #F8FAFC;
                box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            }

            .saas-avatar {
                width: 34px;
                height: 34px;
                border-radius: 50%;
                background: linear-gradient(135deg, #4F46E5 0%, #06B6D4 100%);
                color: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 0.85rem;
                letter-spacing: -0.01em;
                box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
            }

            .saas-user-info {
                display: flex;
                flex-direction: column;
                text-align: left;
                line-height: 1.15;
            }

            .saas-user-name {
                font-size: 0.84rem;
                font-weight: 700;
                color: #0F172A;
                max-width: 140px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .saas-user-role {
                font-size: 0.68rem;
                font-weight: 600;
                color: var(--saas-primary);
                text-transform: uppercase;
                letter-spacing: 0.03em;
            }

            /* Hamburger Toggle */
            .saas-mobile-toggle {
                display: none;
                width: 40px;
                height: 40px;
                border-radius: 10px;
                border: 1px solid var(--saas-border);
                background: #ffffff;
                color: var(--saas-slate-700);
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
                cursor: pointer;
                transition: all 0.15s ease;
            }

            .saas-mobile-toggle:hover {
                background: #F8FAFC;
            }

            /* Mobile Navigation Drawer / Menu */
            .saas-mobile-drawer {
                display: none;
                border-bottom: 1px solid var(--saas-border);
                background: #ffffff;
                padding: 16px 24px;
                animation: saasDrawerIn 0.25s ease-out;
            }

            .saas-mobile-drawer.open {
                display: block;
            }

            @keyframes saasDrawerIn {
                from { opacity: 0; transform: translateY(-8px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .saas-mobile-nav {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .saas-mobile-link {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px 14px;
                font-size: 0.9rem;
                font-weight: 600;
                color: var(--saas-slate-700);
                border-radius: 10px;
                text-decoration: none;
                transition: background 0.15s ease;
            }

            .saas-mobile-link i {
                font-size: 1.15rem;
                color: var(--saas-slate-400);
            }

            .saas-mobile-link:hover, .saas-mobile-link.active {
                background: #EEF2FF;
                color: var(--saas-primary);
            }

            .saas-mobile-link.active i {
                color: var(--saas-primary);
            }

            /* Responsive rules */
            @media (max-width: 1080px) {
                .saas-nav-links { display: none; }
                .saas-mobile-toggle { display: inline-flex; }
                .saas-user-info { display: none; }
                .saas-user-pill { padding: 4px; border: none; }
            }

            /* ========== LAYOUT & MAIN CONTENT CONTAINER ========== */
            .saas-main-container {
                flex: 1;
                width: 100%;
                max-width: 1400px;
                margin: 0 auto;
                padding: 28px 24px 64px 24px;
            }

            /* Subheader banner */
            .saas-page-header {
                margin-bottom: 24px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 16px;
            }

            .saas-page-title {
                font-size: 1.5rem;
                font-weight: 800;
                color: #0F172A;
                letter-spacing: -0.03em;
                margin: 0;
            }

            .saas-page-desc {
                font-size: 0.875rem;
                color: var(--saas-slate-500);
                margin-top: 4px;
                margin-bottom: 0;
            }

            /* ========== CARD & UI ELEMENTS DESIGN SYSTEM ========== */
            .card {
                background: #ffffff;
                border: 1px solid var(--saas-border);
                border-radius: 18px;
                box-shadow: var(--saas-card-shadow);
                transition: box-shadow 0.22s ease, transform 0.22s ease, border-color 0.22s ease;
            }

            .card:hover {
                box-shadow: var(--saas-card-shadow-hover);
            }

            .card-header {
                background: transparent;
                border-bottom: 1px solid var(--saas-border);
                padding: 18px 24px;
            }

            /* Buttons */
            .btn-primary {
                background: var(--saas-primary);
                border-color: var(--saas-primary);
                border-radius: 10px;
                font-weight: 600;
                font-size: 0.875rem;
                padding: 8px 18px;
                box-shadow: 0 2px 4px rgba(79, 70, 229, 0.15);
                transition: all 0.18s ease;
            }

            .btn-primary:hover, .btn-primary:focus {
                background: var(--saas-primary-hover);
                border-color: var(--saas-primary-hover);
                box-shadow: 0 4px 12px rgba(79, 70, 229, 0.28);
                transform: translateY(-1px);
            }

            .btn-light {
                background: #ffffff;
                border: 1px solid var(--saas-border);
                color: var(--saas-slate-700);
                font-weight: 600;
                font-size: 0.875rem;
                border-radius: 10px;
                transition: all 0.15s ease;
            }

            .btn-light:hover {
                background: #F8FAFC;
                border-color: var(--saas-slate-300);
                color: #0F172A;
            }

            /* Tables */
            .table {
                --bs-table-bg: transparent;
                margin-bottom: 0;
            }

            .table thead th {
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                color: var(--saas-slate-400);
                background: #F8FAFC;
                border-bottom: 1px solid var(--saas-border);
                padding: 12px 16px;
            }

            .table tbody td {
                padding: 14px 16px;
                vertical-align: middle;
                border-bottom: 1px solid #F1F5F9;
                font-size: 0.875rem;
                color: var(--saas-slate-700);
            }

            .table tbody tr:hover td {
                background: rgba(248, 250, 252, 0.7);
            }

            /* Form Inputs */
            .form-control, .form-select {
                border: 1.5px solid var(--saas-border);
                border-radius: 10px;
                padding: 9px 14px;
                font-size: 0.875rem;
                color: #0F172A;
                transition: all 0.18s ease;
            }

            .form-control:focus, .form-select:focus {
                border-color: var(--saas-primary);
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
            }

            /* Badges */
            .badge {
                font-weight: 600;
                padding: 5px 10px;
                border-radius: 6px;
                font-size: 0.75rem;
                letter-spacing: 0.01em;
            }

            /* ========== FLOATING AI CHATBOT ========== */
            .chatbot-fab {
                position: fixed;
                bottom: 24px;
                right: 24px;
                width: 52px;
                height: 52px;
                border-radius: 16px;
                background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
                color: #fff;
                border: none;
                box-shadow: 0 8px 24px rgba(79, 70, 229, 0.38);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.35rem;
                cursor: pointer;
                z-index: 1040;
                transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease;
            }

            .chatbot-fab:hover {
                transform: scale(1.06) translateY(-2px);
                box-shadow: 0 12px 28px rgba(79, 70, 229, 0.48);
            }

            .chatbot-panel {
                position: fixed;
                bottom: 88px;
                right: 24px;
                width: 420px;
                max-height: calc(100vh - 120px);
                background: #ffffff;
                border-radius: 20px;
                border: 1px solid var(--saas-border);
                box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.2), 0 0 1px 1px rgba(15, 23, 42, 0.05);
                display: none;
                flex-direction: column;
                z-index: 1040;
                overflow: hidden;
                animation: panelIn .25s cubic-bezier(0.16, 1, 0.3, 1);
            }

            .chatbot-panel.open { display: flex; }

            @keyframes panelIn {
                from { opacity: 0; transform: translateY(14px) scale(.98); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }

            .chatbot-panel-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 16px 20px;
                background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
                color: #fff;
                flex-shrink: 0;
            }

            .chatbot-panel-header .panel-title {
                display: flex; align-items: center; gap: 10px;
                font-weight: 700; font-size: .95rem;
            }

            .chatbot-panel-header .panel-title i { color: #A5B4FC; }

            .chatbot-panel-close {
                background: rgba(255,255,255,.1);
                border: none;
                color: #fff;
                width: 32px; height: 32px;
                border-radius: 8px;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer;
                transition: background .15s;
                font-size: .9rem;
            }
            .chatbot-panel-close:hover { background: rgba(255,255,255,.2); }

            .chatbot-conv-list {
                max-height: 180px;
                overflow-y: auto;
                border-bottom: 1px solid #F1F5F9;
                padding: 8px;
                flex-shrink: 0;
                background: #F8FAFC;
            }

            .chatbot-conv-item {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 10px;
                border-radius: 8px;
                cursor: pointer;
                font-size: .78rem;
                color: var(--saas-slate-700);
                transition: background .12s;
            }
            .chatbot-conv-item:hover { background: #EEF2FF; color: var(--saas-primary); }
            .chatbot-conv-item.active { background: #EEF2FF; color: var(--saas-primary); font-weight: 700; }
            .chatbot-conv-item i { color: var(--saas-slate-400); font-size: .85rem; flex-shrink: 0; }

            .chatbot-conv-title {
                flex: 1;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .chatbot-conv-delete {
                opacity: 0;
                background: none;
                border: none;
                color: var(--saas-slate-400);
                font-size: .75rem;
                cursor: pointer;
                padding: 2px 4px;
                border-radius: 4px;
                flex-shrink: 0;
            }
            .chatbot-conv-item:hover .chatbot-conv-delete { opacity: 1; }
            .chatbot-conv-delete:hover { color: #EF4444; background: #FEF2F2; }

            .chatbot-new-chat {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                width: 100%;
                padding: 7px;
                border: 1px dashed var(--saas-slate-300);
                background: #ffffff;
                border-radius: 8px;
                font-size: .75rem;
                font-weight: 600;
                color: var(--saas-slate-600);
                cursor: pointer;
                transition: all .15s;
                margin-top: 4px;
            }
            .chatbot-new-chat:hover { border-color: var(--saas-primary); color: var(--saas-primary); background: #EEF2FF; }

            .chatbot-messages {
                flex: 1;
                overflow-y: auto;
                padding: 16px;
                display: flex;
                flex-direction: column;
                gap: 12px;
                background: #FAFAFA;
                min-height: 220px;
            }

            .chatbot-empty {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 32px 18px;
                background: #FAFAFA;
            }
            .chatbot-empty-icon {
                width: 52px; height: 52px;
                border-radius: 14px;
                background: #EEF2FF;
                display: flex; align-items: center; justify-content: center;
                margin-bottom: 12px;
            }
            .chatbot-empty-icon i { font-size: 1.45rem; color: var(--saas-primary); }
            .chatbot-empty h6 { font-weight: 700; color: #0F172A; margin-bottom: 4px; font-size: .92rem; }
            .chatbot-empty p { color: var(--saas-slate-500); font-size: .8rem; margin-bottom: 16px; line-height: 1.45; max-width: 290px; }
            .chatbot-chips { display: flex; gap: 6px; flex-wrap: wrap; justify-content: center; }
            .chatbot-chip {
                border: 1px solid var(--saas-border); background: #fff; color: var(--saas-slate-700);
                padding: 6px 12px; border-radius: 9999px; font-size: .74rem;
                font-weight: 600; cursor: pointer; transition: all .15s;
                display: flex; align-items: center; gap: 5px;
            }
            .chatbot-chip:hover { border-color: var(--saas-primary); color: var(--saas-primary); background: #EEF2FF; }

            .cb-msg-row {
                display: flex; align-items: flex-start; gap: 8px;
                max-width: 88%;
                animation: cbMsgIn .2s ease;
            }
            @keyframes cbMsgIn {
                from { opacity: 0; transform: translateY(6px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .cb-msg-user { align-self: flex-end; flex-direction: row-reverse; }
            .cb-msg-ai { align-self: flex-start; }

            .cb-msg-avatar {
                width: 28px; height: 28px; border-radius: 8px;
                display: flex; align-items: center; justify-content: center;
                font-weight: 700; font-size: .7rem; flex-shrink: 0;
            }
            .cb-avatar-user { background: var(--saas-primary); color: #fff; }
            .cb-avatar-ai { background: #EEF2FF; color: var(--saas-primary); font-size: .85rem; }

            .cb-msg-bubble {
                padding: 10px 14px; border-radius: 14px;
                line-height: 1.5; font-size: .825rem; word-break: break-word;
            }
            .cb-msg-bubble-user {
                background: var(--saas-primary); color: #fff;
                border-bottom-right-radius: 4px;
            }
            .cb-msg-bubble-ai {
                background: #ffffff; color: #0F172A;
                border: 1px solid var(--saas-border);
                border-bottom-left-radius: 4px;
                line-height: 1.55;
                font-size: .84rem;
                word-break: break-word;
            }
            .cb-msg-bubble-ai p { margin-bottom: 6px; }
            .cb-msg-bubble-ai p:last-child { margin-bottom: 0; }
            .cb-msg-bubble-ai strong { font-weight: 700; color: #1E293B; }
            .cb-msg-bubble-ai em { font-style: italic; }
            .cb-msg-bubble-ai ul, .cb-msg-bubble-ai ol { margin: 4px 0 6px 0; padding-left: 20px; }
            .cb-msg-bubble-ai ul { list-style-type: disc; }
            .cb-msg-bubble-ai ol { list-style-type: decimal; }
            .cb-msg-bubble-ai li { margin-bottom: 2px; }
            .cb-msg-bubble-ai h5, .cb-msg-bubble-ai h6 { margin: 8px 0 4px 0; font-weight: 700; color: #0F172A; }
            .cb-msg-bubble-ai code { background: #EEF2FF; color: var(--saas-primary); padding: 1px 4px; border-radius: 4px; font-size: .8em; font-family: 'JetBrains Mono', monospace; }
            .cb-msg-bubble-ai pre { background: #0F172A; color: #F8FAFC; padding: 8px 12px; border-radius: 8px; overflow-x: auto; margin: 6px 0; font-size: .78rem; }
            .cb-msg-bubble-ai pre code { background: transparent; color: inherit; padding: 0; }
            .cb-msg-bubble-ai blockquote { border-left: 3px solid var(--saas-primary); padding-left: 8px; margin: 6px 0; color: var(--saas-slate-600); }
            .cb-msg-bubble-ai table { width: 100%; border-collapse: collapse; margin: 6px 0; font-size: .78rem; }
            .cb-msg-bubble-ai th, .cb-msg-bubble-ai td { border: 1px solid #E2E8F0; padding: 4px 8px; text-align: left; }
            .cb-msg-bubble-ai th { background: #F8FAFC; font-weight: 600; }

            .cb-error-msg {
                display: flex; align-items: center; gap: 8px;
                padding: 8px 12px; background: #FEF2F2; border: 1px solid #FCA5A5;
                border-radius: 10px; font-size: .78rem; color: #DC2626; align-self: flex-start;
            }
            .cb-retry-btn {
                background: #fff; border: 1px solid #FCA5A5; color: #DC2626;
                padding: 3px 10px; border-radius: 6px; font-size: .72rem; font-weight: 600; cursor: pointer;
            }

            .cb-thinking {
                display: flex; align-items: center; gap: 7px;
                align-self: flex-start; max-width: 88%;
            }
            .cb-thinking-bubble {
                background: #ffffff; border: 1px solid var(--saas-border);
                border-radius: 12px; border-bottom-left-radius: 4px;
                padding: 8px 14px; display: flex; align-items: center; gap: 7px;
            }
            .cb-thinking-label { font-size: .75rem; color: var(--saas-slate-400); font-weight: 500; }
            .cb-thinking-dots { display: flex; gap: 3px; }
            .cb-thinking-dots span {
                width: 5px; height: 5px; border-radius: 50%; background: var(--saas-primary);
                animation: cbDotPulse 1.4s infinite ease-in-out;
            }
            .cb-thinking-dots span:nth-child(2) { animation-delay: .2s; }
            .cb-thinking-dots span:nth-child(3) { animation-delay: .4s; }

            .chatbot-input {
                padding: 12px 14px;
                border-top: 1px solid var(--saas-border);
                background: #fff;
                flex-shrink: 0;
            }
            .chatbot-input-box {
                display: flex; align-items: flex-end; gap: 6px;
                background: #F8FAFC; border-radius: 12px;
                padding: 6px 6px 6px 12px; border: 1.5px solid var(--saas-border);
                transition: all .15s;
            }
            .chatbot-input-box:focus-within {
                background: #fff;
                border-color: var(--saas-primary);
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
            }
            .chatbot-input-box textarea {
                flex: 1; border: none; background: transparent; resize: none;
                font-size: .825rem; line-height: 1.4; padding: 4px 0;
                max-height: 80px; color: #0F172A; outline: none; font-family: inherit;
            }
            .chatbot-send-btn {
                width: 34px; height: 34px; border: none; background: var(--saas-primary);
                color: #fff; border-radius: 8px; display: flex;
                align-items: center; justify-content: center; font-size: .95rem; cursor: pointer;
            }
            .chatbot-send-btn:hover { background: var(--saas-primary-hover); }
            .chatbot-send-btn:disabled { background: var(--saas-slate-300); cursor: not-allowed; }

            @media (max-width: 575.98px) {
                .chatbot-panel { right: 12px; left: 12px; bottom: 84px; width: auto; }
                .chatbot-fab { bottom: 18px; right: 18px; width: 48px; height: 48px; font-size: 1.25rem; }
                .saas-nav-container { padding: 0 16px; }
                .saas-main-container { padding: 20px 16px 48px 16px; }
            }
        </style>
    </head>
    <body class="antialiased">
        <!-- ========== FULL-WIDTH PREMIUM HORIZONTAL NAVBAR ========== -->
        <header class="saas-navbar">
            <div class="saas-nav-container">
                <!-- Brand / Logo & Desa Name -->
                <a href="{{ route('dashboard') }}" class="saas-brand">
                    <div class="saas-brand-logo">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="saas-brand-text">
                        <span class="saas-brand-title">PrajaDigital</span>
                        <span class="saas-brand-subtitle">{{ config('app.name', 'Sistem Wilayah') }}</span>
                    </div>
                </a>

                <!-- Desktop Navigation Menu -->
                <nav>
                    <ul class="saas-nav-links">
                        <li>
                            <a href="{{ route('dashboard') }}" class="saas-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="bi bi-grid-fill"></i> Dashboard
                            </a>
                        </li>

                        @if(auth()->user()->hasRole('super_admin'))
                        <!-- Dropdown Wilayah & Master -->
                        <li class="dropdown">
                            <a href="#" class="saas-nav-link {{ request()->routeIs('desa.*') || request()->routeIs('rw.*') || request()->routeIs('rt.*') ? 'active' : '' }}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-geo-alt-fill"></i> Wilayah <i class="bi bi-chevron-down ms-1" style="font-size:0.75rem;"></i>
                            </a>
                            <ul class="dropdown-menu saas-dropdown-menu">
                                <li>
                                    <a class="dropdown-item saas-dropdown-item {{ request()->routeIs('desa.*') ? 'active' : '' }}" href="{{ route('desa.index') }}">
                                        <i class="bi bi-building"></i> Profil Desa
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item saas-dropdown-item {{ request()->routeIs('rw.*') ? 'active' : '' }}" href="{{ route('rw.index') }}">
                                        <i class="bi bi-diagram-3"></i> Manajemen RW
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item saas-dropdown-item {{ request()->routeIs('rt.*') ? 'active' : '' }}" href="{{ route('rt.index') }}">
                                        <i class="bi bi-signpost-split"></i> Manajemen RT
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="{{ route('warga.index') }}" class="saas-nav-link {{ request()->routeIs('warga.*') ? 'active' : '' }}">
                                <i class="bi bi-person-lines-fill"></i> Data Warga
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('users.index') }}" class="saas-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="bi bi-people-fill"></i> Manajemen User
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('laporan.index') }}" class="saas-nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-bar-graph-fill"></i> Laporan
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('konfigurasi.index') }}" class="saas-nav-link {{ request()->routeIs('konfigurasi.*') ? 'active' : '' }}">
                                <i class="bi bi-gear-wide-connected"></i> Konfigurasi AI
                            </a>
                        </li>
                        @elseif(auth()->user()->hasRole('admin_desa'))
                        <li class="dropdown">
                            <a href="#" class="saas-nav-link {{ request()->routeIs('rw.*') || request()->routeIs('rt.*') ? 'active' : '' }}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-diagram-3-fill"></i> RT / RW <i class="bi bi-chevron-down ms-1" style="font-size:0.75rem;"></i>
                            </a>
                            <ul class="dropdown-menu saas-dropdown-menu">
                                <li>
                                    <a class="dropdown-item saas-dropdown-item {{ request()->routeIs('rw.*') ? 'active' : '' }}" href="{{ route('rw.index') }}">
                                        <i class="bi bi-diagram-3"></i> Manajemen RW
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item saas-dropdown-item {{ request()->routeIs('rt.*') ? 'active' : '' }}" href="{{ route('rt.index') }}">
                                        <i class="bi bi-signpost-split"></i> Manajemen RT
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="{{ route('warga.index') }}" class="saas-nav-link {{ request()->routeIs('warga.*') ? 'active' : '' }}">
                                <i class="bi bi-person-lines-fill"></i> Data Warga
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('users.index') }}" class="saas-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="bi bi-people-fill"></i> Akun RT/RW & Warga
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('laporan.index') }}" class="saas-nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-bar-graph-fill"></i> Laporan & Rekap
                            </a>
                        </li>
                        @elseif(auth()->user()->hasRole('rw'))
                        <li>
                            <a href="{{ route('rt.index') }}" class="saas-nav-link {{ request()->routeIs('rt.*') ? 'active' : '' }}">
                                <i class="bi bi-signpost-split"></i> Manajemen RT
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('warga.index') }}" class="saas-nav-link {{ request()->routeIs('warga.*') ? 'active' : '' }}">
                                <i class="bi bi-person-lines-fill"></i> Data Warga
                            </a>
                        </li>
                        @elseif(auth()->user()->hasRole('rt'))
                        <li>
                            <a href="{{ route('warga.index') }}" class="saas-nav-link {{ request()->routeIs('warga.*') ? 'active' : '' }}">
                                <i class="bi bi-person-lines-fill"></i> Data Warga
                            </a>
                        </li>
                        @endif

                        <li>
                            <a href="{{ route('surat.index') }}" class="saas-nav-link {{ request()->routeIs('surat.*') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-text-fill"></i> {{ auth()->user()->hasRole('warga') ? 'Layanan Surat' : (auth()->user()->hasRole('admin_desa') ? 'Verifikasi & Penerbitan Surat' : 'Pengajuan Surat') }}
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('informasi.index') }}" class="saas-nav-link {{ request()->routeIs('informasi.*') ? 'active' : '' }}">
                                <i class="bi bi-megaphone-fill"></i> {{ auth()->user()->hasRole('admin_desa') ? 'Pengumuman Desa' : 'Informasi Desa' }}
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Right Actions: Notifications, Profile Dropdown & Mobile Hamburger -->
                <div class="saas-nav-actions">
                    <!-- Notifications -->
                    @php
                        $user = auth()->user();
                        $roleName = $user->role ? strtolower($user->role->name) : 'warga';
                        $notifItems = collect();

                        if ($roleName === 'warga') {
                            $wId = $user->warga?->id;
                            if ($wId) {
                                $suratSelesai = \App\Models\PengajuanSurat::where('warga_id', $wId)->where('status', 'SELESAI')->where('updated_at', '>=', now()->subDays(5))->get();
                                foreach ($suratSelesai as $s) {
                                    $notifItems->push([
                                        'type' => 'success',
                                        'icon' => 'bi-check-circle-fill text-success',
                                        'title' => 'Surat Selesai',
                                        'desc' => "Pengajuan {$s->template?->nama_surat} Anda telah disetujui RW dan siap diunduh.",
                                        'link' => route('surat.show', $s->id),
                                    ]);
                                }
                                $suratDitolak = \App\Models\PengajuanSurat::where('warga_id', $wId)->where('status', 'DITOLAK')->where('updated_at', '>=', now()->subDays(5))->get();
                                foreach ($suratDitolak as $s) {
                                    $notifItems->push([
                                        'type' => 'danger',
                                        'icon' => 'bi-x-circle-fill text-danger',
                                        'title' => 'Surat Ditolak',
                                        'desc' => "Pengajuan {$s->template?->nama_surat} ditolak: " . ($s->catatan ?? 'Cek revisi'),
                                        'link' => route('surat.show', $s->id),
                                    ]);
                                }
                            }
                        } elseif ($roleName === 'rt' && $user->rt_id) {
                            $butuhRt = \App\Models\PengajuanSurat::where('status', 'MENUNGGU_RT')
                                ->whereHas('warga', fn($q) => $q->where('rt_id', $user->rt_id))
                                ->get();
                            foreach ($butuhRt as $s) {
                                $notifItems->push([
                                    'type' => 'warning',
                                    'icon' => 'bi-clock-fill text-warning',
                                    'title' => 'Butuh Tindakan RT',
                                    'desc' => "Permohonan {$s->template?->nama_surat} dari {$s->warga?->nama_lengkap} menunggu verifikasi Anda.",
                                    'link' => route('surat.show', $s->id),
                                ]);
                            }
                        } elseif ($roleName === 'rw' && $user->rw_id) {
                            $butuhRw = \App\Models\PengajuanSurat::where('status', 'MENUNGGU_RW')
                                ->whereHas('warga', fn($q) => $q->where('rw_id', $user->rw_id))
                                ->get();
                            foreach ($butuhRw as $s) {
                                $notifItems->push([
                                    'type' => 'primary',
                                    'icon' => 'bi-envelope-check-fill text-primary',
                                    'title' => 'Butuh Persetujuan RW',
                                    'desc' => "Surat {$s->template?->nama_surat} dari {$s->warga?->nama_lengkap} disetujui RT, menunggu RW.",
                                    'link' => route('surat.show', $s->id),
                                ]);
                            }
                        } elseif (in_array($roleName, ['admin_desa', 'super_admin'])) {
                            $butuhDesa = \App\Models\PengajuanSurat::where('status', 'MENUNGGU_ADMIN_DESA')->get();
                            foreach ($butuhDesa as $s) {
                                $notifItems->push([
                                    'type' => 'primary',
                                    'icon' => 'bi-file-earmark-check-fill text-primary',
                                    'title' => 'Butuh Verifikasi Desa',
                                    'desc' => "Surat {$s->template?->nama_surat} dari {$s->warga?->nama_lengkap} disetujui RW, menunggu penerbitan desa.",
                                    'link' => route('surat.show', $s->id),
                                ]);
                            }
                        }

                        // Kabar info terbaru
                        $infoTerbaru = \App\Models\Informasi::where('status_publikasi', 'published')->where('created_at', '>=', now()->subDays(3))->take(2)->get();
                        foreach ($infoTerbaru as $info) {
                            $notifItems->push([
                                'type' => 'info',
                                'icon' => 'bi-megaphone-fill text-info',
                                'title' => 'Informasi Desa',
                                'desc' => $info->judul,
                                'link' => route('informasi.show', $info->id),
                            ]);
                        }

                        $totalNotif = $notifItems->count();
                    @endphp
                    <div class="dropdown">
                        <a href="#" class="saas-icon-btn" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi">
                            <i class="bi bi-bell"></i>
                            @if($totalNotif > 0)
                                <span class="saas-notif-ping"></span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end saas-dropdown-menu mt-2 p-0 shadow-lg" style="width: 330px; max-height: 420px; overflow-y: auto;">
                            <li class="p-3 border-bottom bg-slate-50 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold" style="font-size:0.875rem;">Notifikasi Sistem</h6>
                                @if($totalNotif > 0)
                                    <span class="badge bg-primary rounded-pill">{{ $totalNotif }} Baru</span>
                                @endif
                            </li>
                            @if($totalNotif > 0)
                                @foreach($notifItems as $item)
                                <li>
                                    <a class="dropdown-item p-3 border-bottom text-wrap" href="{{ $item['link'] }}">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi {{ $item['icon'] }} fs-5"></i>
                                            <div>
                                                <div class="fw-semibold text-dark" style="font-size:0.84rem;">{{ $item['title'] }}</div>
                                                <div class="text-muted" style="font-size:0.75rem;">{{ $item['desc'] }}</div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                @endforeach
                            @else
                                <li class="p-4 text-center text-muted">
                                    <i class="bi bi-bell-slash fs-4 d-block opacity-40 mb-2"></i>
                                    <span style="font-size:0.82rem;">Tidak ada pemberitahuan baru</span>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <div class="saas-user-pill" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                            <div class="saas-avatar">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="saas-user-info">
                                <span class="saas-user-name">{{ auth()->user()->name }}</span>
                                <span class="saas-user-role">{{ str_replace('_', ' ', auth()->user()->role?->name ?? 'User') }}</span>
                            </div>
                            <i class="bi bi-chevron-down ms-1 text-muted" style="font-size: 0.7rem;"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end saas-dropdown-menu mt-2" aria-labelledby="profileDropdown">
                            <li class="px-3 py-2 border-bottom mb-1">
                                <div class="fw-bold text-dark" style="font-size:0.85rem;">{{ auth()->user()->name }}</div>
                                <div class="text-muted" style="font-size:0.75rem;">{{ auth()->user()->email }}</div>
                            </li>
                            @if(auth()->user()->hasRole('warga') && auth()->user()->warga)
                            <li>
                                <a class="dropdown-item saas-dropdown-item" href="{{ route('warga.show', auth()->user()->warga->id) }}">
                                    <i class="bi bi-person-badge"></i> Profil Warga
                                </a>
                            </li>
                            @endif
                            <li>
                                <a class="dropdown-item saas-dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-gear"></i> Pengaturan Akun
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item saas-dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right text-danger"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>

                    <!-- Mobile Hamburger Button -->
                    <button class="saas-mobile-toggle" id="saasMobileToggle" type="button" aria-label="Toggle navigation">
                        <i class="bi bi-list" id="saasMobileToggleIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div class="saas-mobile-drawer" id="saasMobileDrawer">
                <nav class="saas-mobile-nav">
                    <a href="{{ route('dashboard') }}" class="saas-mobile-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-fill"></i> Dashboard
                    </a>

                    @if(auth()->user()->hasRole('super_admin'))
                    <a href="{{ route('desa.index') }}" class="saas-mobile-link {{ request()->routeIs('desa.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i> Profil Desa
                    </a>
                    <a href="{{ route('rw.index') }}" class="saas-mobile-link {{ request()->routeIs('rw.*') ? 'active' : '' }}">
                        <i class="bi bi-diagram-3"></i> Manajemen RW
                    </a>
                    <a href="{{ route('rt.index') }}" class="saas-mobile-link {{ request()->routeIs('rt.*') ? 'active' : '' }}">
                        <i class="bi bi-signpost-split"></i> Manajemen RT
                    </a>
                    <a href="{{ route('warga.index') }}" class="saas-mobile-link {{ request()->routeIs('warga.*') ? 'active' : '' }}">
                        <i class="bi bi-person-lines-fill"></i> Data Warga
                    </a>
                    <a href="{{ route('users.index') }}" class="saas-mobile-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Manajemen User
                    </a>
                    <a href="{{ route('laporan.index') }}" class="saas-mobile-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph-fill"></i> Laporan & Rekap
                    </a>
                    <a href="{{ route('konfigurasi.index') }}" class="saas-mobile-link {{ request()->routeIs('konfigurasi.*') ? 'active' : '' }}">
                        <i class="bi bi-gear-wide-connected"></i> Konfigurasi AI
                    </a>
                    @elseif(auth()->user()->hasRole('admin_desa'))
                    <a href="{{ route('rw.index') }}" class="saas-mobile-link {{ request()->routeIs('rw.*') ? 'active' : '' }}">
                        <i class="bi bi-diagram-3"></i> Manajemen RW
                    </a>
                    <a href="{{ route('rt.index') }}" class="saas-mobile-link {{ request()->routeIs('rt.*') ? 'active' : '' }}">
                        <i class="bi bi-signpost-split"></i> Manajemen RT
                    </a>
                    <a href="{{ route('warga.index') }}" class="saas-mobile-link {{ request()->routeIs('warga.*') ? 'active' : '' }}">
                        <i class="bi bi-person-lines-fill"></i> Data Warga
                    </a>
                    <a href="{{ route('users.index') }}" class="saas-mobile-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Akun RT/RW & Warga
                    </a>
                    <a href="{{ route('laporan.index') }}" class="saas-mobile-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph-fill"></i> Laporan & Rekap
                    </a>
                    @elseif(auth()->user()->hasRole('rw'))
                    <a href="{{ route('rt.index') }}" class="saas-mobile-link {{ request()->routeIs('rt.*') ? 'active' : '' }}">
                        <i class="bi bi-signpost-split"></i> Manajemen RT
                    </a>
                    <a href="{{ route('warga.index') }}" class="saas-mobile-link {{ request()->routeIs('warga.*') ? 'active' : '' }}">
                        <i class="bi bi-person-lines-fill"></i> Data Warga
                    </a>
                    @elseif(auth()->user()->hasRole('rt'))
                    <a href="{{ route('warga.index') }}" class="saas-mobile-link {{ request()->routeIs('warga.*') ? 'active' : '' }}">
                        <i class="bi bi-person-lines-fill"></i> Data Warga
                    </a>
                    @endif

                    <a href="{{ route('surat.index') }}" class="saas-mobile-link {{ request()->routeIs('surat.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text-fill"></i> {{ auth()->user()->hasRole('warga') ? 'Layanan Surat' : (auth()->user()->hasRole('admin_desa') ? 'Verifikasi & Penerbitan Surat' : 'Pengajuan Surat') }}
                    </a>

                    <a href="{{ route('informasi.index') }}" class="saas-mobile-link {{ request()->routeIs('informasi.*') ? 'active' : '' }}">
                        <i class="bi bi-megaphone-fill"></i> {{ auth()->user()->hasRole('admin_desa') ? 'Pengumuman Desa' : 'Informasi Desa' }}
                    </a>

                    <div class="border-top pt-2 mt-2">
                        @if(auth()->user()->hasRole('warga') && auth()->user()->warga)
                        <a href="{{ route('warga.show', auth()->user()->warga->id) }}" class="saas-mobile-link">
                            <i class="bi bi-person-badge"></i> Profil Warga
                        </a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="saas-mobile-link">
                            <i class="bi bi-gear"></i> Pengaturan Akun
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="mt-1">
                            @csrf
                            <button type="submit" class="saas-mobile-link text-danger w-100 bg-transparent border-0 text-start">
                                <i class="bi bi-box-arrow-right text-danger"></i> Logout
                            </button>
                        </form>
                    </div>
                </nav>
            </div>
        </header>

        <!-- ========== MAIN VIEW CONTAINER ========== -->
        <main class="saas-main-container">
            @if(isset($header))
                <div class="saas-page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div>
                        <h1 class="saas-page-title mb-0">{{ $header }}</h1>
                    </div>
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-white border shadow-sm text-secondary" style="font-size: 0.82rem; font-weight: 500;">
                        <i class="bi bi-calendar-event text-primary"></i>
                        <span id="realtimeDateIndo">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                        <span class="text-muted opacity-50">|</span>
                        <i class="bi bi-clock text-primary"></i>
                        <span id="realtimeClockIndo" class="font-monospace fw-semibold">{{ \Carbon\Carbon::now()->format('H:i') }} WIB</span>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>

        <!-- ========== FLOATING AI ASSISTANT WIDGET COMPONENT ========== -->
        <x-ai-assistant-widget />

        <!-- ========== CHATBOT DELETE CONVERSATION MODAL ========== -->
        <div class="modal fade" id="cbDeleteModal" tabindex="-1" aria-labelledby="cbDeleteModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
                <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 18px;">
                    <div class="modal-header border-0 pb-0 pt-4 px-4 justify-content-center position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 58px; height: 58px; background: #FEF2F2; color: #DC2626;">
                            <i class="bi bi-trash3-fill fs-3"></i>
                        </div>
                    </div>
                    <div class="modal-body text-center px-4 py-3">
                        <h6 class="fw-bold text-dark mb-2" id="cbDeleteModalTitle" style="font-size: 1.05rem;">Hapus Percakapan Ini?</h6>
                        <p class="text-muted small mb-0">
                            Percakapan beserta semua pesan di dalamnya akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-2 pb-4 px-4 d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-3 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 10px; min-width: 100px;">
                            Batal
                        </button>
                        <button type="button" class="btn btn-danger px-3 py-2 fw-bold shadow-sm" id="cbConfirmDelete" style="border-radius: 10px; min-width: 110px; background: #DC2626; border-color: #DC2626;">
                            <i class="bi bi-trash3 me-1"></i> Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== CHATBOT DELETE ALL CONVERSATIONS MODAL ========== -->
        <div class="modal fade" id="cbDeleteAllModal" tabindex="-1" aria-labelledby="cbDeleteAllModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
                <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 18px;">
                    <div class="modal-header border-0 pb-0 pt-4 px-4 justify-content-center position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 58px; height: 58px; background: #FEF2F2; color: #DC2626;">
                            <i class="bi bi-trash-fill fs-3"></i>
                        </div>
                    </div>
                    <div class="modal-body text-center px-4 py-3">
                        <h6 class="fw-bold text-dark mb-2" id="cbDeleteAllModalTitle" style="font-size: 1.05rem;">Hapus Seluruh Riwayat Chat?</h6>
                        <p class="text-muted small mb-0">
                            Semua topik percakapan dan pesan chatbot Anda akan dibersihkan secara permanen dari sistem.
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-2 pb-4 px-4 d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-3 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 10px; min-width: 100px;">
                            Batal
                        </button>
                        <button type="button" class="btn btn-danger px-3 py-2 fw-bold shadow-sm" id="cbConfirmDeleteAll" style="border-radius: 10px; min-width: 120px; background: #DC2626; border-color: #DC2626;">
                            <i class="bi bi-trash me-1"></i> Hapus Semua
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== GLOBAL DELETE CONFIRMATION MODAL ========== -->
        <div class="modal fade" id="globalConfirmDeleteModal" tabindex="-1" aria-labelledby="globalConfirmDeleteTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
                <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 20px;">
                    <div class="modal-header border-0 pb-0 pt-4 px-4 justify-content-center position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 64px; height: 64px; background: #FEF2F2; color: #DC2626;">
                            <i class="bi bi-trash3-fill fs-2"></i>
                        </div>
                    </div>
                    <div class="modal-body text-center px-4 py-3">
                        <h5 class="fw-bold text-dark mb-2" id="globalConfirmDeleteTitle" style="font-size: 1.15rem;">Konfirmasi Hapus Data</h5>
                        <p class="text-muted small mb-3" id="globalConfirmDeleteMessage">
                            Tindakan ini bersifat permanen dan tidak dapat dibatalkan.
                        </p>
                        <div class="p-3 rounded-3 bg-light border text-start mb-2" id="globalConfirmDeleteDetailBox" style="display: none;">
                            <div class="text-muted small mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em;">Rincian Data:</div>
                            <div class="fw-bold text-dark small" id="globalConfirmDeleteDetail"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 12px; min-width: 110px;">
                            Batal
                        </button>
                        <button type="button" class="btn btn-danger px-4 py-2 fw-bold shadow-sm" id="globalConfirmDeleteSubmitBtn" style="border-radius: 12px; min-width: 120px; background: #DC2626; border-color: #DC2626;">
                            <i class="bi bi-trash3 me-1"></i> Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== SCRIPTS ========== -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Mobile Hamburger Drawer Toggle
                const mobileToggle = document.getElementById('saasMobileToggle');
                const mobileDrawer = document.getElementById('saasMobileDrawer');
                const mobileToggleIcon = document.getElementById('saasMobileToggleIcon');

                if (mobileToggle && mobileDrawer) {
                    mobileToggle.addEventListener('click', function() {
                        const isOpen = mobileDrawer.classList.toggle('open');
                        if (mobileToggleIcon) {
                            mobileToggleIcon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
                        }
                    });
                }

                // Chatbot functionality
                const fab = document.getElementById('chatbotFab');
                const fabIcon = document.getElementById('chatbotFabIcon');
                const panel = document.getElementById('chatbotPanel');
                const closeBtn = document.getElementById('chatbotClose');
                const msgInput = document.getElementById('cbMsgInput');
                const sendBtn = document.getElementById('cbSendBtn');
                const messagesEl = document.getElementById('cbMessages');
                const emptyState = document.getElementById('cbEmptyState');
                const convListInner = document.getElementById('cbConvListInner');
                const newChatBtn = document.getElementById('cbNewChat');
                const charCount = document.getElementById('cbCharCount');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const userInitial = @json(substr(auth()->user()->name, 0, 1));
                const storeUrl = @json(route('ai.chat.store'));

                let isSending = false;
                let currentConvId = null;
                let panelOpen = false;
                let lastFailedMessage = null;
                let conversationsLoaded = false;

                if (fab && panel) {
                    fab.addEventListener('click', function() {
                        panelOpen = !panelOpen;
                        panel.classList.toggle('open', panelOpen);
                        fabIcon.className = panelOpen ? 'bi bi-x-lg' : 'bi bi-chat-dots-fill';
                        if (panelOpen) {
                            if (!conversationsLoaded) {
                                loadConversations();
                                conversationsLoaded = true;
                            }
                            if (msgInput) msgInput.focus();
                        }
                    });
                }

                if (closeBtn && panel) {
                    closeBtn.addEventListener('click', function() {
                        panelOpen = false;
                        panel.classList.remove('open');
                        fabIcon.className = 'bi bi-chat-dots-fill';
                    });
                }

                const clearAllBtn = document.getElementById('cbClearAllBtn');
                function updateClearAllVisibility() {
                    if (!clearAllBtn) return;
                    const count = convListInner ? convListInner.querySelectorAll('.chatbot-conv-item').length : 0;
                    clearAllBtn.style.display = count > 0 ? 'inline-flex' : 'none';
                }

                function loadConversations() {
                    fetch('{{ route("ai.chat") }}', {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (convListInner) convListInner.innerHTML = '';
                        if (data && Array.isArray(data.conversations)) {
                            data.conversations.forEach(item => {
                                addConvToList(item.id, item.title, false);
                            });
                        }
                        updateClearAllVisibility();
                    })
                    .catch(() => {
                        // Fallback parsing HTML jika backend merespons HTML
                        fetch('{{ route("ai.chat") }}', {
                            headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(r => r.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const items = doc.querySelectorAll('.conv-item');
                            if (convListInner) convListInner.innerHTML = '';
                            items.forEach(item => {
                                const id = item.dataset.id;
                                const title = item.querySelector('.conv-title')?.textContent || 'Percakapan';
                                addConvToList(id, title, false);
                            });
                            updateClearAllVisibility();
                        })
                        .catch(() => {});
                    });
                }

                function addConvToList(id, title, prepend = true) {
                    if (!convListInner) return;
                    const el = document.createElement('div');
                    el.className = 'chatbot-conv-item' + (currentConvId == id ? ' active' : '');
                    el.dataset.id = id;
                    el.innerHTML =
                        '<i class="bi bi-chat-left-text"></i>' +
                        '<span class="chatbot-conv-title">' + esc(title) + '</span>' +
                        '<button class="chatbot-conv-delete" title="Hapus"><i class="bi bi-trash3"></i></button>';

                    el.addEventListener('click', function(e) {
                        if (e.target.closest('.chatbot-conv-delete')) return;
                        selectConversation(id);
                    });

                    el.querySelector('.chatbot-conv-delete').addEventListener('click', function(e) {
                        e.stopPropagation();
                        pendingDeleteId = id;
                        if (cbDeleteModal) {
                            cbDeleteModal.show();
                        } else if (confirm('Hapus percakapan ini?')) {
                            executeDeleteConversation(id);
                        }
                    });

                    if (prepend && convListInner.firstChild) {
                        convListInner.insertBefore(el, convListInner.firstChild);
                    } else {
                        convListInner.appendChild(el);
                    }
                    updateClearAllVisibility();
                }

                function selectConversation(id) {
                    currentConvId = id;
                    document.querySelectorAll('.chatbot-conv-item').forEach(el => {
                        el.classList.toggle('active', el.dataset.id == id);
                    });
                    loadMessages(id);
                }

                function loadMessages(id) {
                    fetch('/ai-chat/' + id, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        messagesEl.innerHTML = '';
                        if (data.messages && data.messages.length > 0) {
                            showMessages();
                            data.messages.forEach(msg => {
                                appendMsg(msg.role, msg.content, msg.content_html);
                            });
                        } else {
                            showEmpty();
                        }
                    })
                    .catch(() => {});
                }

                if (newChatBtn) {
                    newChatBtn.addEventListener('click', function() {
                        currentConvId = null;
                        document.querySelectorAll('.chatbot-conv-item').forEach(el => el.classList.remove('active'));
                        messagesEl.innerHTML = '';
                        showEmpty();
                        if (msgInput) msgInput.focus();
                    });
                }

                document.querySelectorAll('.chatbot-chip').forEach(chip => {
                    chip.addEventListener('click', function() {
                        if (msgInput) {
                            msgInput.value = this.dataset.msg;
                            sendMessage();
                        }
                    });
                });

                if (msgInput) {
                    msgInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            sendMessage();
                        }
                    });
                    msgInput.addEventListener('input', function() {
                        this.style.height = 'auto';
                        this.style.height = Math.min(this.scrollHeight, 80) + 'px';
                    });
                }

                if (sendBtn) {
                    sendBtn.addEventListener('click', sendMessage);
                }

                function showEmpty() {
                    if (emptyState) emptyState.style.display = 'flex';
                    if (messagesEl) messagesEl.style.display = 'none';
                }

                function showMessages() {
                    if (emptyState) emptyState.style.display = 'none';
                    if (messagesEl) messagesEl.style.display = 'flex';
                }

                function sendMessage() {
                    const message = msgInput.value.trim();
                    if (!message || isSending) return;

                    isSending = true;
                    sendBtn.disabled = true;
                    showMessages();
                    appendMsg('user', message);
                    msgInput.value = '';
                    msgInput.style.height = 'auto';

                    showThinking();

                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 60000);

                    fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ message: message, conversation_id: currentConvId }),
                        signal: controller.signal
                    })
                    .then(async res => {
                        clearTimeout(timeoutId);
                        hideThinking();
                        const data = await res.json().catch(() => null);

                        if (!res.ok) {
                            if (res.status === 404 && currentConvId) {
                                currentConvId = null;
                            }
                            lastFailedMessage = message;
                            showError((data && data.error) ? data.error : 'Terjadi kesalahan sistem.', true);
                            return;
                        }

                        if (data && data.reply) {
                            appendMsg('assistant', data.reply, data.reply_html);
                            if (!currentConvId && data.conversation_id) {
                                currentConvId = data.conversation_id;
                                addConvToList(data.conversation_id, message.substring(0, 30) + '...', true);
                            }
                        } else {
                            lastFailedMessage = message;
                            showError('AI tidak memberikan respons. Silakan coba lagi.', true);
                        }
                    })
                    .catch(err => {
                        clearTimeout(timeoutId);
                        hideThinking();
                        lastFailedMessage = message;
                        if (err.name === 'AbortError') {
                            showError('Respons AI membutuhkan waktu terlalu lama.', true);
                        } else {
                            showError('Gagal menghubungi AI. ' + (err.message || ''), true);
                        }
                    })
                    .finally(() => {
                        hideThinking();
                        isSending = false;
                        sendBtn.disabled = false;
                        msgInput.focus();
                    });
                }

                function renderChatMarkdown(raw) {
                    if (!raw) return '';
                    let text = esc(raw);
                    text = text.replace(/```([a-zA-Z0-9_-]*)\n([\s\S]*?)```/g, function(match, lang, code) {
                        return '<pre><code class="language-' + lang + '">' + code + '</code></pre>';
                    });
                    text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
                    text = text.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
                    text = text.replace(/__([^_]+)__/g, '<strong>$1</strong>');
                    text = text.replace(/\*([^*\n]+)\*/g, '<em>$1</em>');
                    text = text.replace(/_([^_\n]+)_/g, '<em>$1</em>');
                    text = text.replace(/^### (.*$)/gim, '<h6>$1</h6>');
                    text = text.replace(/^## (.*$)/gim, '<h5>$1</h5>');
                    text = text.replace(/^# (.*$)/gim, '<h5>$1</h5>');

                    const lines = text.split('\n');
                    let out = [];
                    let inList = false;
                    let listType = 'ul';

                    for (let i = 0; i < lines.length; i++) {
                        let line = lines[i];
                        let ulMatch = line.match(/^\s*[-*]\s+(.*)$/);
                        let olMatch = line.match(/^\s*\d+\.\s+(.*)$/);

                        if (ulMatch) {
                            if (!inList || listType !== 'ul') {
                                if (inList) out.push('</' + listType + '>');
                                out.push('<ul>');
                                inList = true;
                                listType = 'ul';
                            }
                            out.push('<li>' + ulMatch[1] + '</li>');
                        } else if (olMatch) {
                            if (!inList || listType !== 'ol') {
                                if (inList) out.push('</' + listType + '>');
                                out.push('<ol>');
                                inList = true;
                                listType = 'ol';
                            }
                            out.push('<li>' + olMatch[1] + '</li>');
                        } else {
                            if (inList) {
                                out.push('</' + listType + '>');
                                inList = false;
                            }
                            if (line.trim().length > 0) {
                                out.push('<p>' + line + '</p>');
                            }
                        }
                    }
                    if (inList) {
                        out.push('</' + listType + '>');
                    }

                    return out.length > 0 ? out.join('') : text.replace(/\n/g, '<br>');
                }

                function appendMsg(role, content, html = null) {
                    const row = document.createElement('div');
                    row.className = 'cb-msg-row ' + (role === 'user' ? 'cb-msg-user' : 'cb-msg-ai');
                    if (role === 'user') {
                        row.innerHTML =
                            '<div class="cb-msg-bubble cb-msg-bubble-user">' + esc(content) + '</div>' +
                            '<div class="cb-msg-avatar cb-avatar-user">' + userInitial + '</div>';
                    } else {
                        row.innerHTML =
                            '<div class="cb-msg-avatar cb-avatar-ai"><i class="bi bi-robot"></i></div>' +
                            '<div class="cb-msg-bubble cb-msg-bubble-ai">' + (html ? html : renderChatMarkdown(content)) + '</div>';
                    }
                    messagesEl.appendChild(row);
                    scrollEnd();
                }

                function showError(msg, withRetry = false) {
                    const el = document.createElement('div');
                    el.className = 'cb-error-msg';
                    el.innerHTML = '<i class="bi bi-exclamation-circle"></i><span>' + esc(msg) + '</span>';
                    if (withRetry) {
                        const retryBtn = document.createElement('button');
                        retryBtn.className = 'cb-retry-btn';
                        retryBtn.textContent = 'Coba Lagi';
                        retryBtn.addEventListener('click', function() {
                            el.remove();
                            if (lastFailedMessage) {
                                msgInput.value = lastFailedMessage;
                                sendMessage();
                            }
                        });
                        el.appendChild(retryBtn);
                    }
                    messagesEl.appendChild(el);
                    scrollEnd();
                }

                function showThinking() {
                    hideThinking();
                    const el = document.createElement('div');
                    el.className = 'cb-thinking';
                    el.id = 'cbThinkingEl';
                    el.innerHTML =
                        '<div class="cb-msg-avatar cb-avatar-ai"><i class="bi bi-robot"></i></div>' +
                        '<div class="cb-thinking-bubble">' +
                            '<span class="cb-thinking-label">AI sedang berpikir</span>' +
                            '<div class="cb-thinking-dots"><span></span><span></span><span></span></div>' +
                        '</div>';
                    messagesEl.appendChild(el);
                    scrollEnd();
                }

                function hideThinking() {
                    const el = document.getElementById('cbThinkingEl');
                    if (el) el.remove();
                }

                function scrollEnd() { messagesEl.scrollTop = messagesEl.scrollHeight; }
                function esc(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

                let pendingDeleteId = null;
                const deleteModalEl = document.getElementById('cbDeleteModal');
                const cbDeleteModal = deleteModalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(deleteModalEl) : null;

                const deleteAllModalEl = document.getElementById('cbDeleteAllModal');
                const cbDeleteAllModal = deleteAllModalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(deleteAllModalEl) : null;

                async function executeDeleteConversation(id) {
                    try {
                        const res = await fetch('/ai-chat/' + id, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                        });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok && data.success !== false) {
                            const el = convListInner ? convListInner.querySelector('[data-id="' + id + '"]') : null;
                            if (el) el.remove();
                            if (currentConvId == id) {
                                currentConvId = null;
                                messagesEl.innerHTML = '';
                                showEmpty();
                            }
                            updateClearAllVisibility();
                        } else {
                            alert(data.message || 'Gagal menghapus percakapan. Silakan coba lagi.');
                        }
                    } catch (err) {
                        console.error('Delete error:', err);
                        alert('Terjadi kesalahan koneksi saat menghapus percakapan.');
                    } finally {
                        if (cbDeleteModal) cbDeleteModal.hide();
                        pendingDeleteId = null;
                    }
                }

                const confirmDeleteBtn = document.getElementById('cbConfirmDelete');
                if (confirmDeleteBtn) {
                    confirmDeleteBtn.addEventListener('click', function() {
                        if (!pendingDeleteId) return;
                        executeDeleteConversation(pendingDeleteId);
                    });
                }

                if (clearAllBtn) {
                    clearAllBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        if (cbDeleteAllModal) {
                            cbDeleteAllModal.show();
                        } else if (confirm('Apakah Anda yakin ingin menghapus seluruh riwayat percakapan chatbot?')) {
                            executeDeleteAllConversations();
                        }
                    });
                }

                async function executeDeleteAllConversations() {
                    try {
                        const res = await fetch('{{ route("ai.chat.destroy-all") }}', {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                        });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok && data.success !== false) {
                            if (convListInner) convListInner.innerHTML = '';
                            currentConvId = null;
                            messagesEl.innerHTML = '';
                            showEmpty();
                            updateClearAllVisibility();
                        } else {
                            alert(data.message || 'Gagal menghapus seluruh riwayat percakapan.');
                        }
                    } catch (err) {
                        console.error('Delete all error:', err);
                        alert('Terjadi kesalahan saat membersihkan riwayat percakapan.');
                    } finally {
                        if (cbDeleteAllModal) cbDeleteAllModal.hide();
                    }
                }

                const confirmDeleteAllBtn = document.getElementById('cbConfirmDeleteAll');
                if (confirmDeleteAllBtn) {
                    confirmDeleteAllBtn.addEventListener('click', function() {
                        executeDeleteAllConversations();
                    });
                }
            });

            // Global handler untuk toggle tampil/sembunyi password dengan ikon mata
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

            // Update real-time jam WIB setiap detik
            function updateRealtimeClock() {
                const clockEl = document.getElementById('realtimeClockIndo');
                if (!clockEl) return;
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                clockEl.textContent = `${hours}:${minutes}:${seconds} WIB`;
            }
            setInterval(updateRealtimeClock, 1000);
            updateRealtimeClock();

            // ==========================================
            // GLOBAL PREMIUM DELETE CONFIRMATION MODAL
            // ==========================================
            let activeDeleteForm = null;
            const globalDeleteModalEl = document.getElementById('globalConfirmDeleteModal');
            const globalDeleteModal = globalDeleteModalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(globalDeleteModalEl) : null;
            const globalDeleteTitle = document.getElementById('globalConfirmDeleteTitle');
            const globalDeleteMsg = document.getElementById('globalConfirmDeleteMessage');
            const globalDeleteDetailBox = document.getElementById('globalConfirmDeleteDetailBox');
            const globalDeleteDetail = document.getElementById('globalConfirmDeleteDetail');
            const globalDeleteSubmitBtn = document.getElementById('globalConfirmDeleteSubmitBtn');

            document.addEventListener('click', function(e) {
                const triggerBtn = e.target.closest('[data-confirm-delete]');
                if (!triggerBtn) return;

                e.preventDefault();
                e.stopPropagation();

                activeDeleteForm = triggerBtn.closest('form');
                if (!activeDeleteForm) return;

                const title = triggerBtn.getAttribute('data-delete-title') || 'Konfirmasi Hapus Data';
                const message = triggerBtn.getAttribute('data-delete-message') || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini bersifat permanen.';
                const detail = triggerBtn.getAttribute('data-delete-detail') || '';

                if (globalDeleteTitle) globalDeleteTitle.textContent = title;
                if (globalDeleteMsg) globalDeleteMsg.textContent = message;

                if (detail && globalDeleteDetailBox && globalDeleteDetail) {
                    globalDeleteDetail.innerHTML = detail;
                    globalDeleteDetailBox.style.display = 'block';
                } else if (globalDeleteDetailBox) {
                    globalDeleteDetailBox.style.display = 'none';
                }

                if (globalDeleteModal) {
                    globalDeleteModal.show();
                } else {
                    activeDeleteForm.submit();
                }
            });

            if (globalDeleteSubmitBtn) {
                globalDeleteSubmitBtn.addEventListener('click', function() {
                    if (activeDeleteForm) {
                        const form = activeDeleteForm;
                        activeDeleteForm = null;
                        if (globalDeleteModal) globalDeleteModal.hide();
                        form.submit();
                    }
                });
            }
        </script>
    </body>
</html>
