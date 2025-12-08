# GitHub Actions CI/CD Setup Guide

This guide explains how to configure GitHub Actions for automated deployment of the Smart Tailoring Service.

## Overview

The CI/CD pipeline automatically:
1. Runs syntax checks on all PHP files
2. Validates composer.json
3. Tests database connection
4. Runs migrations
5. Deploys to production server via SSH
6. Performs health check after deployment

## Required GitHub Secrets

Navigate to your GitHub repository → Settings → Secrets and variables → Actions → New repository secret

Add the following secrets:

### 1. SSH_HOST
The hostname or IP address of your production server.

**Example:** `example.com` or `192.168.1.100`

### 2. SSH_USER
The SSH username for connecting to your server.

**Example:** `ubuntu` or `your-username`

### 3. SSH_PORT
The SSH port (usually 22).

**Example:** `22`

### 4. SSH_PRIVATE_KEY
Your SSH private key for authentication.

**How to generate:**
```bash
# On your local machine
ssh-keygen -t rsa -b 4096 -C "github-actions-deploy"

# Save to: ~/.ssh/github_actions_deploy
# Do NOT set a passphrase (leave empty)

# Copy the private key
cat ~/.ssh/github_actions_deploy
```

**Copy the entire private key including:**
```
-----BEGIN OPENSSH PRIVATE KEY-----
...content...
-----END OPENSSH PRIVATE KEY-----
```

**Add public key to server:**
```bash
# Copy public key
cat ~/.ssh/github_actions_deploy.pub

# On your server
nano ~/.ssh/authorized_keys
# Paste the public key on a new line
```

### 5. DEPLOY_PATH
The absolute path on your server where the application should be deployed.

**Example:** `/var/www/html` or `/home/username/public_html`

### 6. APP_URL
The full URL of your production application (for health checks).

**Example:** `https://yourdomain.com`

## Production Server Setup

### 1. Install Required Software

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y

# Install PHP 8.2
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl -y

# Install MySQL
sudo apt install mysql-server -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo systemctl restart apache2
```

### 2. Configure Apache Virtual Host

```bash
sudo nano /etc/apache2/sites-available/smart-tailoring.conf
```

Add configuration:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html/smart-tailoring

    <Directory /var/www/html/smart-tailoring>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/smart-tailoring-error.log
    CustomLog ${APACHE_LOG_DIR}/smart-tailoring-access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite smart-tailoring
sudo systemctl reload apache2
```

### 3. Setup MySQL Database

```bash
sudo mysql
```

```sql
CREATE DATABASE smart_tailoring CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tailoring_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON smart_tailoring.* TO 'tailoring_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Create .env File on Server

```bash
cd /var/www/html/smart-tailoring
nano .env
```

Add production configuration:
```env
# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_HOST=localhost
DB_NAME=smart_tailoring
DB_USER=tailoring_user
DB_PASS=your_secure_password

# SMTP Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
SMTP_FROM=your-email@gmail.com
SMTP_FROM_NAME="Smart Tailoring Service"

# Session Security
SESSION_LIFETIME=7200
SESSION_SECURE=true
SESSION_HTTPONLY=true

# Connection Pool
DB_POOL_MIN=2
DB_POOL_MAX=10
```

### 5. Set File Permissions

```bash
cd /var/www/html
sudo chown -R www-data:www-data smart-tailoring
sudo find smart-tailoring -type d -exec chmod 755 {} \;
sudo find smart-tailoring -type f -exec chmod 644 {} \;
sudo chmod 755 smart-tailoring/uploads/profiles
sudo chmod 755 smart-tailoring/uploads/shops
```

### 6. Setup SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Get certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is set up automatically
sudo certbot renew --dry-run
```

### 7. Configure Firewall

```bash
# Allow HTTP and HTTPS
sudo ufw allow 'Apache Full'

# Allow SSH (important!)
sudo ufw allow OpenSSH

# Enable firewall
sudo ufw enable
```

