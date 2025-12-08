# 🔄 Cloud Migration Checklist

Quick reference for migrating from local XAMPP to Render + Aiven + Cloudinary

---

## 📦 Files Created for Cloud Deployment

### Core Files (Use These on Cloud)
- ✅ `config/db_cloud.php` - SSL database connection for Aiven
- ✅ `config/session_cloud.php` - Stateless session handling
- ✅ `utils/cloudinary_helper.php` - Image upload to Cloudinary

### Deployment Files
- ✅ `Dockerfile` - Render container configuration
- ✅ `render.yaml` - Render service blueprint
- ✅ `build.sh` - Build script for deployment
- ✅ `.env.example` - Updated with cloud credentials

### Documentation
- ✅ `CLOUD_DEPLOYMENT_GUIDE.md` - Complete setup guide
- ✅ `api/profile/upload_image_cloud_example.php` - Example controller

---

## 🔧 Required Code Changes

### 1. Update Database Connection

**Before (Local):**
```php
require_once 'config/db.php';
```

**After (Cloud):**
```php
require_once 'config/db_cloud.php';
```

Or use conditional loading:
```php
$db_file = getenv('APP_ENV') === 'production' ? 'db_cloud.php' : 'db.php';
require_once "config/{$db_file}";
```

---

### 2. Update Session Handling

**Before:**
```php
session_start();
```

**After:**
```php
require_once 'config/session_cloud.php';
// session_start() is called automatically
```

---

### 3. Update Image Uploads

**Before (Local Storage):**
```php
$imageUpload = new ImageUpload(__DIR__ . '/../../uploads/profiles/');
$result = $imageUpload->upload($_FILES['profile_image'], 'customer_');

if ($result['success']) {
    $filename = $result['filename']; // Local filename
    $sql = "UPDATE customers SET profile_image = ? WHERE customer_id = ?";
    $stmt->bind_param("si", $filename, $user_id);
}
```

**After (Cloudinary):**
```php
require_once __DIR__ . '/../../utils/cloudinary_helper.php';
$result = handleImageUpload($_FILES['profile_image'], 'profiles/customers');

if ($result['success']) {
    $image_url = $result['url']; // Cloudinary URL
    $sql = "UPDATE customers SET profile_image = ? WHERE customer_id = ?";
    $stmt->bind_param("si", $image_url, $user_id);
}
```

---

### 4. Update Image Display

**Before:**
```php
<img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>">
```

**After:**
```php
<img src="<?php echo htmlspecialchars($user['profile_image']); ?>">
```

Note: `profile_image` now contains full Cloudinary URL

---

### 5. Database Schema Update (Optional)

If your current `profile_image` column is `VARCHAR(255)`, consider increasing it:

```sql
ALTER TABLE customers MODIFY COLUMN profile_image VARCHAR(500);
ALTER TABLE tailors MODIFY COLUMN profile_image VARCHAR(500);
ALTER TABLE tailors MODIFY COLUMN shop_image VARCHAR(500);
```

Cloudinary URLs are longer than local filenames.

---

## 🗂️ Files That Need Updates

### Controllers to Update
- [ ] `api/profile/upload_image.php` - Profile image upload
- [ ] `api/tailors/update_shop_image.php` - Shop image upload (if exists)
- [ ] Any other file upload endpoints

### Files to Check
```bash
# Find all files using ImageUpload class
grep -r "new ImageUpload" .

# Find all files using move_uploaded_file
grep -r "move_uploaded_file" .

# Find all files with $_FILES
grep -r "\$_FILES" .
```

---

## 🌍 Environment Variables Required

### Local Development (.env)
```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASS=
DB_NAME=smart_tailoring
DB_USE_SSL=false
APP_ENV=development
SESSION_STORAGE=file
```

### Production (Render Dashboard)
```env
DB_HOST=<aiven-host>.aivencloud.com
DB_PORT=12345
DB_USER=avnadmin
DB_PASS=<aiven-password>
DB_NAME=smart_tailoring
DB_USE_SSL=true
APP_ENV=production
CLOUDINARY_CLOUD_NAME=<your-cloud-name>
CLOUDINARY_UPLOAD_PRESET=<your-preset>
SESSION_STORAGE=file
```

---

## 📋 Pre-Deployment Checklist

