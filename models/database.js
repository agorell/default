const Database = require('better-sqlite3');
const path = require('path');

class DatabaseManager {
  constructor() {
    this.db = null;
  }

  init() {
    try {
      this.db = new Database(path.join(__dirname, '..', 'database.sqlite'));
      this.db.pragma('journal_mode = WAL');
      this.db.pragma('foreign_keys = ON');
      
      console.log('Database connected successfully');
      this.createTables();
    } catch (error) {
      console.error('Database connection error:', error);
      process.exit(1);
    }
  }

  createTables() {
    const createTables = `
      -- Roles table
      CREATE TABLE IF NOT EXISTS roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(50) UNIQUE NOT NULL,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      );

      -- Users table
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
      );

      -- Housing types table
      CREATE TABLE IF NOT EXISTS housing_types (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(50) NOT NULL,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      );

      -- Housing units table
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
      );

      -- Occupiers table
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
      );

      -- Notes table
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
      );

      -- Create indexes
      CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
      CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
      CREATE INDEX IF NOT EXISTS idx_housing_units_unit_number ON housing_units(unit_number);
      CREATE INDEX IF NOT EXISTS idx_housing_units_occupied ON housing_units(is_occupied);
      CREATE INDEX IF NOT EXISTS idx_occupiers_unit ON occupiers(housing_unit_id);
      CREATE INDEX IF NOT EXISTS idx_notes_unit ON notes(housing_unit_id);
      CREATE INDEX IF NOT EXISTS idx_notes_occupier ON notes(occupier_id);
    `;

    try {
      this.db.exec(createTables);
      console.log('Database tables created successfully');
    } catch (error) {
      console.error('Error creating tables:', error);
    }
  }

  getConnection() {
    return this.db;
  }

  close() {
    if (this.db) {
      this.db.close();
      console.log('Database connection closed');
    }
  }
}

module.exports = new DatabaseManager();