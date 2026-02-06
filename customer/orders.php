<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireCustomer();

$conn = getDatabaseConnection();
$user_id = getCurrentUserId();

// Get customer orders
$orders_query = "SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Gas Delivery System</title>
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="orders.php" class="active">My Orders</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="cart.php" class="btn btn-primary btn-sm">
                         Cart <span id="cart-count" class="badge" style="background-color: #ef4444; margin-left: 0.25rem;">0</span>
                    </a></li>
                    <li><a href="../logout.php" class="btn btn-danger btn-sm">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">My Orders</h1>
            </div>

            <?php displayFlashMessage(); ?>

            <div class="card">
                <div class="card-header">Order History</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Payment Status</th>
                                <th>Order Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($orders->num_rows > 0): ?>
                                <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo formatDateTime($order['order_date']); ?></td>
                                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                    <td><?php echo ucfirst($order['payment_method']); ?></td>
                                    <td>
                                        <span class="badge <?php echo getPaymentStatusClass($order['payment_status']); ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo getOrderStatusClass($order['order_status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">
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
