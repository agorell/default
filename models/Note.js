const { db } = require('../config/database');

class Note {
  constructor(noteData) {
    this.id = noteData.id;
    this.title = noteData.title;
    this.content = noteData.content;
    this.category = noteData.category;
    this.housing_unit_id = noteData.housing_unit_id;
    this.occupier_id = noteData.occupier_id;
    this.created_by = noteData.created_by;
    this.created_at = noteData.created_at;
    this.unit_number = noteData.unit_number;
    this.occupier_name = noteData.occupier_name;
    this.created_by_name = noteData.created_by_name;
  }

  // Get all notes
  static findAll(filters = {}) {
    let query = `
      SELECT n.*, 
             hu.unit_number,
             CASE 
               WHEN o.id IS NOT NULL THEN o.first_name || ' ' || o.last_name
               ELSE NULL 
             END as occupier_name,
             u.first_name || ' ' || u.last_name as created_by_name
      FROM notes n
      LEFT JOIN housing_units hu ON n.housing_unit_id = hu.id
      LEFT JOIN occupiers o ON n.occupier_id = o.id
      LEFT JOIN users u ON n.created_by = u.id
      WHERE 1=1
    `;
    
    const params = [];
    
    if (filters.category) {
      query += ' AND n.category = ?';
      params.push(filters.category);
    }
    
    if (filters.housing_unit_id) {
      query += ' AND n.housing_unit_id = ?';
      params.push(filters.housing_unit_id);
    }
    
    if (filters.occupier_id) {
      query += ' AND n.occupier_id = ?';
      params.push(filters.occupier_id);
    }
    
    if (filters.search) {
      query += ' AND (n.title LIKE ? OR n.content LIKE ?)';
      const searchTerm = `%${filters.search}%`;
      params.push(searchTerm, searchTerm);
    }
    
    query += ' ORDER BY n.created_at DESC';
    
    if (filters.limit) {
      query += ' LIMIT ?';
      params.push(filters.limit);
    }
    
    const rows = db.prepare(query).all(...params);
    return rows.map(row => new Note(row));
  }

  // Find note by ID
  static findById(id) {
    const query = `
      SELECT n.*, 
             hu.unit_number,
             CASE 
               WHEN o.id IS NOT NULL THEN o.first_name || ' ' || o.last_name
               ELSE NULL 
             END as occupier_name,
             u.first_name || ' ' || u.last_name as created_by_name
      FROM notes n
      LEFT JOIN housing_units hu ON n.housing_unit_id = hu.id
      LEFT JOIN occupiers o ON n.occupier_id = o.id
      LEFT JOIN users u ON n.created_by = u.id
      WHERE n.id = ?
    `;
    const row = db.prepare(query).get(id);
    return row ? new Note(row) : null;
  }

  // Find notes by housing unit ID
  static findByHousingUnitId(housingUnitId) {
    return this.findAll({ housing_unit_id: housingUnitId });
  }

  // Find notes by occupier ID
  static findByOccupierId(occupierId) {
    return this.findAll({ occupier_id: occupierId });
  }

  // Create new note
  static create(noteData) {
    const query = `
      INSERT INTO notes (
        title, content, category, housing_unit_id, occupier_id, created_by
      ) VALUES (?, ?, ?, ?, ?, ?)
    `;
    const result = db.prepare(query).run(
      noteData.title,
      noteData.content,
      noteData.category,
      noteData.housing_unit_id || null,
      noteData.occupier_id || null,
      noteData.created_by
    );
    
    return this.findById(result.lastInsertRowid);
  }

  // Update note
  static update(id, noteData) {
    const updates = [];
    const values = [];
    
    if (noteData.title) {
      updates.push('title = ?');
      values.push(noteData.title);
    }
    if (noteData.content) {
      updates.push('content = ?');
      values.push(noteData.content);
    }
    if (noteData.category) {
      updates.push('category = ?');
      values.push(noteData.category);
    }
    if (noteData.housing_unit_id !== undefined) {
      updates.push('housing_unit_id = ?');
      values.push(noteData.housing_unit_id || null);
    }
    if (noteData.occupier_id !== undefined) {
      updates.push('occupier_id = ?');
      values.push(noteData.occupier_id || null);
    }
    
    if (updates.length === 0) return false;
    
    values.push(id);
    
    const query = `UPDATE notes SET ${updates.join(', ')} WHERE id = ?`;
    db.prepare(query).run(...values);
    
    return this.findById(id);
  }

  // Delete note
  static delete(id) {
    const query = 'DELETE FROM notes WHERE id = ?';
    const result = db.prepare(query).run(id);
    return result.changes > 0;
  }

  // Get note categories
  static getCategories() {
    return [
      'General',
      'Maintenance',
      'Financial',
      'Administrative',
      'Emergency',
      'Lease',
      'Inspection',
      'Complaint'
    ];
  }

  // Get recent notes
  static getRecentNotes(limit = 10) {
    return this.findAll({ limit: limit });
  }

  // Get notes by category
  static getNotesByCategory(category) {
    return this.findAll({ category: category });
  }

  // Get note statistics
  static getNoteStats() {
    const query = `
      SELECT 
        COUNT(*) as total_notes,
        COUNT(CASE WHEN category = 'General' THEN 1 END) as general_notes,
        COUNT(CASE WHEN category = 'Maintenance' THEN 1 END) as maintenance_notes,
        COUNT(CASE WHEN category = 'Financial' THEN 1 END) as financial_notes,
        COUNT(CASE WHEN category = 'Administrative' THEN 1 END) as administrative_notes,
        COUNT(CASE WHEN category = 'Emergency' THEN 1 END) as emergency_notes,
        COUNT(CASE WHEN category = 'Lease' THEN 1 END) as lease_notes,
        COUNT(CASE WHEN category = 'Inspection' THEN 1 END) as inspection_notes,
        COUNT(CASE WHEN category = 'Complaint' THEN 1 END) as complaint_notes
      FROM notes
    `;
    return db.prepare(query).get();
  }

  // Search notes
  static searchNotes(searchTerm, category = null) {
    const filters = { search: searchTerm };
    if (category) {
      filters.category = category;
    }
    return this.findAll(filters);
  }

  // Get formatted created date
  getFormattedCreatedDate() {
    if (!this.created_at) return 'N/A';
    return new Date(this.created_at).toLocaleDateString();
  }

  // Get formatted created datetime
  getFormattedCreatedDateTime() {
    if (!this.created_at) return 'N/A';
    return new Date(this.created_at).toLocaleString();
  }

  // Get truncated content
  getTruncatedContent(length = 100) {
    if (!this.content) return '';
    return this.content.length > length ? 
      this.content.substring(0, length) + '...' : 
      this.content;
  }

  // Get category badge class
  getCategoryBadgeClass() {
    const categoryClasses = {
      'General': 'bg-secondary',
      'Maintenance': 'bg-warning',
      'Financial': 'bg-success',
      'Administrative': 'bg-info',
      'Emergency': 'bg-danger',
      'Lease': 'bg-primary',
      'Inspection': 'bg-dark',
      'Complaint': 'bg-danger'
    };
    return categoryClasses[this.category] || 'bg-secondary';
  }

  // Get related entity info
  getRelatedEntityInfo() {
    if (this.unit_number && this.occupier_name) {
      return `Unit ${this.unit_number} (${this.occupier_name})`;
    } else if (this.unit_number) {
      return `Unit ${this.unit_number}`;
    } else if (this.occupier_name) {
      return this.occupier_name;
    }
    return 'General';
  }
}

module.exports = Note;