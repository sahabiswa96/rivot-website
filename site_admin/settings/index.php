<?php
// settings/index.php - Settings Management
 $pageTitle = 'Settings';
require_once __DIR__ . '/../includes/auth.php';
 $pdo = get_pdo();

// Define url_for() helper if not already defined
 $APP_BASE = '/site_admin';
if (!function_exists('url_for')) {
    function url_for(string $path): string {
        global $APP_BASE;
        return $APP_BASE . '/' . ltrim($path, '/');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_type = $_POST['form_type'] ?? '';

    // Handle Email Settings Form
    if ($form_type === 'email_settings') {
        $admin_email = trim($_POST['admin_email'] ?? '');

        // Validate email
        if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            set_flash('danger', 'Please enter a valid email address.');
        } else {
            try {
                // Update or insert admin email setting
                $stmt = $pdo->prepare("
                    INSERT INTO settings (setting_key, setting_value, description)
                    VALUES ('admin_email', :email, 'Admin email address for receiving notifications')
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute(['email' => $admin_email]);

                set_flash('success', 'Email settings updated successfully!');
                header('Location: ' . url_for('settings/index.php'));
                exit;
            } catch (PDOException $e) {
                set_flash('danger', 'Failed to update settings: ' . htmlspecialchars($e->getMessage()));
            }
        }
    }

    // Handle Login Credentials Form
    if ($form_type === 'login_credentials') {
        $user = current_user();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $errors = [];

        // Validate required fields
        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (empty($current_password)) {
            $errors[] = 'Current password is required.';
        }

        // If no errors so far, verify current password
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
                $stmt->execute(['id' => $user['id']]);
                $userRecord = $stmt->fetch();

                if (!$userRecord || !password_verify($current_password, $userRecord['password_hash'])) {
                    $errors[] = 'Current password is incorrect.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . htmlspecialchars($e->getMessage());
            }
        }

        // Validate new password if provided
        if (!empty($new_password) || !empty($confirm_password)) {
            if (strlen($new_password) < 6) {
                $errors[] = 'New password must be at least 6 characters long.';
            }
            if ($new_password !== $confirm_password) {
                $errors[] = 'New passwords do not match.';
            }
        }

        // If no errors, update credentials
        if (empty($errors)) {
            try {
                if (!empty($new_password)) {
                    // Update name, email, and password
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET name = :name, email = :email, password_hash = :password_hash
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'name' => $name,
                        'email' => $email,
                        'password_hash' => $password_hash,
                        'id' => $user['id']
                    ]);
                    set_flash('success', 'Login credentials and password updated successfully!');
                } else {
                    // Update only name and email
                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET name = :name, email = :email
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'name' => $name,
                        'email' => $email,
                        'id' => $user['id']
                    ]);
                    set_flash('success', 'Login credentials updated successfully!');
                }

                // Update session with new user info
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;

                header('Location: ' . url_for('settings/index.php'));
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Failed to update credentials: ' . htmlspecialchars($e->getMessage());
            }
        }

        // Display errors
        if (!empty($errors)) {
            foreach ($errors as $error) {
                set_flash('danger', $error);
            }
        }
    }
}

// Fetch current settings
 $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
 $settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

 $admin_email = $settings['admin_email'] ?? '';

