const User = require('../models/User');

// Middleware to check if user is authenticated
const isAuthenticated = (req, res, next) => {
  if (req.session.user) {
    return next();
  }
  
  req.flash('error_msg', 'Please log in to access this page');
  res.redirect('/auth/login');
};

// Middleware to check if user is not authenticated (for login/register pages)
const isNotAuthenticated = (req, res, next) => {
  if (!req.session.user) {
    return next();
  }
  
  res.redirect('/');
};

// Middleware to check if user has admin role
const requireAdmin = (req, res, next) => {
  if (!req.session.user) {
    req.flash('error_msg', 'Please log in to access this page');
    return res.redirect('/auth/login');
  }
  
  if (req.session.user.role_name !== 'Admin') {
    req.flash('error_msg', 'You do not have permission to access this page');
    return res.redirect('/');
  }
  
  next();
};

// Middleware to check if user can manage units (Admin or Manager)
const canManageUnits = (req, res, next) => {
  if (!req.session.user) {
    req.flash('error_msg', 'Please log in to access this page');
    return res.redirect('/auth/login');
  }
  
  if (!['Admin', 'Manager'].includes(req.session.user.role_name)) {
    req.flash('error_msg', 'You do not have permission to access this page');
    return res.redirect('/');
  }
  
  next();
};

// Middleware to check if user can view reports (Admin, Manager, or Viewer)
const canViewReports = (req, res, next) => {
  if (!req.session.user) {
    req.flash('error_msg', 'Please log in to access this page');
    return res.redirect('/auth/login');
  }
  
  if (!['Admin', 'Manager', 'Viewer'].includes(req.session.user.role_name)) {
    req.flash('error_msg', 'You do not have permission to access this page');
    return res.redirect('/');
  }
  
  next();
};

// Middleware to check if user can perform read-only operations
const canRead = (req, res, next) => {
  if (!req.session.user) {
    req.flash('error_msg', 'Please log in to access this page');
    return res.redirect('/auth/login');
  }
  
  next();
};

// Middleware to ensure user session is up to date
const ensureUserSession = async (req, res, next) => {
  if (req.session.user) {
    try {
      // Refresh user data from database
      const user = User.findById(req.session.user.id);
      
      if (!user || !user.is_active) {
        req.session.destroy();
        req.flash('error_msg', 'Your account has been deactivated. Please contact an administrator.');
        return res.redirect('/auth/login');
      }
      
      // Update session with latest user data
      req.session.user = {
        id: user.id,
        username: user.username,
        email: user.email,
        first_name: user.first_name,
        last_name: user.last_name,
        role_name: user.role_name,
        is_active: user.is_active
      };
      
      res.locals.user = req.session.user;
    } catch (error) {
      console.error('Error updating user session:', error);
      req.session.destroy();
      req.flash('error_msg', 'Session error. Please log in again.');
      return res.redirect('/auth/login');
    }
  }
  
  next();
};

module.exports = {
  isAuthenticated,
  isNotAuthenticated,
  requireAdmin,
  canManageUnits,
  canViewReports,
  canRead,
  ensureUserSession
};