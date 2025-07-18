const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const { requireAuth, requireManagerOrAdmin } = require('../config/auth');
const HousingUnit = require('../models/HousingUnit');
const Occupier = require('../models/Occupier');
const Note = require('../models/Note');

// List all housing units
router.get('/', requireAuth, async (req, res) => {
  try {
    const filters = {};
    
    // Apply filters from query params
    if (req.query.is_occupied !== undefined && req.query.is_occupied !== '') {
      filters.is_occupied = parseInt(req.query.is_occupied);
    }
    
    if (req.query.housing_type_id) {
      filters.housing_type_id = parseInt(req.query.housing_type_id);
    }
    
    if (req.query.search) {
      filters.search = req.query.search;
    }
    
    const units = HousingUnit.findAll(filters);
    const housingTypes = HousingUnit.getHousingTypes();
    const occupancyStats = HousingUnit.getOccupancyStats();
    
    res.render('units/index', {
      title: 'Housing Units',
      units,
      housingTypes,
      occupancyStats,
      filters: req.query
    });
  } catch (error) {
    console.error('Error fetching housing units:', error);
    req.flash('error', 'Error loading housing units');
    res.render('units/index', {
      title: 'Housing Units',
      units: [],
      housingTypes: [],
      occupancyStats: {},
      filters: {}
    });
  }
});

// View housing unit details
router.get('/view/:id', requireAuth, async (req, res) => {
  try {
    const unit = HousingUnit.findById(req.params.id);
    if (!unit) {
      req.flash('error', 'Housing unit not found');
      return res.redirect('/units');
    }

    const occupier = Occupier.findByHousingUnitId(unit.id);
    const notes = Note.findByHousingUnitId(unit.id);

    res.render('units/view', {
      title: `Unit ${unit.unit_number}`,
      unit,
      occupier,
      notes
    });
  } catch (error) {
    console.error('Error loading housing unit:', error);
    req.flash('error', 'Error loading housing unit');
    res.redirect('/units');
  }
});

// Add housing unit form
router.get('/add', requireManagerOrAdmin, async (req, res) => {
  try {
    const housingTypes = HousingUnit.getHousingTypes();
    res.render('units/form', {
      title: 'Add Housing Unit',
      housingTypes,
      action: '/units/add',
      unit: {}
    });
  } catch (error) {
    console.error('Error loading add unit form:', error);
    req.flash('error', 'Error loading form');
    res.redirect('/units');
  }
});

// Create housing unit
router.post('/add', [
  requireManagerOrAdmin,
  body('unit_number').trim().notEmpty().withMessage('Unit number is required'),
  body('housing_type_id').isInt({ min: 1 }).withMessage('Please select a housing type'),
  body('bedrooms').optional().isInt({ min: 0 }).withMessage('Bedrooms must be a positive number'),
  body('bathrooms').optional().isFloat({ min: 0 }).withMessage('Bathrooms must be a positive number'),
  body('square_footage').optional().isInt({ min: 0 }).withMessage('Square footage must be a positive number'),
  body('rental_rate').optional().isFloat({ min: 0 }).withMessage('Rental rate must be a positive number'),
  body('condition_grade').optional().isIn(['Excellent', 'Good', 'Fair', 'Poor']).withMessage('Please select a valid condition grade'),
  body('address').trim().notEmpty().withMessage('Address is required')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      const housingTypes = HousingUnit.getHousingTypes();
      return res.render('units/form', {
        title: 'Add Housing Unit',
        housingTypes,
        action: '/units/add',
        unit: req.body,
        errors: errors.array()
      });
    }

    // Check if unit number already exists
    const existingUnit = HousingUnit.findByUnitNumber(req.body.unit_number);
    if (existingUnit) {
      const housingTypes = HousingUnit.getHousingTypes();
      return res.render('units/form', {
        title: 'Add Housing Unit',
        housingTypes,
        action: '/units/add',
        unit: req.body,
        errors: [{ msg: 'Unit number already exists' }]
      });
    }

    const unit = HousingUnit.create(req.body);
    req.flash('success', 'Housing unit created successfully');
    res.redirect('/units');
  } catch (error) {
    console.error('Error creating housing unit:', error);
    req.flash('error', 'Error creating housing unit');
    const housingTypes = HousingUnit.getHousingTypes();
    res.render('units/form', {
      title: 'Add Housing Unit',
      housingTypes,
      action: '/units/add',
      unit: req.body
    });
  }
});

