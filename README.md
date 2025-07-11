# Independent Living Housing Management System - Phase 1

A comprehensive web-based Independent Living Housing Management System built with PHP Laravel framework and MariaDB database. This system provides complete housing management capabilities with role-based access control, comprehensive reporting, and a full RESTful API.

## 🏠 Project Overview

This Phase 1 implementation establishes the foundational architecture and core housing management features. The system is designed to manage independent living housing units, track occupiers, manage documentation, and provide comprehensive reporting capabilities.

## 🚀 Features

### Core Modules
- **User Management**: Complete user management with role-based access control
- **Housing Unit Management**: Comprehensive housing unit tracking and management
- **Occupier Management**: Occupier information and lease management
- **Notes & Documentation**: Categorized note system with file attachments
- **Reporting System**: Occupancy, vacancy, and activity reports
- **Dashboard**: Real-time statistics and alerts

### Authentication & Security
- Role-based access control (Admin, Manager, Viewer)
- Granular permissions system
- Laravel Sanctum API authentication
- Comprehensive audit logging
- CSRF protection and security measures

### API Features
- Complete RESTful API for all modules
- Token-based authentication
- Rate limiting and security measures
- Mobile app ready
- Comprehensive filtering and pagination

## 🛠️ Technology Stack

- **Backend**: PHP 8.2+ with Laravel 10.x
- **Database**: MariaDB 10.6+ / MySQL 8.0+
- **Authentication**: Laravel Sanctum
- **Caching**: Redis (optional)
- **Email**: Laravel Mail with multiple drivers
- **File Storage**: Laravel Storage

## 📊 Database Schema

The system includes 11 comprehensive database tables:

### Core Tables
- `users` - System users with roles
- `roles` - User roles (Admin, Manager, Viewer)
- `permissions` - Granular permissions
- `role_permissions` - Role-permission relationships
- `housing_units` - Housing unit information
- `housing_types` - Housing types (apartment, house, etc.)
- `occupiers` - Occupier information and lease tracking
- `notes` - Documentation system with categories
- `audit_logs` - Comprehensive activity tracking
- `password_reset_tokens` - Password reset functionality
- `personal_access_tokens` - API authentication tokens

## 🔐 User Roles & Permissions

### Default Roles
- **Admin**: Full system access with all permissions
- **Manager**: Housing unit and occupier management
- **Viewer**: Read-only access to housing data

### Permission Categories
- User Management (view, create, edit, delete)
- Housing Unit Management (view, create, edit, delete)
- Occupier Management (view, create, edit, delete)
- Notes Management (view, create, edit, delete)
- Reports (view, export)
- System Administration (audit logs, settings)

## 📋 Default User Accounts

| Role | Email | Password | Access Level |
|------|-------|----------|-------------|
| Admin | admin@housingmanagement.com | admin123 | Full system access |
| Manager | manager@housingmanagement.com | manager123 | Housing & occupier management |
| Viewer | viewer@housingmanagement.com | viewer123 | Read-only access |

## 🚀 Installation

### Requirements
- PHP 8.2 or higher
- Composer
- MariaDB 10.6+ or MySQL 8.0+
- Redis (optional, for caching)

### Quick Start

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Configuration**
   Update your `.env` file with database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=housing_management
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Run Migrations & Seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start Development Server**
   ```bash
   php artisan serve
   ```

6. **Access the Application**
   - Web Interface: `http://localhost:8000`
   - API Base URL: `http://localhost:8000/api`

## 🔧 Configuration

### Environment Variables
Key environment variables in `.env`:

