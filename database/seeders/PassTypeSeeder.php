<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PassTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get subdivision IDs
        $subdivisions = DB::table('subdivisions')->get();

        $passTypes = [];

        // Create pass types for each subdivision
        $sortOrder = 0;
        foreach ($subdivisions as $subdivision) {
            $passTypes = array_merge($passTypes, [
                // Visitor Pass
                [
                    'subdivision_id' => $subdivision->id,
                    'name' => 'Visitor Pass',
                    'slug' => 'visitor-' . $subdivision->id,
                    'description' => 'For personal guests and family visitors',
                    'requires_approval' => false,
                    'default_validity_hours' => 12,
                    'max_validity_hours' => 24,
                    'color' => '#3B82F6', // Blue
                    'icon' => 'user-group',
                    'sort_order' => ++$sortOrder,
                    'config' => json_encode([
                        'required_fields' => [
                            'visitor_name',
                            'visitor_contact',
                            'purpose',
                            'resident_name',
                        ],
                        'optional_fields' => [
                            'vehicle_plate',
                            'vehicle_model',
                            'companion_count',
                        ],
                        'validation_rules' => [
                            'max_companions' => 5,
                            'advance_booking_hours' => 0,
                        ],
                    ]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Job Order Pass
                [
                    'subdivision_id' => $subdivision->id,
                    'name' => 'Job Order Pass',
                    'slug' => 'job-order-' . $subdivision->id,
                    'description' => 'For service providers, contractors, and maintenance workers',
                    'requires_approval' => true,
                    'default_validity_hours' => 168, // 7 days
                    'max_validity_hours' => 720, // 30 days
                    'color' => '#F59E0B', // Orange
                    'icon' => 'wrench',
                    'sort_order' => ++$sortOrder,
                    'config' => json_encode([
                        'required_fields' => [
                            'company_name',
                            'worker_name',
                            'worker_contact',
                            'purpose',
                            'resident_name',
                        ],
                        'optional_fields' => [
                            'vehicle_plate',
                            'vehicle_model',
                            'company_contact',
                            'worker_count',
                            'tools_equipment',
                        ],
                        'validation_rules' => [
                            'max_workers' => 10,
                            'advance_booking_hours' => 24,
                            'require_work_order' => true,
                        ],
                    ]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Delivery Pass
                [
                    'subdivision_id' => $subdivision->id,
                    'name' => 'Delivery Pass',
                    'slug' => 'delivery-' . $subdivision->id,
                    'description' => 'For couriers, food delivery, and package deliveries',
                    'requires_approval' => false,
                    'default_validity_hours' => 2,
                    'max_validity_hours' => 4,
                    'color' => '#10B981', // Green
                    'icon' => 'truck',
                    'sort_order' => ++$sortOrder,
                    'config' => json_encode([
                        'required_fields' => [
                            'courier_name',
                            'company_name',
                            'recipient_name',
                            'purpose',
                        ],
                        'optional_fields' => [
                            'vehicle_plate',
                            'vehicle_model',
                            'tracking_number',
                            'package_count',
                        ],
                        'validation_rules' => [
                            'advance_booking_hours' => 0,
                            'single_use' => true,
                        ],
                    ]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Event Pass
                [
                    'subdivision_id' => $subdivision->id,
                    'name' => 'Event Pass',
                    'slug' => 'event-' . $subdivision->id,
                    'description' => 'For special events, gatherings, and celebrations',
                    'requires_approval' => true,
                    'default_validity_hours' => 48, // 2 days
                    'max_validity_hours' => 72, // 3 days
                    'color' => '#8B5CF6', // Purple
                    'icon' => 'calendar',
                    'sort_order' => ++$sortOrder,
                    'config' => json_encode([
                        'required_fields' => [
                            'event_name',
                            'organizer_name',
                            'organizer_contact',
                            'purpose',
                            'expected_attendees',
                        ],
                        'optional_fields' => [
                            'event_description',
                            'caterer_name',
                            'vehicle_count',
                            'setup_time',
                        ],
                        'validation_rules' => [
                            'max_attendees' => 100,
                            'advance_booking_hours' => 72,
                            'require_deposit' => false,
                        ],
                    ]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        DB::table('pass_types')->insert($passTypes);

        $this->command->info('Created ' . count($passTypes) . ' pass types for ' . $subdivisions->count() . ' subdivisions!');
    }
}
