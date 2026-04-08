<?php
$pageTitle = 'View Forum Post';
require_once __DIR__ . '/../includes/auth.php';
$pdo = get_pdo();

$id = $_GET['id'] ?? '';
if (!$id || !is_numeric($id)) {
    set_flash('danger', 'Invalid forum post ID');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name
    FROM forum_posts p
    LEFT JOIN forum_categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    set_flash('danger', 'Forum post not found');
    header('Location: index.php');
    exit;
}

$statusColors = [
    'active' => 'success',
    'locked' => 'warning',
    'pinned' => 'info'
];
$statusColor = $statusColors[$post['status']] ?? 'secondary';

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
  html, body {
    height: 100%;
    background-color: #000;
    color: #fff;
    font-family: "Montserrat", sans-serif;
  }

  #app {
    min-height: 100vh;
    background-color: #000;
  }

  .sidebar {
    height: 100%;
    background-color: #111;
    border-right: 1px solid #333 !important;
  }

  .sidebar .nav-link {
    color: rgba(255, 255, 255, 0.8) !important;
    padding: 0.75rem 1rem !important;
    margin: 0.25rem 0;
    border-radius: 6px;
    transition: all 0.3s ease !important;
    display: flex;
    align-items: center;
    text-decoration: none !important;
  }

  .sidebar .nav-link:hover,
  .sidebar .nav-link.active {
    color: #fff !important;
    background-color: rgba(206, 103, 35, 0.1) !important;
  }

  .sidebar .nav-link i {
    margin-right: 0.75rem;
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
  }

  .navbar {
    background-color: #000 !important;
    border-bottom: 1px solid #333 !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3) !important;
    padding: 0.75rem 12px !important;
    height: 70px;
  }

  .navbar-brand {
    color: #fff !important;
    font-weight: 600;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none !important;
  }

  .navbar-brand img {
    height: 35px;
    width: auto;
    transition: transform 0.3s ease;
  }

  .navbar-brand:hover img {
    transform: scale(1.05);
  }

  .container.py-4 {
    min-height: calc(100vh - 120px);
    position: relative;
  }

  .container.py-4::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
      radial-gradient(circle at 70% 30%, rgba(206, 103, 35, 0.05) 0%, transparent 50%),
      radial-gradient(circle at 30% 70%, rgba(206, 103, 35, 0.05) 0%, transparent 50%);
    z-index: -1;
  }

  .page-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
  }

  .page-topbar h1 {
    margin: 0;
    color: #fff;
    font-size: 2rem;
    font-weight: 700;
  }

  .col-lg-4 .card {
  background: rgba(30, 30, 30, 0.8) !important;
  border: 1px solid #333 !important;
  border-radius: 12px !important;
  backdrop-filter: blur(10px);
}

.col-lg-4 .card-header {
  background: rgba(20, 20, 20, 0.75) !important;
  border-bottom: 1px solid #333 !important;
  color: #fff !important;
}

.col-lg-4 .card-body {
  background: transparent !important;
  color: #ddd !important;
}

