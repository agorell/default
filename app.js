const express = require('express');
const session = require('express-session');
const flash = require('connect-flash');
const methodOverride = require('method-override');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const compression = require('compression');
const path = require('path');
const Database = require('./models/database');

const app = express();
const PORT = process.env.PORT || 3000;

// Initialize database
Database.init();

// Security middleware
app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      styleSrc: ["'self'", "'unsafe-inline'", "https://cdn.jsdelivr.net"],
      scriptSrc: ["'self'", "'unsafe-inline'", "https://cdn.jsdelivr.net"],
      imgSrc: ["'self'", "data:", "https:"],
      fontSrc: ["'self'", "https://cdn.jsdelivr.net"],
    },
  },
}));

// Rate limiting
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 100, // limit each IP to 100 requests per windowMs
  message: 'Too many requests from this IP, please try again later.'
});
app.use(limiter);

// Compression
app.use(compression());

// View engine setup
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Middleware
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(methodOverride('_method'));
app.use(express.static(path.join(__dirname, 'public')));

// Session configuration
app.use(session({
  secret: process.env.SESSION_SECRET || 'housing-management-secret-key-change-in-production',
  resave: false,
  saveUninitialized: false,
  cookie: {
    maxAge: 24 * 60 * 60 * 1000, // 24 hours
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production'
  }
}));

// Flash messages
app.use(flash());

// Global variables
app.use((req, res, next) => {
  res.locals.success_msg = req.flash('success_msg');
  res.locals.error_msg = req.flash('error_msg');
  res.locals.user = req.session.user || null;
  next();
});

// Routes
app.use('/', require('./routes/index'));
app.use('/auth', require('./routes/auth'));
app.use('/users', require('./routes/users'));
app.use('/units', require('./routes/units'));
app.use('/occupiers', require('./routes/occupiers'));
app.use('/notes', require('./routes/notes'));
app.use('/reports', require('./routes/reports'));

// Error handling middleware
app.use((req, res, next) => {
  res.status(404).render('error', { 
    title: 'Page Not Found',
    message: 'The page you are looking for does not exist.',
    error: { status: 404 }
  });
});

app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).render('error', { 
    title: 'Server Error',
    message: 'Something went wrong on our end.',
    error: { status: 500 }
  });
});

// Start server
app.listen(PORT, () => {
  console.log(`Housing Management System running on port ${PORT}`);
  console.log(`Visit: http://localhost:${PORT}`);
});

module.exports = app;