# Production Configuration Instructions

## CRITICAL: Update .env for Production

Open the `.env` file and update the following values:

```env
# 1. Database Configuration (REQUIRED)
DB_HOST=localhost                    # Your database host
DB_USER=your_db_user                # Database username (NOT 'root' in production!)
DB_PASS=YourStrongPassword123!      # SET A STRONG PASSWORD!
DB_NAME=smart_tailoring             # Database name

# 2. Application Settings (REQUIRED)
APP_ENV=production                  # MUST be 'production'
APP_DEBUG=false                     # MUST be 'false' 
APP_URL=https://yourdomain.com      # Your actual domain with HTTPS

# 3. SMTP Configuration (Email functionality)
SMTP_HOST=smtp.gmail.com            # Your SMTP server
SMTP_PORT=587                       # SMTP port
SMTP_USERNAME=your-email@gmail.com  # Your email
SMTP_PASSWORD=your-app-password     # Gmail app password or SMTP password
SMTP_FROM_EMAIL=noreply@yourdomain.com
SMTP_FROM_NAME="Smart Tailoring Service"

# 4. Session Configuration (Security)
SESSION_LIFETIME=3600               # Session timeout in seconds
SESSION_SECURE=true                 # MUST be 'true' with HTTPS
SESSION_HTTPONLY=true               # Keep as 'true'

# 5. Performance Settings
USE_CONNECTION_POOL=true            # Enable connection pooling
MAX_CONNECTIONS=10                  # Maximum database connections
MIN_CONNECTIONS=2                   # Minimum database connections
```

## Quick Production Setup Checklist

### ✅ Before Deployment

1. **Update .env file** (see above)
   ```bash
   nano .env
   ```

2. **Set file permissions**
   ```bash
   chmod 600 .env
   chmod 755 uploads/
   chmod 755 uploads/profiles/
   chmod 755 uploads/shops/
   ```

3. **Create production database**
   ```sql
   CREATE DATABASE smart_tailoring;
   CREATE USER 'tailoring_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';
   GRANT ALL ON smart_tailoring.* TO 'tailoring_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

4. **Import database**
   ```bash
   php migrate.php
   ```

5. **Create admin account**
   ```bash
   # Generate password hash
   php -r "echo password_hash('YourAdminPassword', PASSWORD_DEFAULT);"
   
   # Then insert into database
   mysql -u tailoring_user -p smart_tailoring
   INSERT INTO admins (username, password, full_name, email, role, is_active) 
   VALUES ('admin', 'PASTE_HASH_HERE', 'Administrator', 'admin@domain.com', 'super_admin', 1);
   ```

6. **Configure SSL/HTTPS**
   - Install SSL certificate (Let's Encrypt recommended)
   - Update APP_URL to use https://
   - Set SESSION_SECURE=true

7. **Remove test files**
   ```bash
   rm -f test_*.php
   rm -f integration_test.php
   rm -f run_tests.php
   rm -f deployment_check.php
   rm -f setup_deployment.php
   ```

8. **Run final check**
   Before removing deployment_check.php, run it one last time:
   ```
   https://yourdomain.com/deployment_check.php
   ```
   Ensure all checks pass!

### 🔒 Security Hardening

1. **Update .htaccess** (if using Apache)
   ```apache
   # Force HTTPS
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   
   # Prevent directory listing
   Options -Indexes
   
   # Block access to sensitive files
   <FilesMatch "\.(env|log|sql|bak)$">
       Require all denied
   </FilesMatch>
   ```

2. **PHP Configuration** (php.ini or .htaccess)
   ```ini
   display_errors = Off
   log_errors = On
   expose_php = Off
   ```

### 📊 Verify Deployment

After deployment, test:

1. ✅ Admin login: `https://yourdomain.com/admin/`
2. ✅ Customer registration: `https://yourdomain.com/`
3. ✅ Tailor registration: `https://yourdomain.com/`
4. ✅ Health check: `https://yourdomain.com/api/health.php`
5. ✅ File uploads work
6. ✅ Emails send correctly

### 🆘 Troubleshooting

**Error: Database connection failed**
- Check DB credentials in .env
- Verify database exists and user has permissions

**Error: File upload not working**
- Check uploads/ directory permissions (755)
- Verify PHP upload_max_filesize

**Error: Emails not sending**
- Verify SMTP credentials
- Check firewall allows outbound port 587
- For Gmail: Enable "Less secure apps" or use App Password

**Error: Session issues**
- If using HTTPS, ensure SESSION_SECURE=true
- Check PHP session configuration

---

## 🎯 Final Pre-Flight Checklist

Before going live:

- [ ] .env configured for production
- [ ] APP_DEBUG=false
- [ ] Strong database password set
- [ ] HTTPS enabled and working
- [ ] Admin account created
- [ ] Test files removed
- [ ] Backups configured
- [ ] Monitoring set up
- [ ] All features tested
- [ ] Error logging enabled

---

**Once everything is configured, your site is ready to launch! 🚀**
