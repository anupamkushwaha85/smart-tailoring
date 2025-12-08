<?php

/**
 * Email Configuration
 * Setup for PHPMailer with Gmail SMTP
 * Updated for Cloud Deployment (Environment Variables)
 */

// Load environment variables if not already loaded
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';

    // Load .env if exists (local development)
    if (file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->safeLoad();
    }
}

// Helper to get env with fallback
function get_env_value($key, $default = null)
{
    $val = getenv($key);
    if ($val !== false) return $val;

    if (isset($_ENV[$key])) return $_ENV[$key];

    return $default;
}

// Email settings
define('SMTP_HOST', get_env_value('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', (int)get_env_value('SMTP_PORT', 587));
define('SMTP_SECURE', get_env_value('SMTP_SECURE', 'tls'));
define('SMTP_AUTH', filter_var(get_env_value('SMTP_AUTH', 'true'), FILTER_VALIDATE_BOOLEAN));

// Gmail credentials
define('SMTP_USERNAME', get_env_value('SMTP_USERNAME', ''));
define('SMTP_PASSWORD', get_env_value('SMTP_PASSWORD', ''));

// From email details
define('MAIL_FROM_EMAIL', get_env_value('MAIL_FROM_EMAIL', get_env_value('SMTP_USERNAME', '')));
define('MAIL_FROM_NAME', get_env_value('MAIL_FROM_NAME', 'Smart Tailoring Service'));

// Reply-to email
define('MAIL_REPLY_TO', get_env_value('MAIL_REPLY_TO', get_env_value('SMTP_USERNAME', '')));
define('MAIL_REPLY_TO_NAME', get_env_value('MAIL_REPLY_TO_NAME', 'Smart Tailoring Support'));

// OTP settings
define('OTP_EXPIRY_MINUTES', (int)get_env_value('OTP_EXPIRY_MINUTES', 10));
define('OTP_LENGTH', (int)get_env_value('OTP_LENGTH', 6));
define('MAX_OTP_ATTEMPTS', (int)get_env_value('MAX_OTP_ATTEMPTS', 3));

// Email templates directory
define('EMAIL_TEMPLATES_DIR', __DIR__ . '/email_templates/');

// Debug mode
define('SMTP_DEBUG', (int)get_env_value('SMTP_DEBUG', 0));
