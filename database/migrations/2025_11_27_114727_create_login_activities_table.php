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
        Schema::create('login_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('email')->index();
            $table->enum('type', ['success', 'failed', 'locked_out'])->default('success');
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->string('device_type', 50)->nullable(); // desktop, mobile, tablet
            $table->string('browser', 100)->nullable();
            $table->string('platform', 100)->nullable(); // Windows, Mac, Linux, iOS, Android
            $table->string('country', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('logged_in_at');
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'logged_in_at']);
            $table->index(['email', 'type']);
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_activities');
    }
};
