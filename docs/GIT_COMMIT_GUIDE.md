# Git Commit Guide - Smart Tailoring Service

## ⚠️ CRITICAL - Files to NEVER Push

**These contain sensitive passwords and credentials:**

1. `config/db.php` - Database credentials ❌
2. `config/email.php` - Gmail SMTP password ❌
3. `vendor/` folder (if exists) - Composer dependencies ❌
4. `uploads/profiles/*` - User profile images ❌
5. `uploads/shops/*` - Shop images ❌
6. `.env` files - Environment variables ❌

**Already protected by .gitignore ✅**

---

## ✅ Safe Files to Commit

### Configuration Files (Examples Only)
```bash
git add config/config.example.php
git add config/email.example.php
git add config/session_check.php
git add config/security.php
git add config/config_loader.php
git add config/concurrent_users.php
git add config/api_security.php
```

### Core Application Files
```bash
# Index and landing pages
git add index.php about.php contact.php faq.php privacy.php terms.php

# Authentication
git add auth/login_handler.php
git add auth/register_handler.php

# Customer pages
git add customer/dashboard.php
git add customer/orders.php
git add customer/profile.php
git add customer/measurements.php

# Tailor pages
git add tailor/dashboard.php
git add tailor/orders.php
git add tailor/profile.php

# Admin panel
git add admin/
```

### API Endpoints
```bash
git add api/auth/
git add api/check_capacity.php
git add api/get_tailors.php
git add api/measurements/
git add api/orders/
git add api/otp/
git add api/profile/
git add api/submit_contact.php
git add api/tailors/
```

### Assets (CSS, JS, Images)
```bash
git add assets/css/style.css
git add assets/js/app.js
git add assets/js/csrf-helper.js
git add assets/js/map-integration.js
git add assets/js/order-enhancements.js
git add assets/js/tailor-order-enhancements.js

# Safe images (NOT user uploads)
git add assets/images/logo.png
git add assets/images/2.png
# Do NOT add: assets/images/*.pdf (personal files)
```

### Models, Repositories, Services
```bash
git add models/
git add repositories/
git add services/EmailOTPService.php
git add services/OrderService.php
git add services/ProfileService.php
```

### Database Migrations
```bash
git add database/migrations/
# Do NOT add: database/backups/ or *.sql files
```

### Utilities
```bash
git add utils/ImageUpload.php
```

### Documentation
```bash
git add *.md
git add .htaccess
git add uploads/.htaccess
git add .gitignore
```

---

## 🚀 Recommended Commit Commands

### Step 1: Stage All Safe Files
```bash
cd "c:\xampp\htdocs\smart\smart-tailoring"

# Add all modified files
git add .gitignore
git add assets/css/style.css
git add assets/js/app.js
git add index.php
git add customer/
git add tailor/
git add api/
git add auth/
git add models/
git add repositories/
git add services/
git add utils/

# Add new files
git add admin/
git add config/*.example.php
git add config/session_check.php
git add config/security.php
git add config/api_security.php
git add config/config_loader.php
git add config/concurrent_users.php
git add database/migrations/
git add *.md
git add .htaccess
git add uploads/.htaccess
```

### Step 2: Verify What Will Be Committed
```bash
git status
```

**Check output carefully - ensure NO passwords are being committed!**

### Step 3: Commit with Descriptive Message
```bash
git commit -m "feat: Complete mobile responsive design + OTP email authentication

- Added comprehensive mobile CSS (768px, 480px breakpoints)
- Implemented email OTP verification system with PHPMailer
- Enhanced security: email field now read-only in profiles
- Mobile optimization for all pages (dashboard, orders, measurements, profile)
- Added forgot password with OTP functionality
- Fixed navbar alignment and responsiveness
- Applied regex validation across all forms
- Updated order system with new status workflow
- Improved UI/UX for both desktop and mobile"
```

### Step 4: Push to Remote
```bash
git push origin main
```

---

## 🔍 Double-Check Before Pushing

Run this command to see exactly what will be pushed:
```bash
git diff --cached --name-only
```

**If you see any of these, STOP and remove them:**
- `config/db.php`
- `config/email.php`
- `vendor/`
- Files in `uploads/profiles/` or `uploads/shops/`
- `.env` files
- `*.sql` files

To unstage a file:
```bash
git reset HEAD config/db.php
```

---

## 📝 Notes

1. **First time?** Make sure `config/db.php` and `config/email.php` exist locally but are NOT tracked:
   ```bash
   git rm --cached config/db.php
   git rm --cached config/email.php
   ```

2. **Composer packages:** If you install PHPMailer via composer, the `vendor/` folder will be created. It's already in .gitignore.

3. **After cloning on another machine:**
   - Copy `config/config.example.php` → `config/db.php` (add your DB credentials)
   - Copy `config/email.example.php` → `config/email.php` (add your Gmail credentials)

4. **User uploads:** Profile and shop images are excluded from git. Backup separately if needed.

---

## ⚡ Quick Commit (All at Once)

If you're confident everything is safe:
```bash
cd "c:\xampp\htdocs\smart\smart-tailoring"
git add .
git commit -m "feat: Mobile responsive design + OTP authentication system"
git push origin main
```

But **ALWAYS** run `git status` first to verify!
