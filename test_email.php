<?php
// Test email script
$to = "parthait2003@gmail.com";
$subject = "SMTP Test Email";
$message = "This is a test email to verify SMTP is working correctly.\n\n";
$message .= "Sent from: " . gethostname() . "\n";
$message .= "Date: " . date('Y-m-d H:i:s') . "\n";

$headers = "From: webserver@" . gethostname() . "\r\n";
$headers .= "Reply-To: webserver@" . gethostname() . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

echo "Attempting to send email to: $to\n";
echo "Subject: $subject\n";
echo "---\n";

if (mail($to, $subject, $message, $headers)) {
    echo "SUCCESS: Email was sent successfully!\n";
    echo "Check the inbox at $to\n";
} else {
    echo "ERROR: Email sending failed.\n";
    echo "Please check your SMTP/mail configuration.\n";
}
?>
