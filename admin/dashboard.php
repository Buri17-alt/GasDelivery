<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireAdmin();

$conn = getDatabaseConnection();

// Get statistics
$total_customers_query = "SELECT COUNT(*) as count FROM users WHERE user_type = 'customer'";
$total_customers = $conn->query($total_customers_query)->fetch_assoc()['count'];

$total_orders_query = "SELECT COUNT(*) as count FROM orders";
$total_orders = $conn->query($total_orders_query)->fetch_assoc()['count'];

$pending_orders_query = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'";
$pending_orders = $conn->query($pending_orders_query)->fetch_assoc()['count'];

$total_revenue_query = "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'";
$total_revenue = $conn->query($total_revenue_query)->fetch_assoc()['total'] ?? 0;

// Get recent orders
$recent_orders_query = "SELECT o.*, u.full_name, u.phone 
                        FROM orders o 
                        JOIN users u ON o.customer_id = u.user_id 
                        ORDER BY o.created_at DESC 
                        LIMIT 10";
$recent_orders = $conn->query($recent_orders_query);

// Get low stock products
$low_stock_query = "SELECT * FROM products WHERE stock_quantity < 20 ORDER BY stock_quantity ASC";
$low_stock = $conn->query($low_stock_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gas Delivery System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">
                    <span class="logo-icon"></span>
                    GasDelivery Admin
                </a>
                <ul class="nav-menu">
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="deliveries.php">Deliveries</a></li>
                    <li><a href="../logout.php" class="btn btn-danger btn-sm">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
                <div>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</div>
            </div>

            <?php displayFlashMessage(); ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"></div>
                    <div class="stat-value"><?php echo $total_customers; ?></div>
                    <div class="stat-label">Total Customers</div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon"></div>
                    <div class="stat-value"><?php echo $total_orders; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon"></div>
                    <div class="stat-value"><?php echo $pending_orders; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                
                <div class="stat-card danger">
                    <div class="stat-icon"></div>
                    <div class="stat-value"><?php echo formatCurrency($total_revenue); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Orders -->
                <div class="col-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-between align-center">
                                <span>Recent Orders</span>
                                <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_orders->num_rows > 0): ?>
                                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $order['order_id']; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($order['full_name']); ?><br>
                                                <small><?php echo htmlspecialchars($order['phone']); ?></small>
                                            </td>
                                            <td><?php echo formatDate($order['order_date']); ?></td>
                                            <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                            <td>
                                                <span class="badge <?php echo getOrderStatusClass($order['order_status']); ?>">
                                                    <?php echo ucfirst($order['order_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No orders found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="col-4">
                    <div class="card">
                        <div class="card-header">Low Stock Alert</div>
                        <div class="card-body">
                            <?php if ($low_stock->num_rows > 0): ?>
                                <?php while ($product = $low_stock->fetch_assoc()): ?>
                                <div style="padding: 0.75rem; border-bottom: 1px solid #e5e7eb;">
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($product['product_name']); ?></div>
                                    <div style="color: #6b7280; font-size: 0.875rem;"><?php echo htmlspecialchars($product['size']); ?></div>
                                    <div style="color: #ef4444; font-weight: 600; margin-top: 0.25rem;">
                                        Stock: <?php echo $product['stock_quantity']; ?> units
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>All products have sufficient stock</p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="products.php" class="btn btn-sm btn-primary" style="width: 100%;">Manage Stock</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Gas Delivery Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="../js/main.js"></script>
</body>
</html>

<?php closeDatabaseConnection($conn); ?>