// Fetch current user data for login credentials form
 $user = current_user();
 $current_user_name = $user['name'] ?? '';
 $current_user_email = $user['email'] ?? '';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
  /* Base dark theme - Copied from dashboard */
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

  /* Navbar Styling - RIVOT Design - Copied from dashboard */
  .navbar {
    background-color: #000 !important;
    border-bottom: 1px solid #333 !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3) !important;
    padding: 0.75rem 0 !important;
    height: 70px; /* Fixed height to match dashboard */
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
  
  .navbar-nav .nav-link {
    color: rgba(255, 255, 255, 0.8) !important;
    font-weight: 500;
    padding: 0.5rem 1rem !important;
    margin: 0 0.25rem;
    border-radius: 6px;
    transition: all 0.3s ease !important;
  }
  
  .navbar-nav .nav-link:hover,
  .navbar-nav .nav-link.active {
    color: #fff !important;
    background-color: rgba(206, 103, 35, 0.1) !important;
  }
  
  .navbar-nav .nav-link i {
    margin-right: 0.5rem;
    font-size: 1.1rem;
  }
  
  /* User menu styling - Copied from dashboard */
  .navbar .dropdown-menu {
    background-color: #1a1a1a !important;
    border: 1px solid #333 !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
    border-radius: 8px !important;
    margin-top: 0.5rem !important;
  }
  
  .navbar .dropdown-item {
    color: rgba(255, 255, 255, 0.8) !important;
    padding: 0.75rem 1.25rem !important;
    transition: all 0.3s ease !important;
  }
  
  .navbar .dropdown-item:hover {
    background-color: rgba(206, 103, 35, 0.1) !important;
    color: #fff !important;
  }
  
  .navbar .dropdown-item i {
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
  }
  
  .navbar .dropdown-divider {
    border-color: #333 !important;
    margin: 0.5rem 0 !important;
  }
  
  /* Sidebar Styling - Copied from dashboard */
  .sidebar {
    background-color: #111 !important;
    border-right: 1px solid #333 !important;
  }
  
  .sidebar-logo {
    padding: 1.5rem;
    text-align: center;
    border-bottom: 1px solid #333;
    margin-bottom: 1rem;
    background-color: #000;
  }
  
  .sidebar-logo a {
    display: inline-block;
    text-decoration: none;
    transition: transform 0.3s ease;
  }
  
  .sidebar-logo a:hover {
    transform: scale(1.05);
  }
  
  .sidebar-logo img {
    height: 50px;
    width: auto;
    max-width: 100%;
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
  
  /* Dashboard container - Copied from dashboard */
  .dash-fill {
    min-height: calc(100vh - 180px);
    padding: 2rem;
    position: relative;
  }
  
  .dash-fill::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at 70% 30%, rgba(206, 103, 35, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 30% 70%, rgba(206, 103, 35, 0.05) 0%, transparent 50%);
    z-index: -1;
  }
  
  /* Card styling - Copied from dashboard */
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
  
  .card-header {
    background-color: rgba(20, 20, 20, 0.8) !important;
    border-bottom: 1px solid #333 !important;
  }
  
  /* Section headers - Copied from dashboard */
  .section-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
  }
  
  .section-header i {
    color: #CE6723;
    margin-right: 0.75rem;
    font-size: 1.25rem;
  }
  
  .section-header h5 {
    color: #fff;
    font-weight: 600;
    margin: 0;
  }
  
  /* Buttons - Copied from dashboard */
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
  }
  
  .btn-outline-secondary {
    background: transparent;
    border: 1px solid #444;
    color: #ccc;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 6px;
    transition: all 0.3s ease;
  }
  
  .btn-outline-secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: #CE6723;
    color: #fff;
  }
  
  .btn-success {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    border: none;
    color: white;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 6px;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
  }
  
  .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(46, 204, 113, 0.4);
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
  }
  
  /* Form styling for dark theme */
  .form-label {
    color: #ccc;
    font-weight: 500;
    margin-bottom: 0.5rem;
  }
  
  .form-control {
    background-color: rgba(30, 30, 30, 0.8) !important;
    border: 1px solid #444 !important;
    color: #fff !important;
    border-radius: 6px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
  }
  
  .form-control:focus {
    background-color: rgba(40, 40, 40, 0.9) !important;
    border-color: #CE6723 !important;
    color: #fff !important;
    box-shadow: 0 0 0 0.25rem rgba(206, 103, 35, 0.25);
  }
  
  .form-control::placeholder {
    color: #777;
  }
  
  .form-text {
    color: #aaa;
    font-size: 0.875rem;
  }
  
  /* Alert styling */
  .alert {
    border-radius: 8px;
    border: none;
  }
  
  .alert-warning {
    background-color: rgba(241, 196, 15, 0.2);
    color: #f1c40f;
    border: 1px solid rgba(241, 196, 15, 0.3);
  }
  
  .alert-danger {
    background-color: rgba(231, 76, 60, 0.2);
    color: #e74c3c;
    border: 1px solid rgba(231, 76, 60, 0.3);
  }
  
  /* Settings specific styling */
  .settings-card {
    border: 4px solid #333;
    background: rgba(30, 30, 30, 0.8);
    border-radius: 12px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
  }
  
  .settings-card:hover {
    box-shadow: 0 10px 30px rgba(206, 103, 35, 0.2);
    border-color: #CE6723;
  }
  
  .section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #fff;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
  }
  
  .section-title i {
    color: #CE6723;
    margin-right: 0.5rem;
  }
  
  .section-description {
    font-size: 0.9rem;
    color: #aaa;
    margin-bottom: 1rem;
  }
  
  .password-match-indicator {
    font-size: 0.875rem;
    margin-top: 0.25rem;
  }
  
  .password-match-indicator.text-success {
    color: #2ecc71 !important;
  }
  
  .password-match-indicator.text-danger {
    color: #e74c3c !important;
  }
  
  /* Border styling */
  .border-bottom {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
  }
  
  hr {
    border-color: rgba(255, 255, 255, 0.1);
  }
  
  /* Info card styling */
  .bg-light {
    background-color: rgba(30, 30, 30, 0.6) !important;
    border: 1px solid #333;
  }
  
  .card-title {
    color: #fff;
  }
  
  .card-text {
    color: #aaa;
  }
  
  .text-secondary {
    color: #aaa !important;
  }
  
  .text-info {
    color: #3498db !important;
  }
  
  .text-warning {
    color: #f1c40f !important;
  }
  
  .text-danger {
    color: #e74c3c !important;
  }
  
  .fw-semibold {
    color: #fff;
  }
  
  /* Mobile responsiveness */
  @media (max-width: 768px) {
    .card-body {
      padding: 1rem;
    }
    
    .dash-fill {
      padding: 1rem;
    }
    
    /* Hide sidebar on mobile and show mobile menu */
    .sidebar {
      display: none;
    }
    
    .mobile-menu-toggle {
      display: block !important;
    }
    
    .navbar-toggler {
      display: block !important;
      border: none;
      padding: 0.25rem 0.5rem;
      background: transparent !important;
    }
    
    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255,255,255,0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
    }
  }
  
  @media (min-width: 769px) {
    .mobile-menu-toggle {
      display: none !important;
    }
    
    .navbar-toggler {
      display: none !important;
    }
  }
