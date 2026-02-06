<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireAdmin();

$conn = getDatabaseConnection();

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $order_id = intval($_POST['order_id']);
        $order_status = sanitizeInput($_POST['order_status']);
        
        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $order_status, $order_id);
        
        if ($stmt->execute()) {
            redirectWithMessage('orders.php', 'Order status updated successfully!', 'success');
        } else {
            redirectWithMessage('orders.php', 'Failed to update order status', 'danger');
        }
    }
}

// Get all orders
$orders_query = "SELECT o.*, u.full_name, u.phone, u.email 
                 FROM orders o 
                 JOIN users u ON o.customer_id = u.user_id 
                 ORDER BY o.created_at DESC";
$orders = $conn->query($orders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">
                    <span class="logo-icon"></span>
                    GasDelivery Admin
                </a>
                <ul class="nav-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="orders.php" class="active">Orders</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="deliveries.php">Deliveries</a></li>
                    <li><a href="../logout.php" class="btn btn-danger btn-sm">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Manage Orders</h1>
            </div>

            <?php displayFlashMessage(); ?>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search orders..." 
                               onkeyup="searchTable('searchInput', 'ordersTable')" style="max-width: 300px;">
                        <select id="statusFilter" class="form-control" onchange="filterTable('statusFilter', 'ordersTable', 4)" style="max-width: 200px;">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="in_transit">In Transit</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Order Status</th>
                                <th>Payment Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($order['full_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($order['phone']); ?></small>
                                </td>
                                <td><?php echo formatDateTime($order['order_date']); ?></td>
                                <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                <td>
                                    <span class="badge <?php echo getOrderStatusClass($order['order_status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo getPaymentStatusClass($order['payment_status']); ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    <button onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, '<?php echo $order['order_status']; ?>')" class="btn btn-sm btn-warning">Update</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Update Status Modal -->
    <div id="updateStatusModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="hideModal('updateStatusModal')">&times;</span>
            <div class="modal-header">Update Order Status</div>
            <form method="POST" id="updateStatusForm">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="update_order_id">
                <div class="form-group">
                    <label class="form-label">Order Status</label>
                    <select name="order_status" id="update_order_status" class="form-control" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="in_transit">In Transit</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Gas Delivery Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="../js/main.js"></script>
    <script>
        function updateOrderStatus(orderId, currentStatus) {
            document.getElementById('update_order_id').value = orderId;
            document.getElementById('update_order_status').value = currentStatus;
            showModal('updateStatusModal');
        }
    </script>
</body>
</html>

<?php closeDatabaseConnection($conn); ?>
