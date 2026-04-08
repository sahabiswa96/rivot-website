<?php
$pageTitle = 'Forum Categories Management';
require_once __DIR__ . '/../includes/auth.php';
$pdo = get_pdo();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fas fa-comments');
        $color = trim($_POST['color'] ?? '#CE6723');
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        $errors = [];

        if (empty($name)) {
            $errors[] = 'Category name is required';
        }
        if (empty($description)) {
            $errors[] = 'Description is required';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO forum_categories (name, description, icon, color, sort_order)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $description, $icon, $color, $sort_order]);

                set_flash('success', 'Category created successfully!');
                header('Location: categories.php');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Error creating category: ' . $e->getMessage();
            }
        }

        foreach ($errors as $error) {
            set_flash('danger', $error);
        }
    }

    elseif ($action === 'update') {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fas fa-comments');
        $color = trim($_POST['color'] ?? '#CE6723');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];

        if (!$id || !is_numeric($id)) {
            $errors[] = 'Invalid category ID';
        }
        if (empty($name)) {
            $errors[] = 'Category name is required';
        }
        if (empty($description)) {
            $errors[] = 'Description is required';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE forum_categories
                    SET name = ?, description = ?, icon = ?, color = ?, sort_order = ?, is_active = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$name, $description, $icon, $color, $sort_order, $is_active, $id]);

                set_flash('success', 'Category updated successfully!');
                header('Location: categories.php');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Error updating category: ' . $e->getMessage();
            }
        }

        foreach ($errors as $error) {
            set_flash('danger', $error);
        }
    }

    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';

        if ($id && is_numeric($id)) {
            try {
                $checkStmt = $pdo->prepare("SELECT COUNT(*) as post_count FROM forum_posts WHERE category_id = ?");
                $checkStmt->execute([$id]);
                $postCount = $checkStmt->fetch()['post_count'];

                if ($postCount > 0) {
                    set_flash('danger', "Cannot delete category with {$postCount} existing posts. Move or delete posts first.");
                } else {
                    $stmt = $pdo->prepare("DELETE FROM forum_categories WHERE id = ?");
                    $stmt->execute([$id]);
                    set_flash('success', 'Category deleted successfully!');
                }
            } catch (Exception $e) {
                set_flash('danger', 'Error deleting category: ' . $e->getMessage());
            }
        } else {
            set_flash('danger', 'Invalid category ID');
        }

        header('Location: categories.php');
        exit;
    }
}

// Get all categories with post counts
$stmt = $pdo->query("
    SELECT
        c.*,
        COUNT(p.id) as post_count
    FROM forum_categories c
    LEFT JOIN forum_posts p ON c.id = p.category_id
    GROUP BY c.id
    ORDER BY c.sort_order ASC, c.name ASC
");
$categories = $stmt->fetchAll();
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
  .btn-outline-primary {
    border: 1px solid #CE6723;
    color: #CE6723;
  }

  .btn-outline-primary:hover {
    background: rgba(206, 103, 35, 0.12);
    border-color: #CE6723;
    color: #fff;
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

  .modal-content {
    background: rgba(30, 30, 30, 0.96);
    border: 1px solid #333;
    color: #fff;
  }

  .modal-header,
  .modal-footer {
    border-color: #333;
  }

  .btn-close {
    filter: invert(1);
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
    <h1>Forum Categories Management</h1>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="fas fa-plus me-1"></i> Add New Category
        </button>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Category
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($categories)): ?>
            <div class="text-center py-5">
                <i class="fas fa-list fa-3x text-secondary mb-3"></i>
                <h5>No categories found</h5>
                <p class="text-secondary">Create your first forum category to get started.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                    <i class="fas fa-plus me-1"></i> Create Category
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Posts</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary"><?php echo $category['sort_order']; ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="<?php echo e($category['icon']); ?> fa-lg me-3"
                                       style="color: <?php echo e($category['color']); ?>"></i>
                                    <div>
                                        <div class="fw-semibold"><?php echo e($category['name']); ?></div>
                                        <small class="text-muted">Icon: <?php echo e($category['icon']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 300px;" title="<?php echo e($category['description']); ?>">
                                    <?php echo e($category['description']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo $category['post_count']; ?> posts</span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $category['is_active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary"
                                            onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo e($category['name']); ?>', <?php echo $category['post_count']; ?>)"
                                            title="Delete">
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

<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCategoryModalLabel">Create New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="create_name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="create_description" class="form-label">Description *</label>
                        <textarea class="form-control" id="create_description" name="description" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="create_icon" class="form-label">Icon</label>
                                <input type="text" class="form-control" id="create_icon" name="icon"
                                       value="fas fa-comments" placeholder="e.g., fas fa-comments">
                                <div class="form-text">Font Awesome icon class</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="create_color" class="form-label">Color</label>
                                <input type="color" class="form-control form-control-color" id="create_color" name="color" value="#CE6723">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="create_sort_order" class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="create_sort_order" name="sort_order"
                               value="<?php echo count($categories) + 1; ?>" min="0">
                        <div class="form-text">Lower numbers appear first</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description *</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_icon" class="form-label">Icon</label>
                                <input type="text" class="form-control" id="edit_icon" name="icon" placeholder="e.g., fas fa-comments">
                                <div class="form-text">Font Awesome icon class</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_color" class="form-label">Color</label>
                                <input type="color" class="form-control form-control-color" id="edit_color" name="color">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="edit_sort_order" name="sort_order" min="0">
                                <div class="form-text">Lower numbers appear first</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                                    <label class="form-check-label" for="edit_is_active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

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

function editCategory(category) {
    document.getElementById('edit_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_description').value = category.description;
    document.getElementById('edit_icon').value = category.icon;
    document.getElementById('edit_color').value = category.color;
    document.getElementById('edit_sort_order').value = category.sort_order;
    document.getElementById('edit_is_active').checked = category.is_active == 1;

    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}

function deleteCategory(id, name, postCount) {
    if (postCount > 0) {
        if (confirm(`Category "${name}" has ${postCount} posts. Deleting it will also delete all associated posts. Are you sure?`)) {
            submitDeleteForm(id);
        }
    } else {
        if (confirm(`Are you sure you want to delete the category "${name}"?`)) {
            submitDeleteForm(id);
        }
    }
}

function submitDeleteForm(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="${id}">
    `;
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>