const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const { requireAuth, requireManagerOrAdmin } = require('../config/auth');
const Occupier = require('../models/Occupier');
const HousingUnit = require('../models/HousingUnit');
const Note = require('../models/Note');

// List all occupiers
router.get('/', requireAuth, async (req, res) => {
  try {
    const filters = {};
    
    if (req.query.search) {
      filters.search = req.query.search;
    }
    
    const occupiers = Occupier.findAll(filters);
    const occupierStats = Occupier.getOccupierStats();
    
    res.render('occupiers/index', {
      title: 'Occupiers',
      occupiers,
      occupierStats,
      filters: req.query
    });
  } catch (error) {
    console.error('Error fetching occupiers:', error);
    req.flash('error', 'Error loading occupiers');
    res.render('occupiers/index', {
      title: 'Occupiers',
      occupiers: [],
      occupierStats: {},
      filters: {}
    });
  }
});

// View occupier details
router.get('/view/:id', requireAuth, async (req, res) => {
  try {
    const occupier = Occupier.findById(req.params.id);
    if (!occupier) {
      req.flash('error', 'Occupier not found');
      return res.redirect('/occupiers');
    }

    const unit = HousingUnit.findById(occupier.housing_unit_id);
    const notes = Note.findByOccupierId(occupier.id);

    res.render('occupiers/view', {
      title: `${occupier.getFullName()}`,
      occupier,
      unit,
      notes
    });
  } catch (error) {
    console.error('Error loading occupier:', error);
    req.flash('error', 'Error loading occupier');
    res.redirect('/occupiers');
  }
});

// Add occupier form
router.get('/add', requireManagerOrAdmin, async (req, res) => {
  try {
    const vacantUnits = HousingUnit.getVacantUnits();
    res.render('occupiers/form', {
      title: 'Add Occupier',
      vacantUnits,
      action: '/occupiers/add',
      occupier: {}
    });
  } catch (error) {
    console.error('Error loading add occupier form:', error);
    req.flash('error', 'Error loading form');
    res.redirect('/occupiers');
  }
});

// Create occupier
router.post('/add', [
  requireManagerOrAdmin,
  body('housing_unit_id').isInt({ min: 1 }).withMessage('Please select a housing unit'),
  body('first_name').trim().notEmpty().withMessage('First name is required'),
  body('last_name').trim().notEmpty().withMessage('Last name is required'),
  body('phone').optional().isMobilePhone().withMessage('Please enter a valid phone number'),
  body('email').optional().isEmail().withMessage('Please enter a valid email address'),
  body('occupancy_start_date').isISO8601().withMessage('Please enter a valid start date'),
  body('monthly_rent').optional().isFloat({ min: 0 }).withMessage('Monthly rent must be a positive number'),
  body('emergency_contact_name').trim().notEmpty().withMessage('Emergency contact name is required'),
  body('emergency_contact_phone').optional().isMobilePhone().withMessage('Please enter a valid emergency contact phone number')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      const vacantUnits = HousingUnit.getVacantUnits();
      return res.render('occupiers/form', {
        title: 'Add Occupier',
        vacantUnits,
        action: '/occupiers/add',
        occupier: req.body,
        errors: errors.array()
      });
    }

    // Check if housing unit is available
    const unit = HousingUnit.findById(req.body.housing_unit_id);
    if (!unit) {
      const vacantUnits = HousingUnit.getVacantUnits();
      return res.render('occupiers/form', {
        title: 'Add Occupier',
        vacantUnits,
        action: '/occupiers/add',
        occupier: req.body,
        errors: [{ msg: 'Selected housing unit not found' }]
      });
    }

    if (unit.is_occupied) {
      const vacantUnits = HousingUnit.getVacantUnits();
      return res.render('occupiers/form', {
        title: 'Add Occupier',
        vacantUnits,
        action: '/occupiers/add',
        occupier: req.body,
        errors: [{ msg: 'Selected housing unit is already occupied' }]
      });
    }

    const occupier = Occupier.create(req.body);
    req.flash('success', 'Occupier added successfully');
    res.redirect('/occupiers');
  } catch (error) {
    console.error('Error creating occupier:', error);
    req.flash('error', 'Error creating occupier');
    const vacantUnits = HousingUnit.getVacantUnits();
    res.render('occupiers/form', {
      title: 'Add Occupier',
      vacantUnits,
      action: '/occupiers/add',
      occupier: req.body
    });
  }
});

// Edit occupier form
router.get('/edit/:id', requireManagerOrAdmin, async (req, res) => {
  try {
    const occupier = Occupier.findById(req.params.id);
    if (!occupier) {
      req.flash('error', 'Occupier not found');
      return res.redirect('/occupiers');
    }

    const vacantUnits = HousingUnit.getVacantUnits();
    const currentUnit = HousingUnit.findById(occupier.housing_unit_id);
    
    // Add current unit to vacant units for editing
    if (currentUnit) {
      vacantUnits.unshift(currentUnit);
    }

    res.render('occupiers/form', {
      title: 'Edit Occupier',
      vacantUnits,
      action: `/occupiers/edit/${occupier.id}`,
      occupier,
      isEdit: true
    });
  } catch (error) {
    console.error('Error loading edit occupier form:', error);
    req.flash('error', 'Error loading form');
    res.redirect('/occupiers');
  }
});

