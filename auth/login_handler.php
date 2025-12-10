<?php

/**
 * Login Handler
 * Authenticates customers and tailors
 * SECURITY: Uses prepared statements, session regeneration, rate limiting
 */

// Secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 when using HTTPS
ini_set('session.use_strict_mode', 1);

// Disable error display for clean JSON response
ini_set('display_errors', 0);
error_reporting(0);

// Start session
session_start();

// Allow database access
define('DB_ACCESS', true);

// Include database connection
require_once '../config/db.php';

// Set JSON response header
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    // Get form data
    $user_type = isset($_POST['user_type']) ? trim($_POST['user_type']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate required fields
    if (empty($user_type) || empty($email) || empty($password)) {
        throw new Exception('Please fill in all fields');
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate user type
    if (!in_array($user_type, ['customer', 'tailor'])) {
        throw new Exception('Invalid user type');
    }

    // Rate limiting check (simple implementation)
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $attempt_key = 'login_attempts_' . md5($ip_address);
    $attempt_time_key = 'login_attempt_time_' . md5($ip_address);

    if (isset($_SESSION[$attempt_key]) && $_SESSION[$attempt_key] >= 5) {
        $last_attempt = $_SESSION[$attempt_time_key] ?? 0;
        if (time() - $last_attempt < 900) { // 15 minutes lockout
            throw new Exception('Too many failed login attempts. Please try again in 15 minutes.');
        } else {
            // Reset attempts after lockout period
            $_SESSION[$attempt_key] = 0;
        }
    }

    // Login based on user type
    if ($user_type === 'customer') {
        // Customer Login - Using prepared statement
        $stmt = $conn->prepare("SELECT id, full_name, email, phone, password, is_active, is_blocked, email_verified FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            // Increment failed attempts
            $_SESSION[$attempt_key] = ($_SESSION[$attempt_key] ?? 0) + 1;
            $_SESSION[$attempt_time_key] = time();
            throw new Exception('Invalid email or password');
        }

        // Check if account is blocked
        if (isset($user['is_blocked']) && $user['is_blocked'] == 1) {
            throw new Exception('Your account has been blocked. Please contact support.');
        }

        // Check if account is active
        if ($user['is_active'] != 1) {
            throw new Exception('Your account has been deactivated. Please contact support.');
        }

        // Check if email is verified
        if (isset($user['email_verified']) && $user['email_verified'] == 0) {
            // Send OTP for email verification
            require_once '../services/EmailOTPService.php';
            $otpService = new EmailOTPService($conn);
            $otp_result = $otpService->sendOTP($email, 'registration', 'customer', $user['id'], $user['full_name']);

            echo json_encode([
                'success' => true,
                'message' => 'Email verification required. OTP sent to ' . $email,
                'requires_otp' => true,
                'email' => $email,
                'user_type' => 'customer',
                'user_id' => $user['id']
            ]);
            exit;
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            // Increment failed attempts
            $_SESSION[$attempt_key] = ($_SESSION[$attempt_key] ?? 0) + 1;
            $_SESSION[$attempt_time_key] = time();
            throw new Exception('Invalid email or password');
        }

        // Reset failed attempts on successful login
        unset($_SESSION[$attempt_key]);
        unset($_SESSION[$attempt_time_key]);

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_type'] = 'customer';
        $_SESSION['user_name'] = htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8');
        $_SESSION['user_email'] = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();

        echo json_encode([
            'success' => true,
            'message' => 'Login successful! Redirecting...',
            'userType' => 'customer',
            'redirect' => 'customer/dashboard.php'
        ]);
    } elseif ($user_type === 'tailor') {
        // Tailor Login - Using prepared statement
        $stmt = $conn->prepare("SELECT id, shop_name, owner_name, email, phone, password, is_active, is_verified, is_blocked, email_verified FROM tailors WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            // Increment failed attempts
            $_SESSION[$attempt_key] = ($_SESSION[$attempt_key] ?? 0) + 1;
            $_SESSION[$attempt_time_key] = time();
            throw new Exception('Invalid email or password');
        }

        // Check if account is blocked
        if (isset($user['is_blocked']) && $user['is_blocked'] == 1) {
            throw new Exception('Your account has been blocked. Please contact support.');
        }

        // Check if account is active
        if ($user['is_active'] != 1) {
            throw new Exception('Your account has been deactivated. Please contact support.');
        }

        // Check if email is verified
        if (isset($user['email_verified']) && $user['email_verified'] == 0) {
            // Send OTP for email verification
            require_once '../services/EmailOTPService.php';
            $otpService = new EmailOTPService($conn);
            $otp_result = $otpService->sendOTP($email, 'registration', 'tailor', $user['id'], $user['owner_name']);

            echo json_encode([
                'success' => true,
                'message' => 'Email verification required. OTP sent to ' . $email,
                'requires_otp' => true,
                'email' => $email,
                'user_type' => 'tailor',
                'user_id' => $user['id']
            ]);
            exit;
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            // Increment failed attempts
            $_SESSION[$attempt_key] = ($_SESSION[$attempt_key] ?? 0) + 1;
            $_SESSION[$attempt_time_key] = time();
            throw new Exception('Invalid email or password');
        }

        // Reset failed attempts on successful login
        unset($_SESSION[$attempt_key]);
        unset($_SESSION[$attempt_time_key]);

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_type'] = 'tailor';
        $_SESSION['user_name'] = htmlspecialchars($user['owner_name'], ENT_QUOTES, 'UTF-8');
        $_SESSION['shop_name'] = htmlspecialchars($user['shop_name'], ENT_QUOTES, 'UTF-8');
        $_SESSION['user_email'] = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');
        $_SESSION['is_verified'] = (int)$user['is_verified'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();

        echo json_encode([
            'success' => true,
            'message' => 'Login successful! Redirecting...',
            'userType' => 'tailor',
            'redirect' => 'tailor/dashboard.php'
        ]);
    }
} catch (Exception $e) {
    // Log error securely (in production, log to file instead)
    error_log('Login error: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}
