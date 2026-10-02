<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_rates', function (Blueprint $table) { $table->id(); $table->string('account_type', 20)->index(); $table->string('name'); $table->decimal('amount', 15, 2); $table->date('active_from'); $table->date('active_until')->nullable(); $table->timestamps(); $table->softDeletes(); });
        Schema::create('invoices', function (Blueprint $table) { $table->id(); $table->foreignId('membership_application_id')->unique()->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('membership_rate_id')->nullable()->constrained()->nullOnDelete(); $table->string('number')->unique(); $table->decimal('amount', 15, 2); $table->date('issued_at'); $table->date('due_at'); $table->string('status', 20)->default('unpaid')->index(); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('invoices'); Schema::dropIfExists('membership_rates'); }
};
