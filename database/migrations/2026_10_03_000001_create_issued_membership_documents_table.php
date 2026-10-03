<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('issued_membership_documents', function (Blueprint $table) { $table->id(); $table->foreignId('membership_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('type', 20); $table->string('number')->unique(); $table->string('file_path'); $table->json('snapshot'); $table->timestamp('issued_at'); $table->timestamps(); $table->unique(['membership_id', 'type']); }); }
    public function down(): void { Schema::dropIfExists('issued_membership_documents'); }
};
