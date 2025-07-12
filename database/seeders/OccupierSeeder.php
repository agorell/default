<?php

namespace Database\Seeders;

use App\Models\Occupier;
use App\Models\HousingUnit;
use Illuminate\Database\Seeder;

class OccupierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $occupiedUnits = HousingUnit::where('is_occupied', true)->get();

        $occupiers = [
            [
                'housing_unit_id' => $occupiedUnits->where('unit_number', 'A101')->first()->id,
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@email.com',
                'phone' => '555-1001',
                'emergency_contact_name' => 'Michael Johnson',
                'emergency_contact_phone' => '555-1002',
                'occupancy_start_date' => '2023-06-01',
                'lease_terms' => '12-month lease, renewable',
                'is_current' => true,
                'monthly_rent' => 850.00,
                'security_deposit' => 850.00,
                'notes' => 'Quiet tenant, always pays on time',
            ],
            [
                'housing_unit_id' => $occupiedUnits->where('unit_number', 'B201')->first()->id,
                'name' => 'David Chen',
                'email' => 'david.chen@email.com',
                'phone' => '555-1003',
                'emergency_contact_name' => 'Lisa Chen',
                'emergency_contact_phone' => '555-1004',
                'occupancy_start_date' => '2023-03-15',
                'lease_terms' => '24-month lease',
                'is_current' => true,
                'monthly_rent' => 1350.00,
                'security_deposit' => 1350.00,
                'notes' => 'Works from home, has requested better internet',
            ],
            [
                'housing_unit_id' => $occupiedUnits->where('unit_number', 'H001')->first()->id,
                'name' => 'Emily Rodriguez',
                'email' => 'emily.rodriguez@email.com',
                'phone' => '555-1005',
                'emergency_contact_name' => 'Carlos Rodriguez',
                'emergency_contact_phone' => '555-1006',
                'occupancy_start_date' => '2022-12-01',
                'lease_terms' => '12-month lease, month-to-month after',
                'is_current' => true,
                'monthly_rent' => 1650.00,
                'security_deposit' => 1650.00,
                'notes' => 'Family with two children, excellent tenants',
            ],
            [
                'housing_unit_id' => $occupiedUnits->where('unit_number', 'T301')->first()->id,
                'name' => 'James Wilson',
                'email' => 'james.wilson@email.com',
                'phone' => '555-1007',
                'emergency_contact_name' => 'Mary Wilson',
                'emergency_contact_phone' => '555-1008',
                'occupancy_start_date' => '2023-09-01',
                'lease_terms' => '18-month lease',
                'is_current' => true,
                'monthly_rent' => 1450.00,
                'security_deposit' => 1450.00,
                'notes' => 'Recently moved in, settling in well',
            ],
            [
                'housing_unit_id' => $occupiedUnits->where('unit_number', 'R201')->first()->id,
                'name' => 'Anna Martinez',
                'email' => 'anna.martinez@email.com',
                'phone' => '555-1009',
                'emergency_contact_name' => 'Roberto Martinez',
                'emergency_contact_phone' => '555-1010',
                'occupancy_start_date' => '2023-07-15',
                'lease_terms' => '6-month lease, renewable',
                'is_current' => true,
                'monthly_rent' => 450.00,
                'security_deposit' => 450.00,
                'notes' => 'Student, responsible and quiet',
            ],
            [
                'housing_unit_id' => $occupiedUnits->where('unit_number', 'B101')->first()->id,
                'name' => 'Robert Thompson',
                'email' => 'robert.thompson@email.com',
                'phone' => '555-1011',
                'emergency_contact_name' => 'Susan Thompson',
                'emergency_contact_phone' => '555-1012',
                'occupancy_start_date' => '2023-04-01',
                'lease_terms' => '12-month lease',
                'is_current' => true,
                'monthly_rent' => 1500.00,
                'security_deposit' => 1500.00,
                'notes' => 'Family with teenage children, very responsible',
            ],
        ];

        foreach ($occupiers as $occupier) {
            Occupier::firstOrCreate(['housing_unit_id' => $occupier['housing_unit_id']], $occupier);
        }
    }
}