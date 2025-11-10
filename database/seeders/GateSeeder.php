<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get subdivision IDs
        $greenfieldId = DB::table('subdivisions')->where('code', 'GFH001')->value('id');
        $sunsetId = DB::table('subdivisions')->where('code', 'SUV001')->value('id');
        $lakesideId = DB::table('subdivisions')->where('code', 'LSR001')->value('id');

        $gates = [
            // Greenfield Heights gates
            [
                'subdivision_id' => $greenfieldId,
                'name' => 'Main Gate',
                'code' => 'GFH-MAIN',
                'type' => 'both',
                'location' => 'Main entrance on Greenfield Avenue',
                'notes' => 'Primary entrance for residents and visitors',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subdivision_id' => $greenfieldId,
                'name' => 'Service Gate',
                'code' => 'GFH-SERVICE',
                'type' => 'entry',
                'location' => 'Back entrance near service road',
                'notes' => 'For deliveries and service providers',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subdivision_id' => $greenfieldId,
                'name' => 'Emergency Exit',
                'code' => 'GFH-EMERGENCY',
                'type' => 'exit',
                'location' => 'East side emergency access',
                'notes' => 'Emergency vehicles only',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Sunset Village gates
            [
                'subdivision_id' => $sunsetId,
                'name' => 'North Gate',
                'code' => 'SUV-NORTH',
                'type' => 'both',
                'location' => 'North entrance on Sunset Boulevard',
                'notes' => 'Main entrance from Sunset Boulevard',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subdivision_id' => $sunsetId,
                'name' => 'South Gate',
                'code' => 'SUV-SOUTH',
                'type' => 'both',
                'location' => 'South entrance on Village Road',
                'notes' => 'Secondary entrance for residents',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Lakeside Residences gates
            [
                'subdivision_id' => $lakesideId,
                'name' => 'Tower A Gate',
                'code' => 'LSR-TOWER-A',
                'type' => 'entry',
                'location' => 'Tower A lobby entrance',
                'notes' => 'Main entrance for Tower A',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subdivision_id' => $lakesideId,
                'name' => 'Tower B Gate',
                'code' => 'LSR-TOWER-B',
                'type' => 'entry',
                'location' => 'Tower B lobby entrance',
                'notes' => 'Main entrance for Tower B',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subdivision_id' => $lakesideId,
                'name' => 'Parking Gate',
                'code' => 'LSR-PARKING',
                'type' => 'both',
                'location' => 'Underground parking entrance',
                'notes' => 'Parking and delivery access',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('gates')->insert($gates);

        $this->command->info('Created ' . count($gates) . ' gates successfully!');
    }
}
