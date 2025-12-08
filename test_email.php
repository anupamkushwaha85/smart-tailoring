<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Email Sending Test</h1>";

// Load configuration
require_once 'config/email.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<p>Configuration Loaded.</p>";
echo "<ul>";
echo "<li>Host: " . SMTP_HOST . "</li>";
echo "<li>Port: " . SMTP_PORT . "</li>";
echo "<li>Username: " . (SMTP_USERNAME ? 'Set' : 'Not Set') . "</li>";
echo "<li>Password: " . (SMTP_PASSWORD ? 'Set' : 'Not Set') . "</li>";
echo "</ul>";

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = 2;                      // Enable verbose debug output
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = SMTP_HOST;                     // Set the SMTP server to send through
    $mail->SMTPAuth   = SMTP_AUTH;                                   // Enable SMTP authentication
    $mail->Username   = SMTP_USERNAME;                     // SMTP username
    $mail->Password   = SMTP_PASSWORD;                               // SMTP password
    $mail->SMTPSecure = SMTP_SECURE;            // Enable implicit TLS encryption
    $mail->Port       = SMTP_PORT;                                    // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    // Recipients
    $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
    // Send to the sender for testing
    $mail->addAddress(SMTP_USERNAME);

    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Test Email from Smart Tailoring';
    $mail->Body    = 'This is a test email to verify SMTP configuration on Render.';
    $mail->AltBody = 'This is a test email to verify SMTP configuration on Render.';

    echo "<p>Attempting to send email...</p>";
    $mail->send();
    echo '<p style="color:green"><strong>Message has been sent successfully!</strong></p>';
} catch (Exception $e) {
    echo '<p style="color:red"><strong>Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . '</strong></p>';
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
