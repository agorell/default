const bcrypt = require('bcrypt');
const Database = require('./database');

class User {
  constructor(data) {
    this.id = data.id;
    this.username = data.username;
    this.email = data.email;
    this.password_hash = data.password_hash;
    this.first_name = data.first_name;
    this.last_name = data.last_name;
    this.role_id = data.role_id;
    this.role_name = data.role_name;
    this.is_active = data.is_active;
    this.created_at = data.created_at;
    this.updated_at = data.updated_at;
  }

  static async create(userData) {
    const db = Database.getConnection();
    const hashedPassword = await bcrypt.hash(userData.password, 10);
    
    const stmt = db.prepare(`
      INSERT INTO users (username, email, password_hash, first_name, last_name, role_id)
      VALUES (?, ?, ?, ?, ?, ?)
    `);
    
    const result = stmt.run(
      userData.username,
      userData.email,
      hashedPassword,
      userData.first_name,
      userData.last_name,
      userData.role_id
    );
    
    return this.findById(result.lastInsertRowid);
  }

  static findById(id) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT u.*, r.name as role_name
      FROM users u
      JOIN roles r ON u.role_id = r.id
      WHERE u.id = ?
    `);
    
    const row = stmt.get(id);
    return row ? new User(row) : null;
  }

  static findByUsername(username) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT u.*, r.name as role_name
      FROM users u
      JOIN roles r ON u.role_id = r.id
      WHERE u.username = ?
    `);
    
    const row = stmt.get(username);
    return row ? new User(row) : null;
  }

  static findByEmail(email) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT u.*, r.name as role_name
      FROM users u
      JOIN roles r ON u.role_id = r.id
      WHERE u.email = ?
    `);
    
    const row = stmt.get(email);
    return row ? new User(row) : null;
  }

  static findAll() {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      SELECT u.*, r.name as role_name
      FROM users u
      JOIN roles r ON u.role_id = r.id
      ORDER BY u.created_at DESC
    `);
    
    const rows = stmt.all();
    return rows.map(row => new User(row));
  }

  static async authenticate(username, password) {
    const user = this.findByUsername(username);
    if (!user || !user.is_active) {
      return null;
    }
    
    const isValid = await bcrypt.compare(password, user.password_hash);
    return isValid ? user : null;
  }

  update(userData) {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      UPDATE users
      SET first_name = ?, last_name = ?, email = ?, role_id = ?, updated_at = CURRENT_TIMESTAMP
      WHERE id = ?
    `);
    
    stmt.run(
      userData.first_name,
      userData.last_name,
      userData.email,
      userData.role_id,
      this.id
    );
    
    return User.findById(this.id);
  }

  async updatePassword(newPassword) {
    const db = Database.getConnection();
    const hashedPassword = await bcrypt.hash(newPassword, 10);
    
    const stmt = db.prepare(`
      UPDATE users
      SET password_hash = ?, updated_at = CURRENT_TIMESTAMP
      WHERE id = ?
    `);
    
    stmt.run(hashedPassword, this.id);
  }

  deactivate() {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      UPDATE users
      SET is_active = 0, updated_at = CURRENT_TIMESTAMP
      WHERE id = ?
    `);
    
    stmt.run(this.id);
  }

  activate() {
    const db = Database.getConnection();
    const stmt = db.prepare(`
      UPDATE users
      SET is_active = 1, updated_at = CURRENT_TIMESTAMP
      WHERE id = ?
    `);
    
    stmt.run(this.id);
  }

  getFullName() {
    return `${this.first_name} ${this.last_name}`;
  }

  hasRole(roleName) {
    return this.role_name === roleName;
  }

  canManageUsers() {
    return this.role_name === 'Admin';
  }

  canManageUnits() {
    return ['Admin', 'Manager'].includes(this.role_name);
  }

  canViewReports() {
    return ['Admin', 'Manager', 'Viewer'].includes(this.role_name);
  }
}

module.exports = User;