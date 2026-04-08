<?php
$pageTitle = 'Edit Blog Post';
require_once __DIR__ . '/../includes/auth.php';
$pdo = get_pdo();

$id = $_GET['id'] ?? '';
if (!$id || !is_numeric($id)) {
    set_flash('danger', 'Invalid blog post ID');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
$stmt->execute([$id]);
$blog = $stmt->fetch();

if (!$blog) {
    set_flash('danger', 'Blog post not found');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $status = $_POST['status'] ?? 'draft';

    $errors = [];

    if (empty($title)) {
        $errors[] = 'Title is required';
    }
    if (empty($excerpt)) {
        $errors[] = 'Excerpt is required';
    }
    if (empty($content)) {
        $errors[] = 'Content is required';
    }
    if (empty($image_url)) {
        $errors[] = 'Image URL is required';
    }
    if (empty($author)) {
        $errors[] = 'Author is required';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE blogs
                SET title = ?, excerpt = ?, content = ?, image_url = ?, author = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$title, $excerpt, $content, $image_url, $author, $status, $id]);

            set_flash('success', 'Blog post updated successfully!');
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Error updating blog post: ' . $e->getMessage();
        }
    }

    foreach ($errors as $error) {
        set_flash('danger', $error);
    }
} else {
    $_POST['title'] = $blog['title'];
    $_POST['excerpt'] = $blog['excerpt'];
    $_POST['content'] = $blog['content'];
    $_POST['image_url'] = $blog['image_url'];
    $_POST['author'] = $blog['author'];
    $_POST['status'] = $blog['status'];
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

  .btn-outline-secondary,
  .btn-outline-info {
    background: transparent;
    font-weight: 600;
    border-radius: 6px;
    transition: all 0.3s ease;
  }

  .btn-outline-secondary {
    border: 1px solid #444;
    color: #ccc;
  }

  .btn-outline-secondary:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: #CE6723;
    color: #fff;
  }

  .btn-outline-info {
    border: 1px solid #CE6723;
    color: #CE6723;
  }

  .btn-outline-info:hover {
    background: rgba(206, 103, 35, 0.12);
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
    <h1>Edit Blog Post</h1>
    <div class="btn-group">
        <a href="view.php?id=<?php echo $blog['id']; ?>" class="btn btn-outline-info">
            <i class="fas fa-eye me-1"></i> Preview
        </a>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" class="form-control" id="title" name="title"
                           value="<?php echo e($_POST['title']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="excerpt" class="form-label">Excerpt *</label>
                    <textarea class="form-control" id="excerpt" name="excerpt" rows="3" required
                              placeholder="Brief description of the blog post"><?php echo e($_POST['excerpt']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Content *</label>
                    <textarea class="form-control" id="content" name="content" rows="15" required
                              placeholder="Write your blog post content here. You can use HTML tags for formatting."><?php echo e($_POST['content']); ?></textarea>
                    <div class="form-text">You can use HTML tags like &lt;p&gt;, &lt;h3&gt;, &lt;ul&gt;, &lt;li&gt;, etc. for formatting.</div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Image URL *</label>
                            <input type="text" class="form-control" id="image_url" name="image_url"
                                   value="<?php echo e($_POST['image_url']); ?>" required
                                   placeholder="e.g., Story_page/blog-image.webp">
                            <div class="form-text">Relative path to the image file</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="author" class="form-label">Author *</label>
                            <input type="text" class="form-control" id="author" name="author"
                                   value="<?php echo e($_POST['author']); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="draft" <?php echo $_POST['status'] === 'draft' ? 'selected' : ''; ?>>
                            Draft
                        </option>
                        <option value="published" <?php echo $_POST['status'] === 'published' ? 'selected' : ''; ?>>
                            Published
                        </option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Blog Post
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
                    <i class="fa-solid fa-info-circle me-2"></i>Blog Info
                </h5>
            </div>
            <div class="card-body">
                <p><strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($blog['created_at'])); ?></p>
                <p><strong>Last Updated:</strong> <?php echo date('M j, Y g:i A', strtotime($blog['updated_at'])); ?></p>
                <p><strong>Current Status:</strong>
                    <span class="badge bg-<?php echo $blog['status'] === 'published' ? 'success' : 'warning'; ?>">
                        <?php echo ucfirst($blog['status']); ?>
                    </span>
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fa-solid fa-lightbulb me-2"></i>Tips
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fa-solid fa-check text-success me-2"></i>
                        Use clear, engaging titles
                    </li>
                    <li class="mb-2">
                        <i class="fa-solid fa-check text-success me-2"></i>
                        Write compelling excerpts to attract readers
                    </li>
                    <li class="mb-2">
                        <i class="fa-solid fa-check text-success me-2"></i>
                        Use HTML for better formatting
                    </li>
                    <li class="mb-2">
                        <i class="fa-solid fa-check text-success me-2"></i>
                        Choose high-quality images
                    </li>
                    <li>
                        <i class="fa-solid fa-check text-success me-2"></i>
                        Preview before publishing
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>