### Aiven Setup
- [ ] MySQL service created and running
- [ ] Database `smart_tailoring` created
- [ ] SSL certificate (`ca.pem`) downloaded
- [ ] Connection tested from local machine

### Cloudinary Setup
- [ ] Account created
- [ ] Cloud name obtained
- [ ] Unsigned upload preset created
- [ ] Test upload successful

### Code Updates
- [ ] All image upload controllers updated to use Cloudinary
- [ ] All files using `config/db.php` updated to use `config/db_cloud.php`
- [ ] All `session_start()` calls updated to use `config/session_cloud.php`
- [ ] Database schema updated for longer URL columns

### GitHub
- [ ] All changes committed
- [ ] Code pushed to GitHub
- [ ] Repository is public (or Render has access)

### Render Setup
- [ ] Web service created
- [ ] Environment variables configured
- [ ] Dockerfile validated
- [ ] Build script tested

---

## 🧪 Testing Locally Before Deployment

### 1. Test Database Connection with SSL

```bash
php -r "
\$_ENV['DB_HOST'] = 'your-host.aivencloud.com';
\$_ENV['DB_PORT'] = '12345';
\$_ENV['DB_USER'] = 'avnadmin';
\$_ENV['DB_PASS'] = 'your-password';
\$_ENV['DB_NAME'] = 'smart_tailoring';
\$_ENV['DB_USE_SSL'] = 'true';
require 'config/db_cloud.php';
echo db_health_check() ? 'Connection OK' : 'Connection FAILED';
"
```

### 2. Test Cloudinary Upload

```bash
php -r "
\$_ENV['CLOUDINARY_CLOUD_NAME'] = 'your-cloud-name';
\$_ENV['CLOUDINARY_UPLOAD_PRESET'] = 'your-preset';
require 'utils/cloudinary_helper.php';
\$result = uploadToCloudinary('test-image.jpg', 'test');
print_r(\$result);
"
```

---

## 🚀 Deployment Steps

1. **Push to GitHub**
   ```bash
   git add .
   git commit -m "feat: Cloud deployment ready"
   git push origin main
   ```

2. **Create Render Service**
   - Connect GitHub repository
   - Set environment variables
   - Deploy

3. **Run Migrations**
   ```bash
   # In Render shell
   php migrate.php run
   ```

4. **Create Admin Account**
   ```sql
   # Connect to Aiven MySQL
   INSERT INTO admins (username, password, name, email, role, created_at)
   VALUES ('admin', '$2y$10$...', 'Admin', 'your@email.com', 'super_admin', NOW());
   ```

5. **Test Application**
   - Visit Render URL
   - Register user
   - Upload image
   - Create order

---

## 🐛 Common Issues & Solutions

### "Cannot connect to database"
```bash
# Check environment variables
printenv | grep DB_

# Test connection
php -r "require 'config/db_cloud.php'; var_dump(\$conn);"
```

### "Cloudinary upload failed"
```bash
# Check credentials
printenv | grep CLOUDINARY

# Test upload
curl -X POST "https://api.cloudinary.com/v1_1/YOUR_CLOUD/image/upload" \
  -F "upload_preset=YOUR_PRESET" \
  -F "file=@test.jpg"
```

### "Sessions keep expiring"
- Expected with `SESSION_STORAGE=file` on Render free tier
- Change to `SESSION_STORAGE=database` for persistence

---

## 📊 Performance Optimization

### Database
- Use connection pooling (already configured)
- Add indexes to frequently queried columns
- Use prepared statements (already implemented)

### Images
- Cloudinary automatically optimizes and serves via CDN
- Use Cloudinary transformations for thumbnails:
  ```php
  $thumbnail = str_replace('/upload/', '/upload/w_200,h_200,c_fill/', $image_url);
  ```

### Caching
- Enable OpCache on Render (already in Dockerfile)
- Use browser caching (already in .htaccess)

---

## 📞 Need Help?

**Stuck? Contact:**
- Email: anupamkushwaha639@gmail.com
- LinkedIn: linkedin.com/in/anupamkushwaha85

**Resources:**
- Render Docs: https://render.com/docs
- Aiven Docs: https://docs.aiven.io/
- Cloudinary Docs: https://cloudinary.com/documentation

---

**Last Updated:** December 2025  
**Status:** ✅ Production Ready
