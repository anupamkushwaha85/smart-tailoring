<?php

/**
 * Notification System Test
 * This file helps test the notification system by creating sample notifications
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    die("Please login first to test notifications. <a href='../index.php'>Go to Homepage</a>");
}

define('DB_ACCESS', true);
require_once '../config/db.php';
require_once '../services/NotificationService.php';

$notificationService = new NotificationService($conn);
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification System Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            border-bottom: 3px solid #58d1f9;
            padding-bottom: 10px;
        }

        .user-info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #17a2b8;
        }

        .test-section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .test-section h2 {
            color: #495057;
            margin-top: 0;
        }

        .btn {
            background: linear-gradient(135deg, #58d1f9, #4ba282);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            margin: 5px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(88, 209, 249, 0.3);
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #28a745;
        }

        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔔 Notification System Test</h1>

        <div class="user-info">
            <strong>Logged in as:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?><br>
            <strong>User Type:</strong> <?php echo ucfirst($user_type); ?><br>
            <strong>User ID:</strong> <?php echo $user_id; ?>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_action'])) {
            $action = $_POST['test_action'];
            $created = false;

            switch ($action) {
                case 'order_accepted':
                    if ($user_type === 'customer') {
                        $created = $notificationService->notifyOrderStatus($user_id, 999, 'accepted', 'Test Tailor');
                    }
                    break;

                case 'order_stitching':
                    if ($user_type === 'customer') {
                        $created = $notificationService->notifyOrderStatus($user_id, 999, 'stitching', 'Test Tailor');
                    }
                    break;

                case 'order_completed':
                    if ($user_type === 'customer') {
                        $created = $notificationService->notifyOrderStatus($user_id, 999, 'completed', 'Test Tailor');
                    }
                    break;

                case 'new_order':
                    if ($user_type === 'tailor') {
                        $created = $notificationService->notifyNewOrder($user_id, 999, 'Test Customer', 'Test Shirt');
                    }
                    break;

                case 'verification':
                    if ($user_type === 'tailor') {
                        $created = $notificationService->notifyVerification($user_id, true);
                    }
                    break;

                case 'custom':
                    $created = $notificationService->createNotification(
                        $user_id,
                        $user_type,
                        'Test Notification',
                        'This is a test notification created at ' . date('h:i:s A'),
                        'test_type',
                        null
                    );
                    break;
            }

            if ($created) {
                echo "<div class='success'>
                        ✅ Test notification created successfully! Check the bell icon in the navigation bar.
                      </div>";
            } else {
                echo "<div class='warning'>
                        ⚠️ Failed to create notification or action not applicable for your user type.
                      </div>";
            }
        }
        ?>

        <div class="test-section">
            <h2>📋 Create Test Notifications</h2>
            <p>Click the buttons below to create test notifications. Then check the bell icon (🔔) in the navigation bar.</p>

            <?php if ($user_type === 'customer'): ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="test_action" value="order_accepted">
                    <button type="submit" class="btn">✅ Order Accepted</button>
                </form>

                <form method="POST" style="display: inline;">
                    <input type="hidden" name="test_action" value="order_stitching">
                    <button type="submit" class="btn">✂️ Order Stitching</button>
                </form>

                <form method="POST" style="display: inline;">
                    <input type="hidden" name="test_action" value="order_completed">
                    <button type="submit" class="btn">⭐ Order Completed</button>
                </form>
            <?php endif; ?>

            <?php if ($user_type === 'tailor'): ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="test_action" value="new_order">
                    <button type="submit" class="btn">🔔 New Order</button>
                </form>

                <form method="POST" style="display: inline;">
                    <input type="hidden" name="test_action" value="verification">
                    <button type="submit" class="btn">🛡️ Verification Approved</button>
                </form>
            <?php endif; ?>

            <form method="POST" style="display: inline;">
                <input type="hidden" name="test_action" value="custom">
                <button type="submit" class="btn">🎯 Custom Notification</button>
            </form>
        </div>

        <div class="test-section">
            <h2>📊 Your Current Notifications</h2>
            <?php
            // Get current notifications
            $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND user_type = ? ORDER BY created_at DESC LIMIT 10");
            $stmt->bind_param("is", $user_id, $user_type);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<table style='width: 100%; border-collapse: collapse;'>";
                echo "<tr style='background: #e9ecef;'>";
                echo "<th style='padding: 10px; text-align: left;'>Status</th>";
                echo "<th style='padding: 10px; text-align: left;'>Title</th>";
                echo "<th style='padding: 10px; text-align: left;'>Message</th>";
                echo "<th style='padding: 10px; text-align: left;'>Time</th>";
                echo "</tr>";

                while ($row = $result->fetch_assoc()) {
                    $status = $row['is_read'] ? '📖' : '🔵';
                    $statusText = $row['is_read'] ? 'Read' : 'Unread';
                    echo "<tr style='border-bottom: 1px solid #dee2e6;'>";
                    echo "<td style='padding: 10px;' title='{$statusText}'>{$status}</td>";
                    echo "<td style='padding: 10px;'>" . htmlspecialchars($row['title']) . "</td>";
                    echo "<td style='padding: 10px;'>" . htmlspecialchars($row['message']) . "</td>";
                    echo "<td style='padding: 10px; font-size: 0.85em; color: #6c757d;'>" . $row['created_at'] . "</td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p style='color: #6c757d;'>No notifications yet. Create some using the buttons above!</p>";
            }
            ?>
        </div>

        <div class="test-section">
            <h2>🔙 Back to Application</h2>
            <a href="../index.php" class="btn">← Go to Homepage</a>
            <a href="../<?php echo $user_type; ?>/dashboard.php" class="btn">📊 Go to Dashboard</a>
        </div>

        <div class="warning">
            <strong>⚠️ Note:</strong> This is a test page. The notifications created here use a dummy order ID (999).
            In production, notifications are created automatically when real orders are placed or updated.
        </div>
    </div>
</body>

</html>