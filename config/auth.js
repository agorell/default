// Authentication middleware

const requireAuth = (req, res, next) => {
  if (!req.session.user) {
    req.flash('error', 'Please log in to access this page.');
    return res.redirect('/auth/login');
  }
  next();
};

const requireRole = (roles) => {
  return (req, res, next) => {
    if (!req.session.user) {
      req.flash('error', 'Please log in to access this page.');
      return res.redirect('/auth/login');
    }
    
    const userRole = req.session.user.role;
    if (!roles.includes(userRole)) {
      req.flash('error', 'You do not have permission to access this page.');
      return res.redirect('/');
    }
    next();
  };
};

const requireAdmin = requireRole(['Admin']);
const requireManagerOrAdmin = requireRole(['Admin', 'Manager']);

const redirectIfAuthenticated = (req, res, next) => {
  if (req.session.user) {
    return res.redirect('/');
  }
  next();
};

module.exports = {
  requireAuth,
  requireRole,
  requireAdmin,
  requireManagerOrAdmin,
  redirectIfAuthenticated
};