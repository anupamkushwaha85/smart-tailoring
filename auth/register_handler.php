<?php

/**
 * Registration Handler
 * Processes customer and tailor registrations
 */

// Disable error display for clean JSON response
ini_set('display_errors', 0);
error_reporting(0);

// Start session
session_start();

// Allow database access
define('DB_ACCESS', true);

// Include database connection
require_once '../config/db.php';
require_once '../services/EmailOTPService.php';

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
    // Get form data with proper sanitization
    $user_type = isset($_POST['user_type']) ? trim($_POST['user_type']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate required fields
    if (empty($user_type) || empty($name) || empty($email) || empty($phone) || empty($password)) {
        throw new Exception('All required fields must be filled');
    }

    // Validate user type
    if (!in_array($user_type, ['customer', 'tailor'])) {
        throw new Exception('Invalid user type');
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate phone number (Indian format)
    if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        throw new Exception('Invalid phone number. Must be 10 digits starting with 6-9');
    }

    // Validate password length
    if (strlen($password) < 6) {
        throw new Exception('Password must be at least 6 characters long');
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Register based on user type
    if ($user_type === 'customer') {
        // Register Customer - Using prepared statements

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing = $result->fetch_assoc();
        $stmt->close();

        if ($existing) {
            throw new Exception('Email already registered. Please login instead.');
        }

        // Check if phone already exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_phone = $result->fetch_assoc();
        $stmt->close();

        if ($existing_phone) {
            throw new Exception('Phone number already registered.');
        }

        // Get optional address
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';

        // Insert customer using prepared statement
        // Auto-verify email since SMTP is blocked on Render free tier
        $stmt = $conn->prepare("INSERT INTO customers (full_name, email, phone, password, address, email_verified) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sssss", $name, $email, $phone, $password_hash, $address);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            $stmt->close();

            // Skip OTP sending to prevent timeouts
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful! You can login now.',
                'user_type' => 'customer',
                'requires_otp' => false
            ]);

            /* OTP Disabled for Render Deployment
            // Send OTP for email verification
            try {
                $otpService = new EmailOTPService($conn);
                $otp_result = $otpService->sendOTP($email, 'registration', 'customer', $user_id, $name);

                if ($otp_result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Registration successful! Please verify your email with the OTP sent to ' . $email,
                        'user_type' => 'customer',
                        'email' => $email,
                        'user_id' => $user_id,
                        'requires_otp' => true
                    ]);
                } else {
                    // Registration successful but OTP failed - user can still login
                    echo json_encode([
                        'success' => true,
                        'message' => 'Registration successful! You can login now. (Email verification pending)',
                        'user_type' => 'customer',
                        'requires_otp' => false,
                        'otp_warning' => 'Email verification OTP could not be sent. You can verify later from your profile.'
                    ]);
                }
            } catch (Exception $e) {
                // Registration successful but OTP failed
                echo json_encode([
                    'success' => true,
                    'message' => 'Registration successful! Please login to continue.',
                    'user_type' => 'customer',
                    'requires_otp' => false
                ]);
            }
            */
        } else {
            $stmt->close();
            throw new Exception('Registration failed. Please try again.');
        }
    } elseif ($user_type === 'tailor') {
        // Register Tailor - Using prepared statements

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM tailors WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing = $result->fetch_assoc();
        $stmt->close();

        if ($existing) {
            throw new Exception('Email already registered. Please login instead.');
        }

        // Check if phone already exists
        $stmt = $conn->prepare("SELECT id FROM tailors WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_phone = $result->fetch_assoc();
        $stmt->close();

        if ($existing_phone) {
            throw new Exception('Phone number already registered.');
        }

        // Get tailor-specific fields
        $shop_address = isset($_POST['shop_address']) ? trim($_POST['shop_address']) : '';
        $area = isset($_POST['area']) ? trim($_POST['area']) : 'Satna';
        $speciality = isset($_POST['speciality']) ? trim($_POST['speciality']) : 'General Tailoring';
        $services = isset($_POST['services']) ? trim($_POST['services']) : 'Stitching, Alteration';
        $experience = isset($_POST['experience']) ? (int)$_POST['experience'] : 0;

        // Validate tailor required fields
        if (empty($shop_address)) {
            throw new Exception('Shop address is required for tailor registration');
        }

        // Insert tailor using prepared statement
        // Auto-verify email since SMTP is blocked on Render free tier
        $stmt = $conn->prepare("INSERT INTO tailors (shop_name, owner_name, email, phone, password, shop_address, area, speciality, services_offered, experience_years, price_range, email_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'medium', 1)");
        $stmt->bind_param("sssssssssi", $name, $name, $email, $phone, $password_hash, $shop_address, $area, $speciality, $services, $experience);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            $stmt->close();

            // Skip OTP sending to prevent timeouts
            echo json_encode([
                'success' => true,
                'message' => 'Tailor registration successful! Your profile will be reviewed and activated soon.',
                'user_type' => 'tailor',
                'requires_otp' => false
            ]);

            /* OTP Disabled for Render Deployment
            // Send OTP for email verification
            try {
                $otpService = new EmailOTPService($conn);
                $otp_result = $otpService->sendOTP($email, 'registration', 'tailor', $user_id, $name);

                if ($otp_result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Registration successful! Please verify your email with the OTP sent to ' . $email,
                        'user_type' => 'tailor',
                        'email' => $email,
                        'user_id' => $user_id,
                        'requires_otp' => true,
                        'note' => 'Your profile will be reviewed and activated soon.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Tailor registration successful! Your profile will be reviewed and activated soon.',
                        'user_type' => 'tailor',
                        'requires_otp' => false
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Tailor registration successful! Your profile will be reviewed and activated soon.',
                    'user_type' => 'tailor',
                    'requires_otp' => false
                ]);
            }
            */
        } else {
            $stmt->close();
            throw new Exception('Registration failed. Please try again.');
        }
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Close database connection
db_close();
