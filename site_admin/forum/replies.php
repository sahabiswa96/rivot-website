<?php
$pageTitle = 'Forum Replies Management';
require_once __DIR__ . '/../includes/auth.php';
$pdo = get_pdo();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $id = $_POST['id'] ?? '';
        $status = $_POST['status'] ?? '';

        if ($id && is_numeric($id) && in_array($status, ['active', 'deleted', 'moderated'], true)) {
            try {
                $stmt = $pdo->prepare("UPDATE forum_replies SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$status, $id]);

                $replyStmt = $pdo->prepare("SELECT post_id FROM forum_replies WHERE id = ?");
                $replyStmt->execute([$id]);
                $reply = $replyStmt->fetch(PDO::FETCH_ASSOC);

                if ($reply && !empty($reply['post_id'])) {
                    $updatePostStmt = $pdo->prepare("
                        UPDATE forum_posts
                        SET replies = (
                            SELECT COUNT(*) FROM forum_replies
                            WHERE post_id = ? AND status = 'active'
                        ), updated_at = NOW()
                        WHERE id = ?
                    ");
                    $updatePostStmt->execute([$reply['post_id'], $reply['post_id']]);
                }

                set_flash('success', 'Reply status updated successfully!');
            } catch (Throwable $e) {
                set_flash('danger', 'Error updating reply: ' . $e->getMessage());
            }
        } else {
            set_flash('danger', 'Invalid reply ID or status.');
        }

        header('Location: replies.php');
        exit;
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';

        if ($id && is_numeric($id)) {
            try {
                $replyStmt = $pdo->prepare("SELECT post_id FROM forum_replies WHERE id = ?");
                $replyStmt->execute([$id]);
                $reply = $replyStmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare("DELETE FROM forum_replies WHERE id = ?");
                $stmt->execute([$id]);

                if ($reply && !empty($reply['post_id'])) {
                    $updatePostStmt = $pdo->prepare("
                        UPDATE forum_posts
                        SET replies = (
                            SELECT COUNT(*) FROM forum_replies
                            WHERE post_id = ? AND status = 'active'
                        ), updated_at = NOW()
                        WHERE id = ?
                    ");
                    $updatePostStmt->execute([$reply['post_id'], $reply['post_id']]);
                }

                set_flash('success', 'Reply deleted successfully!');
            } catch (Throwable $e) {
                set_flash('danger', 'Error deleting reply: ' . $e->getMessage());
            }
        } else {
            set_flash('danger', 'Invalid reply ID.');
        }

        header('Location: replies.php');
        exit;
    }
}

// Filters
$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$post_id = trim($_GET['post_id'] ?? '');

$whereParts = [];
$params = [];

if ($search !== '') {
    $whereParts[] = "(r.content LIKE ? OR r.author LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($status !== '') {
    $whereParts[] = "r.status = ?";
    $params[] = $status;
}

if ($post_id !== '' && is_numeric($post_id)) {
    $whereParts[] = "r.post_id = ?";
    $params[] = $post_id;
}

$whereClause = '';
if (!empty($whereParts)) {
    $whereClause = ' WHERE ' . implode(' AND ', $whereParts);
}

// Get replies with post information
$sql = "
    SELECT
        r.*,
        p.title AS post_title,
        c.name AS category_name,
        DATE_FORMAT(r.created_at, '%M %d, %Y at %h:%i %p') AS formatted_date
    FROM forum_replies r
    LEFT JOIN forum_posts p ON r.post_id = p.id
    LEFT JOIN forum_categories c ON p.category_id = c.id
    {$whereClause}
    ORDER BY r.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$statsStmt = $pdo->query("
    SELECT
        COUNT(*) AS total_replies,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_replies,
        SUM(CASE WHEN status = 'moderated' THEN 1 ELSE 0 END) AS moderated_replies,
        SUM(CASE WHEN status = 'deleted' THEN 1 ELSE 0 END) AS deleted_replies
    FROM forum_replies
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Safe fallbacks
$stats['total_replies'] = (int)($stats['total_replies'] ?? 0);
$stats['active_replies'] = (int)($stats['active_replies'] ?? 0);
$stats['moderated_replies'] = (int)($stats['moderated_replies'] ?? 0);
$stats['deleted_replies'] = (int)($stats['deleted_replies'] ?? 0);
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
  color: rgba(255,255,255,0.8) !important;
  padding: 0.75rem 1rem !important;
  margin: 0.25rem 0;
  border-radius: 6px;
  transition: all .3s ease !important;
  display: flex;
  align-items: center;
  text-decoration: none !important;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
  color: #fff !important;
  background-color: rgba(206,103,35,0.1) !important;
}

.sidebar .nav-link i {
  margin-right: 10px;
  width: 20px;
  text-align: center;
}

.navbar {
  background-color: #000 !important;
  border-bottom: 1px solid #333 !important;
  box-shadow: 0 2px 10px rgba(0,0,0,0.3) !important;
  padding: 0.75rem 12px !important;
  height: 70px;
}

.navbar-brand {
  color: #fff !important;
  font-weight: 600;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
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
  content:'';
  position:absolute;
  inset:0;
  background:
    radial-gradient(circle at 70% 30%, rgba(206,103,35,.05) 0%, transparent 50%),
    radial-gradient(circle at 30% 70%, rgba(206,103,35,.05) 0%, transparent 50%);
  z-index:-1;
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
  color: #fff;
  font-size: 2rem;
  font-weight: 700;
}

.card {
  background: rgba(30,30,30,.8);
  border:1px solid #333;
  border-radius:12px;
  backdrop-filter: blur(10px);
  transition: all .3s ease;
}

.card:hover {
  border-color:#CE6723;
  box-shadow:0 10px 30px rgba(206,103,35,.15);
}

.card-header {
  background: rgba(20,20,20,.7);
  border-bottom: 1px solid #333;
  color: #fff;
}

.card-title,
.card-text,
.form-label,
h5,
strong {
  color: #fff;
}

.text-secondary,
.text-muted {
  color: #aaa !important;
}

.form-control,
.form-select {
  background: rgba(255,255,255,.08);
  border:1px solid #444;
  color:#fff;
  border-radius:6px;
}

.form-control:focus,
.form-select:focus {
  background: rgba(255,255,255,.08);
  border-color:#CE6723;
  color:#fff;
  box-shadow:0 0 0 .2rem rgba(206,103,35,.18);
}

.form-control::placeholder {
  color: rgba(255,255,255,.65);
}

.form-select option {
  background: #111;
  color: #fff;
}

.btn-primary {
  background: linear-gradient(135deg,#CE6723 0%,#e07a3a 100%);
  border:none;
  color:white;
  font-weight:600;
  padding:10px 22px;
  border-radius:6px;
  box-shadow:0 5px 15px rgba(206,103,35,.3);
}

.btn-primary:hover {
  transform:translateY(-2px);
  box-shadow:0 8px 20px rgba(206,103,35,.4);
  color:#fff;
}

.btn-outline-secondary,
.btn-outline-primary,
.btn-outline-danger {
  background: transparent;
  font-weight: 600;
  border-radius: 6px;
  transition: all .3s ease;
}

.btn-outline-secondary {
  border:1px solid #444;
  color:#ccc;
}

.btn-outline-secondary:hover {
  background:rgba(255,255,255,.08);
  border-color:#CE6723;
  color:#fff;
}

.btn-outline-primary {
  border:1px solid #CE6723;
  color:#CE6723;
}

.btn-outline-primary:hover {
  background:rgba(206,103,35,.1);
  color:#fff;
  border-color:#CE6723;
}

.btn-outline-danger {
  border:1px solid rgba(231,76,60,.45);
  color:#e74c3c;
}

.btn-outline-danger:hover {
  background:rgba(231,76,60,.12);
  border-color:#e74c3c;
  color:#fff;
}

.table {
  color:#fff;
  margin-bottom: 0;
}

.table thead th {
  color:#fff;
  border-bottom:2px solid #CE6723;
  background: rgba(30,30,30,.92);
  white-space: nowrap;
}

.table tbody td {
  color:#fff;
  border-color:#333;
  background: rgba(20,20,20,.72);
  vertical-align: middle;
}

.table-hover tbody tr:hover > * {
  background: rgba(206,103,35,.08) !important;
  color: #fff !important;
}

.badge.bg-secondary {
  background: rgba(255,255,255,.12) !important;
  color: #ddd !important;
  border: 1px solid rgba(255,255,255,.14);
}

.badge.bg-success {
  background: rgba(46,204,113,.18) !important;
  color: #2ecc71 !important;
  border: 1px solid rgba(46,204,113,.24);
}

.badge.bg-warning {
  background: rgba(255,193,7,.16) !important;
  color: #ffc107 !important;
  border: 1px solid rgba(255,193,7,.22);
}

.badge.bg-danger {
  background: rgba(231,76,60,.16) !important;
  color: #e74c3c !important;
  border: 1px solid rgba(231,76,60,.22);
}

.badge.bg-primary {
  background: rgba(206,103,35,.18) !important;
  color: #ffb27d !important;
  border: 1px solid rgba(206,103,35,.24);
}

.dropdown-menu {
  background:#111 !important;
  border:1px solid #333 !important;
}

.dropdown-item {
  color:#ddd !important;
}

.dropdown-item:hover {
  background:rgba(206,103,35,.1) !important;
  color:#fff !important;
}

.dropdown-divider {
  border-top:1px solid #333 !important;
}

.modal-content {
  background:#111;
  color:#fff;
  border:1px solid #333;
}

.modal-header,
.modal-footer {
  border-color:#333;
}

.btn-close {
  filter: invert(1);
}

.border.rounded.p-3 {
  background: rgba(255,255,255,.04);
  border-color:#333 !important;
  color:#fff;
}

.stats-card-value {
  font-size: 2rem;
  font-weight: 700;
  line-height: 1;
}

.empty-state i {
  color: #CE6723 !important;
  opacity: .9;
}

@media (max-width: 768px) {
  .page-header h1 {
    font-size: 1.5rem;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded',function(){
  const navbarBrand=document.querySelector('.navbar-brand');
  if(navbarBrand && !navbarBrand.querySelector('img')){
      const logo=document.createElement('img');
      logo.src='/img/logo.svg';
      logo.alt='Admin Panel Logo';
      logo.style.height='35px';
      logo.style.marginRight='10px';

      const text=navbarBrand.textContent.trim();
      navbarBrand.innerHTML='';
      navbarBrand.appendChild(logo);
      navbarBrand.appendChild(document.createTextNode(text));
  }
});
</script>

<div class="page-header">
    <h1>Forum Replies Management</h1>
    <div class="btn-group">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Posts
        </a>
        <a href="categories.php" class="btn btn-outline-primary">
            <i class="fas fa-tags me-1"></i> Manage Categories
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <h5 class="card-title stats-card-value text-primary"><?php echo $stats['total_replies']; ?></h5>
                <p class="card-text">Total Replies</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <h5 class="card-title stats-card-value text-success"><?php echo $stats['active_replies']; ?></h5>
                <p class="card-text">Active Replies</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <h5 class="card-title stats-card-value text-warning"><?php echo $stats['moderated_replies']; ?></h5>
                <p class="card-text">Moderated</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <h5 class="card-title stats-card-value text-danger"><?php echo $stats['deleted_replies']; ?></h5>
                <p class="card-text">Deleted</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="<?php echo e($search); ?>" placeholder="Search replies or authors...">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="moderated" <?php echo $status === 'moderated' ? 'selected' : ''; ?>>Moderated</option>
                    <option value="deleted" <?php echo $status === 'deleted' ? 'selected' : ''; ?>>Deleted</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="post_id" class="form-label">Post ID</label>
                <input type="number" class="form-control" id="post_id" name="post_id"
                       value="<?php echo e($post_id); ?>" placeholder="Filter by post ID">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Replies Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($replies)): ?>
            <div class="text-center py-5 empty-state">
                <i class="fas fa-comments fa-3x text-secondary mb-3"></i>
                <h5>No replies found</h5>
                <p class="text-secondary">
                    <?php if ($search || $status || $post_id): ?>
                        Try adjusting your filters or search terms.
                    <?php else: ?>
                        Replies will appear here as users engage with forum posts.
                    <?php endif; ?>
                </p>
                <?php if ($search || $status || $post_id): ?>
                    <a href="replies.php" class="btn btn-outline-primary">Clear Filters</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Post</th>
                            <th>Author</th>
                            <th>Content Preview</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($replies as $reply): ?>
                        <?php
                            $replyId = (int)($reply['id'] ?? 0);
                            $replyPostId = (int)($reply['post_id'] ?? 0);
                            $replyAuthor = $reply['author'] ?? 'Unknown';
                            $replyContent = $reply['content'] ?? '';
                            $replyStatus = $reply['status'] ?? 'deleted';
                            $replyPostTitle = $reply['post_title'] ?: 'Untitled Post';
                            $replyCategory = $reply['category_name'] ?: 'Uncategorized';
                            $replyDate = $reply['formatted_date'] ?: (!empty($reply['created_at']) ? date('F d, Y \a\t h:i A', strtotime($reply['created_at'])) : 'N/A');
                        ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary">#<?php echo $replyId; ?></span>
                            </td>
                            <td>
                                <div>
                                    <a href="../../../forum-post.html?id=<?php echo $replyPostId; ?>"
                                       target="_blank" class="text-decoration-none text-white">
                                        <?php echo e(mb_strimwidth($replyPostTitle, 0, 40, '...')); ?>
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        ID: <?php echo $replyPostId; ?> •
                                        <?php echo e($replyCategory); ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo e($replyAuthor); ?></strong>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 300px;" title="<?php echo e($replyContent); ?>">
                                    <?php echo e(mb_strimwidth($replyContent, 0, 100, '...')); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?php
                                    echo $replyStatus === 'active' ? 'success' :
                                        ($replyStatus === 'moderated' ? 'warning' : 'danger');
                                ?>">
                                    <?php echo ucfirst($replyStatus); ?>
                                </span>
                            </td>
                            <td>
                                <small><?php echo e($replyDate); ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#"
                                               onclick='viewReply(<?php echo json_encode([
                                                   "author" => $replyAuthor,
                                                   "formatted_date" => $replyDate,
                                                   "post_title" => $replyPostTitle,
                                                   "status" => $replyStatus,
                                                   "content" => $replyContent
                                               ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP); ?>)'>
                                                <i class="fas fa-eye me-1"></i> View Full Content
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateReplyStatus(<?php echo $replyId; ?>, 'active')">
                                            <i class="fas fa-check me-1"></i> Mark Active
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateReplyStatus(<?php echo $replyId; ?>, 'moderated')">
                                            <i class="fas fa-flag me-1"></i> Mark Moderated
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateReplyStatus(<?php echo $replyId; ?>, 'deleted')">
                                            <i class="fas fa-trash me-1"></i> Mark Deleted
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteReply(<?php echo $replyId; ?>, '<?php echo e($replyAuthor); ?>')">
                                            <i class="fas fa-times me-1"></i> Delete Permanently
                                        </a></li>
                                    </ul>
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

