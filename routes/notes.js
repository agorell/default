const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const { requireAuth, requireManagerOrAdmin } = require('../config/auth');
const Note = require('../models/Note');
const HousingUnit = require('../models/HousingUnit');
const Occupier = require('../models/Occupier');

// List all notes
router.get('/', requireAuth, async (req, res) => {
  try {
    const filters = {};
    
    if (req.query.category) {
      filters.category = req.query.category;
    }
    
    if (req.query.housing_unit_id) {
      filters.housing_unit_id = parseInt(req.query.housing_unit_id);
    }
    
    if (req.query.search) {
      filters.search = req.query.search;
    }
    
    const notes = Note.findAll(filters);
    const categories = Note.getCategories();
    const housingUnits = HousingUnit.findAll();
    const noteStats = Note.getNoteStats();
    
    res.render('notes/index', {
      title: 'Notes',
      notes,
      categories,
      housingUnits,
      noteStats,
      filters: req.query
    });
  } catch (error) {
    console.error('Error fetching notes:', error);
    req.flash('error', 'Error loading notes');
    res.render('notes/index', {
      title: 'Notes',
      notes: [],
      categories: [],
      housingUnits: [],
      noteStats: {},
      filters: {}
    });
  }
});

// View note details
router.get('/view/:id', requireAuth, async (req, res) => {
  try {
    const note = Note.findById(req.params.id);
    if (!note) {
      req.flash('error', 'Note not found');
      return res.redirect('/notes');
    }

    const unit = note.housing_unit_id ? HousingUnit.findById(note.housing_unit_id) : null;
    const occupier = note.occupier_id ? Occupier.findById(note.occupier_id) : null;

    res.render('notes/view', {
      title: note.title,
      note,
      unit,
      occupier
    });
  } catch (error) {
    console.error('Error loading note:', error);
    req.flash('error', 'Error loading note');
    res.redirect('/notes');
  }
});

// Add note form
router.get('/add', requireManagerOrAdmin, async (req, res) => {
  try {
    const categories = Note.getCategories();
    const housingUnits = HousingUnit.findAll();
    const occupiers = Occupier.findAll();
    
    res.render('notes/form', {
      title: 'Add Note',
      categories,
      housingUnits,
      occupiers,
      action: '/notes/add',
      note: {}
    });
  } catch (error) {
    console.error('Error loading add note form:', error);
    req.flash('error', 'Error loading form');
    res.redirect('/notes');
  }
});

// Create note
router.post('/add', [
  requireManagerOrAdmin,
  body('title').trim().notEmpty().withMessage('Title is required'),
  body('content').trim().notEmpty().withMessage('Content is required'),
  body('category').isIn(Note.getCategories()).withMessage('Please select a valid category'),
  body('housing_unit_id').optional().isInt({ min: 1 }).withMessage('Please select a valid housing unit'),
  body('occupier_id').optional().isInt({ min: 1 }).withMessage('Please select a valid occupier')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      const categories = Note.getCategories();
      const housingUnits = HousingUnit.findAll();
      const occupiers = Occupier.findAll();
      
      return res.render('notes/form', {
        title: 'Add Note',
        categories,
        housingUnits,
        occupiers,
        action: '/notes/add',
        note: req.body,
        errors: errors.array()
      });
    }

    // Set created_by to current user
    req.body.created_by = req.session.user.id;

    // Convert empty strings to null for optional fields
    if (req.body.housing_unit_id === '') req.body.housing_unit_id = null;
    if (req.body.occupier_id === '') req.body.occupier_id = null;

    const note = Note.create(req.body);
    req.flash('success', 'Note created successfully');
    res.redirect('/notes');
  } catch (error) {
    console.error('Error creating note:', error);
    req.flash('error', 'Error creating note');
    const categories = Note.getCategories();
    const housingUnits = HousingUnit.findAll();
    const occupiers = Occupier.findAll();
    
    res.render('notes/form', {
      title: 'Add Note',
      categories,
      housingUnits,
      occupiers,
      action: '/notes/add',
      note: req.body
    });
  }
});

// Edit note form
router.get('/edit/:id', requireManagerOrAdmin, async (req, res) => {
  try {
    const note = Note.findById(req.params.id);
    if (!note) {
      req.flash('error', 'Note not found');
      return res.redirect('/notes');
    }

    // Only allow editing own notes unless admin
    if (req.session.user.role !== 'Admin' && note.created_by !== req.session.user.id) {
      req.flash('error', 'You can only edit your own notes');
      return res.redirect('/notes');
    }

    const categories = Note.getCategories();
    const housingUnits = HousingUnit.findAll();
    const occupiers = Occupier.findAll();
    
    res.render('notes/form', {
      title: 'Edit Note',
      categories,
      housingUnits,
      occupiers,
      action: `/notes/edit/${note.id}`,
      note,
      isEdit: true
    });
  } catch (error) {
    console.error('Error loading edit note form:', error);
    req.flash('error', 'Error loading form');
    res.redirect('/notes');
  }
});

