<?php
// Database configuration
$host = '127.0.0.1';
$dbname = 'rivot_booking';
$username = 'rivot';
$password = 'Riv0t@211';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Helper function to convert paise to rupees
function paiseToRupees($paise) {
    return number_format($paise / 100, 2);
}

// Helper function to convert rupees to paise
function rupeesToPaise($rupees) {
    return round($rupees * 100);
}
?>