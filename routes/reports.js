const express = require('express');
const router = express.Router();
const { requireAuth } = require('../config/auth');
const HousingUnit = require('../models/HousingUnit');
const Occupier = require('../models/Occupier');
const Note = require('../models/Note');
const User = require('../models/User');

// Reports dashboard
router.get('/', requireAuth, async (req, res) => {
  try {
    const occupancyStats = HousingUnit.getOccupancyStats();
    const occupierStats = Occupier.getOccupierStats();
    const noteStats = Note.getNoteStats();
    
    res.render('reports/index', {
      title: 'Reports',
      occupancyStats,
      occupierStats,
      noteStats
    });
  } catch (error) {
    console.error('Error loading reports dashboard:', error);
    req.flash('error', 'Error loading reports');
    res.render('reports/index', {
      title: 'Reports',
      occupancyStats: {},
      occupierStats: {},
      noteStats: {}
    });
  }
});

// Occupancy summary report
router.get('/occupancy', requireAuth, async (req, res) => {
  try {
    const occupancyStats = HousingUnit.getOccupancyStats();
    const occupiedUnits = HousingUnit.getOccupiedUnits();
    const vacantUnits = HousingUnit.getVacantUnits();
    const housingTypes = HousingUnit.getHousingTypes();
    
    // Get occupancy by housing type
    const occupancyByType = {};
    housingTypes.forEach(type => {
      const typeUnits = HousingUnit.findAll({ housing_type_id: type.id });
      const occupiedTypeUnits = typeUnits.filter(unit => unit.is_occupied);
      occupancyByType[type.name] = {
        total: typeUnits.length,
        occupied: occupiedTypeUnits.length,
        vacant: typeUnits.length - occupiedTypeUnits.length,
        occupancy_rate: typeUnits.length > 0 ? ((occupiedTypeUnits.length / typeUnits.length) * 100).toFixed(1) : 0
      };
    });
    
    res.render('reports/occupancy', {
      title: 'Occupancy Report',
      occupancyStats,
      occupiedUnits,
      vacantUnits,
      occupancyByType
    });
  } catch (error) {
    console.error('Error loading occupancy report:', error);
    req.flash('error', 'Error loading occupancy report');
    res.redirect('/reports');
  }
});

// Unit listing report
router.get('/units', requireAuth, async (req, res) => {
  try {
    const units = HousingUnit.findAll();
    const housingTypes = HousingUnit.getHousingTypes();
    
    // Group units by housing type
    const unitsByType = {};
    housingTypes.forEach(type => {
      unitsByType[type.name] = units.filter(unit => unit.housing_type_id === type.id);
    });
    
    res.render('reports/units', {
      title: 'Units Report',
      units,
      unitsByType,
      housingTypes
    });
  } catch (error) {
    console.error('Error loading units report:', error);
    req.flash('error', 'Error loading units report');
    res.redirect('/reports');
  }
});

// Current occupiers report
router.get('/occupiers', requireAuth, async (req, res) => {
  try {
    const occupiers = Occupier.findAll();
    const occupierStats = Occupier.getOccupierStats();
    const upcomingRenewals = Occupier.getUpcomingLeaseRenewals();
    
    // Group occupiers by housing type
    const occupiersByType = {};
    occupiers.forEach(occupier => {
      const typeName = occupier.housing_type_name || 'Unknown';
      if (!occupiersByType[typeName]) {
        occupiersByType[typeName] = [];
      }
      occupiersByType[typeName].push(occupier);
    });
    
    res.render('reports/occupiers', {
      title: 'Occupiers Report',
      occupiers,
      occupierStats,
      upcomingRenewals,
      occupiersByType
    });
  } catch (error) {
    console.error('Error loading occupiers report:', error);
    req.flash('error', 'Error loading occupiers report');
    res.redirect('/reports');
  }
});

