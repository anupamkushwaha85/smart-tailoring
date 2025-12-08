# 🚀 Deployment Guide - Smart Tailoring Service

## Pre-Deployment Checklist

### 1. Run Deployment Readiness Check
```
http://your-domain.com/deployment_check.php
```
Ensure all critical issues are resolved before proceeding.

---

## Production Environment Setup

### Step 1: Server Requirements

**Minimum Requirements:**
- PHP 7.4 or higher (8.0+ recommended)
- MySQL 5.7 or higher (8.0+ recommended)
- Apache/Nginx web server
- SSL Certificate (for HTTPS)
- 512MB RAM minimum (1GB+ recommended)
- 5GB disk space

**Required PHP Extensions:**
- mysqli
- pdo_mysql
- curl
- json
- mbstring
- openssl
- fileinfo

**Verify PHP Extensions:**
```bash
php -m | grep -E "mysqli|pdo|curl|json|mbstring|openssl"
```

---

### Step 2: Database Setup

**1. Create Production Database:**
```sql
CREATE DATABASE smart_tailoring CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tailoring_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON smart_tailoring.* TO 'tailoring_user'@'localhost';
FLUSH PRIVILEGES;
```

**2. Import Database Schema:**
```bash
mysql -u tailoring_user -p smart_tailoring < database/schema.sql
```

**3. Run Migrations:**
```bash
php migrate.php
```

---

### Step 3: Environment Configuration

**1. Copy Environment File:**
```bash
cp .env.example .env
```

**2. Configure .env for Production:**
```env
# Database Configuration
DB_HOST=localhost
DB_USER=tailoring_user
DB_PASS=your_strong_password
DB_NAME=smart_tailoring

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# SMTP Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_FROM_EMAIL=noreply@yourdomain.com
SMTP_FROM_NAME="Smart Tailoring Service"

# Session Configuration
SESSION_LIFETIME=3600
SESSION_SECURE=true
SESSION_HTTPONLY=true

# Connection Pool
USE_CONNECTION_POOL=true
MAX_CONNECTIONS=10
MIN_CONNECTIONS=2
```

**3. Set Proper Permissions:**
```bash
chmod 600 .env
chmod 600 config/email.php
chmod 755 uploads/
chmod 755 uploads/profiles/
chmod 755 uploads/shops/
```

---

### Step 4: Security Hardening

**1. Remove Test Files:**
```bash
rm -f test_*.php
rm -f integration_test.php
rm -f run_tests.php
rm -f deployment_check.php
```

**2. Update .htaccess (Apache):**
```apache
# Prevent directory browsing
Options -Indexes

# Prevent access to sensitive files
<FilesMatch "\.(env|log|sql|bak|config)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Enable HTTPS redirect
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Prevent PHP execution in uploads
<Directory "uploads">
    php_flag engine off
</Directory>
```

**3. Configure PHP Security (php.ini):**
```ini
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
max_execution_time = 30
max_input_time = 60
memory_limit = 256M
post_max_size = 10M
upload_max_filesize = 5M
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

---

### Step 5: SSL Certificate Setup

**Using Let's Encrypt (Free):**
```bash
# Install certbot
sudo apt-get install certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

**Verify SSL:**
- Visit: https://www.ssllabs.com/ssltest/
- Enter your domain
- Aim for A+ rating

---

### Step 6: File Structure & Permissions

**Correct Ownership:**
```bash
chown -R www-data:www-data /var/www/smart-tailoring
```

**Set Directory Permissions:**
```bash
find /var/www/smart-tailoring -type d -exec chmod 755 {} \;
find /var/www/smart-tailoring -type f -exec chmod 644 {} \;
```

**Writable Directories:**
```bash
chmod 775 uploads/
chmod 775 uploads/profiles/
chmod 775 uploads/shops/
chmod 775 cache/ (if exists)
```

---

### Step 7: Create Admin Account

**Run this SQL or use admin creation script:**
```sql
INSERT INTO admins (username, password, full_name, email, role, is_active) 
VALUES (
    'admin',
    '$2y$10$abcdefghijklmnopqrstuvwxyz...', -- Use password_hash('your_password', PASSWORD_DEFAULT)
    'System Administrator',
    'admin@yourdomain.com',
    'super_admin',
    1
);
```

**Or use PHP script:**
```bash
php -r "echo password_hash('YourSecurePassword123!', PASSWORD_DEFAULT);"
```

---

### Step 8: Configure Cron Jobs

**1. Database Cleanup (Optional):**
```bash
# Add to crontab: crontab -e
0 2 * * * cd /var/www/smart-tailoring && php scripts/cleanup.php
```

**2. Session Cleanup:**
```bash
0 */6 * * * cd /var/www/smart-tailoring && php scripts/session_cleanup.php
```

---

### Step 9: Performance Optimization

**1. Enable OPcache (php.ini):**
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

**2. Enable Gzip Compression (.htaccess):**
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

