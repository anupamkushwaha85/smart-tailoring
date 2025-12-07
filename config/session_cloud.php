<?php

/**
 * Cloud-Ready Session Configuration
 * Optimized for: Stateless Deployments (Render)
 * 
 * OPTIONS:
 * 1. File-based sessions (default) - Simple but clears on restart
 * 2. Database sessions (recommended) - Persistent across deployments
 * 3. Redis sessions (best) - Fast and scalable
 * 
 * For portfolio/small projects: File-based is acceptable
 * For production: Use database or Redis sessions
 */

// Prevent direct access
if (!defined('SESSION_ACCESS')) {
    define('SESSION_ACCESS', true);
}

/**
 * OPTION 1: File-Based Sessions (Default - Simple)
 * 
 * Pros: No setup required, works immediately
 * Cons: Sessions clear on Render restart/redeploy
 * Use Case: Development, portfolio projects, demos
 */
function configureFileBasedSessions()
{
    // Session configuration
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_secure', getenv('APP_ENV') === 'production' ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', '7200'); // 2 hours
    ini_set('session.use_strict_mode', '1');
    
    // Note: /tmp is writable on Render but clears on restart
    session_save_path('/tmp/sessions');
    
    if (!file_exists('/tmp/sessions')) {
        @mkdir('/tmp/sessions', 0700, true);
    }
}

/**
 * OPTION 2: Database Sessions (Recommended for Production)
 * 
 * Pros: Persistent across restarts, no external service needed
 * Cons: Slight performance overhead
 * Use Case: Production apps without Redis
 */
class DatabaseSessionHandler implements SessionHandlerInterface
{
    private $conn;
    private $table = 'user_sessions';
    
    public function __construct($connection)
    {
        $this->conn = $connection;
        $this->createTableIfNotExists();
    }
    
    private function createTableIfNotExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            session_id VARCHAR(128) PRIMARY KEY,
            session_data TEXT,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            user_agent VARCHAR(255),
            ip_address VARCHAR(45),
            INDEX idx_last_activity (last_activity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        mysqli_query($this->conn, $sql);
    }
    
    public function open($save_path, $session_name): bool
    {
        return true;
    }
    
    public function close(): bool
    {
        return true;
    }
    
    public function read($session_id): string|false
    {
        $stmt = mysqli_prepare($this->conn, 
            "SELECT session_data FROM {$this->table} WHERE session_id = ? AND last_activity > DATE_SUB(NOW(), INTERVAL 2 HOUR)");
        mysqli_stmt_bind_param($stmt, 's', $session_id);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        return $row ? $row['session_data'] : '';
    }
    
    public function write($session_id, $session_data): bool
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $stmt = mysqli_prepare($this->conn,
            "INSERT INTO {$this->table} (session_id, session_data, user_agent, ip_address) 
             VALUES (?, ?, ?, ?) 
             ON DUPLICATE KEY UPDATE session_data = ?, last_activity = CURRENT_TIMESTAMP");
        
        mysqli_stmt_bind_param($stmt, 'sssss', $session_id, $session_data, $user_agent, $ip_address, $session_data);
        
        return mysqli_stmt_execute($stmt);
    }
    
    public function destroy($session_id): bool
    {
        $stmt = mysqli_prepare($this->conn, "DELETE FROM {$this->table} WHERE session_id = ?");
        mysqli_stmt_bind_param($stmt, 's', $session_id);
        
        return mysqli_stmt_execute($stmt);
    }
    
    public function gc($maxlifetime): int|false
    {
        $stmt = mysqli_prepare($this->conn,
            "DELETE FROM {$this->table} WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)");
        mysqli_stmt_bind_param($stmt, 'i', $maxlifetime);
        mysqli_stmt_execute($stmt);
        
        return mysqli_stmt_affected_rows($this->conn);
    }
}

/**
 * Initialize Session Based on Environment
 */
function initializeCloudSession()
{
    $session_type = getenv('SESSION_STORAGE') ?: ($_ENV['SESSION_STORAGE'] ?? 'file');
    
    if ($session_type === 'database') {
        // Use database sessions
        global $conn;
        
        if (!isset($conn)) {
            require_once __DIR__ . '/db_cloud.php';
        }
        
        $handler = new DatabaseSessionHandler($conn);
        session_set_save_handler($handler, true);
        
        error_log("Cloud Session: Using database storage");
        
    } else {
        // Use file-based sessions (default)
        configureFileBasedSessions();
        
        error_log("Cloud Session: Using file storage (/tmp/sessions)");
    }
    
    // Common security settings
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_secure', getenv('APP_ENV') === 'production' ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', '7200');
    ini_set('session.use_strict_mode', '1');
    
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Session hijacking prevention
    if (!isset($_SESSION['user_agent'])) {
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    } else {
        if ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            session_unset();
            session_destroy();
            session_start();
        }
    }
    
    // Session timeout check
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
        session_unset();
        session_destroy();
        session_start();
    }
    
    $_SESSION['last_activity'] = time();
}

// Auto-initialize session when this file is included
initializeCloudSession();
