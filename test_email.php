<?php
require_once __DIR__ . '/config/email.php';
require_once __DIR__ . '/vendor/phpmailer/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_ENCRYPTION;
    $mail->Port       = SMTP_PORT;

    $mail->setFrom(FROM_EMAIL, FROM_NAME);
    $mail->addAddress('your-email@gmail.com', 'Test');
    $mail->Subject = 'Test Email';
    $mail->Body    = 'This is a test email';

    if ($mail->send()) {
        echo "✅ Email sent successfully!";
    }
} catch (Exception $e) {
    echo "❌ Error: {$mail->ErrorInfo}";
}
?>