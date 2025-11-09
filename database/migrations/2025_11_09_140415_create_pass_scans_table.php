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
        Schema::create('pass_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pass_id')->constrained()->onDelete('cascade');
            $table->foreignId('gate_id')->constrained()->onDelete('cascade');
            $table->foreignId('guard_id')->constrained('users')->onDelete('cascade');
            $table->enum('scan_type', ['entry', 'exit', 'validation'])->default('entry');
            $table->enum('scan_method', ['qr', 'pin', 'manual'])->default('qr');
            $table->enum('result', ['success', 'failed', 'warning'])->default('success');
            $table->text('result_message')->nullable(); // e.g., "Pass expired", "Invalid QR"
            $table->json('scan_data')->nullable(); // Additional scan metadata
            $table->string('device_id')->nullable(); // Device identifier
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('location')->nullable(); // GPS coordinates at time of scan
            $table->boolean('was_offline')->default(false); // Scanned while offline
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamp('synced_at')->nullable(); // For offline scans

            // Indexes
            $table->index('pass_id');
            $table->index('gate_id');
            $table->index('guard_id');
            $table->index('scan_type');
            $table->index('result');
            $table->index('scanned_at');
            $table->index(['pass_id', 'scanned_at']);
            $table->index(['gate_id', 'scanned_at']);
            $table->index(['guard_id', 'scanned_at']);
            $table->index('was_offline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pass_scans');
    }
};
