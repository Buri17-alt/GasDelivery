<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireAdmin();

$conn = getDatabaseConnection();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details
$order_query = "SELECT o.*, u.full_name, u.email, u.phone, u.address 
                FROM orders o 
                JOIN users u ON o.customer_id = u.user_id 
                WHERE o.order_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    header('Location: orders.php');
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items_query = "SELECT oi.*, p.product_name, p.size 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.product_id 
                WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();

// Get delivery person if assigned
$delivery_person = null;
if ($order['delivery_person_id']) {
    $delivery_query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($delivery_query);
    $stmt->bind_param("i", $order['delivery_person_id']);
    $stmt->execute();
    $delivery_person = $stmt->get_result()->fetch_assoc();
}

// Handle assign delivery
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'assign_delivery') {
        $delivery_person_id = intval($_POST['delivery_person_id']);
        
        $stmt = $conn->prepare("UPDATE orders SET delivery_person_id = ?, order_status = 'confirmed' WHERE order_id = ?");
        $stmt->bind_param("ii", $delivery_person_id, $order_id);
        
        if ($stmt->execute()) {
            // Create delivery record
            $stmt = $conn->prepare("INSERT INTO deliveries (order_id, delivery_person_id, delivery_status) VALUES (?, ?, 'assigned')");
            $stmt->bind_param("ii", $order_id, $delivery_person_id);
            $stmt->execute();
            
            redirectWithMessage("order-details.php?id=$order_id", 'Delivery assigned successfully!', 'success');
        }
    }
}

// Get available delivery personnel
$delivery_personnel_query = "SELECT * FROM users WHERE user_type = 'delivery' AND status = 'active'";
$delivery_personnel = $conn->query($delivery_personnel_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin</title>
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
                <h1 class="page-title">Order #<?php echo $order['order_id']; ?></h1>
                <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
            </div>

            <?php displayFlashMessage(); ?>

            <div class="row">
                <div class="col-8">
                    <!-- Order Items -->
                    <div class="card">
                        <div class="card-header">Order Items</div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Size</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $items->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['size']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo formatCurrency($item['unit_price']); ?></td>
                                        <td><?php echo formatCurrency($item['subtotal']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                        <td><strong><?php echo formatCurrency($order['total_amount']); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="card">
                        <div class="card-header">Customer Information</div>
                        <div class="card-body">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                            <p><strong>Delivery Address:</strong><br><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                            <?php if ($order['notes']): ?>
                            <p><strong>Notes:</strong><br><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-4">
                    <!-- Order Status -->
                    <div class="card">
                        <div class="card-header">Order Status</div>
                        <div class="card-body">
                            <p><strong>Order Date:</strong><br><?php echo formatDateTime($order['order_date']); ?></p>
                            <p><strong>Order Status:</strong><br>
                                <span class="badge <?php echo getOrderStatusClass($order['order_status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                </span>
                            </p>
                            <p><strong>Payment Method:</strong><br><?php echo ucfirst($order['payment_method']); ?></p>
                            <p><strong>Payment Status:</strong><br>
                                <span class="badge <?php echo getPaymentStatusClass($order['payment_status']); ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </p>
                            <?php if ($order['delivery_date']): ?>
                            <p><strong>Delivery Date:</strong><br><?php echo formatDateTime($order['delivery_date']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Delivery Assignment -->
                    <div class="card">
                        <div class="card-header">Delivery Assignment</div>
                        <div class="card-body">
                            <?php if ($delivery_person): ?>
                                <p><strong>Assigned To:</strong><br><?php echo htmlspecialchars($delivery_person['full_name']); ?></p>
                                <p><strong>Phone:</strong><br><?php echo htmlspecialchars($delivery_person['phone']); ?></p>
                            <?php else: ?>
                                <p>No delivery person assigned yet</p>
                                <form method="POST">
                                    <input type="hidden" name="action" value="assign_delivery">
                                    <div class="form-group">
                                        <label class="form-label">Assign Delivery Person</label>
                                        <select name="delivery_person_id" class="form-control" required>
                                            <option value="">Select...</option>
                                            <?php while ($dp = $delivery_personnel->fetch_assoc()): ?>
                                            <option value="<?php echo $dp['user_id']; ?>">
                                                <?php echo htmlspecialchars($dp['full_name']); ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">Assign</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
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
