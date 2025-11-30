<?php

/**
 * Check if customer has a default measurement for a specific garment type
 */

session_start();

header('Content-Type: application/json');

// Check if user is logged in and is a customer
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'customer') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Check if garment_type is provided
if (!isset($_GET['garment_type']) || empty($_GET['garment_type'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Garment type is required'
    ]);
    exit;
}

$customer_id = $_SESSION['user_id'];
$garment_type = $_GET['garment_type'];

// Include database connection
define('DB_ACCESS', true);
require_once '../../config/db.php';

try {
    // Check if customer has a default measurement for this garment type
    $stmt = $conn->prepare("
        SELECT id, label, measurements 
        FROM measurements 
        WHERE customer_id = ? 
        AND garment_context = ? 
        AND is_default = 1
        LIMIT 1
    ");

    $stmt->bind_param("is", $customer_id, $garment_type);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $measurement = $result->fetch_assoc();

        echo json_encode([
            'success' => true,
            'has_default' => true,
            'measurement' => [
                'id' => $measurement['id'],
                'label' => $measurement['label'],
                'measurements' => json_decode($measurement['measurements'], true)
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'has_default' => false,
            'message' => 'No default measurement found for ' . $garment_type
        ]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error checking measurements: ' . $e->getMessage()
    ]);
}

$conn->close();
