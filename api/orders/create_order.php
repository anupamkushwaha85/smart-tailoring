<?php

/**
 * API: Create Order
 * POST /api/orders/create_order.php
 * Creates a new order
 */

session_start();

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'customer') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login as customer.'
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
    // Get POST data (supporting both old and new fields)
    $orderData = [
        'customer_id' => $_SESSION['user_id'],
        'tailor_id' => $_POST['tailor_id'] ?? null,
        'service_type' => $_POST['service_type'] ?? null,
        'garment_type' => $_POST['garment_type'] ?? null,
        'quantity' => $_POST['quantity'] ?? 1,
        'measurements' => $_POST['measurements'] ?? '',
        'special_instructions' => $_POST['special_instructions'] ?? '',
        'estimated_price' => $_POST['estimated_price'] ?? 0,
        'delivery_date' => $_POST['delivery_date'] ?? null
    ];

    // Add new enhanced workflow fields if provided
    if (isset($_POST['fabric_type'])) {
        $orderData['fabric_type'] = $_POST['fabric_type'];
    }

    if (isset($_POST['fabric_color'])) {
        $orderData['fabric_color'] = $_POST['fabric_color'];
    }

    if (isset($_POST['measurement_id'])) {
        $orderData['measurement_id'] = $_POST['measurement_id'];
    }

    if (isset($_POST['deadline'])) {
        $orderData['deadline'] = $_POST['deadline'];
    }

    if (isset($_POST['deposit_amount'])) {
        $orderData['deposit_amount'] = $_POST['deposit_amount'];
    }

    if (isset($_POST['source_order_id'])) {
        $orderData['source_order_id'] = $_POST['source_order_id'];
    }

    // Handle design_specifications (can be JSON string or array)
    if (isset($_POST['design_specifications'])) {
        $orderData['design_specifications'] = $_POST['design_specifications'];
    }

    // Handle measurements_snapshot (can be JSON string or array)
    if (isset($_POST['measurements_snapshot'])) {
        $orderData['measurements_snapshot'] = $_POST['measurements_snapshot'];
    }

    // Create service instance
    $orderService = new OrderService($conn);
    $notificationService = new NotificationService($conn);

    // Decide which method to use based on whether enhanced fields are present
    $hasEnhancedFields = isset($_POST['fabric_type']) || isset($_POST['measurement_id']) ||
        isset($_POST['deadline']) || isset($_POST['design_specifications']);

    if ($hasEnhancedFields) {
        // Use new enhanced method
        $result = $orderService->createOrderWithMeasurements($orderData);
    } else {
        // Use legacy method for backward compatibility
        $result = $orderService->createOrder($orderData);
    }

    // If order was created successfully, send notification to tailor
    if ($result['success'] && isset($result['order_id'])) {
        // Get customer and tailor details for notification
        $customer_query = $conn->prepare("SELECT full_name FROM customers WHERE id = ?");
        $customer_query->bind_param("i", $_SESSION['user_id']);
        $customer_query->execute();
        $customer_result = $customer_query->get_result();
        $customer_data = $customer_result->fetch_assoc();

        $notificationService->notifyNewOrder(
            $orderData['tailor_id'],
            $result['order_id'],
            $customer_data['full_name'] ?? 'A customer',
            $orderData['garment_type'] ?? 'custom garment'
        );
    }

    // Set HTTP status code
    http_response_code($result['success'] ? 201 : 400);

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
