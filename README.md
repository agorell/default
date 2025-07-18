# Housing Management System - Phase 1

A comprehensive web-based Independent Living Housing Management System built with Node.js and Express.js.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Security Features](#security-features)
- [Development](#development)
- [Contributing](#contributing)
- [License](#license)

## 🏠 Overview

The Housing Management System is designed to streamline the management of independent living housing units, occupiers, and related documentation. This Phase 1 implementation provides core functionality including user management, housing unit tracking, occupier management, notes system, and comprehensive reporting.

## ✨ Features

### Core Features
- **User Management**: Role-based access control with Admin, Manager, and Viewer roles
- **Housing Unit Management**: Complete CRUD operations for housing units
- **Occupier Management**: Track current occupiers with lease information
- **Notes System**: Categorized documentation with search capabilities
- **Reporting**: Comprehensive reports and analytics
- **Dashboard**: Real-time statistics and quick actions

### Security Features
- **Authentication**: Session-based authentication with bcrypt password hashing
- **Authorization**: Role-based access control
- **Input Validation**: Comprehensive validation using express-validator
- **CSRF Protection**: Built-in CSRF protection
- **XSS Prevention**: HTML escaping and sanitization
- **Rate Limiting**: API rate limiting to prevent abuse

### User Interface
- **Responsive Design**: Mobile-friendly Bootstrap 5 interface
- **Interactive Dashboard**: Real-time statistics and quick actions
- **Search & Filtering**: Advanced search across all modules
- **Flash Messages**: User feedback for all operations
- **Professional Design**: Clean, modern interface

## 🛠 Technology Stack

- **Backend**: Node.js 18+ with Express.js
- **Database**: SQLite with Better-sqlite3 (development) / PostgreSQL (production)
- **Frontend**: EJS templates with Bootstrap 5
- **Authentication**: express-session with bcrypt
- **Security**: helmet, express-rate-limit, express-validator
- **File Uploads**: multer for document handling
- **Utilities**: compression, morgan, dotenv

## 📦 Installation

### Prerequisites
- Node.js 18.0.0 or higher
- npm or yarn package manager

### Quick Start

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd housing-management
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Set up environment variables**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

4. **Initialize the database**
   ```bash
   npm run init-db
   ```

5. **Seed with sample data (optional)**
   ```bash
   npm run seed-db
   ```

6. **Start the application**
   ```bash
   npm start
   ```

7. **Access the application**
   - Open your browser to `http://localhost:3000`
   - Use default credentials to log in

## ⚙️ Configuration

### Environment Variables

Create a `.env` file in the root directory with the following variables:

```env
NODE_ENV=development
PORT=3000
SESSION_SECRET=your-secret-key-change-this-in-production
DB_PATH=./database.sqlite
BCRYPT_ROUNDS=10
```

### Database Configuration

The application uses SQLite for development and is configured to easily migrate to PostgreSQL for production. The database schema includes:

- **users**: System users with role-based access
- **roles**: User roles (Admin, Manager, Viewer)
- **housing_types**: Types of housing units
- **housing_units**: Main housing unit information
- **occupiers**: Current occupier information
- **notes**: General notes and documentation system

## 🚀 Usage

### Default User Accounts

The system comes with three default user accounts:

| Role | Username | Password | Access Level |
|------|----------|----------|--------------|
| Admin | admin | admin123 | Full system access |
| Manager | manager | manager123 | Housing & occupier management |
| Viewer | viewer | viewer123 | Read-only access |

### Core Workflows

1. **User Management** (Admin only)
   - Add, edit, and manage system users
   - Assign roles and permissions
   - Activate/deactivate user accounts

2. **Housing Unit Management**
   - Create and manage housing units
   - Track occupancy status
   - Update unit information and rates

3. **Occupier Management**
   - Add new occupiers to vacant units
   - Track lease information and payments
   - Manage move-in/move-out processes

4. **Notes System**
   - Create categorized notes
   - Link notes to units or occupiers
   - Search and filter notes

5. **Reporting**
   - Occupancy reports
   - Financial overviews
   - Vacancy analysis
   - Activity reports

## 📊 API Documentation

### Authentication Endpoints

- `POST /auth/login` - User login
- `POST /auth/logout` - User logout
- `GET /auth/login` - Login form

### User Management Endpoints

- `GET /users` - List all users (Admin only)
- `GET /users/add` - Add user form (Admin only)
- `POST /users/add` - Create new user (Admin only)
- `GET /users/edit/:id` - Edit user form (Admin only)
- `POST /users/edit/:id` - Update user (Admin only)
- `POST /users/toggle-status/:id` - Toggle user status (Admin only)

### Housing Unit Endpoints

- `GET /units` - List all housing units
- `GET /units/view/:id` - View unit details
- `GET /units/add` - Add unit form
- `POST /units/add` - Create new unit
- `GET /units/edit/:id` - Edit unit form
- `POST /units/edit/:id` - Update unit
- `POST /units/delete/:id` - Delete unit

### Occupier Endpoints

- `GET /occupiers` - List all occupiers
- `GET /occupiers/view/:id` - View occupier details
- `GET /occupiers/add` - Add occupier form
- `POST /occupiers/add` - Create new occupier
- `GET /occupiers/edit/:id` - Edit occupier form
- `POST /occupiers/edit/:id` - Update occupier
- `POST /occupiers/remove/:id` - Remove occupier

### Notes Endpoints

- `GET /notes` - List all notes
- `GET /notes/view/:id` - View note details
- `GET /notes/add` - Add note form
- `POST /notes/add` - Create new note
- `GET /notes/edit/:id` - Edit note form
- `POST /notes/edit/:id` - Update note
- `POST /notes/delete/:id` - Delete note

### Reporting Endpoints

- `GET /reports` - Reports dashboard
- `GET /reports/occupancy` - Occupancy report
- `GET /reports/units` - Units report
- `GET /reports/occupiers` - Occupiers report
- `GET /reports/vacancy` - Vacancy report
- `GET /reports/financial` - Financial report

## 🔒 Security Features

### Authentication & Authorization
- Session-based authentication
- Bcrypt password hashing
- Role-based access control
- Session timeout management

### Input Validation
- Server-side validation using express-validator
- Client-side validation with Bootstrap
- SQL injection prevention
- XSS protection

### Security Headers
- Helmet.js for security headers
- CSRF protection
- Content Security Policy
- Rate limiting

## 🧪 Development

### Project Structure

```
housing-management/
├── app.js                 # Main application file
├── package.json           # Dependencies and scripts
├── README.md             # This file
├── .env.example          # Environment configuration template
├── config/               # Configuration files
│   ├── database.js       # Database connection
│   └── auth.js           # Authentication middleware
├── models/               # Database models
│   ├── database.js       # Database initialization
│   ├── User.js           # User model
│   ├── HousingUnit.js    # Housing unit model
│   ├── Occupier.js       # Occupier model
│   └── Note.js           # Notes model
├── routes/               # Route handlers
│   ├── index.js          # Dashboard routes
│   ├── auth.js           # Authentication routes
│   ├── users.js          # User management routes
│   ├── units.js          # Housing unit routes
│   ├── occupiers.js      # Occupier routes
│   ├── notes.js          # Notes routes
│   └── reports.js        # Reporting routes
├── views/                # EJS templates
│   ├── layout.ejs        # Main layout
│   ├── index.ejs         # Dashboard
│   ├── login.ejs         # Login page
│   ├── error.ejs         # Error page
│   ├── partials/         # Partial templates
│   ├── users/            # User management views
│   ├── units/            # Housing unit views
│   ├── occupiers/        # Occupier views
│   ├── notes/            # Notes views
│   └── reports/          # Reporting views
├── public/               # Static assets
│   ├── css/             # CSS files
│   ├── js/              # JavaScript files
│   └── uploads/         # File uploads
├── scripts/             # Database scripts
│   ├── init-database.js # Database initialization
│   └── seed-database.js # Sample data seeding
├── cache/               # Application cache
└── logs/                # Application logs
```

### Available Scripts

- `npm start` - Start the application
- `npm run dev` - Start with nodemon for development
- `npm run init-db` - Initialize database
- `npm run seed-db` - Seed database with sample data

### Database Models

The system uses the following data models:

1. **User Model**: Manages system users with role-based access
2. **HousingUnit Model**: Manages housing units with occupancy tracking
3. **Occupier Model**: Manages occupier information and lease tracking
4. **Note Model**: Manages notes and documentation system

### Adding New Features

To add new features to the system:

1. Create the database table (if needed) in `models/database.js`
2. Create the model class in `models/`
3. Add route handlers in `routes/`
4. Create EJS templates in `views/`
5. Add navigation links in `views/layout.ejs`
6. Update this README with new features

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support, please contact the development team or create an issue in the repository.

---

**Phase 1 Complete** - This implementation provides a solid foundation for the Housing Management System with core functionality, security features, and a professional user interface. Future phases will add enhanced features like historical tracking, financial management, communication systems, and advanced integrations.