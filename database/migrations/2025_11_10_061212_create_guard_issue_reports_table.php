<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('guard_issue_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guard_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pass_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('gate_id')->nullable()->constrained()->nullOnDelete();
            $table->string('issue_type');
            $table->enum('severity', ['low', 'medium', 'high'])->default('low');
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'resolved'])->default('open');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['guard_id', 'status']);
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guard_issue_reports');
    }
};
