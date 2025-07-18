#!/usr/bin/env node

const path = require('path');
const { initializeTables, insertDefaultData } = require('../models/database');

console.log('Housing Management System - Database Initialization');
console.log('=' .repeat(50));

try {
    // Initialize database tables
    console.log('Creating database tables...');
    initializeTables();
    
    // Insert default data
    console.log('Inserting default data...');
    insertDefaultData();
    
    console.log('✅ Database initialization completed successfully!');
    console.log('');
    console.log('Default user accounts created:');
    console.log('- Admin: admin / admin123');
    console.log('- Manager: manager / manager123');
    console.log('- Viewer: viewer / viewer123');
    console.log('');
    console.log('You can now start the application with: npm start');
    
} catch (error) {
    console.error('❌ Database initialization failed:', error.message);
    process.exit(1);
}