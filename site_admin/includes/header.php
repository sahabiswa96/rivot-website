<?php
// includes/header.php
// Expected variables: $pageTitle (optional)

// === App base (change only if you move the app) ===
$APP_BASE = '/site_admin';

// Helper to build URLs under /site_admin (define once here)
if (!function_exists('url_for')) {
  function url_for(string $path): string {
    global $APP_BASE;
    return $APP_BASE . '/' . ltrim($path, '/');
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo isset($pageTitle) ? e($pageTitle) . ' | ' : ''; ?>Admin Panel</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <!-- Global CSS (base-aware) -->
  <link rel="stylesheet" href="<?php echo htmlspecialchars(url_for('assets/css/styles.css')); ?>">

  <!-- Full-height layout -->
  <style>
    html, body { height: 100%; }
    #app { min-height: 100vh; }
    .sidebar { min-height: 100vh; width: 240px; }
    @media (max-width: 991.98px) { .sidebar { width: 220px; } }
  </style>
</head>
<body>
  <div class="d-flex" id="app">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="flex-grow-1">
      <nav class="navbar navbar-expand-lg bg-body-tertiary shadow-sm">
        <div class="container-fluid">
          <a class="navbar-brand fw-semibold" href="<?php echo htmlspecialchars(url_for('index.php')); ?>">
            Admin Panel
          </a>
          <div class="d-flex align-items-center gap-3">
            <span class="text-secondary small d-none d-md-inline">
              Signed in as <?php echo e(current_user()['name'] ?? ''); ?>
            </span>
            <!-- Correct logout path under /site_admin -->
            <a class="btn btn-outline-danger btn-sm" href="<?php echo htmlspecialchars(url_for('logout.php')); ?>">
              <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
          </div>
        </div>
      </nav>

      <div class="container py-4">
        <?php display_flash(); ?>
