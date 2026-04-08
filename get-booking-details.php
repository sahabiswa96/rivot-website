<?php
// Start output buffering to catch any warnings
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Disable error display (log only)
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/booking_details_errors.log');

// Database connection
$servername = "localhost";
$username = "rivot";
$password = "Riv0t@211";
$dbname = "rivot_booking";

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get order_id from URL parameter
    $orderId = $_GET['order_id'] ?? '';

    if (empty($orderId)) {
        throw new Exception('Order ID is required');
    }

    // Prepare and execute query - check both orderId and trackId
    $stmt = $conn->prepare("SELECT * FROM orders WHERE orderId = ? OR trackId = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $orderId, $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();

        // Map database fields to expected field names for frontend
        $mappedBooking = [
            'payment_id' => $booking['transaction_id'],
            'order_id' => $booking['orderId'],
            'first_name' => $booking['name'],
            'last_name' => $booking['lastName'],
            'phone' => $booking['mobile'],
            'email' => $booking['email'],
            'address' => $booking['address'],
            'city' => $booking['city'],
            'state' => $booking['state'],
            'country' => $booking['country'],
            'pincode' => $booking['pincode'],
            'model' => $booking['model'],
            'color' => $booking['color'],
            'product_name' => $booking['product_name'],
            'amount' => $booking['amount'],
            'status' => ($booking['statid'] == '1') ? 'Confirmed' : 'Pending',
            'created_at' => $booking['created_at']
        ];

        // Clean output buffer
        ob_clean();

        // Return success response with booking details
        echo json_encode([
            'success' => true,
            'booking' => $mappedBooking
        ]);
    } else {
        throw new Exception('Booking not found');
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Clean output buffer
    ob_clean();

    // Send error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Flush output buffer
ob_end_flush();
?>
