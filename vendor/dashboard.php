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
$user_id = getCurrentUserId();

// Get vendor statistics
$stats = [];

// Total products (if we implement vendor-specific products in future)
$result = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
$stats['total_products'] = $result->fetch_assoc()['count'];

// Total orders (customers ordering products)
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status != 'cancelled'");
$stats['total_orders'] = $result->fetch_assoc()['count'];

// Pending orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
$stats['pending_orders'] = $result->fetch_assoc()['count'];

// Total revenue
$result = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE order_status = 'confirmed' AND payment_status = 'paid'");
$stats['total_revenue'] = $result->fetch_assoc()['total'];

// Get recent orders
$orders_query = "SELECT o.*, u.full_name, u.phone, u.email 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.user_id 
                 ORDER BY o.created_at DESC 
                 LIMIT 10";
$orders_result = $conn->query($orders_query);

$pageTitle = 'Vendor Dashboard';
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

    
    <div class="dashboard-container">
    <?php
        $current_page = basename($_SERVER['PHP_SELF']);
    ?>

        
        <main class="main-content">
            <div class="container">
                <div class="page-header">
                <h1>Vendor Dashboard</h1>
               <strong><p>Welcome back, <?php echo htmlspecialchars(getCurrentUserName()); ?>!</p></strong> 
                </div>
            
                <!-- Statistics Cards -->
                <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"></div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_products']; ?></h3>
                        <p>Total Products</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"></div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"></div>
                    <div class="stat-details">
                        <h3><?php echo $stats['pending_orders']; ?></h3>
                        <p>Pending Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"></div>
                    <div class="stat-details">
                        <h3><?php echo formatCurrency($stats['total_revenue']); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                </div>
            
                <!-- Recent Orders -->
                <div class="card">
                <div class="card-header">
                    <h2>Recent Orders</h2>
                    <a href="orders.php" class="btn btn-primary btn-sm">View All Orders</a>
                </div>
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
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                                        <td><?php echo formatCurrency($order['total_amount']); ?></td>
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
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No orders yet.</p>
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
