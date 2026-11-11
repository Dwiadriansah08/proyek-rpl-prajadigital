<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
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
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE pengajuan_surat
                MODIFY status ENUM(
                    'DRAFT',
                    'MENUNGGU',
                    'DIPROSES',
                    'DISETUJUI',
                    'DITOLAK',
                    'SELESAI'
                ) NOT NULL DEFAULT 'MENUNGGU'
            ");
        }
    }
};
