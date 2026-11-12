<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE intellectual_properties MODIFY first_published_at DATE NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE intellectual_properties ALTER COLUMN first_published_at DROP NOT NULL');
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::transaction(function () {
                $hasTypeDetails = Schema::hasColumn('intellectual_properties', 'type_details');
                $typeDetailsColumn = $hasTypeDetails ? ', type_details TEXT NULL' : '';
                $typeDetailsSelect = $hasTypeDetails ? ', type_details' : '';

                DB::statement("CREATE TABLE intellectual_properties_nullable (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, application_number VARCHAR NOT NULL, application_date DATE NOT NULL, creator VARCHAR NULL, rights_holder VARCHAR NOT NULL, ip_type VARCHAR(100) NOT NULL, recordation_number VARCHAR NULL, title VARCHAR(500) NOT NULL, first_published_at DATE NULL, protection_period VARCHAR NOT NULL{$typeDetailsColumn}, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL, FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE)");
                DB::statement("INSERT INTO intellectual_properties_nullable (id, user_id, application_number, application_date, creator, rights_holder, ip_type, recordation_number, title, first_published_at, protection_period{$typeDetailsSelect}, created_at, updated_at, deleted_at) SELECT id, user_id, application_number, application_date, creator, rights_holder, ip_type, recordation_number, title, first_published_at, protection_period{$typeDetailsSelect}, created_at, updated_at, deleted_at FROM intellectual_properties");
                DB::statement('DROP TABLE intellectual_properties');
                DB::statement('ALTER TABLE intellectual_properties_nullable RENAME TO intellectual_properties');
                DB::statement('CREATE INDEX intellectual_properties_ip_type_application_date_index ON intellectual_properties (ip_type, application_date)');
            });
            DB::statement('PRAGMA foreign_keys = ON');
            return;
        }

        throw new RuntimeException("Driver database {$driver} belum didukung oleh migration tanggal pengumuman HKI nullable.");
    }

    public function down(): void
    {
        if (DB::table('intellectual_properties')->whereNull('first_published_at')->exists()) {
            throw new RuntimeException('Migration tidak dapat dibatalkan selama ada data HKI tanpa tanggal pengumuman.');
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE intellectual_properties MODIFY first_published_at DATE NOT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE intellectual_properties ALTER COLUMN first_published_at SET NOT NULL');
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::transaction(function () {
                $hasTypeDetails = Schema::hasColumn('intellectual_properties', 'type_details');
                $typeDetailsColumn = $hasTypeDetails ? ', type_details TEXT NULL' : '';
                $typeDetailsSelect = $hasTypeDetails ? ', type_details' : '';

                DB::statement("CREATE TABLE intellectual_properties_required (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, application_number VARCHAR NOT NULL, application_date DATE NOT NULL, creator VARCHAR NULL, rights_holder VARCHAR NOT NULL, ip_type VARCHAR(100) NOT NULL, recordation_number VARCHAR NULL, title VARCHAR(500) NOT NULL, first_published_at DATE NOT NULL, protection_period VARCHAR NOT NULL{$typeDetailsColumn}, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL, FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE)");
                DB::statement("INSERT INTO intellectual_properties_required (id, user_id, application_number, application_date, creator, rights_holder, ip_type, recordation_number, title, first_published_at, protection_period{$typeDetailsSelect}, created_at, updated_at, deleted_at) SELECT id, user_id, application_number, application_date, creator, rights_holder, ip_type, recordation_number, title, first_published_at, protection_period{$typeDetailsSelect}, created_at, updated_at, deleted_at FROM intellectual_properties");
                DB::statement('DROP TABLE intellectual_properties');
                DB::statement('ALTER TABLE intellectual_properties_required RENAME TO intellectual_properties');
                DB::statement('CREATE INDEX intellectual_properties_ip_type_application_date_index ON intellectual_properties (ip_type, application_date)');
            });
            DB::statement('PRAGMA foreign_keys = ON');
            return;
        }

        throw new RuntimeException("Driver database {$driver} belum didukung oleh rollback migration tanggal pengumuman HKI nullable.");
    }
};
