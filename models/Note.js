const Database = require('./database');

class Note {
  constructor(data) {
    this.id = data.id;
    this.title = data.title;
    this.content = data.content;
    this.category = data.category;
    this.housing_unit_id = data.housing_unit_id;
    this.occupier_id = data.occupier_id;
    this.created_by = data.created_by;
    this.created_at = data.created_at;
    this.created_by_name = data.created_by_name;
    this.unit_number = data.unit_number;
    this.occupier_name = data.occupier_name;
  }

  static create(noteData) {
    const db = Database.getConnection();
    
    const stmt = db.prepare(`
      INSERT INTO notes (
        title, content, category, housing_unit_id, occupier_id, created_by
      )
      VALUES (?, ?, ?, ?, ?, ?)
    `);
    
    const result = stmt.run(
      noteData.title,
      noteData.content,
      noteData.category,
      noteData.housing_unit_id || null,
      noteData.occupier_id || null,
      noteData.created_by
    );
    
    return this.findById(result.lastInsertRowid);
  }

  static findById(id) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT 
        n.*,
        u.first_name || ' ' || u.last_name as created_by_name,
        hu.unit_number,
        o.first_name || ' ' || o.last_name as occupier_name
      FROM notes n
      JOIN users u ON n.created_by = u.id
      LEFT JOIN housing_units hu ON n.housing_unit_id = hu.id
      LEFT JOIN occupiers o ON n.occupier_id = o.id
      WHERE n.id = ?
    `);
    
    const row = stmt.get(id);
    return row ? new Note(row) : null;
  }

  static findAll(filters = {}) {
    const db = Database.getConnection();
    let query = `
      SELECT 
        n.*,
        u.first_name || ' ' || u.last_name as created_by_name,
        hu.unit_number,
        o.first_name || ' ' || o.last_name as occupier_name
      FROM notes n
      JOIN users u ON n.created_by = u.id
      LEFT JOIN housing_units hu ON n.housing_unit_id = hu.id
      LEFT JOIN occupiers o ON n.occupier_id = o.id
    `;
    
    const conditions = [];
    const params = [];
    
    if (filters.category) {
      conditions.push('n.category = ?');
      params.push(filters.category);
    }
    
    if (filters.housing_unit_id) {
      conditions.push('n.housing_unit_id = ?');
      params.push(filters.housing_unit_id);
    }
    
    if (filters.occupier_id) {
      conditions.push('n.occupier_id = ?');
      params.push(filters.occupier_id);
    }
    
    if (filters.created_by) {
      conditions.push('n.created_by = ?');
      params.push(filters.created_by);
    }
    
    if (filters.search) {
      conditions.push('(n.title LIKE ? OR n.content LIKE ? OR n.category LIKE ?)');
      params.push(`%${filters.search}%`, `%${filters.search}%`, `%${filters.search}%`);
    }
    
    if (conditions.length > 0) {
      query += ' WHERE ' + conditions.join(' AND ');
    }
    
    query += ' ORDER BY n.created_at DESC';
    
    if (filters.limit) {
      query += ' LIMIT ?';
      params.push(filters.limit);
    }
    
    const stmt = db.prepare(query);
    const rows = stmt.all(...params);
    return rows.map(row => new Note(row));
  }

  static findByUnit(unitId) {
    return this.findAll({ housing_unit_id: unitId });
  }

  static findByOccupier(occupierId) {
    return this.findAll({ occupier_id: occupierId });
  }

  static findByCategory(category) {
    return this.findAll({ category });
  }

  static findRecent(limit = 10) {
    return this.findAll({ limit });
  }

  static getCategories() {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT DISTINCT category
      FROM notes
      WHERE category IS NOT NULL
      ORDER BY category
    `);
    
    return stmt.all().map(row => row.category);
  }

  static getCategoryStats() {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT 
        category,
        COUNT(*) as count
      FROM notes
      WHERE category IS NOT NULL
      GROUP BY category
      ORDER BY count DESC
    `);
    
    return stmt.all();
  }

  static getRecentActivity(limit = 10) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT 
        n.*,
        u.first_name || ' ' || u.last_name as created_by_name,
        hu.unit_number,
        o.first_name || ' ' || o.last_name as occupier_name
      FROM notes n
      JOIN users u ON n.created_by = u.id
      LEFT JOIN housing_units hu ON n.housing_unit_id = hu.id
      LEFT JOIN occupiers o ON n.occupier_id = o.id
      ORDER BY n.created_at DESC
      LIMIT ?
    `);
    
    const rows = stmt.all(limit);
    return rows.map(row => new Note(row));
  }

  update(noteData) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      UPDATE notes
      SET 
        title = ?, content = ?, category = ?, housing_unit_id = ?, occupier_id = ?
      WHERE id = ?
    `);
    
    stmt.run(
      noteData.title,
      noteData.content,
      noteData.category,
      noteData.housing_unit_id || null,
      noteData.occupier_id || null,
      this.id
    );
    
    return Note.findById(this.id);
  }

  delete() {
    const db = Database.getConnection();
    const stmt = db.prepare('DELETE FROM notes WHERE id = ?');
    stmt.run(this.id);
  }

  getHousingUnit() {
    if (!this.housing_unit_id) return null;
    
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

  getOccupier() {
    if (!this.occupier_id) return null;
    
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT 
        o.*,
        hu.unit_number
      FROM occupiers o
      JOIN housing_units hu ON o.housing_unit_id = hu.id
      WHERE o.id = ?
    `);
    
    return stmt.get(this.occupier_id);
  }

  getCreatedBy() {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT * FROM users WHERE id = ?
    `);
    
    return stmt.get(this.created_by);
  }

  getFormattedDate() {
    if (!this.created_at) return 'N/A';
    
    const date = new Date(this.created_at);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
  }

  getShortContent(maxLength = 100) {
    if (!this.content) return '';
    
    if (this.content.length <= maxLength) {
      return this.content;
    }
    
    return this.content.substring(0, maxLength) + '...';
  }

  getCategoryBadgeClass() {
    switch (this.category) {
      case 'General': return 'primary';
      case 'Maintenance': return 'warning';
      case 'Financial': return 'success';
      case 'Complaint': return 'danger';
      case 'Inspection': return 'info';
      default: return 'secondary';
    }
  }

  getRelatedEntity() {
    if (this.unit_number) {
      return {
        type: 'Unit',
        identifier: this.unit_number,
        id: this.housing_unit_id
      };
    }
    
    if (this.occupier_name) {
      return {
        type: 'Occupier',
        identifier: this.occupier_name,
        id: this.occupier_id
      };
    }
    
    return null;
  }
}

module.exports = Note;