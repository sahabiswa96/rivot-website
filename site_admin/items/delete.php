<?php
// items/delete.php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Disallow direct GET access
    set_flash('danger', 'Invalid request method.');
    header('Location: index.php');
    exit;
}

verify_csrf();

$pdo = get_pdo();
$id  = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    set_flash('danger', 'Invalid record ID.');
    header('Location: index.php');
    exit;
}

try {
    // Ensure the row exists first (optional but nicer UX)
    $chk = $pdo->prepare('SELECT id FROM orders WHERE id = :id LIMIT 1');
    $chk->execute([':id' => $id]);
    if (!$chk->fetch()) {
        set_flash('danger', 'Record not found.');
        header('Location: index.php');
        exit;
    }

    // Delete from ORDERS table (correct table)
    $stmt = $pdo->prepare('DELETE FROM orders WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        set_flash('success', 'Order deleted.');
    } else {
        set_flash('danger', 'Delete failed or no changes made.');
    }
} catch (Throwable $e) {
    // Show a safe error string
    set_flash('danger', 'Delete failed: ' . e($e->getMessage()));
}

header('Location: index.php');
exit;