## Manual Deployment (First Time)

Before setting up automated deployment, do a manual deployment first:

```bash
# On your local machine
cd smart-tailoring
composer install --no-dev --optimize-autoloader

# Upload to server (replace with your details)
rsync -avz --exclude='.git' \
           --exclude='.github' \
           --exclude='.env' \
           --exclude='*.md' \
           -e ssh \
           . username@yourserver.com:/var/www/html/smart-tailoring/

# SSH into server
ssh username@yourserver.com

# Navigate to project
cd /var/www/html/smart-tailoring

# Run migrations
php database/migrate.php run

# Test health endpoint
curl http://localhost/api/health.php
```

## Triggering Deployments

### Automatic Deployment
Push to main branch:
```bash
git add .
git commit -m "Update feature"
git push origin main
```

The GitHub Action will automatically:
- Run tests
- Deploy to production
- Run migrations
- Perform health check

### Manual Deployment
Go to your GitHub repository:
1. Click "Actions" tab
2. Select "Deploy to Production" workflow
3. Click "Run workflow" button
4. Select branch and click "Run workflow"

## Monitoring Deployments

### View Workflow Status
- GitHub repository → Actions tab
- Click on the workflow run
- View logs for each step

### Check Application Health
```bash
curl https://yourdomain.com/api/health.php
```

Expected response:
```json
{
    "status": "ok",
    "timestamp": "2024-01-01T12:00:00+00:00",
    "environment": "production",
    "checks": {
        "database": {
            "status": "ok",
            "message": "Database connection successful"
        },
        "uploads": {
            "status": "ok",
            "message": "Uploads directory is writable"
        },
        "session": {
            "status": "ok",
            "message": "Session system operational"
        }
    }
}
```

## Rollback Procedure

If deployment fails, the workflow automatically keeps a backup:

```bash
# SSH into server
ssh username@yourserver.com

# Navigate to deploy path
cd /var/www/html

# List backups
ls -la smart-tailoring-backup-*

# Restore from backup
mv smart-tailoring smart-tailoring-failed
mv smart-tailoring-backup-YYYYMMDD-HHMMSS smart-tailoring

# Restart Apache
sudo systemctl restart apache2
```

## Troubleshooting

### Deployment Fails at SSH Step
- Verify SSH_PRIVATE_KEY is correct (including headers)
- Check SSH_HOST, SSH_USER, SSH_PORT are correct
- Ensure public key is in server's `~/.ssh/authorized_keys`

### Database Migration Fails
- Check database credentials in `.env`
- Ensure database user has proper permissions
- Verify migrations haven't already been run

### Health Check Fails
- Check Apache error logs: `sudo tail -f /var/log/apache2/smart-tailoring-error.log`
- Verify `.env` file exists on server
- Check file permissions
- Ensure database is accessible

### Permission Denied Errors
```bash
sudo chown -R www-data:www-data /var/www/html/smart-tailoring
```

## Best Practices

1. **Always test in development first** - Push to a `develop` branch before `main`
2. **Monitor logs after deployment** - Check for errors immediately after deployment
3. **Keep backups** - The workflow creates automatic backups, but also do manual backups
4. **Test migrations** - Test migrations locally before deploying
5. **Use environment variables** - Never commit sensitive data to repository
6. **Enable error logging** - Check logs regularly for issues

## Security Notes

- Never commit `.env` file to repository
- Use strong passwords for database users
- Keep SSH keys secure and never share them
- Enable firewall on production server
- Keep software updated (PHP, Apache, MySQL)
- Monitor failed login attempts
- Use HTTPS in production (Let's Encrypt is free)

## Additional Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [SSH Key Authentication](https://www.ssh.com/academy/ssh/keygen)
- [Let's Encrypt SSL](https://letsencrypt.org/getting-started/)
- [Apache Configuration](https://httpd.apache.org/docs/2.4/)
