<?php
 $pageTitle = 'Order Details';
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

/* Pretty labels & grouping — adjust order/labels if you like */
 $groups = [
  'Order' => [
    'id'                => 'ID',
    'price'             => 'Price (₹)',
    'model'             => 'Model',
    'color'             => 'Color',
    'product_name'      => 'Product Name',
    'trackId'           => 'Track ID',
    'orderId'           => 'Order ID',
    'productDescription'=> 'Product Description',
    'transaction_id'    => 'Transaction ID',
    'amount'            => 'Amount',
    'statid'            => 'Status',
  ],
  'Buyer' => [
    'name'     => 'First Name',
    'lastName' => 'Last Name',
    'mobile'   => 'Mobile',
    'email'    => 'Email',
  ],
  'Address' => [
    'address' => 'Address',
    'country' => 'Country',
    'pincode' => 'Pincode',
    'state'   => 'State',
    'city'    => 'City',
  ],
  'Meta' => [
    'source'       => 'Source',
    'referralCode' => 'Referral Code',
    'terms'        => 'Terms',
    'created_at'   => 'Created At',
  ],
];

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money2($v){ return number_format((float)$v, 2); }

function render_value($key, $val) {
    // Field-specific formatting
    switch ($key) {
        case 'price':
        case 'amount':
            return is_numeric($val) ? money2($val) : h($val);

        case 'statid':
            if ((string)$val === '1')  return '<span class="badge badge-success">Success</span>';
            if ((string)$val === '0' || $val === '' || $val === null) return '<span class="badge badge-pending">Pending</span>';
            return '<span class="badge badge-other">'.h($val).'</span>';

        case 'terms':
            return ((int)$val === 1)
                ? '<span class="badge badge-success">Yes</span>'
                : '<span class="badge badge-pending">No</span>';

        default:
            return nl2br(h($val));
    }
}

/* For copy buttons */
 $copyable = ['trackId','orderId','transaction_id'];
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
    color: #ffffffff;
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
  
  /* Badge styling - Updated for dark theme */
  .badge {
    font-weight: 500;
    padding: 0.4em 0.8em;
    border-radius: 6px;
  }
  
  .badge-pending { 
    background: rgba(243, 244, 246, 0.2); 
    color: #e5e7eb; 
    border: 1px solid rgba(255, 255, 255, 0.1);
  }
  
  .badge-success { 
    background: rgba(46, 204, 113, 0.2); 
    color: #2ecc71; 
    border: 1px solid rgba(46, 204, 113, 0.3);
  }
  
  .badge-other { 
    background: rgba(231, 76, 60, 0.2); 
    color: #e74c3c; 
    border: 1px solid rgba(231, 76, 60, 0.3);
  }
  
  /* Detail section styling - Updated for dark theme */
  .detail-section + .detail-section { 
    margin-top: 1rem; 
  }
  
  .detail-title { 
    font-size: 1rem; 
    font-weight: 700; 
    letter-spacing: .02em; 
    color: #fff;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
  }
  
  .detail-title::before {
    content: '';
    display: inline-block;
    width: 4px;
    height: 16px;
    background-color: #CE6723;
    margin-right: 10px;
    border-radius: 2px;
  }
  
  .detail-card  { 
    border: 1px solid #333;
    background: rgba(30, 30, 30, 0.6);
    border-radius: 12px;
  }
  
  .detail-row   { 
    padding: .75rem 0; 
    border-bottom: 1px dashed rgba(255, 255, 255, 0.1); 
  }
  
  .detail-row:last-child { 
    border-bottom: 0; 
  }
  
  .detail-label { 
    color: #aaa; 
    font-size: .85rem; 
    margin-bottom: .25rem;
    font-weight: 500;
  }
  
  .detail-value { 
    font-size: .98rem; 
    word-break: break-word;
    color: #fff;
  }
  
  .copy-btn { 
    border: none; 
    background: transparent; 
    padding: 0 .25rem; 
    color: #aaa;
    transition: all 0.3s ease;
  }
  
  .copy-btn:hover { 
    color: #CE6723; 
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
});
</script>

<div class="dash-fill d-flex flex-column gap-4">
  <div class="card activity-card">
    <div class="card-body">
      <div class="section-header">
        <i class="fa-solid fa-eye"></i>
        <h5>Order Details</h5>
        <div class="ms-auto">
          <a href="edit.php?id=<?php echo (int)$item['id']; ?>" class="btn btn-primary me-2">
            <i class="fa-solid fa-pen me-2"></i> Edit
          </a>
          <a href="index.php" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
          </a>
        </div>
      </div>

      <div class="row g-3">
        <?php foreach ($groups as $groupName => $fields): ?>
          <div class="col-12">
            <div class="detail-section">
              <div class="detail-title"><?php echo h($groupName); ?></div>
              <div class="card border-0 detail-card">
                <div class="card-body">
                  <div class="row">
                    <?php foreach ($fields as $key => $label): if (!array_key_exists($key, $item)) continue; ?>
                      <div class="col-md-6">
                        <div class="detail-row">
                          <div class="d-flex align-items-start justify-content-between">
                            <div class="detail-label"><?php echo h($label); ?></div>
                            <?php if (in_array($key, $copyable, true) && !empty($item[$key])): ?>
                              <button class="copy-btn" type="button" data-copy="<?php echo h($item[$key]); ?>" title="Copy">
                                <i class="fa-regular fa-copy"></i>
                              </button>
                            <?php endif; ?>
                          </div>
                          <div class="detail-value">
                            <?php echo render_value($key, $item[$key]); ?>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('.copy-btn').forEach(function(btn){
    btn.addEventListener('click', function(){
      const txt = this.getAttribute('data-copy') || '';
      navigator.clipboard.writeText(txt).then(() => {
        this.innerHTML = '<i class="fa-solid fa-check text-success"></i>';
        setTimeout(() => { this.innerHTML = '<i class="fa-regular fa-copy"></i>'; }, 1200);
      });
    });
  });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>