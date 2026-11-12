<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE intellectual_properties MODIFY creator VARCHAR(255) NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE intellectual_properties ALTER COLUMN creator DROP NOT NULL');
            return;
        }

        if ($driver === 'sqlite') {
            $column = collect(DB::select('PRAGMA table_info(intellectual_properties)'))
                ->firstWhere('name', 'creator');

            if ($column && (int) $column->notnull === 0) {
                return;
            }

            throw new RuntimeException('Kolom creator pada SQLite lama perlu dibuat nullable melalui rebuild tabel terkontrol.');
        }

        throw new RuntimeException("Driver database {$driver} belum didukung oleh migration creator HKI nullable.");
    }

    public function down(): void
    {
        if (DB::table('intellectual_properties')->whereNull('creator')->exists()) {
            throw new RuntimeException('Migration tidak dapat dibatalkan selama masih ada data HKI tanpa creator.');
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE intellectual_properties MODIFY creator VARCHAR(255) NOT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE intellectual_properties ALTER COLUMN creator SET NOT NULL');
            return;
        }

        if ($driver === 'sqlite') {
            throw new RuntimeException('SQLite tidak dapat mengembalikan constraint NOT NULL tanpa rebuild tabel.');
        }

        throw new RuntimeException("Driver database {$driver} belum didukung oleh rollback migration creator HKI.");
    }
};
