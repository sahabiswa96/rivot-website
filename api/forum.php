<?php
// api/forum.php - Forum API endpoint for frontend
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../site_admin/config.php';

try {
    $pdo = get_pdo();

    // Get query parameters
    $type = $_GET['type'] ?? 'posts'; // posts, categories, or stats
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $filter = $_GET['filter'] ?? 'recent'; // recent, popular, unanswered
    $category = $_GET['category'] ?? '';

    // Validate limit
    $limit = max(1, min(50, $limit));

    if ($type === 'categories') {
        // Get forum categories with statistics
        $stmt = $pdo->query("
            SELECT
                c.*,
                COUNT(p.id) as topics_count,
                COALESCE(SUM(p.replies), 0) as total_posts
            FROM forum_categories c
            LEFT JOIN forum_posts p ON c.id = p.category_id AND p.status = 'active'
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY c.sort_order ASC
        ");
        $categories = $stmt->fetchAll();

        // Calculate total posts (topics + replies) for each category
        foreach ($categories as &$category) {
            $category['posts_count'] = $category['topics_count'] + $category['total_posts'];
        }

        echo json_encode([
            'success' => true,
            'data' => $categories
        ]);

    } elseif ($type === 'posts') {
        // Check if requesting a single post by ID
        $postId = $_GET['id'] ?? '';

        if ($postId && is_numeric($postId)) {
            // Get single post with full content
            $stmt = $pdo->prepare("
                SELECT
                    p.*,
                    c.name as category_name,
                    c.color as category_color
                FROM forum_posts p
                LEFT JOIN forum_categories c ON p.category_id = c.id
                WHERE p.id = ? AND p.status = 'active'
            ");
            $stmt->execute([$postId]);
            $post = $stmt->fetch();

            if (!$post) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Post not found'
                ]);
                exit;
            }

            // Format data for frontend
            $post['created_at'] = date('M j, Y g:i A', strtotime($post['created_at']));
            $post['time_ago'] = timeAgo($post['created_at']);

            // Increment view count
            $updateViews = $pdo->prepare("UPDATE forum_posts SET views = views + 1 WHERE id = ?");
            $updateViews->execute([$postId]);
            $post['views']++; // Update the value in response

            echo json_encode([
                'success' => true,
                'data' => $post
            ]);
            exit;
        }

        // Build WHERE clause based on filters
        $whereClause = "WHERE p.status = 'active'";
        $params = [];
        $orderBy = "ORDER BY p.created_at DESC";

        if ($category && is_numeric($category)) {
            $whereClause .= " AND p.category_id = ?";
            $params[] = $category;
        }

        // Apply sorting based on filter
        switch ($filter) {
            case 'popular':
                $orderBy = "ORDER BY (p.replies + p.views) DESC, p.created_at DESC";
                break;
            case 'unanswered':
                $whereClause .= " AND p.replies = 0";
                $orderBy = "ORDER BY p.created_at DESC";
                break;
            case 'recent':
            default:
                $orderBy = "ORDER BY p.created_at DESC";
                break;
        }

        // Get forum posts
        $stmt = $pdo->prepare("
            SELECT
                p.id,
                p.title,
                p.excerpt,
                p.author,
                p.icon,
                p.replies,
                p.views,
                p.status,
                p.created_at,
                c.name as category_name,
                c.color as category_color
            FROM forum_posts p
            LEFT JOIN forum_categories c ON p.category_id = c.id
            $whereClause
            $orderBy
            LIMIT ? OFFSET ?
        ");

        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        // Get total count for pagination
        $countParams = array_slice($params, 0, -2); // Remove limit and offset
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM forum_posts p
            LEFT JOIN forum_categories c ON p.category_id = c.id
            $whereClause
        ");
        $countStmt->execute($countParams);
        $total = $countStmt->fetch()['total'];

        // Format data for frontend
        foreach ($posts as &$post) {
            $post['created_at'] = date('M j, Y g:i A', strtotime($post['created_at']));
            $post['time_ago'] = timeAgo($post['created_at']);
        }

        echo json_encode([
            'success' => true,
            'data' => $posts,
            'meta' => [
                'total' => (int)$total,
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total,
                'filter' => $filter
            ]
        ]);

    } elseif ($type === 'stats') {
        // Get overall forum statistics
        $statsStmt = $pdo->query("
            SELECT
                COUNT(DISTINCT p.id) as total_topics,
                COALESCE(SUM(p.replies), 0) as total_replies,
                COUNT(DISTINCT p.author) as total_members,
                MAX(p.created_at) as latest_post
            FROM forum_posts p
            WHERE p.status = 'active'
        ");
        $stats = $statsStmt->fetch();

        $stats['total_posts'] = $stats['total_topics'] + $stats['total_replies'];
        $stats['latest_post'] = $stats['latest_post'] ? date('M j, Y', strtotime($stats['latest_post'])) : null;

        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid type parameter'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);

    // Log error for debugging (in production)
    error_log('Forum API Error: ' . $e->getMessage());
}

// Helper function to calculate time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}
?>