#!/usr/bin/env node

const HousingUnit = require('../models/HousingUnit');
const Occupier = require('../models/Occupier');
const Note = require('../models/Note');

console.log('Housing Management System - Database Seeding');
console.log('=' .repeat(50));

try {
    // Sample housing units
    console.log('Creating sample housing units...');
    
    const sampleUnits = [
        {
            unit_number: 'A101',
            housing_type_id: 1, // Apartment
            bedrooms: 2,
            bathrooms: 1.5,
            square_footage: 850,
            rental_rate: 1200.00,
            condition_grade: 'Good',
            address: '123 Main St, Unit A101, Anytown, ST 12345',
            description: 'Spacious 2-bedroom apartment with modern amenities'
        },
        {
            unit_number: 'A102',
            housing_type_id: 1, // Apartment
            bedrooms: 1,
            bathrooms: 1,
            square_footage: 650,
            rental_rate: 950.00,
            condition_grade: 'Excellent',
            address: '123 Main St, Unit A102, Anytown, ST 12345',
            description: 'Cozy 1-bedroom apartment, recently renovated'
        },
        {
            unit_number: 'B201',
            housing_type_id: 2, // House
            bedrooms: 3,
            bathrooms: 2,
            square_footage: 1200,
            rental_rate: 1800.00,
            condition_grade: 'Good',
            address: '456 Oak Ave, Anytown, ST 12345',
            description: 'Single-family house with yard and garage'
        },
        {
            unit_number: 'C301',
            housing_type_id: 4, // Studio
            bedrooms: 0,
            bathrooms: 1,
            square_footage: 400,
            rental_rate: 750.00,
            condition_grade: 'Fair',
            address: '789 Pine St, Unit C301, Anytown, ST 12345',
            description: 'Compact studio apartment, perfect for students'
        },
        {
            unit_number: 'D401',
            housing_type_id: 5, // Townhouse
            bedrooms: 3,
            bathrooms: 2.5,
            square_footage: 1400,
            rental_rate: 2000.00,
            condition_grade: 'Excellent',
            address: '321 Elm Dr, Anytown, ST 12345',
            description: 'Modern townhouse with attached garage'
        }
    ];
    
    const createdUnits = [];
    for (const unitData of sampleUnits) {
        const unit = HousingUnit.create(unitData);
        createdUnits.push(unit);
        console.log(`  ✅ Created unit: ${unit.unit_number}`);
    }
    
    // Sample occupiers
    console.log('Creating sample occupiers...');
    
    const sampleOccupiers = [
        {
            housing_unit_id: createdUnits[0].id,
            first_name: 'John',
            last_name: 'Smith',
            phone: '555-0123',
            email: 'john.smith@email.com',
            occupancy_start_date: '2024-01-15',
            monthly_rent: 1200.00,
            emergency_contact_name: 'Jane Smith',
            emergency_contact_phone: '555-0124'
        },
        {
            housing_unit_id: createdUnits[2].id,
            first_name: 'Sarah',
            last_name: 'Johnson',
            phone: '555-0234',
            email: 'sarah.johnson@email.com',
            occupancy_start_date: '2024-03-01',
            monthly_rent: 1800.00,
            emergency_contact_name: 'Michael Johnson',
            emergency_contact_phone: '555-0235'
        },
        {
            housing_unit_id: createdUnits[4].id,
            first_name: 'Robert',
            last_name: 'Williams',
            phone: '555-0345',
            email: 'robert.williams@email.com',
            occupancy_start_date: '2024-02-01',
            monthly_rent: 2000.00,
            emergency_contact_name: 'Linda Williams',
            emergency_contact_phone: '555-0346'
        }
    ];
    
    const createdOccupiers = [];
    for (const occupierData of sampleOccupiers) {
        const occupier = Occupier.create(occupierData);
        createdOccupiers.push(occupier);
        console.log(`  ✅ Created occupier: ${occupier.first_name} ${occupier.last_name}`);
    }
    
    // Sample notes
    console.log('Creating sample notes...');
    
    const sampleNotes = [
        {
            title: 'Maintenance Request - Leaky Faucet',
            content: 'Tenant reported a leaky faucet in the kitchen. Scheduled plumber visit for tomorrow.',
            category: 'Maintenance',
            housing_unit_id: createdUnits[0].id,
            occupier_id: createdOccupiers[0].id,
            created_by: 1 // Admin user
        },
        {
            title: 'Rent Payment Received',
            content: 'Monthly rent payment received on time. Payment processed successfully.',
            category: 'Financial',
            housing_unit_id: createdUnits[2].id,
            occupier_id: createdOccupiers[1].id,
            created_by: 2 // Manager user
        },
        {
            title: 'Property Inspection',
            content: 'Annual property inspection completed. Overall condition is excellent.',
            category: 'Inspection',
            housing_unit_id: createdUnits[4].id,
            occupier_id: createdOccupiers[2].id,
            created_by: 1 // Admin user
        },
        {
            title: 'Lease Renewal Notice',
            content: 'Lease renewal notice sent to tenant. Current lease expires in 30 days.',
            category: 'Lease',
            housing_unit_id: createdUnits[0].id,
            occupier_id: createdOccupiers[0].id,
            created_by: 2 // Manager user
        },
        {
            title: 'Unit Vacancy - Ready for Showing',
            content: 'Unit has been cleaned and is ready for prospective tenant showings.',
            category: 'General',
            housing_unit_id: createdUnits[1].id,
            occupier_id: null,
            created_by: 1 // Admin user
        },
        {
            title: 'Emergency Contact Update',
            content: 'Tenant requested to update emergency contact information.',
            category: 'Administrative',
            housing_unit_id: createdUnits[2].id,
            occupier_id: createdOccupiers[1].id,
            created_by: 2 // Manager user
        }
    ];
    
    for (const noteData of sampleNotes) {
        const note = Note.create(noteData);
        console.log(`  ✅ Created note: ${note.title}`);
    }
    
    console.log('');
    console.log('✅ Database seeding completed successfully!');
    console.log('');
    console.log('Sample data created:');
    console.log(`- ${createdUnits.length} housing units`);
    console.log(`- ${createdOccupiers.length} occupiers`);
    console.log(`- ${sampleNotes.length} notes`);
    console.log('');
    console.log('You can now start the application with: npm start');
    
} catch (error) {
    console.error('❌ Database seeding failed:', error.message);
    console.error(error.stack);
    process.exit(1);
}