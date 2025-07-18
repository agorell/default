const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const { isAuthenticated, requireAdmin, canRead } = require('../config/auth');
const User = require('../models/User');
const Database = require('../models/database');

// Get all users (Admin only)
router.get('/', isAuthenticated, requireAdmin, (req, res) => {
  try {
    const users = User.findAll();
    
    res.render('users/index', {
      title: 'Users',
      users,
      user: req.session.user
    });
  } catch (error) {
    console.error('Users index error:', error);
    req.flash('error_msg', 'Error loading users');
    res.redirect('/');
  }
});

// User profile
router.get('/profile', isAuthenticated, canRead, (req, res) => {
  try {
    const user = User.findById(req.session.user.id);
    
    if (!user) {
      req.flash('error_msg', 'User not found');
      return res.redirect('/');
    }
    
    res.render('users/profile', {
      title: 'User Profile',
      profileUser: user,
      user: req.session.user
    });
  } catch (error) {
    console.error('User profile error:', error);
    req.flash('error_msg', 'Error loading user profile');
    res.redirect('/');
  }
});

// Edit profile form
router.get('/profile/edit', isAuthenticated, canRead, (req, res) => {
  try {
    const user = User.findById(req.session.user.id);
    
    if (!user) {
      req.flash('error_msg', 'User not found');
      return res.redirect('/');
    }
    
    res.render('users/edit-profile', {
      title: 'Edit Profile',
      profileUser: user,
      user: req.session.user
    });
  } catch (error) {
    console.error('Edit profile form error:', error);
    req.flash('error_msg', 'Error loading edit profile form');
    res.redirect('/users/profile');
  }
});

// Add user form (Admin only)
router.get('/add', isAuthenticated, requireAdmin, (req, res) => {
  try {
    const db = Database.getConnection();
    const roles = db.prepare('SELECT * FROM roles ORDER BY name').all();
    
    res.render('users/form', {
      title: 'Add User',
      userData: {},
      roles,
      action: 'add',
      user: req.session.user
    });
  } catch (error) {
    console.error('Add user form error:', error);
    req.flash('error_msg', 'Error loading add user form');
    res.redirect('/users');
  }
});

// Edit user form (Admin only)
router.get('/edit/:id', isAuthenticated, requireAdmin, (req, res) => {
  try {
    const userData = User.findById(req.params.id);
    
    if (!userData) {
      req.flash('error_msg', 'User not found');
      return res.redirect('/users');
    }
    
    const db = Database.getConnection();
    const roles = db.prepare('SELECT * FROM roles ORDER BY name').all();
    
    res.render('users/form', {
      title: 'Edit User',
      userData,
      roles,
      action: 'edit',
      user: req.session.user
    });
  } catch (error) {
    console.error('Edit user form error:', error);
    req.flash('error_msg', 'Error loading edit user form');
    res.redirect('/users');
  }
});

// Update profile
router.post('/profile/edit', isAuthenticated, canRead, [
  body('first_name').notEmpty().trim().withMessage('First name is required'),
  body('last_name').notEmpty().trim().withMessage('Last name is required'),
  body('email').isEmail().normalizeEmail().withMessage('Valid email is required'),
  body('current_password').optional(),
  body('new_password').optional().isLength({ min: 6 }).withMessage('New password must be at least 6 characters'),
  body('confirm_password').optional().custom((value, { req }) => {
    if (req.body.new_password && value !== req.body.new_password) {
      throw new Error('Password confirmation does not match password');
    }
    return true;
  })
], async (req, res) => {
  const errors = validationResult(req);
  
  if (!errors.isEmpty()) {
    const user = User.findById(req.session.user.id);
    
    return res.render('users/edit-profile', {
      title: 'Edit Profile',
      profileUser: user,
      errors: errors.array(),
      user: req.session.user
    });
  }

  try {
    const user = User.findById(req.session.user.id);
    
    if (!user) {
      req.flash('error_msg', 'User not found');
      return res.redirect('/');
    }
    
    // Check if email is already taken by another user
    const existingUser = User.findByEmail(req.body.email);
    if (existingUser && existingUser.id !== user.id) {
      return res.render('users/edit-profile', {
        title: 'Edit Profile',
        profileUser: user,
        error: 'Email is already in use',
        user: req.session.user
      });
    }
    
    // Update basic info
    const userData = {
      first_name: req.body.first_name,
      last_name: req.body.last_name,
      email: req.body.email,
      role_id: user.role_id // Keep same role
    };
    
    const updatedUser = user.update(userData);
    
    // Update password if provided
    if (req.body.new_password) {
      if (!req.body.current_password) {
        return res.render('users/edit-profile', {
          title: 'Edit Profile',
          profileUser: user,
          error: 'Current password is required to change password',
          user: req.session.user
        });
      }
      
      // Verify current password
      const isValid = await User.authenticate(user.username, req.body.current_password);
      if (!isValid) {
        return res.render('users/edit-profile', {
          title: 'Edit Profile',
          profileUser: user,
          error: 'Current password is incorrect',
          user: req.session.user
        });
      }
      
      await updatedUser.updatePassword(req.body.new_password);
    }
    
    // Update session
    req.session.user = {
      id: updatedUser.id,
      username: updatedUser.username,
      email: updatedUser.email,
      first_name: updatedUser.first_name,
      last_name: updatedUser.last_name,
      role_name: updatedUser.role_name,
      is_active: updatedUser.is_active
    };
    
    req.flash('success_msg', 'Profile updated successfully');
    res.redirect('/users/profile');
  } catch (error) {
    console.error('Update profile error:', error);
    
    const user = User.findById(req.session.user.id);
    res.render('users/edit-profile', {
      title: 'Edit Profile',
      profileUser: user,
      error: 'Error updating profile',
      user: req.session.user
    });
  }
});

