<?php

/**
 * Mark All Notifications as Read API
 */

session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

require_once '../../config/db.php';

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

try {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE 
                           WHERE user_id = ? AND user_type = ? AND is_read = FALSE");
    $stmt->bind_param("is", $user_id, $user_type);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'All notifications marked as read'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update notifications'
    ]);
}
