<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Email Connectivity & Sending Test</h1>";

// Load configuration
require_once 'config/email.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$host = SMTP_HOST;
$ports = [587, 465];

// 1. DNS Resolution Test
echo "<h2>1. DNS Resolution</h2>";
$ip = gethostbyname($host);
echo "Resolved <strong>$host</strong> to IPv4: <strong>$ip</strong><br>";

// 2. Port Connectivity Test
echo "<h2>2. Port Connectivity</h2>";
foreach ($ports as $port) {
    echo "Testing connection to <strong>$host:$port</strong>... ";
    $fp = @fsockopen($host, $port, $errno, $errstr, 5);
    if ($fp) {
        echo "<span style='color:green'>SUCCESS</span><br>";
        fclose($fp);
    } else {
        echo "<span style='color:red'>FAILED ($errno: $errstr)</span><br>";
    }
}

// 3. Email Sending Test
echo "<h2>3. Sending Test</h2>";

echo "<ul>";
echo "<li>Host: " . SMTP_HOST . "</li>";
echo "<li>Port: " . SMTP_PORT . "</li>";
echo "<li>Username: " . (SMTP_USERNAME ? 'Set' : 'Not Set') . "</li>";
echo "</ul>";

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = 2;
    $mail->isSMTP();

    // FORCE IPv4 workaround if standard host fails
    // $mail->Host = gethostbyname(SMTP_HOST); 
    $mail->Host       = SMTP_HOST;

    $mail->SMTPAuth   = SMTP_AUTH;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;

    // Timeout settings
    $mail->Timeout = 10;
    $mail->Timelimit = 10;

    // Recipients
    $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
    $mail->addAddress(SMTP_USERNAME);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Smart Tailoring';
    $mail->Body    = 'This is a test email to verify SMTP configuration on Render.';

    echo "<p>Attempting to send email...</p>";
    $mail->send();
    echo '<p style="color:green"><strong>Message has been sent successfully!</strong></p>';
} catch (Exception $e) {
    echo '<p style="color:red"><strong>Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . '</strong></p>';
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
