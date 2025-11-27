<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FixUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:fix-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix user role assignments for seeded users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Fixing user role assignments...');
        $this->newLine();

        // Define user email to role mappings
        $userRoles = [
            // Super Admin
            'admin@subdivipass.com' => 'super-admin',

            // Admins
            'john.smith@greenfieldheights.com' => 'admin',
            'maria.santos@sunsetvillage.com' => 'admin',

            // Employees
            'anna.rodriguez@greenfieldheights.com' => 'employee',
            'carlos.reyes@lakesideresidences.com' => 'employee',

            // Guards
            'pedro.cruz@greenfieldheights.com' => 'guard',
            'roberto.garcia@greenfieldheights.com' => 'guard',
            'miguel.delacruz@sunsetvillage.com' => 'guard',

            // Requesters
            'sofia.fernandez@gmail.com' => 'requester',
            'luis.mendoza@yahoo.com' => 'requester',
        ];

        $fixed = 0;
        $notFound = 0;
        $errors = 0;

        foreach ($userRoles as $email => $role) {
            try {
                $user = User::where('email', $email)->first();

                if (!$user) {
                    $this->warn("  ⚠  User not found: {$email}");
                    $notFound++;
                    continue;
                }

                // Sync roles (replaces all existing roles with the new one)
                $user->syncRoles([$role]);

                $this->info("  ✓  {$user->name} ({$email}) → {$role}");
                $fixed++;
            } catch (\Exception $e) {
                $this->error("  ✗  Error assigning role to {$email}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();
        $this->info("📊 Summary:");
        $this->info("  • Fixed: {$fixed}");

        if ($notFound > 0) {
            $this->warn("  • Not found: {$notFound}");
        }

        if ($errors > 0) {
            $this->error("  • Errors: {$errors}");
        }

        $this->newLine();

        if ($errors === 0 && $notFound === 0) {
            $this->info('✅ All user roles fixed successfully!');
        } elseif ($fixed > 0) {
            $this->info('✅ User roles partially fixed. Check warnings above.');
        } else {
            $this->error('❌ Failed to fix user roles. Check errors above.');
        }

        return $errors === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
