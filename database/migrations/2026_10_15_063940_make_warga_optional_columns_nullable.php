<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Membuat kolom-kolom opsional di tabel warga menjadi nullable.
 *
 * Kolom yang diubah:
 * - pendidikan: tidak ditampilkan di form sebelumnya, NOT NULL menyebabkan error saat insert
 * - pekerjaan: tidak ditampilkan di form, NOT NULL menyebabkan error saat insert
 * - tempat_lahir, tanggal_lahir: field opsional secara bisnis, divalidasi nullable di controller
 * - agama, status_perkawinan: field dengan select, tapi masih bisa opsional
 * - alamat: textarea opsional
 * - status_dalam_keluarga, status_kependudukan: punya default selected di form
 *
 * Migrasi ini tidak merusak data yang sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warga', function (Blueprint $table) {
            $table->string('pendidikan')->nullable()->change();
            $table->string('pekerjaan')->nullable()->change();
            $table->string('tempat_lahir')->nullable()->change();
            $table->date('tanggal_lahir')->nullable()->change();
            $table->string('agama')->nullable()->change();
            $table->string('status_perkawinan')->nullable()->change();
            $table->text('alamat')->nullable()->change();
            $table->string('status_dalam_keluarga')->nullable()->change();
            $table->string('status_kependudukan')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('warga', function (Blueprint $table) {
            // Perhatian: rollback ini bisa gagal jika ada data NULL di kolom tersebut
            $table->string('pendidikan')->nullable(false)->change();
            $table->string('pekerjaan')->nullable(false)->change();
            $table->string('tempat_lahir')->nullable(false)->change();
            $table->date('tanggal_lahir')->nullable(false)->change();
            $table->string('agama')->nullable(false)->change();
            $table->string('status_perkawinan')->nullable(false)->change();
            $table->text('alamat')->nullable(false)->change();
            $table->string('status_dalam_keluarga')->nullable(false)->change();
            $table->string('status_kependudukan')->nullable(false)->change();
        });
    }
};
