<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surat_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_surat_id')->constrained('pengajuan_surat')->onDelete('cascade');
            $table->string('event_status'); // MENUNGGU_RT, MENUNGGU_RW, SELESAI, DITOLAK
            $table->string('recipient_role'); // rt, rw, warga, super_admin
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('recipient_warga_id')->nullable()->constrained('warga')->onDelete('set null');
            $table->string('recipient_phone');
            $table->text('message');
            $table->string('status')->default('menunggu'); // menunggu, simulated, sent, failed, error
            $table->text('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Kunci idempotensi unik: satu transisi status per pengajuan surat ke satu penerima telepon hanya boleh terjadi 1 kali
            $table->unique(['pengajuan_surat_id', 'event_status', 'recipient_phone'], 'uq_surat_status_phone');
            $table->index(['pengajuan_surat_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_notifications');
    }
};
