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
        Schema::create('pass_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subdivision_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Visitor", "Delivery", "Job Order", "Event"
            $table->string('slug')->unique(); // e.g., "visitor", "delivery"
            $table->text('description')->nullable();
            $table->json('config')->nullable(); // Configuration: required fields, validity rules, approval workflow
            $table->integer('default_validity_hours')->default(24); // Default validity in hours
            $table->integer('max_validity_hours')->nullable(); // Maximum allowed validity
            $table->boolean('requires_approval')->default(false);
            $table->string('color')->default('#3B82F6'); // Color code for UI distinction
            $table->string('icon')->nullable(); // Icon class or path
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('subdivision_id');
            $table->index('slug');
            $table->index('is_active');
            $table->index(['subdivision_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pass_types');
    }
};
