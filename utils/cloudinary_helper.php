<?php

/**
 * Cloudinary Image Upload Helper
 * For Stateless Cloud Deployment (Render)
 * 
 * This helper uploads images to Cloudinary instead of local filesystem
 * Required for ephemeral file systems that reset on deployment
 * 
 * Features:
 * - Unsigned upload using preset
 * - Direct PHP cURL (no SDK required)
 * - Returns secure HTTPS URL
 * - Error handling and logging
 */

/**
 * Upload image to Cloudinary using unsigned preset
 * 
 * @param string $filePath Path to the uploaded file ($_FILES['file']['tmp_name'])
 * @param string $folder Optional folder name in Cloudinary (e.g., 'profiles', 'shops')
 * @return array ['success' => bool, 'url' => string|null, 'error' => string|null]
 */
function uploadToCloudinary($filePath, $folder = '')
{
    // Get Cloudinary credentials from environment
    $cloudName = getenv('CLOUDINARY_CLOUD_NAME') ?: ($_ENV['CLOUDINARY_CLOUD_NAME'] ?? null);
    $uploadPreset = getenv('CLOUDINARY_UPLOAD_PRESET') ?: ($_ENV['CLOUDINARY_UPLOAD_PRESET'] ?? null);
    
    // Validate environment variables
    if (empty($cloudName) || empty($uploadPreset)) {
        error_log("Cloudinary: Missing CLOUDINARY_CLOUD_NAME or CLOUDINARY_UPLOAD_PRESET");
        return [
            'success' => false,
            'url' => null,
            'error' => 'Cloudinary configuration missing'
        ];
    }
    
    // Validate file exists
    if (!file_exists($filePath)) {
        error_log("Cloudinary: File not found at {$filePath}");
        return [
            'success' => false,
            'url' => null,
            'error' => 'File not found'
        ];
    }
    
    // Cloudinary upload endpoint
    $uploadUrl = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";
    
    // Prepare file for upload
    $fileData = new CURLFile($filePath, mime_content_type($filePath), basename($filePath));
    
    // Build POST data
    $postData = [
        'file' => $fileData,
        'upload_preset' => $uploadPreset
    ];
    
    // Add folder if specified
    if (!empty($folder)) {
        $postData['folder'] = $folder;
    }
    
    // Initialize cURL
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $uploadUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    // Check for cURL errors
    if ($response === false) {
        error_log("Cloudinary cURL error: {$curlError}");
        return [
            'success' => false,
            'url' => null,
            'error' => "Upload failed: {$curlError}"
        ];
    }
    
    // Parse JSON response
    $result = json_decode($response, true);
    
    // Check HTTP status
    if ($httpCode !== 200) {
        $errorMsg = $result['error']['message'] ?? 'Unknown error';
        error_log("Cloudinary upload failed (HTTP {$httpCode}): {$errorMsg}");
        return [
            'success' => false,
            'url' => null,
            'error' => $errorMsg
        ];
    }
    
    // Success! Return secure URL
    $secureUrl = $result['secure_url'] ?? null;
    
    if (empty($secureUrl)) {
        error_log("Cloudinary: No secure_url in response");
        return [
            'success' => false,
            'url' => null,
            'error' => 'Invalid response from Cloudinary'
        ];
    }
    
    error_log("Cloudinary: Successfully uploaded to {$secureUrl}");
    
    return [
        'success' => true,
        'url' => $secureUrl,
        'error' => null,
        'public_id' => $result['public_id'] ?? null,
        'width' => $result['width'] ?? null,
        'height' => $result['height'] ?? null,
        'format' => $result['format'] ?? null
    ];
}

/**
 * Delete image from Cloudinary (requires API key - signed request)
 * For unsigned presets, deletion is not supported without upgrading
 * 
 * @param string $publicId The public_id returned from upload
 * @return array ['success' => bool, 'error' => string|null]
 */
function deleteFromCloudinary($publicId)
{
    $cloudName = getenv('CLOUDINARY_CLOUD_NAME') ?: ($_ENV['CLOUDINARY_CLOUD_NAME'] ?? null);
    $apiKey = getenv('CLOUDINARY_API_KEY') ?: ($_ENV['CLOUDINARY_API_KEY'] ?? null);
    $apiSecret = getenv('CLOUDINARY_API_SECRET') ?: ($_ENV['CLOUDINARY_API_SECRET'] ?? null);
    
    // Check if signed API credentials are available
    if (empty($apiKey) || empty($apiSecret)) {
        error_log("Cloudinary: Cannot delete without API_KEY and API_SECRET");
        return [
            'success' => false,
            'error' => 'Deletion requires API credentials'
        ];
    }
    
    $timestamp = time();
    $signature = sha1("public_id={$publicId}&timestamp={$timestamp}{$apiSecret}");
    
    $deleteUrl = "https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy";
    
    $postData = [
        'public_id' => $publicId,
        'timestamp' => $timestamp,
        'api_key' => $apiKey,
        'signature' => $signature
    ];
    
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $deleteUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && ($result['result'] ?? '') === 'ok') {
        return ['success' => true, 'error' => null];
    }
    
    return [
        'success' => false,
        'error' => $result['error']['message'] ?? 'Deletion failed'
    ];
}

/**
 * Helper function to handle uploaded file and upload to Cloudinary
 * Use this in your controllers
 * 
 * @param array $file The $_FILES['fieldname'] array
 * @param string $folder Cloudinary folder
 * @param array $allowedTypes Allowed MIME types
 * @param int $maxSize Max file size in bytes (default 5MB)
 * @return array ['success' => bool, 'url' => string|null, 'error' => string|null]
 */
function handleImageUpload($file, $folder = '', $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'], $maxSize = 5242880)
{
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return [
            'success' => false,
            'url' => null,
            'error' => 'No file uploaded'
        ];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        return [
            'success' => false,
            'url' => null,
            'error' => $errorMessages[$file['error']] ?? 'Upload failed'
        ];
    }
    
    // Validate file type
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        return [
            'success' => false,
            'url' => null,
            'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes)
        ];
    }
    
    // Validate file size
    if ($file['size'] > $maxSize) {
        $maxSizeMB = round($maxSize / 1048576, 2);
        return [
            'success' => false,
            'url' => null,
            'error' => "File too large. Max size: {$maxSizeMB}MB"
        ];
    }
    
    // Upload to Cloudinary
    return uploadToCloudinary($file['tmp_name'], $folder);
}
