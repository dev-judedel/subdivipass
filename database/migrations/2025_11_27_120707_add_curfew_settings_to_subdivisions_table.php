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
        Schema::table('subdivisions', function (Blueprint $table) {
            $table->boolean('curfew_enabled')->default(false)->after('status');
            $table->time('curfew_start')->default('22:00:00')->after('curfew_enabled'); // 10:00 PM
            $table->time('curfew_end')->default('05:00:00')->after('curfew_start'); // 5:00 AM
            $table->json('curfew_exemptions')->nullable()->after('curfew_end'); // Pass type IDs that can bypass curfew
            $table->text('curfew_message')->nullable()->after('curfew_exemptions'); // Custom message shown during curfew
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subdivisions', function (Blueprint $table) {
            $table->dropColumn([
                'curfew_enabled',
                'curfew_start',
                'curfew_end',
                'curfew_exemptions',
                'curfew_message',
            ]);
        });
    }
};
