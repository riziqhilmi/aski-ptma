<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('membership_document_settings', function (Blueprint $table) { $table->id(); $table->string('type', 20)->unique(); $table->string('title'); $table->string('number_prefix'); $table->string('background_color')->default('#0b6254'); $table->string('accent_color')->default('#d4a72c'); $table->timestamps(); }); } public function down(): void { Schema::dropIfExists('membership_document_settings'); } };
