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
$status = $input['status'] ?? '';

if (!$id || !is_numeric($id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid blog post ID']);
    exit;
}

if (!in_array($status, ['draft', 'published'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
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

    // Update blog post status
    $stmt = $pdo->prepare("UPDATE blogs SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $id]);

    echo json_encode(['success' => true, 'message' => 'Blog post status updated successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>