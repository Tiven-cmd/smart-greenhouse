<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

$config = require __DIR__ . '/email_config.php';

$mail = new PHPMailer(true);

try {

    // Gmail SMTP
    $mail->isSMTP();
    $mail->Host       = $config['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_username'];
    $mail->Password   = $config['smtp_password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $config['smtp_port'];

    // Sender
    $mail->setFrom(
        $config['from_email'],
        $config['from_name']
    );

    // Send test email to yourself
    $mail->addAddress(
        'smartgreenhouse07@gmail.com'
    );

    $mail->isHTML(true);

    $mail->Subject = 'Smart Greenhouse Email Test';

    $mail->Body = '
        <h2>🌱 Smart Greenhouse</h2>
        <p>This is a test email.</p>
        <p>Gmail SMTP is working correctly.</p>
    ';

    $mail->send();

    echo "<h2 style='color:green;'>✅ Email sent successfully!</h2>";
    echo "<p>Check your Gmail inbox.</p>";

} catch (Exception $e) {

    echo "<h2 style='color:red;'>❌ Email could not be sent.</h2>";
    echo "<p><strong>Error:</strong></p>";
    echo "<pre>" . htmlspecialchars($mail->ErrorInfo) . "</pre>";
}
?>