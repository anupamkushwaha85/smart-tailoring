<?php

/**
 * Tailor Dashboard
 * Main page for logged-in tailors
 */

// Start secure session
require_once '../config/session.php';

// Check if user is logged in and is a tailor
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'tailor') {
    header('Location: ../index.php');
    exit;
}

// Include database connection
define('DB_ACCESS', true);
require_once '../config/db.php';
require_once '../repositories/TailorRepository.php';

// Get tailor details
$tailor_id = $_SESSION['user_id'];

$tailorRepo = new TailorRepository($conn);
$tailor = $tailorRepo->findById($tailor_id);
$shop_image = $tailor['shop_image'] ?? null;

// Get tailor's order count
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE tailor_id = " . intval($tailor_id));
$order_data = $result->fetch_assoc();
$total_orders = $order_data['count'] ?? 0;

// Get pending orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE tailor_id = " . intval($tailor_id) . " AND order_status = 'pending'");
$pending_data = $result->fetch_assoc();
$pending_orders = $pending_data['count'] ?? 0;

// Get completed orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE tailor_id = " . intval($tailor_id) . " AND order_status = 'completed'");
$completed_data = $result->fetch_assoc();
$completed_orders = $completed_data['count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tailor Dashboard - Smart Tailoring Service</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpg" href="../assets/images/STP-favicon.jpg">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        /* Dashboard-specific styles */
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 2rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-section h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            opacity: 0.9;
        }

        .verification-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .verified {
            background: #10b981;
        }

        .not-verified {
            background: #f59e0b;
        }

        .logout-btn {
            background: var(--white);
            color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .stat-icon.purple {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
        }

        .stat-content h3 {
            font-size: 2rem;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .stat-content p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .dashboard-section {
            background: var(--white);
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            font-size: 1.75rem;
            color: var(--text-dark);
        }

        .shop-card {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 2rem;
            align-items: start;
        }

        .shop-avatar {
            width: 120px;
            height: 120px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 3rem;
            font-weight: 700;
        }

        .shop-details {
            flex: 1;
        }

        .shop-details h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .shop-subtitle {
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        .shop-info {
            display: grid;
            gap: 0.75rem;
        }

        .info-row {
            display: flex;
            gap: 1rem;
            color: var(--text-light);
        }

        .info-row i {
            width: 20px;
            color: var(--primary-color);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-btn {
            padding: 1rem;
            background: var(--light-bg);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--text-dark);
        }

        .action-btn:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .action-btn i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            display: block;
        }

        /* Mobile Responsive */
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

            .dashboard-container {
                padding: 1rem;
                display: flex;
                flex-direction: column;
            }

            /* Reorder sections for mobile */
            .dashboard-header {
                order: 0;
                flex-direction: column;
                text-align: center;
                gap: 1rem;
                padding: 1.5rem;
            }

            .dashboard-header h1 {
                font-size: 1.5rem;
            }

            .dashboard-header .logout-btn {
                display: none;
            }

            .stats-grid {
                order: 1;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }

            /* Quick Actions section comes before Profile on mobile */
            .dashboard-section:has(.quick-actions) {
                order: 2;
            }

            .dashboard-section:has(.shop-card) {
                order: 3;
            }

            .stat-card {
                flex-direction: column;
                padding: 1rem;
                text-align: center;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .stat-content h3 {
                font-size: 1.5rem;
            }

            .stat-content p {
                font-size: 0.75rem;
            }

            .shop-card {
                grid-template-columns: 1fr;
                text-align: center;
                justify-items: center;
                gap: 1rem;
            }

            .shop-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }

            .shop-details h3 {
                font-size: 1.3rem;
            }

            .shop-info {
                gap: 0.5rem;
            }

            .info-row {
                font-size: 0.9rem;
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .action-btn {
                padding: 1rem 0.5rem;
                font-size: 0.85rem;
            }

            .action-btn i {
                font-size: 1.5rem;
            }

            .action-btn strong {
                font-size: 0.85rem;
            }

            .section-header h2 {
                font-size: 1.3rem;
            }

            .dashboard-section {
                padding: 1.5rem 1rem;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 0 0.75rem;
            }

            .dashboard-header {
                padding: 1.25rem 1rem;
            }

            .dashboard-header h1 {
                font-size: 1.25rem;
            }

            .stats-grid {
                gap: 0.4rem;
            }

            .stat-card {
                padding: 0.75rem 0.5rem;
            }

            .stat-icon {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }

            .stat-content h3 {
                font-size: 1.25rem;
            }

            .stat-content p {
                font-size: 0.7rem;
            }

            .shop-avatar {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }

            .shop-details h3 {
                font-size: 1.15rem;
            }

            .info-row {
                font-size: 0.85rem;
            }

            .dashboard-section {
                padding: 1rem;
            }

            .section-header h2 {
                font-size: 1.15rem;
            }

            .action-btn {
                padding: 0.85rem 0.4rem;
            }

            .action-btn i {
                font-size: 1.3rem;
            }

            .action-btn strong {
                font-size: 0.75rem;
            }
        }
    </style>
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <!-- Logo -->
            <div class="nav-logo">
                <img src="../assets/images/logo.png" alt="Smart Tailoring Service Logo">
                <span class="logo-text">Smart Tailoring Service</span>
            </div>

            <!-- Navigation Menu -->
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link">Home</a></li>
                <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                <li><a href="orders.php" class="nav-link">Orders</a></li>

                <li><a href="profile.php" class="nav-link">Shop Profile</a></li>
            </ul>

            <!-- User Info -->
            <div class="nav-auth">
                <span class="welcome-text" style="margin-right: 1rem;">Welcome, <?php echo htmlspecialchars($tailor['owner_name']); ?>!</span>
                <a href="dashboard.php" class="btn-dashboard" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i> <span class="btn-text">Dashboard</span>
                </a>
                <button class="btn-logout" onclick="window.location.href='../auth/logout.php'">
                    <i class="fas fa-sign-out-alt"></i> <span class="btn-text">Logout</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Dashboard Container -->
    <div class="dashboard-container">

        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1>Welcome back, <?php echo htmlspecialchars($tailor['owner_name']); ?>!</h1>
                <p>Manage your shop and orders</p>
                <span class="verification-badge <?php echo $tailor['is_verified'] ? 'verified' : 'not-verified'; ?>">
                    <i class="fas fa-<?php echo $tailor['is_verified'] ? 'check-circle' : 'clock'; ?>"></i>
                    <?php echo $tailor['is_verified'] ? 'Verified Shop' : 'Pending Verification'; ?>
                </span>
            </div>
            <button class="logout-btn" onclick="window.location.href='../auth/logout.php'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $total_orders; ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $pending_orders; ?></h3>
                    <p>Pending Orders</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $completed_orders; ?></h3>
                    <p>Completed Orders</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($tailor['rating'], 1); ?></h3>
                    <p>Average Rating</p>
                </div>
            </div>
        </div>

        <!-- Shop Profile Section -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-store"></i> Your Shop Profile</h2>
            </div>

            <div class="shop-card">

                <div class="shop-avatar">
                    <?php if ($shop_image): ?>
                        <?php
                        $image_src = (strpos($shop_image, 'http') === 0)
                            ? $shop_image
                            : '../uploads/shops/' . $shop_image;
                        ?>
                        <img src="<?php echo htmlspecialchars($image_src); ?>"
                            alt="Shop Image"
                            style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <?php echo strtoupper(substr($tailor['shop_name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>


                <div class="shop-details">
                    <h3><?php echo htmlspecialchars($tailor['shop_name']); ?></h3>
                    <p class="shop-subtitle">Owned by <?php echo htmlspecialchars($tailor['owner_name']); ?></p>

                    <div class="shop-info">
                        <div class="info-row">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($tailor['email']); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($tailor['phone']); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($tailor['shop_address']); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-map"></i>
                            <span><?php echo htmlspecialchars($tailor['area']); ?>, Satna</span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-star"></i>
                            <span><?php echo htmlspecialchars($tailor['speciality']); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-briefcase"></i>
                            <span><?php echo $tailor['experience_years']; ?>+ years of experience</span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-calendar"></i>
                            <span>Member since <?php echo date('F Y', strtotime($tailor['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            </div>

            <div class="quick-actions">
                <a href="../index.php" class="action-btn">
                    <i class="fas fa-home"></i>
                    <strong>Home</strong>
                </a>

                <a href="orders.php" class="action-btn">
                    <i class="fas fa-inbox"></i>
                    <strong>View Orders</strong>
                </a>

                <a href="profile.php" class="action-btn">
                    <i class="fas fa-edit"></i>
                    <strong>Edit Shop Profile</strong>
                </a>

                <a href="../index.php#tailors" class="action-btn">
                    <i class="fas fa-eye"></i>
                    <strong>View My Listing</strong>
                </a>
            </div>
        </div>

    </div>

</body>

</html>