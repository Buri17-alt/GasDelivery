<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Check if user is vendor
if (!isLoggedIn() || (!isVendor() && !isAdmin())) {
    header('Location: ../login.php');
    exit();
}

$conn = getDatabaseConnection();

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$query = "SELECT o.*, u.full_name, u.phone, u.email 
          FROM orders o 
          JOIN users u ON o.user_id = u.user_id";

if (!empty($status_filter)) {
    $query .= " WHERE o.order_status = '" . $conn->real_escape_string($status_filter) . "'";
}

$query .= " ORDER BY o.created_at DESC";
$orders_result = $conn->query($query);

$pageTitle = 'Orders';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Gas Delivery System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="header">
<div class="container">
<nav class="navbar">
    <div class="navbar-brand">
        <h2>GasDelivery Vendor</h2>
    </div>
    <div class="nav-menu">
        <span class="user-info">
            <strong><?php echo htmlspecialchars(getCurrentUserName()); ?></strong>
            <span class="badge status-delivered">Vendor</span>
        </span>
        <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="products.php" class="nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
            <span class="nav-text">Products</span>
        </a>
        <a href="orders.php" class="nav-link <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
            <span class="nav-text">Orders</span>
        </a>
        <a href="profile.php" class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
            <span class="nav-text">Profile</span>
        </a>
        <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
    </nav>
    </div>
</header>

        <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Orders Management</h1>
            </div>
            
            <!-- Filter Section -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-body">
                    <form method="GET" class="filter-form" style="display: flex; gap: 1rem; align-items: center;">
                        <label for="status" style="font-weight: 600;">Filter by Status:</label>
                        <select name="status" id="status" class="form-control" style="max-width: 200px;">
                            <option value="">All Orders</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Apply Filter</button>
                        <a href="orders.php" class="btn btn-secondary btn-sm">Clear</a>
                    </form>
                </div>
            </div>
            
            <!-- Orders Table -->
            <div class="card">
                <div class="card-body">
                    <?php if ($orders_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Contact</th>
                                        <th>Amount</th>
                                        <th>Payment Method</th>
                                        <th>Payment Status</th>
                                        <th>Order Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($order['phone']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                        </td>
                                        <td><strong><?php echo formatCurrency($order['total_amount']); ?></strong></td>
                                        <td><?php echo ucfirst($order['payment_method']); ?></td>
                                        <td>
                                            <span class="badge status-<?php echo $order['payment_status']; ?>">
                                                <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge status-<?php echo $order['order_status']; ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p class="text-muted">No orders found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </div>
        </main>
    </div>
    
    <script src="../js/main.js"></script>
</body>
</html>

<?php closeDatabaseConnection($conn); ?>