// Vacancy report
router.get('/vacancy', requireAuth, async (req, res) => {
  try {
    const vacantUnits = HousingUnit.getVacantUnits();
    const housingTypes = HousingUnit.getHousingTypes();
    
    // Group vacant units by housing type
    const vacantByType = {};
    housingTypes.forEach(type => {
      vacantByType[type.name] = vacantUnits.filter(unit => unit.housing_type_id === type.id);
    });
    
    // Calculate potential revenue from vacant units
    const potentialRevenue = vacantUnits.reduce((sum, unit) => {
      return sum + (parseFloat(unit.rental_rate) || 0);
    }, 0);
    
    res.render('reports/vacancy', {
      title: 'Vacancy Report',
      vacantUnits,
      vacantByType,
      potentialRevenue
    });
  } catch (error) {
    console.error('Error loading vacancy report:', error);
    req.flash('error', 'Error loading vacancy report');
    res.redirect('/reports');
  }
});

// Notes activity report
router.get('/notes', requireAuth, async (req, res) => {
  try {
    const notes = Note.findAll();
    const noteStats = Note.getNoteStats();
    const categories = Note.getCategories();
    
    // Group notes by category
    const notesByCategory = {};
    categories.forEach(category => {
      notesByCategory[category] = notes.filter(note => note.category === category);
    });
    
    // Get recent notes by month
    const notesByMonth = {};
    notes.forEach(note => {
      const date = new Date(note.created_at);
      const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
      if (!notesByMonth[monthKey]) {
        notesByMonth[monthKey] = [];
      }
      notesByMonth[monthKey].push(note);
    });
    
    res.render('reports/notes', {
      title: 'Notes Activity Report',
      notes,
      noteStats,
      notesByCategory,
      notesByMonth
    });
  } catch (error) {
    console.error('Error loading notes report:', error);
    req.flash('error', 'Error loading notes report');
    res.redirect('/reports');
  }
});

// Financial overview report
router.get('/financial', requireAuth, async (req, res) => {
  try {
    const occupiers = Occupier.findAll();
    const occupierStats = Occupier.getOccupierStats();
    const vacantUnits = HousingUnit.getVacantUnits();
    
    // Calculate current monthly revenue
    const currentMonthlyRevenue = occupiers.reduce((sum, occupier) => {
      return sum + (parseFloat(occupier.monthly_rent) || 0);
    }, 0);
    
    // Calculate potential revenue from vacant units
    const potentialRevenue = vacantUnits.reduce((sum, unit) => {
      return sum + (parseFloat(unit.rental_rate) || 0);
    }, 0);
    
    // Calculate revenue by housing type
    const revenueByType = {};
    occupiers.forEach(occupier => {
      const typeName = occupier.housing_type_name || 'Unknown';
      if (!revenueByType[typeName]) {
        revenueByType[typeName] = {
          count: 0,
          total_revenue: 0,
          average_rent: 0
        };
      }
      revenueByType[typeName].count++;
      revenueByType[typeName].total_revenue += parseFloat(occupier.monthly_rent) || 0;
    });
    
    // Calculate averages
    Object.keys(revenueByType).forEach(type => {
      if (revenueByType[type].count > 0) {
        revenueByType[type].average_rent = revenueByType[type].total_revenue / revenueByType[type].count;
      }
    });
    
    res.render('reports/financial', {
      title: 'Financial Overview',
      occupiers,
      occupierStats,
      currentMonthlyRevenue,
      potentialRevenue,
      revenueByType,
      totalPotentialRevenue: currentMonthlyRevenue + potentialRevenue
    });
  } catch (error) {
    console.error('Error loading financial report:', error);
    req.flash('error', 'Error loading financial report');
    res.redirect('/reports');
  }
});

// User activity report (Admin only)
router.get('/activity', requireAuth, async (req, res) => {
  try {
    // Only admins can view user activity
    if (req.session.user.role !== 'Admin') {
      req.flash('error', 'Access denied');
      return res.redirect('/reports');
    }
    
    const users = User.findAll();
    const notes = Note.findAll();
    
    // Group notes by user
    const notesByUser = {};
    users.forEach(user => {
      notesByUser[user.id] = {
        user: user,
        notes: notes.filter(note => note.created_by === user.id)
      };
    });
    
    res.render('reports/activity', {
      title: 'User Activity Report',
      users,
      notesByUser
    });
  } catch (error) {
    console.error('Error loading activity report:', error);
    req.flash('error', 'Error loading activity report');
    res.redirect('/reports');
  }
});

module.exports = router;