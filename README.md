# Independent Living Housing Management System - Phase 1

A comprehensive web-based housing management system built with Node.js, Express.js, and SQLite. This system provides complete management capabilities for independent living facilities, including housing units, occupiers, notes, and comprehensive reporting.

## 🚀 Features

### Core Functionality
- **Housing Unit Management**: Complete CRUD operations for housing units with detailed information
- **Occupier Management**: Track current occupiers, lease information, and move-in/move-out processes
- **Notes & Documentation**: Categorized note system with search capabilities
- **Comprehensive Reporting**: Various reports including occupancy, financial, and activity reports
- **User Management**: Role-based access control with Admin, Manager, and Viewer roles

### Technical Features
- **Role-Based Authentication**: Secure login system with granular permissions
- **Responsive Design**: Mobile-friendly Bootstrap 5 interface
- **Database Management**: SQLite with Better-sqlite3 for optimal performance
- **Search & Filtering**: Advanced search capabilities across all modules
- **Audit Logging**: Track all system activities and changes
- **Data Validation**: Comprehensive input validation and sanitization

## 🛠️ Technology Stack

- **Backend**: Node.js 18+ with Express.js
- **Database**: SQLite with Better-sqlite3 ORM
- **Frontend**: EJS templates with Bootstrap 5
- **Authentication**: express-session with bcrypt password hashing
- **Security**: helmet, express-rate-limit, express-validator
- **Performance**: compression middleware

## 📋 Requirements

- Node.js 18.0 or higher
- npm 8.0 or higher
- SQLite 3.x (included with better-sqlite3)

## 🔧 Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd housing-management
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Initialize the database**
   ```bash
   npm run init-db
   ```

4. **Seed with sample data (optional)**
   ```bash
   npm run seed-db
   ```

5. **Start the application**
   ```bash
   npm start
   ```

6. **Access the application**
   - Open your browser and go to `http://localhost:3000`
   - Use the default login credentials below

## 🔐 Default User Accounts

| Role | Username | Password | Access Level |
|------|----------|----------|--------------|
| Admin | admin | admin123 | Full system access |
| Manager | manager | manager123 | Housing & occupier management |
| Viewer | viewer | viewer123 | Read-only access |

## 📁 Project Structure

```
housing-management/
├── app.js                 # Main application file
├── package.json           # Dependencies and scripts
├── README.md             # This file
├── config/               # Configuration files
│   └── auth.js           # Authentication middleware
├── models/               # Database models
│   ├── database.js       # Database connection and initialization
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
│   ├── layout.ejs        # Main layout template
│   ├── login.ejs         # Login page
│   ├── dashboard.ejs     # Dashboard
│   ├── error.ejs         # Error pages
│   ├── units/            # Housing unit views
│   ├── occupiers/        # Occupier views
│   ├── notes/            # Notes views
│   ├── reports/          # Report views
│   └── users/            # User management views
├── scripts/              # Database scripts
│   ├── init-db.js        # Database initialization
│   └── seed-db.js        # Sample data seeding
├── public/               # Static assets
├── cache/                # Application cache
├── logs/                 # Application logs
└── database.sqlite       # SQLite database file
```

## 🎯 Key Features Breakdown

### 1. Dashboard
- Real-time occupancy statistics
- Recent activity feeds
- Quick action buttons
- Financial overview

### 2. Housing Unit Management
- Complete unit inventory
- Detailed unit information (bedrooms, bathrooms, sq ft, rent)
- Occupancy status tracking
- Condition grading system
- Advanced search and filtering

### 3. Occupier Management
- Current occupier tracking
- Lease information management
- Move-in/move-out processing
- Emergency contact information
- Rent tracking

### 4. Notes & Documentation
- Categorized note system (General, Maintenance, Financial, etc.)
- Unit and occupier associations
- Search and filter capabilities
- User attribution and timestamps

### 5. Reporting System
- Occupancy reports with statistics
- Financial reports and revenue tracking
- Vacancy reports for available units
- Activity reports for system usage
- Export capabilities (print/PDF ready)

### 6. User Management (Admin Only)
- User account creation and management
- Role assignment and permissions
- User profile management
- Account activation/deactivation

## 🔒 Security Features

- **Authentication**: Session-based authentication with secure password hashing
- **Authorization**: Role-based access control with granular permissions
- **Input Validation**: Comprehensive validation using express-validator
- **Security Headers**: Helmet.js for security headers
- **Rate Limiting**: Protection against brute force attacks
- **CSRF Protection**: Built-in CSRF protection
- **XSS Prevention**: Input sanitization and output encoding

## 📊 Database Schema

The system uses a normalized SQLite database with the following main tables:

- **users**: System users with role-based access
- **roles**: User roles (Admin, Manager, Viewer)
- **housing_types**: Different types of housing units
- **housing_units**: Main housing unit information
- **occupiers**: Current occupier information
- **notes**: General notes and documentation system

## 🚀 Available Scripts

- `npm start` - Start the production server
- `npm run dev` - Start development server with nodemon
- `npm run init-db` - Initialize database with default data
- `npm run seed-db` - Seed database with sample data

## 🎨 User Interface

The system features a modern, responsive interface built with Bootstrap 5:

- **Mobile-friendly**: Responsive design that works on all devices
- **Professional styling**: Clean, modern design with gradient accents
- **Interactive elements**: Hover effects and smooth transitions
- **Accessibility**: ARIA labels and keyboard navigation support
- **Dark/light theme ready**: CSS variables for easy theming

## 🔧 Configuration

### Environment Variables
The application supports the following environment variables:

- `PORT`: Server port (default: 3000)
- `NODE_ENV`: Environment mode (development/production)
- `SESSION_SECRET`: Session secret key (auto-generated if not provided)

### Database Configuration
- Database file: `database.sqlite` (created automatically)
- Connection pooling: Built-in with better-sqlite3
- Migrations: Automatic table creation on startup

## 📈 Performance Optimizations

- **Database Indexing**: Proper indexes on frequently queried fields
- **Connection Pooling**: Efficient database connection management
- **Compression**: Gzip compression for all responses
- **Caching**: Static asset caching and session storage
- **Pagination**: Built-in pagination for large datasets

## 🔮 Future Enhancements (Phase 2+)

- **Historical Tracking**: Occupancy history and lease tracking
- **Maintenance Requests**: Maintenance request system
- **Financial Management**: Invoice generation and payment tracking
- **Communication System**: Email/SMS notifications
- **Advanced Reporting**: Analytics and trend analysis
- **Mobile App**: React Native mobile application
- **API Enhancements**: RESTful API expansion

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 📞 Support

For support and questions:
- Create an issue in the GitHub repository
- Check the documentation in the `/docs` folder
- Review the code comments for implementation details

## 🎉 Acknowledgments

- Built with love for independent living communities
- Designed for ease of use and maximum functionality
- Focused on security and data protection

---

**Housing Management System - Phase 1** - Ready for production use with comprehensive features for managing independent living facilities.