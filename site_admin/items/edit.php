<?php
 $pageTitle = 'Edit Order';
require_once __DIR__ . '/../includes/auth.php';
 $pdo = get_pdo();

 $id = (int)($_GET['id'] ?? 0);
 $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
 $stmt->execute([':id' => $id]);
 $item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    set_flash('danger', 'Order not found.');
    header('Location: index.php');
    exit;
}

 $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // ---- Collect inputs (trim where sensible) ----
    $price            = trim((string)($_POST['price'] ?? ''));           // decimal(10,2) NOT NULL
    $model            = trim($_POST['model'] ?? '');                     // varchar(100) NOT NULL
    $color            = trim($_POST['color'] ?? '');                     // varchar(100) NOT NULL
    $product_name     = trim($_POST['product_name'] ?? '');              // varchar(255) NOT NULL

    $trackId          = trim($_POST['trackId'] ?? '');                   // varchar(100) NOT NULL
    $orderId          = (string)($_POST['orderId'] ?? '');               // text NOT NULL
    $productDesc      = (string)($_POST['productDescription'] ?? '');    // text NOT NULL
    $transactionId    = (string)($_POST['transaction_id'] ?? '');        // text NOT NULL
    $amount           = (string)($_POST['amount'] ?? '');                // text NOT NULL
    $statid           = (string)($_POST['statid'] ?? '0');               // text (nullable in DB, we allow any string)

    $name             = trim($_POST['name'] ?? '');                      // varchar(100) NOT NULL
    $lastName         = trim($_POST['lastName'] ?? '');                  // varchar(100) NULL
    $mobile           = trim($_POST['mobile'] ?? '');                    // varchar(15)  NOT NULL
    $email            = trim($_POST['email'] ?? '');                     // varchar(255) NOT NULL
    $address          = trim($_POST['address'] ?? '');                   // text NULL

    $country          = trim($_POST['country'] ?? '');                   // varchar(100) NULL
    $pincode          = trim($_POST['pincode'] ?? '');                   // varchar(20)  NULL
    $state            = trim($_POST['state'] ?? '');                     // varchar(100) NULL
    $city             = trim($_POST['city'] ?? '');                      // varchar(100) NULL

    $source           = trim($_POST['source'] ?? '');                    // varchar(100) NULL
    $referralCode     = trim($_POST['referralCode'] ?? '');              // varchar(100) NULL

    $terms            = isset($_POST['terms']) ? 1 : 0;                  // tinyint(1) DEFAULT 0

    // ---- Minimal validation for required business fields ----
    if ($price === '' || !is_numeric($price)) $errors[] = 'Valid price is required.';
    if ($model === '')        $errors[] = 'Model is required.';
    if ($color === '')        $errors[] = 'Color is required.';
    if ($product_name === '') $errors[] = 'Product name is required.';
    if ($trackId === '')      $errors[] = 'Track ID is required.';
    if ($name === '')         $errors[] = 'First name is required.';
    if ($mobile === '')       $errors[] = 'Mobile is required.';
    if ($email === '')        $errors[] = 'Email is required.';

    if (!$errors) {
        try {
            $sql = "UPDATE orders SET
                        price = :price,
                        model = :model,
                        color = :color,
                        product_name = :product_name,
                        trackId = :trackId,
                        orderId = :orderId,
                        productDescription = :productDescription,
                        transaction_id = :transaction_id,
                        amount = :amount,
                        statid = :statid,
                        name = :name,
                        lastName = :lastName,
                        mobile = :mobile,
                        email = :email,
                        address = :address,
                        country = :country,
                        pincode = :pincode,
                        state = :state,
                        city = :city,
                        source = :source,
                        referralCode = :referralCode,
                        terms = :terms
                    WHERE id = :id
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':price'              => number_format((float)$price, 2, '.', ''), // decimal(10,2)
                ':model'              => $model,
                ':color'              => $color,
                ':product_name'       => $product_name,

                ':trackId'            => $trackId,
                ':orderId'            => $orderId,
                ':productDescription' => $productDesc,
                ':transaction_id'     => $transactionId,
                ':amount'             => $amount,
                ':statid'             => $statid,

                ':name'               => $name,
                // nullable fields -> pass NULL when blank
                ':lastName'           => ($lastName !== '' ? $lastName : null),
                ':mobile'             => $mobile,
                ':email'              => $email,
                ':address'            => ($address !== '' ? $address : null),

                ':country'            => ($country !== '' ? $country : null),
                ':pincode'            => ($pincode !== '' ? $pincode : null),
                ':state'              => ($state !== '' ? $state : null),
                ':city'               => ($city !== '' ? $city : null),

                ':source'             => ($source !== '' ? $source : null),
                ':referralCode'       => ($referralCode !== '' ? $referralCode : null),

                ':terms'              => (int)$terms,
                ':id'                 => $id,
            ]);

            set_flash('success', 'Order updated successfully.');
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Update failed: ' . e($e->getMessage());
        }
    }
}
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
  
  /* Override browser autofill styles - More robust approach */
  input:-webkit-autofill,
  input:-webkit-autofill:hover,
  input:-webkit-autofill:focus,
  input:-webkit-autofill:active {
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: #fff !important;
    transition: background-color 9999s ease-in-out 0s !important;
  }
  
  input:-webkit-autofill::first-line {
    color: #fff !important;
  }
  
  input:-webkit-autofill {
    background-color: rgba(30, 30, 30, 0.8) !important;
  }
  
  /* For Firefox */
  input:autofill,
  input:autofill:hover,
  input:autofill:focus,
  input:autofill:active {
    background-color: rgba(30, 30, 30, 0.8) !important;
    color: #fff !important;
  }
  
  /* For Edge */
  input:-ms-autofill,
  input:-ms-autofill:hover,
  input:-ms-autofill:focus,
  input:-ms-autofill:active {
    background-color: rgba(30, 30, 30, 0.8) !important;
    color: #fff !important;
  }
  
  /* Force override for all autofill states */
  @supports (-webkit-touch-callout: none) {
    input:-webkit-autofill {
      background-color: rgba(30, 30, 30, 0.8) !important;
      color: #fff !important;
    }
  }
  
  /* For all browsers */
  input[data-autofill] {
    background-color: rgba(30, 30, 30, 0.8) !important;
    color: #fff !important;
  }
  
  .form-check-input {
    background-color: rgba(30, 30, 30, 0.8) !important;
    border: 1px solid #444 !important;
  }
  
  .form-check-input:checked {
    background-color: #CE6723 !important;
    border-color: #CE6723 !important;
  }
  
  .form-check-label {
    color: #ccc;
  }
  
  /* Alert styling */
  .alert {
    border-radius: 8px;
    border: none;
  }
  
  .alert-danger {
    background-color: rgba(231, 76, 60, 0.2);
    color: #e74c3c;
    border: 1px solid rgba(231, 76, 60, 0.3);
  }
  
  .alert-danger ul {
    margin-bottom: 0;
    padding-left: 1.25rem;
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
    
    // Fix for autofill fields turning white - More robust approach
    function fixAutofill() {
        var inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="number"], input[type="tel"], input[type="password"]');
        
        inputs.forEach(function(input) {
            // Check if the input has been autofilled
            var isAutofilled = false;
            
            // Method 1: Check for autofill by value presence and browser-specific styles
            try {
                if (input.matches(':-webkit-autofill') || 
                    input.matches(':autofill') || 
                    input.matches(':-ms-autofill')) {
                    isAutofilled = true;
                }
            } catch (e) {
                // Some browsers don't support querying for autofill pseudo-class
            }
            
            // Method 2: Check if value exists but wasn't typed by user
            if (!isAutofilled && input.value !== '') {
                // Check if the input has the default autofill styling
                var computedStyle = window.getComputedStyle(input);
                if (computedStyle.backgroundColor === 'rgb(250, 255, 189)' || 
                    computedStyle.backgroundColor === 'rgb(232, 240, 254)' ||
                    computedStyle.backgroundColor === 'rgb(255, 250, 205)' ||
                    computedStyle.backgroundColor === 'rgb(255, 255, 255)') {
                    isAutofilled = true;
                }
            }
            
            // If autofilled, apply our dark theme
            if (isAutofilled) {
                input.style.backgroundColor = 'rgba(30, 30, 30, 0.8)';
                input.style.color = '#fff';
                input.setAttribute('data-autofill', 'true');
            }
            
            // Add event listeners to maintain dark theme
            input.addEventListener('paste', function() {
                setTimeout(function() {
                    input.style.backgroundColor = 'rgba(30, 30, 30, 0.8)';
                    input.style.color = '#fff';
                }, 0);
            });
            
            input.addEventListener('input', function() {
                input.style.backgroundColor = 'rgba(30, 30, 30, 0.8)';
                input.style.color = '#fff';
            });
            
            input.addEventListener('change', function() {
                input.style.backgroundColor = 'rgba(30, 30, 30, 0.8)';
                input.style.color = '#fff';
            });
        });
    }
    
    // Run the fix immediately
    fixAutofill();
    
    // Run again after a short delay to catch delayed autofill
    setTimeout(fixAutofill, 100);
    setTimeout(fixAutofill, 500);
    
    // Also run on focus events as autofill can trigger then
    document.addEventListener('focusin', function(e) {
        if (e.target.matches('input')) {
            setTimeout(fixAutofill, 10);
        }
    }, true);
    
    // Set up a MutationObserver to detect changes in input values
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                var input = mutation.target;
                if (input.tagName === 'INPUT') {
                    fixAutofill();
                }
            }
        });
    });
    
    // Observe all input elements
    var allInputs = document.querySelectorAll('input');
    allInputs.forEach(function(input) {
        observer.observe(input, { attributes: true });
    });
});
</script>

