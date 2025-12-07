# Smart Tailoring Service

A comprehensive web-based platform connecting customers with tailors for custom clothing orders. Features include measurement management, order tracking, real-time notifications, and geolocation-based tailor discovery.

## Features

### For Customers
- **User Registration & Authentication** - Secure account creation with email OTP verification
- **Measurement Management** - Save, edit, and manage body measurements with custom notes
- **Tailor Discovery** - Find nearby tailors using geolocation and map integration
- **Order Placement** - Create orders with custom measurements and instructions
- **Order Tracking** - Real-time status updates from placed to completed
- **Review System** - Rate and review tailors after service completion
- **Notifications** - Real-time alerts for order status changes

### For Tailors
- **Profile Management** - Showcase shop details, location, and contact information
- **Order Management** - View, accept, and update order statuses
- **Customer Measurements** - Access customer measurements and notes
- **Fitting Scheduler** - Set fitting dates and manage appointments
- **Location Services** - Display shop location on interactive maps

### For Administrators
- **Dashboard** - Overview of system statistics and activity
- **User Management** - Manage customers, tailors, and admins
- **Order Oversight** - Monitor all orders across the platform
- **Contact Management** - Handle customer inquiries and support requests

## Technology Stack

- **Backend**: PHP 8.2+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Maps**: MapLibre GL JS with OpenStreetMap
- **Email**: PHPMailer
- **Environment**: PHP dotenv for configuration management

## Requirements

- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- Composer (for dependency management)
- OpenSSL extension (for secure sessions)
- PDO MySQL extension

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/anupamkushwaha85/smart-tailoring.git
cd smart-tailoring
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit `.env` with your configuration:

```env
# Application Settings
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/smart-tailoring

# Database Configuration
DB_HOST=localhost
DB_NAME=smart_tailoring
DB_USER=root
DB_PASS=

# SMTP Configuration (for email OTP)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
SMTP_FROM=your-email@gmail.com
SMTP_FROM_NAME="Smart Tailoring Service"

# Session Security
SESSION_LIFETIME=7200
SESSION_SECURE=false
SESSION_HTTPONLY=true

# Connection Pool Settings
DB_POOL_MIN=2
DB_POOL_MAX=10
```

### 4. Database Setup

Create the database:

```sql
CREATE DATABASE smart_tailoring CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run migrations:

```bash
php database/migrate.php run
```

This will create all necessary tables:
- users (customers and tailors)
- measurements
- measurement_fields
- orders
- reviews
- notifications
- admins
- contacts

### 5. Configure Apache

#### Development (XAMPP/Local)

Ensure `.htaccess` is enabled. The file includes:
- HTTPS redirect (commented for local development)
- Asset compression
- Browser caching
- Security headers (CSP, HSTS)

#### Production

1. Uncomment HTTPS redirect in `.htaccess`:
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteCond %{HTTPS} off
       RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   </IfModule>
   ```

2. Uncomment HSTS header:
   ```apache
   Header set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
   ```

