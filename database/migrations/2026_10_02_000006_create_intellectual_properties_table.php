<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intellectual_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('application_number');
            $table->date('application_date');
            $table->string('creator');
            $table->string('rights_holder');
            $table->string('ip_type', 100);
            $table->string('recordation_number')->nullable();
            $table->string('title', 500);
            $table->date('first_published_at');
            $table->string('protection_period');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ip_type', 'application_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intellectual_properties');
    }
};