**3. Browser Caching (.htaccess):**
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

### Step 10: Monitoring & Logging

**1. Error Logging:**
Create `logs/` directory:
```bash
mkdir -p logs
chmod 775 logs
```

**2. Configure Error Handling:**
Ensure `config/db.php` has:
```php
if ($_ENV['APP_ENV'] === 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
}
```

**3. Monitor Logs:**
```bash
tail -f logs/php-errors.log
```

---

### Step 11: Backup Strategy

**1. Database Backup Script:**
```bash
#!/bin/bash
# backup.sh
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u tailoring_user -p smart_tailoring > backups/db_$DATE.sql
gzip backups/db_$DATE.sql

# Keep only last 7 days
find backups/ -name "*.sql.gz" -mtime +7 -delete
```

**2. Schedule Backups:**
```bash
# Daily backup at 3 AM
0 3 * * * /var/www/smart-tailoring/backup.sh
```

**3. Files Backup:**
```bash
tar -czf backups/uploads_$(date +%Y%m%d).tar.gz uploads/
```

---

### Step 12: Testing in Production

**1. Health Check:**
```
https://yourdomain.com/api/health.php
```

**2. Test Key Features:**
- [ ] Admin login works
- [ ] Customer registration works
- [ ] Tailor registration works
- [ ] Order creation works
- [ ] File uploads work
- [ ] Email sending works
- [ ] Notifications work

**3. Load Testing (Optional):**
```bash
# Install Apache Bench
sudo apt-get install apache2-utils

# Test with 100 requests, 10 concurrent
ab -n 100 -c 10 https://yourdomain.com/
```

---

## Post-Deployment

### Immediate Actions

1. **Verify SSL is Working:**
   - Check padlock icon in browser
   - Test all pages load via HTTPS

2. **Test Core Functionality:**
   - Register test customer
   - Register test tailor
   - Create test order
   - Upload test images

3. **Monitor Error Logs:**
   ```bash
   tail -f logs/php-errors.log
   ```

4. **Set Up Monitoring:**
   - Use services like Uptime Robot, Pingdom
   - Monitor: uptime, response time, SSL expiry

### Security Checklist

- [ ] HTTPS enabled and working
- [ ] .env file permissions set to 600
- [ ] Test files removed
- [ ] Database credentials are strong
- [ ] Error display is off
- [ ] File upload directory not executable
- [ ] Admin account has strong password
- [ ] SMTP credentials configured
- [ ] Session security enabled
- [ ] CSRF protection working

### Performance Checklist

- [ ] OPcache enabled
- [ ] Gzip compression enabled
- [ ] Browser caching configured
- [ ] Database indexed properly
- [ ] Connection pooling enabled
- [ ] Static assets minified (if applicable)

---

## Maintenance

### Daily
- Monitor error logs
- Check disk space
- Verify backups completed

### Weekly
- Review admin activity logs
- Check system performance
- Update content if needed

### Monthly
- Security updates (PHP, MySQL)
- Review user feedback
- Database optimization
- SSL certificate check

### Quarterly
- Full security audit
- Performance review
- Backup restoration test
- Disaster recovery drill

---

## Troubleshooting

### Common Issues

**1. 500 Internal Server Error**
- Check Apache error logs: `tail -f /var/log/apache2/error.log`
- Check PHP error logs: `tail -f logs/php-errors.log`
- Verify file permissions
- Check .htaccess syntax

**2. Database Connection Failed**
- Verify credentials in .env
- Check MySQL is running: `sudo systemctl status mysql`
- Test connection: `mysql -u user -p database`

**3. File Upload Not Working**
- Check directory permissions: `ls -la uploads/`
- Verify PHP upload settings
- Check disk space: `df -h`

**4. Email Not Sending**
- Verify SMTP credentials
- Check firewall allows port 587/465
- Test with simple mail script
- Check spam folder

**5. Session Issues**
- Clear browser cookies
- Check session directory permissions
- Verify session configuration in php.ini

---

## Rollback Procedure

If deployment fails:

1. **Restore Database:**
   ```bash
   mysql -u user -p database < backups/db_backup.sql
   ```

2. **Restore Files:**
   ```bash
   tar -xzf backups/files_backup.tar.gz -C /var/www/
   ```

3. **Revert Configuration:**
   ```bash
   cp .env.backup .env
   ```

---

## Support & Documentation

- **Technical Issues:** Check logs first
- **User Guides:** Create in `/docs` directory
- **API Documentation:** Document all endpoints
- **Database Schema:** Keep ER diagrams updated

---

## Success Criteria

Deployment is successful when:

✅ Site is accessible via HTTPS  
✅ All core features work  
✅ No errors in logs  
✅ Backups are automated  
✅ Monitoring is active  
✅ Performance is acceptable  
✅ Security score is A+  

---

**Deployment Date:** _________________  
**Deployed By:** _________________  
**Version:** 1.0  
**Next Review:** _________________
