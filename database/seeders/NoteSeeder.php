<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Note;
use App\Models\User;
use App\Models\HousingUnit;
use App\Models\Occupier;
use Carbon\Carbon;

class NoteSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@housingmanagement.com')->first();
        $manager = User::where('email', 'manager@housingmanagement.com')->first();
        $viewer = User::where('email', 'viewer@housingmanagement.com')->first();
        
        $unit1 = HousingUnit::where('unit_number', '101')->first();
        $unit2 = HousingUnit::where('unit_number', '201')->first();
        $unit3 = HousingUnit::where('unit_number', '102')->first();
        
        $occupier1 = Occupier::where('name', 'Alice Johnson')->first();
        $occupier2 = Occupier::where('name', 'Michael Chen')->first();
        
        $notes = [
            [
                'user_id' => $admin->id,
                'housing_unit_id' => $unit1->id,
                'occupier_id' => $occupier1->id,
                'title' => 'Maintenance Request - Kitchen Faucet',
                'content' => 'Tenant reported that the kitchen faucet is dripping. Maintenance team scheduled for tomorrow at 10 AM.',
                'category' => 'maintenance',
                'priority' => 'medium',
                'is_private' => false,
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $manager->id,
                'housing_unit_id' => $unit1->id,
                'occupier_id' => $occupier1->id,
                'title' => 'Lease Renewal Discussion',
                'content' => 'Tenant expressed interest in renewing lease for another year. Current lease expires in 4 months. Consider offering 3% rent increase.',
                'category' => 'lease',
                'priority' => 'low',
                'is_private' => false,
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $admin->id,
                'housing_unit_id' => $unit2->id,
                'occupier_id' => $occupier2->id,
                'title' => 'Noise Complaint Resolution',
                'content' => 'Addressed noise complaint from neighboring unit. Tenant was cooperative and agreed to keep noise levels down after 10 PM.',
                'category' => 'complaint',
                'priority' => 'high',
                'is_private' => false,
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $manager->id,
                'housing_unit_id' => $unit2->id,
                'occupier_id' => null,
                'title' => 'Unit Inspection Completed',
                'content' => 'Annual unit inspection completed. Minor issues found: paint touch-ups needed in living room, replace air filter. Overall condition good.',
                'category' => 'inspection',
                'priority' => 'medium',
                'is_private' => false,
                'created_at' => Carbon::now()->subDays(7),
            ],
            [
                'user_id' => $viewer->id,
                'housing_unit_id' => $unit3->id,
                'occupier_id' => null,
                'title' => 'Unit Preparation for Showing',
                'content' => 'Unit 102 is ready for showing. Deep cleaning completed, all maintenance items addressed. Unit is in excellent condition.',
                'category' => 'general',
                'priority' => 'medium',
                'is_private' => false,
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $admin->id,
                'housing_unit_id' => null,
                'occupier_id' => null,
                'title' => 'Property Manager Meeting Notes',
                'content' => 'Monthly property manager meeting held. Discussed upcoming renovations, budget allocations, and new tenant screening procedures.',
                'category' => 'communication',
                'priority' => 'low',
                'is_private' => true,
                'created_at' => Carbon::now()->subDays(10),
            ],
            [
                'user_id' => $manager->id,
                'housing_unit_id' => $unit1->id,
                'occupier_id' => $occupier1->id,
                'title' => 'Late Payment Follow-up',
                'content' => 'Tenant was 3 days late with rent payment. Payment received with late fee. Reminded tenant about auto-pay option.',
                'category' => 'payment',
                'priority' => 'medium',
                'is_private' => false,
                'created_at' => Carbon::now()->subDays(15),
            ],
            [
                'user_id' => $admin->id,
                'housing_unit_id' => $unit2->id,
                'occupier_id' => $occupier2->id,
                'title' => 'Emergency Contact Update',
                'content' => 'Tenant requested to update emergency contact information. New contact: Lisa Chen, 555-1004. Updated in system.',
                'category' => 'general',
                'priority' => 'low',
                'is_private' => false,
                'created_at' => Carbon::now()->subDays(8),
            ],
            [
                'user_id' => $manager->id,
                'housing_unit_id' => null,
                'occupier_id' => null,
                'title' => 'System Maintenance Scheduled',
                'content' => 'Monthly system maintenance scheduled for this weekend. All units will have hot water temporarily interrupted on Sunday from 8 AM to 12 PM.',
                'category' => 'maintenance',
                'priority' => 'high',
                'is_private' => false,
                'created_at' => Carbon::now()->subDays(4),
            ],
            [
                'user_id' => $viewer->id,
                'housing_unit_id' => $unit3->id,
                'occupier_id' => null,
                'title' => 'Prospective Tenant Inquiry',
                'content' => 'Received inquiry about Unit 102 from prospective tenant. Showing scheduled for Friday at 2 PM. Tenant pre-qualified.',
                'category' => 'general',
                'priority' => 'medium',
                'is_private' => false,
                'created_at' => Carbon::now()->subHours(6),
            ],
        ];

        foreach ($notes as $note) {
            Note::create($note);
        }
    }
}