<!-- AI Assistant Floating Widget Component (PrajaDigital) -->
<button class="chatbot-fab" id="chatbotFab" title="AI Assistant Desa" type="button" aria-label="Buka Chat AI Assistant">
    <i class="bi bi-chat-dots-fill" id="chatbotFabIcon"></i>
</button>

<div class="chatbot-panel" id="chatbotPanel" role="dialog" aria-labelledby="cbPanelTitle" aria-modal="true">
    <!-- Header -->
    <div class="chatbot-panel-header">
        <div class="panel-title" id="cbPanelTitle">
            <i class="bi bi-robot"></i>
            <span>AI Assistant Desa</span>
        </div>
        <button class="chatbot-panel-close" id="chatbotClose" title="Tutup Chat" type="button" aria-label="Tutup">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Conversation list -->
    <div class="chatbot-conv-list" id="cbConvList">
        <div id="cbConvListInner">
            <!-- Populated dynamically -->
        </div>
        <div class="d-flex gap-1 mt-1">
            <button class="chatbot-new-chat flex-grow-1" id="cbNewChat" type="button">
                <i class="bi bi-plus-lg"></i> Percakapan Baru
            </button>
            <button class="chatbot-new-chat text-danger border-danger-subtle px-2" id="cbClearAllBtn" type="button" title="Hapus Seluruh Riwayat" style="display:none; flex:0 0 auto;">
                <i class="bi bi-trash3"></i>
            </button>
        </div>
    </div>

    <!-- Messages / Empty State -->
    <div class="chatbot-empty" id="cbEmptyState">
        <div class="chatbot-empty-icon">
            <i class="bi bi-robot"></i>
        </div>
        <h6>Halo, {{ auth()->user()->name }}!</h6>
        <p>Saya AI Assistant Sistem Informasi Desa. Siap membantu informasi layanan, alur persuratan, dan administrasi desa secara ramah dan solutif.</p>
        <div class="chatbot-chips">
            <button class="chatbot-chip" type="button" data-msg="Apa saja layanan surat yang tersedia?">
                <i class="bi bi-envelope"></i> Layanan Surat
            </button>
            <button class="chatbot-chip" type="button" data-msg="Bagaimana cara mengajukan surat?">
                <i class="bi bi-file-earmark-text"></i> Cara Pengajuan
            </button>
            <button class="chatbot-chip" type="button" data-msg="Cek status pengajuan surat saya">
                <i class="bi bi-search"></i> Cek Status
            </button>
        </div>
    </div>
    <div class="chatbot-messages" id="cbMessages" style="display:none;"></div>

    <!-- Input Box -->
    <div class="chatbot-input">
        <div class="chatbot-input-box">
            <textarea id="cbMsgInput" rows="1" placeholder="Ketik pesan untuk AI Assistant..." maxlength="2000"></textarea>
            <button class="chatbot-send-btn" id="cbSendBtn" title="Kirim Pesan" type="button" aria-label="Kirim">
                <i class="bi bi-arrow-up"></i>
            </button>
        </div>
        <div style="font-size:.65rem; color:#9ca3af; text-align:center; margin-top:4px;" id="cbCharCount"></div>
    </div>
</div>

