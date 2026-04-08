<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Include the PHPMailer library files
// Make sure the path 'PHPMailer-master/src/...' is correct for your project structure
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Include database config to get settings
require_once __DIR__ . '/site_admin/config.php';

// Function to get a setting value from the database
function get_setting($key, $default = '') {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1");
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        error_log("Failed to get setting '{$key}': " . $e->getMessage());
        return $default;
    }
}

// Check if the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. COLLECT AND SANITIZE FORM DATA ---

    $name = isset($_POST['name']) ? filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $mobile = isset($_POST['mobile']) ? filter_var(trim($_POST['mobile']), FILTER_SANITIZE_STRING) : '';
    $state = isset($_POST['state']) ? filter_var(trim($_POST['state']), FILTER_SANITIZE_STRING) : '';
    $city = isset($_POST['city']) ? filter_var(trim($_POST['city']), FILTER_SANITIZE_STRING) : '';
    $date = isset($_POST['date']) ? filter_var(trim($_POST['date']), FILTER_SANITIZE_STRING) : '';

    // --- 2. VALIDATE FORM DATA ---

    $errors = [];
    
    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Invalid name. Name must be at least 2 characters long.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }
    
    // Ensure mobile number is exactly 10 digits
    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = "Invalid mobile number. Must be 10 digits.";
    }
    
    if (empty($state)) {
        $errors[] = "State is required.";
    }
    
    if (empty($city)) {
        $errors[] = "City is required.";
    }
    
    if (empty($date)) {
        $errors[] = "Preferred date is required.";
    }

    // --- 3. IF THERE ARE VALIDATION ERRORS, REDIRECT BACK TO THE FORM ---

    if (!empty($errors)) {
        // Join all error messages into a single string and redirect
        $errorString = urlencode(implode(", ", $errors));
        header("Location: testride.html?error=" . $errorString);
        exit();
    }

    // --- 4. IF NO ERRORS, PROCEED TO SEND EMAIL ---

    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // --- SERVER SETTINGS ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'change@rivotmotors.com';
        $mail->Password   = 'RIVOT@M0tors';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL encryption
        $mail->Port       = 465;

        // Optional: Enable verbose debug output for troubleshooting
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;

        // --- RECIPIENTS ---
        // Get admin email from database settings (fallback to default if not set)
        $adminEmail = get_setting('admin_email', 'parthait2003@gmail.com');

        $mail->setFrom('change@rivotmotors.com', 'RIVOT Motors');
        $mail->addAddress($adminEmail, 'RIVOT Motors Admin'); // Send to admin email from settings
        $mail->addReplyTo($email, $name); // Set reply-to to the customer's email

        // --- EMAIL CONTENT ---
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'New Test Ride Booking Request - RIVOT Motors';
        
        // HTML Email Body
        $mail->Body    = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 20px; }
                    .container { max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
                    .header { background: #000; color: #fff; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f9f9f9; }
                    .field { margin-bottom: 10px; padding: 5px 0; }
                    .label { font-weight: bold; color: #CE6723; display: inline-block; width: 120px; }
                    .footer { background: #ddd; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                    h1 { margin: 0; font-size: 24px; }
                    h2 { color: #333; margin-top: 0; border-bottom: 2px solid #CE6723; padding-bottom: 10px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>New Test Ride Booking</h1>
                    </div>
                    <div class='content'>
                        <h2>Customer Details:</h2>
                        <div class='field'><span class='label'>Name:</span> " . htmlspecialchars($name) . "</div>
                        <div class='field'><span class='label'>Email:</span> " . htmlspecialchars($email) . "</div>
                        <div class='field'><span class='label'>Mobile:</span> " . htmlspecialchars($mobile) . "</div>
                        <div class='field'><span class='label'>State:</span> " . htmlspecialchars($state) . "</div>
                        <div class='field'><span class='label'>City:</span> " . htmlspecialchars($city) . "</div>
                        <div class='field'><span class='label'>Preferred Date:</span> " . htmlspecialchars($date) . "</div>
                        <div class='field'><span class='label'>Submitted:</span> " . date('Y-m-d H:i:s') . "</div>
                    </div>
                    <div class='footer'>
                        <p>This email was sent from the RIVOT Motors website contact form.</p>
                        <p>IP Address: " . $_SERVER['REMOTE_ADDR'] . "</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        // Plain text version for email clients that don't support HTML
        $mail->AltBody = "NEW TEST RIDE BOOKING REQUEST\n\n" .
                         "Customer Details:\n" .
                         "Name: " . $name . "\n" .
                         "Email: " . $email . "\n" .
                         "Mobile: " . $mobile . "\n" .
                         "State: " . $state . "\n" .
                         "City: " . $city . "\n" .
                         "Preferred Date: " . $date . "\n" .
                         "Submitted: " . date('Y-m-d H:i:s') . "\n" .
                         "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n\n" .
                         "This email was sent from the RIVOT Motors website contact form.";

        // --- SEND THE EMAIL ---
        $mail->send();

        // --- ON SUCCESS, REDIRECT WITH A SUCCESS PARAMETER ---
        // Log success for your records (optional)
        error_log("Test ride email sent successfully to " . $adminEmail . " from: " . $email);

        // Redirect back to the form with a success status
        header("Location: testride.html?status=success");
        exit();

    } catch (Exception $e) {
        // --- ON FAILURE, REDIRECT WITH AN ERROR MESSAGE ---
        // Log the detailed error for your records
        error_log("Email sending failed: " . $mail->ErrorInfo);

        // Redirect back to the form with a generic error message for the user
        header("Location: testride.html?error=" . urlencode("Failed to send email. Please try again later."));
        exit();
    }

} else {
    // If the script is accessed directly without a POST request, redirect to the form page
    header("Location: testride.html");
    exit();
}
?>