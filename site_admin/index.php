<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/auth.php';
$pdo = get_pdo();

/** KPIs */
$itemsCount = (int)$pdo->query("SELECT COUNT(*) AS c FROM orders")->fetch()['c'];
$successCount = (int)$pdo->query("SELECT COUNT(*) AS c FROM orders WHERE statid = '1'")->fetch()['c'];
$pendingCount = (int)$pdo->query("SELECT COUNT(*) AS c FROM orders WHERE statid = '0' OR statid IS NULL OR statid = ''")->fetch()['c'];

/** Revenue */
$revenueStmt = $pdo->query("
  SELECT COALESCE(SUM(CAST(amount AS DECIMAL(12,2))),0) AS total
  FROM orders
  WHERE amount REGEXP '^[0-9]+(\\.[0-9]+)?$'
    AND statid = '1'
");
$revenue = (float)($revenueStmt->fetch()['total'] ?? 0);

/** Today orders */
$todayOrdersStmt = $pdo->query("
  SELECT COUNT(*) AS c
  FROM orders
  WHERE DATE(created_at) = CURDATE()
");
$todayOrders = (int)($todayOrdersStmt->fetch()['c'] ?? 0);

/** Success rate */
$successRate = $itemsCount > 0 ? round(($successCount / $itemsCount) * 100) : 0;

/** Latest Orders */
$latestOrdersStmt = $pdo->query("
  SELECT id, name, mobile, product_name, model, amount, statid, created_at
  FROM orders
  ORDER BY id DESC
  LIMIT 6
");
$latestOrders = $latestOrdersStmt->fetchAll(PDO::FETCH_ASSOC);

/** Top ordered models */
$topModelsStmt = $pdo->query("
  SELECT model, COUNT(*) AS total
  FROM orders
  WHERE model IS NOT NULL AND model <> ''
  GROUP BY model
  ORDER BY total DESC, model ASC
  LIMIT 5
");
$topModels = $topModelsStmt->fetchAll(PDO::FETCH_ASSOC);

/** Payment summary panel data */
$paymentFailedCount = 0;
$orderNotCompletedCount = 0;

try {
    $paymentFailedStmt = $pdo->query("SELECT COUNT(*) AS c FROM orders WHERE payment_status = 'payment_failed'");
    $paymentFailedCount = (int)($paymentFailedStmt->fetch()['c'] ?? 0);

    $orderNotCompletedStmt = $pdo->query("SELECT COUNT(*) AS c FROM orders WHERE payment_status = 'order_not_completed'");
    $orderNotCompletedCount = (int)($orderNotCompletedStmt->fetch()['c'] ?? 0);
} catch (Throwable $e) {
    $paymentFailedCount = 0;
    $orderNotCompletedCount = 0;
}

function money2($v){ return number_format((float)$v, 2); }

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 2592000) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('d M Y', $time);
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

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
    padding: 0.75rem 0 !important;
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

  .hero-bar {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    flex-wrap: wrap;
  }

  .hero-title h2 {
    margin: 0;
    font-size: 2.1rem;
    font-weight: 700;
    color: #fff;
  }

  .hero-title p {
    margin: 8px 0 0;
    color: #aaa;
    font-size: 1rem;
  }

  .hero-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
  }

  .btn-primary {
    background: linear-gradient(135deg, #CE6723 0%, #e07a3a 100%);
    border: none;
    color: white;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(206, 103, 35, 0.3);
    text-decoration: none;
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(206, 103, 35, 0.4);
    background: linear-gradient(135deg, #e07a3a 0%, #CE6723 100%);
    color: #fff;
  }

  .btn-outline-secondary {
    background: transparent;
    border: 1px solid #444;
    color: #ccc;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
  }

  .btn-outline-secondary:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: #CE6723;
    color: #fff;
  }

  .dashboard-card {
    background: rgba(30, 30, 30, 0.82);
    border: 1px solid #333;
    border-radius: 18px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    height: 100%;
    overflow: hidden;
  }

  .dashboard-card:hover {
    box-shadow: 0 10px 30px rgba(206, 103, 35, 0.15);
    border-color: #CE6723;
  }

  .stat-card {
    position: relative;
    padding: 1.35rem;
  }

  .stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, #CE6723, #e07a3a);
  }

  .stat-card .top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
  }

  .stat-label {
    color: #bdbdbd;
    font-size: 0.95rem;
    margin-bottom: 6px;
  }

  .stat-kpi {
    font-size: 3rem;
    font-weight: 700;
    line-height: 1;
    color: #fff;
    letter-spacing: -1px;
  }

  .stat-sub {
    margin-top: 10px;
    font-size: 0.9rem;
    color: #8f8f8f;
  }

  .icon-pill {
    width: 64px;
    height: 64px;
    min-width: 64px;
    flex-shrink: 0;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(206, 103, 35, 0.12);
    color: #CE6723;
    font-size: 1.35rem;
    border: 1px solid rgba(206, 103, 35, 0.18);
    overflow: visible;
  }

  .icon-pill.success {
    background: rgba(46, 204, 113, 0.12);
    color: #2ecc71;
    border-color: rgba(46, 204, 113, 0.2);
  }

  .icon-pill.warning {
    background: rgba(155, 89, 182, 0.12);
    color: #c084fc;
    border-color: rgba(192, 132, 252, 0.2);
  }

  .section-card {
    padding: 1.35rem;
  }

  .section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 1.3rem;
    flex-wrap: wrap;
  }

  .section-title {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .section-title i {
    color: #CE6723;
    font-size: 1.15rem;
  }

  .section-title h5 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #fff;
  }

  .section-muted {
    color: #9a9a9a;
    font-size: 0.9rem;
    margin: 0;
  }

  .action-grid {
    display: grid;
    gap: 12px;
  }

  .mini-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-top: 18px;
  }

  .mini-stat {
    background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 14px;
    padding: 14px;
    min-width: 0;
  }

  .mini-stat .label {
    color: #9d9d9d;
    font-size: 0.85rem;
    margin-bottom: 8px;
  }

  .mini-stat .value {
    color: #fff;
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .summary-list {
    display: grid;
    gap: 14px;
  }

  .summary-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid rgba(255,255,255,0.08);
  }

  .summary-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }

  .summary-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
  }

  .summary-icon {
    width: 42px;
    height: 42px;
    min-width: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #CE6723;
    background: rgba(206, 103, 35, 0.12);
  }

  .summary-icon.success {
    color: #2ecc71;
    background: rgba(46, 204, 113, 0.12);
  }

  .summary-icon.fail {
    color: #e74c3c;
    background: rgba(231, 76, 60, 0.12);
  }

  .summary-text h6 {
    margin: 0 0 4px;
    color: #fff;
    font-size: 0.98rem;
    font-weight: 600;
  }

  .summary-text p {
    margin: 0;
    color: #9c9c9c;
    font-size: 0.85rem;
  }

  .summary-value {
    color: #fff;
    font-weight: 700;
    font-size: 1.15rem;
    white-space: nowrap;
  }

  .table-card table {
    width: 100%;
  }

  .table-card thead th {
    color: #9f9f9f;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0 0 14px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
  }

  .table-card tbody td {
    padding: 14px 0;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    color: #fff;
    vertical-align: top;
  }

  .table-card tbody tr:last-child td {
    border-bottom: none;
  }

  .customer-sub {
    color: #8c8c8c;
    font-size: 0.82rem;
    margin-top: 3px;
  }

  .status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
  }

  .status-success {
    color: #2ecc71;
    background: rgba(46, 204, 113, 0.14);
    border: 1px solid rgba(46, 204, 113, 0.18);
  }

  .status-pending {
    color: #e5e7eb;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.08);
  }

  .model-bars {
    display: grid;
    gap: 18px;
  }

  .model-row {
    display: grid;
    grid-template-columns: 95px 1fr 28px;
    gap: 12px;
    align-items: center;
  }

  .model-name {
    color: #fff;
    font-weight: 600;
  }

  .bar-wrap {
    width: 100%;
    height: 10px;
    background: rgba(255,255,255,0.08);
    border-radius: 999px;
    overflow: hidden;
  }

  .bar-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #CE6723, #f08a3c);
  }

  .model-total {
    color: #b9b9b9;
    text-align: right;
    font-size: 0.9rem;
  }

  @media (max-width: 1200px) {
    .stat-kpi {
      font-size: 2.4rem;
    }
  }

  @media (max-width: 768px) {
    .dash-fill {
      padding: 1rem;
    }

    .hero-title h2 {
      font-size: 1.6rem;
    }

    .stat-kpi {
      font-size: 2rem;
    }

    .icon-pill {
      width: 56px;
      height: 56px;
      min-width: 56px;
    }

    .mini-grid {
      grid-template-columns: 1fr;
    }

    .model-row {
      grid-template-columns: 80px 1fr 24px;
    }

    .sidebar {
      display: none;
    }
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var navbarBrand = document.querySelector('.navbar-brand');
    if (navbarBrand) {
        var logo = document.createElement('img');
        logo.src = '/img/logo.svg';
        logo.alt = 'Admin Panel Logo';
        logo.style.height = '35px';
        logo.style.marginRight = '10px';

        var originalText = navbarBrand.textContent;
        navbarBrand.innerHTML = '';
        navbarBrand.appendChild(logo);
        navbarBrand.appendChild(document.createTextNode(originalText));
    }
});
</script>

