<?php

/**
 * Customer Profile Edit Page
 * Allows customers to edit their profile and change password
 */

// Security
define('DB_ACCESS', true);
require_once '../config/session_check.php';
require_once '../config/security.php';

// Check if user is customer
if ($_SESSION['user_type'] !== 'customer') {
    header('Location: ../index.php');
    exit;
}

$customer_id = $_SESSION['user_id'];
$customer_name = $_SESSION['user_name'];

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
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
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

            .form-input {
                padding: 0.75rem;
                font-size: 0.95rem;
            }

            .form-button {
                padding: 0.75rem;
                font-size: 0.9rem;
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
                <li><a href="orders.php" class="nav-link">My Orders</a></li>
                <li><a href="profile.php" class="nav-link active">Profile</a></li>
            </ul>

            <div class="nav-auth">
                <span class="welcome-text" style="margin-right: 1rem;">Welcome, <?php echo htmlspecialchars($customer_name); ?>!</span>
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
            <h1><i class="fas fa-user-edit"></i> Edit Profile</h1>
            <p>Manage your account settings and preferences</p>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">

            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar-container">
                    <div class="profile-avatar" id="profileAvatarDisplay">
                        <span id="avatarInitial"><?php echo strtoupper(substr($customer_name, 0, 1)); ?></span>
                        <img id="avatarImage" src="" alt="Profile" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    </div>
                    <div class="avatar-upload-controls">
                        <label for="profileImageInput" class="avatar-upload-btn" title="Upload Image">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="profileImageInput" accept="image/*" style="display: none;">
                        <button type="button" class="avatar-delete-btn" id="deleteAvatarBtn" style="display: none;" title="Delete Image">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($customer_name); ?></div>
                <div class="profile-type"><i class="fas fa-user"></i> Customer</div>

                <ul class="profile-nav">
                    <li>
                        <a href="#" class="profile-nav-link active" data-tab="profile-info">
                            <i class="fas fa-user"></i>
                            <span>Profile Information</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="profile-nav-link" data-tab="change-password">
                            <i class="fas fa-lock"></i>
                            <span>Change Password</span>
                        </a>
                    </li>
                </ul>
            </div>


            <!-- Main Content -->
            <div class="profile-main">

                <!-- Profile Information Tab -->
                <div id="profile-info" class="tab-content active">
                    <h2 class="section-title">Profile Information</h2>

                    <div class="alert alert-success" id="profileSuccessAlert"></div>
                    <div class="alert alert-error" id="profileErrorAlert"></div>

                    <form id="profileForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-input" id="fullName"
                                    required pattern="[A-Za-z\s]{2,50}"
                                    title="Name should only contain letters and spaces (2-50 characters)"
                                    minlength="2" maxlength="50">
                            </div>

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
                        </div>

                        <div class="form-row">
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
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-input form-textarea" id="address"
                                    placeholder="Enter your full address"
                                    maxlength="500"
                                    title="Address should not exceed 500 characters"></textarea>
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
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadProfileData();
            initializeTabs();
            initializeForms();
            initializeImageHandlers();
        });

        // Tab switching
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

        // Load profile data
        function loadProfileData() {
            fetch('../api/profile/get_profile.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const profile = data.data;
                        document.getElementById('fullName').value = profile.full_name || '';
                        document.getElementById('email').value = profile.email || '';
                        document.getElementById('phone').value = profile.phone || '';
                        document.getElementById('address').value = profile.address || '';

                        // Load profile image if exists
                        if (profile.profile_image) {
                            let imagePath;
                            // Check if it's a full URL (Cloudinary) or local file
                            if (profile.profile_image.startsWith('http')) {
                                imagePath = profile.profile_image;
                            } else {
                                // Build correct image path for local files
                                imagePath = '/smart/smart-tailoring/uploads/profiles/' + profile.profile_image;
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


        // Initialize form handlers
        function initializeForms() {
            // Profile form
            document.getElementById('profileForm').addEventListener('submit', function(e) {
                e.preventDefault();
                updateProfile();
            });

            // Password form
            document.getElementById('passwordForm').addEventListener('submit', function(e) {
                e.preventDefault();
                changePassword();
            });
        }

        // Initialize image upload handlers
        function initializeImageHandlers() {
            // Upload handler
            document.getElementById('profileImageInput').addEventListener('change', handleImageUpload);

            // Delete handler
            document.getElementById('deleteAvatarBtn').addEventListener('click', handleImageDelete);
        }


        // Handle image upload
        function handleImageUpload(e) {
            const file = e.target.files[0];

            if (!file) return;

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file');
                e.target.value = '';
                return;
            }

            // Validate file size (5MB)
            if (file.size > 5242880) {
                alert('File size must be less than 5MB');
                e.target.value = '';
                return;
            }

            // Upload image
            const formData = new FormData();
            formData.append('profile_image', file);

            fetch('../api/profile/upload_image.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Get the text first to see what we're actually receiving
                    return response.text();
                })
                .then(text => {

                    // Try to parse as JSON
                    try {
                        const data = JSON.parse(text);

                        if (data.success) {
                            displayProfileImage(data.image_url);
                            document.getElementById('deleteAvatarBtn').style.display = 'flex';
                            alert('Profile image uploaded successfully!');
                        } else {
                            alert('Error: ' + data.message);
                        }
                    } catch (error) {
                        console.error('JSON parse error:', error);
                        console.error('Received text:', text);
                        alert('Server returned invalid response. Check console for details.');
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    alert('Failed to upload image. Please try again.');
                });

            // Reset input
            e.target.value = '';
        }


        // Handle image delete
        function handleImageDelete() {
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
                    console.error('Delete error:', error);
                    alert('Failed to delete image. Please try again.');
                });
        }

        // Display profile image
        function displayProfileImage(imageUrl) {
            const avatarImage = document.getElementById('avatarImage');
            const avatarInitial = document.getElementById('avatarInitial');

            if (avatarImage && avatarInitial) {
                // Remove escaped backslashes
                imageUrl = imageUrl.replace(/\\/g, '/');

                // Convert relative path to absolute path
                if (imageUrl.startsWith('../')) {
                    // Count how many ../ there are
                    const levels = (imageUrl.match(/\.\.\//g) || []).length;

                    // Remove all ../
                    imageUrl = imageUrl.replace(/\.\.\//g, '');

                    // Build absolute path
                    imageUrl = '/smart/smart-tailoring/' + imageUrl;
                }

                avatarImage.src = imageUrl;
                avatarImage.style.display = 'block';
                avatarInitial.style.display = 'none';
            }
        }



        // Hide profile image
        function hideProfileImage() {
            const avatarImage = document.getElementById('avatarImage');
            const avatarInitial = document.getElementById('avatarInitial');

            if (avatarImage && avatarInitial) {
                avatarImage.style.display = 'none';
                avatarInitial.style.display = 'block';
            }
        }

        // Update profile
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

        // Change password
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

        // Show alert
        function showAlert(alertId, message) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.textContent = message;
                alert.classList.add('show');
            }
        }

        // Hide alert
        function hideAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.classList.remove('show');
            }
        }
    </script>

</body>

</html>