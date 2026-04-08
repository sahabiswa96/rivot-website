<?php

$servername = "localhost"; // Database server
$username   = "rivot";      // Database username
$password   = "Riv0t@211";          // Database password
$dbname     = "rivot_booking";   // Database name

try {
    // Create PDO instance
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    
    // Set error mode to Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Close connection (optional, done automatically at script end)
$conn = null;
?>
