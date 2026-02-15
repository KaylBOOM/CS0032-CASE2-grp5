<?php

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');          // Gmail SMTP server
define('SMTP_PORT', 587);                        // Port for TLS (use 465 for SSL)
define('SMTP_USERNAME', 'storyscapeOBS@gmail.com'); // CHANGE THIS: Your Gmail address
define('SMTP_PASSWORD', 'kjrvqdfigmhuivya');  // CHANGE THIS: Your 16-character App Password
define('SMTP_ENCRYPTION', 'tls');                // tls or ssl

// Email Settings
define('FROM_EMAIL', 'noreply@storyscape.com');  // Sender email (can be different)
define('FROM_NAME', 'Storyscape Bookstore');     // Sender name
define('REPLY_TO_EMAIL', 'support@storyscape.com'); // Reply-to email

/**
 * TROUBLESHOOTING:
 * ================
 * - If emails don't send, check XAMPP Apache error log
 * - Make sure 2-Factor Authentication is enabled on Gmail
 * - Make sure you're using the App Password, NOT your Gmail password
 * - Check your Gmail "Less secure apps" settings (modern Gmail uses App Passwords)
 * - Try port 465 with 'ssl' if port 587 doesn't work
 */
