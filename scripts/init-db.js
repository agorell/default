#!/usr/bin/env node

const path = require('path');
const Database = require('../models/database');

console.log('🚀 Initializing Housing Management Database...');

try {
    // Initialize database connection and create tables
    Database.init();
    
    const db = Database.getConnection();
    
    // Insert default roles
    console.log('📝 Creating default roles...');
    const insertRole = db.prepare(`
        INSERT OR IGNORE INTO roles (name, description)
        VALUES (?, ?)
    `);
    
    insertRole.run('Admin', 'Full system administration access');
    insertRole.run('Manager', 'Housing unit and occupier management');
    insertRole.run('Viewer', 'Read-only access to system data');
    
    // Insert default housing types
    console.log('🏠 Creating default housing types...');
    const insertHousingType = db.prepare(`
        INSERT OR IGNORE INTO housing_types (name, description)
        VALUES (?, ?)
    `);
    
    insertHousingType.run('Apartment', 'Multi-story residential building unit');
    insertHousingType.run('House', 'Single-family detached house');
    insertHousingType.run('Townhouse', 'Multi-story attached house');
    insertHousingType.run('Studio', 'Single-room living space');
    insertHousingType.run('Room', 'Private room in shared housing');
    
    // Get role IDs for user creation
    const roles = db.prepare('SELECT id, name FROM roles').all();
    const roleMap = {};
    roles.forEach(role => {
        roleMap[role.name] = role.id;
    });
    
    // Insert default users
    console.log('👥 Creating default users...');
    const bcrypt = require('bcrypt');
    
    const insertUser = db.prepare(`
        INSERT OR IGNORE INTO users (username, email, password_hash, first_name, last_name, role_id)
        VALUES (?, ?, ?, ?, ?, ?)
    `);
    
    // Create admin user
    const adminHash = bcrypt.hashSync('admin123', 10);
    insertUser.run('admin', 'admin@housingmanagement.com', adminHash, 'System', 'Administrator', roleMap['Admin']);
    
    // Create manager user
    const managerHash = bcrypt.hashSync('manager123', 10);
    insertUser.run('manager', 'manager@housingmanagement.com', managerHash, 'Housing', 'Manager', roleMap['Manager']);
    
    // Create viewer user
    const viewerHash = bcrypt.hashSync('viewer123', 10);
    insertUser.run('viewer', 'viewer@housingmanagement.com', viewerHash, 'System', 'Viewer', roleMap['Viewer']);
    
    console.log('✅ Database initialization completed successfully!');
    console.log('');
    console.log('🔐 Default user accounts created:');
    console.log('   Admin:   admin / admin123');
    console.log('   Manager: manager / manager123');
    console.log('   Viewer:  viewer / viewer123');
    console.log('');
    console.log('🏠 Default housing types created:');
    console.log('   - Apartment, House, Townhouse, Studio, Room');
    console.log('');
    console.log('🎉 Ready to run: npm start');
    
} catch (error) {
    console.error('❌ Database initialization failed:', error);
    process.exit(1);
} finally {
    Database.close();
}