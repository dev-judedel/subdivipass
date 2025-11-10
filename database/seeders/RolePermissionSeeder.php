<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions grouped by module
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage user roles',

            // Subdivision Management
            'view subdivisions',
            'create subdivisions',
            'edit subdivisions',
            'delete subdivisions',
            'manage subdivision settings',

            // Gate Management
            'view gates',
            'create gates',
            'edit gates',
            'delete gates',

            // Pass Type Management
            'view pass types',
            'create pass types',
            'edit pass types',
            'delete pass types',

            // Pass Management
            'view passes',
            'create passes',
            'edit passes',
            'delete passes',
            'approve passes',
            'reject passes',
            'revoke passes',
            'extend passes',
            'view own passes',

            // Scanning Operations
            'scan passes',
            'manual pin entry',
            'view scan history',
            'report issues',

            // Reporting & Analytics
            'view reports',
            'export reports',
            'view analytics',
            'view audit logs',

            // Settings
            'manage system settings',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // 1. Super Admin - Full system access
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. Admin/Property Manager - Manage assigned subdivisions
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'manage user roles',
            'view subdivisions',
            'edit subdivisions',
            'manage subdivision settings',
            'view gates',
            'create gates',
            'edit gates',
            'delete gates',
            'view pass types',
            'create pass types',
            'edit pass types',
            'delete pass types',
            'view passes',
            'create passes',
            'edit passes',
            'delete passes',
            'approve passes',
            'reject passes',
            'revoke passes',
            'extend passes',
            'view reports',
            'export reports',
            'view analytics',
            'view audit logs',
        ]);

        // 3. Employee - Create passes, basic reporting
        $employee = Role::create(['name' => 'employee']);
        $employee->givePermissionTo([
            'view passes',
            'create passes',
            'edit passes',
            'view pass types',
            'view subdivisions',
            'view gates',
            'view reports',
            'export reports',
        ]);

        // 4. Guard - Scan/validate, report issues, manual entry
        $guard = Role::create(['name' => 'guard']);
        $guard->givePermissionTo([
            'scan passes',
            'manual pin entry',
            'view scan history',
            'report issues',
            'view passes',
        ]);

        // 5. Requester - Request passes, view own history
        $requester = Role::create(['name' => 'requester']);
        $requester->givePermissionTo([
            'create passes',
            'view own passes',
        ]);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Created ' . count($permissions) . ' permissions');
        $this->command->info('Created 5 roles: super-admin, admin, employee, guard, requester');
    }
}
