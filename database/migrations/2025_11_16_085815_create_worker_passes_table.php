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
        Schema::create('worker_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pass_id')->constrained('passes')->onDelete('cascade');

            // Worker Details
            $table->string('worker_name');
            $table->string('worker_contact')->nullable();
            $table->string('worker_email')->nullable();
            $table->string('worker_position')->nullable()->comment('Job role/position');
            $table->string('worker_id_number')->nullable()->comment('Employee ID or Gov ID');

            // Photo & QR Code
            $table->string('photo_path')->nullable()->comment('Worker photo for ID badge');
            $table->string('qr_code_path')->nullable()->comment('Individual QR code');
            $table->string('qr_signature')->nullable()->comment('HMAC signature for QR');

            // Admission Tracking
            $table->boolean('is_admitted')->default(false)->comment('Admitted today via scan');
            $table->timestamp('last_scan_at')->nullable();
            $table->foreignId('last_scan_gate_id')->nullable()->constrained('gates');
            $table->foreignId('last_scan_guard_id')->nullable()->constrained('users');
            $table->integer('total_scans')->default(0);

            // Status
            $table->enum('status', ['active', 'suspended', 'revoked'])->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('pass_id');
            $table->index('worker_name');
            $table->index('worker_contact');
            $table->index(['pass_id', 'is_admitted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_passes');
    }
};
