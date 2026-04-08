<?php
$pageTitle = 'Blog Management';
require_once __DIR__ . '/../includes/auth.php';
$pdo = get_pdo();

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$whereClause = '';
$params = [];

if ($search) {
    $whereClause .= " WHERE title LIKE ? OR content LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $whereClause .= $whereClause ? " AND status = ?" : " WHERE status = ?";
    $params[] = $status;
}

$stmt = $pdo->prepare("SELECT * FROM blogs $whereClause ORDER BY created_at DESC");
$stmt->execute($params);
$blogs = $stmt->fetchAll();
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

  .sidebar {
    background-color: #111 !important;
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

  .dash-fill {
    min-height: calc(100vh - 180px);
    padding: 2rem;
    position: relative;
  }

  .dash-fill::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
      radial-gradient(circle at 70% 30%, rgba(206, 103, 35, 0.05) 0%, transparent 50%),
      radial-gradient(circle at 30% 70%, rgba(206, 103, 35, 0.05) 0%, transparent 50%);
    z-index: -1;
  }

  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
  }

  .page-header h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
  }

  .card {
    background: rgba(30, 30, 30, 0.8);
    border: 1px solid #333;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    height: 100%;
  }

  .card:hover {
    box-shadow: 0 10px 30px rgba(206, 103, 35, 0.2);
    border-color: #CE6723;
  }

  .card-body {
    padding: 1.5rem;
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

  .btn-outline-primary,
  .btn-outline-secondary,
  .btn-outline-danger {
    background: transparent;
    font-weight: 600;
    border-radius: 6px;
    transition: all 0.3s ease;
  }

  .btn-outline-primary {
    border: 1px solid #CE6723;
    color: #CE6723;
  }

  .btn-outline-primary:hover {
    background: rgba(206, 103, 35, 0.12);
    color: #fff;
    border-color: #CE6723;
  }

  .btn-outline-secondary {
    border: 1px solid #444;
    color: #ccc;
  }

  .btn-outline-secondary:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    border-color: #CE6723;
  }

  .btn-outline-danger {
    border: 1px solid rgba(231, 76, 60, 0.5);
    color: #e74c3c;
  }

  .btn-outline-danger:hover {
    background: rgba(231, 76, 60, 0.12);
    color: #fff;
    border-color: #e74c3c;
  }

  .form-label {
    color: #fff;
    font-weight: 500;
    margin-bottom: 0.5rem;
  }

  .form-control,
  .form-select {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid #444;
    color: #fff;
    border-radius: 6px;
    padding: 12px 14px;
  }

  .form-control:focus,
  .form-select:focus {
    background: rgba(255, 255, 255, 0.08);
    border-color: #CE6723;
    color: #fff;
    box-shadow: 0 0 0 0.2rem rgba(206, 103, 35, 0.18);
  }

  .form-control::placeholder {
    color: rgba(255,255,255,0.65);
  }

  .form-select option {
    background: #111;
    color: #fff;
  }

  .table {
    color: #fff;
    margin-bottom: 0;
  }

  .table thead th {
    color: #fff;
    border-bottom: 2px solid #CE6723;
    background: rgba(30, 30, 30, 0.92);
    white-space: nowrap;
    font-weight: 600;
  }

  .table tbody td {
    color: #fff;
    border-color: #333;
    background: rgba(20, 20, 20, 0.72);
    vertical-align: middle;
  }

  .table-hover > tbody > tr:hover > * {
    background-color: rgba(206, 103, 35, 0.10) !important;
    color: #fff !important;
  }

  .text-secondary {
    color: #aaa !important;
  }

  .fw-semibold {
    color: #fff;
  }

  .badge {
    padding: 0.45em 0.8em;
    font-size: 0.78rem;
    border-radius: 999px;
    font-weight: 600;
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

  .empty-state {
    text-align: center;
    padding: 3rem 1rem;
  }

  .empty-state i {
    color: #CE6723;
    opacity: 0.85;
  }

  .btn-group .btn {
    padding: 0.45rem 0.7rem;
  }

  .blog-thumb {
    width: 50px;
    height: 40px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #333;
    background: #111;
  }

  @media (max-width: 768px) {
    .dash-fill {
      padding: 1rem;
    }

    .page-header h1 {
      font-size: 1.5rem;
    }

    .card-body {
      padding: 1rem;
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

<div class="dash-fill">
  <div class="page-header">
    <h1>Blog Management</h1>
    <a href="create.php" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> Add New Blog Post
    </a>
  </div>

  <!-- Filters -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-6">
          <label for="search" class="form-label">Search</label>
          <input type="text" class="form-control" id="search" name="search"
                 value="<?php echo e($search); ?>" placeholder="Search by title or content...">
        </div>
        <div class="col-md-4">
          <label for="status" class="form-label">Status</label>
          <select class="form-select" id="status" name="status">
            <option value="">All Status</option>
            <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
            <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
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

  <!-- Blog Posts Table -->
  <div class="card">
    <div class="card-body">
      <?php if (empty($blogs)): ?>
        <div class="empty-state">
          <i class="far fa-file-alt fa-3x mb-3"></i>
          <h5 class="mb-2">No blog posts found</h5>
          <p class="text-secondary mb-3">Create your first blog post to get started.</p>
          <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Blog Post
          </a>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($blogs as $blog): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <img src="/<?php echo e($blog['image_url']); ?>"
                         alt="Blog image"
                         class="blog-thumb me-3">
                    <div>
                      <div class="fw-semibold"><?php echo e($blog['title']); ?></div>
                      <div class="text-secondary small">
                        <?php echo e(substr($blog['excerpt'], 0, 80)); ?>...
                      </div>
                    </div>
                  </div>
                </td>
                <td><?php echo e($blog['author']); ?></td>
                <td>
                  <span class="badge bg-<?php echo $blog['status'] === 'published' ? 'success' : 'warning'; ?>">
                    <?php echo ucfirst($blog['status']); ?>
                  </span>
                </td>
                <td><?php echo date('M j, Y', strtotime($blog['created_at'])); ?></td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="view.php?id=<?php echo $blog['id']; ?>"
                       class="btn btn-outline-primary" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="edit.php?id=<?php echo $blog['id']; ?>"
                       class="btn btn-outline-secondary" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger"
                            onclick="deleteBlog(<?php echo $blog['id']; ?>)" title="Delete">
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
</div>

<script>
function deleteBlog(id) {
    if (confirm('Are you sure you want to delete this blog post? This action cannot be undone.')) {
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
                alert('Error deleting blog post: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the blog post.');
        });
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>