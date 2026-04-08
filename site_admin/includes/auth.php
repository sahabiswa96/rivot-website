<?php
// includes/auth.php
require_once __DIR__ . '/functions.php';
if (!is_logged_in()) {
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/login.php');
    exit;
}
?>