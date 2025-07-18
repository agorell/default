const Database = require('./database');

class Occupier {
  constructor(data) {
    this.id = data.id;
    this.housing_unit_id = data.housing_unit_id;
    this.first_name = data.first_name;
    this.last_name = data.last_name;
    this.phone = data.phone;
    this.email = data.email;
    this.occupancy_start_date = data.occupancy_start_date;
    this.monthly_rent = data.monthly_rent;
    this.emergency_contact_name = data.emergency_contact_name;
    this.emergency_contact_phone = data.emergency_contact_phone;
    this.created_at = data.created_at;
    this.updated_at = data.updated_at;
    this.unit_number = data.unit_number;
    this.housing_type_name = data.housing_type_name;
  }

  static create(occupierData) {
    const db = Database.getConnection();
    
    const stmt = db.prepare(`
      INSERT INTO occupiers (
        housing_unit_id, first_name, last_name, phone, email,
        occupancy_start_date, monthly_rent, emergency_contact_name, emergency_contact_phone
      )
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);
    
    const result = stmt.run(
      occupierData.housing_unit_id,
      occupierData.first_name,
      occupierData.last_name,
      occupierData.phone,
      occupierData.email,
      occupierData.occupancy_start_date,
      occupierData.monthly_rent,
      occupierData.emergency_contact_name,
      occupierData.emergency_contact_phone
    );
    
    // Mark the unit as occupied
    const unitStmt = db.prepare(`
      UPDATE housing_units SET is_occupied = 1, updated_at = CURRENT_TIMESTAMP 
      WHERE id = ?
    `);
    unitStmt.run(occupierData.housing_unit_id);
    
    return this.findById(result.lastInsertRowid);
  }

  static findById(id) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT 
        o.*,
        hu.unit_number,
        ht.name as housing_type_name
      FROM occupiers o
      JOIN housing_units hu ON o.housing_unit_id = hu.id
      JOIN housing_types ht ON hu.housing_type_id = ht.id
      WHERE o.id = ?
    `);
    
    const row = stmt.get(id);
    return row ? new Occupier(row) : null;
  }

  static findByUnitId(unitId) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT 
        o.*,
        hu.unit_number,
        ht.name as housing_type_name
      FROM occupiers o
      JOIN housing_units hu ON o.housing_unit_id = hu.id
      JOIN housing_types ht ON hu.housing_type_id = ht.id
      WHERE o.housing_unit_id = ?
    `);
    
    const row = stmt.get(unitId);
    return row ? new Occupier(row) : null;
  }

  static findAll(filters = {}) {
    const db = Database.getConnection();
    let query = `
      SELECT 
        o.*,
        hu.unit_number,
        ht.name as housing_type_name
      FROM occupiers o
      JOIN housing_units hu ON o.housing_unit_id = hu.id
      JOIN housing_types ht ON hu.housing_type_id = ht.id
    `;
    
    const conditions = [];
    const params = [];
    
    if (filters.housing_type_id) {
      conditions.push('hu.housing_type_id = ?');
      params.push(filters.housing_type_id);
    }
    
    if (filters.search) {
      conditions.push(`(
        o.first_name LIKE ? OR 
        o.last_name LIKE ? OR 
        o.email LIKE ? OR 
        o.phone LIKE ? OR 
        hu.unit_number LIKE ?
      )`);
      params.push(
        `%${filters.search}%`,
        `%${filters.search}%`,
        `%${filters.search}%`,
        `%${filters.search}%`,
        `%${filters.search}%`
      );
    }
    
    if (conditions.length > 0) {
      query += ' WHERE ' + conditions.join(' AND ');
    }
    
    query += ' ORDER BY o.last_name, o.first_name';
    
    const stmt = db.prepare(query);
    const rows = stmt.all(...params);
    return rows.map(row => new Occupier(row));
  }

  static getOccupancyStats() {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT 
        COUNT(*) as total_occupiers,
        AVG(monthly_rent) as average_rent,
        MIN(monthly_rent) as min_rent,
        MAX(monthly_rent) as max_rent,
        SUM(monthly_rent) as total_monthly_revenue
      FROM occupiers
    `);
    
    return stmt.get();
  }

  static getRecentMoveIns(limit = 10) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT 
        o.*,
        hu.unit_number,
        ht.name as housing_type_name
      FROM occupiers o
      JOIN housing_units hu ON o.housing_unit_id = hu.id
      JOIN housing_types ht ON hu.housing_type_id = ht.id
      ORDER BY o.occupancy_start_date DESC
      LIMIT ?
    `);
    
    const rows = stmt.all(limit);
    return rows.map(row => new Occupier(row));
  }

  update(occupierData) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      UPDATE occupiers
      SET 
        first_name = ?, last_name = ?, phone = ?, email = ?,
        occupancy_start_date = ?, monthly_rent = ?, emergency_contact_name = ?,
        emergency_contact_phone = ?, updated_at = CURRENT_TIMESTAMP
      WHERE id = ?
    `);
    
    stmt.run(
      occupierData.first_name,
      occupierData.last_name,
      occupierData.phone,
      occupierData.email,
      occupierData.occupancy_start_date,
      occupierData.monthly_rent,
      occupierData.emergency_contact_name,
      occupierData.emergency_contact_phone,
      this.id
    );
    
    return Occupier.findById(this.id);
  }

  delete() {
    const db = Database.getConnection();
    
    // Mark the unit as vacant
    const unitStmt = db.prepare(`
      UPDATE housing_units SET is_occupied = 0, updated_at = CURRENT_TIMESTAMP 
      WHERE id = ?
    `);
    unitStmt.run(this.housing_unit_id);
    
    // Delete the occupier
    const stmt = db.prepare('DELETE FROM occupiers WHERE id = ?');
    stmt.run(this.id);
  }

  moveOut() {
    this.delete();
  }

  getHousingUnit() {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT 
        hu.*,
        ht.name as housing_type_name
      FROM housing_units hu
      JOIN housing_types ht ON hu.housing_type_id = ht.id
      WHERE hu.id = ?
    `);
    
    return stmt.get(this.housing_unit_id);
  }

  getNotes() {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT n.*, u.first_name || ' ' || u.last_name as created_by_name
      FROM notes n
      JOIN users u ON n.created_by = u.id
      WHERE n.occupier_id = ?
      ORDER BY n.created_at DESC
    `);
    
    return stmt.all(this.id);
  }

  getFullName() {
    return `${this.first_name} ${this.last_name}`;
  }

  getFormattedRent() {
    return this.monthly_rent ? `$${parseFloat(this.monthly_rent).toFixed(2)}` : 'N/A';
  }

  getFormattedMoveInDate() {
    if (!this.occupancy_start_date) return 'N/A';
    
    const date = new Date(this.occupancy_start_date);
    return date.toLocaleDateString();
  }

  getOccupancyDuration() {
    if (!this.occupancy_start_date) return 'N/A';
    
    const moveInDate = new Date(this.occupancy_start_date);
    const today = new Date();
    const diffTime = Math.abs(today - moveInDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays < 30) {
      return `${diffDays} days`;
    } else if (diffDays < 365) {
      const months = Math.floor(diffDays / 30);
      return `${months} month${months > 1 ? 's' : ''}`;
    } else {
      const years = Math.floor(diffDays / 365);
      const remainingMonths = Math.floor((diffDays % 365) / 30);
      return `${years} year${years > 1 ? 's' : ''}${remainingMonths > 0 ? ` ${remainingMonths} month${remainingMonths > 1 ? 's' : ''}` : ''}`;
    }
  }
}

module.exports = Occupier;