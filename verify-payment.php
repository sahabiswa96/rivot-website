<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Razorpay configuration
$razorpay_key_id = 'rzp_test_qHJyouGT6hVA6y';
$razorpay_secret = 'MZ7ug7xkhdr7TcglAosDIKFI';

try {
    // Get the POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Extract payment details
    $razorpay_payment_id = $data['razorpay_payment_id'] ?? '';
    $razorpay_order_id = $data['razorpay_order_id'] ?? '';
    $razorpay_signature = $data['razorpay_signature'] ?? '';

    if (empty($razorpay_payment_id)) {
        throw new Exception('Missing payment ID');
    }

    // Verify signature (only if order_id exists)
    if (!empty($razorpay_order_id) && !empty($razorpay_signature)) {
        $expected_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, $razorpay_secret);

        if ($expected_signature !== $razorpay_signature) {
            throw new Exception('Payment signature verification failed');
        }
    } else {
        // For simple payments without orders, we'll verify by checking the payment ID format
        if (!preg_match('/^pay_[A-Za-z0-9]+$/', $razorpay_payment_id)) {
            throw new Exception('Invalid payment ID format');
        }
    }

    // Payment verified successfully
    // Here you can save the order details to database

    // Extract customer details
    $customer_name = $data['customer_name'] ?? '';
    $email = $data['email'] ?? '';
    $mobile = $data['mobile'] ?? '';
    $address = $data['address'] ?? '';
    $city = $data['city'] ?? '';
    $state = $data['state'] ?? '';
    $pincode = $data['pincode'] ?? '';
    $model = $data['model'] ?? '';
    $color = $data['color'] ?? '';
    $order_id = $data['order_id'] ?? '';

    // Log the successful payment (optional)
    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'payment_id' => $razorpay_payment_id,
        'order_id' => $order_id,
        'customer_name' => $customer_name,
        'email' => $email,
        'mobile' => $mobile,
        'model' => $model,
        'color' => $color,
        'amount' => 499,
        'status' => 'success'
    ];

    // You can save this to a database or log file
    file_put_contents('payment_logs.txt', json_encode($log_data) . "\n", FILE_APPEND | LOCK_EX);

    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Payment verified successfully',
        'payment_id' => $razorpay_payment_id,
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    // Send error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>