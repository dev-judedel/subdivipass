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
            $table->time('allowed_entry_time_start')->nullable()->after('valid_to'); // e.g., 06:00:00
            $table->time('allowed_entry_time_end')->nullable()->after('allowed_entry_time_start'); // e.g., 21:59:59
            $table->boolean('curfew_exempt')->default(false)->after('allowed_entry_time_end');
            $table->text('time_restriction_notes')->nullable()->after('curfew_exempt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passes', function (Blueprint $table) {
            $table->dropColumn([
                'allowed_entry_time_start',
                'allowed_entry_time_end',
                'curfew_exempt',
                'time_restriction_notes',
            ]);
        });
    }
};
