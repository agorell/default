const { db } = require('../config/database');

// Create all tables
const initializeTables = () => {
  console.log('Initializing database tables...');

  // Create roles table
  db.exec(`
    CREATE TABLE IF NOT EXISTS roles (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name VARCHAR(50) UNIQUE NOT NULL,
      description TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
  `);

  // Create users table
  db.exec(`
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username VARCHAR(50) UNIQUE NOT NULL,
      email VARCHAR(100) UNIQUE NOT NULL,
      password_hash VARCHAR(255) NOT NULL,
      first_name VARCHAR(50) NOT NULL,
      last_name VARCHAR(50) NOT NULL,
      role_id INTEGER NOT NULL,
      is_active BOOLEAN DEFAULT 1,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (role_id) REFERENCES roles(id)
    )
  `);

  // Create housing_types table
  db.exec(`
    CREATE TABLE IF NOT EXISTS housing_types (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name VARCHAR(50) NOT NULL,
      description TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
  `);

  // Create housing_units table
  db.exec(`
    CREATE TABLE IF NOT EXISTS housing_units (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      unit_number VARCHAR(20) UNIQUE NOT NULL,
      housing_type_id INTEGER NOT NULL,
      bedrooms INTEGER,
      bathrooms DECIMAL(3,1),
      square_footage INTEGER,
      rental_rate DECIMAL(10,2),
      is_occupied BOOLEAN DEFAULT 0,
      condition_grade VARCHAR(20),
      address TEXT,
      description TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (housing_type_id) REFERENCES housing_types(id)
    )
  `);

  // Create occupiers table
  db.exec(`
    CREATE TABLE IF NOT EXISTS occupiers (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      housing_unit_id INTEGER UNIQUE,
      first_name VARCHAR(50) NOT NULL,
      last_name VARCHAR(50) NOT NULL,
      phone VARCHAR(20),
      email VARCHAR(100),
      occupancy_start_date DATE NOT NULL,
      monthly_rent DECIMAL(10,2),
      emergency_contact_name VARCHAR(100),
      emergency_contact_phone VARCHAR(20),
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (housing_unit_id) REFERENCES housing_units(id)
    )
  `);

  // Create notes table
  db.exec(`
    CREATE TABLE IF NOT EXISTS notes (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      title VARCHAR(200) NOT NULL,
      content TEXT NOT NULL,
      category VARCHAR(50),
      housing_unit_id INTEGER,
      occupier_id INTEGER,
      created_by INTEGER NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (housing_unit_id) REFERENCES housing_units(id),
      FOREIGN KEY (occupier_id) REFERENCES occupiers(id),
      FOREIGN KEY (created_by) REFERENCES users(id)
    )
  `);

  // Create indexes for performance
  db.exec(`
    CREATE INDEX IF NOT EXISTS idx_users_role ON users(role_id);
    CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
    CREATE INDEX IF NOT EXISTS idx_housing_units_type ON housing_units(housing_type_id);
    CREATE INDEX IF NOT EXISTS idx_housing_units_occupied ON housing_units(is_occupied);
    CREATE INDEX IF NOT EXISTS idx_occupiers_unit ON occupiers(housing_unit_id);
    CREATE INDEX IF NOT EXISTS idx_notes_unit ON notes(housing_unit_id);
    CREATE INDEX IF NOT EXISTS idx_notes_occupier ON notes(occupier_id);
    CREATE INDEX IF NOT EXISTS idx_notes_category ON notes(category);
  `);

  console.log('Database tables created successfully.');
};

// Insert default data
const insertDefaultData = () => {
  console.log('Inserting default data...');

  // Insert roles
  const insertRole = db.prepare('INSERT OR IGNORE INTO roles (name, description) VALUES (?, ?)');
  insertRole.run('Admin', 'Full system access and user management');
  insertRole.run('Manager', 'Manage housing units, occupiers, and notes');
  insertRole.run('Viewer', 'Read-only access to housing units and occupiers');

  // Insert housing types
  const insertHousingType = db.prepare('INSERT OR IGNORE INTO housing_types (name, description) VALUES (?, ?)');
  insertHousingType.run('Apartment', 'Multi-unit residential building');
  insertHousingType.run('House', 'Single-family detached home');
  insertHousingType.run('Room', 'Single room in shared facility');
  insertHousingType.run('Studio', 'One-room living space');
  insertHousingType.run('Townhouse', 'Multi-story attached home');

  // Insert default admin user
  const bcrypt = require('bcrypt');
  const adminPassword = bcrypt.hashSync('admin123', 10);
  const managerPassword = bcrypt.hashSync('manager123', 10);
  const viewerPassword = bcrypt.hashSync('viewer123', 10);

  const insertUser = db.prepare(`
    INSERT OR IGNORE INTO users (username, email, password_hash, first_name, last_name, role_id) 
    VALUES (?, ?, ?, ?, ?, ?)
  `);
  
  insertUser.run('admin', 'admin@housingmanagement.com', adminPassword, 'System', 'Administrator', 1);
  insertUser.run('manager', 'manager@housingmanagement.com', managerPassword, 'Housing', 'Manager', 2);
  insertUser.run('viewer', 'viewer@housingmanagement.com', viewerPassword, 'Housing', 'Viewer', 3);

  console.log('Default data inserted successfully.');
};

module.exports = {
  initializeTables,
  insertDefaultData
};