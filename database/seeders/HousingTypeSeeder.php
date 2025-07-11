<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HousingType;

class HousingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $housingTypes = [
            [
                'name' => 'Apartment',
                'description' => 'Multi-unit residential building with individual apartments',
                'is_active' => true,
            ],
            [
                'name' => 'House',
                'description' => 'Single-family detached house',
                'is_active' => true,
            ],
            [
                'name' => 'Townhouse',
                'description' => 'Multi-story house sharing one or more walls with adjacent units',
                'is_active' => true,
            ],
            [
                'name' => 'Studio',
                'description' => 'Single room living space with combined living/sleeping area',
                'is_active' => true,
            ],
            [
                'name' => 'Duplex',
                'description' => 'Two-unit residential building with separate entrances',
                'is_active' => true,
            ],
            [
                'name' => 'Room',
                'description' => 'Individual room in a shared housing arrangement',
                'is_active' => true,
            ],
            [
                'name' => 'Condominium',
                'description' => 'Individually owned unit in a multi-unit complex',
                'is_active' => true,
            ],
        ];

        foreach ($housingTypes as $type) {
            HousingType::create($type);
        }
    }
}