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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->json('subdivision_ids')->nullable()->after('password'); // Array of assigned subdivision IDs
            $table->foreignId('primary_subdivision_id')->nullable()->after('subdivision_ids')->constrained('subdivisions')->nullOnDelete();
            $table->json('gate_ids')->nullable()->after('primary_subdivision_id'); // For guards - assigned gates
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('gate_ids');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->string('avatar_path')->nullable()->after('last_login_ip');
            $table->boolean('two_factor_enabled')->default(false)->after('avatar_path');
            $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('primary_subdivision_id');
            $table->index('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['primary_subdivision_id']);
            $table->dropIndex(['last_login_at']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'phone',
                'subdivision_ids',
                'primary_subdivision_id',
                'gate_ids',
                'status',
                'last_login_at',
                'last_login_ip',
                'avatar_path',
                'two_factor_enabled',
                'two_factor_secret',
            ]);
        });
    }
};
