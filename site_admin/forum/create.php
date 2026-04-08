<?php
$pageTitle = 'Create Forum Post';
require_once __DIR__ . '/../includes/auth.php';
$pdo = get_pdo();

// Get categories for dropdown
$categoriesStmt = $pdo->query("SELECT * FROM forum_categories WHERE is_active = 1 ORDER BY sort_order ASC");
$categories = $categoriesStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $icon = trim($_POST['icon'] ?? 'fas fa-comments');
    $status = $_POST['status'] ?? 'active';
    $replies = (int)($_POST['replies'] ?? 0);
    $views = (int)($_POST['views'] ?? 0);

    $errors = [];

    if (empty($title)) {
        $errors[] = 'Title is required';
    }
    if (empty($content)) {
        $errors[] = 'Content is required';
    }
    if (empty($excerpt)) {
        $errors[] = 'Excerpt is required';
    }
    if (empty($author)) {
        $errors[] = 'Author is required';
    }
    if (empty($category_id) || !is_numeric($category_id)) {
        $errors[] = 'Please select a valid category';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO forum_posts (title, content, excerpt, author, category_id, icon, status, replies, views)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $content, $excerpt, $author, $category_id, $icon, $status, $replies, $views]);

            set_flash('success', 'Forum post created successfully!');
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Error creating forum post: ' . $e->getMessage();
        }
    }

    foreach ($errors as $error) {
        set_flash('danger', $error);
    }
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

  .btn-outline-secondary {
    background: transparent;
    font-weight: 600;
    border-radius: 6px;
    transition: all 0.3s ease;
    border: 1px solid #444;
    color: #ccc;
  }

  .btn-outline-secondary:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: #CE6723;
    color: #fff;
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
    <h1>Create Forum Post</h1>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Forum Management
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" class="form-control" id="title" name="title"
                           value="<?php echo e($_POST['title'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="excerpt" class="form-label">Excerpt *</label>
                    <textarea class="form-control" id="excerpt" name="excerpt" rows="2" required
                              placeholder="Brief description that appears in the forum list"><?php echo e($_POST['excerpt'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Content *</label>
                    <textarea class="form-control" id="content" name="content" rows="12" required
                              placeholder="Write the full forum post content here. You can use HTML tags for formatting."><?php echo e($_POST['content'] ?? ''); ?></textarea>
                    <div class="form-text">You can use HTML tags like &lt;p&gt;, &lt;h3&gt;, &lt;ul&gt;, &lt;li&gt;, etc. for formatting.</div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="author" class="form-label">Author *</label>
                            <input type="text" class="form-control" id="author" name="author"
                                   value="<?php echo e($_POST['author'] ?? 'RIVOT Team'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"
                                            <?php echo ($_POST['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo e($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon</label>
                            <input type="text" class="form-control" id="icon" name="icon"
                                   value="<?php echo e($_POST['icon'] ?? 'fas fa-comments'); ?>"
                                   placeholder="e.g., fas fa-bolt">
                            <div class="form-text">Font Awesome icon class</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="replies" class="form-label">Replies</label>
                            <input type="number" class="form-control" id="replies" name="replies"
                                   value="<?php echo e($_POST['replies'] ?? 0); ?>" min="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="views" class="form-label">Views</label>
                            <input type="number" class="form-control" id="views" name="views"
                                   value="<?php echo e($_POST['views'] ?? 0); ?>" min="0">
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?php echo ($_POST['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>
                            Active
                        </option>
                        <option value="locked" <?php echo ($_POST['status'] ?? '') === 'locked' ? 'selected' : ''; ?>>
                            Locked
                        </option>
                        <option value="pinned" <?php echo ($_POST['status'] ?? '') === 'pinned' ? 'selected' : ''; ?>>
                            Pinned
                        </option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Forum Post
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-lightbulb me-2"></i>Tips
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Write engaging, descriptive titles
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Keep excerpts concise but informative
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Use appropriate categories
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Choose relevant icons
                    </li>
                    <li>
                        <i class="fas fa-check text-success me-2"></i>
                        Use HTML for better formatting
                    </li>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-icons me-2"></i>Common Icons
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <small><code>fas fa-bolt</code> - Technical</small>
                    </div>
                    <div class="col-6">
                        <small><code>fas fa-road</code> - Travel</small>
                    </div>
                    <div class="col-6">
                        <small><code>fas fa-tools</code> - DIY/Tips</small>
                    </div>
                    <div class="col-6">
                        <small><code>fas fa-motorcycle</code> - Scooters</small>
                    </div>
                    <div class="col-6">
                        <small><code>fas fa-comments</code> - Discussion</small>
                    </div>
                    <div class="col-6">
                        <small><code>fas fa-exclamation-triangle</code> - Warning</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>