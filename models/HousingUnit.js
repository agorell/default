const { db } = require('../config/database');

class HousingUnit {
  constructor(unitData) {
    this.id = unitData.id;
    this.unit_number = unitData.unit_number;
    this.housing_type_id = unitData.housing_type_id;
    this.bedrooms = unitData.bedrooms;
    this.bathrooms = unitData.bathrooms;
    this.square_footage = unitData.square_footage;
    this.rental_rate = unitData.rental_rate;
    this.is_occupied = unitData.is_occupied;
    this.condition_grade = unitData.condition_grade;
    this.address = unitData.address;
    this.description = unitData.description;
    this.created_at = unitData.created_at;
    this.updated_at = unitData.updated_at;
    this.housing_type_name = unitData.housing_type_name;
    this.occupier_name = unitData.occupier_name;
  }

  // Get all housing units
  static findAll(filters = {}) {
    let query = `
      SELECT hu.*, 
             ht.name as housing_type_name,
             CASE 
               WHEN o.id IS NOT NULL THEN o.first_name || ' ' || o.last_name
               ELSE NULL 
             END as occupier_name
      FROM housing_units hu
      LEFT JOIN housing_types ht ON hu.housing_type_id = ht.id
      LEFT JOIN occupiers o ON hu.id = o.housing_unit_id
      WHERE 1=1
    `;
    
    const params = [];
    
    if (filters.is_occupied !== undefined) {
      query += ' AND hu.is_occupied = ?';
      params.push(filters.is_occupied);
    }
    
    if (filters.housing_type_id) {
      query += ' AND hu.housing_type_id = ?';
      params.push(filters.housing_type_id);
    }
    
    if (filters.search) {
      query += ' AND (hu.unit_number LIKE ? OR hu.address LIKE ? OR hu.description LIKE ?)';
      const searchTerm = `%${filters.search}%`;
      params.push(searchTerm, searchTerm, searchTerm);
    }
    
    query += ' ORDER BY hu.unit_number';
    
    const rows = db.prepare(query).all(...params);
    return rows.map(row => new HousingUnit(row));
  }

  // Find housing unit by ID
  static findById(id) {
    const query = `
      SELECT hu.*, 
             ht.name as housing_type_name,
             CASE 
               WHEN o.id IS NOT NULL THEN o.first_name || ' ' || o.last_name
               ELSE NULL 
             END as occupier_name
      FROM housing_units hu
      LEFT JOIN housing_types ht ON hu.housing_type_id = ht.id
      LEFT JOIN occupiers o ON hu.id = o.housing_unit_id
      WHERE hu.id = ?
    `;
    const row = db.prepare(query).get(id);
    return row ? new HousingUnit(row) : null;
  }

  // Find housing unit by unit number
  static findByUnitNumber(unitNumber) {
    const query = `
      SELECT hu.*, 
             ht.name as housing_type_name,
             CASE 
               WHEN o.id IS NOT NULL THEN o.first_name || ' ' || o.last_name
               ELSE NULL 
             END as occupier_name
      FROM housing_units hu
      LEFT JOIN housing_types ht ON hu.housing_type_id = ht.id
      LEFT JOIN occupiers o ON hu.id = o.housing_unit_id
      WHERE hu.unit_number = ?
    `;
    const row = db.prepare(query).get(unitNumber);
    return row ? new HousingUnit(row) : null;
  }

  // Create new housing unit
  static create(unitData) {
    const query = `
      INSERT INTO housing_units (
        unit_number, housing_type_id, bedrooms, bathrooms, square_footage,
        rental_rate, condition_grade, address, description
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    `;
    const result = db.prepare(query).run(
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

  // Update housing unit
  static update(id, unitData) {
    const updates = [];
    const values = [];
    
    if (unitData.unit_number) {
      updates.push('unit_number = ?');
      values.push(unitData.unit_number);
    }
    if (unitData.housing_type_id) {
      updates.push('housing_type_id = ?');
      values.push(unitData.housing_type_id);
    }
    if (unitData.bedrooms !== undefined) {
      updates.push('bedrooms = ?');
      values.push(unitData.bedrooms);
    }
    if (unitData.bathrooms !== undefined) {
      updates.push('bathrooms = ?');
      values.push(unitData.bathrooms);
    }
    if (unitData.square_footage !== undefined) {
      updates.push('square_footage = ?');
      values.push(unitData.square_footage);
    }
    if (unitData.rental_rate !== undefined) {
      updates.push('rental_rate = ?');
      values.push(unitData.rental_rate);
    }
    if (unitData.condition_grade) {
      updates.push('condition_grade = ?');
      values.push(unitData.condition_grade);
    }
    if (unitData.address) {
      updates.push('address = ?');
      values.push(unitData.address);
    }
    if (unitData.description) {
      updates.push('description = ?');
      values.push(unitData.description);
    }
    
    if (updates.length === 0) return false;
    
    updates.push('updated_at = CURRENT_TIMESTAMP');
    values.push(id);
    
    const query = `UPDATE housing_units SET ${updates.join(', ')} WHERE id = ?`;
    db.prepare(query).run(...values);
    
    return this.findById(id);
  }

  // Delete housing unit
  static delete(id) {
    const query = 'DELETE FROM housing_units WHERE id = ?';
    const result = db.prepare(query).run(id);
    return result.changes > 0;
  }

  // Update occupancy status
  static updateOccupancyStatus(id, isOccupied) {
    const query = 'UPDATE housing_units SET is_occupied = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
    const result = db.prepare(query).run(isOccupied ? 1 : 0, id);
    return result.changes > 0;
  }

  // Get all housing types
  static getHousingTypes() {
    const query = 'SELECT * FROM housing_types ORDER BY name';
    return db.prepare(query).all();
  }

  // Get occupancy statistics
  static getOccupancyStats() {
    const query = `
      SELECT 
        COUNT(*) as total_units,
        SUM(CASE WHEN is_occupied = 1 THEN 1 ELSE 0 END) as occupied_units,
        SUM(CASE WHEN is_occupied = 0 THEN 1 ELSE 0 END) as vacant_units,
        ROUND(
          (SUM(CASE WHEN is_occupied = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2
        ) as occupancy_rate
      FROM housing_units
    `;
    return db.prepare(query).get();
  }

  // Get vacant units
  static getVacantUnits() {
    return this.findAll({ is_occupied: 0 });
  }

  // Get occupied units
  static getOccupiedUnits() {
    return this.findAll({ is_occupied: 1 });
  }

  // Check if unit is occupied
  isOccupied() {
    return this.is_occupied === 1;
  }

  // Get formatted rental rate
  getFormattedRentalRate() {
    return this.rental_rate ? `$${parseFloat(this.rental_rate).toFixed(2)}` : 'N/A';
  }

  // Get unit summary
  getUnitSummary() {
    const bedrooms = this.bedrooms || 0;
    const bathrooms = this.bathrooms || 0;
    return `${bedrooms} bed, ${bathrooms} bath`;
  }
}

module.exports = HousingUnit;