<!-- View Reply Modal -->
<div class="modal fade" id="viewReplyModal" tabindex="-1" aria-labelledby="viewReplyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reply Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Author:</strong> <span id="modal-author"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Date:</strong> <span id="modal-date"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Post:</strong> <span id="modal-post"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong> <span id="modal-status"></span>
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Full Content:</strong>
                    <div class="border rounded p-3 mt-2" id="modal-content"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewReply(reply) {
    document.getElementById('modal-author').textContent = reply.author || '';
    document.getElementById('modal-date').textContent = reply.formatted_date || '';
    document.getElementById('modal-post').textContent = reply.post_title || '';
    document.getElementById('modal-status').innerHTML =
        `<span class="badge bg-${reply.status === 'active' ? 'success' : (reply.status === 'moderated' ? 'warning' : 'danger')}">${(reply.status || '').charAt(0).toUpperCase() + (reply.status || '').slice(1)}</span>`;
    document.getElementById('modal-content').textContent = reply.content || '';

    new bootstrap.Modal(document.getElementById('viewReplyModal')).show();
}

function updateReplyStatus(id, status) {
    if (confirm(`Are you sure you want to mark this reply as ${status}?`)) {
        submitForm('update_status', {id: id, status: status});
    }
}

function deleteReply(id, author) {
    if (confirm(`Are you sure you want to permanently delete the reply by "${author}"? This action cannot be undone.`)) {
        submitForm('delete', {id: id});
    }
}

function submitForm(action, data) {
    const form = document.createElement('form');
    form.method = 'POST';

    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = action;
    form.appendChild(actionInput);

    for (const [key, value] of Object.entries(data)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>