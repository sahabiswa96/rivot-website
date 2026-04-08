<?php
require_once('./../lib/Checksum.php');
require_once('./../../../resources/Config.php');
require_once(__DIR__ . '/../../../../../PHPMailer-master/src/Exception.php');
require_once(__DIR__ . '/../../../../../PHPMailer-master/src/PHPMailer.php');
require_once(__DIR__ . '/../../../../../PHPMailer-master/src/SMTP.php');
require_once(__DIR__ . '/../../../../../site_admin/config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get setting value from DB
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

// Keep DB amount in rupees if gateway sends paise
function normalizeAmountToRupees($amount) {
    $raw = trim((string)$amount);

    if ($raw === '') {
        return '0.00';
    }

    $clean = preg_replace('/[^0-9.]/', '', $raw);

    if ($clean === '' || !is_numeric($clean)) {
        return '0.00';
    }

    $num = (float)$clean;

    // Zaakpay usually returns paise like 49900
    if ($num > 999) {
        $num = $num / 100;
    }

    return number_format($num, 2, '.', '');
}

// STATIC mail amount
$mailStaticAmount = '499.00';

$checksumFlag = $environment != "https://api.zaakpay.com";

// Verify Zaakpay response checksum
$recd_checksum  = $_POST['checksum'] ?? '';
$all            = Checksum::getAllResponseParams();
$checksum_check = Checksum::verifyChecksum($recd_checksum, $all, $secretKey);

// Collect gateway fields
$trackId_from_gateway    = $_POST['product1Description'] ?? '';
$orderId_from_gateway    = $_POST['orderId'] ?? '';
$txn_from_gateway        = $_POST['pgTransId'] ?? '';
$amount_from_gateway_raw = $_POST['amount'] ?? '';
$response_code           = $_POST['responseCode'] ?? '';

// DB amount (not for mail display)
$amount_from_gateway = normalizeAmountToRupees($amount_from_gateway_raw);

// Update DB only when checksum is valid and trackId exists
if ($checksum_check && $trackId_from_gateway !== '') {
    $conn = new mysqli('localhost', 'rivot', 'Riv0t@211', 'rivot_booking');

    if ($conn->connect_error) {
        error_log("DB connection failed in response.php: " . $conn->connect_error);
    } else {
        if ($response_code === '100') {
            $sql = "UPDATE orders
                    SET orderId = ?, transaction_id = ?, amount = ?, statid = '1', payment_status = 'payment_completed'
                    WHERE trackId = ?
                    LIMIT 1";
        } else {
            $sql = "UPDATE orders
                    SET orderId = ?, transaction_id = ?, amount = ?, payment_status = 'payment_failed'
                    WHERE trackId = ?
                    LIMIT 1";
        }

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param(
                "ssss",
                $orderId_from_gateway,
                $txn_from_gateway,
                $amount_from_gateway,
                $trackId_from_gateway
            );

            if (!$stmt->execute()) {
                error_log("response.php UPDATE failed: " . $stmt->error);
            }

            $stmt->close();

            // Fetch updated order
            $orderQuery = $conn->prepare("SELECT * FROM orders WHERE trackId = ? LIMIT 1");
            if ($orderQuery) {
                $orderQuery->bind_param("s", $trackId_from_gateway);
                $orderQuery->execute();
                $orderResult = $orderQuery->get_result();

                if ($orderResult->num_rows > 0) {
                    $orderData = $orderResult->fetch_assoc();

                    try {
                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.hostinger.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'support@protomatically.com';
                        $mail->Password   = 'sufMub2rappejuvfy@';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        $mail->Port       = 465;

                        $adminEmail = get_setting('admin_email', 'parthait2003@gmail.com');
                        $mail->setFrom('support@protomatically.com', 'RIVOT Motors');
                        $mail->addAddress($adminEmail, 'RIVOT Motors Admin');

                        if (!empty($orderData['email'])) {
                            $fullName = trim(($orderData['name'] ?? '') . ' ' . ($orderData['lastName'] ?? ''));
                            $mail->addAddress($orderData['email'], $fullName);
                            $mail->addReplyTo($orderData['email'], $fullName);
                        }

                        $mail->CharSet = 'UTF-8';
                        $mail->isHTML(true);

                        if ($response_code === '100') {
                            $mail->Subject = 'New Booking Confirmation - Order #' . $orderId_from_gateway . ' - RIVOT Motors';

                            $htmlBody = "
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <style>
                                    body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 20px; }
                                    .container { max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
                                    .header { background: #000; color: #fff; padding: 20px; text-align: center; }
                                    .header h1 { margin: 0; font-size: 24px; color: #fff !important; }
                                    .content { padding: 20px; background: #f9f9f9; }
                                    .field { margin-bottom: 10px; padding: 5px 0; }
                                    .label { font-weight: bold; color: #CE6723; display: inline-block; width: 180px; }
                                    .footer { background: #ddd; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                                    h2 { color: #333; margin-top: 0; border-bottom: 2px solid #CE6723; padding-bottom: 10px; }
                                    .success { color: #4CAF50; font-weight: bold; font-size: 18px; margin-bottom: 15px; }
                                </style>
                            </head>
                            <body>
                                <div class='container'>
                                    <div class='header'>
                                        <h1>New Booking Confirmed</h1>
                                    </div>
                                    <div class='content'>
                                        <div class='success'>Payment Successful!</div>
                                        <h2>Booking Details:</h2>
                                        <div class='field'><span class='label'>Order ID:</span> " . htmlspecialchars($orderId_from_gateway) . "</div>
                                        <div class='field'><span class='label'>Payment ID:</span> " . htmlspecialchars($txn_from_gateway) . "</div>
                                        <div class='field'><span class='label'>Amount Paid:</span> ₹499.00</div>
                                        <div class='field'><span class='label'>Status:</span> <span style='color: #4CAF50;'>Payment Completed</span></div>

                                        <h2 style='margin-top: 20px;'>Customer Details:</h2>
                                        <div class='field'><span class='label'>Name:</span> " . htmlspecialchars(trim(($orderData['name'] ?? '') . ' ' . ($orderData['lastName'] ?? ''))) . "</div>
                                        <div class='field'><span class='label'>Email:</span> " . htmlspecialchars($orderData['email'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>Phone:</span> " . htmlspecialchars($orderData['mobile'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>Address:</span> " . htmlspecialchars($orderData['address'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>City:</span> " . htmlspecialchars($orderData['city'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>State:</span> " . htmlspecialchars($orderData['state'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>Country:</span> " . htmlspecialchars($orderData['country'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>Pincode:</span> " . htmlspecialchars($orderData['pincode'] ?? '-') . "</div>

                                        <h2 style='margin-top: 20px;'>Product Details:</h2>
                                        <div class='field'><span class='label'>Product:</span> " . htmlspecialchars($orderData['product_name'] ?? 'nx100') . "</div>
                                        <div class='field'><span class='label'>Model:</span> " . htmlspecialchars($orderData['model'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>Color:</span> " . htmlspecialchars($orderData['color'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>Source:</span> " . htmlspecialchars($orderData['source'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>Referral Code:</span> " . htmlspecialchars($orderData['referralCode'] ?? '-') . "</div>

                                        <div class='field' style='margin-top: 20px;'><span class='label'>Booking Date:</span> " . date('Y-m-d H:i:s') . "</div>
                                    </div>
                                    <div class='footer'>
                                        <p>This email was sent from the RIVOT Motors booking system.</p>
                                        <p>Track ID: " . htmlspecialchars($trackId_from_gateway) . "</p>
                                    </div>
                                </div>
                            </body>
                            </html>";

                            $textBody = "NEW BOOKING CONFIRMATION\n\n";
                            $textBody .= "Payment Successful!\n\n";
                            $textBody .= "Booking Details:\n";
                            $textBody .= str_repeat("=", 50) . "\n\n";
                            $textBody .= "Order ID: " . $orderId_from_gateway . "\n";
                            $textBody .= "Payment ID: " . $txn_from_gateway . "\n";
                            $textBody .= "Amount Paid: ₹499.00\n";
                            $textBody .= "Status: Payment Completed\n\n";
                            $textBody .= "Customer Details:\n";
                            $textBody .= "Name: " . trim(($orderData['name'] ?? '') . ' ' . ($orderData['lastName'] ?? '')) . "\n";
                            $textBody .= "Email: " . ($orderData['email'] ?? '-') . "\n";
                            $textBody .= "Phone: " . ($orderData['mobile'] ?? '-') . "\n";
                            $textBody .= "Address: " . ($orderData['address'] ?? '-') . "\n";
                            $textBody .= "City: " . ($orderData['city'] ?? '-') . "\n";
                            $textBody .= "State: " . ($orderData['state'] ?? '-') . "\n";
                            $textBody .= "Country: " . ($orderData['country'] ?? '-') . "\n";
                            $textBody .= "Pincode: " . ($orderData['pincode'] ?? '-') . "\n\n";
                            $textBody .= "Product Details:\n";
                            $textBody .= "Product: " . ($orderData['product_name'] ?? 'nx100') . "\n";
                            $textBody .= "Model: " . ($orderData['model'] ?? '-') . "\n";
                            $textBody .= "Color: " . ($orderData['color'] ?? '-') . "\n";
                            $textBody .= "Source: " . ($orderData['source'] ?? '-') . "\n";
                            $textBody .= "Referral Code: " . ($orderData['referralCode'] ?? '-') . "\n\n";
                            $textBody .= "Booking Date: " . date('Y-m-d H:i:s') . "\n";
                            $textBody .= "Track ID: " . $trackId_from_gateway . "\n\n";
                            $textBody .= "This email was sent from the RIVOT Motors booking system.";
                        } else {
                            $mail->Subject = 'Payment Failed Alert - Order #' . ($orderId_from_gateway ?: $trackId_from_gateway) . ' - RIVOT Motors';

                            $htmlBody = "
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <style>
                                    body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 20px; }
                                    .container { max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
                                    .header { background: #000; color: #fff; padding: 20px; text-align: center; }
                                    .header h1 { margin: 0; font-size: 24px; color: #fff !important; }
                                    .content { padding: 20px; background: #f9f9f9; }
                                    .field { margin-bottom: 10px; padding: 5px 0; }
                                    .label { font-weight: bold; color: #CE6723; display: inline-block; width: 180px; }
                                    .footer { background: #ddd; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                                    h2 { color: #333; margin-top: 0; border-bottom: 2px solid #CE6723; padding-bottom: 10px; }
                                    .failed { color: #FF4444; font-weight: bold; font-size: 18px; margin-bottom: 15px; }
                                </style>
                            </head>
                            <body>
                                <div class='container'>
                                    <div class='header'>
                                        <h1>Payment Failed</h1>
                                    </div>
                                    <div class='content'>
                                        <div class='failed'>Payment was not successful</div>
                                        <h2>Transaction Details:</h2>
                                        <div class='field'><span class='label'>Track ID:</span> " . htmlspecialchars($trackId_from_gateway) . "</div>
                                        <div class='field'><span class='label'>Order ID:</span> " . htmlspecialchars($orderId_from_gateway ?: 'Not assigned') . "</div>
                                        <div class='field'><span class='label'>Payment ID:</span> " . htmlspecialchars($txn_from_gateway ?: 'Not available') . "</div>
                                        <div class='field'><span class='label'>Amount:</span> ₹499.00</div>
                                        <div class='field'><span class='label'>Response Code:</span> " . htmlspecialchars($response_code) . "</div>
                                        <div class='field'><span class='label'>Status:</span> <span style='color: #FF4444;'>Payment Failed</span></div>

                                        <h2 style='margin-top: 20px;'>Customer Details:</h2>
                                        <div class='field'><span class='label'>Name:</span> " . htmlspecialchars(trim(($orderData['name'] ?? '') . ' ' . ($orderData['lastName'] ?? ''))) . "</div>
                                        <div class='field'><span class='label'>Email:</span> " . htmlspecialchars($orderData['email'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>Phone:</span> " . htmlspecialchars($orderData['mobile'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>Address:</span> " . htmlspecialchars($orderData['address'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>City:</span> " . htmlspecialchars($orderData['city'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>State:</span> " . htmlspecialchars($orderData['state'] ?? '-') . "</div>

                                        <h2 style='margin-top: 20px;'>Product Details:</h2>
                                        <div class='field'><span class='label'>Product:</span> " . htmlspecialchars($orderData['product_name'] ?? 'nx100') . "</div>
                                        <div class='field'><span class='label'>Model:</span> " . htmlspecialchars($orderData['model'] ?? '-') . "</div>
                                        <div class='field'><span class='label'>Color:</span> " . htmlspecialchars($orderData['color'] ?? '-') . "</div>

                                        <div class='field' style='margin-top: 20px;'><span class='label'>Attempt Date:</span> " . date('Y-m-d H:i:s') . "</div>

                                        <div style='margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; color: #856404;'>
                                            <strong>Action Required:</strong> Customer may need assistance to complete the booking.
                                        </div>
                                    </div>
                                    <div class='footer'>
                                        <p>This email was sent from the RIVOT Motors booking system.</p>
                                        <p>Track ID: " . htmlspecialchars($trackId_from_gateway) . "</p>
                                    </div>
                                </div>
                            </body>
                            </html>";

                            $textBody = "PAYMENT FAILED ALERT\n\n";
                            $textBody .= "Payment was not successful\n\n";
                            $textBody .= "Transaction Details:\n";
                            $textBody .= str_repeat("=", 50) . "\n\n";
                            $textBody .= "Track ID: " . $trackId_from_gateway . "\n";
                            $textBody .= "Order ID: " . ($orderId_from_gateway ?: 'Not assigned') . "\n";
                            $textBody .= "Payment ID: " . ($txn_from_gateway ?: 'Not available') . "\n";
                            $textBody .= "Amount: ₹499.00\n";
                            $textBody .= "Response Code: " . $response_code . "\n";
                            $textBody .= "Status: Payment Failed\n\n";
                            $textBody .= "Customer Details:\n";
                            $textBody .= "Name: " . trim(($orderData['name'] ?? '') . ' ' . ($orderData['lastName'] ?? '')) . "\n";
                            $textBody .= "Email: " . ($orderData['email'] ?? '-') . "\n";
                            $textBody .= "Phone: " . ($orderData['mobile'] ?? '-') . "\n";
                            $textBody .= "Address: " . ($orderData['address'] ?? '-') . "\n";
                            $textBody .= "City: " . ($orderData['city'] ?? '-') . "\n";
                            $textBody .= "State: " . ($orderData['state'] ?? '-') . "\n\n";
                            $textBody .= "Product Details:\n";
                            $textBody .= "Product: " . ($orderData['product_name'] ?? 'nx100') . "\n";
                            $textBody .= "Model: " . ($orderData['model'] ?? '-') . "\n";
                            $textBody .= "Color: " . ($orderData['color'] ?? '-') . "\n\n";
                            $textBody .= "Attempt Date: " . date('Y-m-d H:i:s') . "\n";
                            $textBody .= "Track ID: " . $trackId_from_gateway . "\n\n";
                            $textBody .= "Action Required: Customer may need assistance to complete the booking.\n\n";
                            $textBody .= "This email was sent from the RIVOT Motors booking system.";
                        }

                        $mail->Body = $htmlBody;
                        $mail->AltBody = $textBody;
                        $mail->send();

                        error_log("Booking mail sent successfully for Track ID: " . $trackId_from_gateway);
                    } catch (Exception $e) {
                        error_log("Failed to send booking mail: " . $mail->ErrorInfo);
                    }
                }

                $orderQuery->close();
            }
        } else {
            error_log("response.php UPDATE prepare failed: " . $conn->error);
        }

        $conn->close();
    }
} else {
    if (!$checksum_check) {
        error_log("response.php: checksum verification failed; DB not updated.");
    } elseif ($trackId_from_gateway === '') {
        error_log("response.php: product1Description (trackId) missing; DB not updated.");
    }
}

// Redirect user
if ($response_code === '100') {
    $redirectUrl = 'https://rivotmotors.com/thankyou.html?order_id=' . urlencode($orderId_from_gateway);
} else {
    $redirectUrl = 'https://rivotmotors.com/payment-failed.html';
}

if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Location: ' . $redirectUrl, true, 303);
    exit;
}

echo '<!doctype html><html><head>
<meta http-equiv="refresh" content="0;url=' . $redirectUrl . '">
<script>window.location.replace("' . $redirectUrl . '");</script>
</head><body></body></html>';
exit;
?>