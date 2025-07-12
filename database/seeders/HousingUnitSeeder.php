<?php

namespace Database\Seeders;

use App\Models\HousingUnit;
use App\Models\HousingType;
use Illuminate\Database\Seeder;

class HousingUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apartmentType = HousingType::where('name', 'Apartment')->first();
        $houseType = HousingType::where('name', 'House')->first();
        $townhouseType = HousingType::where('name', 'Townhouse')->first();
        $studioType = HousingType::where('name', 'Studio')->first();
        $roomType = HousingType::where('name', 'Room')->first();

        $units = [
            [
                'unit_number' => 'A101',
                'housing_type_id' => $apartmentType->id,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'square_footage' => 650.00,
                'parking_spaces' => 1,
                'rental_rate' => 850.00,
                'is_occupied' => true,
                'condition_grade' => 'B',
                'property_address' => '123 Maple Street, Unit A101, Springfield, SP 12345',
                'description' => 'Cozy 1-bedroom apartment with balcony',
            ],
            [
                'unit_number' => 'A102',
                'housing_type_id' => $apartmentType->id,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'square_footage' => 850.00,
                'parking_spaces' => 1,
                'rental_rate' => 1100.00,
                'is_occupied' => false,
                'condition_grade' => 'A',
                'property_address' => '123 Maple Street, Unit A102, Springfield, SP 12345',
                'description' => 'Spacious 2-bedroom apartment with updated kitchen',
            ],
            [
                'unit_number' => 'B201',
                'housing_type_id' => $apartmentType->id,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_footage' => 950.00,
                'parking_spaces' => 2,
                'rental_rate' => 1350.00,
                'is_occupied' => true,
                'condition_grade' => 'B',
                'property_address' => '123 Maple Street, Unit B201, Springfield, SP 12345',
                'description' => '2-bedroom, 2-bathroom apartment with washer/dryer',
            ],
            [
                'unit_number' => 'H001',
                'housing_type_id' => $houseType->id,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'square_footage' => 1200.00,
                'parking_spaces' => 2,
                'rental_rate' => 1650.00,
                'is_occupied' => true,
                'condition_grade' => 'A',
                'property_address' => '456 Oak Avenue, Springfield, SP 12345',
                'description' => 'Single-family house with yard and garage',
            ],
            [
                'unit_number' => 'H002',
                'housing_type_id' => $houseType->id,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'square_footage' => 1800.00,
                'parking_spaces' => 3,
                'rental_rate' => 2100.00,
                'is_occupied' => false,
                'condition_grade' => 'B',
                'property_address' => '789 Pine Road, Springfield, SP 12345',
                'description' => 'Large family house with finished basement',
            ],
            [
                'unit_number' => 'T301',
                'housing_type_id' => $townhouseType->id,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_footage' => 1100.00,
                'parking_spaces' => 2,
                'rental_rate' => 1450.00,
                'is_occupied' => true,
                'condition_grade' => 'B',
                'property_address' => '321 Elm Street, Unit T301, Springfield, SP 12345',
                'description' => 'Modern townhouse with private entrance',
            ],
            [
                'unit_number' => 'S101',
                'housing_type_id' => $studioType->id,
                'bedrooms' => 0,
                'bathrooms' => 1,
                'square_footage' => 400.00,
                'parking_spaces' => 1,
                'rental_rate' => 650.00,
                'is_occupied' => false,
                'condition_grade' => 'C',
                'property_address' => '654 Cedar Lane, Unit S101, Springfield, SP 12345',
                'description' => 'Compact studio with kitchenette',
            ],
            [
                'unit_number' => 'R201',
                'housing_type_id' => $roomType->id,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'square_footage' => 300.00,
                'parking_spaces' => 0,
                'rental_rate' => 450.00,
                'is_occupied' => true,
                'condition_grade' => 'B',
                'property_address' => '987 Birch Drive, Room R201, Springfield, SP 12345',
                'description' => 'Private room with shared common areas',
            ],
            [
                'unit_number' => 'A201',
                'housing_type_id' => $apartmentType->id,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'square_footage' => 700.00,
                'parking_spaces' => 1,
                'rental_rate' => 900.00,
                'is_occupied' => false,
                'condition_grade' => 'A',
                'property_address' => '123 Maple Street, Unit A201, Springfield, SP 12345',
                'description' => 'Recently renovated 1-bedroom apartment',
            ],
            [
                'unit_number' => 'B101',
                'housing_type_id' => $apartmentType->id,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'square_footage' => 1050.00,
                'parking_spaces' => 2,
                'rental_rate' => 1500.00,
                'is_occupied' => true,
                'condition_grade' => 'B',
                'property_address' => '123 Maple Street, Unit B101, Springfield, SP 12345',
                'description' => 'Spacious 3-bedroom apartment',
            ],
        ];

        foreach ($units as $unit) {
            HousingUnit::firstOrCreate(['unit_number' => $unit['unit_number']], $unit);
        }
    }
}