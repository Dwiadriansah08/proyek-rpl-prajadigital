<?php

use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\InformasiController;
use App\Http\Controllers\KeluargaController;
use App\Http\Controllers\KonfigurasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengajuanSuratController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RtController;
use App\Http\Controllers\RwController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WargaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:super_admin')->group(function () {
        Route::resource('desa', DesaController::class);
        Route::get('konfigurasi', [KonfigurasiController::class, 'index'])->name('konfigurasi.index');
        Route::post('konfigurasi/ai', [KonfigurasiController::class, 'updateAi'])->name('konfigurasi.update-ai');
        Route::post('konfigurasi/whatsapp', [KonfigurasiController::class, 'updateWhatsapp'])->name('konfigurasi.update-whatsapp');
        Route::post('konfigurasi/test-ai', [KonfigurasiController::class, 'testAi'])->name('konfigurasi.test-ai');
        Route::post('konfigurasi/test-wa', [KonfigurasiController::class, 'testWa'])->name('konfigurasi.test-wa');
    });

    Route::middleware('role:super_admin,admin_desa')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export-pdf');
        Route::get('laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.export-excel');
    });

    Route::middleware('role:super_admin,admin_desa,rw')->group(function () {
        Route::resource('rw', RwController::class);
    });

    Route::middleware('role:super_admin,admin_desa,rw,rt')->group(function () {
        Route::resource('rt', RtController::class);
        Route::resource('keluarga', KeluargaController::class);
    });

    Route::resource('warga', WargaController::class);

    // Surat Routes
    Route::resource('surat', PengajuanSuratController::class);
    Route::post('surat/{surat}/process', [PengajuanSuratController::class, 'process'])->name('surat.process');
    Route::get('surat/{surat}/download', [PengajuanSuratController::class, 'download'])->name('surat.download');

    // Informasi Publik
    Route::resource('informasi', InformasiController::class);
    Route::post('informasi/{informasi}/broadcast', [InformasiController::class, 'broadcastWhatsapp'])->name('informasi.broadcast');

    // AI Chat & Assistant Routes
    Route::get('ai-chat', [AiChatController::class, 'index'])->name('ai.chat');
    Route::delete('ai-chat', [AiChatController::class, 'destroyAll'])->name('ai.chat.destroy-all');
    Route::get('ai-chat/{id}', [AiChatController::class, 'show'])->name('ai.chat.show');
    Route::post('ai-chat', [AiChatController::class, 'store'])->name('ai.chat.store');
    Route::delete('ai-chat/{id}', [AiChatController::class, 'destroy'])->name('ai.chat.destroy');
    Route::post('ai-assistant/chat', [AiAssistantController::class, 'chat'])->name('ai.assistant.chat');
});

require __DIR__.'/auth.php';
