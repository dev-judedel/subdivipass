<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubdivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subdivisions = [
            [
                'name' => 'Greenfield Heights',
                'code' => 'GFH001',
                'address' => '123 Greenfield Avenue, Metro Manila',
                'contact_person' => 'Admin Office',
                'contact_phone' => '+63-912-345-6789',
                'contact_email' => 'admin@greenfieldheights.com',
                'logo_path' => null,
                'settings' => json_encode([
                    'require_approval' => true,
                    'auto_approve_visitors' => false,
                    'max_pass_duration_days' => 30,
                    'allow_recurring_passes' => true,
                    'notification_email' => 'security@greenfieldheights.com',
                    'working_hours' => [
                        'start' => '06:00',
                        'end' => '22:00',
                    ],
                ]),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sunset Village',
                'code' => 'SUV001',
                'address' => '456 Sunset Boulevard, Quezon City',
                'contact_person' => 'Management Office',
                'contact_phone' => '+63-917-876-5432',
                'contact_email' => 'info@sunsetvillage.com',
                'logo_path' => null,
                'settings' => json_encode([
                    'require_approval' => false,
                    'auto_approve_visitors' => true,
                    'max_pass_duration_days' => 7,
                    'allow_recurring_passes' => false,
                    'notification_email' => 'guardhouse@sunsetvillage.com',
                    'working_hours' => [
                        'start' => '00:00',
                        'end' => '23:59',
                    ],
                ]),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lakeside Residences',
                'code' => 'LSR001',
                'address' => '789 Lakeside Drive, Pasig City',
                'contact_person' => 'Property Manager',
                'contact_phone' => '+63-920-111-2222',
                'contact_email' => 'management@lakesideresidences.com',
                'logo_path' => null,
                'settings' => json_encode([
                    'require_approval' => true,
                    'auto_approve_visitors' => false,
                    'max_pass_duration_days' => 14,
                    'allow_recurring_passes' => true,
                    'notification_email' => 'office@lakesideresidences.com',
                    'working_hours' => [
                        'start' => '07:00',
                        'end' => '21:00',
                    ],
                ]),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('subdivisions')->insert($subdivisions);

        $this->command->info('Created ' . count($subdivisions) . ' subdivisions successfully!');
    }
}
