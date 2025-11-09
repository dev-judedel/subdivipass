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
        Schema::create('passes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // UUID for QR code
            $table->string('pass_number')->unique(); // Human-readable pass number (e.g., PASS-2024-001)
            $table->foreignId('subdivision_id')->constrained()->onDelete('cascade');
            $table->foreignId('pass_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();

            // Visitor Information
            $table->string('visitor_name');
            $table->string('visitor_phone')->nullable();
            $table->string('visitor_email')->nullable();
            $table->string('visitor_id_type')->nullable(); // e.g., "Driver's License", "Passport"
            $table->string('visitor_id_number')->nullable();
            $table->string('visitor_company')->nullable();
            $table->text('visitor_address')->nullable();
            $table->string('vehicle_plate_number')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->json('visitor_details')->nullable(); // Additional custom fields

            // Pass Details
            $table->string('purpose')->nullable(); // Purpose of visit
            $table->text('notes')->nullable();
            $table->string('qr_code_path')->nullable(); // Path to generated QR code image
            $table->string('qr_signature'); // HMAC signature for validation
            $table->string('pin', 6); // 6-digit PIN for manual entry

            // Validity
            $table->timestamp('valid_from');
            $table->timestamp('valid_to');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('activated_at')->nullable(); // First successful scan
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            // Status
            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'active',
                'expired',
                'revoked',
                'rejected'
            ])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->text('revocation_reason')->nullable();

            // Tracking
            $table->integer('scan_count')->default(0);
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('uuid');
            $table->index('pass_number');
            $table->index('subdivision_id');
            $table->index('pass_type_id');
            $table->index('requester_id');
            $table->index('status');
            $table->index('pin');
            $table->index('valid_from');
            $table->index('valid_to');
            $table->index(['subdivision_id', 'status']);
            $table->index(['status', 'valid_from', 'valid_to']);
            $table->index('visitor_phone');
            $table->index('vehicle_plate_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passes');
    }
};
