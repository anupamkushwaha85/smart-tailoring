# Forgot Password Feature - Testing Guide

## 🎯 Overview
The forgot password system allows customers and tailors to reset their passwords via email (token-based reset).

## 📋 Prerequisites
1. Run database migration: `database/migrations/003_create_password_resets.sql`
2. Make sure XAMPP Apache and MySQL are running
3. Have a registered customer or tailor account to test with

## 🧪 Testing Steps

### Test 1: Request Password Reset

1. **Open your website homepage**
   - URL: `http://localhost/smart/smart-tailoring/`

2. **Open Login Modal**
   - Click "Login" button in header

3. **Click "Forgot Password?" link**
   - Should see forgot password modal appear
   - Login modal should hide

4. **Fill the form:**
   - Select user type: Customer or Tailor
   - Enter email address (use existing account)
   - Click "Send Reset Link"

5. **Expected Result:**
   - Green success message appears
   - Shows clickable reset link (development mode)
   - Link format: `http://localhost/smart/smart-tailoring/reset_password.php?token=xxxxx`
   - Message says "Link expires in 1 hour"

### Test 2: Use Reset Link

1. **Click the reset link** from previous step
   - Opens `reset_password.php` page

2. **Verify token validation:**
   - Should see reset password form (if token valid)
   - If token invalid/expired: Shows error message

3. **Enter new password:**
   - Type new password (min 6 characters)
   - Confirm password (must match)
   - Click eye icon to toggle password visibility

4. **Submit form:**
   - Click "Reset Password" button

5. **Expected Result:**
   - Green success message: "Password reset successful!"
   - Redirects to homepage after 3 seconds
   - Token marked as "used" in database

### Test 3: Login with New Password

1. **Open login modal** again

2. **Login with:**
   - Email: (same email used for reset)
   - Password: (new password you just set)
   - User Type: (Customer or Tailor)

3. **Expected Result:**
   - Should login successfully
   - Redirects to dashboard

## 🔍 Database Verification

Check the `password_resets` table after testing:

```sql
SELECT * FROM password_resets ORDER BY created_at DESC LIMIT 5;
```

You should see:
- `user_type`: 'customer' or 'tailor'
- `email`: the email address
- `token`: 64-character hex string
- `expires_at`: timestamp (1 hour from creation)
- `used`: 1 (after successful reset)

## ⚠️ Error Cases to Test

### Test 4: Invalid Email
- Enter email that doesn't exist
- Should still show success (security: don't reveal if email exists)
- No token created in database

### Test 5: Expired Token
1. Get reset link
2. Wait 1 hour (or manually change `expires_at` in database)
3. Try to use link
4. Should show: "This reset link has expired"

### Test 6: Used Token
1. Complete password reset successfully
2. Try to use same link again
3. Should show: "This reset link has already been used"

### Test 7: Password Mismatch
- Enter different passwords in "New Password" and "Confirm Password"
- Should show error: "Passwords do not match"

### Test 8: Short Password
- Enter password less than 6 characters
- Should show error: "Password must be at least 6 characters"

## 🔐 Security Features

✅ **Token-based system** (not predictable)
✅ **1-hour expiry** (tokens auto-expire)
✅ **One-time use** (tokens marked as used)
✅ **Separate user types** (customer/tailor isolation)
✅ **No email enumeration** (same response for existing/non-existing emails)
✅ **Old tokens deleted** (when new request made)

## 🚀 Production Deployment

For production, you need to:

1. **Set up email sending** in `api/auth/forgot_password.php`:
   ```php
   // Install PHPMailer
   composer require phpmailer/phpmailer
   
   // Configure SMTP
   $mail = new PHPMailer\PHPMailer\PHPMailer();
   $mail->isSMTP();
   $mail->Host = 'smtp.gmail.com';
   $mail->SMTPAuth = true;
   $mail->Username = 'your-email@gmail.com';
   $mail->Password = 'your-app-password';
   $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
   $mail->Port = 587;
   ```

2. **Remove development mode**:
   - Change line in `forgot_password.php`:
   ```php
   // Remove this line (or set to false):
   'reset_link' => $reset_link  // Don't send link in response
   
   // Keep only:
   'message' => 'Password reset link sent to your email'
   ```

3. **Send actual emails**:
   ```php
   $mail->setFrom('noreply@yourdomain.com', 'Smart Tailoring');
   $mail->addAddress($email);
   $mail->Subject = 'Password Reset Request';
   $mail->Body = "Click here to reset: " . $reset_link;
   $mail->send();
   ```

## 📁 Files Created

1. `database/migrations/003_create_password_resets.sql` - Database table
2. `api/auth/forgot_password.php` - Token generation API
3. `reset_password.php` - Password reset page
4. `assets/js/app.js` - Modal functions (added)
5. `index.php` - Forgot password modal (modified)

## ✅ Quick Test Checklist

- [ ] Modal opens when clicking "Forgot Password?"
- [ ] Can select Customer or Tailor
- [ ] Email validation works
- [ ] Reset link is generated
- [ ] Reset page loads with valid token
- [ ] Password can be updated
- [ ] Can login with new password
- [ ] Token marked as used after reset
- [ ] Expired tokens are rejected
- [ ] Used tokens are rejected
- [ ] Back to Login button works
- [ ] Modal closes when clicking outside
- [ ] Close button (×) works

## 🐛 Troubleshooting

### Issue: "Database error"
- Check if `password_resets` table exists
- Run migration: `003_create_password_resets.sql`

### Issue: "Invalid or expired token"
- Token may have expired (1 hour limit)
- Request new reset link

### Issue: Modal doesn't open
- Check browser console for JavaScript errors
- Verify `app.js` loaded correctly

### Issue: Can't update password
- Check if user exists in `customers` or `tailors` table
- Verify token is valid and not used

---

**Development Mode:** Currently shows reset link in the modal (for easy testing without email setup).

**Production Mode:** Will send reset link via email (requires SMTP configuration).
