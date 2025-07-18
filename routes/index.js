const express = require('express');
const router = express.Router();
const { requireAuth } = require('../config/auth');
const HousingUnit = require('../models/HousingUnit');
const Occupier = require('../models/Occupier');
const Note = require('../models/Note');

// Dashboard route
router.get('/', requireAuth, async (req, res) => {
  try {
    // Get statistics
    const occupancyStats = HousingUnit.getOccupancyStats();
    const occupierStats = Occupier.getOccupierStats();
    const noteStats = Note.getNoteStats();
    
    // Get recent notes
    const recentNotes = Note.getRecentNotes(5);
    
    // Get vacant units
    const vacantUnits = HousingUnit.getVacantUnits();
    
    // Get upcoming lease renewals
    const upcomingRenewals = Occupier.getUpcomingLeaseRenewals();
    
    res.render('index', {
      title: 'Dashboard',
      occupancyStats,
      occupierStats,
      noteStats,
      recentNotes,
      vacantUnits,
      upcomingRenewals
    });
  } catch (error) {
    console.error('Dashboard error:', error);
    req.flash('error', 'Error loading dashboard data');
    res.render('index', {
      title: 'Dashboard',
      occupancyStats: {},
      occupierStats: {},
      noteStats: {},
      recentNotes: [],
      vacantUnits: [],
      upcomingRenewals: []
    });
  }
});

module.exports = router;