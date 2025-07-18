const express = require('express');
const router = express.Router();
const { isAuthenticated, canViewReports } = require('../config/auth');
const HousingUnit = require('../models/HousingUnit');
const Occupier = require('../models/Occupier');
const Note = require('../models/Note');
const User = require('../models/User');

// Reports dashboard
router.get('/', isAuthenticated, canViewReports, (req, res) => {
  try {
    // Get basic statistics
    const occupancyStats = HousingUnit.getOccupancyStats();
    const typeStats = HousingUnit.getTypeStats();
    const occupierStats = Occupier.getOccupancyStats();
    const categoryStats = Note.getCategoryStats();
    
    // Get recent activity
    const recentNotes = Note.getRecentActivity(10);
    const recentMoveIns = Occupier.getRecentMoveIns(10);
    
    // Get user count (Admin only)
    let userCount = 0;
    if (req.session.user.role_name === 'Admin') {
      const users = User.findAll();
      userCount = users.length;
    }
    
    res.render('reports/index', {
      title: 'Reports',
      occupancyStats,
      typeStats,
      occupierStats,
      categoryStats,
      recentNotes,
      recentMoveIns,
      userCount,
      user: req.session.user
    });
  } catch (error) {
    console.error('Reports index error:', error);
    req.flash('error_msg', 'Error loading reports');
    res.redirect('/');
  }
});

// Occupancy report
router.get('/occupancy', isAuthenticated, canViewReports, (req, res) => {
  try {
    const occupancyStats = HousingUnit.getOccupancyStats();
    const typeStats = HousingUnit.getTypeStats();
    const occupiedUnits = HousingUnit.findOccupied();
    const vacantUnits = HousingUnit.findVacant();
    
    res.render('reports/occupancy', {
      title: 'Occupancy Report',
      occupancyStats,
      typeStats,
      occupiedUnits,
      vacantUnits,
      user: req.session.user
    });
  } catch (error) {
    console.error('Occupancy report error:', error);
    req.flash('error_msg', 'Error loading occupancy report');
    res.redirect('/reports');
  }
});

// Units report
router.get('/units', isAuthenticated, canViewReports, (req, res) => {
  try {
    const allUnits = HousingUnit.findAll();
    const occupancyStats = HousingUnit.getOccupancyStats();
    const typeStats = HousingUnit.getTypeStats();
    
    res.render('reports/units', {
      title: 'Units Report',
      units: allUnits,
      occupancyStats,
      typeStats,
      user: req.session.user
    });
  } catch (error) {
    console.error('Units report error:', error);
    req.flash('error_msg', 'Error loading units report');
    res.redirect('/reports');
  }
});

// Occupiers report
router.get('/occupiers', isAuthenticated, canViewReports, (req, res) => {
  try {
    const allOccupiers = Occupier.findAll();
    const occupierStats = Occupier.getOccupancyStats();
    const recentMoveIns = Occupier.getRecentMoveIns(20);
    
    res.render('reports/occupiers', {
      title: 'Occupiers Report',
      occupiers: allOccupiers,
      occupierStats,
      recentMoveIns,
      user: req.session.user
    });
  } catch (error) {
    console.error('Occupiers report error:', error);
    req.flash('error_msg', 'Error loading occupiers report');
    res.redirect('/reports');
  }
});

// Vacancy report
router.get('/vacancy', isAuthenticated, canViewReports, (req, res) => {
  try {
    const vacantUnits = HousingUnit.findVacant();
    const occupancyStats = HousingUnit.getOccupancyStats();
    const typeStats = HousingUnit.getTypeStats();
    
    res.render('reports/vacancy', {
      title: 'Vacancy Report',
      vacantUnits,
      occupancyStats,
      typeStats,
      user: req.session.user
    });
  } catch (error) {
    console.error('Vacancy report error:', error);
    req.flash('error_msg', 'Error loading vacancy report');
    res.redirect('/reports');
  }
});

// Notes activity report
router.get('/notes', isAuthenticated, canViewReports, (req, res) => {
  try {
    const recentNotes = Note.getRecentActivity(50);
    const categoryStats = Note.getCategoryStats();
    
    res.render('reports/notes', {
      title: 'Notes Activity Report',
      notes: recentNotes,
      categoryStats,
      user: req.session.user
    });
  } catch (error) {
    console.error('Notes report error:', error);
    req.flash('error_msg', 'Error loading notes report');
    res.redirect('/reports');
  }
});

// Financial report
router.get('/financial', isAuthenticated, canViewReports, (req, res) => {
  try {
    const occupierStats = Occupier.getOccupancyStats();
    const occupiedUnits = HousingUnit.findOccupied();
    const vacantUnits = HousingUnit.findVacant();
    
    // Calculate potential revenue from vacant units
    let potentialRevenue = 0;
    vacantUnits.forEach(unit => {
      if (unit.rental_rate) {
        potentialRevenue += parseFloat(unit.rental_rate);
      }
    });
    
    res.render('reports/financial', {
      title: 'Financial Report',
      occupierStats,
      occupiedUnits,
      vacantUnits,
      potentialRevenue,
      user: req.session.user
    });
  } catch (error) {
    console.error('Financial report error:', error);
    req.flash('error_msg', 'Error loading financial report');
    res.redirect('/reports');
  }
});

module.exports = router;