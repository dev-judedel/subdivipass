<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get subdivision and gate IDs for assignments
        $greenfieldId = DB::table('subdivisions')->where('code', 'GFH001')->value('id');
        $sunsetId = DB::table('subdivisions')->where('code', 'SUV001')->value('id');
        $lakesideId = DB::table('subdivisions')->where('code', 'LSR001')->value('id');

        $mainGateId = DB::table('gates')->where('code', 'GFH-MAIN')->value('id');
        $serviceGateId = DB::table('gates')->where('code', 'GFH-SERVICE')->value('id');

        // Super Admin
        $superAdmin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@subdivipass.com',
            'password' => Hash::make('password'),
            'phone' => '+63-999-999-9999',
            'subdivision_ids' => json_encode([$greenfieldId, $sunsetId, $lakesideId]),
            'gate_ids' => null,
            'two_factor_enabled' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super-admin');

        // Admin for Greenfield Heights
        $admin1 = User::create([
            'name' => 'John Smith',
            'email' => 'john.smith@greenfieldheights.com',
            'password' => Hash::make('password'),
            'phone' => '+63-912-345-6789',
            'subdivision_ids' => json_encode([$greenfieldId]),
            'gate_ids' => null,
            'two_factor_enabled' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin1->assignRole('admin');

        // Admin for Sunset Village
        $admin2 = User::create([
            'name' => 'Maria Santos',
            'email' => 'maria.santos@sunsetvillage.com',
            'password' => Hash::make('password'),
            'phone' => '+63-917-876-5432',
            'subdivision_ids' => json_encode([$sunsetId]),
            'gate_ids' => null,
            'two_factor_enabled' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin2->assignRole('admin');

        // Employee for Greenfield Heights
        $employee1 = User::create([
            'name' => 'Anna Rodriguez',
            'email' => 'anna.rodriguez@greenfieldheights.com',
            'password' => Hash::make('password'),
            'phone' => '+63-918-111-2222',
            'subdivision_ids' => json_encode([$greenfieldId]),
            'gate_ids' => null,
            'two_factor_enabled' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $employee1->assignRole('employee');

        // Employee for Lakeside
        $employee2 = User::create([
            'name' => 'Carlos Reyes',
            'email' => 'carlos.reyes@lakesideresidences.com',
            'password' => Hash::make('password'),
            'phone' => '+63-919-333-4444',
            'subdivision_ids' => json_encode([$lakesideId]),
            'gate_ids' => null,
            'two_factor_enabled' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $employee2->assignRole('employee');

        // Guard for Greenfield Main Gate
        $guard1 = User::create([
            'name' => 'Pedro Cruz',
            'email' => 'pedro.cruz@greenfieldheights.com',
            'password' => Hash::make('password'),
            'phone' => '+63-920-555-6666',
            'subdivision_ids' => json_encode([$greenfieldId]),
            'gate_ids' => json_encode([$mainGateId]),
            'two_factor_enabled' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $guard1->assignRole('guard');

        // Guard for Greenfield Service Gate
        $guard2 = User::create([
            'name' => 'Roberto Garcia',
            'email' => 'roberto.garcia@greenfieldheights.com',
            'password' => Hash::make('password'),
            'phone' => '+63-921-777-8888',
            'subdivision_ids' => json_encode([$greenfieldId]),
            'gate_ids' => json_encode([$serviceGateId]),
            'two_factor_enabled' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $guard2->assignRole('guard');

        // Guard for Sunset Village
        $guard3 = User::create([
            'name' => 'Miguel Dela Cruz',
            'email' => 'miguel.delacruz@sunsetvillage.com',
            'password' => Hash::make('password'),
            'phone' => '+63-922-999-0000',
            'subdivision_ids' => json_encode([$sunsetId]),
            'gate_ids' => json_encode([
                DB::table('gates')->where('code', 'SUV-NORTH')->value('id')
            ]),
            'two_factor_enabled' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $guard3->assignRole('guard');

        // Requester (resident)
        $requester1 = User::create([
            'name' => 'Sofia Fernandez',
            'email' => 'sofia.fernandez@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '+63-923-111-2222',
            'subdivision_ids' => json_encode([$greenfieldId]),
            'gate_ids' => null,
            'two_factor_enabled' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $requester1->assignRole('requester');

        $requester2 = User::create([
            'name' => 'Luis Mendoza',
            'email' => 'luis.mendoza@yahoo.com',
            'password' => Hash::make('password'),
            'phone' => '+63-924-333-4444',
            'subdivision_ids' => json_encode([$sunsetId]),
            'gate_ids' => null,
            'two_factor_enabled' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $requester2->assignRole('requester');

        $this->command->info('Created 11 users with different roles successfully!');
        $this->command->info('Default password for all users: password');
    }
}
