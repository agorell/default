const Database = require('better-sqlite3');
const path = require('path');

const dbPath = path.join(__dirname, '..', 'database.sqlite');
const db = new Database(dbPath);

// Enable foreign keys
db.pragma('foreign_keys = ON');

// Database configuration
const dbConfig = {
  filename: dbPath,
  options: {
    verbose: console.log,
    fileMustExist: false
  }
};

module.exports = {
  db,
  dbConfig
};