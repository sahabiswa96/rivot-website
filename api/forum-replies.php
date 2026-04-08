<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../site_admin/config.php';

try {
    $pdo = get_pdo();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get replies for a specific post
        $postId = $_GET['post_id'] ?? '';

        if (!$postId || !is_numeric($postId)) {
            http_response_code(400);
            echo json_encode(['error' => 'Valid post_id is required']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT
                r.*,
                DATE_FORMAT(r.created_at, '%M %d, %Y at %h:%i %p') as formatted_date,
                TIME_TO_SEC(TIMEDIFF(NOW(), r.created_at)) as seconds_ago
            FROM forum_replies r
            WHERE r.post_id = ? AND r.status = 'active'
            ORDER BY r.created_at ASC
        ");
        $stmt->execute([$postId]);
        $replies = $stmt->fetchAll();

        // Format relative time for each reply
        foreach ($replies as &$reply) {
            $seconds = $reply['seconds_ago'];
            if ($seconds < 60) {
                $reply['time_ago'] = 'Just now';
            } elseif ($seconds < 3600) {
                $minutes = floor($seconds / 60);
                $reply['time_ago'] = $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
            } elseif ($seconds < 86400) {
                $hours = floor($seconds / 3600);
                $reply['time_ago'] = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
            } elseif ($seconds < 604800) {
                $days = floor($seconds / 86400);
                $reply['time_ago'] = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
            } else {
                $reply['time_ago'] = $reply['formatted_date'];
            }
            unset($reply['seconds_ago']);
        }

        echo json_encode([
            'success' => true,
            'replies' => $replies,
            'count' => count($replies)
        ]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Add a new reply
        $input = json_decode(file_get_contents('php://input'), true);

        $postId = $input['post_id'] ?? '';
        $author = trim($input['author'] ?? '');
        $content = trim($input['content'] ?? '');

        $errors = [];

        if (!$postId || !is_numeric($postId)) {
            $errors[] = 'Valid post ID is required';
        }

        if (empty($author)) {
            $errors[] = 'Author name is required';
        } elseif (strlen($author) > 100) {
            $errors[] = 'Author name must be 100 characters or less';
        }

        if (empty($content)) {
            $errors[] = 'Reply content is required';
        } elseif (strlen($content) > 5000) {
            $errors[] = 'Reply content must be 5000 characters or less';
        }

        // Verify post exists
        if (empty($errors)) {
            $checkStmt = $pdo->prepare("SELECT id FROM forum_posts WHERE id = ? AND status != 'deleted'");
            $checkStmt->execute([$postId]);
            if (!$checkStmt->fetch()) {
                $errors[] = 'Forum post not found';
            }
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        try {
            // Insert new reply
            $stmt = $pdo->prepare("
                INSERT INTO forum_replies (post_id, author, content, status, created_at)
                VALUES (?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([$postId, $author, $content]);

            $replyId = $pdo->lastInsertId();

            // Update post reply count
            $updateStmt = $pdo->prepare("
                UPDATE forum_posts
                SET replies = (
                    SELECT COUNT(*) FROM forum_replies
                    WHERE post_id = ? AND status = 'active'
                ), updated_at = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$postId, $postId]);

            // Get the newly created reply
            $getReplyStmt = $pdo->prepare("
                SELECT
                    r.*,
                    DATE_FORMAT(r.created_at, '%M %d, %Y at %h:%i %p') as formatted_date
                FROM forum_replies r
                WHERE r.id = ?
            ");
            $getReplyStmt->execute([$replyId]);
            $newReply = $getReplyStmt->fetch();
            $newReply['time_ago'] = 'Just now';

            echo json_encode([
                'success' => true,
                'message' => 'Reply added successfully',
                'reply' => $newReply
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to add reply: ' . $e->getMessage()
            ]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>