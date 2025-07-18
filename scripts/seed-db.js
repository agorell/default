#!/usr/bin/env node

const path = require('path');
const Database = require('../models/database');
const HousingUnit = require('../models/HousingUnit');
const Occupier = require('../models/Occupier');
const Note = require('../models/Note');
const User = require('../models/User');

console.log('🌱 Seeding Housing Management Database with sample data...');

try {
    // Initialize database connection
    Database.init();
    
    const db = Database.getConnection();
    
    // Get housing type IDs
    const housingTypes = db.prepare('SELECT id, name FROM housing_types').all();
    const typeMap = {};
    housingTypes.forEach(type => {
        typeMap[type.name] = type.id;
    });
    
    // Get user IDs
    const users = User.findAll();
    const adminUser = users.find(u => u.role_name === 'Admin');
    const managerUser = users.find(u => u.role_name === 'Manager');
    
    // Create sample housing units
    console.log('🏠 Creating sample housing units...');
    
    const sampleUnits = [
        {
            unit_number: 'A-101',
            housing_type_id: typeMap['Apartment'],
            bedrooms: 2,
            bathrooms: 1,
            square_footage: 850,
            rental_rate: 1200.00,
            condition_grade: 'Excellent',
            address: '123 Main Street, Apt A-101, City, State 12345',
            description: 'Modern 2-bedroom apartment with updated kitchen and bathroom'
        },
        {
            unit_number: 'A-102',
            housing_type_id: typeMap['Apartment'],
            bedrooms: 1,
            bathrooms: 1,
            square_footage: 650,
            rental_rate: 950.00,
            condition_grade: 'Good',
            address: '123 Main Street, Apt A-102, City, State 12345',
            description: 'Cozy 1-bedroom apartment with hardwood floors'
        },
        {
            unit_number: 'B-201',
            housing_type_id: typeMap['Apartment'],
            bedrooms: 3,
            bathrooms: 2,
            square_footage: 1200,
            rental_rate: 1500.00,
            condition_grade: 'Excellent',
            address: '123 Main Street, Apt B-201, City, State 12345',
            description: 'Spacious 3-bedroom apartment with balcony and parking'
        },
        {
            unit_number: 'TH-001',
            housing_type_id: typeMap['Townhouse'],
            bedrooms: 3,
            bathrooms: 2.5,
            square_footage: 1450,
            rental_rate: 1800.00,
            condition_grade: 'Good',
            address: '456 Oak Avenue, Townhouse 1, City, State 12345',
            description: 'Three-story townhouse with garage and small yard'
        },
        {
            unit_number: 'TH-002',
            housing_type_id: typeMap['Townhouse'],
            bedrooms: 2,
            bathrooms: 1.5,
            square_footage: 1100,
            rental_rate: 1400.00,
            condition_grade: 'Fair',
            address: '456 Oak Avenue, Townhouse 2, City, State 12345',
            description: 'Two-story townhouse with attached garage'
        },
        {
            unit_number: 'H-301',
            housing_type_id: typeMap['House'],
            bedrooms: 4,
            bathrooms: 3,
            square_footage: 2200,
            rental_rate: 2500.00,
            condition_grade: 'Excellent',
            address: '789 Pine Street, City, State 12345',
            description: 'Single-family house with large backyard and two-car garage'
        },
        {
            unit_number: 'S-101',
            housing_type_id: typeMap['Studio'],
            bedrooms: 0,
            bathrooms: 1,
            square_footage: 450,
            rental_rate: 750.00,
            condition_grade: 'Good',
            address: '321 Elm Street, Studio 101, City, State 12345',
            description: 'Efficient studio apartment with murphy bed and kitchenette'
        },
        {
            unit_number: 'R-205',
            housing_type_id: typeMap['Room'],
            bedrooms: 1,
            bathrooms: 1,
            square_footage: 300,
            rental_rate: 600.00,
            condition_grade: 'Fair',
            address: '654 Maple Drive, Room 205, City, State 12345',
            description: 'Private room in shared house with common kitchen and living area'
        },
        {
            unit_number: 'A-103',
            housing_type_id: typeMap['Apartment'],
            bedrooms: 2,
            bathrooms: 1,
            square_footage: 800,
            rental_rate: 1100.00,
            condition_grade: 'Good',
            address: '123 Main Street, Apt A-103, City, State 12345',
            description: 'Recently renovated apartment with in-unit laundry'
        },
        {
            unit_number: 'A-104',
            housing_type_id: typeMap['Apartment'],
            bedrooms: 1,
            bathrooms: 1,
            square_footage: 600,
            rental_rate: 900.00,
            condition_grade: 'Excellent',
            address: '123 Main Street, Apt A-104, City, State 12345',
            description: 'Ground floor apartment with patio access'
        }
    ];
    
    const createdUnits = [];
    sampleUnits.forEach(unitData => {
        const unit = HousingUnit.create(unitData);
        createdUnits.push(unit);
        console.log(`   ✓ Created unit: ${unit.unit_number}`);
    });
    
    // Create sample occupiers for some units
    console.log('👥 Creating sample occupiers...');
    
    const sampleOccupiers = [
        {
            housing_unit_id: createdUnits[0].id,
            first_name: 'John',
            last_name: 'Smith',
            phone: '555-123-4567',
            email: 'john.smith@email.com',
            occupancy_start_date: '2023-01-15',
            monthly_rent: 1200.00,
            emergency_contact_name: 'Sarah Smith',
            emergency_contact_phone: '555-987-6543'
        },
        {
            housing_unit_id: createdUnits[1].id,
            first_name: 'Maria',
            last_name: 'Garcia',
            phone: '555-234-5678',
            email: 'maria.garcia@email.com',
            occupancy_start_date: '2023-03-01',
            monthly_rent: 950.00,
            emergency_contact_name: 'Carlos Garcia',
            emergency_contact_phone: '555-876-5432'
        },
        {
            housing_unit_id: createdUnits[2].id,
            first_name: 'Robert',
            last_name: 'Johnson',
            phone: '555-345-6789',
            email: 'robert.johnson@email.com',
            occupancy_start_date: '2023-02-15',
            monthly_rent: 1500.00,
            emergency_contact_name: 'Lisa Johnson',
            emergency_contact_phone: '555-765-4321'
        },
        {
            housing_unit_id: createdUnits[3].id,
            first_name: 'Emily',
            last_name: 'Davis',
            phone: '555-456-7890',
            email: 'emily.davis@email.com',
            occupancy_start_date: '2023-04-01',
            monthly_rent: 1800.00,
            emergency_contact_name: 'Michael Davis',
            emergency_contact_phone: '555-654-3210'
        },
        {
            housing_unit_id: createdUnits[5].id,
            first_name: 'James',
            last_name: 'Wilson',
            phone: '555-567-8901',
            email: 'james.wilson@email.com',
            occupancy_start_date: '2023-05-15',
            monthly_rent: 2500.00,
            emergency_contact_name: 'Jennifer Wilson',
            emergency_contact_phone: '555-543-2109'
        },
        {
            housing_unit_id: createdUnits[6].id,
            first_name: 'Amanda',
            last_name: 'Brown',
            phone: '555-678-9012',
            email: 'amanda.brown@email.com',
            occupancy_start_date: '2023-06-01',
            monthly_rent: 750.00,
            emergency_contact_name: 'David Brown',
            emergency_contact_phone: '555-432-1098'
        }
    ];
    
    const createdOccupiers = [];
    sampleOccupiers.forEach(occupierData => {
        const occupier = Occupier.create(occupierData);
        createdOccupiers.push(occupier);
        console.log(`   ✓ Created occupier: ${occupier.getFullName()}`);
    });
    
    // Create sample notes
    console.log('📝 Creating sample notes...');
    
    const sampleNotes = [
        {
            title: 'Welcome Package Delivered',
            content: 'Delivered welcome package with keys, lease agreement, and building rules to new tenant.',
            category: 'General',
            housing_unit_id: createdUnits[0].id,
            occupier_id: createdOccupiers[0].id,
            created_by: adminUser.id
        },
        {
            title: 'AC Unit Maintenance',
            content: 'Scheduled maintenance for AC unit. Filter replaced and system cleaned. All working properly.',
            category: 'Maintenance',
            housing_unit_id: createdUnits[1].id,
            created_by: managerUser.id
        },
        {
            title: 'Rent Payment Received',
            content: 'Monthly rent payment received on time. Payment processed and recorded.',
            category: 'Financial',
            housing_unit_id: createdUnits[2].id,
            occupier_id: createdOccupiers[2].id,
            created_by: adminUser.id
        },
        {
            title: 'Noise Complaint Resolution',
            content: 'Addressed noise complaint from neighboring unit. Spoke with tenant and resolved issue amicably.',
            category: 'Complaint',
            housing_unit_id: createdUnits[3].id,
            occupier_id: createdOccupiers[3].id,
            created_by: managerUser.id
        },
        {
            title: 'Unit Inspection Completed',
            content: 'Quarterly inspection completed. Unit in excellent condition, no issues found.',
            category: 'Inspection',
            housing_unit_id: createdUnits[5].id,
            occupier_id: createdOccupiers[4].id,
            created_by: adminUser.id
        },
        {
            title: 'Plumbing Repair',
            content: 'Fixed leaky faucet in kitchen. Replaced worn gaskets and tested for proper operation.',
            category: 'Maintenance',
            housing_unit_id: createdUnits[4].id,
            created_by: managerUser.id
        },
        {
            title: 'Lease Renewal Discussion',
            content: 'Discussed lease renewal with tenant. They are interested in extending for another year.',
            category: 'General',
            housing_unit_id: createdUnits[6].id,
            occupier_id: createdOccupiers[5].id,
            created_by: adminUser.id
        },
        {
            title: 'Parking Space Assignment',
            content: 'Assigned parking space #15 to tenant. Updated parking records and provided new permit.',
            category: 'Administrative',
            housing_unit_id: createdUnits[0].id,
            occupier_id: createdOccupiers[0].id,
            created_by: managerUser.id
        }
    ];
    
    sampleNotes.forEach(noteData => {
        const note = Note.create(noteData);
        console.log(`   ✓ Created note: ${note.title}`);
    });
    
    // Print summary
    console.log('');
    console.log('✅ Database seeding completed successfully!');
    console.log('');
    console.log('📊 Sample data created:');
    console.log(`   🏠 ${createdUnits.length} housing units`);
    console.log(`   👥 ${createdOccupiers.length} occupiers`);
    console.log(`   📝 ${sampleNotes.length} notes`);
    console.log('');
    console.log('🎯 Statistics:');
    
    const occupancyStats = HousingUnit.getOccupancyStats();
    console.log(`   📈 Occupancy Rate: ${occupancyStats.occupancy_rate}%`);
    console.log(`   🏠 Occupied Units: ${occupancyStats.occupied_units}`);
    console.log(`   🏠 Vacant Units: ${occupancyStats.vacant_units}`);
    
    const occupierStats = Occupier.getOccupancyStats();
    console.log(`   💰 Monthly Revenue: $${occupierStats.total_monthly_revenue}`);
    console.log(`   💰 Average Rent: $${occupierStats.average_rent}`);
    console.log('');
    console.log('🚀 Ready to run: npm start');
    
} catch (error) {
    console.error('❌ Database seeding failed:', error);
    process.exit(1);
} finally {
    Database.close();
}