<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? '';

if (!$id || !is_numeric($id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid blog post ID']);
    exit;
}

try {
    $pdo = get_pdo();

    // Check if blog post exists
    $stmt = $pdo->prepare("SELECT id FROM blogs WHERE id = ?");
    $stmt->execute([$id]);

    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Blog post not found']);
        exit;
    }

    // Delete blog post
    $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Blog post deleted successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>