</style>

<!-- Script to add logo to navbar - Copied from dashboard -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Find the navbar brand element
    var navbarBrand = document.querySelector('.navbar-brand');
    if (navbarBrand) {
        // Create logo image element
        var logo = document.createElement('img');
        logo.src = '/img/logo.svg'; // Updated logo path
        logo.alt = 'Admin Panel Logo';
        logo.style.height = '35px';
        logo.style.marginRight = '10px';
        
        // Get the original text content
        var originalText = navbarBrand.textContent;
        
        // Clear the brand element content
        navbarBrand.innerHTML = '';
        
        // Add the logo
        navbarBrand.appendChild(logo);
        
        // Add the original text
        navbarBrand.appendChild(document.createTextNode(originalText));
    }
    
    // Password matching validation
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');

    if (newPassword && confirmPassword) {
        // Create match indicator
        const matchIndicator = document.createElement('div');
        matchIndicator.className = 'password-match-indicator';
        confirmPassword.parentNode.appendChild(matchIndicator);

        function checkPasswordMatch() {
            const newPass = newPassword.value;
            const confirmPass = confirmPassword.value;

            if (confirmPass === '') {
                matchIndicator.textContent = '';
                matchIndicator.className = 'password-match-indicator';
            } else if (newPass === confirmPass) {
                matchIndicator.innerHTML = '<i class="fa-solid fa-check-circle me-1"></i> Passwords match';
                matchIndicator.className = 'password-match-indicator text-success';
            } else {
                matchIndicator.innerHTML = '<i class="fa-solid fa-times-circle me-1"></i> Passwords do not match';
                matchIndicator.className = 'password-match-indicator text-danger';
            }
        }

        newPassword.addEventListener('input', checkPasswordMatch);
        confirmPassword.addEventListener('input', checkPasswordMatch);
    }
});
</script>

