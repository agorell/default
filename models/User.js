const { db } = require('../config/database');
const bcrypt = require('bcrypt');

class User {
  constructor(userData) {
    this.id = userData.id;
    this.username = userData.username;
    this.email = userData.email;
    this.password_hash = userData.password_hash;
    this.first_name = userData.first_name;
    this.last_name = userData.last_name;
    this.role_id = userData.role_id;
    this.is_active = userData.is_active;
    this.created_at = userData.created_at;
    this.updated_at = userData.updated_at;
  }

  // Get all users with their roles
  static findAll() {
    const query = `
      SELECT u.*, r.name as role_name 
      FROM users u 
      LEFT JOIN roles r ON u.role_id = r.id 
      ORDER BY u.created_at DESC
    `;
    const rows = db.prepare(query).all();
    return rows.map(row => new User(row));
  }

  // Find user by ID
  static findById(id) {
    const query = `
      SELECT u.*, r.name as role_name 
      FROM users u 
      LEFT JOIN roles r ON u.role_id = r.id 
      WHERE u.id = ?
    `;
    const row = db.prepare(query).get(id);
    return row ? new User(row) : null;
  }

  // Find user by username
  static findByUsername(username) {
    const query = `
      SELECT u.*, r.name as role_name 
      FROM users u 
      LEFT JOIN roles r ON u.role_id = r.id 
      WHERE u.username = ?
    `;
    const row = db.prepare(query).get(username);
    return row ? new User(row) : null;
  }

  // Find user by email
  static findByEmail(email) {
    const query = `
      SELECT u.*, r.name as role_name 
      FROM users u 
      LEFT JOIN roles r ON u.role_id = r.id 
      WHERE u.email = ?
    `;
    const row = db.prepare(query).get(email);
    return row ? new User(row) : null;
  }

  // Create new user
  static create(userData) {
    const hashedPassword = bcrypt.hashSync(userData.password, 10);
    const query = `
      INSERT INTO users (username, email, password_hash, first_name, last_name, role_id, is_active)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `;
    const result = db.prepare(query).run(
      userData.username,
      userData.email,
      hashedPassword,
      userData.first_name,
      userData.last_name,
      userData.role_id,
      userData.is_active !== undefined ? userData.is_active : 1
    );
    
    return this.findById(result.lastInsertRowid);
  }

  // Update user
  static update(id, userData) {
    const updates = [];
    const values = [];
    
    if (userData.username) {
      updates.push('username = ?');
      values.push(userData.username);
    }
    if (userData.email) {
      updates.push('email = ?');
      values.push(userData.email);
    }
    if (userData.password) {
      updates.push('password_hash = ?');
      values.push(bcrypt.hashSync(userData.password, 10));
    }
    if (userData.first_name) {
      updates.push('first_name = ?');
      values.push(userData.first_name);
    }
    if (userData.last_name) {
      updates.push('last_name = ?');
      values.push(userData.last_name);
    }
    if (userData.role_id) {
      updates.push('role_id = ?');
      values.push(userData.role_id);
    }
    if (userData.is_active !== undefined) {
      updates.push('is_active = ?');
      values.push(userData.is_active);
    }
    
    if (updates.length === 0) return false;
    
    updates.push('updated_at = CURRENT_TIMESTAMP');
    values.push(id);
    
    const query = `UPDATE users SET ${updates.join(', ')} WHERE id = ?`;
    db.prepare(query).run(...values);
    
    return this.findById(id);
  }

  // Delete user (soft delete)
  static delete(id) {
    const query = 'UPDATE users SET is_active = 0 WHERE id = ?';
    const result = db.prepare(query).run(id);
    return result.changes > 0;
  }

  // Verify password
  static verifyPassword(plainPassword, hashedPassword) {
    return bcrypt.compareSync(plainPassword, hashedPassword);
  }

  // Get all roles
  static getRoles() {
    const query = 'SELECT * FROM roles ORDER BY name';
    return db.prepare(query).all();
  }

  // Get user's full name
  getFullName() {
    return `${this.first_name} ${this.last_name}`;
  }

  // Check if user is active
  isActive() {
    return this.is_active === 1;
  }
}

module.exports = User;