// Update note
router.post('/edit/:id', [
  requireManagerOrAdmin,
  body('title').trim().notEmpty().withMessage('Title is required'),
  body('content').trim().notEmpty().withMessage('Content is required'),
  body('category').isIn(Note.getCategories()).withMessage('Please select a valid category'),
  body('housing_unit_id').optional().isInt({ min: 1 }).withMessage('Please select a valid housing unit'),
  body('occupier_id').optional().isInt({ min: 1 }).withMessage('Please select a valid occupier')
], async (req, res) => {
  try {
    const note = Note.findById(req.params.id);
    if (!note) {
      req.flash('error', 'Note not found');
      return res.redirect('/notes');
    }

    // Only allow editing own notes unless admin
    if (req.session.user.role !== 'Admin' && note.created_by !== req.session.user.id) {
      req.flash('error', 'You can only edit your own notes');
      return res.redirect('/notes');
    }

    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      const categories = Note.getCategories();
      const housingUnits = HousingUnit.findAll();
      const occupiers = Occupier.findAll();
      
      return res.render('notes/form', {
        title: 'Edit Note',
        categories,
        housingUnits,
        occupiers,
        action: `/notes/edit/${req.params.id}`,
        note: { ...note, ...req.body },
        isEdit: true,
        errors: errors.array()
      });
    }

    // Convert empty strings to null for optional fields
    if (req.body.housing_unit_id === '') req.body.housing_unit_id = null;
    if (req.body.occupier_id === '') req.body.occupier_id = null;

    const updatedNote = Note.update(req.params.id, req.body);
    req.flash('success', 'Note updated successfully');
    res.redirect('/notes');
  } catch (error) {
    console.error('Error updating note:', error);
    req.flash('error', 'Error updating note');
    res.redirect('/notes');
  }
});

// Delete note
router.post('/delete/:id', requireManagerOrAdmin, async (req, res) => {
  try {
    const note = Note.findById(req.params.id);
    if (!note) {
      req.flash('error', 'Note not found');
      return res.redirect('/notes');
    }

    // Only allow deleting own notes unless admin
    if (req.session.user.role !== 'Admin' && note.created_by !== req.session.user.id) {
      req.flash('error', 'You can only delete your own notes');
      return res.redirect('/notes');
    }

    const deleted = Note.delete(req.params.id);
    if (deleted) {
      req.flash('success', 'Note deleted successfully');
    } else {
      req.flash('error', 'Error deleting note');
    }
    
    res.redirect('/notes');
  } catch (error) {
    console.error('Error deleting note:', error);
    req.flash('error', 'Error deleting note');
    res.redirect('/notes');
  }
});

// Quick add note for specific unit
router.get('/add-for-unit/:unit_id', requireManagerOrAdmin, async (req, res) => {
  try {
    const unit = HousingUnit.findById(req.params.unit_id);
    if (!unit) {
      req.flash('error', 'Housing unit not found');
      return res.redirect('/units');
    }

    const categories = Note.getCategories();
    const occupier = Occupier.findByHousingUnitId(unit.id);
    
    res.render('notes/form', {
      title: `Add Note for Unit ${unit.unit_number}`,
      categories,
      housingUnits: [unit],
      occupiers: occupier ? [occupier] : [],
      action: '/notes/add',
      note: {
        housing_unit_id: unit.id,
        occupier_id: occupier ? occupier.id : null
      },
      preselectedUnit: unit,
      preselectedOccupier: occupier
    });
  } catch (error) {
    console.error('Error loading add note form for unit:', error);
    req.flash('error', 'Error loading form');
    res.redirect('/units');
  }
});

// Quick add note for specific occupier
router.get('/add-for-occupier/:occupier_id', requireManagerOrAdmin, async (req, res) => {
  try {
    const occupier = Occupier.findById(req.params.occupier_id);
    if (!occupier) {
      req.flash('error', 'Occupier not found');
      return res.redirect('/occupiers');
    }

    const unit = HousingUnit.findById(occupier.housing_unit_id);
    const categories = Note.getCategories();
    
    res.render('notes/form', {
      title: `Add Note for ${occupier.getFullName()}`,
      categories,
      housingUnits: unit ? [unit] : [],
      occupiers: [occupier],
      action: '/notes/add',
      note: {
        housing_unit_id: occupier.housing_unit_id,
        occupier_id: occupier.id
      },
      preselectedUnit: unit,
      preselectedOccupier: occupier
    });
  } catch (error) {
    console.error('Error loading add note form for occupier:', error);
    req.flash('error', 'Error loading form');
    res.redirect('/occupiers');
  }
});

module.exports = router;