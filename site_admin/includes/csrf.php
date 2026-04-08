<?php
// includes/csrf.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_field() {
    $t = csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($t) . '">';
}
function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            http_response_code(400);
            die('Invalid CSRF token.');
        }
    }
}
?>