// Edit housing unit form
router.get('/edit/:id', requireManagerOrAdmin, async (req, res) => {
  try {
    const unit = HousingUnit.findById(req.params.id);
    if (!unit) {
      req.flash('error', 'Housing unit not found');
      return res.redirect('/units');
    }

    const housingTypes = HousingUnit.getHousingTypes();
    res.render('units/form', {
      title: 'Edit Housing Unit',
      housingTypes,
      action: `/units/edit/${unit.id}`,
      unit,
      isEdit: true
    });
  } catch (error) {
    console.error('Error loading edit unit form:', error);
    req.flash('error', 'Error loading form');
    res.redirect('/units');
  }
});

// Update housing unit
router.post('/edit/:id', [
  requireManagerOrAdmin,
  body('unit_number').trim().notEmpty().withMessage('Unit number is required'),
  body('housing_type_id').isInt({ min: 1 }).withMessage('Please select a housing type'),
  body('bedrooms').optional().isInt({ min: 0 }).withMessage('Bedrooms must be a positive number'),
  body('bathrooms').optional().isFloat({ min: 0 }).withMessage('Bathrooms must be a positive number'),
  body('square_footage').optional().isInt({ min: 0 }).withMessage('Square footage must be a positive number'),
  body('rental_rate').optional().isFloat({ min: 0 }).withMessage('Rental rate must be a positive number'),
  body('condition_grade').optional().isIn(['Excellent', 'Good', 'Fair', 'Poor']).withMessage('Please select a valid condition grade'),
  body('address').trim().notEmpty().withMessage('Address is required')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      const unit = HousingUnit.findById(req.params.id);
      const housingTypes = HousingUnit.getHousingTypes();
      return res.render('units/form', {
        title: 'Edit Housing Unit',
        housingTypes,
        action: `/units/edit/${req.params.id}`,
        unit: { ...unit, ...req.body },
        isEdit: true,
        errors: errors.array()
      });
    }

    // Check if unit number already exists (excluding current unit)
    const existingUnit = HousingUnit.findByUnitNumber(req.body.unit_number);
    if (existingUnit && existingUnit.id !== parseInt(req.params.id)) {
      const unit = HousingUnit.findById(req.params.id);
      const housingTypes = HousingUnit.getHousingTypes();
      return res.render('units/form', {
        title: 'Edit Housing Unit',
        housingTypes,
        action: `/units/edit/${req.params.id}`,
        unit: { ...unit, ...req.body },
        isEdit: true,
        errors: [{ msg: 'Unit number already exists' }]
      });
    }

    const unit = HousingUnit.update(req.params.id, req.body);
    req.flash('success', 'Housing unit updated successfully');
    res.redirect('/units');
  } catch (error) {
    console.error('Error updating housing unit:', error);
    req.flash('error', 'Error updating housing unit');
    res.redirect('/units');
  }
});

// Delete housing unit
router.post('/delete/:id', requireManagerOrAdmin, async (req, res) => {
  try {
    const unit = HousingUnit.findById(req.params.id);
    if (!unit) {
      req.flash('error', 'Housing unit not found');
      return res.redirect('/units');
    }

    // Check if unit is occupied
    if (unit.is_occupied) {
      req.flash('error', 'Cannot delete occupied housing unit');
      return res.redirect('/units');
    }

    // Check if unit has notes
    const notes = Note.findByHousingUnitId(unit.id);
    if (notes.length > 0) {
      req.flash('error', 'Cannot delete housing unit with existing notes');
      return res.redirect('/units');
    }

    const deleted = HousingUnit.delete(req.params.id);
    if (deleted) {
      req.flash('success', 'Housing unit deleted successfully');
    } else {
      req.flash('error', 'Error deleting housing unit');
    }
    
    res.redirect('/units');
  } catch (error) {
    console.error('Error deleting housing unit:', error);
    req.flash('error', 'Error deleting housing unit');
    res.redirect('/units');
  }
});

module.exports = router;