const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const { isNotAuthenticated, ensureUserSession } = require('../config/auth');
const User = require('../models/User');

// Apply session middleware to all routes
router.use(ensureUserSession);

// Login page
router.get('/login', isNotAuthenticated, (req, res) => {
  res.render('login', {
    title: 'Login',
    layout: false
  });
});

// Login form handler
router.post('/login', [
  body('username').notEmpty().trim().withMessage('Username is required'),
  body('password').notEmpty().withMessage('Password is required')
], async (req, res) => {
  const errors = validationResult(req);
  
  if (!errors.isEmpty()) {
    return res.render('login', {
      title: 'Login',
      layout: false,
      errors: errors.array(),
      username: req.body.username
    });
  }

  try {
    const { username, password } = req.body;
    
    const user = await User.authenticate(username, password);
    
    if (!user) {
      return res.render('login', {
        title: 'Login',
        layout: false,
        error: 'Invalid username or password',
        username: req.body.username
      });
    }

    // Store user in session
    req.session.user = {
      id: user.id,
      username: user.username,
      email: user.email,
      first_name: user.first_name,
      last_name: user.last_name,
      role_name: user.role_name,
      is_active: user.is_active
    };

    req.flash('success_msg', `Welcome back, ${user.first_name}!`);
    res.redirect('/');
  } catch (error) {
    console.error('Login error:', error);
    res.render('login', {
      title: 'Login',
      layout: false,
      error: 'An error occurred during login. Please try again.',
      username: req.body.username
    });
  }
});

// Logout handler
router.get('/logout', (req, res) => {
  req.session.destroy((err) => {
    if (err) {
      console.error('Logout error:', err);
    }
    res.redirect('/auth/login');
  });
});

module.exports = router;