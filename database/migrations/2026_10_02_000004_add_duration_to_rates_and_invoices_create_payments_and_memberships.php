<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_rates', function (Blueprint $table) { $table->unsignedInteger('duration_months')->default(12)->after('amount'); });
        Schema::table('invoices', function (Blueprint $table) { $table->unsignedInteger('duration_months')->default(12)->after('amount'); });
        Schema::create('payments', function (Blueprint $table) { $table->id(); $table->foreignId('invoice_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('proof_path'); $table->string('status', 20)->default('submitted')->index(); $table->text('admin_note')->nullable(); $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('reviewed_at')->nullable(); $table->timestamps(); });
        Schema::create('memberships', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('membership_application_id')->constrained()->cascadeOnDelete(); $table->foreignId('invoice_id')->unique()->constrained()->cascadeOnDelete(); $table->date('starts_at'); $table->date('ends_at'); $table->string('status', 20)->default('active'); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('memberships'); Schema::dropIfExists('payments'); Schema::table('invoices', fn (Blueprint $table) => $table->dropColumn('duration_months')); Schema::table('membership_rates', fn (Blueprint $table) => $table->dropColumn('duration_months')); }
};
