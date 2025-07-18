const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const { isAuthenticated, canRead } = require('../config/auth');
const Note = require('../models/Note');
const HousingUnit = require('../models/HousingUnit');
const Occupier = require('../models/Occupier');
const Database = require('../models/database');

// Get all notes
router.get('/', isAuthenticated, canRead, (req, res) => {
  try {
    const { search, category, unit_id, occupier_id } = req.query;
    
    const filters = {};
    if (search) filters.search = search;
    if (category) filters.category = category;
    if (unit_id) filters.housing_unit_id = unit_id;
    if (occupier_id) filters.occupier_id = occupier_id;
    
    const notes = Note.findAll(filters);
    const categories = Note.getCategories();
    
    // Get housing units and occupiers for filters
    const db = Database.getConnection();
    const housingUnits = db.prepare('SELECT id, unit_number FROM housing_units ORDER BY unit_number').all();
    const occupiers = db.prepare('SELECT id, first_name, last_name FROM occupiers ORDER BY last_name, first_name').all();
    
    res.render('notes/index', {
      title: 'Notes',
      notes,
      categories,
      housingUnits,
      occupiers,
      filters: req.query,
      user: req.session.user
    });
  } catch (error) {
    console.error('Notes index error:', error);
    req.flash('error_msg', 'Error loading notes');
    res.redirect('/');
  }
});

// Show note details
router.get('/view/:id', isAuthenticated, canRead, (req, res) => {
  try {
    const note = Note.findById(req.params.id);
    
    if (!note) {
      req.flash('error_msg', 'Note not found');
      return res.redirect('/notes');
    }
    
    const housingUnit = note.getHousingUnit();
    const occupier = note.getOccupier();
    
    res.render('notes/view', {
      title: note.title,
      note,
      housingUnit,
      occupier,
      user: req.session.user
    });
  } catch (error) {
    console.error('Note view error:', error);
    req.flash('error_msg', 'Error loading note details');
    res.redirect('/notes');
  }
});

// Add note form
router.get('/add', isAuthenticated, canRead, (req, res) => {
  try {
    const db = Database.getConnection();
    const housingUnits = db.prepare('SELECT id, unit_number FROM housing_units ORDER BY unit_number').all();
    const occupiers = db.prepare('SELECT id, first_name, last_name FROM occupiers ORDER BY last_name, first_name').all();
    
    const categories = ['General', 'Maintenance', 'Financial', 'Complaint', 'Inspection', 'Administrative'];
    
    res.render('notes/form', {
      title: 'Add Note',
      note: {},
      housingUnits,
      occupiers,
      categories,
      action: 'add',
      user: req.session.user
    });
  } catch (error) {
    console.error('Add note form error:', error);
    req.flash('error_msg', 'Error loading add note form');
    res.redirect('/notes');
  }
});

// Edit note form
router.get('/edit/:id', isAuthenticated, canRead, (req, res) => {
  try {
    const note = Note.findById(req.params.id);
    
    if (!note) {
      req.flash('error_msg', 'Note not found');
      return res.redirect('/notes');
    }
    
    // Check if user can edit this note (only creator or admin)
    if (note.created_by !== req.session.user.id && req.session.user.role_name !== 'Admin') {
      req.flash('error_msg', 'You do not have permission to edit this note');
      return res.redirect('/notes');
    }
    
    const db = Database.getConnection();
    const housingUnits = db.prepare('SELECT id, unit_number FROM housing_units ORDER BY unit_number').all();
    const occupiers = db.prepare('SELECT id, first_name, last_name FROM occupiers ORDER BY last_name, first_name').all();
    
    const categories = ['General', 'Maintenance', 'Financial', 'Complaint', 'Inspection', 'Administrative'];
    
    res.render('notes/form', {
      title: 'Edit Note',
      note,
      housingUnits,
      occupiers,
      categories,
      action: 'edit',
      user: req.session.user
    });
  } catch (error) {
    console.error('Edit note form error:', error);
    req.flash('error_msg', 'Error loading edit note form');
    res.redirect('/notes');
  }
});

