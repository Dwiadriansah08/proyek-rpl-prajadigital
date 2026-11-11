<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE pengajuan_surat
                MODIFY status ENUM(
                    'DRAFT',
                    'MENUNGGU_RT',
                    'MENUNGGU_RW',
                    'MENUNGGU_ADMIN_DESA',
                    'DITOLAK',
                    'SELESAI'
                ) NOT NULL DEFAULT 'MENUNGGU_RT'
            ");
        }

        Schema::table('pengajuan_surat', function (Blueprint $table) {
            if (! Schema::hasColumn('pengajuan_surat', 'approved_by_admin_desa_at')) {
                $table->timestamp('approved_by_admin_desa_at')->nullable()->after('approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE pengajuan_surat
                MODIFY status ENUM(
                    'DRAFT',
                    'MENUNGGU_RT',
                    'MENUNGGU_RW',
                    'DITOLAK',
                    'SELESAI'
                ) NOT NULL DEFAULT 'MENUNGGU_RT'
            ");
        }

        Schema::table('pengajuan_surat', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_surat', 'approved_by_admin_desa_at')) {
                $table->dropColumn('approved_by_admin_desa_at');
            }
        });
    }
};
