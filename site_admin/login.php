<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
verify_csrf();

$pdo = get_pdo();
$errors = [];
$hasUsers = null;

try {
    $stmt = $pdo->query("SELECT COUNT(*) AS c FROM users");
    $hasUsers = (int)$stmt->fetch()['c'] > 0;
} catch (Throwable $e) {
    $errors[] = "Database not initialized. Please import database.sql into your MySQL server first.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'setup') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($name === '' || $email === '' || $password === '' || $confirm === '') {
            $errors[] = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        } elseif ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (!$errors) {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :hash)");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':hash' => $hash
                ]);

                $userId = (int)$pdo->lastInsertId();
                $_SESSION['user'] = [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email
                ];
                session_regenerate_id(true);

                header('Location: index.php');
                exit;
            } catch (Throwable $e) {
                $errors[] = 'Could not create admin user: ' . e($e->getMessage());
            }
        }
    } elseif ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $errors[] = 'Email and password are required.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email']
                    ];
                    session_regenerate_id(true);

                    header('Location: index.php');
                    exit;
                } else {
                    $errors[] = 'Invalid credentials.';
                }
            } catch (Throwable $e) {
                $errors[] = 'Login failed: ' . e($e->getMessage());
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Admin Panel</title>

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Montserrat", sans-serif;
      background-color: #000;
      color: #fff;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
    }

    .login-container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      position: relative;
    }

    .login-container::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(circle at 70% 30%, rgba(206, 103, 35, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 30% 70%, rgba(206, 103, 35, 0.08) 0%, transparent 50%);
      z-index: 1;
    }

    .login-card {
      background: rgba(30, 30, 30, 0.8);
      border: 1px solid #333;
      border-radius: 12px;
      padding: 3rem;
      width: 100%;
      max-width: 420px;
      backdrop-filter: blur(10px);
      position: relative;
      z-index: 2;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .login-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, #CE6723, #e07a3a);
      border-radius: 12px 12px 0 0;
    }

    .login-title {
      font-size: 28px;
      font-weight: 600;
      margin-bottom: 2rem;
      text-align: center;
      color: #fff;
      letter-spacing: 1px;
    }

    .login-title .highlight {
      color: #CE6723;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-label {
      display: block;
      margin-bottom: 0.7rem;
      color: #fff;
      font-weight: 500;
      font-size: 15px;
    }

    .form-control {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #444;
      background: rgba(255, 255, 255, 0.1);
      color: white;
      font-size: 16px;
      border-radius: 4px;
      font-family: "Montserrat", sans-serif;
      transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-control:focus {
      outline: none;
      border-color: #CE6723;
      box-shadow: 0 0 5px rgba(206, 103, 35, 0.5);
      background: rgba(255, 255, 255, 0.1);
    }

    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.7);
    }

    .password-wrap {
      position: relative;
    }

    .password-wrap .form-control {
      padding-right: 72px;
    }

    .password-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: transparent;
      border: none;
      color: #bbb;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      padding: 4px 8px;
      border-radius: 4px;
      transition: color 0.3s ease, background 0.3s ease;
    }

    .password-toggle:hover {
      color: #fff;
      background: rgba(255, 255, 255, 0.06);
    }

    .btn {
      display: block;
      width: 100%;
      padding: 15px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 4px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      border: none;
      font-family: "Montserrat", sans-serif;
    }

    .btn-primary {
      background: linear-gradient(135deg, #CE6723 0%, #e07a3a 100%);
      color: white;
      box-shadow: 0 5px 15px rgba(206, 103, 35, 0);
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(206, 103, 35, 0.06);
    }

    .alert {
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 1.5rem;
    }

    .alert-danger {
      background: rgba(220, 53, 69, 0.2);
      border: 1px solid #dc3545;
      color: #dc3545;
    }

    .alert ul {
      margin: 0;
      padding-left: 20px;
    }

    .alert li {
      margin-bottom: 5px;
    }

    .text-secondary {
      color: #ccc;
      font-size: 14px;
      text-align: center;
      margin-bottom: 1.5rem;
    }

    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
      -webkit-box-shadow: 0 0 0 30px rgba(255, 255, 255, 0.1) inset !important;
      -webkit-text-fill-color: white !important;
      transition: background-color 5000s ease-in-out 0s;
      background-color: rgba(255, 255, 255, 0.1) !important;
    }

    @media (max-width: 480px) {
      .login-card {
        padding: 2rem;
      }

      .login-title {
        font-size: 24px;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <h1 class="login-title">Admin <span class="highlight">Panel</span></h1>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?php echo e($e); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($hasUsers === false): ?>
        <p class="text-secondary">No users found. Create the first admin account.</p>

        <form method="post" novalidate>
          <?php csrf_field(); ?>
          <input type="hidden" name="action" value="setup">

          <div class="form-group">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" placeholder="Your full name" required>
          </div>

          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" placeholder="your.email@example.com" required>
          </div>

          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="password-wrap">
              <input type="password" class="form-control" id="setupPassword" name="password" placeholder="Create a password" required>
              <button type="button" class="password-toggle" onclick="togglePassword('setupPassword', this)">Show</button>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <div class="password-wrap">
              <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm your password" required>
              <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', this)">Show</button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Create Admin</button>
        </form>

      <?php elseif ($hasUsers === true): ?>

        <form method="post" novalidate>
          <?php csrf_field(); ?>
          <input type="hidden" name="action" value="login">

          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" placeholder="your.email@example.com" required>
          </div>

          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="password-wrap">
              <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Enter your password" required>
              <button type="button" class="password-toggle" onclick="togglePassword('loginPassword', this)">Show</button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Login</button>
        </form>

      <?php else: ?>
        <p class="text-secondary">Please initialize the database first.</p>
      <?php endif; ?>
    </div>
  </div>

  <script>
    function togglePassword(inputId, btn) {
      const input = document.getElementById(inputId);
      if (!input) return;

      if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Hide';
      } else {
        input.type = 'password';
        btn.textContent = 'Show';
      }
    }
  </script>
</body>
</html>