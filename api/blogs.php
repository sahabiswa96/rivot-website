<?php
// api/blogs.php - Blog API endpoint for frontend
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../site_admin/config.php';

try {
    $pdo = get_pdo();

    // Get query parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $status = $_GET['status'] ?? 'published';
    $id = $_GET['id'] ?? null;

    // Validate limit
    $limit = max(1, min(50, $limit));

    if ($id) {
        // Get single blog post
        $stmt = $pdo->prepare("
            SELECT id, title, excerpt, content, image_url, author, status, created_at, updated_at
            FROM blogs
            WHERE id = ? AND status = 'published'
        ");
        $stmt->execute([$id]);
        $blog = $stmt->fetch();

        if ($blog) {
            // Format dates
            $blog['created_at'] = date('M j, Y', strtotime($blog['created_at']));
            $blog['updated_at'] = date('M j, Y', strtotime($blog['updated_at']));

            echo json_encode([
                'success' => true,
                'data' => $blog
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Blog post not found'
            ]);
        }
    } else {
        // Get list of blog posts
        $stmt = $pdo->prepare("
            SELECT id, title, excerpt, image_url, author, created_at
            FROM blogs
            WHERE status = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$status, $limit, $offset]);
        $blogs = $stmt->fetchAll();

        // Get total count for pagination
        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM blogs WHERE status = ?");
        $countStmt->execute([$status]);
        $total = $countStmt->fetch()['total'];

        // Format dates
        foreach ($blogs as &$blog) {
            $blog['created_at'] = date('M j, Y', strtotime($blog['created_at']));
        }

        echo json_encode([
            'success' => true,
            'data' => $blogs,
            'meta' => [
                'total' => (int)$total,
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total
            ]
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);

    // Log error for debugging (in production)
    error_log('Blog API Error: ' . $e->getMessage());
}
?>