<div class="dash-fill d-flex flex-column gap-4">

  <div class="hero-bar">
    <div class="hero-title">
      <h2>Dashboard Overview</h2>
      <p>Monitor orders, payments, activity, and admin shortcuts from one place.</p>
    </div>
    <div class="hero-actions">
      <a href="/site_admin/items/create.php" class="btn btn-primary">
        <i class="fa-solid fa-plus me-2"></i> Add Order
      </a>
      <a href="/site_admin/items/index.php" class="btn btn-outline-secondary">
        <i class="fa-solid fa-table me-2"></i> Manage Orders
      </a>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-12 col-md-6 col-xl-3">
      <div class="dashboard-card stat-card">
        <div class="top">
          <div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-kpi"><?php echo $itemsCount; ?></div>
          </div>
          <div class="icon-pill">
            <i class="fa-solid fa-boxes-stacked"></i>
          </div>
        </div>
        <div class="stat-sub">All recorded orders</div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
      <div class="dashboard-card stat-card">
        <div class="top">
          <div>
            <div class="stat-label">Successful Payments</div>
            <div class="stat-kpi"><?php echo $successCount; ?></div>
          </div>
          <div class="icon-pill success">
            <i class="fa-solid fa-circle-check"></i>
          </div>
        </div>
        <div class="stat-sub"><?php echo $successRate; ?>% success rate</div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
      <div class="dashboard-card stat-card">
        <div class="top">
          <div>
            <div class="stat-label">Pending Payments</div>
            <div class="stat-kpi"><?php echo $pendingCount; ?></div>
          </div>
          <div class="icon-pill warning">
            <i class="fa-solid fa-hourglass-half"></i>
          </div>
        </div>
        <div class="stat-sub">Need follow-up</div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
      <div class="dashboard-card stat-card">
        <div class="top">
          <div style="min-width:0; flex:1;">
            <div class="stat-label">Recorded Revenue (₹)</div>
            <div class="stat-kpi" style="font-size:2.4rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              <?php echo money2($revenue); ?>
            </div>
          </div>
          <div class="icon-pill">
            <i class="fa-solid fa-indian-rupee-sign"></i>
          </div>
        </div>
        <div class="stat-sub">Numeric successful records</div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-12 col-lg-4">
      <div class="dashboard-card section-card h-100">
        <div class="section-header">
          <div class="section-title">
            <i class="fa-solid fa-bolt"></i>
            <h5>Quick Actions</h5>
          </div>
        </div>

        <div class="action-grid">
          <a href="/site_admin/items/create.php" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i> Add Order
          </a>
          <a href="/site_admin/items/index.php" class="btn btn-outline-secondary">
            <i class="fa-solid fa-table me-2"></i> Manage Orders
          </a>
          <a href="/site_admin/blog/index.php" class="btn btn-outline-secondary">
            <i class="fa-solid fa-blog me-2"></i> Blog Management
          </a>
          <a href="/site_admin/settings.php" class="btn btn-outline-secondary">
            <i class="fa-solid fa-gear me-2"></i> Settings
          </a>
        </div>

        <div class="mini-grid">
          <div class="mini-stat">
            <div class="label">Today Orders</div>
            <div class="value"><?php echo $todayOrders; ?></div>
          </div>
          <div class="mini-stat">
            <div class="label">Success Rate</div>
            <div class="value"><?php echo $successRate; ?>%</div>
          </div>
          <div class="mini-stat">
            <div class="label">Pending Orders</div>
            <div class="value"><?php echo $pendingCount; ?></div>
          </div>
          <div class="mini-stat">
            <div class="label">Revenue</div>
            <div class="value">₹<?php echo money2($revenue); ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-8">
      <div class="dashboard-card section-card h-100">
        <div class="section-header">
          <div class="section-title">
            <i class="fa-solid fa-chart-pie"></i>
            <h5>Booking Insights</h5>
          </div>
          <p class="section-muted">Latest actions from orders, payments, and users</p>
        </div>

        <div class="summary-list">
          <div class="summary-item">
            <div class="summary-left">
              <div class="summary-icon">
                <i class="fa-solid fa-clock-rotate-left"></i>
              </div>
              <div class="summary-text">
                <h6>Orders not completed</h6>
                <p>Customers who started booking but did not finish payment.</p>
              </div>
            </div>
            <div class="summary-value"><?php echo $orderNotCompletedCount; ?></div>
          </div>

          <div class="summary-item">
            <div class="summary-left">
              <div class="summary-icon success">
                <i class="fa-solid fa-circle-check"></i>
              </div>
              <div class="summary-text">
                <h6>Payments completed</h6>
                <p>Successful payment confirmations received from gateway.</p>
              </div>
            </div>
            <div class="summary-value"><?php echo $successCount; ?></div>
          </div>

          <div class="summary-item">
            <div class="summary-left">
              <div class="summary-icon fail">
                <i class="fa-solid fa-circle-xmark"></i>
              </div>
              <div class="summary-text">
                <h6>Payments failed</h6>
                <p>Transactions that returned from gateway without success.</p>
              </div>
            </div>
            <div class="summary-value"><?php echo $paymentFailedCount; ?></div>
          </div>

          <div class="summary-item">
            <div class="summary-left">
              <div class="summary-icon">
                <i class="fa-solid fa-wallet"></i>
              </div>
              <div class="summary-text">
                <h6>Recorded revenue</h6>
                <p>Total successful numeric collection from paid orders.</p>
              </div>
            </div>
            <div class="summary-value">₹<?php echo money2($revenue); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-12 col-xl-8">
      <div class="dashboard-card section-card table-card h-100">
        <div class="section-header">
          <div class="section-title">
            <i class="fa-solid fa-table-list"></i>
            <h5>Latest Orders</h5>
          </div>
          <a href="/site_admin/items/index.php" class="btn btn-outline-secondary btn-sm">View All</a>
        </div>

        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>#ID</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Model</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Created</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($latestOrders)): ?>
                <?php foreach ($latestOrders as $order): ?>
                  <tr>
                    <td>#<?php echo (int)$order['id']; ?></td>
                    <td>
                      <div><?php echo htmlspecialchars($order['name'] ?: '-'); ?></div>
                      <div class="customer-sub"><?php echo htmlspecialchars($order['mobile'] ?: '-'); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($order['product_name'] ?: 'nx100'); ?></td>
                    <td><?php echo htmlspecialchars($order['model'] ?: '-'); ?></td>
                    <td>
                      ₹<?php
                        if (is_numeric($order['amount'])) {
                          echo money2($order['amount']);
                        } else {
                          echo htmlspecialchars($order['amount'] ?: '0.00');
                        }
                      ?>
                    </td>
                    <td>
                      <?php if ((string)$order['statid'] === '1'): ?>
                        <span class="status-badge status-success">Success</span>
                      <?php else: ?>
                        <span class="status-badge status-pending">Pending</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo timeAgo($order['created_at']); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" style="color:#999; padding:20px 0;">No orders found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-4">
      <div class="dashboard-card section-card h-100">
        <div class="section-header">
          <div class="section-title">
            <i class="fa-solid fa-chart-column"></i>
            <h5>Top Ordered Models</h5>
          </div>
        </div>

        <div class="model-bars">
          <?php
            $maxModelCount = 1;
            if (!empty($topModels)) {
              $counts = array_column($topModels, 'total');
              $maxModelCount = max($counts);
            }
          ?>

          <?php if (!empty($topModels)): ?>
            <?php foreach ($topModels as $model): ?>
              <?php
                $width = $maxModelCount > 0 ? round(($model['total'] / $maxModelCount) * 100) : 0;
              ?>
              <div class="model-row">
                <div class="model-name"><?php echo htmlspecialchars($model['model']); ?></div>
                <div class="bar-wrap">
                  <div class="bar-fill" style="width: <?php echo $width; ?>%;"></div>
                </div>
                <div class="model-total"><?php echo (int)$model['total']; ?></div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="color:#999;">No model data found.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>