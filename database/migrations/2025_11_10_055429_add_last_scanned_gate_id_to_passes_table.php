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
        Schema::table('passes', function (Blueprint $table) {
            $table->foreignId('last_scanned_gate_id')
                ->nullable()
                ->after('last_scanned_at')
                ->constrained('gates')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_scanned_gate_id');
        });
    }
};