```env
APP_NAME="Housing Management System"
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_DATABASE=housing_management

# Housing Management Settings
HOUSING_PAGINATION_LIMIT=25
HOUSING_MAX_UPLOAD_SIZE=10240
HOUSING_DEFAULT_ROLE=viewer

# Mail Configuration
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS="noreply@housingmanagement.com"
MAIL_FROM_NAME="${APP_NAME}"

# Redis (optional)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## 📱 API Documentation

### Authentication
All API endpoints require authentication via Laravel Sanctum tokens.

**Login**
```bash
POST /api/login
{
  "email": "admin@housingmanagement.com",
  "password": "admin123"
}
```

**Get User Info**
```bash
GET /api/user
Authorization: Bearer {token}
```

### Core Endpoints

#### Housing Units
- `GET /api/housing-units` - List housing units
- `GET /api/housing-units/{id}` - Get housing unit details
- `POST /api/housing-units` - Create housing unit
- `PUT /api/housing-units/{id}` - Update housing unit
- `DELETE /api/housing-units/{id}` - Delete housing unit

#### Occupiers
- `GET /api/occupiers` - List occupiers
- `GET /api/occupiers/{id}` - Get occupier details
- `POST /api/occupiers` - Create occupier
- `PUT /api/occupiers/{id}` - Update occupier
- `DELETE /api/occupiers/{id}` - Delete occupier

#### Notes
- `GET /api/notes` - List notes
- `GET /api/notes/{id}` - Get note details
- `POST /api/notes` - Create note
- `PUT /api/notes/{id}` - Update note
- `DELETE /api/notes/{id}` - Delete note

#### Reports
- `GET /api/reports/dashboard` - Dashboard statistics
- `GET /api/reports/occupancy` - Occupancy report
- `GET /api/reports/vacancy` - Vacancy report
- `GET /api/reports/activity` - Activity report

### API Features
- Comprehensive filtering and searching
- Pagination support
- Role-based access control
- Rate limiting (60 requests per minute)
- Detailed error messages
- Statistics and analytics

## 📊 Sample Data

The system includes comprehensive sample data:
- 5 users with different roles
- 7 housing types
- 15 housing units with realistic details
- 7 occupiers with lease information
- 10 notes with various categories and priorities

## 🎯 Key Features

### Dashboard
- Real-time occupancy statistics
- Financial revenue tracking
- Lease expiration alerts
- High-priority note alerts
- Housing condition monitoring

### Housing Management
- Multiple housing types support
- Condition grading system
- Occupancy tracking
- Financial calculations
- Advanced filtering and search

### Occupier Management
- Lease tracking and management
- Emergency contact information
- Move-in/move-out tracking
- Lease renewal capabilities
- Payment tracking

### Notes & Documentation
- Categorized note system
- Priority levels
- File attachments
- Privacy controls
- Activity tracking

### Reporting
- Occupancy reports with statistics
- Vacancy analysis
- Activity and audit logs
- Exportable data
- Real-time analytics

## 🔒 Security Features

- Role-based access control
- Granular permissions
- Comprehensive audit logging
- CSRF protection
- SQL injection prevention
- XSS protection
- Password hashing
- Session management
- API rate limiting

## 📈 Performance Features

- Database query optimization
- Redis caching support
- Indexed database queries
- Pagination for large datasets
- Efficient relationship loading
- Background job processing ready

## 🏗️ Architecture

### MVC Pattern
- **Models**: Eloquent ORM with relationships
- **Views**: Blade templates (ready for implementation)
- **Controllers**: Comprehensive business logic

### API Architecture
- RESTful design
- JSON responses
- Token authentication
- Consistent error handling
- Comprehensive status codes

### Database Design
- Normalized schema
- Foreign key constraints
- Soft deletes
- Audit trail
- Indexed queries

## 📞 Support

For technical support or questions:
- Check the issue tracker
- Review the documentation
- Contact the development team

## 🔄 Future Phases

### Phase 2 - Enhanced Management
- Historical occupancy tracking
- Maintenance request system
- Enhanced contact management
- Improved reporting

### Phase 3 - Financial Management
- Invoice generation
- Payment tracking
- Financial reporting
- Late payment management

### Phase 4 - Communication & Scheduling
- Email/SMS notifications
- Maintenance scheduling
- Automated reminders
- Template management

### Phase 5 - Advanced Features
- Advanced analytics
- Third-party integrations
- Mobile app
- API enhancements

## 📝 License

This project is proprietary software. All rights reserved.

## 🤝 Contributing

This is a private project. Contributions are managed internally.

---

**Independent Living Housing Management System - Phase 1**  
*Built with Laravel 10.x - Professional Housing Management Solution*