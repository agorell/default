const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const { isAuthenticated, canManageUnits, canRead } = require('../config/auth');
const Occupier = require('../models/Occupier');
const HousingUnit = require('../models/HousingUnit');
const Database = require('../models/database');

// Get all occupiers
router.get('/', isAuthenticated, canRead, (req, res) => {
  try {
    const { search, type } = req.query;
    
    const filters = {};
    if (search) filters.search = search;
    if (type) filters.housing_type_id = type;
    
    const occupiers = Occupier.findAll(filters);
    
    // Get housing types for filter
    const db = Database.getConnection();
    const housingTypes = db.prepare('SELECT * FROM housing_types ORDER BY name').all();
    
    res.render('occupiers/index', {
      title: 'Occupiers',
      occupiers,
      housingTypes,
      filters: req.query,
      user: req.session.user
    });
  } catch (error) {
    console.error('Occupiers index error:', error);
    req.flash('error_msg', 'Error loading occupiers');
    res.redirect('/');
  }
});

// Show occupier details
router.get('/view/:id', isAuthenticated, canRead, (req, res) => {
  try {
    const occupier = Occupier.findById(req.params.id);
    
    if (!occupier) {
      req.flash('error_msg', 'Occupier not found');
      return res.redirect('/occupiers');
    }
    
    const housingUnit = occupier.getHousingUnit();
    const notes = occupier.getNotes();
    
    res.render('occupiers/view', {
      title: `${occupier.getFullName()}`,
      occupier,
      housingUnit,
      notes,
      user: req.session.user
    });
  } catch (error) {
    console.error('Occupier view error:', error);
    req.flash('error_msg', 'Error loading occupier details');
    res.redirect('/occupiers');
  }
});

// Add occupier form
router.get('/add', isAuthenticated, canManageUnits, (req, res) => {
  try {
    const vacantUnits = HousingUnit.findVacant();
    
    if (vacantUnits.length === 0) {
      req.flash('error_msg', 'No vacant units available');
      return res.redirect('/occupiers');
    }
    
    res.render('occupiers/form', {
      title: 'Add Occupier',
      occupier: {},
      vacantUnits,
      action: 'add',
      user: req.session.user
    });
  } catch (error) {
    console.error('Add occupier form error:', error);
    req.flash('error_msg', 'Error loading add occupier form');
    res.redirect('/occupiers');
  }
});

// Edit occupier form
router.get('/edit/:id', isAuthenticated, canManageUnits, (req, res) => {
  try {
    const occupier = Occupier.findById(req.params.id);
    
    if (!occupier) {
      req.flash('error_msg', 'Occupier not found');
      return res.redirect('/occupiers');
    }
    
    // Get vacant units plus the current unit
    const vacantUnits = HousingUnit.findVacant();
    const currentUnit = HousingUnit.findById(occupier.housing_unit_id);
    if (currentUnit) {
      vacantUnits.push(currentUnit);
    }
    
    res.render('occupiers/form', {
      title: 'Edit Occupier',
      occupier,
      vacantUnits,
      action: 'edit',
      user: req.session.user
    });
  } catch (error) {
    console.error('Edit occupier form error:', error);
    req.flash('error_msg', 'Error loading edit occupier form');
    res.redirect('/occupiers');
  }
});

// Create occupier
router.post('/add', isAuthenticated, canManageUnits, [
  body('housing_unit_id').isInt().withMessage('Housing unit is required'),
  body('first_name').notEmpty().trim().withMessage('First name is required'),
  body('last_name').notEmpty().trim().withMessage('Last name is required'),
  body('phone').optional().trim(),
  body('email').optional().isEmail().withMessage('Valid email is required'),
  body('occupancy_start_date').notEmpty().withMessage('Occupancy start date is required'),
  body('monthly_rent').optional().isFloat({ min: 0 }).withMessage('Monthly rent must be a non-negative number'),
  body('emergency_contact_name').optional().trim(),
  body('emergency_contact_phone').optional().trim()
], async (req, res) => {
  const errors = validationResult(req);
  
  if (!errors.isEmpty()) {
    const vacantUnits = HousingUnit.findVacant();
    
    return res.render('occupiers/form', {
      title: 'Add Occupier',
      occupier: req.body,
      vacantUnits,
      action: 'add',
      errors: errors.array(),
      user: req.session.user
    });
  }

  try {
    // Check if unit is actually vacant
    const unit = HousingUnit.findById(req.body.housing_unit_id);
    if (!unit) {
      throw new Error('Housing unit not found');
    }
    
    if (unit.is_occupied) {
      throw new Error('Housing unit is already occupied');
    }
    
    const occupier = Occupier.create(req.body);
    req.flash('success_msg', `Occupier ${occupier.getFullName()} added successfully`);
    res.redirect('/occupiers');
  } catch (error) {
    console.error('Create occupier error:', error);
    
    const vacantUnits = HousingUnit.findVacant();
    
    res.render('occupiers/form', {
      title: 'Add Occupier',
      occupier: req.body,
      vacantUnits,
      action: 'add',
      error: error.message || 'Error creating occupier',
      user: req.session.user
    });
  }
});

