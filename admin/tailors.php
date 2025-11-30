<?php

/**
 * Admin - Tailor Management
 * View, verify, and manage tailors
 */

// Security check
require_once 'includes/admin_security.php';

// Database connection
define('DB_ACCESS', true);
require_once '../config/db.php';
require_once '../services/NotificationService.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $tailor_id = intval($_POST['tailor_id']);
    $action = $_POST['action'];

    try {
        $notificationService = new NotificationService($conn);

        if ($action === 'verify') {
            // Verify tailor
            $stmt = $conn->prepare("UPDATE tailors SET is_verified = 1, verified_by_admin_id = ?, verified_at = NOW() WHERE id = ?");
            $stmt->bind_param("ii", $admin_id, $tailor_id);
            $stmt->execute();

            logActivity('verify_tailor', "Verified tailor ID: $tailor_id", 'tailor', $tailor_id);
            $success_message = "Tailor verified successfully!";

            // Send verification notification to tailor
            $notificationService->notifyVerification($tailor_id, true);
        } elseif ($action === 'unverify') {
            // Unverify tailor
            $stmt = $conn->prepare("UPDATE tailors SET is_verified = 0, verified_by_admin_id = NULL, verified_at = NULL WHERE id = ?");
            $stmt->bind_param("i", $tailor_id);
            $stmt->execute();

            logActivity('unverify_tailor', "Unverified tailor ID: $tailor_id", 'tailor', $tailor_id);
            $success_message = "Tailor unverified successfully!";

            // Send unverification notification to tailor
            $notificationService->notifyVerification($tailor_id, false);
        } elseif ($action === 'block') {
            // Block tailor
            $stmt = $conn->prepare("UPDATE tailors SET is_blocked = 1, blocked_at = NOW(), blocked_by_admin_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $admin_id, $tailor_id);
            $stmt->execute();

            logActivity('block_tailor', "Blocked tailor ID: $tailor_id", 'tailor', $tailor_id);
            $success_message = "Tailor blocked successfully!";
        } elseif ($action === 'unblock') {
            // Unblock tailor
            $stmt = $conn->prepare("UPDATE tailors SET is_blocked = 0, blocked_at = NULL, blocked_by_admin_id = NULL WHERE id = ?");
            $stmt->bind_param("i", $tailor_id);
            $stmt->execute();

            logActivity('unblock_tailor', "Unblocked tailor ID: $tailor_id", 'tailor', $tailor_id);
            $success_message = "Tailor unblocked successfully!";
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = [];
$params = [];
$types = '';

if ($filter === 'pending') {
    $where_conditions[] = "is_verified = 0 AND is_blocked = 0";
} elseif ($filter === 'verified') {
    $where_conditions[] = "is_verified = 1";
} elseif ($filter === 'blocked') {
    $where_conditions[] = "is_blocked = 1";
}

if (!empty($search)) {
    $where_conditions[] = "(shop_name LIKE ? OR owner_name LIKE ? OR phone LIKE ? OR area LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= 'ssss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get tailors
$query = "SELECT * FROM tailors $where_clause ORDER BY created_at DESC";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$tailors = $result->fetch_all(MYSQLI_ASSOC);

// Get counts for tabs
$total_result = db_fetch_one("SELECT COUNT(*) as count FROM tailors");
$total_count = $total_result['count'] ?? 0;

$pending_result = db_fetch_one("SELECT COUNT(*) as count FROM tailors WHERE is_verified = 0 AND is_blocked = 0");
$pending_count = $pending_result['count'] ?? 0;

$verified_result = db_fetch_one("SELECT COUNT(*) as count FROM tailors WHERE is_verified = 1");
$verified_count = $verified_result['count'] ?? 0;

$blocked_result = db_fetch_one("SELECT COUNT(*) as count FROM tailors WHERE is_blocked = 1");
$blocked_count = $blocked_result['count'] ?? 0;

db_close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tailor Management - Admin Panel</title>

    <link rel="icon" type="image/jpg" href="../assets/images/STP-favicon.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/admin.css">

    <style>
        .filter-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--border-color);
        }

        .filter-tab {
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--text-light);
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-tab:hover {
            color: var(--primary-color);
        }

        .filter-tab.active {
            color: var(--primary-color);
        }

        .filter-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .tab-count {
            background: var(--light-color);
            padding: 0.15rem 0.6rem;
            border-radius: 12px;
            font-size: 0.85rem;
        }

        .filter-tab.active .tab-count {
            background: var(--primary-color);
            color: white;
        }

        .search-bar {
            margin-bottom: 2rem;
        }

        .search-input {
            width: 100%;
            max-width: 500px;
            padding: 0.9rem 1rem 0.9rem 3rem;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .tailors-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .tailors-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .tailors-table th {
            background: var(--light-color);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            border-bottom: 2px solid var(--border-color);
        }

        .tailors-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .tailors-table tr:last-child td {
            border-bottom: none;
        }

        .tailors-table tr:hover {
            background: var(--light-color);
        }

        .tailor-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .tailor-owner {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-verified {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-blocked {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-verify {
            background: #d1fae5;
            color: #065f46;
        }

        .btn-verify:hover {
            background: #10b981;
            color: white;
        }

        .btn-block {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-block:hover {
            background: #ef4444;
            color: white;
        }

        .btn-unblock {
            background: #dbeafe;
            color: #1e40af;
        }

        .btn-unblock:hover {
            background: #3b82f6;
            color: white;
        }

        .message-box {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .message-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .message-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <?php include 'includes/admin_nav.php'; ?>

    <!-- Main Content -->
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1><i class="fas fa-store"></i> Tailor Management</h1>
                <p>Verify, manage, and monitor tailor accounts</p>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($success_message)): ?>
            <div class="message-box message-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message-box message-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All Tailors <span class="tab-count"><?php echo $total_count; ?></span>
            </a>
            <a href="?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Pending Verification <span class="tab-count"><?php echo $pending_count; ?></span>
            </a>
            <a href="?filter=verified" class="filter-tab <?php echo $filter === 'verified' ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i> Verified <span class="tab-count"><?php echo $verified_count; ?></span>
            </a>
            <a href="?filter=blocked" class="filter-tab <?php echo $filter === 'blocked' ? 'active' : ''; ?>">
                <i class="fas fa-ban"></i> Blocked <span class="tab-count"><?php echo $blocked_count; ?></span>
            </a>
        </div>

        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" style="position: relative; display: inline-block; width: 100%; max-width: 500px;">
                <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                <input type="text" name="search" class="search-input" placeholder="Search by shop name, owner, phone, area..." value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
            </form>
        </div>

        <!-- Tailors Table -->
        <div class="tailors-table">
            <table>
                <thead>
                    <tr>
                        <th>Shop Details</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tailors)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-light);">
                                <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                                No tailors found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tailors as $tailor): ?>
                            <tr>
                                <td>
                                    <div class="tailor-name"><?php echo htmlspecialchars($tailor['shop_name']); ?></div>
                                    <div class="tailor-owner"><i class="fas fa-user"></i> <?php echo htmlspecialchars($tailor['owner_name']); ?></div>
                                </td>
                                <td>
                                    <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($tailor['phone']); ?></div>
                                    <div style="color: var(--text-light); font-size: 0.9rem;"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($tailor['email']); ?></div>
                                </td>
                                <td>
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($tailor['area']); ?>, Satna
                                </td>
                                <td>
                                    <?php if ($tailor['is_blocked']): ?>
                                        <span class="badge badge-blocked"><i class="fas fa-ban"></i> Blocked</span>
                                    <?php elseif ($tailor['is_verified']): ?>
                                        <span class="badge badge-verified"><i class="fas fa-check-circle"></i> Verified</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending"><i class="fas fa-clock"></i> Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: var(--text-light); font-size: 0.9rem;">
                                    <?php echo date('M d, Y', strtotime($tailor['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if (!$tailor['is_blocked']): ?>
                                            <?php if (!$tailor['is_verified']): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="tailor_id" value="<?php echo $tailor['id']; ?>">
                                                    <input type="hidden" name="action" value="verify">
                                                    <button type="submit" class="btn-action btn-verify" onclick="return confirmAction('Verify this tailor?')">
                                                        <i class="fas fa-check"></i> Verify
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="tailor_id" value="<?php echo $tailor['id']; ?>">
                                                    <input type="hidden" name="action" value="unverify">
                                                    <button type="submit" class="btn-action btn-block" onclick="return confirmAction('Remove verification?')">
                                                        <i class="fas fa-times"></i> Unverify
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="tailor_id" value="<?php echo $tailor['id']; ?>">
                                                <input type="hidden" name="action" value="block">
                                                <button type="submit" class="btn-action btn-block" onclick="return confirmAction('Block this tailor? They will not be able to login.')">
                                                    <i class="fas fa-ban"></i> Block
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="tailor_id" value="<?php echo $tailor['id']; ?>">
                                                <input type="hidden" name="action" value="unblock">
                                                <button type="submit" class="btn-action btn-unblock" onclick="return confirmAction('Unblock this tailor?')">
                                                    <i class="fas fa-unlock"></i> Unblock
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="assets/admin.js"></script>
</body>

</html>