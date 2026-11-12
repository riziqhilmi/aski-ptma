<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intellectual_properties', function (Blueprint $table) {
            $table->json('type_details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('intellectual_properties', function (Blueprint $table) {
            $table->dropColumn('type_details');
        });
    }
};
