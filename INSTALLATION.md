# Installation Guide - Independent Living Housing Management System

This guide provides detailed instructions for installing and setting up the Independent Living Housing Management System Phase 1.

## 📋 Prerequisites

Before beginning the installation, ensure you have the following requirements:

### Required Software
- **PHP 8.2 or higher** with the following extensions:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - Fileinfo
  - MySQL/MariaDB extensions
- **Composer** (latest version)
- **MariaDB 10.6+** or **MySQL 8.0+**
- **Web server** (Apache, Nginx, or PHP built-in server)
- **Redis** (optional, for caching and queue processing)

### System Requirements
- **Memory**: Minimum 512MB RAM, recommended 1GB+
- **Disk Space**: Minimum 500MB for application files
- **Operating System**: Linux, Windows, or macOS

## 🚀 Installation Steps

### 1. Download and Extract

Clone or download the project files to your web server directory:

```bash
# Clone from repository
git clone <repository-url> housing-management
cd housing-management

# Or extract from archive
unzip housing-management.zip
cd housing-management
```

### 2. Install Dependencies

Install PHP dependencies using Composer:

```bash
composer install --optimize-autoloader --no-dev
```

**Note**: Remove `--no-dev` if you want to install development dependencies.

### 3. Environment Configuration

Create and configure your environment file:

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

#### Create Database
Create a new database for the housing management system:

```sql
-- MariaDB/MySQL
CREATE DATABASE housing_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'housing_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON housing_management.* TO 'housing_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Configure Database Connection
Edit your `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=housing_management
DB_USERNAME=housing_user
DB_PASSWORD=secure_password
```

### 5. Application Configuration

Configure the remaining environment variables in your `.env` file:

```env
# Application Settings
APP_NAME="Housing Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=housing_management
DB_USERNAME=housing_user
DB_PASSWORD=secure_password

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Redis Configuration (optional)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache Configuration
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Housing Management Settings
HOUSING_PAGINATION_LIMIT=25
HOUSING_MAX_UPLOAD_SIZE=10240
HOUSING_DEFAULT_ROLE=viewer
```

### 6. Database Migration and Seeding

Run database migrations to create all necessary tables:

```bash
# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed

# Or run both commands together
php artisan migrate --seed
```

### 7. Storage and Permissions

Set up the storage directories and permissions:

```bash
# Create storage symlink
php artisan storage:link

# Set proper permissions (Linux/macOS)
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# If using Apache/Nginx, ensure web server can write to these directories
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

### 8. Web Server Configuration

#### Apache Configuration

Create a virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /path/to/housing-management/public
    
    <Directory /path/to/housing-management/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/housing-management_error.log
    CustomLog ${APACHE_LOG_DIR}/housing-management_access.log combined
</VirtualHost>
```

#### Nginx Configuration

Create a server block configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/housing-management/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 9. SSL Configuration (Recommended)

For production environments, configure SSL:

```bash
# Using Let's Encrypt (Certbot)
sudo certbot --apache -d yourdomain.com

# Or for Nginx
sudo certbot --nginx -d yourdomain.com
```

### 10. Final Verification

Test your installation:

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check system status
php artisan about

# Start development server (for testing)
php artisan serve --host=0.0.0.0 --port=8000
```

## 🔧 Configuration Options

### Redis Configuration (Optional)

For better performance, configure Redis:

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Email Configuration

Configure email settings based on your provider:

#### Gmail SMTP
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

#### SendGrid
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
```

### File Upload Configuration

Configure file upload limits:

```env
HOUSING_MAX_UPLOAD_SIZE=10240
ALLOWED_FILE_TYPES=pdf,doc,docx,jpg,jpeg,png,gif,txt
```

## 🎯 Post-Installation Steps

### 1. Create Admin User

After installation, log in with the default admin account:

- **Email**: admin@housingmanagement.com
- **Password**: admin123

**Important**: Change this password immediately after first login!

### 2. Configure System Settings

1. Update user profiles and passwords
2. Configure email settings
3. Set up housing types and units
4. Configure permissions as needed

### 3. Security Checklist

- [ ] Change default admin password
- [ ] Remove or secure test user accounts
- [ ] Configure firewall rules
- [ ] Set up SSL certificates
- [ ] Configure regular database backups
- [ ] Set up monitoring and logging

### 4. Backup Configuration

Set up regular backups:

```bash
# Create backup script
cat > /home/backup-housing.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/housing-management"
DB_NAME="housing_management"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u housing_user -p$DB_PASSWORD $DB_NAME > $BACKUP_DIR/database_$DATE.sql

# Application backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /path/to/housing-management --exclude=vendor --exclude=node_modules

# Keep only last 7 days of backups
find $BACKUP_DIR -type f -mtime +7 -delete
EOF

# Make executable
chmod +x /home/backup-housing.sh

# Add to crontab for daily backups
echo "0 2 * * * /home/backup-housing.sh" | crontab -
```

## 🔍 Troubleshooting

### Common Issues

#### 1. Database Connection Error
```bash
# Check database credentials
php artisan tinker
>>> DB::connection()->getPdo();

# Verify database server is running
sudo systemctl status mysql
```

#### 2. Permission Errors
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### 3. Missing Extensions
```bash
# Check PHP extensions
php -m | grep -E "(pdo|mbstring|tokenizer|xml|ctype|json|bcmath|fileinfo)"

# Install missing extensions (Ubuntu/Debian)
sudo apt-get install php8.2-mysql php8.2-mbstring php8.2-xml php8.2-bcmath
```

#### 4. Application Key Error
```bash
# Generate new application key
php artisan key:generate
```

### Performance Optimization

#### 1. Enable Caching
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

#### 2. Optimize Composer
```bash
# Optimize autoloader
composer dump-autoload --optimize --no-dev
```

#### 3. Configure OPcache
Add to your PHP configuration:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

## 📊 System Monitoring

### Log Files
Monitor these log files:

- **Application**: `storage/logs/laravel.log`
- **Web Server**: Check Apache/Nginx error logs
- **Database**: Check MySQL/MariaDB error logs

### Health Check
Create a health check endpoint:

```bash
# Add to crontab for monitoring
*/5 * * * * curl -f http://yourdomain.com/up || echo "Housing Management System is down" | mail -s "Alert" admin@yourdomain.com
```

## 🆘 Support

If you encounter issues during installation:

1. Check the log files for error messages
2. Verify all prerequisites are met
3. Review the configuration settings
4. Consult the troubleshooting section above

## 🎉 Congratulations!

Your Independent Living Housing Management System is now installed and ready to use. Access the application at your configured domain or IP address.

**Default Login:**
- URL: `http://yourdomain.com`
- Email: `admin@housingmanagement.com`
- Password: `admin123`

Remember to change the default password and configure the system according to your needs!