// Create note
router.post('/add', isAuthenticated, canRead, [
  body('title').notEmpty().trim().withMessage('Title is required'),
  body('content').notEmpty().trim().withMessage('Content is required'),
  body('category').optional().trim(),
  body('housing_unit_id').optional().isInt().withMessage('Invalid housing unit'),
  body('occupier_id').optional().isInt().withMessage('Invalid occupier')
], async (req, res) => {
  const errors = validationResult(req);
  
  if (!errors.isEmpty()) {
    const db = Database.getConnection();
    const housingUnits = db.prepare('SELECT id, unit_number FROM housing_units ORDER BY unit_number').all();
    const occupiers = db.prepare('SELECT id, first_name, last_name FROM occupiers ORDER BY last_name, first_name').all();
    const categories = ['General', 'Maintenance', 'Financial', 'Complaint', 'Inspection', 'Administrative'];
    
    return res.render('notes/form', {
      title: 'Add Note',
      note: req.body,
      housingUnits,
      occupiers,
      categories,
      action: 'add',
      errors: errors.array(),
      user: req.session.user
    });
  }

  try {
    const noteData = {
      ...req.body,
      created_by: req.session.user.id,
      housing_unit_id: req.body.housing_unit_id || null,
      occupier_id: req.body.occupier_id || null
    };
    
    const note = Note.create(noteData);
    req.flash('success_msg', `Note "${note.title}" created successfully`);
    res.redirect('/notes');
  } catch (error) {
    console.error('Create note error:', error);
    
    const db = Database.getConnection();
    const housingUnits = db.prepare('SELECT id, unit_number FROM housing_units ORDER BY unit_number').all();
    const occupiers = db.prepare('SELECT id, first_name, last_name FROM occupiers ORDER BY last_name, first_name').all();
    const categories = ['General', 'Maintenance', 'Financial', 'Complaint', 'Inspection', 'Administrative'];
    
    res.render('notes/form', {
      title: 'Add Note',
      note: req.body,
      housingUnits,
      occupiers,
      categories,
      action: 'add',
      error: 'Error creating note',
      user: req.session.user
    });
  }
});

// Update note
router.post('/edit/:id', isAuthenticated, canRead, [
  body('title').notEmpty().trim().withMessage('Title is required'),
  body('content').notEmpty().trim().withMessage('Content is required'),
  body('category').optional().trim(),
  body('housing_unit_id').optional().isInt().withMessage('Invalid housing unit'),
  body('occupier_id').optional().isInt().withMessage('Invalid occupier')
], async (req, res) => {
  const errors = validationResult(req);
  
  if (!errors.isEmpty()) {
    const db = Database.getConnection();
    const housingUnits = db.prepare('SELECT id, unit_number FROM housing_units ORDER BY unit_number').all();
    const occupiers = db.prepare('SELECT id, first_name, last_name FROM occupiers ORDER BY last_name, first_name').all();
    const categories = ['General', 'Maintenance', 'Financial', 'Complaint', 'Inspection', 'Administrative'];
    
    return res.render('notes/form', {
      title: 'Edit Note',
      note: { ...req.body, id: req.params.id },
      housingUnits,
      occupiers,
      categories,
      action: 'edit',
      errors: errors.array(),
      user: req.session.user
    });
  }

  try {
    const note = Note.findById(req.params.id);
    
    if (!note) {
      req.flash('error_msg', 'Note not found');
      return res.redirect('/notes');
    }
    
    // Check if user can edit this note (only creator or admin)
    if (note.created_by !== req.session.user.id && req.session.user.role_name !== 'Admin') {
      req.flash('error_msg', 'You do not have permission to edit this note');
      return res.redirect('/notes');
    }
    
    const noteData = {
      ...req.body,
      housing_unit_id: req.body.housing_unit_id || null,
      occupier_id: req.body.occupier_id || null
    };
    
    const updatedNote = note.update(noteData);
    req.flash('success_msg', `Note "${updatedNote.title}" updated successfully`);
    res.redirect('/notes');
  } catch (error) {
    console.error('Update note error:', error);
    
    const db = Database.getConnection();
    const housingUnits = db.prepare('SELECT id, unit_number FROM housing_units ORDER BY unit_number').all();
    const occupiers = db.prepare('SELECT id, first_name, last_name FROM occupiers ORDER BY last_name, first_name').all();
    const categories = ['General', 'Maintenance', 'Financial', 'Complaint', 'Inspection', 'Administrative'];
    
    res.render('notes/form', {
      title: 'Edit Note',
      note: { ...req.body, id: req.params.id },
      housingUnits,
      occupiers,
      categories,
      action: 'edit',
      error: 'Error updating note',
      user: req.session.user
    });
  }
});

// Delete note
router.post('/delete/:id', isAuthenticated, canRead, (req, res) => {
  try {
    const note = Note.findById(req.params.id);
    
    if (!note) {
      req.flash('error_msg', 'Note not found');
      return res.redirect('/notes');
    }
    
    // Check if user can delete this note (only creator or admin)
    if (note.created_by !== req.session.user.id && req.session.user.role_name !== 'Admin') {
      req.flash('error_msg', 'You do not have permission to delete this note');
      return res.redirect('/notes');
    }
    
    const noteTitle = note.title;
    note.delete();
    req.flash('success_msg', `Note "${noteTitle}" deleted successfully`);
    res.redirect('/notes');
  } catch (error) {
    console.error('Delete note error:', error);
    req.flash('error_msg', 'Error deleting note');
    res.redirect('/notes');
  }
});

module.exports = router;