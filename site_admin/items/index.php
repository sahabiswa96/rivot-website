<?php
$pageTitle = 'Orders';
require_once __DIR__ . '/../includes/auth.php';
$pdo = get_pdo();

/* Pull columns explicitly in table order to keep header stable */
$sql = "SELECT
        id, price, model, color, product_name,
        trackId, orderId, productDescription, transaction_id,
        amount, statid, payment_status, name, lastName, mobile, email, address,
        country, pincode, state, city, source, referralCode,
        terms, created_at
      FROM orders
      ORDER BY id DESC";
$items = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

/* Pretty labels for headers (same order as query) */
$cols = [
  'id' => 'Id',
  'price' => 'Price (₹)',
  'model' => 'Model',
  'color' => 'Color',
  'product_name' => 'Product Name',
  'trackId' => 'Track ID',
  'orderId' => 'Order ID',
  'productDescription' => 'Product Description',
  'transaction_id' => 'Transaction ID',
  'amount' => 'Amount',
  'statid' => 'Status',
  'payment_status' => 'Payment Status',
  'name' => 'First Name',
  'lastName' => 'Last Name',
  'mobile' => 'Mobile',
  'email' => 'Email',
  'address' => 'Address',
  'country' => 'Country',
  'pincode' => 'Pincode',
  'state' => 'State',
  'city' => 'City',
  'source' => 'Source',
  'referralCode' => 'Referral Code',
  'terms' => 'Terms',
  'created_at' => 'Created At'
];

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function price2($v){ return number_format((float)$v, 2); }
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-buttons-bs5@2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-responsive-bs5@2.5.0/css/responsive.bootstrap5.min.css">

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

  .section-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    gap: 10px;
    flex-wrap: wrap;
  }

  .section-header i.section-icon {
    color: #fff;
    margin-right: 0.75rem;
    font-size: 1.25rem;
  }

  .section-header h5 {
    color: #fff;
    font-weight: 600;
    margin: 0;
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

  .btn-sm {
    padding: 6px 12px;
    font-size: 0.875rem;
  }

  .table {
    color: #fff !important;
  }

  .table thead th {
    background-color: rgba(30, 30, 30, 0.9);
    color: #fff;
    border-bottom: 2px solid #CE6723;
    white-space: nowrap;
    font-weight: 600;
  }

  .table tbody td {
    background-color: rgba(20, 20, 20, 0.7);
    border-color: #333;
    vertical-align: middle;
    color: #fff !important;
  }

  .table-striped > tbody > tr:nth-of-type(odd) > td {
    background-color: rgba(30, 30, 30, 0.5);
    color: #fff !important;
  }

  .table-hover > tbody > tr:hover > td {
    background-color: rgba(206, 103, 35, 0.15) !important;
    color: #fff !important;
    transition: background-color 0.3s ease;
  }

  .alert {
    border-radius: 8px;
    padding: 15px 20px;
    margin-bottom: 1rem;
    border: 1px solid;
  }

  .alert-success {
    background: rgba(46, 204, 113, 0.15);
    border-color: rgba(46, 204, 113, 0.4);
    color: #2ecc71;
  }

  .alert-danger {
    background: rgba(231, 76, 60, 0.15);
    border-color: rgba(231, 76, 60, 0.4);
    color: #e74c3c;
  }

  .alert .btn-close {
    filter: invert(1);
    opacity: 0.7;
  }

  .alert .btn-close:hover {
    opacity: 1;
  }

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

  .dataTables_wrapper .dataTables_filter input {
    background-color: rgba(30, 30, 30, 0.8);
    border: 1px solid #444;
    color: #fff;
    border-radius: 6px;
    padding: 8px 12px;
  }

  .dataTables_wrapper .dataTables_length select {
    background-color: rgba(30, 30, 30, 0.8) !important;
    border: 1px solid #444 !important;
    color: #fff !important;
    border-radius: 6px !important;
    padding: 6px 38px 6px 10px !important;
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>") !important;
    background-repeat: no-repeat !important;
    background-position: right 10px center !important;
    background-size: 14px !important;
  }

  .dataTables_wrapper .dataTables_info {
    color: #aaa;
  }

  .dataTables_wrapper .dataTables_paginate .paginate_button {
    background: transparent;
    border: 1px solid #444;
    color: #ccc !important;
    border-radius: 6px;
    margin: 0 3px;
    padding: 8px 12px;
    transition: all 0.3s ease;
  }

  .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: rgba(206, 103, 35, 0.1);
    border-color: #CE6723;
    color: #fff !important;
  }

  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #CE6723 0%, #e07a3a 100%);
    border: none;
    color: white !important;
  }

  .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    opacity: 0.5;
  }

  .dataTables_wrapper .row.mb-2 {
    background-color: rgba(206, 103, 35, 0.15);
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 15px !important;
    color: white !important;
  }

  .container-fluid {
    padding-left: 0;
    padding-right: 0;
  }

  .table-responsive {
    overflow-x: auto;
    width: 100%;
  }

  #ordersTable {
    min-width: 100%;
    width: 100% !important;
  }

  .col-wide {
    min-width: 260px;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .no-wrap {
    white-space: nowrap;
  }

  .export-toolbar .btn {
    margin: 0;
  }

  .dt-buttons {
    display: none !important;
  }

  @media (max-width: 768px) {
    .card-body {
      padding: 1rem;
    }

    .dataTables_wrapper .dataTables_filter {
      margin-bottom: 10px;
    }

    .container-fluid {
      padding-left: 15px;
      padding-right: 15px;
    }

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

    .section-header {
      align-items: flex-start;
    }

    .export-toolbar {
      width: 100%;
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
  <div class="card activity-card">
    <div class="card-body">
      <div class="section-header">
        <i class="fa-solid fa-boxes-stacked section-icon"></i>
        <h5>Orders</h5>

        <div class="ms-auto d-flex align-items-center gap-2 flex-wrap export-toolbar">
          <button type="button" id="exportCopy" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-copy me-1"></i> Copy
          </button>
          <button type="button" id="exportCsv" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-file-csv me-1"></i> CSV
          </button>
          <button type="button" id="exportExcel" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-file-excel me-1"></i> Excel
          </button>
          <button type="button" id="exportPdf" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-file-pdf me-1"></i> PDF
          </button>
          <button type="button" id="exportPrint" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-print me-1"></i> Print
          </button>

          <a href="create.php" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i> Add New
          </a>
        </div>
      </div>

      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fa-solid fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['success']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fa-solid fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_SESSION['error']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <form id="bulkDeleteForm" action="bulk_delete.php" method="post" style="display:inline;">
        <?php csrf_field(); ?>
        <button type="submit" class="btn btn-danger mb-3" id="bulkDeleteBtn" style="display:none;" onclick="return confirm('Are you sure you want to delete the selected orders?');">
          <i class="fa-solid fa-trash me-2"></i>Delete Selected (<span id="selectedCount">0</span>)
        </button>

        <div class="table-responsive">
          <table id="ordersTable" class="table table-striped table-hover table-bordered align-middle" style="width:100%">
            <thead>
              <tr>
                <th style="width:30px;"><input type="checkbox" id="selectAll" title="Select All"></th>
                <?php foreach ($cols as $key => $label): ?>
                  <th><?php echo $label; ?></th>
                <?php endforeach; ?>
                <th class="no-sort">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $row): ?>
                <tr>
                  <td><input type="checkbox" name="ids[]" value="<?php echo (int)$row['id']; ?>" class="row-checkbox"></td>
                  <?php foreach ($cols as $key => $label): ?>
                    <td class="<?php echo in_array($key, ['productDescription','address','email']) ? 'col-wide' : ''; ?>">
                      <?php
                        $val = $row[$key];
                        switch ($key) {
                          case 'price':
                            echo price2($val);
                            break;

                          case 'amount':
                            echo is_numeric($val) ? price2($val) : h($val);
                            break;

                          case 'statid':
                            if ((string)$val === '1') {
                              echo '<span class="badge badge-success">Success</span>';
                            } elseif ((string)$val === '0' || $val === '' || $val === null) {
                              echo '<span class="badge badge-pending">Pending</span>';
                            } else {
                              echo '<span class="badge badge-other">' . h($val) . '</span>';
                            }
                            break;

                          case 'payment_status':
                            if ($val === 'payment_completed') {
                              echo '<span class="badge badge-success">Payment Completed</span>';
                            } elseif ($val === 'payment_failed') {
                              echo '<span class="badge badge-other">Payment Failed</span>';
                            } else {
                              echo '<span class="badge badge-pending">Order Not Completed</span>';
                            }
                            break;

                          case 'terms':
                            echo ((int)$val === 1)
                              ? '<span class="badge badge-success">Yes</span>'
                              : '<span class="badge badge-pending">No</span>';
                            break;

                          default:
                            echo h($val);
                            break;
                        }
                      ?>
                    </td>
                  <?php endforeach; ?>
                  <td class="no-wrap">
                    <a class="btn btn-sm btn-outline-secondary" href="view.php?id=<?php echo (int)$row['id']; ?>" title="View">
                      <i class="fa-solid fa-eye"></i>
                    </a>
                    <a class="btn btn-sm btn-outline-primary" href="edit.php?id=<?php echo (int)$row['id']; ?>" title="Edit">
                      <i class="fa-solid fa-pen"></i>
                    </a>
                    <form action="delete.php" method="post" class="d-inline" onsubmit="return confirm('Delete this order?');">
                      <?php csrf_field(); ?>
                      <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                      <button class="btn btn-sm btn-outline-danger" title="Delete" type="submit">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-responsive@2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-responsive-bs5@2.5.0/js/responsive.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons-bs5@2.4.2/js/buttons.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdfmake@0.2.7/build/pdfmake.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdfmake@0.2.7/build/vfs_fonts.js"></script>

<script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.2/js/buttons.print.min.js"></script>

<script>
$(function () {
  if (typeof $.fn.dataTable === 'undefined') {
    console.error('DataTables core not loaded');
    return;
  }

  if (typeof $.fn.dataTable.Buttons === 'undefined') {
    console.error('DataTables Buttons extension not loaded');
    return;
  }

  if ($.fn.DataTable.isDataTable('#ordersTable')) {
    $('#ordersTable').DataTable().destroy();
  }

  var table = $('#ordersTable').DataTable({
    scrollX: true,
    autoWidth: false,
    responsive: false,
    pageLength: 25,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
    order: [[1, 'desc']],
    dom:
      '<"row mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6">>' +
      'rt' +
      '<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    columnDefs: [
      { targets: 0, orderable: false, searchable: false },
      { targets: -1, orderable: false, searchable: false }
    ],
    drawCallback: function () {
      var paginateButtons = $(this).closest('.dataTables_wrapper').find('.paginate_button');
      paginateButtons.addClass('btn btn-outline-secondary');
      paginateButtons.filter('.current').removeClass('btn-outline-secondary').addClass('btn-primary');
    }
  });

  new $.fn.dataTable.Buttons(table, {
    buttons: [
      {
        extend: 'copyHtml5',
        className: 'buttons-copy',
        title: 'RIVOT Orders',
        exportOptions: {
          columns: ':not(:first-child):not(:last-child)',
          modifier: { search: 'applied', order: 'applied', page: 'all' }
        }
      },
      {
        extend: 'csvHtml5',
        className: 'buttons-csv',
        filename: 'RIVOT_Orders_' + new Date().toISOString().slice(0, 10),
        title: 'RIVOT Orders',
        exportOptions: {
          columns: ':not(:first-child):not(:last-child)',
          modifier: { search: 'applied', order: 'applied', page: 'all' }
        }
      },
      {
        extend: 'excelHtml5',
        className: 'buttons-excel',
        filename: 'RIVOT_Orders_' + new Date().toISOString().slice(0, 10),
        title: 'RIVOT Orders',
        exportOptions: {
          columns: ':not(:first-child):not(:last-child)',
          modifier: { search: 'applied', order: 'applied', page: 'all' }
        }
      },
      {
        extend: 'pdfHtml5',
        className: 'buttons-pdf',
        filename: 'RIVOT_Orders_' + new Date().toISOString().slice(0, 10),
        title: 'RIVOT Orders',
        orientation: 'landscape',
        pageSize: 'A3',
        exportOptions: {
          columns: ':not(:first-child):not(:last-child)',
          modifier: { search: 'applied', order: 'applied', page: 'all' }
        }
      },
      {
        extend: 'print',
        className: 'buttons-print',
        title: 'RIVOT Orders',
        exportOptions: {
          columns: ':not(:first-child):not(:last-child)',
          modifier: { search: 'applied', order: 'applied', page: 'all' }
        }
      }
    ]
  });

  var hiddenButtons = table.buttons().container();
  hiddenButtons.css({
    position: 'absolute',
    left: '-9999px',
    top: '-9999px',
    width: '1px',
    height: '1px',
    overflow: 'hidden'
  });
  $('body').append(hiddenButtons);

  $('#exportCopy').on('click', function (e) {
    e.preventDefault();
    hiddenButtons.find('.buttons-copy').trigger('click');
  });

  $('#exportCsv').on('click', function (e) {
    e.preventDefault();
    hiddenButtons.find('.buttons-csv').trigger('click');
  });

  $('#exportExcel').on('click', function (e) {
    e.preventDefault();
    hiddenButtons.find('.buttons-excel').trigger('click');
  });

  $('#exportPdf').on('click', function (e) {
    e.preventDefault();
    hiddenButtons.find('.buttons-pdf').trigger('click');
  });

  $('#exportPrint').on('click', function (e) {
    e.preventDefault();
    hiddenButtons.find('.buttons-print').trigger('click');
  });

  $('#selectAll').on('click', function () {
    var checked = this.checked;
    $('.row-checkbox').prop('checked', checked);
    updateBulkDeleteButton();
  });

  $(document).on('change', '.row-checkbox', function () {
    updateBulkDeleteButton();
    var totalCheckboxes = $('.row-checkbox').length;
    var checkedCheckboxes = $('.row-checkbox:checked').length;
    $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
  });

  function updateBulkDeleteButton() {
    var checkedCount = $('.row-checkbox:checked').length;
    if (checkedCount > 0) {
      $('#bulkDeleteBtn').show();
      $('#selectedCount').text(checkedCount);
    } else {
      $('#bulkDeleteBtn').hide();
    }
  }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>