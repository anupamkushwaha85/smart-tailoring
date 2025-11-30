<?php

/**
 * Mark Notification as Read API
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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$notification_id = isset($input['notification_id']) ? intval($input['notification_id']) : 0;

if ($notification_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid notification ID'
    ]);
    exit;
}

try {
    // Mark as read only if it belongs to the current user
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE 
                           WHERE id = ? AND user_id = ? AND user_type = ?");
    $stmt->bind_param("iis", $notification_id, $user_id, $user_type);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Notification marked as read'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update notification'
    ]);
}
