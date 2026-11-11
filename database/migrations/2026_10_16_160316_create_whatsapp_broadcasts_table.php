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
        // 1. Tambah target fields ke tabel informasi bila belum ada
        Schema::table('informasi', function (Blueprint $table) {
            if (! Schema::hasColumn('informasi', 'target_type')) {
                $table->string('target_type')->default('desa')->after('scope_wilayah'); // desa, rw, rt, role
            }
            if (! Schema::hasColumn('informasi', 'target_role')) {
                $table->string('target_role')->nullable()->after('target_type'); // super_admin, rw, rt, warga
            }
        });

        // 2. Buat tabel whatsapp_broadcasts untuk audit & tracking pengiriman WA per pengumuman
        Schema::create('whatsapp_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('informasi_id')->constrained('informasi')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->string('target_description');
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->string('status')->default('completed'); // pending, sending, completed, failed
            $table->text('error_summary')->nullable();
            $table->timestamps();
        });

        // 3. Buat tabel whatsapp_recipients untuk detail penerima, cegah nomor ganda, dan catat status
        Schema::create('whatsapp_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained('whatsapp_broadcasts')->onDelete('cascade');
            $table->foreignId('informasi_id')->constrained('informasi')->onDelete('cascade');
            $table->foreignId('warga_id')->nullable()->constrained('warga')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->enum('status', ['menunggu', 'berhasil', 'gagal'])->default('menunggu');
            $table->text('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Cegah nomor ganda pada satu pengumuman
            $table->unique(['informasi_id', 'recipient_phone'], 'uq_info_phone');
            $table->index(['broadcast_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_recipients');
        Schema::dropIfExists('whatsapp_broadcasts');

        Schema::table('informasi', function (Blueprint $table) {
            if (Schema::hasColumn('informasi', 'target_role')) {
                $table->dropColumn('target_role');
            }
            if (Schema::hasColumn('informasi', 'target_type')) {
                $table->dropColumn('target_type');
            }
        });
    }
};
