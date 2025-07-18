const Database = require('./database');

class HousingUnit {
  constructor(data) {
    this.id = data.id;
    this.unit_number = data.unit_number;
    this.housing_type_id = data.housing_type_id;
    this.housing_type_name = data.housing_type_name;
    this.bedrooms = data.bedrooms;
    this.bathrooms = data.bathrooms;
    this.square_footage = data.square_footage;
    this.rental_rate = data.rental_rate;
    this.is_occupied = data.is_occupied;
    this.condition_grade = data.condition_grade;
    this.address = data.address;
    this.description = data.description;
    this.created_at = data.created_at;
    this.updated_at = data.updated_at;
    this.occupier = data.occupier;
  }

  static create(unitData) {
    const db = Database.getConnection();
    
    const stmt = db.prepare(`
      INSERT INTO housing_units (
        unit_number, housing_type_id, bedrooms, bathrooms, square_footage,
        rental_rate, condition_grade, address, description
      )
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);
    
    const result = stmt.run(
      unitData.unit_number,
      unitData.housing_type_id,
      unitData.bedrooms,
      unitData.bathrooms,
      unitData.square_footage,
      unitData.rental_rate,
      unitData.condition_grade,
      unitData.address,
      unitData.description
    );
    
    return this.findById(result.lastInsertRowid);
  }

  static findById(id) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT 
        hu.*,
        ht.name as housing_type_name,
        o.first_name || ' ' || o.last_name as occupier_name,
        o.phone as occupier_phone,
        o.email as occupier_email,
        o.occupancy_start_date,
        o.monthly_rent as occupier_rent
      FROM housing_units hu
      JOIN housing_types ht ON hu.housing_type_id = ht.id
      LEFT JOIN occupiers o ON hu.id = o.housing_unit_id
      WHERE hu.id = ?
    `);
    
    const row = stmt.get(id);
    return row ? new HousingUnit(row) : null;
  }

  static findByUnitNumber(unitNumber) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT 
        hu.*,
        ht.name as housing_type_name,
        o.first_name || ' ' || o.last_name as occupier_name
      FROM housing_units hu
      JOIN housing_types ht ON hu.housing_type_id = ht.id
      LEFT JOIN occupiers o ON hu.id = o.housing_unit_id
      WHERE hu.unit_number = ?
    `);
    
    const row = stmt.get(unitNumber);
    return row ? new HousingUnit(row) : null;
  }

  static findAll(filters = {}) {
    const db = Database.getConnection();
    let query = `
      SELECT 
        hu.*,
        ht.name as housing_type_name,
        o.first_name || ' ' || o.last_name as occupier_name
      FROM housing_units hu
      JOIN housing_types ht ON hu.housing_type_id = ht.id
      LEFT JOIN occupiers o ON hu.id = o.housing_unit_id
    `;
    
    const conditions = [];
    const params = [];
    
    if (filters.housing_type_id) {
      conditions.push('hu.housing_type_id = ?');
      params.push(filters.housing_type_id);
    }
    
    if (filters.is_occupied !== undefined) {
      conditions.push('hu.is_occupied = ?');
      params.push(filters.is_occupied);
    }
    
    if (filters.condition_grade) {
      conditions.push('hu.condition_grade = ?');
      params.push(filters.condition_grade);
    }
    
    if (filters.search) {
      conditions.push('(hu.unit_number LIKE ? OR hu.address LIKE ? OR hu.description LIKE ?)');
      params.push(`%${filters.search}%`, `%${filters.search}%`, `%${filters.search}%`);
    }
    
    if (conditions.length > 0) {
      query += ' WHERE ' + conditions.join(' AND ');
    }
    
    query += ' ORDER BY hu.unit_number';
    
    const stmt = db.prepare(query);
    const rows = stmt.all(...params);
    return rows.map(row => new HousingUnit(row));
  }

  static findVacant() {
    return this.findAll({ is_occupied: 0 });
  }

  static findOccupied() {
    return this.findAll({ is_occupied: 1 });
  }

  static getOccupancyStats() {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT 
        COUNT(*) as total_units,
        SUM(is_occupied) as occupied_units,
        (COUNT(*) - SUM(is_occupied)) as vacant_units,
        ROUND((SUM(is_occupied) * 100.0 / COUNT(*)), 2) as occupancy_rate
      FROM housing_units
    `);
    
    return stmt.get();
  }

  static getTypeStats() {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT 
        ht.name as housing_type,
        COUNT(*) as total_units,
        SUM(hu.is_occupied) as occupied_units,
        (COUNT(*) - SUM(hu.is_occupied)) as vacant_units
      FROM housing_units hu
      JOIN housing_types ht ON hu.housing_type_id = ht.id
      GROUP BY ht.id, ht.name
      ORDER BY ht.name
    `);
    
    return stmt.all();
  }

  update(unitData) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      UPDATE housing_units
      SET 
        unit_number = ?, housing_type_id = ?, bedrooms = ?, bathrooms = ?,
        square_footage = ?, rental_rate = ?, condition_grade = ?, address = ?,
        description = ?, updated_at = CURRENT_TIMESTAMP
      WHERE id = ?
    `);
    
    stmt.run(
      unitData.unit_number,
      unitData.housing_type_id,
      unitData.bedrooms,
      unitData.bathrooms,
      unitData.square_footage,
      unitData.rental_rate,
      unitData.condition_grade,
      unitData.address,
      unitData.description,
      this.id
    );
    
    return HousingUnit.findById(this.id);
  }

  setOccupancyStatus(isOccupied) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      UPDATE housing_units
      SET is_occupied = ?, updated_at = CURRENT_TIMESTAMP
      WHERE id = ?
    `);
    
    stmt.run(isOccupied ? 1 : 0, this.id);
  }

  delete() {
    const db = Database.getConnection();
    const stmt = db.prepare('DELETE FROM housing_units WHERE id = ?');
    stmt.run(this.id);
  }

  getOccupier() {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT * FROM occupiers WHERE housing_unit_id = ?
    `);
    
    return stmt.get(this.id);
  }

  getNotes() {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT n.*, u.first_name || ' ' || u.last_name as created_by_name
      FROM notes n
      JOIN users u ON n.created_by = u.id
      WHERE n.housing_unit_id = ?
      ORDER BY n.created_at DESC
    `);
    
    return stmt.all(this.id);
  }

  getFormattedRentalRate() {
    return this.rental_rate ? `$${parseFloat(this.rental_rate).toFixed(2)}` : 'N/A';
  }

  getOccupancyStatus() {
    return this.is_occupied ? 'Occupied' : 'Vacant';
  }

  getConditionBadgeClass() {
    switch (this.condition_grade) {
      case 'Excellent': return 'success';
      case 'Good': return 'primary';
      case 'Fair': return 'warning';
      case 'Poor': return 'danger';
      default: return 'secondary';
    }
  }
}

module.exports = HousingUnit;