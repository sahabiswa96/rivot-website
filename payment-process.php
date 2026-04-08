<?php
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function for debugging
function logMessage($message) {
    $logFile = 'payment_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Get POST data
 $data = json_decode(file_get_contents('php://input'), true);
logMessage("Received data: " . print_r($data, true));

 $responseCode = $data['responseCode'] ?? '';
 $orderId = $data['orderId'] ?? '';
 $paymentId = $data['paymentId'] ?? '';
 $bookingData = $data['bookingData'] ?? [];

// Verify payment with Zaakpay
 $checksum = $_POST['checksum'] ?? '';
 $yourSecretKey = 'd38f1436894f4d4f9d423eeebec5a38f'; // Zaakpay live secret key

// Generate checksum to verify
 $calculatedChecksum = generateChecksum($data, $yourSecretKey);

if ($responseCode === '100' && $checksum === $calculatedChecksum) {
    // Payment successful
    // Save booking to database
    $saveResult = saveBookingToDatabase($bookingData, $orderId, $paymentId);
    
    if ($saveResult) {
        // Send emails to customer and admin
        $emailResult = sendEmails($bookingData, $orderId, $paymentId);
        logMessage("Email sending result: " . ($emailResult ? 'Success' : 'Failed'));
        
        echo json_encode([
            'success' => true,
            'redirect' => 'thankyou.html?order_id=' . urlencode($orderId),
            'order_id' => $orderId
        ]);
    } else {
        logMessage("Failed to save booking to database");
        echo json_encode([
            'success' => false,
            'redirect' => 'payment-failed.html',
            'message' => 'Failed to save booking'
        ]);
    }
} else {
    logMessage("Payment verification failed. Response code: $responseCode, Checksum match: " . ($checksum === $calculatedChecksum ? 'Yes' : 'No'));
    echo json_encode([
        'success' => false,
        'redirect' => 'payment-failed.html',
        'message' => 'Payment verification failed'
    ]);
}

function generateChecksum($data, $secretKey) {
    // Implement checksum generation as per Zaakpay documentation
    return hash_hmac('sha256', json_encode($data), $secretKey);
}

function saveBookingToDatabase($bookingData, $orderId, $paymentId) {
    // Database connection details
    $servername = "145.223.108.203";
    $username = "u462945421_rivotdata";
    $password = "localhost";
    $dbname = "u462945421_rivotdata";
    
    try {
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        // Check connection
        if ($conn->connect_error) {
            logMessage("Database connection failed: " . $conn->connect_error);
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO bookings (order_id, payment_id, first_name, last_name, email, phone, address, city, pincode, model, color, amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            logMessage("Prepare failed: " . $conn->error);
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $amount = '499';
        $status = 'Confirmed';
        $stmt->bind_param("sssssssssssss", $orderId, $paymentId, $bookingData['firstName'], $bookingData['lastName'], $bookingData['email'], $bookingData['phone'], $bookingData['address'], $bookingData['city'], $bookingData['pincode'], $bookingData['model'], $bookingData['color'], $amount, $status);
        
        // Execute the statement
        $result = $stmt->execute();
        if (!$result) {
            logMessage("Execute failed: " . $stmt->error);
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        // Close the connection
        $stmt->close();
        $conn->close();
        
        logMessage("Booking saved successfully to database");
        return $result;
    } catch (Exception $e) {
        logMessage("Database error: " . $e->getMessage());
        return false;
    }
}

function sendEmails($bookingData, $orderId, $paymentId) {
    // Admin email
    $adminEmail = 'support@rivotmotors.com';
    
    // Email to customer
    $customerSubject = 'Booking Confirmed - RIVOT Motors';
    $customerMessage = "
Dear {$bookingData['firstName']} {$bookingData['lastName']},

Thank you for booking your RIVOT {$bookingData['model']} (Color: {$bookingData['color']})!

Booking Details:
- Order ID: {$orderId}
- Payment ID: {$paymentId}
- Model: {$bookingData['model']}
- Color: {$bookingData['color']}
- Amount Paid: ₹499
- Status: Confirmed

Your booking has been confirmed successfully. Our team will contact you soon for further details.

Best regards,
Team RIVOT Motors
";
    
    // Email to admin
    $adminSubject = 'New Booking Received - RIVOT Motors';
    $adminMessage = "
New booking received:

Customer Details:
- Name: {$bookingData['firstName']} {$bookingData['lastName']}
- Email: {$bookingData['email']}
- Phone: {$bookingData['phone']}
- Address: {$bookingData['address']}, {$bookingData['city']} - {$bookingData['pincode']}

Booking Details:
- Order ID: {$orderId}
- Payment ID: {$paymentId}
- Model: {$bookingData['model']}
- Color: {$bookingData['color']}
- Amount Paid: ₹499
- Status: Confirmed
";
    
    // Send emails
    $headers = "From: noreply@rivotmotors.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    $customerEmailSent = mail($bookingData['email'], $customerSubject, $customerMessage, $headers);
    logMessage("Customer email sent to {$bookingData['email']}: " . ($customerEmailSent ? 'Success' : 'Failed'));
    
    $adminEmailSent = mail($adminEmail, $adminSubject, $adminMessage, $headers);
    logMessage("Admin email sent to {$adminEmail}: " . ($adminEmailSent ? 'Success' : 'Failed'));
    
    return $customerEmailSent && $adminEmailSent;
}
?>