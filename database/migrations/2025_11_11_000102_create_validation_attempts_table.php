<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pass_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('gate_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('guard_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('method')->nullable();
            $table->string('input_code')->nullable();
            $table->string('status')->default('error');
            $table->string('message')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->boolean('was_offline')->default(false);
            $table->timestamps();

            $table->index(['gate_id', 'created_at']);
            $table->index(['guard_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_attempts');
    }
};
