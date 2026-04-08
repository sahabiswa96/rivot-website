<?php
session_start();
require_once('db_config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'delete':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $_SESSION['message'] = "Order deleted successfully!";
                header("Location: admin.php");
                exit();
            }
            break;
    }
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(order_id LIKE ? OR buyer_email LIKE ? OR buyer_first_name LIKE ? OR buyer_last_name LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if (!empty($status_filter)) {
    $where_conditions[] = "payment_status = ?";
    $params[] = $status_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_sql = "SELECT COUNT(*) FROM orders $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get orders
$sql = "SELECT * FROM orders $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rivot Motors - Order Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 1.5rem;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 5px;
            border: 1px solid rgba(255,255,255,0.3);
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .filters form {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filters input, .filters select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .filters input[type="text"] {
            min-width: 250px;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d68910;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status.success {
            background: #d4edda;
            color: #155724;
        }
        
        .status.failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .pagination a {
            padding: 0.5rem 0.75rem;
            border: 1px solid #ddd;
            color: #667eea;
            text-decoration: none;
            border-radius: 4px;
        }
        
        .pagination a:hover, .pagination a.current {
            background: #667eea;
            color: white;
        }
        
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }
            
            .filters form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filters input[type="text"] {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rivot Motors - Order Management</h1>
        <a href="admin_logout.php" class="logout-btn">Logout</a>
    </div>
    
    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="filters">
            <form method="GET">
                <input type="text" name="search" placeholder="Search by Order ID, Email, or Name" value="<?php echo htmlspecialchars($search); ?>">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="SUCCESS" <?php echo $status_filter === 'SUCCESS' ? 'selected' : ''; ?>>Success</option>
                    <option value="FAILED" <?php echo $status_filter === 'FAILED' ? 'selected' : ''; ?>>Failed</option>
                    <option value="PENDING" <?php echo $status_filter === 'PENDING' ? 'selected' : ''; ?>>Pending</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="admin.php" class="btn btn-warning">Clear</a>
            </form>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment Mode</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['buyer_first_name'] . ' ' . $order['buyer_last_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['buyer_email']); ?></td>
                            <td>₹<?php echo number_format($order['amount'], 2); ?></td>
                            <td>
                                <span class="status <?php echo strtolower($order['payment_status']); ?>">
                                    <?php echo $order['payment_status']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($order['payment_mode'] ?: 'N/A'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="btn btn-warning">Edit</a>
                                    <a href="admin.php?action=delete&id=<?php echo $order['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this order?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: #666;">
                                No orders found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" 
                       class="<?php echo $i === $page ? 'current' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
        
        <p style="text-align: center; margin-top: 2rem; color: #666;">
            Showing <?php echo count($orders); ?> of <?php echo $total_records; ?> orders
        </p>
    </div>
</body>
</html>