// Create user (Admin only)
router.post('/add', isAuthenticated, requireAdmin, [
  body('username').notEmpty().trim().withMessage('Username is required'),
  body('email').isEmail().normalizeEmail().withMessage('Valid email is required'),
  body('password').isLength({ min: 6 }).withMessage('Password must be at least 6 characters'),
  body('first_name').notEmpty().trim().withMessage('First name is required'),
  body('last_name').notEmpty().trim().withMessage('Last name is required'),
  body('role_id').isInt().withMessage('Role is required')
], async (req, res) => {
  const errors = validationResult(req);
  
  if (!errors.isEmpty()) {
    const db = Database.getConnection();
    const roles = db.prepare('SELECT * FROM roles ORDER BY name').all();
    
    return res.render('users/form', {
      title: 'Add User',
      userData: req.body,
      roles,
      action: 'add',
      errors: errors.array(),
      user: req.session.user
    });
  }

  try {
    const user = await User.create(req.body);
    req.flash('success_msg', `User ${user.username} created successfully`);
    res.redirect('/users');
  } catch (error) {
    console.error('Create user error:', error);
    
    const db = Database.getConnection();
    const roles = db.prepare('SELECT * FROM roles ORDER BY name').all();
    
    res.render('users/form', {
      title: 'Add User',
      userData: req.body,
      roles,
      action: 'add',
      error: 'Error creating user. Username or email may already exist.',
      user: req.session.user
    });
  }
});

// Update user (Admin only)
router.post('/edit/:id', isAuthenticated, requireAdmin, [
  body('first_name').notEmpty().trim().withMessage('First name is required'),
  body('last_name').notEmpty().trim().withMessage('Last name is required'),
  body('email').isEmail().normalizeEmail().withMessage('Valid email is required'),
  body('role_id').isInt().withMessage('Role is required'),
  body('password').optional().isLength({ min: 6 }).withMessage('Password must be at least 6 characters')
], async (req, res) => {
  const errors = validationResult(req);
  
  if (!errors.isEmpty()) {
    const db = Database.getConnection();
    const roles = db.prepare('SELECT * FROM roles ORDER BY name').all();
    
    return res.render('users/form', {
      title: 'Edit User',
      userData: { ...req.body, id: req.params.id },
      roles,
      action: 'edit',
      errors: errors.array(),
      user: req.session.user
    });
  }

  try {
    const userData = User.findById(req.params.id);
    
    if (!userData) {
      req.flash('error_msg', 'User not found');
      return res.redirect('/users');
    }
    
    // Check if email is already taken by another user
    const existingUser = User.findByEmail(req.body.email);
    if (existingUser && existingUser.id !== userData.id) {
      const db = Database.getConnection();
      const roles = db.prepare('SELECT * FROM roles ORDER BY name').all();
      
      return res.render('users/form', {
        title: 'Edit User',
        userData: { ...req.body, id: req.params.id },
        roles,
        action: 'edit',
        error: 'Email is already in use',
        user: req.session.user
      });
    }
    
    const updatedUser = userData.update(req.body);
    
    // Update password if provided
    if (req.body.password) {
      await updatedUser.updatePassword(req.body.password);
    }
    
    req.flash('success_msg', `User ${updatedUser.username} updated successfully`);
    res.redirect('/users');
  } catch (error) {
    console.error('Update user error:', error);
    
    const db = Database.getConnection();
    const roles = db.prepare('SELECT * FROM roles ORDER BY name').all();
    
    res.render('users/form', {
      title: 'Edit User',
      userData: { ...req.body, id: req.params.id },
      roles,
      action: 'edit',
      error: 'Error updating user',
      user: req.session.user
    });
  }
});

// Deactivate user (Admin only)
router.post('/deactivate/:id', isAuthenticated, requireAdmin, (req, res) => {
  try {
    const userData = User.findById(req.params.id);
    
    if (!userData) {
      req.flash('error_msg', 'User not found');
      return res.redirect('/users');
    }
    
    // Don't allow deactivating self
    if (userData.id === req.session.user.id) {
      req.flash('error_msg', 'Cannot deactivate your own account');
      return res.redirect('/users');
    }
    
    userData.deactivate();
    req.flash('success_msg', `User ${userData.username} deactivated successfully`);
    res.redirect('/users');
  } catch (error) {
    console.error('Deactivate user error:', error);
    req.flash('error_msg', 'Error deactivating user');
    res.redirect('/users');
  }
});

// Activate user (Admin only)
router.post('/activate/:id', isAuthenticated, requireAdmin, (req, res) => {
  try {
    const userData = User.findById(req.params.id);
    
    if (!userData) {
      req.flash('error_msg', 'User not found');
      return res.redirect('/users');
    }
    
    userData.activate();
    req.flash('success_msg', `User ${userData.username} activated successfully`);
    res.redirect('/users');
  } catch (error) {
    console.error('Activate user error:', error);
    req.flash('error_msg', 'Error activating user');
    res.redirect('/users');
  }
});

module.exports = router;