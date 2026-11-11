<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informasi', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('isi');
            $table->string('gambar')->nullable();
            $table->string('kategori'); // pengumuman, kegiatan, jadwal, dll
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('tanggal')->useCurrent();
            $table->boolean('status_publikasi')->default(true);
            $table->enum('scope_wilayah', ['Desa', 'RW', 'RT']);
            $table->foreignId('rw_id')->nullable()->constrained('rw')->onDelete('cascade');
            $table->foreignId('rt_id')->nullable()->constrained('rt')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informasi');
    }
};
