const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const { isAuthenticated, canManageUnits, canRead } = require('../config/auth');
const HousingUnit = require('../models/HousingUnit');
const Database = require('../models/database');

// Get all housing units
router.get('/', isAuthenticated, canRead, (req, res) => {
  try {
    const { search, type, status, condition } = req.query;
    
    const filters = {};
    if (search) filters.search = search;
    if (type) filters.housing_type_id = type;
    if (status !== undefined) filters.is_occupied = status === 'occupied' ? 1 : 0;
    if (condition) filters.condition_grade = condition;
    
    const units = HousingUnit.findAll(filters);
    
    // Get housing types for filter
    const db = Database.getConnection();
    const housingTypes = db.prepare('SELECT * FROM housing_types ORDER BY name').all();
    
    res.render('units/index', {
      title: 'Housing Units',
      units,
      housingTypes,
      filters: req.query,
      user: req.session.user
    });
  } catch (error) {
    console.error('Units index error:', error);
    req.flash('error_msg', 'Error loading housing units');
    res.redirect('/');
  }
});

// Show unit details
router.get('/view/:id', isAuthenticated, canRead, (req, res) => {
  try {
    const unit = HousingUnit.findById(req.params.id);
    
    if (!unit) {
      req.flash('error_msg', 'Housing unit not found');
      return res.redirect('/units');
    }
    
    const occupier = unit.getOccupier();
    const notes = unit.getNotes();
    
    res.render('units/view', {
      title: `Unit ${unit.unit_number}`,
      unit,
      occupier,
      notes,
      user: req.session.user
    });
  } catch (error) {
    console.error('Unit view error:', error);
    req.flash('error_msg', 'Error loading unit details');
    res.redirect('/units');
  }
});

// Add unit form
router.get('/add', isAuthenticated, canManageUnits, (req, res) => {
  try {
    const db = Database.getConnection();
    const housingTypes = db.prepare('SELECT * FROM housing_types ORDER BY name').all();
    
    res.render('units/form', {
      title: 'Add Housing Unit',
      unit: {},
      housingTypes,
      action: 'add',
      user: req.session.user
    });
  } catch (error) {
    console.error('Add unit form error:', error);
    req.flash('error_msg', 'Error loading add unit form');
    res.redirect('/units');
  }
});

// Edit unit form
router.get('/edit/:id', isAuthenticated, canManageUnits, (req, res) => {
  try {
    const unit = HousingUnit.findById(req.params.id);
    
    if (!unit) {
      req.flash('error_msg', 'Housing unit not found');
      return res.redirect('/units');
    }
    
    const db = Database.getConnection();
    const housingTypes = db.prepare('SELECT * FROM housing_types ORDER BY name').all();
    
    res.render('units/form', {
      title: 'Edit Housing Unit',
      unit,
      housingTypes,
      action: 'edit',
      user: req.session.user
    });
  } catch (error) {
    console.error('Edit unit form error:', error);
    req.flash('error_msg', 'Error loading edit unit form');
    res.redirect('/units');
  }
});

