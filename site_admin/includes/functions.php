<?php
// includes/functions.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/csrf.php';

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function set_flash($type, $message) {
    if (!isset($_SESSION['flash'])) $_SESSION['flash'] = [];
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function display_flash() {
    if (empty($_SESSION['flash'])) return;
    foreach ($_SESSION['flash'] as $msg) {
        $type = e($msg['type']);
        $text = e($msg['message']);
        echo "<div class='alert alert-$type alert-dismissible fade show' role='alert'>$text<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
    unset($_SESSION['flash']);
}

function is_logged_in() {
    return !empty($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}
?>