// Update occupier
router.post('/edit/:id', [
  requireManagerOrAdmin,
  body('first_name').trim().notEmpty().withMessage('First name is required'),
  body('last_name').trim().notEmpty().withMessage('Last name is required'),
  body('phone').optional().isMobilePhone().withMessage('Please enter a valid phone number'),
  body('email').optional().isEmail().withMessage('Please enter a valid email address'),
  body('occupancy_start_date').isISO8601().withMessage('Please enter a valid start date'),
  body('monthly_rent').optional().isFloat({ min: 0 }).withMessage('Monthly rent must be a positive number'),
  body('emergency_contact_name').trim().notEmpty().withMessage('Emergency contact name is required'),
  body('emergency_contact_phone').optional().isMobilePhone().withMessage('Please enter a valid emergency contact phone number')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      const occupier = Occupier.findById(req.params.id);
      const vacantUnits = HousingUnit.getVacantUnits();
      const currentUnit = HousingUnit.findById(occupier.housing_unit_id);
      
      if (currentUnit) {
        vacantUnits.unshift(currentUnit);
      }

      return res.render('occupiers/form', {
        title: 'Edit Occupier',
        vacantUnits,
        action: `/occupiers/edit/${req.params.id}`,
        occupier: { ...occupier, ...req.body },
        isEdit: true,
        errors: errors.array()
      });
    }

    const occupier = Occupier.update(req.params.id, req.body);
    req.flash('success', 'Occupier updated successfully');
    res.redirect('/occupiers');
  } catch (error) {
    console.error('Error updating occupier:', error);
    req.flash('error', 'Error updating occupier');
    res.redirect('/occupiers');
  }
});

// Move occupier to different unit
router.get('/move/:id', requireManagerOrAdmin, async (req, res) => {
  try {
    const occupier = Occupier.findById(req.params.id);
    if (!occupier) {
      req.flash('error', 'Occupier not found');
      return res.redirect('/occupiers');
    }

    const vacantUnits = HousingUnit.getVacantUnits();
    const currentUnit = HousingUnit.findById(occupier.housing_unit_id);

    res.render('occupiers/move', {
      title: `Move ${occupier.getFullName()}`,
      occupier,
      currentUnit,
      vacantUnits
    });
  } catch (error) {
    console.error('Error loading move occupier form:', error);
    req.flash('error', 'Error loading form');
    res.redirect('/occupiers');
  }
});

// Process move occupier
router.post('/move/:id', [
  requireManagerOrAdmin,
  body('new_housing_unit_id').isInt({ min: 1 }).withMessage('Please select a new housing unit')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      const occupier = Occupier.findById(req.params.id);
      const vacantUnits = HousingUnit.getVacantUnits();
      const currentUnit = HousingUnit.findById(occupier.housing_unit_id);

      return res.render('occupiers/move', {
        title: `Move ${occupier.getFullName()}`,
        occupier,
        currentUnit,
        vacantUnits,
        errors: errors.array()
      });
    }

    const newUnit = HousingUnit.findById(req.body.new_housing_unit_id);
    if (!newUnit) {
      req.flash('error', 'Selected housing unit not found');
      return res.redirect(`/occupiers/move/${req.params.id}`);
    }

    if (newUnit.is_occupied) {
      req.flash('error', 'Selected housing unit is already occupied');
      return res.redirect(`/occupiers/move/${req.params.id}`);
    }

    const success = Occupier.moveToUnit(req.params.id, req.body.new_housing_unit_id);
    if (success) {
      req.flash('success', 'Occupier moved successfully');
    } else {
      req.flash('error', 'Error moving occupier');
    }
    
    res.redirect('/occupiers');
  } catch (error) {
    console.error('Error moving occupier:', error);
    req.flash('error', 'Error moving occupier');
    res.redirect('/occupiers');
  }
});

// Remove occupier (move out)
router.post('/remove/:id', requireManagerOrAdmin, async (req, res) => {
  try {
    const occupier = Occupier.findById(req.params.id);
    if (!occupier) {
      req.flash('error', 'Occupier not found');
      return res.redirect('/occupiers');
    }

    const deleted = Occupier.delete(req.params.id);
    if (deleted) {
      req.flash('success', 'Occupier removed successfully');
    } else {
      req.flash('error', 'Error removing occupier');
    }
    
    res.redirect('/occupiers');
  } catch (error) {
    console.error('Error removing occupier:', error);
    req.flash('error', 'Error removing occupier');
    res.redirect('/occupiers');
  }
});

module.exports = router;