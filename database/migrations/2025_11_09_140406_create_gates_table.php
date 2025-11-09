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
        Schema::create('gates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subdivision_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Main Gate", "Gate 2"
            $table->string('code')->unique(); // Unique gate code (e.g., GATE001)
            $table->text('location')->nullable(); // Physical location description
            $table->json('coordinates')->nullable(); // GPS coordinates
            $table->enum('type', ['entry', 'exit', 'both'])->default('both');
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('subdivision_id');
            $table->index('code');
            $table->index('status');
            $table->index(['subdivision_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gates');
    }
};
