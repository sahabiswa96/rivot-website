<?php
$pageTitle = 'Blog Post Preview';
require_once __DIR__ . '/../includes/auth.php';
$pdo = get_pdo();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = 'Invalid blog ID.';
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
    $_SESSION['error'] = 'Blog post not found.';
    header('Location: index.php');
    exit;
}

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

  .page-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .page-title i {
    color: #fff;
    font-size: 1.2rem;
  }

  .page-title h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
  }

  .header-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
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

  .card-body {
    padding: 1.5rem;
  }

  .btn-primary {
    background: linear-gradient(135deg, #CE6723 0%, #e07a3a 100%);
    border: none;
    color: white;
    font-weight: 600;
    padding: 11px 20px;
    border-radius: 6px;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(206, 103, 35, 0.3);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(206, 103, 35, 0.35);
    background: linear-gradient(135deg, #e07a3a 0%, #CE6723 100%);
    color: #fff;
  }

  .btn-outline-secondary,
  .btn-outline-primary,
  .btn-outline-danger {
    background: transparent;
    font-weight: 600;
    border-radius: 6px;
    transition: all 0.3s ease;
    padding: 11px 18px;
  }

  .btn-outline-secondary {
    border: 1px solid #444;
    color: #ddd;
  }

  .btn-outline-secondary:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: #CE6723;
    color: #fff;
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

  .btn-outline-danger {
    border: 1px solid rgba(231, 76, 60, 0.45);
    color: #e74c3c;
  }

  .btn-outline-danger:hover {
    background: rgba(231, 76, 60, 0.12);
    color: #fff;
    border-color: #e74c3c;
  }

  .preview-shell {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 1.5rem;
    align-items: start;
  }

  .main-preview-card {
    overflow: hidden;
  }

  .blog-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.6rem;
    color: #fff;
  }

  .blog-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
    color: #aaa;
    font-size: 0.92rem;
    margin-bottom: 1rem;
  }

  .blog-meta span {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
  }

  .status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.36rem 0.8rem;
    font-size: 0.76rem;
    font-weight: 600;
    border-radius: 999px;
  }

  .status-published {
    background: rgba(46, 204, 113, 0.16);
    color: #2ecc71;
    border: 1px solid rgba(46, 204, 113, 0.24);
  }

  .status-draft {
    background: rgba(255, 193, 7, 0.15);
    color: #ffc107;
    border: 1px solid rgba(255, 193, 7, 0.24);
  }

  .blog-image {
    width: 100%;
    max-height: 390px;
    object-fit: cover;
    border-radius: 12px;
    border: 1px solid #333;
    margin-bottom: 1rem;
    background: #111;
  }

  .excerpt-box {
    background: rgba(206, 103, 35, 0.08);
    border: 1px solid rgba(206, 103, 35, 0.14);
    border-radius: 10px;
    padding: 1rem;
    color: #ddd;
    margin-bottom: 1rem;
    font-weight: 500;
    line-height: 1.7;
  }

  .content-box {
    color: #d7d7d7;
    line-height: 1.9;
    font-size: 0.98rem;
  }

  .content-box h1,
  .content-box h2,
  .content-box h3,
  .content-box h4,
  .content-box h5,
  .content-box h6 {
    color: #fff;
    margin-top: 1.35rem;
    margin-bottom: 0.75rem;
  }

  .content-box p {
    margin-bottom: 1rem;
  }

  .content-box ul,
  .content-box ol {
    margin-bottom: 1rem;
    padding-left: 1.2rem;
  }

  .content-box li {
    margin-bottom: 0.5rem;
  }

  .content-box img {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
  }

  .right-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .side-card .card-body {
    padding: 1rem;
  }

  .section-heading {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 1rem;
  }

  .section-heading i {
    color: #fff;
    font-size: 0.95rem;
  }

  .action-stack {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .action-stack .btn,
  .action-stack a,
  .action-stack button {
    width: 100%;
    min-height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    font-weight: 600;
  }

  .action-stack form {
    margin: 0;
  }

  .status-box {
    padding: 14px 16px;
    border-radius: 10px;
    border: 1px solid rgba(46, 204, 113, 0.25);
    background: rgba(46, 204, 113, 0.12);
    color: #2ecc71;
    font-weight: 600;
    text-align: center;
  }

  .status-box.status-draft-box {
    border: 1px solid rgba(255, 193, 7, 0.24);
    background: rgba(255, 193, 7, 0.12);
    color: #ffc107;
  }

  .details-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .details-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
  }

  .details-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }

  .details-label {
    color: #aaa;
    font-size: 13px;
    font-weight: 600;
  }

  .details-value {
    color: #fff;
    font-size: 13px;
    text-align: right;
    max-width: 60%;
    word-break: break-word;
  }

  .preview-note {
    color: #999;
    font-size: 13px;
    line-height: 1.6;
    margin-bottom: 14px;
  }

  .muted-card {
    color: #aaa;
  }

  @media (max-width: 991px) {
    .preview-shell {
      grid-template-columns: 1fr;
    }

    .dash-fill {
      padding: 1rem;
    }

    .page-title h1 {
      font-size: 1.5rem;
    }

    .blog-title {
      font-size: 1.5rem;
    }

    .details-value {
      max-width: 55%;
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
    <div class="page-title">
      <i class="fa-solid fa-file-lines"></i>
      <h1>Blog Post Preview</h1>
    </div>

    <div class="header-actions">
      <a href="edit.php?id=<?php echo (int)$blog['id']; ?>" class="btn btn-outline-primary">
        <i class="fas fa-pen-to-square me-2"></i> Edit
      </a>
      <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back
      </a>
    </div>
  </div>

  <div class="preview-shell">
    <!-- Left/Main -->
    <div class="card main-preview-card">
      <div class="card-body">
        <div class="blog-title"><?php echo h($blog['title']); ?></div>

        <div class="blog-meta">
          <span><i class="fas fa-user" style="color:#CE6723;"></i> By <?php echo h($blog['author']); ?></span>
          <span><i class="fas fa-calendar" style="color:#CE6723;"></i> <?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span>
          <span class="status-badge <?php echo ($blog['status'] === 'published') ? 'status-published' : 'status-draft'; ?>">
            <?php echo ucfirst($blog['status']); ?>
          </span>
        </div>

        <?php if (!empty($blog['image_url'])): ?>
          <img
            src="/<?php echo h($blog['image_url']); ?>"
            alt="<?php echo h($blog['title']); ?>"
            class="blog-image"
          >
        <?php endif; ?>

        <?php if (!empty($blog['excerpt'])): ?>
          <div class="excerpt-box">
            <?php echo nl2br(h($blog['excerpt'])); ?>
          </div>
        <?php endif; ?>

        <div class="content-box">
          <?php echo $blog['content']; ?>
        </div>
      </div>
    </div>

    <!-- Right Sidebar -->
    <div class="right-sidebar">
      <div class="card side-card">
        <div class="card-body">
          <div class="section-heading">
            <i class="fas fa-bolt"></i>
            <span>Actions</span>
          </div>

          <div class="action-stack">
            <a href="edit.php?id=<?php echo (int)$blog['id']; ?>" class="btn btn-primary">
              <i class="fas fa-pen-to-square me-2"></i> Edit Post
            </a>

            <div class="status-box <?php echo $blog['status'] === 'draft' ? 'status-draft-box' : ''; ?>">
              <i class="fas <?php echo $blog['status'] === 'published' ? 'fa-check' : 'fa-clock'; ?> me-2"></i>
              <?php echo ucfirst($blog['status']); ?>
            </div>

            <form action="delete.php" method="post" onsubmit="return confirm('Are you sure you want to delete this blog post?');">
              <?php csrf_field(); ?>
              <input type="hidden" name="id" value="<?php echo (int)$blog['id']; ?>">
              <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-2"></i> Delete Post
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="card side-card">
        <div class="card-body">
          <div class="section-heading">
            <i class="fas fa-circle-info"></i>
            <span>Post Details</span>
          </div>

          <div class="details-list">
            <div class="details-row">
              <div class="details-label">ID</div>
              <div class="details-value"><?php echo (int)$blog['id']; ?></div>
            </div>

            <div class="details-row">
              <div class="details-label">Author</div>
              <div class="details-value"><?php echo h($blog['author']); ?></div>
            </div>

            <div class="details-row">
              <div class="details-label">Status</div>
              <div class="details-value">
                <span class="status-badge <?php echo ($blog['status'] === 'published') ? 'status-published' : 'status-draft'; ?>">
                  <?php echo ucfirst($blog['status']); ?>
                </span>
              </div>
            </div>

            <div class="details-row">
              <div class="details-label">Created</div>
              <div class="details-value"><?php echo date('M d, Y g:i A', strtotime($blog['created_at'])); ?></div>
            </div>

            <div class="details-row">
              <div class="details-label">Updated</div>
              <div class="details-value">
                <?php
                  $updated = !empty($blog['updated_at']) ? $blog['updated_at'] : $blog['created_at'];
                  echo date('M d, Y g:i A', strtotime($updated));
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card side-card">
        <div class="card-body">
          <div class="section-heading">
            <i class="fas fa-up-right-from-square"></i>
            <span>Frontend Preview</span>
          </div>

          <p class="preview-note">
            This is the admin preview of the selected blog post. The public website layout may appear differently depending on the frontend design.
          </p>

          <?php
            $frontendUrl = !empty($blog['slug'])
              ? '/blog/' . urlencode($blog['slug'])
              : '/blog.php?id=' . (int)$blog['id'];
          ?>

          <a href="<?php echo h($frontendUrl); ?>" target="_blank" class="btn btn-outline-primary w-100">
            <i class="fas fa-eye me-2"></i> View Frontend Blog
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>