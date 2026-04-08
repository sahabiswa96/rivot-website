<?php
// SMTP Configuration - Hostinger
$smtp_host = "smtp.hostinger.com";
$smtp_port = 465;
$smtp_user = "change@rivotmotors.com";
$smtp_pass = "RIVOT@M0tors";
$smtp_encryption = "SSL";

// Email details
$to = "parthait2003@gmail.com";
$subject = "SMTP Test Email from Rivot Motors (Hostinger)";
$message = "This is a test email to verify SMTP is working correctly.\n\n";
$message .= "SMTP Server: $smtp_host:$smtp_port\n";
$message .= "Encryption: $smtp_encryption\n";
$message .= "From: $smtp_user\n";
$message .= "Date: " . date('Y-m-d H:i:s') . "\n";

echo "=== SMTP Test - Hostinger ===\n";
echo "MAIL_MAILER: SMTP\n";
echo "MAIL_HOST: $smtp_host\n";
echo "MAIL_PORT: $smtp_port\n";
echo "MAIL_USERNAME: $smtp_user\n";
echo "MAIL_ENCRYPTION: $smtp_encryption\n";
echo "To: $to\n";
echo "---\n\n";

// Test SSL connection (port 465 uses implicit SSL)
echo "Testing SMTP connection with SSL...\n";
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);

$socket = @stream_socket_client(
    "ssl://$smtp_host:$smtp_port",
    $errno,
    $errstr,
    10,
    STREAM_CLIENT_CONNECT,
    $context
);

if (!$socket) {
    echo "ERROR: Cannot connect to SMTP server\n";
    echo "Error ($errno): $errstr\n";
    exit(1);
}
echo "SUCCESS: Connected to SMTP server with SSL\n\n";

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

    $email_data = "From: Rivot Motors <$smtp_user>\r\n";
    $email_data .= "To: $to\r\n";
    $email_data .= "Subject: $subject\r\n";
    $email_data .= "Date: " . date('r') . "\r\n";
    $email_data .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $email_data .= "\r\n";
    $email_data .= $message;
    $email_data .= "\r\n.\r\n";

    fwrite($socket, $email_data);
    $response = fgets($socket, 515);
    echo $response;

    if (strpos($response, '250') === 0) {
        echo "\n✓ SUCCESS: Email sent successfully!\n";
        echo "✓ SMTP is working correctly\n";
        echo "✓ Check inbox at $to\n";
    } else {
        echo "\n✗ ERROR: Failed to send email\n";
    }

    fwrite($socket, "QUIT\r\n");
    $response = fgets($socket, 515);
    echo $response;
} else {
    echo "ERROR: Authentication failed\n";
    echo "Check your username and password\n";
    if (strpos($response, '535') !== false) {
        echo "\nPossible issues:\n";
        echo "- Incorrect username or password\n";
        echo "- Email account not set up properly in Hostinger\n";
        echo "- Account may be locked or suspended\n";
    }
}

fclose($socket);
echo "\n=== Test Complete ===\n";
?>
