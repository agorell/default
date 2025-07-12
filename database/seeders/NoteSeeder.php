<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use App\Models\HousingUnit;
use App\Models\Occupier;
use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@housingmanagement.com')->first();
        $managerUser = User::where('email', 'manager@housingmanagement.com')->first();
        
        $unit101 = HousingUnit::where('unit_number', 'A101')->first();
        $unit201 = HousingUnit::where('unit_number', 'B201')->first();
        $house001 = HousingUnit::where('unit_number', 'H001')->first();
        
        $occupier1 = Occupier::where('name', 'Sarah Johnson')->first();
        $occupier2 = Occupier::where('name', 'David Chen')->first();

        $notes = [
            [
                'title' => 'Maintenance Request - Leaky Faucet',
                'content' => 'Tenant reported a dripping faucet in the kitchen. Scheduled repair for next week.',
                'category' => 'maintenance',
                'priority' => 'medium',
                'user_id' => $managerUser->id,
                'housing_unit_id' => $unit101->id,
                'occupier_id' => $occupier1->id,
                'is_private' => false,
            ],
            [
                'title' => 'Lease Renewal Discussion',
                'content' => 'Tenant expressed interest in renewing lease for another 12 months. Discussed potential rent increase.',
                'category' => 'lease',
                'priority' => 'high',
                'user_id' => $adminUser->id,
                'housing_unit_id' => $unit201->id,
                'occupier_id' => $occupier2->id,
                'is_private' => false,
            ],
            [
                'title' => 'Property Inspection Completed',
                'content' => 'Annual property inspection completed. Unit is in good condition with minor wear noted.',
                'category' => 'inspection',
                'priority' => 'low',
                'user_id' => $managerUser->id,
                'housing_unit_id' => $house001->id,
                'is_private' => false,
            ],
            [
                'title' => 'Noise Complaint Follow-up',
                'content' => 'Addressed noise complaint from neighbor. Tenant was cooperative and agreed to keep music down after 10 PM.',
                'category' => 'complaint',
                'priority' => 'medium',
                'user_id' => $adminUser->id,
                'housing_unit_id' => $unit201->id,
                'occupier_id' => $occupier2->id,
                'is_private' => false,
            ],
            [
                'title' => 'Payment Arrangement',
                'content' => 'Tenant requested payment plan due to temporary financial hardship. Approved 3-month payment plan.',
                'category' => 'payment',
                'priority' => 'high',
                'user_id' => $adminUser->id,
                'housing_unit_id' => $unit101->id,
                'occupier_id' => $occupier1->id,
                'is_private' => true,
            ],
            [
                'title' => 'General Property Update',
                'content' => 'Exterior painting scheduled for next month. All tenants will be notified of temporary parking restrictions.',
                'category' => 'general',
                'priority' => 'medium',
                'user_id' => $managerUser->id,
                'is_private' => false,
            ],
            [
                'title' => 'Emergency Contact Update',
                'content' => 'Tenant provided updated emergency contact information.',
                'category' => 'general',
                'priority' => 'low',
                'user_id' => $managerUser->id,
                'housing_unit_id' => $house001->id,
                'is_private' => false,
            ],
            [
                'title' => 'Maintenance Schedule',
                'content' => 'HVAC system maintenance scheduled for all units next month.',
                'category' => 'maintenance',
                'priority' => 'medium',
                'user_id' => $adminUser->id,
                'is_private' => false,
            ],
        ];

        foreach ($notes as $note) {
            Note::create($note);
        }
    }
}