// Update occupier
router.post('/edit/:id', isAuthenticated, canManageUnits, [
  body('housing_unit_id').isInt().withMessage('Housing unit is required'),
  body('first_name').notEmpty().trim().withMessage('First name is required'),
  body('last_name').notEmpty().trim().withMessage('Last name is required'),
  body('phone').optional().trim(),
  body('email').optional().isEmail().withMessage('Valid email is required'),
  body('occupancy_start_date').notEmpty().withMessage('Occupancy start date is required'),
  body('monthly_rent').optional().isFloat({ min: 0 }).withMessage('Monthly rent must be a non-negative number'),
  body('emergency_contact_name').optional().trim(),
  body('emergency_contact_phone').optional().trim()
], async (req, res) => {
  const errors = validationResult(req);
  
  if (!errors.isEmpty()) {
    const vacantUnits = HousingUnit.findVacant();
    const currentUnit = HousingUnit.findById(req.body.housing_unit_id);
    if (currentUnit) {
      vacantUnits.push(currentUnit);
    }
    
    return res.render('occupiers/form', {
      title: 'Edit Occupier',
      occupier: { ...req.body, id: req.params.id },
      vacantUnits,
      action: 'edit',
      errors: errors.array(),
      user: req.session.user
    });
  }

  try {
    const occupier = Occupier.findById(req.params.id);
    
    if (!occupier) {
      req.flash('error_msg', 'Occupier not found');
      return res.redirect('/occupiers');
    }
    
    // If changing units, check if new unit is vacant
    if (req.body.housing_unit_id != occupier.housing_unit_id) {
      const newUnit = HousingUnit.findById(req.body.housing_unit_id);
      if (!newUnit) {
        throw new Error('New housing unit not found');
      }
      
      if (newUnit.is_occupied) {
        throw new Error('New housing unit is already occupied');
      }
      
      // Mark old unit as vacant
      const oldUnit = HousingUnit.findById(occupier.housing_unit_id);
      if (oldUnit) {
        oldUnit.setOccupancyStatus(false);
      }
      
      // Mark new unit as occupied
      newUnit.setOccupancyStatus(true);
    }
    
    const updatedOccupier = occupier.update(req.body);
    req.flash('success_msg', `Occupier ${updatedOccupier.getFullName()} updated successfully`);
    res.redirect('/occupiers');
  } catch (error) {
    console.error('Update occupier error:', error);
    
    const vacantUnits = HousingUnit.findVacant();
    const currentUnit = HousingUnit.findById(req.body.housing_unit_id);
    if (currentUnit) {
      vacantUnits.push(currentUnit);
    }
    
    res.render('occupiers/form', {
      title: 'Edit Occupier',
      occupier: { ...req.body, id: req.params.id },
      vacantUnits,
      action: 'edit',
      error: error.message || 'Error updating occupier',
      user: req.session.user
    });
  }
});

// Move out occupier
router.post('/move-out/:id', isAuthenticated, canManageUnits, (req, res) => {
  try {
    const occupier = Occupier.findById(req.params.id);
    
    if (!occupier) {
      req.flash('error_msg', 'Occupier not found');
      return res.redirect('/occupiers');
    }
    
    const occupierName = occupier.getFullName();
    const unitNumber = occupier.unit_number;
    
    occupier.moveOut();
    req.flash('success_msg', `${occupierName} moved out from unit ${unitNumber} successfully`);
    res.redirect('/occupiers');
  } catch (error) {
    console.error('Move out occupier error:', error);
    req.flash('error_msg', 'Error moving out occupier');
    res.redirect('/occupiers');
  }
});

module.exports = router;