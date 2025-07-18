const express = require('express');
const router = express.Router();
const { isAuthenticated } = require('../config/auth');
const HousingUnit = require('../models/HousingUnit');
const Occupier = require('../models/Occupier');
const Note = require('../models/Note');
const User = require('../models/User');

// Dashboard route
router.get('/', isAuthenticated, async (req, res) => {
  try {
    // Get statistics
    const occupancyStats = HousingUnit.getOccupancyStats();
    const typeStats = HousingUnit.getTypeStats();
    const occupierStats = Occupier.getOccupancyStats();
    const recentNotes = Note.getRecentActivity(5);
    const recentMoveIns = Occupier.getRecentMoveIns(5);
    
    // Get user count (Admin only)
    let userCount = 0;
    if (req.session.user.role_name === 'Admin') {
      const users = User.findAll();
      userCount = users.length;
    }
    
    res.render('dashboard', {
      title: 'Dashboard',
      user: req.session.user,
      occupancyStats,
      typeStats,
      occupierStats,
      recentNotes,
      recentMoveIns,
      userCount
    });
  } catch (error) {
    console.error('Dashboard error:', error);
    req.flash('error_msg', 'Error loading dashboard');
    res.render('dashboard', {
      title: 'Dashboard',
      user: req.session.user,
      occupancyStats: { total_units: 0, occupied_units: 0, vacant_units: 0, occupancy_rate: 0 },
      typeStats: [],
      occupierStats: { total_occupiers: 0, average_rent: 0, total_monthly_revenue: 0 },
      recentNotes: [],
      recentMoveIns: [],
      userCount: 0
    });
  }
});

module.exports = router;