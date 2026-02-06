<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireCustomer();

$conn = getDatabaseConnection();
$user_id = getCurrentUserId();

// Get customer statistics
$total_orders_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = ?";
$stmt = $conn->prepare($total_orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_orders = $stmt->get_result()->fetch_assoc()['count'];

$pending_orders_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND order_status = 'pending'";
$stmt = $conn->prepare($pending_orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_orders = $stmt->get_result()->fetch_assoc()['count'];

$delivered_orders_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND order_status = 'confirmed'";
$stmt = $conn->prepare($delivered_orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$delivered_orders = $stmt->get_result()->fetch_assoc()['count'];

// Get recent orders
$recent_orders_query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($recent_orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Gas Delivery System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">
                    <span class="logo-icon"></span>
                    GasDelivery
                </a>
                <ul class="nav-menu">
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="orders.php">My Orders</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="../logout.php" class="btn btn-danger btn-sm">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                <a href="products.php" class="btn btn-primary">Order Now</a>
            </div>

        

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"></div>
                    <div class="stat-value"><?php echo $total_orders; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon"></div>
                    <div class="stat-value"><?php echo $pending_orders; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon"></div>
                    <div class="stat-value"><?php echo $delivered_orders; ?></div>
                    <div class="stat-label">Delivered Orders</div>
                </div>
            </div>

            <!-- Recent Orders -->
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
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Payment Status</th>
                                <th>Order Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_orders->num_rows > 0): ?>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                    <td>
                                        <span class="badge status-<?php echo $order['payment_status']; ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        No orders yet. <a href="products.php">Start shopping!</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Gas Delivery Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="../js/main.js"></script>
</body>
</html>

<?php closeDatabaseConnection($conn); ?>
