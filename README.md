# Independent Living Housing Management System - Phase 1

A comprehensive web-based housing management system built with Laravel 10.x and MariaDB for managing independent living housing units, occupiers, and related documentation.

## Features

### Phase 1 - Foundation & Core Functionality ✅

- **User Management**: Role-based authentication (Admin, Manager, Viewer)
- **Housing Unit Management**: Complete CRUD operations for housing units
- **Occupier Management**: Track current occupiers with lease information
- **Notes & Documentation**: Categorized notes with file attachments
- **Reporting System**: Occupancy, vacancy, and activity reports
- **API Support**: Complete RESTful API with Laravel Sanctum authentication
- **Audit Logging**: Comprehensive activity tracking

## Technology Stack

- **Backend**: PHP 8.1+ with Laravel 10.x
- **Database**: MariaDB 10.6+ / MySQL 8.0+
- **Frontend**: Laravel Blade templates with Bootstrap 5
- **Authentication**: Laravel Sanctum for API authentication
- **File Storage**: Laravel Storage (local/cloud ready)
- **Caching**: Redis support ready

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- MariaDB 10.6+ or MySQL 8.0+
- Node.js and npm (for frontend assets)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd housing-management
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database configuration**
   Edit `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=housing_management
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seed data**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Create storage symlink**
   ```bash
   php artisan storage:link
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

   The application will be available at `http://localhost:8000`

## Default User Accounts

| Role | Email | Password | Access Level |
|------|--------|----------|-------------|
| Admin | admin@housingmanagement.com | admin123 | Full system access |
| Manager | manager@housingmanagement.com | manager123 | Housing & occupier management |
| Viewer | viewer@housingmanagement.com | viewer123 | Read-only access |

## API Usage

### Authentication

**Login:**
```bash
POST /api/login
Content-Type: application/json

{
  "email": "admin@housingmanagement.com",
  "password": "admin123"
}
```

**API Endpoints:**
- `GET /api/user` - Get current user
- `GET /api/housing-units` - List housing units
- `POST /api/housing-units` - Create housing unit
- `GET /api/occupiers` - List occupiers
- `GET /api/reports/occupancy` - Occupancy report
- `GET /api/reports/vacancy` - Vacancy report
- `GET /api/reports/activity` - Activity report

All API endpoints require authentication using Bearer token.

## Database Schema

### Core Tables

- **users** - System users with roles
- **roles** - User roles (Admin, Manager, Viewer)
- **permissions** - Granular permissions
- **role_permissions** - Role-permission relationships
- **housing_units** - Housing unit information
- **housing_types** - Types of housing (apartment, house, etc.)
- **occupiers** - Current and former occupiers
- **notes** - Documentation and notes system
- **audit_logs** - System activity tracking

## Module Overview

### 1. User Management
- User registration and authentication
- Role-based access control
- Profile management
- Password reset functionality

### 2. Housing Unit Management
- Add/Edit/Delete housing units
- Unit properties and details
- Occupancy status tracking
- Condition grading

### 3. Occupier Management
- Current occupier tracking
- Lease information management
- Emergency contact details
- Move-in/move-out functionality

### 4. Notes & Documentation
- Categorized notes system
- File attachments support
- Priority levels
- Privacy controls

### 5. Reporting System
- Occupancy summary reports
- Vacancy analysis
- User activity reports
- Export functionality

## Security Features

- Role-based access control
- CSRF protection
- XSS protection
- SQL injection prevention
- Audit logging
- Session management
- Password hashing

## Future Phases

### Phase 2 - Enhanced Management & History
- Historical occupancy tracking
- Basic maintenance request system
- Enhanced contact management
- Improved reporting

### Phase 3 - Financial Management
- Invoice generation
- Payment tracking
- Financial reporting
- Late payment management

### Phase 4 - Communication & Scheduling
- Email/SMS system
- Calendar functionality
- Automated notifications
- Template management

### Phase 5 - Advanced Features
- Advanced analytics
- API enhancements
- Third-party integrations
- Mobile app support

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests
5. Submit a pull request

## Support

For support and questions, please contact the development team or create an issue in the repository.

## License

This project is proprietary software. All rights reserved.