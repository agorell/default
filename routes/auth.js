const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const { redirectIfAuthenticated } = require('../config/auth');
const User = require('../models/User');

// Login page
router.get('/login', redirectIfAuthenticated, (req, res) => {
  res.render('login', {
    title: 'Login'
  });
});

// Login POST
router.post('/login', [
  redirectIfAuthenticated,
  body('username').trim().notEmpty().withMessage('Username is required'),
  body('password').notEmpty().withMessage('Password is required')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.render('login', {
        title: 'Login',
        errors: errors.array(),
        formData: req.body
      });
    }

    const { username, password } = req.body;
    
    // Find user
    const user = User.findByUsername(username);
    if (!user) {
      req.flash('error', 'Invalid username or password');
      return res.render('login', {
        title: 'Login',
        formData: req.body
      });
    }

    // Check if user is active
    if (!user.isActive()) {
      req.flash('error', 'Your account has been deactivated. Please contact an administrator.');
      return res.render('login', {
        title: 'Login',
        formData: req.body
      });
    }

    // Verify password
    const isValidPassword = User.verifyPassword(password, user.password_hash);
    if (!isValidPassword) {
      req.flash('error', 'Invalid username or password');
      return res.render('login', {
        title: 'Login',
        formData: req.body
      });
    }

    // Set session
    req.session.user = {
      id: user.id,
      username: user.username,
      email: user.email,
      firstName: user.first_name,
      lastName: user.last_name,
      role: user.role_name,
      roleId: user.role_id
    };

    req.flash('success', `Welcome back, ${user.first_name}!`);
    res.redirect('/');
  } catch (error) {
    console.error('Login error:', error);
    req.flash('error', 'An error occurred during login');
    res.render('login', {
      title: 'Login',
      formData: req.body
    });
  }
});

// Logout
router.post('/logout', (req, res) => {
  req.session.destroy((err) => {
    if (err) {
      console.error('Logout error:', err);
      req.flash('error', 'Error logging out');
      return res.redirect('/');
    }
    res.redirect('/auth/login');
  });
});

// Logout GET (for convenience)
router.get('/logout', (req, res) => {
  req.session.destroy((err) => {
    if (err) {
      console.error('Logout error:', err);
      req.flash('error', 'Error logging out');
      return res.redirect('/');
    }
    res.redirect('/auth/login');
  });
});

module.exports = router;