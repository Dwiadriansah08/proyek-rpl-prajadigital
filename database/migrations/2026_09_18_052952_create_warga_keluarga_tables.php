<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warga', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique();
            $table->string('nomor_kk');
            $table->string('nama_lengkap');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('agama');
            $table->string('status_perkawinan');
            $table->string('pendidikan');
            $table->string('pekerjaan');
            $table->string('nomor_telepon')->nullable();
            $table->string('email')->nullable();
            $table->text('alamat');
            $table->foreignId('rw_id')->constrained('rw')->onDelete('cascade');
            $table->foreignId('rt_id')->constrained('rt')->onDelete('cascade');
            $table->string('status_dalam_keluarga');
            $table->string('status_kependudukan');
            $table->string('foto')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
            $table->index('nik');
            $table->index('nomor_kk');
            $table->index('nama_lengkap');
            $table->index(['rw_id', 'rt_id']);
        });

        Schema::create('keluarga', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kk')->unique();
            $table->string('kepala_keluarga');
            $table->text('alamat');
            $table->foreignId('rw_id')->constrained('rw')->onDelete('cascade');
            $table->foreignId('rt_id')->constrained('rt')->onDelete('cascade');
            $table->timestamps();

            $table->index(['nomor_kk', 'rw_id', 'rt_id']);
        });

        Schema::create('keluarga_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluarga_id')->constrained('keluarga')->onDelete('cascade');
            $table->foreignId('warga_id')->constrained('warga')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keluarga_members');
        Schema::dropIfExists('keluarga');
        Schema::dropIfExists('warga');
    }
};
