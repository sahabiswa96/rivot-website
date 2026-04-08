<?php
// SMTP Configuration
$smtp_host = "smtp.gmail.com";
$smtp_port = 587;
$smtp_user = "change@rivotmotors.com";
$smtp_pass = "RIVOT@M0tors";

// Email details
$to = "parthait2003@gmail.com";
$subject = "SMTP Test Email from Rivot Motors";
$message = "This is a test email to verify SMTP is working correctly.\n\n";
$message .= "SMTP Server: $smtp_host:$smtp_port\n";
$message .= "From: $smtp_user\n";
$message .= "Date: " . date('Y-m-d H:i:s') . "\n";

// Using PHPMailer or manual SMTP
// First, let's try with a simple SMTP connection
echo "=== SMTP Test ===\n";
echo "Host: $smtp_host\n";
echo "Port: $smtp_port\n";
echo "User: $smtp_user\n";
echo "To: $to\n";
echo "---\n\n";

// Test connection
echo "Testing SMTP connection...\n";
$socket = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);
if (!$socket) {
    echo "ERROR: Cannot connect to SMTP server\n";
    echo "Error ($errno): $errstr\n";
    exit(1);
}
echo "SUCCESS: Connected to SMTP server\n\n";

// Read greeting
$response = fgets($socket, 515);
echo "Server: " . $response;

// Send EHLO
fwrite($socket, "EHLO localhost\r\n");
$response = '';
while ($str = fgets($socket, 515)) {
    $response .= $str;
    if (substr($str, 3, 1) === ' ') break;
}
echo "EHLO Response:\n" . $response . "\n";

// Start TLS
fwrite($socket, "STARTTLS\r\n");
$response = fgets($socket, 515);
echo "STARTTLS: " . $response;

if (strpos($response, '220') === 0) {
    // Enable crypto
    if (stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        echo "TLS enabled successfully\n\n";

        // EHLO again after TLS
        fwrite($socket, "EHLO localhost\r\n");
        while ($str = fgets($socket, 515)) {
            if (substr($str, 3, 1) === ' ') break;
        }

        // AUTH LOGIN
        echo "Authenticating...\n";
        fwrite($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 515);
        echo $response;

        fwrite($socket, base64_encode($smtp_user) . "\r\n");
        $response = fgets($socket, 515);
        echo $response;

        fwrite($socket, base64_encode($smtp_pass) . "\r\n");
        $response = fgets($socket, 515);
        echo $response;

        if (strpos($response, '235') === 0) {
            echo "SUCCESS: Authentication successful!\n\n";

            // Send email
            echo "Sending email...\n";
            fwrite($socket, "MAIL FROM: <$smtp_user>\r\n");
            $response = fgets($socket, 515);
            echo $response;

            fwrite($socket, "RCPT TO: <$to>\r\n");
            $response = fgets($socket, 515);
            echo $response;

            fwrite($socket, "DATA\r\n");
            $response = fgets($socket, 515);
            echo $response;

            $email_data = "From: $smtp_user\r\n";
            $email_data .= "To: $to\r\n";
            $email_data .= "Subject: $subject\r\n";
            $email_data .= "Date: " . date('r') . "\r\n";
            $email_data .= "\r\n";
            $email_data .= $message;
            $email_data .= "\r\n.\r\n";

            fwrite($socket, $email_data);
            $response = fgets($socket, 515);
            echo $response;

            if (strpos($response, '250') === 0) {
                echo "\n✓ SUCCESS: Email sent successfully!\n";
                echo "Check inbox at $to\n";
            } else {
                echo "\n✗ ERROR: Failed to send email\n";
            }

            fwrite($socket, "QUIT\r\n");
        } else {
            echo "ERROR: Authentication failed\n";
            echo "Check your username and password\n";
        }
    } else {
        echo "ERROR: Failed to enable TLS\n";
    }
} else {
    echo "ERROR: STARTTLS failed\n";
}

fclose($socket);
?>
