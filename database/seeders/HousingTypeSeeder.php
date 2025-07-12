<?php

namespace Database\Seeders;

use App\Models\HousingType;
use Illuminate\Database\Seeder;

class HousingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Apartment',
                'description' => 'Multi-unit residential building with shared common areas',
                'is_active' => true,
            ],
            [
                'name' => 'House',
                'description' => 'Single-family detached residential dwelling',
                'is_active' => true,
            ],
            [
                'name' => 'Townhouse',
                'description' => 'Multi-story attached residential unit',
                'is_active' => true,
            ],
            [
                'name' => 'Condo',
                'description' => 'Individually owned residential unit in a building',
                'is_active' => true,
            ],
            [
                'name' => 'Studio',
                'description' => 'Single-room living space with kitchenette',
                'is_active' => true,
            ],
            [
                'name' => 'Room',
                'description' => 'Single room in a shared living arrangement',
                'is_active' => true,
            ],
            [
                'name' => 'Duplex',
                'description' => 'Two-unit residential building',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            HousingType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}