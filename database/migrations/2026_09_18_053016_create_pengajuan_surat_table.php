<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_surat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga')->onDelete('cascade');
            $table->foreignId('template_id')->constrained('surat_templates')->onDelete('cascade');
            $table->string('nomor_surat')->nullable()->unique();
            $table->json('data_tambahan')->nullable(); // Answers to form_fields
            $table->enum('status', ['DRAFT', 'MENUNGGU_RT', 'MENUNGGU_RW', 'MENUNGGU_ADMIN_DESA', 'DITOLAK', 'SELESAI', 'MENUNGGU', 'DIPROSES', 'DISETUJUI'])->default('MENUNGGU_RT');
            $table->text('catatan')->nullable(); // Reason for rejection or notes
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->string('file_pdf')->nullable(); // Path to generated PDF
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_surat');
    }
};
