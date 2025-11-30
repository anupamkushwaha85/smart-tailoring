<?php

/**
 * API: Cancel Order
 * POST /api/orders/cancel_order.php
 * Cancels an order
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

    if (!$order_id) {
        throw new Exception('Order ID is required');
    }

    // Create service instance
    $orderService = new OrderService($conn);
    $notificationService = new NotificationService($conn);

    // Get order details before cancellation for notification
    $order_query = $conn->prepare(
        "SELECT customer_id, tailor_id, c.full_name as customer_name 
         FROM orders o
         LEFT JOIN customers c ON o.customer_id = c.id
         WHERE o.id = ?"
    );
    $order_query->bind_param("i", $order_id);
    $order_query->execute();
    $order_result = $order_query->get_result();
    $order_data = $order_result->fetch_assoc();

    // Cancel order
    $result = $orderService->cancelOrder(
        $order_id,
        $_SESSION['user_id'],
        $_SESSION['user_type']
    );

    // If cancellation was successful, send notifications
    if ($result['success'] && $order_data) {
        // Notify customer if tailor cancelled
        if ($_SESSION['user_type'] === 'tailor') {
            $notificationService->notifyOrderStatus(
                $order_data['customer_id'],
                $order_id,
                'cancelled',
                ''
            );
        }
        // Notify tailor if customer cancelled
        else if ($_SESSION['user_type'] === 'customer') {
            $notificationService->notifyOrderCancelled(
                $order_data['tailor_id'],
                $order_id,
                $order_data['customer_name'] ?? 'A customer'
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
