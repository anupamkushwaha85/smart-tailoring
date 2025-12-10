<?php

/**
 * Admin Dashboard
 * Main control panel for administrators
 */

// Security check
require_once 'includes/admin_security.php';

// Database connection
define('DB_ACCESS', true);
require_once '../config/db.php';

// Get dashboard statistics
$stats = [
    'total_customers' => 0,
    'total_tailors' => 0,
    'total_orders' => 0,
    'pending_verifications' => 0,
    'active_orders' => 0,
    'completed_orders' => 0,
    'total_revenue' => 0,
    'pending_disputes' => 0
];

try {
    // Total customers
    $result = db_fetch_one("SELECT COUNT(*) as count FROM customers WHERE is_blocked = 0");
    $stats['total_customers'] = $result['count'] ?? 0;

    // Total tailors
    $result = db_fetch_one("SELECT COUNT(*) as count FROM tailors WHERE is_blocked = 0");
    $stats['total_tailors'] = $result['count'] ?? 0;

    // Pending verifications
    $result = db_fetch_one("SELECT COUNT(*) as count FROM tailors WHERE is_verified = 0 AND is_blocked = 0");
    $stats['pending_verifications'] = $result['count'] ?? 0;

    // Total orders
    $result = db_fetch_one("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $result['count'] ?? 0;

    // Active orders
    $result = db_fetch_one("SELECT COUNT(*) as count FROM orders WHERE order_status NOT IN ('delivered', 'completed', 'cancelled')");
    $stats['active_orders'] = $result['count'] ?? 0;

    // Completed orders
    $result = db_fetch_one("SELECT COUNT(*) as count FROM orders WHERE order_status IN ('delivered', 'completed')");
    $stats['completed_orders'] = $result['count'] ?? 0;

    // Total revenue (sum of final prices from completed orders)
    $result = db_fetch_one("SELECT SUM(final_price) as total FROM orders WHERE order_status IN ('delivered', 'completed')");
    $stats['total_revenue'] = $result['total'] ?? 0;

    // Pending disputes (if table exists)
    $result = db_fetch_one("SELECT COUNT(*) as count FROM dispute_reports WHERE status = 'pending'");
    $stats['pending_disputes'] = $result['count'] ?? 0;

    // Recent activities (last 10)
    $recent_activities = db_fetch_all("
        SELECT * FROM admin_activity_log 
        ORDER BY created_at DESC 
        LIMIT 10
    ");

    // Recent orders (last 10)
    $recent_orders = db_fetch_all("
        SELECT o.*, 
               c.full_name as customer_name,
               t.shop_name as tailor_name
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        LEFT JOIN tailors t ON o.tailor_id = t.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ");

    // Pending tailor verifications (top 5)
    $pending_tailors = db_fetch_all("
        SELECT id, shop_name, owner_name, phone, area, created_at
        FROM tailors
        WHERE is_verified = 0 AND is_blocked = 0
        ORDER BY created_at ASC
        LIMIT 5
    ");
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
}

db_close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Tailoring Service</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpg" href="../assets/images/STP-favicon.jpg">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="assets/admin.css">
</head>

<body>
    <!-- Navigation -->
    <?php include 'includes/admin_nav.php'; ?>

    <!-- Main Content -->
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <p>Overview of your tailoring service platform</p>
            </div>
            <div class="header-actions">
                <span class="admin-badge"><?php echo htmlspecialchars($admin_role); ?></span>
                <span class="welcome-text">Welcome, <?php echo htmlspecialchars($admin_name); ?>!</span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_customers']); ?></h3>
                    <p>Total Customers</p>
                </div>
            </div>

            <div class="stat-card purple">
                <div class="stat-icon">
                    <i class="fas fa-store"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_tailors']); ?></h3>
                    <p>Total Tailors</p>
                </div>
            </div>

            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['pending_verifications']); ?></h3>
                    <p>Pending Verifications</p>
                    <a href="tailors.php?filter=pending" class="stat-link-inline">View All <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_orders']); ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>

            <div class="stat-card cyan">
                <div class="stat-icon">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['active_orders']); ?></h3>
                    <p>Active Orders</p>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['completed_orders']); ?></h3>
                    <p>Completed Orders</p>
                </div>
            </div>

            <div class="stat-card indigo">
                <div class="stat-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>₹<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>

            <div class="stat-card red">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['pending_disputes']); ?></h3>
                    <p>Pending Disputes</p>
                    <?php if ($stats['pending_disputes'] > 0): ?>
                        <a href="disputes.php" class="stat-link-inline">Resolve <i class="fas fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="dashboard-grid">
            <!-- Pending Verifications -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-user-check"></i> Pending Tailor Verifications</h2>
                    <a href="tailors.php?filter=pending" class="btn-view-all">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_tailors)): ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p>No pending verifications</p>
                        </div>
                    <?php else: ?>
                        <div class="list-items">
                            <?php foreach ($pending_tailors as $tailor): ?>
                                <div class="list-item">
                                    <div class="item-info">
                                        <h4><?php echo htmlspecialchars($tailor['shop_name']); ?></h4>
                                        <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($tailor['owner_name']); ?></p>
                                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($tailor['area']); ?></p>
                                        <span class="item-time"><?php echo date('M d, Y', strtotime($tailor['created_at'])); ?></span>
                                    </div>
                                    <div class="item-actions">
                                        <a href="tailors.php?view=<?php echo $tailor['id']; ?>" class="btn-small btn-primary">
                                            <i class="fas fa-eye"></i> Review
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-shopping-bag"></i> Recent Orders</h2>
                    <a href="orders.php" class="btn-view-all">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No orders yet</p>
                        </div>
                    <?php else: ?>
                        <div class="list-items">
                            <?php foreach ($recent_orders as $order): ?>
                                <div class="list-item">
                                    <div class="item-info">
                                        <h4>#<?php echo htmlspecialchars($order['order_number']); ?></h4>
                                        <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></p>
                                        <p><i class="fas fa-store"></i> <?php echo htmlspecialchars($order['tailor_name'] ?? 'Unassigned'); ?></p>
                                        <span class="order-status status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                        </span>
                                    </div>
                                    <div class="item-actions">
                                        <span class="price-tag">₹<?php echo number_format($order['final_price'] ?? 0, 2); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="dashboard-card full-width">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Recent Admin Activity</h2>
            </div>
            <div class="card-body">
                <?php if (empty($recent_activities)): ?>
                    <div class="empty-state">
                        <i class="fas fa-clock"></i>
                        <p>No recent activity</p>
                    </div>
                <?php else: ?>
                    <div class="activity-timeline">
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-<?php echo getActivityIcon($activity['action_type']); ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <p><strong><?php echo htmlspecialchars($activity['action_description']); ?></strong></p>
                                    <span class="activity-time">
                                        <i class="fas fa-clock"></i>
                                        <?php echo timeAgo($activity['created_at']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/admin.js"></script>
</body>

</html>

<?php
// Helper function to get activity icon
function getActivityIcon($action_type)
{
    $icons = [
        'login' => 'sign-in-alt',
        'logout' => 'sign-out-alt',
        'verify_tailor' => 'check-circle',
        'block_user' => 'ban',
        'unblock_user' => 'unlock',
        'delete_order' => 'trash',
        'update_order' => 'edit',
        'resolve_dispute' => 'gavel'
    ];

    return $icons[$action_type] ?? 'circle';
}

// Helper function for time ago
function timeAgo($datetime)
{
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';

    return date('M d, Y', $time);
}
?>