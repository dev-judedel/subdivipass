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
        Schema::create('pass_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pass_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // User who performed the action
            $table->foreignId('gate_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // e.g., "created", "updated", "approved", "rejected", "scanned"
            $table->text('description')->nullable(); // Human-readable description
            $table->json('old_values')->nullable(); // Previous state
            $table->json('new_values')->nullable(); // New state
            $table->json('metadata')->nullable(); // Additional context
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('logged_at')->useCurrent();

            // Indexes
            $table->index('pass_id');
            $table->index('user_id');
            $table->index('gate_id');
            $table->index('action');
            $table->index('logged_at');
            $table->index(['pass_id', 'logged_at']);
            $table->index(['user_id', 'logged_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pass_logs');
    }
};