.col-lg-4 .card-title,
.col-lg-4 .card-header i,
.col-lg-4 strong {
  color: #fff !important;
}

  .btn-primary {
    background: linear-gradient(135deg, #CE6723 0%, #e07a3a 100%);
    border: none;
    color: white;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 6px;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(206, 103, 35, 0.3);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(206, 103, 35, 0.4);
    background: linear-gradient(135deg, #e07a3a 0%, #CE6723 100%);
    color: #fff;
  }

  .btn-outline-secondary,
  .btn-outline-primary,
  .btn-outline-danger,
  .btn-outline-info,
  .btn-warning,
  .btn-success {
    font-weight: 600;
    border-radius: 6px;
    transition: all 0.3s ease;
  }

  .btn-outline-secondary {
    border: 1px solid #444;
    color: #ccc;
    background: transparent;
  }

  .btn-outline-secondary:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: #CE6723;
    color: #fff;
  }

  .btn-outline-primary,
  .btn-outline-info {
    border: 1px solid #CE6723;
    color: #CE6723;
    background: transparent;
  }

  .btn-outline-primary:hover,
  .btn-outline-info:hover {
    background: rgba(206, 103, 35, 0.12);
    border-color: #CE6723;
    color: #fff;
  }

  .btn-outline-danger {
    border: 1px solid rgba(231, 76, 60, 0.45);
    color: #e74c3c;
    background: transparent;
  }

  .btn-outline-danger:hover {
    background: rgba(231, 76, 60, 0.12);
    color: #fff;
    border-color: #e74c3c;
  }

  .btn-warning {
    background: rgba(255, 193, 7, 0.14);
    border: 1px solid rgba(255, 193, 7, 0.22);
    color: #ffc107;
  }

  .btn-warning:hover {
    background: rgba(255, 193, 7, 0.22);
    color: #fff;
  }

  .btn-success {
    background: rgba(46, 204, 113, 0.14);
    border: 1px solid rgba(46, 204, 113, 0.22);
    color: #2ecc71;
  }

  .btn-success:hover {
    background: rgba(46, 204, 113, 0.22);
    color: #fff;
  }

  .btn-info {
    background: rgba(52, 152, 219, 0.14);
    border: 1px solid rgba(52, 152, 219, 0.22);
    color: #4aa8ea;
  }

  .btn-info:hover {
    background: rgba(52, 152, 219, 0.22);
    color: #fff;
  }

  .card {
    background: rgba(30, 30, 30, 0.8);
    border: 1px solid #333;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    height: auto;
  }

  .card:hover {
    box-shadow: 0 10px 30px rgba(206, 103, 35, 0.16);
    border-color: #CE6723;
  }

  .card-header {
    background: rgba(20, 20, 20, 0.75);
    border-bottom: 1px solid #333;
    color: #fff;
  }

  .card-title,
  .form-label,
  h1, h2, h3, h4, h5, h6 {
    color: #fff;
  }

  .card-body {
    color: #ddd;
  }

  .text-dark {
    color: #fff !important;
  }

  .text-muted {
    color: #aaa !important;
  }

  .text-primary {
    color: #CE6723 !important;
  }

  .forum-content {
    line-height: 1.8;
    font-size: 16px;
    color: #d7d7d7;
  }

  .forum-content h1,
  .forum-content h2,
  .forum-content h3,
  .forum-content h4,
  .forum-content h5,
  .forum-content h6 {
    color: #fff;
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
  }

  .forum-content p {
    margin-bottom: 1.25rem;
    color: #cfcfcf;
  }

  .forum-content ul,
  .forum-content ol {
    margin-bottom: 1.25rem;
    padding-left: 1.25rem;
  }

  .forum-content li {
    margin-bottom: 0.5rem;
    color: #cfcfcf;
  }

  .forum-content strong {
    color: #fff;
  }

  .forum-content a {
    color: #CE6723;
  }

  .forum-content blockquote {
    border-left: 4px solid #CE6723;
    padding-left: 1rem;
    color: #bbb;
    margin: 1rem 0;
  }

  .badge.bg-secondary {
    background: rgba(255, 255, 255, 0.1) !important;
    color: #ddd !important;
    border: 1px solid rgba(255,255,255,0.08);
  }

  .badge.bg-success {
    background: rgba(46, 204, 113, 0.18) !important;
    color: #2ecc71 !important;
    border: 1px solid rgba(46, 204, 113, 0.24);
  }

  .badge.bg-warning {
    background: rgba(255, 193, 7, 0.16) !important;
    color: #ffc107 !important;
    border: 1px solid rgba(255, 193, 7, 0.22);
  }

  .badge.bg-info {
    background: rgba(52, 152, 219, 0.16) !important;
    color: #4aa8ea !important;
    border: 1px solid rgba(52, 152, 219, 0.22);
  }

  .action-stack {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .action-stack .btn,
  .action-stack button,
  .action-stack a {
    width: 100%;
    min-height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-align: center;
  }

  .action-stack form {
    margin: 0;
  }

  @media (max-width: 991px) {
    .page-topbar {
      align-items: flex-start;
    }

    .page-topbar h1 {
      font-size: 1.5rem;
    }
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var navbarBrand = document.querySelector('.navbar-brand');
    if (navbarBrand && !navbarBrand.querySelector('img')) {
        var logo = document.createElement('img');
        logo.src = '/img/logo.svg';
        logo.alt = 'Admin Panel Logo';
        logo.style.height = '35px';
        logo.style.marginRight = '10px';

        var originalText = navbarBrand.textContent.trim();
        navbarBrand.innerHTML = '';
        navbarBrand.appendChild(logo);
        navbarBrand.appendChild(document.createTextNode(originalText));
    }
});
</script>

<div class="page-topbar">
    <h1>Forum Post Preview</h1>
    <div class="btn-group">
        <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-primary">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <article class="card">
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="<?php echo h($post['icon']); ?> fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h1 class="h3 fw-bold text-dark mb-1"><?php echo h($post['title']); ?></h1>
                            <div class="d-flex align-items-center text-muted flex-wrap gap-2">
                                <span><i class="fas fa-user me-2"></i>By <?php echo h($post['author']); ?></span>
                                <span>•</span>
                                <span><i class="fas fa-calendar me-2"></i><?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?></span>
                                <span>•</span>
                                <span class="badge bg-secondary"><?php echo h($post['category_name']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                        <span class="badge bg-<?php echo $statusColor; ?>">
                            <?php echo ucfirst($post['status']); ?>
                        </span>
                        <div class="text-muted small">
                            <i class="fas fa-reply me-1"></i><?php echo $post['replies']; ?> replies
                            <span class="mx-2">•</span>
                            <i class="fas fa-eye me-1"></i><?php echo $post['views']; ?> views
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="text-muted"><?php echo h($post['excerpt']); ?></h5>
                </div>

                <div class="forum-content">
                    <?php echo $post['content']; ?>
                </div>
            </div>
        </article>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cog me-2"></i>Actions
                </h5>
            </div>
            <div class="card-body action-stack">
                <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Edit Post
                </a>

                <?php if ($post['status'] === 'active'): ?>
                    <button type="button" class="btn btn-warning"
                            onclick="updateStatus(<?php echo $post['id']; ?>, 'locked')">
                        <i class="fas fa-lock me-2"></i>Lock Post
                    </button>
                    <button type="button" class="btn btn-info"
                            onclick="updateStatus(<?php echo $post['id']; ?>, 'pinned')">
                        <i class="fas fa-thumbtack me-2"></i>Pin Post
                    </button>
                <?php elseif ($post['status'] === 'locked'): ?>
                    <button type="button" class="btn btn-success"
                            onclick="updateStatus(<?php echo $post['id']; ?>, 'active')">
                        <i class="fas fa-unlock me-2"></i>Unlock Post
                    </button>
                <?php elseif ($post['status'] === 'pinned'): ?>
                    <button type="button" class="btn btn-success"
                            onclick="updateStatus(<?php echo $post['id']; ?>, 'active')">
                        <i class="fas fa-thumbtack me-2"></i>Unpin Post
                    </button>
                <?php endif; ?>

                <button type="button" class="btn btn-outline-danger"
                        onclick="deletePost(<?php echo $post['id']; ?>)">
                    <i class="fas fa-trash me-2"></i>Delete Post
                </button>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Post Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-sm-5"><strong>ID:</strong></div>
                    <div class="col-sm-7"><?php echo $post['id']; ?></div>

                    <div class="col-sm-5"><strong>Author:</strong></div>
                    <div class="col-sm-7"><?php echo h($post['author']); ?></div>

                    <div class="col-sm-5"><strong>Category:</strong></div>
                    <div class="col-sm-7"><?php echo h($post['category_name']); ?></div>

                    <div class="col-sm-5"><strong>Status:</strong></div>
                    <div class="col-sm-7">
                        <span class="badge bg-<?php echo $statusColor; ?>">
                            <?php echo ucfirst($post['status']); ?>
                        </span>
                    </div>

                    <div class="col-sm-5"><strong>Replies:</strong></div>
                    <div class="col-sm-7"><?php echo $post['replies']; ?></div>

                    <div class="col-sm-5"><strong>Views:</strong></div>
                    <div class="col-sm-7"><?php echo $post['views']; ?></div>

                    <div class="col-sm-5"><strong>Created:</strong></div>
                    <div class="col-sm-7"><?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?></div>

                    <div class="col-sm-5"><strong>Updated:</strong></div>
                    <div class="col-sm-7"><?php echo date('M j, Y g:i A', strtotime($post['updated_at'])); ?></div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-external-link-alt me-2"></i>Frontend Preview
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">This is how the forum post appears in the admin panel. The frontend layout may differ.</p>
                <a href="/forum.html" target="_blank" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i> View Frontend Forum
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus(id, status) {
    if (confirm(`Are you sure you want to change this post status to ${status}?`)) {
        fetch('update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id, status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating status: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status.');
        });
    }
}

function deletePost(id) {
    if (confirm('Are you sure you want to delete this forum post? This action cannot be undone.')) {
        fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'index.php';
            } else {
                alert('Error deleting forum post: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the forum post.');
        });
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>