3. Update `.env`:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   SESSION_SECURE=true
   ```

### 6. File Permissions

Ensure upload directories are writable:

```bash
chmod 755 uploads/profiles
chmod 755 uploads/shops
```

### 7. Default Admin Account

Create an admin account in the database:

```sql
INSERT INTO admins (username, password, name, email, role, created_at)
VALUES ('admin', '$2y$10$[hashed_password]', 'Administrator', 'anupamkushwaha639@gmail.com', 'super_admin', NOW());
```

Generate password hash:
```php
<?php echo password_hash('your_password', PASSWORD_DEFAULT); ?>
```

## Project Structure

```
smart-tailoring/
├── admin/              # Admin panel
│   ├── api/           # Admin API endpoints
│   ├── assets/        # Admin CSS/JS
│   └── includes/      # Admin navigation and security
├── api/               # REST API endpoints
│   ├── auth/          # Authentication
│   ├── measurements/  # Measurement management
│   ├── notifications/ # Notification system
│   ├── orders/        # Order management
│   ├── profile/       # Profile management
│   └── reviews/       # Review system
├── assets/            # Frontend assets
│   ├── css/          # Stylesheets
│   ├── images/       # Images and icons
│   └── js/           # JavaScript files
├── auth/             # Authentication handlers
├── config/           # Configuration files
│   ├── db.php        # Database connection
│   ├── security.php  # Security functions
│   └── session.php   # Session configuration
├── customer/         # Customer dashboard pages
├── database/         # Database migrations
│   └── migrations/   # Migration files
├── models/           # Data models
├── repositories/     # Data access layer
├── services/         # Business logic layer
├── tailor/          # Tailor dashboard pages
├── uploads/         # User-uploaded files
│   ├── profiles/    # Profile images
│   └── shops/       # Shop images
├── utils/           # Utility classes
├── vendor/          # Composer dependencies
├── .env             # Environment configuration (not in git)
├── .env.example     # Example environment file
├── .gitignore       # Git ignore rules
├── .htaccess        # Apache configuration
└── composer.json    # PHP dependencies
```

## API Endpoints

### Health Check
```
GET /api/health.php
```
Returns application status, database connectivity, and system checks.

### Authentication
```
POST /api/auth/register.php
POST /api/auth/login.php
POST /api/auth/logout.php
POST /api/auth/forgot_password.php
```

### Measurements
```
GET    /api/measurements/get_measurements.php
GET    /api/measurements/get_measurement.php?id=1
POST   /api/measurements/save_measurement.php
PUT    /api/measurements/set_default.php
DELETE /api/measurements/delete_measurement.php
```

### Orders
```
GET    /api/orders/get_orders.php
GET    /api/orders/get_customer_orders.php
POST   /api/orders/create_order.php
PUT    /api/orders/update_status.php
PUT    /api/orders/update_fitting_date.php
DELETE /api/orders/cancel_order.php
```

## Testing

### Manual Testing Checklist

1. **User Registration**
   - [ ] Register new customer account
   - [ ] Verify email OTP
   - [ ] Login with credentials

2. **Measurements**
   - [ ] Add new measurement
   - [ ] Edit existing measurement
   - [ ] Set default measurement
   - [ ] Delete measurement

3. **Order Placement**
   - [ ] Search for tailors by location
   - [ ] Create order with saved measurements
   - [ ] Create order with on-the-fly measurements
   - [ ] View order status

4. **Tailor Dashboard**
   - [ ] View incoming orders
   - [ ] Access customer measurements
   - [ ] Update order status
   - [ ] Set fitting dates

5. **Admin Panel**
   - [ ] Login to admin dashboard
   - [ ] View statistics
   - [ ] Manage users
   - [ ] Handle contact requests

### Testing Credentials

**Customer:**
- Email: devesh@gmail.com
- Password: devesh123

**Tailor:** (Register via application)

**Admin:** (Use created admin account)

## Deployment

### GitHub Repository Setup

1. Create new repository on GitHub
2. Add remote:
   ```bash
   git remote add origin https://github.com/anupamkushwaha85/smart-tailoring.git
   ```
3. Push code:
   ```bash
   git add .
   git commit -m "Initial commit"
   git push -u origin main
   ```

### CI/CD with GitHub Actions

See `.github/workflows/deploy.yml` for automated deployment configuration.

### Production Deployment Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Configure HTTPS certificate
- [ ] Uncomment HTTPS redirect in `.htaccess`
- [ ] Uncomment HSTS header in `.htaccess`
- [ ] Set `SESSION_SECURE=true` in `.env`
- [ ] Configure production SMTP credentials
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Enable error logging (not display)
- [ ] Configure backup strategy for database
- [ ] Set up monitoring (health check endpoint)

## Security Features

- **Password Hashing**: bcrypt with automatic salt generation
- **CSRF Protection**: Token-based validation for forms
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Protection**: Input sanitization and output escaping
- **Session Security**: HTTP-only cookies, secure flag, SameSite attribute
- **Session Hijacking Prevention**: User agent validation
- **Session Timeout**: 30-minute inactivity timeout
- **HTTPS Enforcement**: Automatic redirect (production)
- **HSTS**: Strict Transport Security header (production)
- **Content Security Policy**: Restricts resource loading
- **Environment Variables**: Sensitive data in `.env` (not committed)

## Troubleshooting

### Database Connection Errors

Check `.env` configuration:
```bash
php -r "require 'vendor/autoload.php'; \$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); \$dotenv->load(); echo 'DB_HOST: ' . \$_ENV['DB_HOST'] . PHP_EOL;"
```

### Session Issues

Clear sessions:
```bash
rm -rf /path/to/php/sessions/*
```

### Upload Errors

Check directory permissions:
```bash
ls -la uploads/
```

Fix permissions:
```bash
chmod 755 uploads/profiles uploads/shops
```

### Migration Issues

Reset migrations (development only):
```bash
php database/migrate.php reset
php database/migrate.php run
```

## Development

### Adding New Migrations

Create migration file in `database/migrations/`:

```php
<?php
return [
    'description' => 'Add new feature table',
    'up' => "CREATE TABLE feature (...)",
    'down' => "DROP TABLE IF EXISTS feature"
];
```

Run migration:
```bash
php database/migrate.php run
```

### Code Style Guidelines

- Use PSR-12 coding standard
- Document all functions with PHPDoc
- Use type hints for function parameters
- Validate and sanitize all user input
- Use prepared statements for database queries
- Follow repository pattern for data access

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/new-feature`)
3. Commit changes (`git commit -am 'Add new feature'`)
4. Push to branch (`git push origin feature/new-feature`)
5. Create Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Author

**Anupam Kushwaha**
- Email: [anupamkushwaha639@gmail.com](mailto:anupamkushwaha639@gmail.com)
- LinkedIn: [linkedin.com/in/anupamkushwaha85](https://linkedin.com/in/anupamkushwaha85)
- GitHub: [@anupamkushwaha85](https://github.com/anupamkushwaha85)

## Support

For issues and questions:
- **Email**: [anupamkushwaha639@gmail.com](mailto:anupamkushwaha639@gmail.com)
- **LinkedIn**: [linkedin.com/in/anupamkushwaha85](https://linkedin.com/in/anupamkushwaha85)
- **GitHub Issues**: [github.com/anupamkushwaha85/smart-tailoring/issues](https://github.com/anupamkushwaha85/smart-tailoring/issues)

## Changelog

### Version 1.0.0 (Current)
- Initial release
- User authentication with email OTP
- Measurement management system
- Order placement and tracking
- Tailor discovery with maps
- Review and rating system
- Real-time notifications
- Admin panel
- Environment-based configuration
- Comprehensive security features
