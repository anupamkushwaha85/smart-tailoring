<?php

/**
 * Tailor Profile Edit Page
 */

session_start();

// Security
define('DB_ACCESS', true);
require_once '../config/security.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'tailor') {
    header('Location: ../index.php');
    exit;
}

$tailor_id = $_SESSION['user_id'];
$tailor_name = $_SESSION['user_name'];
$shop_name = $_SESSION['shop_name'];

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Smart Tailoring Service</title>

    <link rel="icon" type="image/jpg" href="../assets/images/STP-favicon.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- MapLibre GL JS CSS (Modern GPU-accelerated maps) -->
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />

    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Same styles as customer profile.php */
        .profile-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 2rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
        }

        .page-header h1 {
            margin: 0;
            font-size: 2rem;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
        }

        .profile-sidebar {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            height: fit-content;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 3rem;
            color: var(--white);
            font-weight: 700;
        }

        .profile-avatar-container {
            position: relative;
            margin-bottom: 1rem;
        }

        .profile-avatar {
            position: relative;
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-avatar span {
            display: block;
        }

        .avatar-upload-controls {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .avatar-upload-btn,
        .avatar-delete-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .avatar-upload-btn {
            background: var(--primary-color);
            color: var(--white);
        }

        .avatar-upload-btn:hover {
            background: var(--secondary-color);
            transform: scale(1.1);
        }

        .avatar-delete-btn {
            background: #ef4444;
            color: var(--white);
        }

        .avatar-delete-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
        }


        .profile-name {
            text-align: center;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .profile-type {
            text-align: center;
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .profile-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .profile-nav li {
            margin-bottom: 0.5rem;
        }

        .profile-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .profile-nav-link:hover {
            background: var(--light-bg);
            color: var(--primary-color);
        }

        .profile-nav-link.active {
            background: var(--primary-color);
            color: var(--white);
        }

        .profile-main {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--light-bg);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-row-full {
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-button {
            background: var(--primary-color);
            color: var(--white);
            padding: 0.875rem 2rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .form-button:disabled {
            background: var(--border-color);
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 2px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 2px solid #ef4444;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 768px) {

            /* Hide welcome text in navbar on mobile */
            .welcome-text {
                display: none !important;
            }

            /* Hide navigation menu on mobile */
            .nav-menu {
                display: none;
            }

            /* Make dashboard and logout buttons icon-only on mobile */
            .btn-dashboard .btn-text,
            .btn-logout .btn-text {
                display: none;
            }

            .btn-dashboard,
            .btn-logout {
                width: 40px;
                height: 40px;
                padding: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                margin-left: 0.5rem;
            }

            .btn-dashboard i,
            .btn-logout i {
                margin: 0;
                font-size: 1.1rem;
            }

            .profile-container {
                padding: 0 1rem;
                margin: 1rem auto;
            }

            .page-header {
                padding: 1.5rem 1rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .profile-content {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .profile-sidebar {
                padding: 1.25rem;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }

            .profile-main {
                padding: 1.25rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .form-button {
                padding: 0.85rem;
                font-size: 0.95rem;
            }

            #map {
                height: 250px;
            }
        }

        @media (max-width: 480px) {
            .profile-container {
                padding: 0 0.75rem;
            }

            .page-header {
                padding: 1.25rem 0.85rem;
            }

            .page-header h1 {
                font-size: 1.25rem;
            }

            .profile-sidebar,
            .profile-main {
                padding: 1rem;
            }

            .profile-avatar {
                width: 90px;
                height: 90px;
                font-size: 2.25rem;
            }

            .profile-name {
                font-size: 1.1rem;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .form-input,
            .form-select,
            .form-textarea {
                padding: 0.75rem;
                font-size: 0.95rem;
            }

            .form-button {
                padding: 0.75rem;
                font-size: 0.9rem;
            }

            #map {
                height: 220px;
            }
        }
    </style>
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="../assets/images/logo.png" alt="Logo">
                <span class="logo-text">Smart Tailoring Service</span>
            </div>

            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link">Home</a></li>
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="orders.php" class="nav-link">Orders</a></li>
                <li><a href="profile.php" class="nav-link active">Shop Profile</a></li>
            </ul>

            <div class="nav-auth">
                <span class="welcome-text" style="margin-right: 1rem;">Welcome, <?php echo htmlspecialchars($tailor_name); ?>!</span>
                <a href="dashboard.php" class="btn-dashboard" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i> <span class="btn-text">Dashboard</span>
                </a>
                <button class="btn-logout" onclick="window.location.href='../auth/logout.php'">
                    <i class="fas fa-sign-out-alt"></i> <span class="btn-text">Logout</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Profile Container -->
    <div class="profile-container">

        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-store"></i> Edit Shop Profile</h1>
            <p>Manage your shop information and account settings</p>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">

            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar-container">
                    <div class="profile-avatar" id="profileAvatarDisplay">
                        <span id="avatarInitial"><?php echo strtoupper(substr($shop_name, 0, 1)); ?></span>
                        <img id="avatarImage" src="" alt="Shop Image" style="display: none;">
                    </div>
                    <div class="avatar-upload-controls">
                        <label for="profileImageInput" class="avatar-upload-btn" title="Upload Shop Image">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="profileImageInput" accept="image/*" style="display: none;">
                        <button class="avatar-delete-btn" id="deleteAvatarBtn" style="display: none;" title="Delete Image">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($shop_name); ?></div>
                <div class="profile-type"><i class="fas fa-store"></i> Tailor Shop</div>


                <ul class="profile-nav">
                    <li>
                        <a href="#" class="profile-nav-link active" data-tab="shop-info">
                            <i class="fas fa-store"></i>
                            <span>Shop Information</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="profile-nav-link" data-tab="change-password">
                            <i class="fas fa-lock"></i>
                            <span>Change Password</span>
                        </a>
                    </li>
                </ul>

                <!-- Location Setter Button -->
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                    <button onclick="showLocationSetterModal()" style="width: 100%; padding: 0.75rem; background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Set Shop Location</span>
                    </button>
                    <p style="font-size: 0.75rem; color: #6b7280; text-align: center; margin-top: 0.5rem;">
                        Help customers find you on map
                    </p>
                </div>
            </div>

            <!-- Main Content -->
            <div class="profile-main">

                <!-- Shop Information Tab -->
                <div id="shop-info" class="tab-content active">
                    <h2 class="section-title">Shop Information</h2>

                    <div class="alert alert-success" id="profileSuccessAlert"></div>
                    <div class="alert alert-error" id="profileErrorAlert"></div>

                    <form id="profileForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Shop Name *</label>
                                <input type="text" name="shop_name" class="form-input" id="shopName"
                                    required pattern="[A-Za-z0-9\s&'-]{2,100}"
                                    title="Shop name should be 2-100 characters"
                                    minlength="2" maxlength="100">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Owner Name *</label>
                                <input type="text" name="owner_name" class="form-input" id="ownerName"
                                    required pattern="[A-Za-z\s]{2,50}"
                                    title="Name should only contain letters and spaces (2-50 characters)"
                                    minlength="2" maxlength="50">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-input" id="email"
                                    required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                                    title="Email cannot be changed"
                                    maxlength="100" readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                                <small style="color: #6b7280; font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                                    <i class="fas fa-info-circle"></i> Email cannot be changed for security reasons
                                </small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" name="phone" class="form-input" id="phone"
                                    pattern="[6-9][0-9]{9}" placeholder="10 digit phone number"
                                    title="Phone number must be 10 digits starting with 6-9"
                                    minlength="10" maxlength="10" required>
                            </div>
                        </div>

                        <div class="form-row-full">
                            <div class="form-group">
                                <label class="form-label">Shop Address *</label>
                                <textarea name="shop_address" class="form-input form-textarea" id="shopAddress"
                                    placeholder="Enter your shop address" required
                                    minlength="10" maxlength="500"
                                    title="Address should be between 10 and 500 characters"></textarea>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Area *</label>
                                <input type="text" name="area" class="form-input" id="area"
                                    placeholder="e.g., Civil Lines, Ramnagar" required
                                    pattern="[A-Za-z\s,.-]{2,50}"
                                    title="Area name should be 2-50 characters"
                                    minlength="2" maxlength="50">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Speciality</label>
                                <input type="text" name="speciality" class="form-input" id="speciality"
                                    placeholder="e.g., Wedding Specialist, Designer Work"
                                    maxlength="100"
                                    title="Speciality should not exceed 100 characters">
                            </div>
                        </div>

                        <div class="form-row-full">
                            <div class="form-group">
                                <label class="form-label">Services Offered</label>
                                <input type="text" name="services_offered" class="form-input" id="servicesOffered"
                                    placeholder="e.g., Stitching, Alteration, Embroidery"
                                    maxlength="200"
                                    title="Services should not exceed 200 characters">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Experience (Years)</label>
                                <input type="number" name="experience_years" class="form-input" id="experienceYears"
                                    min="0" max="50" placeholder="Years of experience"
                                    title="Experience should be between 0 and 50 years">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Price Range</label>
                                <input type="text" name="price_range" class="form-input" id="priceRange"
                                    placeholder="e.g., ₹500-2000, Budget Friendly"
                                    maxlength="50"
                                    title="Price range should not exceed 50 characters">
                            </div>
                        </div>

                        <button type="submit" class="form-button" id="profileSubmitBtn">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>

                <!-- Change Password Tab -->
                <div id="change-password" class="tab-content">
                    <h2 class="section-title">Change Password</h2>

                    <div class="alert alert-success" id="passwordSuccessAlert"></div>
                    <div class="alert alert-error" id="passwordErrorAlert"></div>

                    <form id="passwordForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="form-group">
                            <label class="form-label">Current Password *</label>
                            <input type="password" name="current_password" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">New Password *</label>
                            <input type="password" name="new_password" class="form-input"
                                minlength="6" placeholder="At least 6 characters" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm New Password *</label>
                            <input type="password" name="confirm_password" class="form-input"
                                minlength="6" placeholder="Re-enter new password" required>
                        </div>

                        <button type="submit" class="form-button" id="passwordSubmitBtn">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </div>

    <script>
        // Same JavaScript as customer profile, adapted for tailor fields
        document.addEventListener('DOMContentLoaded', function() {
            loadProfileData();
            initializeTabs();
            initializeForms();
        });

        function initializeTabs() {
            const navLinks = document.querySelectorAll('.profile-nav-link');

            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    navLinks.forEach(l => l.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        }

        function loadProfileData() {
            fetch('../api/profile/get_profile.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const profile = data.data;
                        document.getElementById('shopName').value = profile.shop_name || '';
                        document.getElementById('ownerName').value = profile.owner_name || '';
                        document.getElementById('email').value = profile.email || '';
                        document.getElementById('phone').value = profile.phone || '';
                        document.getElementById('shopAddress').value = profile.shop_address || '';
                        document.getElementById('area').value = profile.area || '';
                        document.getElementById('speciality').value = profile.speciality || '';
                        document.getElementById('servicesOffered').value = profile.services_offered || '';
                        document.getElementById('experienceYears').value = profile.experience_years || '';
                        document.getElementById('priceRange').value = profile.price_range || '';

                        // Load shop image if exists
                        if (profile.shop_image) {
                            let imagePath;
                            // Check if it's a full URL (Cloudinary) or local file
                            if (profile.shop_image.startsWith('http')) {
                                imagePath = profile.shop_image;
                            } else {
                                // Build correct image path for local files
                                imagePath = '../uploads/shops/' + profile.shop_image;
                            }
                            displayProfileImage(imagePath);
                            document.getElementById('deleteAvatarBtn').style.display = 'flex';
                        }

                    }
                })
                .catch(error => {
                    console.error('Error loading profile:', error);
                });
        }


        // Display profile image
        function displayProfileImage(imageUrl) {
            const avatarImage = document.getElementById('avatarImage');
            const avatarInitial = document.getElementById('avatarInitial');

            if (avatarImage && avatarInitial) {
                // Remove escaped backslashes
                imageUrl = imageUrl.replace(/\\/g, '/');

                // Use the path as is - relative paths work fine

                avatarImage.src = imageUrl;
                avatarImage.style.display = 'block';
                avatarInitial.style.display = 'none';
            }
        }


        // Hide profile image and show initial
        function hideProfileImage() {
            const avatarImage = document.getElementById('avatarImage');
            const avatarInitial = document.getElementById('avatarInitial');

            avatarImage.style.display = 'none';
            avatarInitial.style.display = 'block';
        }

        // Handle image upload
        document.getElementById('profileImageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (!file) return;

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file');
                return;
            }

            // Validate file size (5MB)
            if (file.size > 5242880) {
                alert('File size must be less than 5MB');
                return;
            }

            // Upload image
            const formData = new FormData();
            formData.append('profile_image', file);

            fetch('../api/profile/upload_image.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let imagePath;
                        if (data.image_url.startsWith('http')) {
                            imagePath = data.image_url;
                        } else {
                            imagePath = '../uploads/shops/' + data.filename;
                        }
                        displayProfileImage(imagePath);
                        document.getElementById('deleteAvatarBtn').style.display = 'flex';
                        alert('Profile image uploaded successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to upload image');
                });

            // Reset input
            e.target.value = '';
        });

        // Handle image delete
        document.getElementById('deleteAvatarBtn').addEventListener('click', function() {
            if (!confirm('Are you sure you want to delete your profile image?')) {
                return;
            }

            fetch('../api/profile/delete_image.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hideProfileImage();
                        document.getElementById('deleteAvatarBtn').style.display = 'none';
                        alert('Profile image deleted successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete image');
                });
        });


        function initializeForms() {
            document.getElementById('profileForm').addEventListener('submit', function(e) {
                e.preventDefault();
                updateProfile();
            });

            document.getElementById('passwordForm').addEventListener('submit', function(e) {
                e.preventDefault();
                changePassword();
            });
        }

        function updateProfile() {
            const form = document.getElementById('profileForm');
            const formData = new FormData(form);
            const submitBtn = document.getElementById('profileSubmitBtn');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            fetch('../api/profile/update_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('profileSuccessAlert', data.message);
                        hideAlert('profileErrorAlert');
                    } else {
                        showAlert('profileErrorAlert', data.message);
                        hideAlert('profileSuccessAlert');
                    }

                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('profileErrorAlert', 'Failed to update profile. Please try again.');
                    hideAlert('profileSuccessAlert');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                });
        }

        function changePassword() {
            const form = document.getElementById('passwordForm');
            const formData = new FormData(form);
            const submitBtn = document.getElementById('passwordSubmitBtn');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing...';

            fetch('../api/profile/change_password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('passwordSuccessAlert', data.message);
                        hideAlert('passwordErrorAlert');
                        form.reset();
                    } else {
                        showAlert('passwordErrorAlert', data.message);
                        hideAlert('passwordSuccessAlert');
                    }

                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-key"></i> Change Password';
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('passwordErrorAlert', 'Failed to change password. Please try again.');
                    hideAlert('passwordSuccessAlert');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-key"></i> Change Password';
                });
        }

        function showAlert(alertId, message) {
            const alert = document.getElementById(alertId);
            alert.textContent = message;
            alert.classList.add('show');
        }

        function hideAlert(alertId) {
            const alert = document.getElementById(alertId);
            alert.classList.remove('show');
        }
    </script>

    <!-- MapLibre GL JS (Modern GPU-accelerated maps - free, no API key) -->
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>

    <!-- Map Integration Script -->
    <script src="../assets/js/map-integration.js"></script>

</body>

</html>