// Create unit
router.post('/add', isAuthenticated, canManageUnits, [
  body('unit_number').notEmpty().trim().withMessage('Unit number is required'),
  body('housing_type_id').isInt().withMessage('Housing type is required'),
  body('bedrooms').optional().isInt({ min: 0 }).withMessage('Bedrooms must be a non-negative integer'),
  body('bathrooms').optional().isFloat({ min: 0 }).withMessage('Bathrooms must be a non-negative number'),
  body('square_footage').optional().isInt({ min: 0 }).withMessage('Square footage must be a non-negative integer'),
  body('rental_rate').optional().isFloat({ min: 0 }).withMessage('Rental rate must be a non-negative number'),
  body('condition_grade').optional().isIn(['Excellent', 'Good', 'Fair', 'Poor']).withMessage('Invalid condition grade')
], async (req, res) => {
  const errors = validationResult(req);
  
  if (!errors.isEmpty()) {
    const db = Database.getConnection();
    const housingTypes = db.prepare('SELECT * FROM housing_types ORDER BY name').all();
    
    return res.render('units/form', {
      title: 'Add Housing Unit',
      unit: req.body,
      housingTypes,
      action: 'add',
      errors: errors.array(),
      user: req.session.user
    });
  }

  try {
    const unit = HousingUnit.create(req.body);
    req.flash('success_msg', `Housing unit ${unit.unit_number} created successfully`);
    res.redirect('/units');
  } catch (error) {
    console.error('Create unit error:', error);
    
    const db = Database.getConnection();
    const housingTypes = db.prepare('SELECT * FROM housing_types ORDER BY name').all();
    
    res.render('units/form', {
      title: 'Add Housing Unit',
      unit: req.body,
      housingTypes,
      action: 'add',
      error: 'Error creating housing unit. Unit number may already exist.',
      user: req.session.user
    });
  }
});

// Update unit
router.post('/edit/:id', isAuthenticated, canManageUnits, [
  body('unit_number').notEmpty().trim().withMessage('Unit number is required'),
  body('housing_type_id').isInt().withMessage('Housing type is required'),
  body('bedrooms').optional().isInt({ min: 0 }).withMessage('Bedrooms must be a non-negative integer'),
  body('bathrooms').optional().isFloat({ min: 0 }).withMessage('Bathrooms must be a non-negative number'),
  body('square_footage').optional().isInt({ min: 0 }).withMessage('Square footage must be a non-negative integer'),
  body('rental_rate').optional().isFloat({ min: 0 }).withMessage('Rental rate must be a non-negative number'),
  body('condition_grade').optional().isIn(['Excellent', 'Good', 'Fair', 'Poor']).withMessage('Invalid condition grade')
], async (req, res) => {
  const errors = validationResult(req);
  
  if (!errors.isEmpty()) {
    const db = Database.getConnection();
    const housingTypes = db.prepare('SELECT * FROM housing_types ORDER BY name').all();
    
    return res.render('units/form', {
      title: 'Edit Housing Unit',
      unit: { ...req.body, id: req.params.id },
      housingTypes,
      action: 'edit',
      errors: errors.array(),
      user: req.session.user
    });
  }

  try {
    const unit = HousingUnit.findById(req.params.id);
    
    if (!unit) {
      req.flash('error_msg', 'Housing unit not found');
      return res.redirect('/units');
    }
    
    const updatedUnit = unit.update(req.body);
    req.flash('success_msg', `Housing unit ${updatedUnit.unit_number} updated successfully`);
    res.redirect('/units');
  } catch (error) {
    console.error('Update unit error:', error);
    
    const db = Database.getConnection();
    const housingTypes = db.prepare('SELECT * FROM housing_types ORDER BY name').all();
    
    res.render('units/form', {
      title: 'Edit Housing Unit',
      unit: { ...req.body, id: req.params.id },
      housingTypes,
      action: 'edit',
      error: 'Error updating housing unit. Unit number may already exist.',
      user: req.session.user
    });
  }
});

// Delete unit
router.post('/delete/:id', isAuthenticated, canManageUnits, (req, res) => {
  try {
    const unit = HousingUnit.findById(req.params.id);
    
    if (!unit) {
      req.flash('error_msg', 'Housing unit not found');
      return res.redirect('/units');
    }
    
    // Check if unit is occupied
    if (unit.is_occupied) {
      req.flash('error_msg', 'Cannot delete occupied housing unit');
      return res.redirect('/units');
    }
    
    unit.delete();
    req.flash('success_msg', `Housing unit ${unit.unit_number} deleted successfully`);
    res.redirect('/units');
  } catch (error) {
    console.error('Delete unit error:', error);
    req.flash('error_msg', 'Error deleting housing unit');
    res.redirect('/units');
  }
});

module.exports = router;