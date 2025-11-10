<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');

        // Seed in specific order due to dependencies
        $this->call([
            RolePermissionSeeder::class,     // 1. Create roles and permissions first
            SubdivisionSeeder::class,        // 2. Create subdivisions
            GateSeeder::class,               // 3. Create gates (depends on subdivisions)
            PassTypeSeeder::class,           // 4. Create pass types (depends on subdivisions)
            UserSeeder::class,               // 5. Create users (depends on roles and subdivisions)
        ]);

        $this->command->info('Database seeding completed successfully!');
        $this->command->line('');
        $this->command->info('Test Credentials:');
        $this->command->line('Super Admin: admin@subdivipass.com / password');
        $this->command->line('Admin: john.smith@greenfieldheights.com / password');
        $this->command->line('Employee: anna.rodriguez@greenfieldheights.com / password');
        $this->command->line('Guard: pedro.cruz@greenfieldheights.com / password');
        $this->command->line('Requester: sofia.fernandez@gmail.com / password');
    }
}
