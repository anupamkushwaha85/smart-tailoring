<?php

/**
 * Cloud-Ready Database Configuration with SSL Support
 * Optimized for: Render (App) + Aiven (MySQL)
 * 
 * Features:
 * - SSL/TLS connection for Aiven MySQL
 * - Environment variable configuration
 * - Connection timeout handling
 * - Fallback for local development
 * - Error logging
 */

// Prevent direct access
if (!defined('DB_ACCESS')) {
    define('DB_ACCESS', true);
}

// Load environment variables from .env file (development) or system environment (production)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    if (file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
    }
}

// Database Configuration from Environment Variables
$db_host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost');
$db_user = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root');
$db_pass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? '');
$db_name = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'smart_tailoring');
$db_port = (int)(getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? 3306));
$use_ssl = filter_var(getenv('DB_USE_SSL') ?: ($_ENV['DB_USE_SSL'] ?? 'false'), FILTER_VALIDATE_BOOLEAN);

// SSL Certificate Path (for Aiven MySQL)
$ca_cert_path = __DIR__ . '/../ca.pem';

/**
 * Establish Database Connection with SSL Support
 */
function getCloudDatabaseConnection()
{
    global $db_host, $db_user, $db_pass, $db_name, $db_port, $use_ssl, $ca_cert_path;
    
    // Initialize mysqli
    $conn = mysqli_init();
    
    if (!$conn) {
        error_log("Cloud DB: mysqli_init failed");
        return false;
    }
    
    // Set connection timeout (important for cloud deployments)
    mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 10);
    
    // Set read timeout
    mysqli_options($conn, MYSQLI_OPT_READ_TIMEOUT, 30);
    
    // Set write timeout
    mysqli_options($conn, MYSQLI_OPT_WRITE_TIMEOUT, 30);
    
    try {
        if ($use_ssl && file_exists($ca_cert_path)) {
            // SSL Connection for Aiven MySQL
            mysqli_ssl_set(
                $conn,
                NULL,           // key
                NULL,           // cert
                $ca_cert_path,  // ca
                NULL,           // capath
                NULL            // cipher
            );
            
            // Connect with SSL
            $connected = mysqli_real_connect(
                $conn,
                $db_host,
                $db_user,
                $db_pass,
                $db_name,
                $db_port,
                NULL,
                MYSQLI_CLIENT_SSL
            );
            
            if (!$connected) {
                throw new Exception("SSL connection failed: " . mysqli_connect_error());
            }
            
            error_log("Cloud DB: Connected with SSL to {$db_host}:{$db_port}");
        } else {
            // Non-SSL connection (local development)
            $connected = mysqli_real_connect(
                $conn,
                $db_host,
                $db_user,
                $db_pass,
                $db_name,
                $db_port
            );
            
            if (!$connected) {
                throw new Exception("Connection failed: " . mysqli_connect_error());
            }
            
            error_log("Cloud DB: Connected without SSL to {$db_host}:{$db_port}");
        }
        
        // Set character set to UTF-8
        mysqli_set_charset($conn, "utf8mb4");
        
        // Set timezone
        mysqli_query($conn, "SET time_zone = '+00:00'");
        
        return $conn;
        
    } catch (Exception $e) {
        error_log("Cloud DB Connection Error: " . $e->getMessage());
        return false;
    }
}

// Establish connection
$conn = getCloudDatabaseConnection();

if (!$conn) {
    // Critical error - cannot proceed without database
    if (getenv('APP_ENV') === 'production') {
        die("Database service unavailable. Please try again later.");
    } else {
        die("Database Connection Failed. Check your .env configuration.");
    }
}

/**
 * Helper function for safe query execution with prepared statements
 * 
 * @param string $sql SQL query with placeholders
 * @param string $types Parameter types (e.g., "ssi" for string, string, int)
 * @param array $params Parameters to bind
 * @return mysqli_stmt|bool
 */
function db_query($sql, $types = "", $params = [])
{
    global $conn;

    if (empty($types)) {
        return mysqli_query($conn, $sql);
    }

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Database prepare error: " . mysqli_error($conn));
        return false;
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        error_log("Database execute error: " . mysqli_stmt_error($stmt));
    }

    return $result ? $stmt : false;
}

/**
 * Fetch a single row from database
 * 
 * @param string $query SQL query
 * @return array|false|null
 */
function db_fetch_one($query)
{
    global $conn;

    $result = mysqli_query($conn, $query);
    if (!$result) {
        error_log("Database query error: " . mysqli_error($conn));
        return false;
    }

    return mysqli_fetch_assoc($result);
}

/**
 * Fetch all rows from database
 * 
 * @param string $query SQL query
 * @return array
 */
function db_fetch_all($query)
{
    global $conn;

    $result = mysqli_query($conn, $query);
    if (!$result) {
        error_log("Database query error: " . mysqli_error($conn));
        return [];
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}

/**
 * Close database connection
 */
function db_close()
{
    global $conn;

    if (isset($conn) && $conn instanceof mysqli) {
        mysqli_close($conn);
    }
}

/**
 * Test database connection health
 * 
 * @return bool
 */
function db_health_check()
{
    global $conn;
    
    if (!$conn) {
        return false;
    }
    
    return mysqli_ping($conn);
}

// Set error handling based on environment
$app_env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'development');

if ($app_env === 'production') {
    // Production: Log errors, don't display
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    // Development: Show errors
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Register shutdown function to close connection
register_shutdown_function('db_close');
