const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const { requireAuth, requireAdmin } = require('../config/auth');
const User = require('../models/User');

// List all users (Admin only)
router.get('/', requireAdmin, async (req, res) => {
  try {
    const users = User.findAll();
    res.render('users/index', {
      title: 'User Management',
      users
    });
  } catch (error) {
    console.error('Error fetching users:', error);
    req.flash('error', 'Error loading users');
    res.render('users/index', {
      title: 'User Management',
      users: []
    });
  }
});

// Add user form (Admin only)
router.get('/add', requireAdmin, async (req, res) => {
  try {
    const roles = User.getRoles();
    res.render('users/form', {
      title: 'Add User',
      roles,
      action: '/users/add',
      user: {}
    });
  } catch (error) {
    console.error('Error loading add user form:', error);
    req.flash('error', 'Error loading form');
    res.redirect('/users');
  }
});

// Create user (Admin only)
router.post('/add', [
  requireAdmin,
  body('username').trim().isLength({ min: 3 }).withMessage('Username must be at least 3 characters'),
  body('email').isEmail().withMessage('Please enter a valid email address'),
  body('password').isLength({ min: 6 }).withMessage('Password must be at least 6 characters'),
  body('first_name').trim().notEmpty().withMessage('First name is required'),
  body('last_name').trim().notEmpty().withMessage('Last name is required'),
  body('role_id').isInt({ min: 1 }).withMessage('Please select a role')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      const roles = User.getRoles();
      return res.render('users/form', {
        title: 'Add User',
        roles,
        action: '/users/add',
        user: req.body,
        errors: errors.array()
      });
    }

    // Check if username already exists
    const existingUser = User.findByUsername(req.body.username);
    if (existingUser) {
      const roles = User.getRoles();
      return res.render('users/form', {
        title: 'Add User',
        roles,
        action: '/users/add',
        user: req.body,
        errors: [{ msg: 'Username already exists' }]
      });
    }

    // Check if email already exists
    const existingEmail = User.findByEmail(req.body.email);
    if (existingEmail) {
      const roles = User.getRoles();
      return res.render('users/form', {
        title: 'Add User',
        roles,
        action: '/users/add',
        user: req.body,
        errors: [{ msg: 'Email already exists' }]
      });
    }

    const user = User.create(req.body);
    req.flash('success', 'User created successfully');
    res.redirect('/users');
  } catch (error) {
    console.error('Error creating user:', error);
    req.flash('error', 'Error creating user');
    const roles = User.getRoles();
    res.render('users/form', {
      title: 'Add User',
      roles,
      action: '/users/add',
      user: req.body
    });
  }
});

// Edit user form (Admin only)
router.get('/edit/:id', requireAdmin, async (req, res) => {
  try {
    const user = User.findById(req.params.id);
    if (!user) {
      req.flash('error', 'User not found');
      return res.redirect('/users');
    }

    const roles = User.getRoles();
    res.render('users/form', {
      title: 'Edit User',
      roles,
      action: `/users/edit/${user.id}`,
      user,
      isEdit: true
    });
  } catch (error) {
    console.error('Error loading edit user form:', error);
    req.flash('error', 'Error loading form');
    res.redirect('/users');
  }
});

// Update user (Admin only)
router.post('/edit/:id', [
  requireAdmin,
  body('username').trim().isLength({ min: 3 }).withMessage('Username must be at least 3 characters'),
  body('email').isEmail().withMessage('Please enter a valid email address'),
  body('password').optional().isLength({ min: 6 }).withMessage('Password must be at least 6 characters'),
  body('first_name').trim().notEmpty().withMessage('First name is required'),
  body('last_name').trim().notEmpty().withMessage('Last name is required'),
  body('role_id').isInt({ min: 1 }).withMessage('Please select a role')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      const user = User.findById(req.params.id);
      const roles = User.getRoles();
      return res.render('users/form', {
        title: 'Edit User',
        roles,
        action: `/users/edit/${req.params.id}`,
        user: { ...user, ...req.body },
        isEdit: true,
        errors: errors.array()
      });
    }

    // Check if username already exists (excluding current user)
    const existingUser = User.findByUsername(req.body.username);
    if (existingUser && existingUser.id !== parseInt(req.params.id)) {
      const user = User.findById(req.params.id);
      const roles = User.getRoles();
      return res.render('users/form', {
        title: 'Edit User',
        roles,
        action: `/users/edit/${req.params.id}`,
        user: { ...user, ...req.body },
        isEdit: true,
        errors: [{ msg: 'Username already exists' }]
      });
    }

    // Check if email already exists (excluding current user)
    const existingEmail = User.findByEmail(req.body.email);
    if (existingEmail && existingEmail.id !== parseInt(req.params.id)) {
      const user = User.findById(req.params.id);
      const roles = User.getRoles();
      return res.render('users/form', {
        title: 'Edit User',
        roles,
        action: `/users/edit/${req.params.id}`,
        user: { ...user, ...req.body },
        isEdit: true,
        errors: [{ msg: 'Email already exists' }]
      });
    }

    const updateData = { ...req.body };
    if (!updateData.password) {
      delete updateData.password;
    }

    const user = User.update(req.params.id, updateData);
    req.flash('success', 'User updated successfully');
    res.redirect('/users');
  } catch (error) {
    console.error('Error updating user:', error);
    req.flash('error', 'Error updating user');
    res.redirect('/users');
  }
});

// Toggle user active status (Admin only)
router.post('/toggle-status/:id', requireAdmin, async (req, res) => {
  try {
    const user = User.findById(req.params.id);
    if (!user) {
      req.flash('error', 'User not found');
      return res.redirect('/users');
    }

    // Don't allow deactivating the current user
    if (user.id === req.session.user.id) {
      req.flash('error', 'You cannot deactivate your own account');
      return res.redirect('/users');
    }

    const newStatus = user.is_active === 1 ? 0 : 1;
    User.update(req.params.id, { is_active: newStatus });
    
    const statusText = newStatus === 1 ? 'activated' : 'deactivated';
    req.flash('success', `User ${statusText} successfully`);
    res.redirect('/users');
  } catch (error) {
    console.error('Error toggling user status:', error);
    req.flash('error', 'Error updating user status');
    res.redirect('/users');
  }
});

// View user profile
router.get('/profile/:id', requireAuth, async (req, res) => {
  try {
    const user = User.findById(req.params.id);
    if (!user) {
      req.flash('error', 'User not found');
      return res.redirect('/users');
    }

    // Non-admin users can only view their own profile
    if (req.session.user.role !== 'Admin' && user.id !== req.session.user.id) {
      req.flash('error', 'You can only view your own profile');
      return res.redirect('/');
    }

    res.render('users/profile', {
      title: 'User Profile',
      profileUser: user
    });
  } catch (error) {
    console.error('Error loading user profile:', error);
    req.flash('error', 'Error loading profile');
    res.redirect('/');
  }
});

module.exports = router;