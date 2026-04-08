<?php
require_once __DIR__ . '/../includes/auth.php';
verify_csrf();

$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['ids'] ?? [];

    // Validate that IDs is an array and not empty
    if (!is_array($ids) || empty($ids)) {
        $_SESSION['error'] = 'No orders selected for deletion.';
        header('Location: index.php');
        exit;
    }

    // Sanitize IDs (ensure they're all integers)
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, function($id) { return $id > 0; });

    if (empty($ids)) {
        $_SESSION['error'] = 'Invalid order IDs provided.';
        header('Location: index.php');
        exit;
    }

    try {
        // Create placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Delete the orders
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id IN ($placeholders)");
        $stmt->execute($ids);

        $deletedCount = $stmt->rowCount();
        $_SESSION['success'] = "Successfully deleted $deletedCount order(s).";

    } catch (Exception $e) {
        $_SESSION['error'] = 'Failed to delete orders: ' . htmlspecialchars($e->getMessage());
    }
}

header('Location: index.php');
exit;
