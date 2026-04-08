<?php
// includes/sidebar.php
// Uses $APP_BASE and url_for() defined in header.php

// Safety: if header wasn't included for some reason, define fallbacks
if (!function_exists('url_for')) {
  $APP_BASE = '/site_admin';
  function url_for(string $path): string {
    global $APP_BASE;
    return $APP_BASE . '/' . ltrim($path, '/');
  }
}

/** Active state helper */
if (!function_exists('is_active')) {
  function is_active($paths): string {
    $current = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
    $current = rtrim($current, '/');
    foreach ((array)$paths as $p) {
      $target = rtrim(url_for($p), '/');
      if ($current === $target || str_starts_with($current, $target . '/')) {
        return 'active';
      }
    }
    return '';
  }
}
?>
<aside class="sidebar bg-dark text-white p-3 d-flex flex-column flex-shrink-0">
  <div class="d-flex align-items-center gap-2 mb-4">
    <i class="fa-solid fa-gauge-high"></i>
    <span class="fw-semibold">Dashboard</span>
  </div>

  <ul class="nav nav-pills flex-column gap-1">
    <li class="nav-item">
      <a class="nav-link <?php echo is_active(['', 'index.php']); ?>"
         href="<?php echo htmlspecialchars(url_for('index.php')); ?>">
        <i class="fa-solid fa-house me-2"></i> Home
      </a>
    </li>

    <li class="nav-item mt-2 text-uppercase text-secondary small px-2">Manage</li>

    <li class="nav-item">
      <a class="nav-link <?php echo is_active(['items/index.php','items/create.php','items/edit.php','items/view.php']); ?>"
         href="<?php echo htmlspecialchars(url_for('items/index.php')); ?>">
        <i class="fa-solid fa-boxes-stacked me-2"></i> Orders
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link <?php echo is_active(['blog/index.php','blog/create.php','blog/edit.php','blog/view.php']); ?>"
         href="<?php echo htmlspecialchars(url_for('blog/index.php')); ?>">
        <i class="fas fa-blog me-2"></i> Blog Management
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link <?php echo is_active(['forum/index.php','forum/create.php','forum/edit.php','forum/view.php','forum/categories.php']); ?>"
         href="<?php echo htmlspecialchars(url_for('forum/index.php')); ?>">
        <i class="fas fa-comments me-2"></i> Forum Management
      </a>
    </li>

    <li class="nav-item mt-2 text-uppercase text-secondary small px-2">System</li>

    <li class="nav-item">
      <a class="nav-link <?php echo is_active(['settings/index.php']); ?>"
         href="<?php echo htmlspecialchars(url_for('settings/index.php')); ?>">
        <i class="fa-solid fa-gear me-2"></i> Settings
      </a>
    </li>
  </ul>
</aside>
