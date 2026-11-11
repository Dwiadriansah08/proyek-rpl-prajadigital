<x-app-layout>
    <x-slot name="header">
        AI Assistant Desa
    </x-slot>

    <div class="ai-chat-container">
        {{-- Sidebar --}}
        <div class="ai-sidebar" id="aiSidebar">
            <div class="ai-sidebar-header">
                <div class="d-flex gap-2">
                    <a href="{{ route('ai.chat') }}" class="btn-new-chat flex-grow-1">
                        <i class="bi bi-plus-lg"></i> Percakapan Baru
                    </a>
                    <button class="btn btn-outline-danger btn-clear-all" id="btnClearAllHistory" title="Hapus Seluruh Riwayat" style="{{ $conversations->isEmpty() ? 'display:none;' : '' }}; border-radius:10px; padding:0 10px;">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>
            <div class="ai-sidebar-list" id="conversationList">
                @forelse($conversations as $conv)
                    <div class="conv-item {{ $activeConversation && $activeConversation->id == $conv->id ? 'active' : '' }}" data-id="{{ $conv->id }}">
                        <a href="{{ route('ai.chat.show', $conv->id) }}" class="conv-link">
                            <i class="bi bi-chat-dots"></i>
                            <div class="conv-info">
                                <span class="conv-title">{{ $conv->title }}</span>
                                <span class="conv-time">{{ $conv->created_at->diffForHumans() }}</span>
                            </div>
                        </a>
                        <div class="conv-menu-wrapper">
                            <button class="btn-conv-menu" title="Opsi">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div class="conv-dropdown">
                                <button class="conv-dropdown-item conv-dropdown-delete" data-id="{{ $conv->id }}">
                                    <i class="bi bi-trash3"></i> Hapus percakapan
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="conv-empty" id="convEmpty">
                        <i class="bi bi-chat-square-text"></i>
                        <span>Belum ada percakapan</span>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Overlay mobile --}}
        <div class="ai-sidebar-overlay" id="aiSidebarOverlay"></div>

        {{-- Main Chat --}}
        <div class="ai-chat-main">
            {{-- Header --}}
            <div class="ai-chat-header">
                <button class="btn-sidebar-toggle d-md-none" id="btnToggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <div class="ai-chat-header-title">
                    <i class="bi bi-robot"></i>
                    <span>AI Assistant Desa</span>
                </div>
            </div>

            @if($activeConversation)
                {{-- Messages --}}
                <div class="ai-messages" id="chatMessages">
                    @foreach($activeConversation->messages as $msg)
                        @if($msg->role === 'user')
                            <div class="msg-row msg-user">
                                <div class="msg-bubble msg-bubble-user">{{ $msg->content }}</div>
                                <div class="msg-avatar avatar-user">{{ substr(auth()->user()->name, 0, 1) }}</div>
                            </div>
                        @else
                            <div class="msg-row msg-ai">
                                <div class="msg-avatar avatar-ai"><i class="bi bi-robot"></i></div>
                                <div class="msg-bubble msg-bubble-ai markdown-body">{!! \Illuminate\Support\Str::markdown($msg->content, ['html_input' => 'escape']) !!}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <div class="ai-empty-state" id="emptyState">
                    <div class="empty-icon-ring">
                        <i class="bi bi-robot"></i>
                    </div>
                    <h5>Halo, {{ auth()->user()->name }}!</h5>
                    <p>Saya AI Assistant Sistem Informasi Desa. Siap membantu informasi layanan, persuratan, dan administrasi desa Anda.</p>
                    <div class="empty-chips">
                        <button class="chip" onclick="fillMessage('Apa saja layanan surat yang tersedia?')">
                            <i class="bi bi-envelope"></i> Layanan Surat
                        </button>
                        <button class="chip" onclick="fillMessage('Bagaimana cara mengajukan surat?')">
                            <i class="bi bi-file-earmark-text"></i> Cara Pengajuan
                        </button>
                        <button class="chip" onclick="fillMessage('Apa syarat membuat Surat Pengantar RT?')">
                            <i class="bi bi-card-checklist"></i> Syarat Surat
                        </button>
                        <button class="chip" onclick="fillMessage('Bagaimana cara cek status pengajuan surat saya?')">
                            <i class="bi bi-search"></i> Cek Status
                        </button>
                    </div>
                </div>
                <div class="ai-messages" id="chatMessages" style="display:none;"></div>
            @endif

            {{-- Input --}}
            <div class="ai-input-bar">
                <form id="chatForm" autocomplete="off">
                    @csrf
                    <input type="hidden" name="conversation_id" id="conversationId" value="{{ $activeConversation?->id ?? '' }}">
                    <div class="input-box">
                        <textarea name="message" id="msgInput" rows="1" placeholder="Ketik pesan..." required maxlength="2000"></textarea>
                        <button type="submit" class="btn-send" id="btnSend" title="Kirim">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                <div class="modal-body text-center px-4 py-4">
                    <div class="mb-3"><i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:2rem;"></i></div>
                    <h6 class="fw-bold mb-1">Hapus Percakapan?</h6>
                    <p class="text-muted small mb-3">Seluruh pesan akan dihapus permanen dan tidak bisa dikembalikan.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button class="btn btn-sm btn-light px-3" data-bs-dismiss="modal" style="border-radius:8px;">Batal</button>
                        <button class="btn btn-sm btn-danger px-3" id="btnConfirmDelete" style="border-radius:8px;">
                            <i class="bi bi-trash3 me-1"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete All Modal --}}
    <div class="modal fade" id="deleteAllModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                <div class="modal-body text-center px-4 py-4">
                    <div class="mb-3"><i class="bi bi-trash-fill text-danger" style="font-size:2rem;"></i></div>
                    <h6 class="fw-bold mb-1">Hapus Seluruh Riwayat?</h6>
                    <p class="text-muted small mb-3">Semua topik obrolan dan pesan chatbot Anda akan dibersihkan permanen.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button class="btn btn-sm btn-light px-3" data-bs-dismiss="modal" style="border-radius:8px;">Batal</button>
                        <button class="btn btn-sm btn-danger px-3" id="btnConfirmDeleteAll" style="border-radius:8px;">
                            <i class="bi bi-trash me-1"></i> Hapus Semua
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* ========== Container ========== */
    .ai-chat-container {
        display: flex;
        height: calc(100vh - 146px);
        background: #fff;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 16px rgba(0,0,0,.05);
        position: relative;
    }

    /* ========== Sidebar ========== */
    .ai-sidebar {
        width: 260px;
        min-width: 260px;
        background: #f7f8fa;
        border-right: 1px solid #ebedf0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .ai-sidebar-header { padding: 12px; border-bottom: 1px solid #ebedf0; }
    .btn-new-chat {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        width: 100%; padding: 9px 0; background: #4f46e5; color: #fff;
        border: none; border-radius: 10px; font-size: .82rem; font-weight: 600;
        text-decoration: none; transition: background .15s;
    }
    .btn-new-chat:hover { background: #4338ca; color: #fff; }
    .ai-sidebar-list { flex: 1; overflow-y: auto; padding: 6px; }

    /* Conv item */
    .conv-item {
        display: flex; align-items: center; border-radius: 8px;
        margin-bottom: 1px; transition: background .12s; position: relative;
    }
    .conv-item:hover { background: #eef0f5; }
    .conv-item.active { background: #e8e5ff; }
    .conv-link {
        flex: 1; display: flex; align-items: center; gap: 8px;
        padding: 8px 10px; text-decoration: none; color: #333; min-width: 0;
    }
    .conv-link > i { font-size: .85rem; color: #a0a5b0; flex-shrink: 0; }
    .conv-info { min-width: 0; display: flex; flex-direction: column; }
    .conv-title {
        font-size: .8rem; font-weight: 500; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis; line-height: 1.3;
    }
    .conv-time { font-size: .68rem; color: #a0a5b0; line-height: 1.3; }

    /* ⋮ Menu */
    .conv-menu-wrapper { position: relative; flex-shrink: 0; }
    .btn-conv-menu {
        opacity: 0; border: none; background: none; color: #999;
        padding: 4px 6px; border-radius: 6px; cursor: pointer;
        font-size: .9rem; transition: opacity .12s;
    }
    .conv-item:hover .btn-conv-menu { opacity: 1; }
    .btn-conv-menu:hover { color: #555; background: rgba(0,0,0,.06); }

    /* Dropdown */
    .conv-dropdown {
        display: none; position: absolute; right: 0; top: 100%;
        background: #fff; border-radius: 10px; box-shadow: 0 6px 24px rgba(0,0,0,.12);
        min-width: 170px; z-index: 20; padding: 4px; overflow: hidden;
    }
    .conv-dropdown.show { display: block; }
    .conv-dropdown-item {
        display: flex; align-items: center; gap: 8px; width: 100%;
        padding: 8px 12px; border: none; background: none; font-size: .8rem;
        color: #374151; cursor: pointer; border-radius: 8px; transition: background .1s;
    }
    .conv-dropdown-item:hover { background: #f3f4f6; }
    .conv-dropdown-delete:hover { background: #fef2f2; color: #dc2626; }

    .conv-empty {
        display: flex; flex-direction: column; align-items: center;
        gap: 6px; padding: 32px 12px; color: #a0a5b0; font-size: .8rem;
    }
    .conv-empty i { font-size: 1.6rem; opacity: .35; }

    /* ========== Main ========== */
    .ai-chat-main {
        flex: 1; display: flex; flex-direction: column; min-width: 0;
    }

    /* Header */
    .ai-chat-header {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 16px; border-bottom: 1px solid #ebedf0;
        background: #fafbfc;
    }
    .btn-sidebar-toggle {
        border: none; background: none; font-size: 1.2rem;
        color: #555; padding: 4px 8px; border-radius: 6px;
    }
    .btn-sidebar-toggle:hover { background: #eef0f4; }
    .ai-chat-header-title {
        display: flex; align-items: center; gap: 6px;
        font-size: .88rem; font-weight: 600; color: #333;
    }
    .ai-chat-header-title i { color: #4f46e5; font-size: 1rem; }

    /* ========== Messages ========== */
    .ai-messages {
        flex: 1; overflow-y: auto; padding: 16px;
        display: flex; flex-direction: column; gap: 12px;
        background: #fafbfc;
    }
    /* Markdown Body */
    .markdown-body {
        font-family: inherit;
        line-height: 1.5;
        font-size: .88rem;
    }
    .markdown-body p { margin-bottom: 8px; }
    .markdown-body p:last-child { margin-bottom: 0; }
    .markdown-body strong { font-weight: 600; }
    .markdown-body em { font-style: italic; }
    .markdown-body ul, .markdown-body ol { 
        padding-left: 20px; 
        margin-bottom: 8px; 
    }
    .markdown-body ul { list-style-type: disc; }
    .markdown-body ol { list-style-type: decimal; }
    .markdown-body li { margin-bottom: 2px; }
    .markdown-body code {
        background-color: rgba(0,0,0,0.06);
        padding: 2px 4px;
        border-radius: 4px;
        font-family: monospace;
        font-size: .8em;
    }
    .markdown-body pre {
        background-color: #f6f8fa;
        padding: 10px;
        border-radius: 6px;
        overflow-x: auto;
        margin-bottom: 8px;
    }
    .markdown-body pre code {
        background-color: transparent;
        padding: 0;
    }

    .msg-row {
        display: flex; align-items: flex-start; gap: 8px;
        max-width: 78%; animation: msgIn .25s ease;
    }
    @keyframes msgIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .msg-user { align-self: flex-end; flex-direction: row-reverse; }
    .msg-ai { align-self: flex-start; }

    .msg-avatar {
        width: 30px; height: 30px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .75rem; flex-shrink: 0;
    }
    .avatar-user { background: #4f46e5; color: #fff; }
    .avatar-ai { background: #e8e5ff; color: #4f46e5; font-size: .9rem; }

    .msg-bubble {
        padding: 10px 14px; border-radius: 14px;
        line-height: 1.55; font-size: .85rem; word-break: break-word;
    }
    .msg-bubble-user {
        background: #4f46e5; color: #fff;
        border-bottom-right-radius: 4px;
    }
    .msg-bubble-ai {
        background: #f0f1f3; color: #1f2937;
        border-bottom-left-radius: 4px;
    }

    /* ========== Thinking ========== */
    .thinking-row {
        display: flex; align-items: flex-start; gap: 8px;
        max-width: 78%; align-self: flex-start; animation: msgIn .25s ease;
    }
    .thinking-bubble {
        background: #f0f1f3; border-radius: 14px; border-bottom-left-radius: 4px;
        padding: 10px 16px; display: flex; align-items: center; gap: 8px;
    }
    .thinking-label { font-size: .8rem; color: #6b7280; font-weight: 500; }
    .thinking-dots { display: flex; gap: 3px; }
    .thinking-dots span {
        width: 6px; height: 6px; border-radius: 50%; background: #4f46e5;
        animation: dotPulse 1.4s infinite ease-in-out;
    }
    .thinking-dots span:nth-child(2) { animation-delay: .2s; }
    .thinking-dots span:nth-child(3) { animation-delay: .4s; }
    @keyframes dotPulse {
        0%,80%,100% { transform: scale(.5); opacity: .3; }
        40% { transform: scale(1); opacity: 1; }
    }

    /* ========== Empty State ========== */
    .ai-empty-state {
        flex: 1; display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        text-align: center; padding: 32px 20px; background: #fafbfc;
    }
    .empty-icon-ring {
        width: 64px; height: 64px; border-radius: 50%;
        background: linear-gradient(135deg,#e8e5ff,#c7d2fe);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 16px;
    }
    .empty-icon-ring i { font-size: 1.8rem; color: #4f46e5; }
    .ai-empty-state h5 { font-weight: 700; color: #1f2937; margin-bottom: 4px; font-size: 1.05rem; }
    .ai-empty-state p { color: #6b7280; font-size: .84rem; max-width: 380px; margin-bottom: 20px; line-height: 1.5; }
    .empty-chips { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
    .chip {
        border: 1px solid #e5e7eb; background: #fff; color: #374151;
        padding: 7px 14px; border-radius: 20px; font-size: .78rem;
        font-weight: 500; cursor: pointer; transition: all .15s;
        display: flex; align-items: center; gap: 5px;
    }
    .chip:hover {
        border-color: #4f46e5; color: #4f46e5; background: #f5f3ff;
    }
    .chip i { font-size: .85rem; }

    /* ========== Input ========== */
    .ai-input-bar { padding: 10px 16px; border-top: 1px solid #ebedf0; background: #fff; }
    .input-box {
        display: flex; align-items: flex-end; gap: 8px;
        background: #f3f4f6; border-radius: 12px;
        padding: 5px 5px 5px 14px; border: 1px solid transparent;
        transition: all .15s;
    }
    .input-box:focus-within {
        background: #fff; border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79,70,229,.1);
    }
    .input-box textarea {
        flex: 1; border: none; background: transparent; resize: none;
        font-size: .85rem; line-height: 1.45; padding: 7px 0;
        max-height: 100px; color: #1f2937; outline: none;
        font-family: inherit;
    }
    .input-box textarea::placeholder { color: #9ca3af; }
    .btn-send {
        width: 36px; height: 36px; border: none; background: #4f46e5;
        color: #fff; border-radius: 9px; display: flex;
        align-items: center; justify-content: center;
        font-size: 1rem; cursor: pointer; transition: all .15s; flex-shrink: 0;
    }
    .btn-send:hover { background: #4338ca; }
    .btn-send:disabled { background: #c7c7cc; cursor: not-allowed; }

    /* ========== Overlay ========== */
    .ai-sidebar-overlay {
        display: none; position: absolute; inset: 0;
        background: rgba(0,0,0,.3); z-index: 9;
    }
    .ai-sidebar-overlay.show { display: block; }

    /* ========== Responsive ========== */
    @media (max-width: 767.98px) {
        .ai-chat-container { height: calc(100vh - 130px); border-radius: 0; }
        .ai-sidebar {
            position: absolute; left: 0; top: 0; bottom: 0; z-index: 10;
            transform: translateX(-100%); transition: transform .25s ease;
            box-shadow: 4px 0 20px rgba(0,0,0,.08);
        }
        .ai-sidebar.show { transform: translateX(0); }
        .msg-row { max-width: 92%; }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatForm     = document.getElementById('chatForm');
        const msgInput     = document.getElementById('msgInput');
        const chatMessages = document.getElementById('chatMessages');
        const emptyState   = document.getElementById('emptyState');
        const convIdInput  = document.getElementById('conversationId');
        const btnSend      = document.getElementById('btnSend');
        const csrfToken    = document.querySelector('meta[name="csrf-token"]').content;
        const userInitial  = @json(substr(auth()->user()->name, 0, 1));

        let isSending = false;
        let lastFailedMessage = null;

        /* ===== Textarea auto-resize ===== */
        msgInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });

        /* ===== Enter = send, Shift+Enter = newline ===== */
        msgInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!isSending && msgInput.value.trim()) {
                    chatForm.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            }
        });

        /* ===== Send via AJAX ===== */
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const message = msgInput.value.trim();
            if (!message || isSending) return;

            if (message.length > 2000) {
                appendError('Pesan terlalu panjang. Maksimal 2000 karakter.');
                return;
            }

            isSending = true;
            btnSend.disabled = true;
            lastFailedMessage = null;

            // Hide empty state, show messages area
            if (emptyState) {
                emptyState.style.display = 'none';
                chatMessages.style.display = 'flex';
            }

            appendMsg('user', message);
            msgInput.value = '';
            msgInput.style.height = 'auto';
            showThinking();

            const abortController = new AbortController();
            const timeoutId = setTimeout(() => abortController.abort(), 25000);

            try {
                const fd = new FormData();
                fd.append('message', message);
                fd.append('_token', csrfToken);
                const cid = convIdInput.value;
                if (cid) fd.append('conversation_id', cid);

                const res = await fetch('{{ route("ai.chat.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: fd,
                    signal: abortController.signal
                });

                clearTimeout(timeoutId);
                hideThinking();

                if (!res.ok) {
                    if (res.status === 422) {
                        const errData = await res.json();
                        lastFailedMessage = message;
                        appendError(errData.message || 'Validasi gagal.', true);
                    } else {
                        throw new Error('Server error: ' + res.status);
                    }
                    return;
                }

                const data = await res.json();

                if (data.success) {
                    convIdInput.value = data.conversation_id;
                    appendMsg('assistant', data.ai_message.content, data.ai_message.html);
                    if (!cid) {
                        addToSidebar(data.conversation_id, data.conversation_title);
                        history.replaceState({}, '', '/ai-chat/' + data.conversation_id);
                    }
                } else {
                    lastFailedMessage = message;
                    appendError('AI tidak memberikan respons. Silakan coba lagi.', true);
                }
            } catch (err) {
                clearTimeout(timeoutId);
                hideThinking();
                lastFailedMessage = message;

                if (err.name === 'AbortError') {
                    appendError('Respons AI membutuhkan waktu terlalu lama. Silakan coba kirim ulang.', true);
                } else {
                    appendError('Gagal menghubungi AI. Silakan coba lagi.', true);
                }
            } finally {
                hideThinking();
                isSending = false;
                btnSend.disabled = false;
                msgInput.focus();
            }
        });

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

        /* ===== Append message ===== */
        function appendMsg(role, content, html = null) {
            const row = document.createElement('div');
            row.className = 'msg-row ' + (role === 'user' ? 'msg-user' : 'msg-ai');

            if (role === 'user') {
                row.innerHTML =
                    '<div class="msg-bubble msg-bubble-user">' + esc(content) + '</div>' +
                    '<div class="msg-avatar avatar-user">' + userInitial + '</div>';
            } else {
                row.innerHTML =
                    '<div class="msg-avatar avatar-ai"><i class="bi bi-robot"></i></div>' +
                    '<div class="msg-bubble msg-bubble-ai markdown-body">' + (html ? html : renderChatMarkdown(content)) + '</div>';
            }
            chatMessages.appendChild(row);
            scrollEnd();
        }

        /* ===== Append error with retry ===== */
        function appendError(msg, withRetry = false) {
            hideThinking();
            const el = document.createElement('div');
            el.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;font-size:.8rem;color:#dc2626;align-self:flex-start;max-width:78%;animation:msgIn .25s ease;';
            el.innerHTML = '<i class="bi bi-exclamation-circle"></i><span>' + esc(msg) + '</span>';
            if (withRetry && lastFailedMessage) {
                const retryBtn = document.createElement('button');
                retryBtn.style.cssText = 'background:#fff;border:1px solid #fecaca;color:#dc2626;padding:3px 10px;border-radius:6px;font-size:.72rem;font-weight:500;cursor:pointer;flex-shrink:0;';
                retryBtn.textContent = 'Coba Lagi';
                retryBtn.addEventListener('click', function() {
                    el.remove();
                    if (lastFailedMessage) {
                        msgInput.value = lastFailedMessage;
                        chatForm.dispatchEvent(new Event('submit', { cancelable: true }));
                    }
                });
                el.appendChild(retryBtn);
            }
            chatMessages.appendChild(el);
            scrollEnd();
        }

        /* ===== Thinking ===== */
        function showThinking() {
            hideThinking();
            const el = document.createElement('div');
            el.className = 'thinking-row';
            el.id = 'thinkingEl';
            el.innerHTML =
                '<div class="msg-avatar avatar-ai"><i class="bi bi-robot"></i></div>' +
                '<div class="thinking-bubble">' +
                    '<span class="thinking-label">Mengetik</span>' +
                    '<div class="thinking-dots"><span></span><span></span><span></span></div>' +
                '</div>';
            chatMessages.appendChild(el);
            scrollEnd();
        }
        function hideThinking() {
            const el = document.getElementById('thinkingEl');
            if (el) el.remove();
        }

        /* ===== Helpers ===== */
        function scrollEnd() { chatMessages.scrollTop = chatMessages.scrollHeight; }
        function esc(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

        /* ===== Add to sidebar ===== */
        function addToSidebar(id, title) {
            const list = document.getElementById('conversationList');
            const empty = document.getElementById('convEmpty');
            if (empty) empty.remove();

            const item = document.createElement('div');
            item.className = 'conv-item active';
            item.dataset.id = id;
            item.innerHTML =
                '<a href="/ai-chat/' + id + '" class="conv-link">' +
                    '<i class="bi bi-chat-dots"></i>' +
                    '<div class="conv-info">' +
                        '<span class="conv-title">' + esc(title) + '</span>' +
                        '<span class="conv-time">baru saja</span>' +
                    '</div>' +
                '</a>' +
                '<div class="conv-menu-wrapper">' +
                    '<button class="btn-conv-menu" title="Opsi"><i class="bi bi-three-dots-vertical"></i></button>' +
                    '<div class="conv-dropdown">' +
                        '<button class="conv-dropdown-item conv-dropdown-delete" data-id="' + id + '">' +
                            '<i class="bi bi-trash3"></i> Hapus percakapan' +
                        '</button>' +
                    '</div>' +
                '</div>';

            list.querySelectorAll('.conv-item').forEach(el => el.classList.remove('active'));
            list.prepend(item);
            bindMenuEvents(item);
        }

        /* ===== ⋮ Dropdown menu ===== */
        function bindMenuEvents(container) {
            const btn = container.querySelector('.btn-conv-menu');
            const dd  = container.querySelector('.conv-dropdown');
            const del = container.querySelector('.conv-dropdown-delete');

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeAllDropdowns();
                dd.classList.toggle('show');
            });

            if (del) {
                del.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dd.classList.remove('show');
                    deleteTarget = this.dataset.id;
                    deleteModal.show();
                });
            }
        }

        function closeAllDropdowns() {
            document.querySelectorAll('.conv-dropdown.show').forEach(d => d.classList.remove('show'));
        }

        // Close dropdown on outside click
        document.addEventListener('click', function() { closeAllDropdowns(); });

        // Bind existing items
        document.querySelectorAll('.conv-item').forEach(item => bindMenuEvents(item));

        /* ===== Delete conversation ===== */
        let deleteTarget = null;
        const deleteModalEl = document.getElementById('deleteModal');
        const deleteModal = deleteModalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(deleteModalEl) : null;

        const deleteAllModalEl = document.getElementById('deleteAllModal');
        const deleteAllModal = deleteAllModalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(deleteAllModalEl) : null;
        const btnClearAllHistory = document.getElementById('btnClearAllHistory');

        function updateClearAllHistoryBtn() {
            if (!btnClearAllHistory) return;
            const hasItems = document.querySelectorAll('.conv-item').length > 0;
            btnClearAllHistory.style.display = hasItems ? 'inline-block' : 'none';
        }

        async function executeDeleteSingle(id) {
            try {
                const res = await fetch('/ai-chat/' + id, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success !== false) {
                    const el = document.querySelector('.conv-item[data-id="' + id + '"]');
                    if (el) el.remove();

                    updateClearAllHistoryBtn();

                    if (convIdInput.value == id) {
                        window.location.href = '{{ route("ai.chat") }}';
                        return;
                    }
                    if (!document.querySelectorAll('.conv-item').length) {
                        document.getElementById('conversationList').innerHTML =
                            '<div class="conv-empty" id="convEmpty">' +
                                '<i class="bi bi-chat-square-text"></i>' +
                                '<span>Belum ada percakapan</span>' +
                            '</div>';
                    }
                } else {
                    alert(data.message || 'Gagal menghapus percakapan.');
                }
            } catch (err) {
                console.error('Delete error:', err);
                alert('Terjadi kesalahan koneksi saat menghapus percakapan.');
            } finally {
                if (deleteModal) deleteModal.hide();
                deleteTarget = null;
            }
        }

        document.getElementById('btnConfirmDelete').addEventListener('click', function() {
            if (!deleteTarget) return;
            executeDeleteSingle(deleteTarget);
        });

        if (btnClearAllHistory) {
            btnClearAllHistory.addEventListener('click', function(e) {
                e.preventDefault();
                if (deleteAllModal) {
                    deleteAllModal.show();
                } else if (confirm('Apakah Anda yakin ingin menghapus seluruh riwayat percakapan?')) {
                    executeDeleteAll();
                }
            });
        }

        async function executeDeleteAll() {
            try {
                const res = await fetch('{{ route("ai.chat.destroy-all") }}', {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success !== false) {
                    window.location.href = '{{ route("ai.chat") }}';
                } else {
                    alert(data.message || 'Gagal menghapus seluruh riwayat percakapan.');
                }
            } catch (err) {
                console.error('Delete all error:', err);
                alert('Terjadi kesalahan saat menghapus seluruh percakapan.');
            } finally {
                if (deleteAllModal) deleteAllModal.hide();
            }
        }

        const btnConfirmDeleteAll = document.getElementById('btnConfirmDeleteAll');
        if (btnConfirmDeleteAll) {
            btnConfirmDeleteAll.addEventListener('click', function() {
                executeDeleteAll();
            });
        }

        /* ===== Mobile sidebar ===== */
        const btnToggle = document.getElementById('btnToggleSidebar');
        const sidebar   = document.getElementById('aiSidebar');
        const overlay   = document.getElementById('aiSidebarOverlay');

        if (btnToggle) {
            btnToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });
        }
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }

        /* Init */
        if (chatMessages && chatMessages.style.display !== 'none') scrollEnd();
        msgInput.focus();
    });

    function fillMessage(text) {
        const i = document.getElementById('msgInput');
        i.value = text;
        i.focus();
        i.dispatchEvent(new Event('input'));
    }
    </script>
</x-app-layout>
