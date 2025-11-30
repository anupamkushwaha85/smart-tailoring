<?php

/**
 * API: Update Order Status
 * POST /api/orders/update_status.php
 * Updates order status (with authorization)
 */

session_start();

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login.'
    ]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Include dependencies
define('DB_ACCESS', true);
require_once '../../config/db.php';
require_once '../../services/OrderService.php';
require_once '../../services/NotificationService.php';

try {
    // Get POST data
    $order_id = $_POST['order_id'] ?? null;
    $new_status = $_POST['status'] ?? null;
    $notes = $_POST['notes'] ?? null;

    if (!$order_id || !$new_status) {
        throw new Exception('Order ID and status are required');
    }

    // Create service instance
    $orderService = new OrderService($conn);
    $notificationService = new NotificationService($conn);

    // Update status with history recording
    $result = $orderService->updateOrderStatusWithHistory(
        $order_id,
        $new_status,
        $_SESSION['user_id'],
        $_SESSION['user_type'],
        $notes
    );

    // If status update was successful, send notification to customer
    if ($result['success']) {
        // Get order details for notification
        $order_query = $conn->prepare(
            "SELECT o.customer_id, t.shop_name 
             FROM orders o 
             LEFT JOIN tailors t ON o.tailor_id = t.id 
             WHERE o.id = ?"
        );
        $order_query->bind_param("i", $order_id);
        $order_query->execute();
        $order_result = $order_query->get_result();

        if ($order_data = $order_result->fetch_assoc()) {
            $notificationService->notifyOrderStatus(
                $order_data['customer_id'],
                $order_id,
                $new_status,
                $order_data['shop_name'] ?? 'Your tailor'
            );
        }
    }

    // Set HTTP status code
    http_response_code($result['success'] ? 200 : 400);

    // Return response
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

// Close database connection
db_close();
