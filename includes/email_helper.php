<?php
/**
 * Email Helper Functions
 *
 * This file contains functions to send emails using PHPMailer
 */

require_once __DIR__ . '/../config/email.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send Order Confirmation Email
 *
 * @param array $orderData Order details
 * @return bool True if email sent successfully, false otherwise
 */
function sendOrderConfirmationEmail($orderData) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($orderData['customer_email'], $orderData['customer_name']);
        $mail->addReplyTo(REPLY_TO_EMAIL, FROM_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Order Confirmation #' . $orderData['order_id'] . ' - Storyscape';
        $mail->Body    = generateOrderEmailHTML($orderData);
        $mail->AltBody = generateOrderEmailPlainText($orderData);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Generate HTML email template for order confirmation
 */
function generateOrderEmailHTML($data) {
    $orderNumber = $data['order_id'];
    $customerName = htmlspecialchars($data['customer_name']);
    $itemsHTML = '';

    foreach ($data['items'] as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $itemsHTML .= "
        <tr>
            <td style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>" . htmlspecialchars($item['title']) . "</td>
            <td style='padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: center;'>" . $item['quantity'] . "</td>
            <td style='padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: right;'>$" . number_format($itemTotal, 2) . "</td>
        </tr>";
    }

    $shippingAddress = htmlspecialchars($data['shipping_name']) . '<br>' .
                      htmlspecialchars($data['shipping_address']) . '<br>' .
                      htmlspecialchars($data['shipping_city']) . ', ' .
                      htmlspecialchars($data['shipping_zip']) . '<br>' .
                      htmlspecialchars($data['shipping_country']);

    $paymentMethod = ucwords(str_replace('_', ' ', htmlspecialchars($data['payment_method'])));

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #6B4423 0%, #8B5A3C 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #ffffff; padding: 30px; border: 1px solid #e2e8f0; }
            .footer { background: #f8fafc; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; font-size: 14px; color: #64748b; }
            .order-details { margin: 20px 0; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th { background: #f8fafc; padding: 12px; text-align: left; font-weight: 600; color: #475569; }
            .total-row { font-weight: bold; font-size: 18px; background: #f8fafc; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0; font-family: Playfair Display, serif;'>📚 Storyscape</h1>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Your Literary Journey Begins</p>
            </div>

            <div class='content'>
                <h2 style='color: #16a34a; margin-top: 0;'>✓ Order Confirmed!</h2>
                <p>Dear {$customerName},</p>
                <p>Thank you for your order! We're excited to get your books to you.</p>

                <div style='background: #eff6ff; padding: 15px; border-left: 4px solid #3b82f6; margin: 20px 0;'>
                    <strong>Order Number:</strong> #{$orderNumber}<br>
                    <strong>Order Date:</strong> " . date('F d, Y') . "
                </div>

                <h3 style='color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;'>Order Details</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th style='text-align: center; width: 80px;'>Qty</th>
                            <th style='text-align: right; width: 100px;'>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemsHTML}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan='2' style='padding: 12px; text-align: right;'><strong>Subtotal:</strong></td>
                            <td style='padding: 12px; text-align: right;'>$" . number_format($data['subtotal'], 2) . "</td>
                        </tr>
                        <tr>
                            <td colspan='2' style='padding: 12px; text-align: right;'><strong>Tax:</strong></td>
                            <td style='padding: 12px; text-align: right;'>$" . number_format($data['tax'], 2) . "</td>
                        </tr>
                        <tr class='total-row'>
                            <td colspan='2' style='padding: 12px; text-align: right;'><strong>Total:</strong></td>
                            <td style='padding: 12px; text-align: right;'>$" . number_format($data['total'], 2) . "</td>
                        </tr>
                    </tfoot>
                </table>

                <h3 style='color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;'>Shipping Address</h3>
                <p style='background: #f8fafc; padding: 15px; border-radius: 6px;'>{$shippingAddress}</p>

                <h3 style='color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;'>Payment Method</h3>
                <p style='background: #f8fafc; padding: 15px; border-radius: 6px;'>{$paymentMethod}</p>

                <div style='margin-top: 30px; padding: 20px; background: #fef3c7; border-left: 4px solid #eab308; border-radius: 4px;'>
                    <strong>📦 What's Next?</strong><br>
                    We're preparing your order for shipment. You'll receive another email with tracking information once your order ships!
                </div>
            </div>

            <div class='footer'>
                <p>Thank you for shopping with Storyscape!</p>
                <p style='margin: 10px 0;'>
                    Questions? Reply to this email or contact us at support@storyscape.com
                </p>
                <p style='font-size: 12px; color: #94a3b8;'>
                    &copy; " . date('Y') . " Storyscape. All rights reserved.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Generate plain text version for email clients that don't support HTML
 */
function generateOrderEmailPlainText($data) {
    $text = "ORDER CONFIRMATION\n";
    $text .= "===================\n\n";
    $text .= "Dear " . $data['customer_name'] . ",\n\n";
    $text .= "Thank you for your order from Storyscape!\n\n";
    $text .= "Order Number: #" . $data['order_id'] . "\n";
    $text .= "Order Date: " . date('F d, Y') . "\n\n";
    $text .= "ORDER DETAILS:\n";
    $text .= "-----------------\n";

    foreach ($data['items'] as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $text .= $item['title'] . " x" . $item['quantity'] . " - $" . number_format($itemTotal, 2) . "\n";
    }

    $text .= "\nSubtotal: $" . number_format($data['subtotal'], 2) . "\n";
    $text .= "Tax: $" . number_format($data['tax'], 2) . "\n";
    $text .= "TOTAL: $" . number_format($data['total'], 2) . "\n\n";
    $text .= "SHIPPING ADDRESS:\n";
    $text .= "-----------------\n";
    $text .= $data['shipping_name'] . "\n";
    $text .= $data['shipping_address'] . "\n";
    $text .= $data['shipping_city'] . ", " . $data['shipping_zip'] . "\n";
    $text .= $data['shipping_country'] . "\n\n";
    $text .= "Payment Method: " . ucwords(str_replace('_', ' ', $data['payment_method'])) . "\n\n";
    $text .= "We're preparing your order for shipment. You'll receive tracking information soon!\n\n";
    $text .= "Thank you for shopping with Storyscape!\n";
    $text .= "Questions? Email us at support@storyscape.com\n";

    return $text;
}
