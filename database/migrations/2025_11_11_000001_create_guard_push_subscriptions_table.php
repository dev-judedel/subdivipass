<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guard_push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('endpoint')->unique();
            $table->string('auth_token');
            $table->string('p256dh_key');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guard_push_subscriptions');
    }
};
