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
    echo json_encode(['success' => false, 'error' => 'Invalid forum post ID']);
    exit;
}

try {
    $pdo = get_pdo();

    // Check if forum post exists
    $stmt = $pdo->prepare("SELECT id FROM forum_posts WHERE id = ?");
    $stmt->execute([$id]);

    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Forum post not found']);
        exit;
    }

    // Delete forum post
    $stmt = $pdo->prepare("DELETE FROM forum_posts WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Forum post deleted successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>