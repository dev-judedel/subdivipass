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
            // Pass mode: 'single' or 'group'
            $table->enum('pass_mode', ['single', 'group'])->default('single')->after('status');

            // Group pass details
            $table->integer('group_size')->nullable()->after('pass_mode')
                ->comment('Number of people in group pass');

            $table->json('group_members')->nullable()->after('group_size')
                ->comment('Array of group member details (name, contact, etc.)');

            // Index for filtering by pass mode
            $table->index('pass_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passes', function (Blueprint $table) {
            $table->dropIndex(['pass_mode']);
            $table->dropColumn(['pass_mode', 'group_size', 'group_members']);
        });
    }
};
