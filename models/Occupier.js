const { db } = require('../config/database');

class Occupier {
  constructor(occupierData) {
    this.id = occupierData.id;
    this.housing_unit_id = occupierData.housing_unit_id;
    this.first_name = occupierData.first_name;
    this.last_name = occupierData.last_name;
    this.phone = occupierData.phone;
    this.email = occupierData.email;
    this.occupancy_start_date = occupierData.occupancy_start_date;
    this.monthly_rent = occupierData.monthly_rent;
    this.emergency_contact_name = occupierData.emergency_contact_name;
    this.emergency_contact_phone = occupierData.emergency_contact_phone;
    this.created_at = occupierData.created_at;
    this.updated_at = occupierData.updated_at;
    this.unit_number = occupierData.unit_number;
    this.housing_type_name = occupierData.housing_type_name;
  }

  // Get all occupiers
  static findAll(filters = {}) {
    let query = `
      SELECT o.*, 
             hu.unit_number,
             ht.name as housing_type_name
      FROM occupiers o
      LEFT JOIN housing_units hu ON o.housing_unit_id = hu.id
      LEFT JOIN housing_types ht ON hu.housing_type_id = ht.id
      WHERE 1=1
    `;
    
    const params = [];
    
    if (filters.housing_unit_id) {
      query += ' AND o.housing_unit_id = ?';
      params.push(filters.housing_unit_id);
    }
    
    if (filters.search) {
      query += ' AND (o.first_name LIKE ? OR o.last_name LIKE ? OR o.email LIKE ? OR hu.unit_number LIKE ?)';
      const searchTerm = `%${filters.search}%`;
      params.push(searchTerm, searchTerm, searchTerm, searchTerm);
    }
    
    query += ' ORDER BY o.last_name, o.first_name';
    
    const rows = db.prepare(query).all(...params);
    return rows.map(row => new Occupier(row));
  }

  // Find occupier by ID
  static findById(id) {
    const query = `
      SELECT o.*, 
             hu.unit_number,
             ht.name as housing_type_name
      FROM occupiers o
      LEFT JOIN housing_units hu ON o.housing_unit_id = hu.id
      LEFT JOIN housing_types ht ON hu.housing_type_id = ht.id
      WHERE o.id = ?
    `;
    const row = db.prepare(query).get(id);
    return row ? new Occupier(row) : null;
  }

  // Find occupier by housing unit ID
  static findByHousingUnitId(housingUnitId) {
    const query = `
      SELECT o.*, 
             hu.unit_number,
             ht.name as housing_type_name
      FROM occupiers o
      LEFT JOIN housing_units hu ON o.housing_unit_id = hu.id
      LEFT JOIN housing_types ht ON hu.housing_type_id = ht.id
      WHERE o.housing_unit_id = ?
    `;
    const row = db.prepare(query).get(housingUnitId);
    return row ? new Occupier(row) : null;
  }

