<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HousingUnit;
use App\Models\HousingType;

class HousingUnitSeeder extends Seeder
{
    public function run(): void
    {
        $apartmentType = HousingType::where('name', 'Apartment')->first();
        $houseType = HousingType::where('name', 'House')->first();
        $townhouseType = HousingType::where('name', 'Townhouse')->first();
        $studioType = HousingType::where('name', 'Studio')->first();
        $duplexType = HousingType::where('name', 'Duplex')->first();
        $roomType = HousingType::where('name', 'Room')->first();
        
        $housingUnits = [
            // Apartments
            [
                'unit_number' => '101',
                'housing_type_id' => $apartmentType->id,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'square_footage' => 650.00,
                'parking_spaces' => 1,
                'rental_rate' => 1200.00,
                'is_occupied' => true,
                'condition_grade' => 'A',
                'property_address' => '123 Main St, Springfield, IL 62701',
                'description' => 'Modern one-bedroom apartment with updated kitchen and bathroom',
                'is_active' => true,
            ],
            [
                'unit_number' => '102',
                'housing_type_id' => $apartmentType->id,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'square_footage' => 650.00,
                'parking_spaces' => 1,
                'rental_rate' => 1200.00,
                'is_occupied' => false,
                'condition_grade' => 'A',
                'property_address' => '123 Main St, Springfield, IL 62701',
                'description' => 'Modern one-bedroom apartment with updated kitchen and bathroom',
                'is_active' => true,
            ],
            [
                'unit_number' => '201',
                'housing_type_id' => $apartmentType->id,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_footage' => 950.00,
                'parking_spaces' => 2,
                'rental_rate' => 1650.00,
                'is_occupied' => true,
                'condition_grade' => 'B',
                'property_address' => '123 Main St, Springfield, IL 62701',
                'description' => 'Spacious two-bedroom apartment with balcony',
                'is_active' => true,
            ],
            [
                'unit_number' => '202',
                'housing_type_id' => $apartmentType->id,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_footage' => 950.00,
                'parking_spaces' => 2,
                'rental_rate' => 1650.00,
                'is_occupied' => false,
                'condition_grade' => 'B',
                'property_address' => '123 Main St, Springfield, IL 62701',
                'description' => 'Spacious two-bedroom apartment with balcony',
                'is_active' => true,
            ],
            
            // Houses
            [
                'unit_number' => '1',
                'housing_type_id' => $houseType->id,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'square_footage' => 1450.00,
                'parking_spaces' => 2,
                'rental_rate' => 2200.00,
                'is_occupied' => true,
                'condition_grade' => 'A',
                'property_address' => '456 Oak Ave, Springfield, IL 62702',
                'description' => 'Single-family home with large backyard and garage',
                'is_active' => true,
            ],
            [
                'unit_number' => '1',
                'housing_type_id' => $houseType->id,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'square_footage' => 1850.00,
                'parking_spaces' => 2,
                'rental_rate' => 2800.00,
                'is_occupied' => false,
                'condition_grade' => 'B',
                'property_address' => '789 Pine St, Springfield, IL 62703',
                'description' => 'Two-story family home with finished basement',
                'is_active' => true,
            ],
            
            // Townhouses
            [
                'unit_number' => 'A',
                'housing_type_id' => $townhouseType->id,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_footage' => 1100.00,
                'parking_spaces' => 1,
                'rental_rate' => 1800.00,
                'is_occupied' => true,
                'condition_grade' => 'A',
                'property_address' => '321 Elm Dr, Springfield, IL 62704',
                'description' => 'Modern townhouse with attached garage',
                'is_active' => true,
            ],
            [
                'unit_number' => 'B',
                'housing_type_id' => $townhouseType->id,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_footage' => 1100.00,
                'parking_spaces' => 1,
                'rental_rate' => 1800.00,
                'is_occupied' => false,
                'condition_grade' => 'A',
                'property_address' => '321 Elm Dr, Springfield, IL 62704',
                'description' => 'Modern townhouse with attached garage',
                'is_active' => true,
            ],
            
            // Studios
            [
                'unit_number' => 'S1',
                'housing_type_id' => $studioType->id,
                'bedrooms' => 0,
                'bathrooms' => 1,
                'square_footage' => 425.00,
                'parking_spaces' => 0,
                'rental_rate' => 900.00,
                'is_occupied' => true,
                'condition_grade' => 'B',
                'property_address' => '654 Maple St, Springfield, IL 62705',
                'description' => 'Cozy studio apartment near downtown',
                'is_active' => true,
            ],
            [
                'unit_number' => 'S2',
                'housing_type_id' => $studioType->id,
                'bedrooms' => 0,
                'bathrooms' => 1,
                'square_footage' => 425.00,
                'parking_spaces' => 0,
                'rental_rate' => 900.00,
                'is_occupied' => false,
                'condition_grade' => 'B',
                'property_address' => '654 Maple St, Springfield, IL 62705',
                'description' => 'Cozy studio apartment near downtown',
                'is_active' => true,
            ],
            
            // Duplex
            [
                'unit_number' => 'Upper',
                'housing_type_id' => $duplexType->id,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'square_footage' => 850.00,
                'parking_spaces' => 1,
                'rental_rate' => 1400.00,
                'is_occupied' => true,
                'condition_grade' => 'C',
                'property_address' => '987 Cedar Ln, Springfield, IL 62706',
                'description' => 'Upper level of duplex with separate entrance',
                'is_active' => true,
            ],
            [
                'unit_number' => 'Lower',
                'housing_type_id' => $duplexType->id,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'square_footage' => 850.00,
                'parking_spaces' => 1,
                'rental_rate' => 1400.00,
                'is_occupied' => false,
                'condition_grade' => 'C',
                'property_address' => '987 Cedar Ln, Springfield, IL 62706',
                'description' => 'Lower level of duplex with separate entrance',
                'is_active' => true,
            ],
            
            // Rooms
            [
                'unit_number' => 'Room1',
                'housing_type_id' => $roomType->id,
                'bedrooms' => 1,
                'bathrooms' => 0,
                'square_footage' => 150.00,
                'parking_spaces' => 0,
                'rental_rate' => 600.00,
                'is_occupied' => true,
                'condition_grade' => 'B',
                'property_address' => '147 Birch St, Springfield, IL 62707',
                'description' => 'Private room in shared house with common areas',
                'is_active' => true,
            ],
            [
                'unit_number' => 'Room2',
                'housing_type_id' => $roomType->id,
                'bedrooms' => 1,
                'bathrooms' => 0,
                'square_footage' => 150.00,
                'parking_spaces' => 0,
                'rental_rate' => 600.00,
                'is_occupied' => false,
                'condition_grade' => 'B',
                'property_address' => '147 Birch St, Springfield, IL 62707',
                'description' => 'Private room in shared house with common areas',
                'is_active' => true,
            ],
        ];

        foreach ($housingUnits as $unit) {
            HousingUnit::create($unit);
        }
    }
}