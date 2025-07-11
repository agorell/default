<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Occupier;
use App\Models\HousingUnit;
use Carbon\Carbon;

class OccupierSeeder extends Seeder
{
    public function run(): void
    {
        $occupiedUnits = HousingUnit::where('is_occupied', true)->get();
        
        $occupiers = [
            [
                'housing_unit_id' => $occupiedUnits->where('unit_number', '101')->first()->id,
                'name' => 'Alice Johnson',
                'email' => 'alice.johnson@email.com',
                'phone' => '555-1001',
                'emergency_contact_name' => 'Bob Johnson',
                'emergency_contact_phone' => '555-1002',
                'move_in_date' => Carbon::now()->subMonths(8),
                'move_out_date' => null,
                'lease_start_date' => Carbon::now()->subMonths(8),
                'lease_end_date' => Carbon::now()->addMonths(4),
                'rental_amount' => 1200.00,
                'deposit_amount' => 1200.00,
                'is_active' => true,
            ],
            [
                'housing_unit_id' => $occupiedUnits->where('unit_number', '201')->first()->id,
                'name' => 'Michael Chen',
                'email' => 'michael.chen@email.com',
                'phone' => '555-1003',
                'emergency_contact_name' => 'Lisa Chen',
                'emergency_contact_phone' => '555-1004',
                'move_in_date' => Carbon::now()->subMonths(6),
                'move_out_date' => null,
                'lease_start_date' => Carbon::now()->subMonths(6),
                'lease_end_date' => Carbon::now()->addMonths(6),
                'rental_amount' => 1650.00,
                'deposit_amount' => 1650.00,
                'is_active' => true,
            ],
            [
                'housing_unit_id' => $occupiedUnits->where('property_address', '456 Oak Ave, Springfield, IL 62702')->first()->id,
                'name' => 'Sarah Williams',
                'email' => 'sarah.williams@email.com',
                'phone' => '555-1005',
                'emergency_contact_name' => 'David Williams',
                'emergency_contact_phone' => '555-1006',
                'move_in_date' => Carbon::now()->subMonths(14),
                'move_out_date' => null,
                'lease_start_date' => Carbon::now()->subMonths(14),
                'lease_end_date' => Carbon::now()->addMonths(10),
                'rental_amount' => 2200.00,
                'deposit_amount' => 2200.00,
                'is_active' => true,
            ],
            [
                'housing_unit_id' => $occupiedUnits->where('unit_number', 'A')->first()->id,
                'name' => 'James Rodriguez',
                'email' => 'james.rodriguez@email.com',
                'phone' => '555-1007',
                'emergency_contact_name' => 'Maria Rodriguez',
                'emergency_contact_phone' => '555-1008',
                'move_in_date' => Carbon::now()->subMonths(3),
                'move_out_date' => null,
                'lease_start_date' => Carbon::now()->subMonths(3),
                'lease_end_date' => Carbon::now()->addMonths(9),
                'rental_amount' => 1800.00,
                'deposit_amount' => 1800.00,
                'is_active' => true,
            ],
            [
                'housing_unit_id' => $occupiedUnits->where('unit_number', 'S1')->first()->id,
                'name' => 'Emily Davis',
                'email' => 'emily.davis@email.com',
                'phone' => '555-1009',
                'emergency_contact_name' => 'Robert Davis',
                'emergency_contact_phone' => '555-1010',
                'move_in_date' => Carbon::now()->subMonths(5),
                'move_out_date' => null,
                'lease_start_date' => Carbon::now()->subMonths(5),
                'lease_end_date' => Carbon::now()->addMonths(7),
                'rental_amount' => 900.00,
                'deposit_amount' => 900.00,
                'is_active' => true,
            ],
            [
                'housing_unit_id' => $occupiedUnits->where('unit_number', 'Upper')->first()->id,
                'name' => 'Thomas Anderson',
                'email' => 'thomas.anderson@email.com',
                'phone' => '555-1011',
                'emergency_contact_name' => 'Patricia Anderson',
                'emergency_contact_phone' => '555-1012',
                'move_in_date' => Carbon::now()->subMonths(18),
                'move_out_date' => null,
                'lease_start_date' => Carbon::now()->subMonths(18),
                'lease_end_date' => Carbon::now()->addMonths(6),
                'rental_amount' => 1400.00,
                'deposit_amount' => 1400.00,
                'is_active' => true,
            ],
            [
                'housing_unit_id' => $occupiedUnits->where('unit_number', 'Room1')->first()->id,
                'name' => 'Jennifer Martinez',
                'email' => 'jennifer.martinez@email.com',
                'phone' => '555-1013',
                'emergency_contact_name' => 'Carlos Martinez',
                'emergency_contact_phone' => '555-1014',
                'move_in_date' => Carbon::now()->subMonths(2),
                'move_out_date' => null,
                'lease_start_date' => Carbon::now()->subMonths(2),
                'lease_end_date' => Carbon::now()->addMonths(10),
                'rental_amount' => 600.00,
                'deposit_amount' => 600.00,
                'is_active' => true,
            ],
        ];

        foreach ($occupiers as $occupier) {
            Occupier::create($occupier);
        }
    }
}