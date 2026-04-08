<?php
$pageTitle = 'Forum Management';
require_once __DIR__ . '/../includes/auth.php';
$pdo = get_pdo();

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';

$whereClause = '';
$params = [];

if ($search) {
    $whereClause .= " WHERE p.title LIKE ? OR p.content LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $whereClause .= $whereClause ? " AND p.category_id = ?" : " WHERE p.category_id = ?";
    $params[] = $category;
}

if ($status) {
    $whereClause .= $whereClause ? " AND p.status = ?" : " WHERE p.status = ?";
    $params[] = $status;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name
    FROM forum_posts p
    LEFT JOIN forum_categories c ON p.category_id = c.id
    $whereClause
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$categoriesStmt = $pdo->query("SELECT * FROM forum_categories ORDER BY sort_order ASC");
$categories = $categoriesStmt->fetchAll();
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
  html, body {
    min-height: 100%;
    background: #000;
    color: #fff;
    font-family: "Montserrat", sans-serif;
  }

  #app {
    min-height: 100vh;
    background: #000;
  }

  /* Topbar */
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

  /* Sidebar */
  .sidebar {
    background-color: #111 !important;
    border-right: 1px solid #333 !important;
    min-height: 100vh;
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
    background-color: rgba(206, 103, 35, 0.12) !important;
  }

  .sidebar .nav-link i {
    margin-right: 0.75rem;
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
  }

  /* Page bg glow */
  .container.py-4 {
    position: relative;
    min-height: calc(100vh - 120px);
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

  /* Headings */
  h1, h2, h3, h4, h5, h6,
  .card-title,
  .form-label {
    color: #fff !important;
  }

  .text-secondary,
  .text-muted,
  small {
    color: #aaa !important;
  }

  /* Cards */
  .card {
    background: rgba(20, 20, 20, 0.92) !important;
    border: 1px solid #333 !important;
    border-radius: 12px !important;
    box-shadow: 0 10px 30px rgba(0,0,0,.25);
    backdrop-filter: blur(10px);
  }

  .card-body {
    background: transparent !important;
    color: #fff !important;
  }

  .card-header {
    background: rgba(17, 17, 17, 0.95) !important;
    border-bottom: 1px solid #333 !important;
    color: #fff !important;
  }

  /* Buttons */
  .btn {
    border-radius: 8px;
    font-weight: 600;
    transition: all .25s ease;
  }

  .btn-primary {
    background: linear-gradient(135deg, #CE6723 0%, #e07a3a 100%) !important;
    border: none !important;
    color: #fff !important;
    box-shadow: 0 5px 15px rgba(206, 103, 35, 0.3);
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #e07a3a 0%, #CE6723 100%) !important;
    transform: translateY(-1px);
    color: #fff !important;
  }

  .btn-outline-secondary {
    background: transparent !important;
    border: 1px solid #444 !important;
    color: #ccc !important;
  }

  .btn-outline-secondary:hover {
    background: rgba(255,255,255,.08) !important;
    border-color: #CE6723 !important;
    color: #fff !important;
  }

  .btn-outline-info,
  .btn-outline-primary {
    background: transparent !important;
    border: 1px solid #CE6723 !important;
    color: #CE6723 !important;
  }

  .btn-outline-info:hover,
  .btn-outline-primary:hover {
    background: rgba(206,103,35,.12) !important;
    color: #fff !important;
    border-color: #CE6723 !important;
  }

  .btn-outline-danger {
    background: transparent !important;
    border: 1px solid rgba(231,76,60,.5) !important;
    color: #e74c3c !important;
  }

  .btn-outline-danger:hover {
    background: rgba(231,76,60,.12) !important;
    color: #fff !important;
    border-color: #e74c3c !important;
  }

  /* Forms */
  .form-label {
    color: #fff !important;
    font-weight: 500;
  }

  .form-control,
  .form-select {
    background: rgba(255,255,255,.07) !important;
    border: 1px solid #444 !important;
    color: #fff !important;
    border-radius: 8px !important;
    box-shadow: none !important;
  }

  .form-control:focus,
  .form-select:focus {
    background: rgba(255,255,255,.07) !important;
    border-color: #CE6723 !important;
    color: #fff !important;
    box-shadow: 0 0 0 .2rem rgba(206,103,35,.16) !important;
  }

  .form-control::placeholder {
    color: rgba(255,255,255,.6) !important;
  }

  .form-select option {
    background: #111;
    color: #fff;
  }

  /* Table - full dark */
  .table-responsive {
    background: #111 !important;
    border-radius: 10px;
    overflow: hidden;
  }

  .table {
    background: #111 !important;
    color: #ddd !important;
    border-color: #333 !important;
    margin-bottom: 0;
  }

  .table thead,
  .table thead tr,
  .table thead th {
    background: #0b0b0b !important;
    color: #fff !important;
    border-color: #333 !important;
    white-space: nowrap;
  }

  .table thead th {
    border-bottom: 1px solid rgba(206,103,35,.6) !important;
    font-weight: 700;
  }

  .table tbody,
  .table tbody tr,
  .table tbody td {
    background: #111 !important;
    color: #ddd !important;
    border-color: #333 !important;
    vertical-align: middle;
  }

  .table-hover > tbody > tr:hover > * {
    background: rgba(206,103,35,.08) !important;
    color: #fff !important;
  }

  /* Badges */
  .badge {
    border-radius: 999px;
    padding: .42em .82em;
    font-size: .76rem;
    font-weight: 700;
  }

  .badge.bg-success {
    background: rgba(46,204,113,.16) !important;
    color: #2ecc71 !important;
    border: 1px solid rgba(46,204,113,.22);
  }

  .badge.bg-warning {
    background: rgba(255,193,7,.15) !important;
    color: #ffc107 !important;
    border: 1px solid rgba(255,193,7,.22);
  }

  .badge.bg-info {
    background: rgba(52,152,219,.16) !important;
    color: #59b8ff !important;
    border: 1px solid rgba(52,152,219,.22);
  }

  .badge.bg-secondary {
    background: rgba(255,255,255,.11) !important;
    color: #ddd !important;
    border: 1px solid rgba(255,255,255,.1);
  }

  /* Icons / misc */
  .text-primary {
    color: #2f7cff !important;
  }

  @media (max-width: 768px) {
    .container.py-4 {
      padding-top: 1rem !important;
      padding-bottom: 1rem !important;
    }

    .btn-group {
      flex-wrap: wrap;
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

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <h1>Forum Management</h1>
    <div class="btn-group">
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Forum Post
        </a>
        <a href="replies.php" class="btn btn-outline-info">
            <i class="fas fa-comments me-1"></i> Manage Replies
        </a>
        <a href="categories.php" class="btn btn-outline-secondary">
            <i class="fas fa-list me-1"></i> Manage Categories
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="<?php echo e($search); ?>" placeholder="Search by title or content...">
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo e($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="locked" <?php echo $status === 'locked' ? 'selected' : ''; ?>>Locked</option>
                    <option value="pinned" <?php echo $status === 'pinned' ? 'selected' : ''; ?>>Pinned</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Forum Posts Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($posts)): ?>
            <div class="text-center py-5">
                <i class="fas fa-comments fa-3x text-secondary mb-3"></i>
                <h5>No forum posts found</h5>
                <p class="text-secondary">Create your first forum post to get started.</p>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create Forum Post
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Stats</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="<?php echo e($post['icon']); ?> fa-lg text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?php echo e($post['title']); ?></div>
                                        <div class="text-secondary small">
                                            <?php echo e(substr($post['excerpt'], 0, 80)); ?>...
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?php echo e($post['category_name']); ?></span>
                            </td>
                            <td><?php echo e($post['author']); ?></td>
                            <td>
                                <small class="text-muted">
                                    <i class="fas fa-reply me-1"></i><?php echo $post['replies']; ?>
                                    <i class="fas fa-eye me-1 ms-2"></i><?php echo $post['views']; ?>
                                </small>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'active' => 'success',
                                    'locked' => 'warning',
                                    'pinned' => 'info'
                                ];
                                $statusColor = $statusColors[$post['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $statusColor; ?>">
                                    <?php echo ucfirst($post['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="view.php?id=<?php echo $post['id']; ?>"
                                       class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?php echo $post['id']; ?>"
                                       class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="deletePost(<?php echo $post['id']; ?>)" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
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
                location.reload();
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