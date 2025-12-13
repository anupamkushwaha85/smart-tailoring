<?php

/**
 * Homepage - Smart Tailoring Service
 * Main landing page with tailor listings
 */

// Start session to maintain login state
session_start();

// Include database connection
define('DB_ACCESS', true);
require_once __DIR__ . '/config/db.php';

// Include concurrent user manager
require_once __DIR__ . '/config/concurrent_users.php';

// Initialize concurrent user manager (max 100 concurrent users)
$userManager = new ConcurrentUserManager($conn, 100);

// Check if server is full
if ($userManager->isServerFull()) {
    $capacity = $userManager->getCapacityInfo();
    include 'server_busy.php';
    exit;
}

// Register/update this session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
$userManager->registerSession($user_id, $user_type);

// Check if user is logged in
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$user_name = $is_logged_in ? $_SESSION['user_name'] : '';
$user_type = $is_logged_in ? $_SESSION['user_type'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Tailoring Service - Find Best Tailors in Satna</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpg" href="assets/images/STP-favicon.jpg">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- MapLibre GL JS CSS (Modern GPU-accelerated maps) -->
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="<?php echo $is_logged_in ? 'logged-in' : ''; ?>" data-user-type="<?php echo $user_type; ?>">
    <!-- Login/Register Modal -->
    <div class="modal-overlay" id="authModal">
        <div class="modal-container">
            <!-- Modal Header -->
            <div class="modal-header">
                <h2 id="modalTitle">Welcome Back!</h2>
                <p id="modalSubtitle">Login to access your account</p>
                <button class="modal-close" onclick="closeAuthModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- User Type Selection (Customer/Tailor) -->
                <div class="modal-tabs">
                    <button class="modal-tab active" data-user-type="customer" onclick="switchUserType('customer')">
                        <i class="fas fa-user"></i> Customer
                    </button>
                    <button class="modal-tab" data-user-type="tailor" onclick="switchUserType('tailor')">
                        <i class="fas fa-store"></i> Tailor
                    </button>
                </div>

                <!-- Login/Register Toggle -->
                <div class="auth-toggle" style="text-align: center; margin-bottom: 2rem;">
                    <button class="modal-tab active" id="loginTab" onclick="showLoginForm()">Login</button>
                    <button class="modal-tab" id="registerTab" onclick="showRegisterForm()">Register</button>
                </div>

                <!-- Error/Success Message -->
                <div class="form-message" id="authMessage"></div>

                <!-- LOGIN FORM -->
                <form id="loginForm" class="auth-form" autocomplete="off">
                    <input type="hidden" name="user_type" id="loginUserType" value="customer">

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-input"
                                placeholder="Enter your email" required autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" id="loginPassword"
                                class="form-input" placeholder="Enter your password" required autocomplete="new-password">
                            <button type="button" class="password-toggle" onclick="togglePassword('loginPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="remember"> Remember Me
                        </label>
                        <a href="#" onclick="showForgotPasswordModal(); return false;" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                            <i class="fas fa-key"></i> Forgot Password?
                        </a>
                    </div>

                    <button type="submit" class="form-button">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <!-- REGISTER FORM -->
                <form id="registerForm" class="auth-form" style="display: none;">
                    <input type="hidden" name="user_type" id="registerUserType" value="customer">

                    <!-- Common Fields -->
                    <div class="form-group">
                        <label class="form-label">Full Name / Shop Name</label>
                        <div class="input-group">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="name" class="form-input"
                                placeholder="Enter your name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-input"
                                placeholder="Enter your email" required autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <div class="input-group">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel" name="phone" class="form-input"
                                placeholder="10-digit mobile number" pattern="[6-9][0-9]{9}"
                                minlength="10" maxlength="10" required autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" id="registerPassword"
                                class="form-input" placeholder="Create password (min 6 characters)"
                                minlength="6" required autocomplete="new-password">
                            <button type="button" class="password-toggle" onclick="togglePassword('registerPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Customer-Specific Fields -->
                    <div id="customerFields">
                        <div class="form-group">
                            <label class="form-label">Address (Optional)</label>
                            <textarea name="address" class="form-input form-textarea"
                                placeholder="Enter your address"></textarea>
                        </div>
                    </div>

                    <!-- Tailor-Specific Fields -->
                    <div id="tailorFields" style="display: none;">
                        <div class="form-group">
                            <label class="form-label">Shop Address</label>
                            <textarea name="shop_address" class="form-input form-textarea"
                                placeholder="Complete shop address" required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Area/Locality</label>
                            <input type="text" name="area" class="form-input"
                                placeholder="e.g., Civil Lines, Rewa Road">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Speciality</label>
                            <input type="text" name="speciality" class="form-input"
                                placeholder="e.g., Wedding Wear Specialist">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Services Offered</label>
                            <input type="text" name="services" class="form-input"
                                placeholder="e.g., Stitching, Alteration, Embroidery">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Experience (Years)</label>
                            <input type="number" name="experience" class="form-input"
                                placeholder="Years of experience" min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" required> <span>I agree to <a href="terms.php" target="_blank" style="color: #2c3e50; text-decoration: underline;">Terms</a> & <a href="privacy.php" target="_blank" style="color: #2c3e50; text-decoration: underline;">Conditions</a></span>
                        </label>
                    </div>

                    <button type="submit" class="form-button">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </form>

                <!-- Switch Between Login/Register -->
                <div class="modal-switch">
                    <p id="switchText">
                        Don't have an account?
                        <a onclick="showRegisterForm()">Register Now</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal-overlay" id="forgotPasswordModal" style="display: none;">
        <div class="modal-container" style="max-width: 500px;">
            <!-- Modal Header -->
            <div class="modal-header">
                <h2><i class="fas fa-key"></i> Reset Password</h2>
                <p>Enter your email to receive password reset instructions</p>
                <button class="modal-close" onclick="closeForgotPasswordModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Error/Success Message -->
                <div class="form-message" id="forgotMessage"></div>

                <!-- Forgot Password Form -->
                <form id="forgotPasswordForm">
                    <div class="form-group">
                        <label class="form-label">Select Account Type</label>
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                            <label style="flex: 1; display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="user_type" value="customer" checked>
                                <i class="fas fa-user"></i> Customer
                            </label>
                            <label style="flex: 1; display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="user_type" value="tailor">
                                <i class="fas fa-store"></i> Tailor
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-input"
                                placeholder="Enter your registered email" required>
                        </div>
                        <small style="color: #6b7280; font-size: 0.875rem;">
                            We'll send you an OTP to reset your password
                        </small>
                    </div>

                    <button type="submit" class="form-button">
                        <i class="fas fa-paper-plane"></i> Send OTP
                    </button>

                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="#" onclick="backToLogin(); return false;" style="color: var(--primary-color); text-decoration: none;">
                            <i class="fas fa-arrow-left"></i> Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- OTP Verification Modal -->
    <div class="modal-overlay" id="otpModal" style="display: none;">
        <div class="modal-container" style="max-width: 500px;">
            <!-- Modal Header -->
            <div class="modal-header">
                <h2>📧 Verify Your Email</h2>
                <p>We've sent a 6-digit OTP to <strong id="otpEmail"></strong></p>
                <button class="modal-close" onclick="closeOTPModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Success/Error Message -->
                <div class="form-message" id="otpMessage"></div>

                <form id="otpVerificationForm">
                    <input type="hidden" id="otpEmailHidden" name="email">
                    <input type="hidden" id="otpPurpose" name="purpose" value="registration">

                    <div class="form-group">
                        <label class="form-label">Enter 6-Digit OTP</label>
                        <div class="input-group">
                            <i class="fas fa-key input-icon"></i>
                            <input type="text" name="otp_code" id="otpCode" class="form-input"
                                placeholder="000000" maxlength="6" pattern="[0-9]{6}"
                                style="text-align: center; font-size: 1.5rem; letter-spacing: 0.5rem; font-weight: bold;"
                                required autocomplete="off">
                        </div>
                        <small style="color: #6b7280; font-size: 0.875rem; display: block; margin-top: 0.5rem;">
                            ⏱️ OTP expires in <strong id="otpTimer">10:00</strong>
                        </small>
                    </div>

                    <button type="submit" class="form-button">
                        <i class="fas fa-check-circle"></i> Verify OTP
                    </button>

                    <div style="text-align: center; margin-top: 1rem;">
                        <p style="color: #6b7280; font-size: 0.875rem;">Didn't receive the code?</p>
                        <button type="button" id="resendOtpBtn" onclick="resendOTP()"
                            style="background: transparent; border: none; color: var(--primary-color); font-weight: 600; cursor: pointer; text-decoration: underline;">
                            <i class="fas fa-redo"></i> Resend OTP
                        </button>
                        <p id="resendTimer" style="color: #6b7280; font-size: 0.875rem; margin-top: 0.5rem; display: none;">
                            Resend available in <strong id="resendCountdown">120</strong>s
                        </p>
                    </div>

                    <div style="text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                        <button type="button" onclick="skipOTPVerification()"
                            style="background: transparent; border: none; color: #6b7280; font-size: 0.875rem; cursor: pointer;">
                            <i class="fas fa-arrow-right"></i> Skip for now (verify later)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom JavaScript -->
    <script src="assets/js/app.js?v=<?php echo time(); ?>"></script>

    <!-- Order Placement Modal -->
    <div class="modal-overlay" id="orderModal">
        <div class="modal-container" style="max-width: 600px;">
            <!-- Modal Header -->
            <div class="modal-header">
                <h2>Place Your Order</h2>
                <p>Fill in the details for your tailoring order</p>
                <button class="modal-close" onclick="closeOrderModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Error/Success Message -->
                <div class="form-message" id="orderMessage"></div>

                <!-- Order Form -->
                <form id="orderForm">
                    <input type="hidden" name="tailor_id" id="orderTailorId">
                    <input type="hidden" name="measurement_id" id="measurementId">
                    <input type="hidden" name="measurements_snapshot" id="measurementsSnapshot">

                    <!-- Tailor Info Display -->
                    <div id="orderTailorInfo" style="padding: 1rem; background: var(--light-bg); border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                        <h4 style="margin: 0 0 0.5rem 0; color: var(--text-dark);">
                            <i class="fas fa-store"></i> <span id="orderTailorName">Loading...</span>
                        </h4>
                        <p style="margin: 0; color: var(--text-light); font-size: 0.9rem;">
                            <i class="fas fa-map-marker-alt"></i> <span id="orderTailorArea">-</span>
                        </p>
                    </div>

                    <!-- Service Type -->
                    <div class="form-group">
                        <label class="form-label">Service Type *</label>
                        <select name="service_type" class="form-input form-select" required>
                            <option value="">Select service</option>
                            <option value="Stitching">Stitching</option>
                            <option value="Alteration">Alteration</option>
                            <option value="Embroidery">Embroidery</option>
                            <option value="Repair">Repair</option>
                            <option value="Designer Work">Designer Work</option>
                        </select>
                    </div>

                    <!-- Garment Type -->
                    <div class="form-group">
                        <label class="form-label">Garment Type *</label>
                        <select name="garment_type" class="form-input form-select" required>
                            <option value="">Select garment type</option>
                            <optgroup label="Men's Wear">
                                <option value="Shirt">Shirt</option>
                                <option value="Pants">Pants/Trousers</option>
                                <option value="Suit">Suit</option>
                                <option value="Kurta">Kurta</option>
                                <option value="Sherwani">Sherwani</option>
                                <option value="Blazer">Blazer</option>
                                <option value="Waistcoat">Waistcoat</option>
                            </optgroup>
                            <optgroup label="Women's Wear">
                                <option value="Blouse">Blouse</option>
                                <option value="Saree">Saree</option>
                                <option value="Salwar Kameez">Salwar Kameez</option>
                                <option value="Lehenga">Lehenga</option>
                                <option value="Dress">Dress</option>
                                <option value="Gown">Gown</option>
                                <option value="Kurti">Kurti</option>
                            </optgroup>
                            <optgroup label="Kids Wear">
                                <option value="Kids Shirt">Kids Shirt</option>
                                <option value="Kids Dress">Kids Dress</option>
                                <option value="Kids Frock">Kids Frock</option>
                            </optgroup>
                            <option value="Other">Other (Specify in instructions)</option>
                        </select>
                    </div>

                    <!-- Fabric Type -->
                    <div class="form-group">
                        <label class="form-label">Fabric Type *</label>
                        <select name="fabric_type" class="form-input form-select" required>
                            <option value="">Select fabric type</option>
                            <option value="Cotton">Cotton</option>
                            <option value="Silk">Silk</option>
                            <option value="Linen">Linen</option>
                            <option value="Polyester">Polyester</option>
                            <option value="Wool">Wool</option>
                            <option value="Denim">Denim</option>
                            <option value="Chiffon">Chiffon</option>
                            <option value="Georgette">Georgette</option>
                            <option value="Velvet">Velvet</option>
                            <option value="Satin">Satin</option>
                            <option value="Crepe">Crepe</option>
                            <option value="Rayon">Rayon</option>
                            <option value="Khadi">Khadi</option>
                            <option value="Mixed/Blend">Mixed/Blend</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Fabric Color -->
                    <div class="form-group">
                        <label class="form-label">Fabric Color *</label>
                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 0.5rem;">
                            <input type="text" name="fabric_color" id="fabricColorText" class="form-input"
                                placeholder="Enter color name" required>
                            <input type="color" id="fabricColorPicker" class="form-input" value="#000000"
                                style="width: 60px; height: 45px; padding: 5px; cursor: pointer;"
                                title="Pick a color">
                        </div>
                        <small style="color: var(--text-light);">Type color name or use color picker</small>
                    </div>

                    <!-- Quantity -->
                    <div class="form-group">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-input"
                            value="1" min="1" required>
                    </div>

                    <!-- Measurements -->
                    <div class="form-group">
                        <label class="form-label">Measurements</label>

                        <!-- Measurement Options -->
                        <div class="measurement-options" style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem;">
                                <input type="radio" name="measurement_option" value="default" id="measurementDefault" checked>
                                Use My Default Measurements
                            </label>
                            <label style="display: block;">
                                <input type="radio" name="measurement_option" value="custom" id="measurementCustom">
                                Enter Custom Measurements
                            </label>
                        </div>

                        <!-- No Default Measurement Error -->
                        <div id="noDefaultError" class="measurement-error" style="display: none; padding: 0.75rem; background: #fee; border-left: 3px solid #f44336; border-radius: 4px; margin-bottom: 1rem; color: #c62828;">
                            <i class="fas fa-exclamation-triangle"></i> <strong>No default measurement found!</strong><br>
                            <small>You don't have a saved measurement for this garment type. Please select "Enter Custom Measurements" or <a href="customer/measurements.php" style="color: #1976d2; text-decoration: underline;">create a measurement profile</a> first.</small>
                        </div>

                        <!-- Default Measurements Message -->
                        <div id="defaultMeasurementMsg" class="measurement-message" style="display: block; padding: 0.75rem; background: #e8f5e9; border-left: 3px solid #4caf50; border-radius: 4px; margin-bottom: 1rem;">
                            <i class="fas fa-info-circle"></i> Your saved default measurements will be used for this order.
                        </div>

                        <!-- Custom Measurement Fields Container -->
                        <div id="customMeasurementFields" class="custom-measurement-fields" style="display: none;">
                            <div id="dynamicMeasurementFields">
                                <!-- Fields will be dynamically populated based on garment type -->
                                <p style="color: var(--text-light); font-style: italic;">
                                    <i class="fas fa-arrow-up"></i> Please select a garment type first
                                </p>
                            </div>
                        </div>

                        <small style="color: var(--text-light); display: block; margin-top: 0.5rem;">
                            Make sure your measurements are accurate for the best fit
                        </small>
                    </div>

                    <!-- Special Instructions -->
                    <div class="form-group">
                        <label class="form-label">Special Instructions (Optional)</label>
                        <textarea name="special_instructions" class="form-input form-textarea"
                            placeholder="Any specific requirements, design preferences, fabric details, etc."></textarea>
                    </div>

                    <!-- Estimated Price -->
                    <div class="form-group">
                        <label class="form-label">Estimated Budget (₹)</label>
                        <input type="number" name="estimated_price" class="form-input"
                            placeholder="Enter your budget" min="0">
                        <small style="color: var(--text-light);">Final price will be confirmed by tailor</small>
                    </div>

                    <!-- Delivery Date -->
                    <div class="form-group">
                        <label class="form-label">Expected Delivery Date (Optional)</label>
                        <input type="date" name="delivery_date" class="form-input"
                            min="<?php echo date('Y-m-d', strtotime('+3 days')); ?>">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="form-button">
                        <i class="fas fa-shopping-cart"></i> Place Order
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- </body> -->

</html>



<body <?php if ($is_logged_in) echo 'class="logged-in"'; ?> data-user-type="<?php echo $user_type; ?>">


    <!-- Navigation Bar  -->
    <nav class="navbar">
        <div class="nav-container">
            <!-- Logo -->
            <div class="nav-logo">
                <img src="assets/images/logo.png" alt="Smart Tailoring Service Logo">
                <span class="logo-text">Smart Tailoring Service</span>
            </div>

            <!-- Navigation Menu -->
            <ul class="nav-menu">
                <li><a href="#home" class="nav-link active">Home</a></li>
                <li><a href="#services" class="nav-link">Services</a></li>
                <li><a href="#tailors" class="nav-link">Find Tailors</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
            </ul>


            <!-- Auth Buttons -->
            <div class="nav-auth">
                <?php if ($is_logged_in): ?>
                    <!-- Logged in state -->
                    <span class="welcome-text" style="margin-right: 1rem; color: var(--text-dark);">
                        Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong>!
                    </span>
                    <button class="btn-dashboard" onclick="window.location.href='<?php echo $user_type === 'customer' ? 'customer' : 'tailor'; ?>/dashboard.php'">
                        <i class="fas fa-tachometer-alt"></i> <span class="btn-text">Dashboard</span>
                    </button>
                    <button class="btn-logout" onclick="window.location.href='auth/logout.php'">
                        <i class="fas fa-sign-out-alt"></i> <span class="btn-text">Logout</span>
                    </button>
                <?php else: ?>
                    <!-- Not logged in state -->
                    <button class="btn-login-register" onclick="openLoginModal()">
                        <span class="login-text-full">Login & Register</span>
                        <span class="login-text-short">Login</span>
                    </button>
                <?php endif; ?>

                <!-- Notification Icon -->
                <div class="notification-container">
                    <button class="btn-notification" id="notificationBtn" onclick="toggleNotifications()">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <h3>Notifications</h3>
                            <button class="mark-all-read" id="markAllReadBtn" onclick="markAllAsRead()">
                                Mark all as read
                            </button>
                        </div>
                        <div class="notification-list" id="notificationList">
                            <!-- Notifications will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Admin Login Button (Always visible) -->
                <button class="btn-admin-login" onclick="window.location.href='admin/'" title="Admin Portal">
                    <i class="fas fa-shield-alt"></i>
                </button>
            </div>

            <!-- Search Icon -->
            <div class="nav-search" onclick="activateSearch()" style="cursor: pointer;" title="Search Tailors">
                <i class="fas fa-search"></i>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-overlay"></div>
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">Find skilled tailors to - Snip, Stitch Style Up!</h1>
                <p class="hero-subtitle">
                    From fabric to fashion — in your way.
                    <br>
                    Custom fits, bold styles, crafted just for you.
                </p>
                <div class="hero-buttons">
                    <button class="btn btn-primary" onclick="scrollToTailors()">
                        <i class="fas fa-search"></i> Find Tailors
                    </button>
                    <button class="btn btn-secondary" onclick="openRegisterModal('tailor')">
                        <i class="fas fa-store"></i> Register Your Shop
                    </button>
                </div>
                <p class="hero-subtitle">
                    Dress it. Flaunt it. Snitch it.
                </p>

                <!-- Stats -->
                <div class="hero-stats">
                    <div class="stat-item">
                        <h3 class="stat-number" id="tailorCount">0</h3>
                        <p class="stat-label">Registered Tailors</p>
                    </div>
                    <div class="stat-item">
                        <h3 class="stat-number" id="customerCount">0</h3>
                        <p class="stat-label">Happy Customers</p>
                    </div>
                    <div class="stat-item">
                        <h3 class="stat-number">500+</h3>
                        <p class="stat-label">Orders Completed</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section" id="services">
        <div class="section-container">
            <h2 class="section-title">Our Services</h2>
            <p class="section-subtitle">Everything you need for your tailoring requirements</p>

            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-cut"></i>
                    </div>
                    <h3>Custom Stitching</h3>
                    <p>Get your clothes stitched according to your measurements and preferences.</p>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-ruler"></i>
                    </div>
                    <h3>Alterations</h3>
                    <p>Perfect fit guaranteed with our professional alteration services.</p>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <h3>Embroidery</h3>
                    <p>Beautiful embroidery work to make your clothes stand out.</p>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <h3>Designer Wear</h3>
                    <p>Custom designer outfits for weddings and special occasions.</p>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3>Repairs</h3>
                    <p>Quick and reliable repair services for damaged garments.</p>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Express Service</h3>
                    <p>Need it urgently? We offer express delivery options.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tailors Section -->
    <section class="tailors-section" id="tailors">
        <div class="section-container">
            <h2 class="section-title">Featured Tailors in Satna</h2>
            <p class="section-subtitle">Browse through our verified and experienced tailors</p>

            <!-- Search and Filter -->
            <div class="search-filter-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search by name, area, or service...">
                </div>

                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="verified">Verified</button>
                    <button class="filter-btn" data-filter="premium">Premium</button>
                </div>
            </div>

            <!-- Tailors Grid -->
            <div class="tailors-grid" id="tailorsGrid">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading tailors...
                </div>
            </div>
        </div>
    </section>

    <!-- Footer (Indian Explorer Style) -->
    <footer class="footer">
        <div class="footer-container">
            <!-- Footer Main Content -->
            <div class="footer-main">
                <!-- Quick Links Column -->
                <div class="footer-column">
                    <h3 class="footer-heading">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#tailors">Find Tailors</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>

                <!-- Extra Links Column -->
                <div class="footer-column">
                    <h3 class="footer-heading">Extra Links</h3>
                    <ul class="footer-links">
                        <li><a href="faq.php">Ask Questions</a></li>
                        <li><a href="terms.php">Terms Of Use</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="about.php">About Us</a></li>
                    </ul>
                </div>

                <!-- Contact Info Column -->
                <div class="footer-column">
                    <h3 class="footer-heading">Contact Info</h3>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>+91 6262161170</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>anupamkushwaha639@gmail.com</span>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Satna M.P. 485001</span>
                        </li>
                    </ul>
                </div>

                <!-- Follow Us Column -->
                <div class="footer-column">
                    <h3 class="footer-heading">Follow Us</h3>
                    <ul class="footer-social">
                        <li><a href="https://fb.com/anupam kushwaha"><i class="fab fa-facebook"></i> Facebook</a></li>
                        <li><a href="https://instagram.com/anupam_kushwaha_85"><i class="fab fa-instagram"></i> Instagram</a></li>
                        <li><a href="www.linkedin.com/in/anupamkushwaha85"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
                        <li><a href="https://github.com/anupamkushwaha85"><i class="fab fa-github"></i> Github</a></li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p>Created By <span class="highlight">Anupam Kushwaha</span> | All Rights Reserved!</p>
            </div>
        </div>
    </footer>

    <!-- MapLibre GL JS (Modern GPU-accelerated maps - free, no API key) -->
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>

    <!-- Map Integration Script -->
    <script src="assets/js/map-integration.js"></script>

    <!-- Notification System Script -->
    <script src="assets/js/notifications.js"></script>

    <!-- Dynamic Measurement Fields Script -->
    <script src="assets/js/measurement-fields.js"></script>

    <!-- Custom JavaScript -->
    <!-- app.js already loaded above -->
    <script>
        // Load database stats
        fetch('api/get_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    document.getElementById('tailorCount').textContent = data.data.registered_tailors || 0;
                    document.getElementById('customerCount').textContent = data.data.happy_customers || 0;
                }
            })
            .catch(error => {
                console.error('Error loading stats:', error);
                // Keep showing 0 if API fails
                document.getElementById('tailorCount').textContent = '0';
                document.getElementById('customerCount').textContent = '0';
            });

        // Fabric color picker sync
        document.addEventListener('DOMContentLoaded', function() {
            const colorPicker = document.getElementById('fabricColorPicker');
            const colorText = document.getElementById('fabricColorText');

            if (colorPicker && colorText) {
                // When color picker changes, update text input
                colorPicker.addEventListener('input', function() {
                    const hexColor = this.value;
                    const colorName = getColorName(hexColor);
                    colorText.value = colorName;
                });

                // When text input changes, try to update color picker
                colorText.addEventListener('input', function() {
                    const colorName = this.value.toLowerCase();
                    const hexColor = getHexFromColorName(colorName);
                    if (hexColor) {
                        colorPicker.value = hexColor;
                    }
                });
            }
        });

        // Convert hex to approximate color name
        function getColorName(hex) {
            const colorNames = {
                '#ff0000': 'Red',
                '#00ff00': 'Green',
                '#0000ff': 'Blue',
                '#ffff00': 'Yellow',
                '#ff00ff': 'Magenta',
                '#00ffff': 'Cyan',
                '#ffffff': 'White',
                '#000000': 'Black',
                '#808080': 'Gray',
                '#ffc0cb': 'Pink',
                '#ffa500': 'Orange',
                '#800080': 'Purple',
                '#a52a2a': 'Brown',
                '#ffd700': 'Gold',
                '#c0c0c0': 'Silver',
                '#008080': 'Teal',
                '#000080': 'Navy',
                '#800000': 'Maroon'
            };

            // If exact match exists
            if (colorNames[hex.toLowerCase()]) {
                return colorNames[hex.toLowerCase()];
            }

            // Otherwise return the hex value
            return hex;
        }

        // Convert common color names to hex
        function getHexFromColorName(name) {
            const colors = {
                'red': '#ff0000',
                'green': '#00ff00',
                'blue': '#0000ff',
                'yellow': '#ffff00',
                'magenta': '#ff00ff',
                'cyan': '#00ffff',
                'white': '#ffffff',
                'black': '#000000',
                'gray': '#808080',
                'grey': '#808080',
                'pink': '#ffc0cb',
                'orange': '#ffa500',
                'purple': '#800080',
                'brown': '#a52a2a',
                'gold': '#ffd700',
                'silver': '#c0c0c0',
                'teal': '#008080',
                'navy': '#000080',
                'maroon': '#800000'
            };

            return colors[name] || null;
        }
    </script>
</body>

</html>