<div class="dash-fill d-flex flex-column gap-4">
  <div class="section-header">
    <i class="fa-solid fa-gear"></i>
    <h5>Settings</h5>
  </div>

  <div class="row">
    <div class="col-12 col-lg-8">
      <div class="card settings-card">
        <div class="card-header border-0">
          <div class="d-flex align-items-center">
            <i class="fa-solid fa-gear me-2 text-warning"></i>
            <h4 class="mb-0 text-white">Application Settings</h4>
          </div>
        </div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="form_type" value="email_settings">

            <!-- Email Settings Section -->
            <div class="mb-4 pb-4 border-bottom">
              <div class="section-title">
                <i class="fa-solid fa-envelope me-2"></i>
                Email Configuration
              </div>
              <div class="section-description">
                Configure email addresses for system notifications and alerts.
              </div>

              <div class="row">
                <div class="col-12">
                  <label for="admin_email" class="form-label">
                    Admin Email Address
                    <span class="text-danger">*</span>
                  </label>
                  <input
                    type="email"
                    class="form-control"
                    id="admin_email"
                    name="admin_email"
                    value="<?php echo htmlspecialchars($admin_email); ?>"
                    required
                    placeholder="admin@example.com"
                  >
                  <div class="form-text">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    This email will receive test ride booking notifications and other system alerts.
                  </div>
                </div>
              </div>
            </div>

            <!-- Save Button for Email Settings -->
            <div class="d-flex justify-content-between align-items-center">
              <a href="<?php echo htmlspecialchars(url_for('index.php')); ?>" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk me-1"></i> Save Email Settings
              </button>
            </div>

          </form>

          <!-- Login Credentials Section (Separate Form) -->
          <hr class="my-4">

          <form method="POST">
            <input type="hidden" name="form_type" value="login_credentials">

            <div class="mb-4 pb-4 border-bottom">
              <div class="section-title">
                <i class="fa-solid fa-user-lock me-2"></i>
                Login Credentials
              </div>
              <div class="section-description">
                Update your admin account name, email, and password. Current password is required for security.
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="name" class="form-label">
                    Name
                    <span class="text-danger">*</span>
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    id="name"
                    name="name"
                    value="<?php echo htmlspecialchars($current_user_name); ?>"
                    required
                    placeholder="Your Name"
                  >
                </div>

                <div class="col-md-6 mb-3">
                  <label for="email" class="form-label">
                    Login Email
                    <span class="text-danger">*</span>
                  </label>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($current_user_email); ?>"
                    required
                    placeholder="admin@example.com"
                  >
                </div>
              </div>

              <div class="row">
                <div class="col-12 mb-3">
                  <label for="current_password" class="form-label">
                    Current Password
                    <span class="text-danger">*</span>
                  </label>
                  <input
                    type="password"
                    class="form-control"
                    id="current_password"
                    name="current_password"
                    required
                    placeholder="Enter current password to confirm changes"
                  >
                  <div class="form-text">
                    <i class="fa-solid fa-shield-halved me-1"></i>
                    Required to verify your identity before making changes.
                  </div>
                </div>
              </div>

              <div class="alert alert-warning" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <strong>Optional:</strong> Leave password fields empty if you don't want to change your password.
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="new_password" class="form-label">
                    New Password (Optional)
                  </label>
                  <input
                    type="password"
                    class="form-control"
                    id="new_password"
                    name="new_password"
                    placeholder="Enter new password"
                    minlength="6"
                  >
                  <div class="form-text">
                    <i class="fa-solid fa-info-circle me-1"></i>
                    Minimum 6 characters
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="confirm_password" class="form-label">
                    Confirm New Password
                  </label>
                  <input
                    type="password"
                    class="form-control"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Confirm new password"
                    minlength="6"
                  >
                </div>
              </div>
            </div>

            <!-- Save Button for Login Credentials -->
            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-key me-1"></i> Update Login Credentials
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>

    <!-- Sidebar with helpful info -->
    <div class="col-12 col-lg-4">
      <div class="card border-0 bg-light">
        <div class="card-body">
          <h5 class="card-title">
            <i class="fa-solid fa-lightbulb me-2 text-warning"></i>
            Help & Information
          </h5>
          <p class="card-text small text-secondary">
            Settings are stored securely in the database and can be updated at any time.
          </p>
          <hr>
          <div class="small">
            <div class="mb-3">
              <strong>Admin Email:</strong>
              <p class="text-secondary mb-0">Used for receiving test ride booking requests and system notifications.</p>
            </div>
            <div class="mb-2">
              <strong>Login Credentials:</strong>
              <p class="text-secondary mb-0">Manage your admin account details and password. Current password required for all changes.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Current Settings Info -->
      <div class="card border-0 bg-light mt-3">
        <div class="card-body">
          <h6 class="card-title">
            <i class="fa-solid fa-info-circle me-2 text-info"></i>
            Current Configuration
          </h6>
          <div class="small">
            <div class="d-flex justify-content-between mb-2">
              <span class="text-secondary">Notification Email:</span>
              <span class="fw-semibold"><?php echo $admin_email ? e($admin_email) : 'Not set'; ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-secondary">Admin Name:</span>
              <span class="fw-semibold"><?php echo $current_user_name ? e($current_user_name) : 'Not set'; ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-secondary">Login Email:</span>
              <span class="fw-semibold"><?php echo $current_user_email ? e($current_user_email) : 'Not set'; ?></span>
            </div>
            <div class="d-flex justify-content-between">
              <span class="text-secondary">Last Updated:</span>
              <span class="fw-semibold">
                <?php
                  $updated = $pdo->query("SELECT updated_at FROM settings WHERE setting_key = 'admin_email'")->fetch();
                  echo $updated ? date('M j, Y', strtotime($updated['updated_at'])) : 'Never';
                ?>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>