  // Create new occupier
  static create(occupierData) {
    const transaction = db.transaction(() => {
      // Insert occupier
      const query = `
        INSERT INTO occupiers (
          housing_unit_id, first_name, last_name, phone, email,
          occupancy_start_date, monthly_rent, emergency_contact_name, emergency_contact_phone
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
      `;
      const result = db.prepare(query).run(
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
      
      // Update housing unit as occupied
      const updateUnitQuery = 'UPDATE housing_units SET is_occupied = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
      db.prepare(updateUnitQuery).run(occupierData.housing_unit_id);
      
      return result.lastInsertRowid;
    });
    
    const occupierId = transaction();
    return this.findById(occupierId);
  }

  // Update occupier
  static update(id, occupierData) {
    const updates = [];
    const values = [];
    
    if (occupierData.first_name) {
      updates.push('first_name = ?');
      values.push(occupierData.first_name);
    }
    if (occupierData.last_name) {
      updates.push('last_name = ?');
      values.push(occupierData.last_name);
    }
    if (occupierData.phone) {
      updates.push('phone = ?');
      values.push(occupierData.phone);
    }
    if (occupierData.email) {
      updates.push('email = ?');
      values.push(occupierData.email);
    }
    if (occupierData.occupancy_start_date) {
      updates.push('occupancy_start_date = ?');
      values.push(occupierData.occupancy_start_date);
    }
    if (occupierData.monthly_rent !== undefined) {
      updates.push('monthly_rent = ?');
      values.push(occupierData.monthly_rent);
    }
    if (occupierData.emergency_contact_name) {
      updates.push('emergency_contact_name = ?');
      values.push(occupierData.emergency_contact_name);
    }
    if (occupierData.emergency_contact_phone) {
      updates.push('emergency_contact_phone = ?');
      values.push(occupierData.emergency_contact_phone);
    }
    
    if (updates.length === 0) return false;
    
    updates.push('updated_at = CURRENT_TIMESTAMP');
    values.push(id);
    
    const query = `UPDATE occupiers SET ${updates.join(', ')} WHERE id = ?`;
    db.prepare(query).run(...values);
    
    return this.findById(id);
  }

  // Delete occupier (move out)
  static delete(id) {
    const transaction = db.transaction(() => {
      // Get occupier's housing unit ID
      const occupier = this.findById(id);
      if (!occupier) return false;
      
      // Delete occupier
      const deleteQuery = 'DELETE FROM occupiers WHERE id = ?';
      const deleteResult = db.prepare(deleteQuery).run(id);
      
      // Update housing unit as vacant
      const updateUnitQuery = 'UPDATE housing_units SET is_occupied = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
      db.prepare(updateUnitQuery).run(occupier.housing_unit_id);
      
      return deleteResult.changes > 0;
    });
    
    return transaction();
  }

  // Move occupier to different unit
  static moveToUnit(occupierId, newHousingUnitId) {
    const transaction = db.transaction(() => {
      // Get current occupier
      const occupier = this.findById(occupierId);
      if (!occupier) return false;
      
      // Update occupier's housing unit
      const updateOccupierQuery = 'UPDATE occupiers SET housing_unit_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
      db.prepare(updateOccupierQuery).run(newHousingUnitId, occupierId);
      
      // Mark old unit as vacant
      const markVacantQuery = 'UPDATE housing_units SET is_occupied = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
      db.prepare(markVacantQuery).run(occupier.housing_unit_id);
      
      // Mark new unit as occupied
      const markOccupiedQuery = 'UPDATE housing_units SET is_occupied = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
      db.prepare(markOccupiedQuery).run(newHousingUnitId);
      
      return true;
    });
    
    return transaction();
  }

  // Get occupier statistics
  static getOccupierStats() {
    const query = `
      SELECT 
        COUNT(*) as total_occupiers,
        AVG(monthly_rent) as average_rent,
        MIN(monthly_rent) as min_rent,
        MAX(monthly_rent) as max_rent,
        SUM(monthly_rent) as total_monthly_revenue
      FROM occupiers
    `;
    return db.prepare(query).get();
  }

  // Get occupiers with upcoming lease renewals (within 30 days)
  static getUpcomingLeaseRenewals() {
    const query = `
      SELECT o.*, 
             hu.unit_number,
             ht.name as housing_type_name
      FROM occupiers o
      LEFT JOIN housing_units hu ON o.housing_unit_id = hu.id
      LEFT JOIN housing_types ht ON hu.housing_type_id = ht.id
      WHERE DATE(o.occupancy_start_date, '+1 year') <= DATE('now', '+30 days')
      ORDER BY o.occupancy_start_date
    `;
    const rows = db.prepare(query).all();
    return rows.map(row => new Occupier(row));
  }

  // Get full name
  getFullName() {
    return `${this.first_name} ${this.last_name}`;
  }

  // Get formatted monthly rent
  getFormattedRent() {
    return this.monthly_rent ? `$${parseFloat(this.monthly_rent).toFixed(2)}` : 'N/A';
  }

  // Get formatted occupancy start date
  getFormattedStartDate() {
    if (!this.occupancy_start_date) return 'N/A';
    return new Date(this.occupancy_start_date).toLocaleDateString();
  }

  // Calculate occupancy duration
  getOccupancyDuration() {
    if (!this.occupancy_start_date) return 'N/A';
    const startDate = new Date(this.occupancy_start_date);
    const today = new Date();
    const diffTime = Math.abs(today - startDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays < 30) {
      return `${diffDays} days`;
    } else if (diffDays < 365) {
      const months = Math.floor(diffDays / 30);
      return `${months} month${months > 1 ? 's' : ''}`;
    } else {
      const years = Math.floor(diffDays / 365);
      const remainingMonths = Math.floor((diffDays % 365) / 30);
      return `${years} year${years > 1 ? 's' : ''}${remainingMonths > 0 ? `, ${remainingMonths} month${remainingMonths > 1 ? 's' : ''}` : ''}`;
    }
  }
}

module.exports = Occupier;