<div class="dash-fill d-flex flex-column gap-4">
  <div class="card activity-card">
    <div class="card-body">
      <div class="section-header">
        <i class="fa-solid fa-pen"></i>
        <h5>Edit Order</h5>
      </div>
      
      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <ul class="mb-0"><?php foreach ($errors as $e) echo '<li>'.e($e).'</li>'; ?></ul>
        </div>
      <?php endif; ?>

      <form method="post">
        <?php csrf_field(); ?>
        <div class="row g-3">
          <!-- price / model / color / product_name -->
          <div class="col-md-3">
            <label class="form-label">Price (₹)</label>
            <input type="number" step="0.01" class="form-control" name="price" value="<?php echo e($item['price']); ?>" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Model</label>
            <input class="form-control" name="model" value="<?php echo e($item['model']); ?>" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Color</label>
            <input class="form-control" name="color" value="<?php echo e($item['color']); ?>" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Product Name</label>
            <input class="form-control" name="product_name" value="<?php echo e($item['product_name']); ?>" required>
          </div>

          <!-- trackId / orderId / transaction_id -->
          <div class="col-md-4">
            <label class="form-label">Track ID</label>
            <input class="form-control" name="trackId" value="<?php echo e($item['trackId']); ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Order ID (gateway)</label>
            <input class="form-control" name="orderId" value="<?php echo e($item['orderId']); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Transaction ID</label>
            <input class="form-control" name="transaction_id" value="<?php echo e($item['transaction_id']); ?>">
          </div>

          <!-- productDescription / amount / statid -->
          <div class="col-md-8">
            <label class="form-label">Product Description</label>
            <input class="form-control" name="productDescription" value="<?php echo e($item['productDescription']); ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Amount</label>
            <input class="form-control" name="amount" value="<?php echo e($item['amount']); ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Stat ID</label>
            <input class="form-control" name="statid" value="<?php echo e($item['statid']); ?>" readonly>
          </div>

          <!-- buyer fields -->
          <div class="col-md-4">
            <label class="form-label">First Name</label>
            <input class="form-control" name="name" value="<?php echo e($item['name']); ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Last Name</label>
            <input class="form-control" name="lastName" value="<?php echo e($item['lastName']); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Mobile</label>
            <input class="form-control" name="mobile" value="<?php echo e($item['mobile']); ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?php echo e($item['email']); ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Address</label>
            <input class="form-control" name="address" value="<?php echo e($item['address']); ?>">
          </div>

          <!-- location -->
          <div class="col-md-3">
            <label class="form-label">Country</label>
            <input class="form-control" name="country" value="<?php echo e($item['country']); ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Pincode</label>
            <input class="form-control" name="pincode" value="<?php echo e($item['pincode']); ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">State</label>
            <input class="form-control" name="state" value="<?php echo e($item['state']); ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">City</label>
            <input class="form-control" name="city" value="<?php echo e($item['city']); ?>">
          </div>

          <!-- misc -->
          <div class="col-md-6">
            <label class="form-label">Source</label>
            <input class="form-control" name="source" value="<?php echo e($item['source']); ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Referral Code</label>
            <input class="form-control" name="referralCode" value="<?php echo e($item['referralCode']); ?>">
          </div>

          <div class="col-12 form-check mt-2">
            <input class="form-check-input" type="checkbox" value="1" id="terms" name="terms" <?php echo ((int)$item['terms'] === 1 ? 'checked' : ''); ?>>
            <label class="form-check-label" for="terms">Accepted Terms</label>
          </div>
        </div>

        <div class="mt-4 d-flex gap-2">
          <button class="btn btn-primary">
            <i class="fa-solid fa-check me-2"></i> Update
          </button>
          <a href="index.php" class="btn btn-outline-secondary">
            <i class="fa-solid fa-times me-2"></i> Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>