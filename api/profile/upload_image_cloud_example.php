<?php

/**
 * EXAMPLE: Profile Image Upload API - Cloud Ready Version
 * Location: api/profile/upload_image_cloud.php
 * 
 * This is an UPDATED version showing how to use Cloudinary instead of local storage
 * Replace your existing upload_image.php with this cloud-ready version
 */

session_start();
header('Content-Type: application/json');

// Security check
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Include Cloudinary helper (NEW)
require_once __DIR__ . '/../../utils/cloudinary_helper.php';
require_once __DIR__ . '/../../config/db_cloud.php';

/**
 * OLD CODE (Local Storage - DON'T USE ON RENDER):
 * 
 * $imageUpload = new ImageUpload(__DIR__ . '/../../uploads/profiles/');
 * $result = $imageUpload->upload($_FILES['profile_image'], 'customer_');
 * 
 * if ($result['success']) {
 *     $filename = $result['filename'];
 *     // Save filename to database
 * }
 */

/**
 * NEW CODE (Cloudinary - Cloud Ready):
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'] ?? 'customer';
    
    // Validate file upload
    if (!isset($_FILES['profile_image'])) {
        echo json_encode([
            'success' => false,
            'message' => 'No file uploaded'
        ]);
        exit;
    }
    
    // Upload to Cloudinary (replaces move_uploaded_file)
    $folder = $user_type === 'customer' ? 'profiles/customers' : 'profiles/tailors';
    $result = handleImageUpload($_FILES['profile_image'], $folder);
    
    if (!$result['success']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $result['error']
        ]);
        exit;
    }
    
    // Get the Cloudinary URL (instead of local filename)
    $image_url = $result['url'];
    $public_id = $result['public_id'] ?? null;
    
    // Update database with Cloudinary URL
    if ($user_type === 'customer') {
        $sql = "UPDATE customers SET profile_image = ? WHERE customer_id = ?";
    } else {
        $sql = "UPDATE tailors SET profile_image = ? WHERE tailor_id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $image_url, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['profile_image'] = $image_url;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile image updated successfully',
            'image_url' => $image_url
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update database'
        ]);
    }
    
    $stmt->close();
    
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
