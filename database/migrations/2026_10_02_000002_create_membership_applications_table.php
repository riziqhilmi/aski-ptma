<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 40)->index();
            $table->text('admin_note')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();
        });
        Schema::create('membership_application_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('membership_application_id');
            $table->foreign('membership_application_id', 'mah_application_id_fk')->references('id')->on('membership_applications')->cascadeOnDelete();
            $table->string('status', 40);
            $table->text('note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('membership_application_histories'); Schema::dropIfExists('membership_applications'); }
};
