# 📧 Email OTP Authentication Setup Guide

## Complete Guide to Set Up Email OTP Verification (100% FREE with Gmail)

---

## 📋 Prerequisites

Before you start, you need:
1. A Gmail account (free)
2. Composer installed on your system
3. XAMPP/MySQL running
4. Access to your database

---

## ⚡ Quick Setup (5 Steps)

### Step 1: Install PHPMailer via Composer

Open terminal in your project root directory:

```bash
cd c:\xampp\htdocs\smart\smart-tailoring
composer require phpmailer/phpmailer
```

If you don't have Composer installed:
- Download from: https://getcomposer.org/download/
- Install it
- Then run the above command

---

### Step 2: Create Gmail App Password

**IMPORTANT**: You cannot use your regular Gmail password. You need an "App Password".

1. Go to your Google Account: https://myaccount.google.com/
2. Click on **Security** (left sidebar)
3. Enable **2-Step Verification** (if not already enabled)
4. After enabling 2-Step Verification, scroll down to find **App passwords**
5. Click on **App passwords**
6. Select:
   - App: **Mail**
   - Device: **Other** (Custom name) - Type: "Smart Tailoring Service"
7. Click **Generate**
8. **COPY THE 16-CHARACTER PASSWORD** (it looks like: `xxxx xxxx xxxx xxxx`)
9. Save this password - you'll need it in the next step

---

### Step 3: Configure Email Settings

1. Open file: `config/email.php`
2. Update these lines with your Gmail details:

```php
// Replace these with your actual Gmail credentials
define('SMTP_USERNAME', 'youremail@gmail.com');  // Your Gmail address
define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx');  // The App Password from Step 2

define('MAIL_FROM_EMAIL', 'youremail@gmail.com');  // Same as SMTP_USERNAME
define('MAIL_REPLY_TO', 'youremail@gmail.com');    // Same as SMTP_USERNAME
```

**Example:**
```php
define('SMTP_USERNAME', 'smarttailoring2025@gmail.com');
define('SMTP_PASSWORD', 'abcd efgh ijkl mnop');  // 16-character App Password
define('MAIL_FROM_EMAIL', 'smarttailoring2025@gmail.com');
define('MAIL_REPLY_TO', 'smarttailoring2025@gmail.com');
```

---

### Step 4: Run Database Migration

Open phpMyAdmin or MySQL command line:
1. Go to: http://localhost/phpmyadmin
2. Select your database: `smart_tailoring`
3. Click on **SQL** tab
4. Copy and paste the entire content from:
   `database/migrations/006_create_email_otp_system.sql`
5. Click **Go** to execute

**OR** Run this command in terminal:

```bash
mysql -u root -p smart_tailoring < database/migrations/006_create_email_otp_system.sql
```

---

### Step 5: Test the System

1. Open your website: http://localhost/smart/smart-tailoring/
2. Click **Login/Register**
3. Fill in registration form
4. Check your email for OTP
5. Enter OTP to verify

---

## 🎯 What Has Been Implemented

### ✅ Features Added:

1. **Email OTP Verification**
   - 6-digit OTP code
   - 10-minute expiry time
   - Maximum 3 verification attempts
   - Rate limiting (1 OTP per 2 minutes)

2. **Registration Flow with OTP**
   - User registers → OTP sent to email
   - User verifies OTP → Email marked as verified
   - Professional email templates

3. **Password Reset with OTP** (Ready to implement)
   - Forgot password → Enter email → Receive OTP
   - Verify OTP → Reset password

4. **Database Changes**
   - New table: `email_otp` (stores OTP codes)
   - Added `email_verified` column to `customers` and `tailors` tables
   - Auto-cleanup of expired OTPs

5. **API Endpoints**
   - `/api/otp/send_otp.php` - Send OTP
   - `/api/otp/verify_otp.php` - Verify OTP
   - `/api/otp/resend_otp.php` - Resend OTP

---

## 🔧 Configuration Options

### OTP Settings (in `config/email.php`):

```php
define('OTP_EXPIRY_MINUTES', 10);   // OTP valid for 10 minutes
define('OTP_LENGTH', 6);             // 6-digit OTP
define('MAX_OTP_ATTEMPTS', 3);       // Maximum verification attempts
```

You can change these values as needed.

---

## 📧 Email Limits (Gmail Free Tier)

- **Daily Limit**: 500 emails per day
- **Per minute**: No strict limit, but avoid sending too many at once
- **Cost**: $0 (Completely FREE)

For a tailoring service website, 500 emails/day should be more than enough!

---

## 🎨 Email Templates

Pre-designed professional templates are in: `config/email_templates/`

1. `otp_verification.html` - For registration
2. `password_reset_otp.html` - For password reset

Templates include:
- Beautiful gradient design
- Responsive layout
- Clear OTP display
- Security warnings
- Professional branding

---

## 🚀 Testing Checklist

- [ ] PHPMailer installed via Composer
- [ ] Gmail App Password created
- [ ] Email configuration updated
- [ ] Database migration executed
- [ ] Test registration with email
- [ ] Receive OTP email
- [ ] Verify OTP successfully
- [ ] Check email_verified column in database

---

## ⚠️ Troubleshooting

### Problem: OTP email not received

**Check:**
1. Is Gmail App Password correct? (16 characters, no spaces)
2. Is 2-Step Verification enabled on Gmail?
3. Check spam/junk folder
4. Check error logs: `error_log` in PHP or browser console

### Problem: "SMTP Error: Could not authenticate"

**Solution:**
- Double-check App Password (must be 16 characters)
- Make sure you're using App Password, NOT regular Gmail password
- Verify 2-Step Verification is enabled

### Problem: Database error

**Solution:**
- Run the migration again
- Check if tables already exist
- Verify database connection in `config/db.php`

---

## 📞 Support

If you encounter issues:
1. Check PHP error logs
2. Check browser console for JavaScript errors
3. Verify all configuration files
4. Test email sending with a simple script first

---

## 🎉 Success!

Once configured, users will:
1. Register with email
2. Receive professional OTP email
3. Verify email with OTP
4. Get email_verified badge in database
5. Use verified email for password recovery

**Cost: $0 | Time: 10 minutes | Benefit: Secure authentication** ✨

---

## 📝 Next Steps (Optional)

To fully integrate OTP verification in your UI:

1. Create OTP verification modal/page
2. Add "Verify Email" button in user profile (for unverified users)
3. Show verification badge for verified emails
4. Implement "Resend OTP" functionality
5. Update forgot password flow to use OTP

Need help implementing these? Let me know!
